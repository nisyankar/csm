  <template>
    <div class="flex h-full flex-col bg-gradient-to-b from-gray-900 to-gray-800 shadow-xl">
      <!-- Logo and Brand -->
      <div class="flex h-16 shrink-0 items-center border-b border-gray-700 px-6">
        <div class="flex items-center space-x-3">
          <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-600">
            <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m2.25-18v18m13.5-18v18M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3a.75.75 0 01.75-.75h3a.75.75 0 01.75.75v3M21 7.5h-3a2.25 2.25 0 00-2.25 2.25v9a2.25 2.25 0 002.25 2.25H21M9 12.75h1.5m-1.5 2.25h1.5m-1.5-4.5h1.5m-4.5-2.25v.75A2.25 2.25 0 004.5 9h.75a2.25 2.25 0 002.25-2.25v-.75C7.5 4.846 6.154 3.5 4.5 3.5S1.5 4.846 1.5 6v.75z" />
            </svg>
          </div>
          <div class="hidden sm:block">
            <h1 class="text-lg font-bold text-white">SPT</h1>
            <p class="text-xs text-gray-300">İnşaat Puantaj</p>
          </div>
        </div>
        
        <!-- Close button for mobile -->
        <button
          v-if="$props.closeable !== false"
          @click="$emit('close')"
          class="ml-auto lg:hidden p-2 rounded-md text-gray-300 hover:bg-gray-700 hover:text-white transition-colors"
        >
          <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- Navigation -->
      <nav class="flex-1 space-y-2 px-4 py-6 overflow-y-auto custom-scrollbar">
        <!-- Dashboard -->
        <div>
          <SidebarItem
            :href="route('dashboard')"
            :active="route().current('dashboard')"
            icon="home"
            label="Dashboard"
            :badge="user?.roles?.some(role => role.name === 'admin') ? pendingCount : null"
          />
        </div>

        <!-- Main Navigation -->
        <div class="space-y-1">
          <!-- Employee Management -->
          <SidebarGroup
            v-if="canAccess(['admin', 'hr', 'project_manager', 'site_manager'])"
            label="Çalışan Yönetimi"
            icon="users"
            :items="[
              { href: route('employees.index'), label: 'Çalışanlar', active: route().current('employees.*') },
              { href: route('employees.create'), label: 'Yeni Çalışan', active: route().current('employees.create') },
              { href: route('employee-assignments.index'), label: 'Proje Atamaları', active: route().current('employee-assignments.*') },
              { href: route('departments.index'), label: 'Departmanlar', active: route().current('departments.*') }
            ]"
          />

          <!-- Timesheet Management -->
          <SidebarGroup
            label="Puantaj Sistemi"
            icon="clock"
            :items="[
              { href: route('timesheets.bulk.entry'), label: 'Puantaj Girişi', active: route().current('timesheets.bulk.*') },
              { href: route('shifts.index'), label: 'Vardiya Tanımları', active: route().current('shifts.*') },
              { href: route('dashboard'), label: 'QR Tarayıcı', active: false, mobile: true }
            ]"
          />

          <!-- Project Management -->
          <SidebarGroup
            v-if="canAccess(['admin', 'hr', 'project_manager', 'site_manager'])"
            label="Proje Yönetimi"
            icon="building-office"
            :items="[
              { href: route('projects.index'), label: 'Projeler', active: route().current('projects.*') },
              { href: route('projects.create'), label: 'Yeni Proje', active: route().current('projects.create') }
            ]"
          />

          <!-- Daily Reports -->
          <SidebarGroup
            v-if="canAccess(['admin', 'project_manager', 'site_manager', 'foreman'])"
            label="Günlük Raporlar"
            icon="clipboard-document-list"
            :items="[
              { href: route('daily-reports.index'), label: 'Tüm Raporlar', active: route().current('daily-reports.index') },
              { href: route('daily-reports.create'), label: 'Yeni Rapor', active: route().current('daily-reports.create') }
            ]"
          />

          <!-- Subcontractor Management -->
          <SidebarGroup
            v-if="canAccess(['admin', 'hr', 'project_manager', 'site_manager'])"
            label="Taşeron Yönetimi"
            icon="user-group"
            :items="[
              { href: route('subcontractors.index'), label: 'Taşeronlar', active: route().current('subcontractors.*') },
              { href: route('subcontractors.create'), label: 'Yeni Taşeron', active: route().current('subcontractors.create') }
            ]"
          />

          <!-- Progress Payment Management -->
          <SidebarGroup
            v-if="canAccess(['admin', 'project_manager', 'site_manager'])"
            label="Hakediş Takibi"
            icon="chart-bar"
            :items="[
              { href: route('progress-payments.dashboard'), label: 'Dashboard', active: route().current('progress-payments.dashboard') },
              { href: route('progress-payments.index'), label: 'Hakediş Listesi', active: route().current('progress-payments.index') || route().current('progress-payments.show') },
              { href: route('progress-payments.create'), label: 'Yeni Hakediş', active: route().current('progress-payments.create') },
              { href: route('progress-payments.quantity-overrun-report'), label: 'Metraj Aşımı Raporu', active: route().current('progress-payments.quantity-overrun-report') }
            ]"
          />

          <!-- Quantity Management (Keşif & Metraj) -->
          <SidebarGroup
            v-if="canAccess(['admin', 'project_manager', 'site_manager'])"
            label="Keşif & Metraj"
            icon="chart-pie"
            :items="[
              { href: route('quantities.dashboard'), label: 'Dashboard', active: route().current('quantities.dashboard') },
              { href: route('quantities.index'), label: 'Metraj Listesi', active: route().current('quantities.index') || route().current('quantities.show') },
              { href: route('quantities.create'), label: 'Yeni Metraj', active: route().current('quantities.create') }
            ]"
          />

          <!-- Financial Management -->
          <SidebarGroup
            v-if="canAccess(['admin', 'project_manager', 'hr'])"
            label="Finansal Yönetim"
            icon="currency-dollar"
            :items="[
              { href: route('financial.dashboard'), label: 'Dashboard', active: route().current('financial.dashboard') },
              { href: route('financial.index'), label: 'Finansal İşlemler', active: route().current('financial.index') || route().current('financial.show') },
              { href: route('financial.create'), label: 'Yeni İşlem', active: route().current('financial.create') },
              { href: route('financial.reports.profit-loss'), label: 'Kar/Zarar Raporu', active: route().current('financial.reports.profit-loss') }
            ]"
          />

          <!-- Contract Management -->
          <SidebarGroup
            v-if="canAccess(['admin', 'project_manager'])"
            label="Sözleşme Yönetimi"
            icon="document-text"
            :items="[
              { href: route('contracts.dashboard'), label: 'Dashboard', active: route().current('contracts.dashboard') },
              { href: route('contracts.index'), label: 'Sözleşmeler', active: route().current('contracts.index') || route().current('contracts.show') || route().current('contracts.edit') },
              { href: route('contracts.create'), label: 'Yeni Sözleşme', active: route().current('contracts.create') }
            ]"
          />

          <!-- Sales & Deed Management -->
          <SidebarGroup
            v-if="canAccess(['admin', 'project_manager', 'sales_manager'])"
            label="Satış & Tapu Yönetimi"
            icon="home"
            :items="[
              { href: route('sales.customers.index'), label: 'Müşteriler', active: route().current('sales.customers.*') },
              { href: route('sales.unit-sales.index'), label: 'Satışlar', active: route().current('sales.unit-sales.*') },
              { href: route('sales.payments.index'), label: 'Ödeme Takibi', active: route().current('sales.payments.*') },
              { href: route('sales.sales-status.index'), label: 'Satış Durumu', active: route().current('sales.sales-status.*') }
            ]"
          />

          <!-- Construction Permits (Ruhsat Yönetimi) -->
          <SidebarGroup
            v-if="canAccess(['admin', 'project_manager', 'site_manager'])"
            label="Ruhsat Yönetimi"
            icon="clipboard-document-check"
            :items="[
              { href: route('construction-permits.dashboard'), label: 'Dashboard', active: route().current('construction-permits.dashboard') },
              { href: route('construction-permits.index'), label: 'Ruhsatlar', active: route().current('construction-permits.index') || route().current('construction-permits.show') },
              { href: route('construction-permits.create'), label: 'Yeni Ruhsat', active: route().current('construction-permits.create') }
            ]"
          />

          <!-- Building Inspections (Yapı Denetim) -->
          <SidebarGroup
            v-if="canAccess(['admin', 'project_manager', 'site_supervisor'])"
            label="Yapı Denetim"
            icon="shield-check"
            :items="[
              { href: route('inspections.dashboard'), label: 'Dashboard', active: route().current('inspections.dashboard') },
              { href: route('inspections.index'), label: 'Denetimler', active: route().current('inspections.index') || route().current('inspections.show') },
              { href: route('inspections.create'), label: 'Yeni Denetim', active: route().current('inspections.create') },
              { href: route('inspection-companies.index'), label: 'Denetim Kuruluşları', active: route().current('inspection-companies.*') }
            ]"
          />

          <!-- Leave Management -->
          <SidebarGroup
            label="İzin Yönetimi"
            icon="calendar-days"
            :items="getLeaveManagementItems()"
          />

          <!-- Purchasing Management -->
          <SidebarGroup
            v-if="canAccess(['admin', 'hr', 'project_manager', 'site_manager', 'foreman'])"
            label="Satınalma & Stok"
            icon="shopping-cart"
            :items="[
              { href: route('purchasing-requests.index'), label: 'Satınalma Talepleri', active: route().current('purchasing-requests.*') },
              { href: route('purchasing-requests.create'), label: 'Yeni Talep', active: route().current('purchasing-requests.create') },
              { href: route('materials.index'), label: 'Malzemeler', active: route().current('materials.*') },
              { href: route('warehouses.index'), label: 'Depolar', active: route().current('warehouses.*') },
              { href: route('stock-movements.index'), label: 'Stok Hareketleri', active: route().current('stock-movements.*') }
            ]"
          />

          <!-- Safety Management (İSG) -->
          <SidebarGroup
            v-if="canAccess(['admin', 'project_manager', 'site_manager', 'safety_officer'])"
            label="İş Sağlığı ve Güvenliği"
            icon="shield-exclamation"
            :items="[
              { href: route('safety-incidents.index'), label: 'İş Kazaları', active: route().current('safety-incidents.*') },
              { href: route('safety-trainings.index'), label: 'İSG Eğitimleri', active: route().current('safety-trainings.*') },
              { href: route('safety-inspections.index'), label: 'Güvenlik Denetimleri', active: route().current('safety-inspections.*') },
              { href: route('risk-assessments.index'), label: 'Risk Değerlendirmeleri', active: route().current('risk-assessments.*') },
              { href: route('ppe-assignments.index'), label: 'KKD Atamaları', active: route().current('ppe-assignments.*') }
            ]"
          />

          <!-- Document Management -->
          <SidebarGroup
            v-if="canAccess(['admin', 'hr', 'project_manager'])"
            label="Belge Yönetimi"
            icon="document-text"
            :items="[
              { href: route('dashboard'), label: 'Belgeler', active: false },
              { href: route('dashboard'), label: 'Süresi Dolacaklar', active: false, badge: expiringDocsCount }
            ]"
          />

          <!-- Reports -->
          <SidebarGroup
            v-if="canAccess(['admin', 'hr', 'project_manager'])"
            label="Raporlar"
            icon="chart-bar"
            :items="[
              { href: route('reports.index'), label: 'Rapor Merkezi', active: route().current('reports.*') },
              { href: route('dashboard'), label: 'Puantaj Raporları', active: false },
              { href: route('dashboard'), label: 'Proje Raporları', active: false }
            ]"
          />

          <!-- System Management (Sadece admin ve system_admin için) -->
          <SidebarGroup
            v-if="canAccess(['admin', 'system_admin'])"
            label="Sistem Yönetimi"
            icon="cog-6-tooth"
            :items="[
              { href: route('system.settings'), label: 'Sistem Ayarları', active: route().current('system.settings') },
              { href: route('system.users'), label: 'Kullanıcı Yönetimi', active: route().current('system.users') },
              { href: route('system.logs'), label: 'Sistem Logları', active: route().current('system.logs') }
            ]"
          />
        </div>

        <!-- QR Code Management -->
        <div v-if="canAccess(['admin', 'project_manager', 'site_manager'])" class="border-t border-gray-700 pt-4">
          <SidebarItem
            :href="route('dashboard')"
            :active="false"
            icon="qr-code"
            label="QR Kod Yönetimi"
          />
        </div>
      </nav>

      <!-- User Profile Footer -->
      <div class="border-t border-gray-700 p-4">
        <div class="flex items-center space-x-3">
          <div class="flex-shrink-0">
            <div class="h-8 w-8 rounded-full bg-blue-600 flex items-center justify-center">
              <span class="text-sm font-medium text-white">
                {{ user?.name?.charAt(0).toUpperCase() || 'U' }}
              </span>
            </div>
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-white truncate">{{ user?.name || 'Kullanıcı' }}</p>
            <p class="text-xs text-gray-300 truncate">{{ getUserRoleDisplay() }}</p>
          </div>
          <div class="flex-shrink-0">
            <UserDropdown />
          </div>
        </div>
      </div>
    </div>
  </template>

  <script setup>
  import { computed } from 'vue'
  import { usePage } from '@inertiajs/vue3'
  import SidebarItem from './SidebarItem.vue'
  import SidebarGroup from './SidebarGroup.vue'
  import UserDropdown from './UserDropdown.vue'

  defineEmits(['close'])

  const page = usePage()

  // User data
  const user = computed(() => page.props.auth?.user)

  // Permission checks - updated to use roles array
  const canAccess = (roles) => {
    if (!user.value || !user.value.roles) return false
    const userRoles = user.value.roles.map(role => role.name)
    return roles.some(role => userRoles.includes(role))
  }

  // Leave management access check
  const canAccessLeaveManagement = computed(() => {
    return canAccess(['admin', 'system_admin', 'hr'])
  })

  // Generate leave management items based on user permissions
  const getLeaveManagementItems = () => {
    const baseItems = [
      { href: route('leave-requests.index'), label: 'İzin Talepleri', active: route().current('leave-requests.*') },
      { href: route('dashboard'), label: 'İzin Takvimi', active: false },
      { href: route('leave-requests.create'), label: 'İzin Talebi', active: route().current('leave-requests.create') }
    ]

    // Admin ve HR için ek menüler
    if (canAccessLeaveManagement.value) {
      baseItems.push(
        { href: route('leave-management.parameters.index'), label: 'İzin Parametreleri', active: route().current('leave-management.parameters.*') },
        { href: route('leave-management.types.index'), label: 'İzin Türleri', active: route().current('leave-management.types.*') },
        { href: route('leave-management.calculations.index'), label: 'İzin Hesaplamaları', active: route().current('leave-management.calculations.*') },
        { href: route('leave-management.reports.index'), label: 'İzin Raporları', active: route().current('leave-management.reports.*') }
      )
    }

    return baseItems
  }

  // User role display
  const getUserRoleDisplay = () => {
    if (!user.value) return 'Kullanıcı'
    
    if (user.value.employee?.category) {
      const categoryLabels = {
        worker: 'İşçi',
        foreman: 'Forman',
        engineer: 'Mühendis',
        manager: 'Proje Yöneticisi',
        system_admin: 'Sistem Yöneticisi'
      }
      return categoryLabels[user.value.employee.category] || 'Personel'
    }
    
    // Fallback to roles
    if (user.value.roles?.length > 0) {
      const roleLabels = {
        admin: 'Yönetici',
        system_admin: 'Sistem Yöneticisi',
        hr: 'İnsan Kaynakları',
        project_manager: 'Proje Yöneticisi',
        site_manager: 'Saha Yöneticisi',
        foreman: 'Forman',
        employee: 'Çalışan'
      }
      return roleLabels[user.value.roles[0].name] || user.value.roles[0].name
    }
    
    return user.value.role_display || user.value.role || 'Kullanıcı'
  }

  // Dashboard stats for badges
  const pendingCount = computed(() => {
    const stats = page.props.stats
    if (!stats) return null
    
    const total = (stats.pending_leaves || 0) + (stats.pending_timesheets || 0)
    return total > 0 ? total : null
  })

  const expiringDocsCount = computed(() => {
    return page.props.stats?.expiring_documents || null
  })
  </script>

  <style scoped>
  /* Custom scrollbar - using standard CSS instead of @apply */
  .custom-scrollbar::-webkit-scrollbar {
    width: 4px;
  }

  .custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
  }

  .custom-scrollbar::-webkit-scrollbar-thumb {
    background-color: #4b5563;
    border-radius: 9999px;
  }

  .custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background-color: #6b7280;
  }
  </style>