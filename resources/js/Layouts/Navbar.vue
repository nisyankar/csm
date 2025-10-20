<template>
  <header class="sticky top-0 z-40 bg-white shadow-sm border-b border-gray-200">
    <div class="mx-auto flex h-16 max-w-full items-center justify-between px-4 sm:px-6 lg:px-8">
      <!-- Left section: Mobile menu button and search -->
      <div class="flex items-center space-x-4">
        <!-- Mobile menu button -->
        <button @click="$emit('toggle-sidebar')"
          class="lg:hidden -ml-2 p-2 rounded-md text-gray-500 hover:bg-gray-100 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
          <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
          </svg>
        </button>

        <!-- Global Search -->
        <div class="hidden sm:block relative max-w-md">
          <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
              </svg>
            </div>
            <input v-model="searchQuery" @keydown.enter="performSearch" @focus="showSearchResults = true" type="text"
              placeholder="Çalışan, proje veya belge ara..."
              class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
          </div>

          <!-- Search Results Dropdown -->
          <transition enter-active-class="transition duration-200 ease-out"
            enter-from-class="transform scale-95 opacity-0" enter-to-class="transform scale-100 opacity-100"
            leave-active-class="transition duration-150 ease-in" leave-from-class="transform scale-100 opacity-100"
            leave-to-class="transform scale-95 opacity-0">
            <div v-show="showSearchResults && searchQuery"
              class="absolute top-full left-0 right-0 mt-1 bg-white rounded-lg shadow-lg ring-1 ring-black ring-opacity-5 z-50">
              <div class="p-2 max-h-96 overflow-y-auto">
                <div v-if="searchLoading" class="px-3 py-2 text-sm text-gray-500">
                  Aranıyor...
                </div>
                <div v-else-if="searchResults.length === 0" class="px-3 py-2 text-sm text-gray-500">
                  Sonuç bulunamadı
                </div>
                <div v-else class="space-y-1">
                  <Link v-for="result in searchResults" :key="`${result.type}-${result.id}`" :href="result.url"
                    class="flex items-center px-3 py-2 text-sm rounded-md hover:bg-gray-100 transition-colors"
                    @click="closeSearch">
                  <div class="flex-shrink-0 mr-3">
                    <component :is="getSearchIcon(result.type)" class="h-4 w-4 text-gray-400" />
                  </div>
                  <div class="flex-1 min-w-0">
                    <p class="font-medium text-gray-900 truncate">{{ result.title }}</p>
                    <p class="text-gray-500 truncate">{{ result.subtitle }}</p>
                  </div>
                  <div class="flex-shrink-0">
                    <span :class="[
                      'inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium',
                      getSearchBadgeClass(result.type)
                    ]">
                      {{ getSearchTypeLabel(result.type) }}
                    </span>
                  </div>
                  </Link>
                </div>
              </div>
            </div>
          </transition>
        </div>
      </div>

      <!-- Center section: Page title for mobile -->
      <div class="flex-1 lg:hidden">
        <h1 class="text-lg font-semibold text-gray-900 text-center truncate">
          {{ currentPageTitle }}
        </h1>
      </div>

      <!-- Right section: Notifications and user menu -->
      <div class="flex items-center space-x-4">
        <!-- Quick Actions (Desktop) -->
        <div class="hidden lg:flex items-center space-x-2">
          <!-- QR Scanner Button -->
          <Link :href="route('dashboard')"
            class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
          <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 3.75 9.375v-4.5ZM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 0 1-1.125-1.125v-4.5ZM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 13.5 9.375v-4.5Z" />
          </svg>
          QR Tara
          </Link>
        </div>

        <!-- Notifications -->
        <div class="relative">
          <button @click="toggleNotifications"
            class="p-2 rounded-full text-gray-500 hover:bg-gray-100 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
            </svg>
            <!-- Notification badge -->
            <span v-if="unreadNotificationsCount > 0"
              class="absolute -top-1 -right-1 h-5 w-5 rounded-full bg-red-500 flex items-center justify-center">
              <span class="text-xs font-medium text-white">
                {{ unreadNotificationsCount > 9 ? '9+' : unreadNotificationsCount }}
              </span>
            </span>
          </button>

          <!-- Notifications Dropdown -->
          <div v-show="showNotifications"
            class="absolute right-0 mt-2 w-80 bg-white rounded-md shadow-lg py-1 z-50 ring-1 ring-black ring-opacity-5">
            <div class="px-4 py-2 text-sm font-medium text-gray-900 border-b">Bildirimler</div>
            <div class="px-4 py-2 text-sm text-gray-500">Henüz bildirim yok</div>
          </div>
        </div>

        <!-- User Menu -->
        <div class="relative">
          <button @click="toggleUserMenu"
            class="flex items-center space-x-3 p-1.5 rounded-full focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors">
            <!-- User Avatar with Role-based Color -->
            <div :class="[
              'h-8 w-8 rounded-full flex items-center justify-center',
              getUserAvatarColor()
            ]">
              <span class="text-sm font-medium text-white">
                {{ user?.name?.charAt(0).toUpperCase() || 'U' }}
              </span>
            </div>
            <div class="hidden lg:block text-left">
              <p class="text-sm font-medium text-gray-900">{{ user?.name || 'Kullanıcı' }}</p>
              <div class="flex items-center space-x-1">
                <span class="text-xs text-gray-500">{{ getUserRoleDisplay() }}</span>
              </div>
            </div>
            <svg class="hidden lg:block h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
            </svg>
          </button>

          <!-- User Dropdown -->
          <div v-show="showUserMenu"
            class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50 ring-1 ring-black ring-opacity-5">
            <Link :href="route('profile.show')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
            <svg class="w-4 h-4 mr-2 inline" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
            </svg>
            Profil
            </Link>
            <Link v-if="user?.roles?.some(role => ['admin', 'system_admin'].includes(role.name))" :href="route('system.settings')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
            <svg class="w-4 h-4 mr-2 inline" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a6.759 6.759 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />
            </svg>
            Sistem Ayarları
            </Link>
            <hr class="my-1">
            <Link :href="route('logout')" method="post" as="button"
              class="w-full text-left block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
            <svg class="w-4 h-4 mr-2 inline" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
            </svg>
            Çıkış Yap
            </Link>
          </div>
        </div>
      </div>
    </div>

    <!-- Mobile Search (when needed) -->
    <div v-show="showMobileSearch" class="lg:hidden border-t border-gray-200 px-4 py-3">
      <div class="relative">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
          <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
          </svg>
        </div>
        <input v-model="searchQuery" @keydown.enter="performSearch" type="text" placeholder="Ara..."
          class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
      </div>
    </div>
  </header>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted, h } from 'vue'
