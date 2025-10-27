<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Customer;
use App\Models\UnitSale;
use App\Models\SalePayment;
use App\Models\Project;
use App\Models\ProjectUnit;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SalesModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::beginTransaction();
        try {
            $this->command->info('🏘️ Satış ve Tapu Yönetimi Modülü Seeder başlatılıyor...');

            // Get first project and user
            $project = Project::first();
            $user = User::first();

            if (!$project || !$user) {
                $this->command->error('⚠️ Proje veya kullanıcı bulunamadı. Önce ilgili seeder\'ları çalıştırın.');
                return;
            }

            // Get or create customers
            $this->command->info('👥 Müşteriler kontrol ediliyor...');
            $existingCustomers = Customer::all();

            if ($existingCustomers->count() >= 10) {
                $this->command->info('✅ ' . $existingCustomers->count() . ' mevcut müşteri bulundu.');
                $customers = $existingCustomers->take(10);
            } else {
                $this->command->info('👥 Müşteriler oluşturuluyor...');
                $customers = $this->createCustomers($user);
                $this->command->info('✅ ' . count($customers) . ' müşteri oluşturuldu.');
            }

            // Get available units through project structures
            $availableUnits = ProjectUnit::whereHas('structure', function ($query) use ($project) {
                    $query->where('project_id', $project->id);
                })
                ->where('is_sold', false)
                ->limit(10)
                ->get();

            if ($availableUnits->isEmpty()) {
                $this->command->warn('⚠️ Satış yapılabilecek uygun birim bulunamadı.');
                DB::commit();
                return;
            }

            // Create unit sales
            $this->command->info('🏠 Satışlar oluşturuluyor...');
            $unitSales = $this->createUnitSales($project, $availableUnits, $customers, $user);
            $this->command->info('✅ ' . count($unitSales) . ' satış oluşturuldu.');

            // Create sale payments
            $this->command->info('💰 Ödeme planları oluşturuluyor...');
            $paymentsCount = $this->createSalePayments($unitSales, $user);
            $this->command->info('✅ ' . $paymentsCount . ' ödeme kaydı oluşturuldu.');

            DB::commit();
            $this->command->info('🎉 Satış ve Tapu Yönetimi Modülü Seeder başarıyla tamamlandı!');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('❌ Seeder hatası: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create sample customers
     */
    private function createCustomers($user): array
    {
        $customers = [];

        // Individual customers
        $individualCustomers = [
            ['first_name' => 'Ahmet', 'last_name' => 'Yılmaz', 'email' => 'ahmet.yilmaz@example.com', 'phone' => '05321234567', 'tc_number' => '12345678901'],
            ['first_name' => 'Ayşe', 'last_name' => 'Demir', 'email' => 'ayse.demir@example.com', 'phone' => '05321234568', 'tc_number' => '12345678902'],
            ['first_name' => 'Mehmet', 'last_name' => 'Kaya', 'email' => 'mehmet.kaya@example.com', 'phone' => '05321234569', 'tc_number' => '12345678903'],
            ['first_name' => 'Fatma', 'last_name' => 'Çelik', 'email' => 'fatma.celik@example.com', 'phone' => '05321234570', 'tc_number' => '12345678904'],
            ['first_name' => 'Ali', 'last_name' => 'Öztürk', 'email' => 'ali.ozturk@example.com', 'phone' => '05321234571', 'tc_number' => '12345678905'],
            ['first_name' => 'Zeynep', 'last_name' => 'Şahin', 'email' => 'zeynep.sahin@example.com', 'phone' => '05321234572', 'tc_number' => '12345678906'],
            ['first_name' => 'Mustafa', 'last_name' => 'Aydın', 'email' => 'mustafa.aydin@example.com', 'phone' => '05321234573', 'tc_number' => '12345678907'],
            ['first_name' => 'Emine', 'last_name' => 'Arslan', 'email' => 'emine.arslan@example.com', 'phone' => '05321234574', 'tc_number' => '12345678908'],
        ];

        foreach ($individualCustomers as $data) {
            $customers[] = Customer::create(array_merge($data, [
                'customer_type' => 'individual',
                'customer_status' => 'active',
                'city' => 'İstanbul',
                'district' => 'Kadıköy',
                'address' => 'Örnek Mahallesi, Örnek Sokak, No: 1',
                'birth_date' => now()->subYears(rand(25, 50)),
                'gender' => rand(0, 1) ? 'male' : 'female',
                'marital_status' => ['single', 'married'][rand(0, 1)],
                'nationality' => 'TC',
                'reference_source' => ['Website', 'Referans', 'Sosyal Medya', 'Gazete İlanı'][rand(0, 3)],
                'satisfaction_score' => rand(7, 10),
                'created_by' => $user->id,
            ]));
        }

        // Corporate customers
        $corporateCustomers = [
            ['company_name' => 'ABC İnşaat A.Ş.', 'email' => 'info@abcinsaat.com', 'phone' => '02161234567', 'tax_office' => 'Kadıköy', 'tax_number' => '1234567890'],
            ['company_name' => 'XYZ Holding A.Ş.', 'email' => 'info@xyzholding.com', 'phone' => '02161234568', 'tax_office' => 'Beşiktaş', 'tax_number' => '1234567891'],
        ];

        foreach ($corporateCustomers as $data) {
            $customers[] = Customer::create(array_merge($data, [
                'first_name' => 'Kurumsal',
                'last_name' => 'Müşteri',
                'customer_type' => 'corporate',
                'customer_status' => 'active',
                'city' => 'İstanbul',
                'district' => 'Şişli',
                'address' => 'İş Merkezi, Kat: 5',
                'nationality' => 'TC',
                'reference_source' => 'İş Ortağı',
                'satisfaction_score' => rand(8, 10),
                'created_by' => $user->id,
            ]));
        }

        return $customers;
    }

    /**
     * Create sample unit sales
     */
    private function createUnitSales($project, $availableUnits, $customers, $user): array
    {
        $unitSales = [];
        $saleTypes = ['reservation', 'sale', 'presale'];
        $paymentMethods = ['cash', 'installment', 'bank_loan', 'mixed'];
        $statuses = ['reserved', 'contracted', 'in_payment', 'completed'];

        foreach ($availableUnits as $index => $unit) {
            $customer = $customers[$index % count($customers)];
            $listPrice = rand(500000, 2000000);
            $discountPercentage = rand(0, 15);
            $discountAmount = ($listPrice * $discountPercentage) / 100;
            $finalPrice = $listPrice - $discountAmount;
            $downPayment = $finalPrice * 0.3; // 30% peşinat
            $installmentCount = rand(12, 60);
            $monthlyInstallment = ($finalPrice - $downPayment) / $installmentCount;

            $contractDate = now()->subDays(rand(1, 180));

            $unitSale = UnitSale::create([
                'project_id' => $project->id,
                'project_unit_id' => $unit->id,
                'customer_id' => $customer->id,
                'sale_type' => $saleTypes[rand(0, 2)],
                'list_price' => $listPrice,
                'discount_amount' => $discountAmount,
                'discount_percentage' => $discountPercentage,
                'final_price' => $finalPrice,
                'currency' => 'TRY',
                'down_payment' => $downPayment,
                'installment_count' => $installmentCount,
                'monthly_installment' => $monthlyInstallment,
                'payment_method' => $paymentMethods[rand(0, 3)],
                'reservation_date' => $contractDate->copy()->subDays(7),
                'sale_date' => $contractDate->copy()->subDays(3),
                'contract_date' => $contractDate,
                'delivery_date' => $contractDate->copy()->addMonths($installmentCount + 6),
                'deed_status' => 'not_transferred',
                'status' => $statuses[rand(0, 3)],
                'sales_agent_id' => $user->id,
                'commission_percentage' => 2.5,
                'commission_amount' => $finalPrice * 0.025,
                'created_by' => $user->id,
            ]);

            // Update unit status
            $unit->update(['status' => 'sold']);

            $unitSales[] = $unitSale;
        }

        return $unitSales;
    }

    /**
     * Create sale payments
     */
    private function createSalePayments($unitSales, $user): int
    {
        $paymentsCount = 0;

        foreach ($unitSales as $unitSale) {
            // Create down payment
            if ($unitSale->down_payment > 0) {
                $downPaymentStatus = ['pending', 'paid'][rand(0, 1)];
                SalePayment::create([
                    'unit_sale_id' => $unitSale->id,
                    'customer_id' => $unitSale->customer_id,
                    'installment_number' => 0,
                    'payment_type' => 'down_payment',
                    'amount' => $unitSale->down_payment,
                    'paid_amount' => $downPaymentStatus === 'paid' ? $unitSale->down_payment : 0,
                    'remaining_amount' => $downPaymentStatus === 'paid' ? 0 : $unitSale->down_payment,
                    'currency' => $unitSale->currency,
                    'due_date' => $unitSale->contract_date,
                    'payment_date' => $downPaymentStatus === 'paid' ? $unitSale->contract_date : null,
                    'payment_method' => $downPaymentStatus === 'paid' ? 'bank_transfer' : null,
                    'status' => $downPaymentStatus,
                    'approved_by' => $downPaymentStatus === 'paid' ? $user->id : null,
                    'approved_at' => $downPaymentStatus === 'paid' ? $unitSale->contract_date : null,
                    'created_by' => $user->id,
                ]);
                $paymentsCount++;
            }

            // Create installments
            for ($i = 1; $i <= $unitSale->installment_count; $i++) {
                $dueDate = Carbon::parse($unitSale->contract_date)->addMonths($i);
                $isPast = $dueDate->isPast();

                // Past installments are more likely to be paid
                if ($isPast) {
                    $status = ['paid', 'paid', 'paid', 'overdue'][rand(0, 3)];
                } else {
                    $status = 'pending';
                }

                $paidAmount = $status === 'paid' ? $unitSale->monthly_installment : 0;

                SalePayment::create([
                    'unit_sale_id' => $unitSale->id,
                    'customer_id' => $unitSale->customer_id,
                    'installment_number' => $i,
                    'payment_type' => 'installment',
                    'amount' => $unitSale->monthly_installment,
                    'paid_amount' => $paidAmount,
                    'remaining_amount' => $unitSale->monthly_installment - $paidAmount,
                    'currency' => $unitSale->currency,
                    'due_date' => $dueDate,
                    'payment_date' => $status === 'paid' ? $dueDate : null,
                    'payment_method' => $status === 'paid' ? ['bank_transfer', 'credit_card', 'cash'][rand(0, 2)] : null,
                    'status' => $status,
                    'approved_by' => $status === 'paid' ? $user->id : null,
                    'approved_at' => $status === 'paid' ? $dueDate : null,
                    'created_by' => $user->id,
                ]);
                $paymentsCount++;
            }
        }

        return $paymentsCount;
    }
}
