<script setup>
import { ref } from 'vue'
import { useForm, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    categories: Array,
})

const form = useForm({
    company_name: '',
    trade_title: '',
    tax_office: '',
    tax_number: '',
    address: '',
    city: '',
    district: '',
    postal_code: '',
    phone: '',
    fax: '',
    email: '',
    website: '',
    authorized_person: '',
    authorized_phone: '',
    authorized_email: '',
    authorized_title: '',
    bank_name: '',
    branch_name: '',
    branch_code: '',
    account_number: '',
    iban: '',
    category_id: '',
    status: 'active',
    is_approved: false,
    notes: '',
    tags: [],
})

const submit = () => {
    form.post(route('subcontractors.store'))
}
</script>

<template>
    <AppLayout :full-width="true">
        <!-- Full Width Header -->
        <template #fullWidthHeader>
            <div class="bg-gradient-to-r from-purple-600 to-indigo-600 shadow-lg">
                <div class="px-4 sm:px-6 lg:px-8 py-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-3xl font-bold text-white">Yeni Taşeron Ekle</h1>
                            <p class="mt-2 text-purple-100">Taşeron bilgilerini ekleyin</p>
                        </div>
                        <Link
                            :href="route('subcontractors.index')"
                            class="inline-flex items-center px-4 py-2 bg-white border border-transparent rounded-md font-semibold text-xs text-purple-600 uppercase tracking-widest hover:bg-purple-50 active:bg-purple-100 focus:outline-none focus:border-purple-300 focus:ring focus:ring-purple-200 disabled:opacity-25 transition"
                        >
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                            </svg>
                            Geri Dön
                        </Link>
                    </div>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto px-4 sm:px-6 lg:px-8">
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Firma Bilgileri -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h2 class="text-lg font-medium text-gray-900 mb-4">Firma Bilgileri</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Firma Adı <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="form.company_name"
                                    type="text"
                                    required
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-200"
                                />
                                <p v-if="form.errors.company_name" class="mt-1 text-sm text-red-600">{{ form.errors.company_name }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Ticari Ünvan</label>
                                <input
                                    v-model="form.trade_title"
                                    type="text"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-200"
                                />
                                <p v-if="form.errors.trade_title" class="mt-1 text-sm text-red-600">{{ form.errors.trade_title }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Vergi Dairesi <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="form.tax_office"
                                    type="text"
                                    required
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-200"
                                />
                                <p v-if="form.errors.tax_office" class="mt-1 text-sm text-red-600">{{ form.errors.tax_office }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Vergi Numarası <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="form.tax_number"
                                    type="text"
                                    required
                                    maxlength="20"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-200"
                                />
                                <p v-if="form.errors.tax_number" class="mt-1 text-sm text-red-600">{{ form.errors.tax_number }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Kategori</label>
                                <select
                                    v-model="form.category_id"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-200"
                                >
                                    <option value="">Seçiniz</option>
                                    <option v-for="category in categories" :key="category.id" :value="category.id">
                                        {{ category.name }}
                                    </option>
                                </select>
                                <p v-if="form.errors.category_id" class="mt-1 text-sm text-red-600">{{ form.errors.category_id }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Durum <span class="text-red-500">*</span>
                                </label>
                                <select
                                    v-model="form.status"
                                    required
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-200"
                                >
                                    <option value="active">Aktif</option>
                                    <option value="inactive">Pasif</option>
                                    <option value="blacklisted">Kara Liste</option>
                                </select>
                                <p v-if="form.errors.status" class="mt-1 text-sm text-red-600">{{ form.errors.status }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Adres ve İletişim -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h2 class="text-lg font-medium text-gray-900 mb-4">Adres ve İletişim</h2>
                        <div class="grid grid-cols-1 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Adres</label>
                                <textarea
                                    v-model="form.address"
                                    rows="3"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-200"
                                ></textarea>
                                <p v-if="form.errors.address" class="mt-1 text-sm text-red-600">{{ form.errors.address }}</p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Şehir</label>
                                    <input
                                        v-model="form.city"
                                        type="text"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-200"
                                    />
                                    <p v-if="form.errors.city" class="mt-1 text-sm text-red-600">{{ form.errors.city }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">İlçe</label>
                                    <input
                                        v-model="form.district"
                                        type="text"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-200"
                                    />
                                    <p v-if="form.errors.district" class="mt-1 text-sm text-red-600">{{ form.errors.district }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Posta Kodu</label>
                                    <input
                                        v-model="form.postal_code"
                                        type="text"
                                        maxlength="10"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-200"
                                    />
                                    <p v-if="form.errors.postal_code" class="mt-1 text-sm text-red-600">{{ form.errors.postal_code }}</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Telefon</label>
                                    <input
                                        v-model="form.phone"
                                        type="tel"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-200"
                                    />
                                    <p v-if="form.errors.phone" class="mt-1 text-sm text-red-600">{{ form.errors.phone }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Fax</label>
                                    <input
                                        v-model="form.fax"
                                        type="tel"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-200"
                                    />
                                    <p v-if="form.errors.fax" class="mt-1 text-sm text-red-600">{{ form.errors.fax }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">E-posta</label>
                                    <input
                                        v-model="form.email"
                                        type="email"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-200"
                                    />
                                    <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</p>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Website</label>
                                <input
                                    v-model="form.website"
                                    type="url"
                                    placeholder="https://example.com"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-200"
                                />
                                <p v-if="form.errors.website" class="mt-1 text-sm text-red-600">{{ form.errors.website }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Yetkili Kişi Bilgileri -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h2 class="text-lg font-medium text-gray-900 mb-4">Yetkili Kişi Bilgileri</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Ad Soyad</label>
                                <input
                                    v-model="form.authorized_person"
                                    type="text"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-200"
                                />
                                <p v-if="form.errors.authorized_person" class="mt-1 text-sm text-red-600">{{ form.errors.authorized_person }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Ünvan</label>
                                <input
                                    v-model="form.authorized_title"
                                    type="text"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-200"
                                />
                                <p v-if="form.errors.authorized_title" class="mt-1 text-sm text-red-600">{{ form.errors.authorized_title }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Telefon</label>
                                <input
                                    v-model="form.authorized_phone"
                                    type="tel"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-200"
                                />
                                <p v-if="form.errors.authorized_phone" class="mt-1 text-sm text-red-600">{{ form.errors.authorized_phone }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">E-posta</label>
                                <input
                                    v-model="form.authorized_email"
                                    type="email"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-200"
                                />
                                <p v-if="form.errors.authorized_email" class="mt-1 text-sm text-red-600">{{ form.errors.authorized_email }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Banka Bilgileri -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h2 class="text-lg font-medium text-gray-900 mb-4">Banka Bilgileri</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Banka Adı</label>
                                <input
                                    v-model="form.bank_name"
                                    type="text"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-200"
                                />
                                <p v-if="form.errors.bank_name" class="mt-1 text-sm text-red-600">{{ form.errors.bank_name }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Şube Adı</label>
                                <input
                                    v-model="form.branch_name"
                                    type="text"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-200"
                                />
                                <p v-if="form.errors.branch_name" class="mt-1 text-sm text-red-600">{{ form.errors.branch_name }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Şube Kodu</label>
                                <input
                                    v-model="form.branch_code"
                                    type="text"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-200"
                                />
                                <p v-if="form.errors.branch_code" class="mt-1 text-sm text-red-600">{{ form.errors.branch_code }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Hesap Numarası</label>
                                <input
                                    v-model="form.account_number"
                                    type="text"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-200"
                                />
                                <p v-if="form.errors.account_number" class="mt-1 text-sm text-red-600">{{ form.errors.account_number }}</p>
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">IBAN</label>
                                <input
                                    v-model="form.iban"
                                    type="text"
                                    maxlength="34"
                                    placeholder="TR00 0000 0000 0000 0000 0000 00"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-200"
                                />
                                <p v-if="form.errors.iban" class="mt-1 text-sm text-red-600">{{ form.errors.iban }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Diğer Bilgiler -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h2 class="text-lg font-medium text-gray-900 mb-4">Diğer Bilgiler</h2>
                        <div class="space-y-4">
                            <div>
                                <label class="flex items-center">
                                    <input
                                        v-model="form.is_approved"
                                        type="checkbox"
                                        class="rounded border-gray-300 text-purple-600 shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-200"
                                    />
                                    <span class="ml-2 text-sm text-gray-700">Taşeronu onaylı olarak işaretle</span>
                                </label>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Notlar</label>
                                <textarea
                                    v-model="form.notes"
                                    rows="4"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-200"
                                    placeholder="Taşeron hakkında notlar..."
                                ></textarea>
                                <p v-if="form.errors.notes" class="mt-1 text-sm text-red-600">{{ form.errors.notes }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Form Butonları -->
                    <div class="flex items-center justify-end space-x-3">
                        <Link
                            :href="route('subcontractors.index')"
                            class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:border-purple-300 focus:ring focus:ring-purple-200 disabled:opacity-25 transition"
                        >
                            İptal
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="inline-flex items-center px-4 py-2 bg-purple-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-700 active:bg-purple-900 focus:outline-none focus:border-purple-900 focus:ring focus:ring-purple-300 disabled:opacity-25 transition"
                        >
                            <svg v-if="form.processing" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Kaydet
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
