<template>
  <AppLayout title="Yeni Risk Değerlendirmesi" :full-width="true">
    <!-- Full-width header -->
    <template #fullWidthHeader>
      <div class="bg-gradient-to-r from-red-600 via-orange-600 to-red-700 border-b border-red-900/20 w-full">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
          <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
              <div class="flex-shrink-0 w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm">
                <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                </svg>
              </div>
              <div>
                <h1 class="text-2xl lg:text-3xl font-bold text-white">Yeni Risk Değerlendirmesi</h1>
                <p class="text-red-100 text-sm mt-1">İş aktivitesi için risk analizi ve değerlendirmesi oluşturun</p>
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
                  <Link :href="route('risk-assessments.index')" class="text-red-100 hover:text-white text-sm">Risk Değerlendirmeleri</Link>
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
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Genel Risk Seviyesi <span class="text-red-500">*</span>
                </label>
                <select v-model="form.overall_risk_level" required class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all">
                  <option value="low">Düşük Risk</option>
                  <option value="medium">Orta Risk</option>
                  <option value="high">Yüksek Risk</option>
                  <option value="critical">Kritik Risk</option>
                </select>
              </div>

              <div class="lg:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Değerlendirme Başlığı <span class="text-red-500">*</span>
                </label>
                <input v-model="form.assessment_title" type="text" required placeholder="Örn: Beton Dökümü Risk Değerlendirmesi" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all" />
              </div>

              <div class="lg:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  İş Aktivitesi <span class="text-red-500">*</span>
                </label>
                <input v-model="form.work_activity" type="text" required placeholder="Örn: Kolon ve kiriş beton dökümü" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all" />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Konum <span class="text-red-500">*</span>
                </label>
                <input v-model="form.location" type="text" required placeholder="Örn: 5. Kat" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all" />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Değerlendirme Tarihi <span class="text-red-500">*</span>
                </label>
                <input v-model="form.assessment_date" type="date" required class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all" />
              </div>

              <div class="lg:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Açıklama</label>
                <textarea v-model="form.description" rows="3" placeholder="Risk değerlendirmesinin detaylı açıklaması..." class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all"></textarea>
              </div>
            </div>
          </div>
        </div>

        <!-- Kontrol Tedbirleri -->
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-medium text-gray-900">Kontrol Tedbirleri ve Geçerlilik</h3>
          </div>
          <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
              <div class="lg:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Kontrol Tedbirleri</label>
                <textarea v-model="form.control_measures" rows="4" placeholder="Örn: Emniyet kemeri, bariyer sistemi, eğitimli personel..." class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all"></textarea>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Geçerlilik Başlangıç</label>
                <input v-model="form.valid_from" type="date" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all" />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Geçerlilik Bitiş</label>
                <input v-model="form.valid_until" type="date" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all" />
              </div>
            </div>
          </div>
        </div>

        <!-- Form Actions -->
        <div class="flex justify-end space-x-4 pb-6">
          <Link :href="route('risk-assessments.index')" class="px-6 py-3 bg-white border-2 border-gray-300 rounded-lg font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all">
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

defineProps({
  projects: Array,
  users: Array
})

const processing = ref(false)

const form = ref({
  project_id: '',
  assessment_title: '',
  work_activity: '',
  location: '',
  assessment_date: new Date().toISOString().split('T')[0],
  overall_risk_level: 'medium',
  description: '',
  control_measures: '',
  valid_from: new Date().toISOString().split('T')[0],
  valid_until: ''
})

const submit = () => {
  processing.value = true
  router.post(route('risk-assessments.store'), form.value, {
    onFinish: () => { processing.value = false }
  })
}
</script>
