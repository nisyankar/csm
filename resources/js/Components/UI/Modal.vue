<template>
  <teleport to="body">
    <transition
      enter-active-class="transition ease-out duration-300"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition ease-in duration-200"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="show"
        class="fixed inset-0 z-50 overflow-y-auto"
        @click="handleBackdropClick"
      >
        <!-- Backdrop -->
        <div
          class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
          :class="backdropClass"
        ></div>

        <!-- Modal Container -->
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
          <transition
            enter-active-class="transition ease-out duration-300"
            enter-from-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            enter-to-class="opacity-100 translate-y-0 sm:scale-100"
            leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100 translate-y-0 sm:scale-100"
            leave-to-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
          >
            <div
              v-if="show"
              ref="modalRef"
              :class="[
                'relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all',
                sizeClasses[size],
                modalClass
              ]"
              role="dialog"
              aria-modal="true"
              :aria-labelledby="titleId"
              :aria-describedby="contentId"
            >
              <!-- Close Button (Top Right) -->
              <button
                v-if="closable && !hideCloseButton"
                @click="closeModal"
                type="button"
                class="absolute top-4 right-4 z-10 text-gray-400 hover:text-gray-500 transition-colors"
              >
                <span class="sr-only">Kapat</span>
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>

              <!-- Modal Header -->
              <div
                v-if="title || $slots.header"
                :class="[
                  'border-b border-gray-200 px-6 py-4',
                  headerClass
                ]"
              >
                <slot name="header">
                  <div class="flex items-center justify-between">
                    <h3
                      :id="titleId"
                      :class="[
                        'text-lg font-semibold leading-6 text-gray-900',
                        titleClass
                      ]"
                    >
                      {{ title }}
                    </h3>
                    
                    <!-- Header Actions -->
                    <div v-if="$slots.headerActions" class="flex items-center space-x-2">
                      <slot name="headerActions" />
                    </div>
                  </div>
                </slot>
              </div>

              <!-- Modal Body -->
              <div
                :id="contentId"
                :class="[
                  'px-6 py-4',
                  bodyClass,
                  { 'max-h-96 overflow-y-auto': scrollable }
                ]"
              >
                <slot />
              </div>

              <!-- Modal Footer -->
              <div
                v-if="$slots.footer || showDefaultFooter"
                :class="[
                  'border-t border-gray-200 px-6 py-4 flex items-center justify-end space-x-3',
                  footerClass
                ]"
              >
                <slot name="footer">
                  <div v-if="showDefaultFooter" class="flex items-center space-x-3">
                    <!-- Cancel Button -->
                    <button
                      v-if="showCancelButton"
                      @click="handleCancel"
                      type="button"
                      :disabled="loading"
                      class="inline-flex justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                      {{ cancelText }}
                    </button>

                    <!-- Confirm Button -->
                    <button
                      v-if="showConfirmButton"
                      @click="handleConfirm"
                      type="button"
                      :disabled="loading || confirmDisabled"
                      :class="[
                        'inline-flex justify-center rounded-md px-4 py-2 text-sm font-medium shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed',
                        confirmButtonClasses
                      ]"
                    >
                      <!-- Loading Spinner -->
                      <svg
                        v-if="loading"
                        class="animate-spin -ml-1 mr-2 h-4 w-4"
                        fill="none"
                        viewBox="0 0 24 24"
                      >
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                      </svg>
                      {{ loading ? loadingText : confirmText }}
                    </button>
                  </div>
                </slot>
              </div>
            </div>
          </transition>
        </div>
      </div>
    </transition>
  </teleport>
</template>

<script setup>
import { ref, computed, useId, watch, nextTick, onMounted, onUnmounted } from 'vue'

// Props
const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  title: {
    type: String,
    default: ''
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['xs', 'sm', 'md', 'lg', 'xl', '2xl', 'full'].includes(value)
  },
  closable: {
    type: Boolean,
    default: true
  },
  closeOnBackdrop: {
    type: Boolean,
    default: true
  },
  closeOnEscape: {
    type: Boolean,
    default: true
  },
  hideCloseButton: {
    type: Boolean,
    default: false
  },
  scrollable: {
    type: Boolean,
    default: false
  },
  loading: {
    type: Boolean,
    default: false
  },
  // Footer props
  showDefaultFooter: {
    type: Boolean,
    default: false
  },
  showCancelButton: {
    type: Boolean,
    default: true
  },
  showConfirmButton: {
    type: Boolean,
    default: true
  },
  cancelText: {
    type: String,
    default: 'İptal'
  },
  confirmText: {
    type: String,
    default: 'Tamam'
  },
  loadingText: {
    type: String,
    default: 'Yükleniyor...'
  },
  confirmType: {
    type: String,
    default: 'primary',
    validator: (value) => ['primary', 'secondary', 'success', 'warning', 'danger'].includes(value)
  },
  confirmDisabled: {
    type: Boolean,
    default: false
  },
  // Styling props
  modalClass: {
    type: String,
    default: ''
  },
  backdropClass: {
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
  }
})

