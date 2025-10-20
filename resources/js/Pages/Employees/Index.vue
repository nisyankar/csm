<template>
  <AppLayout
    title="Çalışanlar - SPT İnşaat Puantaj Sistemi"
    :full-width="true"
  >
    <!-- Full-width header with improved design -->
    <template #fullWidthHeader>
      <div class="bg-gradient-to-r from-blue-600 via-blue-700 to-blue-800 border-b border-blue-900/20 w-full">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
          <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <!-- Header Info -->
            <div class="flex-1 min-w-0">
              <div class="flex items-center space-x-4 mb-3">
                <div class="flex-shrink-0 w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm">
                  <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                  </svg>
                </div>
                <div>
                  <h1 class="text-2xl lg:text-3xl font-bold text-white">Çalışan Yönetimi</h1>
                  <p class="text-blue-100 text-sm mt-1">Çalışan bilgilerini görüntüleyin ve yönetin</p>
                </div>
              </div>
              
              <!-- Stats Row -->
              <div class="flex flex-wrap items-center gap-4 lg:gap-6">
                <div class="bg-white/10 backdrop-blur-sm rounded-lg px-3 py-2">
                  <span class="text-blue-100 text-sm">Toplam:</span>
                  <span class="font-semibold text-white ml-1">{{ employeesData.total || 0 }}</span>
                </div>
                <div class="flex items-center space-x-4 text-sm">
                  <span class="flex items-center text-blue-100">
                    <div class="w-2 h-2 bg-green-400 rounded-full mr-2"></div>
                    Aktif: <span class="text-white font-medium ml-1">{{ getStatusCount('active') }}</span>
                  </span>
                  <span class="flex items-center text-blue-100">
                    <div class="w-2 h-2 bg-yellow-400 rounded-full mr-2"></div>
                    Beklemede: <span class="text-white font-medium ml-1">{{ getStatusCount('suspended') }}</span>
                  </span>
                  <span class="flex items-center text-blue-100">
                    <div class="w-2 h-2 bg-red-400 rounded-full mr-2"></div>
                    Pasif: <span class="text-white font-medium ml-1">{{ getStatusCount('inactive') }}</span>
                  </span>
                </div>
              </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="flex-shrink-0">
              <div class="flex flex-wrap items-center gap-3">
                <button
                  @click="showExportModal = true"
                  class="inline-flex items-center px-4 py-2 bg-white/10 backdrop-blur-sm border border-white/30 text-white text-sm font-medium rounded-lg hover:bg-white/20 transition-all duration-200"
                >
                  <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                  </svg>
                  Dışa Aktar
                </button>
                
                <Link
                  :href="route('employees.import')"
                  class="inline-flex items-center px-4 py-2 bg-white/10 backdrop-blur-sm border border-white/30 text-white text-sm font-medium rounded-lg hover:bg-white/20 transition-all duration-200"
                >
                  <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
                  </svg>
                  İçe Aktar
                </Link>
                
                <Link
                  :href="route('employees.create')"
                  class="inline-flex items-center px-4 py-2 bg-white text-blue-600 text-sm font-medium rounded-lg hover:bg-blue-50 shadow-lg hover:shadow-xl transition-all duration-200"
                >
                  <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                  </svg>
                  Yeni Çalışan
                </Link>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Breadcrumb inside header -->
        <div class="bg-black/10 border-t border-white/10">
          <div class="w-full px-4 sm:px-6 lg:px-8">
            <div class="flex items-center space-x-2 py-2">
              <ol class="flex items-center space-x-2">
                <!-- Home -->
                <li>
                  <Link
                    :href="route('dashboard')"
                    class="text-blue-100 hover:text-white transition-colors"
                  >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                    </svg>
                    <span class="sr-only">Ana Sayfa</span>
                  </Link>
                </li>

                <!-- Breadcrumb items -->
                <li v-for="(item, index) in breadcrumbs" :key="index" class="flex items-center">
                  <!-- Separator -->
                  <svg class="h-3 w-3 text-blue-200 mx-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                  </svg>

                  <!-- Breadcrumb item -->
                  <div class="flex items-center">
                    <Link
                      v-if="item.href && index < breadcrumbs.length - 1"
                      :href="item.href"
                      class="text-xs font-medium text-blue-100 hover:text-white transition-colors"
                    >
                      {{ item.label }}
                    </Link>
                    <span
                      v-else
                      class="text-xs font-medium text-white"
                      :aria-current="index === breadcrumbs.length - 1 ? 'page' : undefined"
                    >
                      {{ item.label }}
                    </span>
                  </div>
                </li>
              </ol>
            </div>
          </div>
        </div>
      </div>
    </template>

    <!-- Main Content Container -->
    <div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
      <!-- Advanced Filters Panel -->
      <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
          <div class="flex items-center justify-between">
            <h3 class="text-lg font-medium text-gray-900">Arama ve Filtreler</h3>
            <button
              @click="showAdvancedFilters = !showAdvancedFilters"
              class="flex items-center text-sm text-blue-600 hover:text-blue-800 transition-colors"
            >
              <svg 
                :class="['h-4 w-4 mr-1 transition-transform', showAdvancedFilters ? 'rotate-180' : '']"
                fill="none" 
                viewBox="0 0 24 24" 
                stroke-width="1.5" 
                stroke="currentColor"
              >
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
              </svg>
              {{ showAdvancedFilters ? 'Gizle' : 'Gelişmiş Filtreler' }}
            </button>
          </div>
        </div>

        <div class="p-6">
          <!-- Primary Filters -->
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-6 gap-4 mb-6">
            <!-- Search -->
            <div class="lg:col-span-2 xl:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-2">Genel Arama</label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                  </svg>
                </div>
                <input
                  v-model="filters.search"
                  @input="debouncedSearch"
                  type="text"
                  placeholder="Ad, soyad, sicil no, email ara..."
                  class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                />
              </div>
            </div>

            <!-- Department Filter -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Departman</label>
              <select
                v-model="filters.department"
                @change="applyFilters"
                class="block w-full px-3 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
              >
                <option value="">Tümü</option>
                <option v-for="dept in uniqueDepartments" :key="dept.id" :value="dept.id">
                  {{ dept.name }}
                </option>
              </select>
            </div>

            <!-- Status Filter -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Durum</label>
              <select
                v-model="filters.status"
                @change="applyFilters"
                class="block w-full px-3 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
              >
                <option value="">Tümü</option>
                <option value="active">Aktif</option>
                <option value="inactive">Pasif</option>
                <option value="suspended">Askıya Alınmış</option>
              </select>
            </div>

            <!-- Quick Actions -->
            <div class="xl:col-span-2 flex items-end space-x-2">
              <Button
                variant="outline"
                size="md"
                @click="clearAllFilters"
                :disabled="!hasActiveFilters"
                class="flex-1"
              >
                Filtreleri Temizle
              </Button>
              <Button
                variant="primary"
                size="md"
                @click="applyFilters"
                class="flex-1"
              >
                Ara
              </Button>
            </div>
          </div>

          <!-- Advanced Filters (Collapsible) -->
          <transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="opacity-0 -translate-y-4"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 -translate-y-4"
          >
            <div v-show="showAdvancedFilters" class="border-t border-gray-200 pt-6">
              <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                <!-- Project Filter -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Proje</label>
                  <select
                    v-model="filters.project"
                    @change="applyFilters"
                    class="block w-full px-3 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                  >
                    <option value="">Tümü</option>
                    <option v-for="project in uniqueProjects" :key="project.id" :value="project.id">
                      {{ project.name }}
                    </option>
                  </select>
                </div>

                <!-- Position Filter -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Pozisyon</label>
                  <select
                    v-model="filters.position"
                    @change="applyFilters"
                    class="block w-full px-3 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                  >
                    <option value="">Tümü</option>
                    <option v-for="position in uniquePositions" :key="position" :value="position">
                      {{ position }}
                    </option>
                  </select>
                </div>

                <!-- Hire Date Range -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">İşe Başlama (Başlangıç)</label>
                  <input
                    v-model="filters.hire_date_from"
                    @change="applyFilters"
                    type="date"
                    class="block w-full px-3 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                  />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">İşe Başlama (Bitiş)</label>
                  <input
                    v-model="filters.hire_date_to"
                    @change="applyFilters"
                    type="date"
                    class="block w-full px-3 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                  />
                </div>
              </div>
            </div>
          </transition>

          <!-- Active Filters Display -->
          <div v-if="hasActiveFilters" class="mt-6 pt-6 border-t border-gray-200">
            <div class="flex flex-wrap items-center gap-2">
              <span class="text-sm font-medium text-gray-700">Aktif filtreler:</span>
              <span
                v-for="filter in activeFiltersList"
                :key="filter.key"
                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 border border-blue-200"
              >
                {{ filter.label }}
                <button
                  @click="removeFilter(filter.key)"
                  class="ml-2 inline-flex items-center justify-center w-4 h-4 rounded-full text-blue-600 hover:bg-blue-200 hover:text-blue-800 transition-colors"
                >
                  <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Data Table Panel -->
      <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
        <!-- Table Controls -->
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-4 sm:space-y-0">
            <div class="flex items-center space-x-4">
              <!-- Bulk Actions -->
              <div v-if="selectedEmployees.length > 0" class="flex items-center space-x-3">
                <span class="text-sm font-medium text-gray-700">
                  {{ selectedEmployees.length }} çalışan seçildi
                </span>
                <Button
                  variant="outline"
                  size="sm"
                  @click="showBulkActionsModal = true"
                >
                  Toplu İşlemler
                </Button>
              </div>
              
              <!-- View Toggle -->
              <div v-else class="flex items-center space-x-1 bg-gray-100 rounded-lg p-1">
                <button
                  @click="viewMode = 'table'"
                  :class="[
                    'px-3 py-2 rounded-md text-sm font-medium transition-all',
                    viewMode === 'table'
                      ? 'bg-white text-gray-900 shadow-sm'
                      : 'text-gray-600 hover:text-gray-900'
                  ]"
                >
                  <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.375 19.5h17.25m-17.25 0a1.125 1.125 0 0 1-1.125-1.125M3.375 19.5h17.25m-17.25 0a1.125 1.125 0 0 1-1.125-1.125M3.375 19.5a1.125 1.125 0 0 0 1.125 1.125m0 0h17.25A1.125 1.125 0 0 0 21.75 18.375M3.375 19.5A1.125 1.125 0 0 0 4.5 18.375m17.25 0A1.125 1.125 0 0 0 21.75 19.5M4.5 18.375A1.125 1.125 0 0 1 3.375 17.25M21.75 17.25A1.125 1.125 0 0 1 20.625 18.375M3.375 17.25a1.125 1.125 0 0 0 1.125 1.125m17.25-1.125a1.125 1.125 0 0 0-1.125 1.125" />
                  </svg>
                </button>
                <button
                  @click="viewMode = 'grid'"
                  :class="[
                    'px-3 py-2 rounded-md text-sm font-medium transition-all',
                    viewMode === 'grid'
                      ? 'bg-white text-gray-900 shadow-sm'
                      : 'text-gray-600 hover:text-gray-900'
                  ]"
                >
                  <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.375 19.5h4.5m0 0v-15m0 15h13.125m-13.125 0a1.125 1.125 0 0 1-1.125-1.125V5.625a1.125 1.125 0 0 1 1.125-1.125m1.125 13.125h13.125A1.125 1.125 0 0 0 21.75 18.375V5.625A1.125 1.125 0 0 0 20.625 4.5H5.625A1.125 1.125 0 0 0 4.5 5.625v12.75z" />
                  </svg>
                </button>
              </div>
            </div>

            <!-- Results Info and Pagination Controls -->
            <div class="flex items-center space-x-4">
              <div class="text-sm text-gray-700">
                <span class="font-medium">{{ employeesData.from || 0 }}-{{ employeesData.to || 0 }}</span>
                / {{ employeesData.total || 0 }} sonuç
              </div>
              
              <div class="flex items-center space-x-2">
                <span class="text-sm text-gray-700">Sayfa başına:</span>
                <select
                  v-model="employeesData.per_page"
                  @change="changePerPage"
                  class="px-2 py-1 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                >
                  <option :value="10">10</option>
                  <option :value="25">25</option>
                  <option :value="50">50</option>
                  <option :value="100">100</option>
                </select>
              </div>
            </div>
          </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="p-8">
          <div class="animate-pulse space-y-4">
            <div v-for="i in 8" :key="i" class="flex items-center space-x-4">
              <div class="w-10 h-10 bg-gray-200 rounded-full"></div>
              <div class="flex-1 space-y-2">
                <div class="h-4 bg-gray-200 rounded w-3/4"></div>
                <div class="h-3 bg-gray-200 rounded w-1/2"></div>
              </div>
              <div class="w-20 h-8 bg-gray-200 rounded"></div>
            </div>
          </div>
        </div>

        <!-- Table View -->
        <div v-else-if="viewMode === 'table'" class="w-full overflow-x-auto">
          <table class="w-full min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-4 text-left">
                  <input
                    type="checkbox"
                    :checked="allSelected"
                    @change="toggleSelectAll"
                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                  />
                </th>
                <th
                  v-for="column in tableColumns"
                  :key="column.key"
                  @click="column.sortable ? sort(column.key) : null"
                  :class="[
                    'px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider',
                    column.sortable ? 'cursor-pointer hover:bg-gray-100 select-none' : ''
                  ]"
                >
                  <div class="flex items-center space-x-1">
                    <span>{{ column.label }}</span>
                    <div v-if="column.sortable" class="flex flex-col">
                      <svg
                        :class="[
                          'h-3 w-3 transition-colors',
                          sortField === column.key && sortDirection === 'asc' ? 'text-blue-600' : 'text-gray-300'
                        ]"
                        fill="currentColor"
                        viewBox="0 0 20 20"
                      >
                        <path d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" />
                      </svg>
                      <svg
                        :class="[
                          'h-3 w-3 -mt-1 transition-colors',
                          sortField === column.key && sortDirection === 'desc' ? 'text-blue-600' : 'text-gray-300'
                        ]"
                        fill="currentColor"
                        viewBox="0 0 20 20"
                      >
                        <path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" />
                      </svg>
                    </div>
                  </div>
                </th>
                <th class="px-6 py-4 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                  İşlemler
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr
                v-for="employee in employeesList"
                :key="employee.id"
                :class="[
                  'hover:bg-gray-50 transition-colors',
                  selectedEmployees.includes(employee.id) ? 'bg-blue-50 border-blue-200' : ''
                ]"
              >
                <td class="px-6 py-4">
                  <input
                    type="checkbox"
                    :checked="selectedEmployees.includes(employee.id)"
                    @change="toggleSelectEmployee(employee.id)"
                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                  />
                </td>
                
                <!-- Employee Info -->
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 h-12 w-12">
                      <img
                        v-if="employee.avatar"
                        :src="employee.avatar"
                        :alt="employee.full_name"
                        class="h-12 w-12 rounded-full object-cover border-2 border-gray-200"
                      />
                      <div
                        v-else
                        class="h-12 w-12 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center border-2 border-gray-200"
                      >
                        <span class="text-sm font-semibold text-white">
                          {{ employee.initials || (employee.first_name ? employee.first_name.charAt(0) + (employee.last_name ? employee.last_name.charAt(0) : '') : 'NN') }}
                        </span>
                      </div>
                    </div>
                    <div class="ml-4">
                      <div class="text-base font-semibold text-gray-900">
                        <Link
                          :href="route('employees.show', employee.id)"
                          class="hover:text-blue-600 transition-colors"
                          @click="handleShowClick(employee)"
                        >
                          {{ employee.full_name || `${employee.first_name || ''} ${employee.last_name || ''}`.trim() }}
                        </Link>
                      </div>
                      <div class="text-sm text-gray-500">{{ employee.email || '-' }}</div>
                    </div>
                  </div>
                </td>

                <!-- Employee Code -->
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-mono text-gray-900 bg-gray-100 px-2 py-1 rounded">
                    {{ employee.employee_code || '-' }}
                  </div>
                </td>

                <!-- Department -->
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900">
                    {{ employee.department?.name || '-' }}
                  </div>
                </td>

                <!-- Position -->
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900">{{ employee.position || '-' }}</div>
                </td>

                <!-- Status -->
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="[
                    'inline-flex px-3 py-1 text-xs font-semibold rounded-full',
                    getStatusClasses(employee.status)
                  ]">
                    {{ getStatusLabel(employee.status) }}
                  </span>
                </td>

                <!-- Hire Date -->
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ formatDate(employee.hire_date) }}
                </td>

                <!-- Actions -->
                <td class="px-6 py-4 whitespace-nowrap text-right">
                  <div class="flex items-center justify-end space-x-2">
                    <Button
                      variant="ghost"
                      size="sm"
                      :href="route('employees.show', employee.id)"
                      title="Görüntüle"
                      @click="handleShowClick(employee)"
                    >
                      <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                      </svg>
                    </Button>
                    
                    <Button
                      variant="ghost"
                      size="sm"
                      :href="route('employees.edit', employee.id)"
                      title="Düzenle"
                    >
                      <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                      </svg>
                    </Button>

                    <!-- QR Code Button -->
                    <Button
                      variant="ghost"
                      size="sm"
                      @click="generateQRCode(employee)"
                      title="QR Kod Üret"
                    >
                      <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 3.75 9.375v-4.5ZM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 0 1-1.125-1.125v-4.5ZM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 13.5 9.375v-4.5Z" />
                      </svg>
                    </Button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>

          <!-- Empty State for Table -->
          <div v-if="employeesList.length === 0" class="text-center py-16">
            <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
            </svg>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Çalışan bulunamadı</h3>
            <p class="text-gray-500 mb-6">
              {{ hasActiveFilters ? 'Filtrelere uygun çalışan bulunamadı.' : 'Henüz hiç çalışan eklenmemiş.' }}
            </p>
            <div class="space-y-3">
              <Button
                v-if="!hasActiveFilters"
                variant="primary"
                :href="route('employees.create')"
                size="lg"
              >
                <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                İlk Çalışanı Ekle
              </Button>
              <Button
                v-else
                variant="outline"
                @click="clearAllFilters"
                size="lg"
              >
                Filtreleri Temizle
              </Button>
            </div>
          </div>
        </div>

        <!-- Grid View -->
        <div v-else-if="viewMode === 'grid'" class="p-6">
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-6">
            <div
              v-for="employee in employeesList"
              :key="employee.id"
              :class="[
                'relative bg-white border-2 rounded-xl p-6 hover:shadow-lg transition-all duration-200 transform hover:-translate-y-1',
                selectedEmployees.includes(employee.id) 
                  ? 'border-blue-500 bg-blue-50' 
                  : 'border-gray-200 hover:border-gray-300'
              ]"
            >
              <!-- Selection checkbox -->
              <div class="absolute top-4 right-4">
                <input
                  type="checkbox"
                  :checked="selectedEmployees.includes(employee.id)"
                  @change="toggleSelectEmployee(employee.id)"
                  class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                />
              </div>

              <!-- Employee Avatar -->
              <div class="flex flex-col items-center text-center">
                <div class="flex-shrink-0 h-20 w-20 mb-4">
                  <img
                    v-if="employee.avatar"
                    :src="employee.avatar"
                    :alt="employee.full_name"
                    class="h-20 w-20 rounded-full object-cover border-4 border-gray-200"
                  />
                  <div
                    v-else
                    class="h-20 w-20 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center border-4 border-gray-200"
                  >
                    <span class="text-lg font-bold text-white">
                      {{ employee.initials || (employee.first_name ? employee.first_name.charAt(0) + (employee.last_name ? employee.last_name.charAt(0) : '') : 'NN') }}
                    </span>
                  </div>
                </div>

                <!-- Employee Info -->
                <div class="w-full">
                  <h3 class="text-lg font-semibold text-gray-900 mb-1 truncate">
                    <Link
                      :href="route('employees.show', employee.id)"
                      class="hover:text-blue-600 transition-colors"
                      @click="handleShowClick(employee)"
                    >
                      {{ employee.full_name || `${employee.first_name || ''} ${employee.last_name || ''}`.trim() }}
                    </Link>
                  </h3>
                  
                  <div class="text-sm text-gray-500 mb-2 truncate">{{ employee.email || '-' }}</div>
                  
                  <div class="space-y-2">
                    <!-- Employee Code -->
                    <div class="text-xs font-mono text-gray-700 bg-gray-100 px-2 py-1 rounded">
                      {{ employee.employee_code || '-' }}
                    </div>
                    
                    <!-- Department and Position -->
                    <div class="text-sm text-gray-700">
                      <div class="truncate">{{ employee.department?.name || employee.department_name || '-' }}</div>
                      <div class="truncate text-xs text-gray-500">{{ employee.position || '-' }}</div>
                    </div>
                    
                    <!-- Status -->
                    <div class="flex justify-center">
                      <span :class="[
                        'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                        getStatusClasses(employee.status)
                      ]">
                        {{ getStatusLabel(employee.status) }}
                      </span>
                    </div>
                    
                    <!-- Hire Date -->
                    <div class="text-xs text-gray-500">
                      İşe başlama: {{ formatDate(employee.hire_date) }}
                    </div>
                  </div>
                </div>

                <!-- Actions -->
                <div class="mt-4 flex justify-center space-x-2 w-full">
                  <Button
                    variant="outline"
                    size="sm"
                    :href="route('employees.show', employee.id)"
                    class="flex-1"
                    @click="handleShowClick(employee)"
                  >
                    Görüntüle
                  </Button>
                  <Button
                    variant="outline"
                    size="sm"
                    :href="route('employees.edit', employee.id)"
                    class="flex-1"
                  >
                    Düzenle
                  </Button>
                  <Button
                    variant="ghost"
                    size="sm"
                    @click="generateQRCode(employee)"
                    title="QR Kod"
                  >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 3.75 9.375v-4.5ZM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 0 1-1.125-1.125v-4.5ZM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 13.5 9.375v-4.5Z" />
                    </svg>
                  </Button>
                </div>
              </div>
            </div>
          </div>

          <!-- Empty State for Grid -->
          <div v-if="employeesList.length === 0" class="text-center py-16">
            <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
            </svg>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Çalışan bulunamadı</h3>
            <p class="text-gray-500 mb-6">
              {{ hasActiveFilters ? 'Filtrelere uygun çalışan bulunamadı.' : 'Henüz hiç çalışan eklenmemiş.' }}
            </p>
            <div class="space-y-3">
              <Button
                v-if="!hasActiveFilters"
                variant="primary"
                :href="route('employees.create')"
                size="lg"
              >
                <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                İlk Çalışanı Ekle
              </Button>
              <Button
                v-else
                variant="outline"
                @click="clearAllFilters"
                size="lg"
              >
                Filtreleri Temizle
              </Button>
            </div>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="employeesList.length > 0" class="bg-gray-50 px-6 py-4 border-t border-gray-200">
          <Pagination
            :pagination="employeesData"
            @page-changed="changePage"
          />
        </div>
      </div>
    </div>

    <!-- Modals -->
    <ExportModal
      v-if="showExportModal"
      :show="showExportModal"
      @close="showExportModal = false"
      @export="handleExport"
    />

    <BulkActionsModal
      v-if="showBulkActionsModal"
      :show="showBulkActionsModal"
      :selected-count="selectedEmployees.length"
      @close="showBulkActionsModal = false"
      @action="handleBulkAction"
    />

    <QRCodeModal
      v-if="showQRModal"
      :show="showQRModal"
      :employee="selectedEmployeeForQR"
      @close="closeQRModal"
    />
  </AppLayout>
