<template>
  <AppLayout title="Yeni Personel Ataması" :breadcrumbs="breadcrumbs">
    <template #header>
      <h1 class="text-2xl font-bold text-gray-900">Yeni Personel Ataması</h1>
      <p class="mt-1 text-sm text-gray-500">
        Personeli projeye atayın
      </p>
    </template>

    <Card>
      <form @submit.prevent="submit">
        <div class="space-y-6">
          <!-- Employee Selection -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Personel <span class="text-red-500">*</span>
            </label>
            <Select
              v-model="form.employee_id"
              :options="employeeOptions"
              placeholder="Personel seçin"
              required
              :error="form.errors.employee_id"
            />
            <p v-if="form.errors.employee_id" class="mt-1 text-sm text-red-600">
              {{ form.errors.employee_id }}
            </p>
          </div>

          <!-- Project Selection -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Proje <span class="text-red-500">*</span>
            </label>
            <Select
              v-model="form.project_id"
              :options="projectOptions"
              placeholder="Proje seçin"
              required
              :error="form.errors.project_id"
            />
            <p v-if="form.errors.project_id" class="mt-1 text-sm text-red-600">
              {{ form.errors.project_id }}
            </p>
          </div>

          <!-- Primary Project Checkbox -->
          <div class="flex items-center">
            <input
              id="is_primary"
              v-model="form.is_primary"
              type="checkbox"
              class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
            />
            <label for="is_primary" class="ml-2 block text-sm text-gray-900">
              Ana Proje
              <span class="text-gray-500 text-xs block">
                (Her personelin sadece bir ana projesi olabilir)
              </span>
            </label>
          </div>

          <!-- Date Range -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Başlangıç Tarihi <span class="text-red-500">*</span>
              </label>
              <Input
                v-model="form.start_date"
                type="date"
                required
                :error="form.errors.start_date"
              />
              <p v-if="form.errors.start_date" class="mt-1 text-sm text-red-600">
                {{ form.errors.start_date }}
              </p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Bitiş Tarihi
                <span class="text-gray-500 text-xs">(Opsiyonel)</span>
              </label>
              <Input
                v-model="form.end_date"
                type="date"
                :min="form.start_date"
                :error="form.errors.end_date"
              />
              <p v-if="form.errors.end_date" class="mt-1 text-sm text-red-600">
                {{ form.errors.end_date }}
              </p>
            </div>
          </div>

          <!-- Role in Project -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Projedeki Rolü
            </label>
            <Input
              v-model="form.role_in_project"
              type="text"
              placeholder="Örn: İnşaat Ustası, Elektrik Teknisyeni"
              :error="form.errors.role_in_project"
            />
            <p v-if="form.errors.role_in_project" class="mt-1 text-sm text-red-600">
              {{ form.errors.role_in_project }}
            </p>
          </div>

          <!-- Notes -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Notlar
            </label>
            <textarea
              v-model="form.notes"
              rows="4"
              class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              placeholder="Atama hakkında notlar..."
            ></textarea>
            <p v-if="form.errors.notes" class="mt-1 text-sm text-red-600">
              {{ form.errors.notes }}
            </p>
          </div>

          <!-- Info Box -->
          <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex">
              <svg class="h-5 w-5 text-blue-600 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
              </svg>
              <div class="text-sm text-blue-700">
                <p class="font-medium mb-1">Bilgi:</p>
                <ul class="list-disc list-inside space-y-1">
                  <li>Bir personel birden fazla projede görev alabilir</li>
                  <li>Ana proje işaretlenirse, diğer aktif ana proje atamaları otomatik kaldırılır</li>
                  <li>Bitiş tarihi belirtilmezse atama süresiz olarak devam eder</li>
                </ul>
              </div>
            </div>
          </div>
        </div>

        <!-- Form Actions -->
        <div class="mt-6 flex justify-end space-x-3 border-t border-gray-200 pt-6">
          <Button
            type="button"
            variant="outline"
            @click="router.visit(route('employee-assignments.index'))"
            :disabled="form.processing"
          >
            İptal
          </Button>
          <Button
            type="submit"
            variant="primary"
            :disabled="form.processing"
          >
            <Spinner v-if="form.processing" class="w-4 h-4 mr-2" />
            {{ form.processing ? 'Kaydediliyor...' : 'Kaydet' }}
          </Button>
        </div>
      </form>
    </Card>
  </AppLayout>
</template>

<script setup>
import { reactive, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Card from '@/Components/UI/Card.vue'
import Button from '@/Components/UI/Button.vue'
import Input from '@/Components/UI/Input.vue'
import Select from '@/Components/UI/Select.vue'
import Spinner from '@/Components/UI/Spinner.vue'
import { format } from 'date-fns'

const props = defineProps({
  employees: {
    type: Array,
    required: true
  },
  projects: {
    type: Array,
    required: true
  }
})

const form = reactive({
  employee_id: '',
  project_id: '',
  is_primary: false,
  start_date: format(new Date(), 'yyyy-MM-dd'),
  end_date: '',
  role_in_project: '',
  notes: '',
  processing: false,
  errors: {}
})

const breadcrumbs = computed(() => [
  { label: 'Ana Sayfa', url: route('dashboard') },
  { label: 'Personel Atamaları', url: route('employee-assignments.index') },
  { label: 'Yeni Atama', url: null }
])

const employeeOptions = computed(() => [
  { value: '', label: 'Personel seçin' },
  ...props.employees.map(emp => ({
    value: emp.id,
    label: `${emp.first_name} ${emp.last_name}${emp.employee_code ? ' (' + emp.employee_code + ')' : ''}`
  }))
])

const projectOptions = computed(() => [
  { value: '', label: 'Proje seçin' },
  ...props.projects.map(proj => ({
    value: proj.id,
    label: proj.name
  }))
])

const submit = () => {
  form.processing = true
  form.errors = {}

  router.post(route('employee-assignments.store'), {
    employee_id: form.employee_id,
    project_id: form.project_id,
    is_primary: form.is_primary,
    start_date: form.start_date,
    end_date: form.end_date || null,
    role_in_project: form.role_in_project || null,
    notes: form.notes || null,
  }, {
    onError: (errors) => {
      form.errors = errors
      form.processing = false
    },
    onFinish: () => {
      form.processing = false
    }
  })
}
</script>
