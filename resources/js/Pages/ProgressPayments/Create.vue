<template>
  <AppLayout title="Yeni Hakediş Kaydı" :full-width="true">
    <!-- Full-width header -->
    <template #fullWidthHeader>
      <div class="bg-gradient-to-r from-blue-600 via-blue-700 to-indigo-800 border-b border-blue-900/20 w-full">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
          <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
              <div class="flex-shrink-0 w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm">
                <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
              </div>
              <div>
                <h1 class="text-2xl lg:text-3xl font-bold text-white">Yeni Hakediş Kaydı</h1>
                <p class="text-blue-100 text-sm mt-1">İlerleme ve hakediş bilgilerini girin</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Breadcrumb -->
        <div class="bg-black/10 border-t border-white/10">
          <div class="w-full px-4 sm:px-6 lg:px-8">
            <div class="flex items-center space-x-2 py-2">
              <ol class="flex items-center space-x-2">
                <li>
                  <Link :href="route('dashboard')" class="text-blue-100 hover:text-white transition-colors">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                    </svg>
                  </Link>
                </li>
                <li class="flex items-center">
                  <svg class="h-3 w-3 text-blue-200 mx-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                  </svg>
                  <Link :href="route('progress-payments.index')" class="text-blue-100 hover:text-white text-sm">Hakediş Listesi</Link>
                </li>
                <li class="flex items-center">
                  <svg class="h-3 w-3 text-blue-200 mx-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                  </svg>
                  <span class="text-white font-medium text-sm">Yeni Kayıt</span>
                </li>
              </ol>
            </div>
          </div>
        </div>
      </div>
    </template>

    <!-- Main Content -->
    <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
      <form @submit.prevent="submit" class="space-y-6">
        <!-- Temel Bilgiler -->
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-medium text-gray-900">Temel Bilgiler</h3>
          </div>
          <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
              <!-- Proje Seçimi -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Proje <span class="text-red-500">*</span>
                </label>
                <select
                  v-model="form.project_id"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                  :class="{'border-red-300 focus:ring-red-500': form.errors.project_id}"
                  @change="onProjectChange"
                >
                  <option value="">Seçiniz...</option>
                  <option v-for="project in projects" :key="project.id" :value="project.id">
                    {{ project.name }}
                  </option>
                </select>
                <p v-if="form.errors.project_id" class="text-red-600 text-sm mt-2">
                  {{ form.errors.project_id }}
                </p>
              </div>

              <!-- Taşeron Seçimi -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Taşeron <span class="text-red-500">*</span>
                </label>
                <select
                  v-model="form.subcontractor_id"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                  :class="{'border-red-300 focus:ring-red-500': form.errors.subcontractor_id}"
                  :disabled="!form.project_id"
                >
                  <option value="">Seçiniz...</option>
                  <option v-for="subcontractor in filteredSubcontractors" :key="subcontractor.id" :value="subcontractor.id">
                    {{ subcontractor.company_name }}
                  </option>
                </select>
                <p v-if="form.errors.subcontractor_id" class="text-red-600 text-sm mt-2">
                  {{ form.errors.subcontractor_id }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Lokasyon Bilgileri -->
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-medium text-gray-900">Lokasyon (Opsiyonel)</h3>
            <p class="text-sm text-gray-600 mt-1">Hakediş kaydının hangi blok, kat veya birimle ilişkili olduğunu belirtin</p>
          </div>
          <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
              <!-- Blok/Yapı -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Blok/Yapı</label>
                <select
                  v-model="form.project_structure_id"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                  :disabled="!form.project_id"
                  @change="onStructureChange"
                >
                  <option :value="null">Seçiniz...</option>
                  <option v-for="structure in structureOptions" :key="structure.value" :value="structure.value">
                    {{ structure.label }}
                  </option>
                </select>
              </div>

              <!-- Kat -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Kat</label>
                <select
                  v-model="form.project_floor_id"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                  :disabled="!form.project_structure_id"
                  @change="onFloorChange"
                >
                  <option :value="null">Seçiniz...</option>
                  <option v-for="floor in floorOptions" :key="floor.value" :value="floor.value">
                    {{ floor.label }}
                  </option>
                </select>
              </div>

              <!-- Birim/Daire -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Birim/Daire</label>
                <select
                  v-model="form.project_unit_id"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                  :disabled="!form.project_floor_id"
                >
                  <option :value="null">Seçiniz...</option>
                  <option v-for="unit in unitOptions" :key="unit.value" :value="unit.value">
                    {{ unit.label }}
                  </option>
                </select>
              </div>
            </div>
          </div>
        </div>

        <!-- İş Kalemi ve Metraj -->
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-medium text-gray-900">İş Kalemi ve Metraj</h3>
          </div>
          <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
              <!-- İş Kalemi -->
              <div class="lg:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  İş Kalemi <span class="text-red-500">*</span>
                </label>
                <select
                  v-model="form.work_item_id"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                  :class="{'border-red-300 focus:ring-red-500': form.errors.work_item_id}"
                  @change="onWorkItemChange"
                >
                  <option value="">Seçiniz...</option>
                  <option v-for="item in workItems" :key="item.id" :value="item.id">
                    {{ item.name }} ({{ item.unit }})
                  </option>
                </select>
                <p v-if="form.errors.work_item_id" class="text-red-600 text-sm mt-2">
                  {{ form.errors.work_item_id }}
                </p>
              </div>

              <!-- Planlanan Metraj -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Planlanan Metraj <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="form.planned_quantity"
                  type="number"
                  step="0.01"
                  min="0"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                  :class="{'border-red-300 focus:ring-red-500': form.errors.planned_quantity}"
                />
                <p v-if="form.errors.planned_quantity" class="text-red-600 text-sm mt-2">
                  {{ form.errors.planned_quantity }}
                </p>
              </div>

              <!-- Tamamlanan Metraj -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Tamamlanan Metraj <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="form.completed_quantity"
                  type="number"
                  step="0.01"
                  min="0"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                  :class="{'border-red-300 focus:ring-red-500': form.errors.completed_quantity}"
                />
                <p v-if="form.errors.completed_quantity" class="text-red-600 text-sm mt-2">
                  {{ form.errors.completed_quantity }}
                </p>
              </div>

              <!-- Birim -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Birim <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="form.unit"
                  type="text"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                  :class="{'border-red-300 focus:ring-red-500': form.errors.unit}"
                  placeholder="m², m³, adet, kg vb."
                />
                <p v-if="form.errors.unit" class="text-red-600 text-sm mt-2">
                  {{ form.errors.unit }}
                </p>
              </div>

              <!-- İlerleme Yüzdesi -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">İlerleme Yüzdesi</label>
                <div class="p-4 bg-gradient-to-br from-blue-50 to-indigo-50 rounded-lg border border-blue-200">
                  <div class="flex items-center justify-between mb-2">
                    <span class="text-3xl font-bold text-blue-600">{{ completionPercentage }}%</span>
                    <span class="text-sm text-gray-600">Tamamlanma</span>
                  </div>
                  <div class="w-full bg-gray-200 rounded-full h-2.5">
                    <div
                      class="h-2.5 rounded-full transition-all duration-300"
                      :class="completionPercentage >= 100 ? 'bg-green-600' : 'bg-blue-600'"
                      :style="{ width: `${Math.min(completionPercentage, 100)}%` }"
                    ></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Fiyat Bilgileri -->
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-medium text-gray-900">Fiyat Bilgileri</h3>
          </div>
          <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
              <!-- Birim Fiyat -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Birim Fiyat (TL) <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="form.unit_price"
                  type="number"
                  step="0.01"
                  min="0"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                  :class="{'border-red-300 focus:ring-red-500': form.errors.unit_price}"
                />
                <p v-if="form.errors.unit_price" class="text-red-600 text-sm mt-2">
                  {{ form.errors.unit_price }}
                </p>
              </div>

              <!-- Toplam Tutar -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Toplam Tutar</label>
                <div class="p-4 bg-gradient-to-br from-green-50 to-emerald-50 rounded-lg border border-green-200">
                  <div class="text-3xl font-bold text-green-700">
                    {{ formatCurrency(totalAmount) }}
                  </div>
                  <div class="text-sm text-green-600 mt-1">
                    {{ form.completed_quantity }} {{ form.unit }} × {{ formatCurrency(form.unit_price) }}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Durum ve Dönem -->
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-medium text-gray-900">Durum ve Dönem</h3>
          </div>
          <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
              <!-- Durum -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Durum <span class="text-red-500">*</span>
                </label>
                <select
                  v-model="form.status"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                >
                  <option value="planned">Planlandı</option>
                  <option value="in_progress">Devam Ediyor</option>
                  <option value="completed">Tamamlandı</option>
                </select>
              </div>

              <!-- Yıl -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Yıl</label>
                <input
                  v-model="form.period_year"
                  type="number"
                  min="2000"
                  max="2100"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                />
              </div>

              <!-- Ay -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Ay</label>
                <select
                  v-model="form.period_month"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                >
                  <option :value="null">Seçiniz...</option>
                  <option :value="1">Ocak</option>
                  <option :value="2">Şubat</option>
                  <option :value="3">Mart</option>
                  <option :value="4">Nisan</option>
                  <option :value="5">Mayıs</option>
                  <option :value="6">Haziran</option>
                  <option :value="7">Temmuz</option>
                  <option :value="8">Ağustos</option>
                  <option :value="9">Eylül</option>
                  <option :value="10">Ekim</option>
                  <option :value="11">Kasım</option>
                  <option :value="12">Aralık</option>
                </select>
              </div>
            </div>
          </div>
        </div>

        <!-- Notlar -->
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-medium text-gray-900">Ek Notlar</h3>
          </div>
          <div class="p-6">
            <textarea
              v-model="form.notes"
              rows="4"
              class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
              placeholder="Hakediş kaydı ile ilgili ek notlar, açıklamalar veya detaylar..."
            ></textarea>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex justify-end space-x-4 pb-6">
          <Link
            :href="route('progress-payments.index')"
            class="px-6 py-3 bg-white border-2 border-gray-300 rounded-lg font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all"
          >
            İptal
          </Link>
          <button
            type="submit"
            :disabled="form.processing"
            class="px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 border border-transparent rounded-lg font-medium text-white hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all"
          >
            <span v-if="form.processing">Kaydediliyor...</span>
            <span v-else>Kaydet</span>
          </button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useForm, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  projects: {
    type: Array,
    required: true
  },
  subcontractors: {
    type: Array,
    required: true
  },
  workItems: {
    type: Array,
    required: true
  }
})

