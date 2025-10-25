<template>
  <AppLayout :title="`${project.name} - Proje Detayı - SPT İnşaat Puantaj Sistemi`" :full-width="true">
    <!-- Full-width header -->
    <template #fullWidthHeader>
      <div class="bg-gradient-to-r from-cyan-600 via-cyan-700 to-teal-800 border-b border-cyan-900/20 w-full">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
          <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
              <div class="flex-shrink-0 w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm">
                <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008z" />
                </svg>
              </div>
              <div>
                <h1 class="text-2xl lg:text-3xl font-bold text-white">{{ project.name }}</h1>
                <p class="text-cyan-100 text-sm mt-1">{{ project.project_code }}</p>
              </div>
            </div>

            <div class="flex items-center space-x-3">
              <Link
                v-if="can_edit"
                :href="route('projects.edit', project.id)"
                class="inline-flex items-center px-4 py-2 bg-white/10 backdrop-blur-sm border border-white/30 text-white text-sm font-medium rounded-lg hover:bg-white/20 transition-all duration-200"
              >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Düzenle
              </Link>
            </div>
          </div>
        </div>

        <!-- Tabs -->
        <div class="bg-black/10 border-t border-white/10">
          <div class="w-full px-4 sm:px-6 lg:px-8">
            <nav class="flex space-x-8 -mb-px">
              <button
                @click="activeTab = 'details'"
                :class="[
                  'py-3 px-1 border-b-2 font-medium text-sm transition-colors',
                  activeTab === 'details'
                    ? 'border-white text-white'
                    : 'border-transparent text-cyan-100 hover:text-white hover:border-cyan-200'
                ]"
              >
                Detaylar
              </button>
              <button
                @click="activeTab = 'structures'"
                :class="[
                  'py-3 px-1 border-b-2 font-medium text-sm transition-colors',
                  activeTab === 'structures'
                    ? 'border-white text-white'
                    : 'border-transparent text-cyan-100 hover:text-white hover:border-cyan-200'
                ]"
              >
                Proje Yapısı
                <span v-if="project.structures?.length" class="ml-2 bg-white/20 px-2 py-0.5 rounded-full text-xs">
                  {{ project.structures.length }}
                </span>
              </button>
              <button
                @click="activeTab = 'subcontractors'"
                :class="[
                  'py-3 px-1 border-b-2 font-medium text-sm transition-colors',
                  activeTab === 'subcontractors'
                    ? 'border-white text-white'
                    : 'border-transparent text-cyan-100 hover:text-white hover:border-cyan-200'
                ]"
              >
                Taşeronlar
                <span v-if="project.subcontractors?.length" class="ml-2 bg-white/20 px-2 py-0.5 rounded-full text-xs">
                  {{ project.subcontractors.length }}
                </span>
              </button>
              <button
                @click="activeTab = 'progress-payments'"
                :class="[
                  'py-3 px-1 border-b-2 font-medium text-sm transition-colors',
                  activeTab === 'progress-payments'
                    ? 'border-white text-white'
                    : 'border-transparent text-cyan-100 hover:text-white hover:border-cyan-200'
                ]"
              >
                Hakediş Kayıtları
                <span v-if="project.progress_payments?.length" class="ml-2 bg-white/20 px-2 py-0.5 rounded-full text-xs">
                  {{ project.progress_payments.length }}
                </span>
              </button>
            </nav>
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
                  <span class="text-xs font-medium text-white">Proje Detayı</span>
                </li>
              </ol>
            </div>
          </div>
        </div>
      </div>
    </template>

    <!-- Main Content -->
    <div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
      <!-- Details Tab -->
      <div v-show="activeTab === 'details'" class="space-y-6">
      <!-- Statistics Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <Card>
          <div class="p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0 bg-blue-100 rounded-lg p-3">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Toplam Çalışan</p>
                <p class="text-2xl font-bold text-gray-900">{{ stats?.total_employees || 0 }}</p>
              </div>
            </div>
          </div>
        </Card>

        <Card>
          <div class="p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0 bg-green-100 rounded-lg p-3">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Bölümler</p>
                <p class="text-2xl font-bold text-gray-900">
                  {{ stats?.completed_departments || 0 }} / {{ stats?.total_departments || 0 }}
                </p>
              </div>
            </div>
          </div>
        </Card>

        <Card>
          <div class="p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0 bg-yellow-100 rounded-lg p-3">
                <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Çalışılan Saat</p>
                <p class="text-2xl font-bold text-gray-900">{{ Math.round(stats?.total_hours_worked || 0) }}</p>
              </div>
            </div>
          </div>
        </Card>

        <Card>
          <div class="p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0 bg-purple-100 rounded-lg p-3">
                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Bu Ay Harcama</p>
                <p class="text-2xl font-bold text-gray-900">{{ formatCurrency(stats?.this_month_expenses || 0) }}</p>
              </div>
            </div>
          </div>
        </Card>
      </div>

      <!-- Project Information -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Basic Information -->
        <Card>
          <div class="p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Temel Bilgiler</h2>
            <dl class="space-y-3">
              <div>
                <dt class="text-sm font-medium text-gray-500">Proje Kodu</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ project.project_code || '-' }}</dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500">Durum</dt>
                <dd class="mt-1">
                  <Badge :variant="getStatusVariant(project.status)">
                    {{ getStatusLabel(project.status) }}
                  </Badge>
                </dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500">Tür</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ getTypeLabel(project.type) }}</dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500">Öncelik</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ getPriorityLabel(project.priority) }}</dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500">Hafta Tatili Günleri</dt>
                <dd class="mt-1">
                  <div class="flex flex-wrap gap-1">
                    <span
                      v-for="day in getWeekendDaysDisplay(project.weekend_days)"
                      :key="day"
                      class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-cyan-100 text-cyan-800"
                    >
                      {{ day }}
                    </span>
                  </div>
                </dd>
              </div>
              <div v-if="project.description">
                <dt class="text-sm font-medium text-gray-500">Açıklama</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ project.description }}</dd>
              </div>
            </dl>
          </div>
        </Card>

        <!-- Location Information -->
        <Card>
          <div class="p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Konum Bilgileri</h2>
            <dl class="space-y-3">
              <div>
                <dt class="text-sm font-medium text-gray-500">Şehir</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ project.city }}</dd>
              </div>
              <div v-if="project.district">
                <dt class="text-sm font-medium text-gray-500">İlçe</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ project.district }}</dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500">Konum</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ project.location }}</dd>
              </div>
              <div v-if="project.full_address">
                <dt class="text-sm font-medium text-gray-500">Tam Adres</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ project.full_address }}</dd>
              </div>
            </dl>
          </div>
        </Card>

        <!-- Schedule Information -->
        <Card>
          <div class="p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Tarih Bilgileri</h2>
            <dl class="space-y-3">
              <div>
                <dt class="text-sm font-medium text-gray-500">Başlangıç Tarihi</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ formatDate(project.start_date) }}</dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500">Planlanan Bitiş</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ formatDate(project.planned_end_date) }}</dd>
              </div>
              <div v-if="project.actual_end_date">
                <dt class="text-sm font-medium text-gray-500">Gerçekleşen Bitiş</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ formatDate(project.actual_end_date) }}</dd>
              </div>
              <div v-if="stats?.days_remaining">
                <dt class="text-sm font-medium text-gray-500">Kalan Gün</dt>
                <dd class="mt-1 text-sm text-gray-900">
                  {{ stats.days_remaining > 0 ? stats.days_remaining : 'Süre doldu' }}
                </dd>
              </div>
            </dl>
          </div>
        </Card>

        <!-- Budget Information -->
        <Card>
          <div class="p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Bütçe Bilgileri</h2>
            <dl class="space-y-3">
              <div>
                <dt class="text-sm font-medium text-gray-500">Toplam Bütçe</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ formatCurrency(project.budget) }}</dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500">İşçilik Bütçesi</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ formatCurrency(project.labor_budget) }}</dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500">Harcanan Tutar</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ formatCurrency(project.spent_amount) }}</dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500">Bütçe Kullanımı</dt>
                <dd class="mt-1">
                  <div class="flex items-center">
                    <div class="flex-1 bg-gray-200 rounded-full h-2 mr-2">
                      <div
                        :class="[
                          'h-2 rounded-full',
                          project.budget_usage_percentage > 100 ? 'bg-red-600' :
                          project.budget_usage_percentage > 90 ? 'bg-orange-600' :
                          'bg-green-600'
                        ]"
                        :style="{ width: `${Math.min(project.budget_usage_percentage, 100)}%` }"
                      ></div>
                    </div>
                    <span class="text-sm text-gray-900">%{{ project.budget_usage_percentage }}</span>
                  </div>
                </dd>
              </div>
            </dl>
          </div>
        </Card>

        <!-- Management Information -->
        <Card>
          <div class="p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Yönetim Bilgileri</h2>
            <dl class="space-y-3">
              <div v-if="project.project_manager">
                <dt class="text-sm font-medium text-gray-500">Proje Yöneticisi</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ project.project_manager.full_name }}</dd>
              </div>
              <div v-if="project.site_manager">
                <dt class="text-sm font-medium text-gray-500">Şantiye Şefi</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ project.site_manager.full_name }}</dd>
              </div>
              <div v-if="project.contact_phone">
                <dt class="text-sm font-medium text-gray-500">İletişim Telefonu</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ project.contact_phone }}</dd>
              </div>
              <div v-if="project.contact_email">
                <dt class="text-sm font-medium text-gray-500">İletişim E-posta</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ project.contact_email }}</dd>
              </div>
            </dl>
          </div>
        </Card>

        <!-- Client Information -->
        <Card v-if="project.client_name || project.client_contact">
          <div class="p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Müşteri Bilgileri</h2>
            <dl class="space-y-3">
              <div v-if="project.client_name">
                <dt class="text-sm font-medium text-gray-500">Müşteri Adı</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ project.client_name }}</dd>
              </div>
              <div v-if="project.client_contact">
                <dt class="text-sm font-medium text-gray-500">Müşteri İletişim</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ project.client_contact }}</dd>
              </div>
            </dl>
          </div>
        </Card>
      </div>

      <!-- Notes -->
      <Card v-if="project.notes">
        <div class="p-6">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Notlar</h2>
          <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ project.notes }}</p>
        </div>
      </Card>
      </div>

      <!-- Structures Tab -->
      <div v-show="activeTab === 'structures'" class="space-y-6">
        <div class="flex items-center justify-between mb-6">
          <div>
            <h2 class="text-2xl font-bold text-gray-900">Proje Yapısı</h2>
            <p class="mt-1 text-sm text-gray-500">Bloklar, katlar ve birimler</p>
          </div>
        </div>

        <!-- Structures List -->
        <div v-if="project.structures && project.structures.length > 0" class="space-y-6">
          <Card v-for="structure in project.structures" :key="structure.id">
            <div class="p-6">
              <!-- Structure Header -->
              <div class="flex items-start justify-between mb-4">
                <div class="flex items-center space-x-4">
                  <div class="flex-shrink-0 w-12 h-12 bg-cyan-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-cyan-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                  </div>
                  <div>
                    <h3 class="text-xl font-bold text-gray-900">{{ structure.name }}</h3>
                    <p class="text-sm text-gray-500">Kod: {{ structure.code }} | {{ getStructureTypeLabel(structure.structure_type) }}</p>
                  </div>
                </div>
                <Badge :variant="getStatusVariant(structure.status)">
                  {{ getStatusLabel(structure.status) }}
                </Badge>
              </div>

              <!-- Structure Stats -->
              <div class="grid grid-cols-4 gap-4 mb-6">
                <div class="bg-gray-50 rounded-lg p-4">
                  <div class="text-sm text-gray-500">Toplam Kat</div>
                  <div class="text-2xl font-bold text-gray-900">{{ structure.calculated_total_floors || 0 }}</div>
                </div>
                <div class="bg-gray-50 rounded-lg p-4">
                  <div class="text-sm text-gray-500">Toplam Birim</div>
                  <div class="text-2xl font-bold text-gray-900">{{ structure.calculated_total_units || 0 }}</div>
                </div>
                <div class="bg-gray-50 rounded-lg p-4">
                  <div class="text-sm text-gray-500">Toplam Alan</div>
                  <div class="text-2xl font-bold text-gray-900">{{ structure.calculated_total_area || 0 }} m²</div>
                </div>
                <div class="bg-gray-50 rounded-lg p-4">
                  <div class="text-sm text-gray-500">İlerleme</div>
                  <div class="text-2xl font-bold text-gray-900">{{ structure.calculated_progress || 0 }}%</div>
                </div>
              </div>

              <!-- Floors -->
              <div v-if="structure.floors && structure.floors.length > 0" class="space-y-4">
                <h4 class="text-lg font-semibold text-gray-900">Katlar</h4>
                <div class="space-y-3">
                  <div v-for="floor in structure.floors" :key="floor.id" class="bg-gray-50 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-3">
                      <div class="flex items-center space-x-3">
                        <div class="flex-shrink-0 w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center">
                          <span class="text-sm font-bold text-indigo-600">{{ floor.floor_number }}</span>
                        </div>
                        <div>
                          <h5 class="font-semibold text-gray-900">{{ floor.name }}</h5>
                          <p class="text-xs text-gray-500">{{ getFloorTypeLabel(floor.floor_type) }}</p>
                        </div>
                      </div>
                      <Badge :variant="getStatusVariant(floor.status)">
                        {{ getStatusLabel(floor.status) }}
                      </Badge>
                    </div>

                    <!-- Units -->
                    <div v-if="floor.units && floor.units.length > 0" class="mt-4">
                      <h6 class="text-sm font-medium text-gray-700 mb-2">Birimler ({{ floor.units.length }})</h6>
                      <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2">
                        <div v-for="unit in floor.units" :key="unit.id" class="bg-white rounded-lg p-3 border border-gray-200">
                          <div class="flex items-center justify-between mb-1">
                            <span class="text-sm font-semibold text-gray-900">{{ unit.unit_code }}</span>
                            <Badge size="sm" :variant="getStatusVariant(unit.status)">
                              {{ getStatusLabel(unit.status) }}
                            </Badge>
                          </div>
                          <div class="text-xs text-gray-600 space-y-1">
                            <div>{{ getUnitTypeLabel(unit.unit_type) }}</div>
                            <div v-if="unit.room_count">{{ unit.room_count }}</div>
                            <div v-if="unit.gross_area" class="flex items-center justify-between">
                              <span>Brüt:</span>
                              <span class="font-medium">{{ unit.gross_area }} m²</span>
                            </div>
                            <div v-if="unit.net_area" class="flex items-center justify-between">
                              <span>Net:</span>
                              <span class="font-medium">{{ unit.net_area }} m²</span>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div v-else class="mt-3 text-xs text-gray-500 text-center py-2 bg-white rounded border border-gray-200">
                      Henüz birim eklenmedi
                    </div>
                  </div>
                </div>
              </div>
              <div v-else class="text-sm text-gray-500 text-center py-4 bg-gray-50 rounded">
                Henüz kat eklenmedi
              </div>

              <!-- Structure Description -->
              <div v-if="structure.description" class="mt-4 pt-4 border-t border-gray-200">
                <h5 class="text-sm font-semibold text-gray-700 mb-2">Açıklama</h5>
                <p class="text-sm text-gray-600">{{ structure.description }}</p>
              </div>
            </div>
          </Card>
        </div>

        <!-- Empty State -->
        <Card v-else>
          <div class="p-12 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Henüz yapı eklenmedi</h3>
            <p class="mt-1 text-sm text-gray-500">Projeye blok, kat ve birimler eklemek için düzenle butonunu kullanın.</p>
            <div class="mt-6">
              <Link
                v-if="can_edit"
                :href="route('projects.edit', project.id)"
                class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-cyan-600 hover:bg-cyan-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-cyan-500"
              >
                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Yapı Ekle
              </Link>
            </div>
          </div>
        </Card>
      </div>

      <!-- Subcontractors Tab -->
      <div v-show="activeTab === 'subcontractors'" class="space-y-6">
        <!-- Header with Add Button -->
        <div class="flex items-center justify-between">
          <div>
            <h2 class="text-2xl font-bold text-gray-900">Taşeronlar</h2>
            <p class="mt-1 text-sm text-gray-500">Bu projeye atanan taşeronları yönetin</p>
          </div>
          <button
            v-if="can_edit"
            @click="showAssignModal = true"
            class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-lg transition-colors"
          >
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Taşeron Ata
          </button>
        </div>

        <!-- Subcontractors List -->
        <div v-if="project.subcontractors && project.subcontractors.length > 0" class="grid grid-cols-1 gap-6">
          <Card v-for="subcontractor in project.subcontractors" :key="subcontractor.pivot.id">
            <div class="p-6">
              <div class="flex items-start justify-between">
                <div class="flex-1">
                  <div class="flex items-center space-x-3">
                    <h3 class="text-lg font-semibold text-gray-900">
                      {{ subcontractor.company_name }}
                    </h3>
                    <Badge :variant="getSubcontractorStatusVariant(subcontractor.pivot.status)">
                      {{ getSubcontractorStatusLabel(subcontractor.pivot.status) }}
                    </Badge>
                  </div>

                  <div class="mt-2 text-sm text-gray-600">
                    <p v-if="subcontractor.category" class="font-medium">
                      {{ subcontractor.category.name }}
                    </p>
                    <p class="mt-1">
                      <span class="font-medium">İş Türü:</span> {{ subcontractor.pivot.work_type }}
                    </p>
                  </div>

                  <div class="mt-4 grid grid-cols-2 gap-4">
                    <div>
                      <dt class="text-xs font-medium text-gray-500">İş Kapsamı</dt>
                      <dd class="mt-1 text-sm text-gray-900">{{ subcontractor.pivot.scope_of_work }}</dd>
                    </div>
                    <div>
                      <dt class="text-xs font-medium text-gray-500">Sözleşme Tutarı</dt>
                      <dd class="mt-1 text-sm text-gray-900">
                        {{ formatCurrency(subcontractor.pivot.contract_amount) }}
                      </dd>
                    </div>
                    <div>
                      <dt class="text-xs font-medium text-gray-500">Atanma Tarihi</dt>
                      <dd class="mt-1 text-sm text-gray-900">
                        {{ formatDate(subcontractor.pivot.assigned_date) }}
                      </dd>
                    </div>
                    <div>
                      <dt class="text-xs font-medium text-gray-500">Başlangıç - Bitiş</dt>
                      <dd class="mt-1 text-sm text-gray-900">
                        {{ formatDate(subcontractor.pivot.start_date) }} - {{ formatDate(subcontractor.pivot.end_date) }}
                      </dd>
                    </div>
                  </div>

                  <div v-if="subcontractor.pivot.notes" class="mt-3">
                    <dt class="text-xs font-medium text-gray-500">Notlar</dt>
                    <dd class="mt-1 text-sm text-gray-600">{{ subcontractor.pivot.notes }}</dd>
                  </div>
                </div>

                <div v-if="can_edit" class="ml-4 flex items-start space-x-2">
                  <button
                    @click="editSubcontractor(subcontractor)"
                    class="p-2 text-gray-400 hover:text-blue-600 rounded-lg hover:bg-blue-50 transition-colors"
                    title="Düzenle"
                  >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                  </button>
                  <button
                    @click="removeSubcontractor(subcontractor.pivot.id)"
                    class="p-2 text-gray-400 hover:text-red-600 rounded-lg hover:bg-red-50 transition-colors"
                    title="Çıkar"
                  >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                  </button>
                </div>
              </div>
            </div>
          </Card>
        </div>

        <!-- Empty State -->
        <Card v-else>
          <div class="p-12 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Henüz taşeron atanmadı</h3>
            <p class="mt-1 text-sm text-gray-500">Bu projeye ilk taşeronu atayarak başlayın.</p>
            <div class="mt-6">
              <button
                v-if="can_edit"
                @click="showAssignModal = true"
                class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-lg transition-colors"
              >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                İlk Taşeronu Ata
              </button>
            </div>
          </div>
        </Card>
      </div>

      <!-- Progress Payments Tab -->
      <div v-show="activeTab === 'progress-payments'" class="space-y-6">
        <!-- Header with Add Button -->
        <div class="flex items-center justify-between">
          <div>
            <h2 class="text-2xl font-bold text-gray-900">Hakediş Kayıtları</h2>
            <p class="mt-1 text-sm text-gray-500">Bu projeye ait hakediş kayıtlarını görüntüleyin</p>
          </div>
          <Link
            :href="route('progress-payments.create')"
            class="inline-flex items-center px-4 py-2 bg-cyan-600 hover:bg-cyan-700 text-white text-sm font-medium rounded-lg transition-colors"
          >
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Yeni Hakediş
          </Link>
        </div>

        <!-- Progress Payments List -->
        <Card v-if="project.progress_payments && project.progress_payments.length > 0">
          <!-- Stats Summary -->
          <div class="bg-gradient-to-r from-cyan-50 to-teal-50 p-6 border-b border-gray-200">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
              <div>
                <p class="text-sm font-medium text-gray-600">Toplam Kayıt</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">{{ progressPaymentStats.total }}</p>
              </div>
              <div>
                <p class="text-sm font-medium text-gray-600">Toplam Tutar</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">{{ formatCurrency(progressPaymentStats.totalAmount) }}</p>
              </div>
              <div>
                <p class="text-sm font-medium text-gray-600">Tamamlanan</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">{{ progressPaymentStats.completed }}</p>
              </div>
              <div>
                <p class="text-sm font-medium text-gray-600">Ortalama İlerleme</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">{{ progressPaymentStats.avgProgress }}%</p>
              </div>
            </div>
          </div>

          <!-- Table -->
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Taşeron</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">İş Kalemi</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">İlerleme</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tutar</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Durum</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">İşlemler</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="payment in project.progress_payments" :key="payment.id" class="hover:bg-gray-50">
                  <td class="px-6 py-4 text-sm">
                    <div class="font-medium text-gray-900">{{ payment.subcontractor?.company_name }}</div>
                    <div v-if="payment.period_year && payment.period_month" class="text-xs text-gray-500">
                      {{ payment.period_month }}/{{ payment.period_year }}
                    </div>
                  </td>
                  <td class="px-6 py-4 text-sm text-gray-900">{{ payment.work_item?.name }}</td>
                  <td class="px-6 py-4">
                    <div class="flex items-center">
                      <div class="w-24 bg-gray-200 rounded-full h-2 mr-2">
                        <div
                          class="h-2 rounded-full"
                          :class="payment.completion_percentage >= 100 ? 'bg-green-600' : 'bg-cyan-600'"
                          :style="{ width: `${Math.min(payment.completion_percentage || 0, 100)}%` }"
                        ></div>
                      </div>
                      <span class="text-xs font-medium text-gray-700">{{ payment.completion_percentage || 0 }}%</span>
                    </div>
                    <div class="text-xs text-gray-500 mt-1">
                      {{ payment.completed_quantity }} / {{ payment.planned_quantity }} {{ payment.unit }}
                    </div>
                  </td>
                  <td class="px-6 py-4 text-sm font-bold text-gray-900">
                    {{ formatCurrency(payment.total_amount || 0) }}
                  </td>
                  <td class="px-6 py-4">
                    <Badge :variant="getPaymentStatusVariant(payment.status)">
                      {{ getPaymentStatusLabel(payment.status) }}
                    </Badge>
                  </td>
                  <td class="px-6 py-4 text-sm font-medium">
                    <Link
                      :href="route('progress-payments.show', payment.id)"
                      class="text-cyan-600 hover:text-cyan-900"
                    >
                      Görüntüle
                    </Link>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </Card>

        <!-- Empty State -->
        <Card v-else>
          <div class="p-12 text-center">
            <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <p class="text-gray-500 mb-4">Henüz hakediş kaydı yok</p>
            <Link
              :href="route('progress-payments.create')"
              class="inline-flex items-center px-4 py-2 bg-cyan-600 text-white rounded-lg hover:bg-cyan-700 transition-colors"
            >
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
              </svg>
              İlk Hakediş Kaydını Oluştur
            </Link>
          </div>
        </Card>
      </div>
    </div>

    <!-- Assign/Edit Subcontractor Modal -->
    <Modal :show="showAssignModal" @close="closeModal" size="2xl">
      <div class="p-6">
        <div class="flex items-center justify-between mb-6">
          <h3 class="text-lg font-semibold text-gray-900">
            {{ editingSubcontractor ? 'Taşeron Bilgilerini Düzenle' : 'Taşeron Ata' }}
          </h3>
          <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <form @submit.prevent="submitAssignForm" class="space-y-4">
          <!-- Subcontractor Selection -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Taşeron <span class="text-red-500">*</span>
            </label>
            <select
              v-model="assignForm.subcontractor_id"
              :disabled="editingSubcontractor !== null"
              class="w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 disabled:bg-gray-100"
              required
            >
              <option value="">Taşeron Seçin</option>
              <option
                v-for="sub in available_subcontractors"
                :key="sub.id"
                :value="sub.id"
              >
                {{ sub.company_name }} {{ sub.category ? `(${sub.category.name})` : '' }}
              </option>
            </select>
            <p v-if="assignForm.errors.subcontractor_id" class="mt-1 text-sm text-red-600">
              {{ assignForm.errors.subcontractor_id }}
            </p>
          </div>

          <!-- Work Type -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              İş Türü <span class="text-red-500">*</span>
            </label>
            <input
              v-model="assignForm.work_type"
              type="text"
              placeholder="Örn: Elektrik Tesisatı, Sıhhi Tesisat, Dış Cephe"
              class="w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500"
              required
            />
            <p v-if="assignForm.errors.work_type" class="mt-1 text-sm text-red-600">
              {{ assignForm.errors.work_type }}
            </p>
          </div>

          <!-- Scope of Work -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              İş Kapsamı Detayı <span class="text-red-500">*</span>
            </label>
            <textarea
              v-model="assignForm.scope_of_work"
              rows="3"
              placeholder="İşin kapsamını detaylı olarak açıklayın"
              class="w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500"
              required
            ></textarea>
            <p v-if="assignForm.errors.scope_of_work" class="mt-1 text-sm text-red-600">
              {{ assignForm.errors.scope_of_work }}
            </p>
          </div>

          <!-- Contract Amount -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Sözleşme Tutarı (TRY)
            </label>
            <input
              v-model="assignForm.contract_amount"
              type="number"
              step="0.01"
              min="0"
              placeholder="0.00"
              class="w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500"
            />
            <p v-if="assignForm.errors.contract_amount" class="mt-1 text-sm text-red-600">
              {{ assignForm.errors.contract_amount }}
            </p>
          </div>

          <!-- Dates -->
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Başlangıç Tarihi
              </label>
              <input
                v-model="assignForm.start_date"
                type="date"
                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500"
              />
              <p v-if="assignForm.errors.start_date" class="mt-1 text-sm text-red-600">
                {{ assignForm.errors.start_date }}
              </p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Bitiş Tarihi
              </label>
              <input
                v-model="assignForm.end_date"
                type="date"
                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500"
              />
              <p v-if="assignForm.errors.end_date" class="mt-1 text-sm text-red-600">
                {{ assignForm.errors.end_date }}
              </p>
            </div>
          </div>

          <!-- Status (only when editing) -->
          <div v-if="editingSubcontractor">
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Durum <span class="text-red-500">*</span>
            </label>
            <select
              v-model="assignForm.status"
              class="w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500"
              required
            >
              <option value="active">Aktif</option>
              <option value="completed">Tamamlandı</option>
              <option value="terminated">Sonlandırıldı</option>
              <option value="suspended">Askıya Alındı</option>
            </select>
            <p v-if="assignForm.errors.status" class="mt-1 text-sm text-red-600">
              {{ assignForm.errors.status }}
            </p>
          </div>

          <!-- Notes -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Notlar
            </label>
            <textarea
              v-model="assignForm.notes"
              rows="2"
              placeholder="İsteğe bağlı notlar"
              class="w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500"
            ></textarea>
            <p v-if="assignForm.errors.notes" class="mt-1 text-sm text-red-600">
              {{ assignForm.errors.notes }}
            </p>
          </div>

          <!-- Actions -->
          <div class="flex items-center justify-end space-x-3 pt-4 border-t">
            <button
              type="button"
              @click="closeModal"
              class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
            >
              İptal
            </button>
            <button
              type="submit"
              :disabled="assignForm.processing"
              class="px-4 py-2 text-sm font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700 disabled:opacity-50"
            >
              {{ assignForm.processing ? 'Kaydediliyor...' : (editingSubcontractor ? 'Güncelle' : 'Ata') }}
            </button>
          </div>
        </form>
      </div>
    </Modal>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, router, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Card from '@/Components/UI/Card.vue'
