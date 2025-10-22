<template>
  <AppLayout
    title="Toplu Puantaj Girişi (v2)"
    :breadcrumbs="breadcrumbs"
    :fullWidth="true"
  >
    <template #fullWidthHeader>
      <div class="bg-white border-b border-gray-200 px-6 py-4">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-2xl font-bold text-gray-900">Toplu Puantaj Girişi</h1>
            <p class="mt-1 text-sm text-gray-500">
              {{ selectedMonth ? format(parseISO(selectedMonth + '-01'), 'MMMM yyyy', { locale: tr }) : '' }}
              {{ selectedProject ? ` - ${selectedProject.name}` : '' }}
            </p>
          </div>
          <div class="flex items-center space-x-3">
            <Button
              variant="outline"
              size="sm"
              @click="$inertia.visit(route('timesheets-v2.index'))"
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

        <!-- Bulk Actions with Shift Selection -->
        <div v-if="selectedProjectId && employees.length > 0" class="mt-4 flex items-center gap-3 p-3 bg-blue-50 rounded-lg border border-blue-200">
          <span class="text-sm font-medium text-blue-900">Toplu İşlemler:</span>

          <Select
            v-model="bulkShiftId"
            :options="shiftOptions"
            placeholder="Vardiya Seç"
            size="sm"
            class="w-64"
          />

          <Button
            variant="outline"
            size="sm"
            @click="applyToWeekends"
            :disabled="!bulkShiftId"
          >
            Hafta Sonlarına Uygula
          </Button>

          <Button
            variant="outline"
            size="sm"
            @click="applyToWeekdays"
            :disabled="!bulkShiftId"
          >
            Hafta İçine Uygula
          </Button>

          <Button
            variant="outline"
            size="sm"
            @click="applyToAllDays"
            :disabled="!bulkShiftId"
          >
            Tüm Günlere Uygula
          </Button>

          <Button
            variant="danger"
            size="sm"
            @click="clearAllShifts"
          >
            Tümünü Temizle
          </Button>
        </div>
      </div>
    </template>

    <!-- Legend -->
    <div class="px-6 py-3 bg-gray-50 border-b border-gray-200">
      <div class="flex items-center justify-between text-xs">
        <div class="flex items-center space-x-4">
          <div v-for="shift in legendShifts" :key="shift.code" class="flex items-center">
            <div :class="['w-8 h-8 rounded border-2 mr-2', getShiftCellClass(shift.id)]"></div>
            <span class="text-gray-700">{{ shift.code }}</span>
          </div>
        </div>
        <div class="text-gray-600">
          <span class="font-medium">Kısayollar:</span> {{ shortcutKeys }} | <span class="font-medium">Sağ Tık:</span> Fazla Mesai | <span class="font-medium">Space:</span> Temizle
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
                style="min-width: 60px; max-width: 60px; width: 60px;"
              >
                <div>{{ day.dayNumber }}</div>
                <div class="text-[10px] font-normal">{{ day.dayName }}</div>
              </th>

              <!-- Summary Column -->
              <th class="sticky right-0 z-20 bg-gray-100 px-2 py-3 text-center text-xs font-medium text-gray-700 uppercase tracking-wider border-l-2 border-gray-300 min-w-[100px]">
                Toplam Gün
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

              <!-- Day Cells - Clickable with Keyboard Shortcuts -->
              <td
                v-for="day in daysInMonth"
                :key="`${employee.id}-${day.date}`"
                :class="[
                  'px-1 py-1 text-center border-r border-gray-200 transition-colors cursor-pointer hover:bg-blue-50',
                  day.isWeekend ? 'bg-gray-50' : 'bg-white'
                ]"
                style="min-width: 60px; max-width: 60px; width: 60px;"
                @click="toggleShift(employee.id, day.date)"
                @contextmenu.prevent="showOvertimeMenu(employee.id, day.date, $event)"
                @keydown="handleKeyDown($event, employee.id, day.date)"
                :tabindex="0"
              >
                <div
                  :class="[
                    'w-full h-10 rounded flex flex-col items-center justify-center text-sm font-bold transition-all',
                    getShiftCellClass(getShift(employee.id, day.date))
                  ]"
                >
                  <span>{{ getShiftLabel(employee.id, day.date) }}</span>
                  <span v-if="getOvertimeHours(employee.id, day.date)" class="text-[10px] font-normal mt-0.5">
                    +{{ getOvertimeHours(employee.id, day.date) }}s
                  </span>
                </div>
              </td>

              <!-- Summary Cell -->
              <td class="sticky right-0 z-10 bg-gray-50 px-2 py-3 text-center border-l-2 border-gray-300 font-semibold text-gray-900">
                {{ getEmployeeTotalDays(employee.id) }}
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
import { ref, computed, reactive } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Button from '@/Components/UI/Button.vue'
import Input from '@/Components/UI/Input.vue'
import Select from '@/Components/UI/Select.vue'
import Spinner from '@/Components/UI/Spinner.vue'
import { format, parseISO, startOfMonth, endOfMonth, eachDayOfInterval, isWeekend } from 'date-fns'
import { tr } from 'date-fns/locale'

