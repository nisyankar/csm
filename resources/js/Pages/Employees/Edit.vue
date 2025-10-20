<template>
  <AppLayout
    title="Çalışan Düzenle - SPT İnşaat Puantaj Sistemi"
    :full-width="true"
  >
    <!-- Full-width header with improved design -->
    <template #fullWidthHeader>
      <div class="bg-gradient-to-r from-purple-600 via-purple-700 to-purple-800 border-b border-purple-900/20 w-full">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
          <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <!-- Header Info -->
            <div class="flex-1 min-w-0">
              <div class="flex items-center space-x-4 mb-3">
                <!-- Employee Photo -->
                <div class="flex-shrink-0">
                  <img
                    :src="photoPreview || getPhotoUrl(employee.photo_path)"
                    :alt="`${employee.first_name} ${employee.last_name}`"
                    class="h-16 w-16 rounded-full object-cover border-4 border-white/20 shadow-lg"
                    @error="handleImageError"
                  />
                </div>
                
                <div>
                  <h1 class="text-2xl lg:text-3xl font-bold text-white">
                    Çalışan Düzenle
                  </h1>
                  <div class="flex flex-wrap items-center gap-3 mt-2">
                    <p class="text-purple-100 text-sm">
                      {{ employee.first_name }} {{ employee.last_name }} • {{ employee.employee_code }}
                    </p>
                    <Badge
                      :text="getStatusLabel(employee.status)"
                      :variant="getStatusVariant(employee.status)"
                    />
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="flex-shrink-0">
              <div class="flex flex-wrap items-center gap-3">
                <!-- View Button -->
                <Button
                  variant="outline"
                  size="sm"
                  :href="route('employees.show', employee.id)"
                  class="bg-white/10 backdrop-blur-sm border-white/30 text-white hover:bg-white/20"
                >
                  <EyeIcon class="w-4 h-4 mr-2" />
                  Görüntüle
                </Button>
                
                <!-- Back Button -->
                <Button
                  variant="outline"
                  :href="route('employees.index')"
                  class="bg-white text-purple-600 hover:bg-purple-50"
                >
                  <ArrowLeftIcon class="w-4 h-4 mr-2" />
                  Geri Dön
                </Button>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Breadcrumb inside header -->
        <div class="bg-black/10 border-t border-white/10">
          <div class="w-full px-4 sm:px-6 lg:px-8">
            <div class="flex items-center space-x-2 py-2">
              <ol class="flex items-center space-x-2">
                <!-- Home -->
                <li>
                  <Link
                    :href="route('dashboard')"
                    class="text-purple-100 hover:text-white transition-colors"
                  >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                    </svg>
                    <span class="sr-only">Ana Sayfa</span>
                  </Link>
                </li>

                <!-- Breadcrumb items -->
                <li v-for="(item, index) in breadcrumbs" :key="index" class="flex items-center">
                  <!-- Separator -->
                  <svg class="h-3 w-3 text-purple-200 mx-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                  </svg>

                  <!-- Breadcrumb item -->
                  <div class="flex items-center">
                    <Link
                      v-if="item.href && index < breadcrumbs.length - 1"
                      :href="item.href"
                      class="text-xs font-medium text-purple-100 hover:text-white transition-colors"
                    >
                      {{ item.label }}
                    </Link>
                    <span
                      v-else
                      class="text-xs font-medium text-white"
                      :aria-current="index === breadcrumbs.length - 1 ? 'page' : undefined"
                    >
                      {{ item.label }}
                    </span>
                  </div>
                </li>
              </ol>
            </div>
          </div>
        </div>
      </div>
    </template>

    <!-- Main Content Container with proper spacing -->
    <div class="w-full px-4 sm:px-6 lg:px-8 pt-8 pb-6 space-y-6">
      <!-- Change Tracking Alert -->
      <div v-if="hasChanges" class="bg-amber-50 border border-amber-200 rounded-md p-4">
        <div class="flex">
          <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
            </svg>
          </div>
          <div class="ml-3">
            <h3 class="text-sm font-medium text-amber-800">Kaydedilmemiş Değişiklikler</h3>
            <div class="mt-2 text-sm text-amber-700">
              <p>{{ changedFields.length }} alanda değişiklik yapıldı. Değişiklikleri kaydetmeyi unutmayın.</p>
              <ul class="mt-1 list-disc list-inside">
                <li v-for="field in changedFields" :key="field" class="text-xs">
                  {{ fieldLabels[field] || field }}
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>

      <!-- Tabbed Form -->
      <Card>
        <!-- Tab Navigation -->
        <div class="border-b border-gray-200">
          <nav class="-mb-px flex space-x-8 px-6">
            <button
              v-for="tab in tabs"
              :key="tab.id"
              @click="activeTab = tab.id"
              :class="[
                'py-4 px-1 border-b-2 font-medium text-sm transition-colors',
                activeTab === tab.id
                  ? 'border-purple-500 text-purple-600'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
              ]"
            >
              <component :is="tab.icon" class="w-4 h-4 mr-2 inline" />
              {{ tab.label }}
              <Badge
                v-if="getTabChanges(tab.id).length > 0"
                text="!"
                variant="warning"
                size="xs"
                class="ml-2"
              />
            </button>
          </nav>
        </div>

        <form @submit.prevent="handleSubmit" class="p-6">
          <!-- Personal Information Tab -->
          <div v-if="activeTab === 'personal'" class="space-y-6">
            <div class="flex items-center justify-between pb-4 border-b border-gray-200">
              <div>
                <h2 class="text-lg font-semibold text-gray-900">Kişisel Bilgiler</h2>
                <p class="text-sm text-gray-600 mt-1">Temel kişisel bilgileri güncelleyin</p>
              </div>
              
              <!-- Current Photo -->
              <div v-if="employee.photo_path || photoPreview" class="flex-shrink-0">
                <img
                  :src="photoPreview || getPhotoUrl(employee.photo_path)"
                  :alt="`${employee.first_name} ${employee.last_name}`"
                  class="h-16 w-16 rounded-full object-cover border-2 border-gray-200"
                  @error="handleImageError"
                />
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Ad -->
              <Input
                v-model="form.first_name"
                label="Ad"
                placeholder="Adını girin"
                required
                :error="form.errors.first_name"
                @update:model-value="trackChange('first_name')"
              />

              <!-- Soyad -->
              <Input
                v-model="form.last_name"
                label="Soyad"
                placeholder="Soyadını girin"
                required
                :error="form.errors.last_name"
                @update:model-value="trackChange('last_name')"
              />

              <!-- TC Kimlik No -->
              <Input
                v-model="form.tc_number"
                label="TC Kimlik Numarası"
                placeholder="12345678901"
                type="text"
                pattern="[0-9]{11}"
                maxlength="11"
                required
                :error="form.errors.tc_number"
                help-text="11 haneli TC kimlik numarası"
                @update:model-value="trackChange('tc_number')"
              />

              <!-- Doğum Tarihi -->
              <Input
                v-model="form.birth_date"
                label="Doğum Tarihi"
                type="date"
                required
                :error="form.errors.birth_date"
                @update:model-value="trackChange('birth_date')"
              />

              <!-- Cinsiyet -->
              <Select
                v-model="form.gender"
                label="Cinsiyet"
                placeholder="Cinsiyet seçin"
                :options="genderOptions"
                :error="form.errors.gender"
                @update:model-value="trackChange('gender')"
              />

              <!-- Telefon -->
              <Input
                v-model="form.phone"
                label="Telefon"
                type="tel"
                placeholder="+90 (555) 123-4567"
                :error="form.errors.phone"
                @update:model-value="trackChange('phone')"
              />

              <!-- Email -->
              <Input
                v-model="form.email"
                label="E-posta"
                type="email"
                placeholder="ornek@email.com"
                :error="form.errors.email"
                @update:model-value="trackChange('email')"
              />

              <!-- Durum -->
              <Select
                v-model="form.status"
                label="Durum"
                placeholder="Durum seçin"
                :options="statusOptions"
                :error="form.errors.status"
                @update:model-value="trackChange('status')"
              />
            </div>

            <!-- Adres -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Adres</label>
              <textarea
                v-model="form.address"
                @input="trackChange('address')"
                rows="3"
                class="block w-full rounded-md border-0 py-2 px-3 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-purple-500 text-gray-900 text-sm"
                placeholder="Tam adres bilgisi"
                :class="form.errors.address ? 'ring-red-300 focus:ring-red-500' : ''"
              ></textarea>
              <p v-if="form.errors.address" class="text-xs text-red-600 mt-1">{{ form.errors.address }}</p>
            </div>

            <!-- Fotoğraf Güncelleme -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-4">Profil Fotoğrafı</label>
              
              <div class="flex items-center space-x-6">
                <!-- Current/Preview Photo -->
                <div class="flex-shrink-0">
                  <img
                    :src="photoPreview || getPhotoUrl(employee.photo_path)"
                    :alt="`${employee.first_name} ${employee.last_name}`"
                    class="h-20 w-20 rounded-full object-cover border-2 border-gray-200"
                    @error="handleImageError"
                  />
                </div>

                <!-- Upload Controls -->
                <div class="flex-1">
                  <div class="flex items-center space-x-3">
                    <Button
                      type="button"
                      variant="outline"
                      size="sm"
                      @click="$refs.photoInput?.click()"
                    >
                      <PhotoIcon class="w-4 h-4 mr-2" />
                      {{ employee.photo_path ? 'Fotoğrafı Değiştir' : 'Fotoğraf Ekle' }}
                    </Button>
                    
                    <Button
                      v-if="employee.photo_path || photoPreview"
                      type="button"
                      variant="ghost"
                      size="sm"
                      @click="removePhoto"
                    >
                      <XMarkIcon class="w-4 h-4 mr-2" />
                      Kaldır
                    </Button>
                  </div>
                  
                  <input
                    ref="photoInput"
                    @change="handlePhotoUpload"
                    type="file"
                    accept="image/jpeg,image/png,image/jpg"
                    class="hidden"
                  />
                  
                  <p class="text-xs text-gray-500 mt-2">PNG, JPG, JPEG dosyaları (max 2MB)</p>
                </div>
              </div>
              
              <p v-if="form.errors.photo" class="text-xs text-red-600 mt-1">{{ form.errors.photo }}</p>
            </div>
          </div>

          <!-- Job Information Tab -->
          <div v-if="activeTab === 'job'" class="space-y-6">
            <div class="pb-4 border-b border-gray-200">
              <h2 class="text-lg font-semibold text-gray-900">İş Bilgileri</h2>
              <p class="text-sm text-gray-600 mt-1">Pozisyon, kategori ve çalışma detayları</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Çalışan Kodu -->
              <Input
                v-model="form.employee_code"
                label="Çalışan Kodu"
                placeholder="Otomatik oluşturulacak"
                required
                :error="form.errors.employee_code"
                @update:model-value="trackChange('employee_code')"
              />

              <!-- Pozisyon -->
              <Input
                v-model="form.position"
                label="Pozisyon"
                placeholder="İnşaat İşçisi, Forman, Mühendis vb."
                required
                :error="form.errors.position"
                @update:model-value="trackChange('position')"
              />

              <!-- Kategori -->
              <Select
                v-model="form.category"
                label="Kategori"
                placeholder="Kategori seçin"
                :options="categoryOptions"
                required
                :error="form.errors.category"
                @update:model-value="trackChange('category')"
              />

              <!-- İşe Başlama Tarihi -->
              <Input
                v-model="form.start_date"
                label="İşe Başlama Tarihi"
                type="date"
                required
                :error="form.errors.start_date"
                @update:model-value="trackChange('start_date')"
              />

              <!-- İşten Ayrılma Tarihi -->
              <Input
                v-model="form.end_date"
                label="İşten Ayrılma Tarihi"
                type="date"
                :error="form.errors.end_date"
                help-text="Boş bırakılırsa aktif çalışan olarak devam eder"
                @update:model-value="trackChange('end_date')"
              />

              <!-- Departman -->
              <Select
                v-model="form.department_id"
                label="Departman"
                placeholder="Departman seçin"
                :options="departmentOptions"
                :error="form.errors.department_id"
                @update:model-value="trackChange('department_id')"
              />

              <!-- Yönetici -->
              <Select
                v-model="form.manager_id"
                label="Yönetici"
                placeholder="Yönetici seçin"
                :options="managerOptions"
                :error="form.errors.manager_id"
                @update:model-value="trackChange('manager_id')"
              />

              <!-- Mevcut Proje -->
              <Select
                v-model="form.current_project_id"
                label="Mevcut Proje"
                placeholder="Proje seçin"
                :options="projectOptions"
                :error="form.errors.current_project_id"
                @update:model-value="trackChange('current_project_id')"
              />

              <!-- Yıllık İzin Günü -->
              <Input
                v-model="form.annual_leave_days"
                label="Yıllık İzin Günü"
                type="number"
                min="14"
                max="30"
                required
                :error="form.errors.annual_leave_days"
                help-text="14-30 gün arası"
                @update:model-value="trackChange('annual_leave_days')"
              />

              <!-- Kullanılan İzin Günü -->
              <Input
                v-model="form.used_leave_days"
                label="Kullanılan İzin Günü"
                type="number"
                min="0"
                :max="form.annual_leave_days"
                :error="form.errors.used_leave_days"
                help-text="Bu yıl kullanılan izin günü sayısı"
                @update:model-value="trackChange('used_leave_days')"
              />
            </div>

            <!-- İzin Durumu Kartı -->
            <Card class="bg-blue-50 border-blue-200">
              <div class="flex items-center justify-between">
                <div>
                  <h3 class="text-sm font-medium text-blue-900">İzin Durumu</h3>
                  <p class="text-sm text-blue-700 mt-1">
                    {{ (form.annual_leave_days || 0) - (form.used_leave_days || 0) }} gün kalan izin hakkı
                  </p>
                </div>
                <div class="text-right">
                  <div class="text-2xl font-bold text-blue-600">
                    {{ Math.round((((form.annual_leave_days || 0) - (form.used_leave_days || 0)) / (form.annual_leave_days || 1)) * 100) }}%
                  </div>
                  <div class="text-xs text-blue-600">Kalan</div>
                </div>
              </div>
            </Card>
          </div>

          <!-- Salary Information Tab -->
          <div v-if="activeTab === 'salary'" class="space-y-6">
            <div class="pb-4 border-b border-gray-200">
              <h2 class="text-lg font-semibold text-gray-900">Maaş Bilgileri</h2>
              <p class="text-sm text-gray-600 mt-1">Ücret türü ve maaş detayları</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Ücret Türü -->
              <Select
                v-model="form.wage_type"
                label="Ücret Türü"
                placeholder="Ücret türü seçin"
                :options="wageTypeOptions"
                required
                :error="form.errors.wage_type"
                @update:model-value="handleWageTypeChange"
              />

              <!-- Günlük Ücret -->
              <Input
                v-if="form.wage_type === 'daily'"
                v-model="form.daily_wage"
                label="Günlük Ücret (₺)"
                type="number"
                step="0.01"
                min="0"
                placeholder="500.00"
                :error="form.errors.daily_wage"
                @update:model-value="trackChange('daily_wage')"
              />

              <!-- Saatlik Ücret -->
              <Input
                v-if="form.wage_type === 'hourly'"
                v-model="form.hourly_wage"
                label="Saatlik Ücret (₺)"
                type="number"
                step="0.01"
                min="0"
                placeholder="50.00"
                :error="form.errors.hourly_wage"
                @update:model-value="trackChange('hourly_wage')"
              />

              <!-- Aylık Maaş -->
              <Input
                v-if="form.wage_type === 'monthly'"
                v-model="form.monthly_salary"
                label="Aylık Maaş (₺)"
                type="number"
                step="0.01"
                min="0"
                placeholder="15000.00"
                :error="form.errors.monthly_salary"
                @update:model-value="trackChange('monthly_salary')"
              />
            </div>

            <!-- Maaş Geçmişi -->
            <Card>
              <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-medium text-gray-900">Maaş Değişiklik Geçmişi</h3>
                <Badge text="Son 6 Ay" variant="secondary" size="sm" />
              </div>
              
              <div class="space-y-3">
                <div v-for="(change, index) in salaryHistory" :key="index" class="flex items-center justify-between py-2 border-b border-gray-100 last:border-0">
                  <div class="flex items-center space-x-3">
                    <div class="flex-shrink-0">
                      <Badge
                        :text="change.type"
                        :variant="change.type === 'increase' ? 'success' : change.type === 'decrease' ? 'error' : 'info'"
                        size="xs"
                      />
                    </div>
                    <div>
                      <p class="text-sm font-medium text-gray-900">{{ change.amount }} ₺</p>
                      <p class="text-xs text-gray-500">{{ change.date }}</p>
                    </div>
                  </div>
                  <p class="text-xs text-gray-600">{{ change.reason }}</p>
                </div>
              </div>
            </Card>
          </div>

          <!-- Submit Buttons -->
          <div class="flex items-center justify-between pt-6 border-t border-gray-200 mt-8">
            <div class="flex items-center space-x-2">
              <Button
                type="button"
                variant="ghost"
                @click="resetForm"
                :disabled="!hasChanges"
              >
                <ArrowPathIcon class="w-4 h-4 mr-2" />
                Değişiklikleri Geri Al
              </Button>
            </div>

            <div class="flex items-center space-x-3">
              <Button
                type="button"
                variant="outline"
                :href="route('employees.show', employee.id)"
              >
                İptal
              </Button>

              <Button
                type="submit"
                variant="primary"
                :loading="form.processing"
                :disabled="!hasChanges"
              >
                <CheckIcon class="w-4 h-4 mr-2" />
                Değişiklikleri Kaydet
              </Button>
            </div>
          </div>
        </form>
      </Card>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue'
