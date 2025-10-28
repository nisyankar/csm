<template>
  <AppLayout title="Yeni Ruhsat Ekle" :full-width="true">
    <!-- Full-width header -->
    <template #fullWidthHeader>
      <div class="bg-gradient-to-r from-amber-600 via-amber-700 to-orange-800 border-b border-amber-900/20 w-full">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
          <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
              <div class="flex-shrink-0 w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm">
                <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
              </div>
              <div>
                <h1 class="text-2xl lg:text-3xl font-bold text-white">Yeni Ruhsat Ekle</h1>
                <p class="text-amber-100 text-sm mt-1">Yapı Ruhsatı, İskan İzni veya Diğer İzinler</p>
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
                  <Link :href="route('dashboard')" class="text-amber-100 hover:text-white transition-colors">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                    </svg>
                  </Link>
                </li>
                <li class="flex items-center">
                  <svg class="h-3 w-3 text-amber-200 mx-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                  </svg>
                  <Link :href="route('construction-permits.index')" class="text-amber-100 hover:text-white text-sm">Ruhsat Listesi</Link>
                </li>
                <li class="flex items-center">
                  <svg class="h-3 w-3 text-amber-200 mx-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
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
              <!-- Proje -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Proje <span class="text-red-500">*</span>
                </label>
                <select
                  v-model="form.project_id"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-all"
                  :class="{'border-red-300 focus:ring-red-500': form.errors.project_id}"
                  required
                >
                  <option :value="null">Seçiniz...</option>
                  <option v-for="project in projects" :key="project.id" :value="project.id">
                    {{ project.name }} ({{ project.project_code }})
                  </option>
                </select>
                <p v-if="form.errors.project_id" class="text-red-600 text-sm mt-2">
                  {{ form.errors.project_id }}
                </p>
              </div>

              <!-- Ruhsat Türü -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Ruhsat Türü <span class="text-red-500">*</span>
                </label>
                <select
                  v-model="form.permit_type"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-all"
                  :class="{'border-red-300 focus:ring-red-500': form.errors.permit_type}"
                  required
                >
                  <option :value="null">Tür Seçin</option>
                  <option value="building">Yapı Ruhsatı (Proje Geneli)</option>
                  <option value="demolition">Yıkım Ruhsatı (Proje Geneli)</option>
                  <option value="occupancy">İskan İzni (Birim Bazlı)</option>
                  <option value="usage">Yapı Kullanma İzni (Birim Bazlı)</option>
                </select>
                <p v-if="form.errors.permit_type" class="text-red-600 text-sm mt-2">
                  {{ form.errors.permit_type }}
                </p>
                <p v-if="form.permit_type === 'occupancy' || form.permit_type === 'usage'" class="text-blue-600 text-sm mt-2">
                  Bu ruhsat türü için birim seçimi yapabilirsiniz
                </p>
              </div>

              <!-- Birim Seçimi (İskan/Kullanma İzni için) -->
              <div v-if="form.permit_type === 'occupancy' || form.permit_type === 'usage'" class="lg:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Birim
                </label>
                <select
                  v-model="form.project_unit_id"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-all"
                  :class="{'border-red-300 focus:ring-red-500': form.errors.project_unit_id}"
                  :disabled="!form.project_id"
                >
                  <option :value="null">Tüm Proje (Birim Bazlı Değil)</option>
                  <option v-for="unit in projectUnits" :key="unit.id" :value="unit.id">
                    {{ unit.unit_code }} - {{ unit.unit_type }} ({{ unit.gross_area }}m²)
                  </option>
                </select>
                <p v-if="form.errors.project_unit_id" class="text-red-600 text-sm mt-2">
                  {{ form.errors.project_unit_id }}
                </p>
                <p v-if="!form.project_id" class="text-gray-600 text-sm mt-2">Önce proje seçiniz</p>
                <p v-else-if="projectUnits.length === 0" class="text-gray-600 text-sm mt-2">Bu projede kayıtlı birim bulunmuyor</p>
              </div>

              <!-- Ruhsat Numarası -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Ruhsat Numarası
                </label>
                <input
                  v-model="form.permit_number"
                  type="text"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-all"
                  :class="{'border-red-300 focus:ring-red-500': form.errors.permit_number}"
                  placeholder="Boş bırakılırsa otomatik oluşturulur"
                />
                <p v-if="form.errors.permit_number" class="text-red-600 text-sm mt-2">
                  {{ form.errors.permit_number }}
                </p>
                <p class="text-gray-600 text-sm mt-2">Boş bırakılırsa proje koduna göre otomatik oluşturulur</p>
              </div>

              <!-- Durum -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Durum <span class="text-red-500">*</span>
                </label>
                <select
                  v-model="form.status"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-all"
                  :class="{'border-red-300 focus:ring-red-500': form.errors.status}"
                  required
                >
                  <option value="pending">Beklemede</option>
                  <option value="approved">Onaylandı</option>
                  <option value="rejected">Reddedildi</option>
                  <option value="expired">Süresi Doldu</option>
                  <option value="renewed">Yenilendi</option>
                </select>
                <p v-if="form.errors.status" class="text-red-600 text-sm mt-2">
                  {{ form.errors.status }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Kurum ve İmar Bilgileri -->
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-medium text-gray-900">Kurum ve İmar Bilgileri</h3>
          </div>
          <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
              <!-- Veren Kurum -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Veren Kurum/Belediye
                </label>
                <input
                  v-model="form.issuing_authority"
                  type="text"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-all"
                  :class="{'border-red-300 focus:ring-red-500': form.errors.issuing_authority}"
                  placeholder="Ör: İstanbul Büyükşehir Belediyesi"
                />
                <p v-if="form.errors.issuing_authority" class="text-red-600 text-sm mt-2">
                  {{ form.errors.issuing_authority }}
                </p>
              </div>

              <!-- İmar Durumu -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  İmar Durumu
                </label>
                <input
                  v-model="form.zoning_status"
                  type="text"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-all"
                  :class="{'border-red-300 focus:ring-red-500': form.errors.zoning_status}"
                  placeholder="Ör: Konut Alanı"
                />
                <p v-if="form.errors.zoning_status" class="text-red-600 text-sm mt-2">
                  {{ form.errors.zoning_status }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Tarihler -->
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-medium text-gray-900">Tarihler</h3>
          </div>
          <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
              <!-- Başvuru Tarihi -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Başvuru Tarihi
                </label>
                <input
                  v-model="form.application_date"
                  type="date"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-all"
                  :class="{'border-red-300 focus:ring-red-500': form.errors.application_date}"
                />
                <p v-if="form.errors.application_date" class="text-red-600 text-sm mt-2">
                  {{ form.errors.application_date }}
                </p>
              </div>

              <!-- Onay Tarihi -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Onay Tarihi
                </label>
                <input
                  v-model="form.approval_date"
                  type="date"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-all"
                  :class="{'border-red-300 focus:ring-red-500': form.errors.approval_date}"
                />
                <p v-if="form.errors.approval_date" class="text-red-600 text-sm mt-2">
                  {{ form.errors.approval_date }}
                </p>
              </div>

              <!-- Son Geçerlilik Tarihi -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Son Geçerlilik Tarihi
                </label>
                <input
                  v-model="form.expiry_date"
                  type="date"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-all"
                  :class="{'border-red-300 focus:ring-red-500': form.errors.expiry_date}"
                />
                <p v-if="form.errors.expiry_date" class="text-red-600 text-sm mt-2">
                  {{ form.errors.expiry_date }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Notlar -->
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-medium text-gray-900">Notlar</h3>
          </div>
          <div class="p-6">
            <textarea
              v-model="form.notes"
              rows="4"
              class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-all"
              :class="{'border-red-300 focus:ring-red-500': form.errors.notes}"
              placeholder="Ruhsat ile ilgili notlar, özel durumlar..."
            ></textarea>
            <p v-if="form.errors.notes" class="text-red-600 text-sm mt-2">
              {{ form.errors.notes }}
            </p>
          </div>
        </div>

        <!-- Form Actions -->
        <div class="flex items-center justify-end space-x-3">
          <Link
            :href="route('construction-permits.index')"
            class="inline-flex items-center px-6 py-3 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition-colors"
          >
            İptal
          </Link>
          <button
            type="submit"
            :disabled="form.processing"
            class="inline-flex items-center px-6 py-3 bg-amber-600 text-white text-sm font-medium rounded-lg hover:bg-amber-700 transition-colors disabled:opacity-50"
          >
            <svg v-if="!form.processing" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            <svg v-else class="animate-spin h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            {{ form.processing ? 'Kaydediliyor...' : 'Kaydet' }}
          </button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed, watch } from 'vue'
import { useForm, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  projects: Array,
})

const form = useForm({
  project_id: null,
  project_unit_id: null,
  permit_type: null,
  permit_number: '',
  application_date: null,
  approval_date: null,
  expiry_date: null,
  status: 'pending',
  issuing_authority: '',
  zoning_status: '',
  notes: '',
})

// Seçili projenin birimlerini getir
const projectUnits = computed(() => {
  if (!form.project_id) return []

  const selectedProject = props.projects.find(p => p.id === form.project_id)
  return selectedProject?.units || []
})

// Proje değiştiğinde birim seçimini sıfırla
watch(() => form.project_id, () => {
  form.project_unit_id = null
})

const submit = () => {
  form.post(route('construction-permits.store'))
}
</script>
