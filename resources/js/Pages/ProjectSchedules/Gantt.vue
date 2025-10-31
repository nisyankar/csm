<template>
  <AppLayout :title="`Gantt - ${project.name}`" :full-width="true">
    <template #fullWidthHeader>
      <div class="bg-gradient-to-r from-orange-600 via-amber-600 to-yellow-600 border-b border-orange-900/20 w-full">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
          <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <div class="flex-1 min-w-0">
              <div class="flex items-center space-x-4 mb-3">
                <div class="flex-shrink-0 w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm">
                  <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                  </svg>
                </div>
                <div>
                  <h1 class="text-2xl lg:text-3xl font-bold text-white">Gantt Görünümü</h1>
                  <p class="text-orange-100 text-sm mt-1">{{ project.project_code }} - {{ project.name }}</p>
                </div>
              </div>
              <div class="flex flex-wrap items-center gap-4">
                <div class="bg-white/10 backdrop-blur-sm rounded-lg px-3 py-2">
                  <span class="text-orange-100 text-sm">Toplam Görev:</span>
                  <span class="font-semibold text-white ml-1">{{ statistics.total_tasks }}</span>
                </div>
                <div class="bg-white/10 backdrop-blur-sm rounded-lg px-3 py-2">
                  <span class="text-orange-100 text-sm">Devam Eden:</span>
                  <span class="font-semibold text-white ml-1">{{ statistics.in_progress }}</span>
                </div>
                <div class="bg-white/10 backdrop-blur-sm rounded-lg px-3 py-2">
                  <span class="text-orange-100 text-sm">Tamamlanma:</span>
                  <span class="font-semibold text-white ml-1">{{ statistics.completion_rate }}%</span>
                </div>
              </div>
            </div>
            <div class="flex-shrink-0 flex gap-3">
              <Link :href="route('project-schedules.index')" class="inline-flex items-center px-4 py-2 bg-white/20 text-white text-sm font-medium rounded-lg hover:bg-white/30 shadow-lg transition-all backdrop-blur-sm">
                ← Liste Görünümü
              </Link>
              <Link :href="route('project-schedules.create', { project_id: project.id })" class="inline-flex items-center px-4 py-2 bg-white text-orange-600 text-sm font-medium rounded-lg hover:bg-orange-50 shadow-lg transition-all">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
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
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <select v-model="filters.task_type" @change="applyFilters" class="rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500">
            <option value="">Tüm Tipler</option>
            <option value="phase">Faz</option>
            <option value="milestone">Kilometre Taşı</option>
            <option value="activity">Aktivite</option>
            <option value="deliverable">Çıktı</option>
            <option value="meeting">Toplantı</option>
          </select>
          <select v-model="filters.status" @change="applyFilters" class="rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500">
            <option value="">Tüm Durumlar</option>
            <option value="not_started">Başlamadı</option>
            <option value="in_progress">Devam Ediyor</option>
            <option value="completed">Tamamlandı</option>
            <option value="on_hold">Beklemede</option>
          </select>
          <select v-model="filters.priority" @change="applyFilters" class="rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500">
            <option value="">Tüm Öncelikler</option>
            <option value="low">Düşük</option>
            <option value="medium">Orta</option>
            <option value="high">Yüksek</option>
            <option value="critical">Kritik</option>
          </select>
          <button @click="resetFilters" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
            Filtreleri Temizle
          </button>
        </div>
      </div>

      <!-- Gantt Chart -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden">
        <div class="px-6 py-4 bg-gradient-to-r from-orange-50 to-amber-50 border-b border-gray-200">
          <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-900">Proje Zaman Çizelgesi</h2>
            <div class="flex gap-2">
              <button @click="zoomLevel = 'day'" :class="zoomLevel === 'day' ? 'bg-orange-600 text-white' : 'bg-white text-gray-700 border border-gray-300'" class="px-3 py-1 text-sm rounded-lg transition-colors">Günlük</button>
              <button @click="zoomLevel = 'week'" :class="zoomLevel === 'week' ? 'bg-orange-600 text-white' : 'bg-white text-gray-700 border border-gray-300'" class="px-3 py-1 text-sm rounded-lg transition-colors">Haftalık</button>
              <button @click="zoomLevel = 'month'" :class="zoomLevel === 'month' ? 'bg-orange-600 text-white' : 'bg-white text-gray-700 border border-gray-300'" class="px-3 py-1 text-sm rounded-lg transition-colors">Aylık</button>
            </div>
          </div>
        </div>

        <div class="p-6">
          <apexchart
            v-if="chartReady"
            type="rangeBar"
            height="600"
            :options="chartOptions"
            :series="chartSeries"
          ></apexchart>
          <div v-else class="flex items-center justify-center h-96">
            <div class="text-center">
              <svg class="animate-spin h-12 w-12 text-orange-600 mx-auto mb-4" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <p class="text-gray-500">Gantt chart yükleniyor...</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Task Legend -->
      <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mt-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200/80 p-4">
          <div class="flex items-center gap-3">
            <div class="w-4 h-4 bg-purple-500 rounded"></div>
            <span class="text-sm font-medium text-gray-700">Faz</span>
          </div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200/80 p-4">
          <div class="flex items-center gap-3">
            <div class="w-4 h-4 bg-yellow-500 rounded"></div>
            <span class="text-sm font-medium text-gray-700">Kilometre Taşı</span>
          </div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200/80 p-4">
          <div class="flex items-center gap-3">
            <div class="w-4 h-4 bg-blue-500 rounded"></div>
            <span class="text-sm font-medium text-gray-700">Aktivite</span>
          </div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200/80 p-4">
          <div class="flex items-center gap-3">
            <div class="w-4 h-4 bg-green-500 rounded"></div>
            <span class="text-sm font-medium text-gray-700">Çıktı</span>
          </div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200/80 p-4">
          <div class="flex items-center gap-3">
            <div class="w-4 h-4 bg-gray-500 rounded"></div>
            <span class="text-sm font-medium text-gray-700">Toplantı</span>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import VueApexCharts from 'vue3-apexcharts'

