<script setup lang="ts">
import { useUserLevelStore } from '@/stores/useUserLevelStore'
import { VDataTable } from 'vuetify/labs/VDataTable'

interface UserLevel {
  id: number
  name: string
  min_plants: number
}

const userLevelStore = useUserLevelStore()

const deleteDialog = ref(false)
const addDialog = ref(false)

const defaultItem = ref<UserLevel>({
  id: -1,
  name: '',
  min_plants: 0,
})

const editedItem = ref<UserLevel>({ ...defaultItem.value })
const levelList = ref<UserLevel[]>([])
const submitting = ref(false)

const headers = [
  { title: '#', key: 'id' },
  { title: 'اسم اللقب / المستوى', key: 'name' },
  { title: 'الحد الأدنى للنباتات', key: 'min_plants' },
  { title: 'إجراءات', key: 'actions', sortable: false },
]

const fetchLevels = () => {
  userLevelStore.fetchUserLevels().then(response => {
    levelList.value = response.data.data
  }).catch(error => {
    console.error(error)
  })
}

onMounted(() => {
  fetchLevels()
})

const editItem = (item: UserLevel) => {
  editedItem.value = { ...item }
  addDialog.value = true
}

const deleteItem = (item: UserLevel) => {
  editedItem.value = { ...item }
  deleteDialog.value = true
}

const closeDelete = () => {
  deleteDialog.value = false
  editedItem.value = { ...defaultItem.value }
}

const closeAdd = () => {
  addDialog.value = false
  editedItem.value = { ...defaultItem.value }
}

const submitLevel = () => {
  submitting.value = true

  const request = editedItem.value.id === -1
    ? userLevelStore.createUserLevel(editedItem.value)
    : userLevelStore.updateUserLevel(editedItem.value.id, editedItem.value)

  request.then(() => {
    closeAdd()
    fetchLevels()
  }).finally(() => {
    submitting.value = false
  })
}

const deleteItemConfirm = () => {
  userLevelStore.deleteUserLevel(editedItem.value.id).then(() => {
    closeDelete()
    fetchLevels()
  })
}
</script>

<template>
  <section>
    <VCard>
      <VCardText class="d-flex flex-wrap gap-4">
        <VSpacer />
        <VBtn @click="addDialog = true">
          إضافة لقب جديد
        </VBtn>
      </VCardText>

      <VDataTable
        :headers="headers"
        :items="levelList"
        :items-per-page="-1"
        hide-default-footer
      >
        <!-- name -->
        <template #item.name="{ item }">
          <span class="font-weight-medium text-high-emphasis">{{ item.raw.name }}</span>
        </template>

        <!-- Actions -->
        <template #item.actions="{ item }">
          <VBtn
            icon
            variant="text"
            size="small"
            color="primary"
            @click="editItem(item.raw)"
          >
            <VIcon icon="mdi-pencil-outline" />
          </VBtn>
          <VBtn
            icon
            variant="text"
            size="small"
            color="error"
            @click="deleteItem(item.raw)"
          >
            <VIcon icon="mdi-delete-outline" />
          </VBtn>
        </template>
      </VDataTable>
    </VCard>

    <!-- 👉 Add/Edit Dialog -->
    <VDialog
      v-model="addDialog"
      max-width="500px"
    >
      <VCard>
        <VCardTitle class="d-flex align-center pa-5 pb-0">
          <span class="text-h6">{{ editedItem.id === -1 ? 'إضافة لقب جديد' : 'تعديل اللقب' }}</span>
        </VCardTitle>

        <VCardText class="pa-5">
          <VRow>
            <VCol cols="12">
              <VTextField
                v-model="editedItem.name"
                label="اسم اللقب (مثال: بطل التشجير)"
              />
            </VCol>
            <VCol cols="12">
              <VTextField
                v-model="editedItem.min_plants"
                label="الحد الأدنى من النباتات المقبولة للحصول عليه"
                type="number"
                min="0"
              />
            </VCol>
          </VRow>
        </VCardText>

        <VCardActions class="pa-5 pt-0">
          <VSpacer />
          <VBtn
            variant="outlined"
            @click="closeAdd"
          >
            إلغاء
          </VBtn>
          <VBtn
            color="primary"
            variant="elevated"
            :loading="submitting"
            @click="submitLevel"
          >
            حفظ
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- 👉 Delete Dialog -->
    <VDialog
      v-model="deleteDialog"
      max-width="500px"
    >
      <VCard>
        <VCardTitle>
          هل أنت متأكد من حذف هذا اللقب؟
        </VCardTitle>
        <VCardActions>
          <VSpacer />
          <VBtn
            color="error"
            variant="outlined"
            @click="closeDelete"
          >
            إلغاء
          </VBtn>
          <VBtn
            color="success"
            variant="elevated"
            @click="deleteItemConfirm"
          >
            حذف
          </VBtn>
          <VSpacer />
        </VCardActions>
      </VCard>
    </VDialog>
  </section>
</template>

<route lang="yaml">
meta:
  action: read
  subject: user_levels
</route>
