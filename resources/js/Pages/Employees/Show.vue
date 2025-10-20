<template>
  <AppLayout
    title="Çalışan Detayı - SPT İnşaat Puantaj Sistemi"
    :full-width="true"
  >
    <!-- Full-width header with improved design -->
    <template #fullWidthHeader>
      <div class="bg-gradient-to-r from-blue-600 via-blue-700 to-blue-800 border-b border-blue-900/20 w-full">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
          <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <!-- Header Info -->
            <div class="flex-1 min-w-0">
              <div class="flex items-center space-x-4 mb-3">
                <!-- Employee Photo -->
                <div class="flex-shrink-0">
                  <img
                    :src="getPhotoUrl(employee.photo_path)"
                    :alt="`${employee.first_name} ${employee.last_name}`"
                    class="h-16 w-16 rounded-full object-cover border-4 border-white/20 shadow-lg"
                    @error="handleImageError"
                  />
                </div>
                
                <div>
                  <h1 class="text-2xl lg:text-3xl font-bold text-white">
                    {{ employee.first_name }} {{ employee.last_name }}
                  </h1>
                  <div class="flex flex-wrap items-center gap-3 mt-2">
                    <p class="text-blue-100 text-sm">
                      {{ employee.position }} • {{ employee.employee_code }}
                    </p>
                    <Badge
                      :text="getStatusLabel(employee.status)"
                      :variant="getStatusVariant(employee.status)"
                    />
                    <Badge
                      :text="getCategoryLabel(employee.category)"
                      variant="secondary"
                    />
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="flex-shrink-0">
              <div class="flex flex-wrap items-center gap-3">
                <!-- QR Code Modal Button -->
                <Button
                  variant="outline"
                  size="sm"
                  @click="showQRModal = true"
                  class="bg-white/10 backdrop-blur-sm border-white/30 text-white hover:bg-white/20"
                >
                  <QrCodeIcon class="w-4 h-4 mr-2" />
                  QR Kod
                </Button>
                
                <!-- Edit Button -->
                <Button
                  variant="outline"
                  :href="route('employees.edit', employee.id)"
                  class="bg-white/10 backdrop-blur-sm border-white/30 text-white hover:bg-white/20"
                >
                  <PencilIcon class="w-4 h-4 mr-2" />
                  Düzenle
                </Button>
                
                <!-- Back Button -->
                <Button
                  variant="outline"
                  :href="route('employees.index')"
                  class="bg-white text-blue-600 hover:bg-blue-50"
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
                    class="text-blue-100 hover:text-white transition-colors"
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
                  <svg class="h-3 w-3 text-blue-200 mx-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                  </svg>

                  <!-- Breadcrumb item -->
                  <div class="flex items-center">
                    <Link
                      v-if="item.href && index < breadcrumbs.length - 1"
                      :href="item.href"
                      class="text-xs font-medium text-blue-100 hover:text-white transition-colors"
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

    <!-- Main Content Container -->
    <div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
      <!-- Statistics Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Timesheets -->
        <Card class="bg-gradient-to-br from-blue-50 to-blue-100 border-blue-200">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center">
                <ClockIcon class="w-5 h-5 text-white" />
              </div>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-blue-900">Toplam Puantaj</p>
              <p class="text-2xl font-bold text-blue-600">{{ stats.total_timesheets || 0 }}</p>
            </div>
          </div>
        </Card>

        <!-- This Month Hours -->
        <Card class="bg-gradient-to-br from-green-50 to-green-100 border-green-200">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-green-600 rounded-lg flex items-center justify-center">
                <CalendarIcon class="w-5 h-5 text-white" />
              </div>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-green-900">Bu Ay Çalışma</p>
              <p class="text-2xl font-bold text-green-600">{{ Math.round(stats.this_month_hours || 0) }}h</p>
            </div>
          </div>
        </Card>

        <!-- Overtime Hours -->
        <Card class="bg-gradient-to-br from-orange-50 to-orange-100 border-orange-200">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-orange-600 rounded-lg flex items-center justify-center">
                <FireIcon class="w-5 h-5 text-white" />
              </div>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-orange-900">Toplam Mesai</p>
              <p class="text-2xl font-bold text-orange-600">{{ Math.round(stats.total_overtime || 0) }}h</p>
            </div>
          </div>
        </Card>

        <!-- This Month Earnings -->
        <Card class="bg-gradient-to-br from-purple-50 to-purple-100 border-purple-200">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-purple-600 rounded-lg flex items-center justify-center">
                <CurrencyDollarIcon class="w-5 h-5 text-white" />
              </div>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-purple-900">Bu Ay Kazanç</p>
              <p class="text-2xl font-bold text-purple-600">₺{{ formatMoney(stats.this_month_earnings || 0) }}</p>
            </div>
          </div>
        </Card>
      </div>

      <!-- Main Content Tabs -->
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
                  ? 'border-blue-500 text-blue-600'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
              ]"
            >
              <component :is="tab.icon" class="w-4 h-4 mr-2 inline" />
              {{ tab.label }}
            </button>
          </nav>
        </div>

        <div class="p-6">
          <!-- Personal Information Tab -->
          <div v-if="activeTab === 'personal'" class="space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
              <!-- Basic Information -->
              <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Temel Bilgiler</h3>
                <dl class="space-y-3">
                  <div class="flex justify-between">
                    <dt class="text-sm font-medium text-gray-500">Ad Soyad</dt>
                    <dd class="text-sm text-gray-900">{{ employee.first_name }} {{ employee.last_name }}</dd>
                  </div>
                  <div class="flex justify-between">
                    <dt class="text-sm font-medium text-gray-500">TC Kimlik No</dt>
                    <dd class="text-sm text-gray-900">{{ maskTC(employee.tc_number) }}</dd>
                  </div>
                  <div class="flex justify-between">
                    <dt class="text-sm font-medium text-gray-500">Doğum Tarihi</dt>
                    <dd class="text-sm text-gray-900">{{ formatDate(employee.birth_date) }}</dd>
                  </div>
                  <div class="flex justify-between">
                    <dt class="text-sm font-medium text-gray-500">Yaş</dt>
                    <dd class="text-sm text-gray-900">{{ calculateAge(employee.birth_date) }} yaşında</dd>
                  </div>
                  <div class="flex justify-between">
                    <dt class="text-sm font-medium text-gray-500">Cinsiyet</dt>
                    <dd class="text-sm text-gray-900">{{ getGenderLabel(employee.gender) }}</dd>
                  </div>
                  <div class="flex justify-between">
                    <dt class="text-sm font-medium text-gray-500">Durum</dt>
                    <dd>
                      <Badge
                        :text="getStatusLabel(employee.status)"
                        :variant="getStatusVariant(employee.status)"
                        size="sm"
                      />
                    </dd>
                  </div>
                </dl>
              </div>

              <!-- Contact Information -->
              <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">İletişim Bilgileri</h3>
                <dl class="space-y-3">
                  <div class="flex justify-between">
                    <dt class="text-sm font-medium text-gray-500">Telefon</dt>
                    <dd class="text-sm text-gray-900">
                      <a v-if="employee.phone" :href="`tel:${employee.phone}`" class="text-blue-600 hover:text-blue-500">
                        {{ employee.phone }}
                      </a>
                      <span v-else class="text-gray-400">Belirtilmemiş</span>
                    </dd>
                  </div>
                  <div class="flex justify-between">
                    <dt class="text-sm font-medium text-gray-500">E-posta</dt>
                    <dd class="text-sm text-gray-900">
                      <a v-if="employee.email" :href="`mailto:${employee.email}`" class="text-blue-600 hover:text-blue-500">
                        {{ employee.email }}
                      </a>
                      <span v-else class="text-gray-400">Belirtilmemiş</span>
                    </dd>
                  </div>
                  <div>
                    <dt class="text-sm font-medium text-gray-500 mb-1">Adres</dt>
                    <dd class="text-sm text-gray-900">
                      {{ employee.address || 'Belirtilmemiş' }}
                    </dd>
                  </div>
                </dl>
              </div>
            </div>

            <!-- Work Information -->
            <div>
              <h3 class="text-lg font-semibold text-gray-900 mb-4">İş Bilgileri</h3>
              <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <dl class="space-y-3">
                  <div class="flex justify-between">
                    <dt class="text-sm font-medium text-gray-500">Çalışan Kodu</dt>
                    <dd class="text-sm text-gray-900 font-mono">{{ employee.employee_code }}</dd>
                  </div>
                  <div class="flex justify-between">
                    <dt class="text-sm font-medium text-gray-500">Pozisyon</dt>
                    <dd class="text-sm text-gray-900">{{ employee.position }}</dd>
                  </div>
                  <div class="flex justify-between">
                    <dt class="text-sm font-medium text-gray-500">Kategori</dt>
                    <dd>
                      <Badge
                        :text="getCategoryLabel(employee.category)"
                        variant="secondary"
                        size="sm"
                      />
                    </dd>
                  </div>
                </dl>

                <dl class="space-y-3">
                  <div class="flex justify-between">
                    <dt class="text-sm font-medium text-gray-500">İşe Başlama</dt>
                    <dd class="text-sm text-gray-900">{{ formatDate(employee.start_date) }}</dd>
                  </div>
                  <div class="flex justify-between">
                    <dt class="text-sm font-medium text-gray-500">Kıdem</dt>
                    <dd class="text-sm text-gray-900">{{ calculateTenure(employee.start_date) }}</dd>
                  </div>
                  <div class="flex justify-between">
                    <dt class="text-sm font-medium text-gray-500">Yönetici</dt>
                    <dd class="text-sm text-gray-900">
                      <a
                        v-if="employee.manager"
                        :href="route('employees.show', employee.manager.id)"
                        class="text-blue-600 hover:text-blue-500"
                      >
                        {{ employee.manager.first_name }} {{ employee.manager.last_name }}
                      </a>
                      <span v-else class="text-gray-400">Belirtilmemiş</span>
                    </dd>
                  </div>
                </dl>

                <dl class="space-y-3">
                  <div class="flex justify-between">
                    <dt class="text-sm font-medium text-gray-500">Mevcut Proje</dt>
                    <dd class="text-sm text-gray-900">
                      <a
                        v-if="employee.current_project"
                        :href="route('projects.show', employee.current_project.id)"
                        class="text-blue-600 hover:text-blue-500"
                      >
                        {{ employee.current_project.name }}
                      </a>
                      <span v-else class="text-gray-400">Atanmamış</span>
                    </dd>
                  </div>
                  <div class="flex justify-between">
                    <dt class="text-sm font-medium text-gray-500">Ücret Türü</dt>
                    <dd class="text-sm text-gray-900">{{ getWageTypeLabel(employee.wage_type) }}</dd>
                  </div>
                  <div class="flex justify-between">
                    <dt class="text-sm font-medium text-gray-500">Ücret</dt>
                    <dd class="text-sm text-gray-900">
                      {{ getWageDisplay(employee) }}
                    </dd>
                  </div>
                </dl>
              </div>
            </div>

            <!-- Leave Information -->
            <div>
              <h3 class="text-lg font-semibold text-gray-900 mb-4">İzin Bilgileri</h3>
              <div class="bg-blue-50 rounded-lg p-4">
                <div class="flex items-center justify-between">
                  <div>
                    <p class="text-sm font-medium text-blue-900">Yıllık İzin Durumu</p>
                    <p class="text-2xl font-bold text-blue-600 mt-1">
                      {{ (employee.annual_leave_days || 0) - (employee.used_leave_days || 0) }} / {{ employee.annual_leave_days || 0 }} gün
                    </p>
                    <p class="text-xs text-blue-700 mt-1">Kalan / Toplam</p>
                  </div>
                  <div class="w-16 h-16">
                    <div class="relative w-full h-full">
                      <svg class="w-full h-full transform -rotate-90" viewBox="0 0 36 36">
                        <path
                          d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                          fill="none"
                          stroke="#E5E7EB"
                          stroke-width="3"
                        />
                        <path
                          d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                          fill="none"
                          stroke="#3B82F6"
                          stroke-width="3"
                          :stroke-dasharray="`${leavePercentage}, 100`"
                        />
                      </svg>
                      <div class="absolute inset-0 flex items-center justify-center">
                        <span class="text-xs font-semibold text-blue-600">{{ Math.round(leavePercentage) }}%</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Other tabs content -->
          <div v-else-if="activeTab === 'timesheets'" class="space-y-6">
            <h3 class="text-lg font-semibold text-gray-900">Puantaj bilgileri burada görüntülenecek</h3>
          </div>

          <div v-else-if="activeTab === 'projects'" class="space-y-6">
            <h3 class="text-lg font-semibold text-gray-900">Proje bilgileri burada görüntülenecek</h3>
          </div>

          <div v-else-if="activeTab === 'leaves'" class="space-y-6">
            <h3 class="text-lg font-semibold text-gray-900">İzin bilgileri burada görüntülenecek</h3>
          </div>

          <div v-else-if="activeTab === 'documents'" class="space-y-6">
            <h3 class="text-lg font-semibold text-gray-900">Belge bilgileri burada görüntülenecek</h3>
          </div>
        </div>
      </Card>
    </div>

    <!-- QR Code Modal -->
    <Modal
      :show="showQRModal"
      title="Çalışan QR Kodu"
      size="md"
      @close="showQRModal = false"
      closable
    >
      <div class="text-center space-y-4">
        <div class="flex justify-center">
          <div class="bg-white p-6 rounded-lg shadow-sm border">
            <div v-html="generatedQrCode" class="qr-code"></div>
          </div>
        </div>
        
        <div>
          <p class="text-lg font-medium text-gray-900">
            {{ employee.first_name }} {{ employee.last_name }}
          </p>
          <p class="text-sm text-gray-600">{{ employee.employee_code }}</p>
          <p class="text-xs text-gray-500 mt-2">
            Bu QR kodu puantaj giriş/çıkış işlemleri için kullanılabilir
          </p>
        </div>

        <div class="flex justify-center space-x-3 pt-4">
          <Button
            variant="outline"
            size="sm"
            @click="downloadQR"
          >
            <ArrowDownTrayIcon class="w-4 h-4 mr-2" />
            İndir
          </Button>
          <Button
            variant="outline"
            size="sm"
            @click="printQR"
          >
            <PrinterIcon class="w-4 h-4 mr-2" />
            Yazdır
          </Button>
        </div>
      </div>
    </Modal>
  </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Button from '@/Components/UI/Button.vue'
