<template>
  <AppLayout
    title="Toplu Puantaj Girişi"
    :breadcrumbs="breadcrumbs"
    :fullWidth="true"
  >
    <template #fullWidthHeader>
      <div class="bg-white border-b border-gray-200 px-6 py-4">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-2xl font-bold text-gray-900">Toplu Puantaj Girişi</h1>
            <p class="mt-1 text-sm text-gray-500">
              {{ selectedMonth ? format(parseISO(selectedMonth + '-01'), 'MMMM yyyy', { locale: tr }) : '' }} -
              {{ selectedProject?.name || 'Proje seçilmedi' }}
            </p>
          </div>
          <div class="flex items-center space-x-3">
            <Button
              variant="outline"
              size="sm"
              @click="$inertia.visit(route('timesheets.index'))"
            >
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
              </svg>
              Listeye Dön
            </Button>
            <Button
              variant="success"
              size="sm"
              @click="saveAll"
              :disabled="!hasChanges || saving"
            >
              <Spinner v-if="saving" class="w-4 h-4 mr-2" />
              <svg v-else class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
              Tümünü Kaydet
            </Button>
          </div>
        </div>

        <!-- Filters -->
        <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Ay Seçin <span class="text-red-500">*</span></label>
            <Input
              v-model="selectedMonth"
              type="month"
              @change="loadData"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Proje <span class="text-red-500">*</span></label>
            <Select
              v-model="selectedProjectId"
              :options="projectOptions"
              placeholder="Proje seçin"
              @change="loadData"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Departman</label>
            <Select
              v-model="selectedDepartmentId"
              :options="departmentOptions"
              placeholder="Tüm departmanlar"
              @change="loadData"
              :disabled="!selectedProjectId"
            />
          </div>
        </div>

        <!-- Bulk Actions -->
        <div v-if="selectedProjectId && employees.length > 0" class="mt-4 flex items-center gap-3 p-3 bg-blue-50 rounded-lg border border-blue-200">
          <span class="text-sm font-medium text-blue-900">Toplu İşlemler:</span>

          <select v-model="bulkAction.attendanceType" class="text-sm border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
            <option value="">Vardiya Seç</option>
            <option value="present">Çalıştı (C)</option>
            <option value="absent">Devamsız (D)</option>
            <option value="leave">İzin (İ)</option>
            <option value="sick">Hastalık (H)</option>
          </select>

          <Button
            variant="outline"
            size="sm"
            @click="applyToWeekends"
            :disabled="!bulkAction.attendanceType"
          >
            Hafta Sonlarına Uygula
          </Button>

          <Button
            variant="outline"
            size="sm"
            @click="applyToWeekdays"
            :disabled="!bulkAction.attendanceType"
          >
            Hafta İçine Uygula
          </Button>

          <Button
            variant="outline"
            size="sm"
            @click="applyToAllDays"
            :disabled="!bulkAction.attendanceType"
          >
            Tüm Günlere Uygula
          </Button>

          <Button
            variant="danger"
            size="sm"
            @click="clearAllAttendance"
          >
            Tümünü Temizle
          </Button>
        </div>
      </div>
    </template>

    <!-- Legend -->
    <div class="px-6 py-3 bg-gray-50 border-b border-gray-200">
      <div class="flex items-center space-x-6 text-xs">
        <div class="flex items-center">
          <div class="w-8 h-8 rounded border-2 border-green-500 bg-green-50 mr-2"></div>
          <span class="text-gray-700">Çalıştı (C)</span>
        </div>
        <div class="flex items-center">
          <div class="w-8 h-8 rounded border-2 border-red-500 bg-red-50 mr-2"></div>
          <span class="text-gray-700">Devamsız (D)</span>
        </div>
        <div class="flex items-center">
          <div class="w-8 h-8 rounded border-2 border-yellow-500 bg-yellow-50 mr-2"></div>
          <span class="text-gray-700">İzin (İ)</span>
        </div>
        <div class="flex items-center">
          <div class="w-8 h-8 rounded border-2 border-blue-500 bg-blue-50 mr-2"></div>
          <span class="text-gray-700">Hastalık (H)</span>
        </div>
        <div class="flex items-center">
          <div class="w-8 h-8 rounded border-2 border-gray-300 bg-gray-100 mr-2"></div>
          <span class="text-gray-700">Hafta Sonu</span>
        </div>
        <div class="ml-auto text-gray-600">
          <span class="font-medium">Kısayollar:</span> C, D, İ, H, Space (temizle) | <span class="font-medium">Sağ Tık:</span> Fazla Mesai
        </div>
      </div>
    </div>

    <!-- Calendar Table -->
    <div v-if="selectedProjectId && selectedMonth" class="flex-1 overflow-hidden">
      <div class="h-full overflow-auto">
        <table class="min-w-full divide-y divide-gray-200 border-collapse">
          <thead class="bg-gray-50 sticky top-0 z-10">
            <tr>
              <!-- Employee Column Header -->
              <th class="sticky left-0 z-20 bg-gray-100 px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider border-r-2 border-gray-300 w-64">
                Personel
              </th>

              <!-- Day Headers -->
              <th
                v-for="day in daysInMonth"
                :key="day.date"
                :class="[
                  'px-2 py-3 text-center text-xs font-medium uppercase tracking-wider border-r border-gray-200',
                  day.isWeekend ? 'bg-gray-200 text-gray-500' : 'bg-gray-50 text-gray-700'
                ]"
                style="min-width: 50px; max-width: 50px; width: 50px;"
              >
                <div>{{ day.dayNumber }}</div>
                <div class="text-[10px] font-normal">{{ day.dayName }}</div>
              </th>

              <!-- Total Columns Headers -->
              <th class="sticky right-0 z-20 bg-gray-100 px-2 py-3 text-center text-xs font-medium text-gray-700 uppercase tracking-wider border-l-2 border-gray-300 min-w-[60px]">
                Toplam<br>Gün
              </th>
              <th class="sticky z-20 bg-gray-100 px-2 py-3 text-center text-xs font-medium text-gray-700 uppercase tracking-wider border-l border-gray-300 min-w-[50px]" style="right: 210px;">
                Çalışılan
              </th>
              <th class="sticky z-20 bg-gray-100 px-2 py-3 text-center text-xs font-medium text-gray-700 uppercase tracking-wider border-l border-gray-300 min-w-[50px]" style="right: 160px;">
                İzin
              </th>
              <th class="sticky z-20 bg-gray-100 px-2 py-3 text-center text-xs font-medium text-gray-700 uppercase tracking-wider border-l border-gray-300 min-w-[50px]" style="right: 110px;">
                Hastalık
              </th>
              <th class="sticky z-20 bg-gray-100 px-2 py-3 text-center text-xs font-medium text-gray-700 uppercase tracking-wider border-l border-gray-300 min-w-[50px]" style="right: 60px;">
                FM Hİ
              </th>
              <th class="sticky z-20 bg-gray-100 px-2 py-3 text-center text-xs font-medium text-gray-700 uppercase tracking-wider border-l border-gray-300 min-w-[50px]" style="right: 10px;">
                FM HS
              </th>
              <th class="sticky z-20 bg-gray-100 px-2 py-3 text-center text-xs font-medium text-gray-700 uppercase tracking-wider border-l border-gray-300 min-w-[50px]" style="right: -40px;">
                FM RT
              </th>
            </tr>
          </thead>

          <tbody class="bg-white divide-y divide-gray-200">
            <tr
              v-for="employee in employees"
              :key="employee.id"
              class="hover:bg-gray-50"
            >
              <!-- Employee Info -->
              <td class="sticky left-0 z-10 bg-white px-4 py-3 border-r-2 border-gray-300 w-64">
                <div class="flex items-center">
                  <div class="flex-shrink-0 h-8 w-8">
                    <div class="h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center">
                      <span class="text-xs font-medium text-gray-600">
                        {{ employee.first_name.charAt(0) }}{{ employee.last_name.charAt(0) }}
                      </span>
                    </div>
                  </div>
                  <div class="ml-3">
                    <p class="text-sm font-medium text-gray-900">
                      {{ employee.first_name }} {{ employee.last_name }}
                    </p>
                    <p class="text-xs text-gray-500">{{ employee.employee_code }}</p>
                  </div>
                </div>
              </td>

              <!-- Day Cells -->
              <td
                v-for="day in daysInMonth"
                :key="`${employee.id}-${day.date}`"
                :class="[
                  'px-1 py-1 text-center border-r border-gray-200 transition-colors relative',
                  day.isWeekend ? 'bg-gray-100' : 'bg-white',
                  isDayEditable(employee, day.date)
                    ? 'cursor-pointer hover:bg-blue-50'
                    : 'cursor-not-allowed bg-gray-200 opacity-50'
                ]"
                style="min-width: 50px; max-width: 50px; width: 50px;"
                @click="isDayEditable(employee, day.date) && toggleAttendance(employee.id, day.date)"
                @contextmenu.prevent="isDayEditable(employee, day.date) && showOvertimeMenu(employee.id, day.date, $event)"
                @keydown="handleKeyDown($event, employee.id, day.date)"
                :tabindex="isDayEditable(employee, day.date) ? 0 : -1"
                :title="!isDayEditable(employee, day.date) ? getDisabledDayTooltip(employee, day.date) : ''"
              >
                <div
                  :class="[
                    'w-full h-10 rounded flex flex-col items-center justify-center text-sm font-bold transition-all',
                    getAttendanceCellClass(employee.id, day.date)
                  ]"
                >
                  <span>{{ getAttendanceLabel(employee.id, day.date) }}</span>
                  <span v-if="getOvertimeHours(employee.id, day.date)" class="text-[10px] font-normal mt-0.5">
                    +{{ getOvertimeHours(employee.id, day.date) }}s
                  </span>
                </div>
              </td>

              <!-- Total Cells -->
              <td class="sticky right-0 z-10 bg-gray-50 px-2 py-3 text-center border-l-2 border-gray-300 font-semibold text-gray-900">
                {{ getEmployeeTotalDays(employee.id) }}
              </td>
              <td class="sticky z-10 bg-green-50 px-2 py-3 text-center border-l border-gray-300 font-semibold text-green-700" style="right: 210px;">
                {{ getEmployeeWorkDays(employee.id) }}
              </td>
              <td class="sticky z-10 bg-yellow-50 px-2 py-3 text-center border-l border-gray-300 font-semibold text-yellow-700" style="right: 160px;">
                {{ getEmployeeLeaveDays(employee.id) }}
              </td>
              <td class="sticky z-10 bg-blue-50 px-2 py-3 text-center border-l border-gray-300 font-semibold text-blue-700" style="right: 110px;">
                {{ getEmployeeSickDays(employee.id) }}
              </td>
              <td class="sticky z-10 bg-purple-50 px-2 py-3 text-center border-l border-gray-300 font-semibold text-purple-700" style="right: 60px;">
                {{ getEmployeeOvertimeHours(employee.id, 'weekday') }}
              </td>
              <td class="sticky z-10 bg-purple-50 px-2 py-3 text-center border-l border-gray-300 font-semibold text-purple-700" style="right: 10px;">
                {{ getEmployeeOvertimeHours(employee.id, 'weekend') }}
              </td>
              <td class="sticky z-10 bg-purple-50 px-2 py-3 text-center border-l border-gray-300 font-semibold text-purple-700" style="right: -40px;">
                {{ getEmployeeOvertimeHours(employee.id, 'holiday') }}
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Empty State -->
        <div v-if="employees.length === 0" class="text-center py-12">
          <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
          </svg>
          <h3 class="mt-2 text-sm font-medium text-gray-900">Personel bulunamadı</h3>
          <p class="mt-1 text-sm text-gray-500">
            Seçilen projeye atanmış aktif personel bulunmuyor.
          </p>
        </div>
      </div>
    </div>

    <!-- Overtime Context Menu -->
    <div
      v-if="contextMenu.show"
      :style="{
        position: 'fixed',
        top: contextMenu.y + 'px',
        left: contextMenu.x + 'px',
        zIndex: 9999
      }"
      class="bg-white rounded-lg shadow-xl border border-gray-200 py-2 min-w-[280px]"
      @click.stop
    >
      <div class="px-4 py-2 border-b border-gray-200">
        <h3 class="text-sm font-semibold text-gray-700">Fazla Mesai Girişi</h3>
        <p v-if="contextMenu.date" class="text-xs text-gray-500 mt-0.5">
          {{ format(parseISO(contextMenu.date), 'd MMMM yyyy', { locale: tr }) }}
        </p>
      </div>

      <div class="px-4 py-3 space-y-3">
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">Mesai Tipi</label>
          <select
            v-model="contextMenu.overtimeType"
            class="w-full text-sm border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
          >
            <option value="weekday">Hafta İçi (%50)</option>
            <option value="weekend">Hafta Sonu (%100)</option>
            <option value="holiday">Resmi Tatil (%200)</option>
          </select>
        </div>

        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">Saat</label>
          <input
            v-model.number="contextMenu.hours"
            type="number"
            min="0"
            max="24"
            step="0.5"
            class="w-full text-sm border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
            placeholder="Örn: 2, 2.5, 3"
          />
        </div>
      </div>

      <div class="px-4 py-2 border-t border-gray-200 flex justify-end space-x-2">
        <button
          @click="closeOvertimeMenu"
          class="px-3 py-1.5 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
        >
          İptal
        </button>
        <button
          v-if="getOvertimeHours(contextMenu.employeeId, contextMenu.date)"
          @click="removeOvertime"
          class="px-3 py-1.5 text-xs font-medium text-white bg-red-600 rounded-md hover:bg-red-700"
        >
          Fazla Mesai Sil
        </button>
        <button
          @click="saveOvertime"
          class="px-3 py-1.5 text-xs font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700"
          :disabled="!contextMenu.hours || contextMenu.hours <= 0"
        >
          Kaydet
        </button>
      </div>
    </div>

    <!-- Click Outside to Close Context Menu -->
    <div
      v-if="contextMenu.show"
      class="fixed inset-0"
      style="z-index: 9998"
      @click="closeOvertimeMenu"
    ></div>

    <!-- No Selection State -->
    <div v-else class="flex-1 flex items-center justify-center">
      <div class="text-center">
        <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
        <h3 class="mt-4 text-lg font-medium text-gray-900">Toplu Puantaj Girişi</h3>
        <p class="mt-2 text-sm text-gray-500">
          Başlamak için ay ve proje seçin
        </p>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Button from '@/Components/UI/Button.vue'