import { router, useForm, Link } from '@inertiajs/vue3'
import axios from 'axios'
import AppLayout from '@/Layouts/AppLayout.vue'
import Button from '@/Components/UI/Button.vue'
import Input from '@/Components/UI/Input.vue'
import Select from '@/Components/UI/Select.vue'
import Card from '@/Components/UI/Card.vue'
import Badge from '@/Components/UI/Badge.vue'

// Heroicons
import {
  UserIcon,
  BriefcaseIcon,
  CurrencyDollarIcon,
  ArrowLeftIcon,
  EyeIcon,
  CheckIcon,
  PhotoIcon,
  XMarkIcon,
  ArrowPathIcon
} from '@heroicons/vue/24/outline'

// Props - UPDATED to match controller data structure
const props = defineProps({
  employee: {
    type: Object,
    required: true
  },
  departments: {
    type: Array,
    default: () => []
  },
  projects: {
    type: Array,
    default: () => []
  },
  managers: {
    type: Array,
    default: () => []
  },
  wageTypes: {
    type: Array,
    default: () => []
  },
  categories: {
    type: Array,
    default: () => []
  },
  statuses: {
    type: Array,
    default: () => []
  }
})

// Breadcrumbs
const breadcrumbs = [
  { label: 'Çalışanlar', href: route('employees.index') },
  { label: props.employee.first_name + ' ' + props.employee.last_name, href: route('employees.show', props.employee.id) },
  { label: 'Düzenle' }
]

