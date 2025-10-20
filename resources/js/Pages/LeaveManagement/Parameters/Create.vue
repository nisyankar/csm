<template>
  <AppLayout title="Yeni İzin Parametresi">
    <template #header>
      <div class="flex items-center space-x-4">
        <Link :href="route('leave-management.parameters.index')" class="text-gray-500 hover:text-gray-700">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
          </svg>
        </Link>
        <div>
          <h2 class="text-xl font-semibold text-gray-900">Yeni İzin Parametresi</h2>
          <p class="text-sm text-gray-600">Yeni sistem parametresi oluşturun</p>
        </div>
      </div>
    </template>

    <div class="max-w-4xl mx-auto">
      <form @submit.prevent="submit" class="space-y-6">
        <!-- Basic Information -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Temel Bilgiler</h3>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Parametre Adı *
              </label>
              <input
                v-model="form.name"
                type="text"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                :class="{ 'border-red-300': errors.name }"
                placeholder="Örn: Yıllık İzin Gün Sayısı"
              />
              <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Parametre Anahtarı *
              </label>
              <input
                v-model="form.parameter_key"
                type="text"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                :class="{ 'border-red-300': errors.parameter_key }"
                placeholder="Örn: annual_leave_days"
              />
              <p v-if="errors.parameter_key" class="mt-1 text-sm text-red-600">{{ errors.parameter_key }}</p>
              <p class="mt-1 text-xs text-gray-500">Sistem içinde kullanılacak benzersiz anahtar</p>
            </div>
          </div>

          <div class="mt-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Açıklama
            </label>
            <textarea
              v-model="form.description"
              rows="3"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              :class="{ 'border-red-300': errors.description }"
              placeholder="Parametrenin ne işe yaradığını açıklayın..."
            ></textarea>
            <p v-if="errors.description" class="mt-1 text-sm text-red-600">{{ errors.description }}</p>
          </div>
        </div>

        <!-- Type and Category -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Tür ve Kategori</h3>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Kategori *
              </label>
              <select
                v-model="form.category"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                :class="{ 'border-red-300': errors.category }"
              >
                <option value="">Kategori Seçin</option>
                <option value="annual_leave">Yıllık İzin</option>
                <option value="sick_leave">Hastalık İzni</option>
                <option value="maternity_leave">Doğum İzni</option>
                <option value="paternity_leave">Babalık İzni</option>
                <option value="unpaid_leave">Ücretsiz İzin</option>
                <option value="calculation">Hesaplama</option>
                <option value="eligibility">Uygunluk</option>
                <option value="restrictions">Kısıtlamalar</option>
              </select>
              <p v-if="errors.category" class="mt-1 text-sm text-red-600">{{ errors.category }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Veri Türü *
              </label>
              <select
                v-model="form.type"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                :class="{ 'border-red-300': errors.type }"
              >
                <option value="">Tür Seçin</option>
                <option value="integer">Tam Sayı</option>
                <option value="decimal">Ondalık Sayı</option>
                <option value="boolean">Evet/Hayır</option>
                <option value="string">Metin</option>
                <option value="date">Tarih</option>
                <option value="json">JSON</option>
              </select>
              <p v-if="errors.type" class="mt-1 text-sm text-red-600">{{ errors.type }}</p>
            </div>
          </div>
        </div>

        <!-- Value Configuration -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Değer Ayarları</h3>
          
          <div class="space-y-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Varsayılan Değer
              </label>
              <input
                v-model="form.default_value"
                :type="getInputType()"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                :class="{ 'border-red-300': errors.default_value }"
                :placeholder="getPlaceholder()"
              />
              <p v-if="errors.default_value" class="mt-1 text-sm text-red-600">{{ errors.default_value }}</p>
            </div>

            <div v-if="form.type === 'integer' || form.type === 'decimal'" class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Minimum Değer
                </label>
                <input
                  v-model="form.min_value"
                  type="number"
                  step="any"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  :class="{ 'border-red-300': errors.min_value }"
                />
                <p v-if="errors.min_value" class="mt-1 text-sm text-red-600">{{ errors.min_value }}</p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Maksimum Değer
                </label>
                <input
                  v-model="form.max_value"
                  type="number"
                  step="any"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  :class="{ 'border-red-300': errors.max_value }"
                />
                <p v-if="errors.max_value" class="mt-1 text-sm text-red-600">{{ errors.max_value }}</p>
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Doğrulama Kuralları
              </label>
              <textarea
                v-model="form.validation_rules"
                rows="2"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                :class="{ 'border-red-300': errors.validation_rules }"
                placeholder="Ek doğrulama kuralları (opsiyonel)"
              ></textarea>
              <p v-if="errors.validation_rules" class="mt-1 text-sm text-red-600">{{ errors.validation_rules }}</p>
            </div>
          </div>
        </div>

        <!-- Settings -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Ayarlar</h3>
          
          <div class="space-y-4">
            <div class="flex items-center">
              <input
                v-model="form.is_system"
                type="checkbox"
                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
              />
              <label class="ml-2 block text-sm text-gray-900">
                Sistem Parametresi
              </label>
              <span class="ml-2 text-xs text-gray-500">(Sistem parametreleri sonradan değiştirilemez)</span>
            </div>

            <div class="flex items-center">
              <input
                v-model="form.is_editable"
                type="checkbox"
                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
              />
              <label class="ml-2 block text-sm text-gray-900">
                Düzenlenebilir
              </label>
            </div>

            <div class="flex items-center">
              <input
                v-model="form.applies_to_all"
                type="checkbox"
                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
              />
              <label class="ml-2 block text-sm text-gray-900">
                Tüm Çalışanlara Uygulanır
              </label>
            </div>

            <div v-if="!form.applies_to_all">
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Çalışan Kategorileri
              </label>
              <div class="space-y-2">
                <label v-for="(label, category) in employeeCategories" :key="category" class="flex items-center">
                  <input
                    v-model="form.employee_categories"
                    :value="category"
                    type="checkbox"
                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                  />
                  <span class="ml-2 text-sm text-gray-900">{{ label }}</span>
                </label>
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Durum
              </label>
              <select
                v-model="form.status"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                :class="{ 'border-red-300': errors.status }"
              >
                <option value="active">Aktif</option>
                <option value="inactive">Pasif</option>
              </select>
              <p v-if="errors.status" class="mt-1 text-sm text-red-600">{{ errors.status }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Sıralama
              </label>
              <input
                v-model="form.sort_order"
                type="number"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                :class="{ 'border-red-300': errors.sort_order }"
                placeholder="0"
              />
              <p v-if="errors.sort_order" class="mt-1 text-sm text-red-600">{{ errors.sort_order }}</p>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex justify-end space-x-4">
          <Link
            :href="route('leave-management.parameters.index')"
            class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            İptal
          </Link>
          <button
            type="submit"
            :disabled="processing"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50"
          >
            {{ processing ? 'Kaydediliyor...' : 'Kaydet' }}
          </button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>

<script setup>
import { reactive, computed } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  categories: Object,
  employeeCategories: Object,
  errors: Object,
})

