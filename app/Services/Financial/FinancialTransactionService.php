<?php

namespace App\Services\Financial;

use App\Models\FinancialTransaction;
use App\Models\Project;
use App\Models\BudgetVsActual;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FinancialTransactionService
{
    /**
     * Finansal işlem oluştur
     *
     * @param array $data
     * @return FinancialTransaction
     */
    public function createTransaction(array $data): FinancialTransaction
    {
        return DB::transaction(function () use ($data) {
            // Transaction oluştur
            $transaction = FinancialTransaction::create($data);

            // BudgetVsActual'ı güncelle
            $this->updateBudgetVsActual($transaction);

            return $transaction;
        });
    }

    /**
     * Otomatik finansal kayıt oluştur (Puantaj, Satınalma, Hakediş vb.)
     *
     * @param string $sourceModule - 'timesheet', 'purchasing', 'progress_payment', 'sale'
     * @param int $sourceId - Kaynak ID
     * @param array $data - Transaction verileri
     * @return FinancialTransaction
     */
    public function createFromSource(string $sourceModule, int $sourceId, array $data): FinancialTransaction
    {
        // Daha önce oluşturulmuş mu kontrol et
        $existing = FinancialTransaction::fromSource($sourceModule, $sourceId)->first();

        if ($existing) {
            Log::warning("Financial transaction already exists for {$sourceModule}:{$sourceId}");
            return $existing;
        }

        // Source bilgilerini ekle
        $data['source_module'] = $sourceModule;
        $data['source_id'] = $sourceId;

        return $this->createTransaction($data);
    }

    /**
     * Finansal işlemi güncelle
     *
     * @param FinancialTransaction $transaction
     * @param array $data
     * @return FinancialTransaction
     */
    public function updateTransaction(FinancialTransaction $transaction, array $data): FinancialTransaction
    {
        return DB::transaction(function () use ($transaction, $data) {
            $oldYear = $transaction->transaction_date->year;
            $oldMonth = $transaction->transaction_date->month;

            $transaction->update($data);

            // Eski ve yeni dönemlerin BudgetVsActual'ını güncelle
            $this->updateBudgetVsActual($transaction, $oldYear, $oldMonth);

            return $transaction->fresh();
        });
    }

    /**
     * Puantaj onaylandığında finansal kayıt oluştur
     *
     * @param object $timesheet - Timesheet modeli
     * @return FinancialTransaction|null
     */
    public function createFromTimesheet(object $timesheet): ?FinancialTransaction
    {
        // Sadece onaylanmış puantajlar için kayıt oluştur
        if ($timesheet->approval_status !== 'approved') {
            return null;
        }

        // Maaş tutarı 0 ise kayıt oluşturma
        if (!$timesheet->calculated_wage || $timesheet->calculated_wage <= 0) {
            return null;
        }

        // Personel gideri kategorisini bul veya oluştur
        $expenseCategoryId = $this->getOrCreateExpenseCategory('PERSONEL', 'Personel Giderleri');

        return $this->createFromSource('timesheet', $timesheet->id, [
            'project_id' => $timesheet->project_id,
            'transaction_type' => 'expense',
            'category_id' => $expenseCategoryId,
            'transaction_date' => $timesheet->work_date,
            'amount' => $timesheet->calculated_wage,
            'description' => "Personel maaşı - {$timesheet->employee->full_name} - " .
                           $timesheet->work_date->format('d.m.Y'),
            'payment_status' => 'pending',
            'created_by' => auth()->id() ?? 1,
        ]);
    }

    /**
     * Satınalma siparişi oluşturulduğunda finansal kayıt oluştur
     *
     * @param object $purchaseOrder - PurchaseOrder modeli
     * @return FinancialTransaction|null
     */
    public function createFromPurchaseOrder(object $purchaseOrder): ?FinancialTransaction
    {
        // Sadece onaylanmış siparişler için kayıt oluştur
        if ($purchaseOrder->status !== 'approved' && $purchaseOrder->status !== 'received') {
            return null;
        }

        // Malzeme gideri kategorisini bul veya oluştur
        $expenseCategoryId = $this->getOrCreateExpenseCategory('MALZEME', 'Malzeme Giderleri');

        return $this->createFromSource('purchasing', $purchaseOrder->id, [
            'project_id' => $purchaseOrder->project_id,
            'transaction_type' => 'expense',
            'category_id' => $expenseCategoryId,
            'transaction_date' => $purchaseOrder->order_date ?? now(),
            'amount' => $purchaseOrder->total_amount,
            'description' => "Malzeme alımı - " . ($purchaseOrder->supplier?->name ?? 'Tedarikçi'),
            'invoice_number' => $purchaseOrder->po_number,
            'payment_status' => 'pending',
            'created_by' => auth()->id() ?? 1,
        ]);
    }

    /**
     * Hakediş ödendiğinde finansal kayıt oluştur
     *
     * @param object $progressPayment - ProgressPayment modeli
     * @return FinancialTransaction|null
     */
    public function createFromProgressPayment(object $progressPayment): ?FinancialTransaction
    {
        // Sadece ödenmiş hakediş için kayıt oluştur
        if ($progressPayment->status !== 'paid') {
            return null;
        }

        // Taşeron gideri kategorisini bul veya oluştur
        $expenseCategoryId = $this->getOrCreateExpenseCategory('TASERON', 'Taşeron Ödemeleri');

        return $this->createFromSource('progress_payment', $progressPayment->id, [
            'project_id' => $progressPayment->project_id,
            'transaction_type' => 'expense',
            'category_id' => $expenseCategoryId,
            'transaction_date' => $progressPayment->payment_date ?? now(),
            'amount' => $progressPayment->total_amount,
            'description' => "Hakediş ödemesi - " .
                           ($progressPayment->subcontractor?->name ?? 'Taşeron') .
                           " - " . ($progressPayment->workItem?->name ?? 'İş'),
            'payment_status' => 'paid',
            'paid_amount' => $progressPayment->total_amount,
            'created_by' => auth()->id() ?? 1,
        ]);
    }

    /**
     * BudgetVsActual tablosunu güncelle
     *
     * @param FinancialTransaction $transaction
     * @param int|null $oldYear - Güncelleme durumunda eski yıl
     * @param int|null $oldMonth - Güncelleme durumunda eski ay
     * @return void
     */
    protected function updateBudgetVsActual(
        FinancialTransaction $transaction,
        ?int $oldYear = null,
        ?int $oldMonth = null
    ): void {
        $year = $transaction->transaction_date->year;
        $month = $transaction->transaction_date->month;

        // Yeni dönemi güncelle
        $this->recalculateBudgetVsActual(
            $transaction->project_id,
            $year,
            $month,
            $transaction->transaction_type,
            $transaction->category_id
        );

        // Eski dönem farklıysa onu da güncelle
        if ($oldYear && $oldMonth && ($oldYear != $year || $oldMonth != $month)) {
            $this->recalculateBudgetVsActual(
                $transaction->project_id,
                $oldYear,
                $oldMonth,
                $transaction->transaction_type,
                $transaction->category_id
            );
        }
    }

    /**
     * BudgetVsActual için gerçekleşen tutarı yeniden hesapla
     *
     * @param int $projectId
     * @param int $year
     * @param int $month
     * @param string $categoryType
     * @param int $categoryId
     * @return void
     */
    protected function recalculateBudgetVsActual(
        int $projectId,
        int $year,
        int $month,
        string $categoryType,
        int $categoryId
    ): void {
        $budgetRecord = BudgetVsActual::forProject($projectId)
            ->forPeriod($year, $month)
            ->where('category_type', $categoryType)
            ->byCategory($categoryId)
            ->first();

        if ($budgetRecord) {
            $budgetRecord->updateActualAmount();
        }
    }

    /**
     * Gider kategorisini bul veya oluştur
     *
     * @param string $code
     * @param string $name
     * @return int
     */
    protected function getOrCreateExpenseCategory(string $code, string $name): int
    {
        $category = \App\Models\ExpenseCategory::firstOrCreate(
            ['code' => $code],
            [
                'name' => $name,
                'description' => "Otomatik oluşturuldu",
                'is_active' => true,
            ]
        );

        return $category->id;
    }

    /**
     * Gelir kategorisini bul veya oluştur
     *
     * @param string $code
     * @param string $name
     * @return int
     */
    protected function getOrCreateIncomeCategory(string $code, string $name): int
    {
        $category = \App\Models\IncomeCategory::firstOrCreate(
            ['code' => $code],
            [
                'name' => $name,
                'description' => "Otomatik oluşturuldu",
                'is_active' => true,
            ]
        );

        return $category->id;
    }

    /**
     * Proje için kar/zarar özeti
     *
     * @param int $projectId
     * @param int|null $year
     * @param int|null $month
     * @return array
     */
    public function getProfitLossSummary(int $projectId, ?int $year = null, ?int $month = null): array
    {
        $query = FinancialTransaction::forProject($projectId);

        if ($year && $month) {
            $query->forMonth($year, $month);
        } elseif ($year) {
            $query->whereYear('transaction_date', $year);
        }

        $income = (float) $query->clone()->income()->sum('amount');
        $expense = (float) $query->clone()->expense()->sum('amount');
        $profit = $income - $expense;

        return [
            'income' => $income,
            'expense' => $expense,
            'profit' => $profit,
            'profit_margin' => $income > 0 ? round(($profit / $income) * 100, 2) : 0,
        ];
    }

    /**
     * Kategori bazlı kar/zarar raporu
     *
     * @param int $projectId
     * @param int|null $year
     * @param int|null $month
     * @return array
     */
    public function getCategoryBreakdown(int $projectId, ?int $year = null, ?int $month = null): array
    {
        $query = FinancialTransaction::forProject($projectId);

        if ($year && $month) {
            $query->forMonth($year, $month);
        } elseif ($year) {
            $query->whereYear('transaction_date', $year);
        }

        // Gelir kategorileri
        $incomeByCategory = $query->clone()
            ->income()
            ->with('incomeCategory')
            ->select('category_id', DB::raw('SUM(amount) as total'))
            ->groupBy('category_id')
            ->get()
            ->map(function ($item) {
                return [
                    'category_id' => $item->category_id,
                    'category_name' => $item->incomeCategory?->name ?? 'Diğer',
                    'amount' => (float) $item->total,
                ];
            });

        // Gider kategorileri
        $expenseByCategory = $query->clone()
            ->expense()
            ->with('expenseCategory')
            ->select('category_id', DB::raw('SUM(amount) as total'))
            ->groupBy('category_id')
            ->get()
            ->map(function ($item) {
                return [
                    'category_id' => $item->category_id,
                    'category_name' => $item->expenseCategory?->name ?? 'Diğer',
                    'amount' => (float) $item->total,
                ];
            });

        return [
            'income_categories' => $incomeByCategory,
            'expense_categories' => $expenseByCategory,
        ];
    }
}
