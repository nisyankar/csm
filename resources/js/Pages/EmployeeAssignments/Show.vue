<template>
  <AppLayout title="Atama Detayı" :full-width="true">
    <template #header>
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Personel Atama Detayı</h1>
          <p class="mt-1 text-sm text-gray-500">
            {{ assignment.employee.first_name }} {{ assignment.employee.last_name }} - {{ assignment.project.name }}
          </p>
        </div>
        <div class="flex space-x-3">
          <Button
            v-if="assignment.status === 'active'"
            variant="outline"
            size="sm"
            @click="router.visit(`/employee-assignments/${assignment.id}/edit`)"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
            Düzenle
          </Button>
          <Button
            variant="outline"
            size="sm"
            @click="router.visit('/employee-assignments')"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Geri Dön
          </Button>
        </div>
      </div>
    </template>

    <div class="space-y-6">
      <!-- Status Banner -->
      <div
        :class="[
          'rounded-lg p-4 border-l-4',
          assignment.status === 'active' ? 'bg-green-50 border-green-500' :
          assignment.status === 'completed' ? 'bg-blue-50 border-blue-500' :
          'bg-red-50 border-red-500'
        ]"
      >
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <svg
              v-if="assignment.status === 'active'"
              class="h-6 w-6 text-green-600"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <svg
              v-else-if="assignment.status === 'completed'"
              class="h-6 w-6 text-blue-600"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            <svg
              v-else
              class="h-6 w-6 text-red-600"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </div>
          <div class="ml-3">
            <p
              :class="[
                'text-sm font-medium',
                assignment.status === 'active' ? 'text-green-800' :
                assignment.status === 'completed' ? 'text-blue-800' :
                'text-red-800'
              ]"
            >
              Atama Durumu: {{ getStatusLabel(assignment.status) }}
            </p>
            <p
              :class="[
                'mt-1 text-sm',
                assignment.status === 'active' ? 'text-green-700' :
                assignment.status === 'completed' ? 'text-blue-700' :
                'text-red-700'
              ]"
            >
              {{ getStatusDescription(assignment.status) }}
            </p>
          </div>
        </div>
      </div>

      <!-- Main Information Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Employee Information -->
        <Card>
          <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-medium text-gray-900 flex items-center">
              <svg class="w-5 h-5 mr-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
              </svg>
              Personel Bilgileri
            </h3>
          </div>
          <div class="px-6 py-4 space-y-4">
            <div class="flex items-center">
              <div class="flex-shrink-0 h-16 w-16">
                <div class="h-16 w-16 rounded-full bg-blue-100 flex items-center justify-center">
                  <span class="text-xl font-medium text-blue-600">
                    {{ assignment.employee.first_name.charAt(0) }}{{ assignment.employee.last_name.charAt(0) }}
                  </span>
                </div>
              </div>
              <div class="ml-4">
                <p class="text-lg font-medium text-gray-900">
                  {{ assignment.employee.first_name }} {{ assignment.employee.last_name }}
                </p>
                <p v-if="assignment.employee.employee_code" class="text-sm text-gray-500">
                  Personel Kodu: {{ assignment.employee.employee_code }}
                </p>
              </div>
            </div>

            <div class="border-t border-gray-200 pt-4 space-y-3">
              <div v-if="assignment.role_in_project">
                <p class="text-sm font-medium text-gray-500">Projedeki Rolü</p>
                <p class="mt-1 text-sm text-gray-900">{{ assignment.role_in_project }}</p>
              </div>
              <div>
                <p class="text-sm font-medium text-gray-500">Ana Proje</p>
                <p class="mt-1">
                  <Badge :variant="assignment.is_primary ? 'success' : 'secondary'">
                    {{ assignment.is_primary ? 'Evet' : 'Hayır' }}
                  </Badge>
                </p>
              </div>
            </div>
          </div>
        </Card>

        <!-- Project Information -->
        <Card>
          <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-medium text-gray-900 flex items-center">
              <svg class="w-5 h-5 mr-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
              </svg>
              Proje Bilgileri
            </h3>
          </div>
          <div class="px-6 py-4 space-y-4">
            <div>
              <p class="text-sm font-medium text-gray-500">Proje Adı</p>
              <p class="mt-1 text-lg font-medium text-gray-900">{{ assignment.project.name }}</p>
            </div>

            <div v-if="assignment.project.status" class="pt-4 border-t border-gray-200">
              <p class="text-sm font-medium text-gray-500">Proje Durumu</p>
              <p class="mt-1">
                <Badge>{{ getProjectStatusLabel(assignment.project.status) }}</Badge>
              </p>
            </div>

            <div class="pt-4 border-t border-gray-200">
              <Button
                variant="outline"
                size="sm"
                @click="router.visit(`/projects/${assignment.project.id}`)"
              >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                </svg>
                Proje Detayına Git
              </Button>
            </div>
          </div>
        </Card>
      </div>

      <!-- Timeline Information -->
      <Card>
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
          <h3 class="text-lg font-medium text-gray-900 flex items-center">
            <svg class="w-5 h-5 mr-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            Zaman Çizelgesi
          </h3>
        </div>
        <div class="px-6 py-4">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
              <p class="text-sm font-medium text-gray-500">Başlangıç Tarihi</p>
              <p class="mt-1 text-lg font-medium text-gray-900">{{ formatDate(assignment.start_date) }}</p>
            </div>
            <div>
              <p class="text-sm font-medium text-gray-500">Bitiş Tarihi</p>
              <p class="mt-1 text-lg font-medium text-gray-900">
                {{ assignment.end_date ? formatDate(assignment.end_date) : 'Süresiz' }}
              </p>
            </div>
            <div>
              <p class="text-sm font-medium text-gray-500">Atama Süresi</p>
              <p class="mt-1 text-lg font-medium text-gray-900">{{ calculateDuration() }}</p>
            </div>
          </div>

          <!-- Progress Bar (if active and has end date) -->
          <div v-if="assignment.status === 'active' && assignment.end_date" class="mt-6 pt-6 border-t border-gray-200">
            <div class="flex items-center justify-between mb-2">
              <p class="text-sm font-medium text-gray-700">İlerleme</p>
              <p class="text-sm text-gray-500">{{ calculateProgress() }}%</p>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
              <div
                :class="[
                  'h-2 rounded-full transition-all duration-500',
                  calculateProgress() > 90 ? 'bg-red-600' :
                  calculateProgress() > 70 ? 'bg-yellow-600' :
                  'bg-green-600'
                ]"
                :style="{ width: calculateProgress() + '%' }"
              ></div>
            </div>
            <p class="mt-2 text-xs text-gray-500">
              {{ calculateRemainingDays() }} gün kaldı
            </p>
          </div>
        </div>
      </Card>

      <!-- Notes -->
      <Card v-if="assignment.notes">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
          <h3 class="text-lg font-medium text-gray-900 flex items-center">
            <svg class="w-5 h-5 mr-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
            </svg>
            Notlar
          </h3>
        </div>
        <div class="px-6 py-4">
          <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ assignment.notes }}</p>
        </div>
      </Card>

      <!-- System Information -->
      <Card>
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
          <h3 class="text-lg font-medium text-gray-900 flex items-center">
            <svg class="w-5 h-5 mr-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Sistem Bilgileri
          </h3>
        </div>
        <div class="px-6 py-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div>
              <p class="text-gray-500">Oluşturulma Tarihi</p>
              <p class="mt-1 text-gray-900">{{ formatDateTime(assignment.created_at) }}</p>
            </div>
            <div>
              <p class="text-gray-500">Son Güncelleme</p>
              <p class="mt-1 text-gray-900">{{ formatDateTime(assignment.updated_at) }}</p>
            </div>
          </div>
        </div>
      </Card>

      <!-- Actions -->
      <Card v-if="assignment.status === 'active'">
        <div class="px-6 py-4">
          <h3 class="text-lg font-medium text-gray-900 mb-4">İşlemler</h3>
          <div class="flex flex-wrap gap-3">
            <Button
              variant="success"
              @click="completeAssignment"
            >
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              Atamayı Tamamla
            </Button>
            <Button
              variant="danger"
              @click="cancelAssignment"
            >
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
              Atamayı İptal Et
            </Button>
          </div>
        </div>
      </Card>
    </div>
  </AppLayout>
