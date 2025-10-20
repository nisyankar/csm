<template>
  <AppLayout
    title="Proje Yönetimi - SPT İnşaat Puantaj Sistemi"
    :full-width="true"
  >
    <!-- Full-width header -->
    <template #fullWidthHeader>
      <div class="bg-gradient-to-r from-cyan-600 via-cyan-700 to-teal-800 border-b border-cyan-900/20 w-full">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
          <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <!-- Header Info -->
            <div class="flex-1 min-w-0">
              <div class="flex items-center space-x-4 mb-3">
                <div class="flex-shrink-0 w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm">
                  <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008z" />
                  </svg>
                </div>
                <div>
                  <h1 class="text-2xl lg:text-3xl font-bold text-white">Proje Yönetimi</h1>
                  <p class="text-cyan-100 text-sm mt-1">Projeleri görüntüleyin ve yönetin</p>
                </div>
              </div>

              <!-- Stats Row -->
              <div class="flex flex-wrap items-center gap-4 lg:gap-6">
                <div class="bg-white/10 backdrop-blur-sm rounded-lg px-3 py-2">
                  <span class="text-cyan-100 text-sm">Toplam Proje:</span>
                  <span class="font-semibold text-white ml-1">{{ stats?.total || 0 }}</span>
                </div>
                <div class="flex items-center space-x-4 text-sm">
                  <span class="flex items-center text-cyan-100">
                    <div class="w-2 h-2 bg-green-400 rounded-full mr-2"></div>
                    Aktif: <span class="text-white font-medium ml-1">{{ stats?.active || 0 }}</span>
                  </span>
                  <span class="flex items-center text-cyan-100">
                    <div class="w-2 h-2 bg-yellow-400 rounded-full mr-2"></div>
                    Planlama: <span class="text-white font-medium ml-1">{{ stats?.planning || 0 }}</span>
                  </span>
                  <span class="flex items-center text-cyan-100">
                    <div class="w-2 h-2 bg-blue-400 rounded-full mr-2"></div>
                    Tamamlanan: <span class="text-white font-medium ml-1">{{ stats?.completed || 0 }}</span>
                  </span>
                </div>
              </div>
            </div>

            <!-- Action Button -->
            <div class="flex-shrink-0">
              <Link
                :href="route('projects.create')"
                class="inline-flex items-center px-4 py-2 bg-white text-cyan-600 text-sm font-medium rounded-lg hover:bg-cyan-50 shadow-lg hover:shadow-xl transition-all duration-200"
              >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Yeni Proje
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
                    class="text-cyan-100 hover:text-white transition-colors"
                  >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                    </svg>
                    <span class="sr-only">Ana Sayfa</span>
                  </Link>
                </li>
                <li class="flex items-center">
                  <svg class="h-3 w-3 text-cyan-200 mx-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                  </svg>
                  <span class="text-xs font-medium text-white">Proje Yönetimi</span>
                </li>
              </ol>
            </div>
          </div>
        </div>
      </div>
    </template>

    <!-- Main Content -->
    <div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
      <!-- Filters Panel -->
      <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
          <h3 class="text-lg font-medium text-gray-900">Arama ve Filtreler</h3>
        </div>

        <div class="p-6">
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
            <!-- Search -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Ara</label>
              <input
                v-model="filters.search"
                @input="handleFilter"
                type="text"
                placeholder="Proje adı, kod, müşteri..."
                class="block w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500"
              />
            </div>

            <!-- Status Filter -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Durum</label>
              <select
                v-model="filters.status"
                @change="handleFilter"
                class="block w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500"
              >
                <option value="">Tümü</option>
                <option value="planning">Planlama</option>
                <option value="active">Aktif</option>
                <option value="on_hold">Beklemede</option>
                <option value="completed">Tamamlandı</option>
                <option value="cancelled">İptal Edildi</option>
              </select>
            </div>

            <!-- Type Filter -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Tür</label>
              <select
                v-model="filters.type"
                @change="handleFilter"
                class="block w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500"
              >
                <option value="">Tümü</option>
                <option value="residential">Konut</option>
                <option value="commercial">Ticari</option>
                <option value="infrastructure">Altyapı</option>
                <option value="industrial">Endüstriyel</option>
                <option value="other">Diğer</option>
              </select>
            </div>

            <!-- City Filter -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Şehir</label>
              <select
                v-model="filters.city"
                @change="handleFilter"
                class="block w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500"
              >
                <option value="">Tümü</option>
                <option v-for="city in cities" :key="city" :value="city">
                  {{ city }}
                </option>
              </select>
            </div>
          </div>

          <!-- Special Filters -->
          <div class="flex flex-wrap gap-2">
            <button
              @click="setSpecialFilter('delayed')"
              :class="[
                'px-3 py-1.5 rounded-lg text-sm font-medium transition-colors',
                filters.filter === 'delayed'
                  ? 'bg-red-100 text-red-700 border border-red-300'
                  : 'bg-gray-100 text-gray-700 border border-gray-300 hover:bg-gray-200'
              ]"
            >
              Gecikmiş Projeler
            </button>
            <button
              @click="setSpecialFilter('over_budget')"
              :class="[
                'px-3 py-1.5 rounded-lg text-sm font-medium transition-colors',
                filters.filter === 'over_budget'
                  ? 'bg-orange-100 text-orange-700 border border-orange-300'
                  : 'bg-gray-100 text-gray-700 border border-gray-300 hover:bg-gray-200'
              ]"
            >
              Bütçe Aşan
            </button>
            <button
              @click="setSpecialFilter('ending_soon')"
              :class="[
                'px-3 py-1.5 rounded-lg text-sm font-medium transition-colors',
                filters.filter === 'ending_soon'
                  ? 'bg-yellow-100 text-yellow-700 border border-yellow-300'
                  : 'bg-gray-100 text-gray-700 border border-gray-300 hover:bg-gray-200'
              ]"
            >
              Yakında Bitenler
            </button>
            <button
              v-if="hasActiveFilters"
              @click="clearFilters"
              class="px-3 py-1.5 rounded-lg text-sm font-medium bg-gray-200 text-gray-700 border border-gray-400 hover:bg-gray-300"
            >
              Filtreleri Temizle
            </button>
          </div>
        </div>
      </div>

      <!-- Projects Table -->
      <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Proje
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Müşteri
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Konum
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Yöneticiler
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Tarihler
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Bütçe
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Durum
                </th>
                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                  İşlemler
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-if="!projectsData || projectsData.data.length === 0">
                <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                  <div class="flex flex-col items-center">
                    <svg class="w-12 h-12 text-gray-400 mb-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008z" />
                    </svg>
                    <p class="text-sm">Proje bulunamadı</p>
                  </div>
                </td>
              </tr>
              <tr
                v-for="project in projectsData.data"
                :key="project.id"
                class="hover:bg-gray-50 transition-colors"
              >
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div>
                      <Link
                        :href="route('projects.show', project.id)"
                        class="text-sm font-medium text-gray-900 hover:text-cyan-600"
                      >
                        {{ project.name }}
                      </Link>
                      <div class="text-xs text-gray-500">{{ project.project_code }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900">{{ project.client_name || '-' }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900">{{ project.city }}</div>
                  <div class="text-xs text-gray-500">{{ project.district || '-' }}</div>
                </td>
                <td class="px-6 py-4">
                  <div class="text-xs">
                    <div v-if="project.project_manager" class="text-gray-900">
                      PM: {{ project.project_manager.first_name }} {{ project.project_manager.last_name }}
                    </div>
                    <div v-if="project.site_manager" class="text-gray-600">
                      SM: {{ project.site_manager.first_name }} {{ project.site_manager.last_name }}
                    </div>
                    <div v-if="!project.project_manager && !project.site_manager" class="text-gray-400">
                      Yönetici atanmamış
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-xs">
                    <div class="text-gray-900">{{ formatDate(project.start_date) }}</div>
                    <div class="text-gray-500">{{ formatDate(project.planned_end_date) }}</div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-xs">
                    <div class="text-gray-900">{{ formatCurrency(project.budget) }}</div>
                    <div
                      v-if="project.budget_usage_percentage !== null"
                      :class="[
                        'text-xs',
                        project.budget_usage_percentage > 100 ? 'text-red-600' :
                        project.budget_usage_percentage > 90 ? 'text-orange-600' :
                        'text-green-600'
                      ]"
                    >
                      %{{ project.budget_usage_percentage }} kullanıldı
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span
                    :class="[
                      'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                      getStatusClass(project.status)
                    ]"
                  >
                    {{ getStatusLabel(project.status) }}
                  </span>
                  <div v-if="project.is_delayed" class="mt-1">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                      Gecikmiş
                    </span>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <div class="flex justify-end items-center space-x-3">
                    <Link
                      :href="route('projects.show', project.id)"
                      class="text-cyan-600 hover:text-cyan-900"
                    >
                      Görüntüle
                    </Link>
                    <Link
                      :href="route('projects.edit', project.id)"
                      class="text-indigo-600 hover:text-indigo-900"
                    >
                      Düzenle
                    </Link>
                    <button
                      @click="confirmDelete(project)"
                      class="text-red-600 hover:text-red-900"
                    >
                      Sil
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="projectsData && projectsData.data.length > 0" class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
          <div class="flex items-center justify-between">
            <div class="flex-1 flex justify-between sm:hidden">
              <Link
                v-if="projectsData.prev_page_url"
                :href="projectsData.prev_page_url"
                class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
              >
                Önceki
              </Link>
              <Link
                v-if="projectsData.next_page_url"
                :href="projectsData.next_page_url"
                class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
              >
                Sonraki
              </Link>
            </div>
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
              <div>
                <p class="text-sm text-gray-700">
                  <span class="font-medium">{{ projectsData.from || 0 }}</span>
                  -
                  <span class="font-medium">{{ projectsData.to || 0 }}</span>
                  arası gösteriliyor, toplam
                  <span class="font-medium">{{ projectsData.total || 0 }}</span>
                  kayıt
                </p>
              </div>
              <div>
                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                  <Link
                    v-if="projectsData.prev_page_url"
                    :href="projectsData.prev_page_url"
                    class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50"
                  >
                    <span class="sr-only">Önceki</span>
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                  </Link>
                  <Link
                    v-if="projectsData.next_page_url"
                    :href="projectsData.next_page_url"
                    class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50"
                  >
                    <span class="sr-only">Sonraki</span>
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                    </svg>
                  </Link>
                </nav>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div
      v-if="showDeleteModal"
      class="fixed z-50 inset-0 overflow-y-auto"
      aria-labelledby="modal-title"
      role="dialog"
      aria-modal="true"
    >
      <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showDeleteModal = false"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
          <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
            <div class="sm:flex sm:items-start">
              <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                </svg>
              </div>
              <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                  Projeyi Sil
                </h3>
                <div class="mt-2">
                  <p class="text-sm text-gray-500">
                    <strong>{{ selectedProject?.name }}</strong> projesini silmek istediğinizden emin misiniz?
                    Bu işlem geri alınamaz.
                  </p>
                </div>
              </div>
            </div>
          </div>
          <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
            <button
              @click="deleteProject"
              type="button"
              class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm"
            >
              Sil
            </button>
            <button
              @click="showDeleteModal = false"
              type="button"
              class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-cyan-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
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
import { format, parseISO } from 'date-fns'
import { tr } from 'date-fns/locale'

