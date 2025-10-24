<template>
  <AppLayout title="İzin Talebi Detayı" :full-width="true">
    <!-- Full-width header -->
    <template #fullWidthHeader>
      <div class="bg-gradient-to-r from-purple-600 via-purple-700 to-indigo-800 border-b border-purple-900/20 w-full">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
          <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <!-- Header Info -->
            <div class="flex-1 min-w-0">
              <div class="flex items-center space-x-4 mb-3">
                <div class="flex-shrink-0 w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm">
                  <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                  </svg>
                </div>
                <div>
                  <h1 class="text-2xl lg:text-3xl font-bold text-white">
                    {{ leave_request.employee?.first_name }} {{ leave_request.employee?.last_name }}
                  </h1>
                  <p class="text-purple-100 text-sm mt-1">{{ leave_request.employee?.employee_code }}</p>
                </div>
              </div>

              <!-- Status Badge -->
              <div class="flex items-center space-x-3">
                <span :class="getStatusClass(leave_request.status)" class="px-4 py-1.5 text-sm font-semibold rounded-full border-2">
                  {{ getStatusText(leave_request.status) }}
                </span>
                <span class="px-4 py-1.5 text-sm bg-white/10 text-white rounded-full border-2 border-white/30">
                  {{ leave_request.working_days }} gün
                </span>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex-shrink-0 flex items-center space-x-3">
              <Link
                v-if="can_edit"
                :href="route('leave-requests.edit', leave_request.id)"
                class="inline-flex items-center px-4 py-2 bg-white text-purple-600 text-sm font-medium rounded-lg hover:bg-purple-50 transition-all duration-200"
              >
                <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                </svg>
                Düzenle
              </Link>
              <Link
                :href="route('leave-requests.index')"
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
                  <Link :href="route('leave-requests.index')" class="text-purple-100 hover:text-white text-sm">İzin Talepleri</Link>
                </li>
                <li class="flex items-center">
                  <svg class="h-3 w-3 text-purple-200 mx-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                  </svg>
                  <span class="text-white font-medium text-sm">Detay</span>
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
          <!-- İzin Bilgileri -->
          <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
              <h3 class="text-lg font-medium text-gray-900">İzin Bilgileri</h3>
            </div>
            <div class="p-6">
              <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <dt class="text-sm font-medium text-gray-500">İzin Türü</dt>
                  <dd class="mt-1 text-sm text-gray-900 font-semibold">{{ leave_request.leave_type?.name || 'Belirtilmemiş' }}</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">İzin Yılı</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ leave_request.leave_year }}</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">Başlangıç Tarihi</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ formatDate(leave_request.start_date) }}</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">Bitiş Tarihi</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ formatDate(leave_request.end_date) }}</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">Toplam Gün</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ leave_request.total_days }} gün</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">Çalışma Günü</dt>
                  <dd class="mt-1 text-sm text-gray-900 font-semibold">{{ leave_request.working_days }} gün</dd>
                </div>
                <div v-if="leave_request.substitute_employee" class="md:col-span-2">
                  <dt class="text-sm font-medium text-gray-500">Vekil Çalışan</dt>
                  <dd class="mt-1 text-sm text-gray-900">
                    {{ leave_request.substitute_employee.first_name }} {{ leave_request.substitute_employee.last_name }}
                    <span class="text-gray-500">({{ leave_request.substitute_employee.employee_code }})</span>
                  </dd>
                </div>
                <div v-if="leave_request.reason" class="md:col-span-2">
                  <dt class="text-sm font-medium text-gray-500">İzin Sebebi</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ leave_request.reason }}</dd>
                </div>
              </dl>
            </div>
          </div>

          <!-- Onay Bilgileri (if approved/rejected) -->
          <div v-if="leave_request.status !== 'pending'" class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
              <h3 class="text-lg font-medium text-gray-900">Onay Bilgileri</h3>
            </div>
            <div class="p-6">
              <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div v-if="leave_request.approver">
                  <dt class="text-sm font-medium text-gray-500">Onaylayan</dt>
                  <dd class="mt-1 text-sm text-gray-900">
                    {{ leave_request.approver.first_name }} {{ leave_request.approver.last_name }}
                  </dd>
                </div>
                <div v-if="leave_request.approved_at">
                  <dt class="text-sm font-medium text-gray-500">Onay Tarihi</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ formatDateTime(leave_request.approved_at) }}</dd>
                </div>
                <div v-if="leave_request.approval_notes" class="md:col-span-2">
                  <dt class="text-sm font-medium text-gray-500">Onay Notu</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ leave_request.approval_notes }}</dd>
                </div>
                <div v-if="leave_request.rejection_reason" class="md:col-span-2">
                  <dt class="text-sm font-medium text-gray-500">Red Sebebi</dt>
                  <dd class="mt-1 text-sm text-red-600">{{ leave_request.rejection_reason }}</dd>
                </div>
              </dl>
            </div>
          </div>
        </div>

        <!-- Right Column - Actions & Status -->
        <div class="space-y-6">
          <!-- Approval Actions (if pending and can approve) -->
          <div v-if="leave_request.status === 'pending' && can_approve" class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
              <h3 class="text-lg font-medium text-gray-900">İşlemler</h3>
            </div>
            <div class="p-6 space-y-4">
              <button
                @click="showApproveModal = true"
                class="w-full inline-flex justify-center items-center px-4 py-3 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors shadow-sm"
              >
                <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                </svg>
                Onayla
              </button>
              <button
                @click="showRejectModal = true"
                class="w-full inline-flex justify-center items-center px-4 py-3 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition-colors shadow-sm"
              >
                <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
                Reddet
              </button>
            </div>
          </div>

          <!-- Cancel Action (if can cancel) -->
          <div v-if="can_cancel && leave_request.status === 'pending'" class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
              <h3 class="text-lg font-medium text-gray-900">Talebi İptal Et</h3>
            </div>
            <div class="p-6">
              <button
                @click="showCancelModal = true"
                class="w-full inline-flex justify-center items-center px-4 py-3 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 transition-colors shadow-sm"
              >
                <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
                İptal Et
              </button>
            </div>
          </div>

          <!-- Status Timeline -->
          <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
              <h3 class="text-lg font-medium text-gray-900">Durum Geçmişi</h3>
            </div>
            <div class="p-6">
              <div class="flow-root">
                <ul class="-mb-8">
                  <li v-if="leave_request.submitted_at">
                    <div class="relative pb-8">
                      <span class="absolute left-4 top-4 -ml-px h-full w-0.5 bg-gray-200"></span>
                      <div class="relative flex space-x-3">
                        <div>
                          <span class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center ring-8 ring-white">
                            <svg class="h-4 w-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                          </span>
                        </div>
                        <div class="flex-1">
                          <p class="text-sm text-gray-900 font-medium">Talep Oluşturuldu</p>
                          <p class="text-xs text-gray-500">{{ formatDateTime(leave_request.submitted_at) }}</p>
                        </div>
                      </div>
                    </div>
                  </li>
                  <li v-if="leave_request.approved_at">
                    <div class="relative pb-8">
                      <div class="relative flex space-x-3">
                        <div>
                          <span class="h-8 w-8 rounded-full bg-green-500 flex items-center justify-center ring-8 ring-white">
                            <svg class="h-4 w-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                          </span>
                        </div>
                        <div class="flex-1">
                          <p class="text-sm text-gray-900 font-medium">Onaylandı</p>
                          <p class="text-xs text-gray-500">{{ formatDateTime(leave_request.approved_at) }}</p>
                        </div>
                      </div>
                    </div>
                  </li>
                  <li v-if="leave_request.status === 'rejected'">
                    <div class="relative pb-8">
                      <div class="relative flex space-x-3">
                        <div>
                          <span class="h-8 w-8 rounded-full bg-red-500 flex items-center justify-center ring-8 ring-white">
                            <svg class="h-4 w-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                          </span>
                        </div>
                        <div class="flex-1">
                          <p class="text-sm text-gray-900 font-medium">Reddedildi</p>
                          <p class="text-xs text-gray-500">{{ formatDateTime(leave_request.reviewed_at) }}</p>
                        </div>
                      </div>
                    </div>
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Approve Modal -->
    <TransitionRoot as="template" :show="showApproveModal">
      <Dialog as="div" class="relative z-50" @close="showApproveModal = false">
        <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0" enter-to="opacity-100" leave="ease-in duration-200" leave-from="opacity-100" leave-to="opacity-0">
          <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" />
        </TransitionChild>

        <div class="fixed inset-0 z-10 overflow-y-auto">
          <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" enter-to="opacity-100 translate-y-0 sm:scale-100" leave="ease-in duration-200" leave-from="opacity-100 translate-y-0 sm:scale-100" leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
              <DialogPanel class="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6">
                <div>
                  <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-green-100">
                    <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                    </svg>
                  </div>
                  <div class="mt-3 text-center sm:mt-5">
                    <DialogTitle as="h3" class="text-base font-semibold leading-6 text-gray-900">
                      İzin Talebini Onayla
                    </DialogTitle>
                    <div class="mt-2">
                      <p class="text-sm text-gray-500">
                        Bu izin talebini onaylamak istediğinizden emin misiniz?
                      </p>
                    </div>
                  </div>
                  <div class="mt-4">
                    <label for="approval_notes" class="block text-sm font-medium text-gray-700">Onay Notu (Opsiyonel)</label>
                    <textarea
                      id="approval_notes"
                      v-model="approvalForm.notes"
                      rows="3"
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 sm:text-sm"
                      placeholder="Onay notunuzu yazabilirsiniz..."
                    ></textarea>
                  </div>
                </div>
                <div class="mt-5 sm:mt-6 sm:grid sm:grid-flow-row-dense sm:grid-cols-2 sm:gap-3">
                  <button
                    type="button"
                    :disabled="approvalForm.processing"
                    @click="approveRequest"
                    class="inline-flex w-full justify-center rounded-md bg-green-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green-600 sm:col-start-2 disabled:opacity-50"
                  >
                    {{ approvalForm.processing ? 'İşleniyor...' : 'Onayla' }}
                  </button>
                  <button
                    type="button"
                    @click="showApproveModal = false"
                    class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:col-start-1 sm:mt-0"
                  >
                    İptal
                  </button>
                </div>
              </DialogPanel>
            </TransitionChild>
          </div>
        </div>
      </Dialog>
    </TransitionRoot>

    <!-- Reject Modal -->
    <TransitionRoot as="template" :show="showRejectModal">
      <Dialog as="div" class="relative z-50" @close="showRejectModal = false">
        <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0" enter-to="opacity-100" leave="ease-in duration-200" leave-from="opacity-100" leave-to="opacity-0">
          <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" />
        </TransitionChild>

        <div class="fixed inset-0 z-10 overflow-y-auto">
          <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" enter-to="opacity-100 translate-y-0 sm:scale-100" leave="ease-in duration-200" leave-from="opacity-100 translate-y-0 sm:scale-100" leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
              <DialogPanel class="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6">
                <div>
                  <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-red-100">
                    <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                  </div>
                  <div class="mt-3 text-center sm:mt-5">
                    <DialogTitle as="h3" class="text-base font-semibold leading-6 text-gray-900">
                      İzin Talebini Reddet
                    </DialogTitle>
                    <div class="mt-2">
                      <p class="text-sm text-gray-500">
                        Bu izin talebini reddetmek istediğinizden emin misiniz?
                      </p>
                    </div>
                  </div>
                  <div class="mt-4">
                    <label for="rejection_reason" class="block text-sm font-medium text-gray-700">
                      Red Sebebi <span class="text-red-500">*</span>
                    </label>
                    <textarea
                      id="rejection_reason"
                      v-model="rejectionForm.reason"
                      rows="3"
                      required
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm"
                      placeholder="Red sebebini yazınız..."
                    ></textarea>
                  </div>
                </div>
                <div class="mt-5 sm:mt-6 sm:grid sm:grid-flow-row-dense sm:grid-cols-2 sm:gap-3">
                  <button
                    type="button"
                    :disabled="rejectionForm.processing || !rejectionForm.reason"
                    @click="rejectRequest"
                    class="inline-flex w-full justify-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600 sm:col-start-2 disabled:opacity-50"
                  >
                    {{ rejectionForm.processing ? 'İşleniyor...' : 'Reddet' }}
                  </button>
                  <button
                    type="button"
                    @click="showRejectModal = false"
                    class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:col-start-1 sm:mt-0"
                  >
                    İptal
                  </button>
                </div>
              </DialogPanel>
            </TransitionChild>
          </div>
        </div>
      </Dialog>
    </TransitionRoot>

    <!-- Cancel Modal -->
    <TransitionRoot as="template" :show="showCancelModal">
      <Dialog as="div" class="relative z-50" @close="showCancelModal = false">
        <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0" enter-to="opacity-100" leave="ease-in duration-200" leave-from="opacity-100" leave-to="opacity-0">
          <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" />
        </TransitionChild>

        <div class="fixed inset-0 z-10 overflow-y-auto">
          <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" enter-to="opacity-100 translate-y-0 sm:scale-100" leave="ease-in duration-200" leave-from="opacity-100 translate-y-0 sm:scale-100" leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
              <DialogPanel class="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6">
                <div>
                  <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-gray-100">
                    <svg class="h-6 w-6 text-gray-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                  </div>
                  <div class="mt-3 text-center sm:mt-5">
                    <DialogTitle as="h3" class="text-base font-semibold leading-6 text-gray-900">
                      İzin Talebini İptal Et
                    </DialogTitle>
                    <div class="mt-2">
                      <p class="text-sm text-gray-500">
                        Bu izin talebini iptal etmek istediğinizden emin misiniz?
                      </p>
                    </div>
                  </div>
                  <div class="mt-4">
                    <label for="cancel_reason" class="block text-sm font-medium text-gray-700">İptal Sebebi (Opsiyonel)</label>
                    <textarea
                      id="cancel_reason"
                      v-model="cancelForm.reason"
                      rows="3"
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500 sm:text-sm"
                      placeholder="İptal sebebini yazabilirsiniz..."
                    ></textarea>
                  </div>
                </div>
                <div class="mt-5 sm:mt-6 sm:grid sm:grid-flow-row-dense sm:grid-cols-2 sm:gap-3">
                  <button
                    type="button"
                    :disabled="cancelForm.processing"
                    @click="cancelRequest"
                    class="inline-flex w-full justify-center rounded-md bg-gray-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-600 sm:col-start-2 disabled:opacity-50"
                  >
                    {{ cancelForm.processing ? 'İşleniyor...' : 'İptal Et' }}
                  </button>
                  <button
                    type="button"
                    @click="showCancelModal = false"
                    class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:col-start-1 sm:mt-0"
                  >
                    Vazgeç
                  </button>
                </div>
              </DialogPanel>
            </TransitionChild>
          </div>
        </div>
      </Dialog>
    </TransitionRoot>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue';

