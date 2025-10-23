<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    leave_requests: Object,
    filters: Object,
    leaveTypes: Array,
    employees: Array,
    stats: Object,
    leave_types: Array,
});

const filters = ref({
    status: props.filters?.status || '',
    leave_type_id: props.filters?.leave_type_id || '',
    search: props.filters?.search || '',
});

const filterResults = () => {
    router.get('/leave-requests', filters.value, {
        preserveState: true,
        preserveScroll: true,
    });
};

const resetFilters = () => {
    filters.value = {
        status: '',
        leave_type_id: '',
        search: '',
    };
    filterResults();
};

const getStatusBadgeClass = (status) => {
    const classes = {
        'pending': 'bg-yellow-100 text-yellow-800',
        'approved': 'bg-green-100 text-green-800',
        'rejected': 'bg-red-100 text-red-800',
        'cancelled': 'bg-gray-100 text-gray-800',
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
};

const getStatusLabel = (status) => {
    const labels = {
        'pending': 'Bekliyor',
        'approved': 'Onaylandı',
        'rejected': 'Reddedildi',
        'cancelled': 'İptal Edildi',
    };
    return labels[status] || status;
};
</script>

<template>
    <AppLayout title="İzin Talepleri">
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    İzin Talepleri
                </h2>
                <a
                    href="/leave-requests/create"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring focus:ring-blue-300 disabled:opacity-25 transition"
                >
                    Yeni İzin Talebi
                </a>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Filtreler -->
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-6 p-6">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Durum
                            </label>
                            <select
                                v-model="filters.status"
                                @change="filterResults"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                            >
                                <option value="">Tümü</option>
                                <option value="pending">Bekliyor</option>
                                <option value="approved">Onaylandı</option>
                                <option value="rejected">Reddedildi</option>
                                <option value="cancelled">İptal Edildi</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                İzin Türü
                            </label>
                            <select
                                v-model="filters.leave_type_id"
                                @change="filterResults"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                            >
                                <option value="">Tümü</option>
                                <option
                                    v-for="type in leaveTypes"
                                    :key="type.id"
                                    :value="type.id"
                                >
                                    {{ type.name }}
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Ara
                            </label>
                            <input
                                v-model="filters.search"
                                @input="filterResults"
                                type="text"
                                placeholder="Çalışan adı..."
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                            />
                        </div>

                        <div class="flex items-end">
                            <button
                                @click="resetFilters"
                                class="w-full px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition"
                            >
                                Filtreleri Temizle
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Tablo -->
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Çalışan
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        İzin Türü
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Başlangıç
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Bitiş
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Gün Sayısı
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Durum
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        İşlemler
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr
                                    v-for="request in leave_requests.data"
                                    :key="request.id"
                                    class="hover:bg-gray-50"
                                >
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ request.employee?.name }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            {{ request.leave_type?.name }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ request.start_date }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ request.end_date }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ request.total_days }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            :class="getStatusBadgeClass(request.status)"
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                        >
                                            {{ getStatusLabel(request.status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a
                                            :href="`/leave-requests/${request.id}`"
                                            class="text-blue-600 hover:text-blue-900 mr-3"
                                        >
                                            Görüntüle
                                        </a>
                                        <a
                                            v-if="request.status === 'pending'"
                                            :href="`/leave-requests/${request.id}/edit`"
                                            class="text-indigo-600 hover:text-indigo-900"
                                        >
                                            Düzenle
                                        </a>
                                    </td>
                                </tr>
                                <tr v-if="leave_requests.data.length === 0">
                                    <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">
                                        İzin talebi bulunamadı.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="leave_requests.links.length > 3" class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                        <div class="flex items-center justify-between">
                            <div class="text-sm text-gray-700">
                                Toplam <span class="font-medium">{{ leave_requests.total }}</span> kayıt
                            </div>
                            <div class="flex space-x-2">
                                <template v-for="(link, index) in leave_requests.links" :key="index">
                                    <a
                                        v-if="link.url"
                                        :href="link.url"
                                        v-html="link.label"
                                        :class="[
                                            'px-3 py-2 text-sm rounded-md',
                                            link.active
                                                ? 'bg-blue-600 text-white'
                                                : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-300'
                                        ]"
                                    />
                                    <span
                                        v-else
                                        v-html="link.label"
                                        class="px-3 py-2 text-sm text-gray-400 rounded-md bg-gray-100"
                                    />
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
