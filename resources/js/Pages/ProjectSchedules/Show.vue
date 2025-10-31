<template>
  <AppLayout :title="`Görev - ${task.task_code}`" :full-width="true">
    <template #fullWidthHeader>
      <div class="bg-gradient-to-r from-orange-600 via-amber-600 to-yellow-600 border-b border-orange-900/20 w-full">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
          <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <div class="flex-1 min-w-0">
              <div class="flex items-center space-x-4 mb-3">
                <div class="flex-shrink-0 w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm">
                  <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" />
                  </svg>
                </div>
                <div>
                  <h1 class="text-2xl lg:text-3xl font-bold text-white">{{ task.task_name }}</h1>
                  <p class="text-orange-100 text-sm mt-1">{{ task.task_code }} - {{ task.project?.name }}</p>
                </div>
              </div>
            </div>
            <div class="flex-shrink-0 flex gap-3">
              <Link :href="route('project-schedules.index')" class="inline-flex items-center px-4 py-2 bg-white/20 text-white text-sm font-medium rounded-lg hover:bg-white/30 shadow-lg transition-all backdrop-blur-sm">
                ← Listeye Dön
              </Link>
              <Link :href="route('project-schedules.edit', task.id)" class="inline-flex items-center px-4 py-2 bg-white text-orange-600 text-sm font-medium rounded-lg hover:bg-orange-50 shadow-lg transition-all">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Düzenle
              </Link>
            </div>
          </div>
        </div>
      </div>
    </template>

    <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
      <!-- Status Cards -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 p-5">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-500">Durum</p>
              <p class="mt-2 text-lg font-bold text-gray-900">{{ getStatusLabel(task.status) }}</p>
            </div>
            <span :class="getStatusBadge(task.status)" class="px-3 py-1.5 text-xs font-medium rounded-full">
              {{ getStatusIcon(task.status) }}
            </span>
          </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 p-5">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-500">İlerleme</p>
              <p class="mt-2 text-lg font-bold text-gray-900">{{ task.completion_percentage }}%</p>
            </div>
            <div class="w-14 h-14">
              <svg class="transform -rotate-90 w-14 h-14">
                <circle cx="28" cy="28" r="24" stroke="#e5e7eb" stroke-width="5" fill="none" />
                <circle cx="28" cy="28" r="24" :stroke="getProgressColor(task.completion_percentage)" stroke-width="5" fill="none"
                  stroke-linecap="round"
                  :stroke-dasharray="2 * Math.PI * 24"
                  :stroke-dashoffset="2 * Math.PI * 24 * (1 - task.completion_percentage / 100)" />
              </svg>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 p-5">
          <p class="text-sm font-medium text-gray-500">Öncelik</p>
          <span :class="getPriorityBadge(task.priority)" class="inline-flex items-center mt-2 px-3 py-1 rounded-full text-sm font-medium">
            {{ getPriorityLabel(task.priority) }}
          </span>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 p-5">
          <p class="text-sm font-medium text-gray-500">Tip</p>
          <span :class="getTaskTypeBadge(task.task_type)" class="inline-flex items-center mt-2 px-3 py-1 rounded-full text-sm font-medium">
            {{ getTaskTypeLabel(task.task_type) }}
          </span>
        </div>
      </div>

      <!-- Main Content -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Description -->
          <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-orange-50 to-amber-50 border-b border-gray-200">
              <h2 class="text-lg font-semibold text-gray-900">Açıklama</h2>
            </div>
            <div class="p-6">
              <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ task.task_description || 'Açıklama girilmemiş' }}</p>
            </div>
          </div>

          <!-- Timeline -->
          <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-orange-50 to-amber-50 border-b border-gray-200">
              <h2 class="text-lg font-semibold text-gray-900">Zaman Çizelgesi</h2>
            </div>
            <div class="p-6 space-y-4">
              <div class="grid grid-cols-2 gap-4">
                <div class="bg-gray-50 rounded-lg p-4">
                  <label class="block text-xs font-medium text-gray-500 mb-1">Başlangıç</label>
                  <p class="text-sm font-semibold text-gray-900">{{ formatDate(task.start_date) }}</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-4">
                  <label class="block text-xs font-medium text-gray-500 mb-1">Bitiş</label>
                  <p class="text-sm font-semibold text-gray-900">{{ formatDate(task.end_date) }}</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-4">
                  <label class="block text-xs font-medium text-gray-500 mb-1">Süre</label>
                  <p class="text-sm font-semibold text-gray-900">{{ task.duration || calculateDuration(task.start_date, task.end_date) }} gün</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-4">
                  <label class="block text-xs font-medium text-gray-500 mb-1">Gerçekleşen</label>
                  <p class="text-sm font-semibold text-gray-900">{{ task.actual_duration || '-' }}</p>
                </div>
              </div>
              <div v-if="task.is_delayed" class="bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="flex items-center">
                  <svg class="w-5 h-5 text-red-600 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  <span class="text-sm font-medium text-red-800">{{ task.delay_days }} gün gecikme var</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Budget -->
          <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-orange-50 to-amber-50 border-b border-gray-200">
              <h2 class="text-lg font-semibold text-gray-900">Bütçe</h2>
            </div>
            <div class="p-6">
              <div class="grid grid-cols-2 gap-6">
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-5 border border-blue-200">
                  <label class="block text-sm font-medium text-blue-900 mb-2">Tahmini Maliyet</label>
                  <p class="text-2xl font-bold text-blue-700">{{ formatCurrency(task.estimated_cost) }}</p>
                </div>
                <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg p-5 border border-green-200">
                  <label class="block text-sm font-medium text-green-900 mb-2">Gerçekleşen</label>
                  <p class="text-2xl font-bold text-green-700">{{ formatCurrency(task.actual_cost) }}</p>
                </div>
              </div>
              <div v-if="task.cost_variance !== 0" class="mt-4 p-4 rounded-lg border" :class="task.cost_variance > 0 ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200'">
                <label class="block text-sm font-medium mb-1" :class="task.cost_variance > 0 ? 'text-green-900' : 'text-red-900'">Bütçe Varyansı</label>
                <p class="text-xl font-bold" :class="task.cost_variance > 0 ? 'text-green-700' : 'text-red-700'">
                  {{ task.cost_variance > 0 ? '+' : '' }}{{ formatCurrency(task.cost_variance) }}
                </p>
              </div>
            </div>
          </div>

          <!-- Sub Tasks -->
          <div v-if="subTasks && subTasks.length > 0" class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-orange-50 to-amber-50 border-b border-gray-200">
              <h2 class="text-lg font-semibold text-gray-900">Alt Görevler ({{ subTasks.length }})</h2>
            </div>
            <div class="divide-y divide-gray-200">
              <div v-for="subTask in subTasks" :key="subTask.id" class="p-4 hover:bg-gray-50 transition-colors">
                <div class="flex items-center justify-between">
                  <div class="flex-1">
                    <Link :href="route('project-schedules.show', subTask.id)" class="text-sm font-medium text-gray-900 hover:text-orange-600">
                      {{ subTask.task_code }} - {{ subTask.task_name }}
                    </Link>
                    <div class="flex items-center gap-3 mt-2">
                      <span :class="getStatusBadge(subTask.status)" class="px-2 py-1 text-xs font-medium rounded-full">
                        {{ getStatusLabel(subTask.status) }}
                      </span>
                      <span class="text-xs text-gray-500">{{ subTask.completion_percentage }}%</span>
                    </div>
                  </div>
                  <div class="ml-4 w-24">
                    <div class="w-full bg-gray-200 rounded-full h-2">
                      <div :class="['h-2 rounded-full', getProgressColorClass(subTask.completion_percentage)]" :style="{ width: subTask.completion_percentage + '%' }"></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Notes -->
          <div v-if="task.notes" class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-orange-50 to-amber-50 border-b border-gray-200">
              <h2 class="text-lg font-semibold text-gray-900">Notlar</h2>
            </div>
            <div class="p-6">
              <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ task.notes }}</p>
            </div>
          </div>
        </div>

        <!-- Right Column -->
        <div class="space-y-6">
          <!-- Quick Actions -->
          <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-orange-50 to-amber-50 border-b border-gray-200">
              <h2 class="text-lg font-semibold text-gray-900">Hızlı İşlemler</h2>
            </div>
            <div class="p-6 space-y-3">
              <button v-if="task.status === 'not_started'" @click="markAsStarted" class="w-full px-4 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium transition-colors shadow-sm">
                ▶ Başlat
              </button>
              <button v-if="task.status === 'in_progress'" @click="markAsCompleted" class="w-full px-4 py-2.5 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm font-medium transition-colors shadow-sm">
                ✓ Tamamlandı Olarak İşaretle
              </button>
              <div v-if="task.status === 'in_progress'" class="space-y-2">
                <label class="block text-sm font-medium text-gray-700">İlerlemeyi Güncelle</label>
                <div class="flex gap-2">
                  <input v-model="progressValue" type="number" min="0" max="100" class="flex-1 rounded-lg border-gray-300 text-sm">
                  <button @click="updateProgress" class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 text-sm font-medium transition-colors">
                    Güncelle
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Assignment -->
          <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-orange-50 to-amber-50 border-b border-gray-200">
              <h2 class="text-lg font-semibold text-gray-900">Atama</h2>
            </div>
            <div class="p-6 space-y-4">
              <div v-if="task.assigned_to" class="flex items-center gap-3">
                <div class="w-10 h-10 bg-orange-100 rounded-full flex items-center justify-center">
                  <span class="text-orange-600 font-semibold text-sm">{{ getInitials(task.assigned?.name) }}</span>
                </div>
                <div>
                  <label class="block text-xs font-medium text-gray-500">Sorumlu</label>
                  <p class="text-sm font-semibold text-gray-900">{{ task.assigned?.name }}</p>
                  <p class="text-xs text-gray-500">{{ task.assigned?.position }}</p>
                </div>
              </div>
              <div v-if="task.department_id" class="pt-3 border-t border-gray-100">
                <label class="block text-xs font-medium text-gray-500 mb-1">Departman</label>
                <p class="text-sm font-semibold text-gray-900">{{ task.department?.name }}</p>
              </div>
              <div v-if="task.parent_task_id" class="pt-3 border-t border-gray-100">
                <label class="block text-xs font-medium text-gray-500 mb-1">Üst Görev</label>
                <Link :href="route('project-schedules.show', task.parent_task_id)" class="text-sm font-semibold text-orange-600 hover:text-orange-900">
                  {{ task.parent_task?.task_code }} - {{ task.parent_task?.task_name }}
                </Link>
              </div>
            </div>
          </div>

          <!-- Dependencies -->
          <div v-if="predecessorTasks && predecessorTasks.length > 0" class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-orange-50 to-amber-50 border-b border-gray-200">
              <h2 class="text-lg font-semibold text-gray-900">Bağımlılıklar</h2>
            </div>
            <div class="p-6 space-y-3">
              <div v-for="pred in predecessorTasks" :key="pred.id" class="bg-gray-50 rounded-lg p-3">
                <Link :href="route('project-schedules.show', pred.id)" class="text-sm font-medium text-gray-900 hover:text-orange-600 block">
                  {{ pred.task_code }} - {{ pred.task_name }}
                </Link>
                <div class="flex items-center gap-2 mt-2">
                  <span class="text-xs text-gray-500">{{ pred.dependency_type }}</span>
                  <span :class="getStatusBadge(pred.status)" class="px-2 py-0.5 text-xs font-medium rounded-full">
                    {{ pred.completion_percentage }}%
                  </span>
                </div>
              </div>
            </div>
          </div>

          <!-- Metadata -->
          <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-orange-50 to-amber-50 border-b border-gray-200">
              <h2 class="text-lg font-semibold text-gray-900">Kayıt Bilgileri</h2>
            </div>
            <div class="p-6 space-y-3 text-sm">
              <div class="flex justify-between">
                <span class="text-gray-500">Oluşturulma:</span>
                <span class="font-medium text-gray-900">{{ formatDateTime(task.created_at) }}</span>
              </div>
              <div class="flex justify-between pt-3 border-t border-gray-100">
                <span class="text-gray-500">Son Güncelleme:</span>
                <span class="font-medium text-gray-900">{{ formatDateTime(task.updated_at) }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  task: Object,
  subTasks: Array,
  predecessorTasks: Array
})