// Props
const props = defineProps({
  projects: { type: Array, default: () => [] },
  shifts: { type: Array, default: () => [] },
  employees: { type: Array, default: () => [] },
  existingTimesheets: { type: Array, default: () => [] },
  month: String,
  projectId: Number,
  departmentId: Number
})

// State
const selectedMonth = ref(props.month || format(new Date(), 'yyyy-MM'))
const selectedProjectId = ref(props.projectId || null)
const selectedDepartmentId = ref(props.departmentId || null)
const shiftData = reactive({})
const overtimeData = reactive({}) // { employeeId: { date: { type: 'weekday|weekend|holiday', hours: number } } }
const bulkShiftId = ref(null)
const saving = ref(false)
const hasChanges = ref(false)
const contextMenu = ref({ show: false, x: 0, y: 0, employeeId: null, date: null, overtimeType: 'weekday', hours: null })

// Common shifts for legend
const legendShifts = computed(() => {
  return props.shifts.filter(s => ['GN', 'GC', 'HS', 'HT', 'YI', 'RP'].includes(s.code))
})

// Shortcut keys display
const shortcutKeys = computed(() => {
  return props.shifts.map(s => s.code).join(', ')
})

// Initialize shift data from existing timesheets
const initializeShiftData = () => {
  Object.keys(shiftData).forEach(key => delete shiftData[key])
  Object.keys(overtimeData).forEach(key => delete overtimeData[key])

  props.existingTimesheets.forEach(ts => {
    const normalizedDate = ts.work_date.split('T')[0]
    if (!shiftData[ts.employee_id]) shiftData[ts.employee_id] = {}
    shiftData[ts.employee_id][normalizedDate] = ts.shift_id

    if (ts.overtime_hours && ts.overtime_hours > 0) {
      if (!overtimeData[ts.employee_id]) overtimeData[ts.employee_id] = {}
      overtimeData[ts.employee_id][normalizedDate] = {
        type: ts.overtime_type || 'weekday',
        hours: ts.overtime_hours
      }
    }
  })
}

initializeShiftData()

// Computed
const breadcrumbs = computed(() => [
  { label: 'Ana Sayfa', url: route('dashboard') },
  { label: 'Yeni Puantaj', url: route('timesheets-v2.index') },
  { label: 'Toplu Giriş', url: null }
])

const projectOptions = computed(() => props.projects.map(proj => ({ value: proj.id, label: proj.name })))
const selectedProject = computed(() => props.projects.find(p => p.id === selectedProjectId.value))
const departmentOptions = computed(() => {
  if (!selectedProjectId.value) return []
  const project = props.projects.find(p => p.id === selectedProjectId.value)
  if (!project || !project.departments) return []
  return project.departments.map(dept => ({ value: dept.id, label: dept.name }))
})

const shiftOptions = computed(() => props.shifts.map(shift => ({ value: shift.id, label: `${shift.code} - ${shift.name}` })))

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

