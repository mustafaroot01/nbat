<script setup lang="ts">
import { useSettingStore } from '@/stores/useSettingStore'
import { VDataTableServer } from 'vuetify/labs/VDataTable'

const settingStore = useSettingStore()

const title = ref('')
const body = ref('')
const target = ref('all')
const topic = ref('')
const image = ref<File[]>([])
const imagePreview = ref<string | null>(null)
const loading = ref(false)
const snackbar = ref(false)
const snackbarMsg = ref('')

const notificationList = ref([])
const totalNotifications = ref(0)
const options = ref({ page: 1, itemsPerPage: 10 })

const targetItems = [
  { title: 'كل الأجهزة', value: 'all' },
  { title: 'Android فقط', value: 'android' },
  { title: 'iOS فقط', value: 'ios' },
]

const headers = [
  { title: '#', key: 'id', sortable: true },
  { title: 'الصورة', key: 'image', sortable: false },
  { title: 'العنوان', key: 'title', sortable: false },
  { title: 'النص', key: 'body', sortable: false },
  { title: 'الجهة المستهدفة', key: 'target', sortable: false },
  { title: 'المرسل', key: 'admin', sortable: false },
  { title: 'التاريخ', key: 'created_at', sortable: false },
  { title: 'الإجراءات', key: 'actions', sortable: false, align: 'center' },
]

const editDialog = ref(false)
const editedItem = ref<any>({})
const editImage = ref<File[]>([])
const editLoading = ref(false)

const deleteDialog = ref(false)
const deleteLoading = ref(false)

const viewDialog = ref(false)
const viewedItem = ref<any>({})

watch(image, (newImage) => {
  if (newImage && newImage.length > 0) {
    imagePreview.value = URL.createObjectURL(newImage[0])
  } else {
    imagePreview.value = null
  }
})

const openViewDialog = (item: any) => {
  viewedItem.value = { ...item.raw }
  viewDialog.value = true
}

const openEditDialog = (item: any) => {
  editedItem.value = { ...item.raw }
  editImage.value = []
  editDialog.value = true
}

const openDeleteDialog = (item: any) => {
  editedItem.value = { ...item.raw }
  deleteDialog.value = true
}

const confirmDelete = () => {
  deleteLoading.value = true
  settingStore.deleteNotification(editedItem.value.id).then(() => {
    snackbarMsg.value = 'تم حذف الإشعار بنجاح'
    snackbar.value = true
    deleteDialog.value = false
    fetchNotifications()
  }).catch(() => {
    snackbarMsg.value = 'حدث خطأ أثناء الحذف'
    snackbar.value = true
  }).finally(() => {
    deleteLoading.value = false
  })
}

const updateNotification = () => {
  editLoading.value = true

  const formData = new FormData()
  formData.append('title', editedItem.value.title)
  formData.append('body', editedItem.value.body)
  formData.append('target', editedItem.value.target)
  if (editedItem.value.target === 'topic') {
    formData.append('topic', editedItem.value.topic)
  }
  if (editImage.value.length) {
    formData.append('image', editImage.value[0])
  }

  settingStore.editNotification(editedItem.value.id, formData).then(() => {
    snackbarMsg.value = 'تم تحديث الإشعار / المقال بنجاح'
    snackbar.value = true
    editDialog.value = false
    fetchNotifications()
  }).catch(() => {
    snackbarMsg.value = 'حدث خطأ أثناء التحديث'
    snackbar.value = true
  }).finally(() => {
    editLoading.value = false
  })
}

const fetchNotifications = () => {
  settingStore.fetchNotifications(options.value).then(response => {
    notificationList.value = response.data.data
    totalNotifications.value = response.data.meta.total
  })
}

watch(options, fetchNotifications, { deep: true })
onMounted(fetchNotifications)