</template>

<script setup>
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Card from '@/Components/UI/Card.vue'
import Button from '@/Components/UI/Button.vue'
import Badge from '@/Components/UI/Badge.vue'
import { format, parseISO, differenceInDays, differenceInCalendarDays } from 'date-fns'
import { tr } from 'date-fns/locale'

const props = defineProps({
  assignment: {
    type: Object,
    required: true
  }
})


const formatDate = (date) => {
  if (!date) return '-'
  try {
    return format(parseISO(date), 'dd MMMM yyyy', { locale: tr })
  } catch {
    return date
  }
}

const formatDateTime = (date) => {
  if (!date) return '-'
  try {
    return format(parseISO(date), 'dd MMMM yyyy HH:mm', { locale: tr })
  } catch {
    return date
  }
}

const calculateDuration = () => {
  if (!props.assignment.start_date) return '-'

  const start = parseISO(props.assignment.start_date)
  const end = props.assignment.end_date ? parseISO(props.assignment.end_date) : new Date()

  const days = differenceInDays(end, start)

  if (days < 30) {
    return `${days} gün`
  } else if (days < 365) {
    const months = Math.floor(days / 30)
    return `${months} ay`
  } else {
    const years = Math.floor(days / 365)
    const months = Math.floor((days % 365) / 30)
    return `${years} yıl ${months} ay`
  }
}