</template>

<script setup>
import { ref, reactive, computed, watch, onMounted } from 'vue'
import { router, usePage, Link } from '@inertiajs/vue3'
import { debounce, uniqBy } from 'lodash'
import { format } from 'date-fns'
import { tr } from 'date-fns/locale'
import AppLayout from '@/Layouts/AppLayout.vue'
import Button from '@/Components/UI/Button.vue'
import Pagination from '@/Components/UI/Pagination.vue'

// Dummy components - replace with actual ones when they exist
const ExportModal = { template: '<div></div>' }
const BulkActionsModal = { template: '<div></div>' }
const QRCodeModal = { template: '<div></div>' }

// Props from controller - Fixed to handle paginated data
const props = defineProps({
  employees: {
    type: Object, // Changed from Array to Object for paginated data
    default: () => ({})
  },
  departments: {
    type: Array,
    default: () => []
  },
  projects: {
    type: Array,
    default: () => []
  },
  positions: {
    type: Array,
    default: () => []
  },
  filters: {
    type: Object,
    default: () => ({})
  }
})

const page = usePage()

// State
const loading = ref(false)
const viewMode = ref('table')
const showAdvancedFilters = ref(false)
const showExportModal = ref(false)
const showBulkActionsModal = ref(false)
const showQRModal = ref(false)
const selectedEmployees = ref([])
const selectedEmployeeForQR = ref(null)