import Badge from '@/Components/UI/Badge.vue'
import Modal from '@/Components/UI/Modal.vue'
import { format, parseISO } from 'date-fns'
import { tr } from 'date-fns/locale'

const props = defineProps({
  project: Object,
  stats: Object,
  recent_activities: Array,
  can_edit: Boolean,
  available_subcontractors: Array
})

const activeTab = ref('details')
const showAssignModal = ref(false)
const editingSubcontractor = ref(null)

const assignForm = useForm({
  subcontractor_id: '',
  work_type: '',
  scope_of_work: '',
  contract_amount: '',
  start_date: '',
  end_date: '',
  notes: ''
})

const formatDate = (date) => {
  if (!date) return '-'
  try {
    return format(parseISO(date), 'dd MMMM yyyy', { locale: tr })
  } catch (e) {
    return date
  }
}

const formatCurrency = (amount) => {
  if (!amount && amount !== 0) return '-'
  return new Intl.NumberFormat('tr-TR', {
    style: 'currency',
    currency: 'TRY',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0
  }).format(amount)
}

const getStatusLabel = (status) => {
  const labels = {
    planning: 'Planlama',
    active: 'Aktif',
    on_hold: 'Beklemede',
    completed: 'Tamamlandı',
    cancelled: 'İptal',
    not_started: 'Başlanmadı',
    in_progress: 'Devam Ediyor'
  }
  return labels[status] || status
}

