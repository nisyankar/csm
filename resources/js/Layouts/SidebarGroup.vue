<template>
  <div class="space-y-1">
    <!-- Group Header -->
    <button
      @click="toggleExpanded"
      :class="[
        'group w-full flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-all duration-200',
        hasActiveItem
          ? 'bg-gray-700 text-white'
          : 'text-gray-300 hover:bg-gray-700 hover:text-white'
      ]"
    >
      <!-- Icon -->
      <div :class="[
        'mr-3 flex-shrink-0 h-5 w-5 transition-colors',
        hasActiveItem ? 'text-white' : 'text-gray-400 group-hover:text-white'
      ]">
        <component :is="iconComponent" />
      </div>

      <!-- Label -->
      <span class="flex-1 text-left truncate">{{ label }}</span>

      <!-- Expand/Collapse Arrow -->
      <svg
        :class="[
          'ml-2 h-4 w-4 transition-transform duration-200',
          expanded ? 'rotate-90' : '',
          hasActiveItem ? 'text-white' : 'text-gray-400 group-hover:text-white'
        ]"
        fill="none"
        viewBox="0 0 24 24"
        stroke-width="1.5"
        stroke="currentColor"
      >
        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
      </svg>
    </button>

    <!-- Submenu Items -->
    <transition
      enter-active-class="transition-all duration-200 ease-out"
      enter-from-class="opacity-0 -translate-y-2"
      enter-to-class="opacity-100 translate-y-0"
      leave-active-class="transition-all duration-150 ease-in"
      leave-from-class="opacity-100 translate-y-0"
      leave-to-class="opacity-0 -translate-y-2"
    >
      <div v-show="expanded" class="ml-6 space-y-1 border-l-2 border-gray-700 pl-4">
        <Link
          v-for="item in items"
          :key="item.href"
          :href="item.href"
          :class="[
            'group flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-all duration-200 relative',
            item.active
              ? 'bg-blue-600 text-white shadow-lg'
              : 'text-gray-300 hover:bg-gray-700 hover:text-white'
          ]"
        >
          <!-- Active indicator -->
          <div
            v-if="item.active"
            class="absolute -left-6 top-1/2 -translate-y-1/2 w-1 h-6 bg-blue-400 rounded-r"
          ></div>

          <!-- Item Label -->
          <span class="flex-1 truncate">{{ item.label }}</span>

          <!-- Badge -->
          <span
            v-if="item.badge"
            :class="[
              'ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium',
              item.active
                ? 'bg-blue-500 text-white'
                : 'bg-red-100 text-red-800'
            ]"
          >
            {{ item.badge }}
          </span>

          <!-- Mobile indicator -->
          <span
            v-if="item.mobile"
            class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 lg:hidden"
          >
            📱
          </span>
        </Link>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, h } from 'vue'
import { Link } from '@inertiajs/vue3'

const props = defineProps({
  label: {
    type: String,
    required: true
  },
  icon: {
    type: String,
    required: true
  },
  items: {
    type: Array,
    required: true
  },
  defaultExpanded: {
    type: Boolean,
    default: null
  }
})

const expanded = ref(false)

// Check if any item is active
const hasActiveItem = computed(() => {
  return props.items.some(item => item.active)
})

// Auto-expand if has active item or defaultExpanded is true
onMounted(() => {
  if (props.defaultExpanded !== null) {
    expanded.value = props.defaultExpanded
  } else {
    expanded.value = hasActiveItem.value
  }
})

const toggleExpanded = () => {
  expanded.value = !expanded.value
}

// Icon mapping - Using h() function instead of JSX
const iconComponents = {
  users: () => h('svg', {
    fill: 'none',
    viewBox: '0 0 24 24',
    'stroke-width': '1.5',
    stroke: 'currentColor',
    class: 'h-5 w-5'
  }, [
    h('path', {
      'stroke-linecap': 'round',
      'stroke-linejoin': 'round',
      d: 'M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z'
    })
  ]),
  clock: () => h('svg', {
    fill: 'none',
    viewBox: '0 0 24 24',
    'stroke-width': '1.5',
    stroke: 'currentColor',
    class: 'h-5 w-5'
  }, [
    h('path', {
      'stroke-linecap': 'round',
      'stroke-linejoin': 'round',
      d: 'M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'
    })
  ]),
  'building-office': () => h('svg', {
    fill: 'none',
    viewBox: '0 0 24 24',
    'stroke-width': '1.5',
    stroke: 'currentColor',
    class: 'h-5 w-5'
  }, [
    h('path', {
      'stroke-linecap': 'round',
      'stroke-linejoin': 'round',
      d: 'M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21'
    })
  ]),
  'calendar-days': () => h('svg', {
    fill: 'none',
    viewBox: '0 0 24 24',
    'stroke-width': '1.5',
    stroke: 'currentColor',
    class: 'h-5 w-5'
  }, [
    h('path', {
      'stroke-linecap': 'round',
      'stroke-linejoin': 'round',
      d: 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5'
    })
  ]),
  'document-text': () => h('svg', {
    fill: 'none',
    viewBox: '0 0 24 24',
    'stroke-width': '1.5',
    stroke: 'currentColor',
    class: 'h-5 w-5'
  }, [
    h('path', {
      'stroke-linecap': 'round',
      'stroke-linejoin': 'round',
      d: 'M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z'
    })
  ]),
  'chart-bar': () => h('svg', {
    fill: 'none',
    viewBox: '0 0 24 24',
    'stroke-width': '1.5',
    stroke: 'currentColor',
    class: 'h-5 w-5'
  }, [
    h('path', {
      'stroke-linecap': 'round',
      'stroke-linejoin': 'round',
      d: 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z'
    })
  ])
}

const iconComponent = computed(() => {
  return iconComponents[props.icon] || iconComponents.users
})
</script>