<script setup lang="ts">
import api from '@axios'
import { VForm } from 'vuetify/components/VForm'

interface Role {
  id?: number
  name: string
  permissions: { id: number; name: string }[]
}

interface Props {
  rolePermissions?: Role
  isDialogVisible: boolean
}

interface Emit {
  (e: 'update:isDialogVisible', value: boolean): void
  (e: 'refresh'): void
}

const props = withDefaults(defineProps<Props>(), {
  rolePermissions: () => ({
    name: '',
    permissions: [],
  }),
})

const emit = defineEmits<Emit>()

const availablePermissions = ref<{ id: number; name: string }[]>([])
const loading = ref(false)
const isSaving = ref(false)

const roleName = ref('')
const selectedPermissions = ref<string[]>([])
const refPermissionForm = ref<VForm>()
const isSelectAll = ref(false)

// Organize permissions by module
const permissionGroups = computed(() => {
  const groups: Record<string, { read: boolean, write: boolean, create: boolean, label: string }> = {}
  
  availablePermissions.value.forEach(p => {
    const [action, ...moduleParts] = p.name.split('_')
    const moduleKey = moduleParts.join('_')
    
    if (!groups[moduleKey]) {
      groups[moduleKey] = { read: false, write: false, create: false, label: formatLabel(moduleKey) }
    }
    
    // Set checked state based on selectedPermissions
    if (action === 'read' || action === 'write' || action === 'create') {
      groups[moduleKey][action] = selectedPermissions.value.includes(p.name)
    }
  })
  
  return groups
})

const formatLabel = (key: string) => {
  const labels: Record<string, string> = {
    users: 'المستخدمون',
    plants: 'النباتات',
    reports: 'الإبلاغات',
    banners: 'البنرات',
    governorates: 'المحافظات',
    settings: 'الإعدادات العامة',
    notifications: 'الإشعارات',
    app_versions: 'إصدارات التطبيق',
    roles: 'الأدوار والصلاحيات',
  }
  return labels[key] || key.replace('_', ' ')
}

const fetchPermissions = async () => {
  loading.value = true
  try {
    const { data } = await api.get('/admin/permissions')
    availablePermissions.value = data.data
  } catch (error) {
    console.error('Failed to fetch permissions', error)
  } finally {
    loading.value = false
  }
}

// Select All logic
const checkedCount = computed(() => selectedPermissions.value.length)
const totalPermissions = computed(() => availablePermissions.value.length)
const isIndeterminate = computed(() => checkedCount.value > 0 && checkedCount.value < totalPermissions.value)

watch(isSelectAll, val => {
  if (val) {
    selectedPermissions.value = availablePermissions.value.map(p => p.name)
  } else if (checkedCount.value === totalPermissions.value) {
    selectedPermissions.value = []
  }
})

watch(isIndeterminate, () => {
  if (!isIndeterminate.value && checkedCount.value === 0)
    isSelectAll.value = false
})

watch(selectedPermissions, () => {
  if (totalPermissions.value && checkedCount.value === totalPermissions.value)
    isSelectAll.value = true
}, { deep: true })

const updatePermission = (moduleKey: string, action: string, value: boolean | null) => {
  const permissionName = `${action}_${moduleKey}`
  if (value) {
    if (!selectedPermissions.value.includes(permissionName)) {
      selectedPermissions.value.push(permissionName)
    }
  } else {
    selectedPermissions.value = selectedPermissions.value.filter(p => p !== permissionName)
  }
}

watch(() => props.isDialogVisible, (isVisible) => {
  if (isVisible) {
    fetchPermissions()
    if (props.rolePermissions?.id) {
      roleName.value = props.rolePermissions.name
      selectedPermissions.value = props.rolePermissions.permissions.map(p => p.name)
    } else {
      roleName.value = ''
      selectedPermissions.value = []
      isSelectAll.value = false
    }
  }
})

