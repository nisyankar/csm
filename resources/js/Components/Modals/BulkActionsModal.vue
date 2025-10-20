<template>
  <Modal
    :show="show"
    title="Toplu İşlemler"
    size="lg"
    @close="$emit('close')"
    @confirm="handleAction"
    :confirm-disabled="!selectedAction || isProcessing"
    :loading="isProcessing"
    :confirm-text="confirmButtonText"
    :confirm-type="confirmButtonType"
    loading-text="İşleniyor..."
    show-default-footer
  >
    <div class="space-y-6">
      <!-- Selection Summary -->
      <div class="bg-blue-50 rounded-lg p-4">
        <div class="flex items-center">
          <svg class="h-5 w-5 text-blue-400 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
          </svg>
          <div>
            <p class="text-sm font-medium text-blue-900">
              {{ selectedCount }} çalışan seçildi
            </p>
            <p class="text-xs text-blue-700">
              Seçilen çalışanlar üzerinde toplu işlem yapabilirsiniz
            </p>
          </div>
        </div>
      </div>

      <!-- Action Selection -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-3">
          Yapılacak İşlem
        </label>
        <div class="space-y-3">
          <div
            v-for="action in availableActions"
            :key="action.value"
            @click="selectedAction = action.value"
            :class="[
              'relative cursor-pointer rounded-lg border p-4 focus:outline-none transition-colors',
              selectedAction === action.value
                ? 'border-blue-600 ring-2 ring-blue-600 bg-blue-50'
                : 'border-gray-300 hover:border-gray-400',
              action.disabled ? 'opacity-50 cursor-not-allowed' : ''
            ]"
          >
            <div class="flex items-start">
              <input
                :id="action.value"
                v-model="selectedAction"
                :value="action.value"
                :disabled="action.disabled"
                type="radio"
                class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500 mt-1"
              />
              <label :for="action.value" class="ml-3 flex-1 cursor-pointer">
                <div class="flex items-center">
                  <component
                    :is="action.icon"
                    :class="[
                      'h-5 w-5 mr-2',
                      action.type === 'danger' ? 'text-red-500' : 
                      action.type === 'warning' ? 'text-yellow-500' : 'text-gray-500'
                    ]"
                  />
                  <div>
                    <div class="text-sm font-medium text-gray-900">
                      {{ action.label }}
                    </div>
                    <div class="text-xs text-gray-500">
                      {{ action.description }}
                    </div>
                  </div>
                </div>
              </label>
            </div>
          </div>
        </div>
      </div>

      <!-- Action Specific Options -->
      <div v-if="selectedAction" class="space-y-4">
        <!-- Status Change Options -->
        <div v-if="selectedAction === 'change_status'">
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Yeni Durum
          </label>
          <select
            v-model="actionOptions.newStatus"
            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
          >
            <option value="">Durum seçiniz</option>
            <option value="active">Aktif</option>
            <option value="inactive">Pasif</option>
            <option value="suspended">Askıya Alınmış</option>
          </select>
        </div>

        <!-- Department Transfer Options -->
        <div v-if="selectedAction === 'transfer_department'">
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Yeni Departman
          </label>
          <select
            v-model="actionOptions.newDepartment"
            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
          >
            <option value="">Departman seçiniz</option>
            <option value="1">Yapısal Mühendislik</option>
            <option value="2">Elektrik</option>
            <option value="3">Mekanik</option>
            <option value="4">İnsan Kaynakları</option>
          </select>
        </div>

        <!-- Project Assignment Options -->
        <div v-if="selectedAction === 'assign_project'">
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Atanacak Proje
          </label>
          <select
            v-model="actionOptions.projectId"
            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
          >
            <option value="">Proje seçiniz</option>
            <option value="1">Konut Projesi A</option>
            <option value="2">Ticari Merkez B</option>
            <option value="3">Altyapı Projesi C</option>
          </select>
        </div>

        <!-- Email Options -->
        <div v-if="selectedAction === 'send_notification'">
          <div class="space-y-3">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Bildirim Türü
              </label>
              <select
                v-model="actionOptions.notificationType"
                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
              >
                <option value="">Bildirim türü seçiniz</option>
                <option value="info">Bilgilendirme</option>
                <option value="warning">Uyarı</option>
                <option value="reminder">Hatırlatma</option>
                <option value="announcement">Duyuru</option>
              </select>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Mesaj
              </label>
              <textarea
                v-model="actionOptions.message"
                rows="3"
                placeholder="Bildirim mesajını yazın..."
                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
              ></textarea>
            </div>
          </div>
        </div>

        <!-- Generate QR Codes Options -->
        <div v-if="selectedAction === 'generate_qr'">
          <div class="space-y-3">
            <div class="flex items-center">
              <input
                id="include-name"
                v-model="actionOptions.includeName"
                type="checkbox"
                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
              />
              <label for="include-name" class="ml-2 text-sm text-gray-700">
                QR kod üzerinde isim göster
              </label>
            </div>
            
            <div class="flex items-center">
              <input
                id="include-photo"
                v-model="actionOptions.includePhoto"
                type="checkbox"
                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
              />
              <label for="include-photo" class="ml-2 text-sm text-gray-700">
                Çalışan fotoğrafı dahil et
              </label>
            </div>
          </div>
        </div>

        <!-- Export Options -->
        <div v-if="selectedAction === 'export_data'">
          <div class="space-y-3">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Dosya Formatı
              </label>
              <select
                v-model="actionOptions.exportFormat"
                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
              >
                <option value="xlsx">Excel (.xlsx)</option>
                <option value="csv">CSV (.csv)</option>
                <option value="pdf">PDF (.pdf)</option>
              </select>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Dahil Edilecek Bilgiler
              </label>
              <div class="space-y-2">
                <div class="flex items-center">
                  <input
                    id="basic-info"
                    v-model="actionOptions.includeFields"
                    value="basic"
                    type="checkbox"
                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                  />
                  <label for="basic-info" class="ml-2 text-sm text-gray-700">
                    Temel bilgiler
                  </label>
                </div>
                <div class="flex items-center">
                  <input
                    id="contact-info"
                    v-model="actionOptions.includeFields"
                    value="contact"
                    type="checkbox"
                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                  />
                  <label for="contact-info" class="ml-2 text-sm text-gray-700">
                    İletişim bilgileri
                  </label>
                </div>
                <div class="flex items-center">
                  <input
                    id="employment-info"
                    v-model="actionOptions.includeFields"
                    value="employment"
                    type="checkbox"
                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                  />
                  <label for="employment-info" class="ml-2 text-sm text-gray-700">
                    İstihdam bilgileri
                  </label>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Warning Messages -->
      <div v-if="showWarning" class="space-y-2">
        <div
          v-if="selectedAction === 'delete'"
          class="bg-red-50 border border-red-200 rounded-md p-3"
        >
          <div class="flex">
            <svg class="h-5 w-5 text-red-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
            </svg>
            <div class="ml-3">
              <p class="text-sm text-red-800">
                <strong>Dikkat!</strong> Bu işlem geri alınamaz. Seçilen {{ selectedCount }} çalışan kalıcı olarak silinecektir.
              </p>
            </div>
          </div>
        </div>

        <div
          v-else-if="selectedAction === 'change_status' && actionOptions.newStatus === 'inactive'"
          class="bg-yellow-50 border border-yellow-200 rounded-md p-3"
        >
          <div class="flex">
            <svg class="h-5 w-5 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
            </svg>
            <div class="ml-3">
              <p class="text-sm text-yellow-800">
                Pasif duruma alınan çalışanlar sisteme giriş yapamayacak ve puantaj işlemlerinde görünmeyecektir.
              </p>
            </div>
          </div>
        </div>

        <div
          v-else-if="selectedAction === 'send_notification' && !actionOptions.message"
          class="bg-yellow-50 border border-yellow-200 rounded-md p-3"
        >
          <div class="flex">
            <svg class="h-5 w-5 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l-.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
            </svg>
            <div class="ml-3">
              <p class="text-sm text-yellow-800">
                Bildirim göndermek için mesaj yazmanız gerekiyor.
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Action Summary -->
      <div v-if="selectedAction && isActionValid" class="bg-gray-50 rounded-lg p-4">
        <h4 class="text-sm font-medium text-gray-900 mb-2">İşlem Özeti</h4>
        <div class="text-sm text-gray-600 space-y-1">
          <p><strong>İşlem:</strong> {{ getActionLabel(selectedAction) }}</p>
          <p><strong>Etkilenecek Çalışan:</strong> {{ selectedCount }} kişi</p>
          <div v-if="getActionDetails()">
            <p><strong>Detaylar:</strong></p>
            <ul class="ml-4 list-disc text-xs space-y-1">
              <li v-for="detail in getActionDetails()" :key="detail">{{ detail }}</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </Modal>
