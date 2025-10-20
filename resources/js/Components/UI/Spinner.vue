<template>
  <div :class="containerClasses">
    <!-- Spinning Circle -->
    <div v-if="type === 'spin'" :class="spinnerClasses">
      <svg class="animate-spin" fill="none" viewBox="0 0 24 24">
        <circle
          class="opacity-25"
          cx="12"
          cy="12"
          r="10"
          stroke="currentColor"
          stroke-width="4"
        ></circle>
        <path
          class="opacity-75"
          fill="currentColor"
          d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
        ></path>
      </svg>
    </div>

    <!-- Pulsing Dots -->
    <div v-else-if="type === 'dots'" class="flex space-x-1">
      <div
        v-for="i in 3"
        :key="i"
        :class="[
          dotClasses,
          `animate-pulse-delay-${i}`
        ]"
        :style="{ animationDelay: `${(i - 1) * 0.2}s` }"
      ></div>
    </div>

    <!-- Bouncing Bars -->
    <div v-else-if="type === 'bars'" class="flex space-x-1">
      <div
        v-for="i in 3"
        :key="i"
        :class="[
          barClasses,
          'animate-bounce'
        ]"
        :style="{ animationDelay: `${(i - 1) * 0.1}s` }"
      ></div>
    </div>

    <!-- Simple Ring -->
    <div v-else-if="type === 'ring'" :class="ringClasses"></div>

    <!-- Loading text -->
    <span v-if="text" :class="textClasses">
      {{ text }}
    </span>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  type: {
    type: String,
    default: 'spin',
    validator: (value) => ['spin', 'dots', 'bars', 'ring'].includes(value)
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['xs', 'sm', 'md', 'lg', 'xl'].includes(value)
  },
  color: {
    type: String,
    default: 'blue',
    validator: (value) => ['blue', 'gray', 'red', 'green', 'yellow', 'purple', 'pink', 'indigo'].includes(value)
  },
  text: {
    type: String,
    default: null
  },
  center: {
    type: Boolean,
    default: false
  }
})

// Size mappings
const sizeClasses = {
  xs: {
    spinner: 'h-3 w-3',
    dot: 'h-1 w-1',
    bar: 'h-2 w-0.5',
    text: 'text-xs ml-2'
  },
  sm: {
    spinner: 'h-4 w-4',
    dot: 'h-1.5 w-1.5',
    bar: 'h-3 w-0.5',
    text: 'text-sm ml-2'
  },
  md: {
    spinner: 'h-5 w-5',
    dot: 'h-2 w-2',
    bar: 'h-4 w-1',
    text: 'text-sm ml-3'
  },
  lg: {
    spinner: 'h-6 w-6',
    dot: 'h-2.5 w-2.5',
    bar: 'h-5 w-1',
    text: 'text-base ml-3'
  },
  xl: {
    spinner: 'h-8 w-8',
    dot: 'h-3 w-3',
    bar: 'h-6 w-1.5',
    text: 'text-lg ml-4'
  }
}

// Color mappings
const colorClasses = {
  blue: 'text-blue-600',
  gray: 'text-gray-600',
  red: 'text-red-600',
  green: 'text-green-600',
  yellow: 'text-yellow-600',
  purple: 'text-purple-600',
  pink: 'text-pink-600',
  indigo: 'text-indigo-600'
}

// Computed classes
const containerClasses = computed(() => {
  const baseClasses = 'inline-flex items-center'
  const centerClasses = props.center ? 'justify-center' : ''
  return `${baseClasses} ${centerClasses}`.trim()
})

const spinnerClasses = computed(() => {
  return `${sizeClasses[props.size].spinner} ${colorClasses[props.color]}`
})

const dotClasses = computed(() => {
  return `${sizeClasses[props.size].dot} ${colorClasses[props.color]} rounded-full animate-pulse`
})

const barClasses = computed(() => {
  return `${sizeClasses[props.size].bar} ${colorClasses[props.color]} rounded-full`
})

const ringClasses = computed(() => {
  return `${sizeClasses[props.size].spinner} border-2 border-current border-t-transparent rounded-full animate-spin ${colorClasses[props.color]}`
})

const textClasses = computed(() => {
  return `${sizeClasses[props.size].text} ${colorClasses[props.color]} font-medium`
})
</script>

<style scoped>
@keyframes pulse-delay-1 {
  0%, 80%, 100% { opacity: 0.4; }
  40% { opacity: 1; }
}

@keyframes pulse-delay-2 {
  0%, 80%, 100% { opacity: 0.4; }
  40% { opacity: 1; }
}

@keyframes pulse-delay-3 {
  0%, 80%, 100% { opacity: 0.4; }
  40% { opacity: 1; }
}

.animate-pulse-delay-1 {
  animation: pulse-delay-1 1.4s ease-in-out infinite;
}

.animate-pulse-delay-2 {
  animation: pulse-delay-2 1.4s ease-in-out infinite;
  animation-delay: 0.2s;
}

.animate-pulse-delay-3 {
  animation: pulse-delay-3 1.4s ease-in-out infinite;
  animation-delay: 0.4s;
}
</style>