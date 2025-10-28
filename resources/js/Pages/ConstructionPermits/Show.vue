<template>
  <AppLayout :title="`Ruhsat #${permit.permit_number}`" :full-width="true">
    <!-- Header -->
    <template #fullWidthHeader>
      <div class="bg-gradient-to-r from-amber-600 via-amber-700 to-orange-800 border-b border-amber-900/20 w-full">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
          <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <!-- Header Info -->
            <div class="flex-1 min-w-0">
              <div class="flex items-center space-x-4 mb-3">
                <div class="flex-shrink-0 w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm">
                  <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" />
                  </svg>
                </div>
                <div>
                  <h1 class="text-2xl lg:text-3xl font-bold text-white">{{ permit.permit_number }}</h1>
                  <p class="text-amber-100 text-sm mt-1">{{ permit.permit_type_label }}</p>
                </div>
              </div>

              <!-- Status Badge -->
              <div class="flex items-center space-x-3 flex-wrap gap-2">
                <span :class="permit.status_badge?.class" class="px-4 py-1.5 text-sm font-semibold rounded-full border">
                  {{ permit.status_badge?.text }}
                </span>
                <span v-if="permit.is_expiring_soon && !permit.is_expired" class="px-4 py-1.5 text-sm bg-orange-100 text-orange-800 rounded-full border-2 border-orange-300">
                  ⚠️ {{ permit.days_until_expiry }} gün kaldı
                </span>
                <span v-if="permit.is_expired" class="px-4 py-1.5 text-sm bg-red-100 text-red-800 rounded-full border-2 border-red-300">
                  ❌ Süresi doldu
                </span>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex-shrink-0 flex items-center space-x-3">
              <Link
                :href="route('construction-permits.edit', permit.id)"
                class="inline-flex items-center px-4 py-2 bg-white text-amber-600 text-sm font-medium rounded-lg hover:bg-amber-50 transition-all duration-200"
              >
                <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                </svg>
                Düzenle
              </Link>
              <Link
                :href="route('construction-permits.index')"
                class="inline-flex items-center px-4 py-2 bg-white/10 backdrop-blur-sm border border-white/30 text-white text-sm font-medium rounded-lg hover:bg-white/20 transition-all duration-200"
              >
                <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
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
                  <Link :href="route('dashboard')" class="text-amber-100 hover:text-white transition-colors">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                    </svg>
                  </Link>
                </li>
                <li class="flex items-center">
                  <svg class="h-3 w-3 text-amber-200 mx-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                  </svg>
                  <Link :href="route('construction-permits.index')" class="text-amber-100 hover:text-white text-sm">Ruhsatlar</Link>
                </li>
                <li class="flex items-center">
                  <svg class="h-3 w-3 text-amber-200 mx-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                  </svg>
                  <span class="text-white font-medium text-sm">{{ permit.permit_number }}</span>
                </li>
              </ol>
            </div>
          </div>
        </div>
      </div>
    </template>

    <!-- Main Content -->
    <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column - Main Details -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Ruhsat Bilgileri -->
          <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
              <h3 class="text-lg font-medium text-gray-900">Ruhsat Bilgileri</h3>
            </div>
            <div class="px-6 py-6">
              <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                <div>
                  <dt class="text-sm font-medium text-gray-500">Ruhsat Numarası</dt>
                  <dd class="mt-1 text-sm text-gray-900 font-mono">{{ permit.permit_number }}</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">Ruhsat Türü</dt>
                  <dd class="mt-1">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-amber-100 text-amber-800">
                      {{ permit.permit_type_label }}
                    </span>
                  </dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">Veren Kurum</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ permit.issuing_authority || '-' }}</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">İmar Durumu</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ permit.zoning_status || '-' }}</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">Başvuru Tarihi</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ formatDate(permit.application_date) }}</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">Onay Tarihi</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ formatDate(permit.approval_date) }}</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">Son Geçerlilik Tarihi</dt>
                  <dd class="mt-1">
                    <span v-if="permit.expiry_date" :class="permit.is_expired ? 'text-red-600' : permit.is_expiring_soon ? 'text-orange-600' : 'text-gray-900'" class="text-sm font-medium">
                      {{ formatDate(permit.expiry_date) }}
                    </span>
                    <span v-else class="text-sm text-gray-900">-</span>
                  </dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">Durum</dt>
                  <dd class="mt-1">
                    <span :class="permit.status_badge.class" class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium border">
                      {{ permit.status_badge.text }}
                    </span>
                  </dd>
                </div>
                <div v-if="permit.notes" class="md:col-span-2">
                  <dt class="text-sm font-medium text-gray-500 mb-2">Notlar</dt>
                  <dd class="mt-1 text-sm text-gray-700 bg-gray-50 rounded-lg p-4 whitespace-pre-wrap">{{ permit.notes }}</dd>
                </div>
              </dl>
            </div>
          </div>

          <!-- Belgeler -->
          <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
              <h3 class="text-lg font-medium text-gray-900">Belgeler</h3>
              <button
                @click="showDocumentModal = true"
                class="inline-flex items-center px-3 py-1.5 bg-amber-600 text-white text-sm font-medium rounded-lg hover:bg-amber-700"
              >
                <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Belge Yükle
              </button>
            </div>
            <div class="px-6 py-6">
              <div v-if="!permit.documents || permit.documents.length === 0" class="text-center py-8 text-gray-500">
                <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                </svg>
                <p>Henüz belge eklenmemiş</p>
              </div>
              <div v-else class="space-y-3">
                <div
                  v-for="(doc, index) in permit.documents"
                  :key="index"
                  class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors"
                >
                  <div class="flex items-center space-x-3 flex-1 min-w-0">
                    <div class="flex-shrink-0">
                      <svg class="h-8 w-8 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                      </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                      <p class="text-sm font-medium text-gray-900 truncate">{{ doc.name }}</p>
                      <p class="text-xs text-gray-500">{{ formatFileSize(doc.size) }} • {{ formatDate(doc.uploaded_at) }}</p>
                    </div>
                  </div>
                  <div class="flex items-center space-x-2 ml-4">
                    <a
                      :href="`/storage/${doc.path}`"
                      target="_blank"
                      class="inline-flex items-center px-3 py-1.5 bg-white text-gray-700 text-xs font-medium rounded border border-gray-300 hover:bg-gray-50"
                    >
                      <svg class="h-3.5 w-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                      </svg>
                      İndir
                    </a>
                    <button
                      @click="deleteDocument(index)"
                      class="inline-flex items-center px-3 py-1.5 bg-red-50 text-red-600 text-xs font-medium rounded border border-red-200 hover:bg-red-100"
                    >
                      <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                      </svg>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Right Column - Sidebar -->
        <div class="space-y-6">
          <!-- Proje Bilgileri -->
          <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
              <h3 class="text-lg font-medium text-gray-900">Proje Bilgileri</h3>
            </div>
            <div class="px-6 py-6">
              <Link :href="route('projects.show', permit.project.id)" class="block hover:bg-gray-50 -mx-6 -my-6 px-6 py-6 transition-colors">
                <div class="space-y-3">
                  <div>
                    <p class="text-sm font-medium text-gray-500">Proje Adı</p>
                    <p class="mt-1 text-base font-semibold text-amber-600">{{ permit.project.name }}</p>
                  </div>
                  <div>
                    <p class="text-sm font-medium text-gray-500">Proje Kodu</p>
                    <p class="mt-1 text-sm text-gray-900">{{ permit.project.project_code }}</p>
                  </div>
                  <div v-if="permit.project.location">
                    <p class="text-sm font-medium text-gray-500">Lokasyon</p>
                    <p class="mt-1 text-sm text-gray-900">{{ permit.project.location }}</p>
                  </div>
                  <div v-if="permit.project.city">
                    <p class="text-sm font-medium text-gray-500">Şehir</p>
                    <p class="mt-1 text-sm text-gray-900">{{ permit.project.city }}</p>
                  </div>
                </div>
              </Link>
            </div>
          </div>

          <!-- Sistem Bilgileri -->
          <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
              <h3 class="text-lg font-medium text-gray-900">Sistem Bilgileri</h3>
            </div>
            <div class="px-6 py-6 space-y-3">
              <div>
                <p class="text-sm font-medium text-gray-500">Oluşturan</p>
                <p class="mt-1 text-sm text-gray-900">{{ permit.creator?.name || '-' }}</p>
              </div>
              <div>
                <p class="text-sm font-medium text-gray-500">Oluşturma Tarihi</p>
                <p class="mt-1 text-sm text-gray-900">{{ formatDateTime(permit.created_at) }}</p>
              </div>
              <div v-if="permit.updater">
                <p class="text-sm font-medium text-gray-500">Son Güncelleyen</p>
                <p class="mt-1 text-sm text-gray-900">{{ permit.updater.name }}</p>
              </div>
              <div>
                <p class="text-sm font-medium text-gray-500">Güncelleme Tarihi</p>
                <p class="mt-1 text-sm text-gray-900">{{ formatDateTime(permit.updated_at) }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Document Upload Modal -->
    <form @submit.prevent="uploadDocument" v-if="showDocumentModal" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex min-h-full items-center justify-center p-4">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showDocumentModal = false"></div>

        <div class="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6">
          <div>
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Belge Yükle</h3>

            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Belge Adı</label>
                <input v-model="documentForm.document_name" type="text" class="w-full rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500" placeholder="Belge adı (opsiyonel)">
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Dosya *</label>
                <input @change="handleFileChange" type="file" accept=".pdf,.jpg,.jpeg,.png" class="w-full rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500" required>
                <p class="mt-1 text-xs text-gray-500">PDF, JPG, PNG (Max 10MB)</p>
              </div>
            </div>
          </div>

          <div class="mt-5 sm:mt-6 sm:grid sm:grid-flow-row-dense sm:grid-cols-2 sm:gap-3">
            <button type="submit" :disabled="documentFormProcessing" class="inline-flex w-full justify-center rounded-md bg-amber-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-amber-500 sm:col-start-2 disabled:opacity-50">
              <span v-if="!documentFormProcessing">Yükle</span>
              <span v-else>Yükleniyor...</span>
            </button>
            <button type="button" @click="showDocumentModal = false" class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:col-start-1 sm:mt-0">
              İptal
            </button>
          </div>
        </div>
      </div>
    </form>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  permit: Object,
})

