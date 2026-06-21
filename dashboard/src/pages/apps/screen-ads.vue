<script setup lang="ts">
import { useScreenAdStore } from '@/stores/useScreenAdStore'
import { VDataTable } from 'vuetify/labs/VDataTable'

interface ScreenAd {
  id: number
  image: string
  is_active: boolean
}

const screenAdStore = useScreenAdStore()

const deleteDialog = ref(false)
const addDialog = ref(false)

const defaultItem = ref<ScreenAd>({
  id: -1,
  image: '',
  is_active: true,
})

const editedItem = ref<ScreenAd>({ ...defaultItem.value })
const adList = ref<ScreenAd[]>([])
const adImage = ref<File[]>([])
const currentPage = ref(1)
const itemsPerPage = ref(10)
const isDragging = ref(false)
const submitting = ref(false)

const imagePreview = computed(() => {
  if (adImage.value.length)
    return URL.createObjectURL(adImage.value[0])

  return null
})

const onDrop = (e: DragEvent) => {
  isDragging.value = false
  const files = e.dataTransfer?.files
  if (files?.length && files[0].type.startsWith('image/'))
    adImage.value = [files[0]]
}

const onFileSelect = () => {
  const input = document.createElement('input')
  input.type = 'file'
  input.accept = 'image/*'
  input.onchange = (e: Event) => {
    const file = (e.target as HTMLInputElement).files?.[0]
    if (file)
      adImage.value = [file]
  }
  input.click()
}

const removeImage = () => {
  adImage.value = []
}

const headers = [
  { title: '#', key: 'index', sortable: false },
  { title: 'الصورة', key: 'image', sortable: false },
  { title: 'الحالة', key: 'is_active', sortable: false },
  { title: 'إجراءات', key: 'actions', sortable: false },
]

const fetchAds = () => {
  screenAdStore.fetchScreenAds({}).then(response => {
    adList.value = response.data.data
  }).catch(error => {
    console.error(error)
  })
}

watchEffect(fetchAds)

const deleteItem = (item: ScreenAd) => {
  editedItem.value = { ...item }
  deleteDialog.value = true
}

const closeDelete = () => {
  deleteDialog.value = false
  editedItem.value = { ...defaultItem.value }
}

const closeAdd = () => {
  addDialog.value = false
  adImage.value = []
}

const submitAd = () => {
  if (!adImage.value.length)
    return

  submitting.value = true

  const formData = new FormData()
  formData.append('image', adImage.value[0])

  screenAdStore.createScreenAd(formData).then(() => {
    closeAdd()
    fetchAds()
  }).finally(() => {
    submitting.value = false
  })
}

const toggleAd = (item: ScreenAd) => {
  screenAdStore.toggleScreenAd(item.id).then(() => fetchAds())
}

const deleteItemConfirm = () => {
  screenAdStore.deleteScreenAd(editedItem.value.id).then(() => {
    closeDelete()
    fetchAds()
  })
}
</script>

<template>
  <section>
    <!-- 👉 Datatable -->
    <VCard>
      <VCardText class="d-flex flex-wrap gap-4">
        <VSpacer />
        <VBtn @click="addDialog = true">
          إضافة إعلان جديد
        </VBtn>
      </VCardText>

      <VDataTable
        :headers="headers"
        :items="adList"
        :items-per-page="itemsPerPage"
        :page="currentPage"
      >
        <!-- # index -->
        <template #item.index="{ index }">
          <span class="text-sm text-high-emphasis">{{ index + 1 }}</span>
        </template>

        <!-- image -->
        <template #item.image="{ item }">
          <VImg
            :src="item.raw.image"
            width="80"
            height="140"
            cover
            class="rounded my-2 bg-grey-200"
          />
        </template>

        <!-- is_active -->
        <template #item.is_active="{ item }">
          <VChip
            :color="item.raw.is_active ? 'success' : 'secondary'"
            density="comfortable"
          >
            {{ item.raw.is_active ? 'مفعل' : 'معطل' }}
          </VChip>
        </template>

        <!-- Actions -->
        <template #item.actions="{ item }">
          <VBtn
            icon
            variant="text"
            size="small"
            color="medium-emphasis"
          >
            <VIcon
              size="24"
              icon="mdi-dots-vertical"
            />

            <VMenu activator="parent">
              <VList>
                <VListItem @click="toggleAd(item.raw)">
                  <template #prepend>
                    <VIcon :icon="item.raw.is_active ? 'mdi-eye-off-outline' : 'mdi-eye-outline'" />
                  </template>
                  <VListItemTitle>{{ item.raw.is_active ? 'تعطيل' : 'تفعيل' }}</VListItemTitle>
                </VListItem>

                <VListItem @click="deleteItem(item.raw)">
                  <template #prepend>
                    <VIcon icon="mdi-delete-outline" />
                  </template>
                  <VListItemTitle>حذف</VListItemTitle>
                </VListItem>
              </VList>
            </VMenu>
          </VBtn>
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
                :length="Math.ceil(adList.length / itemsPerPage)"
                prev-icon="mdi-menu-left"
                next-icon="mdi-menu-right"
              />
            </div>
          </VCardText>
        </template>
      </VDataTable>
    </VCard>

    <!-- 👉 Add Dialog -->
    <VDialog
      v-model="addDialog"
      max-width="580px"
    >
      <VCard>
        <VCardTitle class="d-flex align-center pa-5 pb-0">
          <VIcon
            icon="mdi-image-plus-outline"
            color="primary"
            size="28"
            class="me-2"
          />
          <span class="text-h6">إضافة إعلان شاشة (Splash Ad)</span>
        </VCardTitle>

        <VCardText class="pa-5">
          <!-- Drop zone / Preview -->
          <div
            v-if="!imagePreview"
            class="upload-zone rounded-lg pa-8 text-center"
            :class="{ 'upload-zone--active': isDragging }"
            @dragover.prevent="isDragging = true"
            @dragleave.prevent="isDragging = false"
            @drop.prevent="onDrop"
            @click="onFileSelect"
          >
            <VIcon
              icon="mdi-cloud-upload-outline"
              size="48"
              color="primary"
              class="mb-3"
            />
            <p class="text-body-1 font-weight-medium mb-1">
              اسحب الصورة هنا أو اضغط للاختيار
            </p>
            <p class="text-caption text-medium-emphasis">
              يفضل أن تكون الصورة طولية (Portrait). PNG, JPG — حجم أقصى 5MB
            </p>
          </div>

          <!-- Image Preview -->
          <div
            v-else
            class="preview-container rounded-lg overflow-hidden position-relative d-flex justify-center"
          >
            <VImg
              :src="imagePreview"
              max-height="400"
              contain
              class="rounded-lg"
            />
            <VBtn
              icon
              size="small"
              color="error"
              variant="elevated"
              class="preview-remove position-absolute"
              style="top: 8px; right: 8px;"
              @click="removeImage"
            >
              <VIcon
                icon="mdi-close"
                size="18"
              />
            </VBtn>
          </div>
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
            :disabled="!adImage.length"
            :loading="submitting"
            @click="submitAd"
          >
            <VIcon
              icon="mdi-upload"
              start
            />
            رفع الإعلان
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
          هل أنت متأكد من حذف هذا الإعلان؟
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
  subject: screen_ads
</route>
