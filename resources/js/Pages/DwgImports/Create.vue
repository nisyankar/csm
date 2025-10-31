<template>
  <AppLayout title="DWG Dosyası Yükle">
    <template #header>
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
          <Link :href="route('dwg-imports.index')" class="text-gray-600 hover:text-gray-900">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
            </svg>
          </Link>
          <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            AutoCAD DWG/DXF Dosyası Yükle
          </h2>
        </div>
      </div>
    </template>

    <div class="py-6">
      <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <!-- Info Card -->
        <div class="bg-gradient-to-r from-blue-50 to-cyan-50 border border-blue-200 rounded-xl p-6">
          <div class="flex items-start space-x-4">
            <div class="flex-shrink-0">
              <svg class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
              </svg>
            </div>
            <div class="flex-1">
              <h3 class="text-sm font-medium text-blue-900 mb-2">DWG İçe Aktarım Hakkında</h3>
              <ul class="text-sm text-blue-800 space-y-1">
                <li class="flex items-start">
                  <svg class="w-4 h-4 mr-2 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  DWG veya DXF formatında dosyalar desteklenir (maksimum 50MB)
                </li>
                <li class="flex items-start">
                  <svg class="w-4 h-4 mr-2 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  Dosya yüklendiğinde otomatik olarak işlenmeye başlar
                </li>
                <li class="flex items-start">
                  <svg class="w-4 h-4 mr-2 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  İşlem tamamlandığında tespit edilen yapı/kat/birimler için eşleştirme yapabilirsiniz
                </li>
                <li class="flex items-start">
                  <svg class="w-4 h-4 mr-2 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  Eşleştirme sonrası onaylayarak projeye ekleyebilirsiniz
                </li>
              </ul>
            </div>
          </div>
        </div>

        <!-- Upload Form -->
        <form @submit.prevent="submit" class="space-y-6">
          <!-- Step 1: Project Selection -->
          <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-blue-50 to-cyan-50 border-b border-gray-200">
              <div class="flex items-center space-x-3">
                <div class="flex-shrink-0 w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center text-white font-semibold text-sm">
                  1
                </div>
                <h3 class="text-lg font-semibold text-gray-900">Proje Seçimi</h3>
              </div>
            </div>
            <div class="p-6">
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Proje <span class="text-red-500">*</span>
              </label>
              <select
                v-model="form.project_id"
                required
                class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
              >
                <option value="">Proje Seçin</option>
                <option v-for="project in projects" :key="project.id" :value="project.id">
                  {{ project.name }}
                </option>
              </select>
              <p class="mt-2 text-xs text-gray-500">
                DWG dosyasından çıkarılan yapı bilgileri bu projeye eklenecektir.
              </p>
            </div>
          </div>

          <!-- Step 2: Import Type -->
          <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-cyan-50 to-teal-50 border-b border-gray-200">
              <div class="flex items-center space-x-3">
                <div class="flex-shrink-0 w-8 h-8 bg-cyan-600 rounded-full flex items-center justify-center text-white font-semibold text-sm">
                  2
                </div>
                <h3 class="text-lg font-semibold text-gray-900">İçe Aktarım Tipi</h3>
              </div>
            </div>
            <div class="p-6">
              <label class="block text-sm font-medium text-gray-700 mb-4">
                Ne aktarılsın? <span class="text-red-500">*</span>
              </label>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <label
                  :class="[
                    'relative flex cursor-pointer rounded-lg border p-4 transition-all',
                    form.import_type === 'comprehensive'
                      ? 'border-blue-500 bg-blue-50 ring-2 ring-blue-500'
                      : 'border-gray-300 hover:border-gray-400'
                  ]"
                >
                  <input
                    type="radio"
                    v-model="form.import_type"
                    value="comprehensive"
                    class="sr-only"
                  />
                  <div class="flex flex-1">
                    <div class="flex flex-col">
                      <span class="block text-sm font-medium text-gray-900">Toplu İçe Aktarım</span>
                      <span class="mt-1 flex items-center text-xs text-gray-500">
                        Yapı + Kat + Birim (hepsi birden)
                      </span>
                    </div>
                  </div>
                  <svg
                    v-if="form.import_type === 'comprehensive'"
                    class="h-5 w-5 text-blue-600"
                    fill="currentColor"
                    viewBox="0 0 20 20"
                  >
                    <path
                      fill-rule="evenodd"
                      d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                      clip-rule="evenodd"
                    />
                  </svg>
                </label>

                <label
                  :class="[
                    'relative flex cursor-pointer rounded-lg border p-4 transition-all',
                    form.import_type === 'structures_only'
                      ? 'border-blue-500 bg-blue-50 ring-2 ring-blue-500'
                      : 'border-gray-300 hover:border-gray-400'
                  ]"
                >
                  <input
                    type="radio"
                    v-model="form.import_type"
                    value="structures_only"
                    class="sr-only"
                  />
                  <div class="flex flex-1">
                    <div class="flex flex-col">
                      <span class="block text-sm font-medium text-gray-900">Sadece Yapılar</span>
                      <span class="mt-1 flex items-center text-xs text-gray-500">
                        Bina/blok yapıları
                      </span>
                    </div>
                  </div>
                  <svg
                    v-if="form.import_type === 'structures_only'"
                    class="h-5 w-5 text-blue-600"
                    fill="currentColor"
                    viewBox="0 0 20 20"
                  >
                    <path
                      fill-rule="evenodd"
                      d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                      clip-rule="evenodd"
                    />
                  </svg>
                </label>

                <label
                  :class="[
                    'relative flex cursor-pointer rounded-lg border p-4 transition-all',
                    form.import_type === 'floors_only'
                      ? 'border-blue-500 bg-blue-50 ring-2 ring-blue-500'
                      : 'border-gray-300 hover:border-gray-400'
                  ]"
                >
                  <input
                    type="radio"
                    v-model="form.import_type"
                    value="floors_only"
                    class="sr-only"
                  />
                  <div class="flex flex-1">
                    <div class="flex flex-col">
                      <span class="block text-sm font-medium text-gray-900">Sadece Katlar</span>
                      <span class="mt-1 flex items-center text-xs text-gray-500">
                        Mevcut yapılara kat ekleme
                      </span>
                    </div>
                  </div>
                  <svg
                    v-if="form.import_type === 'floors_only'"
                    class="h-5 w-5 text-blue-600"
                    fill="currentColor"
                    viewBox="0 0 20 20"
                  >
                    <path
                      fill-rule="evenodd"
                      d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                      clip-rule="evenodd"
                    />
                  </svg>
                </label>

                <label
                  :class="[
                    'relative flex cursor-pointer rounded-lg border p-4 transition-all',
                    form.import_type === 'units_only'
                      ? 'border-blue-500 bg-blue-50 ring-2 ring-blue-500'
                      : 'border-gray-300 hover:border-gray-400'
                  ]"
                >
                  <input
                    type="radio"
                    v-model="form.import_type"
                    value="units_only"
                    class="sr-only"
                  />
                  <div class="flex flex-1">
                    <div class="flex flex-col">
                      <span class="block text-sm font-medium text-gray-900">Sadece Birimler</span>
                      <span class="mt-1 flex items-center text-xs text-gray-500">
                        Mevcut katlara daire/birim ekleme
                      </span>
                    </div>
                  </div>
                  <svg
                    v-if="form.import_type === 'units_only'"
                    class="h-5 w-5 text-blue-600"
                    fill="currentColor"
                    viewBox="0 0 20 20"
                  >
                    <path
                      fill-rule="evenodd"
                      d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                      clip-rule="evenodd"
                    />
                  </svg>
                </label>
              </div>
            </div>
          </div>

          <!-- Step 3: File Upload -->
          <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-teal-50 to-green-50 border-b border-gray-200">
              <div class="flex items-center space-x-3">
                <div class="flex-shrink-0 w-8 h-8 bg-teal-600 rounded-full flex items-center justify-center text-white font-semibold text-sm">
                  3
                </div>
                <h3 class="text-lg font-semibold text-gray-900">Dosya Yükleme</h3>
              </div>
            </div>
            <div class="p-6">
              <label class="block text-sm font-medium text-gray-700 mb-2">
                DWG/DXF Dosyası <span class="text-red-500">*</span>
              </label>
              <div
                @dragover.prevent="dragover = true"
                @dragleave.prevent="dragover = false"
                @drop.prevent="handleDrop"
                :class="[
                  'mt-2 flex justify-center rounded-lg border-2 border-dashed px-6 py-10 transition-all',
                  dragover ? 'border-blue-500 bg-blue-50' : 'border-gray-300 hover:border-gray-400'
                ]"
              >
                <div class="text-center">
                  <svg
                    class="mx-auto h-12 w-12 text-gray-400"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="1.5"
                    stroke="currentColor"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m6.75 12l-3-3m0 0l-3 3m3-3v6m-1.5-15H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"
                    />
                  </svg>
                  <div class="mt-4 flex text-sm leading-6 text-gray-600">
                    <label
                      for="file-upload"
                      class="relative cursor-pointer rounded-md bg-white font-semibold text-blue-600 focus-within:outline-none focus-within:ring-2 focus-within:ring-blue-600 focus-within:ring-offset-2 hover:text-blue-500"
                    >
                      <span>Dosya seçin</span>
                      <input
                        id="file-upload"
                        name="file-upload"
                        type="file"
                        class="sr-only"
                        accept=".dwg,.dxf"
                        @change="handleFileChange"
                        required
                      />
                    </label>
                    <p class="pl-1">veya sürükleyip bırakın</p>
                  </div>
                  <p class="text-xs leading-5 text-gray-600">DWG, DXF (max. 50MB)</p>

                  <!-- Selected File Display -->
                  <div v-if="selectedFile" class="mt-4 flex items-center justify-center space-x-2 text-sm">
                    <svg class="h-5 w-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="text-gray-900 font-medium">{{ selectedFile.name }}</span>
                    <span class="text-gray-500">({{ formatFileSize(selectedFile.size) }})</span>
                    <button
                      type="button"
                      @click="clearFile"
                      class="text-red-600 hover:text-red-700"
                    >
                      <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                      </svg>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Step 4: Notes (Optional) -->
          <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-green-50 to-emerald-50 border-b border-gray-200">
              <div class="flex items-center space-x-3">
                <div class="flex-shrink-0 w-8 h-8 bg-green-600 rounded-full flex items-center justify-center text-white font-semibold text-sm">
                  4
                </div>
                <h3 class="text-lg font-semibold text-gray-900">Notlar (Opsiyonel)</h3>
              </div>
            </div>
            <div class="p-6">
              <label class="block text-sm font-medium text-gray-700 mb-2">Açıklama/Notlar</label>
              <textarea
                v-model="form.notes"
                rows="3"
                class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                placeholder="Bu içe aktarım hakkında notlar ekleyebilirsiniz..."
              ></textarea>
            </div>
          </div>

          <!-- Form Actions -->
          <div class="flex items-center justify-end space-x-4">
            <Link
              :href="route('dwg-imports.index')"
              class="px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition-all"
            >
              İptal
            </Link>
            <button
              type="submit"
              :disabled="!isFormValid || form.processing"
              :class="[
                'px-6 py-3 text-white text-sm font-medium rounded-lg transition-all inline-flex items-center',
                isFormValid && !form.processing
                  ? 'bg-blue-600 hover:bg-blue-700'
                  : 'bg-gray-400 cursor-not-allowed'
              ]"
            >
              <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              {{ form.processing ? 'Yükleniyor...' : 'Dosyayı Yükle ve İşle' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  projects: Array,
})

