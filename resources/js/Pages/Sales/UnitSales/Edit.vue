<template>
  <AppLayout title="Satış Düzenle" :full-width="true">
    <!-- Header -->
    <template #fullWidthHeader>
      <div class="bg-gradient-to-r from-emerald-600 via-emerald-700 to-green-800 border-b border-emerald-900/20 w-full">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
          <div class="flex items-center">
            <div class="flex-shrink-0 w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm">
              <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
              </svg>
            </div>
            <div class="ml-6">
              <h1 class="text-2xl lg:text-3xl font-bold text-white">Satış Düzenle</h1>
              <p class="text-emerald-100 text-sm mt-1">{{ unitSale.sale_number }} - {{ unitSale.customer?.full_name }}</p>
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
                  <Link :href="route('sales.unit-sales.index')" class="text-emerald-100 hover:text-white text-sm">Satışlar</Link>
                </li>
                <li class="flex items-center">
                  <svg class="h-3 w-3 text-emerald-200 mx-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                  </svg>
                  <span class="text-white font-medium text-sm">{{ unitSale.sale_number }}</span>
                </li>
                <li class="flex items-center">
                  <svg class="h-3 w-3 text-emerald-200 mx-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
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

        <!-- Satış Tipi ve Tarihler -->
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-medium text-gray-900">Satış Bilgileri</h3>
          </div>
          <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
              <!-- Satış Tipi -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Satış Tipi <span class="text-red-500">*</span>
                </label>
                <select
                  v-model="form.sale_type"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                >
                  <option value="reservation">Rezervasyon</option>
                  <option value="sale">Satış</option>
                  <option value="presale">Ön Satış</option>
                </select>
              </div>

              <!-- Rezervasyon Tarihi -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Rezervasyon Tarihi</label>
                <input
                  v-model="form.reservation_date"
                  type="date"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                />
              </div>

              <!-- Satış Tarihi -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Satış Tarihi</label>
                <input
                  v-model="form.sale_date"
                  type="date"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                />
              </div>

              <!-- Sözleşme Tarihi -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Sözleşme Tarihi</label>
                <input
                  v-model="form.contract_date"
                  type="date"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                />
              </div>

              <!-- Teslim Tarihi -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Planlanan Teslim Tarihi</label>
                <input
                  v-model="form.delivery_date"
                  type="date"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                />
              </div>

              <!-- Durum -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Durum</label>
                <select
                  v-model="form.status"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                >
                  <option value="reserved">Rezerve</option>
                  <option value="contracted">Sözleşmeli</option>
                  <option value="in_payment">Ödeme Aşamasında</option>
                  <option value="completed">Tamamlandı</option>
                  <option value="cancelled">İptal Edildi</option>
                  <option value="delayed">Gecikmiş</option>
                </select>
              </div>
            </div>
          </div>
        </div>

        <!-- Fiyat Bilgileri -->
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-medium text-gray-900">Fiyat ve İndirim</h3>
          </div>
          <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
              <!-- Liste Fiyatı -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Liste Fiyatı (TL) <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="form.list_price"
                  type="number"
                  step="0.01"
                  min="0"
                  required
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                  :class="{'border-red-300 focus:ring-red-500': form.errors.list_price}"
                />
                <p v-if="form.errors.list_price" class="text-red-600 text-sm mt-2">
                  {{ form.errors.list_price }}
                </p>
              </div>

              <!-- İndirim Yüzdesi -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">İndirim Oranı (%)</label>
                <input
                  v-model="form.discount_percentage"
                  type="number"
                  step="0.01"
                  min="0"
                  max="100"
                  @input="calculateDiscount"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                />
              </div>

              <!-- İndirim Tutarı -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">İndirim Tutarı (TL)</label>
                <input
                  v-model="form.discount_amount"
                  type="number"
                  step="0.01"
                  min="0"
                  @input="calculatePercentage"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                />
              </div>

              <!-- Final Fiyat -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Net Satış Fiyatı</label>
                <div class="p-4 bg-gradient-to-br from-emerald-50 to-green-50 rounded-lg border border-emerald-200">
                  <div class="text-3xl font-bold text-emerald-700">
                    {{ formatCurrency(finalPrice) }}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Ödeme Planı -->
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-medium text-gray-900">Ödeme Planı</h3>
          </div>
          <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
              <!-- Peşinat -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Peşinat Tutarı (TL) <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="form.down_payment"
                  type="number"
                  step="0.01"
                  min="0"
                  required
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                  :class="{'border-red-300 focus:ring-red-500': form.errors.down_payment}"
                />
                <p v-if="form.errors.down_payment" class="text-red-600 text-sm mt-2">
                  {{ form.errors.down_payment }}
                </p>
              </div>

              <!-- Taksit Sayısı -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Taksit Sayısı <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="form.installment_count"
                  type="number"
                  min="0"
                  required
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                  :class="{'border-red-300 focus:ring-red-500': form.errors.installment_count}"
                />
                <p v-if="form.errors.installment_count" class="text-red-600 text-sm mt-2">
                  {{ form.errors.installment_count }}
                </p>
              </div>

              <!-- Ödeme Yöntemi -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Ödeme Yöntemi</label>
                <select
                  v-model="form.payment_method"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                >
                  <option value="cash">Nakit</option>
                  <option value="bank_transfer">Havale/EFT</option>
                  <option value="credit_card">Kredi Kartı</option>
                  <option value="check">Çek</option>
                  <option value="bank_loan">Banka Kredisi</option>
                </select>
              </div>

              <!-- Aylık Taksit Tutarı -->
              <div class="lg:col-span-3">
                <div class="p-4 bg-gradient-to-br from-blue-50 to-indigo-50 rounded-lg border border-blue-200">
                  <div class="flex justify-between items-center">
                    <div>
                      <div class="text-sm text-blue-600 mb-1">Aylık Taksit Tutarı</div>
                      <div class="text-2xl font-bold text-blue-700">
                        {{ formatCurrency(monthlyInstallment) }}
                      </div>
                    </div>
                    <div class="text-right">
                      <div class="text-sm text-blue-600 mb-1">Kalan Tutar (Taksitli)</div>
                      <div class="text-xl font-bold text-blue-700">
                        {{ formatCurrency(remainingAmount) }}
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Tapu Bilgileri -->
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-medium text-gray-900">Tapu Bilgileri</h3>
          </div>
          <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
              <!-- Tapu Durumu -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tapu Durumu</label>
                <select
                  v-model="form.deed_status"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                >
                  <option value="not_transferred">Devredilmedi</option>
                  <option value="in_progress">İşlemde</option>
                  <option value="transferred">Devredildi</option>
                  <option value="postponed">Ertelendi</option>
                </select>
              </div>

              <!-- Tapu Tipi -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tapu Tipi</label>
                <input
                  v-model="form.deed_type"
                  type="text"
                  placeholder="Örn: Kat Mülkiyeti, Kat İrtifakı"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                />
              </div>

              <!-- Tapu Devir Tarihi -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tapu Devir Tarihi</label>
                <input
                  v-model="form.deed_transfer_date"
                  type="date"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
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
              class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
              placeholder="Satış kaydı ile ilgili ek notlar, açıklamalar veya detaylar..."
            ></textarea>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex justify-end space-x-4 pb-6">
          <Link
            :href="route('sales.unit-sales.index')"
            class="px-6 py-3 bg-white border-2 border-gray-300 rounded-lg font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all"
          >
            İptal
          </Link>
          <button
            type="submit"
            :disabled="form.processing"
            class="px-6 py-3 bg-gradient-to-r from-emerald-600 to-green-600 border border-transparent rounded-lg font-medium text-white hover:from-emerald-700 hover:to-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all"
          >
            <span v-if="form.processing">Güncelleniyor...</span>
            <span v-else>Satışı Güncelle</span>
          </button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed } from 'vue'
