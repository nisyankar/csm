<template>
  <AppLayout title="Yeni İzin Talebi" :full-width="true">
    <!-- Full-width header -->
    <template #fullWidthHeader>
      <div class="bg-gradient-to-r from-purple-600 via-purple-700 to-indigo-800 border-b border-purple-900/20 w-full">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
          <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
              <div class="flex-shrink-0 w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm">
                <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
              </div>
              <div>
                <h1 class="text-2xl lg:text-3xl font-bold text-white">Yeni İzin Talebi</h1>
                <p class="text-purple-100 text-sm mt-1">İzin talebi oluşturun</p>
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
                  <span class="text-white font-medium text-sm">Yeni Talep</span>
                </li>
              </ol>
            </div>
          </div>
        </div>
      </div>
    </template>

    <!-- Main Content -->
    <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
      <div class="max-w-4xl mx-auto">
        <form @submit.prevent="submitForm" class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
          <div class="p-6 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Çalışan Seçimi -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Çalışan <span class="text-red-500">*</span>
                </label>
                <select
                  v-model="form.employee_id"
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500"
                  :class="{'border-red-300': form.errors.employee_id}"
                >
                  <option value="">Seçiniz...</option>
                  <option v-for="employee in employees" :key="employee.id" :value="employee.id">
                    {{ employee.first_name }} {{ employee.last_name }} ({{ employee.employee_code }})
                  </option>
                </select>
                <p v-if="form.errors.employee_id" class="text-red-600 text-sm mt-1">
                  {{ form.errors.employee_id }}
                </p>
              </div>

              <!-- İzin Türü -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  İzin Türü <span class="text-red-500">*</span>
                </label>
                <select
                  v-model="form.leave_type_id"
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500"
                  :class="{'border-red-300': form.errors.leave_type_id}"
                >
                  <option value="">Seçiniz...</option>
                  <option v-for="type in leave_types" :key="type.id" :value="type.id">
                    {{ type.name }}
                  </option>
                </select>
                <p v-if="form.errors.leave_type_id" class="text-red-600 text-sm mt-1">
                  {{ form.errors.leave_type_id }}
                </p>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Başlangıç Tarihi -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Başlangıç Tarihi <span class="text-red-500">*</span>
                </label>
                <input
                  type="date"
                  v-model="form.start_date"
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500"
                  :class="{'border-red-300': form.errors.start_date}"
                >
                <p v-if="form.errors.start_date" class="text-red-600 text-sm mt-1">
                  {{ form.errors.start_date }}
                </p>
              </div>

              <!-- Bitiş Tarihi -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Bitiş Tarihi <span class="text-red-500">*</span>
                </label>
                <input
                  type="date"
                  v-model="form.end_date"
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500"
                  :class="{'border-red-300': form.errors.end_date}"
                >
                <p v-if="form.errors.end_date" class="text-red-600 text-sm mt-1">
                  {{ form.errors.end_date }}
                </p>
              </div>
            </div>

            <!-- Açıklama -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Açıklama / Sebep
              </label>
              <textarea
                v-model="form.reason"
                rows="4"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500"
                :class="{'border-red-300': form.errors.reason}"
                placeholder="İzin sebebinizi belirtiniz..."
              ></textarea>
              <p v-if="form.errors.reason" class="text-red-600 text-sm mt-1">
                {{ form.errors.reason }}
              </p>
            </div>

            <!-- Vekil Çalışan -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Vekil Çalışan (Opsiyonel)
              </label>
              <select
                v-model="form.substitute_employee_id"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500"
              >
                <option value="">Seçiniz...</option>
                <option v-for="employee in substitute_employees" :key="employee.id" :value="employee.id">
                  {{ employee.first_name }} {{ employee.last_name }} ({{ employee.employee_code }})
                </option>
              </select>
              <p class="text-gray-500 text-sm mt-1">
                İzniniz süresince sizi temsil edecek çalışanı seçebilirsiniz
              </p>
            </div>
          </div>

          <!-- Form Actions -->
          <div class="bg-gray-50 px-6 py-4 flex items-center justify-end gap-3 border-t border-gray-200">
            <Link
              :href="route('leave-requests.index')"
              class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition-colors"
            >
              İptal
            </Link>
            <button
              type="submit"
              :disabled="form.processing"
              class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors inline-flex items-center"
            >
              <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              {{ form.processing ? 'Kaydediliyor...' : 'Talebi Oluştur' }}
            </button>
          </div>
        </form>

        <!-- Info Box -->
        <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
          <div class="flex">
            <svg class="h-5 w-5 text-blue-400 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
            </svg>
            <div class="text-sm text-blue-700">
              <p class="font-medium mb-1">Bilgilendirme:</p>
              <ul class="list-disc list-inside space-y-1">
                <li>İzin talebiniz oluşturulduktan sonra onay sürecine girecektir</li>
                <li>Talebin durumunu "İzin Talepleri" sayfasından takip edebilirsiniz</li>
                <li>Onaylanmamış taleplerinizi düzenleyebilir veya iptal edebilirsiniz</li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
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