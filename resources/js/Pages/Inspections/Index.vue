<template>
  <AppLayout title="Denetim Listesi" :full-width="true">
    <template #fullWidthHeader>
      <div class="bg-gradient-to-r from-purple-600 via-purple-700 to-indigo-800 border-b border-purple-900/20 w-full">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
          <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
              <div class="flex-shrink-0 w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm">
                <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" />
                </svg>
              </div>
              <div>
                <h1 class="text-2xl lg:text-3xl font-bold text-white">Denetim Listesi</h1>
                <p class="text-purple-100 text-sm mt-1">Tüm denetim kayıtlarını görüntüleyin ve yönetin</p>
              </div>
            </div>
            <div class="flex items-center space-x-3">
              <div class="bg-white/10 backdrop-blur-sm rounded-lg px-3 py-2">
                <span class="text-purple-100 text-sm">Toplam Kayıt:</span>
                <span class="font-semibold text-white ml-1">{{ inspections?.total || 0 }}</span>
              </div>
              <Link
                :href="route('inspections.create')"
                class="inline-flex items-center px-4 py-2 bg-white text-purple-700 rounded-lg hover:bg-purple-50 font-medium transition-colors"
              >
                <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Yeni Denetim
              </Link>
            </div>
          </div>
        </div>

        <div class="bg-black/10 border-t border-white/10">
          <div class="w-full px-4 sm:px-6 lg:px-8">
            <div class="flex items-center space-x-2 py-2">
              <ol class="flex items-center space-x-2">
                <li>
                  <Link :href="route('dashboard')" class="text-purple-100 hover:text-white transition-colors">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                    </svg>
                  </Link>
                </li>
                <li class="flex items-center">
                  <svg class="h-3 w-3 text-purple-200 mx-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                  </svg>
                  <span class="text-white font-medium text-sm">Denetimler</span>
                </li>
              </ol>
            </div>
          </div>
        </div>
      </div>
    </template>

    <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
      <!-- Filters -->
      <div class="bg-white shadow-sm rounded-xl border border-gray-200 mb-6 overflow-hidden">
        <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-3 border-b border-gray-200">
          <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider">Filtreler</h3>
        </div>
        <div class="p-6">
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Arama</label>
              <input
                v-model="filterForm.search"
                type="text"
                placeholder="Denetim no, denetçi adı..."
                class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-purple-500 focus:border-transparent"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Proje</label>
              <select
                v-model="filterForm.project_id"
                class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-purple-500 focus:border-transparent"
              >
                <option value="">Tümü</option>
                <option v-for="project in projects" :key="project.id" :value="project.id">
                  {{ project.name }}
                </option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Denetim Kuruluşu</label>
              <select
                v-model="filterForm.inspection_company_id"
                class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-purple-500 focus:border-transparent"
              >
                <option value="">Tümü</option>
                <option v-for="company in inspectionCompanies" :key="company.id" :value="company.id">
                  {{ company.company_name }}
                </option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Denetim Türü</label>
              <select
                v-model="filterForm.inspection_type"
                class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-purple-500 focus:border-transparent"
              >
                <option value="">Tümü</option>
                <option value="periodic">Periyodik</option>
                <option value="special">Özel</option>
                <option value="final">Final</option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Durum</label>
              <select
                v-model="filterForm.status"
                class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-purple-500 focus:border-transparent"
              >
                <option value="">Tümü</option>
                <option value="scheduled">Planlandı</option>
                <option value="completed">Tamamlandı</option>
                <option value="pending_action">Eylem Bekliyor</option>
                <option value="closed">Kapatıldı</option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Başlangıç Tarihi</label>
              <input
                v-model="filterForm.date_from"
                type="date"
                class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-purple-500 focus:border-transparent"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Bitiş Tarihi</label>
              <input
                v-model="filterForm.date_to"
                type="date"
                class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-purple-500 focus:border-transparent"
              />
            </div>

            <div class="flex items-end">
              <button
                @click="resetFilters"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 text-gray-700 font-medium transition-colors"
              >
                Filtreleri Temizle
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Table -->
      <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Denetim No</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Proje</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Denetim Kuruluşu</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Denetçi</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tarih</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tür</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Durum</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">İşlemler</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="inspection in inspections.data" :key="inspection.id" class="hover:bg-gray-50 transition-colors">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-gray-900">{{ inspection.inspection_number }}</div>
                </td>
                <td class="px-6 py-4">
                  <div class="text-sm text-gray-900">{{ inspection.project?.name }}</div>
                  <div class="text-xs text-gray-500">{{ inspection.project?.project_code }}</div>
                </td>
                <td class="px-6 py-4">
                  <div class="text-sm text-gray-900">{{ inspection.inspection_company?.company_name }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900">{{ inspection.inspector_name }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900">{{ new Date(inspection.inspection_date).toLocaleDateString('tr-TR') }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span
                    class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                    :class="{
                      'bg-blue-100 text-blue-800': inspection.inspection_type === 'periodic',
                      'bg-purple-100 text-purple-800': inspection.inspection_type === 'special',
                      'bg-green-100 text-green-800': inspection.inspection_type === 'final'
                    }"
                  >
                    {{ getInspectionTypeLabel(inspection.inspection_type) }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span
                    class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                    :class="{
                      'bg-blue-100 text-blue-800': inspection.status === 'scheduled',
                      'bg-green-100 text-green-800': inspection.status === 'completed',
                      'bg-yellow-100 text-yellow-800': inspection.status === 'pending_action',
                      'bg-gray-100 text-gray-800': inspection.status === 'closed'
                    }"
                  >
                    {{ getStatusLabel(inspection.status) }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <div class="flex items-center justify-end space-x-2">
                    <Link
                      :href="route('inspections.show', inspection.id)"
                      class="text-purple-600 hover:text-purple-900 p-1"
                      title="Detay"
                    >
                      <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                      </svg>
                    </Link>
                    <Link
                      :href="route('inspections.edit', inspection.id)"
                      class="text-blue-600 hover:text-blue-900 p-1"
                      title="Düzenle"
                    >
                      <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                      </svg>
                    </Link>
                    <button
                      @click="deleteInspection(inspection)"
                      class="text-red-600 hover:text-red-900 p-1"
                      title="Sil"
                    >
                      <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Empty State -->
        <div v-if="!inspections.data.length" class="text-center py-12">
          <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" />
          </svg>
          <h3 class="mt-2 text-sm font-medium text-gray-900">Denetim kaydı bulunamadı</h3>
          <p class="mt-1 text-sm text-gray-500">Yeni bir denetim kaydı oluşturarak başlayın.</p>
          <div class="mt-6">
            <Link
              :href="route('inspections.create')"
              class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700"
            >
              <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
              </svg>
              Yeni Denetim Ekle
            </Link>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="inspections.data.length" class="bg-gray-50 px-6 py-4 border-t border-gray-200">
          <div class="flex items-center justify-between">
            <div class="text-sm text-gray-700">
              <span class="font-medium">{{ inspections.from }}</span>
              -
              <span class="font-medium">{{ inspections.to }}</span>
              arası gösteriliyor
              (Toplam: <span class="font-medium">{{ inspections.total }}</span>)
            </div>
            <div class="flex space-x-2">
              <template v-for="link in inspections.links" :key="link.label">
                <Link
                  v-if="link.url"
                  :href="link.url"
                  :class="[
                    'px-3 py-2 text-sm font-medium rounded-lg transition-colors',
                    link.active
                      ? 'bg-purple-600 text-white'
                      : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-300'
                  ]"
                  v-html="link.label"
                />
                <span
                  v-else
                  :class="[
                    'px-3 py-2 text-sm font-medium rounded-lg transition-colors',
                    'bg-gray-100 text-gray-400 cursor-not-allowed'
                  ]"
                  v-html="link.label"
                />
              </template>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, watch } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  inspections: Object,
  filters: Object,
  projects: Array,
  inspectionCompanies: Array,
})

