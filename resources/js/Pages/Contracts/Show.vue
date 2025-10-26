<template>
  <AppLayout :title="`Sözleşme ${contract.contract_number}`" :full-width="true">
    <!-- Full-width header -->
    <template #fullWidthHeader>
      <div class="bg-gradient-to-r from-indigo-600 via-purple-700 to-blue-800 border-b border-indigo-900/20 w-full">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
          <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <!-- Header Info -->
            <div class="flex-1 min-w-0">
              <div class="flex items-center space-x-4 mb-3">
                <div class="flex-shrink-0 w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm">
                  <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                  </svg>
                </div>
                <div>
                  <h1 class="text-2xl lg:text-3xl font-bold text-white">
                    {{ contract.contract_number }}
                  </h1>
                  <p class="text-indigo-100 text-sm mt-1">{{ contract.contract_name }}</p>
                </div>
              </div>

              <!-- Status Badges -->
              <div class="flex items-center space-x-3">
                <span :class="getStatusClass(contract.status)" class="px-4 py-1.5 text-sm font-semibold rounded-full border-2">
                  {{ getStatusLabel(contract.status) }}
                </span>
                <span v-if="contract.is_expiring_soon" class="px-4 py-1.5 text-sm bg-orange-500/20 text-orange-100 rounded-full border-2 border-orange-400/50">
                  {{ contract.days_until_expiry }} gün kaldı
                </span>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex-shrink-0 flex flex-wrap items-center gap-3">
              <Link
                :href="route('contracts.edit', contract.id)"
                class="inline-flex items-center px-4 py-2 bg-white text-indigo-600 text-sm font-medium rounded-lg hover:bg-indigo-50 transition-all duration-200"
              >
                <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                </svg>
                Düzenle
              </Link>

              <button
                v-if="contract.status === 'draft'"
                @click="activateContract"
                class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-all duration-200"
              >
                <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                Aktif Yap
              </button>

              <button
                v-if="contract.status === 'active'"
                @click="showTerminateModal = true"
                class="inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition-all duration-200"
              >
                <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" />
                </svg>
                Feshet
              </button>

              <button
                v-if="contract.status === 'active'"
                @click="completeContract"
                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-all duration-200"
              >
                <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" />
                </svg>
                Tamamlandı İşaretle
              </button>

              <Link
                :href="route('contracts.index')"
                class="inline-flex items-center px-4 py-2 bg-white/10 backdrop-blur-sm border border-white/30 text-white text-sm font-medium rounded-lg hover:bg-white/20 transition-all duration-200"
              >
                <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>
                Geri Dön
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
                  <Link :href="route('contracts.index')" class="text-indigo-100 hover:text-white text-sm">Sözleşmeler</Link>
                </li>
                <li class="flex items-center">
                  <svg class="h-3 w-3 text-indigo-200 mx-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                  </svg>
                  <span class="text-xs font-medium text-white">{{ contract.contract_number }}</span>
                </li>
              </ol>
            </div>
          </div>
        </div>
      </div>
    </template>

    <!-- Main Content -->
    <div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">

      <!-- Summary Cards -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
          <div class="p-6">
            <p class="text-sm font-medium text-gray-600">Sözleşme Bedeli</p>
            <p class="text-2xl font-bold text-gray-900 mt-2">{{ formatCurrency(contract.contract_value) }}</p>
          </div>
        </div>

        <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
          <div class="p-6">
            <p class="text-sm font-medium text-gray-600">Ödenen Tutar</p>
            <p class="text-2xl font-bold text-green-600 mt-2">{{ formatCurrency(summary.total_paid) }}</p>
          </div>
        </div>

        <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
          <div class="p-6">
            <p class="text-sm font-medium text-gray-600">Kalan Tutar</p>
            <p class="text-2xl font-bold text-orange-600 mt-2">{{ formatCurrency(summary.remaining) }}</p>
          </div>
        </div>

        <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
          <div class="p-6">
            <p class="text-sm font-medium text-gray-600">Tamamlanma</p>
            <p class="text-2xl font-bold text-blue-600 mt-2">%{{ summary.completion_percentage }}</p>
          </div>
        </div>
      </div>

      <!-- Contract Details -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Genel Bilgiler -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-semibold text-gray-900">Genel Bilgiler</h3>
          </div>
          <div class="p-6 space-y-4">
            <div class="flex justify-between">
              <span class="text-sm text-gray-600">Proje:</span>
              <span class="text-sm font-medium text-gray-900">{{ contract.project.name }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-sm text-gray-600">Taşeron:</span>
              <span class="text-sm font-medium text-gray-900">{{ contract.subcontractor?.company_name || 'N/A' }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-sm text-gray-600">Sözleşme Tipi:</span>
              <span class="text-sm font-medium text-gray-900">{{ getContractTypeLabel(contract.contract_type) }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-sm text-gray-600">Para Birimi:</span>
              <span class="text-sm font-medium text-gray-900">{{ contract.currency }}</span>
            </div>
          </div>
        </div>

        <!-- Tarihler -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-semibold text-gray-900">Tarihler</h3>
          </div>
          <div class="p-6 space-y-4">
            <div class="flex justify-between">
              <span class="text-sm text-gray-600">İmza Tarihi:</span>
              <span class="text-sm font-medium text-gray-900">{{ formatDate(contract.signing_date) }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-sm text-gray-600">Başlangıç:</span>
              <span class="text-sm font-medium text-gray-900">{{ formatDate(contract.start_date) }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-sm text-gray-600">Bitiş:</span>
              <span class="text-sm font-medium text-gray-900">{{ formatDate(contract.end_date) }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-sm text-gray-600">Süre:</span>
              <span class="text-sm font-medium text-gray-900">{{ calculateDuration(contract.start_date, contract.end_date) }} gün</span>
            </div>
          </div>
        </div>

        <!-- Teminat Bilgileri -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-semibold text-gray-900">Teminat Bilgileri</h3>
          </div>
          <div class="p-6 space-y-4">
            <div class="flex justify-between">
              <span class="text-sm text-gray-600">Teminat Türü:</span>
              <span class="text-sm font-medium text-gray-900">{{ getWarrantyTypeLabel(contract.warranty_type) }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-sm text-gray-600">Teminat Tutarı:</span>
              <span class="text-sm font-medium text-gray-900">{{ formatCurrency(contract.warranty_amount) }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-sm text-gray-600">Teminat Bitiş:</span>
              <span class="text-sm font-medium text-gray-900">{{ formatDate(contract.warranty_end_date) }}</span>
            </div>
          </div>
        </div>

        <!-- İş Tanımı -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-semibold text-gray-900">İş Tanımı</h3>
          </div>
          <div class="p-6">
            <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ contract.work_description || 'Belirtilmemiş' }}</p>
          </div>
        </div>
      </div>

      <!-- Ödeme Koşulları -->
      <div v-if="contract.payment_terms" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
          <h3 class="text-lg font-semibold text-gray-900">Ödeme Koşulları</h3>
        </div>
        <div class="p-6">
          <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ contract.payment_terms }}</p>
        </div>
      </div>

      <!-- İlişkili Hakediş Kayıtları -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
          <div>
            <h3 class="text-lg font-semibold text-gray-900">İlişkili Hakediş Kayıtları</h3>
            <p class="text-sm text-gray-500 mt-1">Bu sözleşme kapsamında yapılan ödemeler ({{ summary.payment_count }} adet)</p>
          </div>
          <Link
            :href="route('progress-payments.create', { contract_id: contract.id })"
            class="inline-flex items-center px-3 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors"
          >
            <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Yeni Hakediş
          </Link>
        </div>
        <div class="overflow-x-auto">
          <table v-if="contract.progress_payments.length > 0" class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">İş Kalemi</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Miktar</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tutar</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Durum</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tarih</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">İşlem</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="payment in contract.progress_payments" :key="payment.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-blue-600">
                  #{{ payment.id }}
                </td>
                <td class="px-6 py-4 text-sm text-gray-900">
                  {{ payment.work_item?.name || 'N/A' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ payment.completed_quantity }} {{ payment.unit }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                  {{ formatCurrency(payment.total_amount) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="getPaymentStatusClass(payment.status)" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                    {{ getPaymentStatusLabel(payment.status) }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ formatDate(payment.payment_date) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                  <Link :href="route('progress-payments.show', payment.id)" class="text-blue-600 hover:text-blue-800">
                    Detay
                  </Link>
                </td>
              </tr>
            </tbody>
          </table>
          <div v-else class="p-6 text-center text-gray-500">
            <p class="text-sm">Henüz hakediş kaydı yok</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Terminate Modal -->
    <div v-if="showTerminateModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-center justify-center">
      <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
        <div class="p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Sözleşmeyi Feshet</h3>
          <p class="text-sm text-gray-600 mb-4">Bu sözleşmeyi feshetmek istediğinizden emin misiniz?</p>

          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Fesih Nedeni</label>
            <textarea
              v-model="terminationReason"
              rows="4"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
              placeholder="Fesih nedenini yazınız..."
            ></textarea>
          </div>

          <div class="flex justify-end space-x-3">
            <button
              @click="showTerminateModal = false"
              class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors"
            >
              İptal
            </button>
            <button
              @click="terminateContract"
              class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition-colors"
            >
              Feshet
            </button>
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
  contract: Object,
  summary: Object,
});

const showTerminateModal = ref(false);
const terminationReason = ref('');

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

const calculateDuration = (startDate, endDate) => {
  if (!startDate || !endDate) return 0;
  const start = new Date(startDate);
  const end = new Date(endDate);
  return Math.ceil((end - start) / (1000 * 60 * 60 * 24));
};

const getStatusClass = (status) => {
  const classes = {
    draft: 'bg-gray-100 text-gray-800 border-gray-300',
    active: 'bg-green-100 text-green-800 border-green-300',
    completed: 'bg-blue-100 text-blue-800 border-blue-300',
    terminated: 'bg-red-100 text-red-800 border-red-300',
    expired: 'bg-orange-100 text-orange-800 border-orange-300',
  };
  return classes[status] || 'bg-gray-100 text-gray-800 border-gray-300';
};

const getPaymentStatusLabel = (status) => {
  const labels = {
    pending: 'Beklemede',
    approved: 'Onaylandı',
    paid: 'Ödendi',
    rejected: 'Reddedildi',
  };
  return labels[status] || status;
};

const getPaymentStatusClass = (status) => {
  const classes = {
    pending: 'bg-yellow-100 text-yellow-800',
    approved: 'bg-blue-100 text-blue-800',
    paid: 'bg-green-100 text-green-800',
    rejected: 'bg-red-100 text-red-800',
  };
  return classes[status] || 'bg-gray-100 text-gray-800';
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

const getContractTypeLabel = (type) => {
  const labels = {
    subcontractor: 'Taşeron Sözleşmesi',
    supplier: 'Tedarikçi Anlaşması',
  };
  return labels[type] || type;
};

const getWarrantyTypeLabel = (type) => {
  const labels = {
    bank_letter: 'Banka Teminat Mektubu',
    cash: 'Nakit Teminat',
    check: 'Çek',
    none: 'Teminatsız',
  };
  return labels[type] || type;
};

const activateContract = () => {
  if (confirm('Bu sözleşmeyi aktif hale getirmek istediğinizden emin misiniz?')) {
    router.post(route('contracts.activate', props.contract.id), {}, {
      preserveScroll: true,
      onSuccess: () => {
        alert('Sözleşme aktif hale getirildi.');
      },
    });
  }
};

const terminateContract = () => {
  if (!terminationReason.value.trim()) {
    alert('Lütfen fesih nedenini belirtiniz.');
    return;
  }

  router.post(route('contracts.terminate', props.contract.id), {
    termination_reason: terminationReason.value,
  }, {
    preserveScroll: true,
    onSuccess: () => {
      showTerminateModal.value = false;
      alert('Sözleşme feshedildi.');
    },
  });
};

const completeContract = () => {
  if (confirm('Bu sözleşmeyi tamamlandı olarak işaretlemek istediğinizden emin misiniz?')) {
    router.post(route('contracts.complete', props.contract.id), {}, {
      preserveScroll: true,
      onSuccess: () => {
        alert('Sözleşme tamamlandı olarak işaretlendi.');
      },
    });
  }
};
</script>