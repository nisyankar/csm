<template>
  <AppLayout title="Yeni Geçici Görevlendirme" :full-width="true">
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
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />
                  </svg>
                </div>
                <div>
                  <h1 class="text-2xl lg:text-3xl font-bold text-white">Yeni Geçici Görevlendirme</h1>
                  <p class="text-indigo-100 text-sm mt-1">Çalışanı geçici olarak başka bir projeye görevlendir</p>
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
                  <span class="text-xs font-medium text-white">Yeni Görevlendirme</span>
                </li>
              </ol>
            </div>
          </div>
        </div>
      </div>
    </template>

    <!-- Content -->
    <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
      <form @submit.prevent="submit" class="space-y-6 max-w-4xl">
        <!-- General Errors -->
        <div v-if="Object.keys(form.errors).length > 0" class="bg-red-50 border border-red-200 text-red-800 rounded-lg p-4">
          <h4 class="font-semibold mb-2">Lütfen aşağıdaki hataları düzeltin:</h4>
          <ul class="list-disc list-inside space-y-1">
            <li v-for="(error, field) in form.errors" :key="field" class="text-sm">{{ error }}</li>
          </ul>
        </div>

        <!-- Step 1: Employee Selection -->
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 bg-gradient-to-r from-indigo-50 to-purple-50 border-b border-gray-200">
            <div class="flex items-center space-x-3">
              <div class="flex-shrink-0 w-8 h-8 bg-indigo-600 rounded-full flex items-center justify-center text-white font-semibold text-sm">1</div>
              <h3 class="text-lg font-semibold text-gray-900">Çalışan Seçimi</h3>
            </div>
          </div>
          <div class="p-6 space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Çalışan <span class="text-red-500">*</span>
              </label>
              <select
                v-model="form.employee_id"
                @change="onEmployeeChange"
                required
                class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                :class="form.errors.employee_id ? 'border-red-500 focus:ring-red-500' : ''"
              >
                <option value="">Çalışan Seçin</option>
                <option v-for="emp in employees" :key="emp.id" :value="emp.id">
                  {{ emp?.first_name || '' }} {{ emp?.last_name || '' }} ({{ emp?.employee_code || 'N/A' }})
                </option>
              </select>
              <p v-if="form.errors.employee_id" class="mt-1 text-sm text-red-600">{{ form.errors.employee_id }}</p>
            </div>

            <!-- Current Project Info Card -->
            <div v-if="selectedEmployee" class="mt-4 p-4 bg-indigo-50 border border-indigo-200 rounded-lg">
              <p class="text-sm font-medium text-indigo-900 mb-3">Mevcut Proje Atamy</p>
              <div class="space-y-2">
                <div>
                  <p class="text-xs text-indigo-600">Ana Proje</p>
                  <p class="text-sm font-semibold text-indigo-900">{{ selectedEmployee?.current_projects?.primary?.name || 'Yok' }}</p>
                </div>
                <div v-if="selectedEmployee?.current_projects?.secondary?.length > 0">
                  <p class="text-xs text-indigo-600">Diğer Projeler</p>
                  <p class="text-sm font-semibold text-indigo-900">
                    {{ selectedEmployee?.current_projects?.secondary?.map((p) => p?.name || 'Bilinmiyor').join(', ') || 'Yok' }}
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Step 2: Project Selection -->
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 bg-gradient-to-r from-purple-50 to-pink-50 border-b border-gray-200">
            <div class="flex items-center space-x-3">
              <div class="flex-shrink-0 w-8 h-8 bg-purple-600 rounded-full flex items-center justify-center text-white font-semibold text-sm">2</div>
              <h3 class="text-lg font-semibold text-gray-900">Proje Seçimi</h3>
            </div>
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

            <!-- Project Transfer Visualization -->
            <div v-if="form.from_project_id && form.to_project_id" class="mt-4 p-4 bg-gray-50 border border-gray-200 rounded-lg">
              <div class="flex items-center justify-between">
                <div class="text-center flex-1">
                  <div class="inline-block px-3 py-2 bg-blue-100 text-blue-700 rounded-lg font-medium text-sm">
                    {{ getProjectName(form.from_project_id) || 'Seçim Yapın' }}
                  </div>
                  <p class="text-xs text-gray-600 mt-1">Ana Proje</p>
                </div>
                <svg class="w-6 h-6 text-gray-400 mx-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
                <div class="text-center flex-1">
                  <div class="inline-block px-3 py-2 bg-green-100 text-green-700 rounded-lg font-medium text-sm">
                    {{ getProjectName(form.to_project_id) || 'Seçim Yapın' }}
                  </div>
                  <p class="text-xs text-gray-600 mt-1">Hedef Proje</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Step 3: Shift Selection -->
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 bg-gradient-to-r from-pink-50 to-indigo-50 border-b border-gray-200">
            <div class="flex items-center space-x-3">
              <div class="flex-shrink-0 w-8 h-8 bg-pink-600 rounded-full flex items-center justify-center text-white font-semibold text-sm">3</div>
              <h3 class="text-lg font-semibold text-gray-900">Vardiya Seçimi</h3>
            </div>
          </div>
          <div class="p-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Tercih Edilen Vardiya <span class="text-red-500">*</span>
              </label>
              <select
                v-model="form.preferred_shift_id"
                required
                class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-pink-500 focus:border-transparent transition-all"
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

        <!-- Step 4: Date Range and Conflict Check -->
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 bg-gradient-to-r from-indigo-50 to-purple-50 border-b border-gray-200">
            <div class="flex items-center space-x-3">
              <div class="flex-shrink-0 w-8 h-8 bg-indigo-600 rounded-full flex items-center justify-center text-white font-semibold text-sm">4</div>
              <h3 class="text-lg font-semibold text-gray-900">Tarih Aralığı ve Kontrol</h3>
            </div>
          </div>
          <div class="p-6 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Başlangıç Tarihi <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="form.start_date"
                  type="date"
                  required
                  :min="today"
                  class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-pink-500 focus:border-transparent transition-all"
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

            <!-- Conflict Warning -->
            <div v-if="hasConflict" class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
              <div class="flex items-start">
                <svg class="w-5 h-5 text-yellow-600 mt-0.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
                <div class="text-sm text-yellow-800">
                  <p class="font-semibold">Uyarı: Çakışan Görevlendirme</p>
                  <p class="mt-1">Bu çalışanın seçilen tarih aralığında başka bir görevlendirmesi var.</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Step 5: Notes -->
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 bg-gradient-to-r from-purple-50 to-pink-50 border-b border-gray-200">
            <div class="flex items-center space-x-3">
              <div class="flex-shrink-0 w-8 h-8 bg-purple-600 rounded-full flex items-center justify-center text-white font-semibold text-sm">5</div>
              <h3 class="text-lg font-semibold text-gray-900">Açıklamalar</h3>
            </div>
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

        <!-- Info Box -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
          <div class="flex">
            <svg class="h-5 w-5 text-blue-600 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
            </svg>
            <div class="text-sm text-blue-700">
              <p class="font-medium mb-1">Bilgi:</p>
              <ul class="list-disc list-inside space-y-1">
                <li>Çalışan geçici olarak hedef projeye atanacaktır</li>
                <li>Ana projesi ile ilişki devam edecektir</li>
                <li>Bitiş tarihine ulaşıldığında görevlendirme otomatik tamamlanacaktır</li>
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
            :disabled="form.processing || !isFormValid"
            class="px-6 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 text-white text-sm font-medium rounded-lg hover:from-indigo-700 hover:to-purple-700 transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center"
          >
            <svg v-if="form.processing" class="w-4 h-4 mr-2 animate-spin" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z" />
            </svg>
            {{ form.processing ? 'Kaydediliyor...' : 'Kaydet' }}
          </button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed, reactive } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  employees: {
    type: Array,
    default: () => []
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

const form = reactive({
  employee_id: '',
  from_project_id: '',
  to_project_id: '',
  preferred_shift_id: '',
  start_date: '',
  end_date: '',
  reason: '',
  notes: '',
  processing: false,
  errors: {}
})

const selectedEmployee = ref(null)
const hasConflict = ref(false)

const onEmployeeChange = () => {
  selectedEmployee.value = props.employees.find(e => e?.id === parseInt(form.employee_id)) || null
  checkConflicts()
}

const checkConflicts = () => {
  if (form.start_date && form.end_date && form.employee_id) {
    hasConflict.value = false
  }
}

const calculateDuration = () => {
  if (!form.start_date || !form.end_date) return 0
  const start = new Date(form.start_date)
  const end = new Date(form.end_date)
  const diffTime = Math.abs(end - start)
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
  return diffDays
}

const getProjectName = (id) => {
  const project = props.projects.find(p => p?.id === parseInt(id))
  return project?.name || null
}

const isFormValid = computed(() => {
  return form.employee_id && form.from_project_id && form.to_project_id &&
         form.preferred_shift_id && form.start_date && form.end_date && form.reason && form.from_project_id !== form.to_project_id
})

const submit = () => {
  form.processing = true
  form.errors = {}

  router.post(route('temporary-assignments.store'), {
    employee_id: form.employee_id,
    from_project_id: form.from_project_id,
    to_project_id: form.to_project_id,
    preferred_shift_id: form.preferred_shift_id,
    start_date: form.start_date,
    end_date: form.end_date,
    reason: form.reason,
    notes: form.notes || null
  }, {
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
  router.visit(route('temporary-assignments.index'))
}
</script>
