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
                <p class="text-indigo-100 text-sm mt-1">Çalışan bilgilerini girin</p>
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
      <form @submit.prevent="handleSubmit" class="space-y-6">
        <!-- Kişisel Bilgiler -->
        <Card>
          <div class="p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
              <UserIcon class="w-5 h-5 mr-2 text-indigo-600" />
              Kişisel Bilgiler
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
              <Input v-model="form.first_name" label="Ad" required :error="errors.first_name" />
              <Input v-model="form.last_name" label="Soyad" required :error="errors.last_name" />
              <Input v-model="form.tc_number" label="TC Kimlik No" type="text" maxlength="11" required :error="errors.tc_number" />
              <Input v-model="form.birth_date" label="Doğum Tarihi" type="date" required :error="errors.birth_date" />
              <Select v-model="form.gender" label="Cinsiyet" :options="[{value:'male',label:'Erkek'},{value:'female',label:'Kadın'}]" :error="errors.gender" />
              <Input v-model="form.phone" label="Telefon" type="tel" :error="errors.phone" />
              <Input v-model="form.email" label="E-posta" type="email" :error="errors.email" />
              <div class="md:col-span-2 lg:col-span-3">
                <Input v-model="form.address" label="Adres" :error="errors.address" />
              </div>
            </div>
          </div>
        </Card>

        <!-- İş Bilgileri -->
        <Card>
          <div class="p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
              <BriefcaseIcon class="w-5 h-5 mr-2 text-indigo-600" />
              İş Bilgileri
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
              <Input v-model="form.position" label="Pozisyon" required :error="errors.position" />
              <Select v-model="form.category" label="Kategori" required :options="categoryOptions" :error="errors.category" />
              <Input v-model="form.start_date" label="İşe Başlama Tarihi" type="date" required :error="errors.start_date" />
              <div class="relative z-20">
                <Select v-model="form.manager_id" label="Yönetici" :options="managerOptions" :error="errors.manager_id" searchable placeholder="Yönetici seçin" />
              </div>
              <div class="relative z-10">
                <Select v-model="form.current_project_id" label="Mevcut Proje" :options="projectOptions" :error="errors.current_project_id" searchable placeholder="Proje seçin" />
              </div>
            </div>
          </div>
        </Card>

        <!-- Maaş Bilgileri -->
        <Card>
          <div class="p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
              <CurrencyDollarIcon class="w-5 h-5 mr-2 text-indigo-600" />
              Maaş Bilgileri
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
              <Select v-model="form.wage_type" label="Ücret Türü" required :options="wageTypeOptions" :error="errors.wage_type" @update:model-value="resetWageFields" />
              <Input v-model="form.daily_wage" label="Günlük Ücret (₺)" type="number" step="0.01" min="0" :disabled="form.wage_type !== 'daily'" :error="errors.daily_wage" />
              <Input v-model="form.hourly_wage" label="Saatlik Ücret (₺)" type="number" step="0.01" min="0" :disabled="form.wage_type !== 'hourly'" :error="errors.hourly_wage" />
              <Input v-model="form.monthly_salary" label="Aylık Maaş (₺)" type="number" step="0.01" min="0" :disabled="form.wage_type !== 'monthly'" :error="errors.monthly_salary" />
            </div>
          </div>
        </Card>

        <!-- Form Actions -->
        <div class="flex justify-end space-x-3">
          <Button type="button" variant="secondary" @click="$inertia.visit(route('employees.index'))">
            İptal
          </Button>
          <Button type="submit" :disabled="processing">
            <span v-if="processing">Kaydediliyor...</span>
            <span v-else>Çalışan Oluştur</span>
          </Button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'
import { Link } from '@inertiajs/vue3'
import { computed } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import Button from '@/Components/UI/Button.vue'
import Input from '@/Components/UI/Input.vue'
import Select from '@/Components/UI/Select.vue'
import Card from '@/Components/UI/Card.vue'
import { UserIcon, UserPlusIcon, BriefcaseIcon, CurrencyDollarIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  projects: {
    type: Array,
    default: () => []
  },
  managers: {
    type: Array,
    default: () => []
  }
})

const form = useForm({
  employee_code: '',
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
  wage_type: '',
  daily_wage: '',
  hourly_wage: '',
  monthly_salary: ''
})

const { processing, errors } = form

const categoryOptions = [
  { value: 'worker', label: 'İşçi' },
  { value: 'foreman', label: 'Ustabaşı' },
  { value: 'technician', label: 'Tekniker' },
  { value: 'engineer', label: 'Mühendis' },
  { value: 'manager', label: 'Yönetici' }
]

const wageTypeOptions = [
  { value: 'daily', label: 'Günlük' },
  { value: 'hourly', label: 'Saatlik' },
  { value: 'monthly', label: 'Aylık' }
]

const managerOptions = computed(() => {
  if (!props.managers || !Array.isArray(props.managers) || props.managers.length === 0) {
    return []
  }
  return props.managers.map(m => {
    const label = m.name || (m.first_name && m.last_name ? `${m.first_name} ${m.last_name}` : 'İsimsiz')
    return {
      value: m.id,
      label: label.trim()
    }
  })
})

const projectOptions = computed(() => {
  if (!props.projects || !Array.isArray(props.projects)) return []
  return props.projects.map(p => ({ value: p.id, label: p.name }))
})

const resetWageFields = () => {
  form.daily_wage = ''
  form.hourly_wage = ''
  form.monthly_salary = ''
}

const handleSubmit = () => {
  // Generate employee code
  if (form.first_name && form.last_name) {
    const date = new Date()
    const year = date.getFullYear()
    const month = String(date.getMonth() + 1).padStart(2, '0')
    const initials = (form.first_name.charAt(0) + form.last_name.charAt(0)).toUpperCase()
    const timestamp = String(Date.now()).slice(-4)
    form.employee_code = `EMP${year}${month}${initials}${timestamp}`
  }

  form.post(route('employees.store'), {
    preserveScroll: true,
    onSuccess: () => {
      console.log('Employee created successfully')
    },
    onError: (errors) => {
      console.error('Validation errors:', errors)
    }
  })
}
</script>