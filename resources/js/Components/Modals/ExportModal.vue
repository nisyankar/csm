<template>
  <Modal
    :show="show"
    title="Dışa Aktar"
    size="lg"
    @close="$emit('close')"
    @confirm="handleExport"
    :confirm-disabled="!selectedFormat || isExporting"
    :loading="isExporting"
    confirm-text="Dışa Aktar"
    loading-text="Dışa Aktarılıyor..."
    show-default-footer
  >
    <div class="space-y-6">
      <!-- Export Format Selection -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-3">
          Dosya Formatı
        </label>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <div
            v-for="format in exportFormats"
            :key="format.value"
            @click="selectedFormat = format.value"
            :class="[
              'relative cursor-pointer rounded-lg border p-4 focus:outline-none transition-colors',
              selectedFormat === format.value
                ? 'border-blue-600 ring-2 ring-blue-600 bg-blue-50'
                : 'border-gray-300 hover:border-gray-400'
            ]"
          >
            <div class="flex items-center">
              <input
                :id="format.value"
                v-model="selectedFormat"
                :value="format.value"
                type="radio"
                class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500"
              />
              <label :for="format.value" class="ml-3 flex-1 cursor-pointer">
                <div class="flex items-center">
                  <component
                    :is="format.icon"
                    class="h-5 w-5 text-gray-400 mr-2"
                  />
                  <div>
                    <div class="text-sm font-medium text-gray-900">
                      {{ format.label }}
                    </div>
                    <div class="text-xs text-gray-500">
                      {{ format.description }}
                    </div>
                  </div>
                </div>
              </label>
            </div>
          </div>
        </div>
      </div>

      <!-- Export Options -->
      <div v-if="selectedFormat">
        <label class="block text-sm font-medium text-gray-700 mb-3">
          Dışa Aktarma Seçenekleri
        </label>
        
        <div class="space-y-4">
          <!-- Date Range -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Başlangıç Tarihi
              </label>
              <input
                v-model="exportOptions.dateFrom"
                type="date"
                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Bitiş Tarihi
              </label>
              <input
                v-model="exportOptions.dateTo"
                type="date"
                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
              />
            </div>
          </div>

          <!-- Include Options -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Dahil Edilecek Veriler
            </label>
            <div class="space-y-2">
              <div
                v-for="option in includeOptions"
                :key="option.value"
                class="flex items-center"
              >
                <input
                  :id="option.value"
                  v-model="exportOptions.include"
                  :value="option.value"
                  type="checkbox"
                  class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                />
                <label :for="option.value" class="ml-2 text-sm text-gray-700">
                  {{ option.label }}
                </label>
              </div>
            </div>
          </div>

          <!-- Excel Specific Options -->
          <div v-if="selectedFormat === 'xlsx'" class="space-y-3">
            <div class="flex items-center">
              <input
                id="include-header"
                v-model="exportOptions.includeHeader"
                type="checkbox"
                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
              />
              <label for="include-header" class="ml-2 text-sm text-gray-700">
                Başlık satırını dahil et
              </label>
            </div>
            
            <div class="flex items-center">
              <input
                id="include-summary"
                v-model="exportOptions.includeSummary"
                type="checkbox"
                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
              />
              <label for="include-summary" class="ml-2 text-sm text-gray-700">
                Özet sayfa oluştur
              </label>
            </div>
          </div>

          <!-- PDF Specific Options -->
          <div v-if="selectedFormat === 'pdf'" class="space-y-3">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Sayfa Yönlendirmesi
              </label>
              <select
                v-model="exportOptions.orientation"
                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
              >
                <option value="portrait">Dikey</option>
                <option value="landscape">Yatay</option>
              </select>
            </div>
            
            <div class="flex items-center">
              <input
                id="include-logo"
                v-model="exportOptions.includeLogo"
                type="checkbox"
                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
              />
              <label for="include-logo" class="ml-2 text-sm text-gray-700">
                Şirket logosunu dahil et
              </label>
            </div>
          </div>

          <!-- File Name -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Dosya Adı
            </label>
            <input
              v-model="exportOptions.fileName"
              type="text"
              placeholder="Dosya adını girin (isteğe bağlı)"
              class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
            />
            <p class="mt-1 text-xs text-gray-500">
              Boş bırakılırsa otomatik ad verilir
            </p>
          </div>
        </div>
      </div>

      <!-- Export Preview -->
      <div v-if="selectedFormat && exportOptions.include.length > 0" class="bg-gray-50 rounded-lg p-4">
        <h4 class="text-sm font-medium text-gray-900 mb-2">Dışa Aktarma Özeti</h4>
        <div class="text-sm text-gray-600 space-y-1">
          <p><strong>Format:</strong> {{ getFormatLabel(selectedFormat) }}</p>
          <p v-if="exportOptions.dateFrom || exportOptions.dateTo">
            <strong>Tarih Aralığı:</strong> 
            {{ exportOptions.dateFrom || 'Başlangıç' }} - {{ exportOptions.dateTo || 'Son' }}
          </p>
          <p><strong>Dahil Edilen Veriler:</strong> {{ exportOptions.include.length }} öğe</p>
          <p v-if="exportOptions.fileName">
            <strong>Dosya Adı:</strong> {{ exportOptions.fileName }}.{{ selectedFormat }}
          </p>
        </div>
      </div>

      <!-- Warning Messages -->
      <div v-if="showWarnings" class="space-y-2">
        <div
          v-if="!exportOptions.include.length"
          class="bg-yellow-50 border border-yellow-200 rounded-md p-3"
        >
          <div class="flex">
            <svg class="h-5 w-5 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
            </svg>
            <div class="ml-3">
              <p class="text-sm text-yellow-800">
                En az bir veri türü seçmelisiniz.
              </p>
            </div>
          </div>
        </div>

        <div
          v-if="hasLargeDataWarning"
          class="bg-blue-50 border border-blue-200 rounded-md p-3"
        >
          <div class="flex">
            <svg class="h-5 w-5 text-blue-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
            </svg>
            <div class="ml-3">
              <p class="text-sm text-blue-800">
                Büyük veri setleri için dışa aktarma işlemi zaman alabilir.
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </Modal>
</template>

