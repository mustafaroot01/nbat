<script setup lang="ts">
import { useReportStore } from '@/stores/useReportStore'
import { usePlantStore } from '@/stores/usePlantStore'
import { useSettingStore } from '@/stores/useSettingStore'
import { VDataTableServer } from 'vuetify/labs/VDataTable'

interface Report {
  id: number
  plant: any
  reporter: any
  reason: string
  status: string
  created_at: string
}

const reportStore = useReportStore()
const plantStore = usePlantStore()
const settingStore = useSettingStore()

const editDialog = ref(false)
const resolveDialog = ref(false)
const rejectDialog = ref(false)
const rejectReason = ref('')

const defaultItem = ref<any>({})
const editedItem = ref<any>({})
const editedReport = ref<any>({})
const reportList = ref<Report[]>([])

// Table options
const totalReports = ref(0)
const options = ref({ page: 1, itemsPerPage: 10, sortBy: [], sortDesc: [] })

// Filters
const selectedStatus = ref('')
const selectedGovernorate = ref<number | null>(null)
const governorates = ref([])

const headers = [
  { title: '# رقم النبتة', key: 'plant_id', sortable: false },
  { title: 'المُبلغ', key: 'reporter', sortable: false },
  { title: 'النبتة المُبلغ عنها', key: 'plant', sortable: false },
  { title: 'السبب', key: 'reason', sortable: false },
  { title: 'التاريخ', key: 'created_at', sortable: false },
  { title: 'الحالة', key: 'status', sortable: false },
  { title: 'الإجراءات', key: 'actions', sortable: false, align: 'center' },
]

const statusItems = [
  { title: 'الكل', value: '' },
  { title: 'قيد المراجعة', value: 'pending' },
  { title: 'تم الحل', value: 'resolved' },
]

const fetchReports = () => {
  reportStore.fetchReports({
    status: selectedStatus.value,
    governorate_id: selectedGovernorate.value,
    page: options.value.page,
    itemsPerPage: options.value.itemsPerPage,
  }).then(response => {
    reportList.value = response.data.data
    totalReports.value = response.data.meta.total
  }).catch(error => {
    console.error(error)
  })
}

watchEffect(fetchReports)

onMounted(() => {
  settingStore.fetchGovernorates().then(response => {
    governorates.value = response.data.data
  })
})

// Methods
const viewPlant = (item: Report) => {
  editedItem.value = { ...item.plant }
  editedReport.value = { ...item }
  editDialog.value = true
}

const resolveItem = (item: Report) => {
  reportStore.resolveReport(item.id).then(() => {
    fetchReports()
  })
}

const close = () => {
  editDialog.value = false
  editedItem.value = { ...defaultItem.value }
}

const openRejectDialog = (item: any) => {
  editedItem.value = { ...item }
  rejectReason.value = ''
  editDialog.value = false // close view dialog
  rejectDialog.value = true
}

const confirmReject = () => {
  if (editedItem.value.id && rejectReason.value) {
    plantStore.rejectPlant(editedItem.value.id, rejectReason.value).then(() => {
      rejectDialog.value = false
      editedItem.value = { ...defaultItem.value }
      fetchReports()
    })
  }
}

const pendingPlant = (item: any) => {
  plantStore.pendingPlant(item.id).then(() => {
    editDialog.value = false
    editedItem.value = { ...defaultItem.value }
    fetchReports()
  })
}