// Filter state
const filters = reactive({
  search: props.filters.search || '',
  department: props.filters.department || '',
  status: props.filters.status || '',
  project: props.filters.project || '',
  position: props.filters.position || '',
  hire_date_from: props.filters.hire_date_from || '',
  hire_date_to: props.filters.hire_date_to || ''
})

// Sorting
const sortField = ref(props.filters.sort || 'first_name')
const sortDirection = ref(props.filters.direction || 'asc')

// Breadcrumbs
const breadcrumbs = [
  { label: 'Çalışanlar' }
]

// Table columns
const tableColumns = [
  { key: 'full_name', label: 'Ad Soyad', sortable: true },
  { key: 'employee_code', label: 'Sicil No', sortable: true },
  { key: 'department', label: 'Departman', sortable: false },
  { key: 'position', label: 'Pozisyon', sortable: true },
  { key: 'status', label: 'Durum', sortable: true },
  { key: 'hire_date', label: 'İşe Başlama', sortable: true }
]

// Computed properties to handle paginated data
const employeesData = computed(() => {
  // If employees is an object with pagination data
  if (props.employees && typeof props.employees === 'object' && props.employees.data) {
    return props.employees
  }
  // If employees is a simple array
  if (Array.isArray(props.employees)) {
    return {
      data: props.employees,
      total: props.employees.length,
      per_page: props.employees.length,
      current_page: 1,
      last_page: 1,
      from: 1,
      to: props.employees.length
    }
  }
  // Default empty state
  return {
    data: [],
    total: 0,
    per_page: 10,
    current_page: 1,
    last_page: 1,
    from: 0,
    to: 0
  }
})