const sendNotification = () => {
  loading.value = true

  const formData = new FormData()
  formData.append('title', title.value)
  formData.append('body', body.value)
  formData.append('target', target.value)
  if (target.value === 'topic') {
    formData.append('topic', topic.value)
  }
  if (image.value.length) {
    formData.append('image', image.value[0])
  }

  settingStore.sendNotification(formData).then(() => {
    snackbarMsg.value = 'تم إرسال الإشعار بنجاح'
    snackbar.value = true
    title.value = ''
    body.value = ''
    target.value = 'all'
    topic.value = ''
    image.value = []
    imagePreview.value = null
    fetchNotifications()
  }).catch(() => {
    snackbarMsg.value = 'حدث خطأ أثناء الإرسال'
    snackbar.value = true
  }).finally(() => {
    loading.value = false
  })
}
</script>

<template>
  <section>
    <!-- 👉 Send Notification Form -->
    <VCard class="mb-6">
      <VCardItem class="pb-4">
        <VCardTitle class="d-flex align-center">
          <VIcon icon="mdi-bell-outline" color="primary" class="me-2" />
          إرسال إشعار جديد
        </VCardTitle>
      </VCardItem>

      <VCardText>
        <VForm @submit.prevent="sendNotification">
          <VRow>
            <VCol cols="12" md="6">
              <VTextField
                v-model="title"
                label="عنوان الإشعار"
                placeholder="أدخل عنواناً جذاباً للإشعار"
                required
              />
            </VCol>

            <VCol cols="12" md="6">
              <VSelect
                v-model="target"
                label="الجهة المستهدفة"
                :items="targetItems"
                required
              />
            </VCol>


            <VCol cols="12">
              <VTextarea
                v-model="body"
                label="نص الإشعار / مقال المدونة"
                placeholder="اكتب تفاصيل الإشعار هنا..."
                rows="3"
                required
              />
            </VCol>

            <VCol cols="12">
              <VFileInput
                v-model="image"
                label="صورة مرفقة (تظهر في التطبيق كمدونة)"
                accept="image/*"
                prepend-icon="mdi-camera-outline"
                show-size
                clearable
              />
              <div v-if="imagePreview" class="mt-4 text-center">
                <VImg :src="imagePreview" max-height="200" class="rounded border" />
                <span class="text-caption mt-1 text-medium-emphasis">معاينة الصورة قبل الإرسال</span>
              </div>
            </VCol>

            <VCol cols="12" class="d-flex gap-4">
              <VBtn
                type="submit"
                :loading="loading"
                color="primary"
                prepend-icon="mdi-send-outline"
              >
                إرسال الإشعار
              </VBtn>
            </VCol>
          </VRow>
        </VForm>
      </VCardText>
    </VCard>

    <!-- 👉 Notifications History Table -->
    <VCard>
      <VCardItem class="pb-4">
        <VCardTitle class="d-flex align-center">
          <VIcon icon="mdi-history" color="primary" class="me-2" />
          سجل الإشعارات / المدونة
        </VCardTitle>
      </VCardItem>

      <VDivider />

      <VDataTableServer
        v-model:items-per-page="options.itemsPerPage"
        v-model:page="options.page"
        :headers="headers"
        :items="notificationList"
        :items-length="totalNotifications"
        class="text-no-wrap"
        @update:options="options = $event"
      >
        <!-- Image -->
        <template #item.image="{ item }">
          <div class="d-flex align-center">
            <VAvatar v-if="item.raw.image_url" rounded size="40" class="me-3">
              <VImg :src="item.raw.image_url" cover />
            </VAvatar>
            <VAvatar v-else rounded size="40" color="grey-100" class="me-3 border">
              <VIcon icon="mdi-image-off-outline" color="grey-400" />
            </VAvatar>
          </div>
        </template>

        <!-- Title & Body -->
        <template #item.title="{ item }">
          <div class="d-flex flex-column">
            <span class="text-body-2 font-weight-bold">{{ item.raw.title }}</span>
            <span class="text-caption text-medium-emphasis text-truncate" style="max-width: 250px;">
              {{ item.raw.body }}
            </span>
          </div>
        </template>

        <!-- Target -->
        <template #item.target="{ item }">
          <VChip
            size="small"
            :color="item.raw.target === 'all' ? 'primary' : 'info'"
            variant="tonal"
            class="font-weight-medium"
          >
            {{ targetItems.find(t => t.value === item.raw.target)?.title || item.raw.target }}

          </VChip>
        </template>
        
        <!-- Admin -->
        <template #item.admin="{ item }">
          <div class="d-flex align-center">
            <VAvatar size="30" color="primary" variant="tonal" class="me-2">
              <VImg v-if="item.raw.admin?.profile_photo" :src="item.raw.admin.profile_photo" />
              <VIcon v-else icon="mdi-shield-account-outline" size="16" />
            </VAvatar>
            <span class="text-body-2">{{ item.raw.admin?.name || '—' }}</span>
          </div>
        </template>

        <!-- Actions -->
        <template #item.actions="{ item }">
          <VBtn icon variant="text" size="small" color="medium-emphasis">
            <VIcon size="24" icon="mdi-dots-vertical" />
            <VMenu activator="parent">
              <VList>
                <VListItem @click="openViewDialog(item)">
                  <template #prepend>
                    <VIcon icon="mdi-eye-outline" class="me-2" />
                  </template>
                  <VListItemTitle>مشاهدة</VListItemTitle>
                </VListItem>

                <VListItem @click="openEditDialog(item)">
                  <template #prepend>
                    <VIcon icon="mdi-pencil-outline" class="me-2" />
                  </template>
                  <VListItemTitle>تعديل</VListItemTitle>
                </VListItem>

                <VListItem @click="openDeleteDialog(item)">
                  <template #prepend>
                    <VIcon icon="mdi-delete-outline" color="error" class="me-2" />
                  </template>
                  <VListItemTitle class="text-error">حذف</VListItemTitle>
                </VListItem>
              </VList>
            </VMenu>
          </VBtn>
        </template>

        <!-- Bottom Pagination -->
        <template #bottom>
          <VDivider />
          <div class="d-flex justify-end gap-x-6 pa-2 flex-wrap">
            <div class="d-flex align-center gap-x-2 text-sm">
              صفوف لكل صفحة:
              <VSelect
                v-model="options.itemsPerPage"
                class="per-page-select text-high-emphasis"
                variant="plain"
                density="compact"
                :items="[10, 20, 50, 100]"
                @update:model-value="options.page = 1"
              />
            </div>

            <span class="d-flex align-center text-sm me-2 text-high-emphasis">
              عرض {{ totalNotifications > 0 ? (options.page - 1) * options.itemsPerPage + 1 : 0 }} إلى {{ Math.min(options.page * options.itemsPerPage, totalNotifications) }} من أصل {{ totalNotifications }}
            </span>

            <div class="d-flex gap-x-2 align-center me-2">
              <VBtn
                icon="mdi-chevron-left"
                class="flip-in-rtl"
                variant="text"
                density="comfortable"
                color="default"
                :disabled="options.page <= 1"
                @click="options.page--"
              />

              <VBtn
                icon="mdi-chevron-right"
                class="flip-in-rtl"
                variant="text"
                density="comfortable"
                color="default"
                :disabled="options.page >= Math.ceil(totalNotifications / options.itemsPerPage)"
                @click="options.page++"
              />
            </div>
          </div>
        </template>
      </VDataTableServer>
    </VCard>

    <!-- 👉 Edit Dialog -->
    <VDialog v-model="editDialog" max-width="600">
      <VCard title="تعديل الإشعار / المقال">
        <VCardText>
          <VForm @submit.prevent="updateNotification">
            <VRow>
              <VCol cols="12">
                <VTextField
                  v-model="editedItem.title"
                  label="العنوان"
                  required
                />
              </VCol>
              
              <VCol cols="12">
                <VSelect
                  v-model="editedItem.target"
                  label="الجهة المستهدفة"
                  :items="targetItems"
                  required
                />
              </VCol>

              <VCol cols="12">
                <VTextarea
                  v-model="editedItem.body"
                  label="نص المقال"
                  rows="4"
                  required
                />
              </VCol>

              <VCol cols="12">
                <div v-if="editedItem.image_url" class="mb-4 text-center">
                  <VImg :src="editedItem.image_url" max-height="150" class="rounded" />
                  <span class="text-caption">الصورة الحالية</span>
                </div>
                <VFileInput
                  v-model="editImage"
                  label="صورة جديدة (تترك فارغة إذا لم ترغب بالتغيير)"
                  accept="image/*"
                  prepend-icon="mdi-camera-outline"
                />
              </VCol>

              <VCol cols="12" class="d-flex gap-4 justify-end">
                <VBtn
                  color="secondary"
                  variant="tonal"
                  @click="editDialog = false"
                >
                  إلغاء
                </VBtn>
                <VBtn
                  type="submit"
                  color="primary"
                  :loading="editLoading"
                >
                  حفظ التعديلات
                </VBtn>
              </VCol>
            </VRow>
          </VForm>
        </VCardText>
      </VCard>
    </VDialog>

    <!-- 👉 Delete Dialog -->
    <VDialog v-model="deleteDialog" max-width="500px">
      <VCard>
        <VCardTitle class="text-h5 text-center mt-4">
          هل أنت متأكد من رغبتك في حذف هذا الإشعار / المقال؟
        </VCardTitle>
        <VCardText class="text-center text-error">
          لا يمكن التراجع عن هذه الخطوة بعد تنفيذها!
        </VCardText>
        <VCardActions class="pb-6 justify-center">
          <VBtn color="secondary" variant="tonal" @click="deleteDialog = false">إلغاء</VBtn>
          <VBtn color="error" variant="elevated" :loading="deleteLoading" @click="confirmDelete">حذف</VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- 👉 View Dialog -->
    <VDialog v-model="viewDialog" max-width="500px">
      <VCard>
        <VCardItem class="pb-2">
          <VCardTitle>تفاصيل الإشعار / المدونة</VCardTitle>
          <template #append>
            <VBtn icon="mdi-close" variant="text" @click="viewDialog = false" />
          </template>
        </VCardItem>
        <VDivider />
        
        <VCardText class="pt-4">
          <div v-if="viewedItem.image_url" class="mb-4">
            <VImg :src="viewedItem.image_url" max-height="250" class="rounded border" cover />
          </div>
          
          <h3 class="text-h6 font-weight-bold mb-2">{{ viewedItem.title }}</h3>
          <p class="text-body-1 mb-4">{{ viewedItem.body }}</p>

          <VList class="bg-var-theme-background rounded border pa-0">
            <VListItem>
              <template #prepend>
                <VIcon icon="mdi-bullseye-arrow" size="20" class="me-3 text-primary" />
              </template>
              <VListItemTitle class="font-weight-bold">الجهة المستهدفة</VListItemTitle>
              <VListItemSubtitle>
                {{ targetItems.find(t => t.value === viewedItem.target)?.title || viewedItem.target }}

              </VListItemSubtitle>
            </VListItem>
            <VDivider />
            <VListItem>
              <template #prepend>
                <VIcon icon="mdi-account-outline" size="20" class="me-3 text-success" />
              </template>
              <VListItemTitle class="font-weight-bold">المرسل</VListItemTitle>
              <VListItemSubtitle>{{ viewedItem.admin?.name || '—' }}</VListItemSubtitle>
            </VListItem>
            <VDivider />
            <VListItem>
              <template #prepend>
                <VIcon icon="mdi-calendar-blank-outline" size="20" class="me-3 text-info" />
              </template>
              <VListItemTitle class="font-weight-bold">التاريخ</VListItemTitle>
              <VListItemSubtitle>{{ viewedItem.created_at }}</VListItemSubtitle>
            </VListItem>
          </VList>
        </VCardText>
      </VCard>
    </VDialog>

    <VSnackbar
      v-model="snackbar"
      :timeout="3000"
    >
      {{ snackbarMsg }}
    </VSnackbar>
  </section>
</template>

<route lang="yaml">
meta:
  action: read
  subject: notifications
</route>
