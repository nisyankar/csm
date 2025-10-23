<template>
    <AppLayout title="Yeni İzin Talebi">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6 sm:px-20 bg-white border-b border-gray-200">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-2xl font-semibold text-gray-800">Yeni İzin Talebi</h2>
                            <Link :href="route('leave-requests.index')" class="text-gray-600 hover:text-gray-900">
                                ← Geri Dön
                            </Link>
                        </div>

                        <form @submit.prevent="submitForm">
                            <div class="grid grid-cols-1 gap-6">
                                <!-- Çalışan Seçimi -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Çalışan</label>
                                    <select v-model="form.employee_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="">Seçiniz...</option>
                                        <option v-for="employee in employees" :key="employee.id" :value="employee.id">
                                            {{ employee.full_name }}
                                        </option>
                                    </select>
                                    <div v-if="form.errors.employee_id" class="text-red-600 text-sm mt-1">
                                        {{ form.errors.employee_id }}
                                    </div>
                                </div>

                                <!-- İzin Türü -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">İzin Türü</label>
                                    <select v-model="form.leave_type_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="">Seçiniz...</option>
                                        <option v-for="type in leave_types" :key="type.id" :value="type.id">
                                            {{ type.name }}
                                        </option>
                                    </select>
                                    <div v-if="form.errors.leave_type_id" class="text-red-600 text-sm mt-1">
                                        {{ form.errors.leave_type_id }}
                                    </div>
                                </div>

                                <!-- Başlangıç Tarihi -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Başlangıç Tarihi</label>
                                    <input type="date" v-model="form.start_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <div v-if="form.errors.start_date" class="text-red-600 text-sm mt-1">
                                        {{ form.errors.start_date }}
                                    </div>
                                </div>

                                <!-- Bitiş Tarihi -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Bitiş Tarihi</label>
                                    <input type="date" v-model="form.end_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <div v-if="form.errors.end_date" class="text-red-600 text-sm mt-1">
                                        {{ form.errors.end_date }}
                                    </div>
                                </div>

                                <!-- Açıklama -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Açıklama</label>
                                    <textarea v-model="form.reason" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                                    <div v-if="form.errors.reason" class="text-red-600 text-sm mt-1">
                                        {{ form.errors.reason }}
                                    </div>
                                </div>

                                <!-- Vekil Çalışan (Opsiyonel) -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Vekil Çalışan (Opsiyonel)</label>
                                    <select v-model="form.substitute_employee_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="">Seçiniz...</option>
                                        <option v-for="employee in substitute_employees" :key="employee.id" :value="employee.id">
                                            {{ employee.full_name }}
                                        </option>
                                    </select>
                                    <div v-if="form.errors.substitute_employee_id" class="text-red-600 text-sm mt-1">
                                        {{ form.errors.substitute_employee_id }}
                                    </div>
                                </div>

                                <!-- Butonlar -->
                                <div class="flex items-center justify-end gap-4">
                                    <Link :href="route('leave-requests.index')" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                                        İptal
                                    </Link>
                                    <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50">
                                        {{ form.processing ? 'Kaydediliyor...' : 'Kaydet' }}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    employees: Array,
    substitute_employees: Array,
    leave_types: Array,
    defaults: Object,
});

const form = useForm({
    employee_id: props.defaults?.employee_id || '',
    leave_type_id: '',
    start_date: '',
    end_date: '',
    reason: '',
    substitute_employee_id: '',
});

const submitForm = () => {
    form.post(route('leave-requests.store'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
        },
    });
};
</script>