const props = defineProps({
  leave_request: Object,
  can_edit: Boolean,
  can_approve: Boolean,
  can_cancel: Boolean,
});

const showApproveModal = ref(false);
const showRejectModal = ref(false);
const showCancelModal = ref(false);

const approvalForm = ref({
  notes: '',
  processing: false,
});

const rejectionForm = ref({
  reason: '',
  processing: false,
});

const cancelForm = ref({
  reason: '',
  processing: false,
});

const approveRequest = () => {
  approvalForm.value.processing = true;
  router.post(
    route('leave-requests.approve', props.leave_request.id),
    { notes: approvalForm.value.notes },
    {
      onSuccess: () => {
        showApproveModal.value = false;
        approvalForm.value.notes = '';
      },
      onFinish: () => {
        approvalForm.value.processing = false;
      },
    }
  );
};

const rejectRequest = () => {
  rejectionForm.value.processing = true;
  router.post(
    route('leave-requests.reject', props.leave_request.id),
    { reason: rejectionForm.value.reason },
    {
      onSuccess: () => {
        showRejectModal.value = false;
        rejectionForm.value.reason = '';
      },
      onFinish: () => {
        rejectionForm.value.processing = false;
      },
    }
  );
};

const cancelRequest = () => {
  cancelForm.value.processing = true;
  router.delete(route('leave-requests.destroy', props.leave_request.id), {
    data: { reason: cancelForm.value.reason },
    onSuccess: () => {
      showCancelModal.value = false;
    },
    onFinish: () => {
      cancelForm.value.processing = false;
    },
  });
};

const formatDate = (date) => {
  if (!date) return '-';
  return new Date(date).toLocaleDateString('tr-TR', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  });
};

const formatDateTime = (datetime) => {
  if (!datetime) return '-';
  return new Date(datetime).toLocaleDateString('tr-TR', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
};

const getStatusClass = (status) => {
  const classes = {
    pending: 'bg-yellow-100 text-yellow-800 border-yellow-300',
    approved: 'bg-green-100 text-green-800 border-green-300',
    rejected: 'bg-red-100 text-red-800 border-red-300',
    cancelled: 'bg-gray-100 text-gray-800 border-gray-300',
  };
  return classes[status] || 'bg-gray-100 text-gray-800 border-gray-300';
};

const getStatusText = (status) => {
  const texts = {
    pending: 'Beklemede',
    approved: 'Onaylandı',
    rejected: 'Reddedildi',
    cancelled: 'İptal Edildi',
  };
  return texts[status] || status;
};
</script>
