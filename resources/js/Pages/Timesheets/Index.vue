<template>
  <AppLayout
    :title="`Puantaj Yönetimi - ${filters.month || 'Bu Ay'} - SPT İnşaat Puantaj Sistemi`"
    :full-width="true"
  >
    <!-- Full-width header -->
    <template #fullWidthHeader>
      <div class="bg-gradient-to-r from-green-600 via-green-700 to-green-800 border-b border-green-900/20 w-full">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
          <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <!-- Header Info -->
            <div class="flex-1 min-w-0">
              <div class="flex items-center space-x-4 mb-3">
                <div class="flex-shrink-0 w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm">
                  <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                  </svg>
                </div>
                <div>
                  <h1 class="text-2xl lg:text-3xl font-bold text-white">Puantaj Yönetimi</h1>
                  <p class="text-green-100 text-sm mt-1">Çalışan puantaj kayıtlarını görüntüleyin ve yönetin</p>
                </div>
              </div>

              <!-- Stats Row -->
              <div class="flex flex-wrap items-center gap-4 lg:gap-6">
                <div class="bg-white/10 backdrop-blur-sm rounded-lg px-3 py-2">
                  <span class="text-green-100 text-sm">Toplam Kayıt:</span>
                  <span class="font-semibold text-white ml-1">{{ stats.total_timesheets || 0 }}</span>
                </div>
                <div class="flex items-center space-x-4 text-sm">
                  <span class="flex items-center text-green-100">
                    <div class="w-2 h-2 bg-yellow-400 rounded-full mr-2"></div>
                    Beklemede: <span class="text-white font-medium ml-1">{{ stats.pending_timesheets || 0 }}</span>
                  </span>
                  <span class="flex items-center text-green-100">
                    <div class="w-2 h-2 bg-green-400 rounded-full mr-2"></div>
                    Onaylı: <span class="text-white font-medium ml-1">{{ stats.approved_timesheets || 0 }}</span>
                  </span>
                </div>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex-shrink-0 flex items-center space-x-3">
              <button
                @click="showExportModal = true"
                class="inline-flex items-center px-4 py-2 bg-white/10 backdrop-blur-sm border border-white/30 text-white text-sm font-medium rounded-lg hover:bg-white/20 transition-all duration-200"
              >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Dışa Aktar
              </button>

              <!-- Dropdown for New Timesheet -->
              <div class="relative">
                <button
                  @click="showNewEntryMenu = !showNewEntryMenu"
                  class="inline-flex items-center px-4 py-2 bg-white text-green-600 text-sm font-medium rounded-lg hover:bg-green-50 shadow-lg hover:shadow-xl transition-all duration-200"
                >
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                  </svg>
                  Yeni Puantaj
                  <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                  </svg>
                </button>

                <!-- Dropdown Menu -->
                <div
                  v-if="showNewEntryMenu"
                  v-click-away="() => showNewEntryMenu = false"
                  class="absolute right-0 mt-2 w-72 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50"
                >
                  <div class="py-1">
                    <button
                      @click="$inertia.visit(route('timesheets.create')); showNewEntryMenu = false"
                      class="flex items-center w-full px-4 py-3 text-sm text-gray-700 hover:bg-gray-100 transition"
                    >
                      <svg class="mr-3 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                      </svg>
                      <div class="text-left">
                        <div class="font-medium">Tekli Giriş</div>
                        <div class="text-xs text-gray-500">Tek personel için detaylı giriş</div>
                      </div>
                    </button>

                    <button
                      @click="$inertia.visit(route('timesheets.bulk-entry')); showNewEntryMenu = false"
                      class="flex items-center w-full px-4 py-3 text-sm text-gray-700 hover:bg-gray-100 transition"
                    >
                      <svg class="mr-3 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                      </svg>
                      <div class="text-left">
                        <div class="font-medium">Toplu Giriş (Takvim)</div>
                        <div class="text-xs text-gray-500">Aylık toplu puantaj girişi</div>
                      </div>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Breadcrumb inside header -->
        <div class="bg-black/10 border-t border-white/10">
          <div class="w-full px-4 sm:px-6 lg:px-8">
            <div class="flex items-center space-x-2 py-2">
              <ol class="flex items-center space-x-2">
                <li>
                  <Link
                    :href="route('dashboard')"
                    class="text-green-100 hover:text-white transition-colors"
                  >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                    </svg>
                    <span class="sr-only">Ana Sayfa</span>
                  </Link>
                </li>
                <li class="flex items-center">
                  <svg class="h-3 w-3 text-green-200 mx-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                  </svg>
                  <span class="text-xs font-medium text-white">Puantaj</span>
                </li>
              </ol>
            </div>
          </div>
        </div>
      </div>
    </template>

    <!-- Main Content -->
    <div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
      <Card>
        <div class="flex items-center">
          <div class="flex-shrink-0 bg-blue-100 rounded-lg p-3">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-500">Toplam Kayıt</p>
            <p class="text-2xl font-bold text-gray-900">{{ stats.total_timesheets || 0 }}</p>
          </div>
        </div>
      </Card>

      <Card>
        <div class="flex items-center">
          <div class="flex-shrink-0 bg-yellow-100 rounded-lg p-3">
            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-500">Onay Bekleyen</p>
            <p class="text-2xl font-bold text-gray-900">{{ stats.pending_approval || 0 }}</p>
          </div>
        </div>
      </Card>

      <Card>
        <div class="flex items-center">
          <div class="flex-shrink-0 bg-green-100 rounded-lg p-3">
            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-500">Onaylanan</p>
            <p class="text-2xl font-bold text-gray-900">{{ stats.approved || 0 }}</p>
          </div>
        </div>
      </Card>

      <Card>
        <div class="flex items-center">
          <div class="flex-shrink-0 bg-purple-100 rounded-lg p-3">
            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-500">Toplam Ücret</p>
            <p class="text-2xl font-bold text-gray-900">₺{{ formatNumber(stats.total_wages || 0) }}</p>
          </div>
        </div>
      </Card>
    </div>

    <!-- Filters -->
    <Card class="mb-6">
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Ay Seçin</label>
          <Input
            v-model="filterForm.month"
            type="month"
            @change="applyFilters"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Çalışan</label>
          <Select
            v-model="filterForm.employee_id"
            :options="employeeOptions"
            placeholder="Tüm çalışanlar"
            @change="applyFilters"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Proje</label>
          <Select
            v-model="filterForm.project_id"
            :options="projectOptions"
            placeholder="Tüm projeler"
            @change="applyFilters"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Onay Durumu</label>
          <Select
            v-model="filterForm.approval_status"
            :options="approvalStatusOptions"
            placeholder="Tüm durumlar"
            @change="applyFilters"
          />
        </div>
      </div>

      <div class="mt-4 flex flex-wrap gap-2">
        <Button
          v-for="quickFilter in quickFilters"
          :key="quickFilter.value"
          variant="outline"
          size="sm"
          :class="{ 'bg-blue-50 border-blue-500 text-blue-700': filters.filter === quickFilter.value }"
          @click="applyQuickFilter(quickFilter.value)"
        >
          {{ quickFilter.label }}
        </Button>

        <Button
          v-if="hasActiveFilters"
          variant="ghost"
          size="sm"
          @click="clearFilters"
        >
          <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
          Filtreleri Temizle
        </Button>
      </div>
    </Card>

    <!-- Timesheets Table -->
    <Card>
      <div v-if="selectedTimesheets.length > 0" class="mb-4 flex items-center justify-between bg-blue-50 border border-blue-200 rounded-lg p-4">
        <span class="text-sm text-blue-700 font-medium">
          {{ selectedTimesheets.length }} kayıt seçildi
        </span>
        <div class="flex space-x-2">
          <Button
            variant="primary"
            size="sm"
            @click="bulkApprove"
          >
            Toplu Onayla
          </Button>
          <Button
            variant="outline"
            size="sm"
            @click="selectedTimesheets = []"
          >
            Seçimi Kaldır
          </Button>
        </div>
      </div>

      <Table
        :columns="columns"
        :data="timesheets.data"
        :loading="loading"
        :sortable="true"
        :selectable="canBulkApprove"
        v-model:selected="selectedTimesheets"
        @sort="handleSort"
      >
        <template #cell-employee="{ row }">
          <div class="flex items-center">
            <div class="flex-shrink-0 h-10 w-10">
              <div class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center">
                <span class="text-sm font-medium text-gray-600">
                  {{ row.employee.first_name.charAt(0) }}{{ row.employee.last_name.charAt(0) }}
                </span>
              </div>
            </div>
            <div class="ml-3">
              <p class="text-sm font-medium text-gray-900">
                {{ row.employee.first_name }} {{ row.employee.last_name }}
              </p>
              <p class="text-sm text-gray-500">{{ row.employee.employee_code }}</p>
            </div>
          </div>
        </template>

        <template #cell-work_date="{ row }">
          <div>
            <p class="text-sm font-medium text-gray-900">{{ formatDate(row.work_date) }}</p>
            <p class="text-xs text-gray-500">{{ getDayName(row.work_date) }}</p>
          </div>
        </template>

        <template #cell-project="{ row }">
          <div v-if="row.project">
            <p class="text-sm text-gray-900">{{ row.project.name }}</p>
            <p v-if="row.department" class="text-xs text-gray-500">{{ row.department.name }}</p>
          </div>
          <span v-else class="text-sm text-gray-400">-</span>
        </template>

        <template #cell-hours="{ row }">
          <div>
            <p class="text-sm font-medium text-gray-900">{{ row.total_hours || '0.0' }} saat</p>
            <p v-if="row.overtime_hours > 0" class="text-xs text-orange-600">
              +{{ row.overtime_hours }} fazla mesai
            </p>
          </div>
        </template>

        <template #cell-attendance_type="{ row }">
          <Badge :variant="getAttendanceBadgeVariant(row.attendance_type)">
            {{ getAttendanceTypeLabel(row.attendance_type) }}
          </Badge>
        </template>

        <template #cell-approval_status="{ row }">
          <Badge :variant="getApprovalBadgeVariant(row.approval_status)">
            {{ getApprovalStatusLabel(row.approval_status) }}
          </Badge>
        </template>

        <template #cell-wage="{ row }">
          <p class="text-sm font-medium text-gray-900">
            ₺{{ formatNumber(row.calculated_wage || 0) }}
          </p>
        </template>

        <template #cell-actions="{ row }">
          <div class="flex items-center space-x-2">
            <Button
              variant="ghost"
              size="sm"
              @click="viewTimesheet(row.id)"
              title="Görüntüle"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
              </svg>
            </Button>

            <Button
              v-if="row.is_editable"
              variant="ghost"
              size="sm"
              @click="editTimesheet(row.id)"
              title="Düzenle"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
              </svg>
            </Button>

            <Button
              v-if="row.approval_status === 'draft'"
              variant="ghost"
              size="sm"
              @click="submitForApproval(row.id)"
              title="Onaya Gönder"
            >
              <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </Button>
          </div>
        </template>
      </Table>

      <!-- Pagination -->
      <div v-if="timesheets.data.length > 0" class="mt-4 border-t border-gray-200 pt-4">
        <Pagination :pagination="timesheets" />
      </div>

      <!-- Empty state -->
      <div v-if="!loading && timesheets.data.length === 0" class="text-center py-12">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">Puantaj kaydı bulunamadı</h3>
        <p class="mt-1 text-sm text-gray-500">Yeni bir puantaj kaydı oluşturarak başlayın.</p>
        <div class="mt-6">
          <Button
            variant="primary"
            @click="$inertia.visit(route('timesheets.create'))"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Yeni Puantaj Ekle
          </Button>
        </div>
      </div>
    </Card>
    </div>

    <!-- Export Modal -->
    <Modal
      v-model="showExportModal"
      title="Puantaj Dışa Aktar"
      max-width="md"
    >
      <form @submit.prevent="handleExport">
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Başlangıç Tarihi</label>
            <Input
              v-model="exportForm.start_date"
              type="date"
              required
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Bitiş Tarihi</label>
            <Input
              v-model="exportForm.end_date"
              type="date"
              required
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Format</label>
            <Select
              v-model="exportForm.format"
              :options="formatOptions"
              required
            />
          </div>
        </div>

        <div class="mt-6 flex justify-end space-x-3">
          <Button
            type="button"
            variant="outline"
            @click="showExportModal = false"
          >
            İptal
          </Button>
          <Button
            type="submit"
            variant="primary"
            :disabled="exportLoading"
          >
            <Spinner v-if="exportLoading" class="w-4 h-4 mr-2" />
            Dışa Aktar
          </Button>
        </div>
      </form>
    </Modal>
  </AppLayout>
