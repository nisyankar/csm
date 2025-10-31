<?php

namespace App\Services;

use App\Models\ReportTemplate;
use App\Models\ProgressPayment;
use App\Models\Timesheet;
use App\Models\FinancialTransaction;
use App\Models\SafetyIncident;
use App\Models\Equipment;
use App\Models\StockMovement;
use App\Models\Quantity;
use App\Models\Project;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Rapor Oluşturma Servisi
 */
class ReportBuilderService
{
    /**
     * Rapor oluştur
     */
    public function generateReport(ReportTemplate $template, array $params = []): array
    {
        // Modüle göre veri al
        $data = $this->getDataByModule($template->module, $params);

        // Rapor türüne göre dosya oluştur
        $filePath = match($template->type) {
            'pdf' => $this->generatePDF($template, $data, $params),
            'excel' => $this->generateExcel($template, $data, $params),
            default => null
        };

        return [
            'success' => true,
            'file_path' => $filePath,
            'template' => $template,
            'data_count' => is_countable($data) ? count($data) : 0,
        ];
    }

    /**
     * PDF raporu oluştur
     */
    protected function generatePDF(ReportTemplate $template, $data, array $params): string
    {
        $fileName = $this->generateFileName($template, 'pdf');
        $filePath = storage_path('app/public/reports/' . $fileName);

        // PDF view oluştur
        $pdf = Pdf::loadView('reports.pdf.' . $template->module, [
            'data' => $data,
            'params' => $params,
            'template' => $template,
            'generated_at' => now(),
        ]);

        $pdf->save($filePath);

        return 'reports/' . $fileName;
    }

    /**
     * Excel raporu oluştur
     */
    protected function generateExcel(ReportTemplate $template, $data, array $params): string
    {
        $fileName = $this->generateFileName($template, 'xlsx');
        $filePath = 'reports/' . $fileName;

        // Excel export class kullan
        $exportClass = $this->getExportClass($template->module);

        if ($exportClass) {
            Excel::store(new $exportClass($data, $params), $filePath, 'public');
        }

        return $filePath;
    }

    /**
     * Modüle göre veri al
     */
    protected function getDataByModule(string $module, array $params)
    {
        return match($module) {
            'progress_payments' => $this->getProgressPaymentsData($params),
            'timesheets' => $this->getTimesheetsData($params),
            'financials' => $this->getFinancialsData($params),
            'safety' => $this->getSafetyData($params),
            'equipment' => $this->getEquipmentData($params),
            'stock' => $this->getStockData($params),
            'quantities' => $this->getQuantitiesData($params),
            'projects' => $this->getProjectsData($params),
            default => []
        };
    }

    /**
     * Hakediş verilerini al
     */
    protected function getProgressPaymentsData(array $params): Collection
    {
        $query = ProgressPayment::with(['project', 'subcontractor', 'workItem']);

        if (!empty($params['project_id'])) {
            $query->where('project_id', $params['project_id']);
        }

        if (!empty($params['status'])) {
            $query->where('status', $params['status']);
        }

        if (!empty($params['date_from'])) {
            $query->where('created_at', '>=', $params['date_from']);
        }

        if (!empty($params['date_to'])) {
            $query->where('created_at', '<=', $params['date_to']);
        }

        return $query->get();
    }

    /**
     * Puantaj verilerini al
     */
    protected function getTimesheetsData(array $params): Collection
    {
        $query = Timesheet::with(['employee', 'project']);

        if (!empty($params['project_id'])) {
            $query->where('project_id', $params['project_id']);
        }

        if (!empty($params['employee_id'])) {
            $query->where('employee_id', $params['employee_id']);
        }

        if (!empty($params['month'])) {
            $query->whereMonth('work_date', $params['month']);
        }

        if (!empty($params['year'])) {
            $query->whereYear('work_date', $params['year']);
        }

        return $query->get();
    }