// Methods
const loadData = () => {
  if (!selectedMonth.value || !selectedProjectId.value) return
  const params = { month: selectedMonth.value, project_id: selectedProjectId.value }
  if (selectedDepartmentId.value) params.department_id = selectedDepartmentId.value

  router.get(route('timesheets-v2.bulk-entry'), params, {
    preserveState: true, preserveScroll: true, only: ['employees', 'existingTimesheets'],
    onSuccess: () => { initializeShiftData(); hasChanges.value = false }
  })
}

const getShift = (employeeId, date) => shiftData[employeeId]?.[date] || null

const updateShift = (employeeId, date, shiftId) => {
  if (!shiftData[employeeId]) shiftData[employeeId] = {}
  shiftData[employeeId][date] = shiftId
  hasChanges.value = true
}

const toggleShift = (employeeId, date) => {
  console.log('toggleShift called:', { employeeId, date })

  const currentShiftId = getShift(employeeId, date)
  console.log('Current shift ID:', currentShiftId)

  if (!currentShiftId) {
    // Eğer boşsa, ilk vardiyayı (GN) ata
    const firstShift = props.shifts[0]
    console.log('Setting first shift:', firstShift)
    updateShift(employeeId, date, firstShift?.id || null)
    return
  }

  // Mevcut vardiyayı bul
  const currentIndex = props.shifts.findIndex(s => s.id === currentShiftId)
  console.log('Current shift index:', currentIndex)

  if (currentIndex === -1) {
    // Vardiya bulunamadıysa, ilk vardiyayı ata
    updateShift(employeeId, date, props.shifts[0]?.id || null)
    return
  }

  // Sonraki vardiyaya geç veya temizle
  const nextIndex = currentIndex + 1
  if (nextIndex >= props.shifts.length) {
    // Son vardiyadan sonra temizle
    console.log('Clearing shift (reached end)')
    updateShift(employeeId, date, null)
  } else {
    // Sonraki vardiyayı ata
    const nextShift = props.shifts[nextIndex]
    console.log('Setting next shift:', nextShift)
    updateShift(employeeId, date, nextShift.id)
  }
}

const handleKeyDown = (event, employeeId, date) => {
  const key = event.key.toUpperCase()

  console.log('Key pressed:', key, 'Employee:', employeeId, 'Date:', date)

  // Space, Backspace veya Delete ile temizle
  if (key === ' ' || key === 'BACKSPACE' || key === 'DELETE') {
    event.preventDefault()
    updateShift(employeeId, date, null)
    return
  }

  // Tek harfli veya iki harfli kod eşleşmesi
  const matchedShift = props.shifts.find(s => s.code.toUpperCase() === key || s.code.toUpperCase().startsWith(key))
  if (matchedShift) {
    event.preventDefault()
    updateShift(employeeId, date, matchedShift.id)
    console.log('Matched shift:', matchedShift.code, matchedShift.name)
  }
}

const getShiftLabel = (employeeId, date) => {
  const shiftId = getShift(employeeId, date)
  if (!shiftId) return ''
  const shift = props.shifts.find(s => s.id === shiftId)
  return shift ? shift.code : ''
}

const getShiftCellClass = (shiftId) => {
  if (!shiftId) return 'border-2 border-gray-200 bg-white text-gray-400'
  const shift = props.shifts.find(s => s.id === shiftId)
  if (!shift) return 'border-2 border-gray-200 bg-white text-gray-400'
  const colorMap = {
    'normal': 'border-2 border-green-500 bg-green-50 text-green-700',
    'weekend': 'border-2 border-purple-500 bg-purple-50 text-purple-700',
    'holiday': 'border-2 border-red-500 bg-red-50 text-red-700',
    'rest_day': 'border-2 border-indigo-500 bg-indigo-50 text-indigo-700',
    'annual_leave': 'border-2 border-yellow-500 bg-yellow-50 text-yellow-700',
    'sick_leave': 'border-2 border-blue-500 bg-blue-50 text-blue-700',
    'unpaid_leave': 'border-2 border-gray-500 bg-gray-50 text-gray-700',
    'excused_leave': 'border-2 border-teal-500 bg-teal-50 text-teal-700',
    'maternity_leave': 'border-2 border-pink-500 bg-pink-50 text-pink-700',
    'half_day': 'border-2 border-orange-500 bg-orange-50 text-orange-700',
  }
  return colorMap[shift.shift_type] || 'border-2 border-gray-300 bg-gray-100 text-gray-700'
}