const onSubmit = async () => {
  isSaving.value = true
  try {
    if (props.rolePermissions?.id) {
      await api.put(`/admin/roles/${props.rolePermissions.id}`, {
        name: roleName.value,
        permissions: selectedPermissions.value,
      })
    } else {
      await api.post('/admin/roles', {
        name: roleName.value,
        permissions: selectedPermissions.value,
      })
    }
    emit('refresh')
    onReset()
  } catch (error) {
    console.error('Failed to save role', error)
  } finally {
    isSaving.value = false
  }
}

const onReset = () => {
  emit('update:isDialogVisible', false)
  isSelectAll.value = false
  refPermissionForm.value?.reset()
}

const moduleNamesAr: Record<string, string> = {
  users: 'المستخدمون',
  plants: 'النباتات',
  reports: 'الإبلاغات',
  banners: 'البنرات',
  screen_ads: 'إعلانات الشاشة',
  campaigns: 'حملات التشجير',
  user_levels: 'ألقاب المستخدمين (المتصدرين)',
  governorates: 'المحافظات',
  roles: 'الأدوار والصلاحيات',
  notifications: 'الإشعارات',
  app_versions: 'إصدارات التطبيق',
  settings: 'الإعدادات العامة'
}

const translateModule = (key: string) => {
  return moduleNamesAr[key] || key
}
</script>

<template>
  <VDialog
    :width="$vuetify.display.smAndDown ? 'auto' : 900"
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

      <!-- 👉 Title -->
      <VCardItem class="text-center">
        <VCardTitle class="text-h5">
          {{ props.rolePermissions?.id ? 'تعديل' : 'إضافة' }} دور
        </VCardTitle>
        <VCardSubtitle>
          تحديد صلاحيات الدور
        </VCardSubtitle>
      </VCardItem>

      <VCardText class="mt-6">
        <!-- 👉 Form -->
        <VForm ref="refPermissionForm" @submit.prevent="onSubmit">
          <!-- 👉 Role name -->
          <VTextField
            v-model="roleName"
            label="اسم الدور"
            placeholder="أدخل اسم الدور"
            :rules="[v => !!v || 'اسم الدور مطلوب']"
            class="mb-6"
          />

          <h6 class="text-h6 mt-4 mb-2">
            صلاحيات الدور
          </h6>

          <!-- 👉 Role Permissions Table -->
          <VTable class="permission-table text-no-wrap">
            <tr>
              <td>
                <span class="font-weight-medium">الوصول بصلاحيات المشرف</span>
              </td>
              <td colspan="3">
                <VCheckbox
                  v-model="isSelectAll"
                  v-model:indeterminate="isIndeterminate"
                  label="تحديد الكل"
                />
              </td>
            </tr>

            <!-- 👉 Dynamic Permission Loop -->
            <tr
              v-for="(group, moduleKey) in permissionGroups"
              :key="moduleKey"
            >
              <td>{{ group.label }}</td>
              <td>
                <VCheckbox
                  :model-value="group.read"
                  label="قراءة"
                  @update:model-value="updatePermission(moduleKey as string, 'read', $event)"
                />
              </td>
              <td>
                <VCheckbox
                  :model-value="group.write"
                  label="تعديل"
                  @update:model-value="updatePermission(moduleKey as string, 'write', $event)"
                />
              </td>
              <td>
                <VCheckbox
                  :model-value="group.create"
                  label="إنشاء"
                  @update:model-value="updatePermission(moduleKey as string, 'create', $event)"
                />
              </td>
            </tr>
          </VTable>

          <!-- 👉 Actions button -->
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

<style lang="scss">
.permission-table {
  td {
    border-block-end: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
    padding-block: 0.5rem;

    .v-checkbox {
      min-inline-size: 4.75rem;
    }

    &:not(:first-child) {
      padding-inline: 0.5rem;
    }

    .v-label {
      white-space: nowrap;
    }
  }
}
</style>
