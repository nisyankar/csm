<template>
  <AppLayout title="Atama Düzenle" :full-width="true">
    <template #header>
      <h1 class="text-2xl font-bold text-gray-900">Personel Atamasını Düzenle</h1>
      <p class="mt-1 text-sm text-gray-500">
        {{ assignment.employee.first_name }} {{ assignment.employee.last_name }} - {{ assignment.project.name }}
      </p>
    </template>

    <Card>
      <form @submit.prevent="submit">
        <div class="space-y-6">
          <!-- Employee Selection (Read-only) -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Personel
            </label>
            <div class="px-4 py-3 bg-gray-50 rounded-md border border-gray-300">
              <p class="text-sm font-medium text-gray-900">
                {{ assignment.employee.first_name }} {{ assignment.employee.last_name }}
                <span v-if="assignment.employee.employee_code" class="text-gray-500">
                  ({{ assignment.employee.employee_code }})
                </span>
              </p>
            </div>
            <p class="mt-1 text-xs text-gray-500">Personel değiştirilemez</p>
          </div>

          <!-- Project Selection (Read-only) -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Proje
            </label>
            <div class="px-4 py-3 bg-gray-50 rounded-md border border-gray-300">
              <p class="text-sm font-medium text-gray-900">{{ assignment.project.name }}</p>
            </div>
            <p class="mt-1 text-xs text-gray-500">Proje değiştirilemez</p>
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

          <!-- Status Info -->
          <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
            <div class="flex items-start">
              <svg class="h-5 w-5 text-gray-500 mr-3 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
              </svg>
              <div class="text-sm">
                <p class="font-medium text-gray-900 mb-1">Atama Bilgileri:</p>
                <ul class="text-gray-600 space-y-1">
                  <li>Durum: <span class="font-medium">{{ getStatusLabel(assignment.status) }}</span></li>
                  <li>Oluşturulma: {{ formatDate(assignment.created_at) }}</li>
                  <li v-if="assignment.updated_at !== assignment.created_at">
                    Son Güncelleme: {{ formatDate(assignment.updated_at) }}
                  </li>
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
            @click="router.visit('/employee-assignments')"
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
            {{ form.processing ? 'Kaydediliyor...' : 'Değişiklikleri Kaydet' }}
          </Button>
        </div>
      </form>
    </Card>
  </AppLayout>
</template>

<script setup>
import { reactive } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Card from '@/Components/UI/Card.vue'
import Button from '@/Components/UI/Button.vue'
import Input from '@/Components/UI/Input.vue'
import Spinner from '@/Components/UI/Spinner.vue'
import { format, parseISO } from 'date-fns'
import { tr } from 'date-fns/locale'

const props = defineProps({
  assignment: {
    type: Object,
    required: true
  },
  employees: {
    type: Array,
    default: () => []
  },
  projects: {
    type: Array,
    default: () => []
  }
})

const form = reactive({
  employee_id: props.assignment.employee_id,
  project_id: props.assignment.project_id,
  is_primary: props.assignment.is_primary || false,
  start_date: props.assignment.start_date || '',
  end_date: props.assignment.end_date || '',
  role_in_project: props.assignment.role_in_project || '',
  notes: props.assignment.notes || '',
  processing: false,
  errors: {}
})


const formatDate = (date) => {
  if (!date) return '-'
  try {
    return format(parseISO(date), 'dd MMMM yyyy HH:mm', { locale: tr })
  } catch {
    return date
  }
}

const getStatusLabel = (status) => {
  const labels = {
    active: 'Aktif',
    completed: 'Tamamlandı',
    cancelled: 'İptal Edildi'
  }
  return labels[status] || status
}

const submit = () => {
  form.processing = true
  form.errors = {}

  router.patch(`/employee-assignments/${props.assignment.id}`, {
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
