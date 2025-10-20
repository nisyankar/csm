<template>
  <div class="absolute right-0 top-full mt-2 w-96 bg-white rounded-lg shadow-lg ring-1 ring-black ring-opacity-5 z-50">
    <!-- Header -->
    <div class="flex items-center justify-between p-4 border-b border-gray-200">
      <h3 class="text-lg font-semibold text-gray-900">Bildirimler</h3>
      <div class="flex items-center space-x-2">
        <button
          v-if="unreadCount > 0"
          @click="markAllAsRead"
          class="text-sm text-blue-600 hover:text-blue-800 font-medium"
        >
          Tümünü Okundu İşaretle
        </button>
        <button
          @click="$emit('close')"
          class="p-1 rounded-full text-gray-400 hover:text-gray-600 hover:bg-gray-100"
        >
          <XMarkIcon class="h-5 w-5" />
        </button>
      </div>
    </div>

    <!-- Notifications List -->
    <div class="max-h-96 overflow-y-auto">
      <div v-if="loading" class="p-4">
        <div class="animate-pulse space-y-3">
          <div v-for="i in 3" :key="i" class="flex items-start space-x-3">
            <div class="w-8 h-8 bg-gray-200 rounded-full"></div>
            <div class="flex-1 space-y-2">
              <div class="h-4 bg-gray-200 rounded w-3/4"></div>
              <div class="h-3 bg-gray-200 rounded w-1/2"></div>
            </div>
          </div>
        </div>
      </div>

      <div v-else-if="notifications.length === 0" class="p-8 text-center">
        <BellIcon class="mx-auto h-12 w-12 text-gray-300" />
        <h3 class="mt-2 text-sm font-medium text-gray-900">Bildirim yok</h3>
        <p class="mt-1 text-sm text-gray-500">Henüz hiç bildiriminiz bulunmuyor.</p>
      </div>

      <div v-else class="divide-y divide-gray-100">
        <div
          v-for="notification in notifications"
          :key="notification.id"
          :class="[
            'p-4 hover:bg-gray-50 transition-colors cursor-pointer',
            !notification.read_at ? 'bg-blue-50' : ''
          ]"
          @click="handleNotificationClick(notification)"
        >
          <div class="flex items-start space-x-3">
            <!-- Icon -->
            <div :class="[
              'flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center',
              getNotificationIconClass(notification.type)
            ]">
              <component :is="getNotificationIcon(notification.type)" class="h-4 w-4" />
            </div>

            <!-- Content -->
            <div class="flex-1 min-w-0">
              <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-gray-900 truncate">
                  {{ notification.data?.title || notification.data?.subject || 'Bildirim' }}
                </p>
                <div class="flex items-center space-x-2">
                  <span class="text-xs text-gray-500">
                    {{ formatTime(notification.created_at) }}
                  </span>
                  <div
                    v-if="!notification.read_at"
                    class="w-2 h-2 bg-blue-500 rounded-full"
                  ></div>
                </div>
              </div>
              <p class="mt-1 text-sm text-gray-600 line-clamp-2">
                {{ notification.data?.message || notification.data?.body || 'Bildirim içeriği' }}
              </p>
              
              <!-- Action buttons for certain notification types -->
              <div
                v-if="hasActions(notification) && !notification.read_at"
                class="mt-2 flex space-x-2"
              >
                <Button
                  size="xs"
                  variant="outline"
                  @click.stop="handleNotificationAction(notification, 'approve')"
                  class="text-green-600 border-green-200 hover:bg-green-50"
                >
                  Onayla
                </Button>
                <Button
                  size="xs"
                  variant="outline"
                  @click.stop="handleNotificationAction(notification, 'reject')"
                  class="text-red-600 border-red-200 hover:bg-red-50"
                >
                  Reddet
                </Button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Footer -->
    <div class="border-t border-gray-200 p-3">
      <Button
        variant="link"
        size="sm"
        :href="safeRoute('notifications.index', 'dashboard')"
        class="w-full justify-center text-blue-600 hover:text-blue-800"
        @click="$emit('close')"
      >
        Tüm Bildirimleri Görüntüle
      </Button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import { formatDistanceToNow } from 'date-fns'
import { tr } from 'date-fns/locale'
import Button from '@/Components/UI/Button.vue'