const getStatusVariant = (status) => {
  const variants = {
    planning: 'warning',
    active: 'success',
    on_hold: 'info',
    completed: 'primary',
    cancelled: 'danger',
    not_started: 'secondary',
    in_progress: 'info'
  }
  return variants[status] || 'secondary'
}

const getTypeLabel = (type) => {
  const labels = {
    residential: 'Konut',
    commercial: 'Ticari',
    infrastructure: 'Altyapı',
    industrial: 'Endüstriyel',
    other: 'Diğer'
  }
  return labels[type] || type
}

const getPriorityLabel = (priority) => {
  const labels = {
    low: 'Düşük',
    medium: 'Orta',
    high: 'Yüksek',
    critical: 'Kritik'
  }
  return labels[priority] || priority
}

const getWeekendDaysDisplay = (weekendDays) => {
  if (!weekendDays || !Array.isArray(weekendDays)) {
    return ['Cumartesi', 'Pazar'] // Default
  }

  const dayNames = {
    monday: 'Pazartesi',
    tuesday: 'Salı',
    wednesday: 'Çarşamba',
    thursday: 'Perşembe',
    friday: 'Cuma',
    saturday: 'Cumartesi',
    sunday: 'Pazar'
  }

  return weekendDays.map(day => dayNames[day] || day)
}