// Form state - UPDATED to include ALL fields from model
const form = useForm({
  employee_code: props.employee.employee_code || '',
  first_name: props.employee.first_name || '',
  last_name: props.employee.last_name || '',
  tc_number: props.employee.tc_number || '',
  birth_date: props.employee.birth_date || '',
  gender: props.employee.gender || '',
  phone: props.employee.phone || '',
  email: props.employee.email || '',
  address: props.employee.address || '',
  position: props.employee.position || '',
  category: props.employee.category || '',
  start_date: props.employee.start_date || '',
  end_date: props.employee.end_date || '',
  manager_id: props.employee.manager_id || '',
  current_project_id: props.employee.current_project_id || '',
  department_id: props.employee.department_id || '',
  annual_leave_days: parseInt(props.employee.annual_leave_days) || 14,
  used_leave_days: parseInt(props.employee.used_leave_days) || 0,
  wage_type: props.employee.wage_type || '',
  daily_wage: parseFloat(props.employee.daily_wage) || '',
  hourly_wage: parseFloat(props.employee.hourly_wage) || '',
  monthly_salary: parseFloat(props.employee.monthly_salary) || '',
  status: props.employee.status || 'active',
  photo: null
})

// Component state
const activeTab = ref('personal')
const photoPreview = ref(null)
const originalData = ref({})
const changedFields = ref([])
const imageLoadFailed = ref(false)

