<template>
  <div class="bg-white shadow rounded-lg">
    <div class="px-6 py-5 border-b border-gray-200">
      <div class="flex items-center justify-between">
        <h3 class="text-lg leading-6 font-medium text-gray-900">
          {{ title }}
        </h3>
        <slot name="header-action"></slot>
      </div>
    </div>
    <div class="px-6 py-5">
      <div v-if="loading" class="text-center py-8">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
        <p class="mt-2 text-sm text-gray-500">Yükleniyor...</p>
      </div>

      <div v-else-if="activities && activities.length > 0" class="flow-root">
        <ul class="-mb-8">
          <li v-for="(activity, activityIdx) in activities" :key="activity.id || activityIdx">
            <div class="relative pb-8">
              <span
                v-if="activityIdx !== activities.length - 1"
                class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200"
                aria-hidden="true"
              ></span>
              <div class="relative flex space-x-3">
                <div>
                  <span :class="[
                    'h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white',
                    getActivityColor(activity)
                  ]">
                    <component :is="getActivityIcon(activity)" class="h-5 w-5 text-white" />
                  </span>
                </div>
                <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                  <div>
                    <p class="text-sm text-gray-900">{{ activity.message || activity.description }}</p>
                    <p v-if="activity.details" class="mt-0.5 text-xs text-gray-500">{{ activity.details }}</p>
                  </div>
                  <div class="text-right text-sm whitespace-nowrap text-gray-500">
                    <time :datetime="activity.created_at">{{ activity.time || formatTime(activity.created_at) }}</time>
                    <div v-if="activity.status" :class="[
                      'mt-1 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium',
                      getStatusClass(activity.status)
                    ]">
                      {{ getStatusLabel(activity.status) }}
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </li>
        </ul>
      </div>

      <div v-else class="text-center py-8">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
        </svg>
        <p class="mt-2 text-sm text-gray-500">{{ emptyMessage }}</p>
      </div>
    </div>

    <div v-if="viewAllLink" class="bg-gray-50 px-6 py-3 border-t border-gray-200">
      <Link :href="viewAllLink.href" class="text-sm font-medium text-blue-700 hover:text-blue-900 transition-colors">
        {{ viewAllLink.text || 'Tümünü görüntüle' }} →
      </Link>
    </div>
  </div>
</template>

<script setup>
import { h } from 'vue'
import { Link } from '@inertiajs/vue3'

const props = defineProps({
  title: {
    type: String,
    required: true
  },
  activities: {
    type: Array,
    default: () => []
  },
  loading: {
    type: Boolean,
    default: false
  },
  emptyMessage: {
    type: String,
    default: 'Henüz aktivite bulunmuyor.'
  },
  viewAllLink: {
    type: Object,
    default: null
  }
})

const getActivityColor = (activity) => {
  const typeColors = {
    timesheet: 'bg-blue-500',
    leave: 'bg-purple-500',
    success: 'bg-green-500',
    warning: 'bg-yellow-500',
    error: 'bg-red-500',
    info: 'bg-blue-500',
    project: 'bg-indigo-500',
    employee: 'bg-green-500',
    approval: 'bg-yellow-500'
  }

  return typeColors[activity.type] || 'bg-gray-500'
}

const getActivityIcon = (activity) => {
  const iconMap = {
    timesheet: () => h('svg', {
      fill: 'none',
      viewBox: '0 0 24 24',
      'stroke-width': '1.5',
      stroke: 'currentColor'
    }, [
      h('path', {
        'stroke-linecap': 'round',
        'stroke-linejoin': 'round',
        d: 'M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'
      })
    ]),
    leave: () => h('svg', {
      fill: 'none',
      viewBox: '0 0 24 24',
      'stroke-width': '1.5',
      stroke: 'currentColor'
    }, [
      h('path', {
        'stroke-linecap': 'round',
        'stroke-linejoin': 'round',
        d: 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5'
      })
    ]),
    success: () => h('svg', {
      fill: 'none',
      viewBox: '0 0 24 24',
      'stroke-width': '1.5',
      stroke: 'currentColor'
    }, [
      h('path', {
        'stroke-linecap': 'round',
        'stroke-linejoin': 'round',
        d: 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z'
      })
    ]),
    warning: () => h('svg', {
      fill: 'none',
      viewBox: '0 0 24 24',
      'stroke-width': '1.5',
      stroke: 'currentColor'
    }, [
      h('path', {
        'stroke-linecap': 'round',
        'stroke-linejoin': 'round',
        d: 'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z'
      })
    ]),
    employee: () => h('svg', {
      fill: 'none',
      viewBox: '0 0 24 24',
      'stroke-width': '1.5',
      stroke: 'currentColor'
    }, [
      h('path', {
        'stroke-linecap': 'round',
        'stroke-linejoin': 'round',
        d: 'M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z'
      })
    ]),
    project: () => h('svg', {
      fill: 'none',
      viewBox: '0 0 24 24',
      'stroke-width': '1.5',
      stroke: 'currentColor'
    }, [
      h('path', {
        'stroke-linecap': 'round',
        'stroke-linejoin': 'round',
        d: 'M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21'
      })
    ])
  }

  return iconMap[activity.type] || iconMap.success
}

const getStatusClass = (status) => {
  const statusClasses = {
    pending: 'bg-yellow-100 text-yellow-800',
    approved: 'bg-green-100 text-green-800',
    rejected: 'bg-red-100 text-red-800',
    submitted: 'bg-blue-100 text-blue-800',
    draft: 'bg-gray-100 text-gray-800',
    completed: 'bg-green-100 text-green-800',
    in_progress: 'bg-blue-100 text-blue-800',
    cancelled: 'bg-red-100 text-red-800'
  }

  return statusClasses[status] || statusClasses.draft
}

const getStatusLabel = (status) => {
  const labels = {
    pending: 'Beklemede',
    approved: 'Onaylandı',
    rejected: 'Reddedildi',
    submitted: 'Gönderildi',
    draft: 'Taslak',
    completed: 'Tamamlandı',
    in_progress: 'Devam Ediyor',
    cancelled: 'İptal Edildi'
  }

  return labels[status] || status
}

const formatTime = (dateString) => {
  if (!dateString) return ''

  const date = new Date(dateString)
  const now = new Date()
  const diff = Math.floor((now - date) / 1000) // seconds

  if (diff < 60) return 'Az önce'
  if (diff < 3600) return `${Math.floor(diff / 60)} dakika önce`
  if (diff < 86400) return `${Math.floor(diff / 3600)} saat önce`
  if (diff < 604800) return `${Math.floor(diff / 86400)} gün önce`

  return date.toLocaleDateString('tr-TR')
}
</script>
