<script lang="ts" setup>
const accountData = ref({
  name: '',
  phone: '',
  governorate: '',
})

onMounted(() => {
  const userData = JSON.parse(localStorage.getItem('userData') || '{}')

  accountData.value = {
    name: userData.name || '',
    phone: userData.phone || '',
    governorate: userData.governorate?.name || '',
  }
})
</script>

<template>
  <VRow>
    <VCol cols="12">
      <VCard title="معلومات الحساب">
        <VCardText>
          <VRow>
            <VCol
              cols="12"
              md="6"
            >
              <VTextField
                v-model="accountData.name"
                label="الاسم"
                readonly
              />
            </VCol>

            <VCol
              cols="12"
              md="6"
            >
              <VTextField
                v-model="accountData.phone"
                label="رقم الهاتف"
                readonly
              />
            </VCol>

            <VCol
              cols="12"
              md="6"
            >
              <VTextField
                v-model="accountData.governorate"
                label="المحافظة"
                readonly
              />
            </VCol>
          </VRow>
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
</template>
