<template>
  <AppLayout
    title="Puantaj Girişi (v3)"
    :fullWidth="true"
  >
    <!-- Header Toolbar -->
    <template #fullWidthHeader>
      <div class="bg-white border-b border-gray-200 px-6 py-4">
        <div class="flex items-center justify-between mb-4">
          <div>
            <h1 class="text-2xl font-bold text-gray-900">Puantaj Girişi v3</h1>
            <p class="mt-1 text-sm text-gray-500">
              {{ selectedMonth ? format(parseISO(selectedMonth + '-01'), 'MMMM yyyy', { locale: tr }) : 'Ay seçiniz' }}
              {{ selectedProject ? ` - ${selectedProject.name}` : '' }}
            </p>
          </div>
          <div class="flex items-center space-x-3">
            <Button variant="outline" size="sm" @click="router.visit('/dashboard')">
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
              </svg>
              Ana Sayfa
            </Button>
            <Button
              v-if="selectedMonth && selectedProjectId"
              variant="primary"
              size="sm"
              @click="openApprovalModal"
            >
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              Aylık Onay
            </Button>
            <Button variant="success" size="sm" @click="saveAll" :disabled="!hasChanges || saving">
              <Spinner v-if="saving" class="w-4 h-4 mr-2" />
              <svg v-else class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
              Kaydet ({{ changedCount }} değişiklik)
            </Button>
          </div>
        </div>

        <!-- Filters -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Ay <span class="text-red-500">*</span></label>
            <Input v-model="selectedMonth" type="month" @change="loadData" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Proje <span class="text-red-500">*</span></label>
            <Select v-model="selectedProjectId" :options="projectOptions" placeholder="Proje seçin" @change="loadData" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Departman</label>
            <Select v-model="selectedDepartmentId" :options="departmentOptions" placeholder="Tümü" @change="loadData" :disabled="!selectedProjectId" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Personel Ara</label>
            <Input v-model="searchQuery" placeholder="Ad, soyad veya sicil..." />
          </div>
        </div>

        <!-- Vardiya Seçim Paneli (Basitleştirilmiş) -->
        <div v-if="selectedProjectId && employees.length > 0" class="p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg border border-blue-200">
          <div class="flex items-center gap-4 flex-wrap">
            <div class="flex items-center gap-2">
              <span class="text-sm font-semibold text-gray-900">Vardiya Seç:</span>
            </div>

            <div class="flex-1 max-w-md flex gap-2">
              <Select
                v-model="selectedShiftId"
                :options="shiftOptions"
                placeholder="Vardiya seçin..."
                size="sm"
                class="flex-1"
              />
              <Button
                v-if="selectedShiftId"
                variant="outline"
                size="sm"
                @click="selectedShiftId = null"
                title="Vardiya seçimini temizle"
              >
                ✕
              </Button>
            </div>

            <div class="flex gap-2">
              <Button variant="outline" size="sm" @click="assignToWeekends" :disabled="!selectedShiftId">
                Hafta Sonları
              </Button>
              <Button variant="outline" size="sm" @click="assignToWeekdays" :disabled="!selectedShiftId">
                Hafta İçi
              </Button>
              <Button variant="outline" size="sm" @click="assignToAll" :disabled="!selectedShiftId">
                Tümü
              </Button>
              <Button variant="danger" size="sm" @click="clearAll">
                Temizle
              </Button>
            </div>

            <div class="text-xs text-gray-600 ml-auto">
              <span class="font-medium">💡 Kullanım:</span> Vardiya seç → Hücreye/Kolona/Satıra tıkla
            </div>
          </div>
        </div>

        <!-- Legend (Tüm Vardiyalar) -->
        <div v-if="shifts.length > 0" class="mt-3 px-3 py-2 bg-gray-50 rounded-lg border border-gray-200">
          <div class="flex items-center gap-3 text-xs flex-wrap">
            <span class="font-semibold text-gray-700">Vardiyalar:</span>
            <div v-for="shift in shifts" :key="shift.id" class="flex items-center gap-1">
              <div :class="['w-6 h-6 rounded border-2 flex items-center justify-center text-[9px] font-bold', getShiftColorClass(shift)]">
                {{ shift.code }}
              </div>
              <span class="text-gray-600 text-[11px]">{{ shift.name }}</span>
            </div>
            <div class="ml-auto flex items-center gap-2 text-gray-500">
              <span>🏖️ = İzinli</span>
              <span>|</span>
              <span>{{ filteredEmployees.length }} Personel</span>
            </div>
          </div>
        </div>
      </div>
    </template>

    <!-- Main Content: Timesheet Table -->
    <div v-if="selectedProjectId && selectedMonth" class="flex-1 overflow-hidden bg-gray-50">
      <div class="h-full overflow-auto">
        <table class="min-w-full divide-y divide-gray-200 border-collapse bg-white">
          <thead class="bg-gradient-to-r from-gray-100 to-gray-50 sticky top-0 z-20 shadow-sm">
            <tr>
              <!-- Employee Column Header -->
              <th class="sticky left-0 z-30 bg-gradient-to-r from-gray-200 to-gray-100 px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase border-r-2 border-gray-400 w-72">
                Personel Adı
              </th>

              <!-- Day Headers (Clickable) -->
              <th
                v-for="day in daysInMonth"
                :key="day.date"
                @click="handleColumnHeaderClick(day)"
                :class="[
                  'px-2 py-3 text-center text-[10px] font-bold uppercase border-r border-gray-300 cursor-pointer hover:bg-blue-100 transition-colors',
                  day.isWeekend ? 'bg-gray-200 text-gray-600' : 'bg-gray-50 text-gray-700',
                  selectedShiftId ? 'ring-2 ring-blue-300 ring-inset' : ''
                ]"
                style="min-width: 50px; max-width: 50px;"
                :title="`${day.dayName} ${day.dayNumber} - Tüm personele atamak için tıklayın`"
              >
                <div class="font-bold">{{ day.dayNumber }}</div>
                <div class="text-[9px] font-normal">{{ day.dayName }}</div>
              </th>

              <!-- Summary Column -->
              <th class="sticky right-0 z-30 bg-gradient-to-r from-gray-100 to-gray-200 px-3 py-3 text-center text-xs font-bold text-gray-700 uppercase border-l-2 border-gray-400 min-w-[80px]">
                Toplam
              </th>
            </tr>
          </thead>

          <tbody class="divide-y divide-gray-200 bg-white">
            <tr
              v-for="employee in filteredEmployees"
              :key="employee.id"
              class="hover:bg-blue-50 transition-colors"
            >
              <!-- Employee Info (Clickable Row Header) -->
              <td
                @click="handleRowHeaderClick(employee)"
                class="sticky left-0 z-10 bg-white hover:bg-blue-50 px-4 py-2 border-r-2 border-gray-300 cursor-pointer"
                :title="`${employee.first_name} ${employee.last_name} - Tüm günlere atamak için tıklayın`"
              >
                <div class="flex items-center gap-3">
                  <div class="flex-shrink-0 w-8 h-8 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white text-xs font-bold shadow">
                    {{ employee.first_name.charAt(0) }}{{ employee.last_name.charAt(0) }}
                  </div>
                  <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-gray-900 truncate">
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
                @click="handleCellClick(employee.id, day.date)"
                @contextmenu.prevent="handleRightClick(employee.id, day.date, $event)"
                :class="[
                  'px-1 py-1 text-center border-r border-gray-200 relative',
                  day.isWeekend ? 'bg-gray-50' : 'bg-white',
                  getCellClass(employee.id, day.date)
                ]"
                style="min-width: 50px; max-width: 50px;"
              >
                <!-- İzin İkonu -->
                <div v-if="isLeaveDay(employee.id, day.date)" class="w-full h-12 flex flex-col items-center justify-center bg-yellow-100 border-2 border-yellow-400 rounded cursor-not-allowed" title="İzinli Gün">
                  <div class="text-lg">🏖️</div>
                  <div class="text-[9px] font-semibold text-yellow-800">{{ getLeaveDayInfo(employee.id, day.date)?.shift_code || 'İZİN' }}</div>
                </div>

                <!-- Normal Vardiya -->
                <div v-else
                  :class="[
                    'w-full h-12 flex flex-col items-center justify-center rounded transition-all relative',
                    getShiftCellClass(employee.id, day.date),
                    'cursor-pointer hover:ring-2 hover:ring-blue-400',
                    getOvertime(employee.id, day.date) ? 'ring-2 ring-orange-400' : '',
                    isApproved(employee.id, day.date) ? 'ring-2 ring-green-500' : ''
                  ]"
                  :title="getShiftTooltip(employee.id, day.date)"
                >
                  <!-- Onay Badge'i -->
                  <div
                    v-if="isApproved(employee.id, day.date)"
                    class="absolute -top-1 -right-1 w-4 h-4 bg-green-500 rounded-full flex items-center justify-center shadow-sm"
                    title="Onaylanmış"
                  >
                    <svg class="w-2.5 h-2.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                    </svg>
                  </div>

                  <span class="text-sm font-bold">{{ getShiftLabel(employee.id, day.date) }}</span>
                  <div class="flex items-center gap-1 text-[9px]">
                    <span v-if="getShiftHours(employee.id, day.date)" class="text-gray-600">
                      {{ getShiftHours(employee.id, day.date) }}s
                    </span>
                    <span v-if="getOvertime(employee.id, day.date)" class="text-orange-600 font-bold">
                      +{{ getOvertime(employee.id, day.date).hours }}FM
                    </span>
                  </div>
                </div>
              </td>

              <!-- Summary Cell -->
              <td class="sticky right-0 z-10 bg-gradient-to-r from-gray-50 to-gray-100 px-3 py-2 text-center border-l-2 border-gray-300">
                <div class="font-bold text-lg text-gray-900">{{ getEmployeeTotalDays(employee.id) }}</div>
                <div class="text-[9px] text-gray-500">gün</div>
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Empty State -->
        <div v-if="filteredEmployees.length === 0 && employees.length > 0" class="text-center py-12 bg-white">
          <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
          <h3 class="mt-2 text-sm font-medium text-gray-900">Personel bulunamadı</h3>
          <p class="mt-1 text-sm text-gray-500">Arama kriterlerinizi değiştirip tekrar deneyin.</p>
        </div>

        <div v-if="employees.length === 0 && selectedProjectId" class="text-center py-12 bg-white">
          <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
          </svg>
          <h3 class="mt-2 text-sm font-medium text-gray-900">Personel bulunamadı</h3>
          <p class="mt-1 text-sm text-gray-500">Bu projeye atanmış aktif personel yok.</p>
        </div>
      </div>
    </div>

    <!-- No Selection State -->
    <div v-else class="flex-1 flex items-center justify-center bg-gray-50">
      <div class="text-center p-8">
        <svg class="mx-auto h-24 w-24 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
        <h3 class="mt-4 text-xl font-semibold text-gray-900">Puantaj Girişi v3</h3>
        <p class="mt-2 text-sm text-gray-600 max-w-md mx-auto">
          Başlamak için yukarıdan <span class="font-semibold">ay</span> ve <span class="font-semibold">proje</span> seçin.
        </p>
      </div>
    </div>

    <!-- Right Click Context Menu (Fazla Mesai) -->
    <div
      v-if="contextMenu.show"
      :style="{ position: 'fixed', top: contextMenu.y + 'px', left: contextMenu.x + 'px', zIndex: 9999 }"
      class="bg-white rounded-lg shadow-2xl border border-gray-300 py-2 min-w-[280px]"
      @click.stop
    >
      <div class="px-4 py-2 border-b border-gray-200 bg-gray-50">
        <h3 class="text-sm font-bold text-gray-800">Fazla Mesai Girişi</h3>
        <p v-if="contextMenu.date" class="text-xs text-gray-600 mt-0.5">
          {{ format(parseISO(contextMenu.date), 'd MMMM yyyy, EEEE', { locale: tr }) }}
        </p>
      </div>

      <div class="px-4 py-3 space-y-3">
        <div>
          <label class="block text-xs font-semibold text-gray-700 mb-1">Mesai Tipi</label>
          <select v-model="contextMenu.overtimeType" class="w-full text-sm border-gray-300 rounded-md">
            <option value="weekday">Hafta İçi (%50)</option>
            <option value="weekend">Hafta Sonu (%100)</option>
            <option value="holiday">Resmi Tatil (%200)</option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-semibold text-gray-700 mb-1">Saat</label>
          <input
            v-model.number="contextMenu.hours"
            type="number"
            min="0"
            max="24"
            step="0.5"
            class="w-full text-sm border-gray-300 rounded-md"
            placeholder="Örn: 2, 2.5, 3"
          />
        </div>
      </div>

      <div class="px-4 py-2 border-t border-gray-200 flex justify-end gap-2">
        <button @click="closeContextMenu" class="px-3 py-1.5 text-xs font-medium text-gray-700 border border-gray-300 rounded-md hover:bg-gray-50">
          İptal
        </button>
        <button @click="saveContextMenu" class="px-3 py-1.5 text-xs font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">
          Kaydet
        </button>
      </div>
    </div>

    <!-- Click outside to close context menu -->
    <div v-if="contextMenu.show" class="fixed inset-0 z-[9998]" @click="closeContextMenu"></div>

    <!-- Aylık Onay Modal -->
    <div
      v-if="showApprovalModal"
      class="fixed inset-0 z-[9999] overflow-y-auto"
      aria-labelledby="modal-title"
      role="dialog"
      aria-modal="true"
    >
      <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Background overlay -->
        <div
          class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
          aria-hidden="true"
          @click="showApprovalModal = false"
        ></div>

        <!-- Center helper -->
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <!-- Modal panel -->
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full relative z-[10000]">
          <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
            <div class="sm:flex sm:items-start">
              <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left flex-1">
                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                  Aylık Toplu Onay
                </h3>
                <div class="mt-4 space-y-4">
                  <div>
                    <p class="text-sm text-gray-700 font-semibold">Seçili Dönem:</p>
                    <p class="text-sm text-gray-900 mt-1">
                      {{ format(parseISO(selectedMonth + '-01'), 'MMMM yyyy', { locale: tr }) }}
                    </p>
                  </div>
                  <div>
                    <p class="text-sm text-gray-700 font-semibold">Proje:</p>
                    <p class="text-sm text-gray-900 mt-1">{{ selectedProject?.name }}</p>
                  </div>
                  <!-- Tüm puantajlar onaylıysa -->
                  <div v-if="approvalStats && approvalStats.total > 0 && approvalStats.pending === 0" class="bg-green-50 border border-green-200 rounded-lg p-3">
                    <div class="flex items-center">
                      <svg class="w-5 h-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                      </svg>
                      <p class="text-sm text-green-800 font-semibold">
                        Tüm puantajlar onaylanmış! İşlem yapmanıza gerek yok.
                      </p>
                    </div>
                  </div>

                  <!-- Hiç puantaj yoksa -->
                  <div v-else-if="approvalStats && approvalStats.total === 0" class="bg-gray-50 border border-gray-200 rounded-lg p-3">
                    <p class="text-sm text-gray-600 text-center">
                      Bu ay ve proje için henüz puantaj girilmemiş.
                    </p>
                  </div>

                  <!-- Bekleyen puantaj varsa -->
                  <div v-else-if="approvalStats && approvalStats.pending > 0" class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                    <p class="text-sm text-yellow-800">
                      <strong>Uyarı:</strong> Bu işlem, seçili ay ve projedeki {{ approvalStats.pending }} onaylanmamış puantajı onaylayacaktır.
                    </p>
                  </div>

                  <!-- İstatistikler -->
                  <div v-if="approvalStats" class="bg-blue-50 rounded-lg p-4">
                    <div class="grid grid-cols-3 gap-4 text-center">
                      <div>
                        <div class="text-2xl font-bold text-gray-900">{{ approvalStats.total }}</div>
                        <div class="text-xs text-gray-600">Toplam</div>
                      </div>
                      <div>
                        <div class="text-2xl font-bold text-green-600">{{ approvalStats.approved }}</div>
                        <div class="text-xs text-gray-600">Onaylı</div>
                      </div>
                      <div>
                        <div class="text-2xl font-bold text-yellow-600">{{ approvalStats.pending }}</div>
                        <div class="text-xs text-gray-600">Bekleyen</div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="bg-gray-50 px-4 py-3 sm:px-6 flex flex-col-reverse sm:flex-row-reverse gap-2">
            <Button
              v-if="approvalStats && approvalStats.pending > 0"
              @click="approveMonthly"
              variant="success"
              size="sm"
              :disabled="approvalLoading"
              class="w-full sm:w-auto"
            >
              <Spinner v-if="approvalLoading" class="w-4 h-4 mr-2" />
              <span v-else>{{ approvalStats.pending }} Puantajı Onayla</span>
            </Button>
            <Button
              @click="showApprovalModal = false"
              :variant="approvalStats?.pending > 0 ? 'outline' : 'primary'"
              size="sm"
              :disabled="approvalLoading"
              class="w-full sm:w-auto"
            >
              {{ approvalStats?.pending > 0 ? 'İptal' : 'Kapat' }}
            </Button>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Button from '@/Components/UI/Button.vue'
