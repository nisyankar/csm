<template>
  <AppLayout :title="`Stok Sayımı - ${count.reference_number}`">
    <div class="max-w-5xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
      <div class="mb-6 flex justify-between items-center">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Stok Sayım Detayı</h1>
          <p class="mt-1 text-sm text-gray-600">Referans: {{ count.reference_number }}</p>
        </div>
        <Link :href="route('stock-counts.index')" class="text-purple-600 hover:text-purple-900">← Listeye Dön</Link>
      </div>

      <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden">
        <div class="px-6 py-4 bg-gradient-to-r from-purple-50 to-pink-50 border-b border-gray-200">
          <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-900">Sayım Bilgileri</h2>
            <span :class="getStatusClass(count.status)" class="px-3 py-1 text-sm font-medium rounded-full">{{ count.status_label }}</span>
          </div>
        </div>

        <div class="p-6 space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-500">Depo</label>
              <p class="mt-1 text-sm text-gray-900">{{ count.warehouse?.name }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-500">Malzeme</label>
              <p class="mt-1 text-sm text-gray-900">{{ count.material?.code }} - {{ count.material?.name }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-500">Sayım Tarihi</label>
              <p class="mt-1 text-sm text-gray-900">{{ formatDate(count.count_date) }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-500">Sayımı Yapan</label>
              <p class="mt-1 text-sm text-gray-900">{{ count.counted_by?.name }}</p>
            </div>
          </div>

          <div class="border-t border-gray-200 pt-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Sayım Sonuçları</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
              <div class="bg-blue-50 rounded-lg p-4">
                <label class="block text-sm font-medium text-blue-900">Sistem Miktarı</label>
                <p class="mt-2 text-2xl font-bold text-blue-700">{{ count.system_quantity }}</p>
              </div>
              <div class="bg-green-50 rounded-lg p-4">
                <label class="block text-sm font-medium text-green-900">Sayılan Miktar</label>
                <p class="mt-2 text-2xl font-bold text-green-700">{{ count.counted_quantity }}</p>
              </div>
              <div :class="getDifferenceCardClass(count.difference)">
                <label class="block text-sm font-medium">Fark</label>
                <p class="mt-2 text-2xl font-bold">{{ count.difference }}</p>
                <p class="mt-1 text-xs">{{ count.difference_type_label }}</p>
              </div>
            </div>
          </div>

          <div v-if="count.notes" class="border-t border-gray-200 pt-6">
            <label class="block text-sm font-medium text-gray-500">Not</label>
            <p class="mt-1 text-sm text-gray-900">{{ count.notes }}</p>
          </div>

          <div v-if="count.approved_by" class="border-t border-gray-200 pt-6">
            <label class="block text-sm font-medium text-gray-500">Onaylayan/Reddeden</label>
            <p class="mt-1 text-sm text-gray-900">{{ count.approved_by?.name }} - {{ formatDate(count.approved_at) }}</p>
            <p v-if="count.rejection_reason" class="mt-2 text-sm text-red-600">Red Nedeni: {{ count.rejection_reason }}</p>
          </div>

          <div v-if="count.status === 'pending'" class="border-t border-gray-200 pt-6 flex justify-end space-x-3">
            <button @click="showRejectModal = true" class="px-4 py-2 border border-red-300 text-red-700 rounded-lg hover:bg-red-50">Reddet</button>
            <button @click="approveCount" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">Onayla</button>
          </div>
        </div>
      </div>

      <!-- Reject Modal -->
      <div v-if="showRejectModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" @click="showRejectModal = false">
        <div @click.stop class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4 p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Sayımı Reddet</h3>
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Red Nedeni *</label>
            <textarea v-model="rejectForm.rejection_reason" rows="3" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500"></textarea>
          </div>
          <div class="flex justify-end space-x-3">
            <button @click="showRejectModal = false" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">İptal</button>
            <button @click="rejectCount" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Reddet</button>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  count: Object
})

const showRejectModal = ref(false)
const rejectForm = reactive({
  rejection_reason: ''
})

function formatDate(date) {
  return date ? new Date(date).toLocaleDateString('tr-TR') : '-'
}

function getStatusClass(status) {
  const classes = {
    pending: 'bg-yellow-100 text-yellow-800',
    approved: 'bg-green-100 text-green-800',
    rejected: 'bg-red-100 text-red-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

function getDifferenceCardClass(diff) {
  if (diff > 0) return 'bg-blue-50 rounded-lg p-4 text-blue-900'
  if (diff < 0) return 'bg-red-50 rounded-lg p-4 text-red-900'
  return 'bg-green-50 rounded-lg p-4 text-green-900'
}

function approveCount() {
  if (confirm('Bu sayımı onaylamak istediğinizden emin misiniz? Fark varsa stok düzeltmesi yapılacaktır.')) {
    router.post(route('stock-counts.approve', props.count.id))
  }
}

function rejectCount() {
  if (!rejectForm.rejection_reason.trim()) {
    alert('Lütfen red nedeni giriniz')
    return
  }
  router.post(route('stock-counts.reject', props.count.id), rejectForm, {
    onSuccess: () => {
      showRejectModal.value = false
    }
  })
}
</script>