const progressValue = ref(props.task.completion_percentage)

function formatDate(date) {
  if (!date) return '-'
  const d = new Date(date)
  return d.toLocaleDateString('tr-TR')
}

function formatDateTime(date) {
  if (!date) return '-'
  const d = new Date(date)
  return d.toLocaleString('tr-TR')
}

function formatCurrency(value) {
  return new Intl.NumberFormat('tr-TR', { style: 'currency', currency: 'TRY' }).format(value || 0)
}

function calculateDuration(start, end) {
  if (!start || !end) return '-'
  const s = new Date(start)
  const e = new Date(end)
  const diff = Math.ceil((e - s) / (1000 * 60 * 60 * 24))
  return diff + 1
}

function getInitials(name) {
  if (!name) return '?'
  return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)
}

function getStatusLabel(status) {
  const labels = {
    not_started: 'Başlamadı',
    in_progress: 'Devam Ediyor',
    completed: 'Tamamlandı',
    on_hold: 'Beklemede'
  }
  return labels[status] || status
}

function getStatusIcon(status) {
  const icons = {
    not_started: '○',
    in_progress: '◐',
    completed: '●',
    on_hold: '⊗'
  }
  return icons[status] || '○'
}

function getStatusBadge(status) {
  const badges = {
    not_started: 'bg-gray-100 text-gray-800',
    in_progress: 'bg-blue-100 text-blue-800',
    completed: 'bg-green-100 text-green-800',
    on_hold: 'bg-yellow-100 text-yellow-800'
  }
  return badges[status] || 'bg-gray-100 text-gray-800'
}

