<template>
  <AppLayout title="DWG İmport Detayı">
    <template #header>
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
          <Link :href="route('dwg-imports.index')" class="text-gray-600 hover:text-gray-900">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
            </svg>
          </Link>
          <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
              {{ import_.original_filename }}
            </h2>
            <p class="text-sm text-gray-500 mt-1">{{ import_.project.name }}</p>
          </div>
        </div>
        <span :class="getStatusClass(import_.status)">
          {{ import_.status_label }}
        </span>
      </div>
    </template>

    <div class="py-6">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <!-- Processing Status -->
        <div v-if="import_.is_processing" class="bg-blue-50 border border-blue-200 rounded-xl p-6">
          <div class="flex items-center space-x-4">
            <div class="flex-shrink-0">
              <svg class="animate-spin h-8 w-8 text-blue-600" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
            </div>
            <div class="flex-1">
              <h3 class="text-lg font-medium text-blue-900">DWG Dosyası İşleniyor</h3>
              <p class="text-sm text-blue-700 mt-1">
                Dosya Python script ile parse ediliyor. Bu işlem birkaç dakika sürebilir. Lütfen bekleyin...
              </p>
              <button
                @click="refreshPage"
                class="mt-3 inline-flex items-center px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-all"
              >
                <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                </svg>
                Sayfayı Yenile
              </button>
            </div>
          </div>
        </div>

        <!-- Error Display -->
        <div v-if="import_.is_failed" class="bg-red-50 border border-red-200 rounded-xl p-6">
          <div class="flex items-start space-x-4">
            <div class="flex-shrink-0">
              <svg class="h-8 w-8 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
              </svg>
            </div>
            <div class="flex-1">
              <h3 class="text-lg font-medium text-red-900">İşlem Başarısız</h3>
              <p class="text-sm text-red-700 mt-1">{{ import_.error_message }}</p>
              <details v-if="import_.error_details" class="mt-3">
                <summary class="text-sm text-red-700 cursor-pointer hover:text-red-800">Detaylı hata bilgisi</summary>
                <pre class="mt-2 text-xs text-red-600 bg-red-100 p-3 rounded">{{ JSON.stringify(import_.error_details, null, 2) }}</pre>
              </details>
            </div>
          </div>
        </div>

        <!-- File Info Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Dosya Bilgileri</h3>
          </div>
          <div class="p-6">
            <dl class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
              <div>
                <dt class="text-sm font-medium text-gray-500">Dosya Adı</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ import_.original_filename }}</dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500">Dosya Boyutu</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ import_.file_size_human }}</dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500">İmport Tipi</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ import_.import_type_label }}</dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500">Yükleyen</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ import_.uploader.name }}</dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500">Yüklenme Tarihi</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ import_.created_at }}</dd>
              </div>
              <div v-if="import_.started_at">
                <dt class="text-sm font-medium text-gray-500">İşlem Başlangıcı</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ import_.started_at }}</dd>
              </div>
              <div v-if="import_.completed_at">
                <dt class="text-sm font-medium text-gray-500">Tamamlanma</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ import_.completed_at }}</dd>
              </div>
              <div v-if="import_.processing_duration_seconds">
                <dt class="text-sm font-medium text-gray-500">İşlem Süresi</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ import_.processing_duration_seconds }} saniye</dd>
              </div>
            </dl>
            <div v-if="import_.notes" class="mt-6 pt-6 border-t border-gray-200">
              <dt class="text-sm font-medium text-gray-500 mb-2">Notlar</dt>
              <dd class="text-sm text-gray-900">{{ import_.notes }}</dd>
            </div>
          </div>
        </div>

        <!-- Layer Mapping Interface (Ready for Review) -->
        <div v-if="import_.is_ready_for_review && detectedLayers.length > 0" class="space-y-6">
          <!-- Instructions -->
          <div class="bg-gradient-to-r from-yellow-50 to-amber-50 border border-yellow-200 rounded-xl p-6">
            <div class="flex items-start space-x-4">
              <div class="flex-shrink-0">
                <svg class="w-6 h-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                </svg>
              </div>
              <div class="flex-1">
                <h3 class="text-lg font-medium text-yellow-900">Layer Eşleştirmesi Gerekli</h3>
                <p class="text-sm text-yellow-800 mt-1">
                  DWG dosyasından <strong>{{ detectedLayers.length }}</strong> adet layer tespit edildi.
                  Her layer için aşağıdaki seçeneklerden birini yapın:
                </p>
                <ul class="mt-3 text-sm text-yellow-700 space-y-1 list-disc list-inside">
                  <li><strong>Mevcut yapı/kat'a bağla:</strong> Projedeki mevcut bir yapı veya kata bağlayın</li>
                  <li><strong>Yeni oluştur:</strong> DWG'den gelen isimle yeni yapı/kat oluşturun</li>
                  <li><strong>Atla:</strong> Bu layer'ı import etmeyin</li>
                </ul>
              </div>
            </div>
          </div>

          <!-- Layer Mapping Cards -->
          <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-blue-50 to-cyan-50 border-b border-gray-200">
              <h3 class="text-lg font-semibold text-gray-900">Tespit Edilen Layer'lar ve Eşleştirmeler</h3>
              <p class="text-sm text-gray-600 mt-1">Hiyerarşik yapı: Yapılar → Katlar → Birimler</p>
            </div>
            <div class="p-6 space-y-4">
              <div
                v-for="(layer, index) in detectedLayers"
                :key="index"
                :class="[
                  'border border-gray-200 rounded-lg overflow-hidden',
                  layer.type === 'structure' ? 'ml-0 border-l-4 border-purple-500' :
                  layer.type === 'floor' ? 'ml-8 border-l-4 border-blue-500' :
                  'ml-16 border-l-4 border-teal-500'
                ]"
              >
                <div :class="[
                  'px-4 py-3 border-b border-gray-200 flex items-center justify-between',
                  layer.type === 'structure' ? 'bg-purple-50' :
                  layer.type === 'floor' ? 'bg-blue-50' :
                  'bg-teal-50'
                ]">
                  <div class="flex items-center space-x-3">
                    <span
                      :class="[
                        'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                        layer.type === 'structure' ? 'bg-purple-100 text-purple-800' :
                        layer.type === 'floor' ? 'bg-blue-100 text-blue-800' :
                        'bg-teal-100 text-teal-800'
                      ]"
                    >
                      {{ layer.type === 'structure' ? 'Yapı' : layer.type === 'floor' ? 'Kat' : 'Birim' }}
                    </span>
                    <h4 class="text-sm font-medium text-gray-900">{{ layer.source_name }}</h4>
                    <span v-if="layer.item_count > 1" class="text-xs text-gray-500">({{ layer.item_count }} adet)</span>
                    <span v-if="layer.type === 'unit' && (layer.gross_area || layer.total_gross_area)" class="text-xs font-medium text-teal-600">
                      <template v-if="layer.item_count > 1">
                        Toplam: {{ Math.round(layer.total_gross_area) }} m² brüt
                      </template>
                      <template v-else>
                        {{ Math.round(layer.gross_area) }} m² brüt
                      </template>
                    </span>
                  </div>
                  <span v-if="layer.description" class="text-xs text-gray-500">{{ layer.description }}</span>
                </div>
                <div class="p-4">
                  <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                    <!-- Option 1: Link to Existing -->
                    <label
                      :class="[
                        'relative flex cursor-pointer rounded-lg border p-4 transition-all',
                        mappings[index]?.action === 'link'
                          ? 'border-blue-500 bg-blue-50 ring-2 ring-blue-500'
                          : 'border-gray-300 hover:border-gray-400'
                      ]"
                    >
                      <input
                        type="radio"
                        :name="`action-${index}`"
                        value="link"
                        v-model="mappings[index].action"
                        @change="updateMapping(index, 'link')"
                        class="sr-only"
                      />
                      <div class="flex flex-col flex-1">
                        <span class="block text-sm font-medium text-gray-900 mb-2">Mevcut'a Bağla</span>

                        <!-- Structure Selection -->
                        <div v-if="layer.type === 'structure'" class="space-y-2">
                          <select
                            v-model="mappings[index].existing_structure_id"
                            @change="updateMapping(index, 'link')"
                            class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500"
                            :disabled="mappings[index]?.action !== 'link'"
                          >
                            <option value="">Yapı Seçin</option>
                            <option v-for="structure in projectStructures" :key="structure.id" :value="structure.id">
                              {{ structure.name }}
                            </option>
                          </select>
                        </div>

                        <!-- Floor Selection -->
                        <div v-else-if="layer.type === 'floor'" class="space-y-2">
                          <select
                            v-model="mappings[index].existing_structure_id"
                            @change="updateMapping(index, 'link')"
                            class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500"
                            :disabled="mappings[index]?.action !== 'link'"
                          >
                            <option value="">Önce Yapı Seçin</option>
                            <option v-for="structure in projectStructures" :key="structure.id" :value="structure.id">
                              {{ structure.name }}
                            </option>
                          </select>
                          <select
                            v-if="mappings[index].existing_structure_id"
                            v-model="mappings[index].existing_floor_id"
                            @change="updateMapping(index, 'link')"
                            class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500"
                            :disabled="mappings[index]?.action !== 'link'"
                          >
                            <option value="">Kat Seçin</option>
                            <option
                              v-for="floor in projectFloors[mappings[index].existing_structure_id]"
                              :key="floor.id"
                              :value="floor.id"
                            >
                              {{ floor.name }}
                            </option>
                          </select>
                        </div>

                        <!-- Unit Selection -->
                        <div v-else class="space-y-2">
                          <select
                            v-model="mappings[index].existing_floor_id"
                            @change="updateMapping(index, 'link')"
                            class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500"
                            :disabled="mappings[index]?.action !== 'link'"
                          >
                            <option value="">Kat Seçin</option>
                            <template v-for="structure in projectStructures" :key="structure.id">
                              <optgroup :label="structure.name">
                                <option
                                  v-for="floor in projectFloors[structure.id]"
                                  :key="floor.id"
                                  :value="floor.id"
                                >
                                  {{ floor.name }}
                                </option>
                              </optgroup>
                            </template>
                          </select>
                        </div>
                      </div>
                      <svg
                        v-if="mappings[index]?.action === 'link'"
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

                    <!-- Option 2: Create New -->
                    <label
                      :class="[
                        'relative flex cursor-pointer rounded-lg border p-4 transition-all',
                        mappings[index]?.action === 'create'
                          ? 'border-green-500 bg-green-50 ring-2 ring-green-500'
                          : 'border-gray-300 hover:border-gray-400'
                      ]"
                    >
                      <input
                        type="radio"
                        :name="`action-${index}`"
                        value="create"
                        v-model="mappings[index].action"
                        @change="updateMapping(index, 'create')"
                        class="sr-only"
                      />
                      <div class="flex flex-col flex-1">
                        <span class="block text-sm font-medium text-gray-900 mb-2">Yeni Oluştur</span>
                        <input
                          type="text"
                          v-model="mappings[index].target_name"
                          @input="updateMapping(index, 'create')"
                          :placeholder="`${layer.source_name} (DWG'den)`"
                          class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 focus:ring-2 focus:ring-green-500"
                          :disabled="mappings[index]?.action !== 'create'"
                        />
                        <p class="text-xs text-gray-500 mt-2">DWG'den gelen isimle yeni kayıt oluşturulacak</p>
                      </div>
                      <svg
                        v-if="mappings[index]?.action === 'create'"
                        class="h-5 w-5 text-green-600"
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

                    <!-- Option 3: Skip -->
                    <label
                      :class="[
                        'relative flex cursor-pointer rounded-lg border p-4 transition-all',
                        mappings[index]?.action === 'skip'
                          ? 'border-gray-500 bg-gray-50 ring-2 ring-gray-500'
                          : 'border-gray-300 hover:border-gray-400'
                      ]"
                    >
                      <input
                        type="radio"
                        :name="`action-${index}`"
                        value="skip"
                        v-model="mappings[index].action"
                        @change="updateMapping(index, 'skip')"
                        class="sr-only"
                      />
                      <div class="flex flex-col flex-1">
                        <span class="block text-sm font-medium text-gray-900 mb-2">Atla</span>
                        <p class="text-sm text-gray-600">Bu layer import edilmeyecek</p>
                      </div>
                      <svg
                        v-if="mappings[index]?.action === 'skip'"
                        class="h-5 w-5 text-gray-600"
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
            </div>
          </div>

          <!-- Actions -->
          <div class="flex items-center justify-end space-x-4">
            <button
              @click="saveMappings"
              :disabled="savingMappings"
              class="px-6 py-3 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition-all inline-flex items-center"
            >
              <svg v-if="savingMappings" class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              {{ savingMappings ? 'Kaydediliyor...' : 'Eşleştirmeleri Kaydet' }}
            </button>
            <button
              @click="approveImport"
              :disabled="!canApprove || approvingImport"
              :class="[
                'px-6 py-3 text-white text-sm font-medium rounded-lg transition-all inline-flex items-center',
                canApprove && !approvingImport
                  ? 'bg-green-600 hover:bg-green-700'
                  : 'bg-gray-400 cursor-not-allowed'
              ]"
            >
              <svg v-if="approvingImport" class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              {{ approvingImport ? 'Onaylanıyor...' : 'Onayla ve Kayıtları Oluştur' }}
            </button>
          </div>
        </div>

        <!-- Completed Results -->
        <div v-if="import_.is_completed" class="bg-green-50 border border-green-200 rounded-xl p-6">
          <div class="flex items-start space-x-4">
            <div class="flex-shrink-0">
              <svg class="h-8 w-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <div class="flex-1">
              <h3 class="text-lg font-medium text-green-900">İçe Aktarım Tamamlandı</h3>
              <p class="text-sm text-green-700 mt-1">DWG dosyasından aşağıdaki kayıtlar başarıyla oluşturuldu:</p>
              <div class="mt-4 grid grid-cols-3 gap-4">
                <div class="bg-white rounded-lg p-4 border border-green-200">
                  <div class="text-3xl font-bold text-purple-600">{{ import_.structures_count }}</div>
                  <div class="text-sm text-gray-600">Yapı</div>
                </div>
                <div class="bg-white rounded-lg p-4 border border-green-200">
                  <div class="text-3xl font-bold text-blue-600">{{ import_.floors_count }}</div>
                  <div class="text-sm text-gray-600">Kat</div>
                </div>
                <div class="bg-white rounded-lg p-4 border border-green-200">
                  <div class="text-3xl font-bold text-teal-600">{{ import_.units_count }}</div>
                  <div class="text-sm text-gray-600">Birim</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, reactive, computed, watch, onUnmounted } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  import: Object,
  project_structures: Array,
  project_floors: Object,
})

