<template>
  <AppLayout :title="`Ruhsat Düzenle - ${permit.permit_number}`" :full-width="true">
    <!-- Header -->
    <template #fullWidthHeader>
      <div class="bg-gradient-to-r from-amber-600 via-amber-700 to-orange-800 border-b border-amber-900/20 w-full">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
          <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <div class="flex-1 min-w-0">
              <div class="flex items-center space-x-4">
                <div class="flex-shrink-0 w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm">
                  <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                  </svg>
                </div>
                <div>
                  <h1 class="text-2xl lg:text-3xl font-bold text-white">Ruhsat Düzenle</h1>
                  <p class="text-amber-100 text-sm mt-1">{{ permit.permit_number }}</p>
                </div>
              </div>
            </div>

            <div class="flex-shrink-0 flex items-center space-x-3">
              <Link
                :href="route('construction-permits.show', permit.id)"
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
      </div>
    </template>

    <!-- Main Content -->
    <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
      <div class="max-w-4xl mx-auto">
        <form @submit.prevent="submit" class="space-y-6">
          <!-- Temel Bilgiler -->
          <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
              <h3 class="text-lg font-medium text-gray-900">Temel Bilgiler</h3>
            </div>
            <div class="px-6 py-6 space-y-6">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Proje -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    Proje *
                  </label>
                  <select
                    v-model="form.project_id"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500"
                    :class="{ 'border-red-300': form.errors.project_id }"
                    required
                  >
                    <option :value="null">Proje Seçin</option>
                    <option v-for="project in projects" :key="project.id" :value="project.id">
                      {{ project.name }} ({{ project.project_code }})
                    </option>
                  </select>
                  <p v-if="form.errors.project_id" class="mt-1 text-sm text-red-600">{{ form.errors.project_id }}</p>
                </div>

                <!-- Ruhsat Türü -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    Ruhsat Türü *
                  </label>
                  <select
                    v-model="form.permit_type"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500"
                    :class="{ 'border-red-300': form.errors.permit_type }"
                    required
                  >
                    <option :value="null">Tür Seçin</option>
                    <option value="building">Yapı Ruhsatı</option>
                    <option value="demolition">Yıkım Ruhsatı</option>
                    <option value="occupancy">İskan İzni</option>
                    <option value="usage">Yapı Kullanma İzni</option>
                  </select>
                  <p v-if="form.errors.permit_type" class="mt-1 text-sm text-red-600">{{ form.errors.permit_type }}</p>
                </div>

                <!-- Ruhsat Numarası -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    Ruhsat Numarası *
                  </label>
                  <input
                    v-model="form.permit_number"
                    type="text"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500"
                    :class="{ 'border-red-300': form.errors.permit_number }"
                    required
                  />
                  <p v-if="form.errors.permit_number" class="mt-1 text-sm text-red-600">{{ form.errors.permit_number }}</p>
                </div>

                <!-- Durum -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    Durum *
                  </label>
                  <select
                    v-model="form.status"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500"
                    :class="{ 'border-red-300': form.errors.status }"
                    required
                  >
                    <option value="pending">Beklemede</option>
                    <option value="approved">Onaylandı</option>
                    <option value="rejected">Reddedildi</option>
                    <option value="expired">Süresi Doldu</option>
                    <option value="renewed">Yenilendi</option>
                  </select>
                  <p v-if="form.errors.status" class="mt-1 text-sm text-red-600">{{ form.errors.status }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Kurum ve İmar Bilgileri -->
          <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
              <h3 class="text-lg font-medium text-gray-900">Kurum ve İmar Bilgileri</h3>
            </div>
            <div class="px-6 py-6 space-y-6">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Veren Kurum -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    Veren Kurum/Belediye
                  </label>
                  <input
                    v-model="form.issuing_authority"
                    type="text"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500"
                    :class="{ 'border-red-300': form.errors.issuing_authority }"
                    placeholder="Ör: İstanbul Büyükşehir Belediyesi"
                  />
                  <p v-if="form.errors.issuing_authority" class="mt-1 text-sm text-red-600">{{ form.errors.issuing_authority }}</p>
                </div>

                <!-- İmar Durumu -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    İmar Durumu
                  </label>
                  <input
                    v-model="form.zoning_status"
                    type="text"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500"
                    :class="{ 'border-red-300': form.errors.zoning_status }"
                    placeholder="Ör: Konut Alanı"
                  />
                  <p v-if="form.errors.zoning_status" class="mt-1 text-sm text-red-600">{{ form.errors.zoning_status }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Tarihler -->
          <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
              <h3 class="text-lg font-medium text-gray-900">Tarihler</h3>
            </div>
            <div class="px-6 py-6 space-y-6">
              <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Başvuru Tarihi -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    Başvuru Tarihi
                  </label>
                  <input
                    v-model="form.application_date"
                    type="date"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500"
                    :class="{ 'border-red-300': form.errors.application_date }"
                  />
                  <p v-if="form.errors.application_date" class="mt-1 text-sm text-red-600">{{ form.errors.application_date }}</p>
                </div>

                <!-- Onay Tarihi -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    Onay Tarihi
                  </label>
                  <input
                    v-model="form.approval_date"
                    type="date"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500"
                    :class="{ 'border-red-300': form.errors.approval_date }"
                  />
                  <p v-if="form.errors.approval_date" class="mt-1 text-sm text-red-600">{{ form.errors.approval_date }}</p>
                </div>

                <!-- Son Geçerlilik Tarihi -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    Son Geçerlilik Tarihi
                  </label>
                  <input
                    v-model="form.expiry_date"
                    type="date"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500"
                    :class="{ 'border-red-300': form.errors.expiry_date }"
                  />
                  <p v-if="form.errors.expiry_date" class="mt-1 text-sm text-red-600">{{ form.errors.expiry_date }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Notlar -->
          <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
              <h3 class="text-lg font-medium text-gray-900">Notlar</h3>
            </div>
            <div class="px-6 py-6">
              <textarea
                v-model="form.notes"
                rows="4"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500"
                :class="{ 'border-red-300': form.errors.notes }"
                placeholder="Ruhsat ile ilgili notlar, özel durumlar..."
              ></textarea>
              <p v-if="form.errors.notes" class="mt-1 text-sm text-red-600">{{ form.errors.notes }}</p>
            </div>
          </div>

          <!-- Form Actions -->
          <div class="flex items-center justify-between">
            <button
              type="button"
              @click="deletePermit"
              class="inline-flex items-center px-6 py-3 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition-colors"
            >
              <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
              </svg>
              Sil
            </button>

            <div class="flex items-center space-x-3">
              <Link
                :href="route('construction-permits.show', permit.id)"
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
                  <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                </svg>
                <svg v-else class="animate-spin h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                {{ form.processing ? 'Kaydediliyor...' : 'Değişiklikleri Kaydet' }}
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { useForm, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  permit: Object,
  projects: Array,
})

const form = useForm({
  project_id: props.permit.project_id,
  permit_type: props.permit.permit_type,
  permit_number: props.permit.permit_number,
  application_date: props.permit.application_date,
  approval_date: props.permit.approval_date,
  expiry_date: props.permit.expiry_date,
  status: props.permit.status,
  issuing_authority: props.permit.issuing_authority,
  zoning_status: props.permit.zoning_status,
  notes: props.permit.notes,
})

const submit = () => {
  form.put(route('construction-permits.update', props.permit.id))
}

const deletePermit = () => {
  if (confirm('Bu ruhsatı silmek istediğinize emin misiniz? Bu işlem geri alınamaz.')) {
    router.delete(route('construction-permits.destroy', props.permit.id))
  }
}
</script>