</template>

<script setup>
import { ref, computed, reactive } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Card from '@/Components/UI/Card.vue'
import Input from '@/Components/UI/Input.vue'
import Select from '@/Components/UI/Select.vue'
import Table from '@/Components/UI/Table.vue'
import Badge from '@/Components/UI/Badge.vue'
import Pagination from '@/Components/UI/Pagination.vue'
import Modal from '@/Components/UI/Modal.vue'
import Spinner from '@/Components/UI/Spinner.vue'
import { format, parseISO } from 'date-fns'
import { tr } from 'date-fns/locale'

// Props
const props = defineProps({
  timesheets: {
    type: Object,
    required: true
  },
  employees: {
    type: Array,
    default: () => []
  },
  projects: {
    type: Array,
    default: () => []
  },
  departments: {
    type: Array,
    default: () => []
  },
  filters: {
    type: Object,
    default: () => ({})
  },
  stats: {
    type: Object,
    default: () => ({})
  }
})

// State
const loading = ref(false)
const selectedTimesheets = ref([])
const showExportModal = ref(false)
const showNewEntryMenu = ref(false)
const exportLoading = ref(false)

const filterForm = reactive({
  month: props.filters.month || format(new Date(), 'yyyy-MM'),
  employee_id: props.filters.employee_id || '',
  project_id: props.filters.project_id || '',
  department_id: props.filters.department_id || '',
  approval_status: props.filters.approval_status || '',
  filter: props.filters.filter || ''
})