// Tab configuration
const tabs = [
  { id: 'personal', label: 'Kişisel Bilgiler', icon: UserIcon },
  { id: 'job', label: 'İş Bilgileri', icon: BriefcaseIcon },
  { id: 'salary', label: 'Maaş Bilgileri', icon: CurrencyDollarIcon }
]

// Field labels for change tracking - UPDATED to include all fields
const fieldLabels = {
  employee_code: 'Çalışan Kodu',
  first_name: 'Ad',
  last_name: 'Soyad',
  tc_number: 'TC Kimlik No',
  birth_date: 'Doğum Tarihi',
  gender: 'Cinsiyet',
  phone: 'Telefon',
  email: 'E-posta',
  address: 'Adres',
  position: 'Pozisyon',
  category: 'Kategori',
  start_date: 'İşe Başlama Tarihi',
  end_date: 'İşten Ayrılma Tarihi',
  manager_id: 'Yönetici',
  current_project_id: 'Mevcut Proje',
  department_id: 'Departman',
  annual_leave_days: 'Yıllık İzin Günü',
  used_leave_days: 'Kullanılan İzin Günü',
  wage_type: 'Ücret Türü',
  daily_wage: 'Günlük Ücret',
  hourly_wage: 'Saatlik Ücret',
  monthly_salary: 'Aylık Maaş',
  status: 'Durum'
}

