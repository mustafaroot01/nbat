<script setup lang="ts">
import { usePlantStore } from '@/stores/usePlantStore'
import { useSettingStore } from '@/stores/useSettingStore'
import { VDataTableServer } from 'vuetify/labs/VDataTable'
import { useRoute } from 'vue-router'

const route = useRoute()

interface Plant {
  id: number
  name: string
  type: string
  age: string
  image: string
  notes: string
  governorate: any
  latitude: number
  longitude: number
  user: any
  status: string
  rejection_reason: string
  created_at: string
}

const plantStore = usePlantStore()
const settingStore = useSettingStore()

const editDialog = ref(false)
const deleteDialog = ref(false)
const rejectDialog = ref(false)

const defaultItem = ref<Plant>({
  id: -1,
  name: '',
  type: '',
  age: '',
  image: '',
  notes: '',
  governorate: '',
  latitude: 0,
  longitude: 0,
  user: '',
  status: 'pending',
  rejection_reason: '',
  created_at: '',
})

const editedItem = ref<Plant>({ ...defaultItem.value })
const plantList = ref<Plant[]>([])

// Table options
const totalPlants = ref(0)
const options = ref({ page: 1, itemsPerPage: 10, sortBy: [], sortDesc: [] })

// Filters
const searchQuery = ref('')
const selectedStatus = ref('')
const selectedGovernorate = ref<number | null>(null)
const governorates = ref([])
const rejectReason = ref('')

const headers = [
  { title: '# المعرف', key: 'id', sortable: true },
  { title: 'المستخدم', key: 'user', sortable: false },
  { title: 'النبتة والصورة', key: 'plant', sortable: false },
  { title: 'الموقع', key: 'location', sortable: false },
  { title: 'تاريخ الزراعة', key: 'date', sortable: false },
  { title: 'الحالة', key: 'status', sortable: false },
  { title: 'ملاحظات', key: 'notes', sortable: false, align: 'center' },
  { title: 'الإجراءات', key: 'actions', sortable: false, align: 'center' },
]

const statusItems = [
  { title: 'الكل', value: '' },
  { title: 'معلق', value: 'pending' },
  { title: 'معتمد', value: 'approved' },
  { title: 'مرفوض', value: 'rejected' },
]

const fetchPlants = () => {
  plantStore.fetchPlants({
    q: searchQuery.value,
    status: selectedStatus.value,
    governorate_id: selectedGovernorate.value,
    page: options.value.page,
    itemsPerPage: options.value.itemsPerPage,
  }).then(response => {
    plantList.value = response.data.data
    totalPlants.value = response.data.meta.total
  }).catch(error => {
    console.error(error)
  })
}

const fetchGovernorates = () => {
  settingStore.fetchGovernorates().then(response => {
    governorates.value = response.data.data
  })
}

onMounted(async () => {
  fetchGovernorates()

  // If navigated from governorate-stats with a specific governorate_id
  if (route.query.governorate_id) {
    selectedGovernorate.value = Number(route.query.governorate_id)
  }

  // If navigated from the map with a specific plant_id → auto-open its dialog
  if (route.query.plant_id) {
    const plantId = Number(route.query.plant_id)
    try {
      const res = await plantStore.fetchPlants({ q: String(plantId), itemsPerPage: 10, page: 1 })
      const found = res.data.data.find((p: Plant) => p.id === plantId)
      if (found) {
        viewItem(found)
      }
    } catch (e) {
      console.error('Failed to auto-open plant', e)
    }
  }
})

watchEffect(fetchPlants)

// Methods
const viewItem = (item: Plant) => {
  editedItem.value = { ...item }
  editDialog.value = true
}

const deleteItem = (item: Plant) => {
  editedItem.value = { ...item }
  deleteDialog.value = true
}

const close = () => {
  editDialog.value = false
  editedItem.value = { ...defaultItem.value }
}

const closeDelete = () => {
  deleteDialog.value = false
  editedItem.value = { ...defaultItem.value }
}