const getSubcontractorStatusLabel = (status) => {
  const labels = {
    active: 'Aktif',
    completed: 'Tamamlandı',
    terminated: 'Sonlandırıldı',
    suspended: 'Askıya Alındı'
  }
  return labels[status] || status
}

const getSubcontractorStatusVariant = (status) => {
  const variants = {
    active: 'success',
    completed: 'primary',
    terminated: 'danger',
    suspended: 'warning'
  }
  return variants[status] || 'secondary'
}

const submitAssignForm = () => {
  if (editingSubcontractor.value) {
    // Update existing
    assignForm.transform((data) => ({
      ...data,
      pivot_id: editingSubcontractor.value.pivot.id
    })).patch(route('projects.update-subcontractor', props.project.id), {
      preserveScroll: true,
      onSuccess: () => {
        showAssignModal.value = false
        editingSubcontractor.value = null
        assignForm.reset()
      }
    })
  } else {
    // Create new
    assignForm.post(route('projects.assign-subcontractor', props.project.id), {
      preserveScroll: true,
      onSuccess: () => {
        showAssignModal.value = false
        assignForm.reset()
      }
    })
  }
}

const editSubcontractor = (subcontractor) => {
  editingSubcontractor.value = subcontractor
  assignForm.subcontractor_id = subcontractor.id
  assignForm.work_type = subcontractor.pivot.work_type
  assignForm.scope_of_work = subcontractor.pivot.scope_of_work
  assignForm.contract_amount = subcontractor.pivot.contract_amount
  assignForm.start_date = subcontractor.pivot.start_date
  assignForm.end_date = subcontractor.pivot.end_date
  assignForm.notes = subcontractor.pivot.notes
  assignForm.status = subcontractor.pivot.status
  showAssignModal.value = true
}

