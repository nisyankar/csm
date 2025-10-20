<template>
  <AppLayout
    :breadcrumbs="breadcrumbs"
    title="Yeni Çalışan Ekle - SPT İnşaat Puantaj Sistemi"
  >
    <template #header>
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Yeni Çalışan Ekle</h1>
          <p class="mt-1 text-sm text-gray-600">
            Adım {{ currentStep }} / {{ totalSteps }} - {{ stepTitles[currentStep - 1] }}
          </p>
        </div>
        <Button
          variant="outline"
          :href="route('employees.index')"
          :left-icon="ArrowLeftIcon"
        >
          Geri Dön
        </Button>
      </div>
    </template>

    <div class="max-w-4xl mx-auto">
      <!-- Progress Steps -->
      <div class="mb-8">
        <nav class="flex items-center justify-center">
          <ol class="flex items-center w-full max-w-2xl">
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
                  'flex items-center justify-center w-10 h-10 rounded-full text-sm font-medium transition-colors',
                  currentStep > index + 1
                    ? 'bg-green-600 text-white'
                    : currentStep === index + 1
                    ? 'bg-blue-600 text-white'
                    : 'bg-gray-200 text-gray-500'
                ]"
              >
                <svg
                  v-if="currentStep > index + 1"
                  class="w-5 h-5"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke-width="1.5"
                  stroke="currentColor"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                </svg>
                <span v-else>{{ index + 1 }}</span>
              </div>

              <!-- Step Title -->
              <div class="ml-3 hidden sm:block">
                <p :class="[
                  'text-sm font-medium',
                  currentStep > index + 1
                    ? 'text-green-600'
                    : currentStep === index + 1
                    ? 'text-blue-600'
                    : 'text-gray-500'
                ]">
                  {{ step.title }}
                </p>
              </div>

              <!-- Connector Line -->
              <div
                v-if="index < steps.length - 1"
                :class="[
                  'hidden sm:block flex-auto h-0.5 ml-4',
                  currentStep > index + 1 ? 'bg-green-600' : 'bg-gray-200'
                ]"
              ></div>
            </li>
          </ol>
        </nav>
      </div>

      <!-- Form Container -->
      <Card class="p-6">
        <form @submit.prevent="handleSubmit">
          <!-- Step 1: Kişisel Bilgiler -->
          <div v-if="currentStep === 1" class="space-y-6">
            <div class="text-center pb-6 border-b border-gray-200">
              <h2 class="text-lg font-semibold text-gray-900">Kişisel Bilgiler</h2>
              <p class="text-sm text-gray-600 mt-1">Çalışanın temel kişisel bilgilerini girin</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
                pattern="[0-9]{11}"
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
            </div>

            <!-- Adres -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Adres</label>
              <textarea
                v-model="form.address"
                rows="3"
                class="block w-full rounded-md border-0 py-2 px-3 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-500 text-gray-900 text-sm"
                placeholder="Tam adres bilgisi"
                :class="errors.address ? 'ring-red-300 focus:ring-red-500' : ''"
              ></textarea>
              <p v-if="errors.address" class="text-xs text-red-600 mt-1">{{ errors.address }}</p>
            </div>
          </div>

          <!-- Step 2: İş Bilgileri -->
          <div v-if="currentStep === 2" class="space-y-6">
            <div class="text-center pb-6 border-b border-gray-200">
              <h2 class="text-lg font-semibold text-gray-900">İş Bilgileri</h2>
              <p class="text-sm text-gray-600 mt-1">Pozisyon, kategori ve çalışma detayları</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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

              <!-- Yıllık İzin Günü -->
              <Input
                v-model="form.annual_leave_days"
                label="Yıllık İzin Günü"
                type="number"
                min="14"
                max="30"
                required
                :error="errors.annual_leave_days"
                help-text="14-30 gün arası"
              />
            </div>
          </div>

          <!-- Step 3: Maaş Bilgileri -->
          <div v-if="currentStep === 3" class="space-y-6">
            <div class="text-center pb-6 border-b border-gray-200">
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
                :error="errors.wage_type"
                @update:model-value="resetWageFields"
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
                :error="errors.daily_wage"
                :left-icon="CurrencyLiraIcon"
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
                :error="errors.hourly_wage"
                :left-icon="CurrencyLiraIcon"
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
                :error="errors.monthly_salary"
                :left-icon="CurrencyLiraIcon"
              />
            </div>

            <!-- Maaş Bilgi Kartı -->
            <Card v-if="form.wage_type" class="bg-blue-50 border-blue-200">
              <div class="flex items-start space-x-3">
                <div class="flex-shrink-0">
                  <svg class="h-5 w-5 text-blue-400 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                  </svg>
                </div>
                <div>
                  <h3 class="text-sm font-medium text-blue-900">Ücret Türü Bilgisi</h3>
                  <div class="mt-2 text-sm text-blue-700">
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
            </Card>
          </div>

          <!-- Step 4: Fotoğraf ve QR Kod -->
          <div v-if="currentStep === 4" class="space-y-6">
            <div class="text-center pb-6 border-b border-gray-200">
              <h2 class="text-lg font-semibold text-gray-900">Fotoğraf ve QR Kod</h2>
              <p class="text-sm text-gray-600 mt-1">Profil fotoğrafı ekleyin ve QR kod oluşturun</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
              <!-- Fotoğraf Yükleme -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-4">Profil Fotoğrafı</label>
                
                <div class="mt-2 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:border-gray-400 transition-colors">
                  <div class="space-y-1 text-center">
                    <!-- Preview Image -->
                    <div v-if="photoPreview" class="mb-4">
                      <img
                        :src="photoPreview"
                        alt="Fotoğraf önizleme"
                        class="mx-auto h-32 w-32 rounded-full object-cover"
                      />
                    </div>
                    
                    <!-- Upload Icon -->
                    <div v-else class="mx-auto h-12 w-12 text-gray-400">
                      <svg class="h-full w-full" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                      </svg>
                    </div>
                    
                    <div class="flex text-sm text-gray-600">
                      <label for="photo-upload" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
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
                
                <Card class="text-center p-6">
                  <div v-if="generatedQrCode" class="space-y-4">
                    <div class="flex justify-center">
                      <div class="bg-white p-4 rounded-lg shadow-sm border">
                        <div v-html="generatedQrCode" class="qr-code"></div>
                      </div>
                    </div>
                    <div class="text-sm text-gray-600">
                      <p class="font-medium">{{ form.first_name }} {{ form.last_name }}</p>
                      <p class="text-xs">Çalışan Kodu: {{ employeeCode }}</p>
                    </div>
                  </div>
                  
                  <div v-else class="text-gray-500">
                    <svg class="mx-auto h-12 w-12 mb-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z" />
                      <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 6.75h.75v.75h-.75v-.75zM6.75 16.5h.75v.75h-.75v-.75zM16.5 6.75h.75v.75h-.75v-.75zM13.5 13.5h4.5v4.5h-4.5v-4.5z" />
                    </svg>
                    <p class="text-sm">QR kod otomatik oluşturulacak</p>
                  </div>
                </Card>
              </div>
            </div>
          </div>

          <!-- Navigation Buttons -->
          <div class="flex items-center justify-between pt-6 border-t border-gray-200 mt-8">
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
                :disabled="!canProceedToNextStep"
              >
                Sonraki
              </Button>

              <Button
                v-else
                type="submit"
                variant="primary"
                :loading="processing"
                :left-icon="CheckIcon"
              >
                Çalışanı Oluştur
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
import { router, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Button from '@/Components/UI/Button.vue'
import Input from '@/Components/UI/Input.vue'
import Select from '@/Components/UI/Select.vue'
import Card from '@/Components/UI/Card.vue'

// Heroicons
import {
  UserIcon,
  IdentificationIcon,
  CalendarIcon,
  PhoneIcon,
  AtSymbolIcon,
  BriefcaseIcon,
  CurrencyDollarIcon as CurrencyLiraIcon,
  ArrowLeftIcon,
  ArrowRightIcon,
  CheckIcon
} from '@heroicons/vue/24/outline'

// Props
const props = defineProps({
  projects: Array,
  managers: Array
})

// Breadcrumbs
const breadcrumbs = [
  { label: 'Çalışanlar', href: route('employees.index') },
  { label: 'Yeni Çalışan Ekle' }
]

// Form state
const { data: form, setData, post, processing, errors, reset } = useForm({
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
  annual_leave_days: 14,
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
  { title: 'Maaş Bilgileri', icon: CurrencyLiraIcon },
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
const managerOptions = computed(() => 
  props.managers.map(manager => ({
    label: `${manager.first_name} ${manager.last_name}`,
    value: manager.id
  }))
)

const projectOptions = computed(() => 
  props.projects.map(project => ({
    label: project.name,
    value: project.id
  }))
)

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
  switch (currentStep.value) {
    case 1:
      return form.first_name && form.last_name && form.tc_number && form.birth_date
    case 2:
      return form.position && form.category && form.start_date && form.annual_leave_days
    case 3:
      return form.wage_type && (
        (form.wage_type === 'daily' && form.daily_wage) ||
        (form.wage_type === 'hourly' && form.hourly_wage) ||
        (form.wage_type === 'monthly' && form.monthly_salary)
      )
    case 4:
      return true
    default:
      return false
  }
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
  setData({
    ...form,
    daily_wage: '',
    hourly_wage: '',
    monthly_salary: ''
  })
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
  const qrData = JSON.stringify({
    employee_code: employeeCode.value,
    name: `${form.first_name} ${form.last_name}`,
    tc_number: form.tc_number,
    position: form.position
  })
  
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
  const submitData = { ...form }
  
  // Set employee code
  submitData.employee_code = employeeCode.value
  
  post(route('employees.store'), {
    preserveScroll: true,
    onSuccess: () => {
      // Redirect handled by backend
    },
    onError: (errors) => {
      // Find first step with errors and go there
      if (errors.first_name || errors.last_name || errors.tc_number || errors.birth_date || errors.gender || errors.phone || errors.email || errors.address) {
        currentStep.value = 1
      } else if (errors.position || errors.category || errors.start_date || errors.manager_id || errors.current_project_id || errors.annual_leave_days) {
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

// Auto-generate employee code when name changes
watch([() => form.first_name, () => form.last_name], () => {
  if (form.first_name && form.last_name) {
    // Update QR code if on last step
    if (currentStep.value === 4) {
      generateQrCode()
    }
  }
})
</script>

<style scoped>
/* QR Code styling */
.qr-code {
  display: inline-block;
  font-family: monospace;
}

/* File upload drag and drop styling */
.border-dashed {
  border-style: dashed;
}

/* Step progress styling */
.step-connector {
  background: linear-gradient(to right, transparent 50%, #e5e7eb 50%);
}

/* Form section spacing */
.form-section {
  scroll-margin-top: 2rem;
}

/* Custom scrollbar for long forms */
.form-container::-webkit-scrollbar {
  width: 6px;
}

.form-container::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 3px;
}

.form-container::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 3px;
}

.form-container::-webkit-scrollbar-thumb:hover {
  background: #a8a8a8;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .step-title {
    display: none;
  }
  
  .form-grid {
    grid-template-columns: 1fr;
  }
}

/* Animation for step transitions */
.step-transition-enter-active,
.step-transition-leave-active {
  transition: all 0.3s ease-in-out;
}

.step-transition-enter-from {
  opacity: 0;
  transform: translateX(20px);
}

.step-transition-leave-to {
  opacity: 0;
  transform: translateX(-20px);
}

/* Photo preview styling */
.photo-preview {
  position: relative;
  display: inline-block;
}

.photo-preview::after {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  border: 2px solid #3b82f6;
  border-radius: 50%;
  opacity: 0;
  transition: opacity 0.2s ease;
}

.photo-preview:hover::after {
  opacity: 1;
}

/* Success state for completed steps */
.step-completed {
  background: linear-gradient(135deg, #10b981, #059669);
  box-shadow: 0 4px 14px 0 rgba(16, 185, 129, 0.25);
}

/* Loading state for form submission */
.form-loading {
  pointer-events: none;
  opacity: 0.7;
}

/* Validation error styling */
.field-error {
  animation: shake 0.5s ease-in-out;
}

@keyframes shake {
  0%, 20%, 40%, 60%, 80%, 100% {
    transform: translateX(0);
  }
  10%, 30%, 50%, 70%, 90% {
    transform: translateX(-2px);
  }
}
</style>