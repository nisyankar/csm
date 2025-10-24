<template>
  <AppLayout title="Yeni İzin Talebi" :full-width="true">
    <!-- Full-width header -->
    <template #fullWidthHeader>
      <div class="bg-gradient-to-r from-purple-600 via-purple-700 to-indigo-800 border-b border-purple-900/20 w-full">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
          <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
              <div class="flex-shrink-0 w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm">
                <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
              </div>
              <div>
                <h1 class="text-2xl lg:text-3xl font-bold text-white">Yeni İzin Talebi</h1>
                <p class="text-purple-100 text-sm mt-1">İzin talebi oluşturun</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Breadcrumb -->
        <div class="bg-black/10 border-t border-white/10">
          <div class="w-full px-4 sm:px-6 lg:px-8">
            <div class="flex items-center space-x-2 py-2">
              <ol class="flex items-center space-x-2">
                <li>
                  <Link :href="route('dashboard')" class="text-purple-100 hover:text-white transition-colors">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                    </svg>
                  </Link>
                </li>
                <li class="flex items-center">
                  <svg class="h-3 w-3 text-purple-200 mx-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                  </svg>
                  <Link :href="route('leave-requests.index')" class="text-purple-100 hover:text-white text-sm">İzin Talepleri</Link>
                </li>
                <li class="flex items-center">
                  <svg class="h-3 w-3 text-purple-200 mx-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                  </svg>
                  <span class="text-white font-medium text-sm">Yeni Talep</span>
                </li>
              </ol>
            </div>
          </div>
        </div>
      </div>
    </template>

    <!-- Main Content -->
    <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
      <form @submit.prevent="submitForm" class="space-y-6">
        <!-- Temel Bilgiler -->
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-medium text-gray-900">Temel Bilgiler</h3>
          </div>
          <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
              <!-- Çalışan Seçimi -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Çalışan <span class="text-red-500">*</span>
                </label>
                <select
                  v-model="form.employee_id"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all"
                  :class="{'border-red-300 focus:ring-red-500': form.errors.employee_id}"
                >
                  <option value="">Seçiniz...</option>
                  <option v-for="employee in employees" :key="employee.id" :value="employee.id">
                    {{ employee.first_name }} {{ employee.last_name }} ({{ employee.employee_code }})
                  </option>
                </select>
                <p v-if="form.errors.employee_id" class="text-red-600 text-sm mt-2">
                  {{ form.errors.employee_id }}
                </p>
              </div>

              <!-- İzin Türü -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  İzin Türü <span class="text-red-500">*</span>
                </label>
                <select
                  v-model="form.leave_type_id"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all"
                  :class="{'border-red-300 focus:ring-red-500': form.errors.leave_type_id}"
                >
                  <option value="">Seçiniz...</option>
                  <option v-for="type in leave_types" :key="type.id" :value="type.id">
                    {{ type.name }}
                  </option>
                </select>
                <p v-if="form.errors.leave_type_id" class="text-red-600 text-sm mt-2">
                  {{ form.errors.leave_type_id }}
                </p>
              </div>

              <!-- Vekil Çalışan -->
              <div class="lg:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Vekil Çalışan (Opsiyonel)
                </label>
                <select
                  v-model="form.substitute_employee_id"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all"
                >
                  <option value="">Seçiniz...</option>
                  <option v-for="employee in substitute_employees" :key="employee.id" :value="employee.id">
                    {{ employee.first_name }} {{ employee.last_name }} ({{ employee.employee_code }})
                  </option>
                </select>
                <p class="text-gray-500 text-sm mt-2">
                  İzniniz süresince sizi temsil edecek çalışanı seçebilirsiniz
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- İzin Tarihleri -->
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-medium text-gray-900">İzin Tarihleri</h3>
          </div>
          <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
              <!-- Başlangıç Tarihi -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Başlangıç Tarihi <span class="text-red-500">*</span>
                </label>
                <input
                  type="date"
                  v-model="form.start_date"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all"
                  :class="{'border-red-300 focus:ring-red-500': form.errors.start_date}"
                >
                <p v-if="form.errors.start_date" class="text-red-600 text-sm mt-2">
                  {{ form.errors.start_date }}
                </p>
              </div>

              <!-- Bitiş Tarihi -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Bitiş Tarihi <span class="text-red-500">*</span>
                </label>
                <input
                  type="date"
                  v-model="form.end_date"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all"
                  :class="{'border-red-300 focus:ring-red-500': form.errors.end_date}"
                >
                <p v-if="form.errors.end_date" class="text-red-600 text-sm mt-2">
                  {{ form.errors.end_date }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- İzin Gün Hesaplama -->
        <div v-if="leaveDaysCalculation.totalDays > 0" class="bg-gradient-to-br from-purple-50 to-indigo-50 shadow-sm rounded-xl border border-purple-200 overflow-hidden">
          <div class="px-6 py-4 border-b border-purple-200 bg-white/50">
            <h3 class="text-lg font-medium text-gray-900">İzin Gün Hesaplama</h3>
          </div>
          <div class="p-6">
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
              <!-- Toplam Gün -->
              <div class="bg-white rounded-lg p-4 border border-gray-200">
                <div class="flex items-center space-x-3">
                  <div class="flex-shrink-0 w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                    </svg>
                  </div>
                  <div>
                    <div class="text-2xl font-bold text-gray-900">{{ leaveDaysCalculation.totalDays }}</div>
                    <div class="text-xs text-gray-600">Toplam Gün</div>
                  </div>
                </div>
              </div>

              <!-- Hafta Sonu -->
              <div class="bg-white rounded-lg p-4 border border-gray-200">
                <div class="flex items-center space-x-3">
                  <div class="flex-shrink-0 w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-orange-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" />
                    </svg>
                  </div>
                  <div>
                    <div class="text-2xl font-bold text-gray-900">{{ leaveDaysCalculation.weekendDays }}</div>
                    <div class="text-xs text-gray-600">Hafta Sonu</div>
                  </div>
                </div>
              </div>

              <!-- Resmi Tatil -->
              <div class="bg-white rounded-lg p-4 border border-gray-200">
                <div class="flex items-center space-x-3">
                  <div class="flex-shrink-0 w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M12 8.25v-1.5m0 1.5c-1.355 0-2.697.056-4.024.166C6.845 8.51 6 9.473 6 10.608v2.513m6-4.87c1.355 0 2.697.055 4.024.165C17.155 8.51 18 9.473 18 10.608v2.513m-3-4.87v-1.5m-6 1.5v-1.5m12 9.75l-1.5.75a3.354 3.354 0 01-3 0 3.354 3.354 0 00-3 0 3.354 3.354 0 01-3 0 3.354 3.354 0 00-3 0 3.354 3.354 0 01-3 0L3 16.5m15-3.38a48.474 48.474 0 00-6-.37c-2.032 0-4.034.125-6 .37m12 0c.39.049.777.102 1.163.16 1.07.16 1.837 1.094 1.837 2.175v5.17c0 .62-.504 1.124-1.125 1.124H4.125A1.125 1.125 0 013 20.625v-5.17c0-1.08.768-2.014 1.837-2.174A47.78 47.78 0 016 13.12M12.265 3.11a.375.375 0 11-.53 0L12 2.845l.265.265zm-3 0a.375.375 0 11-.53 0L9 2.845l.265.265zm6 0a.375.375 0 11-.53 0L15 2.845l.265.265z" />
                    </svg>
                  </div>
                  <div>
                    <div class="text-2xl font-bold text-gray-900">{{ leaveDaysCalculation.holidayDays + (leaveDaysCalculation.halfDayHolidays * 0.5) }}</div>
                    <div class="text-xs text-gray-600">Resmi Tatil</div>
                  </div>
                </div>
              </div>

              <!-- Kullanılacak İzin -->
              <div class="bg-gradient-to-br from-purple-600 to-indigo-600 rounded-lg p-4 shadow-lg">
                <div class="flex items-center space-x-3">
                  <div class="flex-shrink-0 w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center backdrop-blur-sm">
                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                  </div>
                  <div>
                    <div class="text-2xl font-bold text-white">{{ leaveDaysCalculation.workingDays }}</div>
                    <div class="text-xs text-purple-100">Kullanılacak İzin</div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Formül Açıklaması -->
            <div class="bg-white rounded-lg p-4 border border-purple-200">
              <div class="flex items-start space-x-2 text-sm text-gray-600">
                <svg class="w-5 h-5 text-purple-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 18v-5.25m0 0a6.01 6.01 0 001.5-.189m-1.5.189a6.01 6.01 0 01-1.5-.189m3.75 7.478a12.06 12.06 0 01-4.5 0m3.75 2.383a14.406 14.406 0 01-3 0M14.25 18v-.192c0-.983.658-1.823 1.508-2.316a7.5 7.5 0 10-7.517 0c.85.493 1.509 1.333 1.509 2.316V18" />
                </svg>
                <div>
                  <span class="font-medium">Hesaplama:</span>
                  {{ leaveDaysCalculation.totalDays }} gün - {{ leaveDaysCalculation.weekendDays }} hafta sonu - {{ leaveDaysCalculation.holidayDays }} resmi tatil
                  <span v-if="leaveDaysCalculation.halfDayHolidays > 0"> - {{ leaveDaysCalculation.halfDayHolidays }} × 0.5 arefe günü</span> =
                  <span class="font-bold text-purple-600">{{ leaveDaysCalculation.workingDays }} iş günü izin kullanılacak</span>
                </div>
              </div>
            </div>

            <!-- Resmi Tatil Detayları -->
            <div v-if="leaveDaysCalculation.holidays.length > 0" class="mt-4 bg-white rounded-lg p-4 border border-red-200">
              <div class="flex items-start space-x-2">
                <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 8.25v-1.5m0 1.5c-1.355 0-2.697.056-4.024.166C6.845 8.51 6 9.473 6 10.608v2.513m6-4.87c1.355 0 2.697.055 4.024.165C17.155 8.51 18 9.473 18 10.608v2.513m-3-4.87v-1.5m-6 1.5v-1.5m12 9.75l-1.5.75a3.354 3.354 0 01-3 0 3.354 3.354 0 00-3 0 3.354 3.354 0 01-3 0 3.354 3.354 0 00-3 0 3.354 3.354 0 01-3 0L3 16.5m15-3.38a48.474 48.474 0 00-6-.37c-2.032 0-4.034.125-6 .37m12 0c.39.049.777.102 1.163.16 1.07.16 1.837 1.094 1.837 2.175v5.17c0 .62-.504 1.124-1.125 1.124H4.125A1.125 1.125 0 013 20.625v-5.17c0-1.08.768-2.014 1.837-2.174A47.78 47.78 0 016 13.12M12.265 3.11a.375.375 0 11-.53 0L12 2.845l.265.265zm-3 0a.375.375 0 11-.53 0L9 2.845l.265.265zm6 0a.375.375 0 11-.53 0L15 2.845l.265.265z" />
                </svg>
                <div class="flex-1">
                  <div class="text-sm font-medium text-gray-900 mb-2">Bu tarihler arasında resmi tatiller:</div>
                  <div class="space-y-1">
                    <div v-for="holiday in leaveDaysCalculation.holidays" :key="holiday.id" class="text-sm text-gray-700">
                      <span class="font-medium">{{ formatDate(holiday.date) }}</span> - {{ holiday.name }}
                      <span v-if="holiday.is_half_day" class="text-xs text-orange-600 ml-2">(Yarım gün tatil)</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Açıklama -->
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-medium text-gray-900">Açıklama</h3>
          </div>
          <div class="p-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                İzin Sebebi
              </label>
              <textarea
                v-model="form.reason"
                rows="4"
                class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all"
                :class="{'border-red-300 focus:ring-red-500': form.errors.reason}"
                placeholder="İzin sebebinizi belirtiniz..."
              ></textarea>
              <p v-if="form.errors.reason" class="text-red-600 text-sm mt-2">
                {{ form.errors.reason }}
              </p>
            </div>
          </div>
        </div>

        <!-- Bilgilendirme -->
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-6">
          <div class="flex">
            <svg class="h-5 w-5 text-blue-400 mr-3 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
            </svg>
            <div class="text-sm text-blue-700">
              <p class="font-medium mb-2">Bilgilendirme:</p>
              <ul class="list-disc list-inside space-y-1">
                <li>İzin talebiniz oluşturulduktan sonra onay sürecine girecektir</li>
                <li>Talebin durumunu "İzin Talepleri" sayfasından takip edebilirsiniz</li>
                <li>Onaylanmamış taleplerinizi düzenleyebilir veya iptal edebilirsiniz</li>
              </ul>
            </div>
          </div>
        </div>

        <!-- Form Actions -->
        <div class="flex justify-end gap-3">
          <Link
            :href="route('leave-requests.index')"
            class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors font-medium"
          >
            İptal
          </Link>
          <button
            type="submit"
            :disabled="form.processing"
            class="px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors font-medium shadow-sm inline-flex items-center"
          >
            <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            {{ form.processing ? 'Kaydediliyor...' : 'Talebi Oluştur' }}
          </button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>

<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import axios from 'axios';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    employees: Array,
    substitute_employees: Array,
    leave_types: Array,
    defaults: Object,
});

