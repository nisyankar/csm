<template>
  <span
    :class="[
      'inline-flex items-center font-medium',
      sizeClasses[size],
      variantClasses[variant],
      shapeClasses[shape],
      { 'cursor-pointer hover:opacity-80 transition-opacity': clickable },
      badgeClass
    ]"
    @click="handleClick"
  >
    <!-- Left Icon -->
    <component
      v-if="leftIcon"
      :is="leftIcon"
      :class="[
        iconSizeClasses[size],
        text ? (size === 'xs' ? '-ml-0.5 mr-1' : '-ml-1 mr-1.5') : ''
      ]"
    />

    <!-- Badge Text -->
    <span v-if="text">{{ text }}</span>
    
    <!-- Default Slot -->
    <slot />

    <!-- Right Icon -->
    <component
      v-if="rightIcon"
      :is="rightIcon"
      :class="[
        iconSizeClasses[size],
        text ? (size === 'xs' ? 'ml-1 -mr-0.5' : 'ml-1.5 -mr-1') : ''
      ]"
    />

    <!-- Close Button -->
    <button
      v-if="closable"
      @click.stop="handleClose"
      type="button"
      :class="[
        'ml-1 inline-flex items-center justify-center rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 transition-colors',
        closeButtonClasses[variant],
        closeButtonSizeClasses[size]
      ]"
    >
      <span class="sr-only">Kapat</span>
      <svg :class="iconSizeClasses[size]" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
      </svg>
    </button>

    <!-- Dot Indicator -->
    <span
      v-if="dot"
      :class="[
        'rounded-full',
        dotSizeClasses[size],
        dotColorClasses[variant],
        text ? 'ml-1.5' : ''
      ]"
    ></span>

    <!-- Pulse Animation -->
    <span
      v-if="pulse"
      :class="[
        'absolute inline-flex rounded-full opacity-75 animate-ping',
        dotSizeClasses[size],
        dotColorClasses[variant]
      ]"
    ></span>
  </span>
</template>

<script setup>
import { computed } from 'vue'

// Props
const props = defineProps({
  text: {
    type: String,
    default: ''
  },
  variant: {
    type: String,
    default: 'primary',
    validator: (value) => [
      'primary', 'secondary', 'success', 'warning', 'error', 'info', 'gray'
    ].includes(value)
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['xs', 'sm', 'md', 'lg'].includes(value)
  },
  shape: {
    type: String,
    default: 'rounded',
    validator: (value) => ['rounded', 'pill', 'square'].includes(value)
  },
  leftIcon: {
    type: [Object, Function],
    default: null
  },
  rightIcon: {
    type: [Object, Function],
    default: null
  },
  closable: {
    type: Boolean,
    default: false
  },
  clickable: {
    type: Boolean,
    default: false
  },
  dot: {
    type: Boolean,
    default: false
  },
  pulse: {
    type: Boolean,
    default: false
  },
  badgeClass: {
    type: String,
    default: ''
  }
})

// Emits
const emit = defineEmits(['click', 'close'])

// Size classes
const sizeClasses = {
  xs: 'px-2 py-0.5 text-xs',
  sm: 'px-2.5 py-0.5 text-xs',
  md: 'px-3 py-1 text-sm',
  lg: 'px-4 py-1.5 text-base'
}

const iconSizeClasses = {
  xs: 'h-3 w-3',
  sm: 'h-3 w-3',
  md: 'h-4 w-4',
  lg: 'h-5 w-5'
}

const closeButtonSizeClasses = {
  xs: 'h-3 w-3 focus:ring-1',
  sm: 'h-3 w-3 focus:ring-1',
  md: 'h-4 w-4 focus:ring-2',
  lg: 'h-5 w-5 focus:ring-2'
}

const dotSizeClasses = {
  xs: 'h-1.5 w-1.5',
  sm: 'h-2 w-2',
  md: 'h-2.5 w-2.5',
  lg: 'h-3 w-3'
}

// Shape classes
const shapeClasses = {
  rounded: 'rounded-md',
  pill: 'rounded-full',
  square: 'rounded-none'
}

// Variant classes
const variantClasses = {
  primary: 'bg-blue-100 text-blue-800',
  secondary: 'bg-gray-100 text-gray-800',
  success: 'bg-green-100 text-green-800',
  warning: 'bg-yellow-100 text-yellow-800',
  error: 'bg-red-100 text-red-800',
  info: 'bg-cyan-100 text-cyan-800',
  gray: 'bg-gray-100 text-gray-600'
}

const closeButtonClasses = {
  primary: 'text-blue-400 hover:bg-blue-200 hover:text-blue-600 focus:ring-blue-500',
  secondary: 'text-gray-400 hover:bg-gray-200 hover:text-gray-600 focus:ring-gray-500',
  success: 'text-green-400 hover:bg-green-200 hover:text-green-600 focus:ring-green-500',
  warning: 'text-yellow-400 hover:bg-yellow-200 hover:text-yellow-600 focus:ring-yellow-500',
  error: 'text-red-400 hover:bg-red-200 hover:text-red-600 focus:ring-red-500',
  info: 'text-cyan-400 hover:bg-cyan-200 hover:text-cyan-600 focus:ring-cyan-500',
  gray: 'text-gray-400 hover:bg-gray-200 hover:text-gray-600 focus:ring-gray-500'
}

const dotColorClasses = {
  primary: 'bg-blue-400',
  secondary: 'bg-gray-400',
  success: 'bg-green-400',
  warning: 'bg-yellow-400',
  error: 'bg-red-400',
  info: 'bg-cyan-400',
  gray: 'bg-gray-400'
}

// Methods
const handleClick = (event) => {
  if (props.clickable) {
    emit('click', event)
  }
}

const handleClose = (event) => {
  emit('close', event)
}
</script>

<style scoped>
/* Pulse animation */
@keyframes ping {
  75%, 100% {
    transform: scale(2);
    opacity: 0;
  }
}

.animate-ping {
  animation: ping 1s cubic-bezier(0, 0, 0.2, 1) infinite;
}

/* Positioning for pulse effect */
.relative {
  position: relative;
}
</style>