const exportForm = reactive({
  start_date: format(new Date(new Date().getFullYear(), new Date().getMonth(), 1), 'yyyy-MM-dd'),
  end_date: format(new Date(), 'yyyy-MM-dd'),
  format: 'xlsx'
})

// Computed
const page = usePage()
const canBulkApprove = computed(() => {
  return page.props.auth?.user?.roles?.some(role => ['admin', 'hr', 'foreman', 'project_manager'].includes(role.name))
})

const employeeOptions = computed(() => [
  { value: '', label: 'Tüm Çalışanlar' },
  ...props.employees.map(emp => ({
    value: emp.id,
    label: `${emp.first_name} ${emp.last_name} (${emp.employee_code || ''})`
  }))
])

const projectOptions = computed(() => [
  { value: '', label: 'Tüm Projeler' },
  ...props.projects.map(proj => ({
    value: proj.id,
    label: proj.name
  }))
])

const approvalStatusOptions = computed(() => [
  { value: '', label: 'Tüm Durumlar' },
  { value: 'draft', label: 'Taslak' },
  { value: 'pending', label: 'Onay Bekliyor' },
  { value: 'approved', label: 'Onaylandı' },
  { value: 'rejected', label: 'Reddedildi' }
])

const formatOptions = [
  { value: 'csv', label: 'CSV' },
  { value: 'xlsx', label: 'Excel (XLSX)' }
]

