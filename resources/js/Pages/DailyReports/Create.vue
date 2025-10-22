<template>
  <AppLayout
    title="Yeni Günlük Rapor - SPT İnşaat Puantaj Sistemi"
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
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                  </svg>
                </div>
                <div>
                  <h1 class="text-2xl lg:text-3xl font-bold text-white">Yeni Günlük Rapor</h1>
                  <p class="text-green-100 text-sm mt-1">Günlük şantiye raporunu oluşturun</p>
                </div>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex-shrink-0">
              <Link
                :href="route('daily-reports.index')"
                class="inline-flex items-center px-4 py-2 bg-white/10 backdrop-blur-sm border border-white/30 text-white text-sm font-medium rounded-lg hover:bg-white/20 transition-all duration-200"
              >
                <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>
                Geri Dön
              </Link>
            </div>
          </div>
        </div>

        <!-- Breadcrumb -->
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
                  <Link :href="route('daily-reports.index')" class="text-xs font-medium text-green-100 hover:text-white">
                    Günlük Raporlar
                  </Link>
                </li>
                <li class="flex items-center">
                  <svg class="h-3 w-3 text-green-200 mx-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                  </svg>
                  <span class="text-xs font-medium text-white">Yeni Rapor</span>
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
              <!-- Proje -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Proje <span class="text-red-500">*</span>
                </label>
                <select
                  v-model="form.project_id"
                  class="block w-full px-4 py-3 text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors"
                  required
                >
                  <option value="">Proje Seçin</option>
                  <option v-for="project in projects" :key="project.id" :value="project.id">
                    {{ project.name }}
                  </option>
                </select>
                <p v-if="form.errors.project_id" class="mt-2 text-sm text-red-600">{{ form.errors.project_id }}</p>
              </div>

              <!-- Rapor Tarihi -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Rapor Tarihi <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="form.report_date"
                  type="date"
                  :max="today"
                  class="block w-full px-4 py-3 text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors"
                  required
                />
                <p v-if="form.errors.report_date" class="mt-2 text-sm text-red-600">{{ form.errors.report_date }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Hava Durumu -->
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-medium text-gray-900">Hava Durumu</h3>
          </div>

          <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
              <!-- Hava Durumu -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Durum <span class="text-red-500">*</span>
                </label>
                <select
                  v-model="form.weather_condition"
                  class="block w-full px-4 py-3 text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors"
                  required
                >
                  <option value="sunny">Güneşli</option>
                  <option value="cloudy">Bulutlu</option>
                  <option value="rainy">Yağmurlu</option>
                  <option value="snowy">Karlı</option>
                  <option value="windy">Rüzgarlı</option>
                  <option value="stormy">Fırtınalı</option>
                </select>
              </div>

              <!-- Sıcaklık -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Sıcaklık (°C)
                </label>
                <input
                  v-model="form.temperature"
                  type="number"
                  step="0.1"
                  min="-50"
                  max="60"
                  class="block w-full px-4 py-3 text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors"
                  placeholder="25"
                />
              </div>

              <!-- Hava Notları -->
              <div class="lg:col-span-1">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Notlar
                </label>
                <input
                  v-model="form.weather_notes"
                  type="text"
                  class="block w-full px-4 py-3 text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors"
                  placeholder="Sabah yağmurlu, öğleden sonra açtı..."
                />
              </div>
            </div>
          </div>
        </div>

        <!-- İşçi Bilgileri -->
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-medium text-gray-900">İşçi Bilgileri</h3>
          </div>

          <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
              <!-- Toplam İşçi -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Toplam İşçi <span class="text-red-500">*</span>
                </label>
                <input
                  v-model.number="form.total_workers"
                  type="number"
                  min="0"
                  class="block w-full px-4 py-3 text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors"
                  required
                  @input="calculateTotal"
                />
              </div>

              <!-- İç İşçi -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  İç İşçi <span class="text-red-500">*</span>
                </label>
                <input
                  v-model.number="form.internal_workers"
                  type="number"
                  min="0"
                  class="block w-full px-4 py-3 text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors"
                  required
                />
              </div>

              <!-- Taşeron İşçi -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Taşeron İşçi <span class="text-red-500">*</span>
                </label>
                <input
                  v-model.number="form.subcontractor_workers"
                  type="number"
                  min="0"
                  class="block w-full px-4 py-3 text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors"
                  required
                />
              </div>
            </div>
          </div>
        </div>

        <!-- İş Özeti -->
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-medium text-gray-900">İş Özeti</h3>
          </div>

          <div class="p-6 space-y-6">
            <!-- İş Özeti -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Genel Özet <span class="text-red-500">*</span>
              </label>
              <textarea
                v-model="form.work_summary"
                rows="4"
                class="block w-full px-4 py-3 text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors resize-none"
                placeholder="Bugün yapılan işler hakkında genel özet..."
                required
              ></textarea>
            </div>

            <!-- Tamamlanan İşler -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Tamamlanan İşler
              </label>
              <textarea
                v-model="completedWorksText"
                rows="3"
                class="block w-full px-4 py-3 text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors resize-none"
                placeholder="Her satıra bir iş yazın..."
              ></textarea>
              <p class="mt-1 text-sm text-gray-500">Her satıra bir iş yazın</p>
            </div>

            <!-- Devam Eden İşler -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Devam Eden İşler
              </label>
              <textarea
                v-model="ongoingWorksText"
                rows="3"
                class="block w-full px-4 py-3 text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors resize-none"
                placeholder="Her satıra bir iş yazın..."
              ></textarea>
              <p class="mt-1 text-sm text-gray-500">Her satıra bir iş yazın</p>
            </div>

            <!-- Planlanan İşler -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Planlanan İşler
              </label>
              <textarea
                v-model="plannedWorksText"
                rows="3"
                class="block w-full px-4 py-3 text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors resize-none"
                placeholder="Her satıra bir iş yazın..."
              ></textarea>
              <p class="mt-1 text-sm text-gray-500">Her satıra bir iş yazın</p>
            </div>
          </div>
        </div>

        <!-- Sorunlar ve Gecikmeler -->
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-medium text-gray-900">Sorunlar ve Gecikmeler</h3>
          </div>

          <div class="p-6 space-y-6">
            <!-- Gecikme Var Mı -->
            <div>
              <label class="flex items-center">
                <input
                  v-model="form.has_delays"
                  type="checkbox"
                  class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded"
                />
                <span class="ml-2 text-sm font-medium text-gray-700">Gecikme var</span>
              </label>
            </div>

            <!-- Gecikme Nedenleri -->
            <div v-if="form.has_delays">
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Gecikme Nedenleri
              </label>
              <textarea
                v-model="delayReasonsText"
                rows="3"
                class="block w-full px-4 py-3 text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors resize-none"
                placeholder="Her satıra bir gecikme nedeni yazın..."
              ></textarea>
            </div>

            <!-- Kaza Var Mı -->
            <div>
              <label class="flex items-center">
                <input
                  v-model="form.has_accidents"
                  type="checkbox"
                  class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded"
                />
                <span class="ml-2 text-sm font-medium text-gray-700">Kaza meydana geldi</span>
              </label>
            </div>

            <!-- Kaza Detayları -->
            <div v-if="form.has_accidents">
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Kaza Detayları
              </label>
              <textarea
                v-model="accidentDetailsText"
                rows="3"
                class="block w-full px-4 py-3 text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors resize-none"
                placeholder="Kaza detaylarını yazın..."
              ></textarea>
            </div>

            <!-- Malzeme Eksikliği Var Mı -->
            <div>
              <label class="flex items-center">
                <input
                  v-model="form.has_material_shortage"
                  type="checkbox"
                  class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded"
                />
                <span class="ml-2 text-sm font-medium text-gray-700">Malzeme eksikliği var</span>
              </label>
            </div>

            <!-- Malzeme Eksikliği Detayları -->
            <div v-if="form.has_material_shortage">
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Eksik Malzemeler
              </label>
              <textarea
                v-model="materialShortageText"
                rows="3"
                class="block w-full px-4 py-3 text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors resize-none"
                placeholder="Her satıra bir eksik malzeme yazın..."
              ></textarea>
            </div>
          </div>
        </div>

        <!-- Diğer Bilgiler -->
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-medium text-gray-900">Diğer Bilgiler</h3>
          </div>

          <div class="p-6 space-y-6">
            <!-- Ziyaretçiler -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Ziyaretçiler
              </label>
              <textarea
                v-model="visitorsText"
                rows="2"
                class="block w-full px-4 py-3 text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors resize-none"
                placeholder="Her satıra bir ziyaretçi yazın..."
              ></textarea>
            </div>

            <!-- Ekipman Kullanımı -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Ekipman Kullanımı
              </label>
              <div class="space-y-3">
                <div v-for="(equipment, index) in form.equipment_usage" :key="index" class="flex gap-3">
                  <input
                    v-model="equipment.name"
                    type="text"
                    placeholder="Ekipman adı"
                    class="flex-1 px-4 py-2 text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                  />
                  <input
                    v-model.number="equipment.hours"
                    type="number"
                    min="0"
                    step="0.5"
                    placeholder="Saat"
                    class="w-32 px-4 py-2 text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                  />
                  <button
                    type="button"
                    @click="removeEquipment(index)"
                    class="px-3 py-2 bg-red-100 text-red-600 rounded-lg hover:bg-red-200"
                  >
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                  </button>
                </div>
                <button
                  type="button"
                  @click="addEquipment"
                  class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200"
                >
                  <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                  </svg>
                  Ekipman Ekle
                </button>
              </div>
            </div>

            <!-- Fotoğraflar -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Fotoğraflar
              </label>
              <input
                ref="photoInput"
                type="file"
                accept="image/*"
                multiple
                @change="handlePhotoUpload"
                class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100"
              />
              <p class="mt-1 text-sm text-gray-500">En fazla 5MB, JPG, PNG formatında</p>

              <!-- Photo Previews -->
              <div v-if="photoPreviews.length > 0" class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-4">
                <div v-for="(preview, index) in photoPreviews" :key="index" class="relative">
                  <img :src="preview" alt="Preview" class="w-full h-32 object-cover rounded-lg" />
                  <button
                    type="button"
                    @click="removePhoto(index)"
                    class="absolute top-1 right-1 bg-red-500 text-white rounded-full p-1 hover:bg-red-600"
                  >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                  </button>
                </div>
              </div>
            </div>

            <!-- Notlar -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Notlar
              </label>
              <textarea
                v-model="form.notes"
                rows="4"
                class="block w-full px-4 py-3 text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors resize-none"
                placeholder="Ek notlar..."
              ></textarea>
            </div>
          </div>
        </div>

        <!-- Form Actions -->
        <div class="flex items-center justify-end space-x-4">
          <Link
            :href="route('daily-reports.index')"
            class="px-6 py-3 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition-all duration-200"
          >
            İptal
          </Link>
          <button
            type="submit"
            name="action"
            value="draft"
            @click="form.should_submit = false"
            :disabled="form.processing"
            class="px-6 py-3 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 disabled:opacity-50 transition-all duration-200"
          >
            Taslak Kaydet
          </button>
          <button
            type="submit"
            name="action"
            value="submit"
            @click="form.should_submit = true"
            :disabled="form.processing"
            class="px-6 py-3 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 disabled:opacity-50 transition-all duration-200"
          >
            {{ form.processing ? 'Kaydediliyor...' : 'Kaydet ve Gönder' }}
          </button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import { router, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

// Props
const props = defineProps({
  projects: {
    type: Array,
    default: () => []
  },
  selectedProjectId: {
    type: [Number, String],
    default: null
  }
})

// Form
const form = useForm({
  project_id: props.selectedProjectId || '',
  report_date: new Date().toISOString().split('T')[0],
  weather_condition: 'sunny',
  temperature: null,
  weather_notes: '',
  total_workers: 0,
  internal_workers: 0,
  subcontractor_workers: 0,
  work_summary: '',
  completed_works: [],
  ongoing_works: [],
  planned_works: [],
  has_delays: false,
  delay_reasons: [],
  has_accidents: false,
  accident_details: [],
  has_material_shortage: false,
  material_shortage_details: [],
  visitors: [],
  equipment_usage: [],
  photos: [],
  notes: '',
  should_submit: false
})

// Text area helpers
const completedWorksText = ref('')
const ongoingWorksText = ref('')
const plannedWorksText = ref('')
const delayReasonsText = ref('')
const accidentDetailsText = ref('')
const materialShortageText = ref('')
const visitorsText = ref('')

// Photo handling
const photoInput = ref(null)
const photoPreviews = ref([])

// Computed
const today = computed(() => new Date().toISOString().split('T')[0])

// Methods
const calculateTotal = () => {
  // Auto-calculate is manual in this simple version
}

const handlePhotoUpload = (event) => {
  const files = Array.from(event.target.files)

  files.forEach(file => {
    if (file.size > 5 * 1024 * 1024) {
      alert(`${file.name} dosyası 5MB'dan büyük!`)
      return
    }

    const reader = new FileReader()
    reader.onload = (e) => {
      photoPreviews.value.push(e.target.result)
    }
    reader.readAsDataURL(file)

    form.photos.push(file)
  })
}

const removePhoto = (index) => {
  photoPreviews.value.splice(index, 1)
  form.photos.splice(index, 1)
}

const addEquipment = () => {
  form.equipment_usage.push({ name: '', hours: 0 })
}

const removeEquipment = (index) => {
  form.equipment_usage.splice(index, 1)
}

const submitForm = () => {
  // Convert text areas to arrays
  form.completed_works = completedWorksText.value.split('\n').filter(w => w.trim())
  form.ongoing_works = ongoingWorksText.value.split('\n').filter(w => w.trim())
  form.planned_works = plannedWorksText.value.split('\n').filter(w => w.trim())
  form.delay_reasons = delayReasonsText.value.split('\n').filter(w => w.trim())
  form.accident_details = accidentDetailsText.value.split('\n').filter(w => w.trim())
  form.material_shortage_details = materialShortageText.value.split('\n').filter(w => w.trim())
  form.visitors = visitorsText.value.split('\n').filter(w => w.trim())

  // Debug: Form verisini konsola yazdır
  console.log('Form data being submitted:', {
    ...form.data(),
    photos_count: form.photos.length
  })

  // Fotoğraflar varsa veya equipment_usage varsa multipart/form-data kullan
  const options = {
    preserveScroll: true,
    forceFormData: form.photos.length > 0 || form.equipment_usage.length > 0,
    onSuccess: (page) => {
      console.log('Form submitted successfully!', page)
    },
    onError: (errors) => {
      console.error('Form validation errors:', errors)
      alert('Form hatası: ' + Object.values(errors).join(', '))
    },
    onFinish: () => {
      console.log('Form submission finished')
    }
  }

  console.log('Submitting to:', route('daily-reports.store'))
  form.post(route('daily-reports.store'), options)
}
</script>

<style scoped>
.w-full {
  width: 100% !important;
}
</style>