// Document Modal
const showDocumentModal = ref(false)
const documentFormProcessing = ref(false)
const documentForm = ref({
  document: null,
  document_name: '',
})

const handleFileChange = (event) => {
  documentForm.value.document = event.target.files[0]
}

const uploadDocument = () => {
  if (!documentForm.value.document) return

  documentFormProcessing.value = true

  const formData = new FormData()
  formData.append('document', documentForm.value.document)
  if (documentForm.value.document_name) {
    formData.append('document_name', documentForm.value.document_name)
  }

  router.post(
    route('construction-permits.documents.upload', props.permit.id),
    formData,
    {
      onSuccess: () => {
        showDocumentModal.value = false
        documentForm.value = { document: null, document_name: '' }
      },
      onFinish: () => {
        documentFormProcessing.value = false
      },
    }
  )
}

const deleteDocument = (index) => {
  if (!confirm('Bu belgeyi silmek istediğinize emin misiniz?')) return

  router.delete(route('construction-permits.documents.delete', {
    constructionPermit: props.permit.id,
    index: index
  }))
}

const formatDate = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('tr-TR', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

const formatDateTime = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleString('tr-TR', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const formatFileSize = (bytes) => {
  if (!bytes) return '-'
  if (bytes < 1024) return bytes + ' B'
  if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(2) + ' KB'
  return (bytes / (1024 * 1024)).toFixed(2) + ' MB'
}
</script>
