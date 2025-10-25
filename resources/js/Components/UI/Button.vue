<template>
  <component
    :is="tag"
    v-bind="linkProps"
    :type="isButton ? type : undefined"
    :disabled="isButton ? (disabled || loading) : undefined"
    :class="buttonClasses"
    @click="handleClick"
  >
    <!-- Loading spinner -->
    <Spinner
      v-if="loading"
      :size="spinnerSize"
      :color="spinnerColor"
      type="spin"
      class="mr-2"
    />

    <!-- Left icon -->
    <component
      v-if="leftIcon && !loading"
      :is="leftIcon"
      :class="iconClasses"
    />

    <!-- Content -->
    <span v-if="slots.default || label" :class="contentClasses">
      <slot>{{ label }}</slot>
    </span>

    <!-- Right icon -->
    <component
      v-if="rightIcon && !loading"
      :is="rightIcon"
      :class="[iconClasses, leftIcon || slots.default || label ? 'ml-2' : '']"
    />

    <!-- Badge -->
    <span
      v-if="badge"
      :class="badgeClasses"
    >
      {{ badge }}
    </span>
  </component>
</template>

<script setup>
import { computed, useSlots } from 'vue'
import { Link } from '@inertiajs/vue3'
import Spinner from './Spinner.vue'

const props = defineProps({
  // Button content
  label: {
    type: String,
    default: null
  },
  
  // Button type and behavior
  type: {
    type: String,
    default: 'button',
    validator: (value) => ['button', 'submit', 'reset'].includes(value)
  },
  
  // Visual variant
  variant: {
    type: String,
    default: 'primary',
    validator: (value) => [
      'primary', 'secondary', 'outline', 'ghost', 'link',
      'success', 'warning', 'danger'
    ].includes(value)
  },
  
  // Size
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['xs', 'sm', 'md', 'lg', 'xl'].includes(value)
  },
  
  // State
  disabled: {
    type: Boolean,
    default: false
  },
  
  loading: {
    type: Boolean,
    default: false
  },
  
  // Navigation (for Link component)
  href: {
    type: String,
    default: null
  },
  
  to: {
    type: String,
    default: null
  },
  
  method: {
    type: String,
    default: 'get'
  },
  
  preserveScroll: {
    type: Boolean,
    default: false
  },
  
  // Icons
  leftIcon: {
    type: [Object, Function],
    default: null
  },
  
  rightIcon: {
    type: [Object, Function],
    default: null
  },
  
  // Badge
  badge: {
    type: [String, Number],
    default: null
  },
  
  // Layout
  fullWidth: {
    type: Boolean,
    default: false
  },
  
  rounded: {
    type: String,
    default: 'md',
    validator: (value) => ['none', 'sm', 'md', 'lg', 'full'].includes(value)
  }
})

const emit = defineEmits(['click'])

// Slots
const slots = useSlots()

// Computed properties
const tag = computed(() => {
  if (props.href || props.to) {
    return Link
  }
  return 'button'
})

const isButton = computed(() => tag.value === 'button')

const linkProps = computed(() => {
  if (tag.value === Link) {
    return {
      href: props.href || props.to,
      method: props.method,
      preserveScroll: props.preserveScroll
    }
  }
  return {}
})

const buttonClasses = computed(() => {
  const baseClasses = [
    'inline-flex items-center justify-center font-medium transition-all duration-200',
    'focus:outline-none focus:ring-2 focus:ring-offset-2',
    'disabled:opacity-50 disabled:cursor-not-allowed'
  ]
  
  // Size classes
  const sizeClasses = {
    xs: 'px-2.5 py-1.5 text-xs',
    sm: 'px-3 py-2 text-sm',
    md: 'px-4 py-2 text-sm',
    lg: 'px-4 py-2 text-base',
    xl: 'px-6 py-3 text-base'
  }
  
  // Rounded classes
  const roundedClasses = {
    none: 'rounded-none',
    sm: 'rounded-sm',
    md: 'rounded-md',
    lg: 'rounded-lg',
    full: 'rounded-full'
  }
  
  // Variant classes
  const variantClasses = {
    primary: [
      'bg-blue-600 text-white shadow-sm',
      'hover:bg-blue-700 focus:ring-blue-500',
      'active:bg-blue-800'
    ],
    secondary: [
      'bg-gray-600 text-white shadow-sm',
      'hover:bg-gray-700 focus:ring-gray-500',
      'active:bg-gray-800'
    ],
    outline: [
      'bg-white text-gray-700 border border-gray-300 shadow-sm',
      'hover:bg-gray-50 focus:ring-blue-500',
      'active:bg-gray-100'
    ],
    ghost: [
      'bg-transparent text-gray-700',
      'hover:bg-gray-100 focus:ring-gray-500',
      'active:bg-gray-200'
    ],
    link: [
      'bg-transparent text-blue-600 underline',
      'hover:text-blue-800 focus:ring-blue-500',
      'active:text-blue-900'
    ],
    success: [
      'bg-green-600 text-white shadow-sm',
      'hover:bg-green-700 focus:ring-green-500',
      'active:bg-green-800'
    ],
    warning: [
      'bg-yellow-600 text-white shadow-sm',
      'hover:bg-yellow-700 focus:ring-yellow-500',
      'active:bg-yellow-800'
    ],
    danger: [
      'bg-red-600 text-white shadow-sm',
      'hover:bg-red-700 focus:ring-red-500',
      'active:bg-red-800'
    ]
  }
  
  const classes = [
    ...baseClasses,
    sizeClasses[props.size],
    roundedClasses[props.rounded],
    ...variantClasses[props.variant]
  ]
  
  if (props.fullWidth) {
    classes.push('w-full')
  }
  
  return classes.join(' ')
})

const iconClasses = computed(() => {
  const sizeClasses = {
    xs: 'w-3 h-3',
    sm: 'w-4 h-4',
    md: 'w-4 h-4',
    lg: 'w-5 h-5',
    xl: 'w-5 h-5'
  }

  const classes = [sizeClasses[props.size]]

  if (props.leftIcon && (props.label || slots.default)) {
    classes.push('mr-2')
  }

  return classes.join(' ')
})

const contentClasses = computed(() => {
  if (props.loading) {
    return 'opacity-70'
  }
  return ''
})

const spinnerSize = computed(() => {
  const sizes = {
    xs: 'xs',
    sm: 'sm',
    md: 'sm',
    lg: 'md',
    xl: 'md'
  }
  return sizes[props.size]
})

const spinnerColor = computed(() => {
  if (['primary', 'success', 'warning', 'danger', 'secondary'].includes(props.variant)) {
    return 'gray'  // White spinner on colored backgrounds
  }
  return 'blue'  // Colored spinner on light backgrounds
})

const badgeClasses = computed(() => {
  const baseClasses = 'ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium'
  
  if (['primary', 'success', 'warning', 'danger', 'secondary'].includes(props.variant)) {
    return `${baseClasses} bg-white bg-opacity-20 text-white`
  }
  
  return `${baseClasses} bg-blue-100 text-blue-800`
})

// Methods
const handleClick = (event) => {
  if (props.disabled || props.loading) {
    event.preventDefault()
    return
  }
  emit('click', event)
}
</script>