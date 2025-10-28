<template>
  <AppLayout title="Yeni Denetim Kuruluşu" :full-width="true">
    <template #fullWidthHeader>
      <div class="bg-gradient-to-r from-purple-600 via-purple-700 to-indigo-800 border-b border-purple-900/20 w-full">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
          <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
              <div class="flex-shrink-0 w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm">
                <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
              </div>
              <div>
                <h1 class="text-2xl lg:text-3xl font-bold text-white">Yeni Denetim Kuruluşu</h1>
                <p class="text-purple-100 text-sm mt-1">Denetim kuruluşu bilgilerini girin</p>
              </div>
            </div>
          </div>
        </div>

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
                  <Link :href="route('inspection-companies.index')" class="text-purple-100 hover:text-white text-sm">Denetim Kuruluşları</Link>
                </li>
                <li class="flex items-center">
                  <svg class="h-3 w-3 text-purple-200 mx-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                  </svg>
                  <span class="text-white font-medium text-sm">Yeni Kayıt</span>
                </li>
              </ol>
            </div>
          </div>
        </div>
      </div>
    </template>

    <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
      <form @submit.prevent="submit" class="space-y-6">
        <!-- Temel Bilgiler -->
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-medium text-gray-900">Kuruluş Bilgileri</h3>
          </div>
          <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Kuruluş Adı <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="form.company_name"
                  type="text"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all"
                  :class="{'border-red-300 focus:ring-red-500': form.errors.company_name}"
                />
                <p v-if="form.errors.company_name" class="text-red-600 text-sm mt-2">{{ form.errors.company_name }}</p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Faaliyet Belgesi No <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="form.license_number"
                  type="text"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all"
                  :class="{'border-red-300 focus:ring-red-500': form.errors.license_number}"
                />
                <p v-if="form.errors.license_number" class="text-red-600 text-sm mt-2">{{ form.errors.license_number }}</p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Durum</label>
                <select
                  v-model="form.is_active"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all"
                >
                  <option :value="true">Aktif</option>
                  <option :value="false">Pasif</option>
                </select>
              </div>
            </div>
          </div>
        </div>

        <!-- İletişim Bilgileri -->
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-medium text-gray-900">İletişim Bilgileri</h3>
          </div>
          <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">İrtibat Kişisi</label>
                <input
                  v-model="form.contact_person"
                  type="text"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Telefon</label>
                <input
                  v-model="form.phone"
                  type="text"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">E-posta</label>
                <input
                  v-model="form.email"
                  type="email"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all"
                  :class="{'border-red-300 focus:ring-red-500': form.errors.email}"
                />
                <p v-if="form.errors.email" class="text-red-600 text-sm mt-2">{{ form.errors.email }}</p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Adres</label>
                <textarea
                  v-model="form.address"
                  rows="3"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all"
                ></textarea>
              </div>

              <div class="lg:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Notlar</label>
                <textarea
                  v-model="form.notes"
                  rows="3"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all"
                ></textarea>
              </div>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex justify-end gap-3">
          <Link
            :href="route('inspection-companies.index')"
            class="px-6 py-3 border border-gray-300 rounded-lg hover:bg-gray-50 text-gray-700 font-medium transition-colors"
          >
            İptal
          </Link>
          <button
            type="submit"
            :disabled="form.processing"
            class="px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 font-medium transition-colors disabled:opacity-50"
          >
            {{ form.processing ? 'Kaydediliyor...' : 'Kaydet' }}
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
  company_name: '',
  license_number: '',
  contact_person: '',
  phone: '',
  email: '',
  address: '',
  is_active: true,
  notes: '',
})

const submit = () => {
  form.post(route('inspection-companies.store'))
}
</script>
