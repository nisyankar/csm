<template>
  <AppLayout title="Admin Dashboard - SPT İnşaat Puantaj Sistemi" :full-width="true">
    <!-- Full-width header -->
    <template #fullWidthHeader>
      <div class="bg-gradient-to-r from-blue-600 via-blue-700 to-blue-800 border-b border-blue-900/20 w-full">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
          <div class="flex items-center">
            <div class="flex-shrink-0 w-16 h-16 bg-white/10 rounded-full flex items-center justify-center backdrop-blur-sm">
              <svg class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
            </div>
            <div class="ml-6">
              <h1 class="text-2xl lg:text-3xl font-bold text-white">
                Hoş geldiniz, {{ user?.name }}!
              </h1>
              <p class="text-blue-100 text-sm mt-1">
                Sistem yönetici paneline hoş geldiniz. Bugünkü özet bilgiler aşağıda yer almaktadır.
              </p>
            </div>
          </div>
        </div>

        <!-- Breadcrumb inside header -->
        <div class="bg-black/10 border-t border-white/10">
          <div class="w-full px-4 sm:px-6 lg:px-8">
            <div class="flex items-center space-x-2 py-2">
              <ol class="flex items-center space-x-2">
                <li>
                  <Link :href="route('dashboard')" class="text-blue-100 hover:text-white transition-colors">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                    </svg>
                    <span class="sr-only">Ana Sayfa</span>
                  </Link>
                </li>
                <li class="flex items-center">
                  <svg class="h-3 w-3 text-blue-200 mx-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                  </svg>
                  <span class="text-xs font-medium text-white">Dashboard</span>
                </li>
              </ol>
            </div>
          </div>
        </div>
      </div>
    </template>

    <!-- Main Content -->
    <div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
      <!-- Stats Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <StatCard
          v-for="(card, index) in statCards"
          :key="index"
          :title="card.title"
          :value="card.value"
          :icon="card.icon"
          :icon-color="card.iconColor"
          :link="card.link"
        />
      </div>

      <!-- Quick Actions Widget -->
      <QuickActionWidget
        v-if="quickActions && quickActions.length > 0"
        title="Hızlı İşlemler"
        :actions="quickActions"
      />

      <!-- Alerts Widget -->
      <AlertWidget
        v-if="alerts && alerts.length > 0"
        title="Sistem Uyarıları"
        :alerts="alerts || []"
      />

      <!-- Recent Activity Widget -->
      <ActivityWidget
        title="Son Aktiviteler"
        :activities="recent_activities || []"
      />
    </div>
  </AppLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import StatCard from '@/Components/Widgets/StatCard.vue'
import ActivityWidget from '@/Components/Widgets/ActivityWidget.vue'
import AlertWidget from '@/Components/Widgets/AlertWidget.vue'
import QuickActionWidget from '@/Components/Widgets/QuickActionWidget.vue'

// Props
const props = defineProps({
  stats: {
    type: Object,
    default: () => ({})
  },
  charts: {
    type: Object,
    default: () => ({})
  },
  recent_activities: {
    type: Array,
    default: () => []
  },
  alerts: {
    type: Array,
    default: () => []
  }
})

const page = usePage()

// Computed
const user = computed(() => page.props.auth?.user)

// Stat Cards Configuration
const statCards = computed(() => {
  try {
    return [
      {
        title: 'Toplam Çalışan',
        value: props.stats?.total_employees || 0,
        icon: 'users',
        iconColor: 'blue',
        link: { href: route('employees.index'), text: 'Çalışanları görüntüle' }
      },
      {
        title: 'Aktif Projeler',
        value: props.stats?.active_projects || 0,
        icon: 'building',
        iconColor: 'green',
        link: { href: route('projects.index'), text: 'Projeleri görüntüle' }
      },
      {
        title: 'Bekleyen Onaylar',
        value: props.stats?.pending_timesheets || 0,
        icon: 'clock',
        iconColor: 'yellow',
        link: { href: route('timesheets.index'), text: 'Onayları görüntüle' }
      },
      {
        title: 'Bekleyen İzinler',
        value: props.stats?.pending_leaves || 0,
        icon: 'calendar',
        iconColor: 'purple',
        link: { href: route('leave-requests.index'), text: 'İzinleri görüntüle' }
      }
    ]
  } catch (error) {
    console.error('Error building stat cards:', error)
    return []
  }
})

// Quick Actions Configuration
const quickActions = computed(() => {
  try {
    return [
      {
        label: 'Yeni Çalışan',
        description: 'Sisteme yeni çalışan ekleyin',
        href: route('employees.create'),
        icon: 'user-plus',
        color: 'bg-blue-50 text-blue-700'
      },
      {
        label: 'Yeni Proje',
        description: 'Yeni proje oluşturun',
        href: route('projects.create'),
        icon: 'building-plus',
        color: 'bg-green-50 text-green-700'
      },
      {
        label: 'Puantaj Girişi',
        description: 'Toplu puantaj girişi yapın',
        href: route('timesheets.bulk-entry'),
        icon: 'clock',
        color: 'bg-purple-50 text-purple-700'
      },
      {
        label: 'İzin Parametreleri',
        description: 'İzin ayarlarını yönetin',
        href: route('leave-parameters.index'),
        icon: 'cog',
        color: 'bg-yellow-50 text-yellow-700'
      }
    ]
  } catch (error) {
    console.error('Error building quick actions:', error)
    return []
  }
})
</script>