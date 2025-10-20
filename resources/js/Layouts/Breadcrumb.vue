<template>
  <nav class="bg-gray-50 border-b border-gray-200" aria-label="Breadcrumb">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
      <div class="flex items-center space-x-2 py-3">
        <ol class="flex items-center space-x-2">
          <!-- Home -->
          <li>
            <Link
              :href="route('dashboard')"
              class="text-gray-500 hover:text-gray-700 transition-colors"
            >
              <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
              </svg>
              <span class="sr-only">Ana Sayfa</span>
            </Link>
          </li>

          <!-- Breadcrumb items -->
          <li v-for="(item, index) in items" :key="index" class="flex items-center">
            <!-- Separator -->
            <svg class="h-4 w-4 text-gray-400 mx-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
            </svg>

            <!-- Breadcrumb item -->
            <div class="flex items-center">
              <Link
                v-if="item.href && index < items.length - 1"
                :href="item.href"
                class="text-sm font-medium text-gray-500 hover:text-gray-700 transition-colors"
              >
                {{ item.label }}
              </Link>
              <span
                v-else
                class="text-sm font-medium text-gray-900"
                :aria-current="index === items.length - 1 ? 'page' : undefined"
              >
                {{ item.label }}
              </span>
            </div>
          </li>
        </ol>
      </div>
    </div>
  </nav>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'

defineProps({
  items: {
    type: Array,
    required: true,
    validator: (items) => {
      return items.every(item => 
        typeof item === 'object' && 
        typeof item.label === 'string'
      )
    }
  }
})
</script>