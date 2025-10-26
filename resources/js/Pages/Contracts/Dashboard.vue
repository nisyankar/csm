<template>
  <AppLayout title="Sözleşme Dashboard" :full-width="true">
    <!-- Full-width header -->
    <template #fullWidthHeader>
      <div class="bg-gradient-to-r from-blue-600 via-indigo-700 to-purple-800 border-b border-blue-900/20 w-full">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
          <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <!-- Left: Icon + Title -->
            <div class="flex items-center">
              <div class="flex-shrink-0 w-16 h-16 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm">
                <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                </svg>
              </div>
              <div class="ml-6">
                <h1 class="text-2xl lg:text-3xl font-bold text-white">
                  Sözleşme Yönetimi
                </h1>
                <p class="text-blue-100 text-sm mt-1">
                  Taşeron ve tedarikçi sözleşmeleri
                </p>
              </div>
            </div>

            <!-- Right: Action button -->
            <div class="flex-shrink-0">
              <Link :href="route('contracts.create')" class="inline-flex items-center px-4 py-2 bg-white text-blue-700 hover:bg-blue-50 rounded-lg font-medium transition-colors shadow-sm">
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
                  <Link :href="route('dashboard')" class="text-blue-100 hover:text-white transition-colors">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                    </svg>
                    <span class="sr-only">Ana Sayfa</span>
                  </Link>
                </li>
                <li class="flex items-center">
                  <svg class="h-3 w-3 text-blue-200 mx-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                  </svg>
                  <span class="text-xs font-medium text-white">Sözleşme Yönetimi</span>
                </li>
                <li class="flex items-center">
                  <svg class="h-3 w-3 text-blue-200 mx-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                  </svg>
                  <span class="text-xs font-medium text-white">Dashboard</span>
                </li>
              </ol>
            </div>
          </div>
        </div>
      </div>
    </template>

    <!-- Main Content -->
    <div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
      <!-- Project Filter -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden">
        <div class="p-4">
          <div class="flex items-center gap-4">
            <label class="text-sm font-medium text-gray-700">Proje:</label>
            <select
              v-model="selectedProjectId"
              @change="filterByProject"
              class="px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option :value="null">Tüm Projeler</option>
              <option v-for="project in projects" :key="project.id" :value="project.id">
                {{ project.name }}
              </option>
            </select>
          </div>
        </div>
      </div>

      <!-- Stats Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Toplam Sözleşme -->
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden hover:shadow-md transition-shadow">
          <div class="p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-600">Toplam Sözleşme</p>
                <p class="text-2xl font-bold text-gray-900 mt-2">{{ stats.total_contracts }}</p>
              </div>
              <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                <svg class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
              </div>
            </div>
          </div>
        </div>

        <!-- Aktif Sözleşme -->
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden hover:shadow-md transition-shadow">
          <div class="p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-600">Aktif Sözleşme</p>
                <p class="text-2xl font-bold text-green-600 mt-2">{{ stats.active_contracts }}</p>
              </div>
              <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                <svg class="w-6 h-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
            </div>
          </div>
        </div>

        <!-- Yaklaşan Bitiş -->
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden hover:shadow-md transition-shadow">
          <div class="p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-600">Yaklaşan Bitiş</p>
                <p class="text-2xl font-bold text-orange-600 mt-2">{{ stats.expiring_soon }}</p>
              </div>
              <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center">
                <svg class="w-6 h-6 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
            </div>
          </div>
        </div>

        <!-- Toplam Bedel -->
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden hover:shadow-md transition-shadow">
          <div class="p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-600">Toplam Bedel</p>
                <p class="text-2xl font-bold text-indigo-600 mt-2">{{ formatCurrency(stats.total_value) }}</p>
              </div>
              <div class="w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center">
                <svg class="w-6 h-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Tables Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Yaklaşan Bitiş Tarihleri -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-semibold text-gray-900">Yaklaşan Bitiş Tarihleri</h3>
            <p class="text-sm text-gray-500 mt-1">30 gün içinde süresi dolacak sözleşmeler</p>
          </div>
          <div class="overflow-x-auto">
            <table v-if="expiringSoon.length > 0" class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sözleşme</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Taşeron</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Bitiş</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="contract in expiringSoon" :key="contract.id" class="hover:bg-gray-50 transition-colors">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <Link :href="route('contracts.show', contract.id)" class="text-sm font-medium text-blue-600 hover:text-blue-800">
                      {{ contract.contract_number }}
                    </Link>
                    <p class="text-xs text-gray-500">{{ contract.contract_name }}</p>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ contract.subcontractor?.company_name || 'N/A' }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="text-sm font-medium text-orange-600">
                      {{ formatDate(contract.end_date) }}
                    </span>
                    <p class="text-xs text-gray-500">{{ contract.days_until_expiry }} gün kaldı</p>
                  </td>
                </tr>
              </tbody>
            </table>
            <div v-else class="p-6 text-center text-gray-500">
              <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <p class="text-sm">Yaklaşan bitiş tarihi olan sözleşme yok</p>
            </div>
          </div>
        </div>

        <!-- Süresi Dolan Sözleşmeler -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-semibold text-gray-900">Süresi Dolan Sözleşmeler</h3>
            <p class="text-sm text-gray-500 mt-1">Bitiş tarihi geçmiş aktif sözleşmeler</p>
          </div>
          <div class="overflow-x-auto">
            <table v-if="expired.length > 0" class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sözleşme</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Taşeron</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Bitiş</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="contract in expired" :key="contract.id" class="hover:bg-gray-50 transition-colors">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <Link :href="route('contracts.show', contract.id)" class="text-sm font-medium text-blue-600 hover:text-blue-800">
                      {{ contract.contract_number }}
                    </Link>
                    <p class="text-xs text-gray-500">{{ contract.contract_name }}</p>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ contract.subcontractor?.company_name || 'N/A' }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="text-sm font-medium text-red-600">
                      {{ formatDate(contract.end_date) }}
                    </span>
                    <p class="text-xs text-gray-500">{{ Math.abs(contract.days_until_expiry) }} gün geçti</p>
                  </td>
                </tr>
              </tbody>
            </table>
            <div v-else class="p-6 text-center text-gray-500">
              <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <p class="text-sm">Süresi dolan sözleşme yok</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Contracts -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
          <div>
            <h3 class="text-lg font-semibold text-gray-900">Son Eklenen Sözleşmeler</h3>
            <p class="text-sm text-gray-500 mt-1">En son oluşturulan 10 sözleşme</p>
          </div>
          <Link :href="route('contracts.index')" class="text-sm text-blue-600 hover:text-blue-800 font-medium">
            Tümünü Gör →
          </Link>
        </div>
        <div class="overflow-x-auto">
          <table v-if="recentContracts.length > 0" class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sözleşme No</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Proje</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Taşeron</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Bedel</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Durum</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tarih</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="contract in recentContracts" :key="contract.id" class="hover:bg-gray-50 transition-colors">
                <td class="px-6 py-4 whitespace-nowrap">
                  <Link :href="route('contracts.show', contract.id)" class="text-sm font-medium text-blue-600 hover:text-blue-800">
                    {{ contract.contract_number }}
                  </Link>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ contract.project?.name || 'N/A' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ contract.subcontractor?.company_name || 'N/A' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                  {{ formatCurrency(contract.contract_value) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="getStatusBadgeClass(contract.status)" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                    {{ getStatusLabel(contract.status) }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ formatDate(contract.start_date) }}
                </td>
              </tr>
            </tbody>
          </table>
          <div v-else class="p-6 text-center text-gray-500">
            <p class="text-sm">Henüz sözleşme bulunmuyor</p>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
  stats: Object,
  expiringSoon: Array,
  expired: Array,
  recentContracts: Array,
  projects: Array,
  selectedProjectId: Number,
});

const selectedProjectId = ref(props.selectedProjectId);

const filterByProject = () => {
  router.get(route('contracts.dashboard'), {
    project_id: selectedProjectId.value,
  }, {
    preserveState: true,
    preserveScroll: true,
  });
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
