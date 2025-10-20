<template>
  <AppLayout
    :title="`Puantaj Detayı - ${timesheet.employee.first_name} ${timesheet.employee.last_name}`"
    :breadcrumbs="breadcrumbs"
  >
    <template #header>
      <div class="flex items-center justify-between w-full">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Puantaj Detayı</h1>
          <p class="mt-1 text-sm text-gray-500">
            {{ timesheet.employee.first_name }} {{ timesheet.employee.last_name }} -
            {{ formatDate(timesheet.work_date) }}
          </p>
        </div>
        <div class="flex space-x-3">
          <Button
            variant="outline"
            @click="$inertia.visit(route('timesheets.index'))"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Listeye Dön
          </Button>
          <Button
            v-if="canEdit"
            variant="primary"
            @click="$inertia.visit(route('timesheets.edit', timesheet.id))"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
            Düzenle
          </Button>
        </div>
      </div>
    </template>

    <div class="space-y-6">
      <!-- Status Banner -->
      <Alert
        :variant="getStatusAlertVariant(timesheet.approval_status)"
        v-if="timesheet.approval_status !== 'draft'"
      >
        <div class="flex items-center justify-between">
          <div class="flex items-center">
            <svg v-if="timesheet.approval_status === 'approved'" class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            <svg v-else-if="timesheet.approval_status === 'rejected'" class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
            </svg>
            <svg v-else class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
            </svg>
            <span class="font-medium">{{ getApprovalStatusLabel(timesheet.approval_status) }}</span>
          </div>
          <div v-if="canApprove && timesheet.approval_status === 'pending'" class="flex space-x-2">
            <Button variant="success" size="sm" @click="showApproveModal = true">
              Onayla
            </Button>
            <Button variant="danger" size="sm" @click="showRejectModal = true">
              Reddet
            </Button>
          </div>
        </div>
      </Alert>

      <!-- Main Information Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column: Employee & Work Info -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Employee Information -->
          <Card>
            <h3 class="text-lg font-medium text-gray-900 mb-4">Çalışan Bilgileri</h3>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <p class="text-sm text-gray-500">Ad Soyad</p>
                <p class="text-base font-medium text-gray-900 mt-1">
                  {{ timesheet.employee.first_name }} {{ timesheet.employee.last_name }}
                </p>
              </div>
              <div>
                <p class="text-sm text-gray-500">Sicil No</p>
                <p class="text-base font-medium text-gray-900 mt-1">
                  {{ timesheet.employee.employee_code || '-' }}
                </p>
              </div>
              <div>
                <p class="text-sm text-gray-500">Pozisyon</p>
                <p class="text-base font-medium text-gray-900 mt-1">
                  {{ timesheet.employee.position || '-' }}
                </p>
              </div>
              <div>
                <p class="text-sm text-gray-500">Kategori</p>
                <EmployeeCategoryBadge :category="timesheet.employee.category" />
              </div>
            </div>
          </Card>

          <!-- Work Information -->
          <Card>
            <h3 class="text-lg font-medium text-gray-900 mb-4">Çalışma Bilgileri</h3>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <p class="text-sm text-gray-500">Çalışma Tarihi</p>
                <p class="text-base font-medium text-gray-900 mt-1">
                  {{ formatDate(timesheet.work_date) }}
                  <span class="text-sm text-gray-500 ml-2">({{ getDayName(timesheet.work_date) }})</span>
                </p>
              </div>
              <div>
                <p class="text-sm text-gray-500">Proje</p>
                <p class="text-base font-medium text-gray-900 mt-1">
                  {{ timesheet.project?.name || '-' }}
                </p>
              </div>
              <div>
                <p class="text-sm text-gray-500">Departman</p>
                <p class="text-base font-medium text-gray-900 mt-1">
                  {{ timesheet.department?.name || '-' }}
                </p>
              </div>
              <div>
                <p class="text-sm text-gray-500">Devam Durumu</p>
                <Badge :variant="getAttendanceBadgeVariant(timesheet.attendance_type)" class="mt-1">
                  {{ getAttendanceTypeLabel(timesheet.attendance_type) }}
                </Badge>
              </div>
            </div>
          </Card>

          <!-- Working Hours -->
          <Card v-if="showsWorkingHours">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Çalışma Saatleri</h3>
            <div class="space-y-4">
              <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                  <p class="text-sm text-gray-500">Başlangıç</p>
                  <p class="text-lg font-semibold text-gray-900 mt-1">{{ formatTime(timesheet.start_time) }}</p>
                </div>
                <div>
                  <p class="text-sm text-gray-500">Bitiş</p>
                  <p class="text-lg font-semibold text-gray-900 mt-1">{{ formatTime(timesheet.end_time) }}</p>
                </div>
                <div>
                  <p class="text-sm text-gray-500">Mola Başlangıç</p>
                  <p class="text-lg font-semibold text-gray-900 mt-1">{{ formatTime(timesheet.break_start) || '-' }}</p>
                </div>
                <div>
                  <p class="text-sm text-gray-500">Mola Bitiş</p>
                  <p class="text-lg font-semibold text-gray-900 mt-1">{{ formatTime(timesheet.break_end) || '-' }}</p>
                </div>
              </div>

              <div class="border-t border-gray-200 pt-4">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                  <div class="bg-blue-50 rounded-lg p-3">
                    <p class="text-xs text-blue-600">Toplam Süre</p>
                    <p class="text-xl font-bold text-blue-900 mt-1">{{ timesheet.total_hours || '0.0' }}h</p>
                  </div>
                  <div class="bg-green-50 rounded-lg p-3">
                    <p class="text-xs text-green-600">Normal Mesai</p>
                    <p class="text-xl font-bold text-green-900 mt-1">{{ timesheet.normal_hours || '0.0' }}h</p>
                  </div>
                  <div class="bg-orange-50 rounded-lg p-3">
                    <p class="text-xs text-orange-600">Fazla Mesai</p>
                    <p class="text-xl font-bold text-orange-900 mt-1">{{ timesheet.overtime_hours || '0.0' }}h</p>
                  </div>
                  <div class="bg-purple-50 rounded-lg p-3">
                    <p class="text-xs text-purple-600">Mola Süresi</p>
                    <p class="text-xl font-bold text-purple-900 mt-1">{{ timesheet.break_minutes || '0' }}m</p>
                  </div>
                </div>
              </div>
            </div>
          </Card>

          <!-- Absence/Late Reason -->
          <Card v-if="timesheet.absence_reason || timesheet.late_reason">
            <h3 class="text-lg font-medium text-gray-900 mb-4">
              {{ timesheet.absence_reason ? 'Devamsızlık Nedeni' : 'Geç Gelme Nedeni' }}
            </h3>
            <p class="text-gray-700">
              {{ timesheet.absence_reason || timesheet.late_reason }}
            </p>
          </Card>

          <!-- Notes -->
          <Card v-if="timesheet.notes">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Notlar</h3>
            <p class="text-gray-700 whitespace-pre-wrap">{{ timesheet.notes }}</p>
          </Card>

          <!-- Approval History -->
          <Card v-if="timesheet.approvals && timesheet.approvals.length > 0">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Onay Geçmişi</h3>
            <div class="space-y-4">
              <div
                v-for="approval in timesheet.approvals"
                :key="approval.id"
                class="flex items-start border-l-4 pl-4"
                :class="getApprovalBorderColor(approval.approval_status)"
              >
                <div class="flex-1">
                  <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-gray-900">
                      {{ approval.approver?.name || 'Bilinmeyen' }}
                      <span class="ml-2 text-xs text-gray-500">({{ approval.approval_level }}. Seviye)</span>
                    </p>
                    <Badge :variant="getApprovalBadgeVariant(approval.approval_status)">
                      {{ getApprovalStatusLabel(approval.approval_status) }}
                    </Badge>
                  </div>
                  <p v-if="approval.approval_notes" class="text-sm text-gray-600 mt-2">
                    {{ approval.approval_notes }}
                  </p>
                  <p class="text-xs text-gray-500 mt-2">
                    {{ formatDateTime(approval.approved_at || approval.created_at) }}
                  </p>
                </div>
              </div>
            </div>
          </Card>

          <!-- Revision History -->
          <Card v-if="timesheet.revisions && timesheet.revisions.length > 0">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Revizyon Geçmişi</h3>
            <div class="space-y-3">
              <div
                v-for="revision in timesheet.revisions"
                :key="revision.id"
                class="flex items-start p-3 bg-gray-50 rounded-lg"
              >
                <div class="flex-shrink-0">
                  <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                  </svg>
                </div>
                <div class="ml-3 flex-1">
                  <p class="text-sm font-medium text-gray-900">{{ revision.revised_by?.name || 'Sistem' }}</p>
                  <p class="text-sm text-gray-600 mt-1">{{ revision.revision_reason }}</p>
                  <p class="text-xs text-gray-500 mt-1">{{ formatDateTime(revision.created_at) }}</p>
                </div>
              </div>
            </div>
          </Card>
        </div>

        <!-- Right Column: Summary & Actions -->
        <div class="space-y-6">
          <!-- Wage Summary -->
          <Card>
            <h3 class="text-lg font-medium text-gray-900 mb-4">Ücret Özeti</h3>
            <div class="space-y-3">
              <div class="flex justify-between">
                <span class="text-sm text-gray-600">Normal Ücret</span>
                <span class="text-sm font-medium text-gray-900">₺{{ formatNumber(timesheet.normal_wage || 0) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-sm text-gray-600">Fazla Mesai Ücreti</span>
                <span class="text-sm font-medium text-gray-900">₺{{ formatNumber(timesheet.overtime_wage || 0) }}</span>
              </div>
              <div class="border-t border-gray-200 pt-3 mt-3">
                <div class="flex justify-between">
                  <span class="text-base font-medium text-gray-900">Toplam Ücret</span>
                  <span class="text-lg font-bold text-green-600">₺{{ formatNumber(timesheet.calculated_wage || 0) }}</span>
                </div>
              </div>
            </div>
          </Card>

          <!-- Metadata -->
          <Card>
            <h3 class="text-lg font-medium text-gray-900 mb-4">Kayıt Bilgileri</h3>
            <div class="space-y-3">
              <div>
                <p class="text-sm text-gray-500">Giriş Yöntemi</p>
                <Badge variant="secondary" class="mt-1">{{ getEntryMethodLabel(timesheet.entry_method) }}</Badge>
              </div>
              <div>
                <p class="text-sm text-gray-500">Kaydı Oluşturan</p>
                <p class="text-sm font-medium text-gray-900 mt-1">
                  {{ timesheet.entered_by?.name || 'Sistem' }}
                </p>
              </div>
              <div>
                <p class="text-sm text-gray-500">Oluşturma Tarihi</p>
                <p class="text-sm text-gray-900 mt-1">{{ formatDateTime(timesheet.created_at) }}</p>
              </div>
              <div v-if="timesheet.updated_at !== timesheet.created_at">
                <p class="text-sm text-gray-500">Son Güncelleme</p>
                <p class="text-sm text-gray-900 mt-1">{{ formatDateTime(timesheet.updated_at) }}</p>
              </div>
              <div v-if="timesheet.is_revised">
                <Badge variant="warning" class="mt-1">Revize Edildi</Badge>
              </div>
            </div>
          </Card>

          <!-- Quick Actions -->
          <Card v-if="canPerformActions">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Hızlı İşlemler</h3>
            <div class="space-y-2">
              <Button
                v-if="timesheet.approval_status === 'draft'"
                variant="primary"
                size="sm"
                class="w-full"
                @click="submitForApproval"
              >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Onaya Gönder
              </Button>

              <Button
                v-if="canEdit"
                variant="outline"
                size="sm"
                class="w-full"
                @click="$inertia.visit(route('timesheets.edit', timesheet.id))"
              >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Düzenle
              </Button>

              <Button
                variant="outline"
                size="sm"
                class="w-full"
                @click="printTimesheet"
              >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Yazdır
              </Button>
            </div>
          </Card>
        </div>
      </div>
    </div>

    <!-- Approve Modal -->
    <Modal v-model="showApproveModal" title="Puantaj Onaylama" max-width="md">
      <form @submit.prevent="handleApprove">
        <div class="space-y-4">
          <p class="text-sm text-gray-600">
            Bu puantaj kaydını onaylamak istediğinize emin misiniz?
          </p>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Onay Notu (Opsiyonel)</label>
            <Input
              v-model="approveForm.notes"
              type="textarea"
              rows="3"
              placeholder="Onay ile ilgili notlarınızı ekleyin..."
            />
          </div>
        </div>
        <div class="mt-6 flex justify-end space-x-3">
          <Button type="button" variant="outline" @click="showApproveModal = false">İptal</Button>
          <Button type="submit" variant="success" :disabled="approveForm.processing">
            <Spinner v-if="approveForm.processing" class="w-4 h-4 mr-2" />
            Onayla
          </Button>
        </div>
      </form>
    </Modal>

    <!-- Reject Modal -->
    <Modal v-model="showRejectModal" title="Puantaj Reddetme" max-width="md">
      <form @submit.prevent="handleReject">
        <div class="space-y-4">
          <p class="text-sm text-gray-600">
            Bu puantaj kaydını reddetmek istediğinize emin misiniz?
          </p>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Red Nedeni <span class="text-red-500">*</span>
            </label>
            <Input
              v-model="rejectForm.notes"
              type="textarea"
              rows="3"
              placeholder="Neden reddettiğinizi açıklayın..."
              :error="rejectForm.errors.notes"
              required
            />
          </div>
        </div>
        <div class="mt-6 flex justify-end space-x-3">
          <Button type="button" variant="outline" @click="showRejectModal = false">İptal</Button>
          <Button type="submit" variant="danger" :disabled="rejectForm.processing">
            <Spinner v-if="rejectForm.processing" class="w-4 h-4 mr-2" />
            Reddet
          </Button>
        </div>
      </form>
    </Modal>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useForm, router, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Card from '@/Components/UI/Card.vue'
import Button from '@/Components/UI/Button.vue'
import Input from '@/Components/UI/Input.vue'
import Badge from '@/Components/UI/Badge.vue'
import Alert from '@/Components/UI/Alert.vue'
import Modal from '@/Components/UI/Modal.vue'
import Spinner from '@/Components/UI/Spinner.vue'
import EmployeeCategoryBadge from '@/Components/UI/EmployeeCategoryBadge.vue'
import { format, parseISO } from 'date-fns'
import { tr } from 'date-fns/locale'

// Props
const props = defineProps({
  timesheet: {
    type: Object,
    required: true
  }
})

// State
const showApproveModal = ref(false)
const showRejectModal = ref(false)

const approveForm = useForm({
  notes: ''
})

const rejectForm = useForm({
  notes: ''
})

// Computed
const page = usePage()

const canEdit = computed(() => {
  if (props.timesheet.approval_status === 'approved') return false
  const user = page.props.auth?.user
  return user?.roles?.some(role => ['admin', 'hr', 'foreman'].includes(role.name))
})

const canApprove = computed(() => {
  const user = page.props.auth?.user
  return user?.roles?.some(role => ['admin', 'hr', 'foreman', 'project_manager'].includes(role.name))
})

const canPerformActions = computed(() => canEdit.value || canApprove.value)

const showsWorkingHours = computed(() =>
  ['present', 'late', 'early_leave'].includes(props.timesheet.attendance_type)
)

const breadcrumbs = computed(() => [
  { label: 'Ana Sayfa', url: route('dashboard') },
  { label: 'Puantaj', url: route('timesheets.index') },
  { label: 'Detay', url: null }
])

// Methods
const submitForApproval = () => {
  if (confirm('Bu puantaj kaydını onaya göndermek istediğinize emin misiniz?')) {
    router.post(route('timesheets.submit', props.timesheet.id))
  }
}

const handleApprove = () => {
  approveForm.post(route('timesheets.approve', props.timesheet.id), {
    preserveScroll: true,
    onSuccess: () => {
      showApproveModal.value = false
      approveForm.reset()
    }
  })
}

const handleReject = () => {
  rejectForm.post(route('timesheets.reject', props.timesheet.id), {
    preserveScroll: true,
    onSuccess: () => {
      showRejectModal.value = false
      rejectForm.reset()
    }
  })
}

const printTimesheet = () => {
  window.print()
}

// Helper functions
const formatDate = (date) => {
  if (!date) return '-'
  return format(parseISO(date), 'dd.MM.yyyy', { locale: tr })
}

const getDayName = (date) => {
  if (!date) return ''
  return format(parseISO(date), 'EEEE', { locale: tr })
}

const formatDateTime = (datetime) => {
  if (!datetime) return '-'
  return format(parseISO(datetime), 'dd.MM.yyyy HH:mm', { locale: tr })
}

const formatTime = (time) => {
  if (!time) return '-'
  return time.substring(0, 5)
}

const formatNumber = (num) => {
  return new Intl.NumberFormat('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(num)
}

const getApprovalStatusLabel = (status) => {
  const labels = {
    draft: 'Taslak',
    pending: 'Onay Bekliyor',
    approved: 'Onaylandı',
    rejected: 'Reddedildi'
  }
  return labels[status] || status
}

const getApprovalBadgeVariant = (status) => {
  const variants = {
    draft: 'secondary',
    pending: 'warning',
    approved: 'success',
    rejected: 'danger'
  }
  return variants[status] || 'secondary'
}

const getStatusAlertVariant = (status) => {
  const variants = {
    pending: 'warning',
    approved: 'success',
    rejected: 'danger'
  }
  return variants[status] || 'info'
}

const getApprovalBorderColor = (status) => {
  const colors = {
    pending: 'border-yellow-400',
    approved: 'border-green-400',
    rejected: 'border-red-400'
  }
  return colors[status] || 'border-gray-400'
}

const getAttendanceTypeLabel = (type) => {
  const labels = {
    present: 'Çalıştı',
    absent: 'Devamsız',
    late: 'Geç Geldi',
    early_leave: 'Erken Çıkış',
    sick_leave: 'Hastalık İzni',
    annual_leave: 'Yıllık İzin',
    excuse_leave: 'Mazeret İzni',
    unpaid_leave: 'Ücretsiz İzin'
  }
  return labels[type] || type
}

const getAttendanceBadgeVariant = (type) => {
  const variants = {
    present: 'success',
    absent: 'danger',
    late: 'warning',
    early_leave: 'warning',
    sick_leave: 'info',
    annual_leave: 'info',
    excuse_leave: 'info',
    unpaid_leave: 'secondary'
  }
  return variants[type] || 'secondary'
}

const getEntryMethodLabel = (method) => {
  const labels = {
    manual: 'Manuel Giriş',
    qr_code: 'QR Kod',
    biometric: 'Biyometrik',
    mobile: 'Mobil Uygulama'
  }
  return labels[method] || method
}
</script>