import Input from '@/Components/UI/Input.vue'
import Select from '@/Components/UI/Select.vue'
import Spinner from '@/Components/UI/Spinner.vue'
import { format, parseISO, getDaysInMonth, startOfMonth, endOfMonth, eachDayOfInterval, isWeekend, getDay } from 'date-fns'
import { tr } from 'date-fns/locale'

// Props
const props = defineProps({
  projects: {
    type: Array,
    default: () => []
  },
  departments: {
    type: Array,
    default: () => []
  },
  employees: {
    type: Array,
    default: () => []
  },
  existingTimesheets: {
    type: Array,
    default: () => []
  },
  approvedTimesheets: {
    type: Array,
    default: () => []
  },
  month: String,
  projectId: Number,
  departmentId: Number
})

// State
const selectedMonth = ref(props.month || format(new Date(), 'yyyy-MM'))
const selectedProjectId = ref(props.projectId || null)
const selectedDepartmentId = ref(props.departmentId || null)
const attendanceData = ref({}) // { employeeId: { date: 'present|absent|leave|sick' } }
const overtimeData = ref({}) // { employeeId: { date: { type: 'weekday|weekend|holiday', hours: number } } }
const contextMenu = ref({
  show: false,
  x: 0,
  y: 0,
  employeeId: null,
  date: null,
  overtimeType: 'weekday',
  hours: null
})
const saving = ref(false)
const hasChanges = ref(false)
const bulkAction = ref({
  attendanceType: ''
})