const form = useForm({
    employee_id: props.defaults?.employee_id || '',
    leave_type_id: '',
    start_date: '',
    end_date: '',
    reason: '',
    substitute_employee_id: '',
});

const holidays = ref([]);
const loadingHolidays = ref(false);
const projectWeekendDays = ref(['saturday', 'sunday']); // Default
const projectName = ref(null);

// İzin gün hesaplama
const leaveDaysCalculation = computed(() => {
    if (!form.start_date || !form.end_date) {
        return {
            totalDays: 0,
            weekendDays: 0,
            holidayDays: 0,
            halfDayHolidays: 0,
            workingDays: 0,
            holidays: [],
        };
    }

    const start = new Date(form.start_date);
    const end = new Date(form.end_date);

    // Tarih kontrolü
    if (start > end) {
        return {
            totalDays: 0,
            weekendDays: 0,
            holidayDays: 0,
            halfDayHolidays: 0,
            workingDays: 0,
            holidays: [],
        };
    }

    // Toplam gün sayısı (başlangıç ve bitiş dahil)
    const totalDays = Math.ceil((end - start) / (1000 * 60 * 60 * 24)) + 1;

    // Projeye göre hafta sonu günlerini belirle
    const weekendDayNumbers = projectWeekendDays.value.map(day => {
        const dayMap = {
            'monday': 1,
            'tuesday': 2,
            'wednesday': 3,
            'thursday': 4,
            'friday': 5,
            'saturday': 6,
            'sunday': 0,
        };
        return dayMap[day];
    });

    // Hafta sonu sayısı (projeye göre)
    let weekendDays = 0;
    let current = new Date(start);

    while (current <= end) {
        const dayOfWeek = current.getDay();
        if (weekendDayNumbers.includes(dayOfWeek)) {
            weekendDays++;
        }
        current.setDate(current.getDate() + 1);
    }

    // Resmi tatil günlerini filtrele (hafta içi olanlar)
    const holidaysInRange = holidays.value.filter(holiday => {
        const holidayDate = new Date(holiday.date);
        const dayOfWeek = holidayDate.getDay();
        // Sadece hafta içi tatilleri say (hafta sonuna denk gelen tatiller zaten sayılmış)
        return holidayDate >= start && holidayDate <= end && !weekendDayNumbers.includes(dayOfWeek);
    });

    // Tam gün ve yarım gün tatilleri ayır
    const fullDayHolidays = holidaysInRange.filter(h => !h.is_half_day);
    const halfDayHolidays = holidaysInRange.filter(h => h.is_half_day);

    const holidayDays = fullDayHolidays.length;
    const halfDayHolidaysCount = halfDayHolidays.length;

    // Çalışma günü = Toplam - Hafta Sonu - Tam Gün Tatil - (Yarım Gün Tatil / 2)
    const workingDays = totalDays - weekendDays - holidayDays - (halfDayHolidaysCount * 0.5);

    return {
        totalDays,
        weekendDays,
        holidayDays,
        halfDayHolidays: halfDayHolidaysCount,
        workingDays: Math.max(0, workingDays), // Negatif olmasın
        holidays: holidaysInRange,
    };
});

