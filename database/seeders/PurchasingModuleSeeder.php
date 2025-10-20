<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Supplier;
use App\Models\PurchasingRequest;
use App\Models\PurchasingItem;
use App\Models\SupplierQuotation;
use App\Models\PurchaseOrder;
use App\Models\Delivery;
use App\Models\User;
use App\Models\Project;
use App\Models\Employee;
use Carbon\Carbon;

class PurchasingModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🛒 Satınalma Modülü Seeding Başlıyor...');

        $this->createSuppliers();
        $this->createPurchasingRequests();
        $this->createSupplierQuotations();
        $this->createPurchaseOrders();
        $this->createDeliveries();

        $this->command->info('✅ Satınalma Modülü Seeding Tamamlandı!');
        $this->printSummary();
    }

    /**
     * Tedarikçi oluştur
     */
    private function createSuppliers(): void
    {
        $this->command->info('🏭 Tedarikçiler oluşturuluyor...');

        $suppliers = [
            [
                'company_name' => 'Ankara Beton A.Ş.',
                'tax_number' => '1234567890',
                'tax_office' => 'Ankara Vergi Dairesi',
                'address' => 'Organize Sanayi Bölgesi, 5. Cadde No:15, Ankara',
                'contact_person' => 'Mehmet Yılmaz',
                'phone' => '+90 312 555 1234',
                'mobile' => '+90 532 111 2233',
                'email' => 'info@ankarabeton.com',
                'website' => 'https://ankarabeton.com',
                'categories' => ['concrete'],
                'specialization' => 'C25, C30, C35 beton üretimi ve tedariki',
                'rating' => 4.8,
                'total_orders' => 45,
                'total_amount' => 2850000,
                'on_time_delivery_count' => 42,
                'late_delivery_count' => 3,
                'payment_term_days' => 30,
                'payment_method' => 'transfer',
                'credit_limit' => 500000,
                'has_contract' => true,
                'contract_start_date' => Carbon::now()->subMonths(6),
                'contract_end_date' => Carbon::now()->addMonths(18),
                'status' => 'active',
            ],
            [
                'company_name' => 'Demir Çelik Ticaret Ltd. Şti.',
                'tax_number' => '9876543210',
                'tax_office' => 'İstanbul Vergi Dairesi',
                'address' => 'Demirkapı Mah. Çelik Sok. No:42, İstanbul',
                'contact_person' => 'Ali Kaya',
                'phone' => '+90 216 444 5678',
                'mobile' => '+90 535 222 3344',
                'email' => 'satis@demircelik.com',
                'website' => 'https://demircelik.com',
                'categories' => ['steel'],
                'specialization' => 'İnşaat demiri, nervürlü demir, profil malzemeler',
                'rating' => 4.5,
                'total_orders' => 38,
                'total_amount' => 1950000,
                'on_time_delivery_count' => 35,
                'late_delivery_count' => 3,
                'payment_term_days' => 45,
                'payment_method' => 'transfer',
                'credit_limit' => 400000,
                'has_contract' => true,
                'contract_start_date' => Carbon::now()->subMonths(12),
                'contract_end_date' => Carbon::now()->addMonths(12),
                'status' => 'active',
            ],
            [
                'company_name' => 'Modern İnşaat Malzemeleri A.Ş.',
                'tax_number' => '5555666677',
                'tax_office' => 'Bursa Vergi Dairesi',
                'address' => 'Sanayi Mahallesi, 3. Organize Cd. No:88, Bursa',
                'contact_person' => 'Fatma Şahin',
                'phone' => '+90 224 333 4455',
                'mobile' => '+90 538 777 8899',
                'email' => 'info@moderninsaat.com',
                'website' => 'https://moderninsaat.com',
                'categories' => ['material', 'equipment'],
                'specialization' => 'Çimento, kireç, alçı, kum, çakıl ve inşaat ekipmanları',
                'rating' => 4.7,
                'total_orders' => 52,
                'total_amount' => 980000,
                'on_time_delivery_count' => 48,
                'late_delivery_count' => 4,
                'payment_term_days' => 30,
                'payment_method' => 'transfer',
                'credit_limit' => 300000,
                'has_contract' => false,
                'status' => 'active',
            ],
            [
                'company_name' => 'Teknik Servis ve Danışmanlık Ltd.',
                'tax_number' => '3333444455',
                'tax_office' => 'Ankara Vergi Dairesi',
                'address' => 'Çankaya, Kızılırmak Mah. No:25, Ankara',
                'contact_person' => 'Ahmet Özdemir',
                'phone' => '+90 312 666 7788',
                'mobile' => '+90 533 444 5566',
                'email' => 'info@teknikservis.com',
                'categories' => ['service'],
                'specialization' => 'Mühendislik danışmanlığı, kalite kontrol, statik hesaplama',
                'rating' => 4.9,
                'total_orders' => 28,
                'total_amount' => 560000,
                'on_time_delivery_count' => 27,
                'late_delivery_count' => 1,
                'payment_term_days' => 15,
                'payment_method' => 'transfer',
                'credit_limit' => 200000,
                'has_contract' => true,
                'contract_start_date' => Carbon::now()->subMonths(8),
                'contract_end_date' => Carbon::now()->addMonths(16),
                'status' => 'active',
            ],
            [
                'company_name' => 'Güvenilir Tedarik San. Tic.',
                'tax_number' => '7777888899',
                'tax_office' => 'İzmir Vergi Dairesi',
                'address' => 'Kemeraltı, Ticaret Sok. No:15, İzmir',
                'contact_person' => 'Zeynep Aktaş',
                'phone' => '+90 232 111 2233',
                'mobile' => '+90 536 333 4455',
                'email' => 'satis@guvenilirtedarik.com',
                'categories' => ['material', 'other'],
                'specialization' => 'Genel inşaat malzemeleri tedariki',
                'rating' => 3.8,
                'total_orders' => 15,
                'total_amount' => 350000,
                'on_time_delivery_count' => 12,
                'late_delivery_count' => 3,
                'payment_term_days' => 30,
                'payment_method' => 'cash',
                'credit_limit' => 150000,
                'has_contract' => false,
                'status' => 'active',
            ],
        ];

        foreach ($suppliers as $supplierData) {
            Supplier::create($supplierData);
        }

        $this->command->info('   ✓ ' . count($suppliers) . ' tedarikçi oluşturuldu');
    }

    /**
     * Satınalma talepleri oluştur
     */
    private function createPurchasingRequests(): void
    {
        $this->command->info('📝 Satınalma talepleri oluşturuluyor...');

        $projects = Project::all();
        $users = User::whereHas('roles')->get();
        $admin = User::whereHas('roles', function($q) {
            $q->where('name', 'admin');
        })->first();

        if ($projects->isEmpty() || $users->isEmpty()) {
            $this->command->warn('   ⚠ Proje veya kullanıcı bulunamadı, satınalma talepleri atlanıyor');
            return;
        }

        // Onaylanmış ve sipariş verilmiş talep
        $approvedRequest = PurchasingRequest::create([
            'request_code' => PurchasingRequest::generateRequestCode(),
            'title' => 'C30 Beton Tedariki - Kolon Dökümü',
            'description' => '3. Kat kolon dökümü için C30 beton ihtiyacı',
            'requested_by' => $users->random()->id,
            'project_id' => $projects->first()->id,
            'department_id' => null,
            'status' => 'ordered',
            'urgency' => 'high',
            'category' => 'concrete',
            'required_date' => Carbon::now()->addDays(3),
            'submitted_at' => Carbon::now()->subDays(5),
            'approved_at' => Carbon::now()->subDays(3),
            'ordered_at' => Carbon::now()->subDays(1),
            'approved_by_supervisor' => $admin->id ?? null,
            'approved_by_manager' => $admin->id ?? null,
            'supervisor_notes' => 'Acil ihtiyaç, öncelikli tedarik',
            'manager_notes' => 'Bütçe onaylandı',
            'estimated_total' => 45000,
        ]);

        // Beton için kalemler
        $approvedRequest->items()->createMany([
            [
                'item_name' => 'C30 Beton',
                'description' => 'Kolon dökümü için',
                'specification' => 'Slump: 12-16 cm, Agregat boyutu: 16mm',
                'category' => 'concrete',
                'quantity' => 150,
                'unit' => 'm³',
                'estimated_unit_price' => 300,
                'concrete_class' => 'C30',
                'concrete_slump' => '12-16',
                'concrete_aggregate_size' => 16,
                'required_date' => Carbon::now()->addDays(3),
                'delivery_location' => 'Şantiye Alanı - Blok A',
                'priority' => 1,
            ],
        ]);

        // Bekleyen onay talebi
        $pendingRequest = PurchasingRequest::create([
            'request_code' => PurchasingRequest::generateRequestCode(),
            'title' => 'İnşaat Demiri Tedariki',
            'description' => '4. Kat döşeme için nervürlü demir ihtiyacı',
            'requested_by' => $users->random()->id,
            'project_id' => $projects->first()->id,
            'department_id' => null,
            'status' => 'pending',
            'urgency' => 'normal',
            'category' => 'steel',
            'required_date' => Carbon::now()->addDays(10),
            'submitted_at' => Carbon::now()->subDays(2),
            'estimated_total' => 85000,
        ]);

        $pendingRequest->items()->createMany([
            [
                'item_name' => 'Nervürlü Demir Ø12',
                'description' => 'Döşeme taşıyıcı demiri',
                'specification' => 'TS 708 standardına uygun',
                'category' => 'steel',
                'quantity' => 5000,
                'unit' => 'kg',
                'estimated_unit_price' => 12,
                'steel_diameter' => 12,
                'steel_quality' => 'S420',
                'steel_length' => 12,
                'required_date' => Carbon::now()->addDays(10),
                'delivery_location' => 'Şantiye Depo Alanı',
                'priority' => 2,
            ],
            [
                'item_name' => 'Nervürlü Demir Ø16',
                'description' => 'Döşeme ana taşıyıcı demiri',
                'specification' => 'TS 708 standardına uygun',
                'category' => 'steel',
                'quantity' => 2500,
                'unit' => 'kg',
                'estimated_unit_price' => 13,
                'steel_diameter' => 16,
                'steel_quality' => 'S420',
                'steel_length' => 12,
                'required_date' => Carbon::now()->addDays(10),
                'delivery_location' => 'Şantiye Depo Alanı',
                'priority' => 1,
            ],
        ]);

        // Taslak talep
        $draftRequest = PurchasingRequest::create([
            'request_code' => PurchasingRequest::generateRequestCode(),
            'title' => 'İnşaat Malzemeleri - Çimento ve Kum',
            'description' => 'Sıva işleri için malzeme ihtiyacı',
            'requested_by' => $users->random()->id,
            'project_id' => $projects->first()->id,
            'department_id' => null,
            'status' => 'draft',
            'urgency' => 'low',
            'category' => 'general',
            'required_date' => Carbon::now()->addDays(20),
            'estimated_total' => 15000,
        ]);

        $draftRequest->items()->createMany([
            [
                'item_name' => 'Çimento - CEM I 42.5 R',
                'description' => 'Sıva harcı için',
                'quantity' => 200,
                'unit' => 'çuval (50kg)',
                'estimated_unit_price' => 45,
                'required_date' => Carbon::now()->addDays(20),
                'delivery_location' => 'Şantiye Malzeme Deposu',
                'priority' => 3,
            ],
            [
                'item_name' => 'İnce Kum (0-4mm)',
                'description' => 'Sıva harcı karışımı için',
                'quantity' => 15,
                'unit' => 'ton',
                'estimated_unit_price' => 250,
                'required_date' => Carbon::now()->addDays(20),
                'delivery_location' => 'Şantiye Kum Deposu',
                'priority' => 3,
            ],
        ]);

        $this->command->info('   ✓ 3 satınalma talebi ve 5 kalem oluşturuldu');
    }

    /**
     * Tedarikçi teklifleri oluştur
     */
    private function createSupplierQuotations(): void
    {
        $this->command->info('💰 Tedarikçi teklifleri oluşturuluyor...');

        $request = PurchasingRequest::where('status', 'ordered')->first();
        if (!$request) {
            $this->command->warn('   ⚠ Onaylanmış talep bulunamadı, teklifler atlanıyor');
            return;
        }

        $suppliers = Supplier::whereJsonContains('categories', 'concrete')->get();

        foreach ($suppliers->take(2) as $index => $supplier) {
            $isSelected = $index === 0; // İlk teklif seçilmiş olsun

            SupplierQuotation::create([
                'purchasing_request_id' => $request->id,
                'supplier_id' => $supplier->id,
                'quotation_number' => 'QT-2025-' . str_pad($index + 1, 4, '0', STR_PAD_LEFT),
                'quotation_date' => Carbon::now()->subDays(4),
                'valid_until' => Carbon::now()->addDays(26),
                'items' => [
                    [
                        'item_name' => 'C30 Beton',
                        'quantity' => 150,
                        'unit' => 'm³',
                        'unit_price' => $index === 0 ? 300 : 310,
                        'total_price' => $index === 0 ? 45000 : 46500,
                    ]
                ],
                'subtotal' => $index === 0 ? 45000 : 46500,
                'tax_rate' => 20,
                'tax_amount' => $index === 0 ? 9000 : 9300,
                'discount_rate' => $index === 0 ? 5 : 0,
                'discount_amount' => $index === 0 ? 2250 : 0,
                'shipping_cost' => $index === 0 ? 1000 : 1500,
                'total_amount' => $index === 0 ? 52750 : 57300,
                'delivery_days' => $index === 0 ? 2 : 3,
                'delivery_terms' => 'Şantiye alanına teslim',
                'payment_terms' => '30 gün vadeli',
                'status' => $isSelected ? 'selected' : 'submitted',
                'is_selected' => $isSelected,
                'selected_at' => $isSelected ? Carbon::now()->subDays(2) : null,
                'selected_by' => $isSelected ? User::first()->id : null,
                'notes' => $isSelected ? 'En uygun fiyat ve teslimat süresi' : null,
            ]);
        }

        $this->command->info('   ✓ 2 tedarikçi teklifi oluşturuldu');
    }

    /**
     * Satın alma siparişleri oluştur
     */
    private function createPurchaseOrders(): void
    {
        $this->command->info('📦 Satın alma siparişleri oluşturuluyor...');

        $request = PurchasingRequest::where('status', 'ordered')->first();
        $quotation = SupplierQuotation::where('is_selected', true)->first();

        if (!$request || !$quotation) {
            $this->command->warn('   ⚠ Gerekli veriler bulunamadı, siparişler atlanıyor');
            return;
        }

        $order = PurchaseOrder::create([
            'purchasing_request_id' => $request->id,
            'supplier_id' => $quotation->supplier_id,
            'supplier_quotation_id' => $quotation->id,
            'order_date' => Carbon::now()->subDays(1),
            'expected_delivery_date' => Carbon::now()->addDays(2),
            'subtotal' => 45000,
            'tax_amount' => 9000,
            'discount_amount' => 2250,
            'shipping_cost' => 1000,
            'total_amount' => 52750,
            'payment_method' => 'transfer',
            'payment_term_days' => 30,
            'payment_due_date' => Carbon::now()->addDays(29),
            'payment_status' => 'pending',
            'status' => 'approved',
            'approved_by' => User::first()->id,
            'approved_at' => Carbon::now()->subDays(1),
            'delivery_address' => 'Şantiye Alanı - Kozyatağı İnşaat Projesi, Blok A',
            'delivery_contact' => 'Mehmet Yılmaz - +90 532 111 2233',
            'special_instructions' => 'Pompa ile dökülecek, pompa beton özelliğinde olmalı',
            'notes' => 'Acil sipariş - Teslimat saati: 08:00-10:00 arası',
        ]);

        $this->command->info('   ✓ 1 satın alma siparişi oluşturuldu');
    }

    /**
     * Teslimatlar oluştur
     */
    private function createDeliveries(): void
    {
        $this->command->info('🚚 Teslimatlar oluşturuluyor...');

        $order = PurchaseOrder::first();
        if (!$order) {
            $this->command->warn('   ⚠ Sipariş bulunamadı, teslimatlar atlanıyor');
            return;
        }

        // Planlanmış teslimat
        Delivery::create([
            'purchase_order_id' => $order->id,
            'delivery_date' => Carbon::now()->addDays(2),
            'delivery_time' => '08:30',
            'waybill_number' => 'WB-2025-001234',
            'waybill_date' => Carbon::now()->addDays(2),
            'delivery_address' => 'Şantiye Alanı - Kozyatağı İnşaat Projesi, Blok A',
            'driver_name' => 'Ahmet Kılıç',
            'vehicle_plate' => '34 ABC 123',
            'driver_phone' => '+90 532 999 8877',
            'items_count' => 1,
            'is_complete' => true,
            'status' => 'scheduled',
            'notes' => 'Pompa beton - Sabah erken teslimat',
        ]);

        // Tamamlanmış teslimat (geçmiş tarih)
        $completedDelivery = Delivery::create([
            'purchase_order_id' => $order->id,
            'delivery_date' => Carbon::now()->subDays(5),
            'delivery_time' => '09:00',
            'waybill_number' => 'WB-2025-001100',
            'waybill_date' => Carbon::now()->subDays(5),
            'invoice_number' => 'INV-2025-5550',
            'invoice_date' => Carbon::now()->subDays(5),
            'invoice_amount' => 52750,
            'status' => 'completed',
            'received_by' => User::first()->id,
            'received_at' => Carbon::now()->subDays(5)->setTime(9, 45),
            'receiver_name' => 'Mehmet Yılmaz',
            'quality_check' => 'passed',
            'quality_notes' => 'Beton kalitesi uygun, pompa ile döküm yapıldı',
            'delivery_address' => 'Şantiye Alanı - Kozyatağı İnşaat Projesi, Blok A',
            'driver_name' => 'Ali Yıldız',
            'vehicle_plate' => '34 XYZ 789',
            'driver_phone' => '+90 535 888 7766',
            'items_count' => 1,
            'is_complete' => true,
            'notes' => 'Teslimat sorunsuz tamamlandı',
        ]);

        $this->command->info('   ✓ 2 teslimat kaydı oluşturuldu');
    }

    /**
     * Özet yazdır
     */
    private function printSummary(): void
    {
        $this->command->info('');
        $this->command->info('📊 Oluşturulan Veriler:');
        $this->command->info('├── Tedarikçiler: ' . Supplier::count());
        $this->command->info('├── Satınalma Talepleri: ' . PurchasingRequest::count());
        $this->command->info('├── Satınalma Kalemleri: ' . PurchasingItem::count());
        $this->command->info('├── Tedarikçi Teklifleri: ' . SupplierQuotation::count());
        $this->command->info('├── Satın Alma Siparişleri: ' . PurchaseOrder::count());
        $this->command->info('└── Teslimatlar: ' . Delivery::count());
        $this->command->info('');
    }
}
