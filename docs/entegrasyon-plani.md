# Entegrasyon Planı
## 🔗 Modüller Arası Veri Akışı

**Son Güncelleme:** 25 Ekim 2025
**Versiyon:** 1.0

---

## 📊 GENEL BAKIŞ

SPT sistemindeki tüm modüller birbirine entegre çalışır. Bu dokümanda, modüller arası veri akışları, otomatik kayıt servisleri ve event/listener yapısı açıklanmaktadır.

---

## 🎯 OTOMATIK FİNANSAL ENTEGRASYON

### Merkezi Tablo: financial_transactions

Tüm gelir/gider kayıtları bu tabloda toplanır:
- `source_module`: Kaynak modül adı
- `source_id`: İlgili kayıt ID'si

### Otomatik Kayıt Akışları

#### 1. Puantaj → Personel Gideri
```php
// TimesheetController::approve()
Event::dispatch(new TimesheetApproved($timesheet));

// Listener: RecordPayrollExpense
FinancialTransaction::create([
    'project_id' => $timesheet->project_id,
    'transaction_type' => 'expense',
    'source_module' => 'payroll',
    'source_id' => $timesheet->id,
    'amount' => $timesheet->calculated_wage
]);
```

#### 2. Satınalma → Malzeme Gideri
```php
// DeliveryController::confirm()
Event::dispatch(new DeliveryConfirmed($delivery));

// Listener: RecordMaterialExpense
FinancialTransaction::create([
    'source_module' => 'purchasing',
    'amount' => $delivery->total_cost
]);
```

#### 3. Hakediş → Taşeron Gideri
```php
// ProgressPaymentController::approve()
Event::dispatch(new ProgressPaymentApproved($payment));

// Listener: RecordSubcontractorExpense
FinancialTransaction::create([
    'source_module' => 'progress_payment',
    'amount' => $payment->total_amount
]);
```

#### 4. Satış → Gelir Kaydı
```php
// SalePaymentController::recordPayment()
Event::dispatch(new SalePaymentReceived($payment));

// Listener: RecordSaleIncome
FinancialTransaction::create([
    'transaction_type' => 'income',
    'source_module' => 'sales',
    'amount' => $payment->amount
]);
```

---

## 🔄 MODÜL ARASI İLİŞKİLER

### Keşif & Metraj ↔ Hakediş
```php
// ProgressPaymentController::create()
$quantity = Quantity::where('work_item_id', $item->id)
    ->where('project_id', $project->id)
    ->sum('completed_quantity');

$payment->completed_quantity = $quantity;
$payment->total_amount = $quantity * $item->unit_price;
```

### Sözleşme ↔ Taşeron/Tedarikçi/Müşteri
```php
// Contract model (polymorphic)
public function contractable()
{
    return $this->morphTo();
}

// Subcontractor model
public function contracts()
{
    return $this->morphMany(Contract::class, 'contractable');
}
```

### Stok ↔ Satınalma ↔ Günlük Rapor
```php
// Satınalma teslimatı → Stok artışı
StockMovement::create([
    'warehouse_id' => $warehouse->id,
    'material_id' => $material->id,
    'movement_type' => 'in',
    'quantity' => $delivery->quantity,
    'source_module' => 'purchasing',
    'source_id' => $delivery->id
]);

// Günlük rapor → Stok azalışı
StockMovement::create([
    'movement_type' => 'out',
    'source_module' => 'daily_report',
    'source_id' => $report->id
]);
```

---

## 📡 EVENT/LISTENER YAPISI

### Planlanan Event'ler

| Event | Listener | Aksiyon |
|-------|----------|---------|
| TimesheetApproved | RecordPayrollExpense | Maaş gideri kaydı |
| DeliveryConfirmed | RecordMaterialExpense, UpdateStock | Gider + stok artışı |
| ProgressPaymentApproved | RecordSubcontractorExpense | Taşeron gideri |
| SalePaymentReceived | RecordSaleIncome | Gelir kaydı |
| QuantityMeasured | UpdateProgressPayment | Hakediş güncelleme |
| ContractExpiring | SendExpiryNotification | Sözleşme uyarısı |
| PermitExpiring | SendPermitAlert | Ruhsat uyarısı |

---

## 🛠️ SHARED SERVICES

### FinancialTransactionService
```php
class FinancialTransactionService
{
    public static function record(array $data)
    {
        return FinancialTransaction::create([
            'project_id' => $data['project_id'],
            'transaction_type' => $data['type'],
            'amount' => $data['amount'],
            'source_module' => $data['source_module'],
            'source_id' => $data['source_id'],
            'description' => $data['description'] ?? null,
            'created_by' => auth()->id()
        ]);
    }
}
```

### StockMovementService
```php
class StockMovementService
{
    public static function recordIn($warehouse_id, $material_id, $quantity, $source)
    {
        // Stok artışı
    }
    
    public static function recordOut($warehouse_id, $material_id, $quantity, $source)
    {
        // Stok azalışı
    }
    
    public static function transfer($from_warehouse, $to_warehouse, $material_id, $quantity)
    {
        // Depolar arası transfer
    }
}
```

---

## 🔐 YETKİ ENTEGRASYONU

### Proje Bazlı Filtrele
```php
// Middleware: CheckProjectAccess
class CheckProjectAccess
{
    public function handle($request, Closure $next)
    {
        $project_id = $request->route('project');
        
        if (!auth()->user()->hasProjectAccess($project_id)) {
            abort(403, 'Bu projeye erişim yetkiniz yok.');
        }
        
        return $next($request);
    }
}
```

---

**İlgili Fazlar:**
- [Faz 2: Operasyonel Çekirdek](./faz2-operasyonel-moduller.md)
- [Faz 3: Gelişmiş Modüller](./faz3-gelismis-moduller.md)