// Initialize attendance data from existing timesheets
const initializeAttendanceData = () => {
  const data = {}
  const overtime = {}

  console.log('🔍 Initializing with existingTimesheets:', props.existingTimesheets)

  props.existingTimesheets.forEach(ts => {
    // Normalize date format: convert "2025-10-10T00:00:00.000000Z" to "2025-10-10"
    const normalizedDate = ts.work_date.split('T')[0]

    console.log(`📝 Processing timesheet: employee_id=${ts.employee_id}, date=${ts.work_date} -> ${normalizedDate}, type=${ts.attendance_type}`)

    if (!data[ts.employee_id]) {
      data[ts.employee_id] = {}
    }
    data[ts.employee_id][normalizedDate] = ts.attendance_type

    // Initialize overtime data if exists
    if (ts.overtime_hours && ts.overtime_hours > 0) {
      if (!overtime[ts.employee_id]) {
        overtime[ts.employee_id] = {}
      }
      overtime[ts.employee_id][normalizedDate] = {
        type: ts.overtime_type || 'weekday',
        hours: ts.overtime_hours
      }
    }
  })

  console.log('✅ Initialized attendanceData:', data)
  console.log('✅ Initialized overtimeData:', overtime)

  attendanceData.value = data
  overtimeData.value = overtime
}

