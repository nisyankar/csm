<template>
  <AppLayout title="Aktivite Logları" :full-width="true">
    <!-- Full-width header -->
    <template #fullWidthHeader>
      <div class="bg-gradient-to-r from-slate-700 via-gray-700 to-zinc-700 border-b border-slate-900/20 w-full">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
          <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <!-- Header Info -->
            <div class="flex-1 min-w-0">
              <div class="flex items-center space-x-4 mb-3">
                <div class="flex-shrink-0 w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm">
                  <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5m-7.5 3h7.5m3-9h3.375c.621 0 1.125.504 1.125 1.125V18a2.25 2.25 0 0 1-2.25 2.25M16.5 7.5V18a2.25 2.25 0 0 0 2.25 2.25M16.5 7.5V4.875c0-.621-.504-1.125-1.125-1.125H4.125C3.504 3.75 3 4.254 3 4.875V18a2.25 2.25 0 0 0 2.25 2.25h13.5M6 7.5h3v3H6v-3Z" />
                  </svg>
                </div>
                <div>
                  <h1 class="text-2xl lg:text-3xl font-bold text-white">Aktivite Logları</h1>
                  <p class="text-slate-200 text-sm mt-1">Sistem aktivitelerini ve kullanıcı hareketlerini takip edin</p>
                </div>
              </div>

              <!-- Stats Row -->
              <div class="flex flex-wrap items-center gap-4 lg:gap-6">
                <div class="bg-white/10 backdrop-blur-sm rounded-lg px-3 py-2">
                  <span class="text-slate-200 text-sm">Toplam:</span>
                  <span class="font-semibold text-white ml-1">{{ statistics.total_logs || 0 }}</span>
                </div>
                <div class="bg-white/10 backdrop-blur-sm rounded-lg px-3 py-2">
                  <span class="text-slate-200 text-sm">Bugün:</span>
                  <span class="font-semibold text-white ml-1">{{ statistics.today_logs || 0 }}</span>
                </div>
                <div class="flex items-center space-x-4 text-sm">
                  <span class="flex items-center text-slate-200">
                    <div class="w-2 h-2 bg-yellow-400 rounded-full mr-2"></div>
                    Uyarı: <span class="text-white font-medium ml-1">{{ statistics.warning_count || 0 }}</span>
                  </span>
                  <span class="flex items-center text-slate-200">
                    <div class="w-2 h-2 bg-orange-400 rounded-full mr-2"></div>
                    Hata: <span class="text-white font-medium ml-1">{{ statistics.error_count || 0 }}</span>
                  </span>
                  <span class="flex items-center text-slate-200">
                    <div class="w-2 h-2 bg-red-400 rounded-full mr-2"></div>
                    Kritik: <span class="text-white font-medium ml-1">{{ statistics.critical_count || 0 }}</span>
                  </span>
                </div>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex-shrink-0 flex gap-3">
              <button
                @click="exportLogs"
                class="inline-flex items-center px-4 py-2 bg-white/20 text-white text-sm font-medium rounded-lg hover:bg-white/30 shadow-lg transition-all backdrop-blur-sm"
              >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Dışa Aktar
              </button>
            </div>
          </div>
        </div>

        <!-- Breadcrumb -->
        <div class="bg-black/10 border-t border-white/10">
          <div class="w-full px-4 sm:px-6 lg:px-8">
            <div class="flex items-center space-x-2 py-2">
              <ol class="flex items-center space-x-2">
                <li>
                  <Link :href="route('dashboard')" class="text-slate-200 hover:text-white transition-colors">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                    </svg>
                  </Link>
                </li>
                <li class="flex items-center">
                  <svg class="h-3 w-3 text-slate-300 mx-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                  </svg>
                  <span class="text-white font-medium text-sm">Aktivite Logları</span>
                </li>
              </ol>
            </div>
          </div>
        </div>
      </div>
    </template>

    <!-- Main Content -->
    <div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
      <!-- Filters -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden">
        <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
          <h3 class="text-lg font-semibold text-gray-900">Filtreler</h3>
        </div>
        <div class="p-6">
          <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Kullanıcı</label>
              <select
                v-model="filterForm.user_id"
                @change="applyFilters"
                class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-slate-500 focus:border-transparent transition-all"
              >
                <option :value="null">Tüm Kullanıcılar</option>
                <option v-for="user in users" :key="user.id" :value="user.id">
                  {{ user.name }}
                </option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Proje</label>
              <select
                v-model="filterForm.project_id"
                @change="applyFilters"
                class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-slate-500 focus:border-transparent transition-all"
              >
                <option :value="null">Tüm Projeler</option>
                <option v-for="project in projects" :key="project.id" :value="project.id">
                  {{ project.name }}
                </option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Aktivite Tipi</label>
              <select
                v-model="filterForm.activity_type"
                @change="applyFilters"
                class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-slate-500 focus:border-transparent transition-all"
              >
                <option :value="null">Tüm Tipler</option>
                <option v-for="type in activityTypes" :key="type.value" :value="type.value">
                  {{ type.label }}
                </option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Önem Derecesi</label>
              <select
                v-model="filterForm.severity"
                @change="applyFilters"
                class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-slate-500 focus:border-transparent transition-all"
              >
                <option :value="null">Tümü</option>
                <option v-for="level in severityLevels" :key="level.value" :value="level.value">
                  {{ level.label }}
                </option>
              </select>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Başlangıç Tarihi</label>
              <input
                type="date"
                v-model="filterForm.date_from"
                @change="applyFilters"
                class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-slate-500 focus:border-transparent transition-all"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Bitiş Tarihi</label>
              <input
                type="date"
                v-model="filterForm.date_to"
                @change="applyFilters"
                class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-slate-500 focus:border-transparent transition-all"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Arama</label>
              <input
                type="text"
                v-model="filterForm.search"
                @input="applyFilters"
                placeholder="Arama yapın..."
                class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-slate-500 focus:border-transparent transition-all"
              />
            </div>
          </div>
        </div>
      </div>

      <!-- Logs Table -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Zaman</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Kullanıcı</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Aktivite</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Tip</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Önem</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">IP</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-700 uppercase tracking-wider">Detay</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="log in logs.data" :key="log.id" class="hover:bg-gray-50 transition-colors">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ formatDateTime(log.logged_at) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 h-8 w-8">
                      <div class="h-8 w-8 rounded-full bg-slate-100 flex items-center justify-center">
                        <span class="text-slate-600 font-medium text-xs">{{ log.user?.name?.substring(0, 2).toUpperCase() || 'SY' }}</span>
                      </div>
                    </div>
                    <div class="ml-3">
                      <div class="text-sm font-medium text-gray-900">{{ log.user?.name || 'Sistem' }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <div class="text-sm text-gray-900">{{ log.action }}</div>
                  <div v-if="log.description" class="text-xs text-gray-500 mt-0.5">{{ log.description }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="getActivityTypeBadgeClass(log.activity_type)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                    {{ getActivityTypeLabel(log.activity_type) }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="getSeverityBadgeClass(log.severity)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                    {{ getSeverityLabel(log.severity) }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ log.ip_address || '-' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <Link :href="route('activity-logs.show', log.id)" class="text-slate-600 hover:text-slate-900 transition-colors">
                    Detay
                  </Link>
                </td>
              </tr>
              <tr v-if="!logs.data || logs.data.length === 0">
                <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                  <div class="flex flex-col items-center">
                    <svg class="w-12 h-12 text-gray-400 mb-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5m-7.5 3h7.5m3-9h3.375c.621 0 1.125.504 1.125 1.125V18a2.25 2.25 0 0 1-2.25 2.25M16.5 7.5V18a2.25 2.25 0 0 0 2.25 2.25M16.5 7.5V4.875c0-.621-.504-1.125-1.125-1.125H4.125C3.504 3.75 3 4.254 3 4.875V18a2.25 2.25 0 0 0 2.25 2.25h13.5M6 7.5h3v3H6v-3Z" />
                    </svg>
                    <p class="text-lg font-medium">Log kaydı bulunamadı</p>
                    <p class="text-sm text-gray-400 mt-1">Henüz aktivite kaydedilmemiş</p>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="logs.data && logs.data.length > 0" class="bg-gray-50 px-6 py-4 border-t border-gray-200">
          <div class="flex items-center justify-between">
            <div class="text-sm text-gray-700">
              Gösterilen: <span class="font-medium">{{ logs.from }}</span> - <span class="font-medium">{{ logs.to }}</span> / <span class="font-medium">{{ logs.total }}</span>
            </div>
            <div class="flex space-x-2">
              <component
                v-for="link in logs.links"
                :key="link.label"
                :is="link.url ? Link : 'span'"
                :href="link.url || undefined"
                :class="[
                  'px-3 py-2 rounded-lg text-sm font-medium transition-colors',
                  link.active
                    ? 'bg-slate-700 text-white'
                    : link.url
                    ? 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-300'
                    : 'bg-gray-100 text-gray-400 cursor-not-allowed'
                ]"
                v-html="link.label"
              />
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { reactive } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  logs: Object,
  statistics: Object,
  filters: Object,
  users: Array,
  projects: Array,
  activityTypes: Array,
  severityLevels: Array,
})

