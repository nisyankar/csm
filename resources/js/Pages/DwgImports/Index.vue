<template>
  <AppLayout title="AutoCAD DWG İçe Aktarım" :full-width="true">
    <!-- Full-width header -->
    <template #fullWidthHeader>
      <div class="bg-gradient-to-r from-blue-600 via-cyan-600 to-teal-600 border-b border-blue-900/20 w-full">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
          <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <!-- Header Info -->
            <div class="flex-1 min-w-0">
              <div class="flex items-center space-x-4 mb-3">
                <div class="flex-shrink-0 w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm">
                  <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                  </svg>
                </div>
                <div>
                  <h1 class="text-2xl lg:text-3xl font-bold text-white">AutoCAD DWG İçe Aktarım</h1>
                  <p class="text-blue-100 text-sm mt-1">DWG/DXF dosyalarından proje yapısını otomatik oluşturma</p>
                </div>
              </div>

              <!-- Stats Row -->
              <div class="flex flex-wrap items-center gap-4 lg:gap-6">
                <div class="bg-white/10 backdrop-blur-sm rounded-lg px-3 py-2">
                  <span class="text-blue-100 text-sm">Toplam İmport:</span>
                  <span class="font-semibold text-white ml-1">{{ imports.total || 0 }}</span>
                </div>
                <div class="flex items-center space-x-4 text-sm">
                  <span class="flex items-center text-blue-100">
                    <div class="w-2 h-2 bg-yellow-400 rounded-full mr-2"></div>
                    İnceleme Bekliyor: <span class="text-white font-medium ml-1">{{ countByStatus('ready_for_review') }}</span>
                  </span>
                  <span class="flex items-center text-blue-100">
                    <div class="w-2 h-2 bg-blue-400 rounded-full mr-2"></div>
                    İşleniyor: <span class="text-white font-medium ml-1">{{ countByStatus('processing') }}</span>
                  </span>
                  <span class="flex items-center text-blue-100">
                    <div class="w-2 h-2 bg-green-400 rounded-full mr-2"></div>
                    Tamamlandı: <span class="text-white font-medium ml-1">{{ countByStatus('completed') }}</span>
                  </span>
                </div>
              </div>
            </div>

            <!-- Action Button -->
            <div class="flex-shrink-0">
              <Link
                :href="route('dwg-imports.create')"
                class="inline-flex items-center px-4 py-2 bg-white text-blue-600 text-sm font-medium rounded-lg hover:bg-blue-50 shadow-lg hover:shadow-xl transition-all duration-200"
              >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                </svg>
                DWG Dosyası Yükle
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
                  <Link :href="route('dashboard')" class="text-blue-100 hover:text-white transition-colors">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                    </svg>
                  </Link>
                </li>
                <li class="flex items-center">
                  <svg class="h-3 w-3 text-blue-200 mx-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                  </svg>
                  <span class="text-white font-medium text-sm">AutoCAD DWG İçe Aktarım</span>
                </li>
              </ol>
            </div>
          </div>
        </div>
      </div>
    </template>

    <!-- Main Content -->
    <div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
      <!-- Filters -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden">
        <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
          <h3 class="text-lg font-semibold text-gray-900">Filtreler</h3>
        </div>
        <div class="p-6">
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Proje</label>
              <select
                v-model="filters.project_id"
                @change="applyFilters"
                class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
              >
                <option value="">Tümü</option>
                <option v-for="project in projects" :key="project.id" :value="project.id">
                  {{ project.name }}
                </option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Durum</label>
              <select
                v-model="filters.status"
                @change="applyFilters"
                class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
              >
                <option value="">Tümü</option>
                <option value="pending">Beklemede</option>
                <option value="processing">İşleniyor</option>
                <option value="ready_for_review">İnceleme Bekliyor</option>
                <option value="completed">Tamamlandı</option>
                <option value="failed">Başarısız</option>
              </select>
            </div>

            <div class="flex items-end">
              <button
                @click="clearFilters"
                class="w-full px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition-all"
              >
                Filtreleri Temizle
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Imports List -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
              <tr>
                <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                  Dosya / Proje
                </th>
                <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                  İmport Tipi
                </th>
                <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                  Durum
                </th>
                <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                  İstatistikler
                </th>
                <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                  Tarih
                </th>
                <th scope="col" class="px-6 py-4 text-right text-xs font-medium text-gray-700 uppercase tracking-wider">
                  İşlemler
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-if="imports.data.length === 0">
                <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                  <svg class="mx-auto h-12 w-12 text-gray-400 mb-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                  </svg>
                  Henüz DWG import kaydı bulunmuyor
                </td>
              </tr>
              <tr v-for="importRecord in imports.data" :key="importRecord.id" class="hover:bg-gray-50 transition-colors">
                <td class="px-6 py-4">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 h-10 w-10 bg-blue-100 rounded-lg flex items-center justify-center">
                      <svg class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                      </svg>
                    </div>
                    <div class="ml-4">
                      <div class="text-sm font-medium text-gray-900">{{ importRecord.original_filename }}</div>
                      <div class="text-sm text-gray-500">{{ importRecord.project.name }}</div>
                      <div class="text-xs text-gray-400 mt-1">{{ importRecord.file_size_human }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-cyan-100 text-cyan-800">
                    {{ importRecord.import_type_label }}
                  </span>
                </td>
                <td class="px-6 py-4">
                  <span :class="getStatusClass(importRecord.status)">
                    {{ importRecord.status_label }}
                  </span>
                  <div v-if="importRecord.error_message" class="text-xs text-red-600 mt-1">
                    {{ importRecord.error_message }}
                  </div>
                </td>
                <td class="px-6 py-4">
                  <div v-if="importRecord.status === 'completed'" class="space-y-1">
                    <div class="text-xs text-gray-600">
                      <span class="font-medium text-teal-600">{{ importRecord.structures_count }}</span> yapı
                    </div>
                    <div class="text-xs text-gray-600">
                      <span class="font-medium text-teal-600">{{ importRecord.floors_count }}</span> kat
                    </div>
                    <div class="text-xs text-gray-600">
                      <span class="font-medium text-teal-600">{{ importRecord.units_count }}</span> birim
                    </div>
                  </div>
                  <div v-else class="text-xs text-gray-400">-</div>
                </td>
                <td class="px-6 py-4">
                  <div class="text-sm text-gray-900">{{ importRecord.created_at }}</div>
                  <div v-if="importRecord.completed_at" class="text-xs text-gray-500 mt-1">
                    Tamamlanma: {{ importRecord.completed_at }}
                  </div>
                </td>
                <td class="px-6 py-4 text-right text-sm font-medium space-x-2">
                  <Link
                    :href="route('dwg-imports.show', importRecord.id)"
                    class="inline-flex items-center px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium rounded-lg transition-all"
                  >
                    <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                      <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Detay
                  </Link>
                  <button
                    v-if="importRecord.status !== 'processing'"
                    @click="confirmDelete(importRecord)"
                    class="inline-flex items-center px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-xs font-medium rounded-lg transition-all"
                  >
                    <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                    </svg>
                    Sil
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="imports.data.length > 0" class="px-6 py-4 border-t border-gray-200 bg-gray-50">
          <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="text-sm text-gray-700">
              Toplam <span class="font-medium">{{ imports.total }}</span> kayıt bulundu
            </div>
            <div class="flex items-center space-x-2">
              <template v-for="link in imports.links" :key="link.label">
                <Link
                  v-if="link.url"
                  :href="link.url"
                  :class="[
                    'px-3 py-2 text-sm font-medium rounded-lg transition-all',
                    link.active
                      ? 'bg-blue-600 text-white'
                      : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-300'
                  ]"
                  v-html="link.label"
                />
                <span
                  v-else
                  :class="'px-3 py-2 text-sm font-medium rounded-lg transition-all bg-gray-100 text-gray-400 cursor-not-allowed'"
                  v-html="link.label"
                />
              </template>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <Modal :show="deleteModal.show" @close="deleteModal.show = false">
      <div class="p-6">
        <div class="flex items-center justify-center w-12 h-12 mx-auto bg-red-100 rounded-full mb-4">
          <svg class="w-6 h-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
          </svg>
        </div>
        <h3 class="text-lg font-medium text-gray-900 text-center mb-2">DWG Import Kaydını Sil</h3>
        <p class="text-sm text-gray-500 text-center mb-6">
          <strong>{{ deleteModal.import?.original_filename }}</strong> dosyasının import kaydını silmek istediğinizden emin misiniz?
          Bu işlem geri alınamaz.
        </p>
        <div class="flex space-x-3">
          <button
            @click="deleteModal.show = false"
            class="flex-1 px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition-all"
          >
            İptal
          </button>
          <button
            @click="deleteImport"
            class="flex-1 px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-all"
          >
            Sil
          </button>
        </div>
      </div>
    </Modal>
  </AppLayout>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Modal from '@/Components/UI/Modal.vue'

const props = defineProps({
  imports: Object,
  projects: Array,
  filters: Object,
})

// Filters
const filters = reactive({
  project_id: props.filters?.project_id || '',
  status: props.filters?.status || '',
})

// Delete modal
const deleteModal = reactive({
  show: false,
  import: null,
})

// Methods
const applyFilters = () => {
  router.get(route('dwg-imports.index'), filters, {
    preserveState: true,
    preserveScroll: true,
  })
}

const clearFilters = () => {
  filters.project_id = ''
  filters.status = ''
  applyFilters()
}

const countByStatus = (status) => {
  return props.imports.data.filter(imp => imp.status === status).length
}

const getStatusClass = (status) => {
  const classes = {
    pending: 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800',
    processing: 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800',
    ready_for_review: 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800',
    completed: 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800',
    failed: 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800',
  }
  return classes[status] || classes.pending
}

const confirmDelete = (importRecord) => {
  deleteModal.import = importRecord
  deleteModal.show = true
}

const deleteImport = () => {
  router.delete(route('dwg-imports.destroy', deleteModal.import.id), {
    onSuccess: () => {
      deleteModal.show = false
      deleteModal.import = null
    },
  })
}
</script>
