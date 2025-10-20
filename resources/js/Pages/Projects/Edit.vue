<template>
  <AppLayout :title="`${project.name} - Düzenle - SPT İnşaat Puantaj Sistemi`" :full-width="true">
    <!-- Full-width header -->
    <template #fullWidthHeader>
      <div class="bg-gradient-to-r from-indigo-600 via-indigo-700 to-purple-800 border-b border-indigo-900/20 w-full">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
          <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
              <div class="flex-shrink-0 w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm">
                <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                </svg>
              </div>
              <div>
                <h1 class="text-2xl lg:text-3xl font-bold text-white">Proje Düzenle</h1>
                <p class="text-indigo-100 text-sm mt-1">{{ project.name }}</p>
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
                  <Link :href="route('dashboard')" class="text-indigo-100 hover:text-white transition-colors">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                    </svg>
                    <span class="sr-only">Ana Sayfa</span>
                  </Link>
                </li>
                <li class="flex items-center">
                  <svg class="h-3 w-3 text-indigo-200 mx-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                  </svg>
                  <Link :href="route('projects.index')" class="text-indigo-100 hover:text-white text-xs">
                    Proje Yönetimi
                  </Link>
                </li>
                <li class="flex items-center">
                  <svg class="h-3 w-3 text-indigo-200 mx-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                  </svg>
                  <span class="text-xs font-medium text-white">Düzenle</span>
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
        <!-- Basic Information -->
        <Card>
          <div class="p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Temel Bilgiler</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Proje Adı <span class="text-red-500">*</span>
                </label>
                <Input
                  v-model="form.name"
                  type="text"
                  placeholder="Proje adını girin"
                  :error="errors.name"
                  required
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Proje Kodu
                </label>
                <Input
                  v-model="form.project_code"
                  type="text"
                  placeholder="Proje kodu"
                  :error="errors.project_code"
                  disabled
                />
              </div>

              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Açıklama
                </label>
                <textarea
                  v-model="form.description"
                  rows="3"
                  class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                  placeholder="Proje açıklaması"
                ></textarea>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Proje Türü <span class="text-red-500">*</span>
                </label>
                <Select
                  v-model="form.type"
                  :options="typeOptions"
                  :error="errors.type"
                  required
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Öncelik
                </label>
                <Select
                  v-model="form.priority"
                  :options="priorityOptions"
                  :error="errors.priority"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Durum <span class="text-red-500">*</span>
                </label>
                <Select
                  v-model="form.status"
                  :options="statusOptions"
                  :error="errors.status"
                  required
                />
              </div>
            </div>
          </div>
        </Card>

        <!-- Location Information -->
        <Card>
          <div class="p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Konum Bilgileri</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Şehir <span class="text-red-500">*</span>
                </label>
                <Input
                  v-model="form.city"
                  type="text"
                  placeholder="Şehir"
                  :error="errors.city"
                  required
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  İlçe
                </label>
                <Input
                  v-model="form.district"
                  type="text"
                  placeholder="İlçe"
                  :error="errors.district"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Konum <span class="text-red-500">*</span>
                </label>
                <Input
                  v-model="form.location"
                  type="text"
                  placeholder="Kısa konum bilgisi"
                  :error="errors.location"
                  required
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Koordinatlar
                </label>
                <Input
                  v-model="form.coordinates"
                  type="text"
                  placeholder="Enlem, Boylam"
                  :error="errors.coordinates"
                />
              </div>

              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Tam Adres
                </label>
                <textarea
                  v-model="form.full_address"
                  rows="2"
                  class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                  placeholder="Detaylı adres bilgisi"
                ></textarea>
              </div>
            </div>
          </div>
        </Card>

        <!-- Date & Budget Information -->
        <Card>
          <div class="p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Tarih ve Bütçe</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Başlangıç Tarihi <span class="text-red-500">*</span>
                </label>
                <Input
                  v-model="form.start_date"
                  type="date"
                  :error="errors.start_date"
                  required
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Planlanan Bitiş Tarihi <span class="text-red-500">*</span>
                </label>
                <Input
                  v-model="form.planned_end_date"
                  type="date"
                  :error="errors.planned_end_date"
                  required
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Gerçekleşen Bitiş Tarihi
                </label>
                <Input
                  v-model="form.actual_end_date"
                  type="date"
                  :error="errors.actual_end_date"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Toplam Bütçe (₺)
                </label>
                <Input
                  v-model="form.budget"
                  type="number"
                  step="0.01"
                  min="0"
                  placeholder="0.00"
                  :error="errors.budget"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  İşçilik Bütçesi (₺)
                </label>
                <Input
                  v-model="form.labor_budget"
                  type="number"
                  step="0.01"
                  min="0"
                  placeholder="0.00"
                  :error="errors.labor_budget"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Harcanan Tutar (₺)
                </label>
                <Input
                  v-model="form.spent_amount"
                  type="number"
                  step="0.01"
                  min="0"
                  placeholder="0.00"
                  :error="errors.spent_amount"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Tahmini Çalışan Sayısı
                </label>
                <Input
                  v-model="form.estimated_employees"
                  type="number"
                  min="0"
                  placeholder="0"
                  :error="errors.estimated_employees"
                />
              </div>
            </div>
          </div>
        </Card>

        <!-- Management & Client Information -->
        <Card>
          <div class="p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Yönetim ve Müşteri Bilgileri</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Proje Yöneticisi
                </label>
                <Select
                  v-model="form.project_manager_id"
                  :options="managerOptions"
                  :error="errors.project_manager_id"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Şantiye Şefi
                </label>
                <Select
                  v-model="form.site_manager_id"
                  :options="managerOptions"
                  :error="errors.site_manager_id"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Müşteri Adı
                </label>
                <Input
                  v-model="form.client_name"
                  type="text"
                  placeholder="Müşteri/Firma adı"
                  :error="errors.client_name"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Müşteri İletişim
                </label>
                <Input
                  v-model="form.client_contact"
                  type="text"
                  placeholder="Telefon veya e-posta"
                  :error="errors.client_contact"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  İletişim Telefonu
                </label>
                <Input
                  v-model="form.contact_phone"
                  type="tel"
                  placeholder="0xxx xxx xx xx"
                  :error="errors.contact_phone"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  İletişim E-posta
                </label>
                <Input
                  v-model="form.contact_email"
                  type="email"
                  placeholder="ornek@email.com"
                  :error="errors.contact_email"
                />
              </div>

              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Notlar
                </label>
                <textarea
                  v-model="form.notes"
                  rows="3"
                  class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                  placeholder="Ek notlar ve bilgiler"
                ></textarea>
              </div>
            </div>
          </div>
        </Card>

        <!-- Form Actions -->
        <Card>
          <div class="p-6 flex items-center justify-between">
            <Link
              :href="route('projects.index')"
              class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors"
            >
              İptal
            </Link>
            <button
              type="submit"
              :disabled="processing"
              class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50 transition-colors"
            >
              {{ processing ? 'Güncelleniyor...' : 'Değişiklikleri Kaydet' }}
            </button>
          </div>
        </Card>
      </form>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Card from '@/Components/UI/Card.vue'
