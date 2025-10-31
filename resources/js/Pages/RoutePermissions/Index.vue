<template>
  <AppLayout title="Route Yetkileri" :full-width="true">
    <!-- Full-width header -->
    <template #fullWidthHeader>
      <div class="bg-gradient-to-r from-orange-600 via-red-600 to-pink-600 border-b border-orange-900/20 w-full">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
          <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <!-- Title and Stats -->
            <div class="flex-1">
              <div class="flex items-center space-x-4 mb-4">
                <div class="flex-shrink-0 w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm">
                  <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                  </svg>
                </div>
                <div>
                  <h1 class="text-2xl lg:text-3xl font-bold text-white">Route Yetkileri</h1>
                  <p class="text-orange-100 text-sm mt-1">Route bazlı erişim kontrolü ve yetki yönetimi</p>
                </div>
              </div>

              <!-- Statistics -->
              <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white/10 backdrop-blur-sm rounded-lg p-3 border border-white/20">
                  <div class="text-2xl font-bold text-white">{{ statistics.total_routes }}</div>
                  <div class="text-xs text-orange-100 mt-1">Toplam Route</div>
                </div>
                <div class="bg-white/10 backdrop-blur-sm rounded-lg p-3 border border-white/20">
                  <div class="text-2xl font-bold text-white">{{ statistics.active_routes }}</div>
                  <div class="text-xs text-orange-100 mt-1">Aktif Route</div>
                </div>
                <div class="bg-white/10 backdrop-blur-sm rounded-lg p-3 border border-white/20">
                  <div class="text-2xl font-bold text-white">{{ statistics.protected_routes }}</div>
                  <div class="text-xs text-orange-100 mt-1">Korumalı Route</div>
                </div>
                <div class="bg-white/10 backdrop-blur-sm rounded-lg p-3 border border-white/20">
                  <div class="text-2xl font-bold text-white">{{ statistics.public_routes }}</div>
                  <div class="text-xs text-orange-100 mt-1">Public Route</div>
                </div>
              </div>
            </div>

            <!-- Actions -->
            <div class="flex-shrink-0 space-y-2">
              <button
                @click="syncRoutes"
                :disabled="syncing"
                class="w-full lg:w-auto px-4 py-2 bg-white text-orange-600 rounded-lg font-medium hover:bg-orange-50 transition-all flex items-center justify-center space-x-2 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <svg class="w-5 h-5" :class="{ 'animate-spin': syncing }" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                </svg>
                <span>{{ syncing ? 'Senkronize Ediliyor...' : 'Route\'ları Sync Et' }}</span>
              </button>
              <button
                v-if="selectedPermissions.length > 0"
                @click="showBulkUpdateModal = true"
                class="w-full lg:w-auto px-4 py-2 bg-emerald-500 text-white rounded-lg font-medium hover:bg-emerald-600 transition-all flex items-center justify-center space-x-2"
              >
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                </svg>
                <span>Toplu Güncelle ({{ selectedPermissions.length }})</span>
              </button>
            </div>
          </div>
        </div>

        <!-- Filters -->
        <div class="bg-black/10 border-t border-white/10">
          <div class="w-full px-4 sm:px-6 lg:px-8 py-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
              <!-- Search -->
              <div>
                <input
                  v-model="localFilters.search"
                  @input="debouncedFilter"
                  type="text"
                  placeholder="Route ara..."
                  class="w-full px-3 py-2 bg-white/10 border border-white/20 rounded-lg text-white placeholder-orange-200 focus:ring-2 focus:ring-white focus:border-transparent"
                />
              </div>

              <!-- Module Filter -->
              <div>
                <select
                  v-model="localFilters.module"
                  @change="applyFilters"
                  class="w-full px-3 py-2 bg-white/10 border border-white/20 rounded-lg text-white focus:ring-2 focus:ring-white focus:border-transparent"
                >
                  <option value="">Tüm Modüller</option>
                  <option v-for="module in modules" :key="module" :value="module" class="text-gray-900">
                    {{ module }}
                  </option>
                </select>
              </div>

              <!-- Active Filter -->
              <div>
                <select
                  v-model="localFilters.is_active"
                  @change="applyFilters"
                  class="w-full px-3 py-2 bg-white/10 border border-white/20 rounded-lg text-white focus:ring-2 focus:ring-white focus:border-transparent"
                >
                  <option value="">Tüm Durumlar</option>
                  <option value="1" class="text-gray-900">Aktif</option>
                  <option value="0" class="text-gray-900">Pasif</option>
                </select>
              </div>

              <!-- Project Access Filter -->
              <div>
                <select
                  v-model="localFilters.requires_project_access"
                  @change="applyFilters"
                  class="w-full px-3 py-2 bg-white/10 border border-white/20 rounded-lg text-white focus:ring-2 focus:ring-white focus:border-transparent"
                >
                  <option value="">Tüm Erişim Tipleri</option>
                  <option value="1" class="text-gray-900">Proje Erişimi Gerekli</option>
                  <option value="0" class="text-gray-900">Genel Erişim</option>
                </select>
              </div>
            </div>
          </div>
        </div>
      </div>
    </template>

    <!-- Main Content -->
    <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
      <!-- Table -->
      <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-3 text-left">
                  <input
                    type="checkbox"
                    @change="toggleSelectAll"
                    :checked="allSelected"
                    class="h-4 w-4 text-orange-600 focus:ring-orange-500 border-gray-300 rounded"
                  />
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Route</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Modül</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">İzinli Roller</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Durum</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">İşlemler</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="permission in permissions.data" :key="permission.id" class="hover:bg-gray-50">
                <!-- Checkbox -->
                <td class="px-4 py-4">
                  <input
                    type="checkbox"
                    v-model="selectedPermissions"
                    :value="permission.id"
                    class="h-4 w-4 text-orange-600 focus:ring-orange-500 border-gray-300 rounded"
                  />
                </td>

                <!-- Route Name -->
                <td class="px-6 py-4">
                  <div class="text-sm font-semibold text-gray-900">{{ permission.display_name || permission.route_name }}</div>
                  <div class="text-xs text-gray-500 mt-0.5 font-mono">{{ permission.route_name }}</div>
                  <div v-if="permission.description" class="text-xs text-gray-500 mt-1">{{ permission.description }}</div>
                  <div class="text-xs text-gray-400 mt-0.5">{{ permission.uri }}</div>
                </td>

                <!-- Module -->
                <td class="px-6 py-4">
                  <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-700">
                    {{ permission.module }}
                  </span>
                </td>

                <!-- Allowed Roles -->
                <td class="px-6 py-4">
                  <div class="flex flex-wrap gap-1">
                    <span
                      v-if="permission.is_public"
                      class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-700"
                    >
                      Public
                    </span>
                    <span
                      v-for="role in permission.allowed_roles"
                      :key="role"
                      class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-700"
                    >
                      {{ getRoleLabel(role) }}
                    </span>
                    <span
                      v-if="!permission.is_public && (!permission.allowed_roles || permission.allowed_roles.length === 0)"
                      class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-700"
                    >
                      Erişim Yok
                    </span>
                  </div>
                  <div v-if="permission.requires_project_access" class="mt-1">
                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-700">
                      Proje Erişimi Gerekli
                    </span>
                  </div>
                </td>

                <!-- Status -->
                <td class="px-6 py-4">
                  <span
                    :class="permission.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700'"
                    class="px-2 py-1 text-xs font-semibold rounded-full"
                  >
                    {{ permission.is_active ? 'Aktif' : 'Pasif' }}
                  </span>
                </td>

                <!-- Actions -->
                <td class="px-6 py-4">
                  <button
                    @click="editPermission(permission)"
                    class="text-orange-600 hover:text-orange-900 text-sm font-medium"
                  >
                    Düzenle
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
          <div class="flex items-center justify-between">
            <div class="text-sm text-gray-700">
              <span class="font-medium">{{ permissions.from }}</span>
              -
              <span class="font-medium">{{ permissions.to }}</span>
              /
              <span class="font-medium">{{ permissions.total }}</span>
              kayıt
            </div>
            <div class="flex space-x-2">
              <component
                v-for="link in permissions.links"
                :key="link.label"
                :is="link.url ? Link : 'span'"
                :href="link.url || undefined"
                v-html="link.label"
                :class="[
                  'px-3 py-1 text-sm rounded',
                  link.active
                    ? 'bg-orange-600 text-white'
                    : link.url
                    ? 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-300'
                    : 'bg-gray-100 text-gray-400 cursor-not-allowed'
                ]"
              />
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Edit Permission Modal -->
    <Modal :show="showEditModal" @close="showEditModal = false" size="2xl">
      <div class="p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Route Yetkilerini Düzenle</h3>

        <div v-if="editingPermission" class="space-y-4">
          <!-- Route Info -->
          <div class="bg-gray-50 p-4 rounded-lg">
            <div class="text-sm font-mono text-gray-900">{{ editingPermission.route_name }}</div>
            <div class="text-xs text-gray-500 mt-1">{{ editingPermission.uri }}</div>
          </div>

          <!-- Is Public -->
          <div class="flex items-center">
            <input
              v-model="editForm.is_public"
              type="checkbox"
              class="h-4 w-4 text-orange-600 focus:ring-orange-500 border-gray-300 rounded"
            />
            <label class="ml-2 text-sm text-gray-700">Public (Herkes erişebilir)</label>
          </div>

          <!-- Allowed Roles -->
          <div v-if="!editForm.is_public">
            <label class="block text-sm font-medium text-gray-700 mb-2">İzinli Roller</label>
            <div class="grid grid-cols-2 gap-2">
              <div v-for="role in availableRoles" :key="role.value" class="flex items-center">
                <input
                  v-model="editForm.allowed_roles"
                  type="checkbox"
                  :value="role.value"
                  class="h-4 w-4 text-orange-600 focus:ring-orange-500 border-gray-300 rounded"
                />
                <label class="ml-2 text-sm text-gray-700">{{ role.label }}</label>
              </div>
            </div>
          </div>

          <!-- Requires Project Access -->
          <div class="flex items-center">
            <input
              v-model="editForm.requires_project_access"
              type="checkbox"
              class="h-4 w-4 text-orange-600 focus:ring-orange-500 border-gray-300 rounded"
            />
            <label class="ml-2 text-sm text-gray-700">Proje Erişimi Gerekli</label>
          </div>

          <!-- Is Active -->
          <div class="flex items-center">
            <input
              v-model="editForm.is_active"
              type="checkbox"
              class="h-4 w-4 text-orange-600 focus:ring-orange-500 border-gray-300 rounded"
            />
            <label class="ml-2 text-sm text-gray-700">Aktif</label>
          </div>

          <!-- Description -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Açıklama</label>
            <textarea
              v-model="editForm.description"
              rows="2"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
              placeholder="Route açıklaması (opsiyonel)"
            ></textarea>
          </div>

          <!-- Actions -->
          <div class="flex justify-end space-x-3 pt-4">
            <button
              @click="showEditModal = false"
              type="button"
              class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50"
            >
              İptal
            </button>
            <button
              @click="updatePermission"
              type="button"
              class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700"
            >
              Güncelle
            </button>
          </div>
        </div>
      </div>
    </Modal>

    <!-- Bulk Update Modal -->
    <Modal :show="showBulkUpdateModal" @close="showBulkUpdateModal = false" size="2xl">
      <div class="p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Toplu Yetki Güncellemesi</h3>

        <div class="space-y-4">
          <p class="text-sm text-gray-600">{{ selectedPermissions.length }} route için toplu güncelleme yapılacak.</p>

          <!-- Allowed Roles -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">İzinli Roller</label>
            <div class="grid grid-cols-2 gap-2">
              <div v-for="role in availableRoles" :key="role.value" class="flex items-center">
                <input
                  v-model="bulkForm.allowed_roles"
                  type="checkbox"
                  :value="role.value"
                  class="h-4 w-4 text-orange-600 focus:ring-orange-500 border-gray-300 rounded"
                />
                <label class="ml-2 text-sm text-gray-700">{{ role.label }}</label>
              </div>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex justify-end space-x-3 pt-4">
            <button
              @click="showBulkUpdateModal = false"
              type="button"
              class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50"
            >
              İptal
            </button>
            <button
              @click="bulkUpdatePermissions"
              type="button"
              class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700"
            >
              Toplu Güncelle
            </button>
          </div>
        </div>
      </div>
    </Modal>
  </AppLayout>
