<template>
  <AppLayout
    title="Personel Proje Atamaları"
    :full-width="true"
  >
    <template #header>
      <div class="flex items-center justify-between w-full">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Personel Proje Atamaları</h1>
          <p class="mt-1 text-sm text-gray-500">
            Personellerin proje atamalarını görüntüleyin ve yönetin
          </p>
        </div>
        <div class="flex space-x-3">
          <Button
            variant="primary"
            size="sm"
            @click="router.visit(route('employee-assignments.create'))"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Yeni Atama
          </Button>
        </div>
      </div>
    </template>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
      <Card>
        <div class="flex items-center">
          <div class="flex-shrink-0 bg-blue-100 rounded-lg p-3">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-500">Toplam Atama</p>
            <p class="text-2xl font-bold text-gray-900">{{ stats.total_assignments || 0 }}</p>
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
            <p class="text-sm font-medium text-gray-500">Aktif Atama</p>
            <p class="text-2xl font-bold text-gray-900">{{ stats.active_assignments || 0 }}</p>
          </div>
        </div>
      </Card>

      <Card>
        <div class="flex items-center">
          <div class="flex-shrink-0 bg-purple-100 rounded-lg p-3">
            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-500">Aktif Proje</p>
            <p class="text-2xl font-bold text-gray-900">{{ stats.active_projects || 0 }}</p>
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
            <p class="text-sm font-medium text-gray-500">Süresi Dolacak</p>
            <p class="text-2xl font-bold text-gray-900">{{ stats.expiring_soon || 0 }}</p>
          </div>
        </div>
      </Card>
    </div>

    <!-- Filters -->
    <Card class="mb-6">
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Personel</label>
          <Select
            v-model="filterForm.employee_id"
            :options="employeeOptions"
            placeholder="Tüm personeller"
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
          <label class="block text-sm font-medium text-gray-700 mb-2">Durum</label>
          <Select
            v-model="filterForm.status"
            :options="statusOptions"
            placeholder="Tüm durumlar"
            @change="applyFilters"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Ana Proje</label>
          <Select
            v-model="filterForm.is_primary"
            :options="primaryOptions"
            placeholder="Tümü"
            @change="applyFilters"
          />
        </div>
      </div>

      <div class="mt-4 flex justify-between items-center">
        <div class="flex flex-wrap gap-2">
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
        </div>

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

    <!-- Assignments Table -->
    <Card>
      <Table
        :columns="columns"
        :data="assignments.data"
        :loading="loading"
        :sortable="true"
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

        <template #cell-project="{ row }">
          <div>
            <p class="text-sm font-medium text-gray-900">{{ row.project.name }}</p>
            <p v-if="row.is_primary" class="text-xs text-blue-600 font-medium mt-1">
              <svg class="w-3 h-3 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
              </svg>
              Ana Proje
            </p>
          </div>
        </template>

        <template #cell-period="{ row }">
          <div>
            <p class="text-sm text-gray-900">{{ formatDate(row.start_date) }}</p>
            <p class="text-xs text-gray-500">
              {{ row.end_date ? 'Bitiş: ' + formatDate(row.end_date) : 'Devam ediyor' }}
            </p>
          </div>
        </template>

        <template #cell-role="{ row }">
          <p class="text-sm text-gray-900">{{ row.role_in_project || '-' }}</p>
        </template>

        <template #cell-status="{ row }">
          <Badge :variant="getStatusBadgeVariant(row.status)">
            {{ getStatusLabel(row.status) }}
          </Badge>
        </template>

        <template #cell-duration="{ row }">
          <p class="text-sm text-gray-900">{{ calculateDuration(row.start_date, row.end_date) }}</p>
        </template>

        <template #cell-actions="{ row }">
          <div class="flex items-center space-x-2">
            <Button
              variant="ghost"
              size="sm"
              @click="viewAssignment(row.id)"
              title="Görüntüle"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
              </svg>
            </Button>

            <Button
              v-if="row.status === 'active'"
              variant="ghost"
              size="sm"
              @click="editAssignment(row.id)"
              title="Düzenle"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
              </svg>
            </Button>

            <Button
              v-if="row.status === 'active'"
              variant="ghost"
              size="sm"
              @click="completeAssignment(row.id)"
              title="Tamamla"
            >
              <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </Button>
          </div>
        </template>
      </Table>

      <!-- Pagination -->
      <div v-if="assignments.data.length > 0" class="mt-4 border-t border-gray-200 pt-4">
        <Pagination
          :from="assignments.from"
          :to="assignments.to"
          :total="assignments.total"
          :prev-page-url="assignments.prev_page_url"
          :next-page-url="assignments.next_page_url"
          :links="assignments.links"
        />
      </div>

      <!-- Empty state -->
      <div v-if="!loading && assignments.data.length === 0" class="text-center py-12">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">Atama kaydı bulunamadı</h3>
        <p class="mt-1 text-sm text-gray-500">Yeni bir proje ataması oluşturarak başlayın.</p>
        <div class="mt-6">
          <Button
            variant="primary"
            @click="router.visit(route('employee-assignments.create'))"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Yeni Atama Ekle
          </Button>
        </div>
      </div>
    </Card>
  </AppLayout>
