<template>
  <AppLayout title="Ekipman Düzenle" :full-width="true">
    <template #fullWidthHeader>
      <div class="bg-gradient-to-r from-yellow-600 via-amber-600 to-orange-600 border-b border-yellow-900/20 w-full">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
          <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
              <div class="flex-shrink-0 w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm">
                <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                </svg>
              </div>
              <div>
                <h1 class="text-2xl lg:text-3xl font-bold text-white">Ekipman Düzenle</h1>
                <p class="text-yellow-100 text-sm mt-1">{{ equipment.code }} - {{ equipment.name }}</p>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-black/10 border-t border-white/10">
          <div class="w-full px-4 sm:px-6 lg:px-8">
            <div class="flex items-center space-x-2 py-2">
              <ol class="flex items-center space-x-2">
                <li>
                  <Link :href="route('dashboard')" class="text-yellow-100 hover:text-white transition-colors">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                    </svg>
                  </Link>
                </li>
                <li class="flex items-center">
                  <svg class="h-3 w-3 text-yellow-200 mx-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                  </svg>
                  <Link :href="route('equipments.index')" class="text-yellow-100 hover:text-white text-sm">Ekipmanlar</Link>
                </li>
                <li class="flex items-center">
                  <svg class="h-3 w-3 text-yellow-200 mx-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
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

    <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
      <form @submit.prevent="submit" class="space-y-6">
        <!-- Temel Bilgiler -->
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-medium text-gray-900">Temel Bilgiler</h3>
          </div>
          <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Ekipman Kodu <span class="text-red-500">*</span>
                </label>
                <input v-model="form.code" type="text" required class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-yellow-500 focus:border-transparent transition-all" />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Ekipman Adı <span class="text-red-500">*</span>
                </label>
                <input v-model="form.name" type="text" required class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-yellow-500 focus:border-transparent transition-all" />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Ekipman Tipi <span class="text-red-500">*</span>
                </label>
                <select v-model="form.type" required class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-yellow-500 focus:border-transparent transition-all">
                  <option value="excavator">Ekskavatör</option>
                  <option value="bulldozer">Dozer</option>
                  <option value="crane">Vinç</option>
                  <option value="loader">Yükleyici</option>
                  <option value="grader">Greyder</option>
                  <option value="roller">Silindir</option>
                  <option value="forklift">Forklift</option>
                  <option value="concrete_mixer">Beton Mikseri</option>
                  <option value="pump">Pompa</option>
                  <option value="generator">Jeneratör</option>
                  <option value="compressor">Kompresör</option>
                  <option value="tower_crane">Kule Vinç</option>
                  <option value="mobile_crane">Mobil Vinç</option>
                  <option value="other">Diğer</option>
                </select>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Sahiplik <span class="text-red-500">*</span>
                </label>
                <select v-model="form.ownership" required class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-yellow-500 focus:border-transparent transition-all">
                  <option value="owned">Şirket Malı</option>
                  <option value="rented">Kiralık</option>
                  <option value="leased">Leasingli</option>
                </select>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Marka</label>
                <input v-model="form.brand" type="text" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-yellow-500 focus:border-transparent transition-all" />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Model</label>
                <input v-model="form.model" type="text" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-yellow-500 focus:border-transparent transition-all" />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Seri Numarası</label>
                <input v-model="form.serial_number" type="text" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-yellow-500 focus:border-transparent transition-all" />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Üretim Yılı</label>
                <input v-model="form.manufacture_year" type="number" min="1900" :max="new Date().getFullYear() + 1" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-yellow-500 focus:border-transparent transition-all" />
              </div>

              <div class="lg:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Açıklama</label>
                <textarea v-model="form.description" rows="3" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-yellow-500 focus:border-transparent transition-all"></textarea>
              </div>
            </div>
          </div>
        </div>

        <!-- Kira Bilgileri -->
        <div v-if="form.ownership === 'rented' || form.ownership === 'leased'" class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-medium text-gray-900">Kira Bilgileri</h3>
          </div>
          <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Kira Şirketi</label>
                <input v-model="form.rental_company" type="text" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-yellow-500 focus:border-transparent transition-all" />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Günlük Kira Maliyeti (₺)</label>
                <input v-model="form.rental_cost_daily" type="number" step="0.01" min="0" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-yellow-500 focus:border-transparent transition-all" />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Aylık Kira Maliyeti (₺)</label>
                <input v-model="form.rental_cost_monthly" type="number" step="0.01" min="0" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-yellow-500 focus:border-transparent transition-all" />
              </div>
            </div>
          </div>
        </div>

        <!-- Durum ve Konum -->
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-medium text-gray-900">Durum ve Konum</h3>
          </div>
          <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Durum <span class="text-red-500">*</span>
                </label>
                <select v-model="form.status" required class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-yellow-500 focus:border-transparent transition-all">
                  <option value="available">Müsait</option>
                  <option value="in_use">Kullanımda</option>
                  <option value="maintenance">Bakımda</option>
                  <option value="repair">Onarımda</option>
                  <option value="out_of_service">Hizmet Dışı</option>
                </select>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Bulunduğu Proje</label>
                <select v-model="form.current_project_id" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-yellow-500 focus:border-transparent transition-all">
                  <option :value="null">Yok</option>
                  <option v-for="project in projects" :key="project.id" :value="project.id">{{ project.name }}</option>
                </select>
              </div>

              <div class="lg:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Güncel Konum</label>
                <input v-model="form.current_location" type="text" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-yellow-500 focus:border-transparent transition-all" />
              </div>
            </div>
          </div>
        </div>

        <!-- Bakım -->
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-medium text-gray-900">Bakım Ayarları</h3>
          </div>
          <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Son Bakım Tarihi</label>
                <input v-model="form.last_maintenance_date" type="date" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-yellow-500 focus:border-transparent transition-all" />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Sonraki Bakım Tarihi</label>
                <input v-model="form.next_maintenance_date" type="date" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-yellow-500 focus:border-transparent transition-all" />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Bakım Aralığı (Gün)</label>
                <input v-model="form.maintenance_interval_days" type="number" min="1" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-yellow-500 focus:border-transparent transition-all" />
              </div>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end space-x-4">
          <Link :href="route('equipments.index')" class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 bg-white hover:bg-gray-50 transition-all">
            İptal
          </Link>
          <button type="submit" class="px-6 py-3 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition-all">
            Güncelle
          </button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>

<script setup>
import { reactive } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  equipment: Object,
  projects: Array
})

const form = reactive({
  code: props.equipment.code,
  name: props.equipment.name,
  type: props.equipment.type,
  brand: props.equipment.brand,
  model: props.equipment.model,
  serial_number: props.equipment.serial_number,
  manufacture_year: props.equipment.manufacture_year,
  description: props.equipment.description,
  ownership: props.equipment.ownership,
  rental_company: props.equipment.rental_company,
  rental_cost_daily: props.equipment.rental_cost_daily,
  rental_cost_monthly: props.equipment.rental_cost_monthly,
  status: props.equipment.status,
  current_project_id: props.equipment.current_project_id,
  current_location: props.equipment.current_location,
  last_maintenance_date: props.equipment.last_maintenance_date,
  next_maintenance_date: props.equipment.next_maintenance_date,
  maintenance_interval_days: props.equipment.maintenance_interval_days,
  insured: props.equipment.insured,
  requires_operator_license: props.equipment.requires_operator_license
})

const submit = () => {
  router.put(route('equipments.update', props.equipment.id), form)
}
</script>
