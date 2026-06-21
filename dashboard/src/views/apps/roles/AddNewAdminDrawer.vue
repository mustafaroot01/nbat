<script setup lang="ts">
import api from '@axios'
import { PerfectScrollbar } from 'vue3-perfect-scrollbar'
import { VForm } from 'vuetify/components/VForm'

interface Emit {
  (e: 'update:isDrawerOpen', value: boolean): void
  (e: 'refresh'): void
}

interface Props {
  isDrawerOpen: boolean
  adminData?: any
}

const props = defineProps<Props>()
const emit = defineEmits<Emit>()

const isFormValid = ref(false)
const refForm = ref<VForm>()
const isLoading = ref(false)
const roles = ref<any[]>([])
const governorates = ref<any[]>([])

const formData = ref({
  id: null as number | null,
  name: '',
  email: '',
  password: '',
  role: '',
  governorate_id: null as number | null,
})

// Fetch roles for the select dropdown
const fetchRoles = async () => {
  try {
    const { data } = await api.get('/admin/roles')
    roles.value = data.data
  } catch (error) {
    console.error('Failed to fetch roles', error)
  }
}

const fetchGovernorates = async () => {
  try {
    const { data } = await api.get('/admin/governorates')
    governorates.value = data.data
  } catch (error) {
    console.error('Failed to fetch governorates', error)
  }
}

watch(() => props.isDrawerOpen, (isOpen) => {
  if (isOpen) {
    fetchRoles()
    fetchGovernorates()
    if (props.adminData) {
      formData.value.id = props.adminData.id
      formData.value.name = props.adminData.name
      formData.value.email = props.adminData.email
      formData.value.password = '' // Don't populate password on edit
      formData.value.role = props.adminData.roles?.[0]?.name || ''
      formData.value.governorate_id = props.adminData.governorate_id || null
    } else {
      formData.value = { id: null, name: '', email: '', password: '', role: '', governorate_id: null }
    }
  }
})

// 👉 drawer close
const closeNavigationDrawer = () => {
  emit('update:isDrawerOpen', false)
  nextTick(() => {
    refForm.value?.reset()
    refForm.value?.resetValidation()
  })
}

const onSubmit = async () => {
  const { valid } = await refForm.value?.validate() || { valid: false }
  
  if (!valid) return

  isLoading.value = true
  try {
    if (formData.value.id) {
      // Update
      const payload: any = {
        name: formData.value.name,
        email: formData.value.email,
        role: formData.value.role,
        governorate_id: formData.value.governorate_id,
      }
      if (formData.value.password) {
        payload.password = formData.value.password
      }
      await api.put(`/admin/admins/${formData.value.id}`, payload)
    } else {
      // Create
      await api.post('/admin/admins', formData.value)
    }
    
    emit('refresh')
    closeNavigationDrawer()
  } catch (error: any) {
    console.error('Failed to save admin', error)
    if (error.response?.data?.errors) {
      alert(Object.values(error.response.data.errors).flat().join('\n'))
    } else {
      alert('حدث خطأ أثناء حفظ المشرف')
    }
  } finally {
    isLoading.value = false
  }
}

const handleDrawerModelValueUpdate = (val: boolean) => {
  emit('update:isDrawerOpen', val)
}
</script>

<template>
  <VNavigationDrawer
    temporary
    :width="400"
    location="end"
    class="scrollable-content"
    :model-value="props.isDrawerOpen"
    @update:model-value="handleDrawerModelValueUpdate"
  >
    <!-- 👉 Title -->
    <AppDrawerHeaderSection
      :title="formData.id ? 'تعديل بيانات المشرف' : 'إضافة مشرف جديد'"
      @cancel="closeNavigationDrawer"
    />

    <PerfectScrollbar :options="{ wheelPropagation: false }">
      <VCard flat>
        <VCardText>
          <!-- 👉 Form -->
          <VForm
            ref="refForm"
            v-model="isFormValid"
            @submit.prevent="onSubmit"
          >
            <VRow>
              <!-- 👉 Full name -->
              <VCol cols="12">
                <VTextField
                  v-model="formData.name"
                  :rules="[v => !!v || 'الاسم مطلوب']"
                  label="الاسم الكامل"
                  placeholder="أدخل اسم المشرف"
                />
              </VCol>

              <!-- 👉 Email -->
              <VCol cols="12">
                <VTextField
                  v-model="formData.email"
                  :rules="[v => !!v || 'البريد الإلكتروني مطلوب', v => /.+@.+\..+/.test(v) || 'البريد الإلكتروني غير صالح']"
                  label="البريد الإلكتروني"
                  placeholder="admin@example.com"
                  type="email"
                />
              </VCol>

              <!-- 👉 Password -->
              <VCol cols="12">
                <VTextField
                  v-model="formData.password"
                  :rules="formData.id ? [] : [v => !!v || 'كلمة المرور مطلوبة', v => v.length >= 6 || 'كلمة المرور يجب أن تكون 6 أحرف على الأقل']"
                  label="كلمة المرور"
                  placeholder="********"
                  type="password"
                  :hint="formData.id ? 'اترك الحقل فارغاً إذا كنت لا تود تغيير كلمة المرور' : ''"
                  persistent-hint
                />
              </VCol>

              <!-- 👉 Role -->
              <VCol cols="12">
                <VSelect
                  v-model="formData.role"
                  label="اختر الدور"
                  :rules="[v => !!v || 'الدور مطلوب']"
                  :items="roles"
                  item-title="name"
                  item-value="name"
                  placeholder="حدد الدور"
                />
              </VCol>

              <!-- 👉 Governorate -->
              <VCol cols="12">
                <VSelect
                  v-model="formData.governorate_id"
                  label="تخصيص محافظة (اختياري)"
                  :items="governorates"
                  item-title="name_ar"
                  item-value="id"
                  placeholder="جميع المحافظات"
                  clearable
                  hint="إذا تم تحديد محافظة، فلن يرى المشرف سوى بياناتها."
                  persistent-hint
                />
              </VCol>

              <!-- 👉 Submit and Cancel -->
              <VCol cols="12">
                <VBtn
                  type="submit"
                  class="me-3"
                  :loading="isLoading"
                >
                  حفظ
                </VBtn>
                <VBtn
                  type="reset"
                  variant="tonal"
                  color="secondary"
                  @click="closeNavigationDrawer"
                >
                  إلغاء
                </VBtn>
              </VCol>
            </VRow>
          </VForm>
        </VCardText>
      </VCard>
    </PerfectScrollbar>
  </VNavigationDrawer>
</template>
