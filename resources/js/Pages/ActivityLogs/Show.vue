<template>
  <AppLayout :title="`Aktivite Log #${activityLog.id}`" :full-width="true">
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
                  <h1 class="text-2xl lg:text-3xl font-bold text-white">
                    Aktivite Log #{{ activityLog.id }}
                  </h1>
                  <p class="text-slate-100 text-sm mt-1">{{ activityLog.action }}</p>
                </div>
              </div>

              <!-- Badges -->
              <div class="flex items-center space-x-3">
                <span :class="getSeverityBadgeClass(activityLog.severity)" class="px-4 py-1.5 text-sm font-semibold rounded-full">
                  {{ getSeverityLabel(activityLog.severity) }}
                </span>
                <span :class="getActivityTypeBadgeClass(activityLog.activity_type)" class="px-4 py-1.5 text-sm font-semibold rounded-full">
                  {{ getActivityTypeLabel(activityLog.activity_type) }}
                </span>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex-shrink-0 flex items-center space-x-3">
              <Link
                :href="route('activity-logs.index')"
                class="inline-flex items-center px-4 py-2 bg-white/10 backdrop-blur-sm border border-white/30 text-white text-sm font-medium rounded-lg hover:bg-white/20 transition-all duration-200"
              >
                <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>
                Geri Dön
              </Link>
            </div>
          </div>
        </div>

        <!-- Breadcrumb -->
        <div class="bg-black/10 border-t border-white/10">
          <div class="w-full px-4 sm:px-6 lg:px-8">
            <div class="flex items-center space-x-2 py-2">
              <ol class="flex items-center space-x-2">
                <li>
                  <Link :href="route('dashboard')" class="text-slate-100 hover:text-white transition-colors">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                    </svg>
                  </Link>
                </li>
                <li class="flex items-center">
                  <svg class="h-3 w-3 text-slate-200 mx-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                  </svg>
                  <Link :href="route('activity-logs.index')" class="text-slate-100 hover:text-white text-sm">Aktivite Logları</Link>
                </li>
                <li class="flex items-center">
                  <svg class="h-3 w-3 text-slate-200 mx-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                  </svg>
                  <span class="text-white font-medium text-sm">Detay</span>
                </li>
              </ol>
            </div>
          </div>
        </div>
      </div>
    </template>

    <!-- Main Content -->
    <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column - Main Details -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Temel Bilgiler -->
          <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
              <h3 class="text-lg font-medium text-gray-900">Temel Bilgiler</h3>
            </div>
            <div class="p-6">
              <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <dt class="text-sm font-medium text-gray-500">Aktivite ID</dt>
                  <dd class="mt-1 text-sm text-gray-900 font-mono">#{{ activityLog.id }}</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">Tarih/Saat</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ formatDateTime(activityLog.logged_at) }}</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">Aktivite Tipi</dt>
                  <dd class="mt-1">
                    <span :class="getActivityTypeBadgeClass(activityLog.activity_type)" class="px-3 py-1 text-xs font-semibold rounded-full">
                      {{ getActivityTypeLabel(activityLog.activity_type) }}
                    </span>
                  </dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">Önem Seviyesi</dt>
                  <dd class="mt-1">
                    <span :class="getSeverityBadgeClass(activityLog.severity)" class="px-3 py-1 text-xs font-semibold rounded-full">
                      {{ getSeverityLabel(activityLog.severity) }}
                    </span>
                  </dd>
                </div>
                <div class="md:col-span-2">
                  <dt class="text-sm font-medium text-gray-500">Açıklama</dt>
                  <dd class="mt-1 text-sm text-gray-900 font-medium">{{ activityLog.action }}</dd>
                </div>
                <div v-if="activityLog.description" class="md:col-span-2">
                  <dt class="text-sm font-medium text-gray-500">Detaylı Açıklama</dt>
                  <dd class="mt-1 text-sm text-gray-700 whitespace-pre-wrap">{{ activityLog.description }}</dd>
                </div>
              </dl>
            </div>
          </div>

          <!-- Subject (İlgili Kayıt) -->
          <div v-if="activityLog.subject_type" class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-purple-50 to-indigo-50">
              <h3 class="text-lg font-medium text-gray-900">İlgili Kayıt</h3>
            </div>
            <div class="p-6">
              <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <dt class="text-sm font-medium text-gray-500">Model Tipi</dt>
                  <dd class="mt-1 text-sm text-gray-900 font-mono">{{ activityLog.subject_type }}</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">Kayıt ID</dt>
                  <dd class="mt-1 text-sm text-gray-900 font-mono">#{{ activityLog.subject_id }}</dd>
                </div>
              </dl>
            </div>
          </div>

          <!-- Properties (Özellikler) -->
          <div v-if="activityLog.properties" class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
              <h3 class="text-lg font-medium text-gray-900">Özellikler ve Değişiklikler</h3>
            </div>
            <div class="p-6">
              <!-- Old Values -->
              <div v-if="activityLog.properties.old_values" class="mb-6">
                <h4 class="text-sm font-medium text-gray-700 mb-3 flex items-center">
                  <svg class="w-5 h-5 mr-2 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                  </svg>
                  Eski Değerler
                </h4>
                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                  <pre class="text-xs text-red-900 font-mono overflow-x-auto">{{ JSON.stringify(activityLog.properties.old_values, null, 2) }}</pre>
                </div>
              </div>

              <!-- New Values -->
              <div v-if="activityLog.properties.new_values" class="mb-6">
                <h4 class="text-sm font-medium text-gray-700 mb-3 flex items-center">
                  <svg class="w-5 h-5 mr-2 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                  </svg>
                  Yeni Değerler
                </h4>
                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                  <pre class="text-xs text-green-900 font-mono overflow-x-auto">{{ JSON.stringify(activityLog.properties.new_values, null, 2) }}</pre>
                </div>
              </div>

              <!-- Attributes -->
              <div v-if="activityLog.properties.attributes" class="mb-6">
                <h4 class="text-sm font-medium text-gray-700 mb-3 flex items-center">
                  <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                  </svg>
                  Özellikler
                </h4>
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                  <pre class="text-xs text-blue-900 font-mono overflow-x-auto">{{ JSON.stringify(activityLog.properties.attributes, null, 2) }}</pre>
                </div>
              </div>

              <!-- Raw Properties -->
              <div v-if="!activityLog.properties.old_values && !activityLog.properties.new_values && !activityLog.properties.attributes">
                <h4 class="text-sm font-medium text-gray-700 mb-3">Tüm Özellikler</h4>
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                  <pre class="text-xs text-gray-900 font-mono overflow-x-auto">{{ JSON.stringify(activityLog.properties, null, 2) }}</pre>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Right Column - Sidebar Info -->
        <div class="lg:col-span-1 space-y-6">
          <!-- Kullanıcı Bilgisi -->
          <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
              <h3 class="text-lg font-medium text-gray-900">Kullanıcı</h3>
            </div>
            <div class="p-6">
              <div v-if="activityLog.user" class="flex items-center space-x-3 mb-4">
                <div class="flex-shrink-0 w-12 h-12 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-full flex items-center justify-center">
                  <span class="text-white font-bold text-lg">{{ getUserInitials(activityLog.user.name) }}</span>
                </div>
                <div>
                  <div class="text-sm font-semibold text-gray-900">{{ activityLog.user.name }}</div>
                  <div class="text-xs text-gray-500">{{ activityLog.user.email }}</div>
                </div>
              </div>
              <div v-else class="text-sm text-gray-500 italic">Sistem</div>
            </div>
          </div>

          <!-- Proje Bilgisi -->
          <div v-if="activityLog.project" class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
              <h3 class="text-lg font-medium text-gray-900">Proje</h3>
            </div>
            <div class="p-6">
              <div class="text-sm font-semibold text-gray-900 mb-1">{{ activityLog.project.name }}</div>
              <div class="text-xs text-gray-500">{{ activityLog.project.project_code }}</div>
            </div>
          </div>

          <!-- Teknik Bilgiler -->
          <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
              <h3 class="text-lg font-medium text-gray-900">Teknik Bilgiler</h3>
            </div>
            <div class="p-6 space-y-4">
              <div v-if="activityLog.ip_address">
                <dt class="text-xs font-medium text-gray-500 mb-1">IP Adresi</dt>
                <dd class="text-sm text-gray-900 font-mono bg-gray-100 px-2 py-1 rounded">{{ activityLog.ip_address }}</dd>
              </div>
              <div v-if="activityLog.user_agent">
                <dt class="text-xs font-medium text-gray-500 mb-1">User Agent</dt>
                <dd class="text-xs text-gray-900 bg-gray-100 px-2 py-1 rounded break-all">{{ activityLog.user_agent }}</dd>
              </div>
              <div v-if="activityLog.route_name">
                <dt class="text-xs font-medium text-gray-500 mb-1">Route</dt>
                <dd class="text-sm text-gray-900 font-mono bg-gray-100 px-2 py-1 rounded">{{ activityLog.route_name }}</dd>
              </div>
              <div>
                <dt class="text-xs font-medium text-gray-500 mb-1">Oluşturulma</dt>
                <dd class="text-sm text-gray-900">{{ formatDateTime(activityLog.created_at) }}</dd>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  activityLog: {
    type: Object,
    required: true
  }
})

