<template>
  <AppLayout title="Yeni Stok Sayımı">
    <div class="max-w-4xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
      <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Yeni Stok Sayımı</h1>
        <p class="mt-1 text-sm text-gray-600">Depo stok sayım kaydı oluşturun</p>
      </div>

      <form @submit.prevent="submit" class="bg-white rounded-xl shadow-sm border border-gray-200/80 p-6 space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Depo *</label>
            <select v-model="form.warehouse_id" @change="onWarehouseChange" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
              <option value="">Seçiniz</option>
              <option v-for="warehouse in warehouses" :key="warehouse.id" :value="warehouse.id">
                {{ warehouse.name }} ({{ warehouse.project?.name }})
              </option>
            </select>
            <div v-if="form.errors.warehouse_id" class="text-red-600 text-sm mt-1">{{ form.errors.warehouse_id }}</div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Malzeme *</label>
            <select v-model="form.material_id" @change="onMaterialChange" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
              <option value="">Seçiniz</option>
              <option v-for="material in materials" :key="material.id" :value="material.id">
                {{ material.code }} - {{ material.name }}
              </option>
            </select>
            <div v-if="form.errors.material_id" class="text-red-600 text-sm mt-1">{{ form.errors.material_id }}</div>
          </div>

          <div v-if="systemStock !== null" class="md:col-span-2 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex items-center space-x-2">
              <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
              </svg>
              <span class="text-sm font-medium text-blue-900">Sistemdeki Miktar: <span class="text-lg font-bold">{{ systemStock }}</span></span>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Sayılan Miktar *</label>
            <input v-model="form.counted_quantity" type="number" step="0.01" min="0" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
            <div v-if="form.errors.counted_quantity" class="text-red-600 text-sm mt-1">{{ form.errors.counted_quantity }}</div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Sayım Tarihi *</label>
            <input v-model="form.count_date" type="date" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
            <div v-if="form.errors.count_date" class="text-red-600 text-sm mt-1">{{ form.errors.count_date }}</div>
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Not</label>
          <textarea v-model="form.notes" rows="3" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500"></textarea>
          <div v-if="form.errors.notes" class="text-red-600 text-sm mt-1">{{ form.errors.notes }}</div>
        </div>

        <div class="flex justify-end space-x-3">
          <Link :href="route('stock-counts.index')" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">İptal</Link>
          <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 disabled:opacity-50">Kaydet</button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import axios from 'axios'

const props = defineProps({
  warehouses: Array,
  materials: Array
})

const form = useForm({
  warehouse_id: '',
  material_id: '',
  counted_quantity: '',
  count_date: new Date().toISOString().split('T')[0],
  notes: ''
})

const systemStock = ref(null)

async function onWarehouseChange() {
  systemStock.value = null
  if (form.warehouse_id && form.material_id) {
    await fetchSystemStock()
  }
}

async function onMaterialChange() {
  systemStock.value = null
  if (form.warehouse_id && form.material_id) {
    await fetchSystemStock()
  }
}

async function fetchSystemStock() {
  try {
    const response = await axios.get(route('stock-counts.system-stock'), {
      params: {
        warehouse_id: form.warehouse_id,
        material_id: form.material_id
      }
    })
    systemStock.value = response.data.system_stock
  } catch (error) {
    console.error('Sistem stoku alınamadı:', error)
  }
}

function submit() {
  form.post(route('stock-counts.store'))
}
</script>
