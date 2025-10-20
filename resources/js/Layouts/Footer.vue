<template>
  <footer class="bg-white border-t border-gray-200 mt-auto">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-6">
      <div class="flex flex-col sm:flex-row items-center justify-between space-y-4 sm:space-y-0">
        <!-- Left side: Copyright and company info -->
        <div class="flex items-center space-x-4">
          <div class="text-sm text-gray-500">
            © {{ currentYear }} SPT - İnşaat Puantaj Sistemi. Tüm hakları saklıdır.
          </div>
        </div>

        <!-- Center: Quick stats (for desktop) -->
        <div class="hidden lg:flex items-center space-x-6 text-sm text-gray-500">
          <div class="flex items-center space-x-1">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
            </svg>
            <span>{{ totalEmployees }} Çalışan</span>
          </div>
          <div class="flex items-center space-x-1">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
            </svg>
            <span>{{ activeProjects }} Aktif Proje</span>
          </div>
          <div class="flex items-center space-x-1">
            <div class="w-2 h-2 bg-green-500 rounded-full"></div>
            <span>Sistem Aktif</span>
          </div>
        </div>

        <!-- Right side: Links and version -->
        <div class="flex items-center space-x-6">
          <!-- Help links -->
          <div class="flex items-center space-x-4 text-sm">
            <a
              href="#"
              class="text-gray-500 hover:text-gray-700 transition-colors"
              @click.prevent="openHelp"
            >
              Yardım
            </a>
            <a
              href="#"
              class="text-gray-500 hover:text-gray-700 transition-colors"
              @click.prevent="openSupport"
            >
              Destek
            </a>
            <span class="text-gray-300">•</span>
            <span class="text-gray-400 text-xs">
              v{{ appVersion }}
            </span>
          </div>

          <!-- Theme toggle -->
          <button
            @click="toggleTheme"
            class="p-2 rounded-md text-gray-500 hover:text-gray-700 hover:bg-gray-100 transition-colors"
            title="Tema Değiştir"
          >
            <svg
              v-if="!isDarkMode"
              class="h-4 w-4"
              fill="none"
              viewBox="0 0 24 24"
              stroke-width="1.5"
              stroke="currentColor"
            >
              <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" />
            </svg>
            <svg
              v-else
              class="h-4 w-4"
              fill="none"
              viewBox="0 0 24 24"
              stroke-width="1.5"
              stroke="currentColor"
            >
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-6.364-.386 1.591-1.591M3 12h2.25m.386-6.364 1.591 1.591M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
            </svg>
          </button>
        </div>
      </div>

      <!-- Mobile stats -->
      <div class="lg:hidden mt-4 pt-4 border-t border-gray-100">
        <div class="flex items-center justify-center space-x-6 text-sm text-gray-500">
          <div class="flex items-center space-x-1">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
            </svg>
            <span>{{ totalEmployees }}</span>
          </div>
          <div class="flex items-center space-x-1">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
            </svg>
            <span>{{ activeProjects }}</span>
          </div>
          <div class="flex items-center space-x-1">
            <div class="w-2 h-2 bg-green-500 rounded-full"></div>
            <span>Aktif</span>
          </div>
        </div>
      </div>
    </div>
  </footer>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { usePage } from '@inertiajs/vue3'

const page = usePage()

// State
const isDarkMode = ref(false)

// Computed
const currentYear = computed(() => new Date().getFullYear())
const appVersion = computed(() => page.props.app_version || '1.0.0')
const totalEmployees = computed(() => page.props.stats?.total_employees || 0)
const activeProjects = computed(() => page.props.stats?.active_projects || 0)

// Methods
const toggleTheme = () => {
  isDarkMode.value = !isDarkMode.value
  // TODO: Implement actual theme switching
  console.log('Theme toggled:', isDarkMode.value ? 'dark' : 'light')
}

const openHelp = () => {
  // TODO: Open help documentation
  window.open('#', '_blank')
}

const openSupport = () => {
  // TODO: Open support chat or contact form
  window.open('#', '_blank')
}

// Lifecycle
onMounted(() => {
  // Check for saved theme preference
  const savedTheme = localStorage.getItem('spt-theme')
  isDarkMode.value = savedTheme === 'dark'
})
</script>