initializeAttendanceData()

// Computed
const breadcrumbs = computed(() => [
  { label: 'Ana Sayfa', url: route('dashboard') },
  { label: 'Puantaj', url: route('timesheets.index') },
  { label: 'Toplu Giriş', url: null }
])

const projectOptions = computed(() =>
  props.projects.map(proj => ({
    value: proj.id,
    label: proj.name
  }))
)

const selectedProject = computed(() =>
  props.projects.find(p => p.id === selectedProjectId.value)
)

const departmentOptions = computed(() => {
  if (!selectedProjectId.value) return []

  return props.departments
    .filter(dept => dept.project_id === selectedProjectId.value)
    .map(dept => ({
      value: dept.id,
      label: dept.name
    }))
})

const daysInMonth = computed(() => {
  if (!selectedMonth.value) return []

  const monthDate = parseISO(selectedMonth.value + '-01')
  const start = startOfMonth(monthDate)
  const end = endOfMonth(monthDate)

  return eachDayOfInterval({ start, end }).map(date => ({
    date: format(date, 'yyyy-MM-dd'),
    dayNumber: format(date, 'd'),
    dayName: format(date, 'EEE', { locale: tr }),
    isWeekend: isWeekend(date)
  }))
})

// Check if a day is editable for an employee based on their assignment dates and approval status
const isDayEditable = (employee, date) => {
  // Check if this date has an approved timesheet (locked)
  const hasApprovedTimesheet = props.approvedTimesheets.some(ts => {
    const normalizedDate = ts.work_date.split('T')[0]
    return ts.employee_id === employee.id && normalizedDate === date
  })

  if (hasApprovedTimesheet) return false // Approved timesheets cannot be edited

  if (!employee.assignment_start_date) return true // No restrictions if no assignment dates

  const dayDate = new Date(date)
  const startDate = new Date(employee.assignment_start_date)

  // Check if day is before assignment start
  if (dayDate < startDate) return false

  // Check if day is after assignment end
  if (employee.assignment_end_date) {
    const endDate = new Date(employee.assignment_end_date)
    if (dayDate > endDate) return false
  }

  return true
}

