<template>
  <AppLayout :title="`${project.name} - Satış Durumu`" :full-width="true">
    <!-- Header -->
    <template #fullWidthHeader>
      <div class="bg-gradient-to-r from-slate-800 via-slate-700 to-gray-800 border-b border-slate-900/20 w-full">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
          <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <!-- Header Info -->
            <div class="flex-1 min-w-0">
              <div class="flex items-center space-x-4 mb-3">
                <div class="flex-shrink-0 w-12 h-12 bg-emerald-600 rounded-xl flex items-center justify-center shadow-lg">
                  <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                  </svg>
                </div>
                <div>
                  <h1 class="text-2xl lg:text-3xl font-bold text-white">
                    {{ project.name }} - Satış Durumu
                  </h1>
                  <p class="text-slate-300 text-sm mt-1">Proje: {{ project.code }}</p>
                </div>
              </div>

              <!-- Stats Summary -->
              <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                <div class="bg-white/10 backdrop-blur-sm rounded-lg p-3 border border-white/20">
                  <div class="text-xs text-slate-200">Toplam Birim</div>
                  <div class="text-xl font-bold text-white">{{ stats.total_units }}</div>
                </div>
                <div class="bg-emerald-500/20 backdrop-blur-sm rounded-lg p-3 border border-emerald-400/30">
                  <div class="text-xs text-emerald-100">Satılan</div>
                  <div class="text-xl font-bold text-white">{{ stats.sold_units }}</div>
                </div>
                <div class="bg-amber-500/20 backdrop-blur-sm rounded-lg p-3 border border-amber-400/30">
                  <div class="text-xs text-amber-100">Rezerve</div>
                  <div class="text-xl font-bold text-white">{{ stats.reserved_units }}</div>
                </div>
                <div class="bg-sky-500/20 backdrop-blur-sm rounded-lg p-3 border border-sky-400/30">
                  <div class="text-xs text-sky-100">Müsait</div>
                  <div class="text-xl font-bold text-white">{{ stats.available_units }}</div>
                </div>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex-shrink-0 flex items-center space-x-3">
              <Link
                :href="route('sales.sales-status.index')"
                class="inline-flex items-center px-4 py-2 bg-white/10 backdrop-blur-sm border border-white/30 text-white text-sm font-medium rounded-lg hover:bg-white/20 transition-all duration-200"
              >
                <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>
                Proje Listesi
              </Link>
            </div>
          </div>
        </div>

        <!-- Progress Bar -->
        <div class="bg-black/10 border-t border-white/10 px-4 sm:px-6 lg:px-8 py-4">
          <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-medium text-white">Satış Oranı</span>
            <span class="text-2xl font-bold text-white">{{ stats.sales_percentage }}%</span>
          </div>
          <div class="w-full bg-white/20 rounded-full h-3">
            <div
              class="h-3 rounded-full transition-all bg-gradient-to-r from-emerald-500 to-teal-500"
              :style="{ width: `${Math.min(stats.sales_percentage, 100)}%` }"
            ></div>
          </div>
        </div>
      </div>
    </template>

    <!-- Main Content -->
    <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
      <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Left Sidebar - Blocks -->
        <div class="lg:col-span-1">
          <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden sticky top-6">
            <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
              <h3 class="text-sm font-semibold text-gray-900">Bloklar</h3>
            </div>
            <div class="p-2 max-h-[calc(100vh-300px)] overflow-y-auto">
              <button
                v-for="structure in project.structures"
                :key="structure.id"
                @click="selectStructure(structure)"
                :class="[
                  'w-full text-left px-4 py-3 rounded-lg mb-2 transition-all',
                  selectedStructure?.id === structure.id
                    ? 'bg-emerald-600 text-white shadow-md'
                    : 'bg-gray-50 text-gray-700 hover:bg-gray-100'
                ]"
              >
                <div class="font-medium">{{ structure.name }}</div>
                <div class="text-xs mt-1" :class="selectedStructure?.id === structure.id ? 'text-emerald-100' : 'text-gray-500'">
                  {{ structure.code }}
                </div>
              </button>
            </div>
          </div>
        </div>

        <!-- Main Content - Floors & Units -->
        <div class="lg:col-span-3 space-y-6">
          <!-- Legend -->
          <div class="bg-white shadow-sm rounded-xl border border-gray-200 p-4">
            <h3 class="text-sm font-semibold text-gray-900 mb-3">Renk Kodları</h3>
            <div class="flex flex-wrap gap-4">
              <div class="flex items-center space-x-2">
                <div class="w-4 h-4 bg-emerald-500 rounded shadow-sm"></div>
                <span class="text-sm text-gray-700">Müsait</span>
              </div>
              <div class="flex items-center space-x-2">
                <div class="w-4 h-4 bg-rose-500 rounded shadow-sm"></div>
                <span class="text-sm text-gray-700">Satıldı</span>
              </div>
              <div class="flex items-center space-x-2">
                <div class="w-4 h-4 bg-amber-500 rounded shadow-sm"></div>
                <span class="text-sm text-gray-700">Rezerve</span>
              </div>
              <div class="flex items-center space-x-2">
                <div class="w-4 h-4 bg-orange-500 rounded shadow-sm"></div>
                <span class="text-sm text-gray-700">Gecikmiş</span>
              </div>
            </div>
          </div>

          <!-- Floors List -->
          <div v-if="selectedStructure && floors.length > 0" class="space-y-4">
            <div
              v-for="floor in floors"
              :key="floor.id"
              class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden"
            >
              <button
                @click="toggleFloor(floor.id)"
                class="w-full px-6 py-4 flex items-center justify-between hover:bg-gray-50 transition-colors"
              >
                <div class="flex items-center space-x-4 flex-1">
                  <div class="font-medium text-gray-900">{{ floor.name }}</div>
                  <div class="flex items-center space-x-2">
                    <div class="w-48 bg-gray-200 rounded-full h-2">
                      <div
                        class="h-2 rounded-full transition-all"
                        :class="floor.sales_percentage >= 100 ? 'bg-emerald-600' : 'bg-gradient-to-r from-emerald-500 to-teal-500'"
                        :style="{ width: `${Math.min(floor.sales_percentage, 100)}%` }"
                      ></div>
                    </div>
                    <span class="text-sm font-medium text-gray-600">
                      {{ floor.sold_units }}/{{ floor.total_units }}
                    </span>
                  </div>
                </div>
                <svg
                  class="w-5 h-5 text-gray-400 transition-transform"
                  :class="{ 'rotate-180': expandedFloors.includes(floor.id) }"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke-width="1.5"
                  stroke="currentColor"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                </svg>
              </button>

              <!-- Units Grid -->
              <div v-show="expandedFloors.includes(floor.id)" class="border-t border-gray-200 p-6 bg-gray-50">
                <div v-if="loadingFloorUnits[floor.id]" class="text-center py-8">
                  <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-blue-600 border-t-transparent"></div>
                  <p class="text-sm text-gray-600 mt-2">Birimler yükleniyor...</p>
                </div>

                <div v-else-if="floorUnits[floor.id]" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-4">
                  <div
                    v-for="unit in floorUnits[floor.id]"
                    :key="unit.id"
                    @click="showUnitDetails(unit)"
                    class="relative cursor-pointer group"
                  >
                    <div
                      :class="[
                        'rounded-lg p-4 border-2 transition-all transform hover:scale-105 hover:shadow-lg',
                        getUnitColorClass(unit.status)
                      ]"
                    >
                      <div class="text-center">
                        <div class="font-bold text-lg">{{ unit.unit_code }}</div>
                        <div class="text-xs mt-1 opacity-75">{{ unit.room_configuration || '-' }}</div>
                        <div class="text-xs mt-1 font-medium">{{ unit.gross_area }} m²</div>
                      </div>
                    </div>
                    <!-- Tooltip -->
                    <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 hidden group-hover:block z-10">
                      <div class="bg-gray-900 text-white text-xs rounded-lg py-2 px-3 whitespace-nowrap shadow-lg">
                        <div class="font-medium">{{ unit.unit_code }}</div>
                        <div v-if="unit.customer_name" class="mt-1">{{ unit.customer_name }}</div>
                        <div class="mt-1">{{ getStatusLabel(unit.status) }}</div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Empty State -->
          <div v-else-if="!selectedStructure" class="bg-white shadow-sm rounded-xl border border-gray-200 p-12 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Blok Seçin</h3>
            <p class="mt-1 text-sm text-gray-500">Sol taraftan bir blok seçerek detaylarını görüntüleyin.</p>
          </div>

          <div v-else-if="floors.length === 0" class="bg-white shadow-sm rounded-xl border border-gray-200 p-12 text-center">
            <p class="text-sm text-gray-500">Bu blokta henüz kat tanımlanmamış.</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Unit Details Modal -->
    <TransitionRoot as="template" :show="showModal">
      <Dialog as="div" class="relative z-50" @close="showModal = false">
        <TransitionChild
          as="template"
          enter="ease-out duration-300"
          enter-from="opacity-0"
          enter-to="opacity-100"
          leave="ease-in duration-200"
          leave-from="opacity-100"
          leave-to="opacity-0"
        >
          <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" />
        </TransitionChild>

        <div class="fixed inset-0 z-10 overflow-y-auto">
          <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <TransitionChild
              as="template"
              enter="ease-out duration-300"
              enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
              enter-to="opacity-100 translate-y-0 sm:scale-100"
              leave="ease-in duration-200"
              leave-from="opacity-100 translate-y-0 sm:scale-100"
              leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            >
              <DialogPanel class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                  <div class="sm:flex sm:items-start">
                    <div
                      :class="[
                        'mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full sm:mx-0 sm:h-10 sm:w-10',
                        selectedUnit?.status === 'available' ? 'bg-emerald-100' :
                        selectedUnit?.status === 'sold' ? 'bg-rose-100' :
                        selectedUnit?.status === 'reserved' ? 'bg-amber-100' : 'bg-orange-100'
                      ]"
                    >
                      <svg class="h-6 w-6" :class="[
                        selectedUnit?.status === 'available' ? 'text-emerald-600' :
                        selectedUnit?.status === 'sold' ? 'text-rose-600' :
                        selectedUnit?.status === 'reserved' ? 'text-amber-600' : 'text-orange-600'
                      ]" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z" />
                      </svg>
                    </div>
                    <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left flex-1">
                      <DialogTitle as="h3" class="text-lg font-semibold leading-6 text-gray-900">
                        {{ selectedUnit?.unit_code }}
                      </DialogTitle>
                      <div class="mt-4 space-y-3">
                        <div class="flex justify-between py-2 border-b">
                          <span class="text-sm text-gray-500">Oda Konfigürasyonu:</span>
                          <span class="text-sm font-medium text-gray-900">{{ selectedUnit?.room_configuration || '-' }}</span>
                        </div>
                        <div class="flex justify-between py-2 border-b">
                          <span class="text-sm text-gray-500">Brüt Alan:</span>
                          <span class="text-sm font-medium text-gray-900">{{ selectedUnit?.gross_area }} m²</span>
                        </div>
                        <div class="flex justify-between py-2 border-b">
                          <span class="text-sm text-gray-500">Net Alan:</span>
                          <span class="text-sm font-medium text-gray-900">{{ selectedUnit?.net_area }} m²</span>
                        </div>
                        <div class="flex justify-between py-2 border-b">
                          <span class="text-sm text-gray-500">Durum:</span>
                          <span class="text-sm font-medium" :class="getStatusTextClass(selectedUnit?.status)">
                            {{ getStatusLabel(selectedUnit?.status) }}
                          </span>
                        </div>
                        <div v-if="selectedUnit?.customer_name" class="flex justify-between py-2 border-b">
                          <span class="text-sm text-gray-500">Müşteri:</span>
                          <span class="text-sm font-medium text-gray-900">{{ selectedUnit.customer_name }}</span>
                        </div>
                        <div v-if="selectedUnit?.sale_info" class="mt-4 p-3 bg-gray-50 rounded-lg">
                          <div class="text-xs font-medium text-gray-700 mb-2">Satış Bilgileri</div>
                          <div class="space-y-1 text-sm">
                            <div class="flex justify-between">
                              <span class="text-gray-600">Satış No:</span>
                              <span class="font-medium">{{ selectedUnit.sale_info.sale_number }}</span>
                            </div>
                            <div class="flex justify-between">
                              <span class="text-gray-600">Tutar:</span>
                              <span class="font-medium">{{ formatCurrency(selectedUnit.sale_info.final_price) }}</span>
                            </div>
                            <div class="flex justify-between">
                              <span class="text-gray-600">Ödeme:</span>
                              <span class="font-medium">{{ selectedUnit.sale_info.payment_completion }}%</span>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 gap-3">
                  <Link
                    v-if="selectedUnit?.sale_info"
                    :href="route('sales.unit-sales.show', selectedUnit.sale_info.id)"
                    class="inline-flex w-full justify-center rounded-md bg-emerald-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500 sm:w-auto"
                  >
                    Satış Detayı
                  </Link>
                  <button
                    type="button"
                    class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto"
                    @click="showModal = false"
                  >
                    Kapat
                  </button>
                </div>
              </DialogPanel>
            </TransitionChild>
          </div>
        </div>
      </Dialog>
    </TransitionRoot>
  </AppLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue'