const filterForm = ref({
  search: props.filters.search || '',
  project_id: props.filters.project_id || '',
  inspection_company_id: props.filters.inspection_company_id || '',
  inspection_type: props.filters.inspection_type || '',
  status: props.filters.status || '',
  date_from: props.filters.date_from || '',
  date_to: props.filters.date_to || '',
})

watch(filterForm, () => {
  router.get(route('inspections.index'), filterForm.value, {
    preserveState: true,
    replace: true,
  })
}, { deep: true })

const resetFilters = () => {
  filterForm.value = {
    search: '',
    project_id: '',
    inspection_company_id: '',
    inspection_type: '',
    status: '',
    date_from: '',
    date_to: '',
  }
}

const getInspectionTypeLabel = (type) => {
  const labels = {
    periodic: 'Periyodik',
    special: 'Özel',
    final: 'Final',
  }
  return labels[type] || type
}

const getStatusLabel = (status) => {
  const labels = {
    scheduled: 'Planlandı',
    completed: 'Tamamlandı',
    pending_action: 'Eylem Bekliyor',
    closed: 'Kapatıldı',
  }
  return labels[status] || status
}

const deleteInspection = (inspection) => {
  if (confirm(`"${inspection.inspection_number}" numaralı denetim kaydını silmek istediğinize emin misiniz?`)) {
    router.delete(route('inspections.destroy', inspection.id), {
      onSuccess: () => {
        // Success message will be shown via flash
      },
    })
  }
}
</script>
