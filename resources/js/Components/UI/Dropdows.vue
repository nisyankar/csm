<template>
  <div class="relative inline-block text-left" ref="dropdownRef">
    <!-- Trigger Button -->
    <div>
      <button
        ref="triggerRef"
        @click="toggleDropdown"
        @keydown="handleKeydown"
        type="button"
        :class="[
          'inline-flex items-center justify-center w-full rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500',
          disabled ? 'opacity-50 cursor-not-allowed' : '',
          triggerClass
        ]"
        :disabled="disabled"
        :aria-expanded="isOpen"
        :aria-haspopup="true"
      >
        <slot name="trigger" :isOpen="isOpen">
          <span>{{ triggerText }}</span>
          <svg
            :class="[
              '-mr-1 ml-2 h-5 w-5 transition-transform duration-200',
              isOpen ? 'rotate-180' : ''
            ]"
            fill="none"
            viewBox="0 0 24 24"
            stroke-width="1.5"
            stroke="currentColor"
          >
            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
          </svg>
        </slot>
      </button>
    </div>

    <!-- Dropdown Menu -->
    <transition
      enter-active-class="transition ease-out duration-100"
      enter-from-class="transform opacity-0 scale-95"
      enter-to-class="transform opacity-100 scale-100"
      leave-active-class="transition ease-in duration-75"
      leave-from-class="transform opacity-100 scale-100"
      leave-to-class="transform opacity-0 scale-95"
    >
      <div
        v-if="isOpen"
        ref="menuRef"
        :class="[
          'absolute z-50 mt-2 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none',
          widthClasses[width],
          positionClasses[position],
          menuClass
        ]"
        role="menu"
        :aria-orientation="'vertical'"
        :aria-labelledby="triggerId"
      >
        <div class="py-1" role="none">
          <!-- Search Input -->
          <div v-if="searchable" class="px-3 py-2 border-b border-gray-100">
            <input
              ref="searchInputRef"
              v-model="searchQuery"
              @keydown="handleSearchKeydown"
              type="text"
              :placeholder="searchPlaceholder"
              class="block w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            />
          </div>

          <!-- Menu Items -->
          <div :class="{ 'max-h-60 overflow-y-auto': scrollable }">
            <!-- Custom Menu Content -->
            <slot name="menu" :close="closeDropdown" :items="filteredItems" />

            <!-- Default Menu Items -->
            <template v-if="!$slots.menu">
              <!-- No Results -->
              <div
                v-if="searchable && searchQuery && filteredItems.length === 0"
                class="px-4 py-2 text-sm text-gray-500"
              >
                {{ noResultsText }}
              </div>

              <!-- Menu Items -->
              <div
                v-for="(item, index) in filteredItems"
                :key="getItemKey(item, index)"
                @click="handleItemClick(item, index)"
                @mouseenter="highlightedIndex = index"
                :class="[
                  'cursor-pointer select-none relative px-4 py-2 text-sm transition-colors',
                  highlightedIndex === index ? 'bg-gray-100 text-gray-900' : 'text-gray-700',
                  getItemDisabled(item) ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-100',
                  getItemClass(item)
                ]"
                role="menuitem"
              >
                <div class="flex items-center">
                  <!-- Item Icon -->
                  <component
                    v-if="getItemIcon(item)"
                    :is="getItemIcon(item)"
                    class="mr-3 h-5 w-5 text-gray-400"
                  />

                  <!-- Item Content -->
                  <div class="flex-1">
                    <div class="font-medium">{{ getItemLabel(item) }}</div>
                    <div v-if="getItemDescription(item)" class="text-xs text-gray-500 mt-0.5">
                      {{ getItemDescription(item) }}
                    </div>
                  </div>

                  <!-- Item Badge -->
                  <span
                    v-if="getItemBadge(item)"
                    class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800"
                  >
                    {{ getItemBadge(item) }}
                  </span>

                  <!-- Selected Indicator -->
                  <svg
                    v-if="isItemSelected(item)"
                    class="ml-2 h-5 w-5 text-blue-600"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="1.5"
                    stroke="currentColor"
                  >
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                  </svg>
                </div>
              </div>

              <!-- Dividers and Groups -->
              <div
                v-for="(item, index) in filteredItems"
                :key="`divider-${index}`"
              >
                <hr
                  v-if="item.type === 'divider'"
                  class="my-1 border-gray-200"
                  role="separator"
                />
                
                <div
                  v-else-if="item.type === 'header'"
                  class="px-4 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider"
                >
                  {{ item.label }}
                </div>
              </div>
            </template>
          </div>

          <!-- Footer -->
          <div v-if="$slots.footer" class="border-t border-gray-100 py-1">
            <slot name="footer" :close="closeDropdown" />
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, computed, watch, nextTick, onMounted, onUnmounted, useId } from 'vue'

