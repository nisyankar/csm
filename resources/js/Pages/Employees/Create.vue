<template>
  <AppLayout title="Yeni Çalışan Ekle - SPT İnşaat Puantaj Sistemi" :full-width="true">
    <!-- Full-width header -->
    <template #fullWidthHeader>
      <div class="bg-gradient-to-r from-indigo-600 via-purple-700 to-pink-800 border-b border-indigo-900/20 w-full">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
          <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
              <div class="flex-shrink-0 w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm">
                <UserPlusIcon class="w-6 h-6 text-white" />
              </div>
              <div>
                <h1 class="text-2xl lg:text-3xl font-bold text-white">Yeni Çalışan Ekle</h1>
                <p class="text-indigo-100 text-sm mt-1">
                  Adım {{ currentStep }} / {{ totalSteps }} - {{ stepTitles[currentStep - 1] }}
                </p>
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
                  <Link :href="route('employees.index')" class="text-indigo-100 hover:text-white text-xs">
                    Çalışan Yönetimi
                  </Link>
                </li>
                <li class="flex items-center">
                  <svg class="h-3 w-3 text-indigo-200 mx-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                  </svg>
                  <span class="text-xs font-medium text-white">Yeni Çalışan</span>
                </li>
              </ol>
            </div>
          </div>
        </div>
      </div>
    </template>

    <!-- Main Content -->
    <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
      <!-- Progress Steps -->
      <div class="mb-6">
        <nav class="flex items-center justify-center">
          <ol class="flex items-center w-full max-w-4xl">
            <li
              v-for="(step, index) in steps"
              :key="index"
              :class="[
                'flex items-center',
                index < steps.length - 1 ? 'w-full' : ''
              ]"
            >
              <!-- Step Circle -->
              <div
                :class="[
                  'flex items-center justify-center w-10 h-10 rounded-full text-sm font-medium transition-all duration-200',
                  currentStep > index + 1
                    ? 'bg-green-600 text-white shadow-lg'
                    : currentStep === index + 1
                    ? 'bg-gradient-to-br from-indigo-600 to-purple-700 text-white shadow-lg'
                    : 'bg-gray-200 text-gray-500'
                ]"
              >
                <CheckIcon v-if="currentStep > index + 1" class="w-5 h-5" />
                <span v-else>{{ index + 1 }}</span>
              </div>

              <!-- Step Title -->
              <div class="ml-3 hidden sm:block">
                <p :class="[
                  'text-sm font-medium transition-colors',
                  currentStep > index + 1
                    ? 'text-green-600'
                    : currentStep === index + 1
                    ? 'text-indigo-600'
                    : 'text-gray-500'
                ]">
                  {{ step.title }}
                </p>
              </div>

              <!-- Connector Line -->
              <div
                v-if="index < steps.length - 1"
                :class="[
                  'hidden sm:block flex-auto h-0.5 ml-4 transition-colors duration-200',
                  currentStep > index + 1 ? 'bg-green-600' : 'bg-gray-200'
                ]"
              ></div>
            </li>
          </ol>
        </nav>
      </div>

      <!-- Form Container -->
      <form @submit.prevent="handleSubmit" class="space-y-6">
        <!-- Step 1: Kişisel Bilgiler -->
        <Card v-if="currentStep === 1">
          <div class="p-6">
            <div class="flex items-center space-x-2 pb-6 border-b border-gray-200">
              <UserIcon class="w-6 h-6 text-indigo-600" />
              <div>
                <h2 class="text-lg font-semibold text-gray-900">Kişisel Bilgiler</h2>
                <p class="text-sm text-gray-600">Çalışanın temel kişisel bilgilerini girin</p>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
              <!-- Ad -->
              <Input
                v-model="form.first_name"
                label="Ad"
                placeholder="Adını girin"
                required
                :error="errors.first_name"
                :left-icon="UserIcon"
              />

              <!-- Soyad -->
              <Input
                v-model="form.last_name"
                label="Soyad"
                placeholder="Soyadını girin"
                required
                :error="errors.last_name"
                :left-icon="UserIcon"
              />

              <!-- TC Kimlik No -->
              <Input
                v-model="form.tc_number"
                label="TC Kimlik Numarası"
                placeholder="12345678901"
                type="text"
                maxlength="11"
                required
                :error="errors.tc_number"
                help-text="11 haneli TC kimlik numarası"
                :left-icon="IdentificationIcon"
              />

              <!-- Doğum Tarihi -->
              <Input
                v-model="form.birth_date"
                label="Doğum Tarihi"
                type="date"
                required
                :error="errors.birth_date"
                :left-icon="CalendarIcon"
              />

              <!-- Cinsiyet -->
              <Select
                v-model="form.gender"
                label="Cinsiyet"
                placeholder="Cinsiyet seçin"
                :options="genderOptions"
                :error="errors.gender"
              />

              <!-- Telefon -->
              <Input
                v-model="form.phone"
                label="Telefon"
                type="tel"
                placeholder="+90 (555) 123-4567"
                :error="errors.phone"
                :left-icon="PhoneIcon"
              />

              <!-- Email -->
              <Input
                v-model="form.email"
                label="E-posta"
                type="email"
                placeholder="ornek@email.com"
                :error="errors.email"
                :left-icon="AtSymbolIcon"
              />

              <!-- Adres -->
              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Adres</label>
                <textarea
                  v-model="form.address"
                  rows="3"
                  class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                  placeholder="Tam adres bilgisi"
                  :class="errors.address ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : ''"
                ></textarea>
                <p v-if="errors.address" class="text-xs text-red-600 mt-1">{{ errors.address }}</p>
              </div>
            </div>
          </div>
        </Card>

        <!-- Step 2: İş Bilgileri -->
        <Card v-if="currentStep === 2">
          <div class="p-6">
            <div class="flex items-center space-x-2 pb-6 border-b border-gray-200">
              <BriefcaseIcon class="w-6 h-6 text-indigo-600" />
              <div>
                <h2 class="text-lg font-semibold text-gray-900">İş Bilgileri</h2>
                <p class="text-sm text-gray-600">Pozisyon, kategori ve çalışma detayları</p>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
              <!-- Pozisyon -->
              <Input
                v-model="form.position"
                label="Pozisyon"
                placeholder="İnşaat İşçisi, Forman, Mühendis vb."
                required
                :error="errors.position"
                :left-icon="BriefcaseIcon"
              />

              <!-- Kategori -->
              <Select
                v-model="form.category"
                label="Kategori"
                placeholder="Kategori seçin"
                :options="categoryOptions"
                required
                :error="errors.category"
              />

              <!-- İşe Başlama Tarihi -->
              <Input
                v-model="form.start_date"
                label="İşe Başlama Tarihi"
                type="date"
                required
                :error="errors.start_date"
                :left-icon="CalendarIcon"
              />

              <!-- Yönetici -->
              <Select
                v-model="form.manager_id"
                label="Yönetici"
                placeholder="Yönetici seçin"
                :options="managerOptions"
                :error="errors.manager_id"
                searchable
              />

              <!-- Mevcut Proje -->
              <Select
                v-model="form.current_project_id"
                label="Mevcut Proje"
                placeholder="Proje seçin"
                :options="projectOptions"
                :error="errors.current_project_id"
                searchable
              />
            </div>

            <!-- Bilgi Notu -->
            <div class="mt-4 bg-gradient-to-r from-indigo-50 to-blue-50 border border-indigo-200 rounded-lg p-4">
              <div class="flex items-start space-x-3">
                <div class="flex-shrink-0">
                  <svg class="h-5 w-5 text-indigo-600 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                  </svg>
                </div>
                <div>
                  <h3 class="text-sm font-medium text-indigo-900">Yıllık İzin Bilgisi</h3>
                  <p class="mt-2 text-sm text-indigo-800">
                    Yıllık izin günü sayısı, işe başlama tarihine göre otomatik olarak hesaplanacaktır. İşçi Kanunu'na göre:
                  </p>
                  <ul class="mt-2 text-sm text-indigo-700 list-disc list-inside space-y-1">
                    <li>1-5 yıl arası: 14 gün</li>
                    <li>5-15 yıl arası: 20 gün</li>
                    <li>15 yıl ve üzeri: 26 gün</li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
        </Card>

        <!-- Step 3: Maaş Bilgileri -->
        <Card v-if="currentStep === 3">
          <div class="p-6">
            <div class="flex items-center space-x-2 pb-6 border-b border-gray-200">
              <CurrencyDollarIcon class="w-6 h-6 text-indigo-600" />
              <div>
                <h2 class="text-lg font-semibold text-gray-900">Maaş Bilgileri</h2>
                <p class="text-sm text-gray-600">Ücret türü ve maaş detayları</p>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
              <!-- Ücret Türü -->
              <div class="md:col-span-2">
                <Select
                  v-model="form.wage_type"
                  label="Ücret Türü"
                  placeholder="Ücret türü seçin"
                  :options="wageTypeOptions"
                  required
                  :error="errors.wage_type"
                  @update:model-value="resetWageFields"
                />
              </div>

              <!-- Günlük Ücret -->
              <Input
                v-model="form.daily_wage"
                label="Günlük Ücret (₺)"
                type="number"
                step="0.01"
                min="0"
                placeholder="500.00"
                :error="errors.daily_wage"
                :left-icon="CurrencyDollarIcon"
                :disabled="form.wage_type !== 'daily'"
              />

              <!-- Saatlik Ücret -->
              <Input
                v-model="form.hourly_wage"
                label="Saatlik Ücret (₺)"
                type="number"
                step="0.01"
                min="0"
                placeholder="50.00"
                :error="errors.hourly_wage"
                :left-icon="CurrencyDollarIcon"
                :disabled="form.wage_type !== 'hourly'"
              />

              <!-- Aylık Maaş -->
              <Input
                v-model="form.monthly_salary"
                label="Aylık Maaş (₺)"
                type="number"
                step="0.01"
                min="0"
                placeholder="15000.00"
                :error="errors.monthly_salary"
                :left-icon="CurrencyDollarIcon"
                :disabled="form.wage_type !== 'monthly'"
              />
            </div>

            <!-- Maaş Bilgi Kartı -->
            <div v-if="form.wage_type" class="mt-6 bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-lg p-4">
              <div class="flex items-start space-x-3">
                <div class="flex-shrink-0">
                  <svg class="h-5 w-5 text-blue-600 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                  </svg>
                </div>
                <div>
                  <h3 class="text-sm font-medium text-blue-900">Ücret Türü Bilgisi</h3>
                  <div class="mt-2 text-sm text-blue-800">
                    <p v-if="form.wage_type === 'daily'">
                      Günlük ücret: Çalışanın her çalıştığı gün için sabit ücret alacağı sistem.
                    </p>
                    <p v-else-if="form.wage_type === 'hourly'">
                      Saatlik ücret: Çalışanın çalıştığı saat başına ücret alacağı sistem.
                    </p>
                    <p v-else-if="form.wage_type === 'monthly'">
                      Aylık maaş: Çalışanın her ay sabit maaş alacağı sistem.
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </Card>

        <!-- Step 4: Fotoğraf ve QR Kod -->
        <Card v-if="currentStep === 4">
          <div class="p-6">
            <div class="flex items-center space-x-2 pb-6 border-b border-gray-200">
              <svg class="w-6 h-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z" />
              </svg>
              <div>
                <h2 class="text-lg font-semibold text-gray-900">Fotoğraf ve QR Kod</h2>
                <p class="text-sm text-gray-600">Profil fotoğrafı ekleyin ve QR kod oluşturun</p>
              </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mt-6">
              <!-- Fotoğraf Yükleme -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-4">Profil Fotoğrafı</label>

                <div class="mt-2 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-indigo-400 transition-colors">
                  <div class="space-y-1 text-center">
                    <!-- Preview Image -->
                    <div v-if="photoPreview" class="mb-4">
                      <img
                        :src="photoPreview"
                        alt="Fotoğraf önizleme"
                        class="mx-auto h-32 w-32 rounded-full object-cover ring-4 ring-indigo-100"
                      />
                    </div>

                    <!-- Upload Icon -->
                    <div v-else class="mx-auto h-12 w-12 text-gray-400">
                      <svg class="h-full w-full" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                      </svg>
                    </div>

                    <div class="flex text-sm text-gray-600">
                      <label for="photo-upload" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                        <span>{{ photoPreview ? 'Fotoğrafı değiştir' : 'Fotoğraf yükle' }}</span>
                        <input
                          id="photo-upload"
                          ref="photoInput"
                          @change="handlePhotoUpload"
                          name="photo"
                          type="file"
                          accept="image/jpeg,image/png,image/jpg"
                          class="sr-only"
                        />
                      </label>
                      <p class="pl-1">veya sürükleyip bırakın</p>
                    </div>
                    <p class="text-xs text-gray-500">PNG, JPG, JPEG dosyaları (max 2MB)</p>
                  </div>
                </div>

                <p v-if="errors.photo" class="text-xs text-red-600 mt-1">{{ errors.photo }}</p>
              </div>

              <!-- QR Kod Preview -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-4">QR Kod Önizleme</label>

                <div class="bg-gradient-to-br from-gray-50 to-indigo-50 rounded-lg p-6 border border-gray-200">
                  <div v-if="generatedQrCode" class="space-y-4">
                    <div class="flex justify-center">
                      <div class="bg-white p-4 rounded-lg shadow-sm border border-indigo-200">
                        <div v-html="generatedQrCode" class="qr-code"></div>
                      </div>
                    </div>
                    <div class="text-center text-sm text-gray-600">
                      <p class="font-medium text-gray-900">{{ form.first_name }} {{ form.last_name }}</p>
                      <p class="text-xs text-indigo-600 mt-1">Çalışan Kodu: {{ employeeCode }}</p>
                    </div>
                  </div>

                  <div v-else class="text-center text-gray-500">
                    <svg class="mx-auto h-12 w-12 mb-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z" />
                      <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 6.75h.75v.75h-.75v-.75zM6.75 16.5h.75v.75h-.75v-.75zM16.5 6.75h.75v.75h-.75v-.75zM13.5 13.5h4.5v4.5h-4.5v-4.5z" />
                    </svg>
                    <p class="text-sm">QR kod otomatik oluşturulacak</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </Card>

        <!-- Navigation Buttons -->
        <div class="flex items-center justify-between bg-white rounded-lg shadow-sm border border-gray-200 px-6 py-4">
          <Button
            v-if="currentStep > 1"
            variant="outline"
            @click="previousStep"
            :left-icon="ArrowLeftIcon"
          >
            Önceki
          </Button>
          <div v-else></div>

          <div class="flex items-center space-x-3">
            <Button
              variant="ghost"
              :href="route('employees.index')"
            >
              İptal
            </Button>

            <Button
              v-if="currentStep < totalSteps"
              variant="primary"
              @click="nextStep"
              :right-icon="ArrowRightIcon"
            >
              Sonraki
            </Button>

            <Button
              v-else
              type="submit"
              variant="success"
              :loading="processing"
              :left-icon="CheckIcon"
            >
              Çalışanı Oluştur
            </Button>
          </div>
        </div>
      </form>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { useForm, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Button from '@/Components/UI/Button.vue'
import Input from '@/Components/UI/Input.vue'
import Select from '@/Components/UI/Select.vue'
import Card from '@/Components/UI/Card.vue'

// Heroicons
import {
  UserIcon,
  UserPlusIcon,
  IdentificationIcon,
  CalendarIcon,
  PhoneIcon,
  AtSymbolIcon,
  BriefcaseIcon,
  CurrencyDollarIcon,
  ArrowLeftIcon,
  ArrowRightIcon,
  CheckIcon
} from '@heroicons/vue/24/outline'

// Props
const props = defineProps({
  projects: Array,
  managers: Array
})

// Form state
const { data: form, setData, post, processing, errors } = useForm({
  first_name: '',
  last_name: '',
  tc_number: '',
  birth_date: '',
  gender: '',
  phone: '',
  email: '',
  address: '',
  position: '',
  category: '',
  start_date: new Date().toISOString().split('T')[0],
  manager_id: '',
  current_project_id: '',
  // annual_leave_days will be calculated automatically in backend
  wage_type: '',
  daily_wage: '',
  hourly_wage: '',
  monthly_salary: '',
  photo: null
})

// Multi-step state
const currentStep = ref(1)
const totalSteps = 4
const photoPreview = ref(null)
const photoInput = ref(null)
const generatedQrCode = ref('')

// Step configuration
const steps = [
  { title: 'Kişisel Bilgiler', icon: UserIcon },
  { title: 'İş Bilgileri', icon: BriefcaseIcon },
  { title: 'Maaş Bilgileri', icon: CurrencyDollarIcon },
  { title: 'Fotoğraf & QR Kod', icon: CheckIcon }
]

const stepTitles = steps.map(step => step.title)

// Options
const genderOptions = [
  { label: 'Erkek', value: 'male' },
  { label: 'Kadın', value: 'female' }
]

const categoryOptions = [
  { label: 'İşçi', value: 'worker' },
  { label: 'Forman', value: 'foreman' },
  { label: 'Mühendis', value: 'engineer' },
  { label: 'Yönetici', value: 'manager' }
]

const wageTypeOptions = [
  { label: 'Günlük', value: 'daily' },
  { label: 'Saatlik', value: 'hourly' },
  { label: 'Aylık', value: 'monthly' }
]

// Computed options
const managerOptions = computed(() => {
  if (!props.managers || !Array.isArray(props.managers)) {
    return []
  }
  return props.managers.map(manager => ({
    label: manager.name || `${manager.first_name || ''} ${manager.last_name || ''}`.trim(),
    value: manager.id
  }))
})

const projectOptions = computed(() => {
  if (!props.projects || !Array.isArray(props.projects)) {
    return []
  }
  return props.projects.map(project => ({
    label: project.name,
    value: project.id
  }))
})

const employeeCode = computed(() => {
  if (form.first_name && form.last_name) {
    const date = new Date()
    const year = date.getFullYear()
    const month = String(date.getMonth() + 1).padStart(2, '0')
    const initials = (form.first_name.charAt(0) + form.last_name.charAt(0)).toUpperCase()
    const timestamp = String(Date.now()).slice(-4)
    return `EMP${year}${month}${initials}${timestamp}`
  }
  return ''
})

// Step validation
const canProceedToNextStep = computed(() => {
  const step = currentStep.value
  let canProceed = false

  switch (step) {
    case 1:
      canProceed = !!(form.first_name && form.last_name && form.tc_number && form.birth_date)
      break
    case 2:
      canProceed = !!(form.position && form.category && form.start_date)
      break
    case 3:
      canProceed = !!(form.wage_type && (
        (form.wage_type === 'daily' && form.daily_wage) ||
        (form.wage_type === 'hourly' && form.hourly_wage) ||
        (form.wage_type === 'monthly' && form.monthly_salary)
      ))
      break
    case 4:
      canProceed = true
      break
    default:
      canProceed = false
  }

  return canProceed
})

// Methods
const nextStep = () => {
  if (canProceedToNextStep.value && currentStep.value < totalSteps) {
    currentStep.value++

    // Generate QR code when reaching last step
    if (currentStep.value === 4) {
      generateQrCode()
    }
  }
}

const previousStep = () => {
  if (currentStep.value > 1) {
    currentStep.value--
  }
}

const resetWageFields = () => {
  form.daily_wage = ''
  form.hourly_wage = ''
  form.monthly_salary = ''
}

const handlePhotoUpload = (event) => {
  const file = event.target.files[0]
  if (file) {
    // Validate file
    if (!['image/jpeg', 'image/png', 'image/jpg'].includes(file.type)) {
      alert('Sadece JPG, JPEG ve PNG formatları desteklenmektedir.')
      return
    }

    if (file.size > 2 * 1024 * 1024) { // 2MB
      alert('Dosya boyutu 2MB\'dan küçük olmalıdır.')
      return
    }

    // Set form data
    setData('photo', file)

    // Create preview
    const reader = new FileReader()
    reader.onload = (e) => {
      photoPreview.value = e.target.result
    }
    reader.readAsDataURL(file)
  }
}

const generateQrCode = () => {
  // In a real app, you'd use a QR code library like qrcode
  // For demo, using a placeholder

  // Mock QR code generation
  generatedQrCode.value = `
    <div style="width: 128px; height: 128px; background: #000; position: relative;">
      <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); color: white; font-size: 8px; text-align: center;">
        QR CODE<br/>${employeeCode.value}
      </div>
    </div>
  `
}

const handleSubmit = () => {
  // Set employee code before submitting
  form.employee_code = employeeCode.value

  post(route('employees.store'), {
    preserveScroll: true,
    onSuccess: () => {
      // Redirect handled by backend
    },
    onError: (errors) => {
      // Find first step with errors and go there
      if (errors.first_name || errors.last_name || errors.tc_number || errors.birth_date || errors.gender || errors.phone || errors.email || errors.address) {
        currentStep.value = 1
      } else if (errors.position || errors.category || errors.start_date || errors.manager_id || errors.current_project_id) {
        currentStep.value = 2
      } else if (errors.wage_type || errors.daily_wage || errors.hourly_wage || errors.monthly_salary) {
        currentStep.value = 3
      } else if (errors.photo) {
        currentStep.value = 4
      }
    }
  })
}

// Watch for name changes to update QR code
watch([() => form.first_name, () => form.last_name], () => {
  if (currentStep.value === 4) {
    generateQrCode()
  }
})
</script>

<style scoped>
/* QR Code styling */
.qr-code {
  display: inline-block;
  font-family: monospace;
}
</style>
