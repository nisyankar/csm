<template>
  <AppLayout title="Satış Yönetimi" :full-width="true">
    <!-- Full-width header -->
    <template #fullWidthHeader>
      <div class="bg-gradient-to-r from-emerald-600 via-emerald-700 to-teal-800 border-b border-emerald-900/20 w-full">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
          <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <!-- Header Info -->
            <div class="flex-1 min-w-0">
              <div class="flex items-center space-x-4 mb-3">
                <div class="flex-shrink-0 w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm">
                  <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z" />
                  </svg>
                </div>
                <div>
                  <h1 class="text-2xl lg:text-3xl font-bold text-white">Satış Yönetimi</h1>
                  <p class="text-emerald-100 text-sm mt-1">Daire/birim satışlarını yönetin ve takip edin</p>
                </div>
              </div>

              <!-- Stats Row -->
              <div class="flex flex-wrap items-center gap-4 lg:gap-6">
                <div class="bg-white/10 backdrop-blur-sm rounded-lg px-3 py-2">
                  <span class="text-emerald-100 text-sm">Toplam Satış:</span>
                  <span class="font-semibold text-white ml-1">{{ unitSales?.total || 0 }}</span>
                </div>
                <div class="flex items-center space-x-4 text-sm">
                  <span class="flex items-center text-emerald-100">
                    <div class="w-2 h-2 bg-yellow-400 rounded-full mr-2"></div>
                    Rezerve: <span class="text-white font-medium ml-1">{{ countByStatus('reserved') }}</span>
                  </span>
                  <span class="flex items-center text-emerald-100">
                    <div class="w-2 h-2 bg-blue-400 rounded-full mr-2"></div>
                    Sözleşmeli: <span class="text-white font-medium ml-1">{{ countByStatus('contracted') }}</span>
                  </span>
                  <span class="flex items-center text-emerald-100">
                    <div class="w-2 h-2 bg-purple-400 rounded-full mr-2"></div>
                    Ödeme Aşamasında: <span class="text-white font-medium ml-1">{{ countByStatus('in_payment') }}</span>
                  </span>
                  <span class="flex items-center text-emerald-100">
                    <div class="w-2 h-2 bg-green-400 rounded-full mr-2"></div>
                    Tamamlandı: <span class="text-white font-medium ml-1">{{ countByStatus('completed') }}</span>
                  </span>
                </div>
              </div>
            </div>

            <!-- Action Button -->
            <div class="flex-shrink-0">
              <Link
                :href="route('sales.unit-sales.create')"
                class="inline-flex items-center px-4 py-2 bg-white text-emerald-600 text-sm font-medium rounded-lg hover:bg-emerald-50 shadow-lg hover:shadow-xl transition-all duration-200"
              >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Yeni Satış
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
                  <Link :href="route('dashboard')" class="text-emerald-100 hover:text-white transition-colors">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                    </svg>
                  </Link>
                </li>
                <li class="flex items-center">
                  <svg class="h-3 w-3 text-emerald-200 mx-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                  </svg>
                  <span class="text-white font-medium text-sm">Satış Yönetimi</span>
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
          <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Proje</label>
              <select
                v-model="filters.project_id"
                @change="applyFilters"
                class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
              >
                <option :value="null">Tüm Projeler</option>
                <option v-for="project in projects" :key="project.id" :value="project.id">
                  {{ project.name }}
                </option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Satış Tipi</label>
              <select
                v-model="filters.sale_type"
                @change="applyFilters"
                class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
              >
                <option :value="null">Tümü</option>
                <option value="reservation">Rezervasyon</option>
                <option value="sale">Kesin Satış</option>
                <option value="presale">Ön Satış</option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Durum</label>
              <select
                v-model="filters.status"
                @change="applyFilters"
                class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
              >
                <option :value="null">Tüm Durumlar</option>
                <option value="reserved">Rezerve</option>
                <option value="contracted">Sözleşmeli</option>
                <option value="in_payment">Ödeme Aşamasında</option>
                <option value="completed">Tamamlandı</option>
                <option value="cancelled">İptal</option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Tapu Durumu</label>
              <select
                v-model="filters.deed_status"
                @change="applyFilters"
                class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
              >
                <option :value="null">Tümü</option>
                <option value="not_transferred">Devredilmedi</option>
                <option value="in_progress">İşlemde</option>
                <option value="transferred">Devredildi</option>
                <option value="postponed">Ertelendi</option>
              </select>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Arama</label>
            <input
              v-model="filters.search"
              @input="debounceSearch"
              type="text"
              placeholder="Müşteri, satış no veya birim ara..."
              class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
            />
          </div>
        </div>
      </div>

      <!-- Table -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
              <tr>
                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Satış No</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Müşteri</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Proje/Birim</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Fiyat</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Ödeme</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Durum</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Tapu</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">İşlemler</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-if="unitSales.data.length === 0">
                <td colspan="8" class="px-6 py-12 text-center">
                  <div class="flex flex-col items-center justify-center">
                    <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z" />
                    </svg>
                    <p class="text-gray-500 text-sm">Satış kaydı bulunamadı.</p>
                  </div>
                </td>
              </tr>
              <tr v-for="sale in unitSales.data" :key="sale.id" class="hover:bg-emerald-50/50 transition-colors duration-150">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-semibold text-gray-900">{{ sale.sale_number }}</div>
                  <div class="text-xs text-gray-500">{{ formatDate(sale.sale_date) }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-gray-900">{{ sale.customer?.full_name }}</div>
                  <div class="text-xs text-gray-500">{{ sale.customer?.phone }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-gray-900">{{ sale.project?.name }}</div>
                  <div class="text-xs text-gray-500">{{ sale.project_unit?.full_code }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-bold text-gray-900">{{ formatCurrency(sale.final_price) }}</div>
                  <div v-if="sale.discount_percentage > 0" class="text-xs text-green-600">
                    %{{ sale.discount_percentage }} indirim
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="flex-1 mr-3">
                      <div class="w-full bg-gray-200 rounded-full h-2">
                        <div
                          class="h-2 rounded-full transition-all duration-300"
                          :class="sale.payment_completion_percentage >= 100 ? 'bg-green-600' : 'bg-emerald-600'"
                          :style="{ width: `${Math.min(sale.payment_completion_percentage, 100)}%` }"
                        ></div>
                      </div>
                    </div>
                    <span class="text-xs font-bold text-gray-700 min-w-[45px]">{{ sale.payment_completion_percentage }}%</span>
                  </div>
                  <div class="text-xs text-gray-500 mt-1">
                    {{ sale.installment_count }} taksit
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span
                    class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold"
                    :class="getStatusClass(sale.status)"
                  >
                    {{ sale.status_badge.text }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span
                    class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold"
                    :class="getDeedStatusClass(sale.deed_status)"
                  >
                    {{ sale.deed_status_badge.text }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <div class="flex items-center space-x-3">
                    <Link
                      :href="route('sales.unit-sales.show', sale.id)"
                      class="inline-flex items-center text-emerald-600 hover:text-emerald-800 font-medium transition-colors"
                    >
                      <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                      </svg>
                      Detay
                    </Link>
                    <Link
                      :href="route('sales.unit-sales.edit', sale.id)"
                      class="inline-flex items-center text-indigo-600 hover:text-indigo-800 font-medium transition-colors"
                    >
                      <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                      </svg>
                      Düzenle
                    </Link>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="unitSales.meta && unitSales.meta.last_page > 1" class="px-6 py-4 bg-gray-50 border-t border-gray-200">
          <Pagination
            :meta="unitSales.meta"
            :links="unitSales.links"
          />
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Pagination from '@/Components/UI/Pagination.vue'

const props = defineProps({
  unitSales: {
    type: Object,
    required: true
  },
  projects: {
    type: Array,
    default: () => []
  },
  filters: {
    type: Object,
    default: () => ({})
  }
})

const filters = ref({
  project_id: props.filters.project_id || null,
  sale_type: props.filters.sale_type || null,
  status: props.filters.status || null,
  deed_status: props.filters.deed_status || null,
  search: props.filters.search || ''
})

const countByStatus = (status) => {
  return props.unitSales.data.filter(s => s.status === status).length
}

let searchTimeout = null
const debounceSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    applyFilters()
  }, 500)
}

const applyFilters = () => {
  router.get(route('sales.unit-sales.index'), filters.value, {
    preserveState: true,
    preserveScroll: true
  })
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('tr-TR')
}

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('tr-TR', {
    style: 'currency',
    currency: 'TRY',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0
  }).format(amount)
}

const getStatusClass = (status) => {
  const classes = {
    reserved: 'bg-yellow-100 text-yellow-700',
    contracted: 'bg-blue-100 text-blue-700',
    in_payment: 'bg-purple-100 text-purple-700',
    completed: 'bg-green-100 text-green-700',
    cancelled: 'bg-red-100 text-red-700',
    delayed: 'bg-orange-100 text-orange-700'
  }
  return classes[status] || 'bg-gray-100 text-gray-700'
}

const getDeedStatusClass = (status) => {
  const classes = {
    not_transferred: 'bg-gray-100 text-gray-700',
    in_progress: 'bg-blue-100 text-blue-700',
    transferred: 'bg-green-100 text-green-700',
    postponed: 'bg-orange-100 text-orange-700'
  }
  return classes[status] || 'bg-gray-100 text-gray-700'
}
</script>
