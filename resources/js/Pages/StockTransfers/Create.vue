<template>
  <AppLayout title="Yeni Stok Transferi">
    <div class="max-w-4xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
      <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Yeni Stok Transferi</h1>
        <p class="mt-1 text-sm text-gray-600">Depolar arası malzeme transfer işlemi oluşturun</p>
      </div>

      <form @submit.prevent="submit" class="bg-white rounded-xl shadow-sm border border-gray-200/80 p-6 space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Kaynak Depo *</label>
            <select v-model="form.from_warehouse_id" @change="onFromWarehouseChange" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
              <option value="">Seçiniz</option>
              <option v-for="warehouse in warehouses" :key="warehouse.id" :value="warehouse.id">
                {{ warehouse.name }} ({{ warehouse.project?.name }})
              </option>
            </select>
            <div v-if="form.errors.from_warehouse_id" class="text-red-600 text-sm mt-1">{{ form.errors.from_warehouse_id }}</div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Hedef Depo *</label>
            <select v-model="form.to_warehouse_id" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
              <option value="">Seçiniz</option>
              <option v-for="warehouse in warehouses" :key="warehouse.id" :value="warehouse.id" :disabled="warehouse.id === form.from_warehouse_id">
                {{ warehouse.name }} ({{ warehouse.project?.name }})
              </option>
            </select>
            <div v-if="form.errors.to_warehouse_id" class="text-red-600 text-sm mt-1">{{ form.errors.to_warehouse_id }}</div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Malzeme *</label>
            <select v-model="form.material_id" @change="onMaterialChange" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
              <option value="">Seçiniz</option>
              <option v-for="material in materials" :key="material.id" :value="material.id">
                {{ material.code }} - {{ material.name }}
              </option>
            </select>
            <div v-if="form.errors.material_id" class="text-red-600 text-sm mt-1">{{ form.errors.material_id }}</div>
            <div v-if="currentStock !== null" class="text-sm text-gray-600 mt-1">
              Kaynak depoda mevcut stok: <span class="font-semibold">{{ currentStock }}</span>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Miktar *</label>
            <input v-model="form.quantity" type="number" step="0.01" min="0.01" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            <div v-if="form.errors.quantity" class="text-red-600 text-sm mt-1">{{ form.errors.quantity }}</div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Transfer Tarihi *</label>
            <input v-model="form.movement_date" type="date" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            <div v-if="form.errors.movement_date" class="text-red-600 text-sm mt-1">{{ form.errors.movement_date }}</div>
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Not</label>
          <textarea v-model="form.notes" rows="3" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
          <div v-if="form.errors.notes" class="text-red-600 text-sm mt-1">{{ form.errors.notes }}</div>
        </div>

        <div class="flex justify-end space-x-3">
          <Link :href="route('stock-transfers.index')" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">İptal</Link>
          <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50">Kaydet</button>
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
  from_warehouse_id: '',
  to_warehouse_id: '',
  material_id: '',
  quantity: '',
  movement_date: new Date().toISOString().split('T')[0],
  notes: ''
})

const currentStock = ref(null)

async function onFromWarehouseChange() {
  currentStock.value = null
  if (form.from_warehouse_id && form.material_id) {
    await fetchWarehouseStock()
  }
}

async function onMaterialChange() {
  currentStock.value = null
  if (form.from_warehouse_id && form.material_id) {
    await fetchWarehouseStock()
  }
}

async function fetchWarehouseStock() {
  try {
    const response = await axios.get(route('stock-transfers.warehouse-stock'), {
      params: {
        warehouse_id: form.from_warehouse_id,
        material_id: form.material_id
      }
    })
    currentStock.value = response.data.stock
  } catch (error) {
    console.error('Stok bilgisi alınamadı:', error)
  }
}

function submit() {
  form.post(route('stock-transfers.store'))
}
</script>
