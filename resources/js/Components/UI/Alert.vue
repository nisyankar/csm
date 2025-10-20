<template>
  <div
    v-if="visible"
    :class="[
      'rounded-lg p-4 border',
      alertClasses,
      'transition-all duration-300 ease-in-out'
    ]"
    role="alert"
  >
    <div class="flex items-start">
      <!-- Icon -->
      <div class="flex-shrink-0">
        <component :is="iconComponent" :class="iconClasses" />
      </div>

      <!-- Content -->
      <div class="ml-3 flex-1">
        <h3 v-if="title" :class="titleClasses">
          {{ title }}
        </h3>
        <div :class="messageClasses">
          <slot>{{ message }}</slot>
        </div>

        <!-- Action buttons -->
        <div v-if="$slots.actions" class="mt-3">
          <slot name="actions" />
        </div>
      </div>

      <!-- Dismiss button -->
      <div v-if="dismissible" class="ml-auto pl-3">
        <button
          @click="dismiss"
          :class="dismissButtonClasses"
          type="button"
        >
          <span class="sr-only">Kapat</span>
          <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, h } from 'vue'

const props = defineProps({
  type: {
    type: String,
    default: 'info',
    validator: (value) => ['success', 'error', 'warning', 'info'].includes(value)
  },
  title: {
    type: String,
    default: null
  },
  message: {
    type: String,
    default: ''
  },
  dismissible: {
    type: Boolean,
    default: false
  },
  autoHide: {
    type: Boolean,
    default: false
  },
  duration: {
    type: Number,
    default: 5000
  }
})

const emit = defineEmits(['dismiss'])

const visible = ref(true)

// Computed classes
const alertClasses = computed(() => {
  const classes = {
    success: 'bg-green-50 border-green-200',
    error: 'bg-red-50 border-red-200',
    warning: 'bg-yellow-50 border-yellow-200',
    info: 'bg-blue-50 border-blue-200'
  }
  return classes[props.type]
})

const iconClasses = computed(() => {
  const classes = {
    success: 'h-5 w-5 text-green-400',
    error: 'h-5 w-5 text-red-400',
    warning: 'h-5 w-5 text-yellow-400',
    info: 'h-5 w-5 text-blue-400'
  }
  return classes[props.type]
})

const titleClasses = computed(() => {
  const classes = {
    success: 'text-sm font-medium text-green-800',
    error: 'text-sm font-medium text-red-800',
    warning: 'text-sm font-medium text-yellow-800',
    info: 'text-sm font-medium text-blue-800'
  }
  return classes[props.type]
})

const messageClasses = computed(() => {
  const baseClasses = 'text-sm'
  const typeClasses = {
    success: 'text-green-700',
    error: 'text-red-700',
    warning: 'text-yellow-700',
    info: 'text-blue-700'
  }
  return `${baseClasses} ${typeClasses[props.type]} ${props.title ? 'mt-1' : ''}`
})

const dismissButtonClasses = computed(() => {
  const classes = {
    success: 'text-green-400 hover:text-green-600 focus:text-green-600',
    error: 'text-red-400 hover:text-red-600 focus:text-red-600',
    warning: 'text-yellow-400 hover:text-yellow-600 focus:text-yellow-600',
    info: 'text-blue-400 hover:text-blue-600 focus:text-blue-600'
  }
  return `${classes[props.type]} rounded-md p-1.5 inline-flex focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-transparent transition-colors`
})

// Icons
const iconComponent = computed(() => {
  const icons = {
    success: () => h('svg', {
      fill: 'none',
      viewBox: '0 0 24 24',
      'stroke-width': '1.5',
      stroke: 'currentColor',
      class: 'h-5 w-5'
    }, [
      h('path', {
        'stroke-linecap': 'round',
        'stroke-linejoin': 'round',
        d: 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z'
      })
    ]),
    error: () => h('svg', {
      fill: 'none',
      viewBox: '0 0 24 24',
      'stroke-width': '1.5',
      stroke: 'currentColor',
      class: 'h-5 w-5'
    }, [
      h('path', {
        'stroke-linecap': 'round',
        'stroke-linejoin': 'round',
        d: 'M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z'
      })
    ]),
    warning: () => h('svg', {
      fill: 'none',
      viewBox: '0 0 24 24',
      'stroke-width': '1.5',
      stroke: 'currentColor',
      class: 'h-5 w-5'
    }, [
      h('path', {
        'stroke-linecap': 'round',
        'stroke-linejoin': 'round',
        d: 'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z'
      })
    ]),
    info: () => h('svg', {
      fill: 'none',
      viewBox: '0 0 24 24',
      'stroke-width': '1.5',
      stroke: 'currentColor',
      class: 'h-5 w-5'
    }, [
      h('path', {
        'stroke-linecap': 'round',
        'stroke-linejoin': 'round',
        d: 'M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z'
      })
    ])
  }
  return icons[props.type]
})

// Methods
const dismiss = () => {
  visible.value = false
  emit('dismiss')
}

// Auto-hide functionality
onMounted(() => {
  if (props.autoHide) {
    setTimeout(() => {
      if (visible.value) {
        dismiss()
      }
    }, props.duration)
  }
})
</script>