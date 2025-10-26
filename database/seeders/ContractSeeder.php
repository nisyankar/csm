<?php

namespace Database\Seeders;

use App\Models\Contract;
use App\Models\Project;
use App\Models\Subcontractor;
use Illuminate\Database\Seeder;

class ContractSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing projects and subcontractors
        $projects = Project::all();
        $subcontractors = Subcontractor::all();

        if ($projects->isEmpty() || $subcontractors->isEmpty()) {
            $this->command->warn('⚠️  No projects or subcontractors found. Please seed them first.');
            return;
        }

        $this->command->info('🏗️  Creating sample contracts...');

        $contracts = [];

        // Create 15 sample contracts
        foreach ($projects->take(3) as $project) {
            foreach ($subcontractors->take(5) as $index => $subcontractor) {
                $startDate = now()->subMonths(rand(1, 12));
                $endDate = (clone $startDate)->addMonths(rand(6, 24));

                $status = $this->determineStatus($startDate, $endDate);
                $contractValue = rand(100000, 5000000);

                $contracts[] = [
                    'contract_type' => 'subcontractor',
                    'contract_number' => $project->project_code . '-TS-' . date('Y') . '-' . str_pad($index + 1, 4, '0', STR_PAD_LEFT),
                    'contract_name' => $this->getContractName($subcontractor->category_id),
                    'project_id' => $project->id,
                    'subcontractor_id' => $subcontractor->id,
                    'work_description' => $this->getWorkDescription($subcontractor->category_id),
                    'scope_of_work' => 'Proje kapsamındaki tüm ' . strtolower($this->getContractName($subcontractor->category_id)) . ' işlerinin tamamlanması.',
                    'contract_value' => $contractValue,
                    'currency' => 'TRY',
                    'payment_terms' => $this->getPaymentTerms(),
                    'signing_date' => $startDate->copy()->subDays(rand(5, 15)),
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'warranty_amount' => $contractValue * 0.10, // 10% teminat
                    'warranty_type' => $this->getWarrantyType(),
                    'warranty_start_date' => $startDate,
                    'warranty_end_date' => (clone $endDate)->addMonths(12),
                    'status' => $status,
                    'termination_date' => $status === 'terminated' ? $endDate->copy()->subMonths(2) : null,
                    'termination_reason' => $status === 'terminated' ? 'Sözleşme hükümlerine uygun davranılmadığı için feshedilmiştir.' : null,
                    'notes' => 'Seeder ile oluşturulmuş örnek sözleşme.',
                    'documents' => json_encode([]),
                    'created_by' => 1,
                    'updated_by' => null,
                    'created_at' => $startDate->copy()->subDays(rand(15, 30)),
                    'updated_at' => now(),
                ];
            }
        }

        Contract::insert($contracts);

        $this->command->info('✅ Created ' . count($contracts) . ' sample contracts successfully!');
    }

    /**
     * Determine contract status based on dates
     */
    private function determineStatus($startDate, $endDate): string
    {
        $now = now();

        // %10 taslak
        if (rand(1, 10) === 1) {
            return 'draft';
        }

        // %5 feshedilmiş
        if (rand(1, 20) === 1) {
            return 'terminated';
        }

        // Başlangıç tarihinden önce
        if ($startDate->isFuture()) {
            return 'draft';
        }

        // Bitiş tarihi geçmiş
        if ($endDate->isPast()) {
            // %70 tamamlandı, %30 süresi doldu
            return rand(1, 10) <= 7 ? 'completed' : 'expired';
        }

        // Devam eden
        return 'active';
    }

    /**
     * Get contract name based on category
     */
    private function getContractName($categoryId): string
    {
        $names = [
            1 => 'İnşaat İşleri Sözleşmesi',
            2 => 'Elektrik Tesisatı Sözleşmesi',
            3 => 'Mekanik Tesisat Sözleşmesi',
            4 => 'Kaba İnşaat Sözleşmesi',
            5 => 'İnce İnşaat Sözleşmesi',
        ];

        return $names[$categoryId] ?? 'Genel İnşaat Sözleşmesi';
    }

    /**
     * Get work description based on category
     */
    private function getWorkDescription($categoryId): string
    {
        $descriptions = [
            1 => 'Bina inşaat işlerinin tamamlanması, beton, demir, kalıp işleri dahil.',
            2 => 'Elektrik pano montajı, kablo döşeme, aydınlatma ve priz tesisatı.',
            3 => 'Sıhhi tesisat, ısıtma-soğutma sistemleri, havalandırma işleri.',
            4 => 'Temel, beton ve kalıp işleri, kaba inşaat imalatları.',
            5 => 'Sıva, boya, seramik, döşeme ve tavan işleri.',
        ];

        return $descriptions[$categoryId] ?? 'Proje kapsamındaki tüm inşaat işlerinin gerçekleştirilmesi.';
    }

    /**
     * Get payment terms
     */
    private function getPaymentTerms(): string
    {
        $terms = [
            'Aylık hakediş esasına göre ödeme yapılacaktır. Hakediş tutarının %90\'ı 15 gün içinde ödenecektir.',
            'İş bitiminde toplam tutarın %80\'i ödenecek, %20\'si teminat olarak 1 yıl süreyle bloke edilecektir.',
            'Her ay sonu yapılan işlerin hakediş tutarı, 30 gün vadeli olarak ödenecektir.',
            'Fiziki seviyeye göre yapılan ödeme, gerçekleştirilen işin %85\'i peşin, %15\'i iş bitiminde ödenecektir.',
        ];

        return $terms[array_rand($terms)];
    }

    /**
     * Get warranty type
     */
    private function getWarrantyType(): string
    {
        $types = ['bank_letter', 'cash', 'check'];
        return $types[array_rand($types)];
    }
}