// Unique departments - NAME'e göre unique yap
const uniqueDepartments = computed(() => {
  return uniqBy(props.departments, 'name')
})

// Unique projects - NAME'e göre unique yap  
const uniqueProjects = computed(() => {
  return uniqBy(props.projects, 'name')
})

// Unique positions - Array'den unique değerler
const uniquePositions = computed(() => {
  return [...new Set(props.positions)]
})

const employeesList = computed(() => {
  return employeesData.value.data || []
})

const allSelected = computed(() => {
  return employeesList.value.length > 0 && selectedEmployees.value.length === employeesList.value.length
})

const hasActiveFilters = computed(() => {
  return Object.values(filters).some(value => value !== '' && value !== null)
})

const activeFiltersList = computed(() => {
  const list = []
  
  if (filters.search) {
    list.push({ key: 'search', label: `Arama: "${filters.search}"` })
  }
  
  if (filters.department) {
    const dept = uniqueDepartments.value.find(d => d.id == filters.department)
    list.push({ key: 'department', label: `Departman: ${dept?.name}` })
  }
  
  if (filters.status) {
    list.push({ key: 'status', label: `Durum: ${getStatusLabel(filters.status)}` })
  }
  
  if (filters.project) {
    const project = uniqueProjects.value.find(p => p.id == filters.project)
    list.push({ key: 'project', label: `Proje: ${project?.name}` })
  }
  
  if (filters.position) {
    list.push({ key: 'position', label: `Pozisyon: ${filters.position}` })
  }
  
  if (filters.hire_date_from) {
    list.push({ key: 'hire_date_from', label: `Tarih (Başlangıç): ${formatDate(filters.hire_date_from)}` })
  }
  
  if (filters.hire_date_to) {
    list.push({ key: 'hire_date_to', label: `Tarih (Bitiş): ${formatDate(filters.hire_date_to)}` })
  }
  
  return list
})

