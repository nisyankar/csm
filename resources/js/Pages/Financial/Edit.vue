<template>
  <AppLayout :title="`İşlem #${transaction.id} Düzenle`" :full-width="true">
    <!-- Full-width header -->
    <template #fullWidthHeader>
      <div class="bg-gradient-to-r from-emerald-600 via-green-700 to-teal-800 border-b border-emerald-900/20 w-full">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
          <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
              <div class="flex-shrink-0 w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm">
                <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                </svg>
              </div>
              <div>
                <h1 class="text-2xl lg:text-3xl font-bold text-white">Finansal İşlem Düzenle</h1>
                <p class="text-emerald-100 text-sm mt-1">İşlem #{{ transaction.id }}</p>
              </div>
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
                  <Link :href="route('financial.index')" class="text-emerald-100 hover:text-white text-sm">Finansal İşlemler</Link>
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

    <!-- Main Content -->
    <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
      <form @submit.prevent="submit" class="space-y-6">
        <!-- Temel Bilgiler -->
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-gray-100">
            <h3 class="text-lg font-semibold text-gray-900">Temel Bilgiler</h3>
          </div>
          <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
              <!-- İşlem Tipi -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  İşlem Tipi <span class="text-red-500">*</span>
                </label>
                <div class="grid grid-cols-2 gap-3">
                  <button
                    type="button"
                    @click="form.transaction_type = 'income'"
                    class="px-4 py-3 rounded-lg border-2 transition-all font-medium text-sm"
                    :class="form.transaction_type === 'income'
                      ? 'border-green-500 bg-green-50 text-green-700'
                      : 'border-gray-300 bg-white text-gray-700 hover:border-green-300'"
                  >
                    <svg class="w-5 h-5 mx-auto mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12" />
                    </svg>
                    Gelir
                  </button>
                  <button
                    type="button"
                    @click="form.transaction_type = 'expense'"
                    class="px-4 py-3 rounded-lg border-2 transition-all font-medium text-sm"
                    :class="form.transaction_type === 'expense'
                      ? 'border-red-500 bg-red-50 text-red-700'
                      : 'border-gray-300 bg-white text-gray-700 hover:border-red-300'"
                  >
                    <svg class="w-5 h-5 mx-auto mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6" />
                    </svg>
                    Gider
                  </button>
                </div>
                <p v-if="form.errors.transaction_type" class="text-red-600 text-sm mt-2">
                  {{ form.errors.transaction_type }}
                </p>
              </div>

              <!-- Proje -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Proje <span class="text-red-500">*</span>
                </label>
                <select
                  v-model="form.project_id"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                  :class="{'border-red-300 focus:ring-red-500': form.errors.project_id}"
                >
                  <option value="">Seçiniz...</option>
                  <option v-for="project in projects" :key="project.id" :value="project.id">
                    {{ project.name }}
                  </option>
                </select>
                <p v-if="form.errors.project_id" class="text-red-600 text-sm mt-2">
                  {{ form.errors.project_id }}
                </p>
              </div>

              <!-- Kategori -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Kategori <span class="text-red-500">*</span>
                </label>
                <select
                  v-model="form.category_id"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                  :class="{'border-red-300 focus:ring-red-500': form.errors.category_id}"
                  :disabled="!form.transaction_type"
                >
                  <option value="">Önce işlem tipi seçiniz...</option>
                  <option v-if="form.transaction_type === 'income'" v-for="category in incomeCategories" :key="category.id" :value="category.id">
                    {{ category.code }} - {{ category.name }}
                  </option>
                  <option v-if="form.transaction_type === 'expense'" v-for="category in expenseCategories" :key="category.id" :value="category.id">
                    {{ category.code }} - {{ category.name }}
                  </option>
                </select>
                <p v-if="form.errors.category_id" class="text-red-600 text-sm mt-2">
                  {{ form.errors.category_id }}
                </p>
              </div>

              <!-- İşlem Tarihi -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  İşlem Tarihi <span class="text-red-500">*</span>
                </label>
                <input
                  type="date"
                  v-model="form.transaction_date"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                  :class="{'border-red-300 focus:ring-red-500': form.errors.transaction_date}"
                />
                <p v-if="form.errors.transaction_date" class="text-red-600 text-sm mt-2">
                  {{ form.errors.transaction_date }}
                </p>
              </div>

              <!-- Tutar -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Tutar (TRY) <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                  <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500">₺</span>
                  <input
                    type="number"
                    step="0.01"
                    v-model="form.amount"
                    class="w-full pl-8 pr-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                    :class="{'border-red-300 focus:ring-red-500': form.errors.amount}"
                    placeholder="0.00"
                  />
                </div>
                <p v-if="form.errors.amount" class="text-red-600 text-sm mt-2">
                  {{ form.errors.amount }}
                </p>
              </div>

              <!-- Açıklama -->
              <div class="lg:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Açıklama <span class="text-red-500">*</span>
                </label>
                <textarea
                  v-model="form.description"
                  rows="3"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                  :class="{'border-red-300 focus:ring-red-500': form.errors.description}"
                  placeholder="İşlem detaylarını girin..."
                ></textarea>
                <p v-if="form.errors.description" class="text-red-600 text-sm mt-2">
                  {{ form.errors.description }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Fatura ve Muhasebe Bilgileri -->
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-gray-100">
            <h3 class="text-lg font-semibold text-gray-900">Fatura ve Muhasebe Bilgileri</h3>
            <p class="text-sm text-gray-600 mt-1">Opsiyonel bilgiler</p>
          </div>
          <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
              <!-- Fatura Numarası -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Fatura Numarası
                </label>
                <input
                  type="text"
                  v-model="form.invoice_number"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                  placeholder="Örn: FT2025-001"
                />
              </div>

              <!-- Fatura Tarihi -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Fatura Tarihi
                </label>
                <input
                  type="date"
                  v-model="form.invoice_date"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                />
              </div>

              <!-- Muhasebe Kodu -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Muhasebe Kodu
                </label>
                <input
                  type="text"
                  v-model="form.accounting_code"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                  placeholder="Örn: 600.01.001"
                />
              </div>

              <!-- Ödeme Yöntemi -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Ödeme Yöntemi
                </label>
                <select
                  v-model="form.payment_method"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                >
                  <option value="">Seçiniz...</option>
                  <option value="cash">Nakit</option>
                  <option value="bank_transfer">Banka Havalesi</option>
                  <option value="credit_card">Kredi Kartı</option>
                  <option value="check">Çek</option>
                  <option value="promissory_note">Senet</option>
                </select>
              </div>
            </div>
          </div>
        </div>

        <!-- Ödeme Bilgileri -->
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-gray-100">
            <h3 class="text-lg font-semibold text-gray-900">Ödeme Bilgileri</h3>
            <p class="text-sm text-gray-600 mt-1">Opsiyonel: İlk ödeme kaydı</p>
          </div>
          <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
              <!-- Ödeme Durumu -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Ödeme Durumu
                </label>
                <select
                  v-model="form.payment_status"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                >
                  <option value="pending">Beklemede</option>
                  <option value="partial">Kısmi Ödendi</option>
                  <option value="paid">Ödendi</option>
                </select>
              </div>

              <!-- Ödenen Tutar -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Ödenen Tutar
                </label>
                <div class="relative">
                  <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500">₺</span>
                  <input
                    type="number"
                    step="0.01"
                    v-model="form.paid_amount"
                    class="w-full pl-8 pr-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                    placeholder="0.00"
                  />
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Form Actions -->
        <div class="flex items-center justify-end gap-3 pt-4">
          <Link
            :href="route('financial.index')"
            class="px-6 py-3 border-2 border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 transition-colors"
          >
            İptal
          </Link>
          <button
            type="submit"
            :disabled="form.processing"
            class="px-6 py-3 bg-emerald-600 text-white rounded-lg font-medium hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors shadow-lg hover:shadow-xl"
          >
            <span v-if="form.processing">Kaydediliyor...</span>
            <span v-else>Kaydet</span>
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
  transaction: {
    type: Object,
    required: true
  },
  projects: {
    type: Array,
    default: () => []
  },
  incomeCategories: {
    type: Array,
    default: () => []
  },
  expenseCategories: {
    type: Array,
    default: () => []
  }
})

const form = useForm({
  transaction_type: props.transaction.transaction_type,
  project_id: props.transaction.project_id,
  category_id: props.transaction.category_id,
  transaction_date: props.transaction.transaction_date,
  amount: props.transaction.amount,
  description: props.transaction.description,
  invoice_number: props.transaction.invoice_number || '',
  invoice_date: props.transaction.invoice_date || '',
  accounting_code: props.transaction.accounting_code || '',
  payment_method: props.transaction.payment_method || '',
  payment_status: props.transaction.payment_status,
  paid_amount: props.transaction.paid_amount || 0
})

const submit = () => {
  form.put(route('financial.update', props.transaction.id), {
    onSuccess: () => {
      // Redirect will be handled by controller
    }
  })
}
</script>