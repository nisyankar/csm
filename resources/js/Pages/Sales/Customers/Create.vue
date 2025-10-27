<template>
  <AppLayout title="Yeni Müşteri" :full-width="true">
    <!-- Header -->
    <template #fullWidthHeader>
      <div class="bg-gradient-to-r from-purple-600 via-purple-700 to-indigo-800 border-b border-purple-900/20 w-full">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
          <div class="flex items-center">
            <div class="flex-shrink-0 w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm">
              <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z" />
              </svg>
            </div>
            <div class="ml-6">
              <h1 class="text-2xl lg:text-3xl font-bold text-white">Yeni Müşteri</h1>
              <p class="text-purple-100 text-sm mt-1">Yeni müşteri kaydı oluşturun</p>
            </div>
          </div>
        </div>

        <!-- Breadcrumb -->
        <div class="bg-black/10 border-t border-white/10">
          <div class="w-full px-4 sm:px-6 lg:px-8">
            <div class="flex items-center space-x-2 py-2">
              <ol class="flex items-center space-x-2">
                <li>
                  <Link :href="route('dashboard')" class="text-purple-100 hover:text-white transition-colors">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                    </svg>
                  </Link>
                </li>
                <li class="flex items-center">
                  <svg class="h-3 w-3 text-purple-200 mx-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                  </svg>
                  <Link :href="route('sales.customers.index')" class="text-purple-100 hover:text-white text-sm">Müşteriler</Link>
                </li>
                <li class="flex items-center">
                  <svg class="h-3 w-3 text-purple-200 mx-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                  </svg>
                  <span class="text-xs font-medium text-white">Yeni Müşteri</span>
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

        <!-- Customer Type Selection -->
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 p-6">
          <h3 class="text-lg font-semibold text-gray-900 border-b pb-3 mb-4">Müşteri Tipi</h3>
          <div class="flex gap-4">
            <label class="flex-1 cursor-pointer">
              <input type="radio" v-model="form.customer_type" value="individual" class="sr-only peer" />
              <div class="p-4 border-2 rounded-lg transition-all peer-checked:border-purple-500 peer-checked:bg-purple-50 hover:bg-gray-50">
                <div class="flex items-center space-x-3">
                  <div class="flex-shrink-0 w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                    </svg>
                  </div>
                  <div>
                    <p class="font-semibold text-gray-900">Bireysel Müşteri</p>
                    <p class="text-sm text-gray-500">Şahıs müşteri</p>
                  </div>
                </div>
              </div>
            </label>
            <label class="flex-1 cursor-pointer">
              <input type="radio" v-model="form.customer_type" value="corporate" class="sr-only peer" />
              <div class="p-4 border-2 rounded-lg transition-all peer-checked:border-purple-500 peer-checked:bg-purple-50 hover:bg-gray-50">
                <div class="flex items-center space-x-3">
                  <div class="flex-shrink-0 w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z" />
                    </svg>
                  </div>
                  <div>
                    <p class="font-semibold text-gray-900">Kurumsal Müşteri</p>
                    <p class="text-sm text-gray-500">Şirket/Firma</p>
                  </div>
                </div>
              </div>
            </label>
          </div>
        </div>

        <!-- Basic Info -->
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 p-6 space-y-4">
          <h3 class="text-lg font-semibold text-gray-900 border-b pb-3">Temel Bilgiler</h3>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div v-if="form.customer_type === 'corporate'">
              <label class="block text-sm font-medium text-gray-700 mb-2">Şirket Adı *</label>
              <input v-model="form.company_name" type="text" :required="form.customer_type === 'corporate'"
                class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                :class="form.errors.company_name ? 'border-red-500' : 'border-gray-300'" />
              <p v-if="form.errors.company_name" class="mt-1 text-sm text-red-600">{{ form.errors.company_name }}</p>
            </div>

            <div :class="form.customer_type === 'corporate' ? 'md:col-span-1' : 'md:col-span-2'">
              <label class="block text-sm font-medium text-gray-700 mb-2">Ad *</label>
              <input v-model="form.first_name" type="text" required
                class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                :class="form.errors.first_name ? 'border-red-500' : 'border-gray-300'" />
              <p v-if="form.errors.first_name" class="mt-1 text-sm text-red-600">{{ form.errors.first_name }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Soyad *</label>
              <input v-model="form.last_name" type="text" required
                class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                :class="form.errors.last_name ? 'border-red-500' : 'border-gray-300'" />
              <p v-if="form.errors.last_name" class="mt-1 text-sm text-red-600">{{ form.errors.last_name }}</p>
            </div>

            <div v-if="form.customer_type === 'individual'">
              <label class="block text-sm font-medium text-gray-700 mb-2">TC Kimlik No</label>
              <input v-model="form.tc_number" type="text" maxlength="11" pattern="[0-9]{11}"
                class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                :class="form.errors.tc_number ? 'border-red-500' : 'border-gray-300'" />
              <p v-if="form.errors.tc_number" class="mt-1 text-sm text-red-600">{{ form.errors.tc_number }}</p>
            </div>

            <div v-if="form.customer_type === 'corporate'">
              <label class="block text-sm font-medium text-gray-700 mb-2">Vergi Dairesi</label>
              <input v-model="form.tax_office" type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent" />
            </div>

            <div v-if="form.customer_type === 'corporate'">
              <label class="block text-sm font-medium text-gray-700 mb-2">Vergi Numarası</label>
              <input v-model="form.tax_number" type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent" />
            </div>
          </div>
        </div>

        <!-- Contact Info -->
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 p-6 space-y-4">
          <h3 class="text-lg font-semibold text-gray-900 border-b pb-3">İletişim Bilgileri</h3>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">E-posta *</label>
              <input v-model="form.email" type="email" required
                class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                :class="form.errors.email ? 'border-red-500' : 'border-gray-300'" />
              <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Telefon *</label>
              <input v-model="form.phone" type="tel" required placeholder="0532 123 4567"
                class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                :class="form.errors.phone ? 'border-red-500' : 'border-gray-300'" />
              <p v-if="form.errors.phone" class="mt-1 text-sm text-red-600">{{ form.errors.phone }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Şehir</label>
              <input v-model="form.city" type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent" />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">İlçe</label>
              <input v-model="form.district" type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent" />
            </div>

            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-2">Adres</label>
              <textarea v-model="form.address" rows="2"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"></textarea>
            </div>
          </div>
        </div>

        <!-- Additional Info -->
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 p-6 space-y-4">
          <h3 class="text-lg font-semibold text-gray-900 border-b pb-3">Ek Bilgiler</h3>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Müşteri Durumu</label>
              <select v-model="form.customer_status"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                <option value="potential">Potansiyel</option>
                <option value="interested">İlgileniyor</option>
                <option value="active">Aktif</option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Referans Kaynağı</label>
              <input v-model="form.reference_source" type="text" placeholder="Örn: Website, Referans, Sosyal Medya"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent" />
            </div>

            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-2">Notlar</label>
              <textarea v-model="form.notes" rows="3"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"></textarea>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-between gap-4 pt-4">
          <Link :href="route('sales.customers.index')"
            class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
            İptal
          </Link>
          <button type="submit" :disabled="form.processing"
            class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
            <span v-if="form.processing">Kaydediliyor...</span>
            <span v-else>Müşteriyi Kaydet</span>
          </button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>

<script setup>
import { useForm, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const form = useForm({
  customer_type: 'individual',
  first_name: '',
  last_name: '',
  tc_number: '',
  company_name: '',
  tax_office: '',
  tax_number: '',
  email: '',
  phone: '',
  city: '',
  district: '',
  address: '',
  customer_status: 'potential',
  reference_source: '',
  notes: ''
})

const submit = () => {
  form.post(route('sales.customers.store'))
}
</script>
