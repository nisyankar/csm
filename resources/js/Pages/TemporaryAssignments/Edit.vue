<template>
  <AppLayout title="Geçici Görevlendirmeyi Düzenle" :full-width="true">
    <!-- Full-width header -->
    <template #fullWidthHeader>
      <div class="bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 border-b border-indigo-900/20 w-full">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
          <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <!-- Header Info -->
            <div class="flex-1 min-w-0">
              <div class="flex items-center space-x-4 mb-3">
                <div class="flex-shrink-0 w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm">
                  <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                  </svg>
                </div>
                <div>
                  <h1 class="text-2xl lg:text-3xl font-bold text-white">Görevlendirmeyi Düzenle</h1>
                  <p class="text-indigo-100 text-sm mt-1">
                    {{ assignment?.employee?.first_name || '' }} {{ assignment?.employee?.last_name || '' }}
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Breadcrumb -->
        <div class="bg-black/10 border-t border-white/10">
          <div class="w-full px-4 sm:px-6 lg:px-8">
            <div class="flex items-center space-x-2 py-2">
              <ol class="flex items-center space-x-2">
                <li>
                  <Link :href="route('dashboard')" class="text-indigo-100 hover:text-white transition-colors">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                    </svg>
                  </Link>
                </li>
                <li class="flex items-center">
                  <svg class="h-3 w-3 text-indigo-200 mx-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                  </svg>
                  <Link :href="route('temporary-assignments.index')" class="text-indigo-100 hover:text-white text-sm">Geçici Görevlendirmeler</Link>
                </li>
                <li class="flex items-center">
                  <svg class="h-3 w-3 text-indigo-200 mx-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                  </svg>
                  <span class="text-xs font-medium text-white">Düzenle</span>
                </li>
              </ol>
            </div>
          </div>
        </div>
      </div>
    </template>

    <!-- Content -->
    <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
      <!-- Status Info -->
      <div
        class="mb-6 p-4 rounded-lg border-l-4"
        :class="isActive ? 'bg-green-50 border-green-500' : 'bg-yellow-50 border-yellow-500'"
      >
        <div class="flex items-center">
          <svg
            :class="isActive ? 'text-green-600' : 'text-yellow-600'"
            class="h-5 w-5 mr-3"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              :d="isActive ? 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' : 'M12 8v4m0 4v.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'"
            />
          </svg>
          <div>
            <p :class="isActive ? 'text-green-800' : 'text-yellow-800'" class="font-semibold text-sm">
              Durum: {{ assignment?.status_label || 'Bilinmiyor' }}
            </p>
            <p :class="isActive ? 'text-green-700' : 'text-yellow-700'" class="text-sm mt-1">
              {{ isActive ? 'Bu görevlendirme aktiftir. Yalnızca bitiş tarihini uzatabilirsiniz.' : 'Bu görevlendirme henüz onay beklemektedir. Tüm alanları düzenleyebilirsiniz.' }}
            </p>
          </div>
        </div>
      </div>

      <form @submit.prevent="submit" class="space-y-6 max-w-4xl">
        <!-- General Errors -->
        <div v-if="Object.keys(form.errors).length > 0" class="bg-red-50 border border-red-200 text-red-800 rounded-lg p-4">
          <h4 class="font-semibold mb-2">Lütfen aşağıdaki hataları düzeltin:</h4>
          <ul class="list-disc list-inside space-y-1">
            <li v-for="(error, field) in form.errors" :key="field" class="text-sm">{{ error }}</li>
          </ul>
        </div>

        <!-- Employee Information (Read-only for active) -->
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 bg-gradient-to-r from-indigo-50 to-purple-50 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Çalışan Bilgileri</h3>
          </div>
          <div class="p-6">
            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
              <div class="flex items-center space-x-4">
                <div class="h-12 w-12 rounded-full bg-indigo-100 flex items-center justify-center">
                  <span class="text-sm font-semibold text-indigo-700">
                    {{ getInitials(assignment?.employee?.first_name, assignment?.employee?.last_name) }}
                  </span>
                </div>
                <div>
                  <p class="font-semibold text-gray-900">
                    {{ assignment?.employee?.first_name || '' }} {{ assignment?.employee?.last_name || '' }}
                  </p>
                  <p class="text-sm text-gray-600">{{ assignment?.employee?.employee_code || 'N/A' }}</p>
                </div>
              </div>
              <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-indigo-100 text-indigo-700">
                Sabit
              </span>
            </div>
          </div>
        </div>

        <!-- Project Information (Read-only for active) -->
        <div v-if="isActive" class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 bg-gradient-to-r from-purple-50 to-pink-50 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Proje Bilgileri</h3>
          </div>
          <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <p class="text-xs text-blue-600 font-semibold mb-1">Ana Proje</p>
                <p class="text-sm font-semibold text-blue-900">{{ assignment?.from_project?.name || 'Bilinmiyor' }}</p>
              </div>
              <div class="p-4 bg-green-50 border border-green-200 rounded-lg">
                <p class="text-xs text-green-600 font-semibold mb-1">Hedef Proje</p>
                <p class="text-sm font-semibold text-green-900">{{ assignment?.to_project?.name || 'Bilinmiyor' }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Project Information (Editable for pending) -->
        <div v-else class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 bg-gradient-to-r from-purple-50 to-pink-50 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Proje Bilgileri</h3>
          </div>
          <div class="p-6 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Ana Proje <span class="text-red-500">*</span>
                </label>
                <select
                  v-model="form.from_project_id"
                  required
                  class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all"
                  :class="form.errors.from_project_id ? 'border-red-500 focus:ring-red-500' : ''"
                >
                  <option value="">Ana Proje Seçin</option>
                  <option v-for="proj in projects" :key="proj.id" :value="proj.id">
                    {{ proj?.name || 'Bilinmiyor' }}
                  </option>
                </select>
                <p v-if="form.errors.from_project_id" class="mt-1 text-sm text-red-600">{{ form.errors.from_project_id }}</p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Hedef Proje <span class="text-red-500">*</span>
                </label>
                <select
                  v-model="form.to_project_id"
                  required
                  class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-pink-500 focus:border-transparent transition-all"
                  :class="form.errors.to_project_id ? 'border-red-500 focus:ring-red-500' : ''"
                >
                  <option value="">Hedef Proje Seçin</option>
                  <option v-for="proj in projects" :key="proj.id" :value="proj.id">
                    {{ proj?.name || 'Bilinmiyor' }}
                  </option>
                </select>
                <p v-if="form.errors.to_project_id" class="mt-1 text-sm text-red-600">{{ form.errors.to_project_id }}</p>
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Tercih Edilen Vardiya <span class="text-red-500">*</span>
              </label>
              <select
                v-model="form.preferred_shift_id"
                required
                class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all"
                :class="form.errors.preferred_shift_id ? 'border-red-500 focus:ring-red-500' : ''"
              >
                <option value="">Vardiya Seçin</option>
                <option v-for="shift in shifts" :key="shift.id" :value="shift.id">
                  {{ shift?.name || 'Bilinmiyor' }} ({{ shift?.daily_hours || 0 }} saat)
                </option>
              </select>
              <p v-if="form.errors.preferred_shift_id" class="mt-1 text-sm text-red-600">{{ form.errors.preferred_shift_id }}</p>
              <p class="mt-2 text-xs text-gray-500">
                Çalışan geçici görevlendirme sırasında bu vardiya ile puantaja girilecektir.
              </p>
            </div>
          </div>
        </div>

        <!-- Date Range -->
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 bg-gradient-to-r from-pink-50 to-indigo-50 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Tarih Aralığı</h3>
          </div>
          <div class="p-6 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Başlangıç Tarihi
                  <span v-if="isActive" class="text-gray-500 text-xs">(Salt Okunur)</span>
                  <span v-else class="text-red-500">*</span>
                </label>
                <input
                  v-model="form.start_date"
                  type="date"
                  :disabled="isActive"
                  :required="!isActive"
                  class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-pink-500 focus:border-transparent transition-all disabled:bg-gray-100 disabled:cursor-not-allowed"
                  :class="form.errors.start_date ? 'border-red-500 focus:ring-red-500' : ''"
                />
                <p v-if="form.errors.start_date" class="mt-1 text-sm text-red-600">{{ form.errors.start_date }}</p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Bitiş Tarihi <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="form.end_date"
                  type="date"
                  required
                  :min="form.start_date || today"
                  class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-pink-500 focus:border-transparent transition-all"
                  :class="form.errors.end_date ? 'border-red-500 focus:ring-red-500' : ''"
                />
                <p v-if="form.errors.end_date" class="mt-1 text-sm text-red-600">{{ form.errors.end_date }}</p>
              </div>
            </div>

            <!-- Duration Display -->
            <div v-if="form.start_date && form.end_date" class="p-3 bg-indigo-50 border border-indigo-200 rounded-lg">
              <p class="text-sm text-indigo-900">
                <span class="font-semibold">Görevlendirme Süresi:</span> {{ calculateDuration() }} gün
              </p>
            </div>
          </div>
        </div>

        <!-- Notes (Editable only for pending) -->
        <div v-if="!isActive" class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 bg-gradient-to-r from-indigo-50 to-purple-50 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Açıklamalar</h3>
          </div>
          <div class="p-6 space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Görevlendirme Nedeni <span class="text-red-500">*</span>
              </label>
              <input
                v-model="form.reason"
                type="text"
                required
                placeholder="Örn: Proje tamamlanmasına yardımcı olmak, uzman desteği sağlamak"
                class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                :class="form.errors.reason ? 'border-red-500 focus:ring-red-500' : ''"
              />
              <p v-if="form.errors.reason" class="mt-1 text-sm text-red-600">{{ form.errors.reason }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Notlar
              </label>
              <textarea
                v-model="form.notes"
                rows="4"
                placeholder="Ek notlar veya açıklamalar..."
                class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all resize-none"
                :class="form.errors.notes ? 'border-red-500 focus:ring-red-500' : ''"
              ></textarea>
              <p v-if="form.errors.notes" class="mt-1 text-sm text-red-600">{{ form.errors.notes }}</p>
            </div>
          </div>
        </div>

        <!-- Read-only Notes for Active -->
        <div v-else-if="assignment?.notes" class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 bg-gradient-to-r from-indigo-50 to-purple-50 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Notlar</h3>
          </div>
          <div class="p-6">
            <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ assignment?.notes || 'Yok' }}</p>
          </div>
        </div>

        <!-- Info Box -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
          <div class="flex">
            <svg class="h-5 w-5 text-blue-600 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
            </svg>
            <div class="text-sm text-blue-700">
              <p class="font-medium mb-1">Bilgi:</p>
              <ul class="list-disc list-inside space-y-1">
                <li v-if="isActive">Aktif görevlendirmeler için yalnızca bitiş tarihini uzatabilirsiniz</li>
                <li v-else>Onay beklemekte olan görevlendirmeleri tamamen düzenleyebilirsiniz</li>
              </ul>
            </div>
          </div>
        </div>

        <!-- Form Actions -->
        <div class="flex justify-end space-x-3 border-t border-gray-200 pt-6">
          <button
            type="button"
            @click="cancel"
            class="px-6 py-2.5 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition-colors"
          >
            İptal
          </button>
          <button
            type="submit"
            :disabled="form.processing"
            class="px-6 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 text-white text-sm font-medium rounded-lg hover:from-indigo-700 hover:to-purple-700 transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center"
          >
            <svg v-if="form.processing" class="w-4 h-4 mr-2 animate-spin" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z" />
            </svg>
            {{ form.processing ? 'Kaydediliyor...' : 'Güncelle' }}
          </button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  assignment: {
    type: Object,
    required: true
  },
  projects: {
    type: Array,
    default: () => []
  },
  shifts: {
    type: Array,
    default: () => []
  }
})

