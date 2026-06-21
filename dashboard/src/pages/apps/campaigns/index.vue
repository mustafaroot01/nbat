<script setup lang="ts">
import { useCampaignStore } from '@/stores/useCampaignStore'
import { VDataTableServer } from 'vuetify/labs/VDataTable'

interface Campaign {
  id: number
  title: string
  description: string
  image: string | null
  target_plants: number
  is_active: boolean
  start_date: string | null
  end_date: string | null
  plants_count: number
}

const campaignStore = useCampaignStore()

const deleteDialog = ref(false)
const addDialog = ref(false)

const defaultItem = ref<Campaign>({
  id: -1,
  title: '',
  description: '',
  image: null,
  target_plants: 100,
  is_active: true,
  start_date: null,
  end_date: null,
  plants_count: 0,
})

const editedItem = ref<Campaign>({ ...defaultItem.value })
const imageFile = ref<File | null>(null)
const imagePreviewUrl = ref<string | null>(null)

const campaignList = ref<Campaign[]>([])
const submitting = ref(false)
const totalCampaigns = ref(0)
const loading = ref(false)

const options = ref({
  page: 1,
  itemsPerPage: 15,
})

const headers = [
  { title: '#', key: 'id' },
  { title: 'صورة', key: 'image', sortable: false },
  { title: 'عنوان الحملة', key: 'title' },
  { title: 'الهدف', key: 'target_plants' },
  { title: 'تاريخ الحملة', key: 'dates' },
  { title: 'الحالة', key: 'is_active' },
  { title: 'إجراءات', key: 'actions', sortable: false },
]

const fetchCampaigns = () => {
  loading.value = true
  campaignStore.fetchCampaigns({
    page: options.value.page,
    per_page: options.value.itemsPerPage,
  }).then(response => {
    campaignList.value = response.data.data
    totalCampaigns.value = response.data.meta.total
  }).finally(() => {
    loading.value = false
  })
}

const resolveCampaignUrl = (path: string | null) => {
  if (!path) return ''
  return path.startsWith('http') ? path : `http://localhost:8000/storage/${path}`
}

const handleImageUpload = (event: any) => {
  const file = event.target.files[0]
  if (file) {
    imageFile.value = file
    imagePreviewUrl.value = URL.createObjectURL(file)
  }
}

const editItem = (item: Campaign) => {
  editedItem.value = { ...item }
  imageFile.value = null
  imagePreviewUrl.value = item.image ? resolveCampaignUrl(item.image) : null
  addDialog.value = true
}

const deleteItem = (item: Campaign) => {
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
  imageFile.value = null
  imagePreviewUrl.value = null
}

const submitCampaign = () => {
  submitting.value = true

  const formData = new FormData()
  formData.append('title', editedItem.value.title)
  formData.append('description', editedItem.value.description || '')
  formData.append('target_plants', String(editedItem.value.target_plants))
  formData.append('is_active', editedItem.value.is_active ? '1' : '0')
  if (editedItem.value.start_date) formData.append('start_date', editedItem.value.start_date)
  if (editedItem.value.end_date) formData.append('end_date', editedItem.value.end_date)

  if (imageFile.value) {
    formData.append('image', imageFile.value)
  }

  // If edit
  if (editedItem.value.id !== -1) {
    formData.append('_method', 'PUT')
  }

  const request = editedItem.value.id === -1
    ? campaignStore.createCampaign(formData)
    : campaignStore.updateCampaign(editedItem.value.id, formData)

  request.then(() => {
    closeAdd()
    fetchCampaigns()
  }).finally(() => {
    submitting.value = false
  })
}

const deleteItemConfirm = () => {
  campaignStore.deleteCampaign(editedItem.value.id).then(() => {
    closeDelete()
    fetchCampaigns()
  })
}
</script>