const props = defineProps({
  projects: Object,
  managers: Array,
  cities: Array,
  filters: Object,
  stats: Object
})

const filters = ref({
  search: props.filters?.search || '',
  status: props.filters?.status || '',
  type: props.filters?.type || '',
  city: props.filters?.city || '',
  filter: props.filters?.filter || ''
})

const showDeleteModal = ref(false)
const selectedProject = ref(null)

const projectsData = computed(() => {
  if (props.projects && typeof props.projects === 'object' && props.projects.data) {
    return props.projects
  }
  return { data: [], total: 0, from: 0, to: 0, links: [] }
})

const hasActiveFilters = computed(() => {
  return filters.value.search || filters.value.status || filters.value.type || filters.value.city || filters.value.filter
})

const handleFilter = () => {
  router.get(route('projects.index'), filters.value, {
    preserveState: true,
    preserveScroll: true
  })
}

const setSpecialFilter = (filterName) => {
  if (filters.value.filter === filterName) {
    filters.value.filter = ''
  } else {
    filters.value.filter = filterName
  }
  handleFilter()
}

const clearFilters = () => {
  filters.value = {
    search: '',
    status: '',
    type: '',
    city: '',
    filter: ''
  }
  handleFilter()
}

const confirmDelete = (project) => {
  selectedProject.value = project
  showDeleteModal.value = true
}

const deleteProject = () => {
  router.delete(route('projects.destroy', selectedProject.value.id), {
    onSuccess: () => {
      showDeleteModal.value = false
      selectedProject.value = null
    }
  })
}

const formatDate = (date) => {
  if (!date) return '-'
  try {
    return format(parseISO(date), 'dd MMM yyyy', { locale: tr })
  } catch (e) {
    return date
  }
}

const formatCurrency = (amount) => {
  if (!amount && amount !== 0) return '-'
  return new Intl.NumberFormat('tr-TR', {
    style: 'currency',
    currency: 'TRY'
  }).format(amount)
}

const getStatusLabel = (status) => {
  const labels = {
    planning: 'Planlama',
    active: 'Aktif',
    on_hold: 'Beklemede',
    completed: 'Tamamlandı',
    cancelled: 'İptal'
  }
  return labels[status] || status
}

const getStatusClass = (status) => {
  const classes = {
    planning: 'bg-yellow-100 text-yellow-800',
    active: 'bg-green-100 text-green-800',
    on_hold: 'bg-blue-100 text-blue-800',
    completed: 'bg-gray-100 text-gray-800',
    cancelled: 'bg-red-100 text-red-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}
</script>