// Methods
const debouncedSearch = debounce(() => {
  applyFilters()
}, 300)

const applyFilters = () => {
  const params = {
    ...filters,
    sort: sortField.value,
    direction: sortDirection.value,
    page: 1,
    per_page: employeesData.value.per_page || 10
  }
  
  // Remove empty values
  Object.keys(params).forEach(key => {
    if (params[key] === '' || params[key] === null) {
      delete params[key]
    }
  })
  
  loading.value = true
  router.get(route('employees.index'), params, {
    preserveState: true,
    preserveScroll: true,
    onSuccess: () => {
      loading.value = false
    },
    onError: () => {
      loading.value = false
    }
  })
}

const sort = (field) => {
  if (sortField.value === field) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortField.value = field
    sortDirection.value = 'asc'
  }
  applyFilters()
}

const changePage = (page) => {
  const params = {
    ...filters,
    sort: sortField.value,
    direction: sortDirection.value,
    page: page,
    per_page: employeesData.value.per_page || 10
  }
  
  // Remove empty values
  Object.keys(params).forEach(key => {
    if (params[key] === '' || params[key] === null) {
      delete params[key]
    }
  })
  
  router.get(route('employees.index'), params, {
    preserveState: true,
    preserveScroll: true
  })
}

const changePerPage = () => {
  applyFilters()
}