const form = useForm({
  project_id: '',
  subcontractor_id: '',
  work_item_id: '',
  project_structure_id: null,
  project_floor_id: null,
  project_unit_id: null,
  planned_quantity: 0,
  completed_quantity: 0,
  unit: 'm²',
  unit_price: 0,
  payment_date: null,
  status: 'planned',
  period_year: new Date().getFullYear(),
  period_month: new Date().getMonth() + 1,
  notes: ''
})

const structureOptions = ref([])
const floorOptions = ref([])
const unitOptions = ref([])

// Filtrelenmiş taşeronlar - sadece seçili projeye atanan taşeronlar
const filteredSubcontractors = computed(() => {
  if (!form.project_id) {
    return []
  }

  const selectedProject = props.projects.find(p => p.id === form.project_id)
  if (!selectedProject || !selectedProject.subcontractors) {
    return props.subcontractors // Eğer proje ilişkisi yüklü değilse tümünü göster
  }

  return selectedProject.subcontractors
})

const completionPercentage = computed(() => {
  if (!form.planned_quantity || form.planned_quantity === 0) {
    return 0
  }
  return Math.min(Math.round((form.completed_quantity / form.planned_quantity) * 100), 100)
})

const totalAmount = computed(() => {
  return (form.completed_quantity || 0) * (form.unit_price || 0)
})