import Input from '@/Components/UI/Input.vue'
import Select from '@/Components/UI/Select.vue'
import Spinner from '@/Components/UI/Spinner.vue'
import { format, parseISO, startOfMonth, endOfMonth, eachDayOfInterval, isWeekend } from 'date-fns'
import { tr } from 'date-fns/locale'
import axios from 'axios'

// Props
const props = defineProps({
  projects: { type: Array, default: () => [] },
  shifts: { type: Array, default: () => [] },
  employees: { type: Array, default: () => [] },
  existingTimesheets: { type: Array, default: () => [] },
  leaveDays: { type: Object, default: () => ({}) },
  month: String,
  projectId: Number,
  departmentId: Number
})

// State
const selectedMonth = ref(props.month || format(new Date(), 'yyyy-MM'))
const selectedProjectId = ref(props.projectId || null)
const selectedDepartmentId = ref(props.departmentId || null)
const searchQuery = ref('')
const selectedShiftId = ref(null)

// ARRAY kullanarak reactivity sorununu çözelim
// Her kayıt: { employeeId, date, shiftId }
const shiftData = ref([])
const overtimeData = ref([])

const saving = ref(false)
const hasChanges = ref(false)
const contextMenu = ref({ show: false, x: 0, y: 0, employeeId: null, date: null, overtimeType: 'weekday', hours: null })
const showApprovalModal = ref(false)
const approvalLoading = ref(false)
const approvalStats = ref(null)

