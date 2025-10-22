<template>
  <AppLayout title="Vardiya Düzenle">
    <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
      <div class="max-w-3xl mx-auto">
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 p-6">
          <h2 class="text-2xl font-bold text-gray-900 mb-6">Vardiya Düzenle</h2>
          
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

            <div class="mt-6 flex justify-between">
              <button type="button" @click="deleteShift" class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700">
                Sil
              </button>
              <div class="flex space-x-3">
                <Link :href="route('shifts.index')" class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
                  İptal
                </Link>
                <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 disabled:opacity-50">
                  Güncelle
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { Link, useForm, router } from '@inertiajs/vue3'

const props = defineProps({
  shift: Object
})

const form = useForm({
  name: props.shift.name,
  code: props.shift.code,
  shift_type: props.shift.shift_type,
  daily_hours: props.shift.daily_hours,
  overtime_multiplier: props.shift.overtime_multiplier,
  is_paid: props.shift.is_paid,
  counts_as_work_day: props.shift.counts_as_work_day,
  is_active: props.shift.is_active,
  sort_order: props.shift.sort_order,
  description: props.shift.description
})

const submit = () => {
  form.patch(route('shifts.update', props.shift.id))
}

const deleteShift = () => {
  if (confirm('Bu vardiyayı silmek istediğinize emin misiniz?')) {
    router.delete(route('shifts.destroy', props.shift.id))
  }
}
</script>
