<template>
  <AppLayout title="Yeni Proje Oluştur - SPT İnşaat Puantaj Sistemi" :full-width="true">
    <!-- Full-width header -->
    <template #fullWidthHeader>
      <div class="bg-gradient-to-r from-cyan-600 via-cyan-700 to-teal-800 border-b border-cyan-900/20 w-full">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
          <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
              <div class="flex-shrink-0 w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm">
                <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
              </div>
              <div>
                <h1 class="text-2xl lg:text-3xl font-bold text-white">Yeni Proje Oluştur</h1>
                <p class="text-cyan-100 text-sm mt-1">Yeni bir proje kaydı oluşturun</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Breadcrumb -->
        <div class="bg-black/10 border-t border-white/10">
          <div class="w-full px-4 sm:px-6 lg:px-8">
            <div class="flex items-center space-x-2 py-2">
              <ol class="flex items-center space-x-2">
                <li>
                  <Link :href="route('dashboard')" class="text-cyan-100 hover:text-white transition-colors">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                    </svg>
                    <span class="sr-only">Ana Sayfa</span>
                  </Link>
                </li>
                <li class="flex items-center">
                  <svg class="h-3 w-3 text-cyan-200 mx-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                  </svg>
                  <Link :href="route('projects.index')" class="text-cyan-100 hover:text-white text-xs">
                    Proje Yönetimi
                  </Link>
                </li>
                <li class="flex items-center">
                  <svg class="h-3 w-3 text-cyan-200 mx-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                  </svg>
                  <span class="text-xs font-medium text-white">Yeni Proje</span>
                </li>
              </ol>
            </div>
          </div>
        </div>
      </div>
    </template>

    <!-- Main Content -->
    <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
      <form @submit.prevent="submit" class="space-y-6">
        <!-- Basic Information -->
        <Card>
          <div class="p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Temel Bilgiler</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Proje Adı <span class="text-red-500">*</span>
                </label>
                <Input
                  v-model="form.name"
                  type="text"
                  placeholder="Proje adını girin"
                  :error="errors.name"
                  required
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Proje Kodu
                </label>
                <Input
                  v-model="form.project_code"
                  type="text"
                  placeholder="Otomatik oluşturulacak"
                  :error="errors.project_code"
                />
              </div>

              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Açıklama
                </label>
                <textarea
                  v-model="form.description"
                  rows="3"
                  class="w-full rounded-lg border-gray-300 shadow-sm focus:border-cyan-500 focus:ring-cyan-500"
                  placeholder="Proje açıklaması"
                ></textarea>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Proje Türü <span class="text-red-500">*</span>
                </label>
                <Select
                  v-model="form.type"
                  :options="typeOptions"
                  :error="errors.type"
                  required
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Öncelik
                </label>
                <Select
                  v-model="form.priority"
                  :options="priorityOptions"
                  :error="errors.priority"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Durum <span class="text-red-500">*</span>
                </label>
                <Select
                  v-model="form.status"
                  :options="statusOptions"
                  :error="errors.status"
                  required
                />
              </div>
            </div>
          </div>
        </Card>

        <!-- Location Information -->
        <Card>
          <div class="p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Konum Bilgileri</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Şehir <span class="text-red-500">*</span>
                </label>
                <Input
                  v-model="form.city"
                  type="text"
                  placeholder="Şehir"
                  :error="errors.city"
                  required
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  İlçe
                </label>
                <Input
                  v-model="form.district"
                  type="text"
                  placeholder="İlçe"
                  :error="errors.district"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Konum <span class="text-red-500">*</span>
                </label>
                <Input
                  v-model="form.location"
                  type="text"
                  placeholder="Kısa konum bilgisi"
                  :error="errors.location"
                  required
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Koordinatlar
                </label>
                <Input
                  v-model="form.coordinates"
                  type="text"
                  placeholder="Enlem, Boylam"
                  :error="errors.coordinates"
                />
              </div>

              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Tam Adres
                </label>
                <textarea
                  v-model="form.full_address"
                  rows="2"
                  class="w-full rounded-lg border-gray-300 shadow-sm focus:border-cyan-500 focus:ring-cyan-500"
                  placeholder="Detaylı adres bilgisi"
                ></textarea>
              </div>
            </div>
          </div>
        </Card>

        <!-- Date & Budget Information -->
        <Card>
          <div class="p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Tarih ve Bütçe</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Başlangıç Tarihi <span class="text-red-500">*</span>
                </label>
                <Input
                  v-model="form.start_date"
                  type="date"
                  :error="errors.start_date"
                  required
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Planlanan Bitiş Tarihi <span class="text-red-500">*</span>
                </label>
                <Input
                  v-model="form.planned_end_date"
                  type="date"
                  :error="errors.planned_end_date"
                  required
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Toplam Bütçe (₺)
                </label>
                <Input
                  v-model="form.budget"
                  type="number"
                  step="0.01"
                  min="0"
                  placeholder="0.00"
                  :error="errors.budget"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  İşçilik Bütçesi (₺)
                </label>
                <Input
                  v-model="form.labor_budget"
                  type="number"
                  step="0.01"
                  min="0"
                  placeholder="0.00"
                  :error="errors.labor_budget"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Tahmini Çalışan Sayısı
                </label>
                <Input
                  v-model="form.estimated_employees"
                  type="number"
                  min="0"
                  placeholder="0"
                  :error="errors.estimated_employees"
                />
              </div>
            </div>
          </div>
        </Card>

        <!-- Project Structure (Phase 1) -->
        <Card>
          <div class="p-6">
            <div class="flex items-center justify-between mb-4">
              <div>
                <h2 class="text-lg font-semibold text-gray-900">Proje Yapısı</h2>
                <p class="text-sm text-gray-500 mt-1">Bloklar, katlar ve birimleri tanımlayın</p>
              </div>
              <button
                type="button"
                @click="addStructure"
                class="px-4 py-2 bg-cyan-600 text-white rounded-lg hover:bg-cyan-700 transition-colors text-sm flex items-center gap-2"
              >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Blok/Bina Ekle
              </button>
            </div>

            <div v-if="form.structures.length === 0" class="text-center py-8 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300">
              <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
              </svg>
              <p class="mt-2 text-sm text-gray-600">Henüz yapı tanımlanmamış</p>
              <p class="text-xs text-gray-500">Blok veya bina ekleyerek başlayın</p>
            </div>

            <!-- Structures List -->
            <div v-else class="space-y-4">
              <div
                v-for="(structure, sIndex) in form.structures"
                :key="sIndex"
                class="border border-gray-200 rounded-lg overflow-hidden"
              >
                <!-- Structure Header -->
                <div class="bg-gray-50 p-4 flex items-center justify-between cursor-pointer hover:bg-gray-100 transition-colors" @click="toggleStructure(sIndex)">
                  <div class="flex items-center space-x-3 flex-1">
                    <svg
                      class="h-5 w-5 text-gray-500 transition-transform"
                      :class="{ 'rotate-90': expandedStructures[sIndex] }"
                      fill="none"
                      viewBox="0 0 24 24"
                      stroke="currentColor"
                    >
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                    <div class="flex-1">
                      <div class="font-medium text-gray-900">
                        {{ structure.name || 'Yeni Yapı' }}
                        <span v-if="structure.code" class="text-gray-500 text-sm ml-2">({{ structure.code }})</span>
                      </div>
                      <div class="text-xs text-gray-500 mt-1">
                        {{ structure.floors.length }} kat, {{ structure.floors.reduce((sum, f) => sum + f.units.length, 0) }} birim
                      </div>
                    </div>
                  </div>
                  <button
                    type="button"
                    @click.stop="removeStructure(sIndex)"
                    class="ml-4 p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                  >
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                  </button>
                </div>

                <!-- Structure Details (Collapsible) -->
                <div v-show="expandedStructures[sIndex]" class="p-4 space-y-4 bg-white">
                  <!-- Structure Basic Info -->
                  <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                      <label class="block text-xs font-medium text-gray-700 mb-1">Yapı Adı <span class="text-red-500">*</span></label>
                      <Input
                        v-model="structure.name"
                        type="text"
                        placeholder="A Blok"
                        class="text-sm"
                      />
                    </div>
                    <div>
                      <label class="block text-xs font-medium text-gray-700 mb-1">Kod <span class="text-red-500">*</span></label>
                      <Input
                        v-model="structure.code"
                        type="text"
                        placeholder="A, B, C..."
                        class="text-sm"
                      />
                    </div>
                    <div>
                      <label class="block text-xs font-medium text-gray-700 mb-1">Tip</label>
                      <Select
                        v-model="structure.structure_type"
                        :options="structureTypeOptions"
                        class="text-sm"
                      />
                    </div>
                    <div>
                      <label class="block text-xs font-medium text-gray-700 mb-1">Durum</label>
                      <Select
                        v-model="structure.status"
                        :options="structureStatusOptions"
                        class="text-sm"
                      />
                    </div>
                    <div>
                      <label class="block text-xs font-medium text-gray-700 mb-1">Toplam Kat</label>
                      <Input
                        v-model="structure.total_floors"
                        type="number"
                        placeholder="8"
                        class="text-sm"
                      />
                    </div>
                    <div>
                      <label class="block text-xs font-medium text-gray-700 mb-1">Toplam Birim</label>
                      <Input
                        v-model="structure.total_units"
                        type="number"
                        placeholder="32"
                        class="text-sm"
                      />
                    </div>
                    <div class="md:col-span-3">
                      <label class="block text-xs font-medium text-gray-700 mb-1">Toplam Alan (m²)</label>
                      <Input
                        v-model="structure.total_area"
                        type="number"
                        step="0.01"
                        placeholder="3200.00"
                        class="text-sm"
                      />
                    </div>
                  </div>

                  <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Açıklama</label>
                    <textarea
                      v-model="structure.description"
                      rows="2"
                      class="w-full text-sm rounded-lg border-gray-300 shadow-sm focus:border-cyan-500 focus:ring-cyan-500"
                      placeholder="Yapı açıklaması"
                    ></textarea>
                  </div>

                  <!-- Floors Section -->
                  <div class="border-t pt-4">
                    <div class="flex items-center justify-between mb-3">
                      <h4 class="text-sm font-semibold text-gray-900">Katlar</h4>
                      <button
                        type="button"
                        @click="addFloor(sIndex)"
                        class="px-3 py-1.5 bg-indigo-600 text-white rounded-lg text-xs hover:bg-indigo-700 flex items-center gap-1"
                      >
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Kat Ekle
                      </button>
                    </div>

                    <div v-if="structure.floors.length === 0" class="text-center py-4 bg-gray-50 rounded text-xs text-gray-500">
                      Henüz kat eklenmedi
                    </div>

                    <div v-else class="space-y-3">
                      <div
                        v-for="(floor, fIndex) in structure.floors"
                        :key="fIndex"
                        class="bg-gray-50 p-3 rounded-lg border border-gray-200"
                      >
                        <div class="flex items-center justify-between mb-2">
                          <input
                            v-model="floor.name"
                            type="text"
                            placeholder="Kat adı (örn: 1. Kat)"
                            class="text-sm font-medium bg-white border border-gray-300 rounded px-2 py-1 focus:ring-2 focus:ring-indigo-500 flex-1"
                          />
                          <button
                            type="button"
                            @click="removeFloor(sIndex, fIndex)"
                            class="ml-2 p-1.5 text-red-600 hover:bg-red-50 rounded"
                          >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                          </button>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                          <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Kat Numarası</label>
                            <Input
                              v-model="floor.floor_number"
                              type="number"
                              placeholder="1, 2, -1 (bodrum)"
                              class="text-sm"
                            />
                          </div>
                          <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Kat Tipi</label>
                            <Select
                              v-model="floor.floor_type"
                              :options="floorTypeOptions"
                              class="text-sm"
                            />
                          </div>
                          <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Durum</label>
                            <Select
                              v-model="floor.status"
                              :options="structureStatusOptions"
                              class="text-sm"
                            />
                          </div>
                        </div>

                        <!-- Units Section -->
                        <div class="mt-3 border-t border-gray-300 pt-3">
                          <div class="flex items-center justify-between mb-2">
                            <h5 class="text-xs font-semibold text-gray-800">Birimler (Daireler)</h5>
                            <button
                              type="button"
                              @click="addUnit(sIndex, fIndex)"
                              class="px-2 py-1 bg-teal-600 text-white rounded text-xs hover:bg-teal-700 flex items-center gap-1"
                            >
                              <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                              </svg>
                              Birim
                            </button>
                          </div>

                          <div v-if="floor.units.length === 0" class="text-center py-2 bg-white rounded text-xs text-gray-500 border border-gray-200">
                            Henüz birim eklenmedi
                          </div>

                          <div v-else class="space-y-2">
                            <div
                              v-for="(unit, uIndex) in floor.units"
                              :key="uIndex"
                              class="bg-white p-2 rounded border border-gray-300"
                            >
                              <div class="flex items-center justify-between mb-2">
                                <input
                                  v-model="unit.unit_code"
                                  type="text"
                                  placeholder="Birim kodu (örn: D1, D2)"
                                  class="text-xs font-medium bg-gray-50 border border-gray-200 rounded px-2 py-1 focus:ring-2 focus:ring-teal-500 flex-1"
                                />
                                <button
                                  type="button"
                                  @click="removeUnit(sIndex, fIndex, uIndex)"
                                  class="ml-2 p-1 text-red-600 hover:bg-red-50 rounded"
                                >
                                  <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                  </svg>
                                </button>
                              </div>

                              <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                                <div>
                                  <label class="block text-xs text-gray-600 mb-1">Tip</label>
                                  <Select
                                    v-model="unit.unit_type"
                                    :options="unitTypeOptions"
                                    class="text-xs"
                                  />
                                </div>
                                <div>
                                  <label class="block text-xs text-gray-600 mb-1">Oda</label>
                                  <Input
                                    v-model="unit.room_count"
                                    type="text"
                                    placeholder="3+1"
                                    class="text-xs"
                                  />
                                </div>
                                <div>
                                  <label class="block text-xs text-gray-600 mb-1">Brüt m²</label>
                                  <Input
                                    v-model="unit.gross_area"
                                    type="number"
                                    step="0.01"
                                    placeholder="120.00"
                                    class="text-xs"
                                  />
                                </div>
                                <div>
                                  <label class="block text-xs text-gray-600 mb-1">Net m²</label>
                                  <Input
                                    v-model="unit.net_area"
                                    type="number"
                                    step="0.01"
                                    placeholder="100.00"
                                    class="text-xs"
                                  />
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </Card>

        <!-- Management & Client Information -->
        <Card>
          <div class="p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Yönetim ve Müşteri Bilgileri</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Proje Yöneticisi
                </label>
                <Select
                  v-model="form.project_manager_id"
                  :options="managerOptions"
                  :error="errors.project_manager_id"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Şantiye Şefi
                </label>
                <Select
                  v-model="form.site_manager_id"
                  :options="managerOptions"
                  :error="errors.site_manager_id"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Müşteri Adı
                </label>
                <Input
                  v-model="form.client_name"
                  type="text"
                  placeholder="Müşteri/Firma adı"
                  :error="errors.client_name"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Müşteri İletişim
                </label>
                <Input
                  v-model="form.client_contact"
                  type="text"
                  placeholder="Telefon veya e-posta"
                  :error="errors.client_contact"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  İletişim Telefonu
                </label>
                <Input
                  v-model="form.contact_phone"
                  type="tel"
                  placeholder="0xxx xxx xx xx"
                  :error="errors.contact_phone"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  İletişim E-posta
                </label>
                <Input
                  v-model="form.contact_email"
                  type="email"
                  placeholder="ornek@email.com"
                  :error="errors.contact_email"
                />
              </div>

              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Notlar
                </label>
                <textarea
                  v-model="form.notes"
                  rows="3"
                  class="w-full rounded-lg border-gray-300 shadow-sm focus:border-cyan-500 focus:ring-cyan-500"
                  placeholder="Ek notlar ve bilgiler"
                ></textarea>
              </div>
            </div>
          </div>
        </Card>

        <!-- Form Actions -->
        <Card>
          <div class="p-6 flex items-center justify-between">
            <Link
              :href="route('projects.index')"
              class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors"
            >
              İptal
            </Link>
            <button
              type="submit"
              :disabled="processing"
              class="px-6 py-2 bg-cyan-600 text-white rounded-lg hover:bg-cyan-700 disabled:opacity-50 transition-colors"
            >
              {{ processing ? 'Kaydediliyor...' : 'Projeyi Kaydet' }}
            </button>
          </div>
        </Card>
      </form>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Card from '@/Components/UI/Card.vue'
