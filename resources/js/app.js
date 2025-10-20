// resources/js/app.js
import './bootstrap'
import '../css/app.css'



import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import { ZiggyVue } from '../../vendor/tightenco/ziggy'



const appName = import.meta.env.VITE_APP_NAME || 'Laravel'

// Tüm muhtemel konum/uzantıları tek haritada topla (key -> import function)
const pages = {
  ...import.meta.glob('./Pages/**/*.vue'),
  ...import.meta.glob('./pages/**/*.vue'),
  ...import.meta.glob('./Pages/**/*.{jsx,tsx}'),
  ...import.meta.glob('./pages/**/*.{jsx,tsx}'),
}

createInertiaApp({
  title: (title) => `${title} - ${appName}`,
  resolve: (name) => {
    // Aranacak olası anahtarlar
    const tryKeys = [
      `./Pages/${name}.vue`,
      `./pages/${name}.vue`,
      `./Pages/${name}.jsx`,
      `./Pages/${name}.tsx`,
      `./pages/${name}.jsx`,
      `./pages/${name}.tsx`,
    ]

    for (const key of tryKeys) {
      if (pages[key]) {
        // ÖNEMLİ: import fonksiyonunu değil, resolvePageComponent sonucunu döndür
        return resolvePageComponent(key, pages)
      }
    }

    // Teşhis için logla ve yine resolvePageComponent ile standart yolu dene
    console.error('Inertia page missing →', name)
    console.warn('Known page keys:', Object.keys(pages))
    return resolvePageComponent(`./Pages/${name}.vue`, pages)
  },
  setup({ el, App, props, plugin }) {
    return createApp({ render: () => h(App, props) })
      .use(plugin)
      .use(ZiggyVue)
      .mount(el)
  },
  progress: {
    color: '#4B5563',
  },
})
