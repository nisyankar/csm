<template>
  <AppLayout :title="`Satış #${unitSale.sale_number}`" :full-width="true">
    <!-- Header -->
    <template #fullWidthHeader>
      <div class="bg-gradient-to-r from-emerald-600 via-emerald-700 to-green-800 border-b border-emerald-900/20 w-full">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
          <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <!-- Header Info -->
            <div class="flex-1 min-w-0">
              <div class="flex items-center space-x-4 mb-3">
                <div class="flex-shrink-0 w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm">
                  <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z" />
                  </svg>
                </div>
                <div>
                  <h1 class="text-2xl lg:text-3xl font-bold text-white">
                    Satış #{{ unitSale.sale_number }}
                  </h1>
                  <p class="text-emerald-100 text-sm mt-1">{{ unitSale.customer?.full_name }}</p>
                </div>
              </div>

              <!-- Status Badges -->
              <div class="flex items-center space-x-3 flex-wrap gap-2">
                <span :class="unitSale.status_badge?.class" class="px-4 py-1.5 text-sm font-semibold rounded-full">
                  {{ unitSale.status_badge?.text }}
                </span>
                <span :class="unitSale.deed_status_badge?.class" class="px-4 py-1.5 text-sm font-semibold rounded-full">
                  {{ unitSale.deed_status_badge?.text }}
                </span>
                <span class="px-4 py-1.5 text-sm bg-white/10 text-white rounded-full border-2 border-white/30">
                  {{ unitSale.payment_completion_percentage }}% ödendi
                </span>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex-shrink-0 flex items-center space-x-3">
              <Link
                :href="route('sales.unit-sales.edit', unitSale.id)"
                class="inline-flex items-center px-4 py-2 bg-white text-emerald-600 text-sm font-medium rounded-lg hover:bg-emerald-50 transition-all duration-200"
              >
                <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                </svg>
                Düzenle
              </Link>
              <Link
                :href="route('sales.unit-sales.index')"
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
                  <Link :href="route('dashboard')" class="text-emerald-100 hover:text-white transition-colors">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                    </svg>
                  </Link>
                </li>
                <li class="flex items-center">
                  <svg class="h-3 w-3 text-emerald-200 mx-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                  </svg>
                  <Link :href="route('sales.unit-sales.index')" class="text-emerald-100 hover:text-white text-sm">Satışlar</Link>
                </li>
                <li class="flex items-center">
                  <svg class="h-3 w-3 text-emerald-200 mx-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                  </svg>
                  <span class="text-white font-medium text-sm">{{ unitSale.sale_number }}</span>
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
          <!-- Ödeme Durumu -->
          <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
              <h3 class="text-lg font-medium text-gray-900">Ödeme Durumu</h3>
            </div>
            <div class="p-6">
              <!-- Progress Bar -->
              <div class="mb-6">
                <div class="flex justify-between items-center mb-2">
                  <span class="text-sm font-medium text-gray-700">Ödeme Tamamlanma Oranı</span>
                  <span class="text-3xl font-bold text-emerald-600">{{ unitSale.payment_completion_percentage }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-4">
                  <div
                    class="h-4 rounded-full transition-all"
                    :class="unitSale.payment_completion_percentage >= 100 ? 'bg-green-600' : 'bg-emerald-600'"
                    :style="{ width: `${Math.min(unitSale.payment_completion_percentage, 100)}%` }"
                  ></div>
                </div>
              </div>

              <!-- Fiyat Bilgisi -->
              <div class="bg-gradient-to-br from-emerald-50 to-green-50 p-6 rounded-lg border border-emerald-200">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                  <div>
                    <div class="text-sm text-emerald-600 mb-1">Liste Fiyatı</div>
                    <div class="text-2xl font-bold text-gray-900">
                      {{ formatCurrency(unitSale.list_price) }}
                    </div>
                  </div>
                  <div>
                    <div class="text-sm text-red-600 mb-1">İndirim</div>
                    <div class="text-2xl font-bold text-red-600">
                      -{{ formatCurrency(unitSale.discount_amount) }}
                      <span v-if="unitSale.discount_percentage" class="text-sm">(%{{ unitSale.discount_percentage }})</span>
                    </div>
                  </div>
                </div>
                <div class="border-t border-emerald-300 pt-4">
                  <div class="text-sm text-emerald-600 mb-2">Net Satış Fiyatı</div>
                  <div class="text-4xl font-bold text-emerald-700">
                    {{ formatCurrency(unitSale.final_price) }}
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Satış Detayları -->
          <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
              <h3 class="text-lg font-medium text-gray-900">Satış Detayları</h3>
            </div>
            <div class="p-6">
              <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <dt class="text-sm font-medium text-gray-500">Müşteri</dt>
                  <dd class="mt-1">
                    <Link :href="route('sales.customers.show', unitSale.customer_id)" class="text-sm text-emerald-600 hover:text-emerald-800 font-semibold">
                      {{ unitSale.customer?.full_name }}
                    </Link>
                  </dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">Proje</dt>
                  <dd class="mt-1 text-sm text-gray-900 font-semibold">{{ unitSale.project?.name }}</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">Birim/Daire</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ unitSale.project_unit?.name || '-' }}</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">Satış Tipi</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ unitSale.sale_type_label }}</dd>
                </div>
                <div v-if="unitSale.reservation_date">
                  <dt class="text-sm font-medium text-gray-500">Rezervasyon Tarihi</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ formatDate(unitSale.reservation_date) }}</dd>
                </div>
                <div v-if="unitSale.sale_date">
                  <dt class="text-sm font-medium text-gray-500">Satış Tarihi</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ formatDate(unitSale.sale_date) }}</dd>
                </div>
                <div v-if="unitSale.contract_date">
                  <dt class="text-sm font-medium text-gray-500">Sözleşme Tarihi</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ formatDate(unitSale.contract_date) }}</dd>
                </div>
                <div v-if="unitSale.delivery_date">
                  <dt class="text-sm font-medium text-gray-500">Planlanan Teslim</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ formatDate(unitSale.delivery_date) }}</dd>
                </div>
              </dl>
            </div>
          </div>

          <!-- Ödeme Planı -->
          <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
              <h3 class="text-lg font-medium text-gray-900">Ödeme Planı</h3>
            </div>
            <div class="p-6">
              <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                  <dt class="text-xs font-medium text-blue-600 mb-1">Peşinat</dt>
                  <dd class="text-lg font-bold text-blue-700">
                    {{ formatCurrency(unitSale.down_payment) }}
                  </dd>
                </div>
                <div class="bg-purple-50 p-4 rounded-lg border border-purple-200">
                  <dt class="text-xs font-medium text-purple-600 mb-1">Taksit Sayısı</dt>
                  <dd class="text-lg font-bold text-purple-700">
                    {{ unitSale.installment_count }} ay
                  </dd>
                </div>
                <div class="bg-indigo-50 p-4 rounded-lg border border-indigo-200">
                  <dt class="text-xs font-medium text-indigo-600 mb-1">Aylık Taksit</dt>
                  <dd class="text-lg font-bold text-indigo-700">
                    {{ formatCurrency(unitSale.monthly_installment) }}
                  </dd>
                </div>
              </div>
            </div>
          </div>

          <!-- Ödemeler -->
          <div v-if="unitSale.sale_payments && unitSale.sale_payments.length > 0" class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
              <h3 class="text-lg font-medium text-gray-900">Ödemeler</h3>
            </div>
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Taksit</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Vade</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tutar</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ödenen</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Durum</th>
                    <th class="px-6 py-3"></th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="payment in unitSale.sale_payments" :key="payment.id" class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                      {{ payment.installment_number === 0 ? 'Peşinat' : `Taksit ${payment.installment_number}` }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      {{ formatDate(payment.due_date) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      {{ formatCurrency(payment.amount) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      {{ formatCurrency(payment.paid_amount) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span :class="payment.status_badge?.class" class="px-3 py-1 text-xs font-semibold rounded-full">
                        {{ payment.status_badge?.text }}
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                      <Link :href="route('sales.payments.show', payment.id)" class="text-emerald-600 hover:text-emerald-900">
                        Detay
                      </Link>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Tapu Bilgileri ve Yönetimi -->
          <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
              <h3 class="text-lg font-medium text-gray-900">Tapu Bilgileri</h3>
              <button
                @click="showDeedUpdateModal = true"
                class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white text-xs font-medium rounded-lg hover:bg-blue-700 transition-colors"
              >
                <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                </svg>
                Güncelle
              </button>
            </div>
            <div class="p-6">
              <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <dt class="text-sm font-medium text-gray-500">Tapu Durumu</dt>
                  <dd class="mt-1">
                    <span :class="getDeedStatusClass(unitSale.deed_status)" class="px-3 py-1 text-xs font-semibold rounded-full">
                      {{ getDeedStatusLabel(unitSale.deed_status) }}
                    </span>
                  </dd>
                </div>
                <div v-if="unitSale.deed_type">
                  <dt class="text-sm font-medium text-gray-500">Tapu Tipi</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ unitSale.deed_type }}</dd>
                </div>
                <div v-if="unitSale.title_deed_number">
                  <dt class="text-sm font-medium text-gray-500">Tapu Numarası</dt>
                  <dd class="mt-1 text-sm text-gray-900 font-mono">{{ unitSale.title_deed_number }}</dd>
                </div>
                <div v-if="unitSale.deed_transfer_date">
                  <dt class="text-sm font-medium text-gray-500">Devir Tarihi</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ formatDate(unitSale.deed_transfer_date) }}</dd>
                </div>
                <div v-if="unitSale.deed_notes" class="md:col-span-2">
                  <dt class="text-sm font-medium text-gray-500">Tapu Notları</dt>
                  <dd class="mt-1 text-sm text-gray-700 whitespace-pre-wrap">{{ unitSale.deed_notes }}</dd>
                </div>
              </dl>

              <!-- Tapu Belgeleri -->
              <div class="mt-6 pt-6 border-t border-gray-200">
                <div class="flex items-center justify-between mb-4">
                  <h4 class="text-sm font-semibold text-gray-900">Tapu Belgeleri</h4>
                  <button
                    @click="showDeedDocumentModal = true"
                    class="inline-flex items-center px-3 py-1.5 bg-emerald-600 text-white text-xs font-medium rounded-lg hover:bg-emerald-700 transition-colors"
                  >
                    <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Belge Yükle
                  </button>
                </div>

                <div v-if="unitSale.deed_documents && unitSale.deed_documents.length > 0" class="space-y-2">
                  <div
                    v-for="(doc, index) in unitSale.deed_documents"
                    :key="index"
                    class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-200"
                  >
                    <div class="flex items-center space-x-3">
                      <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-red-600" fill="currentColor" viewBox="0 0 24 24">
                          <path d="M7 18h10v-1H7v1zM17 14H7v-1h10v1zm0-4H7V9h10v1zm2.5-5L17 2.5 14.5 5H11v4h3.5l2.5 2.5 2.5-2.5V5h-3.5l2.5-2.5z"/>
                        </svg>
                      </div>
                      <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">{{ doc.file_name }}</p>
                        <p class="text-xs text-gray-500">{{ formatDate(doc.uploaded_at) }}</p>
                      </div>
                    </div>
                    <a
                      :href="`/storage/${doc.file_path}`"
                      target="_blank"
                      class="flex-shrink-0 text-blue-600 hover:text-blue-800 text-sm font-medium"
                    >
                      İndir
                    </a>
                  </div>
                </div>

                <div v-else class="text-center py-6 text-sm text-gray-500">
                  Henüz yüklenen belge bulunmamaktadır.
                </div>
              </div>
            </div>
          </div>

          <!-- Notlar -->
          <div v-if="unitSale.notes" class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
              <h3 class="text-lg font-medium text-gray-900">Notlar</h3>
            </div>
            <div class="p-6">
              <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ unitSale.notes }}</p>
            </div>
          </div>
        </div>

        <!-- Right Column - Stats & Info -->
        <div class="space-y-6">
          <!-- İstatistikler -->
          <div class="bg-gradient-to-br from-emerald-50 to-green-50 rounded-xl border border-emerald-200 p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Özet Bilgiler</h3>
            <div class="space-y-4">
              <div class="bg-white rounded-lg p-4 border border-emerald-100">
                <div class="text-xs text-gray-600 mb-1">Net Satış Fiyatı</div>
                <div class="text-2xl font-bold text-emerald-600">
                  {{ formatCurrency(unitSale.final_price) }}
                </div>
              </div>
              <div class="bg-white rounded-lg p-4 border border-emerald-100">
                <div class="text-xs text-gray-600 mb-1">Ödeme Tamamlanma</div>
                <div class="text-2xl font-bold text-blue-600">
                  {{ unitSale.payment_completion_percentage }}%
                </div>
              </div>
              <div class="bg-white rounded-lg p-4 border border-emerald-100">
                <div class="text-xs text-gray-600 mb-1">Satış Durumu</div>
                <div class="text-sm font-bold text-gray-900">
                  {{ unitSale.status_badge?.text }}
                </div>
              </div>
            </div>
          </div>

          <!-- Zaman Çizelgesi -->
          <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
              <h3 class="text-lg font-medium text-gray-900">Zaman Çizelgesi</h3>
            </div>
            <div class="p-6">
              <dl class="space-y-4">
                <div>
                  <dt class="text-xs font-medium text-gray-500 uppercase">Oluşturulma</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ formatDate(unitSale.created_at) }}</dd>
                </div>
                <div>
                  <dt class="text-xs font-medium text-gray-500 uppercase">Son Güncelleme</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ formatDate(unitSale.updated_at) }}</dd>
                </div>
              </dl>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Tapu Durumu Güncelleme Modalı -->
    <form @submit.prevent="updateDeedStatus" v-if="showDeedUpdateModal" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showDeedUpdateModal = false"></div>

        <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
          <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Tapu Durumunu Güncelle</h3>

            <div class="space-y-4">
              <!-- Tapu Durumu -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tapu Durumu *</label>
                <select v-model="deedForm.deed_status" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                  <option value="not_transferred">Devredilmedi</option>
                  <option value="in_progress">İşlemde</option>
                  <option value="transferred">Devredildi</option>
                  <option value="postponed">Ertelendi</option>
                </select>
              </div>

              <!-- Tapu Tipi -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tapu Tipi</label>
                <input v-model="deedForm.deed_type" type="text" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Ör: Kat Mülkiyeti, Kat İrtifakı">
              </div>

              <!-- Tapu Numarası -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tapu Numarası</label>
                <input v-model="deedForm.title_deed_number" type="text" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Tapu sicil numarası">
              </div>

              <!-- Devir Tarihi -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Devir Tarihi</label>
                <input v-model="deedForm.deed_transfer_date" type="date" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
              </div>

              <!-- Notlar -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tapu Notları</label>
                <textarea v-model="deedForm.deed_notes" rows="3" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Tapu işlemleriyle ilgili notlar"></textarea>
              </div>
            </div>
          </div>

          <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 gap-2">
            <button type="submit" :disabled="deedFormProcessing" class="inline-flex w-full justify-center rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 sm:w-auto disabled:opacity-50">
              <span v-if="!deedFormProcessing">Kaydet</span>
              <span v-else>Kaydediliyor...</span>
            </button>
            <button type="button" @click="showDeedUpdateModal = false" class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto">
              İptal
            </button>
          </div>
        </div>
      </div>
    </form>

    <!-- Tapu Belgesi Yükleme Modalı -->
    <form @submit.prevent="uploadDeedDocument" v-if="showDeedDocumentModal" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showDeedDocumentModal = false"></div>

        <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
          <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Tapu Belgesi Yükle</h3>

            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Belge Dosyası (PDF, JPG, PNG - Max 10MB)</label>
                <input
                  type="file"
                  @change="handleFileChange"
                  accept=".pdf,.jpg,.jpeg,.png"
                  class="w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none"
                  required
                >
              </div>

              <div v-if="selectedFile" class="bg-blue-50 p-3 rounded-lg border border-blue-200">
                <div class="flex items-center space-x-2">
                  <svg class="h-5 w-5 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zm4 18H6V4h7v5h5v11z"/>
                  </svg>
                  <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-blue-900 truncate">{{ selectedFile.name }}</p>
                    <p class="text-xs text-blue-700">{{ (selectedFile.size / 1024 / 1024).toFixed(2) }} MB</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 gap-2">
            <button type="submit" :disabled="documentFormProcessing || !selectedFile" class="inline-flex w-full justify-center rounded-md bg-emerald-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500 sm:w-auto disabled:opacity-50">
              <span v-if="!documentFormProcessing">Yükle</span>
              <span v-else>Yükleniyor...</span>
            </button>
            <button type="button" @click="showDeedDocumentModal = false" class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto">
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
  unitSale: {
    type: Object,
    required: true
  }
})

