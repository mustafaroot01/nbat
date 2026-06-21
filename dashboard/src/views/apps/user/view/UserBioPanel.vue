<script setup lang="ts">
import { ref } from 'vue'
import { avatarText } from '@core/utils/formatters'
import axios from '@axios'
import { useAdminUserStore } from '@/stores/useAdminUserStore'

interface Props {
  userData: any
}

const props = defineProps<Props>()
const emit = defineEmits(['user-updated'])

const userStore = useAdminUserStore()

// Edit Dialog State
const isEditDialogOpen = ref(false)
const governorates = ref<any[]>([])
const isUpdating = ref(false)

const editForm = ref({
  name: '',
  phone: '',
  governorate_id: null as number | null
})

const fetchGovernorates = async () => {
  try {
    const res = await axios.get('/admin/governorates')
    governorates.value = res.data.data
  } catch (err) {
    console.error(err)
  }
}

const openEditDialog = () => {
  editForm.value = {
    name: props.userData.name,
    phone: props.userData.phone,
    governorate_id: props.userData.governorate?.id ?? null
  }
  isEditDialogOpen.value = true
  if (!governorates.value.length) fetchGovernorates()
}

const submitEdit = async () => {
  isUpdating.value = true
  try {
    await userStore.updateUser(props.userData.id, editForm.value)
    isEditDialogOpen.value = false
    emit('user-updated')
  } catch (err) {
    console.error(err)
  } finally {
    isUpdating.value = false
  }
}
</script>

<template>
  <VCard v-if="props.userData" class="pa-6">
    <VCardText class="d-flex flex-column flex-sm-row align-center gap-6">
      <VAvatar
        rounded
        size="120"
        :color="!props.userData.profile_photo ? 'primary' : undefined"
        :variant="!props.userData.profile_photo ? 'tonal' : undefined"
        class="user-profile-avatar flex-shrink-0"
      >
        <VImg v-if="props.userData.profile_photo" :src="props.userData.profile_photo" />
        <span v-else class="text-h3 font-weight-medium">{{ avatarText(props.userData.name) }}</span>
      </VAvatar>

      <div class="user-profile-info w-100">
        <h5 class="text-h5 text-center text-sm-start mb-2 d-flex align-center justify-center justify-sm-start">
          {{ props.userData.name }}
          <VIcon v-if="props.userData.is_trusted" icon="mdi-check-decagram" color="info" size="24" class="ms-2" />
          <VBtn icon="mdi-pencil-outline" size="small" variant="text" color="primary" class="ms-2" @click="openEditDialog" />
        </h5>

        <div class="d-flex align-center justify-center justify-sm-space-between flex-wrap gap-4">
          <div class="d-flex flex-wrap justify-center justify-sm-start flex-grow-1 gap-5">
            <span class="d-flex align-center">
              <VIcon size="24" icon="mdi-map-marker-outline" class="me-2 text-medium-emphasis" />
              <span class="text-body-1 font-weight-medium">{{ props.userData.governorate?.name_ar || 'غير محدد' }}</span>
            </span>

            <span class="d-flex align-center">
              <VIcon size="24" icon="mdi-phone-outline" class="me-2 text-medium-emphasis" />
              <span class="text-body-1 font-weight-medium" dir="ltr">{{ props.userData.phone || '—' }}</span>
            </span>

            <span class="d-flex align-center" v-if="props.userData.email">
              <VIcon size="24" icon="mdi-email-outline" class="me-2 text-medium-emphasis" />
              <span class="text-body-1 font-weight-medium">{{ props.userData.email }}</span>
            </span>

            <span class="d-flex align-center">
              <VIcon size="24" icon="mdi-calendar-blank" class="me-2 text-medium-emphasis" />
              <span class="text-body-1 font-weight-medium">تاريخ الانضمام: {{ props.userData.created_at?.split(' ')[0] || props.userData.created_at?.split('T')[0] || '—' }}</span>
            </span>
          </div>

          <div class="d-flex gap-3">
            <VChip
              v-if="props.userData.is_trusted"
              color="info"
              variant="tonal"
            >
              <VIcon icon="mdi-shield-check-outline" start size="16" />
              حساب موثق
            </VChip>
            
            <VChip
              :color="props.userData.is_active ? 'success' : 'secondary'"
              variant="tonal"
            >
              <VIcon :icon="props.userData.is_active ? 'mdi-account-check-outline' : 'mdi-account-off-outline'" start size="16" />
              {{ props.userData.is_active ? 'حساب نشط' : 'حساب موقوف' }}
            </VChip>
          </div>
        </div>
      </div>
    </VCardText>

    <!-- 👉 Quick Stats -->
    <VDivider class="my-4 mx-6" />
    <div class="px-6 pb-2 d-flex align-center justify-center justify-sm-start gap-6 flex-wrap">
      <div class="d-flex align-center gap-3">
        <VAvatar color="success" variant="tonal" rounded size="42">
          <VIcon icon="mdi-check-circle-outline" size="24" />
        </VAvatar>
        <div>
          <h6 class="text-h6 mb-0">{{ props.userData.approved_plants_count || 0 }}</h6>
          <span class="text-xs text-medium-emphasis">نباتات معتمدة</span>
        </div>
      </div>

      <div class="d-flex align-center gap-3">
        <VAvatar color="error" variant="tonal" rounded size="42">
          <VIcon icon="mdi-close-circle-outline" size="24" />
        </VAvatar>
        <div>
          <h6 class="text-h6 mb-0">{{ props.userData.rejected_plants_count || 0 }}</h6>
          <span class="text-xs text-medium-emphasis">نباتات مرفوضة</span>
        </div>
      </div>

      <div class="d-flex align-center gap-3">
        <VAvatar color="primary" variant="tonal" rounded size="42">
          <VIcon icon="mdi-tree-outline" size="24" />
        </VAvatar>
        <div>
          <h6 class="text-h6 mb-0">{{ props.userData.plants_count || 0 }}</h6>
          <span class="text-xs text-medium-emphasis">إجمالي المساهمات</span>
        </div>
      </div>
    </div>
  </VCard>

  <!-- 👉 Edit Dialog -->
  <VDialog v-model="isEditDialogOpen" max-width="500">
    <VCard title="تعديل بيانات المستخدم">
      <VCardText>
        <VRow>
          <VCol cols="12">
            <VTextField v-model="editForm.name" label="الاسم الكامل" />
          </VCol>
          <VCol cols="12">
            <VTextField v-model="editForm.phone" label="رقم الهاتف" dir="ltr" />
          </VCol>
          <VCol cols="12">
            <VAutocomplete
              v-model="editForm.governorate_id"
              :items="governorates"
              item-title="name_ar"
              item-value="id"
              label="المحافظة"
              :rules="[v => !!v || 'المحافظة مطلوبة']"
            />
          </VCol>
        </VRow>
      </VCardText>

      <VCardActions class="justify-center py-4">
        <VBtn variant="tonal" color="secondary" @click="isEditDialogOpen = false">إلغاء</VBtn>
        <VBtn variant="elevated" color="primary" :loading="isUpdating" @click="submitEdit">حفظ التغييرات</VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>

<style lang="scss" scoped>
.user-profile-avatar {
  border: 4px solid rgb(var(--v-theme-surface));
  background-color: rgb(var(--v-theme-surface)) !important;

  .v-img__img {
    border-radius: 0.125rem;
  }
}
</style>
