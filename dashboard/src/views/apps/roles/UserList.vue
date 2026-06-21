<script setup lang="ts">
import api from '@axios'
import { VDataTableServer } from 'vuetify/labs/VDataTable'
import AddNewAdminDrawer from './AddNewAdminDrawer.vue'

const admins = ref<any[]>([])
const loading = ref(false)

const headers = [
  { title: 'الاسم', key: 'name' },
  { title: 'البريد الإلكتروني', key: 'email' },
  { title: 'الدور', key: 'roles' },
  { title: 'المحافظة (للمشرفين المخصصين)', key: 'governorate' },
  { title: 'تاريخ الإنشاء', key: 'created_at' },
  { title: 'الإجراءات', key: 'actions', sortable: false },
]

const fetchAdmins = async () => {
  loading.value = true
  try {
    const { data } = await api.get('/admin/admins')
    admins.value = data.data
  } catch (error) {
    console.error('Failed to fetch admins', error)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchAdmins()
})

const getRoleColor = (role: string) => {
  if (role === 'superadmin') return 'error'
  if (role === 'admin') return 'primary'
  return 'secondary'
}

const isAddNewAdminDrawerVisible = ref(false)
const selectedAdmin = ref<any>(null)

const openAddDrawer = () => {
  selectedAdmin.value = null
  isAddNewAdminDrawerVisible.value = true
}

const editAdmin = (admin: any) => {
  selectedAdmin.value = admin
  isAddNewAdminDrawerVisible.value = true
}

const deleteAdmin = async (id: number) => {
  if (confirm('هل أنت متأكد من حذف هذا المشرف؟')) {
    try {
      await api.delete(`/admin/admins/${id}`)
      fetchAdmins()
    } catch (error: any) {
      console.error('Failed to delete admin', error)
      alert(error.response?.data?.message || 'لا يمكن حذف هذا المشرف.')
    }
  }
}
</script>

<template>
  <VCard>
    <VCardText class="d-flex flex-wrap gap-4 align-center">
      <h6 class="text-h6">المشرفون</h6>
      <VSpacer />
      
      <!-- 👉 Add admin button -->
      <VBtn prepend-icon="mdi-plus" @click="openAddDrawer">
        إضافة مشرف
      </VBtn>
    </VCardText>

    <VDataTableServer
      :items="admins"
      :items-length="admins.length"
      :headers="headers"
      :loading="loading"
      class="text-no-wrap"
    >
      <template #item.roles="{ item }">
        <div class="d-flex gap-2">
          <VChip
            v-for="role in item.raw.roles"
            :key="role.id"
            :color="getRoleColor(role.name)"
            size="small"
            class="text-capitalize"
          >
            {{ role.name }}
          </VChip>
          <span v-if="!item.raw.roles || item.raw.roles.length === 0" class="text-medium-emphasis">
            لا يوجد
          </span>
        </div>
      </template>

      <template #item.governorate="{ item }">
        <span v-if="item.raw.governorate" class="text-primary font-weight-medium">
          {{ item.raw.governorate.name_ar }}
        </span>
        <span v-else class="text-medium-emphasis">
          مشرف عام (كل العراق)
        </span>
      </template>

      <template #item.created_at="{ item }">
        {{ new Date(item.raw.created_at).toLocaleDateString('ar-EG') }}
      </template>

      <!-- Actions -->
      <template #item.actions="{ item }">
        <VBtn
          icon
          size="small"
          color="medium-emphasis"
          variant="text"
          @click="editAdmin(item.raw)"
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
          @click="deleteAdmin(item.raw.id)"
        >
          <VIcon
            size="24"
            icon="mdi-delete-outline"
          />
        </VBtn>
      </template>
      
      <template #bottom>
        <!-- Hide default pagination for simple list -->
      </template>
    </VDataTableServer>

    <!-- 👉 Add New Admin Drawer -->
    <AddNewAdminDrawer
      v-model:isDrawerOpen="isAddNewAdminDrawerVisible"
      :admin-data="selectedAdmin"
      @refresh="fetchAdmins"
    />
  </VCard>
</template>