import { usePage, Link } from '@inertiajs/vue3'
import { debounce } from 'lodash'

defineEmits(['toggle-sidebar'])

const page = usePage()

// State
const searchQuery = ref('')
const searchResults = ref([])
const searchLoading = ref(false)
const showSearchResults = ref(false)
const showNotifications = ref(false)
const showUserMenu = ref(false)
const showMobileSearch = ref(false)

// Computed
const user = computed(() => page.props.auth?.user)
const unreadNotificationsCount = computed(() => page.props.notifications?.unread_count || 0)

const currentPageTitle = computed(() => {
  const routeName = page.props.routeName
  const titles = {
    'dashboard': 'Dashboard',
    'employees.index': 'Çalışanlar',
    'employees.create': 'Yeni Çalışan',
    'employees.show': 'Çalışan Detayı',
    'projects.index': 'Projeler',
    'projects.create': 'Yeni Proje',
    'projects.show': 'Proje Detayı',
    'timesheets.index': 'Puantajlar',
    'qr.scanner': 'QR Tarayıcı',
    'reports.index': 'Raporlar',
    'leave-requests.index': 'İzin Talepleri',
    'leave-management.parameters.index': 'İzin Parametreleri',
    'leave-management.types.index': 'İzin Türleri',
    'leave-management.calculations.index': 'İzin Hesaplamaları',
    'leave-management.reports.index': 'İzin Raporları',
    'system.settings': 'Sistem Ayarları',
    'system.users': 'Kullanıcı Yönetimi'
  }
  return titles[routeName] || 'SPT İnşaat'
})

// İzin yönetimi erişim kontrolü
const canAccessLeaveManagement = computed(() => {
  if (!user.value) return false
  
  const userRoles = user.value.roles?.map(role => role.name) || []
  return userRoles.some(role => ['admin', 'system_admin', 'hr'].includes(role))
})