const removeFilter = (key) => {
  filters[key] = ''
  applyFilters()
}

const clearAllFilters = () => {
  Object.keys(filters).forEach(key => {
    filters[key] = ''
  })
  applyFilters()
}

const toggleSelectAll = () => {
  if (allSelected.value) {
    selectedEmployees.value = []
  } else {
    selectedEmployees.value = employeesList.value.map(emp => emp.id)
  }
}

const toggleSelectEmployee = (id) => {
  const index = selectedEmployees.value.indexOf(id)
  if (index > -1) {
    selectedEmployees.value.splice(index, 1)
  } else {
    selectedEmployees.value.push(id)
  }
}

const handleShowClick = (employee) => {
  console.log('Redirecting to employee show page:', employee.id)
  router.visit(route('employees.show', employee.id))
}

const generateQRCode = (employee) => {
  selectedEmployeeForQR.value = employee
  showQRModal.value = true
}

const closeQRModal = () => {
  showQRModal.value = false
  selectedEmployeeForQR.value = null
}

const handleExport = (format, options) => {
  // Implementation for export functionality
  console.log('Export:', format, options)
  
  const params = {
    format: format,
    ...filters,
    selected_ids: selectedEmployees.value.length > 0 ? selectedEmployees.value : null
  }
  
  // Remove empty values
  Object.keys(params).forEach(key => {
    if (params[key] === '' || params[key] === null) {
      delete params[key]
    }
  })
  
  router.get(route('employees.export'), params, {
    onSuccess: () => {
      showExportModal.value = false
    },
    onError: (errors) => {
      console.error('Export failed:', errors)
    }
  })
}

