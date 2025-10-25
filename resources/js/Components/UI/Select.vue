<template>
  <div class="space-y-1">
    <!-- Label -->
    <label
      v-if="label"
      :for="selectId"
      :class="[
        'block text-sm font-medium',
        disabled ? 'text-gray-400' : 'text-gray-700',
        required ? 'required' : ''
      ]"
    >
      {{ label }}
      <span v-if="required" class="text-red-500 ml-1">*</span>
    </label>

    <!-- Select Container -->
    <div class="relative">
      <!-- Search Input (for searchable selects) -->
      <div
        v-if="searchable"
        class="relative"
      >
        <input
          ref="searchInputRef"
          :value="isOpen ? searchQuery : displayValue"
          @input="handleSearchInput"
          @focus="openDropdown"
          @blur="handleBlur"
          @keydown="handleKeydown"
          :placeholder="displayValue || searchPlaceholder"
          :disabled="disabled"
          :class="[
            'block w-full rounded-md border-0 py-2 pl-3 pr-10 shadow-sm ring-1 ring-inset transition-colors',
            'placeholder:text-gray-400',
            'focus:ring-2 focus:ring-inset',
            sizeClasses[size],
            getSelectClasses()
          ]"
        />
        
        <!-- Dropdown Arrow -->
        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
          <svg
            :class="[
              'h-4 w-4 transition-transform',
              isOpen ? 'rotate-180' : '',
              error ? 'text-red-400' : 'text-gray-400'
            ]"
            fill="none"
            viewBox="0 0 24 24"
            stroke-width="1.5"
            stroke="currentColor"
          >
            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
          </svg>
        </div>
      </div>

      <!-- Regular Select (non-searchable) -->
      <select
        v-else
        :id="selectId"
        ref="selectRef"
        :value="modelValue"
        @change="handleChange"
        @focus="handleFocus"
        @blur="handleBlur"
        :disabled="disabled"
        :required="required"
        :multiple="multiple"
        :class="[
          'block w-full rounded-md border-0 py-2 pl-3 pr-10 shadow-sm ring-1 ring-inset transition-colors',
          'focus:ring-2 focus:ring-inset',
          sizeClasses[size],
          getSelectClasses()
        ]"
      >
        <option v-if="placeholder && !multiple" value="" disabled>
          {{ placeholder }}
        </option>
        
        <option
          v-for="option in options"
          :key="getOptionValue(option)"
          :value="getOptionValue(option)"
          :disabled="getOptionDisabled(option)"
        >
          {{ getOptionLabel(option) }}
        </option>
      </select>

      <!-- Custom Dropdown for Searchable Select -->
      <transition
        enter-active-class="transition ease-out duration-100"
        enter-from-class="transform opacity-0 scale-95"
        enter-to-class="transform opacity-100 scale-100"
        leave-active-class="transition ease-in duration-75"
        leave-from-class="transform opacity-100 scale-100"
        leave-to-class="transform opacity-0 scale-95"
      >
        <div
          v-if="searchable && isOpen"
          ref="dropdownRef"
          class="absolute z-50 mt-1 w-full bg-white shadow-lg rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 focus:outline-none max-h-60 overflow-auto"
        >
          <!-- No results -->
          <div
            v-if="filteredOptions.length === 0"
            class="px-3 py-2 text-sm text-gray-500"
          >
            {{ noResultsText }}
          </div>

          <!-- Options -->
          <div
            v-for="(option, index) in filteredOptions"
            :key="getOptionValue(option)"
            @mousedown.prevent="selectOption(option)"
            @mouseenter="highlightedIndex = index"
            :class="[
              'cursor-pointer select-none relative px-3 py-2 text-sm',
              highlightedIndex === index ? 'bg-blue-100 text-blue-900' : 'text-gray-900',
              getOptionDisabled(option) ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-100'
            ]"
          >
            <div class="flex items-center justify-between">
              <span :class="isSelected(option) ? 'font-semibold' : 'font-normal'">
                {{ getOptionLabel(option) }}
              </span>
              
              <!-- Selected indicator -->
              <svg
                v-if="isSelected(option)"
                class="h-4 w-4 text-blue-600"
                fill="none"
                viewBox="0 0 24 24"
                stroke-width="1.5"
                stroke="currentColor"
              >
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
              </svg>
            </div>
          </div>
        </div>
      </transition>

      <!-- Loading Spinner -->
      <div
        v-if="loading"
        class="absolute inset-y-0 right-0 pr-8 flex items-center"
      >
        <svg class="animate-spin h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
      </div>
    </div>

    <!-- Selected Items (for multiple select) -->
    <div
      v-if="multiple && selectedOptions.length > 0"
      class="flex flex-wrap gap-1 mt-2"
    >
      <span
        v-for="option in selectedOptions"
        :key="getOptionValue(option)"
        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800"
      >
        {{ getOptionLabel(option) }}
        <button
          @click="removeOption(option)"
          type="button"
          class="ml-1 inline-flex items-center justify-center w-4 h-4 rounded-full text-blue-400 hover:bg-blue-200 hover:text-blue-600"
        >
          <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </span>
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
import { ref, computed, useId, watch, nextTick, onMounted, onUnmounted } from 'vue'

