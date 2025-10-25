<script setup>
import { ref, computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { format, parseISO } from 'date-fns'
import { tr } from 'date-fns/locale'

const props = defineProps({
    subcontractor: Object,
    certificationStats: Object,
    ratingStats: Object,
})

// Active tab state
const activeTab = ref('info')

// Format tarih
const formatDate = (date) => {
    if (!date) return '-'
    try {
        return format(parseISO(date), 'd MMMM yyyy', { locale: tr })
    } catch (e) {
        return date
    }
}

// Format currency
const formatCurrency = (amount) => {
    return new Intl.NumberFormat('tr-TR', {
        style: 'currency',
        currency: 'TRY',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(amount)
}

// Progress payment stats
const progressPaymentStats = computed(() => {
    if (!props.subcontractor.progress_payments || props.subcontractor.progress_payments.length === 0) return {
        total: 0,
        totalAmount: 0,
        completed: 0,
        avgProgress: 0
    }

    const payments = props.subcontractor.progress_payments
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

// Durum renkleri
const getStatusColor = (status) => {
    const colors = {
        active: 'bg-green-100 text-green-800',
        inactive: 'bg-gray-100 text-gray-800',
        blacklisted: 'bg-red-100 text-red-800',
    }
    return colors[status] || 'bg-gray-100 text-gray-800'
}

// Puan renkleri
const getRatingColor = (rating) => {
    if (rating >= 4.5) return 'text-green-600'
    if (rating >= 4.0) return 'text-blue-600'
    if (rating >= 3.0) return 'text-yellow-600'
    if (rating >= 2.0) return 'text-orange-600'
    return 'text-red-600'
}

// Belge durumu renkleri
const getCertStatusColor = (status) => {
    const colors = {
        valid: 'bg-green-100 text-green-800',
        expired: 'bg-red-100 text-red-800',
        pending: 'bg-yellow-100 text-yellow-800',
    }
    return colors[status] || 'bg-gray-100 text-gray-800'
}

// Progress payment status colors
const getPaymentStatusClass = (status) => {
    const classes = {
        planned: 'bg-gray-100 text-gray-700',
        in_progress: 'bg-blue-100 text-blue-700',
        completed: 'bg-purple-100 text-purple-700',
        approved: 'bg-green-100 text-green-700',
        paid: 'bg-emerald-100 text-emerald-700'
    }
    return classes[status] || 'bg-gray-100 text-gray-700'
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

<template>
    <AppLayout :full-width="true">
        <!-- Full Width Header -->
        <template #fullWidthHeader>
            <div class="bg-gradient-to-r from-purple-600 to-indigo-600 shadow-lg">
                <div class="px-4 sm:px-6 lg:px-8 py-6">
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex-1">
                            <div class="flex items-center space-x-4">
                                <h1 class="text-3xl font-bold text-white">{{ subcontractor.company_name }}</h1>
                                <span :class="['inline-flex text-sm leading-5 font-semibold rounded-full px-3 py-1', getStatusColor(subcontractor.status)]">
                                    {{ subcontractor.status_badge?.text || subcontractor.status }}
                                </span>
                                <span v-if="subcontractor.is_approved" class="inline-flex text-sm leading-5 font-semibold rounded-full px-3 py-1 bg-blue-100 text-blue-800">
                                    ✓ Onaylı
                                </span>
                            </div>
                            <p v-if="subcontractor.trade_title" class="mt-2 text-purple-100">{{ subcontractor.trade_title }}</p>
                            <p v-if="subcontractor.category" class="mt-1 text-purple-200 text-sm">{{ subcontractor.category.name }}</p>
                        </div>
                        <div class="flex items-center space-x-2">
                            <Link
                                :href="route('subcontractors.edit', subcontractor.id)"
                                class="inline-flex items-center px-4 py-2 bg-white border border-transparent rounded-md font-semibold text-xs text-purple-600 uppercase tracking-widest hover:bg-purple-50"
                            >
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                Düzenle
                            </Link>
                            <Link
                                :href="route('subcontractors.index')"
                                class="inline-flex items-center px-4 py-2 bg-white bg-opacity-20 border border-white border-opacity-30 rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-opacity-30"
                            >
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                                </svg>
                                Geri
                            </Link>
                        </div>
                    </div>

                    <!-- Stats -->
                    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                        <div class="bg-white/10 backdrop-blur-sm rounded-lg border border-white/30 p-4">
                            <p class="text-sm font-medium text-purple-100">Genel Puan</p>
                            <div class="flex items-center mt-2">
                                <p class="text-3xl font-bold text-yellow-300 mr-2">
                                    {{ subcontractor.rating ? Number(subcontractor.rating).toFixed(2) : '0.00' }}
                                </p>
                                <p class="text-sm text-white">/ 5.00</p>
                            </div>
                            <p class="text-xs text-yellow-200 mt-1">{{ subcontractor.rating_stars || '☆☆☆☆☆' }}</p>
                        </div>
                        <div class="bg-white/10 backdrop-blur-sm rounded-lg border border-white/30 p-4">
                            <p class="text-sm font-medium text-purple-100">Toplam Hakediş</p>
                            <p class="text-3xl font-bold text-white mt-2">{{ progressPaymentStats.total }}</p>
                            <p class="text-xs text-purple-100 mt-1">{{ formatCurrency(progressPaymentStats.totalAmount) }}</p>
                        </div>
                        <div class="bg-white/10 backdrop-blur-sm rounded-lg border border-white/30 p-4">
                            <p class="text-sm font-medium text-purple-100">Ortalama İlerleme</p>
                            <p class="text-3xl font-bold text-white mt-2">{{ progressPaymentStats.avgProgress }}%</p>
                        </div>
                        <div class="bg-white/10 backdrop-blur-sm rounded-lg border border-white/30 p-4">
                            <p class="text-sm font-medium text-purple-100">Geçerli Belge</p>
                            <p class="text-3xl font-bold text-white mt-2">{{ certificationStats?.valid || 0 }}</p>
                        </div>
                        <div class="bg-white/10 backdrop-blur-sm rounded-lg border border-white/30 p-4">
                            <p class="text-sm font-medium text-purple-100">Değerlendirme</p>
                            <p class="text-3xl font-bold text-white mt-2">{{ ratingStats?.count || 0 }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Tabs -->
                <div class="bg-white rounded-lg shadow-sm mb-6">
                    <div class="border-b border-gray-200">
                        <nav class="-mb-px flex space-x-8 px-6" aria-label="Tabs">
                            <button
                                @click="activeTab = 'info'"
                                :class="[
                                    'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm',
                                    activeTab === 'info'
                                        ? 'border-purple-500 text-purple-600'
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                                ]"
                            >
                                Genel Bilgiler
                            </button>
                            <button
                                @click="activeTab = 'progress-payments'"
                                :class="[
                                    'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm',
                                    activeTab === 'progress-payments'
                                        ? 'border-purple-500 text-purple-600'
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                                ]"
                            >
                                Hakediş Kayıtları ({{ subcontractor.progress_payments?.length || 0 }})
                            </button>
                            <button
                                @click="activeTab = 'certifications'"
                                :class="[
                                    'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm',
                                    activeTab === 'certifications'
                                        ? 'border-purple-500 text-purple-600'
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                                ]"
                            >
                                Belgeler ({{ subcontractor.certifications?.length || 0 }})
                            </button>
                            <button
                                @click="activeTab = 'ratings'"
                                :class="[
                                    'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm',
                                    activeTab === 'ratings'
                                        ? 'border-purple-500 text-purple-600'
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                                ]"
                            >
                                Değerlendirmeler ({{ subcontractor.ratings?.length || 0 }})
                            </button>
                        </nav>
                    </div>
                </div>

                <!-- Tab Content: Genel Bilgiler -->
                <div v-show="activeTab === 'info'" class="space-y-6">
                    <!-- Firma Bilgileri -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Firma Bilgileri</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Firma Adı</p>
                                <p class="mt-1 text-sm text-gray-900">{{ subcontractor.company_name }}</p>
                            </div>
                            <div v-if="subcontractor.trade_title">
                                <p class="text-sm font-medium text-gray-500">Ticari Ünvan</p>
                                <p class="mt-1 text-sm text-gray-900">{{ subcontractor.trade_title }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Vergi Dairesi</p>
                                <p class="mt-1 text-sm text-gray-900">{{ subcontractor.tax_office }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Vergi Numarası</p>
                                <p class="mt-1 text-sm text-gray-900">{{ subcontractor.tax_number }}</p>
                            </div>
                            <div v-if="subcontractor.category">
                                <p class="text-sm font-medium text-gray-500">Kategori</p>
                                <p class="mt-1 text-sm text-gray-900">{{ subcontractor.category.name }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Kayıt Tarihi</p>
                                <p class="mt-1 text-sm text-gray-900">{{ formatDate(subcontractor.created_at) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Adres ve İletişim -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Adres ve İletişim</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div v-if="subcontractor.address" class="md:col-span-2">
                                <p class="text-sm font-medium text-gray-500">Adres</p>
                                <p class="mt-1 text-sm text-gray-900">{{ subcontractor.address }}</p>
                            </div>
                            <div v-if="subcontractor.city">
                                <p class="text-sm font-medium text-gray-500">Şehir</p>
                                <p class="mt-1 text-sm text-gray-900">{{ subcontractor.city }}</p>
                            </div>
                            <div v-if="subcontractor.district">
                                <p class="text-sm font-medium text-gray-500">İlçe</p>
                                <p class="mt-1 text-sm text-gray-900">{{ subcontractor.district }}</p>
                            </div>
                            <div v-if="subcontractor.postal_code">
                                <p class="text-sm font-medium text-gray-500">Posta Kodu</p>
                                <p class="mt-1 text-sm text-gray-900">{{ subcontractor.postal_code }}</p>
                            </div>
                            <div v-if="subcontractor.phone">
                                <p class="text-sm font-medium text-gray-500">Telefon</p>
                                <p class="mt-1 text-sm text-gray-900">
                                    <a :href="`tel:${subcontractor.phone}`" class="text-purple-600 hover:text-purple-900">
                                        {{ subcontractor.phone }}
                                    </a>
                                </p>
                            </div>
                            <div v-if="subcontractor.fax">
                                <p class="text-sm font-medium text-gray-500">Fax</p>
                                <p class="mt-1 text-sm text-gray-900">{{ subcontractor.fax }}</p>
                            </div>
                            <div v-if="subcontractor.email">
                                <p class="text-sm font-medium text-gray-500">E-posta</p>
                                <p class="mt-1 text-sm text-gray-900">
                                    <a :href="`mailto:${subcontractor.email}`" class="text-purple-600 hover:text-purple-900">
                                        {{ subcontractor.email }}
                                    </a>
                                </p>
                            </div>
                            <div v-if="subcontractor.website">
                                <p class="text-sm font-medium text-gray-500">Website</p>
                                <p class="mt-1 text-sm text-gray-900">
                                    <a :href="subcontractor.website" target="_blank" class="text-purple-600 hover:text-purple-900">
                                        {{ subcontractor.website }}
                                    </a>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Yetkili Kişi -->
                    <div v-if="subcontractor.authorized_person" class="bg-white rounded-lg shadow-sm p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Yetkili Kişi</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Ad Soyad</p>
                                <p class="mt-1 text-sm text-gray-900">{{ subcontractor.authorized_person }}</p>
                            </div>
                            <div v-if="subcontractor.authorized_title">
                                <p class="text-sm font-medium text-gray-500">Ünvan</p>
                                <p class="mt-1 text-sm text-gray-900">{{ subcontractor.authorized_title }}</p>
                            </div>
                            <div v-if="subcontractor.authorized_phone">
                                <p class="text-sm font-medium text-gray-500">Telefon</p>
                                <p class="mt-1 text-sm text-gray-900">
                                    <a :href="`tel:${subcontractor.authorized_phone}`" class="text-purple-600 hover:text-purple-900">
                                        {{ subcontractor.authorized_phone }}
                                    </a>
                                </p>
                            </div>
                            <div v-if="subcontractor.authorized_email">
                                <p class="text-sm font-medium text-gray-500">E-posta</p>
                                <p class="mt-1 text-sm text-gray-900">
                                    <a :href="`mailto:${subcontractor.authorized_email}`" class="text-purple-600 hover:text-purple-900">
                                        {{ subcontractor.authorized_email }}
                                    </a>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Banka Bilgileri -->
                    <div v-if="subcontractor.bank_name || subcontractor.iban" class="bg-white rounded-lg shadow-sm p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Banka Bilgileri</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div v-if="subcontractor.bank_name">
                                <p class="text-sm font-medium text-gray-500">Banka Adı</p>
                                <p class="mt-1 text-sm text-gray-900">{{ subcontractor.bank_name }}</p>
                            </div>
                            <div v-if="subcontractor.branch_name">
                                <p class="text-sm font-medium text-gray-500">Şube Adı</p>
                                <p class="mt-1 text-sm text-gray-900">{{ subcontractor.branch_name }}</p>
                            </div>
                            <div v-if="subcontractor.branch_code">
                                <p class="text-sm font-medium text-gray-500">Şube Kodu</p>
                                <p class="mt-1 text-sm text-gray-900">{{ subcontractor.branch_code }}</p>
                            </div>
                            <div v-if="subcontractor.account_number">
                                <p class="text-sm font-medium text-gray-500">Hesap Numarası</p>
                                <p class="mt-1 text-sm text-gray-900 font-mono">{{ subcontractor.account_number }}</p>
                            </div>
                            <div v-if="subcontractor.iban" class="md:col-span-2">
                                <p class="text-sm font-medium text-gray-500">IBAN</p>
                                <p class="mt-1 text-sm text-gray-900 font-mono">{{ subcontractor.iban }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Notlar -->
                    <div v-if="subcontractor.notes" class="bg-white rounded-lg shadow-sm p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Notlar</h3>
                        <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ subcontractor.notes }}</p>
                    </div>
                </div>

                <!-- Tab Content: Hakediş Kayıtları -->
                <div v-show="activeTab === 'progress-payments'" class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div v-if="!subcontractor.progress_payments || subcontractor.progress_payments.length === 0" class="p-12 text-center">
                        <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <p class="text-gray-500 mb-4">Henüz hakediş kaydı yok</p>
                        <Link
                            :href="route('progress-payments.create')"
                            class="inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors"
                        >
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Yeni Hakediş Ekle
                        </Link>
                    </div>
                    <div v-else>
                        <!-- Stats Summary -->
                        <div class="bg-gradient-to-r from-purple-50 to-indigo-50 p-6 border-b border-gray-200">
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
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Proje</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">İş Kalemi</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">İlerleme</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tutar</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Durum</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="payment in subcontractor.progress_payments" :key="payment.id" class="hover:bg-gray-50">
                                        <td class="px-6 py-4 text-sm">
                                            <div class="font-medium text-gray-900">{{ payment.project?.name }}</div>
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
                                                        :class="payment.completion_percentage >= 100 ? 'bg-green-600' : 'bg-blue-600'"
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
                                            <span
                                                class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold"
                                                :class="getPaymentStatusClass(payment.status)"
                                            >
                                                {{ getPaymentStatusLabel(payment.status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-sm font-medium">
                                            <Link
                                                :href="route('progress-payments.show', payment.id)"
                                                class="text-purple-600 hover:text-purple-900"
                                            >
                                                Görüntüle
                                            </Link>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Tab Content: Belgeler -->
                <div v-show="activeTab === 'certifications'" class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div v-if="!subcontractor.certifications || subcontractor.certifications.length === 0" class="p-12 text-center">
                        <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <p class="text-gray-500">Henüz belge eklenmemiş</p>
                    </div>
                    <div v-else class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Belge Türü</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Belge Adı</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Belge No</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Düzenleyen</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Veriliş</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Geçerlilik</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Durum</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="cert in subcontractor.certifications" :key="cert.id" class="hover:bg-gray-50">
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ cert.type_label }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ cert.certificate_name }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500 font-mono">{{ cert.certificate_number || '-' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ cert.issuing_authority || '-' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ formatDate(cert.issue_date) }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ formatDate(cert.expiry_date) }}</td>
                                    <td class="px-6 py-4">
                                        <span :class="['inline-flex text-xs leading-5 font-semibold rounded-full px-2 py-1', getCertStatusColor(cert.status)]">
                                            {{ cert.status_badge?.text || cert.status }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tab Content: Değerlendirmeler -->
                <div v-show="activeTab === 'ratings'">
                    <!-- Değerlendirme Özeti -->
                    <div v-if="ratingStats?.count > 0" class="bg-white rounded-lg shadow-sm p-6 mb-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Değerlendirme Özeti</h3>
                        <div class="grid grid-cols-2 md:grid-cols-6 gap-4">
                            <div class="bg-gray-50 rounded-lg p-4">
                                <p class="text-sm font-medium text-gray-700">Kalite</p>
                                <p :class="['text-2xl font-bold mt-1', getRatingColor(ratingStats.quality_avg)]">
                                    {{ ratingStats.quality_avg || '0.00' }}
                                </p>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <p class="text-sm font-medium text-gray-700">Zaman</p>
                                <p :class="['text-2xl font-bold mt-1', getRatingColor(ratingStats.timeline_avg)]">
                                    {{ ratingStats.timeline_avg || '0.00' }}
                                </p>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <p class="text-sm font-medium text-gray-700">Güvenlik</p>
                                <p :class="['text-2xl font-bold mt-1', getRatingColor(ratingStats.safety_avg)]">
                                    {{ ratingStats.safety_avg || '0.00' }}
                                </p>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <p class="text-sm font-medium text-gray-700">İletişim</p>
                                <p :class="['text-2xl font-bold mt-1', getRatingColor(ratingStats.communication_avg)]">
                                    {{ ratingStats.communication_avg || '0.00' }}
                                </p>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <p class="text-sm font-medium text-gray-700">Maliyet</p>
                                <p :class="['text-2xl font-bold mt-1', getRatingColor(ratingStats.cost_avg)]">
                                    {{ ratingStats.cost_avg || '0.00' }}
                                </p>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <p class="text-sm font-medium text-gray-700">Genel</p>
                                <p :class="['text-2xl font-bold mt-1', getRatingColor(ratingStats.overall_avg)]">
                                    {{ ratingStats.overall_avg || '0.00' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Değerlendirmeler Listesi -->
                    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                        <div v-if="!subcontractor.ratings || subcontractor.ratings.length === 0" class="p-12 text-center">
                            <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                            </svg>
                            <p class="text-gray-500">Henüz değerlendirme yapılmamış</p>
                        </div>
                        <div v-else class="divide-y divide-gray-200">
                            <div v-for="rating in subcontractor.ratings" :key="rating.id" class="p-6 hover:bg-gray-50">
                                <div class="flex justify-between items-start mb-3">
                                    <div>
                                        <p class="font-medium text-gray-900">{{ rating.project?.name }}</p>
                                        <p class="text-sm text-gray-500">{{ rating.rater?.name }} - {{ formatDate(rating.rated_at) }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p :class="['text-2xl font-bold', getRatingColor(rating.overall_rating)]">
                                            {{ Number(rating.overall_rating).toFixed(1) }}
                                        </p>
                                        <p class="text-xs text-gray-500">/ 5.0</p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-3">
                                    <div>
                                        <p class="text-xs text-gray-500">Kalite</p>
                                        <p class="text-sm font-medium">{{ rating.quality_rating }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500">Zaman</p>
                                        <p class="text-sm font-medium">{{ rating.timeline_rating }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500">Güvenlik</p>
                                        <p class="text-sm font-medium">{{ rating.safety_rating }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500">İletişim</p>
                                        <p class="text-sm font-medium">{{ rating.communication_rating }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500">Maliyet</p>
                                        <p class="text-sm font-medium">{{ rating.cost_rating }}</p>
                                    </div>
                                </div>
                                <div v-if="rating.comment" class="bg-gray-50 rounded p-3">
                                    <p class="text-sm text-gray-700 italic">{{ rating.comment }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