const getEmployeeTotalDays = (employeeId) => {
  const data = shiftData[employeeId] || {}
  return Object.keys(data).filter(date => data[date]).length
}

const getOvertimeHours = (employeeId, date) => {
  const overtime = overtimeData[employeeId]?.[date]
  return overtime ? overtime.hours : null
}

const showOvertimeMenu = (employeeId, date, event) => {
  const existing = overtimeData[employeeId]?.[date]
  contextMenu.value = {
    show: true,
    x: event.clientX,
    y: event.clientY,
    employeeId,
    date,
    overtimeType: existing?.type || 'weekday',
    hours: existing?.hours || null
  }
}

const closeOvertimeMenu = () => { contextMenu.value.show = false }

const saveOvertime = () => {
  if (!contextMenu.value.hours || contextMenu.value.hours <= 0) return
  if (!overtimeData[contextMenu.value.employeeId]) overtimeData[contextMenu.value.employeeId] = {}
  overtimeData[contextMenu.value.employeeId][contextMenu.value.date] = {
    type: contextMenu.value.overtimeType,
    hours: contextMenu.value.hours
  }
  hasChanges.value = true
  closeOvertimeMenu()
}

const removeOvertime = () => {
  if (overtimeData[contextMenu.value.employeeId]?.[contextMenu.value.date]) {
    delete overtimeData[contextMenu.value.employeeId][contextMenu.value.date]
    hasChanges.value = true
  }
  closeOvertimeMenu()
}

const applyToWeekends = () => {
  if (!bulkShiftId.value) return
  props.employees.forEach(employee => {
    daysInMonth.value.forEach(day => { if (day.isWeekend) updateShift(employee.id, day.date, bulkShiftId.value) })
  })
}

const applyToWeekdays = () => {
  if (!bulkShiftId.value) return
  props.employees.forEach(employee => {
    daysInMonth.value.forEach(day => { if (!day.isWeekend) updateShift(employee.id, day.date, bulkShiftId.value) })
  })
}

const applyToAllDays = () => {
  if (!bulkShiftId.value) return
  props.employees.forEach(employee => {
    daysInMonth.value.forEach(day => { updateShift(employee.id, day.date, bulkShiftId.value) })
  })
}

const clearAllShifts = () => {
  if (!confirm('Tüm vardiya seçimlerini temizlemek istediğinizden emin misiniz?')) return
  Object.keys(shiftData).forEach(employeeId => delete shiftData[employeeId])
  Object.keys(overtimeData).forEach(employeeId => delete overtimeData[employeeId])
  hasChanges.value = true
}

const saveAll = async () => {
  if (!hasChanges.value) return
  saving.value = true

  const timesheets = []
  Object.keys(shiftData).forEach(employeeId => {
    Object.keys(shiftData[employeeId]).forEach(date => {
      const shiftId = shiftData[employeeId][date]
      if (shiftId) {
        const shift = props.shifts.find(s => s.id === shiftId)
        const overtime = overtimeData[employeeId]?.[date]
        if (shift) {
          timesheets.push({
            employee_id: parseInt(employeeId),
            project_id: selectedProjectId.value,
            shift_id: shiftId,
            work_date: date,
            hours_worked: shift.daily_hours + (overtime?.hours || 0),
            overtime_hours: overtime?.hours || 0,
            overtime_type: overtime?.type || null,
            notes: overtime ? `FM (${overtime.type}): ${overtime.hours} saat` : null
          })
        }
      }
    })
  })

  router.post(route('timesheets-v2.bulk-store'), { timesheets, month: selectedMonth.value, project_id: selectedProjectId.value }, {
    onSuccess: () => { hasChanges.value = false; saving.value = false; setTimeout(() => loadData(), 500) },
    onError: (errors) => { console.error('Save failed:', errors); saving.value = false }
  })
}
</script>

<style scoped>
table { table-layout: fixed; }
.overflow-auto { scroll-behavior: smooth; }
td:focus { outline: 2px solid #3b82f6; outline-offset: -2px; }
</style>