import Input from '@/Components/UI/Input.vue'
import Select from '@/Components/UI/Select.vue'

const props = defineProps({
  project: Object,
  managers: Array,
  errors: {
    type: Object,
    default: () => ({})
  }
})

const form = ref({
  name: '',
  project_code: '',
  description: '',
  location: '',
  city: '',
  district: '',
  full_address: '',
  coordinates: '',
  start_date: '',
  planned_end_date: '',
  actual_end_date: '',
  budget: '',
  labor_budget: '',
  spent_amount: '',
  project_manager_id: '',
  site_manager_id: '',
  contact_phone: '',
  contact_email: '',
  status: '',
  type: '',
  priority: '',
  client_name: '',
  client_contact: '',
  estimated_employees: '',
  notes: ''
})

const processing = ref(false)

const typeOptions = [
  { value: '', label: 'Seçiniz' },
  { value: 'residential', label: 'Konut' },
  { value: 'commercial', label: 'Ticari' },
  { value: 'infrastructure', label: 'Altyapı' },
  { value: 'industrial', label: 'Endüstriyel' },
  { value: 'other', label: 'Diğer' }
]

const statusOptions = [
  { value: 'planning', label: 'Planlama' },
  { value: 'active', label: 'Aktif' },
  { value: 'on_hold', label: 'Beklemede' },
  { value: 'completed', label: 'Tamamlandı' },
  { value: 'cancelled', label: 'İptal Edildi' }
]

const priorityOptions = [
  { value: 'low', label: 'Düşük' },
  { value: 'medium', label: 'Orta' },
  { value: 'high', label: 'Yüksek' },
  { value: 'critical', label: 'Kritik' }
]

const managerOptions = [
  { value: '', label: 'Seçiniz' },
  ...props.managers.map(manager => ({
    value: manager.id,
    label: `${manager.first_name} ${manager.last_name}${manager.position ? ` - ${manager.position}` : ''}`
  }))
]

onMounted(() => {
  form.value = {
    name: props.project.name || '',
    project_code: props.project.project_code || '',
    description: props.project.description || '',
    location: props.project.location || '',
    city: props.project.city || '',
    district: props.project.district || '',
    full_address: props.project.full_address || '',
    coordinates: props.project.coordinates || '',
    start_date: props.project.start_date || '',
    planned_end_date: props.project.planned_end_date || '',
    actual_end_date: props.project.actual_end_date || '',
    budget: props.project.budget || '',
    labor_budget: props.project.labor_budget || '',
    spent_amount: props.project.spent_amount || '',
    project_manager_id: props.project.project_manager_id || '',
    site_manager_id: props.project.site_manager_id || '',
    contact_phone: props.project.contact_phone || '',
    contact_email: props.project.contact_email || '',
    status: props.project.status || '',
    type: props.project.type || '',
    priority: props.project.priority || '',
    client_name: props.project.client_name || '',
    client_contact: props.project.client_contact || '',
    estimated_employees: props.project.estimated_employees || '',
    notes: props.project.notes || ''
  }
})

const submit = () => {
  processing.value = true
  router.put(route('projects.update', props.project.id), form.value, {
    onFinish: () => {
      processing.value = false
    }
  })
}
</script>
