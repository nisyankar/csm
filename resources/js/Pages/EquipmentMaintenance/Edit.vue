<template>
  <AppLayout title="Bakım Düzenle" :full-width="true">
    <template #fullWidthHeader>
      <div class="bg-gradient-to-r from-orange-500 via-amber-500 to-yellow-500 border-b border-orange-900/20 w-full">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
          <div class="flex items-center space-x-4">
            <div class="flex-shrink-0 w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm">
              <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
              </svg>
            </div>
            <div>
              <h1 class="text-2xl lg:text-3xl font-bold text-white">Bakım Düzenle</h1>
              <p class="text-orange-100 text-sm mt-1">{{ maintenance.maintenance_code }}</p>
            </div>
          </div>
        </div>
      </div>
    </template>

    <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
      <form @submit.prevent="submit" class="space-y-6">
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-medium text-gray-900">Bakım Bilgileri</h3>
          </div>
          <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Bakım Kodu <span class="text-red-500">*</span></label>
                <input v-model="form.maintenance_code" type="text" required class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-orange-500" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Ekipman <span class="text-red-500">*</span></label>
                <select v-model="form.equipment_id" required class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-orange-500">
                  <option v-for="eq in equipments" :key="eq.id" :value="eq.id">{{ eq.code }} - {{ eq.name }}</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Bakım Tipi <span class="text-red-500">*</span></label>
                <select v-model="form.type" required class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-orange-500">
                  <option value="routine">Rutin Bakım</option>
                  <option value="preventive">Önleyici Bakım</option>
                  <option value="corrective">Onarım Bakımı</option>
                  <option value="breakdown">Arıza</option>
                  <option value="inspection">Muayene</option>
                  <option value="overhaul">Kapsamlı Revizyon</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Bakım Tarihi <span class="text-red-500">*</span></label>
                <input v-model="form.maintenance_date" type="date" required class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-orange-500" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Durum <span class="text-red-500">*</span></label>
                <select v-model="form.status" required class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-orange-500">
                  <option value="scheduled">Planlandı</option>
                  <option value="in_progress">Devam Ediyor</option>
                  <option value="completed">Tamamlandı</option>
                  <option value="cancelled">İptal Edildi</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Servis <span class="text-red-500">*</span></label>
                <select v-model="form.service_provider" required class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-orange-500">
                  <option value="internal">İç Ekip</option>
                  <option value="external">Dış Servis</option>
                </select>
              </div>
              <div class="lg:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Açıklama <span class="text-red-500">*</span></label>
                <textarea v-model="form.description" required rows="3" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-orange-500"></textarea>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-medium text-gray-900">Maliyet</h3>
          </div>
          <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">İşçilik (₺)</label>
                <input v-model="form.labor_cost" type="number" step="0.01" min="0" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-orange-500" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Parça (₺)</label>
                <input v-model="form.parts_cost" type="number" step="0.01" min="0" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-orange-500" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Dış Servis (₺)</label>
                <input v-model="form.external_service_cost" type="number" step="0.01" min="0" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-orange-500" />
              </div>
            </div>
          </div>
        </div>

        <div class="flex items-center justify-end space-x-4">
          <Link :href="route('equipment-maintenance.index')" class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 bg-white hover:bg-gray-50">İptal</Link>
          <button type="submit" class="px-6 py-3 bg-orange-600 text-white rounded-lg hover:bg-orange-700">Güncelle</button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>

<script setup>
import { reactive } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({ maintenance: Object, equipments: Array })

const form = reactive({
  maintenance_code: props.maintenance.maintenance_code, equipment_id: props.maintenance.equipment_id,
  type: props.maintenance.type, maintenance_date: props.maintenance.maintenance_date,
  status: props.maintenance.status, service_provider: props.maintenance.service_provider,
  service_company: props.maintenance.service_company, description: props.maintenance.description,
  labor_cost: props.maintenance.labor_cost, parts_cost: props.maintenance.parts_cost,
  external_service_cost: props.maintenance.external_service_cost
})

const submit = () => router.put(route('equipment-maintenance.update', props.maintenance.id), form)
</script>
