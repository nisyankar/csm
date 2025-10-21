<template>
  <AppLayout
    title="Yeni Satınalma Talebi - SPT İnşaat Puantaj Sistemi"
    :full-width="true"
  >
    <!-- Full-width header -->
    <template #fullWidthHeader>
      <div class="bg-gradient-to-r from-blue-600 via-blue-700 to-blue-800 border-b border-blue-900/20 w-full">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
          <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <!-- Header Info -->
            <div class="flex-1 min-w-0">
              <div class="flex items-center space-x-4 mb-3">
                <div class="flex-shrink-0 w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm">
                  <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                  </svg>
                </div>
                <div>
                  <h1 class="text-2xl lg:text-3xl font-bold text-white">Yeni Satınalma Talebi</h1>
                  <p class="text-blue-100 text-sm mt-1">Satınalma talebi oluşturun ve kalemleri ekleyin</p>
                </div>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex-shrink-0">
              <Link
                :href="route('purchasing-requests.index')"
                class="inline-flex items-center px-4 py-2 bg-white/10 backdrop-blur-sm border border-white/30 text-white text-sm font-medium rounded-lg hover:bg-white/20 transition-all duration-200"
              >
                <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>
                Geri Dön
              </Link>
            </div>
          </div>
        </div>

        <!-- Breadcrumb -->
        <div class="bg-black/10 border-t border-white/10">
          <div class="w-full px-4 sm:px-6 lg:px-8">
            <div class="flex items-center space-x-2 py-2">
              <ol class="flex items-center space-x-2">
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
                <li class="flex items-center">
                  <svg class="h-3 w-3 text-blue-200 mx-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                  </svg>
                  <Link :href="route('purchasing-requests.index')" class="text-xs font-medium text-blue-100 hover:text-white">
                    Satınalma Talepleri
                  </Link>
                </li>
                <li class="flex items-center">
                  <svg class="h-3 w-3 text-blue-200 mx-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                  </svg>
                  <span class="text-xs font-medium text-white">Yeni Talep</span>
                </li>
              </ol>
            </div>
          </div>
        </div>
      </div>
    </template>

    <!-- Main Content -->
    <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
      <form @submit.prevent="submitForm" class="space-y-6">
        <!-- Temel Bilgiler -->
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-medium text-gray-900">Temel Bilgiler</h3>
          </div>

          <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
              <!-- Başlık - Full Width -->
              <div class="lg:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Talep Başlığı <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="form.title"
                  type="text"
                  class="block w-full px-4 py-3 text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                  placeholder="Örn: İnşaat Malzeme Talebi - Proje A"
                  required
                />
                <p v-if="errors.title" class="mt-2 text-sm text-red-600">{{ errors.title }}</p>
              </div>

              <!-- Proje -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Proje <span class="text-red-500">*</span>
                </label>
                <select
                  v-model="form.project_id"
                  class="block w-full px-4 py-3 text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                  required
                >
                  <option value="">Proje Seçin</option>
                  <option v-for="project in uniqueProjects" :key="project.id" :value="project.id">
                    {{ project.name }}
                  </option>
                </select>
                <p v-if="errors.project_id" class="mt-2 text-sm text-red-600">{{ errors.project_id }}</p>
              </div>

              <!-- Departman -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Departman
                </label>
                <select
                  v-model="form.department_id"
                  class="block w-full px-4 py-3 text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                >
                  <option value="">Departman Seçin (Opsiyonel)</option>
                  <option v-for="department in uniqueDepartments" :key="department.id" :value="department.id">
                    {{ department.name }}
                  </option>
                </select>
              </div>

              <!-- Kategori -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Kategori <span class="text-red-500">*</span>
                </label>
                <select
                  v-model="form.category"
                  class="block w-full px-4 py-3 text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                  required
                >
                  <option value="">Kategori Seçin</option>
                  <option value="concrete">Beton</option>
                  <option value="steel">Demir</option>
                  <option value="general">Genel Malzeme</option>
                  <option value="equipment">Ekipman</option>
                  <option value="service">Hizmet</option>
                  <option value="other">Diğer</option>
                </select>
                <p v-if="errors.category" class="mt-2 text-sm text-red-600">{{ errors.category }}</p>
              </div>

              <!-- Aciliyet -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Aciliyet <span class="text-red-500">*</span>
                </label>
                <select
                  v-model="form.urgency"
                  class="block w-full px-4 py-3 text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                  required
                >
                  <option value="low">Düşük</option>
                  <option value="normal">Normal</option>
                  <option value="high">Yüksek</option>
                  <option value="urgent">Acil</option>
                </select>
              </div>

              <!-- İhtiyaç Tarihi -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  İhtiyaç Tarihi <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="form.required_date"
                  type="date"
                  :min="tomorrow"
                  class="block w-full px-4 py-3 text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                  required
                />
                <p v-if="errors.required_date" class="mt-2 text-sm text-red-600">{{ errors.required_date }}</p>
              </div>

              <!-- Açıklama - Full Width -->
              <div class="lg:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Açıklama
                </label>
                <textarea
                  v-model="form.description"
                  rows="4"
                  class="block w-full px-4 py-3 text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors resize-none"
                  placeholder="Talep hakkında ek bilgiler..."
                />
              </div>
            </div>
          </div>
        </div>

        <!-- Kalemler -->
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <div class="flex items-center justify-between">
              <h3 class="text-lg font-medium text-gray-900">Talep Kalemleri</h3>
              <button
                type="button"
                @click="addItem"
                class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors"
              >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Kalem Ekle
              </button>
            </div>
          </div>

          <div class="p-6">
            <!-- Empty State -->
            <div v-if="form.items.length === 0" class="text-center py-16">
              <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 9.776c.112-.017.227-.026.344-.026h15.812c.117 0 .232.009.344.026m-16.5 0a2.25 2.25 0 0 0-1.883 2.542l.857 6a2.25 2.25 0 0 0 2.227 1.932H19.05a2.25 2.25 0 0 0 2.227-1.932l.857-6a2.25 2.25 0 0 0-1.883-2.542m-16.5 0V6A2.25 2.25 0 0 1 6 3.75h3.879a1.5 1.5 0 0 1 1.06.44l2.122 2.12a1.5 1.5 0 0 0 1.06.44H18A2.25 2.25 0 0 1 20.25 9v.776" />
              </svg>
              <h3 class="text-lg font-medium text-gray-900 mb-2">Henüz kalem eklenmedi</h3>
              <p class="text-gray-500 mb-6">"Kalem Ekle" butonuna tıklayarak talep kalemlerini ekleyin.</p>
            </div>

            <!-- Items List -->
            <div v-else class="space-y-6">
              <div
                v-for="(item, index) in form.items"
                :key="index"
                class="relative p-6 border-2 border-gray-200 rounded-xl hover:border-blue-300 transition-colors"
              >
                <!-- Item Header -->
                <div class="flex items-center justify-between mb-4 pb-4 border-b border-gray-200">
                  <div class="flex items-center space-x-3">
                    <div class="flex-shrink-0 w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                      <span class="text-blue-600 font-bold text-sm">#{{ index + 1 }}</span>
                    </div>
                    <h4 class="text-base font-semibold text-gray-900">Kalem {{ index + 1 }}</h4>
                  </div>
                  <button
                    type="button"
                    @click="removeItem(index)"
                    class="text-red-600 hover:text-red-900 hover:bg-red-50 p-2 rounded-lg transition-colors"
                    title="Kalemi Sil"
                  >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                  </button>
                </div>

                <!-- Item Fields -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                  <!-- Malzeme Seçimi -->
                  <div class="lg:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                      Malzeme <span class="text-red-500">*</span>
                    </label>
                    <div class="flex gap-2">
                      <select
                        v-model="item.material_id"
                        class="block w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        @change="onMaterialSelect(index, item.material_id)"
                      >
                        <option value="">Malzeme Seçin veya Manuel Girin</option>
                        <optgroup label="Kayıtlı Malzemeler">
                          <option v-for="material in materials" :key="material.id" :value="material.id">
                            {{ material.name }} ({{ material.material_code }})
                          </option>
                        </optgroup>
                      </select>
                      <Link
                        :href="route('materials.create')"
                        target="_blank"
                        class="flex-shrink-0 inline-flex items-center px-3 py-2.5 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors"
                        title="Yeni Malzeme Ekle"
                      >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                      </Link>
                    </div>
                  </div>

                  <!-- Malzeme Adı (Manuel Giriş) -->
                  <div class="lg:col-span-2" v-if="!item.material_id">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                      Malzeme Adı (Manuel) <span class="text-red-500">*</span>
                    </label>
                    <input
                      v-model="item.item_name"
                      type="text"
                      class="block w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                      placeholder="Örn: C25 Beton"
                      :required="!item.material_id"
                    />
                  </div>

                  <!-- Seçili Malzeme Bilgisi -->
                  <div class="lg:col-span-3" v-if="item.material_id && item.item_name">
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                      <div class="flex items-start">
                        <svg class="w-5 h-5 text-blue-600 mt-0.5 mr-2 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div class="text-sm text-blue-800">
                          <span class="font-medium">Seçili Malzeme:</span> {{ item.item_name }}
                          <span v-if="item.category" class="ml-2 text-blue-600">({{ item.category }})</span>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Alt Kategori -->
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                      Alt Kategori
                    </label>
                    <input
                      v-model="item.category"
                      type="text"
                      class="block w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                      placeholder="Örn: İnşaat Malzemesi"
                    />
                  </div>

                  <!-- Miktar -->
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                      Miktar <span class="text-red-500">*</span>
                    </label>
                    <input
                      v-model="item.quantity"
                      type="number"
                      step="0.01"
                      min="0.01"
                      class="block w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                      placeholder="0"
                      required
                      @input="calculateItemTotal(index)"
                    />
                  </div>

                  <!-- Birim -->
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                      Birim <span class="text-red-500">*</span>
                    </label>
                    <input
                      v-model="item.unit"
                      type="text"
                      class="block w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                      placeholder="m³, kg, adet"
                      required
                    />
                  </div>

                  <!-- Birim Fiyat -->
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                      Birim Fiyat (₺) <span class="text-red-500">*</span>
                    </label>
                    <input
                      v-model="item.estimated_unit_price"
                      type="number"
                      step="0.01"
                      min="0"
                      class="block w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                      placeholder="0.00"
                      required
                      @input="calculateItemTotal(index)"
                    />
                  </div>

                  <!-- Açıklama -->
                  <div class="lg:col-span-3">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                      Açıklama / Spesifikasyon
                    </label>
                    <textarea
                      v-model="item.description"
                      rows="3"
                      class="block w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none"
                      placeholder="Teknik özellikler, marka tercihi, kalite standartları vb."
                    />
                  </div>

                  <!-- Toplam Fiyat -->
                  <div class="lg:col-span-3">
                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 p-4 rounded-lg">
                      <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-700">Kalem Toplamı:</span>
                        <span class="text-lg font-bold text-blue-600">
                          {{ formatCurrency(item.quantity * item.estimated_unit_price) }}
                        </span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Genel Toplam -->
            <div v-if="form.items.length > 0" class="mt-6 pt-6 border-t-2 border-gray-200">
              <div class="bg-gradient-to-r from-green-50 to-emerald-50 border-2 border-green-200 p-6 rounded-xl">
                <div class="flex items-center justify-between">
                  <div>
                    <p class="text-sm text-gray-600 mb-1">Genel Toplam Tutar</p>
                    <p class="text-xs text-gray-500">{{ form.items.length }} kalem</p>
                  </div>
                  <div class="text-right">
                    <p class="text-3xl font-bold text-green-600">
                      {{ formatCurrency(calculateTotal()) }}
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Form Actions -->
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 p-6">
          <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="text-sm text-gray-600">
              <p v-if="form.items.length === 0" class="text-orange-600 font-medium">
                ⚠ En az bir kalem eklemelisiniz
              </p>
              <p v-else class="text-green-600 font-medium">
                ✓ {{ form.items.length }} kalem eklendi
              </p>
            </div>
            <div class="flex items-center gap-3">
              <Link
                :href="route('purchasing-requests.index')"
                class="px-6 py-3 border-2 border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 transition-colors"
              >
                İptal
              </Link>
              <button
                type="submit"
                :disabled="processing || form.items.length === 0"
                class="px-8 py-3 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-medium rounded-lg shadow-lg hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200"
              >
                <span v-if="processing" class="flex items-center">
                  <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  Kaydediliyor...
                </span>
                <span v-else class="flex items-center">
                  <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  Talebi Oluştur
                </span>
              </button>
            </div>
          </div>
        </div>
      </form>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { uniqBy } from 'lodash'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  projects: {
    type: Array,
    default: () => []
  },
  departments: {
    type: Array,
    default: () => []
  },
  materials: {
    type: Array,
    default: () => []
  },
  errors: {
    type: Object,
    default: () => ({})
  }
})