<template>
  <section>
    <VCard>
      <VCardText class="d-flex flex-wrap gap-4">
        <VSpacer />
        <VBtn @click="addDialog = true">
          إضافة حملة تشجير
        </VBtn>
      </VCardText>

      <VDataTableServer
        v-model:items-per-page="options.itemsPerPage"
        v-model:page="options.page"
        :headers="headers"
        :items="campaignList"
        :items-length="totalCampaigns"
        :loading="loading"
        @update:options="fetchCampaigns"
      >
        <!-- Image -->
        <template #item.image="{ item }">
          <VAvatar
            v-if="item.raw.image"
            size="50"
            variant="tonal"
            class="my-2 rounded"
          >
            <VImg :src="resolveCampaignUrl(item.raw.image)" cover />
          </VAvatar>
          <span v-else>بدون صورة</span>
        </template>

        <!-- Target & Stats -->
        <template #item.target_plants="{ item }">
          <div class="d-flex flex-column gap-1 my-2">
            <span class="text-caption">زُرع: {{ item.raw.plants_count }} / {{ item.raw.target_plants }}</span>
            <VProgressLinear
              :model-value="(item.raw.plants_count / item.raw.target_plants) * 100"
              color="success"
              height="8"
              rounded
            />
          </div>
        </template>

        <!-- Dates -->
        <template #item.dates="{ item }">
          <div v-if="item.raw.start_date || item.raw.end_date">
            <VChip size="small" color="primary" class="mb-1" v-if="item.raw.start_date">
              من: {{ item.raw.start_date }}
            </VChip>
            <br v-if="item.raw.start_date && item.raw.end_date">
            <VChip size="small" color="error" v-if="item.raw.end_date">
              إلى: {{ item.raw.end_date }}
            </VChip>
          </div>
          <span v-else class="text-disabled">بدون تاريخ محدد</span>
        </template>

        <!-- Status -->
        <template #item.is_active="{ item }">
          <VChip
            size="small"
            :color="item.raw.is_active ? 'success' : 'error'"
          >
            {{ item.raw.is_active ? 'مفعلة' : 'متوقفة' }}
          </VChip>
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
      </VDataTableServer>
    </VCard>

    <!-- 👉 Add/Edit Dialog -->
    <VDialog
      v-model="addDialog"
      max-width="600px"
    >
      <VCard>
        <VCardTitle class="d-flex align-center pa-5 pb-0">
          <span class="text-h6">{{ editedItem.id === -1 ? 'إضافة حملة جديدة' : 'تعديل الحملة' }}</span>
        </VCardTitle>

        <VCardText class="pa-5">
          <VRow>
            <VCol cols="12" class="text-center">
              <VAvatar
                size="120"
                variant="tonal"
                color="secondary"
                class="mb-3 rounded"
              >
                <VImg v-if="imagePreviewUrl" :src="imagePreviewUrl" cover />
                <VIcon v-else icon="mdi-image-outline" size="40" />
              </VAvatar>
              <div>
                <VBtn
                  color="primary"
                  variant="outlined"
                  size="small"
                  @click="$refs.fileInput.click()"
                >
                  <VIcon icon="mdi-upload" start />
                  رفع صورة للحملة
                </VBtn>
                <input
                  ref="fileInput"
                  type="file"
                  accept="image/*"
                  hidden
                  @change="handleImageUpload"
                >
              </div>
            </VCol>

            <VCol cols="12" md="12">
              <VTextField
                v-model="editedItem.title"
                label="اسم الحملة (مثال: حملة تشجير مدارس بغداد)"
              />
            </VCol>

            <VCol cols="12" md="12">
              <VTextarea
                v-model="editedItem.description"
                label="وصف الحملة (اختياري)"
                rows="2"
              />
            </VCol>

            <VCol cols="12" md="6">
              <VTextField
                v-model="editedItem.target_plants"
                label="الهدف (عدد الأشجار المطلوب)"
                type="number"
                min="1"
              />
            </VCol>

            <VCol cols="12" md="6">
              <VSelect
                v-model="editedItem.is_active"
                label="حالة الحملة"
                :items="[{ title: 'فعالة ومستمرة', value: true }, { title: 'متوقفة / منتهية', value: false }]"
              />
            </VCol>

            <VCol cols="12" md="6">
              <VTextField
                v-model="editedItem.start_date"
                label="تاريخ البدء"
                type="date"
              />
            </VCol>

            <VCol cols="12" md="6">
              <VTextField
                v-model="editedItem.end_date"
                label="تاريخ الانتهاء (اختياري)"
                type="date"
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
            @click="submitCampaign"
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
          هل أنت متأكد من مسح هذه الحملة بالكامل؟
        </VCardTitle>
        <VCardText>
          مسح الحملة سيؤدي إلى مسح اسمها من جميع النباتات المرتبطة بها (الأفضل إيقافها فقط من التعديل).
        </VCardText>
        <VCardActions>
          <VSpacer />
          <VBtn
            color="secondary"
            variant="outlined"
            @click="closeDelete"
          >
            إلغاء
          </VBtn>
          <VBtn
            color="error"
            variant="elevated"
            @click="deleteItemConfirm"
          >
            نعم، مسح نهائي
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
  subject: campaigns
</route>