const removeSubcontractor = (pivotId) => {
  if (confirm('Bu taşeronu projeden çıkarmak istediğinizden emin misiniz?')) {
    router.delete(route('projects.remove-subcontractor', props.project.id), {
      data: { pivot_id: pivotId },
      preserveScroll: true
    })
  }
}

const closeModal = () => {
  showAssignModal.value = false
  editingSubcontractor.value = null
  assignForm.reset()
}

// Helper functions for structures
const getStructureTypeLabel = (type) => {
  const types = {
    'residential_block': 'Konut Bloğu',
    'office_block': 'Ofis Bloğu',
    'commercial': 'Ticari',
    'villa': 'Villa',
    'infrastructure': 'Altyapı',
    'mixed_use': 'Karma Kullanım',
    'other': 'Diğer'
  }
  return types[type] || type
}

const getFloorTypeLabel = (type) => {
  const types = {
    'basement': 'Bodrum',
    'ground': 'Zemin',
    'standard': 'Normal Kat',
    'roof': 'Çatı',
    'penthouse': 'Penthouse',
    'technical': 'Teknik Kat',
    'mezzanine': 'Asma Kat'
  }
  return types[type] || type
}

const getUnitTypeLabel = (type) => {
  const types = {
    'apartment': 'Daire',
    'office': 'Ofis',
    'shop': 'Dükkan',
    'warehouse': 'Depo',
    'parking_space': 'Otopark',
    'storage': 'Depo',
    'technical_room': 'Teknik Oda',
    'common_area': 'Ortak Alan',
    'other': 'Diğer'
  }
  return types[type] || type
}

