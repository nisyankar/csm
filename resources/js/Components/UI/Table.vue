<template>
  <div :class="['w-full', containerClass]">
    <!-- Table Header Actions -->
    <div
      v-if="$slots.header || showSearch || showFilters"
      class="mb-4 flex items-center justify-between"
    >
      <div class="flex items-center space-x-4">
        <!-- Search -->
        <div v-if="showSearch" class="relative">
          <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
            </svg>
          </div>
          <input
            v-model="searchQuery"
            @input="handleSearch"
            type="text"
            :placeholder="searchPlaceholder"
            class="block w-64 pl-9 pr-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          />
        </div>

        <!-- Custom Header Slot -->
        <slot name="header" />
      </div>

      <!-- Table Actions -->
      <div v-if="$slots.actions" class="flex items-center space-x-2">
        <slot name="actions" />
      </div>
    </div>

    <!-- Selection Summary -->
    <div
      v-if="selectedRows.length > 0"
      class="mb-4 bg-blue-50 border border-blue-200 rounded-md p-3"
    >
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-2">
          <svg class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <span class="text-sm font-medium text-blue-900">
            {{ selectedRows.length }} öğe seçildi
          </span>
        </div>
        
        <div class="flex items-center space-x-2">
          <slot name="bulkActions" :selectedRows="selectedRows" />
          
          <button
            @click="clearSelection"
            class="text-sm text-blue-600 hover:text-blue-500"
          >
            Seçimi Temizle
          </button>
        </div>
      </div>
    </div>

    <!-- Table Container -->
    <div :class="['bg-white rounded-lg shadow overflow-hidden', tableContainerClass]">
      <!-- Loading State -->
      <div v-if="loading" class="p-8">
        <div class="animate-pulse space-y-4">
          <div v-for="i in loadingRows" :key="i" class="flex items-center space-x-4">
            <div v-if="selectable" class="w-4 h-4 bg-gray-200 rounded"></div>
            <div v-for="j in columns.length" :key="j" class="flex-1">
              <div class="h-4 bg-gray-200 rounded" :style="{ width: `${Math.random() * 40 + 60}%` }"></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div
        v-else-if="!filteredData.length"
        class="p-8 text-center"
      >
        <slot name="empty">
          <div class="flex flex-col items-center">
            <svg class="h-12 w-12 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
            </svg>
            <p class="text-lg font-medium text-gray-900 mb-1">{{ emptyTitle }}</p>
            <p class="text-sm text-gray-500">{{ emptyMessage }}</p>
          </div>
        </slot>
      </div>

      <!-- Table -->
      <div v-else :class="['overflow-x-auto', { 'max-h-96 overflow-y-auto': fixedHeight }]">
        <table class="min-w-full divide-y divide-gray-200">
          <!-- Table Header -->
          <thead :class="['bg-gray-50', headerClass]">
            <tr>
              <!-- Select All Checkbox -->
              <th v-if="selectable" class="px-6 py-3 text-left">
                <input
                  type="checkbox"
                  :checked="allSelected"
                  @change="toggleSelectAll"
                  class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                />
              </th>

              <!-- Column Headers -->
              <th
                v-for="column in columns"
                :key="column.key"
                @click="column.sortable ? toggleSort(column.key) : null"
                :class="[
                  'px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider',
                  column.sortable ? 'cursor-pointer hover:bg-gray-100 select-none' : '',
                  column.headerClass || ''
                ]"
                :style="column.width ? { width: column.width } : {}"
              >
                <div class="flex items-center space-x-1">
                  <span>{{ column.title }}</span>
                  
                  <!-- Sort Icons -->
                  <div v-if="column.sortable" class="flex flex-col">
                    <svg
                      :class="[
                        'h-3 w-3',
                        sortField === column.key && sortDirection === 'asc' 
                          ? 'text-blue-600' 
                          : 'text-gray-400'
                      ]"
                      fill="none"
                      viewBox="0 0 24 24"
                      stroke-width="2"
                      stroke="currentColor"
                    >
                      <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75l7.5-7.5 7.5 7.5" />
                    </svg>
                    <svg
                      :class="[
                        'h-3 w-3 -mt-1',
                        sortField === column.key && sortDirection === 'desc' 
                          ? 'text-blue-600' 
                          : 'text-gray-400'
                      ]"
                      fill="none"
                      viewBox="0 0 24 24"
                      stroke-width="2"
                      stroke="currentColor"
                    >
                      <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                    </svg>
                  </div>
                </div>
              </th>

              <!-- Actions Column -->
              <th v-if="$slots.rowActions" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                İşlemler
              </th>
            </tr>
          </thead>

          <!-- Table Body -->
          <tbody class="bg-white divide-y divide-gray-200">
            <tr
              v-for="(row, index) in paginatedData"
              :key="getRowKey(row, index)"
              :class="[
                'hover:bg-gray-50 transition-colors',
                isRowSelected(row) ? 'bg-blue-50' : '',
                rowClass ? rowClass(row, index) : ''
              ]"
              @click="handleRowClick(row, index)"
            >
              <!-- Select Checkbox -->
              <td v-if="selectable" class="px-6 py-4 whitespace-nowrap">
                <input
                  type="checkbox"
                  :checked="isRowSelected(row)"
                  @change="toggleRowSelection(row)"
                  @click.stop
                  class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                />
              </td>

              <!-- Data Cells -->
              <td
                v-for="column in columns"
                :key="column.key"
                :class="[
                  'px-6 py-4 whitespace-nowrap text-sm',
                  column.cellClass ? column.cellClass(getCellValue(row, column.key), row) : 'text-gray-900'
                ]"
              >
                <!-- Custom Cell Slot -->
                <slot
                  v-if="$slots[`cell-${column.key}`]"
                  :name="`cell-${column.key}`"
                  :value="getCellValue(row, column.key)"
                  :row="row"
                  :index="index"
                />
                
                <!-- Default Cell Content -->
                <span v-else>
                  {{ formatCellValue(getCellValue(row, column.key), column) }}
                </span>
              </td>

              <!-- Row Actions -->
              <td v-if="$slots.rowActions" class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <slot name="rowActions" :row="row" :index="index" />
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Table Footer -->
      <div
        v-if="showPagination && !loading && filteredData.length > 0"
        class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6"
      >
        <div class="flex items-center justify-between">
          <!-- Results Info -->
          <div class="text-sm text-gray-700">
            <span class="font-medium">{{ startIndex }}</span>
            -
            <span class="font-medium">{{ endIndex }}</span>
            /
            <span class="font-medium">{{ totalItems }}</span>
            sonuç gösteriliyor
          </div>

          <!-- Per Page Selection -->
          <div class="flex items-center space-x-2">
            <span class="text-sm text-gray-700">Sayfa başına:</span>
            <select
              v-model="currentPerPage"
              @change="handlePerPageChange"
              class="px-3 py-1 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
              <option v-for="option in perPageOptions" :key="option" :value="option">
                {{ option }}
              </option>
            </select>
          </div>

          <!-- Pagination Controls -->
          <div class="flex items-center space-x-1">
            <!-- Previous Button -->
            <button
              @click="goToPage(currentPage - 1)"
              :disabled="currentPage === 1"
              class="px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Önceki
            </button>

            <!-- Page Numbers -->
            <template v-for="page in visiblePages" :key="page">
              <button
                v-if="page !== '...'"
                @click="goToPage(page)"
                :class="[
                  'px-3 py-2 border text-sm font-medium rounded-md',
                  currentPage === page
                    ? 'border-blue-500 bg-blue-50 text-blue-600'
                    : 'border-gray-300 text-gray-700 bg-white hover:bg-gray-50'
                ]"
              >
                {{ page }}
              </button>
              <span v-else class="px-3 py-2 text-gray-500">...</span>
            </template>

            <!-- Next Button -->
            <button
              @click="goToPage(currentPage + 1)"
              :disabled="currentPage === totalPages"
              class="px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Sonraki
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, nextTick } from 'vue'
import { debounce } from 'lodash'

