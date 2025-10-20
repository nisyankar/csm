<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Mobile sidebar overlay -->
    <div 
      v-show="sidebarOpen" 
      class="fixed inset-0 z-40 lg:hidden"
      @click="sidebarOpen = false"
    >
      <div class="fixed inset-0 bg-gray-600 bg-opacity-75"></div>
    </div>

    <!-- Mobile sidebar -->
    <transition
      enter-active-class="transition-transform duration-300 ease-out"
      enter-from-class="-translate-x-full"
      enter-to-class="translate-x-0"
      leave-active-class="transition-transform duration-300 ease-in"
      leave-from-class="translate-x-0"
      leave-to-class="-translate-x-full"
    >
      <div 
        v-show="sidebarOpen"
        class="fixed inset-y-0 left-0 z-50 w-64 lg:hidden"
      >
        <Sidebar @close="sidebarOpen = false" />
      </div>
    </transition>

    <!-- Desktop sidebar - HER ZAMAN GÖSTER -->
    <div class="hidden lg:fixed lg:inset-y-0 lg:flex lg:w-64 lg:flex-col">
      <Sidebar />
    </div>

    <!-- Main content area - FULL WIDTH: sidebar boşluğu bırak ama içerik tam genişlik -->
    <div class="flex flex-1 flex-col lg:pl-64">
      <!-- Top navbar -->
      <Navbar @toggle-sidebar="sidebarOpen = !sidebarOpen" />

      <!-- Page header with breadcrumb - NORMAL HEADER -->
      <div v-if="$slots.header" class="bg-white shadow-sm border-b border-gray-200">
        <div :class="fullWidth ? 'w-full px-4 sm:px-6 lg:px-8' : 'mx-auto max-w-7xl px-4 sm:px-6 lg:px-8'">
          <div class="flex h-16 items-center justify-between">
            <slot name="header" />
          </div>
        </div>
      </div>

      <!-- Full Width Header slot - TAM GENİŞLİK HEADER -->
      <div v-if="$slots.fullWidthHeader" class="w-full">
        <slot name="fullWidthHeader" />
      </div>

      <!-- Breadcrumb - SADECE NORMAL LAYOUT İÇİN -->
      <Breadcrumb 
        v-if="breadcrumbs && breadcrumbs.length > 0 && !fullWidth" 
        :items="breadcrumbs" 
      />

      <!-- Main content -->
      <main class="flex-1">
        <!-- Page alerts/notifications -->
        <div v-if="$page.props.flash?.message" :class="fullWidth ? 'w-full px-4 sm:px-6 lg:px-8 pt-6' : 'mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 pt-6'">
          <Alert 
            :type="$page.props.flash.type || 'success'" 
            :message="$page.props.flash.message"
            :dismissible="true"
          />
        </div>

        <!-- Page content -->
        <div :class="fullWidth ? 'w-full' : 'mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-6'">
          <slot />
        </div>
      </main>

      <!-- Footer -->
      <Footer v-if="!fullWidth" />
    </div>

    <!-- Global modals -->
    <teleport to="body">
      <!-- Global notification stack -->
      <div 
        class="fixed top-0 right-0 z-50 p-4 space-y-4"
        style="max-height: 100vh; overflow-y: auto;"
      >
        <transition-group
          enter-active-class="transform ease-out duration-300 transition"
          enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
          enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
          leave-active-class="transition ease-in duration-100"
          leave-from-class="opacity-100"
          leave-to-class="opacity-0"
        >
          <Toast
            v-for="notification in notifications"
            :key="notification.id"
            :type="notification.type"
            :title="notification.title"
            :message="notification.message"
            :dismissible="true"
            @dismiss="removeNotification(notification.id)"
          />
        </transition-group>
      </div>

      <!-- Global loading overlay -->
      <div 
        v-show="isLoading"
        class="fixed inset-0 z-50 bg-gray-900 bg-opacity-50 flex items-center justify-center"
      >
        <div class="bg-white rounded-lg p-6 shadow-xl">
          <div class="flex items-center space-x-3">
            <Spinner class="w-6 h-6 text-blue-600" />
            <span class="text-gray-900 font-medium">{{ loadingMessage || 'Yükleniyor...' }}</span>
          </div>
        </div>
      </div>
    </teleport>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onUnmounted } from 'vue'
import { usePage } from '@inertiajs/vue3'
import Sidebar from './Sidebar.vue'
import Navbar from './Navbar.vue'
import Breadcrumb from './Breadcrumb.vue'
import Footer from './Footer.vue'
import Alert from '@/Components/UI/Alert.vue'
import Toast from '@/Components/UI/Toast.vue'
import Spinner from '@/Components/UI/Spinner.vue'

// Props
const props = defineProps({
  breadcrumbs: {
    type: Array,
    default: () => []
  },
  title: {
    type: String,
    default: 'SPT - İnşaat Puantaj Sistemi'
  },
  fullWidth: {
    type: Boolean,
    default: false
  }
})

// Reactive state
const sidebarOpen = ref(false)
const isLoading = ref(false)
const loadingMessage = ref('')
const notifications = reactive([])

// Page data
const page = usePage()

// Computed
const user = computed(() => page.props.auth?.user)

// Methods
const removeNotification = (id) => {
  const index = notifications.findIndex(n => n.id === id)
  if (index > -1) {
    notifications.splice(index, 1)
  }
}

const addNotification = (notification) => {
  const id = Date.now() + Math.random()
  notifications.push({
    id,
    ...notification
  })
  
  // Auto remove after 5 seconds
  setTimeout(() => {
    removeNotification(id)
  }, 5000)
}

const showLoading = (message = 'Yükleniyor...') => {
  isLoading.value = true
  loadingMessage.value = message
}

const hideLoading = () => {
  isLoading.value = false
  loadingMessage.value = ''
}

// Global event listeners
const handleKeydown = (e) => {
  // ESC key to close sidebar
  if (e.key === 'Escape' && sidebarOpen.value) {
    sidebarOpen.value = false
  }
}

// Lifecycle
onMounted(() => {
  document.addEventListener('keydown', handleKeydown)
  
  // Set page title
  if (props.title) {
    document.title = props.title
  }
  
  // Listen for global events
  window.addEventListener('spt:notification', (e) => {
    addNotification(e.detail)
  })
  
  window.addEventListener('spt:loading:show', (e) => {
    showLoading(e.detail?.message)
  })
  
  window.addEventListener('spt:loading:hide', () => {
    hideLoading()
  })
})

onUnmounted(() => {
  document.removeEventListener('keydown', handleKeydown)
  window.removeEventListener('spt:notification', addNotification)
  window.removeEventListener('spt:loading:show', showLoading)
  window.removeEventListener('spt:loading:hide', hideLoading)
})

// Provide methods globally
defineExpose({
  addNotification,
  showLoading,
  hideLoading
})
</script>

<style scoped>
/* Custom scrollbar for mobile sidebar */
.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}

.custom-scrollbar::-webkit-scrollbar-track {
  background-color: #f3f4f6;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
  background-color: #9ca3af;
  border-radius: 9999px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background-color: #6b7280;
}
</style>