const onProjectChange = () => {
  const project = props.projects.find(p => p.id === form.project_id)
  if (project && project.structures) {
    structureOptions.value = project.structures.map(s => ({
      value: s.id,
      label: s.name
    }))
  } else {
    structureOptions.value = []
  }
  form.project_structure_id = null
  form.project_floor_id = null
  form.subcontractor_id = '' // Reset taşeron seçimi
  floorOptions.value = []
}

const onStructureChange = () => {
  const project = props.projects.find(p => p.id === form.project_id)
  if (project && project.structures) {
    const structure = project.structures.find(s => s.id === form.project_structure_id)
    if (structure && structure.floors) {
      floorOptions.value = structure.floors.map(f => ({
        value: f.id,
        label: f.floor_display || f.name || `${f.floor_number}. Kat`
      }))
    } else {
      floorOptions.value = []
    }
  }
  form.project_floor_id = null
  form.project_unit_id = null
  unitOptions.value = []
}

const onFloorChange = () => {
  const project = props.projects.find(p => p.id === form.project_id)
  if (project && project.structures) {
    const structure = project.structures.find(s => s.id === form.project_structure_id)
    if (structure && structure.floors) {
      const floor = structure.floors.find(f => f.id === form.project_floor_id)
      if (floor && floor.units) {
        unitOptions.value = floor.units.map(u => ({
          value: u.id,
          label: u.name || u.unit_number || `Birim ${u.id}`
        }))
      } else {
        unitOptions.value = []
      }
    }
  }
  form.project_unit_id = null
}

const onWorkItemChange = () => {
  const workItem = props.workItems.find(w => w.id === form.work_item_id)
  if (workItem && workItem.unit) {
    form.unit = workItem.unit
  }
}

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('tr-TR', {
    style: 'currency',
    currency: 'TRY',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0
  }).format(amount)
}

const submit = () => {
  form.post(route('progress-payments.store'), {
    onSuccess: () => {
      // Redirect will be handled by controller
    }
  })
}
</script>