// Props
const props = defineProps({
  modelValue: {
    type: [String, Number, Array],
    default: ''
  },
  options: {
    type: Array,
    required: true
  },
  optionLabel: {
    type: [String, Function],
    default: 'label'
  },
  optionValue: {
    type: [String, Function],
    default: 'value'
  },
  optionDisabled: {
    type: [String, Function],
    default: 'disabled'
  },
  label: {
    type: String,
    default: ''
  },
  placeholder: {
    type: String,
    default: 'Seçiniz...'
  },
  searchPlaceholder: {
    type: String,
    default: 'Arayın...'
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
  required: {
    type: Boolean,
    default: false
  },
  multiple: {
    type: Boolean,
    default: false
  },
  searchable: {
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
  noResultsText: {
    type: String,
    default: 'Sonuç bulunamadı'
  }
})

// Emits
const emit = defineEmits(['update:modelValue', 'focus', 'blur', 'search'])

// Refs
const selectRef = ref(null)
const searchInputRef = ref(null)
const dropdownRef = ref(null)
const isOpen = ref(false)
const searchQuery = ref('')
const highlightedIndex = ref(-1)

// Computed
const selectId = computed(() => props.id || useId())

const sizeClasses = {
  sm: 'text-sm py-1.5',
  md: 'text-sm py-2',
  lg: 'text-base py-2.5'
}

const getSelectClasses = () => {
  if (props.error) {
    return 'ring-red-300 focus:ring-red-500 text-red-900'
  }
  
  if (props.disabled) {
    return 'ring-gray-200 bg-gray-50 text-gray-500 cursor-not-allowed'
  }
  
  return 'ring-gray-300 focus:ring-blue-500 text-gray-900'
}

const filteredOptions = computed(() => {
  if (!props.searchable || !searchQuery.value) {
    return props.options
  }
  
  const query = searchQuery.value.toLowerCase()
  return props.options.filter(option => {
    const label = getOptionLabel(option).toLowerCase()
    return label.includes(query)
  })
})

const selectedOptions = computed(() => {
  if (!props.multiple) return []

  const values = Array.isArray(props.modelValue) ? props.modelValue : []
  return props.options.filter(option => values.includes(getOptionValue(option)))
})

// For single select, show selected label in search input
const displayValue = computed(() => {
  if (props.multiple) return ''
  if (!props.modelValue) return ''

  const selectedOption = props.options.find(option => getOptionValue(option) === props.modelValue)
  return selectedOption ? getOptionLabel(selectedOption) : ''
})

// Helper Methods
const getOptionLabel = (option) => {
  if (typeof props.optionLabel === 'function') {
    return props.optionLabel(option)
  }
  return typeof option === 'object' ? option[props.optionLabel] : option
}

const getOptionValue = (option) => {
  if (typeof props.optionValue === 'function') {
    return props.optionValue(option)
  }
  return typeof option === 'object' ? option[props.optionValue] : option
}

const getOptionDisabled = (option) => {
  if (typeof props.optionDisabled === 'function') {
    return props.optionDisabled(option)
  }
  return typeof option === 'object' ? option[props.optionDisabled] : false
}

const isSelected = (option) => {
  const value = getOptionValue(option)
  if (props.multiple) {
    return Array.isArray(props.modelValue) && props.modelValue.includes(value)
  }
  return props.modelValue === value
}

// Event Handlers
const handleChange = (event) => {
  let value = event.target.value
  
  if (props.multiple) {
    const selectedValues = Array.from(event.target.selectedOptions, option => option.value)
    emit('update:modelValue', selectedValues)
  } else {
    emit('update:modelValue', value)
  }
}

const handleFocus = (event) => {
  emit('focus', event)
}

const handleBlur = (event) => {
  // Don't close immediately - let handleClickOutside handle it
  // This allows dropdown clicks to register before closing
  emit('blur', event)
}

const handleSearchInput = (event) => {
  searchQuery.value = event.target.value
  if (!isOpen.value) {
    openDropdown()
  }
}

const handleKeydown = (event) => {
  if (!props.searchable) return

  switch (event.key) {
    case 'ArrowDown':
      event.preventDefault()
      if (!isOpen.value) {
        openDropdown()
      } else {
        highlightedIndex.value = Math.min(highlightedIndex.value + 1, filteredOptions.value.length - 1)
      }
      break

    case 'ArrowUp':
      event.preventDefault()
      highlightedIndex.value = Math.max(highlightedIndex.value - 1, -1)
      break

    case 'Enter':
      event.preventDefault()
      if (isOpen.value && highlightedIndex.value >= 0) {
        selectOption(filteredOptions.value[highlightedIndex.value])
      } else {
        openDropdown()
      }
      break

    case 'Escape':
      closeDropdown()
      break

    case 'Tab':
      closeDropdown()
      break
  }
}

const openDropdown = () => {
  if (props.disabled) return
  isOpen.value = true
  highlightedIndex.value = -1
}

const closeDropdown = () => {
  isOpen.value = false
  highlightedIndex.value = -1
  if (props.searchable) {
    searchQuery.value = ''
  }
}

const selectOption = (option) => {
  if (getOptionDisabled(option)) return
  
  const value = getOptionValue(option)
  
  if (props.multiple) {
    const currentValue = Array.isArray(props.modelValue) ? props.modelValue : []
    const newValue = currentValue.includes(value)
      ? currentValue.filter(v => v !== value)
      : [...currentValue, value]
    emit('update:modelValue', newValue)
  } else {
    emit('update:modelValue', value)
    closeDropdown()
  }
}

const removeOption = (option) => {
  if (!props.multiple) return
  
  const value = getOptionValue(option)
  const currentValue = Array.isArray(props.modelValue) ? props.modelValue : []
  const newValue = currentValue.filter(v => v !== value)
  emit('update:modelValue', newValue)
}

// Search handling
watch(searchQuery, (newQuery) => {
  emit('search', newQuery)
  highlightedIndex.value = -1
})

// Click outside handler
const handleClickOutside = (event) => {
  if (!isOpen.value) return

  const searchInput = searchInputRef.value
  const dropdown = dropdownRef.value

  // Check if click is outside both search input and dropdown
  const isClickInsideSearchInput = searchInput && searchInput.contains(event.target)
  const isClickInsideDropdown = dropdown && dropdown.contains(event.target)

  if (!isClickInsideSearchInput && !isClickInsideDropdown) {
    closeDropdown()
  }
}

// Lifecycle
onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})

// Public methods
const focus = () => {
  if (props.searchable) {
    searchInputRef.value?.focus()
  } else {
    selectRef.value?.focus()
  }
}

const blur = () => {
  if (props.searchable) {
    searchInputRef.value?.blur()
  } else {
    selectRef.value?.blur()
  }
}

// Expose methods
defineExpose({
  focus,
  blur,
  openDropdown,
  closeDropdown
})
</script>

<style scoped>
/* Required indicator */
.required::after {
  content: ' *';
  color: #EF4444;
}

/* Custom scrollbar for dropdown */
.max-h-60::-webkit-scrollbar {
  width: 6px;
}

.max-h-60::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 3px;
}

.max-h-60::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 3px;
}

.max-h-60::-webkit-scrollbar-thumb:hover {
  background: #a8a8a8;
}
</style>