// Props
const props = defineProps({
  items: {
    type: Array,
    default: () => []
  },
  modelValue: {
    type: [String, Number, Array, Object],
    default: null
  },
  triggerText: {
    type: String,
    default: 'Seçenekler'
  },
  position: {
    type: String,
    default: 'bottom-left',
    validator: (value) => [
      'bottom-left', 'bottom-right', 'top-left', 'top-right'
    ].includes(value)
  },
  width: {
    type: String,
    default: 'auto',
    validator: (value) => ['auto', 'trigger', 'sm', 'md', 'lg', 'xl'].includes(value)
  },
  disabled: {
    type: Boolean,
    default: false
  },
  searchable: {
    type: Boolean,
    default: false
  },
  scrollable: {
    type: Boolean,
    default: true
  },
  closeOnSelect: {
    type: Boolean,
    default: true
  },
  multiple: {
    type: Boolean,
    default: false
  },
  searchPlaceholder: {
    type: String,
    default: 'Ara...'
  },
  noResultsText: {
    type: String,
    default: 'Sonuç bulunamadı'
  },
  // Item property accessors
  itemLabel: {
    type: [String, Function],
    default: 'label'
  },
  itemValue: {
    type: [String, Function],
    default: 'value'
  },
  itemIcon: {
    type: [String, Function],
    default: 'icon'
  },
  itemDescription: {
    type: [String, Function],
    default: 'description'
  },
  itemBadge: {
    type: [String, Function],
    default: 'badge'
  },
  itemDisabled: {
    type: [String, Function],
    default: 'disabled'
  },
  itemClass: {
    type: [String, Function],
    default: 'class'
  },
  // Custom classes
  triggerClass: {
    type: String,
    default: ''
  },
  menuClass: {
    type: String,
    default: ''
  }
})

// Emits
const emit = defineEmits(['update:modelValue', 'select', 'open', 'close', 'search'])

// Refs
const dropdownRef = ref(null)
const triggerRef = ref(null)
const menuRef = ref(null)
const searchInputRef = ref(null)

// State
const isOpen = ref(false)
const searchQuery = ref('')
const highlightedIndex = ref(-1)

// Computed
const triggerId = `dropdown-trigger-${useId()}`

const positionClasses = {
  'bottom-left': 'origin-top-left left-0',
  'bottom-right': 'origin-top-right right-0',
  'top-left': 'origin-bottom-left left-0 bottom-full mb-2',
  'top-right': 'origin-bottom-right right-0 bottom-full mb-2'
}

const widthClasses = {
  auto: 'min-w-max',
  trigger: 'w-full',
  sm: 'w-48',
  md: 'w-64',
  lg: 'w-80',
  xl: 'w-96'
}

const filteredItems = computed(() => {
  let items = props.items.filter(item => item.type !== 'divider' && item.type !== 'header')
  
  if (props.searchable && searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    items = items.filter(item => {
      const label = getItemLabel(item).toLowerCase()
      const description = getItemDescription(item)?.toLowerCase() || ''
      return label.includes(query) || description.includes(query)
    })
  }
  
  return items
})

// Helper Methods
const getItemProperty = (item, property, accessor) => {
  if (typeof accessor === 'function') {
    return accessor(item)
  }
  return typeof item === 'object' ? item[accessor] : item
}