// Initialize shift data from existing timesheets
const initializeShiftData = () => {
  shiftData.value = []
  overtimeData.value = []

  props.existingTimesheets.forEach(ts => {
    const normalizedDate = ts.work_date.split('T')[0]

    shiftData.value.push({
      employeeId: ts.employee_id,
      date: normalizedDate,
      shiftId: ts.shift_id
    })

    if (ts.overtime_hours && ts.overtime_hours > 0) {
      overtimeData.value.push({
        employeeId: ts.employee_id,
        date: normalizedDate,
        type: ts.overtime_type || 'weekday',
        hours: ts.overtime_hours
      })
    }
  })

  hasChanges.value = false
  console.log('📦 Initialized shiftData:', shiftData.value)
}

initializeShiftData()

// Computed
const projectOptions = computed(() => props.projects.map(p => ({ value: p.id, label: p.name })))
const selectedProject = computed(() => props.projects.find(p => p.id === selectedProjectId.value))
const departmentOptions = computed(() => {
  if (!selectedProjectId.value) return []
  const project = props.projects.find(p => p.id === selectedProjectId.value)
  return project?.departments?.map(d => ({ value: d.id, label: d.name })) || []
})

const shiftOptions = computed(() => props.shifts.map(s => ({ value: s.id, label: `${s.code} - ${s.name}` })))

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