function getPriorityLabel(priority) {
  const labels = {
    low: 'Düşük',
    medium: 'Orta',
    high: 'Yüksek',
    critical: 'Kritik'
  }
  return labels[priority] || priority
}

function getPriorityBadge(priority) {
  const badges = {
    low: 'bg-gray-100 text-gray-800',
    medium: 'bg-blue-100 text-blue-800',
    high: 'bg-orange-100 text-orange-800',
    critical: 'bg-red-100 text-red-800'
  }
  return badges[priority] || 'bg-gray-100 text-gray-800'
}

function getTaskTypeLabel(type) {
  const labels = {
    phase: 'Faz',
    milestone: 'Kilometre Taşı',
    activity: 'Aktivite',
    deliverable: 'Çıktı',
    meeting: 'Toplantı'
  }
  return labels[type] || type
}

function getTaskTypeBadge(type) {
  const badges = {
    phase: 'bg-purple-100 text-purple-800',
    milestone: 'bg-yellow-100 text-yellow-800',
    activity: 'bg-blue-100 text-blue-800',
    deliverable: 'bg-green-100 text-green-800',
    meeting: 'bg-gray-100 text-gray-800'
  }
  return badges[type] || 'bg-blue-100 text-blue-800'
}

function getProgressColor(percentage) {
  if (percentage >= 75) return '#10b981'
  if (percentage >= 50) return '#3b82f6'
  if (percentage >= 25) return '#f59e0b'
  return '#f97316'
}

function getProgressColorClass(percentage) {
  if (percentage >= 75) return 'bg-green-500'
  if (percentage >= 50) return 'bg-blue-500'
  if (percentage >= 25) return 'bg-yellow-500'
  return 'bg-orange-500'
}

function markAsStarted() {
  if (confirm('Bu görevi başlatmak istediğinizden emin misiniz?')) {
    router.post(route('project-schedules.update', props.task.id), {
      status: 'in_progress',
      _method: 'PUT'
    })
  }
}

function markAsCompleted() {
  if (confirm('Bu görevi tamamlandı olarak işaretlemek istediğinizden emin misiniz?')) {
    router.post(route('project-schedules.update', props.task.id), {
      status: 'completed',
      completion_percentage: 100,
      _method: 'PUT'
    })
  }
}

function updateProgress() {
  router.post(route('project-schedules.update-progress', props.task.id), {
    completion_percentage: progressValue.value
  })
}
</script>