// Form state
const form = reactive({
  project_id: '',
  import_type: 'comprehensive',
  dwg_file: null,
  notes: '',
  processing: false,
})

const selectedFile = ref(null)
const dragover = ref(false)

// Methods
const handleFileChange = (event) => {
  const file = event.target.files[0]
  if (file) {
    selectedFile.value = file
    form.dwg_file = file
  }
}

const handleDrop = (event) => {
  dragover.value = false
  const file = event.dataTransfer.files[0]
  if (file && (file.name.endsWith('.dwg') || file.name.endsWith('.dxf'))) {
    selectedFile.value = file
    form.dwg_file = file
  }
}

const clearFile = () => {
  selectedFile.value = null
  form.dwg_file = null
  const fileInput = document.getElementById('file-upload')
  if (fileInput) {
    fileInput.value = ''
  }
}

const formatFileSize = (bytes) => {
  if (bytes === 0) return '0 Bytes'
  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i]
}

const isFormValid = computed(() => {
  return form.project_id && form.import_type && form.dwg_file
})

const submit = () => {
  if (!isFormValid.value) return

  form.processing = true

  const formData = new FormData()
  formData.append('project_id', form.project_id)
  formData.append('import_type', form.import_type)
  formData.append('dwg_file', form.dwg_file)
  if (form.notes) {
    formData.append('notes', form.notes)
  }

  router.post(route('dwg-imports.store'), formData, {
    forceFormData: true,
    onFinish: () => {
      form.processing = false
    },
  })
}
</script>
