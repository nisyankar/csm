<template>
  <AppLayout title="Yeni Satış Kaydı" :full-width="true">
    <!-- Header -->
    <template #fullWidthHeader>
      <div class="bg-gradient-to-r from-emerald-600 via-emerald-700 to-green-800 border-b border-emerald-900/20 w-full">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
          <div class="flex items-center">
            <div class="flex-shrink-0 w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm">
              <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
              </svg>
            </div>
            <div class="ml-6">
              <h1 class="text-2xl lg:text-3xl font-bold text-white">Yeni Satış Kaydı</h1>
              <p class="text-emerald-100 text-sm mt-1">Yeni birim satış kaydı oluşturun</p>
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
                  <Link :href="route('sales.unit-sales.index')" class="text-emerald-100 hover:text-white text-sm">Satışlar</Link>
                </li>
                <li class="flex items-center">
                  <svg class="h-3 w-3 text-emerald-200 mx-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                  </svg>
                  <span class="text-white font-medium text-sm">Yeni Satış</span>
                </li>
              </ol>
            </div>
          </div>
        </div>
      </div>
    </template>

    <!-- Content -->
    <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
      <form @submit.prevent="submit" class="space-y-6">
        <!-- General Errors -->
        <div v-if="Object.keys(form.errors).length > 0" class="bg-red-50 border border-red-200 text-red-800 rounded-xl p-4">
          <h4 class="font-semibold mb-2">Lütfen aşağıdaki hataları düzeltin:</h4>
          <ul class="list-disc list-inside space-y-1">
            <li v-for="(error, field) in form.errors" :key="field" class="text-sm">{{ error }}</li>
          </ul>
        </div>

        <!-- Müşteri ve Proje Seçimi -->
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-medium text-gray-900">Müşteri ve Proje Seçimi</h3>
          </div>
          <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
              <!-- Müşteri -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Müşteri <span class="text-red-500">*</span>
                </label>
                <select
                  v-model="form.customer_id"
                  required
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                  :class="{'border-red-300 focus:ring-red-500': form.errors.customer_id}"
                >
                  <option value="">Müşteri seçiniz...</option>
                  <option v-for="customer in customers" :key="customer.id" :value="customer.id">
                    {{ customer.customer_type === 'corporate' ? customer.company_name : (customer.first_name + ' ' + customer.last_name) }}
                    {{ customer.phone ? ' - ' + customer.phone : '' }}
                  </option>
                </select>
                <p v-if="form.errors.customer_id" class="text-red-600 text-sm mt-2">
                  {{ form.errors.customer_id }}
                </p>
              </div>

              <!-- Proje -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Proje <span class="text-red-500">*</span>
                </label>
                <select
                  v-model="form.project_id"
                  required
                  @change="onProjectChange"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                  :class="{'border-red-300 focus:ring-red-500': form.errors.project_id}"
                >
                  <option value="">Proje seçiniz...</option>
                  <option v-for="project in projects" :key="project.id" :value="project.id">
                    {{ project.name }}
                  </option>
                </select>
                <p v-if="form.errors.project_id" class="text-red-600 text-sm mt-2">
                  {{ form.errors.project_id }}
                </p>
              </div>

              <!-- Blok -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Blok <span class="text-red-500">*</span>
                </label>
                <select
                  v-model="selectedStructure"
                  required
                  :disabled="!form.project_id || loadingStructures"
                  @change="onStructureChange"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all disabled:bg-gray-100"
                >
                  <option value="">{{ loadingStructures ? 'Yükleniyor...' : 'Blok seçiniz...' }}</option>
                  <option v-for="structure in structures" :key="structure.id" :value="structure.id">
                    {{ structure.name }}
                  </option>
                </select>
                <p v-if="!form.project_id" class="text-xs text-gray-500 mt-1">
                  Önce bir proje seçiniz
                </p>
              </div>

              <!-- Kat -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Kat <span class="text-red-500">*</span>
                </label>
                <select
                  v-model="selectedFloor"
                  required
                  :disabled="!selectedStructure || loadingFloors"
                  @change="onFloorChange"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all disabled:bg-gray-100"
                >
                  <option value="">{{ loadingFloors ? 'Yükleniyor...' : 'Kat seçiniz...' }}</option>
                  <option v-for="floor in floors" :key="floor.id" :value="floor.id">
                    {{ floor.floor_name }}
                  </option>
                </select>
                <p v-if="!selectedStructure" class="text-xs text-gray-500 mt-1">
                  Önce bir blok seçiniz
                </p>
              </div>

              <!-- Birim -->
              <div class="lg:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Birim/Daire <span class="text-red-500">*</span>
                </label>
                <select
                  v-model="form.project_unit_id"
                  required
                  :disabled="!selectedFloor || loadingUnits"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all disabled:bg-gray-100"
                  :class="{'border-red-300 focus:ring-red-500': form.errors.project_unit_id}"
                >
                  <option value="">{{ loadingUnits ? 'Yükleniyor...' : 'Birim seçiniz...' }}</option>
                  <option v-for="unit in availableUnits" :key="unit.id" :value="unit.id">
                    {{ unit.unit_code }} - {{ unit.unit_type }} ({{ unit.gross_area }} m²)
                  </option>
                </select>
                <p v-if="form.errors.project_unit_id" class="text-red-600 text-sm mt-2">
                  {{ form.errors.project_unit_id }}
                </p>
                <p v-if="!selectedFloor" class="text-xs text-gray-500 mt-1">
                  Önce bir kat seçiniz
                </p>
                <p v-if="selectedFloor && availableUnits.length === 0 && !loadingUnits" class="text-xs text-amber-600 mt-1">
                  Bu katta müsait birim bulunmamaktadır
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Satış Tipi ve Tarihler -->
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-medium text-gray-900">Satış Bilgileri</h3>
          </div>
          <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
              <!-- Satış Tipi -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Satış Tipi <span class="text-red-500">*</span>
                </label>
                <select
                  v-model="form.sale_type"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                >
                  <option value="reservation">Rezervasyon</option>
                  <option value="sale">Satış</option>
                  <option value="presale">Ön Satış</option>
                </select>
              </div>

              <!-- Rezervasyon Tarihi -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Rezervasyon Tarihi</label>
                <input
                  v-model="form.reservation_date"
                  type="date"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                />
              </div>

              <!-- Satış Tarihi -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Satış Tarihi</label>
                <input
                  v-model="form.sale_date"
                  type="date"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                />
              </div>

              <!-- Sözleşme Tarihi -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Sözleşme Tarihi</label>
                <input
                  v-model="form.contract_date"
                  type="date"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                />
              </div>

              <!-- Teslim Tarihi -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Planlanan Teslim Tarihi</label>
                <input
                  v-model="form.delivery_date"
                  type="date"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                />
              </div>

              <!-- Durum -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Durum</label>
                <select
                  v-model="form.status"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                >
                  <option value="reserved">Rezerve</option>
                  <option value="contracted">Sözleşmeli</option>
                  <option value="in_payment">Ödeme Aşamasında</option>
                  <option value="completed">Tamamlandı</option>
                </select>
              </div>
            </div>
          </div>
        </div>

        <!-- Fiyat Bilgileri -->
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-medium text-gray-900">Fiyat ve İndirim</h3>
          </div>
          <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
              <!-- Liste Fiyatı -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Liste Fiyatı (TL) <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="form.list_price"
                  type="number"
                  step="0.01"
                  min="0"
                  required
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                  :class="{'border-red-300 focus:ring-red-500': form.errors.list_price}"
                />
                <p v-if="form.errors.list_price" class="text-red-600 text-sm mt-2">
                  {{ form.errors.list_price }}
                </p>
              </div>

              <!-- İndirim Yüzdesi -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">İndirim Oranı (%)</label>
                <input
                  v-model="form.discount_percentage"
                  type="number"
                  step="0.01"
                  min="0"
                  max="100"
                  @input="calculateDiscount"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                />
              </div>

              <!-- İndirim Tutarı -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">İndirim Tutarı (TL)</label>
                <input
                  v-model="form.discount_amount"
                  type="number"
                  step="0.01"
                  min="0"
                  @input="calculatePercentage"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                />
              </div>

              <!-- Final Fiyat -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Net Satış Fiyatı</label>
                <div class="p-4 bg-gradient-to-br from-emerald-50 to-green-50 rounded-lg border border-emerald-200">
                  <div class="text-3xl font-bold text-emerald-700">
                    {{ formatCurrency(finalPrice) }}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Ödeme Planı -->
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-medium text-gray-900">Ödeme Planı</h3>
          </div>
          <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
              <!-- Peşinat -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Peşinat Tutarı (TL) <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="form.down_payment"
                  type="number"
                  step="0.01"
                  min="0"
                  required
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                  :class="{'border-red-300 focus:ring-red-500': form.errors.down_payment}"
                />
                <p v-if="form.errors.down_payment" class="text-red-600 text-sm mt-2">
                  {{ form.errors.down_payment }}
                </p>
              </div>

              <!-- Taksit Sayısı -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Taksit Sayısı <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="form.installment_count"
                  type="number"
                  min="0"
                  required
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                  :class="{'border-red-300 focus:ring-red-500': form.errors.installment_count}"
                />
                <p v-if="form.errors.installment_count" class="text-red-600 text-sm mt-2">
                  {{ form.errors.installment_count }}
                </p>
              </div>

              <!-- Ödeme Yöntemi -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Ödeme Yöntemi</label>
                <select
                  v-model="form.payment_method"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                >
                  <option value="cash">Nakit</option>
                  <option value="bank_transfer">Havale/EFT</option>
                  <option value="credit_card">Kredi Kartı</option>
                  <option value="check">Çek</option>
                  <option value="bank_loan">Banka Kredisi</option>
                </select>
              </div>

              <!-- Aylık Taksit Tutarı -->
              <div class="lg:col-span-3">
                <div class="p-4 bg-gradient-to-br from-blue-50 to-indigo-50 rounded-lg border border-blue-200">
                  <div class="flex justify-between items-center">
                    <div>
                      <div class="text-sm text-blue-600 mb-1">Aylık Taksit Tutarı</div>
                      <div class="text-2xl font-bold text-blue-700">
                        {{ formatCurrency(monthlyInstallment) }}
                      </div>
                    </div>
                    <div class="text-right">
                      <div class="text-sm text-blue-600 mb-1">Kalan Tutar (Taksitli)</div>
                      <div class="text-xl font-bold text-blue-700">
                        {{ formatCurrency(remainingAmount) }}
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Tapu Bilgileri -->
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-medium text-gray-900">Tapu Bilgileri</h3>
          </div>
          <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
              <!-- Tapu Durumu -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tapu Durumu</label>
                <select
                  v-model="form.deed_status"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                >
                  <option value="not_transferred">Devredilmedi</option>
                  <option value="in_progress">İşlemde</option>
                  <option value="transferred">Devredildi</option>
                  <option value="postponed">Ertelendi</option>
                </select>
              </div>

              <!-- Tapu Tipi -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tapu Tipi</label>
                <input
                  v-model="form.deed_type"
                  type="text"
                  placeholder="Örn: Kat Mülkiyeti, Kat İrtifakı"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                />
              </div>

              <!-- Tapu Devir Tarihi -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tapu Devir Tarihi</label>
                <input
                  v-model="form.deed_transfer_date"
                  type="date"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                />
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
              class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
              placeholder="Satış kaydı ile ilgili ek notlar, açıklamalar veya detaylar..."
            ></textarea>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex justify-end space-x-4 pb-6">
          <Link
            :href="route('sales.unit-sales.index')"
            class="px-6 py-3 bg-white border-2 border-gray-300 rounded-lg font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all"
          >
            İptal
          </Link>
          <button
            type="submit"
            :disabled="form.processing"
            class="px-6 py-3 bg-gradient-to-r from-emerald-600 to-green-600 border border-transparent rounded-lg font-medium text-white hover:from-emerald-700 hover:to-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all"
          >
            <span v-if="form.processing">Kaydediliyor...</span>
            <span v-else>Satışı Kaydet</span>
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
import axios from 'axios'