const filteredEmployees = computed(() => {
  if (!searchQuery.value.trim()) return props.employees
  const query = searchQuery.value.toLowerCase()
  return props.employees.filter(emp =>
    emp.first_name.toLowerCase().includes(query) ||
    emp.last_name.toLowerCase().includes(query) ||
    emp.employee_code.toLowerCase().includes(query)
  )
})

const changedCount = computed(() => {
  return shiftData.value.length
})

// COMPUTED MAP - Bu kesinlikle reactive olacak!
const shiftMap = computed(() => {
  const map = {}
  shiftData.value.forEach(s => {
    const key = `${s.employeeId}_${s.date}`
    map[key] = s.shiftId
  })
  return map
})

// İzin kontrolü
const isLeaveDay = (employeeId, date) => {
  const leaves = props.leaveDays[employeeId]
  return leaves?.some(l => l.date === date) || false
}

const getLeaveDayInfo = (employeeId, date) => {
  const leaves = props.leaveDays[employeeId]
  return leaves?.find(l => l.date === date) || null
}

// Onay kontrolü
const isApproved = (employeeId, date) => {
  const existing = props.existingTimesheets.find(
    ts => ts.employee_id === employeeId &&
          ts.work_date.split('T')[0] === date
  )
  return existing?.is_approved === true
}

