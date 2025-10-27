<template>
  <AppLayout title="Ödeme Düzenle" :full-width="true">
    <!-- Header -->
    <template #fullWidthHeader>
      <div class="bg-gradient-to-r from-amber-600 via-amber-700 to-orange-800 border-b border-amber-900/20 w-full">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
          <div class="flex items-center">
            <div class="flex-shrink-0 w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm">
              <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
              </svg>
            </div>
            <div class="ml-6">
              <h1 class="text-2xl lg:text-3xl font-bold text-white">Ödeme Düzenle</h1>
              <p class="text-amber-100 text-sm mt-1">{{ payment.payment_number }}</p>
            </div>
          </div>
        </div>

        <!-- Breadcrumb -->
        <div class="bg-black/10 border-t border-white/10">
          <div class="w-full px-4 sm:px-6 lg:px-8">
            <div class="flex items-center space-x-2 py-2">
              <ol class="flex items-center space-x-2">
                <li>
                  <Link :href="route('dashboard')" class="text-amber-100 hover:text-white transition-colors">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                    </svg>
                  </Link>
                </li>
                <li class="flex items-center">
                  <svg class="h-3 w-3 text-amber-200 mx-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                  </svg>
                  <Link :href="route('sales.payments.index')" class="text-amber-100 hover:text-white text-sm">Ödeme Takibi</Link>
                </li>
                <li class="flex items-center">
                  <svg class="h-3 w-3 text-amber-200 mx-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                  </svg>
                  <span class="text-white font-medium text-sm">{{ payment.payment_number }}</span>
                </li>
                <li class="flex items-center">
                  <svg class="h-3 w-3 text-amber-200 mx-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                  </svg>
                  <span class="text-white font-medium text-sm">Düzenle</span>
                </li>
              </ol>
            </div>
          </div>
        </div>
      </div>
    </template>

    <!-- Content -->
    <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
      <form @submit.prevent="submit" class="space-y-6">
        <!-- General Errors -->
        <div v-if="Object.keys(form.errors).length > 0" class="bg-red-50 border border-red-200 text-red-800 rounded-xl p-4">
          <h4 class="font-semibold mb-2">Lütfen aşağıdaki hataları düzeltin:</h4>
          <ul class="list-disc list-inside space-y-1">
            <li v-for="(error, field) in form.errors" :key="field" class="text-sm">{{ error }}</li>
          </ul>
        </div>

        <!-- Ödeme Bilgileri -->
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-medium text-gray-900">Ödeme Bilgileri</h3>
          </div>
          <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
              <!-- Ödeme Tipi -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Ödeme Tipi <span class="text-red-500">*</span>
                </label>
                <select
                  v-model="form.payment_type"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-all"
                >
                  <option value="down_payment">Peşinat</option>
                  <option value="installment">Taksit</option>
                  <option value="additional">Ek Ödeme</option>
                  <option value="penalty">Gecikme Cezası</option>
                  <option value="discount">İndirim</option>
                </select>
              </div>

              <!-- Taksit Numarası -->
              <div v-if="form.payment_type === 'installment'">
                <label class="block text-sm font-medium text-gray-700 mb-2">Taksit Numarası</label>
                <input
                  v-model="form.installment_number"
                  type="number"
                  min="1"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-all"
                />
              </div>

              <!-- Tutar -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Tutar (TL) <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="form.amount"
                  type="number"
                  step="0.01"
                  min="0"
                  required
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-all"
                  :class="{'border-red-300 focus:ring-red-500': form.errors.amount}"
                />
                <p v-if="form.errors.amount" class="text-red-600 text-sm mt-2">
                  {{ form.errors.amount }}
                </p>
              </div>

              <!-- Ödenen Tutar -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Ödenen Tutar (TL)</label>
                <input
                  v-model="form.paid_amount"
                  type="number"
                  step="0.01"
                  min="0"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-all"
                />
              </div>

              <!-- Vade Tarihi -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Vade Tarihi <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="form.due_date"
                  type="date"
                  required
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-all"
                  :class="{'border-red-300 focus:ring-red-500': form.errors.due_date}"
                />
                <p v-if="form.errors.due_date" class="text-red-600 text-sm mt-2">
                  {{ form.errors.due_date }}
                </p>
              </div>

              <!-- Ödeme Tarihi -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Ödeme Tarihi</label>
                <input
                  v-model="form.payment_date"
                  type="date"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-all"
                />
              </div>

              <!-- Gecikme Cezası -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Gecikme Cezası (TL)</label>
                <input
                  v-model="form.late_fee"
                  type="number"
                  step="0.01"
                  min="0"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-all"
                />
              </div>

              <!-- Durum -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Durum</label>
                <select
                  v-model="form.status"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-all"
                >
                  <option value="pending">Bekliyor</option>
                  <option value="paid">Ödendi</option>
                  <option value="partial">Kısmi Ödendi</option>
                  <option value="overdue">Gecikmiş</option>
                  <option value="cancelled">İptal</option>
                </select>
              </div>

              <!-- Ödeme Yöntemi -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Ödeme Yöntemi</label>
                <select
                  v-model="form.payment_method"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-all"
                >
                  <option value="cash">Nakit</option>
                  <option value="bank_transfer">Havale/EFT</option>
                  <option value="credit_card">Kredi Kartı</option>
                  <option value="check">Çek</option>
                  <option value="bank_loan">Banka Kredisi</option>
                </select>
              </div>
            </div>
          </div>
        </div>

        <!-- Banka/Çek Bilgileri -->
        <div v-if="['bank_transfer', 'check'].includes(form.payment_method)" class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-medium text-gray-900">Banka ve Çek Bilgileri</h3>
          </div>
          <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
              <!-- Banka Adı -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Banka Adı</label>
                <input
                  v-model="form.bank_name"
                  type="text"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-all"
                />
              </div>

              <!-- Çek Numarası -->
              <div v-if="form.payment_method === 'check'">
                <label class="block text-sm font-medium text-gray-700 mb-2">Çek Numarası</label>
                <input
                  v-model="form.check_number"
                  type="text"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-all"
                />
              </div>

              <!-- Çek Tarihi -->
              <div v-if="form.payment_method === 'check'">
                <label class="block text-sm font-medium text-gray-700 mb-2">Çek Tarihi</label>
                <input
                  v-model="form.check_date"
                  type="date"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-all"
                />
              </div>

              <!-- İşlem Referansı -->
              <div v-if="form.payment_method === 'bank_transfer'">
                <label class="block text-sm font-medium text-gray-700 mb-2">İşlem Referansı</label>
                <input
                  v-model="form.transaction_reference"
                  type="text"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-all"
                />
              </div>
            </div>
          </div>
        </div>

        <!-- Notlar -->
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-medium text-gray-900">Ek Notlar</h3>
          </div>
          <div class="p-6">
            <textarea
              v-model="form.notes"
              rows="4"
              class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-all"
              placeholder="Ödeme kaydı ile ilgili ek notlar, açıklamalar veya detaylar..."
            ></textarea>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex justify-end space-x-4 pb-6">
          <Link
            :href="route('sales.payments.index')"
            class="px-6 py-3 bg-white border-2 border-gray-300 rounded-lg font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all"
          >
            İptal
          </Link>
          <button
            type="submit"
            :disabled="form.processing"
            class="px-6 py-3 bg-gradient-to-r from-amber-600 to-orange-600 border border-transparent rounded-lg font-medium text-white hover:from-amber-700 hover:to-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all"
          >
            <span v-if="form.processing">Güncelleniyor...</span>
            <span v-else>Ödemeyi Güncelle</span>
          </button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>

<script setup>
import { useForm, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  payment: {
    type: Object,
    required: true
  }
})

const form = useForm({
  installment_number: props.payment.installment_number || null,
  payment_type: props.payment.payment_type || 'installment',
  amount: props.payment.amount || 0,
  paid_amount: props.payment.paid_amount || 0,
  late_fee: props.payment.late_fee || 0,
  due_date: props.payment.due_date || '',
  payment_date: props.payment.payment_date || null,
  payment_method: props.payment.payment_method || 'cash',
  bank_name: props.payment.bank_name || '',
  check_number: props.payment.check_number || '',
  check_date: props.payment.check_date || null,
  transaction_reference: props.payment.transaction_reference || '',
  status: props.payment.status || 'pending',
  notes: props.payment.notes || ''
})

const submit = () => {
  form.put(route('sales.payments.update', props.payment.id))
}
</script>