import Input from '@/Components/UI/Input.vue'
import Select from '@/Components/UI/Select.vue'

const props = defineProps({
  managers: Array,
  errors: {
    type: Object,
    default: () => ({})
  }
})

const form = ref({
  name: '',
  description: '',
  location: '',
  city: '',
  district: '',
  full_address: '',
  coordinates: '',
  start_date: '',
  planned_end_date: '',
  budget: '',
  labor_budget: '',
  project_manager_id: '',
  site_manager_id: '',
  contact_phone: '',
  contact_email: '',
  type: '',
  priority: 'medium',
  client_name: '',
  client_contact: '',
  estimated_employees: '',
  notes: '',
  structures: []
})

const processing = ref(false)
const expandedStructures = ref({})

// Structure Types
const structureTypeOptions = [
  { value: 'residential_block', label: 'Konut Bloğu' },
  { value: 'office_block', label: 'Ofis Bloğu' },
  { value: 'commercial', label: 'Ticari' },
  { value: 'villa', label: 'Villa' },
  { value: 'infrastructure', label: 'Altyapı' },
  { value: 'mixed_use', label: 'Karma Kullanım' },
  { value: 'other', label: 'Diğer' }
]

// Floor Types
const floorTypeOptions = [
  { value: 'basement', label: 'Bodrum' },
  { value: 'ground', label: 'Zemin' },
  { value: 'standard', label: 'Normal Kat' },
  { value: 'roof', label: 'Çatı' },
  { value: 'technical', label: 'Teknik Kat' }
]