// Options - UPDATED to use props data
const genderOptions = [
  { label: 'Erkek', value: 'male' },
  { label: 'Kadın', value: 'female' }
]

// Computed options from props
const categoryOptions = computed(() => 
  props.categories.map(cat => ({
    label: cat.label,
    value: cat.value
  }))
)

const wageTypeOptions = computed(() => 
  props.wageTypes.map(wage => ({
    label: wage.label,
    value: wage.value
  }))
)

const statusOptions = computed(() => 
  props.statuses.map(status => ({
    label: status.label,
    value: status.value
  }))
)

const departmentOptions = computed(() => 
  props.departments.map(dept => ({
    label: dept.name,
    value: dept.id
  }))
)

const managerOptions = computed(() => 
  props.managers.map(manager => ({
    label: manager.name,
    value: manager.id
  }))
)

const projectOptions = computed(() => 
  props.projects.map(project => ({
    label: project.name,
    value: project.id
  }))
)

// Change tracking
const hasChanges = computed(() => changedFields.value.length > 0)

// Mock salary history (in real app, this would come from props)
const salaryHistory = computed(() => {
  if (!props.employee.salary_history || props.employee.salary_history.length === 0) {
    return []
  }
  
  return props.employee.salary_history.map(change => ({
    type: change.change_type_label?.toLowerCase() || 'adjustment',
    amount: change.formatted_change_amount || `${change.change_amount} ₺`,
    date: new Date(change.effective_date).toLocaleDateString('tr-TR'),
    reason: change.reason || 'Belirtilmemiş'
  }))
})

