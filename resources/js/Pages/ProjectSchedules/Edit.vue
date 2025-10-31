<template>
  <AppLayout title="Görev Düzenle" :full-width="true">
    <template #fullWidthHeader>
      <div class="bg-gradient-to-r from-orange-600 via-amber-600 to-yellow-600 border-b border-orange-900/20 w-full">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
          <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
              <div class="flex-shrink-0 w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm">
                <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                </svg>
              </div>
              <div>
                <h1 class="text-2xl lg:text-3xl font-bold text-white">Görev Düzenle</h1>
                <p class="text-orange-100 text-sm mt-1">{{ task.task_code }} - {{ task.task_name }}</p>
              </div>
            </div>
            <div class="flex gap-3">
              <Link :href="route('project-schedules.index')" class="inline-flex items-center px-4 py-2 bg-white/20 text-white text-sm font-medium rounded-lg hover:bg-white/30 shadow-lg transition-all backdrop-blur-sm">
                ← Listeye Dön
              </Link>
            </div>
          </div>
        </div>
      </div>
    </template>

    <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
      <form @submit.prevent="submit" class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden">
        <div class="p-6 space-y-6">
          <!-- Temel Bilgiler -->
          <div class="border-b border-gray-200 pb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
              <span class="w-8 h-8 bg-orange-100 text-orange-600 rounded-lg flex items-center justify-center mr-3 text-sm font-bold">1</span>
              Temel Bilgiler
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Proje *</label>
                <select v-model="form.project_id" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500">
                  <option value="">Seçiniz</option>
                  <option v-for="project in projects" :key="project.id" :value="project.id">
                    {{ project.project_code }} - {{ project.name }}
                  </option>
                </select>
                <div v-if="form.errors.project_id" class="text-red-600 text-sm mt-1">{{ form.errors.project_id }}</div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Görev Tipi *</label>
                <select v-model="form.task_type" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500">
                  <option value="">Seçiniz</option>
                  <option value="phase">Faz</option>
                  <option value="milestone">Kilometre Taşı</option>
                  <option value="activity">Aktivite</option>
                  <option value="deliverable">Çıktı</option>
                  <option value="meeting">Toplantı</option>
                </select>
                <div v-if="form.errors.task_type" class="text-red-600 text-sm mt-1">{{ form.errors.task_type }}</div>
              </div>

              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Görev Adı *</label>
                <input v-model="form.task_name" type="text" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500" placeholder="Görev adını girin">
                <div v-if="form.errors.task_name" class="text-red-600 text-sm mt-1">{{ form.errors.task_name }}</div>
              </div>

              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Açıklama</label>
                <textarea v-model="form.task_description" rows="3" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500" placeholder="Görev açıklaması"></textarea>
                <div v-if="form.errors.task_description" class="text-red-600 text-sm mt-1">{{ form.errors.task_description }}</div>
              </div>
            </div>
          </div>

          <!-- Tarih ve Süre -->
          <div class="border-b border-gray-200 pb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
              <span class="w-8 h-8 bg-orange-100 text-orange-600 rounded-lg flex items-center justify-center mr-3 text-sm font-bold">2</span>
              Tarih ve Süre
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Başlangıç Tarihi *</label>
                <input v-model="form.start_date" type="date" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500">
                <div v-if="form.errors.start_date" class="text-red-600 text-sm mt-1">{{ form.errors.start_date }}</div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Bitiş Tarihi *</label>
                <input v-model="form.end_date" type="date" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500">
                <div v-if="form.errors.end_date" class="text-red-600 text-sm mt-1">{{ form.errors.end_date }}</div>
              </div>
            </div>
          </div>

          <!-- Atama ve Sorumluluk -->
          <div class="border-b border-gray-200 pb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
              <span class="w-8 h-8 bg-orange-100 text-orange-600 rounded-lg flex items-center justify-center mr-3 text-sm font-bold">3</span>
              Atama ve Sorumluluk
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Sorumlu Kişi</label>
                <select v-model="form.assigned_to" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500">
                  <option value="">Seçiniz</option>
                  <option v-for="user in users" :key="user.id" :value="user.id">
                    {{ user.name }} - {{ user.position }}
                  </option>
                </select>
                <div v-if="form.errors.assigned_to" class="text-red-600 text-sm mt-1">{{ form.errors.assigned_to }}</div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Departman</label>
                <select v-model="form.department_id" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500">
                  <option value="">Seçiniz</option>
                  <option v-for="department in departments" :key="department.id" :value="department.id">
                    {{ department.name }}
                  </option>
                </select>
                <div v-if="form.errors.department_id" class="text-red-600 text-sm mt-1">{{ form.errors.department_id }}</div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Üst Görev (Hiyerarşi)</label>
                <select v-model="form.parent_task_id" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500">
                  <option value="">Ana Görev (Üst Yok)</option>
                  <option v-for="parentTask in parentTasks" :key="parentTask.id" :value="parentTask.id" :disabled="parentTask.id === task.id">
                    {{ parentTask.task_code }} - {{ parentTask.task_name }}
                  </option>
                </select>
                <div v-if="form.errors.parent_task_id" class="text-red-600 text-sm mt-1">{{ form.errors.parent_task_id }}</div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Öncelik</label>
                <select v-model="form.priority" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500">
                  <option value="low">Düşük</option>
                  <option value="medium">Orta</option>
                  <option value="high">Yüksek</option>
                  <option value="critical">Kritik</option>
                </select>
                <div v-if="form.errors.priority" class="text-red-600 text-sm mt-1">{{ form.errors.priority }}</div>
              </div>
            </div>
          </div>

          <!-- Durum ve İlerleme -->
          <div class="border-b border-gray-200 pb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
              <span class="w-8 h-8 bg-orange-100 text-orange-600 rounded-lg flex items-center justify-center mr-3 text-sm font-bold">4</span>
              Durum ve İlerleme
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Durum</label>
                <select v-model="form.status" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500">
                  <option value="not_started">Başlamadı</option>
                  <option value="in_progress">Devam Ediyor</option>
                  <option value="completed">Tamamlandı</option>
                  <option value="on_hold">Beklemede</option>
                </select>
                <div v-if="form.errors.status" class="text-red-600 text-sm mt-1">{{ form.errors.status }}</div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tamamlanma Yüzdesi (%)</label>
                <div class="flex items-center gap-3">
                  <input v-model="form.completion_percentage" type="range" min="0" max="100" class="flex-1">
                  <input v-model="form.completion_percentage" type="number" min="0" max="100" class="w-20 rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500">
                </div>
                <div v-if="form.errors.completion_percentage" class="text-red-600 text-sm mt-1">{{ form.errors.completion_percentage }}</div>
              </div>
            </div>
          </div>

          <!-- Bütçe -->
          <div class="border-b border-gray-200 pb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
              <span class="w-8 h-8 bg-orange-100 text-orange-600 rounded-lg flex items-center justify-center mr-3 text-sm font-bold">5</span>
              Bütçe
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tahmini Maliyet (₺)</label>
                <input v-model="form.estimated_cost" type="number" step="0.01" min="0" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500">
                <div v-if="form.errors.estimated_cost" class="text-red-600 text-sm mt-1">{{ form.errors.estimated_cost }}</div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Gerçekleşen Maliyet (₺)</label>
                <input v-model="form.actual_cost" type="number" step="0.01" min="0" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500">
                <div v-if="form.errors.actual_cost" class="text-red-600 text-sm mt-1">{{ form.errors.actual_cost }}</div>
              </div>

              <div class="md:col-span-2" v-if="form.estimated_cost || form.actual_cost">
                <div class="bg-gradient-to-r from-orange-50 to-amber-50 rounded-lg p-4 border border-orange-200">
                  <div class="text-sm font-medium text-gray-700">Bütçe Varyansı:
                    <span :class="getCostVarianceClass((form.estimated_cost || 0) - (form.actual_cost || 0))" class="font-bold ml-2 text-lg">
                      {{ formatCurrency((form.estimated_cost || 0) - (form.actual_cost || 0)) }}
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Notlar -->
          <div>
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
              <span class="w-8 h-8 bg-orange-100 text-orange-600 rounded-lg flex items-center justify-center mr-3 text-sm font-bold">6</span>
              Notlar
            </h3>
            <textarea v-model="form.notes" rows="3" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500" placeholder="Ek notlar"></textarea>
            <div v-if="form.errors.notes" class="text-red-600 text-sm mt-1">{{ form.errors.notes }}</div>
          </div>
        </div>

        <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 flex justify-between items-center">
          <button type="button" @click="confirmDelete" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors shadow-sm">
            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
            Sil
          </button>
          <div class="flex space-x-3">
            <Link :href="route('project-schedules.index')" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">İptal</Link>
            <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 disabled:opacity-50 transition-colors shadow-sm">
              <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" v-if="!form.processing">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
              {{ form.processing ? 'Kaydediliyor...' : 'Güncelle' }}
            </button>
          </div>
        </div>
      </form>
    </div>
  </AppLayout>
