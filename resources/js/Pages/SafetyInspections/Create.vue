<template>
  <AppLayout title="Yeni Güvenlik Denetimi" :full-width="true">
    <!-- Full-width header -->
    <template #fullWidthHeader>
      <div class="bg-gradient-to-r from-red-600 via-orange-600 to-red-700 border-b border-red-900/20 w-full">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
          <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
              <div class="flex-shrink-0 w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm">
                <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <div>
                <h1 class="text-2xl lg:text-3xl font-bold text-white">Yeni Güvenlik Denetimi</h1>
                <p class="text-red-100 text-sm mt-1">Şantiye güvenlik denetim kaydı oluşturun</p>
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
                  <Link :href="route('safety-inspections.index')" class="text-red-100 hover:text-white text-sm">Güvenlik Denetimleri</Link>
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
                  Denetim Türü <span class="text-red-500">*</span>
                </label>
                <select v-model="form.inspection_type" required class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all">
                  <option value="daily">Günlük Denetim</option>
                  <option value="weekly">Haftalık Denetim</option>
                  <option value="monthly">Aylık Denetim</option>
                  <option value="quarterly">Üç Aylık Denetim</option>
                  <option value="special">Özel Denetim</option>
                </select>
              </div>

              <div class="lg:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Denetim Başlığı <span class="text-red-500">*</span>
                </label>
                <input v-model="form.inspection_title" type="text" required placeholder="Örn: Haftalık Güvenlik Denetimi" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all" />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Denetim Tarihi <span class="text-red-500">*</span>
                </label>
                <input v-model="form.inspection_date" type="date" required class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all" />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Konum <span class="text-red-500">*</span>
                </label>
                <input v-model="form.location" type="text" required placeholder="Örn: Tüm Şantiye Alanı" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all" />
              </div>
            </div>
          </div>
        </div>

        <!-- Denetim Sonuçları -->
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-medium text-gray-900">Denetim Sonuçları</h3>
          </div>
          <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Genel Durum <span class="text-red-500">*</span>
                </label>
                <select v-model="form.overall_status" required class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all">
                  <option value="passed">Başarılı</option>
                  <option value="passed_with_notes">Notlarla Geçti</option>
                  <option value="requires_action">Aksiyon Gerekli</option>
                  <option value="failed">Başarısız</option>
                </select>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Skor (0-100)</label>
                <input v-model="form.score" type="number" min="0" max="100" placeholder="85" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all" />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Kontrol Edilen Madde</label>
                <input v-model="form.items_checked" type="number" min="0" placeholder="20" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all" />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Başarılı Madde</label>
                <input v-model="form.items_passed" type="number" min="0" placeholder="17" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all" />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Başarısız Madde</label>
                <input v-model="form.items_failed" type="number" min="0" placeholder="3" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all" />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Sonraki Denetim Tarihi</label>
                <input v-model="form.next_inspection_date" type="date" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all" />
              </div>

              <div class="lg:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Notlar</label>
                <textarea v-model="form.notes" rows="3" placeholder="Denetim notları ve gözlemler..." class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all"></textarea>
              </div>
            </div>
          </div>
        </div>

        <!-- Form Actions -->
        <div class="flex justify-end space-x-4 pb-6">
          <Link :href="route('safety-inspections.index')" class="px-6 py-3 bg-white border-2 border-gray-300 rounded-lg font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all">
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
  inspection_title: '',
  inspection_type: 'weekly',
  inspection_date: new Date().toISOString().split('T')[0],
  location: '',
  overall_status: 'passed',
  score: 100,
  items_checked: 0,
  items_passed: 0,
  items_failed: 0,
  next_inspection_date: '',
  notes: ''
})

const submit = () => {
  processing.value = true
  router.post(route('safety-inspections.store'), form.value, {
    onFinish: () => { processing.value = false }
  })
}
</script>