</template>

<script setup>
import { ref, computed, watch, h } from 'vue'
import Modal from '../UI/Modal.vue'

// Props
const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  selectedCount: {
    type: Number,
    default: 0
  }
})

// Emits
const emit = defineEmits(['close', 'action'])

// State
const selectedAction = ref('')
const isProcessing = ref(false)

const actionOptions = ref({
  newStatus: '',
  newDepartment: '',
  projectId: '',
  notificationType: '',
  message: '',
  includeName: true,
  includePhoto: false,
  exportFormat: 'xlsx',
  includeFields: ['basic']
})

// Available actions
const availableActions = [
  {
    value: 'change_status',
    label: 'Durum Değiştir',
    description: 'Seçilen çalışanların durumunu toplu olarak değiştir',
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
        d: 'M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99'
      })
    ]),
    type: 'default'
  },
  {
    value: 'transfer_department',
    label: 'Departman Değiştir',
    description: 'Seçilen çalışanları başka departmana transfer et',
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
        d: 'M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008z'
      })
    ]),
    type: 'default'
  },
  {
    value: 'assign_project',
    label: 'Proje Ata',
    description: 'Seçilen çalışanları bir projeye toplu olarak ata',
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
        d: 'M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 00.75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 00-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0112 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 01-.673-.38m0 0A2.18 2.18 0 013 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 013.413-.387m7.5 0V5.25A2.25 2.25 0 0013.5 3h-3a2.25 2.25 0 00-2.25 2.25v.894m7.5 0a48.667 48.667 0 00-7.5 0M12 12.75h.008v.008H12v-.008z'
      })
    ]),
    type: 'default'
  },
  {
    value: 'send_notification',
    label: 'Bildirim Gönder',
    description: 'Seçilen çalışanlara toplu bildirim gönder',
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
        d: 'M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0'
      })
    ]),
    type: 'default'
  },
  {
    value: 'generate_qr',
    label: 'QR Kod Üret',
    description: 'Seçilen çalışanlar için QR kodları toplu olarak üret',
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
        d: 'M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z'
      }),
      h('path', {
        'stroke-linecap': 'round',
        'stroke-linejoin': 'round',
        d: 'M6.75 6.75h.75v.75h-.75v-.75zM6.75 16.5h.75v.75h-.75v-.75zM16.5 6.75h.75v.75h-.75v-.75zM13.5 13.5h.75v.75h-.75v-.75zM13.5 19.5h.75v.75h-.75v-.75zM19.5 13.5h.75v.75h-.75v-.75zM19.5 19.5h.75v.75h-.75v-.75zM16.5 16.5h.75v.75h-.75v-.75z'
      })
    ]),
    type: 'default'
  },
  {
    value: 'export_data',
    label: 'Veri Dışa Aktar',
    description: 'Seçilen çalışanların verilerini dışa aktar',
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
        d: 'M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3'
      })
    ]),
    type: 'default'
  },
  {
    value: 'delete',
    label: 'Çalışanları Sil',
    description: 'Seçilen çalışanları sistemden kalıcı olarak sil',
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
        d: 'M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0'
      })
    ]),
    type: 'danger',
    disabled: false
  }
]