// Utility methods
const getPhotoUrl = (photoPath) => {
  if (imageLoadFailed.value) {
    return 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjQiIGhlaWdodD0iNjQiIHZpZXdCb3g9IjAgMCA2NCA2NCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjY0IiBoZWlnaHQ9IjY0IiByeD0iMzIiIGZpbGw9IiNGMzU0RjciLz4KPHN2ZyB3aWR0aD0iMzIiIGhlaWdodD0iMzIiIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0ibm9uZSIgeD0iMTYiIHk9IjE2Ij4KPHN2ZyB3aWR0aD0iMzIiIGhlaWdodD0iMzIiIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHBhdGggZD0iTTEyIDEyQzE0LjIwOTEgMTIgMTYgMTAuMjA5MSAxNiA4QzE2IDUuNzkwODYgMTQuMjA5MSA0IDEyIDRDOS43OTA4NiA0IDggNS43OTA4NiA4IDhDOCAxMC4yMDkxIDkuNzkwODYgMTIgMTIgMTJaIiBmaWxsPSJ3aGl0ZSIvPgo8cGF0aCBkPSJNMTIgMTRDOC4xMzQwMSAxNCA1IDE3LjEzNDAgNSAyMUg3QzcgMTguMjM4NiA5LjIzODU4IDE2IDEyIDE2QzE0Ljc2MTQgMTYgMTcgMTguMjM4NiAxNyAyMUgxOUMxOSAxNy4xMzQwIDE1Ljg2NiAxNCAxMiAxNFoiIGZpbGw9IndoaXRlIi8+Cjwvc3ZnPgo8L3N2Zz4K'
  }
  
  if (!photoPath) {
    return 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjQiIGhlaWdodD0iNjQiIHZpZXdCb3g9IjAgMCA2NCA2NCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjY0IiBoZWlnaHQ9IjY0IiByeD0iMzIiIGZpbGw9IiNGMzU0RjciLz4KPHN2ZyB3aWR0aD0iMzIiIGhlaWdodD0iMzIiIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0ibm9uZSIgeD0iMTYiIHk9IjE2Ij4KPHN2ZyB3aWR0aD0iMzIiIGhlaWdodD0iMzIiIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHBhdGggZD0iTTEyIDEyQzE0LjIwOTEgMTIgMTYgMTAuMjA5MSAxNiA4QzE2IDUuNzkwODYgMTQuMjA5MSA0IDEyIDRDOS43OTA4NiA0IDggNS43OTA4NiA4IDhDOCAxMC4yMDkxIDkuNzkwODYgMTIgMTIgMTJaIiBmaWxsPSJ3aGl0ZSIvPgo8cGF0aCBkPSJNMTIgMTRDOC4xMzQwMSAxNCA1IDE3LjEzNDAgNSAyMUg3QzcgMTguMjM4NiA5LjIzODU4IDE2IDEyIDE2QzE0Ljc2MTQgMTYgMTcgMTguMjM4NiAxNyAyMUgxOUMxOSAxNy4xMzQwIDE1Ljg2NiAxNCAxMiAxNFoiIGZpbGw9IndoaXRlIi8+Cjwvc3ZnPgo8L3N2Zz4K'
  }
  
  return `/storage/${photoPath}`
}

