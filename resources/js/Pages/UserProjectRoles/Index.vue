<template>
  <AppLayout title="Proje Rol Yönetimi" :full-width="true">
    <!-- Full-width header -->
    <template #fullWidthHeader>
      <div class="bg-gradient-to-r from-purple-600 via-indigo-600 to-blue-600 border-b border-purple-900/20 w-full">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
          <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <!-- Header Info -->
            <div class="flex-1 min-w-0">
              <div class="flex items-center space-x-4 mb-3">
                <div class="flex-shrink-0 w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm">
                  <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                  </svg>
                </div>
                <div>
                  <h1 class="text-2xl lg:text-3xl font-bold text-white">Proje Rol Yönetimi</h1>
                  <p class="text-purple-100 text-sm mt-1">Kullanıcıların proje bazlı rol atamalarını yönetin</p>
                </div>
              </div>

              <!-- Stats Row -->
              <div class="flex flex-wrap items-center gap-4 lg:gap-6">
                <div class="bg-white/10 backdrop-blur-sm rounded-lg px-3 py-2">
                  <span class="text-purple-100 text-sm">Toplam Atama:</span>
                  <span class="font-semibold text-white ml-1">{{ statistics.total_assignments || 0 }}</span>
                </div>
                <div class="bg-white/10 backdrop-blur-sm rounded-lg px-3 py-2">
                  <span class="text-purple-100 text-sm">Aktif:</span>
                  <span class="font-semibold text-white ml-1">{{ statistics.active_assignments || 0 }}</span>
                </div>
                <div class="bg-white/10 backdrop-blur-sm rounded-lg px-3 py-2">
                  <span class="text-purple-100 text-sm">Kullanıcı:</span>
                  <span class="font-semibold text-white ml-1">{{ statistics.unique_users || 0 }}</span>
                </div>
                <div class="bg-white/10 backdrop-blur-sm rounded-lg px-3 py-2">
                  <span class="text-purple-100 text-sm">Proje:</span>
                  <span class="font-semibold text-white ml-1">{{ statistics.unique_projects || 0 }}</span>
                </div>
              </div>
            </div>

            <!-- Action Button -->
            <div class="flex-shrink-0">
              <Link
                :href="route('user-project-roles.create')"
                class="inline-flex items-center px-4 py-2 bg-white text-purple-600 text-sm font-medium rounded-lg hover:bg-purple-50 shadow-lg hover:shadow-xl transition-all duration-200"
              >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Yeni Rol Ataması
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
                  <Link :href="route('dashboard')" class="text-purple-100 hover:text-white transition-colors">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                    </svg>
                  </Link>
                </li>
                <li class="flex items-center">
                  <svg class="h-3 w-3 text-purple-200 mx-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                  </svg>
                  <span class="text-white font-medium text-sm">Proje Rol Yönetimi</span>
                </li>
              </ol>
            </div>
          </div>
        </div>
      </div>
    </template>

    <!-- Main Content -->
    <div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
      <!-- Filters -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden">
        <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
          <h3 class="text-lg font-semibold text-gray-900">Filtreler</h3>
        </div>
        <div class="p-6">
          <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Kullanıcı</label>
              <select
                v-model="filterForm.user_id"
                @change="applyFilters"
                class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all"
              >
                <option :value="null">Tüm Kullanıcılar</option>
                <option v-for="user in users" :key="user.id" :value="user.id">
                  {{ user.name }}
                </option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Proje</label>
              <select
                v-model="filterForm.project_id"
                @change="applyFilters"
                class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all"
              >
                <option :value="null">Tüm Projeler</option>
                <option v-for="project in projects" :key="project.id" :value="project.id">
                  {{ project.name }}
                </option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Rol</label>
              <select
                v-model="filterForm.role"
                @change="applyFilters"
                class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all"
              >
                <option :value="null">Tüm Roller</option>
                <option value="project_manager">Proje Yöneticisi</option>
                <option value="site_manager">Şantiye Şefi</option>
                <option value="engineer">Mühendis</option>
                <option value="foreman">Forman</option>
                <option value="viewer">Görüntüleyici</option>
                <option value="inspector">Denetçi</option>
                <option value="safety_officer">İSG Uzmanı</option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Durum</label>
              <select
                v-model="filterForm.is_active"
                @change="applyFilters"
                class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all"
              >
                <option :value="null">Tümü</option>
                <option :value="1">Aktif</option>
                <option :value="0">Pasif</option>
              </select>
            </div>
          </div>
        </div>
      </div>

      <!-- Roles Table -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Kullanıcı</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Proje</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Rol</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Tarih Aralığı</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Durum</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Atayan</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-700 uppercase tracking-wider">İşlemler</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="role in roles.data" :key="role.id" class="hover:bg-gray-50 transition-colors">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 h-10 w-10">
                      <div class="h-10 w-10 rounded-full bg-purple-100 flex items-center justify-center">
                        <span class="text-purple-600 font-medium text-sm">{{ role.user?.name?.substring(0, 2).toUpperCase() }}</span>
                      </div>
                    </div>
                    <div class="ml-4">
                      <div class="text-sm font-medium text-gray-900">{{ role.user?.name }}</div>
                      <div class="text-sm text-gray-500">{{ role.user?.email }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-gray-900">{{ role.project?.name }}</div>
                  <div class="text-sm text-gray-500">{{ role.project?.project_code }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="getRoleBadgeClass(role.role)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                    {{ getRoleLabel(role.role) }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  <div v-if="role.start_date || role.end_date">
                    {{ formatDate(role.start_date) }} - {{ formatDate(role.end_date) || 'Devam ediyor' }}
                  </div>
                  <div v-else class="text-gray-400">Süresiz</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span v-if="role.is_active" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                    Aktif
                  </span>
                  <span v-else class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                    Pasif
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ role.assigned_by?.name || '-' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                  <Link :href="route('user-project-roles.edit', role.id)" class="text-purple-600 hover:text-purple-900 transition-colors">
                    Düzenle
                  </Link>
                  <button @click="deleteRole(role.id)" class="text-red-600 hover:text-red-900 transition-colors">
                    Sil
                  </button>
                </td>
              </tr>
              <tr v-if="!roles.data || roles.data.length === 0">
                <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                  <div class="flex flex-col items-center">
                    <svg class="w-12 h-12 text-gray-400 mb-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                    </svg>
                    <p class="text-lg font-medium">Rol ataması bulunamadı</p>
                    <p class="text-sm text-gray-400 mt-1">Yeni bir rol ataması oluşturun</p>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="roles.data && roles.data.length > 0" class="bg-gray-50 px-6 py-4 border-t border-gray-200">
          <div class="flex items-center justify-between">
            <div class="text-sm text-gray-700">
              Gösterilen: <span class="font-medium">{{ roles.from }}</span> - <span class="font-medium">{{ roles.to }}</span> / <span class="font-medium">{{ roles.total }}</span>
            </div>
            <div class="flex space-x-2">
              <component
                v-for="link in roles.links"
                :key="link.label"
                :is="link.url ? Link : 'span'"
                :href="link.url || undefined"
                :class="[
                  'px-3 py-2 rounded-lg text-sm font-medium transition-colors',
                  link.active
                    ? 'bg-purple-600 text-white'
                    : link.url
                    ? 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-300'
                    : 'bg-gray-100 text-gray-400 cursor-not-allowed'
                ]"
                v-html="link.label"
              />
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  roles: Object,
  statistics: Object,
  filters: Object,
  users: Array,
  projects: Array,
})