import Card from '@/Components/UI/Card.vue'
import Badge from '@/Components/UI/Badge.vue'
import Modal from '@/Components/UI/Modal.vue'

// Heroicons
import {
  ArrowLeftIcon,
  PencilIcon,
  QrCodeIcon,
  ClockIcon,
  CalendarIcon,
  FireIcon,
  CurrencyDollarIcon,
  UserIcon,
  BuildingOfficeIcon,
  CalendarDaysIcon,
  DocumentIcon,
  ArrowDownTrayIcon,
  PrinterIcon
} from '@heroicons/vue/24/outline'

// Props
const props = defineProps({
  employee: {
    type: Object,
    required: true
  },
  stats: {
    type: Object,
    default: () => ({})
  }
})

// Component state
const activeTab = ref('personal')
const showQRModal = ref(false)
const generatedQrCode = ref('')
const imageLoadFailed = ref(false)

// Breadcrumbs
const breadcrumbs = [
  { label: 'Çalışanlar', href: route('employees.index') },
  { label: `${props.employee.first_name} ${props.employee.last_name}` }
]

// Tab configuration
const tabs = [
  { id: 'personal', label: 'Kişisel Bilgiler', icon: UserIcon },
  { id: 'timesheets', label: 'Puantajlar', icon: ClockIcon },
  { id: 'projects', label: 'Projeler', icon: BuildingOfficeIcon },
  { id: 'leaves', label: 'İzinler', icon: CalendarDaysIcon },
  { id: 'documents', label: 'Belgeler', icon: DocumentIcon }
]

