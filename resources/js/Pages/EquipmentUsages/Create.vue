<template>
  <AppLayout title="Yeni Ekipman Kullanımı" :full-width="true">
    <template #fullWidthHeader>
      <div class="bg-gradient-to-r from-amber-600 via-yellow-600 to-orange-500 border-b border-amber-900/20 w-full">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
          <div class="flex items-center space-x-4">
            <div class="flex-shrink-0 w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm">
              <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
              </svg>
            </div>
            <div>
              <h1 class="text-2xl lg:text-3xl font-bold text-white">Yeni Kullanım Kaydı</h1>
              <p class="text-amber-100 text-sm mt-1">Ekipman kullanımı oluştur</p>
            </div>
          </div>
        </div>
      </div>
    </template>

    <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
      <form @submit.prevent="submit" class="space-y-6">
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-medium text-gray-900">Kullanım Bilgileri</h3>
          </div>
          <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Ekipman <span class="text-red-500">*</span></label>
                <select v-model="form.equipment_id" required class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-amber-500">
                  <option value="">Seçiniz...</option>
                  <option v-for="eq in equipments" :key="eq.id" :value="eq.id">{{ eq.code }} - {{ eq.name }}</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Proje <span class="text-red-500">*</span></label>
                <select v-model="form.project_id" required class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-amber-500">
                  <option value="">Seçiniz...</option>
                  <option v-for="project in projects" :key="project.id" :value="project.id">{{ project.name }}</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Başlangıç Tarihi <span class="text-red-500">*</span></label>
                <input v-model="form.start_date" type="date" required class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-amber-500" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Bitiş Tarihi</label>
                <input v-model="form.end_date" type="date" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-amber-500" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Operatör</label>
                <select v-model="form.operator_id" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-amber-500">
                  <option value="">Seçiniz...</option>
                  <option v-for="emp in employees" :key="emp.id" :value="emp.id">{{ emp.first_name }} {{ emp.last_name }}</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Kira Maliyeti (₺)</label>
                <input v-model="form.rental_cost" type="number" step="0.01" min="0" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-amber-500" />
              </div>
              <div class="lg:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">İş Açıklaması</label>
                <textarea v-model="form.work_description" rows="3" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-amber-500"></textarea>
              </div>
            </div>
          </div>
        </div>

        <div class="flex items-center justify-end space-x-4">
          <Link :href="route('equipment-usages.index')" class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 bg-white hover:bg-gray-50">İptal</Link>
          <button type="submit" class="px-6 py-3 bg-amber-600 text-white rounded-lg hover:bg-amber-700">Kaydet</button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>

<script setup>
import { reactive } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({ equipments: Array, projects: Array, employees: Array })

const form = reactive({
  equipment_id: '', project_id: '', start_date: '', end_date: '', operator_id: '',
  work_description: '', rental_cost: null, fuel_cost: null
})

const submit = () => router.post(route('equipment-usages.store'), form)
</script>