// Methods
const loadData = () => {
  if (!selectedMonth.value || !selectedProjectId.value) return

  const params = {
    month: selectedMonth.value,
    project_id: selectedProjectId.value
  }

  // Only add department_id if it has a value
  if (selectedDepartmentId.value) {
    params.department_id = selectedDepartmentId.value
  }

  router.get(route('timesheets.bulk-entry'), params, {
    preserveState: true,
    preserveScroll: true,
    only: ['employees', 'existingTimesheets', 'approvedTimesheets'],
    onSuccess: () => {
      initializeAttendanceData()
      hasChanges.value = false
    }
  })
}

const toggleAttendance = (employeeId, date) => {
  // Find employee and check if day is editable
  const employee = props.employees.find(e => e.id === employeeId)
  if (!employee || !isDayEditable(employee, date)) {
    return // Don't allow toggling if day is not editable
  }

  if (!attendanceData.value[employeeId]) {
    attendanceData.value[employeeId] = {}
  }

  const current = attendanceData.value[employeeId][date]
  const states = ['present', 'absent', 'leave', 'sick', null]
  const currentIndex = states.indexOf(current || null)
  const nextIndex = (currentIndex + 1) % states.length

  attendanceData.value[employeeId][date] = states[nextIndex]
  hasChanges.value = true
}

const handleKeyDown = (event, employeeId, date) => {
  // Find employee and check if day is editable
  const employee = props.employees.find(e => e.id === employeeId)
  if (!employee || !isDayEditable(employee, date)) {
    return // Don't allow keyboard input if day is not editable
  }

  const key = event.key.toUpperCase()

  if (!attendanceData.value[employeeId]) {
    attendanceData.value[employeeId] = {}
  }

  let newValue = null

  switch (key) {
    case 'C':
      newValue = 'present'
      break
    case 'D':
      newValue = 'absent'
      break
    case 'İ':
    case 'I':
      newValue = 'leave'
      break
    case 'H':
      newValue = 'sick'
      break
    case ' ':
    case 'BACKSPACE':
    case 'DELETE':
      newValue = null
      break
    default:
      return
  }

  event.preventDefault()
  attendanceData.value[employeeId][date] = newValue
  hasChanges.value = true
}

