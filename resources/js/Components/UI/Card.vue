<template>
  <div
    :class="[
      'bg-white overflow-hidden transition-all duration-200',
      shadowClasses[shadow],
      roundedClasses[rounded],
      borderClasses,
      hoverClasses,
      sizeClasses[size],
      cardClass
    ]"
  >
    <!-- Card Header -->
    <div
      v-if="title || subtitle || $slots.header || $slots.headerActions"
      :class="[
        'px-6 py-4 border-b border-gray-200',
        headerClass
      ]"
    >
      <slot name="header">
        <div class="flex items-center justify-between">
          <div class="flex-1 min-w-0">
            <!-- Title -->
            <h3
              v-if="title"
              :class="[
                'text-lg font-semibold leading-6 text-gray-900 truncate',
                titleClass
              ]"
            >
              {{ title }}
            </h3>
            
            <!-- Subtitle -->
            <p
              v-if="subtitle"
              :class="[
                'mt-1 text-sm text-gray-600 truncate',
                subtitleClass
              ]"
            >
              {{ subtitle }}
            </p>
          </div>
          
          <!-- Header Actions -->
          <div v-if="$slots.headerActions" class="flex items-center space-x-2 ml-4">
            <slot name="headerActions" />
          </div>
        </div>
      </slot>
    </div>

    <!-- Card Body -->
    <div
      :class="[
        'px-6 py-4',
        bodyClass,
        { 'overflow-y-auto': scrollable },
        { [`max-h-${maxHeight}`]: maxHeight }
      ]"
    >
      <slot />
    </div>

    <!-- Card Footer -->
    <div
      v-if="$slots.footer"
      :class="[
        'px-6 py-4 border-t border-gray-200 bg-gray-50',
        footerClass
      ]"
    >
      <slot name="footer" />
    </div>

    <!-- Loading Overlay -->
    <div
      v-if="loading"
      class="absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center"
    >
      <div class="flex flex-col items-center space-y-2">
        <svg class="animate-spin h-8 w-8 text-blue-600" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <span v-if="loadingText" class="text-sm text-gray-600">{{ loadingText }}</span>
      </div>
    </div>

    <!-- Status Indicator -->
    <div
      v-if="status"
      :class="[
        'absolute top-3 right-3 w-3 h-3 rounded-full',
        statusClasses[status]
      ]"
    ></div>

    <!-- Badge -->
    <div
      v-if="badge"
      class="absolute top-3 right-3"
    >
      <span
        :class="[
          'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
          badgeClasses[badgeType]
        ]"
      >
        {{ badge }}
      </span>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

// Props
const props = defineProps({
  title: {
    type: String,
    default: ''
  },
  subtitle: {
    type: String,
    default: ''
  },
  shadow: {
    type: String,
    default: 'sm',
    validator: (value) => ['none', 'sm', 'md', 'lg', 'xl'].includes(value)
  },
  rounded: {
    type: String,
    default: 'lg',
    validator: (value) => ['none', 'sm', 'md', 'lg', 'xl'].includes(value)
  },
  size: {
    type: String,
    default: 'default',
    validator: (value) => ['compact', 'default', 'comfortable'].includes(value)
  },
  border: {
    type: Boolean,
    default: false
  },
  hover: {
    type: Boolean,
    default: false
  },
  loading: {
    type: Boolean,
    default: false
  },
  loadingText: {
    type: String,
    default: ''
  },
  scrollable: {
    type: Boolean,
    default: false
  },
  maxHeight: {
    type: String,
    default: ''
  },
  status: {
    type: String,
    default: '',
    validator: (value) => ['', 'success', 'warning', 'error', 'info'].includes(value)
  },
  badge: {
    type: String,
    default: ''
  },
  badgeType: {
    type: String,
    default: 'primary',
    validator: (value) => ['primary', 'secondary', 'success', 'warning', 'error', 'info'].includes(value)
  },
  // Custom classes
  cardClass: {
    type: String,
    default: ''
  },
  headerClass: {
    type: String,
    default: ''
  },
  bodyClass: {
    type: String,
    default: ''
  },
  footerClass: {
    type: String,
    default: ''
  },
  titleClass: {
    type: String,
    default: ''
  },
  subtitleClass: {
    type: String,
    default: ''
  }
})

// Computed styles
const shadowClasses = {
  none: '',
  sm: 'shadow-sm',
  md: 'shadow-md',
  lg: 'shadow-lg',
  xl: 'shadow-xl'
}

const roundedClasses = {
  none: '',
  sm: 'rounded-sm',
  md: 'rounded-md',
  lg: 'rounded-lg',
  xl: 'rounded-xl'
}

const sizeClasses = {
  compact: '',
  default: '',
  comfortable: ''
}

const borderClasses = computed(() => {
  return props.border ? 'border border-gray-200' : ''
})

const hoverClasses = computed(() => {
  return props.hover ? 'hover:shadow-lg hover:-translate-y-0.5 cursor-pointer' : ''
})

const statusClasses = {
  success: 'bg-green-400',
  warning: 'bg-yellow-400',
  error: 'bg-red-400',
  info: 'bg-blue-400'
}

const badgeClasses = {
  primary: 'bg-blue-100 text-blue-800',
  secondary: 'bg-gray-100 text-gray-800',
  success: 'bg-green-100 text-green-800',
  warning: 'bg-yellow-100 text-yellow-800',
  error: 'bg-red-100 text-red-800',
  info: 'bg-blue-100 text-blue-800'
}
</script>

<style scoped>
/* Custom scrollbar for scrollable cards */
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

/* Loading overlay positioning */
.relative {
  position: relative;
}
</style>