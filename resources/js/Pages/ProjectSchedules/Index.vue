<template>
  <AppLayout title="Proje Takvimi" :full-width="true">
    <template #fullWidthHeader>
      <div class="bg-gradient-to-r from-orange-600 via-amber-600 to-yellow-600 border-b border-orange-900/20 w-full">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
          <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <div class="flex-1 min-w-0">
              <div class="flex items-center space-x-4 mb-3">
                <div class="flex-shrink-0 w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm">
                  <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                  </svg>
                </div>
                <div>
                  <h1 class="text-2xl lg:text-3xl font-bold text-white">Proje Takvimi</h1>
                  <p class="text-orange-100 text-sm mt-1">Proje görev planlaması ve takibi</p>
                </div>
              </div>
              <div class="flex flex-wrap items-center gap-4">
                <div class="bg-white/10 backdrop-blur-sm rounded-lg px-3 py-2">
                  <span class="text-orange-100 text-sm">Toplam:</span>
                  <span class="font-semibold text-white ml-1">{{ statistics.total_tasks }}</span>
                </div>
                <div class="bg-white/10 backdrop-blur-sm rounded-lg px-3 py-2">
                  <span class="text-orange-100 text-sm">Devam Eden:</span>
                  <span class="font-semibold text-white ml-1">{{ statistics.in_progress_tasks }}</span>
                </div>
                <div class="bg-white/10 backdrop-blur-sm rounded-lg px-3 py-2">
                  <span class="text-orange-100 text-sm">Tamamlanan:</span>
                  <span class="font-semibold text-white ml-1">{{ statistics.completed_tasks }}</span>
                </div>
              </div>
            </div>
            <div class="flex-shrink-0 flex gap-3">
              <Link v-if="selectedProject" :href="route('projects.gantt', selectedProject)" class="inline-flex items-center px-4 py-2 bg-white/20 text-white text-sm font-medium rounded-lg hover:bg-white/30 shadow-lg transition-all backdrop-blur-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                Gantt Görünümü
              </Link>
              <Link :href="route('project-schedules.create')" class="inline-flex items-center px-4 py-2 bg-white text-orange-600 text-sm font-medium rounded-lg hover:bg-orange-50 shadow-lg transition-all">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                Yeni Görev
              </Link>
            </div>
          </div>
        </div>
      </div>
    </template>

    <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
      <!-- Filters -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
          <input v-model="filterForm.search" type="text" placeholder="Ara..." class="rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500">
          <select v-model="filterForm.project_id" @change="applyFilters" class="rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500">
            <option value="">Tüm Projeler</option>
            <option v-for="project in projects" :key="project.id" :value="project.id">{{ project.name }}</option>
          </select>
          <select v-model="filterForm.task_type" class="rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500">
            <option value="">Tüm Tipler</option>
            <option value="phase">Faz</option>
            <option value="milestone">Kilometre Taşı</option>
            <option value="activity">Aktivite</option>
            <option value="deliverable">Çıktı</option>
            <option value="meeting">Toplantı</option>
          </select>
          <select v-model="filterForm.status" class="rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500">
            <option value="">Tüm Durumlar</option>
            <option value="not_started">Başlamadı</option>
            <option value="in_progress">Devam Ediyor</option>
            <option value="completed">Tamamlandı</option>
            <option value="on_hold">Beklemede</option>
          </select>
          <input v-model="filterForm.date_from" type="date" class="rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500">
          <button @click="applyFilters" class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition-colors">Filtrele</button>
        </div>
      </div>

      <!-- Table -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kod</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Görev Adı</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Proje</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tip</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Başlangıç</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Bitiş</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">İlerleme</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Durum</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">İşlemler</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="task in tasks.data" :key="task.id" :class="['hover:bg-gray-50', task.is_delayed ? 'bg-red-50/30' : '']">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ task.task_code }}</td>
                <td class="px-6 py-4 text-sm text-gray-900">
                  <div class="flex items-center">
                    <span v-if="task.parent_task_id" class="text-gray-400 mr-2">└─</span>
                    {{ task.task_name }}
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ task.project?.name }}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="getTaskTypeBadge(task.task_type)">{{ getTaskTypeLabel(task.task_type) }}</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ formatDate(task.start_date) }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  <div class="flex items-center gap-2">
                    {{ formatDate(task.end_date) }}
                    <span v-if="task.is_delayed" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                      +{{ task.delay_days }} gün
                    </span>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center gap-2">
                    <div class="flex-1 bg-gray-200 rounded-full h-2 w-20">
                      <div :class="['h-2 rounded-full', getProgressColor(task.completion_percentage)]" :style="{ width: task.completion_percentage + '%' }"></div>
                    </div>
                    <span class="text-sm text-gray-600">{{ task.completion_percentage }}%</span>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="getStatusBadge(task.status)">{{ getStatusLabel(task.status) }}</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  <div class="flex items-center gap-3">
                    <Link :href="route('project-schedules.show', task.id)" class="text-blue-600 hover:text-blue-900 font-medium">Detay</Link>
                    <Link :href="route('project-schedules.edit', task.id)" class="text-orange-600 hover:text-orange-900 font-medium">Düzenle</Link>
                    <Link v-if="task.project_id" :href="route('projects.gantt', task.project_id)" class="inline-flex items-center text-purple-600 hover:text-purple-900 font-medium">
                      <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                      </svg>
                      Gantt
                    </Link>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="tasks.links" class="bg-gray-50 px-4 py-3 border-t border-gray-200">
          <div class="flex justify-between items-center">
            <div class="text-sm text-gray-600">Toplam {{ tasks.total }} kayıt</div>
            <div class="flex space-x-2">
              <template v-for="link in tasks.links" :key="link.label">
                <Link v-if="link.url" :href="link.url" :class="['px-3 py-1 rounded', link.active ? 'bg-orange-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100']" v-html="link.label" />
                <span v-else :class="['px-3 py-1 rounded', 'bg-gray-100 text-gray-400 cursor-not-allowed']" v-html="link.label" />
              </template>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  tasks: Object,
  statistics: Object,
  projects: Array,
  filters: Object
})

const filterForm = reactive({
  search: props.filters?.search || '',
  project_id: props.filters?.project_id || '',
  task_type: props.filters?.task_type || '',
  status: props.filters?.status || '',
  date_from: props.filters?.date_from || '',
  date_to: props.filters?.date_to || ''
})

const selectedProject = computed(() => filterForm.project_id || null)

function applyFilters() {
  router.get(route('project-schedules.index'), filterForm, { preserveState: true })
}

function formatDate(date) {
  return date ? new Date(date).toLocaleDateString('tr-TR') : '-'
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
    phase: 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800',
    milestone: 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800',
    activity: 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800',
    deliverable: 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800',
    meeting: 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800'
  }
  return badges[type] || badges.activity
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

function getStatusBadge(status) {
  const badges = {
    not_started: 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800',
    in_progress: 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800',
    completed: 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800',
    on_hold: 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800'
  }
  return badges[status] || badges.not_started
}

function getProgressColor(percentage) {
  if (percentage >= 75) return 'bg-green-500'
  if (percentage >= 50) return 'bg-blue-500'
  if (percentage >= 25) return 'bg-yellow-500'
  return 'bg-orange-500'
}
</script>