const handleBulkAction = (action) => {
  // Implementation for bulk actions
  
  const params = {
    action: action,
    employee_ids: selectedEmployees.value
  }
  
  router.post(route('employees.bulk-action'), params, {
    onSuccess: () => {
      showBulkActionsModal.value = false
      selectedEmployees.value = []
      // Refresh the page data
      router.reload({ only: ['employees'] })
    },
    onError: (errors) => {
      console.error('Bulk action failed:', errors)
    }
  })
}

// Helper functions
const getStatusClasses = (status) => {
  const classes = {
    active: 'bg-green-100 text-green-800 border border-green-200',
    inactive: 'bg-red-100 text-red-800 border border-red-200',
    suspended: 'bg-yellow-100 text-yellow-800 border border-yellow-200',
    terminated: 'bg-gray-100 text-gray-800 border border-gray-200'
  }
  return classes[status] || classes.active
}

const getStatusLabel = (status) => {
  const labels = {
    active: 'Aktif',
    inactive: 'Pasif',
    suspended: 'Askıya Alınmış',
    terminated: 'İşten Çıkarılmış'
  }
  return labels[status] || 'Aktif'
}

const getStatusCount = (status) => {
  return employeesList.value.filter(emp => emp.status === status).length
}

const formatDate = (date) => {
  if (!date) return '-'
  try {
    return format(new Date(date), 'dd MMM yyyy', { locale: tr })
  } catch (error) {
    return date // Return original if formatting fails
  }
}

