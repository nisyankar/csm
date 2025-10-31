<template>
  <AppLayout title="Geçici Görevlendirme Detayı" :full-width="true">
    <!-- Full-width header -->
    <template #fullWidthHeader>
      <div class="bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 border-b border-indigo-900/20 w-full">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
          <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <!-- Header Info -->
            <div class="flex-1 min-w-0">
              <div class="flex items-center space-x-4 mb-3">
                <div class="flex-shrink-0 w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm">
                  <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                  </svg>
                </div>
                <div>
                  <h1 class="text-2xl lg:text-3xl font-bold text-white">Görevlendirme Detayı</h1>
                  <p class="text-indigo-100 text-sm mt-1">
                    {{ assignment?.employee?.first_name || '' }} {{ assignment?.employee?.last_name || '' }}
                  </p>
                </div>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex-shrink-0 flex flex-wrap gap-2">
              <Link
                v-if="canEdit"
                :href="route('temporary-assignments.edit', assignment?.id)"
                class="inline-flex items-center px-4 py-2 bg-white/20 text-white text-sm font-medium rounded-lg hover:bg-white/30 transition-all duration-200 backdrop-blur-sm"
              >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Düzenle
              </Link>
              <Link
                :href="route('temporary-assignments.index')"
                class="inline-flex items-center px-4 py-2 bg-white text-indigo-600 text-sm font-medium rounded-lg hover:bg-indigo-50 shadow-lg hover:shadow-xl transition-all duration-200"
              >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Geri Dön
              </Link>
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
                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                  </svg>
                  <Link :href="route('temporary-assignments.index')" class="text-indigo-100 hover:text-white text-sm">Geçici Görevlendirmeler</Link>
                </li>
                <li class="flex items-center">
                  <svg class="h-3 w-3 text-indigo-200 mx-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                  </svg>
                  <span class="text-xs font-medium text-white">Detay</span>
                </li>
              </ol>
            </div>
          </div>
        </div>
      </div>
    </template>

    <!-- Content -->
    <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
      <!-- Status Banner -->
      <div
        class="mb-6 rounded-lg p-4 border-l-4"
        :class="{
          'bg-green-50 border-green-500': assignment?.status === 'active',
          'bg-yellow-50 border-yellow-500': assignment?.status === 'pending',
          'bg-blue-50 border-blue-500': assignment?.status === 'completed',
          'bg-red-50 border-red-500': assignment?.status === 'cancelled'
        }"
      >
        <div class="flex items-center">
          <svg
            :class="{
              'text-green-600': assignment?.status === 'active',
              'text-yellow-600': assignment?.status === 'pending',
              'text-blue-600': assignment?.status === 'completed',
              'text-red-600': assignment?.status === 'cancelled'
            }"
            class="h-6 w-6 mr-3 flex-shrink-0"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              :d="getStatusIcon(assignment?.status)"
            />
          </svg>
          <div>
            <p
              :class="{
                'text-green-800': assignment?.status === 'active',
                'text-yellow-800': assignment?.status === 'pending',
                'text-blue-800': assignment?.status === 'completed',
                'text-red-800': assignment?.status === 'cancelled'
              }"
              class="font-semibold text-sm"
            >
              Durum: {{ assignment?.status_label || 'Bilinmiyor' }}
            </p>
          </div>
        </div>
      </div>

      <div class="space-y-6">
        <!-- Employee Card -->
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 bg-gradient-to-r from-indigo-50 to-purple-50 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
              <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
              </svg>
              Çalışan Bilgileri
            </h3>
          </div>
          <div class="px-6 py-4">
            <div class="flex items-center justify-between">
              <div class="flex items-center space-x-4">
                <div class="h-16 w-16 rounded-full bg-indigo-100 flex items-center justify-center">
                  <span class="text-xl font-semibold text-indigo-700">
                    {{ getInitials(assignment?.employee?.first_name, assignment?.employee?.last_name) }}
                  </span>
                </div>
                <div>
                  <p class="text-lg font-semibold text-gray-900">
                    {{ assignment?.employee?.first_name || '' }} {{ assignment?.employee?.last_name || '' }}
                  </p>
                  <p class="text-sm text-gray-600">{{ assignment?.employee?.employee_code || 'N/A' }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Project Transfer Card -->
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 bg-gradient-to-r from-purple-50 to-pink-50 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
              <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
              </svg>
              Proje Transferi
            </h3>
          </div>
          <div class="px-6 py-4">
            <div class="flex flex-col md:flex-row items-center justify-between gap-4">
              <div class="flex-1 text-center">
                <div class="inline-block px-4 py-3 bg-blue-100 text-blue-700 rounded-lg font-semibold text-sm mb-2">
                  {{ assignment?.from_project?.name || 'Bilinmiyor' }}
                </div>
                <p class="text-xs text-gray-600">Ana Proje</p>
              </div>
              <svg class="w-6 h-6 text-gray-400 md:block hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
              </svg>
              <svg class="w-6 h-6 text-gray-400 md:hidden block rotate-90" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
              </svg>
              <div class="flex-1 text-center">
                <div class="inline-block px-4 py-3 bg-green-100 text-green-700 rounded-lg font-semibold text-sm mb-2">
                  {{ assignment?.to_project?.name || 'Bilinmiyor' }}
                </div>
                <p class="text-xs text-gray-600">Hedef Proje</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Shift Information Card -->
        <div v-if="assignment?.preferred_shift" class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 bg-gradient-to-r from-indigo-50 to-purple-50 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
              <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              Vardiya Bilgileri
            </h3>
          </div>
          <div class="px-6 py-4">
            <div class="flex items-center space-x-4 p-4 bg-indigo-50 border border-indigo-200 rounded-lg">
              <div class="flex-shrink-0 h-12 w-12 bg-indigo-100 rounded-full flex items-center justify-center">
                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <div class="flex-1">
                <p class="text-sm font-semibold text-indigo-900">{{ assignment?.preferred_shift?.name || 'Bilinmiyor' }}</p>
                <p class="text-xs text-indigo-700 mt-1">
                  {{ assignment?.preferred_shift?.code || 'N/A' }} • {{ assignment?.preferred_shift?.daily_hours || 0 }} saat/gün
                </p>
              </div>
            </div>
            <p class="mt-3 text-xs text-gray-600">
              Çalışan geçici görevlendirme sırasında bu vardiya ile puantaja girilecektir.
            </p>
          </div>
        </div>

        <!-- Date and Duration Card -->
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 bg-gradient-to-r from-pink-50 to-indigo-50 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
              <svg class="w-5 h-5 mr-2 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
              Tarihler ve Süre
            </h3>
          </div>
          <div class="px-6 py-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
              <div>
                <p class="text-sm font-medium text-gray-500 mb-2">Başlangıç Tarihi</p>
                <p class="text-lg font-semibold text-gray-900">{{ formatDate(assignment?.start_date) }}</p>
              </div>
              <div>
                <p class="text-sm font-medium text-gray-500 mb-2">Bitiş Tarihi</p>
                <p class="text-lg font-semibold text-gray-900">{{ formatDate(assignment?.end_date) }}</p>
              </div>
              <div>
                <p class="text-sm font-medium text-gray-500 mb-2">Toplam Süre</p>
                <p class="text-lg font-semibold text-gray-900">{{ assignment?.duration_days || 0 }} gün</p>
              </div>
            </div>

            <!-- Progress Bar for Active Assignments -->
            <div v-if="assignment?.status === 'active'" class="mt-6 pt-6 border-t border-gray-200">
              <div class="flex items-center justify-between mb-2">
                <p class="text-sm font-medium text-gray-700">İlerleme</p>
                <p class="text-sm font-semibold text-gray-900">{{ calculateProgress() }}%</p>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-2.5">
                <div
                  :class="[
                    'h-2.5 rounded-full transition-all duration-500',
                    calculateProgress() > 90 ? 'bg-red-600' :
                    calculateProgress() > 70 ? 'bg-yellow-600' :
                    'bg-green-600'
                  ]"
                  :style="{ width: calculateProgress() + '%' }"
                ></div>
              </div>
              <p class="mt-2 text-xs text-gray-600">
                {{ getRemainingDays() }} gün kaldı
              </p>
            </div>
          </div>
        </div>

        <!-- Reason Card -->
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 bg-gradient-to-r from-indigo-50 to-purple-50 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
              <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              Görevlendirme Nedeni
            </h3>
          </div>
          <div class="px-6 py-4">
            <p class="text-sm text-gray-700">{{ assignment?.reason || 'Belirtilmedi' }}</p>
          </div>
        </div>

        <!-- Notes Card (if exists) -->
        <div v-if="assignment?.notes" class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 bg-gradient-to-r from-purple-50 to-pink-50 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
              <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
              </svg>
              Notlar
            </h3>
          </div>
          <div class="px-6 py-4">
            <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ assignment?.notes }}</p>
          </div>
        </div>

        <!-- Approval Information Card -->
        <div v-if="assignment?.approved_at || assignment?.approved_by" class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 bg-gradient-to-r from-green-50 to-emerald-50 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
              <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              Onay Bilgileri
            </h3>
          </div>
          <div class="px-6 py-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <p class="text-sm font-medium text-gray-500 mb-2">Onaylayan</p>
                <p class="text-sm text-gray-900">{{ assignment?.approved_by?.first_name || '' }} {{ assignment?.approved_by?.last_name || 'Bilinmiyor' }}</p>
              </div>
              <div>
                <p class="text-sm font-medium text-gray-500 mb-2">Onay Tarihi</p>
                <p class="text-sm text-gray-900">{{ formatDateTime(assignment?.approved_at) }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- System Information Card -->
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
              <svg class="w-5 h-5 mr-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              Sistem Bilgileri
            </h3>
          </div>
          <div class="px-6 py-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
              <div>
                <p class="text-gray-500">Oluşturulma Tarihi</p>
                <p class="mt-1 text-gray-900">{{ formatDateTime(assignment?.created_at) }}</p>
              </div>
              <div>
                <p class="text-gray-500">Son Güncelleme</p>
                <p class="mt-1 text-gray-900">{{ formatDateTime(assignment?.updated_at) }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Action Buttons Card -->
        <div v-if="hasActions" class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 bg-gradient-to-r from-indigo-50 to-purple-50 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">İşlemler</h3>
          </div>
          <div class="px-6 py-4">
            <div class="flex flex-wrap gap-3">
              <button
                v-if="assignment?.status === 'pending'"
                @click="approveAssignment"
                class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors"
              >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                Onayla
              </button>

              <button
                v-if="assignment?.status === 'pending'"
                @click="rejectAssignment"
                class="inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition-colors"
              >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
                Reddet
              </button>

              <button
                v-if="assignment?.status === 'active'"
                @click="completeAssignment"
                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors"
              >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Tamamla
              </button>

              <button
                v-if="['pending', 'active'].includes(assignment?.status)"
                @click="cancelAssignment"
                class="inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition-colors"
              >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
                İptal Et
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { format, parseISO, differenceInDays, differenceInCalendarDays } from 'date-fns'
import { tr } from 'date-fns/locale'

const props = defineProps({
  assignment: {
    type: Object,
    required: true
  }
})

const canEdit = computed(() => {
  return ['pending', 'active'].includes(props.assignment?.status)
})

const hasActions = computed(() => {
  return canEdit.value
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

const getInitials = (firstName, lastName) => {
  const first = firstName ? firstName.charAt(0).toUpperCase() : ''
  const last = lastName ? lastName.charAt(0).toUpperCase() : ''
  return (first + last) || 'NA'
}

const calculateProgress = () => {
  if (!props.assignment?.start_date || !props.assignment?.end_date) return 0

  const start = parseISO(props.assignment.start_date)
  const end = parseISO(props.assignment.end_date)
  const now = new Date()

  const total = differenceInDays(end, start)
  const elapsed = differenceInDays(now, start)

  return Math.min(Math.round((elapsed / total) * 100), 100)
}

const getRemainingDays = () => {
  if (!props.assignment?.end_date) return 0

  const end = parseISO(props.assignment.end_date)
  const now = new Date()

  return Math.max(differenceInCalendarDays(end, now), 0)
}

const getStatusIcon = (status) => {
  const icons = {
    active: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
    pending: 'M12 8v4m0 4v.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
    completed: 'M5 13l4 4L19 7',
    cancelled: 'M6 18L18 6M6 6l12 12'
  }
  return icons[status] || icons.pending
}

const approveAssignment = () => {
  if (confirm('Bu görevlendirmeyi onaylamak istediğinize emin misiniz?')) {
    router.post(route('temporary-assignments.approve', props.assignment?.id), {}, {
      preserveScroll: true
    })
  }
}

const rejectAssignment = () => {
  if (confirm('Bu görevlendirmeyi reddetmek istediğinize emin misiniz?')) {
    router.post(route('temporary-assignments.reject', props.assignment?.id), {}, {
      preserveScroll: true
    })
  }
}

const completeAssignment = () => {
  if (confirm('Bu görevlendirmeyi tamamlamak istediğinize emin misiniz?')) {
    router.post(route('temporary-assignments.complete', props.assignment?.id), {}, {
      preserveScroll: true
    })
  }
}

const cancelAssignment = () => {
  if (confirm('Bu görevlendirmeyi iptal etmek istediğinize emin misiniz? Bu işlem geri alınamaz.')) {
    router.delete(route('temporary-assignments.destroy', props.assignment?.id), {
      onSuccess: () => {
        router.visit(route('temporary-assignments.index'))
      }
    })
  }
}
</script>
