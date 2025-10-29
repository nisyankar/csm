<template>
  <AppLayout title="Stok Hareketi Düzenle" :full-width="true">
    <template #fullWidthHeader>
      <div class="bg-gradient-to-r from-emerald-600 via-emerald-700 to-green-800 border-b border-emerald-900/20 w-full">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
          <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
              <div class="flex-shrink-0 w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm">
                <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                </svg>
              </div>
              <div>
                <h1 class="text-2xl lg:text-3xl font-bold text-white">Stok Hareketi Düzenle</h1>
                <p class="text-emerald-100 text-sm mt-1">Stok hareket bilgilerini güncelleyin</p>
              </div>
            </div>
          </div>
        </div>

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
                  <Link :href="route('stock-movements.index')" class="text-emerald-100 hover:text-white text-sm">Stok Hareketleri</Link>
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

    <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
      <form @submit.prevent="submit" class="space-y-6">
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-medium text-gray-900">Hareket Bilgileri</h3>
          </div>
          <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Depo <span class="text-red-500">*</span>
                </label>
                <select
                  v-model="form.warehouse_id"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                  :class="{'border-red-300 focus:ring-red-500': form.errors.warehouse_id}"
                >
                  <option value="">Seçiniz...</option>
                  <option v-for="warehouse in warehouses" :key="warehouse.id" :value="warehouse.id">
                    {{ warehouse.name }}
                  </option>
                </select>
                <p v-if="form.errors.warehouse_id" class="text-red-600 text-sm mt-2">
                  {{ form.errors.warehouse_id }}
                </p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Malzeme <span class="text-red-500">*</span>
                </label>
                <select
                  v-model="form.material_id"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                  :class="{'border-red-300 focus:ring-red-500': form.errors.material_id}"
                >
                  <option value="">Seçiniz...</option>
                  <option v-for="material in materials" :key="material.id" :value="material.id">
                    {{ material.name }} (Stok: {{ material.current_stock }} {{ material.unit }})
                  </option>
                </select>
                <p v-if="form.errors.material_id" class="text-red-600 text-sm mt-2">
                  {{ form.errors.material_id }}
                </p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Hareket Tipi <span class="text-red-500">*</span>
                </label>
                <select
                  v-model="form.movement_type"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                  :class="{'border-red-300 focus:ring-red-500': form.errors.movement_type}"
                >
                  <option value="">Seçiniz...</option>
                  <option value="in">Giriş</option>
                  <option value="out">Çıkış</option>
                  <option value="transfer">Transfer</option>
                  <option value="adjustment">Düzeltme</option>
                </select>
                <p v-if="form.errors.movement_type" class="text-red-600 text-sm mt-2">
                  {{ form.errors.movement_type }}
                </p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Miktar <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="form.quantity"
                  type="number"
                  step="0.01"
                  min="0.01"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                  :class="{'border-red-300 focus:ring-red-500': form.errors.quantity}"
                  placeholder="0.00"
                />
                <p v-if="form.errors.quantity" class="text-red-600 text-sm mt-2">
                  {{ form.errors.quantity }}
                </p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Birim Fiyat
                </label>
                <input
                  v-model="form.unit_price"
                  type="number"
                  step="0.01"
                  min="0"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                  placeholder="0.00"
                />
                <p v-if="form.errors.unit_price" class="text-red-600 text-sm mt-2">
                  {{ form.errors.unit_price }}
                </p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Hareket Tarihi <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="form.movement_date"
                  type="datetime-local"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                  :class="{'border-red-300 focus:ring-red-500': form.errors.movement_date}"
                />
                <p v-if="form.errors.movement_date" class="text-red-600 text-sm mt-2">
                  {{ form.errors.movement_date }}
                </p>
              </div>

              <div class="lg:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Notlar
                </label>
                <textarea
                  v-model="form.notes"
                  rows="4"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                  placeholder="Ek açıklamalar..."
                ></textarea>
              </div>
            </div>
          </div>
        </div>

        <div class="flex items-center justify-end space-x-4">
          <Link
            :href="route('stock-movements.index')"
            class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium transition-colors"
          >
            İptal
          </Link>
          <button
            type="submit"
            :disabled="form.processing"
            class="px-6 py-3 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span v-if="form.processing">Güncelleniyor...</span>
            <span v-else>Güncelle</span>
          </button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'
import { Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  movement: Object,
  warehouses: Array,
  materials: Array,
})

const form = useForm({
  warehouse_id: props.movement.warehouse_id,
  material_id: props.movement.material_id,
  movement_type: props.movement.movement_type,
  quantity: props.movement.quantity,
  unit_price: props.movement.unit_price,
  notes: props.movement.notes,
  movement_date: props.movement.movement_date?.substring(0, 16) || '',
})

function submit() {
  form.put(route('stock-movements.update', props.movement.id))
}
</script>