<script setup>
import { ref, computed, watch, onMounted, h } from 'vue'
import Modal from '../UI/Modal.vue'

// Props
const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  totalRecords: {
    type: Number,
    default: 0
  }
})

// Emits
const emit = defineEmits(['close', 'export'])

// State
const selectedFormat = ref('')
const isExporting = ref(false)

const exportOptions = ref({
  dateFrom: '',
  dateTo: '',
  include: ['basic_info'],
  includeHeader: true,
  includeSummary: false,
  includeLogo: true,
  orientation: 'portrait',
  fileName: ''
})

// Export formats
const exportFormats = [
  {
    value: 'xlsx',
    label: 'Excel (.xlsx)',
    description: 'Microsoft Excel formatı',
    icon: () => h('svg', {
      fill: 'none',
      viewBox: '0 0 24 24',
      'stroke-width': '1.5',
      stroke: 'currentColor',
      class: 'h-5 w-5'
    }, [
      h('path', {
        'stroke-linecap': 'round',
        'stroke-linejoin': 'round',
        d: 'M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z'
      })
    ])
  },
  {
    value: 'csv',
    label: 'CSV (.csv)',
    description: 'Virgülle ayrılmış değerler',
    icon: () => h('svg', {
      fill: 'none',
      viewBox: '0 0 24 24',
      'stroke-width': '1.5',
      stroke: 'currentColor',
      class: 'h-5 w-5'
    }, [
      h('path', {
        'stroke-linecap': 'round',
        'stroke-linejoin': 'round',
        d: 'M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m3 6.75H7.5m0 0v6m0-6H4.5m3 6h13.5m-13.5 0v-6a3 3 0 013-3h1.5m-3 9.75h13.5'
      })
    ])
  },
  {
    value: 'pdf',
    label: 'PDF (.pdf)',
    description: 'Taşınabilir belge formatı',
    icon: () => h('svg', {
      fill: 'none',
      viewBox: '0 0 24 24',
      'stroke-width': '1.5',
      stroke: 'currentColor',
      class: 'h-5 w-5'
    }, [
      h('path', {
        'stroke-linecap': 'round',
        'stroke-linejoin': 'round',
        d: 'M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m6.75 18.75h-3.75'
      })
    ])
  }
]

// Include options
const includeOptions = [
  { value: 'basic_info', label: 'Temel Bilgiler (Ad, Soyad, Sicil No)' },
  { value: 'contact_info', label: 'İletişim Bilgileri (Email, Telefon)' },
  { value: 'employment_info', label: 'İstihdam Bilgileri (Departman, Pozisyon, Maaş)' },
  { value: 'timesheet_data', label: 'Puantaj Verileri' },
  { value: 'project_assignments', label: 'Proje Atamaları' },
  { value: 'leave_records', label: 'İzin Kayıtları' },
  { value: 'documents', label: 'Belgeler' }
]

// Computed
const showWarnings = computed(() => {
  return !exportOptions.value.include.length || hasLargeDataWarning.value
})

const hasLargeDataWarning = computed(() => {
  return exportOptions.value.include.length > 4
})

// Methods
const getFormatLabel = (format) => {
  const formatObj = exportFormats.find(f => f.value === format)
  return formatObj ? formatObj.label : format.toUpperCase()
}

const handleExport = () => {
  if (!selectedFormat.value || !exportOptions.value.include.length) {
    return
  }

  isExporting.value = true

  // Simulate export process
  setTimeout(() => {
    const exportData = {
      format: selectedFormat.value,
      options: { ...exportOptions.value }
    }

    // Generate filename if not provided
    if (!exportData.options.fileName) {
      const timestamp = new Date().toISOString().slice(0, 10)
      exportData.options.fileName = `calisanlar-${timestamp}`
    }

    // Emit export event
    emit('export', exportData.format, exportData.options)
    
    isExporting.value = false
  }, 2000)
}

// Initialize default date range
onMounted(() => {
  const today = new Date()
  const oneMonthAgo = new Date(today.getFullYear(), today.getMonth() - 1, today.getDate())
  
  exportOptions.value.dateFrom = oneMonthAgo.toISOString().slice(0, 10)
  exportOptions.value.dateTo = today.toISOString().slice(0, 10)
})

// Reset when modal closes
watch(() => props.show, (newValue) => {
  if (!newValue) {
    // Reset state when modal closes
    setTimeout(() => {
      selectedFormat.value = ''
      isExporting.value = false
      exportOptions.value.include = ['basic_info']
      exportOptions.value.fileName = ''
    }, 300)
  }
})
</script>