// Unique departments - Remove duplicates by name
const uniqueDepartments = computed(() => {
  return uniqBy(props.departments, 'name')
})

// Unique projects - Remove duplicates by name
const uniqueProjects = computed(() => {
  return uniqBy(props.projects, 'name')
})

const form = ref({
  title: '',
  description: '',
  project_id: '',
  department_id: '',
  urgency: 'normal',
  category: '',
  required_date: '',
  items: []
})

const processing = ref(false)

const tomorrow = computed(() => {
  const date = new Date()
  date.setDate(date.getDate() + 1)
  return date.toISOString().split('T')[0]
})

const addItem = () => {
  form.value.items.push({
    material_id: '',
    item_name: '',
    description: '',
    specification: '',
    category: '',
    quantity: 1,
    unit: '',
    estimated_unit_price: 0
  })
}

const removeItem = (index) => {
  if (confirm('Bu kalemi silmek istediğinizden emin misiniz?')) {
    form.value.items.splice(index, 1)
  }
}

// Malzeme seçildiğinde bilgileri otomatik doldur
const onMaterialSelect = (index, materialId) => {
  if (!materialId) {
    // Malzeme seçimi kaldırıldıysa, alanları temizle
    form.value.items[index].item_name = ''
    form.value.items[index].description = ''
    form.value.items[index].specification = ''
    form.value.items[index].category = ''
    form.value.items[index].unit = ''
    form.value.items[index].estimated_unit_price = 0
    return
  }

  const material = props.materials.find(m => m.id === parseInt(materialId))
  if (material) {
    form.value.items[index].item_name = material.name
    form.value.items[index].description = material.description || ''
    form.value.items[index].specification = material.specification || ''
    form.value.items[index].category = material.category || ''
    form.value.items[index].unit = material.unit || ''
    form.value.items[index].estimated_unit_price = parseFloat(material.estimated_unit_price) || 0

    // Toplam fiyatı yeniden hesapla
    calculateItemTotal(index)
  }
}