// Manual SVG icons instead of Heroicons for now
const XMarkIcon = {
  template: `
    <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
    </svg>
  `
}

const BellIcon = {
  template: `
    <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
    </svg>
  `
}

const CalendarDaysIcon = {
  template: `
    <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
    </svg>
  `
}

const ClockIcon = {
  template: `
    <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
    </svg>
  `
}

const ExclamationTriangleIcon = {
  template: `
    <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
    </svg>
  `
}

const BuildingOfficeIcon = {
  template: `
    <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
    </svg>
  `
}

const InformationCircleIcon = {
  template: `
    <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
    </svg>
  `
}

const DocumentIcon = {
  template: `
    <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
    </svg>
  `
}

defineEmits(['close'])

// State
const notifications = ref([])
const loading = ref(true)

// Computed
const unreadCount = computed(() => {
  return notifications.value.filter(n => !n.read_at).length
})

// Methods
const safeRoute = (routeName, fallback = 'dashboard') => {
  try {
    return route(routeName)
  } catch (error) {
    console.warn(`Route '${routeName}' not found, using fallback`)
    return route(fallback)
  }
}

const loadNotifications = async () => {
  try {
    loading.value = true
    
    // Route kontrolü - notifications.recent route'unu kullan
    const response = await axios.get(route('notifications.recent'))
    
    if (response.data) {
      notifications.value = response.data.notifications || []
      // Unread count'u güncelle
      if (response.data.unread_count !== undefined) {
        // Navbar'daki badge'i güncellemek için event emit edebilirsiniz
        // veya store kullanıyorsanız store'u güncelleyin
      }
    }
  } catch (error) {
    console.error('Failed to load notifications:', error.message)
    
    // Fallback: Boş array ile devam et
    notifications.value = []
    
    // Eğer 404 hatası ise, alternatif route dene
    if (error.response?.status === 404) {
      try {
        const fallbackResponse = await axios.get('/notifications')
        // Ana notifications sayfasından data al
        if (fallbackResponse.data?.notifications) {
          notifications.value = fallbackResponse.data.notifications.data?.slice(0, 10) || []
        }
      } catch (fallbackError) {
        console.error('Fallback also failed:', fallbackError.message)
      }
    }
  } finally {
    loading.value = false
  }
}


const handleNotificationAction = async (notification, action) => {
  try {
    let endpoint = ''
    let method = 'POST'
    
    switch (action) {
      case 'markRead':
        endpoint = route('notifications.mark-read', { id: notification.id })
        break
      case 'markUnread':
        endpoint = route('notifications.mark-unread', { id: notification.id })
        break
      case 'delete':
        endpoint = route('notifications.destroy', { id: notification.id })
        method = 'DELETE'
        break
      case 'approve':
        // Bu timesheet approval için özel handling
        if (notification.data?.timesheet_id) {
          endpoint = route('timesheets.approve', { timesheet: notification.data.timesheet_id })
        }
        break
      case 'reject':
        // Bu timesheet approval için özel handling
        if (notification.data?.timesheet_id) {
          endpoint = route('timesheets.reject', { timesheet: notification.data.timesheet_id })
        }
        break
      default:
        return
    }
    
    if (endpoint) {
      if (method === 'DELETE') {
        await axios.delete(endpoint)
      } else {
        await axios.post(endpoint)
      }
      
      // Action başarılı olduysa notification'ı listeden güncelle veya kaldır
      if (action === 'delete') {
        notifications.value = notifications.value.filter(n => n.id !== notification.id)
      } else if (action === 'markRead') {
        const index = notifications.value.findIndex(n => n.id === notification.id)
        if (index !== -1) {
          notifications.value[index].read_at = new Date().toISOString()
        }
      } else if (action === 'markUnread') {
        const index = notifications.value.findIndex(n => n.id === notification.id)
        if (index !== -1) {
          notifications.value[index].read_at = null
        }
      }
      
      // Başarı mesajı (opsiyonel)
      // toast.success('İşlem başarıyla tamamlandı')
    }
  } catch (error) {
    console.error(`Failed to ${action} notification:`, error.message)
    // Hata mesajı (opsiyonel)
    // toast.error('İşlem başarısız oldu')
  }
}

// Mark all as read fonksiyonu