const quickFilters = [
  { value: 'pending_approval', label: 'Onay Bekleyenler' },
  { value: 'overtime', label: 'Fazla Mesailer' },
  { value: 'late', label: 'Geç Gelenler' },
  { value: 'weekend', label: 'Hafta Sonu' },
  { value: 'revised', label: 'Revize Edilenler' }
]

const hasActiveFilters = computed(() => {
  return filterForm.employee_id || filterForm.project_id || filterForm.approval_status || filterForm.filter
})

const columns = [
  { key: 'employee', label: 'Çalışan', sortable: true },
  { key: 'work_date', label: 'Tarih', sortable: true },
  { key: 'project', label: 'Proje/Departman', sortable: false },
  { key: 'hours', label: 'Çalışma Saati', sortable: true },
  { key: 'attendance_type', label: 'Devam Durumu', sortable: true },
  { key: 'approval_status', label: 'Onay Durumu', sortable: true },
  { key: 'wage', label: 'Ücret', sortable: true },
  { key: 'actions', label: 'İşlemler', sortable: false }
]

// Methods
const applyFilters = () => {
  router.get(route('timesheets.index'), filterForm, {
    preserveState: true,
    preserveScroll: true,
    only: ['timesheets', 'stats']
  })
}

const applyQuickFilter = (filterValue) => {
  if (filterForm.filter === filterValue) {
    filterForm.filter = ''
  } else {
    filterForm.filter = filterValue
  }
  applyFilters()
}