</template>

<script setup>
import { ref, computed, reactive } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Modal from '@/Components/UI/Modal.vue'

const props = defineProps({
  permissions: {
    type: Object,
    required: true
  },
  statistics: {
    type: Object,
    required: true
  },
  modules: {
    type: Array,
    required: true
  },
  availableRoles: {
    type: Array,
    required: true
  },
  filters: {
    type: Object,
    default: () => ({})
  }
})

const localFilters = reactive({
  search: props.filters.search || '',
  module: props.filters.module || '',
  is_active: props.filters.is_active || '',
  requires_project_access: props.filters.requires_project_access || ''
})

const selectedPermissions = ref([])
const showEditModal = ref(false)
const showBulkUpdateModal = ref(false)
const editingPermission = ref(null)
const syncing = ref(false)

const editForm = reactive({
  allowed_roles: [],
  requires_project_access: false,
  is_public: false,
  is_active: true,
  description: ''
})

const bulkForm = reactive({
  allowed_roles: []
})

const allSelected = computed(() => {
  return props.permissions.data.length > 0 &&
    selectedPermissions.value.length === props.permissions.data.length
})

const toggleSelectAll = () => {
  if (allSelected.value) {
    selectedPermissions.value = []
  } else {
    selectedPermissions.value = props.permissions.data.map(p => p.id)
  }
}