// Computed
const leavePercentage = computed(() => {
  const annual = props.employee.annual_leave_days || 0
  const used = props.employee.used_leave_days || 0
  const remaining = annual - used
  if (annual === 0) return 0
  return (remaining / annual) * 100
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
  // Prevent infinite loop by setting a flag
  if (!imageLoadFailed.value) {
    imageLoadFailed.value = true
    event.target.src = getPhotoUrl(null)
  }
}

const maskTC = (tcNumber) => {
  if (!tcNumber) return ''
  return tcNumber.substring(0, 3) + '*****' + tcNumber.substring(8)
}

const formatDate = (date) => {
  if (!date) return ''
  return new Date(date).toLocaleDateString('tr-TR')
}

const formatMoney = (amount) => {
  if (!amount) return '0'
  return new Intl.NumberFormat('tr-TR', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(amount)
}

const calculateAge = (birthDate) => {
  if (!birthDate) return ''
  const today = new Date()
  const birth = new Date(birthDate)
  let age = today.getFullYear() - birth.getFullYear()
  const monthDiff = today.getMonth() - birth.getMonth()
  if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birth.getDate())) {
    age--
  }
  return age
}

const calculateTenure = (startDate) => {
  if (!startDate) return ''
  const start = new Date(startDate)
  const today = new Date()
  const years = today.getFullYear() - start.getFullYear()
  const months = today.getMonth() - start.getMonth()
  
  let totalMonths = years * 12 + months
  if (today.getDate() < start.getDate()) {
    totalMonths--
  }
  
  const tenureYears = Math.floor(totalMonths / 12)
  const tenureMonths = totalMonths % 12
  
  if (tenureYears > 0) {
    return `${tenureYears} yıl ${tenureMonths} ay`
  } else {
    return `${tenureMonths} ay`
  }
}