// Progress Payment Stats
const progressPaymentStats = computed(() => {
  if (!props.project.progress_payments || props.project.progress_payments.length === 0) return {
    total: 0,
    totalAmount: 0,
    completed: 0,
    avgProgress: 0
  }

  const payments = props.project.progress_payments
  const totalAmount = payments.reduce((sum, p) => {
    const amount = parseFloat(p.total_amount) || 0
    return sum + amount
  }, 0)

  return {
    total: payments.length,
    totalAmount: totalAmount,
    completed: payments.filter(p => ['completed', 'approved', 'paid'].includes(p.status)).length,
    avgProgress: payments.length > 0
      ? Math.round(payments.reduce((sum, p) => sum + (parseFloat(p.completion_percentage) || 0), 0) / payments.length)
      : 0
  }
})

// Progress Payment Status Helpers
const getPaymentStatusVariant = (status) => {
  const variants = {
    planned: 'secondary',
    in_progress: 'info',
    completed: 'primary',
    approved: 'success',
    paid: 'success'
  }
  return variants[status] || 'secondary'
}

const getPaymentStatusLabel = (status) => {
  const labels = {
    planned: 'Planlandı',
    in_progress: 'Devam Ediyor',
    completed: 'Tamamlandı',
    approved: 'Onaylandı',
    paid: 'Ödendi'
  }
  return labels[status] || status
}
</script>
