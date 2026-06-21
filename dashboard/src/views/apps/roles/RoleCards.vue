<script setup lang="ts">
import api from '@axios'
import poseM from '@images/pages/pose_m1.png'
import { ref, onMounted } from 'vue'

interface Role {
  id: number
  name: string
  users_count: number
  permissions: { id: number; name: string }[]
}


const roles = ref<Role[]>([])
const loading = ref(true)

const fetchRoles = async () => {
  loading.value = true
  try {
    const { data } = await api.get('/admin/roles')
    roles.value = data.data
  } catch (error) {
    console.error('Failed to fetch roles', error)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchRoles()
})

const isRoleDialogVisible = ref(false)
const roleDetail = ref<Partial<Role>>()

const isAddRoleDialogVisible = ref(false)

const editRole = (role: Role) => {
  roleDetail.value = role
  isRoleDialogVisible.value = true
}

const deleteRole = async (id: number) => {
  if (confirm('هل أنت متأكد من حذف هذا الدور؟')) {
    try {
      await api.delete(`/admin/roles/${id}`)
      fetchRoles()
    } catch (error) {
      console.error('Failed to delete role', error)
      alert('لا يمكن حذف هذا الدور.')
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

const getAvatarUrl = (userId: number) => {
  // Simple deterministic avatar based on ID
  const avatarId = (userId % 6) + 1
  return new URL(`../../../assets/images/avatars/avatar-${avatarId}.png`, import.meta.url).href
}
</script>

<template>
  <VRow>
    <VCol v-if="loading" cols="12">
      <VCard>
        <VCardText class="d-flex justify-center align-center py-10">
          <VProgressCircular indeterminate color="primary" />
        </VCardText>
      </VCard>
    </VCol>

    <template v-else>
      <!-- 👉 Roles -->
      <VCol
        v-for="item in roles"
        :key="item.id"
        cols="12"
        sm="6"
        lg="4"
      >
        <VCard>
          <VCardText class="d-flex align-center">
            <span>إجمالي {{ item.users_count || 0 }} مستخدمين</span>

            <VSpacer />

            <div class="v-avatar-group">
              <VAvatar
                v-if="item.users_count > 0"
                size="44"
                :image="getAvatarUrl(item.id)"
              />
              <VAvatar
                v-if="item.users_count > 1"
                color="#eee"
              >
                <span
                  class="text-lg"
                  :class="$vuetify.theme.current.dark ? 'text-background' : 'text-high-emphasis'"
                >
                  +{{ item.users_count - 1 }}
                </span>
              </VAvatar>
            </div>
          </VCardText>

          <VCardText>
            <h5 class="text-h5 mb-1 d-flex align-center gap-2">
              {{ item.name }}
            </h5>
            <p class="text-sm text-medium-emphasis mb-4">
              {{ item.permissions.length }} صلاحيات
            </p>

            <div class="d-flex align-center">
              <a
                href="javascript:void(0)"
                @click="editRole(item)"
              >
                تعديل الدور
              </a>

              <VSpacer />

              <IconBtn
                v-if="item.name !== 'superadmin'"
                size="small"
                color="error"
                @click="deleteRole(item.id)"
              >
                <VIcon
                  size="24"
                  icon="mdi-delete-outline"
                />
              </IconBtn>
            </div>
          </VCardText>
        </VCard>
      </VCol>

      <!-- 👉 Add New Role -->
      <VCol
        cols="12"
        sm="6"
        lg="4"
      >
        <VCard
          class="h-100"
          :ripple="false"
          @click="isAddRoleDialogVisible = true"
        >
          <VRow
            no-gutters
            class="h-100"
          >
            <VCol
              cols="5"
              class="d-flex flex-column justify-end align-center mt-5"
            >
              <img
                width="65"
                :src="poseM"
              >
            </VCol>

            <VCol cols="7">
              <VCardText class="d-flex flex-column align-end justify-start gap-2 h-100">
                <VBtn>إضافة دور</VBtn>
                <span class="text-end">إضافة دور جديد إن لم يكن موجوداً.</span>
              </VCardText>
            </VCol>
          </VRow>
        </VCard>
      </VCol>
    </template>
  </VRow>

  <AddEditRoleDialog
    v-model:is-dialog-visible="isAddRoleDialogVisible"
    @refresh="fetchRoles"
  />

  <AddEditRoleDialog
    v-model:is-dialog-visible="isRoleDialogVisible"
    v-model:role-permissions="roleDetail"
    @refresh="fetchRoles"
  />
</template>