const handleImageError = (event) => {
  if (!imageLoadFailed.value) {
    imageLoadFailed.value = true
    event.target.src = getPhotoUrl(null)
  }
}

const getStatusLabel = (status) => {
  const labels = {
    active: 'Aktif',
    inactive: 'Pasif',
    suspended: 'Askıya Alınmış'
  }
  return labels[status] || status
}

const getStatusVariant = (status) => {
  const variants = {
    active: 'success',
    inactive: 'secondary',
    suspended: 'warning'
  }
  return variants[status] || 'secondary'
}

// Methods
const trackChange = (field) => {
  const originalValue = originalData.value[field]
  const currentValue = form[field]  // FIXED: form.data yerine form
  
  if (originalValue !== currentValue) {
    if (!changedFields.value.includes(field)) {
      changedFields.value.push(field)
    }
  } else {
    const index = changedFields.value.indexOf(field)
    if (index > -1) {
      changedFields.value.splice(index, 1)
    }
  }
}

const getTabChanges = (tabId) => {
  const tabFields = {
    personal: ['first_name', 'last_name', 'tc_number', 'birth_date', 'gender', 'phone', 'email', 'address', 'status'],
    job: ['employee_code', 'position', 'category', 'start_date', 'end_date', 'department_id', 'manager_id', 'current_project_id', 'annual_leave_days', 'used_leave_days'],
    salary: ['wage_type', 'daily_wage', 'hourly_wage', 'monthly_salary']
  }
  
  return changedFields.value.filter(field => tabFields[tabId]?.includes(field))
}

const handleWageTypeChange = (newWageType) => {
  trackChange('wage_type')
  
  // Clear other wage fields when type changes
  if (newWageType !== 'daily') {
    form.daily_wage = ''
  }
  if (newWageType !== 'hourly') {
    form.hourly_wage = ''
  }
  if (newWageType !== 'monthly') {
    form.monthly_salary = ''
  }
}

const handlePhotoUpload = (event) => {
  console.log('=== PHOTO UPLOAD DEBUG ===')
  console.log('1. Event triggered')
  console.log('2. Files:', event.target.files)
  console.log('3. Files length:', event.target.files?.length)
  
  const file = event.target.files[0]
  console.log('4. Selected file:', file)
  
  if (file) {
    console.log('5. File details:')
    console.log('   - name:', file.name)
    console.log('   - size:', file.size, 'bytes')
    console.log('   - type:', file.type)
    console.log('   - lastModified:', file.lastModified)
    
    // Validate file
    if (!['image/jpeg', 'image/png', 'image/jpg'].includes(file.type)) {
      console.error('6. Invalid file type:', file.type)
      alert('Sadece JPG, JPEG ve PNG formatları desteklenmektedir.')
      return
    }
    
    if (file.size > 2 * 1024 * 1024) { // 2MB
      console.error('7. File too large:', file.size)
      alert('Dosya boyutu 2MB\'dan küçük olmalıdır.')
      return
    }
    
    console.log('8. File validation passed')
    
    // Set form data
    form.photo = file
    console.log('9. form.photo set to:', form.photo)
    console.log('10. form.photo instanceof File:', form.photo instanceof File)
    
    trackChange('photo')
    console.log('11. trackChange called for photo')
    console.log('12. changedFields now:', [...changedFields.value])
    
    // Create preview
    const reader = new FileReader()
    reader.onload = (e) => {
      photoPreview.value = e.target.result
      console.log('13. Photo preview created, length:', e.target.result?.length)
    }
    reader.onerror = (e) => {
      console.error('14. FileReader error:', e)
    }
    reader.readAsDataURL(file)
  } else {
    console.error('15. No file selected')
  }
}