    /**
     * Finansal verilerini al
     */
    protected function getFinancialsData(array $params): Collection
    {
        $query = FinancialTransaction::with(['project', 'category']);

        if (!empty($params['project_id'])) {
            $query->where('project_id', $params['project_id']);
        }

        if (!empty($params['type'])) {
            $query->where('type', $params['type']);
        }

        if (!empty($params['date_from'])) {
            $query->where('transaction_date', '>=', $params['date_from']);
        }

        if (!empty($params['date_to'])) {
            $query->where('transaction_date', '<=', $params['date_to']);
        }

        return $query->get();
    }

    /**
     * İSG verilerini al
     */
    protected function getSafetyData(array $params): Collection
    {
        $query = SafetyIncident::with(['project', 'reportedBy']);

        if (!empty($params['project_id'])) {
            $query->where('project_id', $params['project_id']);
        }

        if (!empty($params['severity'])) {
            $query->where('severity', $params['severity']);
        }

        if (!empty($params['date_from'])) {
            $query->where('incident_date', '>=', $params['date_from']);
        }

        if (!empty($params['date_to'])) {
            $query->where('incident_date', '<=', $params['date_to']);
        }

        return $query->get();
    }

    /**
     * Ekipman verilerini al
     */
    protected function getEquipmentData(array $params): Collection
    {
        $query = Equipment::with(['currentProject', 'usages', 'maintenances']);

        if (!empty($params['project_id'])) {
            $query->where('current_project_id', $params['project_id']);
        }

        if (!empty($params['type'])) {
            $query->where('type', $params['type']);
        }

        if (!empty($params['status'])) {
            $query->where('status', $params['status']);
        }

        return $query->get();
    }

    /**
     * Stok verilerini al
     */
    protected function getStockData(array $params): Collection
    {
        $query = StockMovement::with(['material', 'warehouse', 'project']);

        if (!empty($params['warehouse_id'])) {
            $query->where('warehouse_id', $params['warehouse_id']);
        }

        if (!empty($params['material_id'])) {
            $query->where('material_id', $params['material_id']);
        }

        if (!empty($params['date_from'])) {
            $query->where('movement_date', '>=', $params['date_from']);
        }

        if (!empty($params['date_to'])) {
            $query->where('movement_date', '<=', $params['date_to']);
        }

        return $query->get();
    }

    /**
     * Metraj verilerini al
     */
    protected function getQuantitiesData(array $params): Collection
    {
        $query = Quantity::with(['project', 'workItem', 'projectStructure']);

        if (!empty($params['project_id'])) {
            $query->where('project_id', $params['project_id']);
        }

        if (!empty($params['work_item_id'])) {
            $query->where('work_item_id', $params['work_item_id']);
        }

        return $query->get();
    }

    /**
     * Proje verilerini al
     */
    protected function getProjectsData(array $params): Collection
    {
        $query = Project::with(['client', 'projectManager']);

        if (!empty($params['status'])) {
            $query->where('status', $params['status']);
        }

        return $query->get();
    }

    /**
     * Dosya adı oluştur
     */
    protected function generateFileName(ReportTemplate $template, string $extension): string
    {
        $slug = str_replace(' ', '_', strtolower($template->name));
        $timestamp = now()->format('Y-m-d_H-i-s');

        return "{$slug}_{$timestamp}.{$extension}";
    }

    /**
     * Export class al
     */
    protected function getExportClass(string $module): ?string
    {
        // Excel export sınıfları oluşturulacak
        // Örnek: App\Exports\ProgressPaymentsExport
        return null;
    }

    /**
     * Rapor özeti oluştur
     */
    public function generateSummary(string $module, array $params = []): array
    {
        $data = $this->getDataByModule($module, $params);

        return [
            'module' => $module,
            'total_records' => is_countable($data) ? count($data) : 0,
            'generated_at' => now(),
            'params' => $params,
        ];
    }
}
