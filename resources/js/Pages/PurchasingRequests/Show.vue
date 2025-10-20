<template>
  <AppLayout
    title="Satınalma Talebi Detayı - SPT İnşaat Puantaj Sistemi"
    :full-width="true"
  >
    <!-- Full-width header -->
    <template #fullWidthHeader>
      <div class="bg-gradient-to-r from-blue-600 via-blue-700 to-blue-800 border-b border-blue-900/20 w-full">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
          <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <!-- Header Info -->
            <div class="flex-1 min-w-0">
              <div class="flex items-center space-x-4 mb-3">
                <div class="flex-shrink-0 w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm">
                  <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                  </svg>
                </div>
                <div>
                  <h1 class="text-2xl lg:text-3xl font-bold text-white">{{ purchasingRequest.request_code }}</h1>
                  <p class="text-blue-100 text-sm mt-1">{{ purchasingRequest.title }}</p>
                </div>
              </div>

              <!-- Status Badge -->
              <div class="flex items-center space-x-3">
                <span
                  :class="getStatusClass(purchasingRequest.status)"
                  class="px-4 py-1.5 text-sm font-semibold rounded-full border-2"
                >
                  {{ getStatusText(purchasingRequest.status) }}
                </span>
                <span
                  :class="getUrgencyClass(purchasingRequest.urgency)"
                  class="px-4 py-1.5 text-sm font-semibold rounded-full border-2"
                >
                  {{ getUrgencyText(purchasingRequest.urgency) }}
                </span>
              </div>
            </div>

            <!-- Action Button -->
            <div class="flex-shrink-0">
              <Link
                :href="route('purchasing-requests.index')"
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
                <li class="flex items-center">
                  <svg class="h-3 w-3 text-blue-200 mx-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                  </svg>
                  <Link :href="route('purchasing-requests.index')" class="text-xs font-medium text-blue-100 hover:text-white">
                    Satınalma Talepleri
                  </Link>
                </li>
                <li class="flex items-center">
                  <svg class="h-3 w-3 text-blue-200 mx-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                  </svg>
                  <span class="text-xs font-medium text-white">{{ purchasingRequest.request_code }}</span>
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
        <!-- Ana İçerik (Sol - 2/3) -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Temel Bilgiler -->
          <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
              <h3 class="text-lg font-medium text-gray-900">Temel Bilgiler</h3>
            </div>

            <div class="p-6">
              <div class="grid grid-cols-2 gap-6">
                <div>
                  <label class="block text-sm font-medium text-gray-500 mb-1">Proje</label>
                  <p class="text-base font-medium text-gray-900">{{ purchasingRequest.project?.name || '-' }}</p>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-500 mb-1">Departman</label>
                  <p class="text-base font-medium text-gray-900">{{ purchasingRequest.department?.name || '-' }}</p>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-500 mb-1">Kategori</label>
                  <p class="text-base font-medium text-gray-900">{{ getCategoryText(purchasingRequest.category) }}</p>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-500 mb-1">Talep Eden</label>
                  <div class="flex items-center space-x-2">
                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                      <span class="text-blue-600 font-medium text-xs">
                        {{ purchasingRequest.requested_by?.name?.charAt(0) || '?' }}
                      </span>
                    </div>
                    <p class="text-base font-medium text-gray-900">{{ purchasingRequest.requested_by?.name || '-' }}</p>
                  </div>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-500 mb-1">İhtiyaç Tarihi</label>
                  <p class="text-base font-medium text-gray-900">{{ formatDate(purchasingRequest.required_date) }}</p>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-500 mb-1">Oluşturulma Tarihi</label>
                  <p class="text-base font-medium text-gray-900">{{ formatDate(purchasingRequest.created_at) }}</p>
                </div>

                <div v-if="purchasingRequest.description" class="col-span-2">
                  <label class="block text-sm font-medium text-gray-500 mb-1">Açıklama</label>
                  <p class="text-base text-gray-700 bg-gray-50 p-4 rounded-lg">{{ purchasingRequest.description }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Kalemler -->
          <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
              <h3 class="text-lg font-medium text-gray-900">Talep Kalemleri</h3>
            </div>

            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Malzeme</th>
                    <th class="px-6 py-4 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Miktar</th>
                    <th class="px-6 py-4 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Birim Fiyat</th>
                    <th class="px-6 py-4 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Toplam</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="(item, index) in purchasingRequest.items" :key="item.id" class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="flex items-center justify-center w-8 h-8 bg-blue-100 rounded-lg">
                        <span class="text-blue-600 font-bold text-sm">{{ index + 1 }}</span>
                      </div>
                    </td>
                    <td class="px-6 py-4">
                      <div class="text-sm font-medium text-gray-900">{{ item.item_name }}</div>
                      <div v-if="item.description" class="text-sm text-gray-500 mt-1">{{ item.description }}</div>
                      <div v-if="item.category" class="text-xs text-gray-400 mt-1">{{ item.category }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-900">
                      {{ item.quantity }} {{ item.unit }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-900">
                      {{ formatCurrency(item.estimated_unit_price) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-gray-900">
                      {{ formatCurrency(item.estimated_total_price) }}
                    </td>
                  </tr>
                </tbody>
                <tfoot class="bg-gradient-to-r from-green-50 to-emerald-50 border-t-2 border-green-200">
                  <tr>
                    <td colspan="4" class="px-6 py-4 text-right text-sm font-medium text-gray-900">
                      Genel Toplam:
                    </td>
                    <td class="px-6 py-4 text-right text-2xl font-bold text-green-600">
                      {{ formatCurrency(purchasingRequest.estimated_total) }}
                    </td>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>

          <!-- Onay Notları -->
          <div v-if="purchasingRequest.supervisor_notes || purchasingRequest.manager_notes || purchasingRequest.rejection_reason" class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
              <h3 class="text-lg font-medium text-gray-900">Onay Notları</h3>
            </div>

            <div class="p-6 space-y-4">
              <div v-if="purchasingRequest.supervisor_notes" class="border-l-4 border-blue-500 bg-blue-50 p-4 rounded-r-lg">
                <label class="block text-sm font-medium text-blue-900 mb-2">Şef Notu</label>
                <p class="text-sm text-blue-800">{{ purchasingRequest.supervisor_notes }}</p>
              </div>

              <div v-if="purchasingRequest.manager_notes" class="border-l-4 border-green-500 bg-green-50 p-4 rounded-r-lg">
                <label class="block text-sm font-medium text-green-900 mb-2">Yönetici Notu</label>
                <p class="text-sm text-green-800">{{ purchasingRequest.manager_notes }}</p>
              </div>

              <div v-if="purchasingRequest.rejection_reason" class="border-l-4 border-red-500 bg-red-50 p-4 rounded-r-lg">
                <label class="block text-sm font-medium text-red-900 mb-2">Red Sebebi</label>
                <p class="text-sm text-red-800">{{ purchasingRequest.rejection_reason }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Yan Panel (Sağ - 1/3) -->
        <div class="space-y-6">
          <!-- İşlemler -->
          <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
              <h3 class="text-lg font-medium text-gray-900">İşlemler</h3>
            </div>

            <div class="p-6 space-y-3">
              <!-- Taslak durumunda -->
              <template v-if="purchasingRequest.status === 'draft'">
                <Link
                  :href="route('purchasing-requests.edit', purchasingRequest.id)"
                  class="w-full inline-flex justify-center items-center px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors"
                >
                  <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                  </svg>
                  Düzenle
                </Link>
                <button
                  @click="submitForApproval"
                  class="w-full inline-flex justify-center items-center px-4 py-3 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors"
                >
                  <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
                  </svg>
                  Onaya Gönder
                </button>
              </template>

              <!-- Onay bekliyor durumunda -->
              <template v-if="purchasingRequest.status === 'pending' && canApprove">
                <button
                  @click="showApprovalModal('supervisor')"
                  class="w-full inline-flex justify-center items-center px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors"
                >
                  <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                  </svg>
                  Şef Onayı Ver
                </button>
                <button
                  @click="showApprovalModal('manager')"
                  class="w-full inline-flex justify-center items-center px-4 py-3 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors"
                >
                  <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                  </svg>
                  Yönetici Onayı Ver
                </button>
                <button
                  @click="showRejectModal"
                  class="w-full inline-flex justify-center items-center px-4 py-3 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-colors"
                >
                  <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                  </svg>
                  Reddet
                </button>
              </template>

              <!-- İptal butonu -->
              <button
                v-if="['draft', 'pending', 'approved'].includes(purchasingRequest.status)"
                @click="cancelRequest"
                class="w-full inline-flex justify-center items-center px-4 py-3 border-2 border-orange-300 text-orange-700 hover:bg-orange-50 text-sm font-medium rounded-lg transition-colors"
              >
                <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" />
                </svg>
                Talebi İptal Et
              </button>

              <!-- Sil butonu (sadece draft ve rejected için) -->
              <button
                v-if="['draft', 'rejected'].includes(purchasingRequest.status) && canDelete"
                @click="deleteRequest"
                class="w-full inline-flex justify-center items-center px-4 py-3 border-2 border-gray-300 text-gray-700 hover:bg-gray-50 text-sm font-medium rounded-lg transition-colors"
              >
                <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                </svg>
                Talebi Sil
              </button>
            </div>
          </div>

          <!-- Zaman Çizelgesi -->
          <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
              <h3 class="text-lg font-medium text-gray-900">Zaman Çizelgesi</h3>
            </div>

            <div class="p-6 space-y-4">
              <div class="flex items-start">
                <div class="flex-shrink-0">
                  <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
                    </svg>
                  </div>
                </div>
                <div class="ml-4">
                  <p class="text-sm font-medium text-gray-900">Talep Oluşturuldu</p>
                  <p class="text-sm text-gray-500">{{ formatDateTime(purchasingRequest.created_at) }}</p>
                </div>
              </div>

              <div v-if="purchasingRequest.submitted_at" class="flex items-start">
                <div class="flex-shrink-0">
                  <div class="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center">
                    <svg class="w-5 h-5 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                      <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                      <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                    </svg>
                  </div>
                </div>
                <div class="ml-4">
                  <p class="text-sm font-medium text-gray-900">Onaya Gönderildi</p>
                  <p class="text-sm text-gray-500">{{ formatDateTime(purchasingRequest.submitted_at) }}</p>
                </div>
              </div>

              <div v-if="purchasingRequest.approved_at" class="flex items-start">
                <div class="flex-shrink-0">
                  <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                  </div>
                </div>
                <div class="ml-4">
                  <p class="text-sm font-medium text-gray-900">Onaylandı</p>
                  <p class="text-sm text-gray-500">{{ formatDateTime(purchasingRequest.approved_at) }}</p>
                </div>
              </div>

              <div v-if="purchasingRequest.ordered_at" class="flex items-start">
                <div class="flex-shrink-0">
                  <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                      <path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3z" />
                    </svg>
                  </div>
                </div>
                <div class="ml-4">
                  <p class="text-sm font-medium text-gray-900">Sipariş Verildi</p>
                  <p class="text-sm text-gray-500">{{ formatDateTime(purchasingRequest.ordered_at) }}</p>
                </div>
              </div>

              <div v-if="purchasingRequest.delivered_at" class="flex items-start">
                <div class="flex-shrink-0">
                  <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                      <path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z" />
                      <path d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H10a1 1 0 001-1V5a1 1 0 00-1-1H3zM14 7a1 1 0 00-1 1v6.05A2.5 2.5 0 0115.95 16H17a1 1 0 001-1v-5a1 1 0 00-.293-.707l-2-2A1 1 0 0015 7h-1z" />
                    </svg>
                  </div>
                </div>
                <div class="ml-4">
                  <p class="text-sm font-medium text-gray-900">Teslim Edildi</p>
                  <p class="text-sm text-gray-500">{{ formatDateTime(purchasingRequest.delivered_at) }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Onaylayan Kişiler -->
          <div v-if="purchasingRequest.supervisor_approval || purchasingRequest.manager_approval" class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
              <h3 class="text-lg font-medium text-gray-900">Onaylayan Kişiler</h3>
            </div>

            <div class="p-6 space-y-4">
              <div v-if="purchasingRequest.supervisor_approval" class="flex items-center p-3 bg-blue-50 rounded-lg">
                <div class="flex-shrink-0">
                  <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center border-2 border-blue-200">
                    <span class="text-blue-600 font-bold text-lg">
                      {{ purchasingRequest.supervisor_approval.name.charAt(0) }}
                    </span>
                  </div>
                </div>
                <div class="ml-4">
                  <p class="text-sm font-medium text-gray-900">{{ purchasingRequest.supervisor_approval.name }}</p>
                  <p class="text-xs text-gray-500">Şef Onayı</p>
                </div>
              </div>

              <div v-if="purchasingRequest.manager_approval" class="flex items-center p-3 bg-green-50 rounded-lg">
                <div class="flex-shrink-0">
                  <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center border-2 border-green-200">
                    <span class="text-green-600 font-bold text-lg">
                      {{ purchasingRequest.manager_approval.name.charAt(0) }}
                    </span>
                  </div>
                </div>
                <div class="ml-4">
                  <p class="text-sm font-medium text-gray-900">{{ purchasingRequest.manager_approval.name }}</p>
                  <p class="text-xs text-gray-500">Yönetici Onayı</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Onay Modalı -->
    <Modal :show="showingApprovalModal" @close="showingApprovalModal = false">
      <div class="p-6">
        <div class="flex items-center mb-4">
          <div class="flex-shrink-0 w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
            <svg class="w-6 h-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
          </div>
          <h3 class="ml-4 text-lg font-medium text-gray-900">
            {{ approvalType === 'supervisor' ? 'Şef Onayı Ver' : 'Yönetici Onayı Ver' }}
          </h3>
        </div>

        <div class="mb-6">
          <label class="block text-sm font-medium text-gray-700 mb-2">Not (Opsiyonel)</label>
          <textarea
            v-model="approvalNotes"
            rows="3"
            class="block w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
            placeholder="Onay notu ekleyebilirsiniz..."
          />
        </div>

        <div class="flex justify-end space-x-3">
          <button
            @click="showingApprovalModal = false"
            class="px-6 py-3 border-2 border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 transition-colors"
          >
            İptal
          </button>
          <button
            @click="approve"
            class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors"
          >
            Onayla
          </button>
        </div>
      </div>
    </Modal>

    <!-- Ret Modalı -->
    <Modal :show="showingRejectModal" @close="showingRejectModal = false">
      <div class="p-6">
        <div class="flex items-center mb-4">
          <div class="flex-shrink-0 w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
            <svg class="w-6 h-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
            </svg>
          </div>
          <h3 class="ml-4 text-lg font-medium text-gray-900">Talebi Reddet</h3>
        </div>

        <div class="mb-6">
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Red Sebebi <span class="text-red-500">*</span>
          </label>
          <textarea
            v-model="rejectionReason"
            rows="3"
            class="block w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500"
            placeholder="Neden reddedildiğini açıklayın..."
            required
          />
        </div>

        <div class="flex justify-end space-x-3">
          <button
            @click="showingRejectModal = false"
            class="px-6 py-3 border-2 border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 transition-colors"
          >
            İptal
          </button>
          <button
            @click="reject"
            :disabled="!rejectionReason"
            class="px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg disabled:opacity-50 transition-colors"
          >
            Reddet
          </button>
        </div>
      </div>
    </Modal>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Modal from '@/Components/UI/Modal.vue'

const props = defineProps({
  purchasingRequest: {
    type: Object,
    required: true
  }
})

const page = usePage()

const showingApprovalModal = ref(false)
const showingRejectModal = ref(false)
const approvalType = ref('')
const approvalNotes = ref('')
const rejectionReason = ref('')

// Onaylayabilir mi kontrolü
const canApprove = computed(() => {
  const userRole = page.props.auth?.user?.role
  return ['admin', 'hr', 'project_manager', 'site_manager'].includes(userRole)
})

// Silebilir mi kontrolü
const canDelete = computed(() => {
  const userRole = page.props.auth?.user?.role
  const isOwner = page.props.auth?.user?.id === props.purchasingRequest.requested_by?.id
  return ['admin', 'hr', 'project_manager'].includes(userRole) || isOwner
})

const showApprovalModal = (type) => {
  approvalType.value = type
  showingApprovalModal.value = true
}

const showRejectModal = () => {
  showingRejectModal.value = true
}

const submitForApproval = () => {
  if (confirm('Talebi onaya göndermek istediğinize emin misiniz?')) {
    router.post(route('purchasing-requests.submit', props.purchasingRequest.id))
  }
}

const approve = () => {
  const routeName = approvalType.value === 'supervisor'
    ? 'purchasing-requests.approve-supervisor'
    : 'purchasing-requests.approve-manager'

  router.post(route(routeName, props.purchasingRequest.id), {
    notes: approvalNotes.value
  }, {
    onSuccess: () => {
      showingApprovalModal.value = false
      approvalNotes.value = ''
    }
  })
}

const reject = () => {
  router.post(route('purchasing-requests.reject', props.purchasingRequest.id), {
    rejection_reason: rejectionReason.value
  }, {
    onSuccess: () => {
      showingRejectModal.value = false
      rejectionReason.value = ''
    }
  })
}

const cancelRequest = () => {
  if (confirm('Talebi iptal etmek istediğinize emin misiniz? Bu işlem geri alınamaz.')) {
    router.post(route('purchasing-requests.cancel', props.purchasingRequest.id))
  }
}

const deleteRequest = () => {
  if (confirm('Talebi silmek istediğinize emin misiniz? Bu işlem geri alınamaz.')) {
    router.delete(route('purchasing-requests.destroy', props.purchasingRequest.id))
  }
}

const getStatusClass = (status) => {
  const classes = {
    draft: 'bg-gray-100 text-gray-800 border-gray-200',
    pending: 'bg-yellow-100 text-yellow-800 border-yellow-200',
    approved: 'bg-green-100 text-green-800 border-green-200',
    ordered: 'bg-blue-100 text-blue-800 border-blue-200',
    delivered: 'bg-green-100 text-green-800 border-green-200',
    cancelled: 'bg-red-100 text-red-800 border-red-200',
    rejected: 'bg-red-100 text-red-800 border-red-200'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const getStatusText = (status) => {
  const texts = {
    draft: 'Taslak',
    pending: 'Onay Bekliyor',
    approved: 'Onaylandı',
    ordered: 'Sipariş Verildi',
    delivered: 'Teslim Edildi',
    cancelled: 'İptal Edildi',
    rejected: 'Reddedildi'
  }
  return texts[status] || status
}

const getUrgencyClass = (urgency) => {
  const classes = {
    low: 'bg-gray-100 text-gray-800 border-gray-200',
    normal: 'bg-blue-100 text-blue-800 border-blue-200',
    high: 'bg-orange-100 text-orange-800 border-orange-200',
    urgent: 'bg-red-100 text-red-800 border-red-200'
  }
  return classes[urgency] || 'bg-gray-100 text-gray-800'
}

const getUrgencyText = (urgency) => {
  const texts = {
    low: 'Düşük',
    normal: 'Normal',
    high: 'Yüksek',
    urgent: 'Acil'
  }
  return texts[urgency] || urgency
}

const getCategoryText = (category) => {
  const texts = {
    concrete: 'Beton',
    steel: 'Demir',
    general: 'Genel Malzeme',
    equipment: 'Ekipman',
    service: 'Hizmet',
    other: 'Diğer'
  }
  return texts[category] || category
}

const formatCurrency = (amount) => {
  if (!amount) return '₺0,00'
  return new Intl.NumberFormat('tr-TR', {
    style: 'currency',
    currency: 'TRY'
  }).format(amount)
}

const formatDate = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('tr-TR')
}

const formatDateTime = (datetime) => {
  if (!datetime) return '-'
  return new Date(datetime).toLocaleString('tr-TR', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}
</script>

<style scoped>
.w-full {
  width: 100% !important;
}
</style>