const pendingPlant = (item: Plant) => {
  plantStore.pendingPlant(item.id).then(() => {
    editDialog.value = false
    editedItem.value = { ...defaultItem.value }
    fetchPlants()
  })
}

const approvePlant = (item: Plant) => {
  plantStore.approvePlant(item.id).then(() => {
    editDialog.value = false
    editedItem.value = { ...defaultItem.value }
    fetchPlants()
  })
}

const openRejectDialog = (item: Plant) => {
  editedItem.value = { ...item }
  rejectReason.value = ''
  editDialog.value = false
  rejectDialog.value = true
}

const confirmReject = () => {
  if (editedItem.value.id && rejectReason.value) {
    plantStore.rejectPlant(editedItem.value.id, rejectReason.value).then(() => {
      rejectDialog.value = false
      editedItem.value = { ...defaultItem.value }
      fetchPlants()
    })
  }
}

const deleteItemConfirm = () => {
  plantStore.deletePlant(editedItem.value.id).then(() => {
    closeDelete()
    fetchPlants()
  })
}

const resolveStatusVariant = (stat: string) => {
  if (stat === 'pending')
    return { color: 'warning', text: 'معلق', icon: 'mdi-clock-outline' }
  if (stat === 'approved')
    return { color: 'success', text: 'معتمد', icon: 'mdi-check-circle' }
  if (stat === 'rejected')
    return { color: 'error', text: 'مرفوض', icon: 'mdi-close-circle' }

  return { color: 'primary', text: stat, icon: 'mdi-help-circle' }
}

const copyCoordinates = (lat: number, lng: number) => {
  if (lat && lng) {
    navigator.clipboard.writeText(`${lat},${lng}`)
  }
}

const avatarText = (value: string) => {
  if (!value) return ''
  const nameArray = value.split(' ')
  return nameArray.map(word => word.charAt(0)).join('').toUpperCase().substring(0, 2)
}
</script>