const props = defineProps({
  projects: {
    type: Array,
    required: true
  },
  customers: {
    type: Array,
    required: true
  }
})

const form = useForm({
  customer_id: '',
  project_id: '',
  project_unit_id: '',
  sale_type: 'sale',
  list_price: 0,
  discount_amount: 0,
  discount_percentage: 0,
  down_payment: 0,
  installment_count: 0,
  payment_method: 'cash',
  reservation_date: null,
  sale_date: new Date().toISOString().split('T')[0],
  contract_date: null,
  delivery_date: null,
  deed_status: 'not_transferred',
  deed_type: '',
  deed_transfer_date: null,
  status: 'reserved',
  notes: '',
  currency: 'TRY'
})

// Cascade dropdown states
const selectedStructure = ref('')
const selectedFloor = ref('')
const structures = ref([])
const floors = ref([])
const availableUnits = ref([])

// Loading states
const loadingStructures = ref(false)
const loadingFloors = ref(false)
const loadingUnits = ref(false)

const onProjectChange = async () => {
  // Reset cascade selections
  selectedStructure.value = ''
  selectedFloor.value = ''
  form.project_unit_id = ''
  structures.value = []
  floors.value = []
  availableUnits.value = []

  if (!form.project_id) return

  // Load structures for selected project
  try {
    loadingStructures.value = true
    const response = await axios.get(route('sales.api.structures', form.project_id))
    structures.value = response.data
  } catch (error) {
    console.error('Error loading structures:', error)
  } finally {
    loadingStructures.value = false
  }
}

