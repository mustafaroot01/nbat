/* eslint-disable import/order */
// import '@/@fake-db/db'
import '@/@iconify/icons-bundle'
import App from '@/App.vue'
import ability from '@/plugins/casl/ability'
import i18n from '@/plugins/i18n'
import layoutsPlugin from '@/plugins/layouts'
import vuetify from '@/plugins/vuetify'
import { loadFonts } from '@/plugins/webfontloader'
import router from '@/router'
import { abilitiesPlugin } from '@casl/vue'
import '@core/scss/template/index.scss'
import * as Sentry from '@sentry/vue'
import '@styles/styles.scss'
import { createPinia } from 'pinia'
import { createApp } from 'vue'

loadFonts()

// Create vue app
const app = createApp(App)

// 🔭 Sentry — monitoring in production
const sentryDsn = import.meta.env.VITE_SENTRY_DSN

if (sentryDsn) {
  Sentry.init({
    app,
    dsn: sentryDsn,
    integrations: [
      Sentry.browserTracingIntegration({ router }),
    ],
    tracesSampleRate: 1.0,
    environment: import.meta.env.MODE,
  })
}

// Use plugins
app.use(vuetify)
app.use(createPinia())
app.use(router)
app.use(layoutsPlugin)
app.use(i18n)
app.use(abilitiesPlugin, ability, {
  useGlobalProperties: true,
})

// Mount vue app
app.mount('#app')