// Props
const props = defineProps({
  data: {
    type: Array,
    required: true
  },
  columns: {
    type: Array,
    required: true
  },
  loading: {
    type: Boolean,
    default: false
  },
  loadingRows: {
    type: Number,
    default: 5
  },
  selectable: {
    type: Boolean,
    default: false
  },
  sortable: {
    type: Boolean,
    default: true
  },
  searchable: {
    type: Boolean,
    default: true
  },
  showSearch: {
    type: Boolean,
    default: true
  },
  showFilters: {
    type: Boolean,
    default: false
  },
  showPagination: {
    type: Boolean,
    default: true
  },
  perPage: {
    type: Number,
    default: 10
  },
  perPageOptions: {
    type: Array,
    default: () => [10, 25, 50, 100]
  },
  fixedHeight: {
    type: Boolean,
    default: false
  },
  emptyTitle: {
    type: String,
    default: 'Veri Bulunamadı'
  },
  emptyMessage: {
    type: String,
    default: 'Henüz hiçbir kayıt eklenmemiş.'
  },
  searchPlaceholder: {
    type: String,
    default: 'Tabloda ara...'
  },
  rowKey: {
    type: [String, Function],
    default: 'id'
  },
  rowClass: {
    type: Function,
    default: null
  },
  // Custom classes
  containerClass: {
    type: String,
    default: ''
  },
  tableContainerClass: {
    type: String,
    default: ''
  },
  headerClass: {
    type: String,
    default: ''
  }
})

// Emits
const emit = defineEmits([
  'update:selection',
  'row-click',
  'sort-change',
  'search',
  'page-change',
  'per-page-change'
])

// State
const searchQuery = ref('')
const selectedRows = ref([])
const sortField = ref('')
const sortDirection = ref('asc')
const currentPage = ref(1)
const currentPerPage = ref(props.perPage)

