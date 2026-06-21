<script setup lang="ts">
import { useSettingStore } from '@/stores/useSettingStore'
import { VDataTable } from 'vuetify/labs/VDataTable'

interface AppVersion {
  id: number
  platform: string
  version_number: string
  version_code: string
  update_type: string
  store_url: string
  release_notes: string
}

const settingStore = useSettingStore()

const editDialog = ref(false)
const deleteDialog = ref(false)
const addDialog = ref(false)

const defaultItem = ref<AppVersion>({
  id: -1,
  platform: 'android',
  version_number: '',
  version_code: '',
  update_type: 'optional',
  store_url: '',
  release_notes: '',
})

const editedItem = ref<AppVersion>({ ...defaultItem.value })
const editedIndex = ref(-1)
const versionList = ref<AppVersion[]>([])
const currentPage = ref(1)
const itemsPerPage = ref(10)

const headers = [
  { title: '#', key: 'id' },
  { title: 'المنصة', key: 'platform' },
  { title: 'رقم الإصدار', key: 'version_number' },
  { title: 'كود الإصدار', key: 'version_code' },
  { title: 'نوع التحديث', key: 'update_type' },
  { title: 'رابط المتجر', key: 'store_url' },
  { title: 'إجراءات', key: 'actions' },
]

const platformItems = [
  { title: 'Android', value: 'android' },
  { title: 'iOS', value: 'ios' },
]

const updateTypeItems = [
  { title: 'اختياري', value: 'optional' },
  { title: 'إجباري', value: 'forced' },
]

const fetchVersions = () => {
  settingStore.fetchAppVersions().then(response => {
    versionList.value = response.data.data
  }).catch(error => {
    console.error(error)
  })
}

watchEffect(fetchVersions)

// 👉 methods
const editItem = (item: AppVersion) => {
  editedIndex.value = versionList.value.indexOf(item)
  editedItem.value = { ...item }
  editDialog.value = true
}

const deleteItem = (item: AppVersion) => {
  editedIndex.value = versionList.value.indexOf(item)
  editedItem.value = { ...item }
  deleteDialog.value = true
}

const close = () => {
  editDialog.value = false
  editedIndex.value = -1
  editedItem.value = { ...defaultItem.value }
}

const closeDelete = () => {
  deleteDialog.value = false
  editedIndex.value = -1
  editedItem.value = { ...defaultItem.value }
}

const closeAdd = () => {
  addDialog.value = false
  editedItem.value = { ...defaultItem.value }
}

const openAdd = () => {
  editedItem.value = { ...defaultItem.value }
  addDialog.value = true
}

const save = () => {
  settingStore.createAppVersion(editedItem.value).then(() => {
    closeAdd()
    fetchVersions()
  })
}

const saveEdit = () => {
  settingStore.updateAppVersion(editedItem.value.id, editedItem.value).then(() => {
    close()
    fetchVersions()
  })
}

const deleteItemConfirm = () => {
  settingStore.deleteAppVersion(editedItem.value.id).then(() => {
    closeDelete()
    fetchVersions()
  })
}
</script>

