<template>
  <AppLayout title="Yeni İş Kazası / Olay Raporu" :full-width="true">
    <!-- Full-width header -->
    <template #fullWidthHeader>
      <div class="bg-gradient-to-r from-red-600 via-orange-600 to-red-700 border-b border-red-900/20 w-full">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
          <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
              <div class="flex-shrink-0 w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm">
                <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                </svg>
              </div>
              <div>
                <h1 class="text-2xl lg:text-3xl font-bold text-white">Yeni İş Kazası / Olay Raporu</h1>
                <p class="text-red-100 text-sm mt-1">İş kazası, ramak kala olay veya güvenlik olayı bildirimini doldurun</p>
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
                  <Link :href="route('dashboard')" class="text-red-100 hover:text-white transition-colors">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                    </svg>
                  </Link>
                </li>
                <li class="flex items-center">
                  <svg class="h-3 w-3 text-red-200 mx-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                  </svg>
                  <Link :href="route('safety-incidents.index')" class="text-red-100 hover:text-white text-sm">İş Kazaları</Link>
                </li>
                <li class="flex items-center">
                  <svg class="h-3 w-3 text-red-200 mx-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
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
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Proje <span class="text-red-500">*</span>
                </label>
                <select v-model="form.project_id" required class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all">
                  <option value="">Seçiniz...</option>
                  <option v-for="project in projects" :key="project.id" :value="project.id">{{ project.name }}</option>
                </select>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Çalışan</label>
                <select v-model="form.employee_id" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all">
                  <option value="">Seçiniz...</option>
                  <option v-for="employee in employees" :key="employee.id" :value="employee.id">{{ employee.first_name }} {{ employee.last_name }}</option>
                </select>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Olay Tarihi <span class="text-red-500">*</span>
                </label>
                <input v-model="form.incident_date" type="date" required class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all" />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Olay Saati</label>
                <input v-model="form.incident_time" type="time" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all" />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Konum <span class="text-red-500">*</span>
                </label>
                <input v-model="form.location" type="text" required placeholder="Örn: 3. Kat İnşaat Alanı" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all" />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Olay Türü <span class="text-red-500">*</span>
                </label>
                <select v-model="form.incident_type" required class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all">
                  <option value="">Seçiniz...</option>
                  <option value="minor_injury">Küçük Yaralanma</option>
                  <option value="major_injury">Büyük Yaralanma</option>
                  <option value="near_miss">Ramak Kala</option>
                  <option value="property_damage">Mal Hasarı</option>
                  <option value="environmental">Çevresel Olay</option>
                  <option value="fatal">Ölümlü Kaza</option>
                </select>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Önem Derecesi <span class="text-red-500">*</span>
                </label>
                <select v-model="form.severity" required class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all">
                  <option value="">Seçiniz...</option>
                  <option value="low">Düşük</option>
                  <option value="medium">Orta</option>
                  <option value="high">Yüksek</option>
                  <option value="critical">Kritik</option>
                </select>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">İş Kaybı (Gün)</label>
                <input v-model="form.days_lost" type="number" min="0" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all" />
              </div>

              <div class="lg:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Olay Açıklaması <span class="text-red-500">*</span>
                </label>
                <textarea v-model="form.description" required rows="4" placeholder="Olayın detaylı açıklaması..." class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all"></textarea>
              </div>

              <div class="lg:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Anında Alınan Aksiyonlar</label>
                <textarea v-model="form.immediate_actions" rows="3" placeholder="Olay sonrası anında alınan tedbirler..." class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all"></textarea>
              </div>

              <!-- Checkboxes -->
              <div class="lg:col-span-2">
                <div class="space-y-3 pt-2">
                  <label class="flex items-center cursor-pointer">
                    <input v-model="form.medical_treatment_required" type="checkbox" class="rounded border-gray-300 text-red-600 focus:ring-red-500 w-4 h-4" />
                    <span class="ml-3 text-sm font-medium text-gray-700">Tıbbi tedavi gerekti</span>
                  </label>
                  <label class="flex items-center cursor-pointer">
                    <input v-model="form.work_stopped" type="checkbox" class="rounded border-gray-300 text-red-600 focus:ring-red-500 w-4 h-4" />
                    <span class="ml-3 text-sm font-medium text-gray-700">İş durduruldu</span>
                  </label>
                  <label class="flex items-center cursor-pointer">
                    <input v-model="form.reported_to_authority" type="checkbox" class="rounded border-gray-300 text-red-600 focus:ring-red-500 w-4 h-4" />
                    <span class="ml-3 text-sm font-medium text-gray-700">Yetkili makamlara bildirildi</span>
                  </label>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Form Actions -->
        <div class="flex justify-end space-x-4 pb-6">
          <Link :href="route('safety-incidents.index')" class="px-6 py-3 bg-white border-2 border-gray-300 rounded-lg font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all">
            İptal
          </Link>
          <button type="submit" :disabled="processing" class="px-6 py-3 bg-gradient-to-r from-red-600 to-orange-600 border border-transparent rounded-lg font-medium text-white hover:from-red-700 hover:to-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all">
            <span v-if="processing">Kaydediliyor...</span>
            <span v-else>Kaydet</span>
          </button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  projects: Array,
  employees: Array
})

const processing = ref(false)

const form = ref({
  project_id: '',
  employee_id: '',
  incident_date: new Date().toISOString().split('T')[0],
  incident_time: '',
  location: '',
  incident_type: '',
  severity: 'medium',
  description: '',
  immediate_actions: '',
  medical_treatment_required: false,
  work_stopped: false,
  days_lost: 0,
  reported_to_authority: false
})

const submit = () => {
  processing.value = true

  router.post(route('safety-incidents.store'), form.value, {
    onFinish: () => {
      processing.value = false
    }
  })
}
</script>