const getItemLabel = (item) => getItemProperty(item, 'label', props.itemLabel)
const getItemValue = (item) => getItemProperty(item, 'value', props.itemValue)
const getItemIcon = (item) => getItemProperty(item, 'icon', props.itemIcon)
const getItemDescription = (item) => getItemProperty(item, 'description', props.itemDescription)
const getItemBadge = (item) => getItemProperty(item, 'badge', props.itemBadge)
const getItemDisabled = (item) => getItemProperty(item, 'disabled', props.itemDisabled)
const getItemClass = (item) => getItemProperty(item, 'class', props.itemClass)

const getItemKey = (item, index) => {
  const value = getItemValue(item)
  return value !== undefined ? value : index
}

const isItemSelected = (item) => {
  const value = getItemValue(item)
  if (props.multiple) {
    return Array.isArray(props.modelValue) && props.modelValue.includes(value)
  }
  return props.modelValue === value
}

// Methods
const toggleDropdown = () => {
  if (props.disabled) return
  
  if (isOpen.value) {
    closeDropdown()
  } else {
    openDropdown()
  }
}

const openDropdown = () => {
  isOpen.value = true
  highlightedIndex.value = -1
  
  nextTick(() => {
    if (props.searchable && searchInputRef.value) {
      searchInputRef.value.focus()
    }
  })
  
  emit('open')
}

const closeDropdown = () => {
  isOpen.value = false
  searchQuery.value = ''
  highlightedIndex.value = -1
  emit('close')
}

const handleItemClick = (item, index) => {
  if (getItemDisabled(item)) return
  
  const value = getItemValue(item)
  
  if (props.multiple) {
    const currentValue = Array.isArray(props.modelValue) ? [...props.modelValue] : []
    const valueIndex = currentValue.indexOf(value)
    
    if (valueIndex > -1) {
      currentValue.splice(valueIndex, 1)
    } else {
      currentValue.push(value)
    }
    
    emit('update:modelValue', currentValue)
  } else {
    emit('update:modelValue', value)
  }
  
  emit('select', { item, value, index })
  
  if (props.closeOnSelect && !props.multiple) {
    closeDropdown()
  }
}

const handleKeydown = (event) => {
  switch (event.key) {
    case 'ArrowDown':
      event.preventDefault()
      if (!isOpen.value) {
        openDropdown()
      } else {
        highlightedIndex.value = Math.min(highlightedIndex.value + 1, filteredItems.value.length - 1)
      }
      break
      
    case 'ArrowUp':
      event.preventDefault()
      if (isOpen.value) {
        highlightedIndex.value = Math.max(highlightedIndex.value - 1, -1)
      }
      break
      
    case 'Enter':
    case ' ':
      event.preventDefault()
      if (!isOpen.value) {
        openDropdown()
      } else if (highlightedIndex.value >= 0) {
        handleItemClick(filteredItems.value[highlightedIndex.value], highlightedIndex.value)
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

const handleSearchKeydown = (event) => {
  if (event.key === 'ArrowDown') {
    event.preventDefault()
    highlightedIndex.value = 0
  } else if (event.key === 'ArrowUp') {
    event.preventDefault()
    highlightedIndex.value = filteredItems.value.length - 1
  } else if (event.key === 'Enter' && highlightedIndex.value >= 0) {
    event.preventDefault()
    handleItemClick(filteredItems.value[highlightedIndex.value], highlightedIndex.value)
  }
}

// Click outside handler
const handleClickOutside = (event) => {
  if (isOpen.value && dropdownRef.value && !dropdownRef.value.contains(event.target)) {
    closeDropdown()
  }
}

// Watchers
watch(searchQuery, (newQuery) => {
  highlightedIndex.value = -1
  emit('search', newQuery)
})

// Lifecycle
onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})

// Expose methods
defineExpose({
  open: openDropdown,
  close: closeDropdown,
  toggle: toggleDropdown
})
</script>

<style scoped>
/* Custom scrollbar for scrollable dropdowns */
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

/* Z-index for dropdown overlay */
.z-50 {
  z-index: 50;
}
</style>