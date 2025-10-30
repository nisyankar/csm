<template>
  <AppLayout title="Yeni Bakım Kaydı" :full-width="true">
    <template #fullWidthHeader>
      <div class="bg-gradient-to-r from-orange-500 via-amber-500 to-yellow-500 border-b border-orange-900/20 w-full">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
          <div class="flex items-center space-x-4">
            <div class="flex-shrink-0 w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm">
              <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
              </svg>
            </div>
            <div>
              <h1 class="text-2xl lg:text-3xl font-bold text-white">Yeni Bakım Kaydı</h1>
              <p class="text-orange-100 text-sm mt-1">Bakım/onarım kaydı oluştur</p>
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
                <input v-model="form.maintenance_code" type="text" required :placeholder="suggestedCode" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-orange-500" />
                <p class="text-xs text-gray-500 mt-1">Önerilen: {{ suggestedCode }}</p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Ekipman <span class="text-red-500">*</span></label>
                <select v-model="form.equipment_id" required class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-orange-500">
                  <option value="">Seçiniz...</option>
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
                <label class="block text-sm font-medium text-gray-700 mb-2">Servis Sağlayıcı <span class="text-red-500">*</span></label>
                <select v-model="form.service_provider" required class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-orange-500">
                  <option value="internal">İç Ekip</option>
                  <option value="external">Dış Servis</option>
                </select>
              </div>
              <div v-if="form.service_provider === 'external'">
                <label class="block text-sm font-medium text-gray-700 mb-2">Servis Şirketi</label>
                <input v-model="form.service_company" type="text" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-orange-500" />
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
            <h3 class="text-lg font-medium text-gray-900">Maliyet Bilgileri</h3>
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
          <button type="submit" class="px-6 py-3 bg-orange-600 text-white rounded-lg hover:bg-orange-700">Kaydet</button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>

<script setup>
import { reactive } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({ equipments: Array, suggestedCode: String })

const form = reactive({
  maintenance_code: props.suggestedCode || '', equipment_id: '', type: 'routine', maintenance_date: '',
  service_provider: 'internal', service_company: '', description: '',
  labor_cost: null, parts_cost: null, external_service_cost: null
})

const submit = () => router.post(route('equipment-maintenance.store'), form)
</script>