// Shift management (COMPUTED MAP kullanarak - GARANTİLİ REACTIVE)
const getShift = (employeeId, date) => {
  const key = `${employeeId}_${date}`
  return shiftMap.value[key] || null
}

const setShift = (employeeId, date, shiftId) => {
  if (isLeaveDay(employeeId, date)) {
    alert('Bu gün izinli! Manuel puantaj girilemez.')
    return
  }

  // Onaylı puantajlar düzenlenemez
  if (isApproved(employeeId, date)) {
    alert('Bu puantaj onaylanmış! Değiştirmek için İK onayı gereklidir.')
    return
  }

  // KRİTİK FİX: shiftId string olarak gelebiliyor (dropdown'dan), number'a çevir!
  const normalizedShiftId = shiftId === null ? null : Number(shiftId)

  // Önce var mı kontrol et
  const existingIndex = shiftData.value.findIndex(
    s => s.employeeId === employeeId && s.date === date
  )

  if (normalizedShiftId === null) {
    // Null ise sil
    if (existingIndex !== -1) {
      shiftData.value.splice(existingIndex, 1)
    }
  } else if (existingIndex !== -1) {
    // Güncelle
    shiftData.value[existingIndex].shiftId = normalizedShiftId
  } else {
    // Yeni ekle
    shiftData.value.push({ employeeId, date, shiftId: normalizedShiftId })
  }

  hasChanges.value = true
}