// Computed
const confirmButtonText = computed(() => {
  if (isProcessing.value) return 'İşleniyor...'
  
  const actionLabels = {
    change_status: 'Durumu Değiştir',
    transfer_department: 'Departmanı Değiştir',
    assign_project: 'Projeye Ata',
    send_notification: 'Bildirim Gönder',
    generate_qr: 'QR Kodları Üret',
    export_data: 'Dışa Aktar',
    delete: 'Sil'
  }
  
  return actionLabels[selectedAction.value] || 'İşlemi Yap'
})

const confirmButtonType = computed(() => {
  return selectedAction.value === 'delete' ? 'danger' : 'primary'
})

const showWarning = computed(() => {
  return selectedAction.value === 'delete' || 
         (selectedAction.value === 'change_status' && actionOptions.value.newStatus === 'inactive') ||
         (selectedAction.value === 'send_notification' && !actionOptions.value.message)
})

const isActionValid = computed(() => {
  if (!selectedAction.value) return false
  
  switch (selectedAction.value) {
    case 'change_status':
      return !!actionOptions.value.newStatus
    case 'transfer_department':
      return !!actionOptions.value.newDepartment
    case 'assign_project':
      return !!actionOptions.value.projectId
    case 'send_notification':
      return !!actionOptions.value.notificationType && !!actionOptions.value.message
    case 'generate_qr':
    case 'export_data':
    case 'delete':
      return true
    default:
      return false
  }
})