const apexchart = VueApexCharts

const props = defineProps({
  project: Object,
  ganttData: Array,
  statistics: Object
})

const chartReady = ref(false)
const zoomLevel = ref('week')
const filters = ref({
  task_type: '',
  status: '',
  priority: ''
})

const filteredData = computed(() => {
  let data = [...props.ganttData]

  if (filters.value.task_type) {
    data = data.filter(item => item.type === filters.value.task_type)
  }

  if (filters.value.status) {
    data = data.filter(item => item.status === filters.value.status)
  }

  if (filters.value.priority) {
    data = data.filter(item => item.priority === filters.value.priority)
  }

  return data
})

const chartSeries = computed(() => {
  return [{
    name: 'Görevler',
    data: filteredData.value.map(task => ({
      x: task.name,
      y: [
        new Date(task.start).getTime(),
        new Date(task.end).getTime()
      ],
      fillColor: getTaskColor(task.type),
      taskId: task.id,
      progress: task.progress,
      status: task.status,
      type: task.type
    }))
  }]
})

const chartOptions = computed(() => ({
  chart: {
    type: 'rangeBar',
    height: 600,
    toolbar: {
      show: true,
      tools: {
        download: true,
        selection: true,
        zoom: true,
        zoomin: true,
        zoomout: true,
        pan: true,
        reset: true
      }
    },
    events: {
      dataPointSelection: (event, chartContext, config) => {
        const taskId = filteredData.value[config.dataPointIndex]?.id
        if (taskId) {
          router.visit(route('project-schedules.show', taskId))
        }
      }
    }
  },
  plotOptions: {
    bar: {
      horizontal: true,
      distributed: false,
      rangeBarGroupRows: true,
      barHeight: '70%',
      dataLabels: {
        hideOverflowingLabels: false
      }
    }
  },
  dataLabels: {
    enabled: true,
    formatter: function(val, opts) {
      const task = filteredData.value[opts.dataPointIndex]
      return task ? `${task.progress}%` : ''
    },
    style: {
      colors: ['#fff'],
      fontSize: '11px',
      fontWeight: 'bold'
    }
  },
  xaxis: {
    type: 'datetime',
    labels: {
      datetimeUTC: false,
      format: getDateFormat()
    }
  },
  yaxis: {
    labels: {
      style: {
        fontSize: '12px',
        fontWeight: 500
      }
    }
  },
  grid: {
    row: {
      colors: ['#f3f4f6', 'transparent'],
      opacity: 0.5
    }
  },
  tooltip: {
    custom: function({ seriesIndex, dataPointIndex, w }) {
      const task = filteredData.value[dataPointIndex]
      if (!task) return ''

      return `
        <div class="p-3 bg-white shadow-lg rounded-lg border border-gray-200" style="min-width: 200px;">
          <div class="font-semibold text-gray-900 mb-2">${task.name}</div>
          <div class="text-sm text-gray-600 space-y-1">
            <div><span class="font-medium">Tip:</span> ${getTaskTypeLabel(task.type)}</div>
            <div><span class="font-medium">Durum:</span> ${getStatusLabel(task.status)}</div>
            <div><span class="font-medium">İlerleme:</span> ${task.progress}%</div>
            <div><span class="font-medium">Başlangıç:</span> ${formatDate(task.start)}</div>
            <div><span class="font-medium">Bitiş:</span> ${formatDate(task.end)}</div>
            ${task.assignee ? `<div><span class="font-medium">Sorumlu:</span> ${task.assignee}</div>` : ''}
          </div>
        </div>
      `
    }
  },
  legend: {
    show: false
  }
}))

function getTaskColor(type) {
  const colors = {
    phase: '#9333ea',      // purple-600
    milestone: '#eab308',  // yellow-500
    activity: '#3b82f6',   // blue-500
    deliverable: '#10b981',// green-500
    meeting: '#6b7280'     // gray-500
  }
  return colors[type] || '#3b82f6'
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

function getStatusLabel(status) {
  const labels = {
    not_started: 'Başlamadı',
    in_progress: 'Devam Ediyor',
    completed: 'Tamamlandı',
    on_hold: 'Beklemede',
    delayed: 'Gecikmiş',
    cancelled: 'İptal'
  }
  return labels[status] || status
}

function formatDate(dateStr) {
  if (!dateStr) return '-'
  const date = new Date(dateStr)
  return date.toLocaleDateString('tr-TR')
}

function getDateFormat() {
  if (zoomLevel.value === 'day') return 'dd MMM'
  if (zoomLevel.value === 'week') return 'dd MMM'
  return 'MMM yyyy'
}

function applyFilters() {
  // Filters are reactive, chart will update automatically
}

function resetFilters() {
  filters.value = {
    task_type: '',
    status: '',
    priority: ''
  }
}

onMounted(() => {
  // Small delay to ensure ApexCharts is ready
  setTimeout(() => {
    chartReady.value = true
  }, 100)
})
</script>