// Rename to avoid conflict with JS 'import' keyword
const import_ = computed(() => props.import)
const projectStructures = computed(() => props.project_structures || [])
const projectFloors = computed(() => props.project_floors || {})

// Detected layers from parsed data
const detectedLayers = computed(() => import_.value?.detected_layers || [])

// Mappings state
const mappings = ref([])
const savingMappings = ref(false)
const approvingImport = ref(false)

// Auto-refresh polling
let pollingInterval = null

// Initialize mappings when detectedLayers changes
watch(
  () => import_.value,
  (newImport) => {
    if (!newImport) return

    if (newImport.layer_mappings && newImport.layer_mappings.length > 0) {
      mappings.value = newImport.layer_mappings
    } else if (newImport.detected_layers && newImport.detected_layers.length > 0) {
      mappings.value = newImport.detected_layers.map(layer => ({
        ...layer,
        action: 'create',
        target_name: layer.source_name,
        existing_structure_id: null,
        existing_floor_id: null,
      }))
    }
  },
  { immediate: true, deep: true }
)

// Auto-refresh when processing
watch(
  () => import_.value?.status,
  (status) => {
    if (status === 'processing' || status === 'pending') {
      // Start polling every 3 seconds
      if (!pollingInterval) {
        pollingInterval = setInterval(() => {
          router.reload({ only: ['import'] })
        }, 3000)
      }
    } else {
      // Stop polling when processing is complete
      if (pollingInterval) {
        clearInterval(pollingInterval)
        pollingInterval = null
      }
    }
  },
  { immediate: true }
)

