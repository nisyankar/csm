<template>
  <AppLayout title="Yeni Metraj Kaydı" :full-width="true">
    <!-- Full-width header -->
    <template #fullWidthHeader>
      <div class="bg-gradient-to-r from-emerald-600 via-teal-700 to-cyan-800 border-b border-emerald-900/20 w-full">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
          <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
              <div class="flex-shrink-0 w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm">
                <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
              </div>
              <div>
                <h1 class="text-2xl lg:text-3xl font-bold text-white">Yeni Metraj Kaydı</h1>
                <p class="text-emerald-100 text-sm mt-1">Proje metraj bilgilerini girin</p>
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
                  <Link :href="route('dashboard')" class="text-emerald-100 hover:text-white transition-colors">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                    </svg>
                  </Link>
                </li>
                <li class="flex items-center">
                  <svg class="h-3 w-3 text-emerald-200 mx-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                  </svg>
                  <Link :href="route('quantities.index')" class="text-emerald-100 hover:text-white text-sm">Metraj Listesi</Link>
                </li>
                <li class="flex items-center">
                  <svg class="h-3 w-3 text-emerald-200 mx-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
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
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
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

              <!-- İş Kalemi -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  İş Kalemi <span class="text-red-500">*</span>
                </label>
                <select
                  v-model="form.work_item_id"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                  :class="{'border-red-300 focus:ring-red-500': form.errors.work_item_id}"
                  @change="onWorkItemChange"
                >
                  <option value="">Seçiniz...</option>
                  <option v-for="item in workItems" :key="item.id" :value="item.id">
                    {{ item.name }} - {{ item.code }} ({{ item.unit }})
                  </option>
                </select>
                <p v-if="form.errors.work_item_id" class="text-red-600 text-sm mt-2">
                  {{ form.errors.work_item_id }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Lokasyon Bilgileri -->
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-medium text-gray-900">Lokasyon (Opsiyonel)</h3>
            <p class="text-sm text-gray-600 mt-1">Metraj kaydının hangi blok, kat veya birimle ilişkili olduğunu belirtin</p>
          </div>
          <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
              <!-- Blok/Yapı -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Blok/Yapı</label>
                <select
                  v-model="form.project_structure_id"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                  :disabled="!form.project_id"
                  @change="onStructureChange"
                >
                  <option :value="null">Seçiniz...</option>
                  <option v-for="structure in structureOptions" :key="structure.id" :value="structure.id">
                    {{ structure.name }}
                  </option>
                </select>
              </div>

              <!-- Kat -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Kat</label>
                <select
                  v-model="form.project_floor_id"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                  :disabled="!form.project_structure_id"
                  @change="onFloorChange"
                >
                  <option :value="null">Seçiniz...</option>
                  <option v-for="floor in floorOptions" :key="floor.id" :value="floor.id">
                    {{ floor.name }}
                  </option>
                </select>
              </div>

              <!-- Birim/Daire -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Birim/Daire</label>
                <select
                  v-model="form.project_unit_id"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                  :disabled="!form.project_floor_id"
                >
                  <option :value="null">Seçiniz...</option>
                  <option v-for="unit in unitOptions" :key="unit.id" :value="unit.id">
                    {{ unit.name }}
                  </option>
                </select>
              </div>
            </div>
          </div>
        </div>

        <!-- Metraj Bilgileri -->
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-medium text-gray-900">Metraj Bilgileri</h3>
          </div>
          <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
              <!-- Planlanan Metraj -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Planlanan Metraj <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="form.planned_quantity"
                  type="number"
                  step="0.01"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                  :class="{'border-red-300 focus:ring-red-500': form.errors.planned_quantity}"
                  placeholder="0.00"
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
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                  :class="{'border-red-300 focus:ring-red-500': form.errors.completed_quantity}"
                  placeholder="0.00"
                  @input="calculateCompletion"
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
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                  :class="{'border-red-300 focus:ring-red-500': form.errors.unit}"
                  placeholder="m², m³, adet, kg..."
                  readonly
                />
                <p v-if="form.errors.unit" class="text-red-600 text-sm mt-2">
                  {{ form.errors.unit }}
                </p>
              </div>

              <!-- İlerleme % (Otomatik) -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  İlerleme Yüzdesi
                </label>
                <div class="relative">
                  <input
                    :value="completionPercentage"
                    type="text"
                    class="w-full px-4 py-3 rounded-lg border border-gray-300 bg-gray-50 text-gray-700"
                    readonly
                  />
                  <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                    <span class="text-gray-500 text-sm">%</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Ölçüm Detayları -->
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-medium text-gray-900">Ölçüm Detayları</h3>
          </div>
          <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
              <!-- Ölçüm Tarihi -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Ölçüm Tarihi
                </label>
                <input
                  v-model="form.measurement_date"
                  type="date"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                />
              </div>

              <!-- Ölçüm Yöntemi -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Ölçüm Yöntemi
                </label>
                <select
                  v-model="form.measurement_method"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                >
                  <option value="">Seçiniz...</option>
                  <option value="Manuel">Manuel</option>
                  <option value="Lazer Metre">Lazer Metre</option>
                  <option value="Drone">Drone</option>
                  <option value="Dijital Ölçüm">Dijital Ölçüm</option>
                  <option value="Teodoli">Teodoli</option>
                </select>
              </div>
            </div>
          </div>
        </div>

        <!-- Ek Notlar -->
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-medium text-gray-900">Ek Notlar</h3>
          </div>
          <div class="p-6">
            <textarea
              v-model="form.notes"
              rows="4"
              class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
              placeholder="Metraj ölçümü ile ilgili ek notlar..."
            ></textarea>
          </div>
        </div>

        <!-- Form Actions -->
        <div class="flex items-center justify-end space-x-4">
          <Link
            :href="route('quantities.index')"
            class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 bg-white hover:bg-gray-50 font-medium transition-all duration-200"
          >
            İptal
          </Link>
          <button
            type="submit"
            :disabled="form.processing"
            class="px-6 py-3 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 font-medium transition-all duration-200 shadow-sm hover:shadow disabled:opacity-50 disabled:cursor-not-allowed"
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
  workItems: {
    type: Array,
    required: true
  },
  structures: {
    type: Array,
    default: () => []
  },
  floors: {
    type: Array,
    default: () => []
  },
  units: {
    type: Array,
    default: () => []
  }
})