const getShiftLabel = (employeeId, date) => {
  const key = `${employeeId}_${date}`
  const shiftId = shiftMap.value[key]
  if (!shiftId) return ''
  const shift = props.shifts.find(s => s.id === shiftId)
  return shift?.code || ''
}

const getShiftHours = (employeeId, date) => {
  const key = `${employeeId}_${date}`
  const shiftId = shiftMap.value[key]
  if (!shiftId) return null
  const shift = props.shifts.find(s => s.id === shiftId)
  return shift?.daily_hours || null
}

const getShiftTooltip = (employeeId, date) => {
  const key = `${employeeId}_${date}`
  const shiftId = shiftMap.value[key]
  if (!shiftId) return 'Boş - Vardiya atamak için tıklayın'
  const shift = props.shifts.find(s => s.id === shiftId)
  const overtime = getOvertime(employeeId, date)
  const approved = isApproved(employeeId, date)

  let tooltip = shift ? `${shift.name} (${shift.code}) - ${shift.daily_hours} saat` : ''
  if (overtime) {
    tooltip += `\nFM: ${overtime.hours} saat (${overtime.type})`
  }
  if (approved) {
    tooltip += `\n✓ ONAYLANMIŞ - Düzenlemek için İK onayı gerekir`
  }
  return tooltip
}

// FM bilgisini al
const getOvertime = (employeeId, date) => {
  return overtimeData.value.find(
    o => Number(o.employeeId) === Number(employeeId) && o.date === date
  ) || null
}

const getShiftColorClass = (shift) => {
  const colorMap = {
    'normal': 'bg-green-100 border-green-400 text-green-800',
    'weekend': 'bg-purple-100 border-purple-400 text-purple-800',
    'holiday': 'bg-red-100 border-red-400 text-red-800',
    'rest_day': 'bg-indigo-100 border-indigo-400 text-indigo-800',
    'annual_leave': 'bg-yellow-100 border-yellow-400 text-yellow-800',
    'sick_leave': 'bg-blue-100 border-blue-400 text-blue-800',
    'unpaid_leave': 'bg-gray-100 border-gray-400 text-gray-800',
  }
  return colorMap[shift.shift_type] || 'bg-gray-100 border-gray-300 text-gray-700'
}

const getShiftCellClass = (employeeId, date) => {
  const key = `${employeeId}_${date}`
  const shiftId = shiftMap.value[key]  // Computed property kullanıyoruz!
  if (!shiftId) return 'border-2 border-gray-200 bg-white hover:bg-gray-50'
  const shift = props.shifts.find(s => s.id === shiftId)
  return shift ? `border-2 ${getShiftColorClass(shift)}` : 'border-2 border-gray-200 bg-white'
}

const getCellClass = (employeeId, date) => {
  if (isLeaveDay(employeeId, date)) return 'cursor-not-allowed'
  return selectedShiftId.value ? 'cursor-pointer hover:ring-2 hover:ring-blue-300' : 'cursor-pointer hover:bg-blue-50'
}

const getEmployeeTotalDays = (employeeId) => {
  return shiftData.value.filter(s => s.employeeId === employeeId).length
}

// Cell click handler (Basitleştirilmiş)
const handleCellClick = (employeeId, date) => {
  if (isLeaveDay(employeeId, date)) {
    alert('Bu gün izinli! Manuel puantaj girilemez.')
    return
  }

  if (selectedShiftId.value) {
    // Seçili vardiya varsa, onu ata
    setShift(employeeId, date, selectedShiftId.value)
  } else {
    // Yoksa toggle mode (cycle through shifts)
    const currentShiftId = getShift(employeeId, date)
    if (!currentShiftId) {
      setShift(employeeId, date, props.shifts[0]?.id)
    } else {
      const currentIndex = props.shifts.findIndex(s => s.id === currentShiftId)
      const nextIndex = currentIndex + 1
      if (nextIndex >= props.shifts.length) {
        setShift(employeeId, date, null)
      } else {
        setShift(employeeId, date, props.shifts[nextIndex].id)
      }
    }
  }
}

// Column header click (.NET: Tüm personele atama)
const handleColumnHeaderClick = (day) => {
  if (!selectedShiftId.value) {
    return // Vardiya seçilmemişse işlem yapma
  }

  filteredEmployees.value.forEach(employee => {
    if (!isLeaveDay(employee.id, day.date)) {
      setShift(employee.id, day.date, selectedShiftId.value)
    }
  })
}

