<template>
  <div class="flex items-center justify-between bg-white px-4 py-3 sm:px-6">
    <!-- Mobile pagination info -->
    <div class="flex flex-1 justify-between sm:hidden">
      <button
        @click="goToPrevious"
        :disabled="!pagination.prev_page_url"
        :class="[
          'relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700',
          pagination.prev_page_url 
            ? 'hover:bg-gray-50' 
            : 'cursor-not-allowed opacity-50'
        ]"
      >
        Önceki
      </button>
      <button
        @click="goToNext"
        :disabled="!pagination.next_page_url"
        :class="[
          'relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700',
          pagination.next_page_url 
            ? 'hover:bg-gray-50' 
            : 'cursor-not-allowed opacity-50'
        ]"
      >
        Sonraki
      </button>
    </div>

    <!-- Desktop pagination -->
    <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
      <!-- Results info -->
      <div>
        <p class="text-sm text-gray-700">
          <span class="font-medium">{{ pagination.from || 0 }}</span>
          -
          <span class="font-medium">{{ pagination.to || 0 }}</span>
          arası gösteriliyor, toplam
          <span class="font-medium">{{ pagination.total || 0 }}</span>
          sonuç
        </p>
      </div>

      <!-- Page navigation -->
      <div>
        <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
          <!-- Previous button -->
          <button
            @click="goToPrevious"
            :disabled="!pagination.prev_page_url"
            :class="[
              'relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 focus:z-20 focus:outline-offset-0',
              pagination.prev_page_url 
                ? 'hover:bg-gray-50 focus:bg-gray-50' 
                : 'cursor-not-allowed opacity-50'
            ]"
          >
            <span class="sr-only">Önceki</span>
            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
              <path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd" />
            </svg>
          </button>

          <!-- Page numbers -->
          <template v-for="(link, index) in paginationLinks" :key="index">
            <!-- Current page -->
            <button
              v-if="link.active"
              class="relative z-10 inline-flex items-center bg-blue-600 px-4 py-2 text-sm font-semibold text-white focus:z-20 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600"
              v-html="link.label"
            ></button>
            
            <!-- Other pages -->
            <button
              v-else-if="link.url && !isEllipsis(link.label)"
              @click="goToPage(link.url)"
              class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0"
              v-html="link.label"
            ></button>
            
            <!-- Ellipsis -->
            <span
              v-else-if="isEllipsis(link.label)"
              class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-700 ring-1 ring-inset ring-gray-300 focus:outline-offset-0"
            >
              ...
            </span>
          </template>

          <!-- Next button -->
          <button
            @click="goToNext"
            :disabled="!pagination.next_page_url"
            :class="[
              'relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 focus:z-20 focus:outline-offset-0',
              pagination.next_page_url 
                ? 'hover:bg-gray-50 focus:bg-gray-50' 
                : 'cursor-not-allowed opacity-50'
            ]"
          >
            <span class="sr-only">Sonraki</span>
            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
              <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
            </svg>
          </button>
        </nav>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { router } from '@inertiajs/vue3'

// Props
const props = defineProps({
  pagination: {
    type: Object,
    required: true,
    default: () => ({
      current_page: 1,
      last_page: 1,
      per_page: 15,
      total: 0,
      from: 0,
      to: 0,
      prev_page_url: null,
      next_page_url: null,
      links: []
    })
  }
})

// Emits
const emit = defineEmits(['page-changed'])

// Computed
const paginationLinks = computed(() => {
  if (!props.pagination.links) return []
  
  // Laravel'in pagination links'ini filtrele (ilk ve son Previous/Next linklerini çıkar)
  return props.pagination.links.slice(1, -1)
})

// Methods
const isEllipsis = (label) => {
  return label === '...' || label.includes('&hellip;')
}

const goToPage = (url) => {
  if (!url) return
  
  // URL'den sayfa numarasını çıkar
  const urlObj = new URL(url, window.location.origin)
  const page = urlObj.searchParams.get('page')
  
  // Emit page change event
  emit('page-changed', parseInt(page))
  
  // Navigate to the new page
  router.get(url, {}, {
    preserveState: true,
    preserveScroll: true,
    replace: true
  })
}

const goToPrevious = () => {
  if (props.pagination.prev_page_url) {
    goToPage(props.pagination.prev_page_url)
  }
}

const goToNext = () => {
  if (props.pagination.next_page_url) {
    goToPage(props.pagination.next_page_url)
  }
}
</script>