const form = useForm({
  name: '',
  parameter_key: '',
  description: '',
  type: '',
  category: '',
  default_value: '',
  min_value: null,
  max_value: null,
  validation_rules: '',
  status: 'active',
  is_system: false,
  is_editable: true,
  applies_to_all: true,
  employee_categories: [],
  sort_order: 0,
})

const employeeCategories = {
  worker: 'İşçi',
  foreman: 'Forman',
  engineer: 'Mühendis',
  manager: 'Proje Yöneticisi',
  system_admin: 'Sistem Yöneticisi',
}

const getInputType = () => {
  switch (form.type) {
    case 'integer':
      return 'number'
    case 'decimal':
      return 'number'
    case 'date':
      return 'date'
    case 'boolean':
      return 'checkbox'
    default:
      return 'text'
  }
}

const getPlaceholder = () => {
  switch (form.type) {
    case 'integer':
      return 'Örn: 20'
    case 'decimal':
      return 'Örn: 1.5'
    case 'boolean':
      return 'true/false'
    case 'date':
      return 'YYYY-MM-DD'
    case 'json':
      return 'Örn: {"key": "value"}'
    default:
      return 'Değer girin...'
  }
}

const submit = () => {
  form.post(route('leave-management.parameters.store'))
}

const processing = computed(() => form.processing)
</script>