// Label getters
const getStatusLabel = (status) => {
  const labels = {
    active: 'Aktif',
    inactive: 'Pasif',
    on_leave: 'İzinli',
    terminated: 'İşten Ayrılmış'
  }
  return labels[status] || status
}

const getStatusVariant = (status) => {
  const variants = {
    active: 'success',
    inactive: 'secondary',
    on_leave: 'warning',
    terminated: 'error'
  }
  return variants[status] || 'secondary'
}

const getCategoryLabel = (category) => {
  const labels = {
    worker: 'İşçi',
    foreman: 'Forman',
    engineer: 'Mühendis',
    manager: 'Yönetici'
  }
  return labels[category] || category
}

const getGenderLabel = (gender) => {
  const labels = {
    male: 'Erkek',
    female: 'Kadın'
  }
  return labels[gender] || 'Belirtilmemiş'
}

const getWageTypeLabel = (wageType) => {
  const labels = {
    daily: 'Günlük',
    hourly: 'Saatlik',
    monthly: 'Aylık'
  }
  return labels[wageType] || wageType
}

const getWageDisplay = (employee) => {
  if (employee.wage_type === 'daily' && employee.daily_wage) {
    return `₺${formatMoney(employee.daily_wage)} / gün`
  } else if (employee.wage_type === 'hourly' && employee.hourly_wage) {
    return `₺${formatMoney(employee.hourly_wage)} / saat`
  } else if (employee.wage_type === 'monthly' && employee.monthly_salary) {
    return `₺${formatMoney(employee.monthly_salary)} / ay`
  }
  return 'Belirtilmemiş'
}

// QR Code methods
const generateQrCode = () => {
  const qrData = JSON.stringify({
    employee_code: props.employee.employee_code,
    name: `${props.employee.first_name} ${props.employee.last_name}`,
    tc_number: props.employee.tc_number,
    position: props.employee.position
  })
  
  // Mock QR code generation (use actual QR library in production)
  generatedQrCode.value = `
    <div style="width: 128px; height: 128px; background: #000; position: relative; margin: 0 auto;">
      <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); color: white; font-size: 8px; text-align: center;">
        QR CODE<br/>${props.employee.employee_code}
      </div>
    </div>
  `
}

const downloadQR = () => {
  console.log('Downloading QR code...')
}

const printQR = () => {
  window.print()
}

// Lifecycle
onMounted(() => {
  generateQrCode()
})
</script>

<style scoped>
.qr-code {
  display: inline-block;
  font-family: monospace;
}

@media print {
  body * {
    visibility: hidden;
  }
  
  .qr-code,
  .qr-code * {
    visibility: visible;
  }
  
  .qr-code {
    position: absolute;
    left: 50%;
    top: 50%;
    transform: translate(-50%, -50%);
  }
}
</style>