// Row header click (.NET: Tek personele tüm günlere atama)
const handleRowHeaderClick = (employee) => {
  if (!selectedShiftId.value) {
    return // Vardiya seçilmemişse işlem yapma
  }

  daysInMonth.value.forEach(day => {
    if (!isLeaveDay(employee.id, day.date)) {
      setShift(employee.id, day.date, selectedShiftId.value)
    }
  })
}

// Right click for overtime
const handleRightClick = (employeeId, date, event) => {
  if (isLeaveDay(employeeId, date)) return

  // Onaylı puantajlar düzenlenemez
  if (isApproved(employeeId, date)) {
    alert('Bu puantaj onaylanmış! Fazla mesai değiştirmek için İK onayı gereklidir.')
    return
  }

  const existing = overtimeData.value.find(o => o.employeeId === employeeId && o.date === date)
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

const closeContextMenu = () => { contextMenu.value.show = false }

const saveContextMenu = () => {
  if (!contextMenu.value.hours || contextMenu.value.hours <= 0) {
    alert('Geçerli bir saat giriniz!')
    return
  }

  const existingIndex = overtimeData.value.findIndex(
    o => o.employeeId === contextMenu.value.employeeId && o.date === contextMenu.value.date
  )

  const overtimeEntry = {
    employeeId: contextMenu.value.employeeId,
    date: contextMenu.value.date,
    type: contextMenu.value.overtimeType,
    hours: contextMenu.value.hours
  }

  if (existingIndex !== -1) {
    overtimeData.value[existingIndex] = overtimeEntry
    console.log('🔧 FM güncellendi:', overtimeEntry)
  } else {
    overtimeData.value.push(overtimeEntry)
    console.log('➕ FM eklendi:', overtimeEntry)
  }

  console.log('📊 Toplam FM kayıtları:', overtimeData.value.length)
  console.log('📦 Tüm FM verileri:', overtimeData.value)

  hasChanges.value = true
  closeContextMenu()
}

// Bulk operations
const assignToWeekends = () => {
  if (!selectedShiftId.value) return

  filteredEmployees.value.forEach(employee => {
    daysInMonth.value.forEach(day => {
      if (day.isWeekend && !isLeaveDay(employee.id, day.date)) {
        setShift(employee.id, day.date, selectedShiftId.value)
      }
    })
  })
}

const assignToWeekdays = () => {
  if (!selectedShiftId.value) return

  filteredEmployees.value.forEach(employee => {
    daysInMonth.value.forEach(day => {
      if (!day.isWeekend && !isLeaveDay(employee.id, day.date)) {
        setShift(employee.id, day.date, selectedShiftId.value)
      }
    })
  })
}

const assignToAll = () => {
  if (!selectedShiftId.value) return

  filteredEmployees.value.forEach(employee => {
    daysInMonth.value.forEach(day => {
      if (!isLeaveDay(employee.id, day.date)) {
        setShift(employee.id, day.date, selectedShiftId.value)
      }
    })
  })
}

const clearAll = () => {
  // Temizle için onay iste (destructive işlem)
  if (!confirm('TÜM vardiya seçimlerini temizlemek istediğinizden emin misiniz?')) return
  shiftData.value = []
  overtimeData.value = []
  hasChanges.value = true
}

// Load data
const loadData = () => {
  if (!selectedMonth.value || !selectedProjectId.value) return

  const params = {
    month: selectedMonth.value,
    project_id: selectedProjectId.value
  }
  if (selectedDepartmentId.value) {
    params.department_id = selectedDepartmentId.value
  }

  router.get('/timesheets/bulk/entry', params, {
    preserveState: true,
    preserveScroll: true,
    only: ['employees', 'existingTimesheets', 'leaveDays'],
    onSuccess: () => {
      initializeShiftData()
    }
  })
}

// Approval functions
const loadApprovalStats = async () => {
  if (!selectedMonth.value || !selectedProjectId.value) {
    console.log('❌ loadApprovalStats: Ay veya proje seçilmemiş', {
      month: selectedMonth.value,
      projectId: selectedProjectId.value
    })
    return
  }

  console.log('🔄 loadApprovalStats başlıyor...', {
    month: selectedMonth.value,
    projectId: selectedProjectId.value
  })

  try {
    const [year, month] = selectedMonth.value.split('-')
    const params = {
      year: parseInt(year),
      month: parseInt(month),
      project_id: selectedProjectId.value
    }

    console.log('📤 API çağrısı yapılıyor:', params)

    const response = await axios.get('/timesheets-v2/approval-stats', { params })

    console.log('✅ API yanıtı:', response.data)
    approvalStats.value = response.data
  } catch (error) {
    console.error('❌ İstatistikler yüklenemedi:', error)
    console.error('Hata detayı:', error.response?.data)
    approvalStats.value = null
    alert('İstatistikler yüklenirken hata oluştu: ' + (error.response?.data?.message || error.message))
  }
}

// Modal açma fonksiyonu
const openApprovalModal = () => {
  showApprovalModal.value = true
  loadApprovalStats()
}

const approveMonthly = async () => {
  if (!selectedMonth.value || !selectedProjectId.value) return

  const pendingCount = approvalStats.value?.pending || 0
  if (pendingCount === 0) {
    alert('Onaylanacak puantaj bulunmamaktadır.')
    return
  }

  if (!confirm(`${pendingCount} puantajı onaylamak istediğinizden emin misiniz?`)) {
    return
  }

  approvalLoading.value = true

  try {
    const [year, month] = selectedMonth.value.split('-')
    const response = await axios.post('/timesheets-v2/approve-monthly', {
      year: parseInt(year),
      month: parseInt(month),
      project_id: selectedProjectId.value
    })

    console.log('✅ Onay başarılı:', response.data)

    // Modal'ı kapat
    showApprovalModal.value = false
    approvalLoading.value = false

    // Başarı mesajı
    alert(`${pendingCount} puantaj başarıyla onaylandı!`)

    // Sayfayı tam yenile (Inertia reload)
    router.reload({
      only: ['employees', 'existingTimesheets', 'leaveDays'],
      onSuccess: () => {
        console.log('📄 Sayfa verileri yenilendi')
        initializeShiftData()
      }
    })
  } catch (error) {
    approvalLoading.value = false
    console.error('❌ Onay hatası:', error)
    alert('Onay işlemi başarısız: ' + (error.response?.data?.message || error.message))
  }
}

// Save all
const saveAll = async () => {
  if (!hasChanges.value) return

  console.log('💾 KAYDETME BAŞLADI')
  console.log('📋 shiftData:', shiftData.value.length, 'kayıt')
  console.log('⏰ overtimeData:', overtimeData.value.length, 'FM kaydı')

  saving.value = true

  const timesheets = []

  shiftData.value.forEach(shift => {
    const shiftInfo = props.shifts.find(s => s.id === shift.shiftId)

    // KRİTİK FİX: employeeId karşılaştırması - number vs string sorununu çöz
    const overtime = overtimeData.value.find(
      o => Number(o.employeeId) === Number(shift.employeeId) && o.date === shift.date
    )

    console.log(`🔍 İşleniyor: Emp=${shift.employeeId}, Date=${shift.date}, Shift=${shift.shiftId}, FM=${overtime?.hours || 0}`)

    if (overtime) {
      console.log('🎯 FM bulundu! Overtime data:', overtime)
    }

    if (shiftInfo) {
      const entry = {
        employee_id: shift.employeeId,
        project_id: selectedProjectId.value,
        shift_id: shift.shiftId,
        work_date: shift.date,
        hours_worked: Number(shiftInfo.daily_hours) + Number(overtime?.hours || 0),
        overtime_hours: Number(overtime?.hours || 0),
        overtime_type: overtime?.type || null,
        notes: overtime ? `FM (${overtime.type}): ${overtime.hours} saat` : null
      }
      timesheets.push(entry)

      if (overtime) {
        console.log('✅ FM ile birlikte kaydediliyor:', entry)
      }
    }
  })

  console.log('📤 Backend\'e gönderilecek:', timesheets.length, 'timesheet')
  console.log('📦 Gönderilen data:', { timesheets, month: selectedMonth.value, project_id: selectedProjectId.value })

  router.post('/timesheets/bulk/store', {
    timesheets,
    month: selectedMonth.value,
    project_id: selectedProjectId.value
  }, {
    onSuccess: () => {
      console.log('✅ Kayıt başarılı!')
      hasChanges.value = false
      saving.value = false
      setTimeout(() => loadData(), 500)
    },
    onError: (errors) => {
      console.error('❌ Kayıt başarısız:', errors)
      saving.value = false
      alert('Kayıt başarısız! Hata: ' + JSON.stringify(errors))
    }
  })
}

// Artık assignMode yok, watch'a gerek yok
</script>

<style scoped>
table { table-layout: fixed; }
.custom-scrollbar::-webkit-scrollbar { width: 8px; height: 8px; }
.custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #888; border-radius: 4px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #555; }
</style>