// Cleanup on unmount
onUnmounted(() => {
  if (pollingInterval) {
    clearInterval(pollingInterval)
    pollingInterval = null
  }
})

// Group layers by type
const groupedLayers = computed(() => {
  const groups = {
    structure: [],
    floor: [],
    unit: []
  }

  detectedLayers.value.forEach((layer, index) => {
    groups[layer.type].push({ ...layer, originalIndex: index })
  })

  return groups
})

// Computed
const canApprove = computed(() => {
  return mappings.value.every(m => {
    if (m.action === 'skip') return true
    if (m.action === 'create') return m.target_name && m.target_name.trim().length > 0
    if (m.action === 'link') {
      if (m.type === 'structure') return m.existing_structure_id
      if (m.type === 'floor') return m.existing_structure_id && m.existing_floor_id
      if (m.type === 'unit') return m.existing_floor_id
    }
    return false
  })
})

// Methods
const getStatusClass = (status) => {
  const classes = {
    pending: 'inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium bg-gray-100 text-gray-800',
    processing: 'inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium bg-blue-100 text-blue-800',
    ready_for_review: 'inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800',
    completed: 'inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium bg-green-100 text-green-800',
    failed: 'inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium bg-red-100 text-red-800',
  }
  return classes[status] || classes.pending
}

const refreshPage = () => {
  router.reload()
}

const updateMapping = (index, action) => {
  // Reset fields based on action
  if (action === 'link') {
    mappings.value[index].target_name = null
  } else if (action === 'create') {
    mappings.value[index].existing_structure_id = null
    mappings.value[index].existing_floor_id = null
    if (!mappings.value[index].target_name) {
      mappings.value[index].target_name = mappings.value[index].source_name
    }
  } else if (action === 'skip') {
    mappings.value[index].target_name = null
    mappings.value[index].existing_structure_id = null
    mappings.value[index].existing_floor_id = null
  }
}

const saveMappings = () => {
  savingMappings.value = true

  router.post(
    route('dwg-imports.update-mappings', import_.value.id),
    { layer_mappings: mappings.value },
    {
      preserveScroll: true,
      onFinish: () => {
        savingMappings.value = false
      },
    }
  )
}

const approveImport = () => {
  if (!canApprove.value) return

  approvingImport.value = true

  // First save mappings if needed, then approve
  router.post(
    route('dwg-imports.approve', import_.value.id),
    { layer_mappings: mappings.value },
    {
      onFinish: () => {
        approvingImport.value = false
      },
    }
  )
}
</script>