const markAllAsRead = async () => {
  try {
    const response = await fetch(route('notifications.mark-all-read'), {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      },
      credentials: 'same-origin'
    })
    
    if (response.ok) {
      notifications.value = notifications.value.map(n => ({
        ...n,
        read_at: new Date().toISOString()
      }))
      
      // Show success notification
      showToast('success', 'Tüm bildirimler okundu olarak işaretlendi')
    }
  } catch (error) {
    console.error('Error marking notifications as read:', error)
    showToast('error', 'Bildirimler güncellenirken hata oluştu')
  }
}

const handleNotificationClick = async (notification) => {
  // Mark as read if not already
  if (!notification.read_at) {
    try {
      const response = await fetch(route('notifications.mark-read', notification.id), {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        credentials: 'same-origin'
      })
      
      if (response.ok) {
        notification.read_at = new Date().toISOString()
      }
    } catch (error) {
      console.error('Error marking notification as read:', error)
    }
  }

  // Navigate to related page if URL exists
  if (notification.data?.url) {
    router.visit(notification.data.url)
  } else {
    // Default navigation based on notification type
    navigateByType(notification)
  }
}



const navigateByType = (notification) => {
  const type = notification.type
  
  // Helper function to safely get route
  const safeRoute = (routeName, fallback = 'dashboard') => {
    try {
      return route(routeName)
    } catch (error) {
      console.warn(`Route '${routeName}' not found, using fallback`)
      return route(fallback)
    }
  }
  
  switch (type) {
    case 'timesheet_approval':
    case 'timesheet_submitted':
      router.visit(safeRoute('timesheets.index'))
      break
    case 'leave_request':
    case 'leave_approved':
    case 'leave_rejected':
      router.visit(safeRoute('leave-requests.index'))
      break
    case 'project_assignment':
    case 'project_update':
      router.visit(safeRoute('projects.index'))
      break
    case 'document_expiry':
    case 'document_uploaded':
      router.visit(safeRoute('documents.index'))
      break
    default:
      router.visit(safeRoute('notifications.index'))
      break
  }
}

const hasActions = (notification) => {
  return ['timesheet_approval', 'leave_request'].includes(notification.type)
}

const getNotificationIcon = (type) => {
  const icons = {
    leave_request: CalendarDaysIcon,
    leave_approved: CalendarDaysIcon,
    leave_rejected: CalendarDaysIcon,
    timesheet_approval: ClockIcon,
    timesheet_submitted: ClockIcon,
    timesheet_approved: ClockIcon,
    timesheet_rejected: ClockIcon,
    document_expiry: ExclamationTriangleIcon,
    document_uploaded: DocumentIcon,
    project_assignment: BuildingOfficeIcon,
    project_update: BuildingOfficeIcon,
    system: InformationCircleIcon
  }
  return icons[type] || InformationCircleIcon
}

const getNotificationIconClass = (type) => {
  const classes = {
    leave_request: 'bg-blue-100 text-blue-600',
    leave_approved: 'bg-green-100 text-green-600',
    leave_rejected: 'bg-red-100 text-red-600',
    timesheet_approval: 'bg-yellow-100 text-yellow-600',
    timesheet_submitted: 'bg-blue-100 text-blue-600',
    timesheet_approved: 'bg-green-100 text-green-600',
    timesheet_rejected: 'bg-red-100 text-red-600',
    document_expiry: 'bg-orange-100 text-orange-600',
    document_uploaded: 'bg-purple-100 text-purple-600',
    project_assignment: 'bg-indigo-100 text-indigo-600',
    project_update: 'bg-indigo-100 text-indigo-600',
    system: 'bg-gray-100 text-gray-600'
  }
  return classes[type] || classes.system
}

const formatTime = (timestamp) => {
  try {
    return formatDistanceToNow(new Date(timestamp), {
      addSuffix: true,
      locale: tr
    })
  } catch (error) {
    return 'Bilinmiyor'
  }
}

const showToast = (type, message) => {
  // Dispatch custom event for toast notifications
  window.dispatchEvent(new CustomEvent('spt:notification', {
    detail: {
      type,
      message
    }
  }))
}

// Lifecycle
onMounted(() => {
  loadNotifications()
})
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* Custom scrollbar */
.overflow-y-auto::-webkit-scrollbar {
  width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
  background: #a8a8a8;
}

/* Animation for new notifications */
@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.notification-enter-active {
  animation: slideIn 0.3s ease-out;
}
</style>