const onStructureChange = async () => {
  // Reset floor and unit selections
  selectedFloor.value = ''
  form.project_unit_id = ''
  floors.value = []
  availableUnits.value = []

  if (!selectedStructure.value) return

  // Load floors for selected structure
  try {
    loadingFloors.value = true
    const response = await axios.get(route('sales.api.floors', selectedStructure.value))
    floors.value = response.data
  } catch (error) {
    console.error('Error loading floors:', error)
  } finally {
    loadingFloors.value = false
  }
}

const onFloorChange = async () => {
  // Reset unit selection
  form.project_unit_id = ''
  availableUnits.value = []

  if (!selectedFloor.value) return

  // Load available units for selected floor
  try {
    loadingUnits.value = true
    const response = await axios.get(route('sales.api.available-units', selectedFloor.value))
    availableUnits.value = response.data
  } catch (error) {
    console.error('Error loading units:', error)
  } finally {
    loadingUnits.value = false
  }
}

const finalPrice = computed(() => {
  const listPrice = parseFloat(form.list_price) || 0
  const discountAmount = parseFloat(form.discount_amount) || 0
  return listPrice - discountAmount
})

const remainingAmount = computed(() => {
  const downPayment = parseFloat(form.down_payment) || 0
  return finalPrice.value - downPayment
})

const monthlyInstallment = computed(() => {
  const installmentCount = parseInt(form.installment_count) || 1
  if (installmentCount === 0) return 0
  return remainingAmount.value / installmentCount
})

const calculateDiscount = () => {
  const listPrice = parseFloat(form.list_price) || 0
  const percentage = parseFloat(form.discount_percentage) || 0
  form.discount_amount = (listPrice * percentage / 100).toFixed(2)
}

const calculatePercentage = () => {
  const listPrice = parseFloat(form.list_price) || 0
  const discountAmount = parseFloat(form.discount_amount) || 0
  if (listPrice > 0) {
    form.discount_percentage = ((discountAmount / listPrice) * 100).toFixed(2)
  }
}

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('tr-TR', {
    style: 'currency',
    currency: 'TRY',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0
  }).format(amount || 0)
}

const submit = () => {
  form.post(route('sales.unit-sales.store'))
}
</script>