const calculateProgress = () => {
  if (!props.assignment.start_date || !props.assignment.end_date) return 0

  const start = parseISO(props.assignment.start_date)
  const end = parseISO(props.assignment.end_date)
  const now = new Date()

  const total = differenceInDays(end, start)
  const elapsed = differenceInDays(now, start)

  return Math.min(Math.round((elapsed / total) * 100), 100)
}

const calculateRemainingDays = () => {
  if (!props.assignment.end_date) return 0

  const end = parseISO(props.assignment.end_date)
  const now = new Date()

  return Math.max(differenceInCalendarDays(end, now), 0)
}

const getStatusLabel = (status) => {
  const labels = {
    active: 'Aktif',
    completed: 'Tamamlandı',
    cancelled: 'İptal Edildi'
  }
  return labels[status] || status
}

const getStatusDescription = (status) => {
  const descriptions = {
    active: 'Bu atama şu anda aktif durumda',
    completed: 'Bu atama tamamlanmış durumda',
    cancelled: 'Bu atama iptal edilmiş durumda'
  }
  return descriptions[status] || ''
}

const getProjectStatusLabel = (status) => {
  const labels = {
    planning: 'Planlama',
    active: 'Aktif',
    on_hold: 'Beklemede',
    completed: 'Tamamlandı',
    cancelled: 'İptal Edildi'
  }
  return labels[status] || status
}

const completeAssignment = () => {
  if (confirm('Bu atamayı tamamlamak istediğinize emin misiniz?')) {
    router.post(route('employee-assignments.complete', props.assignment.id), {}, {
      preserveScroll: true
    })
  }
}

const cancelAssignment = () => {
  if (confirm('Bu atamayı iptal etmek istediğinize emin misiniz? Bu işlem geri alınamaz.')) {
    router.delete(route('employee-assignments.destroy', props.assignment.id), {
      onSuccess: () => {
        router.visit(route('employee-assignments.index'))
      }
    })
  }
}
</script>
