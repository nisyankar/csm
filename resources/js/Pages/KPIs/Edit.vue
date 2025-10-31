<template>
  <AppLayout title="KPI Düzenle" :full-width="true">
    <template #fullWidthHeader>
      <div class="bg-gradient-to-r from-teal-600 via-cyan-600 to-blue-600 border-b border-teal-900/20 w-full">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
          <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <div class="flex-1 min-w-0">
              <div class="flex items-center space-x-4">
                <div class="flex-shrink-0 w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm">
                  <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                  </svg>
                </div>
                <div>
                  <h1 class="text-2xl lg:text-3xl font-bold text-white">KPI Düzenle</h1>
                  <p class="text-teal-100 text-sm mt-1">{{ kpi.name }}</p>
                </div>
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
                  <Link :href="route('dashboard')" class="text-teal-100 hover:text-white transition-colors">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                    </svg>
                  </Link>
                </li>
                <li class="flex items-center">
                  <svg class="h-3 w-3 text-teal-200 mx-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                  </svg>
                  <Link :href="route('kpis.index')" class="text-teal-100 hover:text-white text-sm transition-colors">KPI Tanımları</Link>
                </li>
                <li class="flex items-center">
                  <svg class="h-3 w-3 text-teal-200 mx-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                  </svg>
                  <span class="text-white font-medium text-sm">Düzenle</span>
                </li>
              </ol>
            </div>
          </div>
        </div>
      </div>
    </template>

    <!-- Main Content -->
    <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
      <form @submit.prevent="submit" class="max-w-4xl mx-auto">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden">
          <!-- Form Header -->
          <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">KPI Bilgileri</h3>
            <p class="text-sm text-gray-600 mt-1">Performans göstergesi bilgilerini düzenleyin</p>
          </div>

          <!-- Form Fields -->
          <div class="p-6 space-y-6">
            <!-- Name -->
            <div>
              <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                KPI Adı <span class="text-red-500">*</span>
              </label>
              <input
                id="name"
                v-model="form.name"
                type="text"
                required
                class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-teal-500 focus:border-transparent transition-all"
                placeholder="Örn: Proje Tamamlanma Yüzdesi"
              />
              <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
            </div>

            <!-- Module & Unit Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label for="module" class="block text-sm font-medium text-gray-700 mb-2">
                  Modül <span class="text-red-500">*</span>
                </label>
                <select
                  id="module"
                  v-model="form.module"
                  required
                  class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-teal-500 focus:border-transparent transition-all"
                >
                  <option value="">Seçiniz</option>
                  <option value="progress_payments">Hakediş</option>
                  <option value="timesheets">Puantaj</option>
                  <option value="financials">Finansal</option>
                  <option value="safety">İSG</option>
                  <option value="equipment">Ekipman</option>
                  <option value="stock">Stok</option>
                  <option value="quantities">Metraj</option>
                  <option value="projects">Projeler</option>
                  <option value="general">Genel</option>
                </select>
                <p v-if="form.errors.module" class="mt-1 text-sm text-red-600">{{ form.errors.module }}</p>
              </div>

              <div>
                <label for="unit" class="block text-sm font-medium text-gray-700 mb-2">
                  Birim <span class="text-red-500">*</span>
                </label>
                <input
                  id="unit"
                  v-model="form.unit"
                  type="text"
                  required
                  class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-teal-500 focus:border-transparent transition-all"
                  placeholder="Örn: %, TL, Gün"
                />
                <p v-if="form.errors.unit" class="mt-1 text-sm text-red-600">{{ form.errors.unit }}</p>
              </div>
            </div>

            <!-- Formula -->
            <div>
              <label for="formula" class="block text-sm font-medium text-gray-700 mb-2">
                Formül <span class="text-red-500">*</span>
              </label>
              <select
                id="formula"
                v-model="form.formula"
                required
                class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-teal-500 focus:border-transparent transition-all font-mono"
              >
                <option value="">Seçiniz</option>
                <option value="project_completion_percentage">Proje Tamamlanma Yüzdesi</option>
                <option value="cost_variance">Maliyet Varyansı</option>
                <option value="labor_productivity">İşgücü Verimliliği</option>
                <option value="safety_incident_rate">İSG Kaza Oranı</option>
                <option value="equipment_utilization">Ekipman Kullanım Oranı</option>
                <option value="on_time_delivery">Zamanında Teslim Oranı</option>
              </select>
              <p class="mt-1 text-xs text-gray-500">Önceden tanımlı formüllerden birini seçin</p>
              <p v-if="form.errors.formula" class="mt-1 text-sm text-red-600">{{ form.errors.formula }}</p>
            </div>

            <!-- Target & Warning Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label for="target_value" class="block text-sm font-medium text-gray-700 mb-2">
                  Hedef Değer
                </label>
                <input
                  id="target_value"
                  v-model="form.target_value"
                  type="number"
                  step="0.01"
                  class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-teal-500 focus:border-transparent transition-all"
                  placeholder="Örn: 100"
                />
                <p class="mt-1 text-xs text-gray-500">KPI'ın hedeflediği değer</p>
                <p v-if="form.errors.target_value" class="mt-1 text-sm text-red-600">{{ form.errors.target_value }}</p>
              </div>

              <div>
                <label for="warning_threshold" class="block text-sm font-medium text-gray-700 mb-2">
                  Uyarı Eşiği
                </label>
                <input
                  id="warning_threshold"
                  v-model="form.warning_threshold"
                  type="number"
                  step="0.01"
                  class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-teal-500 focus:border-transparent transition-all"
                  placeholder="Örn: 80"
                />
                <p class="mt-1 text-xs text-gray-500">Bu değerin altında uyarı verilir</p>
                <p v-if="form.errors.warning_threshold" class="mt-1 text-sm text-red-600">{{ form.errors.warning_threshold }}</p>
              </div>
            </div>

            <!-- Description -->
            <div>
              <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                Açıklama
              </label>
              <textarea
                id="description"
                v-model="form.description"
                rows="3"
                class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-teal-500 focus:border-transparent transition-all"
                placeholder="KPI hakkında açıklama..."
              ></textarea>
              <p v-if="form.errors.description" class="mt-1 text-sm text-red-600">{{ form.errors.description }}</p>
            </div>

            <!-- Active Status -->
            <div class="flex items-center">
              <input
                id="is_active"
                v-model="form.is_active"
                type="checkbox"
                class="w-4 h-4 text-teal-600 border-gray-300 rounded focus:ring-teal-500"
              />
              <label for="is_active" class="ml-2 block text-sm text-gray-700">
                Aktif
              </label>
            </div>
          </div>

          <!-- Form Actions -->
          <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
            <Link
              :href="route('kpis.index')"
              class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
            >
              İptal
            </Link>
            <button
              type="submit"
              :disabled="form.processing"
              class="px-6 py-2 text-sm font-medium text-white bg-gradient-to-r from-teal-600 to-cyan-600 rounded-lg hover:from-teal-700 hover:to-cyan-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-md hover:shadow-lg"
            >
              <span v-if="!form.processing">Güncelle</span>
              <span v-else class="flex items-center">
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Güncelleniyor...
              </span>
            </button>
          </div>
        </div>
      </form>
    </div>
  </AppLayout>
</template>

<script setup>
import { useForm, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  kpi: {
    type: Object,
    required: true
  }
})

const form = useForm({
  name: props.kpi.name,
  module: props.kpi.module,
  formula: props.kpi.formula,
  target_value: props.kpi.target_value,
  warning_threshold: props.kpi.warning_threshold,
  unit: props.kpi.unit,
  description: props.kpi.description,
  is_active: props.kpi.is_active
})

const submit = () => {
  form.put(route('kpis.update', props.kpi.id), {
    onSuccess: () => {
      // Redirect will be handled by backend
    }
  })
}
</script>