const calculateItemTotal = (index) => {
  const item = form.value.items[index]
  if (item.quantity && item.estimated_unit_price) {
    item.estimated_total_price = item.quantity * item.estimated_unit_price
  }
}

const calculateTotal = () => {
  return form.value.items.reduce((total, item) => {
    return total + (parseFloat(item.quantity || 0) * parseFloat(item.estimated_unit_price || 0))
  }, 0)
}

const formatCurrency = (amount) => {
  if (!amount) return '₺0,00'
  return new Intl.NumberFormat('tr-TR', {
    style: 'currency',
    currency: 'TRY'
  }).format(amount)
}

const submitForm = () => {
  if (form.value.items.length === 0) {
    alert('En az bir kalem eklemelisiniz!')
    return
  }

  processing.value = true

  router.post(route('purchasing-requests.store'), form.value, {
    onFinish: () => {
      processing.value = false
    }
  })
}
</script>

<style scoped>
.w-full {
  width: 100% !important;
}

/* Smooth transitions */
.transition-colors {
  transition-property: color, background-color, border-color;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 200ms;
}

/* Loading spinner animation */
@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

.animate-spin {
  animation: spin 1s linear infinite;
}

/* Focus visible styles */
input:focus-visible,
select:focus-visible,
textarea:focus-visible {
  outline: 2px solid #3b82f6;
  outline-offset: 2px;
}
</style>
