<template>
  <div class="bg-white shadow rounded-lg">
    <div class="px-6 py-5 border-b border-gray-200">
      <h3 class="text-lg leading-6 font-medium text-gray-900">
        {{ title }}
      </h3>
    </div>
    <div class="px-6 py-5">
      <div v-if="loading" class="text-center py-8">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
        <p class="mt-2 text-sm text-gray-500">Yükleniyor...</p>
      </div>

      <div v-else-if="alerts && alerts.length > 0" class="space-y-4">
        <div
          v-for="(alert, index) in alerts"
          :key="index"
          :class="[
            'rounded-md p-4 border',
            getAlertClasses(alert.type)
          ]"
        >
          <div class="flex">
            <div class="flex-shrink-0">
              <component :is="getAlertIcon(alert.type)" />
            </div>
            <div class="ml-3 flex-1">
              <h3 v-if="alert.title" :class="['text-sm font-medium', getAlertTextColor(alert.type)]">
                {{ alert.title }}
              </h3>
              <div :class="['text-sm', alert.title ? 'mt-2' : '', getAlertTextColor(alert.type)]">
                <p>{{ alert.message }}</p>
              </div>
              <div v-if="alert.action || alert.url" class="mt-4">
                <div class="-mx-2 -my-1.5 flex">
                  <Link
                    v-if="alert.url"
                    :href="alert.url"
                    :class="[
                      'px-2 py-1.5 rounded-md text-sm font-medium focus:outline-none focus:ring-2 focus:ring-offset-2',
                      getAlertButtonClasses(alert.type)
                    ]"
                  >
                    {{ alert.action || 'İncele' }}
                  </Link>
                  <button
                    v-if="dismissible"
                    @click="dismissAlert(index)"
                    type="button"
                    :class="[
                      'ml-3 px-2 py-1.5 rounded-md text-sm font-medium focus:outline-none focus:ring-2 focus:ring-offset-2',
                      getAlertButtonClasses(alert.type, true)
                    ]"
                  >
                    Kapat
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div v-else class="text-center py-8">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <p class="mt-2 text-sm text-gray-500">{{ emptyMessage }}</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, h } from 'vue'
import { Link } from '@inertiajs/vue3'

const props = defineProps({
  title: {
    type: String,
    default: 'Uyarılar'
  },
  alerts: {
    type: Array,
    default: () => []
  },
  loading: {
    type: Boolean,
    default: false
  },
  dismissible: {
    type: Boolean,
    default: true
  },
  emptyMessage: {
    type: String,
    default: 'Herhangi bir uyarı bulunmuyor. Her şey yolunda!'
  }
})

const emit = defineEmits(['dismiss'])

const dismissAlert = (index) => {
  emit('dismiss', index)
}

const getAlertClasses = (type) => {
  const classes = {
    info: 'bg-blue-50 border-blue-200',
    success: 'bg-green-50 border-green-200',
    warning: 'bg-yellow-50 border-yellow-200',
    error: 'bg-red-50 border-red-200',
    danger: 'bg-red-50 border-red-200'
  }
  return classes[type] || classes.info
}

const getAlertTextColor = (type) => {
  const colors = {
    info: 'text-blue-800',
    success: 'text-green-800',
    warning: 'text-yellow-800',
    error: 'text-red-800',
    danger: 'text-red-800'
  }
  return colors[type] || colors.info
}

const getAlertButtonClasses = (type, isSecondary = false) => {
  if (isSecondary) {
    const secondaryClasses = {
      info: 'bg-blue-50 text-blue-700 hover:bg-blue-100 focus:ring-blue-600',
      success: 'bg-green-50 text-green-700 hover:bg-green-100 focus:ring-green-600',
      warning: 'bg-yellow-50 text-yellow-700 hover:bg-yellow-100 focus:ring-yellow-600',
      error: 'bg-red-50 text-red-700 hover:bg-red-100 focus:ring-red-600',
      danger: 'bg-red-50 text-red-700 hover:bg-red-100 focus:ring-red-600'
    }
    return secondaryClasses[type] || secondaryClasses.info
  }

  const primaryClasses = {
    info: 'bg-blue-100 text-blue-800 hover:bg-blue-200 focus:ring-blue-600',
    success: 'bg-green-100 text-green-800 hover:bg-green-200 focus:ring-green-600',
    warning: 'bg-yellow-100 text-yellow-800 hover:bg-yellow-200 focus:ring-yellow-600',
    error: 'bg-red-100 text-red-800 hover:bg-red-200 focus:ring-red-600',
    danger: 'bg-red-100 text-red-800 hover:bg-red-200 focus:ring-red-600'
  }
  return primaryClasses[type] || primaryClasses.info
}

const getAlertIcon = (type) => {
  const iconMap = {
    info: () => h('svg', {
      class: 'h-5 w-5 text-blue-400',
      fill: 'currentColor',
      viewBox: '0 0 20 20'
    }, [
      h('path', {
        'fill-rule': 'evenodd',
        d: 'M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z',
        'clip-rule': 'evenodd'
      })
    ]),
    success: () => h('svg', {
      class: 'h-5 w-5 text-green-400',
      fill: 'currentColor',
      viewBox: '0 0 20 20'
    }, [
      h('path', {
        'fill-rule': 'evenodd',
        d: 'M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z',
        'clip-rule': 'evenodd'
      })
    ]),
    warning: () => h('svg', {
      class: 'h-5 w-5 text-yellow-400',
      fill: 'currentColor',
      viewBox: '0 0 20 20'
    }, [
      h('path', {
        'fill-rule': 'evenodd',
        d: 'M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z',
        'clip-rule': 'evenodd'
      })
    ]),
    error: () => h('svg', {
      class: 'h-5 w-5 text-red-400',
      fill: 'currentColor',
      viewBox: '0 0 20 20'
    }, [
      h('path', {
        'fill-rule': 'evenodd',
        d: 'M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z',
        'clip-rule': 'evenodd'
      })
    ])
  }

  return iconMap[type] || iconMap.info
}
</script>