// Emits
const emit = defineEmits(['close', 'cancel', 'confirm', 'backdrop-click', 'escape'])

// Refs
const modalRef = ref(null)

// Computed
const titleId = computed(() => `modal-title-${useId()}`)
const contentId = computed(() => `modal-content-${useId()}`)

const sizeClasses = {
  xs: 'sm:w-full sm:max-w-xs',
  sm: 'sm:w-full sm:max-w-sm',
  md: 'sm:w-full sm:max-w-md',
  lg: 'sm:w-full sm:max-w-lg',
  xl: 'sm:w-full sm:max-w-xl',
  '2xl': 'sm:w-full sm:max-w-2xl',
  full: 'w-full h-full sm:w-full sm:h-full'
}

const confirmButtonClasses = computed(() => {
  const baseClasses = 'border border-transparent'
  
  const typeClasses = {
    primary: 'bg-blue-600 text-white hover:bg-blue-700 focus:ring-blue-500',
    secondary: 'bg-gray-600 text-white hover:bg-gray-700 focus:ring-gray-500',
    success: 'bg-green-600 text-white hover:bg-green-700 focus:ring-green-500',
    warning: 'bg-yellow-600 text-white hover:bg-yellow-700 focus:ring-yellow-500',
    danger: 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500'
  }
  
  return `${baseClasses} ${typeClasses[props.confirmType] || typeClasses.primary}`
})

// Methods
const closeModal = () => {
  if (!props.closable) return
  emit('close')
}

const handleBackdropClick = (event) => {
  if (event.target === event.currentTarget && props.closeOnBackdrop) {
    emit('backdrop-click')
    closeModal()
  }
}

const handleCancel = () => {
  emit('cancel')
  closeModal()
}

const handleConfirm = () => {
  emit('confirm')
}

const handleEscape = (event) => {
  if (event.key === 'Escape' && props.show && props.closeOnEscape) {
    emit('escape')
    closeModal()
  }
}

// Focus management
const focusFirstElement = () => {
  nextTick(() => {
    if (modalRef.value) {
      const focusableElements = modalRef.value.querySelectorAll(
        'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
      )
      if (focusableElements.length > 0) {
        focusableElements[0].focus()
      } else {
        modalRef.value.focus()
      }
    }
  })
}

const trapFocus = (event) => {
  if (!modalRef.value || !props.show) return

  const focusableElements = modalRef.value.querySelectorAll(
    'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
  )
  
  const firstElement = focusableElements[0]
  const lastElement = focusableElements[focusableElements.length - 1]

  if (event.key === 'Tab') {
    if (event.shiftKey) {
      if (document.activeElement === firstElement) {
        event.preventDefault()
        lastElement.focus()
      }
    } else {
      if (document.activeElement === lastElement) {
        event.preventDefault()
        firstElement.focus()
      }
    }
  }
}

// Watchers
watch(() => props.show, (newValue) => {
  if (newValue) {
    // Prevent body scroll when modal is open
    document.body.style.overflow = 'hidden'
    focusFirstElement()
  } else {
    // Restore body scroll when modal is closed
    document.body.style.overflow = ''
  }
})

// Lifecycle
onMounted(() => {
  document.addEventListener('keydown', handleEscape)
  document.addEventListener('keydown', trapFocus)
})

onUnmounted(() => {
  document.removeEventListener('keydown', handleEscape)
  document.removeEventListener('keydown', trapFocus)
  // Ensure body scroll is restored
  document.body.style.overflow = ''
})

// Public methods
const focus = () => {
  focusFirstElement()
}

// Expose methods
defineExpose({
  focus,
  modalRef
})
</script>

<style scoped>
/* Ensure modal appears above other content */
.z-50 {
  z-index: 50;
}

/* Custom scrollbar for scrollable modals */
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
</style>