const formatDateTime = (dateString) => {
  if (!dateString) return '-'
  return new Date(dateString).toLocaleString('tr-TR', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit'
  })
}

const getUserInitials = (name) => {
  if (!name) return '?'
  return name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase()
}

const getSeverityLabel = (severity) => {
  const labels = {
    info: 'Bilgi',
    warning: 'Uyarı',
    error: 'Hata',
    critical: 'Kritik'
  }
  return labels[severity] || severity
}

const getSeverityBadgeClass = (severity) => {
  const classes = {
    info: 'bg-blue-100 text-blue-700 border border-blue-300',
    warning: 'bg-yellow-100 text-yellow-700 border border-yellow-300',
    error: 'bg-red-100 text-red-700 border border-red-300',
    critical: 'bg-purple-100 text-purple-700 border border-purple-300'
  }
  return classes[severity] || 'bg-gray-100 text-gray-700 border border-gray-300'
}

const getActivityTypeLabel = (type) => {
  const labels = {
    created: 'Oluşturuldu',
    updated: 'Güncellendi',
    deleted: 'Silindi',
    viewed: 'Görüntülendi',
    logged_in: 'Giriş Yapıldı',
    logged_out: 'Çıkış Yapıldı',
    access_denied: 'Erişim Engellendi',
    custom: 'Özel'
  }
  return labels[type] || type
}

const getActivityTypeBadgeClass = (type) => {
  const classes = {
    created: 'bg-green-100 text-green-700 border border-green-300',
    updated: 'bg-blue-100 text-blue-700 border border-blue-300',
    deleted: 'bg-red-100 text-red-700 border border-red-300',
    viewed: 'bg-indigo-100 text-indigo-700 border border-indigo-300',
    logged_in: 'bg-emerald-100 text-emerald-700 border border-emerald-300',
    logged_out: 'bg-orange-100 text-orange-700 border border-orange-300',
    access_denied: 'bg-rose-100 text-rose-700 border border-rose-300',
    custom: 'bg-purple-100 text-purple-700 border border-purple-300'
  }
  return classes[type] || 'bg-gray-100 text-gray-700 border border-gray-300'
}
</script>