const today = computed(() => {
  const d = new Date()
  return d.toISOString().split('T')[0]
})

const isActive = computed(() => props.assignment?.status === 'active')

const form = reactive({
  employee_id: props.assignment?.employee_id || '',
  from_project_id: props.assignment?.from_project_id || '',
  to_project_id: props.assignment?.to_project_id || '',
  preferred_shift_id: props.assignment?.preferred_shift_id || '',
  start_date: props.assignment?.start_date || '',
  end_date: props.assignment?.end_date || '',
  reason: props.assignment?.reason || '',
  notes: props.assignment?.notes || '',
  processing: false,
  errors: {}
})

const calculateDuration = () => {
  if (!form.start_date || !form.end_date) return 0
  const start = new Date(form.start_date)
  const end = new Date(form.end_date)
  const diffTime = Math.abs(end - start)
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
  return diffDays
}

const getInitials = (firstName, lastName) => {
  const first = firstName ? firstName.charAt(0).toUpperCase() : ''
  const last = lastName ? lastName.charAt(0).toUpperCase() : ''
  return (first + last) || 'NA'
}

const submit = () => {
  form.processing = true
  form.errors = {}

  const data = {
    employee_id: form.employee_id,
    start_date: form.start_date,
    end_date: form.end_date,
  }

  if (!isActive.value) {
    data.from_project_id = form.from_project_id
    data.to_project_id = form.to_project_id
    data.preferred_shift_id = form.preferred_shift_id
    data.reason = form.reason
    data.notes = form.notes || null
  }

  router.put(route('temporary-assignments.update', props.assignment?.id), data, {
    onError: (errors) => {
      form.errors = errors
      form.processing = false
    },
    onFinish: () => {
      form.processing = false
    }
  })
}

const cancel = () => {
  router.visit(route('temporary-assignments.show', props.assignment?.id))
}
</script>
