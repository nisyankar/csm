<template>
  <div class="space-y-1">
    <!-- Label -->
    <label
      v-if="label"
      :for="inputId"
      :class="[
        'block text-sm font-medium',
        disabled ? 'text-gray-400' : 'text-gray-700',
        required ? 'required' : ''
      ]"
    >
      {{ label }}
      <span v-if="required" class="text-red-500 ml-1">*</span>
    </label>

    <!-- Input Container -->
    <div class="relative">
      <!-- Left Icon -->
      <div
        v-if="leftIcon"
        class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
      >
        <component
          :is="leftIcon"
          :class="[
            'h-5 w-5',
            error ? 'text-red-400' : 'text-gray-400'
          ]"
        />
      </div>

      <!-- Input Element -->
      <input
        :id="inputId"
        ref="inputRef"
        :type="type"
        :value="modelValue"
        @input="handleInput"
        @blur="handleBlur"
        @focus="handleFocus"
        :placeholder="placeholder"
        :disabled="disabled"
        :readonly="readonly"
        :required="required"
        :min="min"
        :max="max"
        :step="step"
        :pattern="pattern"
        :autocomplete="autocomplete"
        :maxlength="maxlength"
        :class="[
          'block w-full rounded-md border-0 py-2 shadow-sm ring-1 ring-inset transition-colors',
          'placeholder:text-gray-400',
          'focus:ring-2 focus:ring-inset',
          // Padding adjustments for icons
          leftIcon ? 'pl-10' : 'pl-3',
          rightIcon || clearable ? 'pr-10' : 'pr-3',
          // Size variants
          sizeClasses[size],
          // State styles
          getInputClasses(),
          // Disabled state
          disabled ? 'cursor-not-allowed opacity-50' : '',
          // Additional classes
          inputClass
        ]"
      />

      <!-- Right Icon or Clear Button -->
      <div
        v-if="rightIcon || (clearable && modelValue)"
        class="absolute inset-y-0 right-0 pr-3 flex items-center"
      >
        <!-- Clear Button -->
        <button
          v-if="clearable && modelValue && !disabled"
          @click="handleClear"
          type="button"
          class="text-gray-400 hover:text-gray-600 transition-colors"
        >
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>

        <!-- Right Icon -->
        <component
          v-else-if="rightIcon"
          :is="rightIcon"
          :class="[
            'h-5 w-5',
            error ? 'text-red-400' : 'text-gray-400'
          ]"
        />
      </div>

      <!-- Loading Spinner -->
      <div
        v-if="loading"
        class="absolute inset-y-0 right-0 pr-3 flex items-center"
      >
        <svg class="animate-spin h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
      </div>
    </div>

    <!-- Help Text -->
    <p
      v-if="helpText && !error"
      class="text-xs text-gray-500"
    >
      {{ helpText }}
    </p>

    <!-- Error Message -->
    <p
      v-if="error"
      class="text-xs text-red-600"
    >
      {{ error }}
    </p>
  </div>
</template>

<script setup>
import { ref, computed, useId } from 'vue'

// Props
const props = defineProps({
  modelValue: {
    type: [String, Number],
    default: ''
  },
  type: {
    type: String,
    default: 'text',
    validator: (value) => [
      'text', 'email', 'password', 'number', 'tel', 'url', 'search', 'date', 'time', 'datetime-local', 'month', 'week'
    ].includes(value)
  },
  label: {
    type: String,
    default: ''
  },
  placeholder: {
    type: String,
    default: ''
  },
  helpText: {
    type: String,
    default: ''
  },
  error: {
    type: String,
    default: ''
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg'].includes(value)
  },
  disabled: {
    type: Boolean,
    default: false
  },
  readonly: {
    type: Boolean,
    default: false
  },
  required: {
    type: Boolean,
    default: false
  },
  clearable: {
    type: Boolean,
    default: false
  },
  loading: {
    type: Boolean,
    default: false
  },
  leftIcon: {
    type: [Object, Function],
    default: null
  },
  rightIcon: {
    type: [Object, Function],
    default: null
  },
  inputClass: {
    type: String,
    default: ''
  },
  // Input-specific attributes
  min: {
    type: [String, Number],
    default: undefined
  },
  max: {
    type: [String, Number],
    default: undefined
  },
  step: {
    type: [String, Number],
    default: undefined
  },
  pattern: {
    type: String,
    default: undefined
  },
  autocomplete: {
    type: String,
    default: undefined
  },
  maxlength: {
    type: [String, Number],
    default: undefined
  }
})

// Emits
const emit = defineEmits(['update:modelValue', 'focus', 'blur', 'clear'])

// Refs
const inputRef = ref(null)

// Computed
const inputId = computed(() => props.id || useId())

const sizeClasses = {
  sm: 'text-sm py-1.5',
  md: 'text-sm py-2',
  lg: 'text-base py-2.5'
}

const getInputClasses = () => {
  if (props.error) {
    return 'ring-red-300 focus:ring-red-500 text-red-900 placeholder:text-red-300'
  }
  
  if (props.disabled) {
    return 'ring-gray-200 bg-gray-50 text-gray-500'
  }
  
  return 'ring-gray-300 focus:ring-blue-500 text-gray-900'
}

// Methods
const handleInput = (event) => {
  let value = event.target.value
  
  // Number type handling
  if (props.type === 'number' && value !== '') {
    value = parseFloat(value) || 0
  }
  
  emit('update:modelValue', value)
}

const handleFocus = (event) => {
  emit('focus', event)
}

const handleBlur = (event) => {
  emit('blur', event)
}

const handleClear = () => {
  emit('update:modelValue', '')
  emit('clear')
  inputRef.value?.focus()
}

// Public methods
const focus = () => {
  inputRef.value?.focus()
}

const blur = () => {
  inputRef.value?.blur()
}

const select = () => {
  inputRef.value?.select()
}

// Expose methods
defineExpose({
  focus,
  blur,
  select,
  inputRef
})
</script>

<style scoped>
/* Required indicator */
.required::after {
  content: ' *';
  color: #EF4444;
}
</style>