// Methods
const getActionLabel = (action) => {
  const actionObj = availableActions.find(a => a.value === action)
  return actionObj ? actionObj.label : action
}

const getActionDetails = () => {
  const details = []
  
  switch (selectedAction.value) {
    case 'change_status':
      if (actionOptions.value.newStatus) {
        const statusLabels = {
          active: 'Aktif',
          inactive: 'Pasif',
          suspended: 'Askıya Alınmış'
        }
        details.push(`Yeni durum: ${statusLabels[actionOptions.value.newStatus]}`)
      }
      break
      
    case 'transfer_department':
      if (actionOptions.value.newDepartment) {
        const departmentLabels = {
          '1': 'Yapısal Mühendislik',
          '2': 'Elektrik',
          '3': 'Mekanik',
          '4': 'İnsan Kaynakları'
        }
        details.push(`Hedef departman: ${departmentLabels[actionOptions.value.newDepartment]}`)
      }
      break
      
    case 'assign_project':
      if (actionOptions.value.projectId) {
        const projectLabels = {
          '1': 'Konut Projesi A',
          '2': 'Ticari Merkez B',
          '3': 'Altyapı Projesi C'
        }
        details.push(`Atanacak proje: ${projectLabels[actionOptions.value.projectId]}`)
      }
      break
      
    case 'send_notification':
      if (actionOptions.value.notificationType) {
        const notificationLabels = {
          info: 'Bilgilendirme',
          warning: 'Uyarı',
          reminder: 'Hatırlatma',
          announcement: 'Duyuru'
        }
        details.push(`Bildirim türü: ${notificationLabels[actionOptions.value.notificationType]}`)
      }
      if (actionOptions.value.message) {
        details.push(`Mesaj: "${actionOptions.value.message.substring(0, 50)}${actionOptions.value.message.length > 50 ? '...' : ''}"`)
      }
      break
      
    case 'generate_qr':
      if (actionOptions.value.includeName) details.push('İsim dahil edilecek')
      if (actionOptions.value.includePhoto) details.push('Fotoğraf dahil edilecek')
      break
      
    case 'export_data':
      details.push(`Format: ${actionOptions.value.exportFormat.toUpperCase()}`)
      if (actionOptions.value.includeFields.length > 0) {
        const fieldLabels = {
          basic: 'Temel bilgiler',
          contact: 'İletişim bilgileri',
          employment: 'İstihdam bilgileri'
        }
        const includedFields = actionOptions.value.includeFields.map(field => fieldLabels[field]).join(', ')
        details.push(`Dahil edilen alanlar: ${includedFields}`)
      }
      break
  }
  
  return details
}

const handleAction = () => {
  if (!isActionValid.value) return
  
  isProcessing.value = true
  
  // Simulate processing
  setTimeout(() => {
    const actionData = {
      action: selectedAction.value,
      options: { ...actionOptions.value }
    }
    
    emit('action', actionData)
    isProcessing.value = false
  }, 2000)
}

// Reset when modal closes
watch(() => props.show, (newValue) => {
  if (!newValue) {
    setTimeout(() => {
      selectedAction.value = ''
      isProcessing.value = false
      
      // Reset action options
      actionOptions.value = {
        newStatus: '',
        newDepartment: '',
        projectId: '',
        notificationType: '',
        message: '',
        includeName: true,
        includePhoto: false,
        exportFormat: 'xlsx',
        includeFields: ['basic']
      }
    }, 300)
  }
})
</script>