</template>

<script setup>
import { ref, computed, reactive } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Card from '@/Components/UI/Card.vue'
import Button from '@/Components/UI/Button.vue'
import Select from '@/Components/UI/Select.vue'
import Table from '@/Components/UI/Table.vue'
import Badge from '@/Components/UI/Badge.vue'
import Pagination from '@/Components/UI/Pagination.vue'
import { format, parseISO, differenceInDays } from 'date-fns'
import { tr } from 'date-fns/locale'

// Props
const props = defineProps({
  assignments: {
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

const filterForm = reactive({
  employee_id: props.filters.employee_id || '',
  project_id: props.filters.project_id || '',
  status: props.filters.status || '',
  is_primary: props.filters.is_primary || '',
  filter: props.filters.filter || ''
})

// Computed
const breadcrumbs = computed(() => [
  { label: 'Ana Sayfa', url: route('dashboard') },
  { label: 'Personel Atamaları', url: null }
])

const employeeOptions = computed(() => [
  { value: '', label: 'Tüm Personeller' },
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

const statusOptions = [
  { value: '', label: 'Tüm Durumlar' },
  { value: 'active', label: 'Aktif' },
  { value: 'completed', label: 'Tamamlandı' },
  { value: 'cancelled', label: 'İptal Edildi' }
]

const primaryOptions = [
  { value: '', label: 'Tümü' },
  { value: '1', label: 'Ana Proje' },
  { value: '0', label: 'Yardımcı Proje' }
]

const quickFilters = [
  { value: 'active', label: 'Aktif Atamalar' },
  { value: 'primary', label: 'Ana Projeler' },
  { value: 'expiring_soon', label: 'Süresi Dolacaklar' },
  { value: 'without_end_date', label: 'Süresiz' }
]

const hasActiveFilters = computed(() => {
  return filterForm.employee_id || filterForm.project_id || filterForm.status || filterForm.is_primary || filterForm.filter
})

const columns = [
  { key: 'employee', label: 'Personel', sortable: true },
  { key: 'project', label: 'Proje', sortable: true },
  { key: 'period', label: 'Dönem', sortable: true },
  { key: 'role', label: 'Rol', sortable: false },
  { key: 'status', label: 'Durum', sortable: true },
  { key: 'duration', label: 'Süre', sortable: false },
  { key: 'actions', label: 'İşlemler', sortable: false }
]

// Methods
const applyFilters = () => {
  router.get(route('employee-assignments.index'), filterForm, {
    preserveState: true,
    preserveScroll: true,
    only: ['assignments', 'stats']
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
  filterForm.status = ''
  filterForm.is_primary = ''
  filterForm.filter = ''
  applyFilters()
}

const handleSort = ({ field, direction }) => {
  router.get(route('employee-assignments.index'), {
    ...filterForm,
    sort: field,
    direction: direction
  }, {
    preserveState: true,
    preserveScroll: true
  })
}

const viewAssignment = (id) => {
  router.visit(route('employee-assignments.show', id))
}

const editAssignment = (id) => {
  router.visit(route('employee-assignments.edit', id))
}

const completeAssignment = (id) => {
  if (confirm('Bu atamayı tamamlamak istediğinize emin misiniz?')) {
    router.post(route('employee-assignments.complete', id), {}, {
      preserveScroll: true
    })
  }
}

// Helper functions
const formatDate = (date) => {
  if (!date) return '-'
  return format(parseISO(date), 'dd.MM.yyyy', { locale: tr })
}

const calculateDuration = (startDate, endDate) => {
  if (!startDate) return '-'

  const start = parseISO(startDate)
  const end = endDate ? parseISO(endDate) : new Date()

  const days = differenceInDays(end, start)

  if (days < 30) {
    return `${days} gün`
  } else if (days < 365) {
    const months = Math.floor(days / 30)
    return `${months} ay`
  } else {
    const years = Math.floor(days / 365)
    const months = Math.floor((days % 365) / 30)
    return `${years} yıl ${months} ay`
  }
}

const getStatusLabel = (status) => {
  const labels = {
    active: 'Aktif',
    completed: 'Tamamlandı',
    cancelled: 'İptal Edildi'
  }
  return labels[status] || status
}

const getStatusBadgeVariant = (status) => {
  const variants = {
    active: 'success',
    completed: 'secondary',
    cancelled: 'danger'
  }
  return variants[status] || 'secondary'
}
</script>
