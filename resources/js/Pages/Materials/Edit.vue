<template>
  <AppLayout
    title="Malzeme Düzenle - SPT İnşaat Puantaj Sistemi"
    :full-width="true"
  >
    <!-- Full-width header -->
    <template #fullWidthHeader>
      <div class="bg-gradient-to-r from-indigo-600 via-indigo-700 to-indigo-800 border-b border-indigo-900/20 w-full">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
          <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <!-- Header Info -->
            <div class="flex-1 min-w-0">
              <div class="flex items-center space-x-4">
                <div class="flex-shrink-0 w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm">
                  <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                  </svg>
                </div>
                <div>
                  <h1 class="text-2xl lg:text-3xl font-bold text-white">Malzeme Düzenle</h1>
                  <p class="text-indigo-100 text-sm mt-1">{{ material.name }}</p>
                </div>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex-shrink-0 flex items-center space-x-3">
              <Link
                :href="route('materials.index')"
                class="inline-flex items-center px-4 py-2 bg-white/10 text-white text-sm font-medium rounded-lg hover:bg-white/20 backdrop-blur-sm transition-all duration-200"
              >
                <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                Liste
              </Link>
            </div>
          </div>
        </div>

        <!-- Breadcrumb inside header -->
        <div class="bg-black/10 border-t border-white/10">
          <div class="w-full px-4 sm:px-6 lg:px-8">
            <div class="flex items-center space-x-2 py-2">
              <ol class="flex items-center space-x-2">
                <li>
                  <Link :href="route('dashboard')" class="text-indigo-100 hover:text-white transition-colors">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                    </svg>
                    <span class="sr-only">Ana Sayfa</span>
                  </Link>
                </li>
                <li class="flex items-center">
                  <svg class="h-3 w-3 text-indigo-200 mx-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                  </svg>
                  <Link :href="route('materials.index')" class="text-indigo-100 hover:text-white text-xs transition-colors">
                    Malzemeler
                  </Link>
                </li>
                <li class="flex items-center">
                  <svg class="h-3 w-3 text-indigo-200 mx-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                  </svg>
                  <span class="text-xs font-medium text-white">Düzenle</span>
                </li>
              </ol>
            </div>
          </div>
        </div>
      </div>
    </template>

    <!-- Main Content -->
    <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
      <div class="max-w-4xl mx-auto">
        <form @submit.prevent="submitForm" class="space-y-6">
          <!-- Basic Information Card -->
          <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
              <h3 class="text-lg font-medium text-gray-900">Temel Bilgiler</h3>
            </div>
            <div class="p-6 space-y-6">
              <!-- Material Name -->
              <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                  Malzeme Adı <span class="text-red-500">*</span>
                </label>
                <input
                  id="name"
                  v-model="form.name"
                  type="text"
                  required
                  class="block w-full px-4 py-3 text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                  placeholder="Örn: Portland Çimento CEM I 42.5 R"
                />
              </div>

              <!-- Material Code -->
              <div>
                <label for="material_code" class="block text-sm font-medium text-gray-700 mb-2">
                  Malzeme Kodu
                </label>
                <input
                  id="material_code"
                  v-model="form.material_code"
                  type="text"
                  class="block w-full px-4 py-3 text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors font-mono"
                  placeholder="Örn: MAT-001"
                />
                <p class="mt-1 text-sm text-gray-500">Opsiyonel. Sistemde benzersiz olmalıdır.</p>
              </div>

              <!-- Description -->
              <div>
                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                  Açıklama
                </label>
                <textarea
                  id="description"
                  v-model="form.description"
                  rows="3"
                  class="block w-full px-4 py-3 text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                  placeholder="Malzeme hakkında genel açıklama..."
                ></textarea>
              </div>

              <!-- Category and Unit Row -->
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Category -->
                <div>
                  <label for="category" class="block text-sm font-medium text-gray-700 mb-2">
                    Kategori <span class="text-red-500">*</span>
                  </label>
                  <input
                    id="category"
                    v-model="form.category"
                    type="text"
                    required
                    list="category-suggestions"
                    class="block w-full px-4 py-3 text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                    placeholder="Örn: İnşaat Malzemeleri"
                  />
                  <datalist id="category-suggestions">
                    <option v-for="category in existingCategories" :key="category" :value="category" />
                    <option value="İnşaat Malzemeleri" />
                    <option value="Elektrik Malzemeleri" />
                    <option value="Sıhhi Tesisat Malzemeleri" />
                    <option value="Boya ve Yalıtım" />
                    <option value="Demir ve Çelik" />
                    <option value="Ahşap ve Kereste" />
                    <option value="Agregalar" />
                    <option value="Diğer" />
                  </datalist>
                </div>

                <!-- Unit -->
                <div>
                  <label for="unit" class="block text-sm font-medium text-gray-700 mb-2">
                    Birim <span class="text-red-500">*</span>
                  </label>
                  <select
                    id="unit"
                    v-model="form.unit"
                    required
                    class="block w-full px-4 py-3 text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                  >
                    <option value="">Seçiniz...</option>
                    <option value="adet">Adet</option>
                    <option value="kg">Kilogram (kg)</option>
                    <option value="ton">Ton</option>
                    <option value="m">Metre (m)</option>
                    <option value="m2">Metrekare (m²)</option>
                    <option value="m3">Metreküp (m³)</option>
                    <option value="lt">Litre (lt)</option>
                    <option value="paket">Paket</option>
                    <option value="koli">Koli</option>
                    <option value="rulo">Rulo</option>
                    <option value="kutu">Kutu</option>
                  </select>
                </div>
              </div>

              <!-- Estimated Unit Price -->
              <div>
                <label for="estimated_unit_price" class="block text-sm font-medium text-gray-700 mb-2">
                  Tahmini Birim Fiyat (TRY) <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                  <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <span class="text-gray-500 sm:text-sm">₺</span>
                  </div>
                  <input
                    id="estimated_unit_price"
                    v-model="form.estimated_unit_price"
                    type="number"
                    step="0.01"
                    min="0"
                    required
                    class="block w-full pl-8 pr-4 py-3 text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                    placeholder="0.00"
                  />
                </div>
                <p class="mt-1 text-sm text-gray-500">Malzemenin tahmini birim fiyatı. Daha sonra güncellenebilir.</p>
              </div>
            </div>
          </div>

          <!-- Technical Specifications Card -->
          <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
              <h3 class="text-lg font-medium text-gray-900">Teknik Özellikler</h3>
            </div>
            <div class="p-6">
              <div>
                <label for="specification" class="block text-sm font-medium text-gray-700 mb-2">
                  Standart Teknik Özellik
                </label>
                <textarea
                  id="specification"
                  v-model="form.specification"
                  rows="4"
                  class="block w-full px-4 py-3 text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                  placeholder="Malzemenin teknik özellikleri, standartları, sertifikalar vb..."
                ></textarea>
                <p class="mt-1 text-sm text-gray-500">Örn: TS EN 197-1 standardına uygun, dayanım sınıfı 42.5 N/mm²</p>
              </div>
            </div>
          </div>

          <!-- Status Card -->
          <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
              <h3 class="text-lg font-medium text-gray-900">Durum</h3>
            </div>
            <div class="p-6">
              <div class="flex items-center">
                <input
                  id="is_active"
                  v-model="form.is_active"
                  type="checkbox"
                  class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                />
                <label for="is_active" class="ml-3 block text-sm font-medium text-gray-700">
                  Malzeme aktif (Satınalma taleplerinde kullanılabilir)
                </label>
              </div>
              <p class="mt-2 text-sm text-gray-500">Pasif malzemeler yeni taleplerde seçilemez ancak mevcut kayıtlar etkilenmez.</p>
            </div>
          </div>

          <!-- Form Actions -->
          <div class="flex items-center justify-end space-x-4 pt-6">
            <Link
              :href="route('materials.index')"
              class="inline-flex items-center px-6 py-3 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors"
            >
              İptal
            </Link>
            <button
              type="submit"
              :disabled="processing"
              class="inline-flex items-center px-6 py-3 border border-transparent rounded-lg text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <svg v-if="processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              {{ processing ? 'Güncelleniyor...' : 'Güncelle' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  material: {
    type: Object,
    required: true
  },
  categories: {
    type: Array,
    default: () => []
  }
})

const form = ref({
  name: '',
  material_code: '',
  description: '',
  category: '',
  unit: '',
  estimated_unit_price: '',
  specification: '',
  is_active: true
})

const processing = ref(false)
const existingCategories = ref(props.categories || [])

// Load existing data on mount
onMounted(() => {
  form.value = {
    name: props.material.name || '',
    material_code: props.material.material_code || '',
    description: props.material.description || '',
    category: props.material.category || '',
    unit: props.material.unit || '',
    estimated_unit_price: props.material.estimated_unit_price || '',
    specification: props.material.specification || '',
    is_active: props.material.is_active !== undefined ? props.material.is_active : true
  }
})

const submitForm = () => {
  processing.value = true
  router.put(route('materials.update', props.material.id), form.value, {
    onFinish: () => {
      processing.value = false
    }
  })
}
</script>
