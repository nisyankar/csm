<template>
  <AppLayout title="Yeni Vardiya">
    <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
      <div class="max-w-3xl mx-auto">
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 p-6">
          <h2 class="text-2xl font-bold text-gray-900 mb-6">Yeni Vardiya Tanımla</h2>
          
          <form @submit.prevent="submit">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Vardiya Adı *</label>
                <input v-model="form.name" type="text" required class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500" />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Kod *</label>
                <input v-model="form.code" type="text" required maxlength="10" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500" />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Vardiya Tipi *</label>
                <select v-model="form.shift_type" required class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                  <option value="normal">Normal Çalışma</option>
                  <option value="weekend">Hafta Sonu</option>
                  <option value="holiday">Bayram</option>
                  <option value="rest_day">İstirahat Günü</option>
                  <option value="annual_leave">Yıllık İzin</option>
                  <option value="sick_leave">Hastalık Raporu</option>
                  <option value="unpaid_leave">Ücretsiz İzin</option>
                  <option value="excused_leave">Mazeret İzni</option>
                  <option value="maternity_leave">Doğum İzni</option>
                  <option value="half_day">Arefe</option>
                </select>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Günlük Saat *</label>
                <input v-model.number="form.daily_hours" type="number" step="0.5" min="0" max="24" required class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500" />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">FM Çarpanı *</label>
                <input v-model.number="form.overtime_multiplier" type="number" step="0.1" min="0" max="10" required class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500" />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Sıra</label>
                <input v-model.number="form.sort_order" type="number" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500" />
              </div>

              <div class="md:col-span-2">
                <label class="flex items-center space-x-2">
                  <input v-model="form.is_paid" type="checkbox" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                  <span class="text-sm font-medium text-gray-700">Ücretli</span>
                </label>
              </div>

              <div class="md:col-span-2">
                <label class="flex items-center space-x-2">
                  <input v-model="form.counts_as_work_day" type="checkbox" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                  <span class="text-sm font-medium text-gray-700">Çalışma Günü Sayılsın</span>
                </label>
              </div>

              <div class="md:col-span-2">
                <label class="flex items-center space-x-2">
                  <input v-model="form.is_active" type="checkbox" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                  <span class="text-sm font-medium text-gray-700">Aktif</span>
                </label>
              </div>

              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Açıklama</label>
                <textarea v-model="form.description" rows="3" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"></textarea>
              </div>
            </div>

            <div class="mt-6 flex justify-end space-x-3">
              <Link :href="route('shifts.index')" class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
                İptal
              </Link>
              <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 disabled:opacity-50">
                Kaydet
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { Link, useForm } from '@inertiajs/vue3'

const form = useForm({
  name: '',
  code: '',
  shift_type: 'normal',
  daily_hours: 7.5,
  overtime_multiplier: 1.0,
  is_paid: true,
  counts_as_work_day: true,
  is_active: true,
  sort_order: 0,
  description: ''
})

const submit = () => {
  form.post(route('shifts.store'))
}
</script>
