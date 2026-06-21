<script setup lang="ts">
import { useAppAbility } from '@/plugins/casl/useAppAbility'
import axios from '@axios'
import { useGenerateImageVariant } from '@core/composable/useGenerateImageVariant'
import { VNodeRenderer } from '@layouts/components/VNodeRenderer'
import { themeConfig } from '@themeConfig'
import { emailValidator, requiredValidator } from '@validators'
import { VForm } from 'vuetify/components/VForm'

import authThemeMaskDark from '@images/pages/auth-v2-mask-dark.png'
import authThemeMaskLight from '@images/pages/auth-v2-mask-light.png'
import loginTreeIllustration from '@images/pages/login-tree.png'

const isPasswordVisible = ref(false)

const authThemeImg = computed(() => loginTreeIllustration)

const authThemeMask = useGenerateImageVariant(authThemeMaskLight, authThemeMaskDark)

const route = useRoute()
const router = useRouter()

const ability = useAppAbility()

const errors = ref<Record<string, string | undefined>>({
  email: undefined,
  password: undefined,
})

const refVForm = ref<VForm>()
const email = ref('')
const password = ref('')
const rememberMe = ref(false)
const loading = ref(false)
const errorMsg = ref('')

const login = () => {
  loading.value = true
  errorMsg.value = ''

  axios.post('/admin/auth/login', { email: email.value, password: password.value })
    .then(r => {
      const { data } = r.data

      localStorage.setItem('accessToken', JSON.stringify(data.token))
      localStorage.setItem('userData', JSON.stringify(data.admin))
      
      const userAbilities = data.abilities || [{ action: 'read', subject: 'Auth' }]
      localStorage.setItem('userAbilities', JSON.stringify(userAbilities))

      ability.update(userAbilities)

      router.replace(route.query.to ? String(route.query.to) : '/')
    })
    .catch(e => {
      errorMsg.value = e.response?.data?.message || 'حدث خطأ، يرجى المحاولة مجدداً'
    })
    .finally(() => {
      loading.value = false
    })
}

const onSubmit = () => {
  refVForm.value?.validate()
    .then(({ valid: isValid }) => {
      if (isValid)
        login()
    })
}
</script>

<template>
  <div class="auth-wrapper d-flex align-center justify-center pa-4">
    <VCard
      class="auth-card pa-4 pt-7"
      max-width="448"
    >
      <VCardItem class="justify-center">
        <div class="d-flex align-center justify-center gap-3">
          <VNodeRenderer :nodes="themeConfig.app.logo" />
          <VCardTitle class="font-weight-bold text-h5 text-uppercase">
            {{ themeConfig.app.title }}
          </VCardTitle>
        </div>
      </VCardItem>

      <VCardText class="pt-2">
        <h5 class="text-h5 mb-1 text-primary">
          مرحباً بك في {{ themeConfig.app.title }} 🌿
        </h5>
        <p class="mb-0">
          سجّل دخولك للوصول إلى إدارة النظام
        </p>
      </VCardText>

      <VCardText v-if="errorMsg">
        <VAlert
          color="error"
          variant="tonal"
        >
          {{ errorMsg }}
        </VAlert>
      </VCardText>

      <VCardText>
        <VForm
          ref="refVForm"
          @submit.prevent="onSubmit"
        >
          <VRow>
            <!-- email -->
            <VCol cols="12">
              <VTextField
                v-model="email"
                label="البريد الإلكتروني"
                type="email"
                :rules="[requiredValidator, emailValidator]"
                autofocus
              />
            </VCol>

            <!-- password -->
            <VCol cols="12">
              <VTextField
                v-model="password"
                label="كلمة السر"
                :rules="[requiredValidator]"
                :type="isPasswordVisible ? 'text' : 'password'"
                :append-inner-icon="isPasswordVisible ? 'mdi-eye-off-outline' : 'mdi-eye-outline'"
                @click:append-inner="isPasswordVisible = !isPasswordVisible"
              />

              <VBtn
                block
                color="primary"
                type="submit"
                class="mt-6"
                size="large"
                :loading="loading"
              >
                تسجيل الدخول
              </VBtn>
            </VCol>
          </VRow>
        </VForm>
      </VCardText>
    </VCard>
  </div>
</template>

<style lang="scss">
@use "@core/scss/template/pages/page-auth.scss";
</style>

<route lang="yaml">
meta:
  layout: blank
  action: read
  subject: Auth
  redirectIfLoggedIn: true
</route>
