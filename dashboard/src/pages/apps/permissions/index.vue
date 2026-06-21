<script setup lang="ts">
import api from '@axios'
import { VDataTableServer } from 'vuetify/labs/VDataTable'


const permissions = ref<any[]>([])
const loading = ref(false)

const headers = [
  { title: 'الاسم', key: 'name' },
  { title: 'الأدوار المعينة', key: 'roles', sortable: false },
  { title: 'تاريخ الإنشاء', key: 'created_at' },
  { title: 'الإجراءات', key: 'actions', sortable: false },
]

const fetchPermissions = async () => {
  loading.value = true
  try {
    const { data } = await api.get('/admin/permissions')
    permissions.value = data.data
  } catch (error) {
    console.error('Failed to fetch permissions', error)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchPermissions()
})

const isPermissionDialogVisible = ref(false)
const isAddPermissionDialogVisible = ref(false)
const permissionDetail = ref<{ id?: number; name: string } | null>(null)

const editPermission = (permission: any) => {
  permissionDetail.value = permission
  isPermissionDialogVisible.value = true
}

const deletePermission = async (id: number) => {
  if (confirm('هل أنت متأكد من حذف هذه الصلاحية؟')) {
    try {
      await api.delete(`/admin/permissions/${id}`)
      fetchPermissions()
    } catch (error) {
      console.error('Failed to delete permission', error)
      alert('لا يمكن حذف هذه الصلاحية.')
    }
  }
}

const getRoleColor = (roleName: string) => {
  switch (roleName.toLowerCase()) {
    case 'superadmin':
      return 'error'
    case 'admin':
      return 'primary'
    case 'manager':
      return 'success'
    default:
      return 'info'
  }
}
</script>

<template>
  <VRow>
    <VCol cols="12">
      <h5 class="text-h5">
        قائمة الصلاحيات
      </h5>
      <p class="text-sm mb-0">
        يحدد كل تصريح ميزة محددة يمكن للمشرف الوصول إليها في النظام.
      </p>
    </VCol>

    <VCol cols="12">
      <VCard>
        <VCardText class="d-flex align-center flex-wrap gap-4 justify-space-between">
          <h6 class="text-h6">الصلاحيات</h6>

          <VBtn
            density="default"
            @click="isAddPermissionDialogVisible = true"
          >
            إضافة صلاحية
          </VBtn>
        </VCardText>

        <VDataTableServer
          :items="permissions"
          :items-length="permissions.length"
          :headers="headers"
          :loading="loading"
          class="text-no-wrap"
        >
          <!-- name -->
          <template #item.name="{ item }">
            <span class="text-high-emphasis">
              {{ item.raw.name }}
            </span>
          </template>

          <!-- Assigned To -->
          <template #item.roles="{ item }">
            <div class="d-flex gap-2">
              <VChip
                v-for="role in item.raw.roles"
                :key="role.id"
                :color="getRoleColor(role.name)"
                density="comfortable"
                class="text-capitalize"
              >
                {{ role.name }}
              </VChip>
              <span v-if="!item.raw.roles || item.raw.roles.length === 0" class="text-medium-emphasis">
                لا يوجد
              </span>
            </div>
          </template>

          <!-- Created Date -->
          <template #item.created_at="{ item }">
            <span class="text-sm text-medium-emphasis">{{ new Date(item.raw.created_at).toLocaleDateString('ar-EG') }}</span>
          </template>

          <!-- Actions -->
          <template #item.actions="{ item }">
            <VBtn
              icon
              size="small"
              color="medium-emphasis"
              variant="text"
              @click="editPermission(item.raw)"
            >
              <VIcon
                size="24"
                icon="mdi-pencil-outline"
              />
            </VBtn>
            <VBtn
              icon
              size="small"
              variant="text"
              color="error"
              @click="deletePermission(item.raw.id)"
            >
              <VIcon
                size="24"
                icon="mdi-delete-outline"
              />
            </VBtn>
          </template>

          <!-- Pagination -->
          <template #bottom>
            <!-- Hide default pagination since we load all permissions -->
          </template>
        </VDataTableServer>
      </VCard>

      <AddEditPermissionDialog
        v-model:isDialogVisible="isPermissionDialogVisible"
        v-model:permission-detail="permissionDetail"
        @refresh="fetchPermissions"
      />
      <AddEditPermissionDialog 
        v-model:isDialogVisible="isAddPermissionDialogVisible" 
        @refresh="fetchPermissions"
      />
    </VCol>
  </VRow>
</template>