<template>
  <section>
    <!-- 👉 Filters -->
    <VCard class="mb-6">
      <VCardItem class="pb-4">
        <VCardTitle>تصفية الزراعات</VCardTitle>
      </VCardItem>

      <VCardText>
        <VRow>
          <VCol cols="12" sm="4">
            <VTextField
              v-model="searchQuery"
              label="بحث باسم النبتة أو الصنف أو صاحبها"
              placeholder="ابحث..."
              clearable
              prepend-inner-icon="mdi-magnify"
              @update:model-value="options.page = 1"
            />
          </VCol>

          <VCol cols="12" sm="4">
            <VSelect
              v-model="selectedGovernorate"
              label="المحافظة"
              :items="governorates"
              item-title="name_ar"
              item-value="id"
              clearable
              clear-icon="mdi-close"
              @update:model-value="options.page = 1"
            />
          </VCol>

          <VCol cols="12" sm="4">
            <VSelect
              v-model="selectedStatus"
              label="حالة النبتة"
              :items="statusItems"
              clearable
              clear-icon="mdi-close"
              @update:model-value="options.page = 1"
            />
          </VCol>
        </VRow>
      </VCardText>
    </VCard>

    <!-- 👉 Datatable -->
    <VCard>
      <VDataTableServer
        v-model:items-per-page="options.itemsPerPage"
        v-model:page="options.page"
        :headers="headers"
        :items="plantList"
        :items-length="totalPlants"
        class="text-no-wrap"
        @update:options="options = $event"
      >
        <!-- ID -->
        <template #item.id="{ item }">
          <VChip
            size="small"
            variant="tonal"
            color="primary"
            class="font-weight-bold cursor-pointer"
            @click="viewItem(item.raw)"
          >
            #{{ item.raw.id }}
          </VChip>
        </template>

        <!-- User -->
        <template #item.user="{ item }">
          <div class="d-flex align-center">
            <VAvatar
              size="34"
              :variant="!item.raw.user?.profile_photo ? 'tonal' : undefined"
              color="primary"
              class="me-3 cursor-pointer"
              @click="$router.push({ name: 'apps-user-view-id', params: { id: item.raw.user?.id } })"
            >
              <VImg v-if="item.raw.user?.profile_photo" :src="item.raw.user.profile_photo" />
              <span v-else class="text-xs">{{ avatarText(item.raw.user?.name || 'م') }}</span>
            </VAvatar>

            <div class="d-flex flex-column">
              <h6
                class="text-sm font-weight-medium mb-0 text-primary cursor-pointer d-flex align-center hover:underline"
                @click="$router.push({ name: 'apps-user-view-id', params: { id: item.raw.user?.id } })"
              >
                {{ item.raw.user?.name || 'مستخدم مجهول' }}
                <VIcon v-if="item.raw.user?.is_trusted" icon="mdi-check-decagram" color="info" size="16" class="ms-1" />
              </h6>
              <span class="text-xs text-medium-emphasis">{{ item.raw.user?.phone || 'لا يوجد هاتف' }}</span>
            </div>
          </div>
        </template>

        <!-- Plant & Image -->
        <template #item.plant="{ item }">
          <div class="d-flex align-center gap-3 py-2">
            <VAvatar
              size="42"
              variant="tonal"
              :color="!item.raw.image ? 'secondary' : undefined"
              class="rounded"
            >
              <VImg v-if="item.raw.image" :src="item.raw.image" cover />
              <VIcon v-else icon="mdi-image-off-outline" size="20" />
            </VAvatar>
            <div class="d-flex flex-column">
              <span class="text-body-2 font-weight-bold">
                {{ item.raw.name }}
              </span>
              <span class="text-caption text-medium-emphasis">{{ item.raw.type }} | {{ item.raw.age }}</span>
            </div>
          </div>
        </template>

        <!-- Location -->
        <template #item.location="{ item }">
          <div class="d-flex align-center">
            <VIcon icon="mdi-map-marker-outline" size="16" class="me-1 text-primary" />
            <span class="text-body-2 font-weight-medium">{{ item.raw.governorate?.name_ar || 'غير محدد' }}</span>
          </div>
        </template>

        <!-- Date -->
        <template #item.date="{ item }">
          <div class="d-flex flex-column gap-1">
            <span class="text-body-2 font-weight-medium text-high-emphasis d-flex align-center">
              <VIcon icon="mdi-calendar-outline" size="14" class="me-1 text-primary" />
              {{ item.raw.created_at?.split(' ')[0] || '—' }}
            </span>
            <span class="text-caption text-medium-emphasis d-flex align-center">
              <VIcon icon="mdi-clock-outline" size="14" class="me-1 text-secondary" />
              <span dir="ltr">{{ item.raw.created_at?.split(' ')[1] || '' }}</span>
            </span>
          </div>
        </template>

        <!-- Status -->
        <template #item.status="{ item }">
          <VMenu location="bottom">
            <template #activator="{ props }">
              <VChip
                v-bind="props"
                :color="resolveStatusVariant(item.raw.status).color"
                size="small"
                class="font-weight-medium cursor-pointer"
                append-icon="mdi-chevron-down"
              >
                {{ resolveStatusVariant(item.raw.status).text }}
              </VChip>
            </template>
            <VList density="compact">
              <VListItem v-if="item.raw.status !== 'pending'" @click="pendingPlant(item.raw)">
                <template #prepend>
                  <VIcon icon="mdi-clock-outline" class="me-2 text-warning" size="18" />
                </template>
                <VListItemTitle class="text-warning">تعليق</VListItemTitle>
              </VListItem>
              <VListItem v-if="item.raw.status !== 'approved'" @click="approvePlant(item.raw)">
                <template #prepend>
                  <VIcon icon="mdi-check-circle-outline" class="me-2 text-success" size="18" />
                </template>
                <VListItemTitle class="text-success">اعتماد</VListItemTitle>
              </VListItem>
              <VListItem v-if="item.raw.status !== 'rejected'" @click="openRejectDialog(item.raw)">
                <template #prepend>
                  <VIcon icon="mdi-close-circle-outline" class="me-2 text-error" size="18" />
                </template>
                <VListItemTitle class="text-error">رفض</VListItemTitle>
              </VListItem>
            </VList>
          </VMenu>
        </template>

        <!-- Notes -->
        <template #item.notes="{ item }">
          <div class="d-flex justify-center">
            <VTooltip v-if="item.raw.notes || item.raw.rejection_reason" location="top" max-width="300">
              <template #activator="{ props }">
                <VBtn icon="mdi-note-text-outline" variant="tonal" size="small" color="info" v-bind="props" />
              </template>
              <div class="d-flex flex-column gap-1 pa-1 text-start">
                <span v-if="item.raw.notes"><strong>ملاحظات:</strong> {{ item.raw.notes }}</span>
                <span v-if="item.raw.rejection_reason" class="text-error mt-1"><strong>سبب الرفض:</strong> {{ item.raw.rejection_reason }}</span>
              </div>
            </VTooltip>
            <span v-else class="text-medium-emphasis text-caption">—</span>
          </div>
        </template>

        <!-- Actions -->
        <template #item.actions="{ item }">
          <VBtn icon variant="text" size="small" color="medium-emphasis">
            <VIcon size="24" icon="mdi-dots-vertical" />

            <VMenu activator="parent" location="bottom end">
              <VList>
                <VListItem @click="viewItem(item.raw)">
                  <template #prepend>
                    <VIcon icon="mdi-eye-outline" class="me-2 text-primary" />
                  </template>
                  <VListItemTitle>مشاهدة وتعديل</VListItemTitle>
                </VListItem>

                <VListItem @click="deleteItem(item.raw)">
                  <template #prepend>
                    <VIcon icon="mdi-delete-outline" class="me-2 text-error" />
                  </template>
                  <VListItemTitle class="text-error">حذف</VListItemTitle>
                </VListItem>
              </VList>
            </VMenu>
          </VBtn>
        </template>

        <!-- Bottom Pagination Options -->
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
              عرض {{ totalPlants > 0 ? (options.page - 1) * options.itemsPerPage + 1 : 0 }} إلى {{ Math.min(options.page * options.itemsPerPage, totalPlants) }} من أصل {{ totalPlants }} نبتة
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
                :disabled="options.page >= Math.ceil(totalPlants / options.itemsPerPage)"
                @click="options.page++"
              />
            </div>
          </div>
        </template>
      </VDataTableServer>
    </VCard>

    <!-- 👉 View Dialog -->
    <VDialog v-model="editDialog" max-width="700px">
      <VCard v-if="editedItem">
        <VCardTitle class="d-flex align-center pa-5">
          <VIcon icon="mdi-tree-outline" color="primary" class="me-2" />
          <span class="text-h6">تفاصيل النبتة</span>
          <VSpacer />
          <VChip :color="resolveStatusVariant(editedItem.status).color" size="small" density="comfortable">
            <VIcon :icon="resolveStatusVariant(editedItem.status).icon || 'mdi-help-circle'" start size="14" />
            {{ resolveStatusVariant(editedItem.status).text }}
          </VChip>
        </VCardTitle>

        <VCardText class="pa-5 pt-0">
          <!-- Image -->
          <VImg v-if="editedItem.image" :src="editedItem.image" max-height="300" cover class="rounded-lg mb-4" />
          <div v-else class="rounded-lg d-flex align-center justify-center bg-grey-lighten-2 mb-4" style="height: 200px;">
            <VIcon icon="mdi-image-off-outline" size="48" color="grey" />
          </div>

          <!-- Name & Type -->
          <div class="d-flex flex-wrap gap-4 mb-4">
            <div>
              <p class="text-caption text-medium-emphasis mb-0">اسم النبتة</p>
              <p class="text-h6 font-weight-medium mb-0">{{ editedItem.name || '—' }}</p>
            </div>
            <VDivider vertical />
            <div>
              <p class="text-caption text-medium-emphasis mb-0">الصنف</p>
              <p class="text-body-1 font-weight-medium mb-0">{{ editedItem.type || '—' }}</p>
            </div>
            <VDivider vertical />
            <div>
              <p class="text-caption text-medium-emphasis mb-0">العمر</p>
              <p class="text-body-1 font-weight-medium mb-0">{{ editedItem.age || '—' }}</p>
            </div>
          </div>

          <VDivider class="mb-4" />

          <!-- Location -->
            <div class="d-flex align-center justify-space-between mb-4">
            <div class="d-flex align-center gap-2">
              <VIcon icon="mdi-map-marker" color="error" size="20" />
              <div>
                <p class="text-caption text-medium-emphasis mb-0">الموقع الجغرافي</p>
                <p class="text-body-2 font-weight-medium mb-0" dir="ltr">
                  {{ editedItem.latitude ? Number(editedItem.latitude).toFixed(4) : '' }}, 
                  {{ editedItem.longitude ? Number(editedItem.longitude).toFixed(4) : '' }}
                </p>
                <p class="text-caption text-medium-emphasis mb-0 mt-1">{{ editedItem.governorate?.name_ar || '—' }}</p>
              </div>
            </div>
            <div class="d-flex gap-2">
              <VBtn
                v-if="editedItem.latitude && editedItem.longitude"
                prepend-icon="mdi-map-marker-radius-outline"
                variant="tonal"
                size="small"
                color="primary"
                :href="`https://www.google.com/maps?q=${editedItem.latitude},${editedItem.longitude}`"
                target="_blank"
              >
                خرائط جوجل
              </VBtn>
              <VBtn
                v-if="editedItem.latitude && editedItem.longitude"
                prepend-icon="mdi-content-copy"
                variant="tonal"
                size="small"
                color="secondary"
                @click="copyCoordinates(editedItem.latitude, editedItem.longitude)"
              >
                نسخ
              </VBtn>
            </div>
          </div>

          <!-- Notes -->
          <div v-if="editedItem.notes" class="mb-4">
            <p class="text-caption text-medium-emphasis mb-0">ملاحظات</p>
            <p class="text-body-2 bg-grey-100 pa-3 rounded">{{ editedItem.notes }}</p>
          </div>

          <!-- Rejection reason -->
          <div v-if="editedItem.status === 'rejected' && editedItem.rejection_reason" class="mb-4">
            <VAlert type="error" variant="tonal" density="comfortable">
              سبب الرفض: {{ editedItem.rejection_reason }}
            </VAlert>
          </div>

          <VDivider class="mb-4" />

          <!-- User & Date -->
          <div class="d-flex justify-space-between flex-wrap gap-4">
            <div class="d-flex align-center">
              <VAvatar size="40" color="primary" variant="tonal" class="me-3 cursor-pointer" @click="$router.push({ name: 'apps-user-view-id', params: { id: editedItem.user?.id } }); close()">
                <VImg v-if="editedItem.user?.profile_photo" :src="editedItem.user.profile_photo" />
                <span v-else class="text-xs">{{ avatarText(editedItem.user?.name || 'م') }}</span>
              </VAvatar>
              <div>
                <p class="text-caption text-medium-emphasis mb-0">أضافها</p>
                <p class="text-body-1 font-weight-medium mb-0 text-primary cursor-pointer hover:underline" @click="$router.push({ name: 'apps-user-view-id', params: { id: editedItem.user?.id } }); close()">
                  {{ editedItem.user?.name || '—' }}
                </p>
              </div>
            </div>
            <div class="d-flex align-center">
              <VIcon icon="mdi-clock-outline" color="medium-emphasis" size="20" class="me-2" />
              <div>
                <p class="text-caption text-medium-emphasis mb-0">التاريخ</p>
                <p class="text-body-2 font-weight-medium mb-0">{{ editedItem.created_at || '—' }}</p>
              </div>
            </div>
          </div>

          <!-- Timeline -->
          <div v-if="editedItem.status_logs && editedItem.status_logs.length" class="mt-6">
            <h6 class="text-h6 mb-4 d-flex align-center">
              <VIcon icon="mdi-history" color="primary" class="me-2" />
              سجل الحالات
            </h6>
            <VTimeline density="compact" align="start" truncate-line="both">
              <VTimelineItem
                v-for="log in editedItem.status_logs"
                :key="log.id"
                :dot-color="resolveStatusVariant(log.new_status).color"
                size="small"
              >
                <div class="d-flex justify-space-between align-center mb-1">
                  <span class="text-body-2 font-weight-medium">
                    تحولت إلى {{ resolveStatusVariant(log.new_status).text }}
                  </span>
                  <span class="text-caption text-medium-emphasis">{{ log.created_at }}</span>
                </div>
                
                <div class="d-flex align-center gap-2 mt-1">
                  <VAvatar size="20" color="primary" variant="tonal">
                    <VImg v-if="log.admin?.profile_photo" :src="log.admin.profile_photo" />
                    <VIcon v-else icon="mdi-shield-account-outline" size="14" />
                  </VAvatar>
                  <span class="text-caption text-medium-emphasis">بواسطة: {{ log.admin?.name || 'مجهول' }}</span>
                </div>

                <div v-if="log.reason" class="mt-2">
                  <VAlert color="error" variant="tonal" density="compact" class="text-caption py-1">
                    السبب: {{ log.reason }}
                  </VAlert>
                </div>
              </VTimelineItem>
            </VTimeline>
          </div>
        </VCardText>

        <VCardActions class="pa-5 pt-0">
          <VBtn
            v-if="editedItem.status !== 'pending'"
            color="warning"
            variant="flat"
            prepend-icon="mdi-clock-outline"
            @click="pendingPlant(editedItem)"
          >
            تعليق
          </VBtn>
          <VBtn
            v-if="editedItem.status !== 'approved'"
            color="success"
            variant="elevated"
            prepend-icon="mdi-check"
            @click="approvePlant(editedItem)"
          >
            اعتماد
          </VBtn>
          <VBtn
            v-if="editedItem.status !== 'rejected'"
            color="error"
            variant="flat"
            prepend-icon="mdi-close-circle-outline"
            @click="openRejectDialog(editedItem)"
          >
            رفض
          </VBtn>
          <VSpacer />
          <VBtn variant="text" @click="close">
            إغلاق
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- 👉 Reject Dialog -->
    <VDialog v-model="rejectDialog" max-width="500px">
      <VCard>
        <VCardTitle>رفض النبتة</VCardTitle>
        <VCardText>
          <VTextarea v-model="rejectReason" label="سبب الرفض" rows="3" />
        </VCardText>
        <VCardActions>
          <VSpacer />
          <VBtn color="error" variant="outlined" @click="rejectDialog = false">إلغاء</VBtn>
          <VBtn color="success" variant="elevated" @click="confirmReject">تأكيد الرفض</VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- 👉 Delete Dialog -->
    <VDialog v-model="deleteDialog" max-width="500px">
      <VCard>
        <VCardTitle>هل أنت متأكد من حذف هذه النبتة؟</VCardTitle>
        <VCardActions>
          <VSpacer />
          <VBtn color="error" variant="outlined" @click="closeDelete">إلغاء</VBtn>
          <VBtn color="success" variant="elevated" @click="deleteItemConfirm">حذف</VBtn>
          <VSpacer />
        </VCardActions>
      </VCard>
    </VDialog>
  </section>
</template>

<style lang="scss">
.hover\:underline:hover {
  text-decoration: underline;
}
</style>

<route lang="yaml">
meta:
  action: read
  subject: plants
</route>