import axios from 'axios'

const props = defineProps({
  project: {
    type: Object,
    required: true
  },
  stats: {
    type: Object,
    required: true
  }
})

const selectedStructure = ref(null)
const floors = ref([])
const expandedFloors = ref([])
const floorUnits = ref({})
const loadingFloorUnits = ref({})
const showModal = ref(false)
const selectedUnit = ref(null)

// Select structure and load floors
const selectStructure = async (structure) => {
  console.log('Selecting structure:', structure)
  selectedStructure.value = structure
  floors.value = []
  expandedFloors.value = []
  floorUnits.value = {}

  try {
    const response = await axios.get(route('sales.sales-status.api.structure', structure.id))
    console.log('Floors response:', response.data)
    floors.value = response.data.floors
  } catch (error) {
    console.error('Error loading floors:', error)
  }
}

// Toggle floor expansion and load units
const toggleFloor = async (floorId) => {
  const index = expandedFloors.value.indexOf(floorId)

  if (index > -1) {
    expandedFloors.value.splice(index, 1)
  } else {
    expandedFloors.value.push(floorId)

    // Load units if not already loaded
    if (!floorUnits.value[floorId]) {
      loadingFloorUnits.value[floorId] = true

      try {
        const response = await axios.get(route('sales.sales-status.api.floor-units', floorId))
        floorUnits.value[floorId] = response.data.units
      } catch (error) {
        console.error('Error loading floor units:', error)
      } finally {
        loadingFloorUnits.value[floorId] = false
      }
    }
  }
}

