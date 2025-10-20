<template>
  <AppLayout
    title="Puantaj Düzenle"
    :breadcrumbs="breadcrumbs"
  >
    <template #header>
      <div class="flex items-center justify-between w-full">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Puantaj Düzenle</h1>
          <p class="mt-1 text-sm text-gray-500">
            {{ timesheet.employee.first_name }} {{ timesheet.employee.last_name }} -
            {{ formatDate(timesheet.work_date) }}
          </p>
        </div>
        <Button
          variant="outline"
          @click="$inertia.visit(route('timesheets.show', timesheet.id))"
        >
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          Geri Dön
        </Button>
      </div>
    </template>

    <!-- Warning if already approved -->
    <Alert v-if="timesheet.approval_status === 'approved'" variant="warning" class="mb-6">
      <div class="flex items-start">
        <svg class="w-5 h-5 text-yellow-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
        </svg>
        <p class="ml-3 text-sm text-yellow-700">
          Bu puantaj kaydı onaylanmış durumda. Değişiklik yapmanız durumunda tekrar onay sürecinden geçecektir.
        </p>
      </div>
    </Alert>

    <form @submit.prevent="handleSubmit">
      <div class="space-y-6">
        <!-- Employee & Project Information -->
        <Card>
          <h3 class="text-lg font-medium text-gray-900 mb-4">Çalışan ve Proje Bilgileri</h3>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Çalışan <span class="text-red-500">*</span>
              </label>
              <Select
                v-model="form.employee_id"
                :options="employeeOptions"
                :error="form.errors.employee_id"
                :disabled="!canChangeEmployee"
                @change="onEmployeeChange"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Proje <span class="text-red-500">*</span>
              </label>
              <Select
                v-model="form.project_id"
                :options="projectOptions"
                :error="form.errors.project_id"
                @change="onProjectChange"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Departman
              </label>
              <Select
                v-model="form.department_id"
                :options="filteredDepartmentOptions"
                placeholder="Departman seçin (opsiyonel)"
                :error="form.errors.department_id"
                :disabled="!form.project_id"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Çalışma Tarihi <span class="text-red-500">*</span>
              </label>
              <Input
                v-model="form.work_date"
                type="date"
                :error="form.errors.work_date"
                :disabled="!canChangeDate"
              />
            </div>
          </div>
        </Card>

        <!-- Attendance Information -->
        <Card>
          <h3 class="text-lg font-medium text-gray-900 mb-4">Devam Durumu</h3>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Devam Türü <span class="text-red-500">*</span>
              </label>
              <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                <button
                  v-for="type in attendanceTypes"
                  :key="type.value"
                  type="button"
                  :class="[
                    'relative rounded-lg border-2 p-4 flex flex-col items-center transition-all',
                    form.attendance_type === type.value
                      ? 'border-blue-500 bg-blue-50'
                      : 'border-gray-200 hover:border-gray-300'
                  ]"
                  @click="selectAttendanceType(type.value)"
                >
                  <component :is="type.icon" class="w-8 h-8 mb-2" :class="getAttendanceIconColor(type.value)" />
                  <span class="text-sm font-medium text-gray-900">{{ type.label }}</span>
                  <div v-if="form.attendance_type === type.value" class="absolute top-2 right-2">
                    <svg class="w-5 h-5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                  </div>
                </button>
              </div>
            </div>

            <div v-if="needsAbsenceReason" class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Devamsızlık Nedeni
              </label>
              <Input
                v-model="form.absence_reason"
                type="textarea"
                rows="3"
                placeholder="Devamsızlık nedenini açıklayın..."
                :error="form.errors.absence_reason"
              />
            </div>

            <div v-if="needsLateReason" class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Geç Gelme Nedeni
              </label>
              <Input
                v-model="form.late_reason"
                type="textarea"
                rows="2"
                placeholder="Geç gelme nedenini açıklayın..."
                :error="form.errors.late_reason"
              />
            </div>
          </div>
        </Card>

        <!-- Working Hours -->
        <Card v-if="showWorkingHours">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Çalışma Saatleri</h3>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Başlangıç Saati <span class="text-red-500">*</span>
              </label>
              <Input
                v-model="form.start_time"
                type="time"
                :error="form.errors.start_time"
                @change="calculateDuration"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Bitiş Saati <span class="text-red-500">*</span>
              </label>
              <Input
                v-model="form.end_time"
                type="time"
                :error="form.errors.end_time"
                @change="calculateDuration"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Mola Başlangıç
              </label>
              <Input
                v-model="form.break_start"
                type="time"
                :error="form.errors.break_start"
                @change="calculateDuration"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Mola Bitiş
              </label>
              <Input
                v-model="form.break_end"
                type="time"
                :error="form.errors.break_end"
                @change="calculateDuration"
              />
            </div>

            <!-- Duration Summary -->
            <div v-if="calculatedDuration" class="md:col-span-2 bg-blue-50 border border-blue-200 rounded-lg p-4">
              <h4 class="text-sm font-medium text-blue-900 mb-3">Çalışma Süresi Özeti</h4>
              <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                  <p class="text-xs text-blue-600">Toplam Süre</p>
                  <p class="text-lg font-bold text-blue-900">{{ calculatedDuration.totalHours }} saat</p>
                </div>
                <div>
                  <p class="text-xs text-blue-600">Normal Mesai</p>
                  <p class="text-lg font-bold text-blue-900">{{ calculatedDuration.normalHours }} saat</p>
                </div>
                <div>
                  <p class="text-xs text-blue-600">Fazla Mesai</p>
                  <p class="text-lg font-bold text-orange-600">{{ calculatedDuration.overtimeHours }} saat</p>
                </div>
                <div>
                  <p class="text-xs text-blue-600">Tahmini Ücret</p>
                  <p class="text-lg font-bold text-green-600">₺{{ formatNumber(calculatedDuration.estimatedWage) }}</p>
                </div>
              </div>
            </div>
          </div>
        </Card>

        <!-- Additional Information -->
        <Card>
          <h3 class="text-lg font-medium text-gray-900 mb-4">Ek Bilgiler</h3>

          <div class="grid grid-cols-1 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Revizyon Nedeni <span class="text-red-500">*</span>
              </label>
              <Input
                v-model="form.revision_reason"
                type="textarea"
                rows="3"
                placeholder="Bu puantaj kaydını neden düzenliyorsunuz?"
                :error="form.errors.revision_reason"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Notlar
              </label>
              <Input
                v-model="form.notes"
                type="textarea"
                rows="3"
                placeholder="Varsa ek notlar ekleyin..."
                :error="form.errors.notes"
              />
            </div>
          </div>
        </Card>

        <!-- Revision History -->
        <Card v-if="timesheet.revisions && timesheet.revisions.length > 0">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Revizyon Geçmişi</h3>
          <div class="space-y-3">
            <div
              v-for="revision in timesheet.revisions"
              :key="revision.id"
              class="flex items-start p-3 bg-gray-50 rounded-lg"
            >
              <div class="flex-shrink-0">
                <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                </svg>
              </div>
              <div class="ml-3 flex-1">
                <p class="text-sm font-medium text-gray-900">{{ revision.revised_by?.name || 'Sistem' }}</p>
                <p class="text-sm text-gray-600 mt-1">{{ revision.revision_reason }}</p>
                <p class="text-xs text-gray-500 mt-1">{{ formatDateTime(revision.created_at) }}</p>
              </div>
            </div>
          </div>
        </Card>

        <!-- Actions -->
        <Card>
          <div class="flex items-center justify-between">
            <div class="flex space-x-3">
              <Button
                type="button"
                variant="outline"
                @click="$inertia.visit(route('timesheets.show', timesheet.id))"
              >
                İptal
              </Button>

              <Button
                v-if="timesheet.approval_status !== 'draft'"
                type="button"
                variant="danger"
                @click="confirmDelete"
                :disabled="form.processing"
              >
                Sil
              </Button>
            </div>

            <Button
              type="submit"
              variant="primary"
              :disabled="form.processing"
            >
              <Spinner v-if="form.processing" class="w-4 h-4 mr-2" />
              Değişiklikleri Kaydet
            </Button>
          </div>
        </Card>
      </div>
    </form>
  </AppLayout>