// Computed
const filteredData = computed(() => {
  let data = [...props.data]

  // Apply search filter
  if (searchQuery.value && props.searchable) {
    const query = searchQuery.value.toLowerCase()
    data = data.filter(row => {
      return props.columns.some(column => {
        const value = getCellValue(row, column.key)
        return String(value).toLowerCase().includes(query)
      })
    })
  }

  // Apply sorting
  if (sortField.value && props.sortable) {
    data.sort((a, b) => {
      const aValue = getCellValue(a, sortField.value)
      const bValue = getCellValue(b, sortField.value)
      
      let comparison = 0
      if (aValue > bValue) comparison = 1
      if (aValue < bValue) comparison = -1
      
      return sortDirection.value === 'desc' ? -comparison : comparison
    })
  }

  return data
})

const totalItems = computed(() => filteredData.value.length)
const totalPages = computed(() => Math.ceil(totalItems.value / currentPerPage.value))

const paginatedData = computed(() => {
  if (!props.showPagination) return filteredData.value
  
  const start = (currentPage.value - 1) * currentPerPage.value
  const end = start + currentPerPage.value
  return filteredData.value.slice(start, end)
})

const startIndex = computed(() => {
  return totalItems.value === 0 ? 0 : (currentPage.value - 1) * currentPerPage.value + 1
})

const endIndex = computed(() => {
  return Math.min(currentPage.value * currentPerPage.value, totalItems.value)
})

const allSelected = computed(() => {
  return paginatedData.value.length > 0 && 
         paginatedData.value.every(row => isRowSelected(row))
})

const visiblePages = computed(() => {
  const pages = []
  const total = totalPages.value
  const current = currentPage.value
  
  if (total <= 7) {
    for (let i = 1; i <= total; i++) {
      pages.push(i)
    }
  } else {
    if (current <= 4) {
      for (let i = 1; i <= 5; i++) pages.push(i)
      pages.push('...')
      pages.push(total)
    } else if (current >= total - 3) {
      pages.push(1)
      pages.push('...')
      for (let i = total - 4; i <= total; i++) pages.push(i)
    } else {
      pages.push(1)
      pages.push('...')
      for (let i = current - 1; i <= current + 1; i++) pages.push(i)
      pages.push('...')
      pages.push(total)
    }
  }
  
  return pages
})

// Methods
const getCellValue = (row, key) => {
  return key.split('.').reduce((obj, key) => obj?.[key], row)
}

const formatCellValue = (value, column) => {
  if (column.formatter && typeof column.formatter === 'function') {
    return column.formatter(value)
  }
  return value ?? '-'
}

const getRowKey = (row, index) => {
  if (typeof props.rowKey === 'function') {
    return props.rowKey(row, index)
  }
  return getCellValue(row, props.rowKey) || index
}

const isRowSelected = (row) => {
  const key = getRowKey(row)
  return selectedRows.value.some(selectedRow => getRowKey(selectedRow) === key)
}

const toggleRowSelection = (row) => {
  const key = getRowKey(row)
  const index = selectedRows.value.findIndex(selectedRow => getRowKey(selectedRow) === key)
  
  if (index > -1) {
    selectedRows.value.splice(index, 1)
  } else {
    selectedRows.value.push(row)
  }
  
  emit('update:selection', selectedRows.value)
}

const toggleSelectAll = () => {
  if (allSelected.value) {
    // Deselect all current page items
    paginatedData.value.forEach(row => {
      const key = getRowKey(row)
      const index = selectedRows.value.findIndex(selectedRow => getRowKey(selectedRow) === key)
      if (index > -1) {
        selectedRows.value.splice(index, 1)
      }
    })
  } else {
    // Select all current page items
    paginatedData.value.forEach(row => {
      if (!isRowSelected(row)) {
        selectedRows.value.push(row)
      }
    })
  }
  
  emit('update:selection', selectedRows.value)
}

const clearSelection = () => {
  selectedRows.value = []
  emit('update:selection', selectedRows.value)
}

const toggleSort = (field) => {
  if (sortField.value === field) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortField.value = field
    sortDirection.value = 'asc'
  }
  
  emit('sort-change', { field: sortField.value, direction: sortDirection.value })
}

const handleRowClick = (row, index) => {
  emit('row-click', { row, index })
}

const handleSearch = debounce(() => {
  currentPage.value = 1 // Reset to first page on search
  emit('search', searchQuery.value)
}, 300)

const goToPage = (page) => {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page
    emit('page-change', page)
  }
}

const handlePerPageChange = () => {
  currentPage.value = 1 // Reset to first page
  emit('per-page-change', currentPerPage.value)
}

// Watchers
watch(() => props.data, () => {
  // Reset pagination when data changes
  currentPage.value = 1
})

watch(selectedRows, (newSelection) => {
  emit('update:selection', newSelection)
}, { deep: true })
</script>

<style scoped>
/* Custom scrollbar for fixed height tables */
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

/* Hover effects for sortable columns */
.cursor-pointer:hover .text-gray-400 {
  color: rgb(75 85 99); /* Tailwind text-gray-600 karşılığı */
}

/* Loading animation */
@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.5;
  }
}

.animate-pulse {
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>