const getAttendanceLabel = (employeeId, date) => {
  const value = attendanceData.value[employeeId]?.[date]
  const labels = {
    present: 'C',
    absent: 'D',
    leave: 'İ',
    sick: 'H',
    late: 'G',
    early_leave: 'E'
  }
  return labels[value] || ''
}

const getAttendanceCellClass = (employeeId, date) => {
  const value = attendanceData.value[employeeId]?.[date]
  const classes = {
    present: 'border-2 border-green-500 bg-green-50 text-green-700',
    absent: 'border-2 border-red-500 bg-red-50 text-red-700',
    leave: 'border-2 border-yellow-500 bg-yellow-50 text-yellow-700',
    sick: 'border-2 border-blue-500 bg-blue-50 text-blue-700',
    late: 'border-2 border-orange-500 bg-orange-50 text-orange-700',
    early_leave: 'border-2 border-purple-500 bg-purple-50 text-purple-700'
  }
  return classes[value] || 'border-2 border-gray-200 bg-white text-gray-400'
}

const getEmployeeTotalDays = (employeeId) => {
  const data = attendanceData.value[employeeId] || {}
  return Object.keys(data).filter(date => data[date]).length
}

const getEmployeeWorkDays = (employeeId) => {
  const data = attendanceData.value[employeeId] || {}
  return Object.keys(data).filter(date => data[date] === 'present').length
}

const getEmployeeLeaveDays = (employeeId) => {
  const data = attendanceData.value[employeeId] || {}
  return Object.keys(data).filter(date => data[date] === 'leave').length
}

const getEmployeeSickDays = (employeeId) => {
  const data = attendanceData.value[employeeId] || {}
  return Object.keys(data).filter(date => data[date] === 'sick').length
}

const getEmployeeOvertimeHours = (employeeId, type) => {
  const data = overtimeData.value[employeeId] || {}
  let total = 0

  Object.keys(data).forEach(date => {
    if (data[date].type === type) {
      total += data[date].hours || 0
    }
  })

  return total > 0 ? total.toFixed(1) : '-'
}

// Bulk action functions
const applyToWeekends = () => {
  if (!bulkAction.value.attendanceType) return

  props.employees.forEach(employee => {
    daysInMonth.value.forEach(day => {
      if (day.isWeekend && isDayEditable(employee, day.date)) {
        if (!attendanceData.value[employee.id]) {
          attendanceData.value[employee.id] = {}
        }
        attendanceData.value[employee.id][day.date] = bulkAction.value.attendanceType
      }
    })
  })

  hasChanges.value = true
}

const applyToWeekdays = () => {
  if (!bulkAction.value.attendanceType) return

  props.employees.forEach(employee => {
    daysInMonth.value.forEach(day => {
      if (!day.isWeekend && isDayEditable(employee, day.date)) {
        if (!attendanceData.value[employee.id]) {
          attendanceData.value[employee.id] = {}
        }
        attendanceData.value[employee.id][day.date] = bulkAction.value.attendanceType
      }
    })
  })

  hasChanges.value = true
}

const applyToAllDays = () => {
  if (!bulkAction.value.attendanceType) return

  props.employees.forEach(employee => {
    daysInMonth.value.forEach(day => {
      if (isDayEditable(employee, day.date)) {
        if (!attendanceData.value[employee.id]) {
          attendanceData.value[employee.id] = {}
        }
        attendanceData.value[employee.id][day.date] = bulkAction.value.attendanceType
      }
    })
  })

  hasChanges.value = true
}

const clearAllAttendance = () => {
  if (!confirm('Tüm devam kayıtlarını temizlemek istediğinizden emin misiniz?')) return

  attendanceData.value = {}
  overtimeData.value = {}
  hasChanges.value = true
}

// Overtime functions
const getOvertimeHours = (employeeId, date) => {
  return overtimeData.value[employeeId]?.[date]?.hours || null
}