const removePhoto = () => {
  form.photo = null
  photoPreview.value = null
  trackChange('photo')
}

const resetForm = () => {
  // Reset form to original values
  Object.keys(originalData.value).forEach(key => {
    form[key] = originalData.value[key]  // FIXED: form.data yerine form
  })
  
  // Clear photo preview and changes
  photoPreview.value = null
  changedFields.value = []
}

const handleSubmit = () => {
  console.log('=== FORM SUBMIT DEBUG ===')
  console.log('1. Form Data:', form.data)
  console.log('2. Has Changes:', hasChanges.value)
  console.log('3. Changed Fields:', changedFields.value)
  console.log('4. Employee ID:', props.employee.id)
  
  form.patch(route('employees.update', props.employee.id), {
    preserveScroll: true,
    onSuccess: (response) => {
      console.log('SUCCESS:', response)
      
      // Update original data and clear changes
      const newOriginalData = {}
      Object.keys(form.data).forEach(key => {
        newOriginalData[key] = form[key]  // FIXED: form.data yerine form
      })
      originalData.value = newOriginalData
      changedFields.value = []
      photoPreview.value = null
      
      // Redirect to show page
      router.visit(route('employees.show', props.employee.id))
    },
    onError: (errors) => {
      console.log('ERRORS:', errors)
      
      // Switch to tab with errors
      if (errors.first_name || errors.last_name || errors.tc_number || errors.birth_date || errors.gender || errors.phone || errors.email || errors.address || errors.status || errors.photo) {
        activeTab.value = 'personal'
      } else if (errors.employee_code || errors.position || errors.category || errors.start_date || errors.end_date || errors.manager_id || errors.current_project_id || errors.department_id || errors.annual_leave_days || errors.used_leave_days) {
        activeTab.value = 'job'
      } else if (errors.wage_type || errors.daily_wage || errors.hourly_wage || errors.monthly_salary) {
        activeTab.value = 'salary'
      }
    }
  })
}

// FIXED: Save original data correctly on mount
onMounted(() => {
  console.log('Props employee data:', props.employee)
  
  const initialData = {
    employee_code: props.employee.employee_code || '',
    first_name: props.employee.first_name || '',
    last_name: props.employee.last_name || '',
    tc_number: props.employee.tc_number || '',
    birth_date: props.employee.birth_date || '',
    gender: props.employee.gender || '',
    phone: props.employee.phone || '',
    email: props.employee.email || '',
    address: props.employee.address || '',
    position: props.employee.position || '',
    category: props.employee.category || '',
    start_date: props.employee.start_date || '',
    end_date: props.employee.end_date || '',
    manager_id: props.employee.manager_id || '',
    current_project_id: props.employee.current_project_id || '',
    department_id: props.employee.department_id || '',
    annual_leave_days: props.employee.annual_leave_days || 14,
    used_leave_days: props.employee.used_leave_days || 0,
    wage_type: props.employee.wage_type || '',
    daily_wage: props.employee.daily_wage || '',
    hourly_wage: props.employee.hourly_wage || '',
    monthly_salary: props.employee.monthly_salary || '',
    status: props.employee.status || 'active',
    photo: null
  }
  
  originalData.value = initialData
  console.log('Original data saved:', originalData.value)
})

// Handle page leave warning
onMounted(() => {
  const handleBeforeUnload = (e) => {
    if (hasChanges.value) {
      e.preventDefault()
      e.returnValue = ''
    }
  }
  
  window.addEventListener('beforeunload', handleBeforeUnload)
  
  return () => {
    window.removeEventListener('beforeunload', handleBeforeUnload)
  }
})
</script>