</template>

<script setup>
import { Link, useForm, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  task: Object,
  projects: Array,
  users: Array,
  departments: Array,
  parentTasks: Array
})

const form = useForm({
  project_id: props.task.project_id,
  task_type: props.task.task_type,
  task_name: props.task.task_name,
  task_description: props.task.task_description,
  start_date: props.task.start_date ? props.task.start_date.split('T')[0] : '',
  end_date: props.task.end_date ? props.task.end_date.split('T')[0] : '',
  assigned_to: props.task.assigned_to,
  department_id: props.task.department_id,
  parent_task_id: props.task.parent_task_id,
  priority: props.task.priority,
  status: props.task.status,
  completion_percentage: props.task.completion_percentage,
  estimated_cost: props.task.estimated_cost,
  actual_cost: props.task.actual_cost,
  notes: props.task.notes
})

function submit() {
  form.put(route('project-schedules.update', props.task.id))
}

function confirmDelete() {
  if (confirm('Bu görevi silmek istediğinizden emin misiniz?')) {
    router.delete(route('project-schedules.destroy', props.task.id))
  }
}

function getCostVarianceClass(variance) {
  if (variance > 0) return 'text-green-600'
  if (variance < 0) return 'text-red-600'
  return 'text-gray-600'
}

function formatCurrency(value) {
  return new Intl.NumberFormat('tr-TR', { style: 'currency', currency: 'TRY' }).format(value || 0)
}
</script>