</template>

<script setup>
import { ref, computed, reactive, watch } from 'vue'
import { useForm, router, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Card from '@/Components/UI/Card.vue'
import Button from '@/Components/UI/Button.vue'
import Input from '@/Components/UI/Input.vue'
import Select from '@/Components/UI/Select.vue'
import Alert from '@/Components/UI/Alert.vue'
import Spinner from '@/Components/UI/Spinner.vue'
import { format, parseISO } from 'date-fns'
import { tr } from 'date-fns/locale'

// Props
const props = defineProps({
  timesheet: {
    type: Object,
    required: true
  },
  employees: {
    type: Array,
    required: true
  },
  projects: {
    type: Array,
    required: true
  },
  departments: {
    type: Array,
    required: true
  }
})

// State
const calculatedDuration = ref(null)
const selectedEmployee = ref(null)

// Form
const form = useForm({
  employee_id: props.timesheet.employee_id,
  project_id: props.timesheet.project_id,
  department_id: props.timesheet.department_id || '',
  work_date: props.timesheet.work_date,
  start_time: props.timesheet.start_time?.substring(0, 5) || '',
  end_time: props.timesheet.end_time?.substring(0, 5) || '',
  break_start: props.timesheet.break_start?.substring(0, 5) || '',
  break_end: props.timesheet.break_end?.substring(0, 5) || '',
  attendance_type: props.timesheet.attendance_type,
  notes: props.timesheet.notes || '',
  absence_reason: props.timesheet.absence_reason || '',
  late_reason: props.timesheet.late_reason || '',
  revision_reason: ''
})

// Computed
const page = usePage()
const canChangeEmployee = computed(() => {
  return page.props.auth?.user?.roles?.some(role => ['admin', 'hr'].includes(role.name))
})

const canChangeDate = computed(() => {
  return page.props.auth?.user?.roles?.some(role => ['admin', 'hr'].includes(role.name))
})

const breadcrumbs = computed(() => [
  { label: 'Ana Sayfa', url: route('dashboard') },
  { label: 'Puantaj', url: route('timesheets.index') },
  { label: 'Düzenle', url: null }
])

const employeeOptions = computed(() =>
  props.employees.map(emp => ({
    value: emp.id,
    label: `${emp.first_name} ${emp.last_name} ${emp.employee_code ? `(${emp.employee_code})` : ''}`
  }))
)

const projectOptions = computed(() =>
  props.projects.map(proj => ({
    value: proj.id,
    label: proj.name
  }))
)

const filteredDepartmentOptions = computed(() => {
  if (!form.project_id) return []

  return props.departments
    .filter(dept => dept.project_id === form.project_id)
    .map(dept => ({
      value: dept.id,
      label: dept.name
    }))
})

const attendanceTypes = [
  { value: 'present', label: 'Çalıştı', icon: 'CheckCircleIcon' },
  { value: 'absent', label: 'Devamsız', icon: 'XCircleIcon' },
  { value: 'late', label: 'Geç Geldi', icon: 'ClockIcon' },
  { value: 'sick_leave', label: 'Hastalık', icon: 'HeartIcon' },
  { value: 'annual_leave', label: 'Yıllık İzin', icon: 'CalendarIcon' },
  { value: 'excuse_leave', label: 'Mazeret', icon: 'DocumentIcon' },
  { value: 'unpaid_leave', label: 'Ücretsiz İzin', icon: 'BanIcon' },
  { value: 'early_leave', label: 'Erken Çıkış', icon: 'LogoutIcon' }
]

const needsAbsenceReason = computed(() => form.attendance_type === 'absent')
const needsLateReason = computed(() => form.attendance_type === 'late')
const showWorkingHours = computed(() =>
  ['present', 'late', 'early_leave'].includes(form.attendance_type)
)

// Methods
const onEmployeeChange = () => {
  const employee = props.employees.find(e => e.id === form.employee_id)
  selectedEmployee.value = employee
  calculateDuration()
}

const onProjectChange = () => {
  form.department_id = ''
}

const selectAttendanceType = (type) => {
  form.attendance_type = type
  if (!needsAbsenceReason.value) form.absence_reason = ''
  if (!needsLateReason.value) form.late_reason = ''
  if (showWorkingHours.value) calculateDuration()
}

const calculateDuration = () => {
  if (!form.start_time || !form.end_time || !selectedEmployee.value) {
    calculatedDuration.value = null
    return
  }

  const start = parseTime(form.start_time)
  const end = parseTime(form.end_time)
  const breakStart = form.break_start ? parseTime(form.break_start) : null
  const breakEnd = form.break_end ? parseTime(form.break_end) : null

  if (end <= start) {
    calculatedDuration.value = null
    return
  }

  let totalMinutes = end - start

  if (breakStart && breakEnd && breakEnd > breakStart) {
    totalMinutes -= (breakEnd - breakStart)
  }

  const totalHours = Math.round(totalMinutes / 60 * 10) / 10
  const normalHours = Math.min(totalHours, 8)
  const overtimeHours = Math.max(0, totalHours - 8)

  const hourlyWage = selectedEmployee.value.hourly_wage || 0
  const estimatedWage = (normalHours * hourlyWage) + (overtimeHours * hourlyWage * 1.5)

  calculatedDuration.value = {
    totalHours: totalHours.toFixed(1),
    normalHours: normalHours.toFixed(1),
    overtimeHours: overtimeHours.toFixed(1),
    estimatedWage: estimatedWage.toFixed(2)
  }
}

const parseTime = (timeStr) => {
  const [hours, minutes] = timeStr.split(':').map(Number)
  return hours * 60 + minutes
}

const handleSubmit = () => {
  if (!form.revision_reason) {
    alert('Lütfen revizyon nedenini belirtin.')
    return
  }

  form.put(route('timesheets.update', props.timesheet.id), {
    preserveScroll: true
  })
}

const confirmDelete = () => {
  if (confirm('Bu puantaj kaydını silmek istediğinize emin misiniz? Bu işlem geri alınamaz.')) {
    router.delete(route('timesheets.destroy', props.timesheet.id))
  }
}

const formatDate = (date) => {
  if (!date) return '-'
  return format(parseISO(date), 'dd.MM.yyyy', { locale: tr })
}

const formatDateTime = (datetime) => {
  if (!datetime) return '-'
  return format(parseISO(datetime), 'dd.MM.yyyy HH:mm', { locale: tr })
}

const formatNumber = (num) => {
  return new Intl.NumberFormat('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(num)
}

const getAttendanceIconColor = (type) => {
  const colors = {
    present: 'text-green-600',
    absent: 'text-red-600',
    late: 'text-orange-600',
    early_leave: 'text-orange-600',
    sick_leave: 'text-blue-600',
    annual_leave: 'text-purple-600',
    excuse_leave: 'text-indigo-600',
    unpaid_leave: 'text-gray-600'
  }
  return colors[type] || 'text-gray-600'
}

// Initialize
onEmployeeChange()
if (showWorkingHours.value) calculateDuration()

// Icons
const CheckCircleIcon = { template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>' }
const XCircleIcon = { template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>' }
const ClockIcon = { template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>' }
const HeartIcon = { template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" /></svg>' }
const CalendarIcon = { template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>' }
const DocumentIcon = { template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>' }
const BanIcon = { template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg>' }
const LogoutIcon = { template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>' }
</script>
