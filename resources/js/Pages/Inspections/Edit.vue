<template>
  <AppLayout title="Denetim Düzenle" :full-width="true">
    <template #fullWidthHeader>
      <div class="bg-gradient-to-r from-purple-600 via-purple-700 to-indigo-800 border-b border-purple-900/20 w-full">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
          <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
              <div class="flex-shrink-0 w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm">
                <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                </svg>
              </div>
              <div>
                <h1 class="text-2xl lg:text-3xl font-bold text-white">Denetim Düzenle</h1>
                <p class="text-purple-100 text-sm mt-1">Denetim bilgilerini güncelleyin</p>
              </div>
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
                  <Link :href="route('inspections.index')" class="text-purple-100 hover:text-white text-sm">Denetimler</Link>
                </li>
                <li class="flex items-center">
                  <svg class="h-3 w-3 text-purple-200 mx-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
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

    <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
      <form @submit.prevent="submit" class="space-y-6">
        <!-- Denetim Bilgileri -->
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-medium text-gray-900">Denetim Bilgileri</h3>
          </div>
          <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Proje <span class="text-red-500">*</span>
                </label>
                <select
                  v-model="form.project_id"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all"
                  :class="{'border-red-300 focus:ring-red-500': form.errors.project_id}"
                >
                  <option value="">Proje seçin</option>
                  <option v-for="project in projects" :key="project.id" :value="project.id">
                    {{ project.name }} ({{ project.project_code }})
                  </option>
                </select>
                <p v-if="form.errors.project_id" class="text-red-600 text-sm mt-2">{{ form.errors.project_id }}</p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Denetim Kuruluşu <span class="text-red-500">*</span>
                </label>
                <select
                  v-model="form.inspection_company_id"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all"
                  :class="{'border-red-300 focus:ring-red-500': form.errors.inspection_company_id}"
                >
                  <option value="">Denetim kuruluşu seçin</option>
                  <option v-for="company in inspectionCompanies" :key="company.id" :value="company.id">
                    {{ company.company_name }}
                  </option>
                </select>
                <p v-if="form.errors.inspection_company_id" class="text-red-600 text-sm mt-2">{{ form.errors.inspection_company_id }}</p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Denetçi Adı <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="form.inspector_name"
                  type="text"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all"
                  :class="{'border-red-300 focus:ring-red-500': form.errors.inspector_name}"
                />
                <p v-if="form.errors.inspector_name" class="text-red-600 text-sm mt-2">{{ form.errors.inspector_name }}</p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Denetim Tarihi <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="form.inspection_date"
                  type="date"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all"
                  :class="{'border-red-300 focus:ring-red-500': form.errors.inspection_date}"
                />
                <p v-if="form.errors.inspection_date" class="text-red-600 text-sm mt-2">{{ form.errors.inspection_date }}</p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Denetim Türü <span class="text-red-500">*</span>
                </label>
                <select
                  v-model="form.inspection_type"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all"
                  :class="{'border-red-300 focus:ring-red-500': form.errors.inspection_type}"
                >
                  <option value="">Denetim türü seçin</option>
                  <option value="periodic">Periyodik</option>
                  <option value="special">Özel</option>
                  <option value="final">Final</option>
                </select>
                <p v-if="form.errors.inspection_type" class="text-red-600 text-sm mt-2">{{ form.errors.inspection_type }}</p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Durum <span class="text-red-500">*</span>
                </label>
                <select
                  v-model="form.status"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all"
                  :class="{'border-red-300 focus:ring-red-500': form.errors.status}"
                >
                  <option value="scheduled">Planlandı</option>
                  <option value="completed">Tamamlandı</option>
                  <option value="pending_action">Eylem Bekliyor</option>
                  <option value="closed">Kapatıldı</option>
                </select>
                <p v-if="form.errors.status" class="text-red-600 text-sm mt-2">{{ form.errors.status }}</p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Sonraki Denetim Tarihi
                </label>
                <input
                  v-model="form.next_inspection_date"
                  type="date"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all"
                  :class="{'border-red-300 focus:ring-red-500': form.errors.next_inspection_date}"
                />
                <p v-if="form.errors.next_inspection_date" class="text-red-600 text-sm mt-2">{{ form.errors.next_inspection_date }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Bulgular ve Notlar -->
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-medium text-gray-900">Bulgular ve Notlar</h3>
          </div>
          <div class="p-6">
            <div class="space-y-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Genel Bulgular</label>
                <textarea
                  v-model="form.findings"
                  rows="4"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all"
                  placeholder="Denetim sırasında tespit edilen genel bulgular..."
                ></textarea>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Notlar</label>
                <textarea
                  v-model="form.notes"
                  rows="3"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all"
                  placeholder="Ek notlar..."
                ></textarea>
              </div>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex justify-end gap-3">
          <Link
            :href="route('inspections.index')"
            class="px-6 py-3 border border-gray-300 rounded-lg hover:bg-gray-50 text-gray-700 font-medium transition-colors"
          >
            İptal
          </Link>
          <button
            type="submit"
            :disabled="form.processing"
            class="px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 font-medium transition-colors disabled:opacity-50"
          >
            {{ form.processing ? 'Kaydediliyor...' : 'Güncelle' }}
          </button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>

<script setup>
import { useForm, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  inspection: Object,
  projects: Array,
  inspectionCompanies: Array,
})

const form = useForm({
  project_id: props.inspection.project_id,
  inspection_company_id: props.inspection.inspection_company_id,
  inspector_name: props.inspection.inspector_name,
  inspection_date: props.inspection.inspection_date,
  inspection_type: props.inspection.inspection_type,
  status: props.inspection.status,
  findings: props.inspection.findings,
  next_inspection_date: props.inspection.next_inspection_date,
  notes: props.inspection.notes,
})

const submit = () => {
  form.put(route('inspections.update', props.inspection.id))
}
</script>
