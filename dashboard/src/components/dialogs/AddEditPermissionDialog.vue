<script setup lang="ts">
import api from '@axios'

interface Props {
  isDialogVisible: boolean
  permissionDetail?: { id?: number; name: string } | null
}

interface Emit {
  (e: 'update:isDialogVisible', value: boolean): void
  (e: 'refresh'): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emit>()


const permissionName = ref('')
const isSaving = ref(false)

watch(() => props.isDialogVisible, (isVisible) => {
  if (isVisible) {
    if (props.permissionDetail?.id) {
      permissionName.value = props.permissionDetail.name
    } else {
      permissionName.value = ''
    }
  }
})

const onSubmit = async () => {
  isSaving.value = true
  try {
    if (props.permissionDetail?.id) {
      await api.put(`/admin/permissions/${props.permissionDetail.id}`, {
        name: permissionName.value,
      })
    } else {
      await api.post('/admin/permissions', {
        name: permissionName.value,
      })
    }
    emit('refresh')
    onReset()
  } catch (error) {
    console.error('Failed to save permission', error)
  } finally {
    isSaving.value = false
  }
}

const onReset = () => {
  emit('update:isDialogVisible', false)
  permissionName.value = ''
}
</script>

<template>
  <VDialog
    :width="$vuetify.display.smAndDown ? 'auto' : 600"
    :model-value="props.isDialogVisible"
    @update:model-value="onReset"
  >
    <VCard class="pa-sm-8 pa-5">
      <!-- 👉 dialog close btn -->
      <DialogCloseBtn
        variant="text"
        size="small"
        @click="onReset"
      />

      <VCardItem class="text-center">
        <VCardTitle class="text-h5">
          {{ props.permissionDetail?.id ? 'تعديل' : 'إضافة' }} صلاحية
        </VCardTitle>
        <VCardSubtitle>
          إعدادات الصلاحية
        </VCardSubtitle>
      </VCardItem>

      <VCardText class="mt-6">
        <VForm @submit.prevent="onSubmit">
          <VRow>
            <VCol cols="12">
              <VTextField
                v-model="permissionName"
                label="اسم الصلاحية"
                placeholder="أدخل اسم الصلاحية"
                :rules="[v => !!v || 'اسم الصلاحية مطلوب']"
              />
            </VCol>
          </VRow>

          <div class="d-flex align-center justify-center gap-3 mt-6">
            <VBtn type="submit" :loading="isSaving">
              حفظ
            </VBtn>

            <VBtn
              color="secondary"
              variant="tonal"
              @click="onReset"
            >
              إلغاء
            </VBtn>
          </div>
        </VForm>
      </VCardText>
    </VCard>
  </VDialog>
</template>