const filterForm = reactive({
  user_id: props.filters?.user_id || null,
  project_id: props.filters?.project_id || null,
  role: props.filters?.role || null,
  is_active: props.filters?.is_active || null,
})

function applyFilters() {
  router.get(route('user-project-roles.index'), filterForm, {
    preserveState: true,
    preserveScroll: true,
  })
}

function getRoleLabel(role) {
  const labels = {
    project_manager: 'Proje Yöneticisi',
    site_manager: 'Şantiye Şefi',
    engineer: 'Mühendis',
    foreman: 'Forman',
    viewer: 'Görüntüleyici',
    inspector: 'Denetçi',
    safety_officer: 'İSG Uzmanı',
  }
  return labels[role] || role
}

function getRoleBadgeClass(role) {
  const classes = {
    project_manager: 'bg-purple-100 text-purple-800',
    site_manager: 'bg-blue-100 text-blue-800',
    engineer: 'bg-cyan-100 text-cyan-800',
    foreman: 'bg-indigo-100 text-indigo-800',
    viewer: 'bg-gray-100 text-gray-800',
    inspector: 'bg-yellow-100 text-yellow-800',
    safety_officer: 'bg-red-100 text-red-800',
  }
  return classes[role] || 'bg-gray-100 text-gray-800'
}

function formatDate(date) {
  if (!date) return null
  return new Date(date).toLocaleDateString('tr-TR')
}

function deleteRole(id) {
  if (confirm('Bu rol atamasını silmek istediğinize emin misiniz?')) {
    router.delete(route('user-project-roles.destroy', id))
  }
}
</script>