<template>
  <section>
    <!-- 👉 Datatable -->
    <VCard>
      <VCardText class="d-flex flex-wrap gap-4">
        <VSpacer />
        <VBtn @click="openAdd">
          إضافة إصدار
        </VBtn>
      </VCardText>

      <VDataTable
        :headers="headers"
        :items="versionList"
        :items-per-page="itemsPerPage"
        :page="currentPage"
      >
        <!-- platform -->
        <template #item.platform="{ item }">
          <VChip
            :color="item.raw.platform === 'android' ? 'success' : 'info'"
            density="comfortable"
          >
            {{ item.raw.platform }}
          </VChip>
        </template>

        <!-- update_type -->
        <template #item.update_type="{ item }">
          <VChip
            :color="item.raw.update_type === 'forced' ? 'error' : 'warning'"
            density="comfortable"
          >
            {{ item.raw.update_type === 'forced' ? 'إجباري' : 'اختياري' }}
          </VChip>
        </template>

        <!-- store_url -->
        <template #item.store_url="{ item }">
          <span class="text-sm">{{ item.raw.store_url || '—' }}</span>
        </template>

        <!-- Actions -->
        <template #item.actions="{ item }">
          <div class="d-flex gap-1">
            <IconBtn @click="editItem(item.raw)">
              <VIcon icon="mdi-pencil-outline" />
            </IconBtn>

            <IconBtn @click="deleteItem(item.raw)">
              <VIcon icon="mdi-delete-outline" />
            </IconBtn>
          </div>
        </template>
        <template #bottom>
          <VCardText class="pt-2">
            <div class="d-flex flex-wrap justify-center justify-sm-space-between gap-y-2 mt-2">
              <VTextField
                v-model="itemsPerPage"
                label="عدد الصفوف:"
                type="number"
                min="1"
                max="100"
                hide-details
                variant="underlined"
                style="min-width: 5rem;max-width: 8rem;"
              />

              <VPagination
                v-model="currentPage"
                :total-visible="$vuetify.display.smAndDown ? 3 : 5"
                :length="Math.ceil(versionList.length / itemsPerPage)"
                prev-icon="mdi-menu-left"
                next-icon="mdi-menu-right"
              />
            </div>
          </VCardText>
        </template>
      </VDataTable>
    </VCard>

    <!-- 👉 Add/Edit Dialog -->
    <VDialog
      v-model="addDialog"
      max-width="600px"
    >
      <VCard>
        <VCardTitle>
          <span class="headline">إضافة إصدار جديد</span>
        </VCardTitle>

        <VCardText>
          <VContainer>
            <VRow>
              <VCol
                cols="12"
                sm="6"
              >
                <VSelect
                  v-model="editedItem.platform"
                  label="المنصة"
                  :items="platformItems"
                />
              </VCol>
              <VCol
                cols="12"
                sm="6"
              >
                <VSelect
                  v-model="editedItem.update_type"
                  label="نوع التحديث"
                  :items="updateTypeItems"
                />
              </VCol>
              <VCol
                cols="12"
                sm="6"
              >
                <VTextField
                  v-model="editedItem.version_number"
                  label="رقم الإصدار (مثل: 1.2.0)"
                />
              </VCol>
              <VCol
                cols="12"
                sm="6"
              >
                <VTextField
                  v-model="editedItem.version_code"
                  label="كود الإصدار (مثل: 12)"
                />
              </VCol>
              <VCol cols="12">
                <VTextField
                  v-model="editedItem.store_url"
                  label="رابط المتجر"
                />
              </VCol>
              <VCol cols="12">
                <VTextarea
                  v-model="editedItem.release_notes"
                  label="ملاحظات الإصدار"
                  rows="2"
                />
              </VCol>
            </VRow>
          </VContainer>
        </VCardText>

        <VCardActions>
          <VSpacer />
          <VBtn
            color="error"
            variant="outlined"
            @click="closeAdd"
          >
            إلغاء
          </VBtn>
          <VBtn
            color="success"
            variant="elevated"
            @click="save"
          >
            إضافة
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- 👉 Edit Dialog -->
    <VDialog
      v-model="editDialog"
      max-width="600px"
    >
      <VCard>
        <VCardTitle>
          <span class="headline">تعديل الإصدار</span>
        </VCardTitle>

        <VCardText>
          <VContainer>
            <VRow>
              <VCol
                cols="12"
                sm="6"
              >
                <VSelect
                  v-model="editedItem.platform"
                  label="المنصة"
                  :items="platformItems"
                />
              </VCol>
              <VCol
                cols="12"
                sm="6"
              >
                <VSelect
                  v-model="editedItem.update_type"
                  label="نوع التحديث"
                  :items="updateTypeItems"
                />
              </VCol>
              <VCol
                cols="12"
                sm="6"
              >
                <VTextField
                  v-model="editedItem.version_number"
                  label="رقم الإصدار"
                />
              </VCol>
              <VCol
                cols="12"
                sm="6"
              >
                <VTextField
                  v-model="editedItem.version_code"
                  label="كود الإصدار"
                />
              </VCol>
              <VCol cols="12">
                <VTextField
                  v-model="editedItem.store_url"
                  label="رابط المتجر"
                />
              </VCol>
              <VCol cols="12">
                <VTextarea
                  v-model="editedItem.release_notes"
                  label="ملاحظات الإصدار"
                  rows="2"
                />
              </VCol>
            </VRow>
          </VContainer>
        </VCardText>

        <VCardActions>
          <VSpacer />
          <VBtn
            color="error"
            variant="outlined"
            @click="close"
          >
            إلغاء
          </VBtn>
          <VBtn
            color="success"
            variant="elevated"
            @click="saveEdit"
          >
            حفظ التعديلات
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
          هل أنت متأكد من حذف هذا الإصدار؟
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
  subject: app_versions
</route>