// Lifecycle
onMounted(() => {
  // Initialize any needed data
  console.log('Employees Index mounted')
})

// Watch for prop changes (for when Inertia updates the data)
watch(() => props.employees, (newEmployees) => {
  console.log('Employees data updated:', newEmployees)
  // Clear selections when data changes
  selectedEmployees.value = []
}, { deep: true })

// Export for debugging in development
if (import.meta.env.DEV) {
  window.employeesDebug = {
    employeesData,
    employeesList,
    filters,
    props
  }
}
</script>

<style scoped>
/* Responsive Layout Fixes */
.w-full {
  width: 100% !important;
}

/* Header button fixes */
@media (max-width: 640px) {
  .action-buttons .flex-col {
    gap: 0.5rem;
  }
  
  .action-buttons button {
    min-width: 0;
    justify-content: center;
  }
}

/* Breadcrumb positioning fix */
.breadcrumb-container {
  margin-left: 0;
  margin-right: 0;
  padding-left: 1rem;
  padding-right: 1rem;
}

@media (min-width: 640px) {
  .breadcrumb-container {
    padding-left: 1.5rem;
    padding-right: 1.5rem;
  }
}

@media (min-width: 1024px) {
  .breadcrumb-container {
    padding-left: 2rem;
    padding-right: 2rem;
  }
}

/* Custom scrollbar for table */
.overflow-x-auto::-webkit-scrollbar {
  height: 6px;
}

.overflow-x-auto::-webkit-scrollbar-track {
  background: #f1f5f9;
}

.overflow-x-auto::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 3px;
}

.overflow-x-auto::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}

/* Loading animation */
.loading-skeleton {
  background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
  background-size: 200% 100%;
  animation: loading 1.5s infinite;
}

@keyframes loading {
  0% {
    background-position: 200% 0;
  }
  100% {
    background-position: -200% 0;
  }
}

/* Transition improvements */
.transition-all {
  transition-property: all;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 300ms;
}

/* Mobile responsive table */
@media (max-width: 768px) {
  .table-responsive table {
    font-size: 0.875rem;
  }
  
  .table-responsive th,
  .table-responsive td {
    padding: 0.5rem 0.75rem;
  }
}

/* Grid responsive improvements */
@media (min-width: 1536px) {
  .grid-cols-1.sm\\:grid-cols-2.lg\\:grid-cols-3.xl\\:grid-cols-4.\\32xl\\:grid-cols-5 {
    grid-template-columns: repeat(6, minmax(0, 1fr));
  }
}

/* Button responsive fixes */
.action-buttons {
  flex-shrink: 0;
}

.action-buttons button {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

/* Focus states for accessibility */
.focus\\:ring-2:focus {
  outline: 2px solid transparent;
  outline-offset: 2px;
  box-shadow: 0 0 0 2px var(--tw-ring-color);
}

/* Reduced motion support */
@media (prefers-reduced-motion: reduce) {
  .transition-all,
  .transition-colors,
  .transition-transform {
    transition: none;
  }
  
  .animate-pulse {
    animation: none;
  }
  
  .loading-skeleton {
    animation: none;
    background: #f0f0f0;
  }
}

/* High contrast mode support */
@media (prefers-contrast: high) {
  .bg-gradient-to-r {
    background: #1e40af;
  }
  
  .border-gray-200 {
    border-color: #000;
  }
  
  .text-gray-500 {
    color: #000;
  }
}
</style>