const form = useForm({
  project_id: '',
  work_item_id: '',
  project_structure_id: null,
  project_floor_id: null,
  project_unit_id: null,
  planned_quantity: '',
  completed_quantity: '',
  unit: '',
  measurement_date: new Date().toISOString().split('T')[0],
  measurement_method: '',
  notes: ''
})

const structureOptions = computed(() => {
  if (!form.project_id) return []
  return props.structures.filter(s => s.project_id == form.project_id)
})

const floorOptions = computed(() => {
  if (!form.project_structure_id) return []
  return props.floors.filter(f => f.project_structure_id == form.project_structure_id)
})

const unitOptions = computed(() => {
  if (!form.project_floor_id) return []
  return props.units.filter(u => u.project_floor_id == form.project_floor_id)
})

const completionPercentage = computed(() => {
  const planned = parseFloat(form.planned_quantity) || 0
  const completed = parseFloat(form.completed_quantity) || 0
  if (planned === 0) return '0.00'
  return ((completed / planned) * 100).toFixed(2)
})

const onProjectChange = () => {
  form.project_structure_id = null
  form.project_floor_id = null
  form.project_unit_id = null
}

const onWorkItemChange = () => {
  const selectedItem = props.workItems.find(item => item.id == form.work_item_id)
  if (selectedItem) {
    form.unit = selectedItem.unit
  }
}

const onStructureChange = () => {
  form.project_floor_id = null
  form.project_unit_id = null
}

const onFloorChange = () => {
  form.project_unit_id = null
}

const calculateCompletion = () => {
  // Automatically calculated by computed property
}

const submit = () => {
  form.post(route('quantities.store'), {
    onSuccess: () => {
      // Success handled by redirect
    }
  })
}
</script>