// Unit Types
const unitTypeOptions = [
  { value: 'apartment', label: 'Daire' },
  { value: 'office', label: 'Ofis' },
  { value: 'shop', label: 'Dükkan' },
  { value: 'storage', label: 'Depo' },
  { value: 'parking', label: 'Park Yeri' },
  { value: 'other', label: 'Diğer' }
]

// Status options for structures/floors/units
const structureStatusOptions = [
  { value: 'not_started', label: 'Başlanmadı' },
  { value: 'in_progress', label: 'Devam Ediyor' },
  { value: 'completed', label: 'Tamamlandı' },
  { value: 'on_hold', label: 'Beklemede' },
  { value: 'cancelled', label: 'İptal Edildi' }
]

// Add new structure
const addStructure = () => {
  const newIndex = form.value.structures.length
  form.value.structures.push({
    code: '',
    name: '',
    structure_type: 'residential_block',
    total_floors: '',
    total_units: '',
    total_area: '',
    status: 'not_started',
    description: '',
    floors: []
  })
  expandedStructures.value[newIndex] = true
}

// Remove structure
const removeStructure = (index) => {
  form.value.structures.splice(index, 1)
  delete expandedStructures.value[index]
}

// Toggle structure expansion
const toggleStructure = (index) => {
  expandedStructures.value[index] = !expandedStructures.value[index]
}