// Çalışan seçildiğinde proje ayarlarını yükle
watch(() => form.employee_id, async (newEmployeeId) => {
    if (newEmployeeId) {
        try {
            const response = await axios.get('/leave-requests/api/employee-project-settings', {
                params: {
                    employee_id: newEmployeeId,
                },
            });
            projectWeekendDays.value = response.data.weekend_days || ['saturday', 'sunday'];
            projectName.value = response.data.project_name;
        } catch (error) {
            console.error('Proje ayarları yüklenirken hata oluştu:', error);
            projectWeekendDays.value = ['saturday', 'sunday']; // Default
            projectName.value = null;
        }
    }
});

// Tarihleri izle ve tatilleri yükle
watch([() => form.start_date, () => form.end_date], async ([newStart, newEnd]) => {
    if (newStart && newEnd) {
        // Tarih kontrolü: Başlangıç tarihi bitiş tarihinden büyük olamaz
        const start = new Date(newStart);
        const end = new Date(newEnd);

        if (start > end) {
            form.errors.end_date = 'Bitiş tarihi, başlangıç tarihinden küçük olamaz.';
            holidays.value = [];
            return;
        } else {
            // Hata varsa temizle
            if (form.errors.end_date === 'Bitiş tarihi, başlangıç tarihinden küçük olamaz.') {
                form.errors.end_date = null;
            }
        }

        loadingHolidays.value = true;
        try {
            const response = await axios.get('/api/holidays/range', {
                params: {
                    start_date: newStart,
                    end_date: newEnd,
                },
            });
            holidays.value = response.data.holidays || [];
        } catch (error) {
            console.error('Tatiller yüklenirken hata oluştu:', error);
            holidays.value = [];
        } finally {
            loadingHolidays.value = false;
        }
    }
});

// Tarih formatlama
const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('tr-TR', { day: '2-digit', month: '2-digit', year: 'numeric' });
};

const submitForm = () => {
    form.post(route('leave-requests.store'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
        },
    });
};
</script>