<template>
  <AppLayout title="İzin Yönetimi" :full-width="true">
    <!-- Full-width purple gradient header -->
    <template #fullWidthHeader>
      <div class="bg-gradient-to-r from-purple-600 via-purple-700 to-purple-800 border-b border-purple-900/20 w-full">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
          <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <!-- Header Info -->
            <div class="flex-1 min-w-0">
              <div class="flex items-center space-x-4 mb-3">
                <div class="flex-shrink-0 w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm">
                  <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.343 3.94c.09-.542.56-.94 1.11-.94h1.093c.55 0 1.02.398 1.11.94l.149.894c.07.424.384.764.78.93.398.164.855.142 1.205-.108l.737-.527a1.125 1.125 0 0 1 1.45.12l.773.774c.39.389.44 1.002.12 1.45l-.527.737c-.25.35-.272.806-.107 1.204.165.397.505.71.93.78l.893.15c.543.09.94.559.94 1.109v1.094c0 .55-.397 1.02-.94 1.11l-.894.149c-.424.07-.764.383-.929.78-.165.398-.143.854.107 1.204l.527.738c.32.447.269 1.06-.12 1.45l-.774.773a1.125 1.125 0 0 1-1.449.12l-.738-.527c-.35-.25-.806-.272-1.203-.107-.398.165-.71.505-.781.929l-.149.894c-.09.542-.56.94-1.11.94h-1.094c-.55 0-1.019-.398-1.11-.94l-.148-.894c-.071-.424-.384-.764-.781-.93-.398-.164-.854-.142-1.204.108l-.738.527c-.447.32-1.06.269-1.45-.12l-.773-.774a1.125 1.125 0 0 1-.12-1.45l.527-.737c.25-.35.272-.806.108-1.204-.165-.397-.506-.71-.93-.78l-.894-.15c-.542-.09-.94-.56-.94-1.109v-1.094c0-.55.398-1.02.94-1.11l.894-.149c.424-.07.765-.383.93-.78.165-.398.143-.854-.108-1.204l-.526-.738a1.125 1.125 0 0 1 .12-1.45l.773-.773a1.125 1.125 0 0 1 1.45-.12l.737.527c.35.25.807.272 1.204.107.397-.165.71-.505.78-.929l.15-.894Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                  </svg>
                </div>
                <div>
                  <h1 class="text-2xl lg:text-3xl font-bold text-white">İzin Yönetimi</h1>
                  <p class="text-purple-100 text-sm mt-1">İzin parametreleri ve resmi tatilleri yönetin</p>
                </div>
              </div>

              <!-- Stats Row -->
              <div class="flex flex-wrap items-center gap-4 lg:gap-6">
                <div class="bg-white/10 backdrop-blur-sm rounded-lg px-3 py-2">
                  <span class="text-purple-100 text-sm">Parametreler:</span>
                  <span class="font-semibold text-white ml-1">{{ stats?.total || 0 }}</span>
                </div>
                <div class="bg-white/10 backdrop-blur-sm rounded-lg px-3 py-2">
                  <span class="text-purple-100 text-sm">Resmi Tatiller:</span>
                  <span class="font-semibold text-white ml-1">{{ holidays?.length || 0 }}</span>
                </div>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex-shrink-0 flex gap-3">
              <Link
                v-if="activeTab === 'parameters'"
                :href="route('leave-management.parameters.create')"
                class="inline-flex items-center px-4 py-2 bg-white text-purple-600 text-sm font-medium rounded-lg hover:bg-purple-50 shadow-lg hover:shadow-xl transition-all duration-200"
              >
                <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Yeni Parametre
              </Link>
              <button
                v-if="activeTab === 'holidays'"
                @click="showAddHolidayModal = true"
                class="inline-flex items-center px-4 py-2 bg-white text-purple-600 text-sm font-medium rounded-lg hover:bg-purple-50 shadow-lg hover:shadow-xl transition-all duration-200"
              >
                <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Yeni Tatil Ekle
              </button>
            </div>
          </div>
        </div>
      </div>
    </template>

    <!-- Tabs -->
    <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
      <div class="border-b border-gray-200 mb-6">
        <nav class="-mb-px flex space-x-8">
          <button
            @click="activeTab = 'parameters'"
            :class="[
              activeTab === 'parameters'
                ? 'border-purple-500 text-purple-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
              'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors'
            ]"
          >
            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
            </svg>
            İzin Parametreleri
          </button>
          <button
            @click="activeTab = 'holidays'"
            :class="[
              activeTab === 'holidays'
                ? 'border-purple-500 text-purple-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
              'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors'
            ]"
          >
            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
            Resmi Tatiller
          </button>
        </nav>
      </div>

      <!-- Parameters Tab Content -->
      <div v-show="activeTab === 'parameters'">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6 p-6">
          <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Arama</label>
              <input
                v-model="filters.search"
                type="text"
                placeholder="Parametre adı..."
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                @input="search"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Kategori</label>
              <select
                v-model="filters.category"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500"
                @change="search"
              >
                <option value="">Tüm Kategoriler</option>
                <option value="annual_leave">Yıllık İzin</option>
                <option value="sick_leave">Hastalık İzni</option>
                <option value="calculation">Hesaplama</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Durum</label>
              <select
                v-model="filters.status"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500"
                @change="search"
              >
                <option value="">Tüm Durumlar</option>
                <option value="active">Aktif</option>
                <option value="inactive">Pasif</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Parameters Table -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Parametre</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kategori</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Değer</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Durum</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">İşlemler</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="param in parameters.data" :key="param.id" class="hover:bg-gray-50">
                <td class="px-6 py-4">
                  <div class="text-sm font-medium text-gray-900">{{ param.name }}</div>
                  <div class="text-sm text-gray-500">{{ param.parameter_key }}</div>
                </td>
                <td class="px-6 py-4">
                  <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">
                    {{ param.category }}
                  </span>
                </td>
                <td class="px-6 py-4 text-sm text-gray-900">{{ param.default_value || '-' }}</td>
                <td class="px-6 py-4">
                  <span :class="[
                    'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                    param.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'
                  ]">
                    {{ param.status === 'active' ? 'Aktif' : 'Pasif' }}
                  </span>
                </td>
                <td class="px-6 py-4 text-sm">
                  <Link :href="route('leave-management.parameters.edit', param.id)" class="text-purple-600 hover:text-purple-900">
                    Düzenle
                  </Link>
                </td>
              </tr>
            </tbody>
          </table>
          <div class="bg-white px-4 py-3 border-t">
            <Pagination :links="parameters.links" />
          </div>
        </div>
      </div>

      <!-- Holidays Tab Content -->
      <div v-show="activeTab === 'holidays'">
        <!-- Year Filter -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6 p-6">
          <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
              <label class="text-sm font-medium text-gray-700">Yıl Seçin:</label>
              <select
                v-model="selectedYear"
                @change="loadHolidaysForYear"
                class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
              >
                <option v-for="year in availableYears" :key="year" :value="year">{{ year }}</option>
              </select>
            </div>
            <div class="text-sm text-gray-600">
              <span class="font-semibold">{{ filteredHolidays.length }}</span> resmi tatil bulundu
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tarih</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tatil Adı</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tür</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Durum</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">İşlemler</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="holiday in filteredHolidays" :key="holiday.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-gray-900">{{ formatDate(holiday.date) }}</div>
                  <div class="text-xs text-gray-500">{{ getDayName(holiday.date) }}</div>
                </td>
                <td class="px-6 py-4">
                  <div class="text-sm font-medium text-gray-900">{{ holiday.name }}</div>
                  <div v-if="holiday.is_half_day" class="text-xs text-orange-600 font-medium">
                    Yarım Gün ({{ holiday.half_day_start || '13:00' }}'den sonra)
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="[
                    'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                    holiday.type === 'national' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'
                  ]">
                    {{ holiday.type === 'national' ? 'Ulusal Bayram' : 'Dini Bayram' }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="[
                    'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                    holiday.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'
                  ]">
                    {{ holiday.is_active ? 'Aktif' : 'Pasif' }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                  <button @click="editHoliday(holiday)" class="text-purple-600 hover:text-purple-900 mr-3">
                    Düzenle
                  </button>
                  <button @click="deleteHoliday(holiday)" class="text-red-600 hover:text-red-900">
                    Sil
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Add/Edit Holiday Modal -->
    <div v-if="showAddHolidayModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <!-- Modal Header -->
        <div class="bg-purple-600 px-6 py-4 rounded-t-lg">
          <div class="flex items-center justify-between">
            <h3 class="text-xl font-semibold text-white">
              {{ editingHoliday ? 'Resmi Tatil Düzenle' : 'Yeni Resmi Tatil Ekle' }}
            </h3>
            <button @click="closeModal" class="text-white hover:text-purple-200">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>
        </div>

        <!-- Modal Body -->
        <form @submit.prevent="saveHoliday" class="p-6 space-y-6">
          <!-- Tatil Adı -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Tatil Adı <span class="text-red-500">*</span>
            </label>
            <input
              v-model="holidayForm.name"
              type="text"
              required
              placeholder="Örn: Ramazan Bayramı 1. Gün"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
            />
          </div>

          <!-- Tarih -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Tarih <span class="text-red-500">*</span>
            </label>
            <input
              v-model="holidayForm.date"
              type="date"
              required
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
            />
          </div>

          <!-- Tatil Türü -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Tatil Türü <span class="text-red-500">*</span>
            </label>
            <select
              v-model="holidayForm.type"
              required
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
            >
              <option value="national">Ulusal Bayram</option>
              <option value="religious">Dini Bayram</option>
              <option value="other">Diğer</option>
            </select>
          </div>

          <!-- Yarım Gün Tatil (Arefe) -->
          <div class="bg-orange-50 border border-orange-200 rounded-lg p-4">
            <div class="flex items-start">
              <input
                v-model="holidayForm.is_half_day"
                type="checkbox"
                id="is_half_day"
                class="mt-1 h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded"
              />
              <label for="is_half_day" class="ml-3 flex-1">
                <span class="block text-sm font-medium text-gray-900">Yarım Gün Tatil (Arefe Günü)</span>
                <span class="block text-xs text-gray-600 mt-1">
                  Ramazan ve Kurban Bayramı arefesi gibi yarım gün tatil günleri için işaretleyin
                </span>
              </label>
            </div>

            <!-- Yarım Gün Başlangıç Saati -->
            <div v-if="holidayForm.is_half_day" class="mt-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Tatil Başlangıç Saati
              </label>
              <input
                v-model="holidayForm.half_day_start"
                type="time"
                placeholder="13:00"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
              />
              <p class="mt-1 text-xs text-gray-500">
                Bu saatten sonra tatil başlar (genellikle 13:00)
              </p>
            </div>
          </div>

          <!-- Ücretli Tatil -->
          <div class="flex items-center">
            <input
              v-model="holidayForm.is_paid"
              type="checkbox"
              id="is_paid"
              class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded"
            />
            <label for="is_paid" class="ml-3 block text-sm font-medium text-gray-700">
              Ücretli Tatil
            </label>
          </div>

          <!-- Aktif -->
          <div class="flex items-center">
            <input
              v-model="holidayForm.is_active"
              type="checkbox"
              id="is_active"
              class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded"
            />
            <label for="is_active" class="ml-3 block text-sm font-medium text-gray-700">
              Aktif
            </label>
          </div>

          <!-- Açıklama -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Açıklama (Opsiyonel)</label>
            <textarea
              v-model="holidayForm.description"
              rows="3"
              placeholder="Tatil hakkında ek bilgi..."
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
            ></textarea>
          </div>

          <!-- Modal Footer -->
          <div class="flex justify-end space-x-3 pt-4 border-t">
            <button
              type="button"
              @click="closeModal"
              class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium"
            >
              İptal
            </button>
            <button
              type="submit"
              :disabled="saving"
              class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 font-medium disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {{ saving ? 'Kaydediliyor...' : (editingHoliday ? 'Güncelle' : 'Kaydet') }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Pagination from '@/Components/UI/Pagination.vue'
import { debounce } from 'lodash'

const props = defineProps({
  parameters: Object,
  filters: Object,
  stats: Object,
  holidays: Array,
  currentYear: Number,
})

const activeTab = ref('parameters')
const showAddHolidayModal = ref(false)
const editingHoliday = ref(null)
const saving = ref(false)
const selectedYear = ref(props.currentYear)

// Yıl seçenekleri (2020-2030 arası)
const availableYears = ref([])
for (let year = 2020; year <= 2030; year++) {
  availableYears.value.push(year)
}

const filters = reactive({
  search: props.filters?.search || '',
  category: props.filters?.category || '',
  status: props.filters?.status || '',
})

const holidayForm = reactive({
  name: '',
  date: '',
  type: 'national',
  is_half_day: false,
  half_day_start: '13:00',
  description: '',
  is_paid: true,
  is_active: true,
})

// Seçili yıla göre tatilleri filtrele
const filteredHolidays = ref(props.holidays || [])

const loadHolidaysForYear = async () => {
  try {
    const response = await window.axios.get(route('api.holidays.index'), {
      params: { year: selectedYear.value }
    })
    filteredHolidays.value = response.data.holidays
  } catch (error) {
    console.error('Tatil yükleme hatası:', error)
    filteredHolidays.value = []
  }
}

const search = debounce(() => {
  router.get(route('leave-management.parameters.index'), filters, {
    preserveState: true,
    replace: true,
  })
}, 300)

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('tr-TR', { day: '2-digit', month: '2-digit', year: 'numeric' })
}

const getDayName = (date) => {
  return new Date(date).toLocaleDateString('tr-TR', { weekday: 'long' })
}

const resetForm = () => {
  holidayForm.name = ''
  holidayForm.date = ''
  holidayForm.type = 'national'
  holidayForm.is_half_day = false
  holidayForm.half_day_start = '13:00'
  holidayForm.description = ''
  holidayForm.is_paid = true
  holidayForm.is_active = true
  editingHoliday.value = null
}

const closeModal = () => {
  showAddHolidayModal.value = false
  resetForm()
}

const editHoliday = (holiday) => {
  editingHoliday.value = holiday
  holidayForm.name = holiday.name

  // Tarihi YYYY-MM-DD formatına çevir (input type="date" için gerekli)
  const date = new Date(holiday.date)
  const year = date.getFullYear()
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const day = String(date.getDate()).padStart(2, '0')
  holidayForm.date = `${year}-${month}-${day}`

  holidayForm.type = holiday.type
  holidayForm.is_half_day = holiday.is_half_day
  holidayForm.half_day_start = holiday.half_day_start || '13:00'
  holidayForm.description = holiday.description || ''
  holidayForm.is_paid = holiday.is_paid
  holidayForm.is_active = holiday.is_active
  showAddHolidayModal.value = true
}

const saveHoliday = async () => {
  saving.value = true

  const data = {
    name: holidayForm.name,
    date: holidayForm.date,
    type: holidayForm.type,
    is_half_day: holidayForm.is_half_day,
    half_day_start: holidayForm.is_half_day ? holidayForm.half_day_start : null,
    description: holidayForm.description,
    is_paid: holidayForm.is_paid,
    is_active: holidayForm.is_active,
  }

  try {
    if (editingHoliday.value) {
      // Güncelleme
      await window.axios.put(route('api.holidays.update', editingHoliday.value.id), data)
      alert('Resmi tatil başarıyla güncellendi!')
    } else {
      // Yeni ekleme
      await window.axios.post(route('api.holidays.store'), data)
      alert('Resmi tatil başarıyla eklendi!')
    }

    closeModal()
    // Yıl seçimine göre listeyi yenile
    await loadHolidaysForYear()
  } catch (error) {
    console.error('Tatil kaydetme hatası:', error)
    alert('Hata: ' + (error.response?.data?.message || error.message))
  } finally {
    saving.value = false
  }
}

const deleteHoliday = async (holiday) => {
  if (confirm(`${holiday.name} tatilini silmek istediğinizden emin misiniz?`)) {
    try {
      await window.axios.delete(route('api.holidays.destroy', holiday.id))
      alert('Resmi tatil silindi.')
      await loadHolidaysForYear()
    } catch (error) {
      console.error('Tatil silme hatası:', error)
      alert('Hata: ' + (error.response?.data?.message || error.message))
    }
  }
}
</script>
