<template>
    <div class="min-h-screen bg-gray-100 flex items-center justify-center">
        <div class="max-w-md mx-auto bg-white rounded-xl shadow-md p-6">
            <h1 class="text-2xl font-bold text-gray-900 mb-4">
                🏗️ SPT - Şantiye Personel Takip
            </h1>
            <p class="text-gray-600 mb-4">
                Laravel + Inertia.js + Vue 3 projesi başarıyla kuruldu!
            </p>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                ✅ Sistem çalışıyor!
            </div>
            <div class="mt-4">
                <p class="text-sm text-gray-500">
                    Tarih: {{ new Date().toLocaleDateString('tr-TR') }}
                </p>
            </div>
        </div>
    </div>
</template>

<script setup>
// Bu sayfada henüz özel JavaScript kodu yok
</script>