// Add floor to structure
const addFloor = (structureIndex) => {
  form.value.structures[structureIndex].floors.push({
    floor_number: '',
    name: '',
    floor_type: 'standard',
    status: 'not_started',
    description: '',
    units: []
  })
}

// Remove floor
const removeFloor = (structureIndex, floorIndex) => {
  form.value.structures[structureIndex].floors.splice(floorIndex, 1)
}

// Add unit to floor
const addUnit = (structureIndex, floorIndex) => {
  form.value.structures[structureIndex].floors[floorIndex].units.push({
    unit_code: '',
    unit_type: 'apartment',
    room_count: '',
    gross_area: '',
    net_area: '',
    balcony_area: '',
    status: 'not_started',
    description: ''
  })
}

// Remove unit
const removeUnit = (structureIndex, floorIndex, unitIndex) => {
  form.value.structures[structureIndex].floors[floorIndex].units.splice(unitIndex, 1)
}

const typeOptions = [
  { value: '', label: 'Seçiniz' },
  { value: 'residential', label: 'Konut' },
  { value: 'commercial', label: 'Ticari' },
  { value: 'infrastructure', label: 'Altyapı' },
  { value: 'industrial', label: 'Endüstriyel' },
  { value: 'other', label: 'Diğer' }
]

const statusOptions = [
  { value: 'planning', label: 'Planlama' },
  { value: 'active', label: 'Aktif' },
  { value: 'on_hold', label: 'Beklemede' },
  { value: 'completed', label: 'Tamamlandı' },
  { value: 'cancelled', label: 'İptal Edildi' }
]

const priorityOptions = [
  { value: 'low', label: 'Düşük' },
  { value: 'medium', label: 'Orta' },
  { value: 'high', label: 'Yüksek' },
  { value: 'critical', label: 'Kritik' }
]

const managerOptions = [
  { value: '', label: 'Seçiniz' },
  ...props.managers.map(manager => ({
    value: manager.id,
    label: `${manager.first_name} ${manager.last_name}${manager.position ? ` - ${manager.position}` : ''}`
  }))
]

const submit = () => {
  processing.value = true
  console.log('Form data being sent:', form.value)

  router.post(route('projects.store'), form.value, {
    preserveState: false,
    preserveScroll: false,
    onError: (errors) => {
      console.error('Validation errors:', errors)
      alert('Hata: Lütfen formu kontrol edin. Detaylar console\'da.')
      processing.value = false
    },
    onSuccess: () => {
      console.log('Success! Redirecting...')
    },
    onFinish: () => {
      processing.value = false
    }
  })
}
</script>
