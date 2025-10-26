<template>
  <AppLayout :title="`Sözleşme Düzenle: ${contract.contract_number}`" :full-width="true">
    <!-- Header -->
    <template #fullWidthHeader>
      <div class="bg-gradient-to-r from-indigo-600 via-purple-700 to-blue-800 border-b border-indigo-900/20 w-full">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
          <div class="flex items-center">
            <div class="flex-shrink-0 w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm">
              <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
              </svg>
            </div>
            <div class="ml-6">
              <h1 class="text-2xl lg:text-3xl font-bold text-white">Sözleşme Düzenle</h1>
              <p class="text-indigo-100 text-sm mt-1">{{ contract.contract_number }} - {{ contract.contract_name }}</p>
            </div>
          </div>
        </div>

        <!-- Breadcrumb -->
        <div class="bg-black/10 border-t border-white/10">
          <div class="w-full px-4 sm:px-6 lg:px-8">
            <div class="flex items-center space-x-2 py-2">
              <ol class="flex items-center space-x-2">
                <li>
                  <Link :href="route('dashboard')" class="text-indigo-100 hover:text-white transition-colors">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                    </svg>
                  </Link>
                </li>
                <li class="flex items-center">
                  <svg class="h-3 w-3 text-indigo-200 mx-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                  </svg>
                  <Link :href="route('contracts.index')" class="text-indigo-100 hover:text-white text-sm">Sözleşmeler</Link>
                </li>
                <li class="flex items-center">
                  <svg class="h-3 w-3 text-indigo-200 mx-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                  </svg>
                  <Link :href="route('contracts.show', contract.id)" class="text-indigo-100 hover:text-white text-sm">{{ contract.contract_number }}</Link>
                </li>
                <li class="flex items-center">
                  <svg class="h-3 w-3 text-indigo-200 mx-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                  </svg>
                  <span class="text-xs font-medium text-white">Düzenle</span>
                </li>
              </ol>
            </div>
          </div>
        </div>
      </div>
    </template>

    <!-- Content -->
    <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
      <form @submit.prevent="submit" class="space-y-6 max-w-4xl">
        <!-- General Errors -->
        <div v-if="Object.keys(form.errors).length > 0" class="bg-red-50 border border-red-200 text-red-800 rounded-lg p-4">
          <h4 class="font-semibold mb-2">Lütfen aşağıdaki hataları düzeltin:</h4>
          <ul class="list-disc list-inside space-y-1">
            <li v-for="(error, field) in form.errors" :key="field" class="text-sm">{{ error }}</li>
          </ul>
        </div>

        <!-- Basic Info -->
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 p-6 space-y-4">
          <h3 class="text-lg font-semibold text-gray-900 border-b pb-3">Temel Bilgiler</h3>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-2">Sözleşme Adı *</label>
              <input v-model="form.contract_name" type="text" required class="w-full px-3 py-2 border rounded-lg" :class="form.errors.contract_name ? 'border-red-500' : 'border-gray-300'" />
              <p v-if="form.errors.contract_name" class="mt-1 text-sm text-red-600">{{ form.errors.contract_name }}</p>
            </div>

            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-2">İş Tanımı</label>
              <textarea v-model="form.work_description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg"></textarea>
            </div>
          </div>
        </div>

        <!-- Financial Info -->
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 p-6 space-y-4">
          <h3 class="text-lg font-semibold text-gray-900 border-b pb-3">Mali Bilgiler</h3>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Sözleşme Bedeli *</label>
              <input v-model="form.contract_value" type="number" step="0.01" required class="w-full px-3 py-2 border rounded-lg" :class="form.errors.contract_value ? 'border-red-500' : 'border-gray-300'" />
              <p v-if="form.errors.contract_value" class="mt-1 text-sm text-red-600">{{ form.errors.contract_value }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Para Birimi</label>
              <select v-model="form.currency" class="w-full px-3 py-2 border rounded-lg" :class="form.errors.currency ? 'border-red-500' : 'border-gray-300'">
                <option value="TRY">TRY</option>
                <option value="USD">USD</option>
                <option value="EUR">EUR</option>
              </select>
              <p v-if="form.errors.currency" class="mt-1 text-sm text-red-600">{{ form.errors.currency }}</p>
            </div>

            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-2">Ödeme Koşulları</label>
              <textarea v-model="form.payment_terms" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg"></textarea>
            </div>
          </div>
        </div>

        <!-- Dates -->
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 p-6 space-y-4">
          <h3 class="text-lg font-semibold text-gray-900 border-b pb-3">Tarihler</h3>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">İmza Tarihi</label>
              <input v-model="form.signing_date" type="date" class="w-full px-3 py-2 border rounded-lg" :class="form.errors.signing_date ? 'border-red-500' : 'border-gray-300'" />
              <p v-if="form.errors.signing_date" class="mt-1 text-sm text-red-600">{{ form.errors.signing_date }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Başlangıç Tarihi *</label>
              <input v-model="form.start_date" type="date" required class="w-full px-3 py-2 border rounded-lg" :class="form.errors.start_date ? 'border-red-500' : 'border-gray-300'" />
              <p v-if="form.errors.start_date" class="mt-1 text-sm text-red-600">{{ form.errors.start_date }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Bitiş Tarihi *</label>
              <input v-model="form.end_date" type="date" required class="w-full px-3 py-2 border rounded-lg" :class="form.errors.end_date ? 'border-red-500' : 'border-gray-300'" />
              <p v-if="form.errors.end_date" class="mt-1 text-sm text-red-600">{{ form.errors.end_date }}</p>
            </div>
          </div>
        </div>

        <!-- Warranty -->
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 p-6 space-y-4">
          <h3 class="text-lg font-semibold text-gray-900 border-b pb-3">Teminat Bilgileri</h3>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Teminat Türü</label>
              <select v-model="form.warranty_type" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                <option value="none">Teminatsız</option>
                <option value="bank_letter">Banka Teminat Mektubu</option>
                <option value="cash">Nakit</option>
                <option value="check">Çek</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Teminat Tutarı</label>
              <input v-model="form.warranty_amount" type="number" step="0.01" class="w-full px-3 py-2 border border-gray-300 rounded-lg" />
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex justify-end space-x-3">
          <Link :href="route('contracts.show', contract.id)" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
            İptal
          </Link>
          <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50">
            Güncelle
          </button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
  contract: Object,
});

// Format dates for input fields (YYYY-MM-DD)
const formatDateForInput = (date) => {
  if (!date) return '';
  const d = new Date(date);
  return d.toISOString().split('T')[0];
};

const form = useForm({
  contract_name: props.contract.contract_name,
  work_description: props.contract.work_description,
  contract_value: props.contract.contract_value,
  currency: props.contract.currency,
  payment_terms: props.contract.payment_terms,
  signing_date: formatDateForInput(props.contract.signing_date),
  start_date: formatDateForInput(props.contract.start_date),
  end_date: formatDateForInput(props.contract.end_date),
  warranty_amount: props.contract.warranty_amount,
  warranty_type: props.contract.warranty_type,
  notes: props.contract.notes,
});

const submit = () => {
  form.put(route('contracts.update', props.contract.id));
};
</script>