import { useForm, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  unitSale: {
    type: Object,
    required: true
  }
})

const form = useForm({
  sale_type: props.unitSale.sale_type || 'sale',
  list_price: props.unitSale.list_price || 0,
  discount_amount: props.unitSale.discount_amount || 0,
  discount_percentage: props.unitSale.discount_percentage || 0,
  down_payment: props.unitSale.down_payment || 0,
  installment_count: props.unitSale.installment_count || 0,
  payment_method: props.unitSale.payment_method || 'cash',
  reservation_date: props.unitSale.reservation_date || null,
  sale_date: props.unitSale.sale_date || null,
  contract_date: props.unitSale.contract_date || null,
  delivery_date: props.unitSale.delivery_date || null,
  deed_status: props.unitSale.deed_status || 'not_transferred',
  deed_type: props.unitSale.deed_type || '',
  deed_transfer_date: props.unitSale.deed_transfer_date || null,
  status: props.unitSale.status || 'reserved',
  notes: props.unitSale.notes || ''
})

const finalPrice = computed(() => {
  const listPrice = parseFloat(form.list_price) || 0
  const discountAmount = parseFloat(form.discount_amount) || 0
  return listPrice - discountAmount
})

const remainingAmount = computed(() => {
  const downPayment = parseFloat(form.down_payment) || 0
  return finalPrice.value - downPayment
})

const monthlyInstallment = computed(() => {
  const installmentCount = parseInt(form.installment_count) || 1
  if (installmentCount === 0) return 0
  return remainingAmount.value / installmentCount
})

const calculateDiscount = () => {
  const listPrice = parseFloat(form.list_price) || 0
  const percentage = parseFloat(form.discount_percentage) || 0
  form.discount_amount = (listPrice * percentage / 100).toFixed(2)
}

const calculatePercentage = () => {
  const listPrice = parseFloat(form.list_price) || 0
  const discountAmount = parseFloat(form.discount_amount) || 0
  if (listPrice > 0) {
    form.discount_percentage = ((discountAmount / listPrice) * 100).toFixed(2)
  }
}

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('tr-TR', {
    style: 'currency',
    currency: 'TRY',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0
  }).format(amount || 0)
}

const submit = () => {
  form.put(route('sales.unit-sales.update', props.unitSale.id))
}
</script>
