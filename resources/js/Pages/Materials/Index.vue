<template>
  <AppLayout
    title="Malzeme Yönetimi - SPT İnşaat Puantaj Sistemi"
    :full-width="true"
  >
    <!-- Full-width header -->
    <template #fullWidthHeader>
      <div class="bg-gradient-to-r from-purple-600 via-purple-700 to-purple-800 border-b border-purple-900/20 w-full">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
          <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <!-- Header Info -->
            <div class="flex-1 min-w-0">
              <div class="flex items-center space-x-4 mb-3">
                <div class="flex-shrink-0 w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm">
                  <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                  </svg>
                </div>
                <div>
                  <h1 class="text-2xl lg:text-3xl font-bold text-white">Malzeme Yönetimi</h1>
                  <p class="text-purple-100 text-sm mt-1">Satınalma malzemelerini yönetin</p>
                </div>
              </div>

              <!-- Stats Row -->
              <div class="flex flex-wrap items-center gap-4 lg:gap-6">
                <div class="bg-white/10 backdrop-blur-sm rounded-lg px-3 py-2">
                  <span class="text-purple-100 text-sm">Toplam Malzeme:</span>
                  <span class="font-semibold text-white ml-1">{{ materialsData.total || 0 }}</span>
                </div>
                <div class="flex items-center space-x-4 text-sm">
                  <span class="flex items-center text-purple-100">
                    <div class="w-2 h-2 bg-green-400 rounded-full mr-2"></div>
                    Aktif: <span class="text-white font-medium ml-1">{{ getActiveCount() }}</span>
                  </span>
                  <span class="flex items-center text-purple-100">
                    <div class="w-2 h-2 bg-gray-400 rounded-full mr-2"></div>
                    Pasif: <span class="text-white font-medium ml-1">{{ getInactiveCount() }}</span>
                  </span>
                </div>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex-shrink-0">
              <Link
                :href="route('materials.create')"
                class="inline-flex items-center px-4 py-2 bg-white text-purple-600 text-sm font-medium rounded-lg hover:bg-purple-50 shadow-lg hover:shadow-xl transition-all duration-200"
              >
                <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Yeni Malzeme
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
                  <Link
                    :href="route('dashboard')"
                    class="text-purple-100 hover:text-white transition-colors"
                  >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                    </svg>
                    <span class="sr-only">Ana Sayfa</span>
                  </Link>
                </li>
                <li class="flex items-center">
                  <svg class="h-3 w-3 text-purple-200 mx-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                  </svg>
                  <span class="text-xs font-medium text-white">Malzemeler</span>
                </li>
              </ol>
            </div>
          </div>
        </div>
      </div>
    </template>

    <!-- Main Content Container -->
    <div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
      <!-- Filters Panel -->
      <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
          <h3 class="text-lg font-medium text-gray-900">Arama ve Filtreler</h3>
        </div>

        <div class="p-6">
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Search -->
            <div class="lg:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-2">Genel Arama</label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                  </svg>
                </div>
                <input
                  v-model="filters.search"
                  @input="debouncedSearch"
                  type="text"
                  placeholder="Malzeme adı, kodu ara..."
                  class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                />
              </div>
            </div>

            <!-- Category Filter -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Kategori</label>
              <select
                v-model="filters.category"
                @change="applyFilters"
                class="block w-full px-3 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
              >
                <option value="">Tümü</option>
                <option v-for="category in categories" :key="category" :value="category">
                  {{ category }}
                </option>
              </select>
            </div>

            <!-- Active/Inactive Filter -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Durum</label>
              <select
                v-model="filters.is_active"
                @change="applyFilters"
                class="block w-full px-3 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
              >
                <option value="">Tümü</option>
                <option value="true">Aktif</option>
                <option value="false">Pasif</option>
              </select>
            </div>
          </div>

          <!-- Clear Filters Button -->
          <div class="mt-4 flex justify-end">
            <button
              @click="clearFilters"
              class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-purple-500"
            >
              <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
              </svg>
              Filtreleri Temizle
            </button>
          </div>
        </div>
      </div>

      <!-- Materials Table -->
      <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Malzeme Adı
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Kod
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Kategori
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Birim
                </th>
                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Tahmini Fiyat
                </th>
                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Durum
                </th>
                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                  İşlemler
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-if="materialsData.data?.length === 0">
                <td colspan="7" class="px-6 py-12 text-center">
                  <div class="flex flex-col items-center justify-center">
                    <svg class="h-12 w-12 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                    </svg>
                    <p class="text-gray-500 text-sm">Henüz malzeme bulunmuyor</p>
                    <Link
                      :href="route('materials.create')"
                      class="mt-4 inline-flex items-center px-4 py-2 bg-purple-600 text-white text-sm font-medium rounded-lg hover:bg-purple-700 transition-colors"
                    >
                      <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                      </svg>
                      İlk Malzemeyi Ekle
                    </Link>
                  </div>
                </td>
              </tr>
              <tr v-for="material in materialsData.data" :key="material.id" class="hover:bg-gray-50 transition-colors">
                <td class="px-6 py-4">
                  <div class="flex items-center">
                    <div>
                      <div class="text-sm font-medium text-gray-900">{{ material.name }}</div>
                      <div v-if="material.description" class="text-sm text-gray-500 truncate max-w-xs">
                        {{ material.description }}
                      </div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span v-if="material.material_code" class="text-sm text-gray-900 font-mono">
                    {{ material.material_code }}
                  </span>
                  <span v-else class="text-sm text-gray-400">-</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    {{ material.category || 'Diğer' }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ material.unit }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-gray-900">
                  {{ formatPrice(material.estimated_unit_price) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center">
                  <span
                    :class="[
                      'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                      material.is_active
                        ? 'bg-green-100 text-green-800'
                        : 'bg-gray-100 text-gray-800'
                    ]"
                  >
                    {{ material.is_active ? 'Aktif' : 'Pasif' }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <div class="flex items-center justify-end space-x-2">
                    <Link
                      :href="route('materials.edit', material.id)"
                      class="inline-flex items-center px-3 py-1.5 bg-blue-50 text-blue-600 text-xs font-medium rounded-md hover:bg-blue-100 transition-colors"
                    >
                      <svg class="h-3.5 w-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                      </svg>
                      Düzenle
                    </Link>
                    <button
                      v-if="$page.props.auth?.user?.role === 'admin' || $page.props.auth?.user?.role === 'hr'"
                      @click="confirmDelete(material)"
                      class="inline-flex items-center px-3 py-1.5 bg-red-50 text-red-600 text-xs font-medium rounded-md hover:bg-red-100 transition-colors"
                    >
                      <svg class="h-3.5 w-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                      </svg>
                      Sil
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="materialsData.data?.length > 0" class="px-6 py-4 border-t border-gray-200 bg-gray-50">
          <div class="flex items-center justify-between">
            <div class="text-sm text-gray-700">
              <span class="font-medium">{{ materialsData.from || 0 }}</span>
              -
              <span class="font-medium">{{ materialsData.to || 0 }}</span>
              arası gösteriliyor, toplam
              <span class="font-medium">{{ materialsData.total || 0 }}</span>
              kayıt
            </div>
            <div class="flex space-x-2">
              <component
                :is="link.url ? Link : 'span'"
                v-for="(link, index) in materialsData.links"
                :key="index"
                :href="link.url || undefined"
                :class="[
                  'px-3 py-2 text-sm font-medium rounded-md transition-colors',
                  link.active
                    ? 'bg-purple-600 text-white'
                    : link.url
                    ? 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-300'
                    : 'bg-gray-100 text-gray-400 cursor-not-allowed'
                ]"
                v-html="link.label"
              />
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteModal" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="showDeleteModal = false"></div>

        <div class="relative inline-block px-4 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
          <div class="sm:flex sm:items-start">
            <div class="flex items-center justify-center flex-shrink-0 w-12 h-12 mx-auto bg-red-100 rounded-full sm:mx-0 sm:h-10 sm:w-10">
              <svg class="w-6 h-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
              </svg>
            </div>
            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
              <h3 class="text-lg font-medium leading-6 text-gray-900">Malzemeyi Sil</h3>
              <div class="mt-2">
                <p class="text-sm text-gray-500">
                  "<span class="font-medium">{{ materialToDelete?.name }}</span>" malzemesini silmek istediğinizden emin misiniz?
                  Bu işlem geri alınamaz.
                </p>
              </div>
            </div>
          </div>
          <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
            <button
              type="button"
              @click="deleteMaterial"
              class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-white bg-red-600 border border-transparent rounded-md shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm"
            >
              Sil
            </button>
            <button
              type="button"
              @click="showDeleteModal = false"
              class="inline-flex justify-center w-full px-4 py-2 mt-3 text-base font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 sm:mt-0 sm:w-auto sm:text-sm"
            >
              İptal
            </button>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { debounce } from 'lodash'

const props = defineProps({
  materials: {
    type: Object,
    default: () => ({ data: [], total: 0, from: 0, to: 0, links: [] })
  },
  filters: {
    type: Object,
    default: () => ({})
  },
  categories: {
    type: Array,
    default: () => []
  }
})

const filters = ref({
  search: props.filters.search || '',
  category: props.filters.category || '',
  is_active: props.filters.is_active || ''
})

const showDeleteModal = ref(false)
const materialToDelete = ref(null)

const materialsData = computed(() => {
  if (props.materials && typeof props.materials === 'object' && props.materials.data) {
    return props.materials
  }
  return {
    data: [],
    total: 0,
    from: 0,
    to: 0,
    links: []
  }
})

const getActiveCount = () => {
  return materialsData.value.data?.filter(m => m.is_active).length || 0
}

const getInactiveCount = () => {
  return materialsData.value.data?.filter(m => !m.is_active).length || 0
}

const formatPrice = (price) => {
  return new Intl.NumberFormat('tr-TR', {
    style: 'currency',
    currency: 'TRY'
  }).format(price || 0)
}

const applyFilters = () => {
  router.get(route('materials.index'), filters.value, {
    preserveState: true,
    preserveScroll: true
  })
}

const debouncedSearch = debounce(() => {
  applyFilters()
}, 500)

const clearFilters = () => {
  filters.value = {
    search: '',
    category: '',
    is_active: ''
  }
  applyFilters()
}

const confirmDelete = (material) => {
  materialToDelete.value = material
  showDeleteModal.value = true
}

const deleteMaterial = () => {
  if (materialToDelete.value) {
    router.delete(route('materials.destroy', materialToDelete.value.id), {
      onSuccess: () => {
        showDeleteModal.value = false
        materialToDelete.value = null
      }
    })
  }
}
</script>