let filterTimeout
const debouncedFilter = () => {
  clearTimeout(filterTimeout)
  filterTimeout = setTimeout(() => {
    applyFilters()
  }, 500)
}

const applyFilters = () => {
  router.get(route('route-permissions.index'), localFilters, {
    preserveState: true,
    preserveScroll: true
  })
}

const getRoleLabel = (roleValue) => {
  const role = props.availableRoles.find(r => r.value === roleValue)
  return role ? role.label : roleValue
}

const editPermission = (permission) => {
  editingPermission.value = permission
  editForm.allowed_roles = permission.allowed_roles || []
  editForm.requires_project_access = permission.requires_project_access
  editForm.is_public = permission.is_public
  editForm.is_active = permission.is_active
  editForm.description = permission.description || ''
  showEditModal.value = true
}

const updatePermission = () => {
  router.put(route('route-permissions.update', editingPermission.value.id), editForm, {
    onSuccess: () => {
      showEditModal.value = false
      editingPermission.value = null
    }
  })
}

const bulkUpdatePermissions = () => {
  router.post(route('route-permissions.bulk-update'), {
    route_ids: selectedPermissions.value,
    allowed_roles: bulkForm.allowed_roles
  }, {
    onSuccess: () => {
      showBulkUpdateModal.value = false
      selectedPermissions.value = []
      bulkForm.allowed_roles = []
    }
  })
}

const syncRoutes = () => {
  syncing.value = true
  router.post(route('route-permissions.sync'), {}, {
    onFinish: () => {
      syncing.value = false
    }
  })
}
</script>