// Show unit details modal
const showUnitDetails = (unit) => {
  selectedUnit.value = unit
  showModal.value = true
}

// Get unit color class based on status
const getUnitColorClass = (status) => {
  switch (status) {
    case 'available':
      return 'bg-emerald-100 border-emerald-500 text-emerald-900 hover:bg-emerald-200'
    case 'sold':
      return 'bg-rose-100 border-rose-500 text-rose-900 hover:bg-rose-200'
    case 'reserved':
      return 'bg-amber-100 border-amber-500 text-amber-900 hover:bg-amber-200'
    case 'delayed':
      return 'bg-orange-100 border-orange-500 text-orange-900 hover:bg-orange-200'
    default:
      return 'bg-gray-100 border-gray-500 text-gray-900 hover:bg-gray-200'
  }
}

// Get status text class
const getStatusTextClass = (status) => {
  switch (status) {
    case 'available':
      return 'text-emerald-600'
    case 'sold':
      return 'text-rose-600'
    case 'reserved':
      return 'text-amber-600'
    case 'delayed':
      return 'text-orange-600'
    default:
      return 'text-gray-600'
  }
}

// Get status label
const getStatusLabel = (status) => {
  switch (status) {
    case 'available':
      return 'Müsait'
    case 'sold':
      return 'Satıldı'
    case 'reserved':
      return 'Rezerve'
    case 'delayed':
      return 'Gecikmiş'
    default:
      return 'Bilinmiyor'
  }
}

// Format currency
const formatCurrency = (amount) => {
  if (!amount) return '₺0'
  return new Intl.NumberFormat('tr-TR', {
    style: 'currency',
    currency: 'TRY',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0
  }).format(amount)
}

// Auto-select first structure on mount
onMounted(() => {
  if (props.project.structures && props.project.structures.length > 0) {
    selectStructure(props.project.structures[0])
  }
})
</script>