// Tapu Modal States
const showDeedUpdateModal = ref(false)
const showDeedDocumentModal = ref(false)
const deedFormProcessing = ref(false)
const documentFormProcessing = ref(false)
const selectedFile = ref(null)

// Tapu Form Data
const deedForm = ref({
  deed_status: props.unitSale.deed_status || 'not_transferred',
  deed_type: props.unitSale.deed_type || '',
  title_deed_number: props.unitSale.title_deed_number || '',
  deed_transfer_date: props.unitSale.deed_transfer_date || '',
  deed_notes: props.unitSale.deed_notes || ''
})

// Update Deed Status
const updateDeedStatus = () => {
  deedFormProcessing.value = true

  router.post(
    route('sales.unit-sales.deed.update-status', props.unitSale.id),
    deedForm.value,
    {
      onSuccess: () => {
        showDeedUpdateModal.value = false
      },
      onFinish: () => {
        deedFormProcessing.value = false
      }
    }
  )
}

// Handle File Change
const handleFileChange = (event) => {
  selectedFile.value = event.target.files[0]
}

// Upload Deed Document
const uploadDeedDocument = () => {
  if (!selectedFile.value) return

  documentFormProcessing.value = true

  const formData = new FormData()
  formData.append('document', selectedFile.value)

  router.post(
    route('sales.unit-sales.deed.upload-document', props.unitSale.id),
    formData,
    {
      onSuccess: () => {
        showDeedDocumentModal.value = false
        selectedFile.value = null
      },
      onFinish: () => {
        documentFormProcessing.value = false
      }
    }
  )
}

// Get Deed Status Class
const getDeedStatusClass = (status) => {
  switch (status) {
    case 'not_transferred':
      return 'bg-gray-100 text-gray-800'
    case 'in_progress':
      return 'bg-blue-100 text-blue-800'
    case 'transferred':
      return 'bg-green-100 text-green-800'
    case 'postponed':
      return 'bg-orange-100 text-orange-800'
    default:
      return 'bg-gray-100 text-gray-800'
  }
}

// Get Deed Status Label
const getDeedStatusLabel = (status) => {
  switch (status) {
    case 'not_transferred':
      return 'Devredilmedi'
    case 'in_progress':
      return 'İşlemde'
    case 'transferred':
      return 'Devredildi'
    case 'postponed':
      return 'Ertelendi'
    default:
      return 'Bilinmiyor'
  }
}

const formatCurrency = (amount) => {
  if (!amount) return '₺0'
  return new Intl.NumberFormat('tr-TR', {
    style: 'currency',
    currency: 'TRY',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0
  }).format(amount)
}

const formatDate = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('tr-TR', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}
</script>