// Methods
const toggleNotifications = () => {
  showNotifications.value = !showNotifications.value
  showUserMenu.value = false
}

const toggleUserMenu = () => {
  showUserMenu.value = !showUserMenu.value
  showNotifications.value = false
}

const closeSearch = () => {
  showSearchResults.value = false
  searchQuery.value = ''
}

const performSearch = async () => {
  if (!searchQuery.value.trim()) return

  searchLoading.value = true
  try {
    const response = await axios.get(route('api.search'), {
      params: { q: searchQuery.value }
    })
    searchResults.value = response.data.results || []
  } catch (error) {
    console.error('Search error:', error)
    searchResults.value = []
  } finally {
    searchLoading.value = false
  }
}

const debouncedSearch = debounce(performSearch, 300)

const getSearchIcon = (type) => {
  const icons = {
    employee: () => h('svg', {
      fill: 'none',
      viewBox: '0 0 24 24',
      'stroke-width': '1.5',
      stroke: 'currentColor',
      class: 'h-4 w-4'
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
      stroke: 'currentColor',
      class: 'h-4 w-4'
    }, [
      h('path', {
        'stroke-linecap': 'round',
        'stroke-linejoin': 'round',
        d: 'M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21'
      })
    ]),
    document: () => h('svg', {
      fill: 'none',
      viewBox: '0 0 24 24',
      'stroke-width': '1.5',
      stroke: 'currentColor',
      class: 'h-4 w-4'
    }, [
      h('path', {
        'stroke-linecap': 'round',
        'stroke-linejoin': 'round',
        d: 'M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z'
      })
    ])
  }
  return icons[type] || icons.employee
}

const getSearchBadgeClass = (type) => {
  const classes = {
    employee: 'bg-blue-100 text-blue-800',
    project: 'bg-green-100 text-green-800',
    document: 'bg-purple-100 text-purple-800',
    timesheet: 'bg-orange-100 text-orange-800',
    leave: 'bg-yellow-100 text-yellow-800'
  }
  return classes[type] || 'bg-gray-100 text-gray-800'
}

const getSearchTypeLabel = (type) => {
  const labels = {
    employee: 'Çalışan',
    project: 'Proje',
    document: 'Belge',
    timesheet: 'Puantaj',
    leave: 'İzin'
  }
  return labels[type] || 'Öğe'
}

const getUserAvatarColor = () => {
  if (!user.value?.employee?.category) {
    return 'bg-blue-600'
  }

  const categoryColors = {
    worker: 'bg-blue-600',
    foreman: 'bg-green-600',
    engineer: 'bg-purple-600',
    manager: 'bg-orange-600',
    system_admin: 'bg-red-600'
  }

  return categoryColors[user.value.employee.category] || 'bg-blue-600'
}

const getUserRoleDisplay = () => {
  if (user.value?.employee?.category) {
    const categoryLabels = {
      worker: 'İşçi',
      foreman: 'Forman',
      engineer: 'Mühendis',
      manager: 'Proje Yöneticisi',
      system_admin: 'Sistem Yöneticisi'
    }
    return categoryLabels[user.value.employee.category] || 'Personel'
  }
  return user.value?.role_display || user.value?.role || 'Kullanıcı'
}

const handleClickOutside = (event) => {
  // Dropdown'ların dışında tıklama kontrolü
  const target = event.target

  // User menu dropdown kontrol
  if (!target.closest('.relative') && showUserMenu.value) {
    showUserMenu.value = false
  }

  // Notifications dropdown kontrol
  if (!target.closest('.relative') && showNotifications.value) {
    showNotifications.value = false
  }

  // Search dropdown kontrol
  if (!target.closest('.relative') && showSearchResults.value) {
    showSearchResults.value = false
  }
}

const handleEscape = (event) => {
  if (event.key === 'Escape') {
    showSearchResults.value = false
    showNotifications.value = false
    showUserMenu.value = false
  }
}

// Watch search query
watch(searchQuery, (newValue) => {
  if (newValue.trim()) {
    showSearchResults.value = true
    debouncedSearch()
  } else {
    searchResults.value = []
    showSearchResults.value = false
  }
})

// Lifecycle
onMounted(() => {
  document.addEventListener('click', handleClickOutside)
  document.addEventListener('keydown', handleEscape)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
  document.removeEventListener('keydown', handleEscape)
})
</script>