const getDisabledDayTooltip = (employee, date) => {
  // Check if this date has an approved timesheet
  const hasApprovedTimesheet = props.approvedTimesheets.some(ts => {
    const normalizedDate = ts.work_date.split('T')[0]
    return ts.employee_id === employee.id && normalizedDate === date
  })

  if (hasApprovedTimesheet) {
    return 'Bu kayıt onaylanmış - değiştirilemez'
  }

  if (!employee.assignment_start_date) return ''

  const dayDate = new Date(date)
  const startDate = new Date(employee.assignment_start_date)

  if (dayDate < startDate) {
    return `Atama ${format(startDate, 'dd MMMM', { locale: tr })} tarihinde başlıyor`
  }

  if (employee.assignment_end_date) {
    const endDate = new Date(employee.assignment_end_date)
    if (dayDate > endDate) {
      return `Atama ${format(endDate, 'dd MMMM', { locale: tr })} tarihinde sona erdi`
    }
  }

  return ''
}

const showOvertimeMenu = (employeeId, date, event) => {
  const existingOvertime = overtimeData.value[employeeId]?.[date]

  contextMenu.value = {
    show: true,
    x: event.clientX,
    y: event.clientY,
    employeeId: employeeId,
    date: date,
    overtimeType: existingOvertime?.type || 'weekday',
    hours: existingOvertime?.hours || null
  }
}

const closeOvertimeMenu = () => {
  contextMenu.value.show = false
}

const saveOvertime = () => {
  if (!contextMenu.value.hours || contextMenu.value.hours <= 0) return

  if (!overtimeData.value[contextMenu.value.employeeId]) {
    overtimeData.value[contextMenu.value.employeeId] = {}
  }

  overtimeData.value[contextMenu.value.employeeId][contextMenu.value.date] = {
    type: contextMenu.value.overtimeType,
    hours: contextMenu.value.hours
  }

  hasChanges.value = true
  closeOvertimeMenu()
}

const removeOvertime = () => {
  if (overtimeData.value[contextMenu.value.employeeId]?.[contextMenu.value.date]) {
    delete overtimeData.value[contextMenu.value.employeeId][contextMenu.value.date]
    hasChanges.value = true
  }
  closeOvertimeMenu()
}

const saveAll = async () => {
  if (!hasChanges.value) return

  saving.value = true

  // Prepare data for submission
  const timesheets = []

  Object.keys(attendanceData.value).forEach(employeeId => {
    Object.keys(attendanceData.value[employeeId]).forEach(date => {
      const attendanceType = attendanceData.value[employeeId][date]
      if (attendanceType) {
        const overtime = overtimeData.value[employeeId]?.[date]

        timesheets.push({
          employee_id: parseInt(employeeId),
          project_id: selectedProjectId.value,
          department_id: selectedDepartmentId.value || null,
          work_date: date,
          attendance_type: attendanceType,
          entry_method: 'manual',
          overtime_hours: overtime?.hours || null,
          overtime_type: overtime?.type || null
        })
      }
    })
  })

  console.log(`💾 Saving ${timesheets.length} timesheets:`, timesheets)
  console.log(`📊 Before save - existingTimesheets count: ${props.existingTimesheets.length}`)

  // Log each employee's data for debugging
  timesheets.forEach((ts, index) => {
    const employee = props.employees.find(e => e.id === ts.employee_id)
    const employeeName = employee ? `${employee.first_name} ${employee.last_name}` : `ID:${ts.employee_id}`
    console.log(`  [${index}] ${employeeName} - ${ts.work_date} - ${ts.attendance_type}`)
  })

  router.post(route('timesheets.bulk-store'), {
    timesheets: timesheets,
    month: selectedMonth.value,
    project_id: selectedProjectId.value
  }, {
    onSuccess: (page) => {
      console.log('✅ Save successful - reloading data')
      hasChanges.value = false
      saving.value = false
      // Wait a moment then reload to ensure DB transaction is complete
      setTimeout(() => {
        loadData()
      }, 500)
    },
    onError: (errors) => {
      console.error('Save failed:', errors)
      saving.value = false
    }
  })
}

// Watch removed - initializeAttendanceData() is already called by loadData() on line 129
// The watch was causing double-initialization and overwriting user changes after save
</script>

<style scoped>
/* Fixed column widths for better scrolling */
table {
  table-layout: fixed;
}

/* Smooth scrolling */
.overflow-auto {
  scroll-behavior: smooth;
}

/* Focus styles for accessibility */
td:focus {
  outline: 2px solid #3b82f6;
  outline-offset: -2px;
}
</style>