const filterForm = reactive({
  user_id: props.filters?.user_id || null,
  project_id: props.filters?.project_id || null,
  activity_type: props.filters?.activity_type || null,
  severity: props.filters?.severity || null,
  date_from: props.filters?.date_from || null,
  date_to: props.filters?.date_to || null,
  search: props.filters?.search || '',
})

function applyFilters() {
  router.get(route('activity-logs.index'), filterForm, {
    preserveState: true,
    preserveScroll: true,
  })
}

function getActivityTypeLabel(type) {
  const found = props.activityTypes.find(t => t.value === type)
  return found?.label || type
}

function getActivityTypeBadgeClass(type) {
  const classes = {
    created: 'bg-green-100 text-green-800',
    updated: 'bg-blue-100 text-blue-800',
    deleted: 'bg-red-100 text-red-800',
    viewed: 'bg-gray-100 text-gray-800',
    logged_in: 'bg-emerald-100 text-emerald-800',
    logged_out: 'bg-slate-100 text-slate-800',
    access_denied: 'bg-orange-100 text-orange-800',
    custom: 'bg-purple-100 text-purple-800',
  }
  return classes[type] || 'bg-gray-100 text-gray-800'
}

function getSeverityLabel(severity) {
  const found = props.severityLevels.find(s => s.value === severity)
  return found?.label || severity
}

function getSeverityBadgeClass(severity) {
  const classes = {
    info: 'bg-blue-100 text-blue-800',
    warning: 'bg-yellow-100 text-yellow-800',
    error: 'bg-orange-100 text-orange-800',
    critical: 'bg-red-100 text-red-800',
  }
  return classes[severity] || 'bg-gray-100 text-gray-800'
}

function formatDateTime(datetime) {
  if (!datetime) return '-'
  const date = new Date(datetime)
  return date.toLocaleString('tr-TR', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
  })
}

function exportLogs() {
  router.get(route('activity-logs.export'), filterForm)
}
</script>
