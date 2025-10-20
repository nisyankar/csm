<template>
  <div
    v-if="visible"
    :class="[
      'max-w-sm w-full bg-white shadow-lg rounded-lg pointer-events-auto ring-1 ring-black ring-opacity-5 overflow-hidden',
      'transform transition-all duration-300 ease-in-out'
    ]"
  >
    <div class="p-4">
      <div class="flex items-start">
        <!-- Icon -->
        <div class="flex-shrink-0">
          <component :is="iconComponent" :class="iconClasses" />
        </div>

        <!-- Content -->
        <div class="ml-3 w-0 flex-1 pt-0.5">
          <p v-if="title" class="text-sm font-medium text-gray-900">
            {{ title }}
          </p>
          <p :class="[
            'text-sm text-gray-500',
            title ? 'mt-1' : ''
          ]">
            {{ message }}
          </p>

          <!-- Action buttons -->
          <div v-if="actions && actions.length > 0" class="mt-3 flex space-x-2">
            <button
              v-for="action in actions"
              :key="action.label"
              @click="handleAction(action)"
              :class="[
                'text-sm font-medium rounded-md px-3 py-1.5 transition-colors',
                action.type === 'primary'
                  ? 'bg-blue-600 text-white hover:bg-blue-700'
                  : 'bg-gray-100 text-gray-900 hover:bg-gray-200'
              ]"
            >
              {{ action.label }}
            </button>
          </div>
        </div>

        <!-- Dismiss button -->
        <div v-if="dismissible" class="ml-4 flex-shrink-0 flex">
          <button
            @click="dismiss"
            class="bg-white rounded-md inline-flex text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
          >
            <span class="sr-only">Kapat</span>
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </div>
    </div>

    <!-- Progress bar for auto-hide -->
    <div
      v-if="autoHide && showProgress"
      class="h-1 bg-gray-200"
    >
      <div
        :class="[
          'h-full transition-all ease-linear',
          progressBarColor
        ]"
        :style="{ width: `${progress}%` }"
      ></div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, h } from 'vue'

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
    required: true
  },
  dismissible: {
    type: Boolean,
    default: true
  },
  autoHide: {
    type: Boolean,
    default: true
  },
  duration: {
    type: Number,
    default: 5000
  },
  showProgress: {
    type: Boolean,
    default: false
  },
  actions: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['dismiss', 'action'])

const visible = ref(true)
const progress = ref(100)
let progressTimer = null

// Computed
const iconClasses = computed(() => {
  const classes = {
    success: 'h-5 w-5 text-green-400',
    error: 'h-5 w-5 text-red-400',
    warning: 'h-5 w-5 text-yellow-400',
    info: 'h-5 w-5 text-blue-400'
  }
  return classes[props.type]
})

const progressBarColor = computed(() => {
  const colors = {
    success: 'bg-green-400',
    error: 'bg-red-400',
    warning: 'bg-yellow-400',
    info: 'bg-blue-400'
  }
  return colors[props.type]
})

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
  clearProgressTimer()
  emit('dismiss')
}

const handleAction = (action) => {
  emit('action', action)
  if (action.dismiss !== false) {
    dismiss()
  }
}

const clearProgressTimer = () => {
  if (progressTimer) {
    clearInterval(progressTimer)
    progressTimer = null
  }
}

const startProgressTimer = () => {
  if (!props.autoHide || !props.showProgress) return
  
  const interval = 50 // Update every 50ms
  const step = (interval / props.duration) * 100
  
  progressTimer = setInterval(() => {
    progress.value -= step
    if (progress.value <= 0) {
      dismiss()
    }
  }, interval)
}

// Lifecycle
onMounted(() => {
  if (props.autoHide) {
    setTimeout(dismiss, props.duration)
    startProgressTimer()
  }
})

onUnmounted(() => {
  clearProgressTimer()
})
</script>