const approvePlant = (item: any) => {
  plantStore.approvePlant(item.id).then(() => {
    editDialog.value = false
    editedItem.value = { ...defaultItem.value }
    fetchReports()
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
        <VCardTitle>تصفية البلاغات</VCardTitle>
      </VCardItem>

      <VCardText>
        <VRow>
          <VCol cols="12" sm="4">
            <VSelect
              v-model="selectedStatus"
              label="حالة البلاغ"
              :items="statusItems"
              clearable
              clear-icon="mdi-close"
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
        </VRow>
      </VCardText>
    </VCard>

    <!-- 👉 Datatable -->
    <VCard>
      <VDataTableServer
        v-model:items-per-page="options.itemsPerPage"
        v-model:page="options.page"
        :headers="headers"
        :items="reportList"
        :items-length="totalReports"
        class="text-no-wrap"
        @update:options="options = $event"
      >
        <!-- Plant ID -->
        <template #item.plant_id="{ item }">
          <VChip
            size="small"
            variant="tonal"
            color="primary"
            class="font-weight-bold cursor-pointer"
            @click="viewPlant(item.raw)"
          >
            #{{ item.raw.plant?.id }}
          </VChip>
        </template>

        <!-- Reporter -->
        <template #item.reporter="{ item }">
          <div class="d-flex align-center">
            <VAvatar
              size="34"
              :variant="!item.raw.reporter?.profile_photo ? 'tonal' : undefined"
              color="primary"
              class="me-3 cursor-pointer"
              @click="$router.push({ name: 'apps-user-view-id', params: { id: item.raw.reporter?.id } })"
            >
              <VImg v-if="item.raw.reporter?.profile_photo" :src="item.raw.reporter.profile_photo" />
              <span v-else class="text-xs">{{ avatarText(item.raw.reporter?.name || 'م') }}</span>
            </VAvatar>

            <div class="d-flex flex-column">
              <span class="text-body-1 font-weight-medium cursor-pointer text-primary hover:underline" @click="$router.push({ name: 'apps-user-view-id', params: { id: item.raw.reporter?.id } })">
                {{ item.raw.reporter?.name || '—' }}
              </span>
              <span class="text-caption text-medium-emphasis">مُبلغ</span>
            </div>
          </div>
        </template>

        <!-- Plant -->
        <template #item.plant="{ item }">
          <div class="d-flex align-center">
            <VAvatar
              size="40"
              variant="tonal"
              color="secondary"
              class="me-3 rounded"
            >
              <VImg v-if="item.raw.plant?.image" :src="item.raw.plant.image" cover />
              <VIcon v-else icon="mdi-image-off-outline" size="20" />
            </VAvatar>

            <div class="d-flex flex-column">
              <span class="text-body-2 font-weight-bold">
                {{ item.raw.plant?.name || '—' }}
              </span>
              <span class="text-caption text-medium-emphasis">{{ item.raw.plant?.type }} | {{ item.raw.plant?.age }}</span>
            </div>
          </div>
        </template>

        <!-- Reason -->
        <template #item.reason="{ item }">
          <div class="text-wrap" style="max-width: 200px;">
            {{ item.raw.reason }}
          </div>
        </template>

        <!-- Created At -->
        <template #item.created_at="{ item }">
          <span class="text-body-2">{{ item.raw.created_at }}</span>
        </template>

        <!-- Status -->
        <template #item.status="{ item }">
          <VChip
            :color="item.raw.status === 'resolved' ? 'success' : 'warning'"
            size="small"
            class="font-weight-medium"
          >
            {{ item.raw.status === 'resolved' ? 'تم الحل' : 'قيد المراجعة' }}
          </VChip>
        </template>

        <!-- Actions -->
        <template #item.actions="{ item }">
          <VBtn icon variant="text" size="small" color="medium-emphasis">
            <VIcon size="24" icon="mdi-dots-vertical" />

            <VMenu activator="parent" location="bottom end">
              <VList>
                <VListItem @click="viewPlant(item.raw)">
                  <template #prepend>
                    <VIcon icon="mdi-eye-outline" class="me-2 text-primary" />
                  </template>
                  <VListItemTitle>مشاهدة التفاصيل</VListItemTitle>
                </VListItem>

                <VListItem v-if="item.raw.status !== 'resolved'" @click="resolveItem(item.raw)">
                  <template #prepend>
                    <VIcon icon="mdi-check-all" class="me-2 text-success" />
                  </template>
                  <VListItemTitle class="text-success">إغلاق البلاغ وحله</VListItemTitle>
                </VListItem>
              </VList>
            </VMenu>
          </VBtn>
        </template>

      </VDataTableServer>
    </VCard>

    <!-- 👉 View Plant Details Dialog -->
    <VDialog
      v-model="editDialog"
      max-width="700px"
      scrollable
    >
      <VCard>
        <VCardTitle class="pa-5 d-flex align-center justify-space-between bg-error-lighten-5">
          <div class="d-flex align-center">
            <VIcon icon="mdi-alert-octagon-outline" color="error" class="me-2" />
            <span class="text-h6 text-error font-weight-bold">مراجعة البلاغ #{{ editedReport.id }}</span>
          </div>
          <IconBtn @click="close">
            <VIcon icon="mdi-close" />
          </IconBtn>
        </VCardTitle>

        <VDivider />

        <VCardText class="pa-5">
          <!-- Report Info (NEW DESIGN) -->
          <VAlert
            type="error"
            variant="tonal"
            density="comfortable"
            class="mb-6 font-weight-medium"
            border="start"
          >
            <div class="d-flex align-center justify-space-between mb-2">
              <span class="text-subtitle-2 font-weight-bold">المُبلغ: {{ editedReport.reporter?.name }}</span>
              <span class="text-caption">{{ editedReport.created_at }}</span>
            </div>
            <p class="mb-0 text-body-1">"{{ editedReport.reason }}"</p>
          </VAlert>

          <h6 class="text-h6 mb-4 d-flex align-center">
            <VIcon icon="mdi-tree-outline" color="primary" class="me-2" />
            تفاصيل النبتة
          </h6>

          <!-- Modern Grid Layout for Plant -->
          <VRow>
            <!-- Image Column -->
            <VCol cols="12" md="5">
              <div class="position-relative">
                <VImg
                  v-if="editedItem.image"
                  :src="editedItem.image"
                  height="220"
                  cover
                  class="rounded-lg shadow-sm mb-4"
                >
                  <!-- Status Chip -->
                  <VChip
                    :color="resolveStatusVariant(editedItem.status).color"
                    class="position-absolute font-weight-bold"
                    style="top: 12px; right: 12px; z-index: 1;"
                    elevated
                  >
                    <VIcon :icon="resolveStatusVariant(editedItem.status).icon" start size="16" />
                    {{ resolveStatusVariant(editedItem.status).text }}
                  </VChip>
                </VImg>
                <div
                  v-else
                  class="d-flex align-center justify-center bg-grey-100 rounded-lg border mb-4"
                  style="height: 220px;"
                >
                  <VIcon icon="mdi-image-off-outline" size="48" color="grey-400" />
                </div>
              </div>
            </VCol>

            <!-- Details Column -->
            <VCol cols="12" md="7">
              <div class="mb-4">
                <p class="text-caption text-medium-emphasis mb-0">اسم النبتة</p>
                <h5 class="text-h5 font-weight-bold mb-1">{{ editedItem.name || '—' }} <span class="text-primary text-subtitle-1">#{{ editedItem.id }}</span></h5>
              </div>

              <div class="d-flex flex-wrap gap-4 mb-4">
                <VChip size="small" variant="tonal" color="primary">{{ editedItem.type || '—' }}</VChip>
                <VChip size="small" variant="tonal" color="secondary">العمر: {{ editedItem.age || '—' }}</VChip>
                <VChip size="small" variant="tonal" color="info" v-if="editedItem.governorate">
                  <VIcon icon="mdi-map-marker-outline" start size="14" />
                  {{ editedItem.governorate.name_ar }}
                </VChip>
              </div>

              <div class="d-flex align-center mb-4 bg-grey-50 pa-3 rounded border">
                <VAvatar size="40" color="primary" variant="tonal" class="me-3 cursor-pointer" @click="$router.push({ name: 'apps-user-view-id', params: { id: editedItem.user?.id } }); close()">
                  <VImg v-if="editedItem.user?.profile_photo" :src="editedItem.user.profile_photo" />
                  <span v-else class="text-xs">{{ avatarText(editedItem.user?.name || 'م') }}</span>
                </VAvatar>
                <div>
                  <p class="text-caption text-medium-emphasis mb-0">أضيفت بواسطة</p>
                  <p class="text-body-2 font-weight-medium mb-0 text-primary cursor-pointer hover:underline" @click="$router.push({ name: 'apps-user-view-id', params: { id: editedItem.user?.id } }); close()">
                    {{ editedItem.user?.name || '—' }}
                  </p>
                </div>
              </div>

              <div class="d-flex align-center justify-space-between mb-2">
                <div class="d-flex align-center gap-2">
                  <VIcon icon="mdi-map-marker" color="error" size="20" />
                  <div>
                    <p class="text-caption text-medium-emphasis mb-0">الموقع الجغرافي</p>
                    <p class="text-body-2 font-weight-medium mb-0" dir="ltr">
                      {{ editedItem.latitude ? Number(editedItem.latitude).toFixed(4) : '' }}, 
                      {{ editedItem.longitude ? Number(editedItem.longitude).toFixed(4) : '' }}
                    </p>
                  </div>
                </div>
                <VBtn
                  size="small"
                  variant="tonal"
                  color="primary"
                  prepend-icon="mdi-content-copy"
                  @click="copyCoordinates(editedItem.latitude, editedItem.longitude)"
                >
                  نسخ
                </VBtn>
              </div>
            </VCol>
          </VRow>

          <!-- Notes -->
          <div v-if="editedItem.notes" class="mt-2 mb-4 p-3 bg-grey-50 rounded border">
            <p class="text-caption text-medium-emphasis mb-1 font-weight-medium">ملاحظات المُزارع:</p>
            <p class="text-body-2 mb-0">{{ editedItem.notes }}</p>
          </div>
          
          <VDivider class="my-5" />
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
          <VSpacer />
          <!-- Action Buttons Based on Status -->
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
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- 👉 Reject Dialog -->
    <VDialog v-model="rejectDialog" max-width="400px">
      <VCard>
        <VCardTitle>سبب الرفض</VCardTitle>
        <VCardText>
          <VTextarea
            v-model="rejectReason"
            label="يرجى كتابة سبب الرفض ليظهر للمستخدم"
            rows="3"
            outlined
          />
        </VCardText>
        <VCardActions>
          <VSpacer />
          <VBtn color="error" variant="text" @click="rejectDialog = false">إلغاء</VBtn>
          <VBtn color="success" variant="elevated" @click="confirmReject" :disabled="!rejectReason">تأكيد الرفض</VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </section>
</template>

<route lang="yaml">
meta:
  action: read
  subject: reports
</route>