const clearFilters = () => {
  filterForm.employee_id = ''
  filterForm.project_id = ''
  filterForm.department_id = ''
  filterForm.approval_status = ''
  filterForm.filter = ''
  applyFilters()
}

const handleSort = ({ field, direction }) => {
  router.get(route('timesheets.index'), {
    ...filterForm,
    sort: field,
    direction: direction
  }, {
    preserveState: true,
    preserveScroll: true
  })
}

const viewTimesheet = (id) => {
  router.visit(route('timesheets.show', id))
}

const editTimesheet = (id) => {
  router.visit(route('timesheets.edit', id))
}

const submitForApproval = (id) => {
  if (confirm('Bu puantaj kaydını onaya göndermek istediğinize emin misiniz?')) {
    router.post(route('timesheets.submit', id), {}, {
      preserveScroll: true,
      onSuccess: () => {
        // Success message will be shown by flash message
      }
    })
  }
}

const bulkApprove = () => {
  if (selectedTimesheets.value.length === 0) return

  if (confirm(`${selectedTimesheets.value.length} puantaj kaydını onaylamak istediğinize emin misiniz?`)) {
    router.post(route('timesheets.bulk-approve'), {
      timesheet_ids: selectedTimesheets.value.map(t => t.id),
      approval_notes: 'Toplu onay'
    }, {
      preserveScroll: true,
      onSuccess: () => {
        selectedTimesheets.value = []
      }
    })
  }
}

const handleExport = () => {
  exportLoading.value = true

  window.location.href = route('timesheets.export', exportForm)

  setTimeout(() => {
    exportLoading.value = false
    showExportModal.value = false
  }, 2000)
}

// Helper functions
const formatDate = (date) => {
  if (!date) return '-'
  return format(parseISO(date), 'dd.MM.yyyy', { locale: tr })
}

const getDayName = (date) => {
  if (!date) return ''
  return format(parseISO(date), 'EEEE', { locale: tr })
}

const formatNumber = (num) => {
  return new Intl.NumberFormat('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(num)
}

const getAttendanceTypeLabel = (type) => {
  const labels = {
    present: 'Çalıştı',
    absent: 'Devamsız',
    late: 'Geç Geldi',
    early_leave: 'Erken Çıkış',
    sick_leave: 'Hastalık İzni',
    annual_leave: 'Yıllık İzin',
    excuse_leave: 'Mazeret İzni',
    unpaid_leave: 'Ücretsiz İzin'
  }
  return labels[type] || type
}

const getAttendanceBadgeVariant = (type) => {
  const variants = {
    present: 'success',
    absent: 'danger',
    late: 'warning',
    early_leave: 'warning',
    sick_leave: 'info',
    annual_leave: 'info',
    excuse_leave: 'info',
    unpaid_leave: 'secondary'
  }
  return variants[type] || 'secondary'
}

const getApprovalStatusLabel = (status) => {
  const labels = {
    draft: 'Taslak',
    pending: 'Onay Bekliyor',
    approved: 'Onaylandı',
    rejected: 'Reddedildi'
  }
  return labels[status] || status
}

const getApprovalBadgeVariant = (status) => {
  const variants = {
    draft: 'secondary',
    pending: 'warning',
    approved: 'success',
    rejected: 'danger'
  }
  return variants[status] || 'secondary'
}
</script>