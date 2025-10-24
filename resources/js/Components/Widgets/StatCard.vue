<template>
  <div class="bg-white overflow-hidden shadow rounded-lg transition-all hover:shadow-md">
    <div class="p-5">
      <div class="flex items-center">
        <div v-if="icon" class="flex-shrink-0">
          <component :is="iconComponent" :class="iconClass" />
        </div>
        <div class="ml-5 w-0 flex-1">
          <dl>
            <dt class="text-sm font-medium text-gray-500 truncate">{{ title }}</dt>
            <dd class="flex items-baseline">
              <div class="text-2xl font-semibold text-gray-900">{{ formattedValue }}</div>
              <div v-if="trend" :class="[
                'ml-2 flex items-baseline text-sm font-semibold',
                trend.direction === 'up' ? (trend.positive ? 'text-green-600' : 'text-red-600') :
                trend.direction === 'down' ? (trend.positive ? 'text-red-600' : 'text-green-600') :
                'text-gray-500'
              ]">
                <svg v-if="trend.direction === 'up'" class="h-4 w-4 flex-shrink-0 self-center" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                </svg>
                <svg v-if="trend.direction === 'down'" class="h-4 w-4 flex-shrink-0 self-center" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 12.586V5a1 1 0 012 0v7.586l2.293-2.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
                <span class="ml-1">{{ trend.value }}</span>
              </div>
            </dd>
            <dd v-if="subtitle" class="mt-1 text-xs text-gray-500">{{ subtitle }}</dd>
          </dl>
        </div>
      </div>
    </div>
    <div v-if="link || $slots.action" class="bg-gray-50 px-5 py-3">
      <div class="text-sm">
        <slot name="action">
          <Link v-if="link" :href="link.href" class="font-medium text-blue-700 hover:text-blue-900 transition-colors">
            {{ link.text }}
          </Link>
        </slot>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, h } from 'vue'
import { Link } from '@inertiajs/vue3'

const props = defineProps({
  title: {
    type: String,
    required: true
  },
  value: {
    type: [String, Number],
    required: true
  },
  icon: {
    type: String,
    default: null
  },
  iconColor: {
    type: String,
    default: 'gray'
  },
  subtitle: {
    type: String,
    default: null
  },
  link: {
    type: Object,
    default: null
  },
  trend: {
    type: Object,
    default: null
    // Example: { direction: 'up', value: '+12%', positive: true }
  },
  format: {
    type: String,
    default: 'number',
    validator: (value) => ['number', 'currency', 'percentage', 'text'].includes(value)
  },
  loading: {
    type: Boolean,
    default: false
  }
})

const iconClass = computed(() => {
  const colorClasses = {
    gray: 'h-6 w-6 text-gray-400',
    blue: 'h-6 w-6 text-blue-500',
    green: 'h-6 w-6 text-green-500',
    red: 'h-6 w-6 text-red-500',
    yellow: 'h-6 w-6 text-yellow-500',
    purple: 'h-6 w-6 text-purple-500',
    indigo: 'h-6 w-6 text-indigo-500',
  }
  return colorClasses[props.iconColor] || colorClasses.gray
})

const formattedValue = computed(() => {
  if (props.loading) return '...'

  switch (props.format) {
    case 'currency':
      return new Intl.NumberFormat('tr-TR', {
        style: 'currency',
        currency: 'TRY',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
      }).format(props.value)
    case 'percentage':
      return `${props.value}%`
    case 'number':
      return new Intl.NumberFormat('tr-TR').format(props.value)
    default:
      return props.value
  }
})

// Icon component mapping
const iconComponent = computed(() => {
  if (!props.icon) return null

  const icons = {
    users: () => h('svg', {
      class: iconClass.value,
      fill: 'none',
      viewBox: '0 0 24 24',
      'stroke-width': '1.5',
      stroke: 'currentColor'
    }, [
      h('path', {
        'stroke-linecap': 'round',
        'stroke-linejoin': 'round',
        d: 'M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z'
      })
    ]),
    building: () => h('svg', {
      class: iconClass.value,
      fill: 'none',
      viewBox: '0 0 24 24',
      'stroke-width': '1.5',
      stroke: 'currentColor'
    }, [
      h('path', {
        'stroke-linecap': 'round',
        'stroke-linejoin': 'round',
        d: 'M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21'
      })
    ]),
    clock: () => h('svg', {
      class: iconClass.value,
      fill: 'none',
      viewBox: '0 0 24 24',
      'stroke-width': '1.5',
      stroke: 'currentColor'
    }, [
      h('path', {
        'stroke-linecap': 'round',
        'stroke-linejoin': 'round',
        d: 'M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'
      })
    ]),
    calendar: () => h('svg', {
      class: iconClass.value,
      fill: 'none',
      viewBox: '0 0 24 24',
      'stroke-width': '1.5',
      stroke: 'currentColor'
    }, [
      h('path', {
        'stroke-linecap': 'round',
        'stroke-linejoin': 'round',
        d: 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5'
      })
    ]),
    chart: () => h('svg', {
      class: iconClass.value,
      fill: 'none',
      viewBox: '0 0 24 24',
      'stroke-width': '1.5',
      stroke: 'currentColor'
    }, [
      h('path', {
        'stroke-linecap': 'round',
        'stroke-linejoin': 'round',
        d: 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z'
      })
    ]),
    currency: () => h('svg', {
      class: iconClass.value,
      fill: 'none',
      viewBox: '0 0 24 24',
      'stroke-width': '1.5',
      stroke: 'currentColor'
    }, [
      h('path', {
        'stroke-linecap': 'round',
        'stroke-linejoin': 'round',
        d: 'M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z'
      })
    ]),
    check: () => h('svg', {
      class: iconClass.value,
      fill: 'none',
      viewBox: '0 0 24 24',
      'stroke-width': '1.5',
      stroke: 'currentColor'
    }, [
      h('path', {
        'stroke-linecap': 'round',
        'stroke-linejoin': 'round',
        d: 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z'
      })
    ]),
    exclamation: () => h('svg', {
      class: iconClass.value,
      fill: 'none',
      viewBox: '0 0 24 24',
      'stroke-width': '1.5',
      stroke: 'currentColor'
    }, [
      h('path', {
        'stroke-linecap': 'round',
        'stroke-linejoin': 'round',
        d: 'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z'
      })
    ]),
  }

  return icons[props.icon] || null
})
</script>