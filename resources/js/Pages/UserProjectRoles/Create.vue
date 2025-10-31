<template>
  <AppLayout title="Yeni Rol Ataması" :full-width="true">
    <!-- Full-width header -->
    <template #fullWidthHeader>
      <div class="bg-gradient-to-r from-purple-600 via-indigo-600 to-blue-600 border-b border-purple-900/20 w-full">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
          <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
              <div class="flex-shrink-0 w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm">
                <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
              </div>
              <div>
                <h1 class="text-2xl lg:text-3xl font-bold text-white">Yeni Rol Ataması</h1>
                <p class="text-purple-100 text-sm mt-1">Kullanıcıya proje bazlı rol ve yetki tanımlayın</p>
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
                  <Link :href="route('user-project-roles.index')" class="text-purple-100 hover:text-white text-sm">Proje Rolleri</Link>
                </li>
                <li class="flex items-center">
                  <svg class="h-3 w-3 text-purple-200 mx-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                  </svg>
                  <span class="text-white font-medium text-sm">Yeni Atama</span>
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
            <p class="text-sm text-gray-600 mt-1">Kullanıcı, proje ve rol seçimi yapın</p>
          </div>
          <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
              <!-- Kullanıcı Seçimi -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Kullanıcı <span class="text-red-500">*</span>
                </label>
                <select
                  v-model="form.user_id"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all"
                  :class="{'border-red-300 focus:ring-red-500': form.errors.user_id}"
                >
                  <option value="">Seçiniz...</option>
                  <option v-for="user in users" :key="user.id" :value="user.id">
                    {{ user.name }}
                  </option>
                </select>
                <p v-if="form.errors.user_id" class="text-red-600 text-sm mt-2">
                  {{ form.errors.user_id }}
                </p>
              </div>

              <!-- Proje Seçimi -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Proje <span class="text-red-500">*</span>
                </label>
                <select
                  v-model="form.project_id"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all"
                  :class="{'border-red-300 focus:ring-red-500': form.errors.project_id}"
                >
                  <option value="">Seçiniz...</option>
                  <option v-for="project in projects" :key="project.id" :value="project.id">
                    {{ project.project_code }} - {{ project.name }}
                  </option>
                </select>
                <p v-if="form.errors.project_id" class="text-red-600 text-sm mt-2">
                  {{ form.errors.project_id }}
                </p>
              </div>

              <!-- Rol Seçimi -->
              <div class="lg:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Rol <span class="text-red-500">*</span>
                </label>
                <select
                  v-model="form.role"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all"
                  :class="{'border-red-300 focus:ring-red-500': form.errors.role}"
                >
                  <option value="">Seçiniz...</option>
                  <option value="project_manager">Proje Müdürü</option>
                  <option value="site_manager">Şantiye Şefi</option>
                  <option value="engineer">Mühendis</option>
                  <option value="foreman">Usta Başı</option>
                  <option value="inspector">Denetçi</option>
                  <option value="safety_officer">İSG Uzmanı</option>
                  <option value="viewer">Görüntüleyici</option>
                </select>
                <p v-if="form.errors.role" class="text-red-600 text-sm mt-2">
                  {{ form.errors.role }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Tarih ve Durum -->
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-medium text-gray-900">Tarih ve Durum</h3>
            <p class="text-sm text-gray-600 mt-1">Atama geçerlilik tarihleri ve aktiflik durumu</p>
          </div>
          <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
              <!-- Başlangıç Tarihi -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Başlangıç Tarihi
                </label>
                <input
                  v-model="form.start_date"
                  type="date"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all"
                  :class="{'border-red-300 focus:ring-red-500': form.errors.start_date}"
                />
                <p v-if="form.errors.start_date" class="text-red-600 text-sm mt-2">
                  {{ form.errors.start_date }}
                </p>
              </div>

              <!-- Bitiş Tarihi -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Bitiş Tarihi
                </label>
                <input
                  v-model="form.end_date"
                  type="date"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all"
                  :class="{'border-red-300 focus:ring-red-500': form.errors.end_date}"
                />
                <p v-if="form.errors.end_date" class="text-red-600 text-sm mt-2">
                  {{ form.errors.end_date }}
                </p>
              </div>

              <!-- Aktif/Pasif -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Durum
                </label>
                <div class="flex items-center h-12 px-4 py-3 bg-gray-50 rounded-lg border border-gray-300">
                  <input
                    v-model="form.is_active"
                    type="checkbox"
                    class="h-5 w-5 text-purple-600 focus:ring-purple-500 border-gray-300 rounded"
                  />
                  <label class="ml-3 text-sm font-medium text-gray-700">
                    Aktif
                  </label>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Sorumluluklar -->
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-medium text-gray-900">Sorumluluklar</h3>
            <p class="text-sm text-gray-600 mt-1">Bu rol için tanımlanan sorumlulukları girin</p>
          </div>
          <div class="p-6">
            <textarea
              v-model="form.responsibilities"
              rows="4"
              class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all"
              placeholder="Her satıra bir sorumluluk yazın veya virgülle ayırın&#10;Örnek:&#10;Proje planlamasını yapmak&#10;Ekip yönetimi&#10;Raporlama"
            ></textarea>
            <p class="text-xs text-gray-500 mt-2">
              Her satıra bir sorumluluk yazın veya virgülle ayırın
            </p>
          </div>
        </div>

        <!-- Yetkiler -->
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-medium text-gray-900">Yetkiler</h3>
            <p class="text-sm text-gray-600 mt-1">Bu rol için tanımlanan yetkileri girin</p>
          </div>
          <div class="p-6">
            <textarea
              v-model="form.permissions"
              rows="4"
              class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all"
              placeholder="Her satıra bir yetki yazın veya virgülle ayırın&#10;Örnek:&#10;hakediş.görüntüle&#10;hakediş.oluştur&#10;metraj.onay"
            ></textarea>
            <p class="text-xs text-gray-500 mt-2">
              Her satıra bir yetki yazın veya virgülle ayırın (örn: hakediş.görüntüle, metraj.düzenle)
            </p>
          </div>
        </div>

        <!-- Notlar -->
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-medium text-gray-900">Ek Notlar</h3>
          </div>
          <div class="p-6">
            <textarea
              v-model="form.notes"
              rows="3"
              class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all"
              placeholder="Atama ile ilgili ek notlar, açıklamalar veya detaylar..."
            ></textarea>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex justify-end space-x-4 pb-6">
          <Link
            :href="route('user-project-roles.index')"
            class="px-6 py-3 bg-white border-2 border-gray-300 rounded-lg font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all"
          >
            İptal
          </Link>
          <button
            type="submit"
            :disabled="form.processing"
            class="px-6 py-3 bg-gradient-to-r from-purple-600 to-indigo-600 border border-transparent rounded-lg font-medium text-white hover:from-purple-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all"
          >
            <span v-if="form.processing">Kaydediliyor...</span>
            <span v-else>Kaydet</span>
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
  users: {
    type: Array,
    required: true
  },
  projects: {
    type: Array,
    required: true
  }
})

const form = useForm({
  user_id: '',
  project_id: '',
  role: '',
  is_active: true,
  start_date: null,
  end_date: null,
  responsibilities: '',
  permissions: '',
  notes: ''
})

const submit = () => {
  form.post(route('user-project-roles.store'), {
    onSuccess: () => {
      // Redirect will be handled by controller
    }
  })
}
</script>
