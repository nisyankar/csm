<template>
  <AppLayout title="Sözleşmeler" :full-width="true">
    <!-- Full-width header -->
    <template #fullWidthHeader>
      <div class="bg-gradient-to-r from-indigo-600 via-purple-700 to-blue-800 border-b border-indigo-900/20 w-full">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
          <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <div class="flex items-center">
              <div class="flex-shrink-0 w-16 h-16 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm">
                <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                </svg>
              </div>
              <div class="ml-6">
                <h1 class="text-2xl lg:text-3xl font-bold text-white">Sözleşmeler</h1>
                <p class="text-indigo-100 text-sm mt-1">Tüm taşeron ve tedarikçi sözleşmeleri</p>
              </div>
            </div>
            <div class="flex-shrink-0">
              <Link :href="route('contracts.create')" class="inline-flex items-center px-4 py-2 bg-white text-indigo-700 hover:bg-indigo-50 rounded-lg font-medium transition-colors shadow-sm">
                <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Yeni Sözleşme
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
                  <Link :href="route('dashboard')" class="text-indigo-100 hover:text-white transition-colors">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                    </svg>
                  </Link>
                </li>
                <li class="flex items-center">
                  <svg class="h-3 w-3 text-indigo-200 mx-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                  </svg>
                  <span class="text-xs font-medium text-white">Sözleşmeler</span>
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
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
          <h3 class="text-lg font-medium text-gray-900">Arama ve Filtreler</h3>
        </div>
        <div class="p-6">
          <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Proje</label>
              <select v-model="form.project_id" @change="applyFilters" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                <option :value="null">Tüm Projeler</option>
                <option v-for="project in projects" :key="project.id" :value="project.id">{{ project.name }}</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Taşeron</label>
              <select v-model="form.subcontractor_id" @change="applyFilters" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                <option :value="null">Tüm Taşeronlar</option>
                <option v-for="sub in subcontractors" :key="sub.id" :value="sub.id">{{ sub.company_name }}</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Durum</label>
              <select v-model="form.status" @change="applyFilters" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                <option :value="null">Tüm Durumlar</option>
                <option value="draft">Taslak</option>
                <option value="active">Aktif</option>
                <option value="completed">Tamamlandı</option>
                <option value="terminated">Feshedildi</option>
                <option value="expired">Süresi Doldu</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Arama</label>
              <input
                v-model="form.search"
                @input="debouncedSearch"
                type="text"
                placeholder="Sözleşme no, adı..."
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
              />
            </div>
          </div>
        </div>
      </div>

      <!-- Data Table -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
          <table v-if="contracts.data.length > 0" class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sözleşme No</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Proje</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Taşeron</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Bedel</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Durum</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tarih</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">İşlem</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="contract in contracts.data" :key="contract.id" class="hover:bg-gray-50 transition-colors">
                <td class="px-6 py-4 whitespace-nowrap">
                  <Link :href="route('contracts.show', contract.id)" class="text-sm font-medium text-blue-600 hover:text-blue-800">
                    {{ contract.contract_number }}
                  </Link>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ contract.project?.name }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ contract.subcontractor?.company_name }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ formatCurrency(contract.contract_value) }}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="getStatusBadgeClass(contract.status)" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                    {{ getStatusLabel(contract.status) }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ formatDate(contract.start_date) }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                  <Link :href="route('contracts.show', contract.id)" class="text-blue-600 hover:text-blue-800 mr-3">Detay</Link>
                  <Link :href="route('contracts.edit', contract.id)" class="text-indigo-600 hover:text-indigo-800">Düzenle</Link>
                </td>
              </tr>
            </tbody>
          </table>
          <div v-else class="p-6 text-center text-gray-500">
            <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <p class="text-sm">Sözleşme bulunamadı</p>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="contracts.data.length > 0" class="bg-gray-50 px-4 py-3 border-t border-gray-200 sm:px-6">
          <div class="flex items-center justify-between">
            <div class="text-sm text-gray-700">
              Toplam <span class="font-medium">{{ contracts.total }}</span> kayıt
            </div>
            <div class="flex space-x-2">
              <template v-for="(link, index) in contracts.links" :key="index">
                <Link
                  v-if="link.url"
                  :href="link.url"
                  v-html="link.label"
                  :class="[
                    link.active ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50',
                    'px-3 py-2 text-sm font-medium rounded-md border border-gray-300'
                  ]"
                />
                <span
                  v-else
                  v-html="link.label"
                  class="px-3 py-2 text-sm font-medium rounded-md border border-gray-300 bg-gray-100 text-gray-400 cursor-not-allowed"
                />
              </template>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
  contracts: Object,
  filters: Object,
  projects: Array,
  subcontractors: Array,
});

const form = reactive({
  project_id: props.filters.project_id || null,
  subcontractor_id: props.filters.subcontractor_id || null,
  status: props.filters.status || null,
  search: props.filters.search || '',
});

let debounceTimeout = null;

const applyFilters = () => {
  router.get(route('contracts.index'), form, {
    preserveState: true,
    preserveScroll: true,
  });
};

const debouncedSearch = () => {
  clearTimeout(debounceTimeout);
  debounceTimeout = setTimeout(() => {
    applyFilters();
  }, 500);
};

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('tr-TR', {
    style: 'currency',
    currency: 'TRY',
    minimumFractionDigits: 2,
  }).format(amount || 0);
};

const formatDate = (date) => {
  if (!date) return 'N/A';
  return new Date(date).toLocaleDateString('tr-TR');
};

const getStatusLabel = (status) => {
  const labels = {
    draft: 'Taslak',
    active: 'Aktif',
    completed: 'Tamamlandı',
    terminated: 'Feshedildi',
    expired: 'Süresi Doldu',
  };
  return labels[status] || status;
};

const getStatusBadgeClass = (status) => {
  const classes = {
    draft: 'bg-gray-100 text-gray-800',
    active: 'bg-green-100 text-green-800',
    completed: 'bg-blue-100 text-blue-800',
    terminated: 'bg-red-100 text-red-800',
    expired: 'bg-orange-100 text-orange-800',
  };
  return classes[status] || 'bg-gray-100 text-gray-800';
};
</script>
