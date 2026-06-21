<script setup lang="ts">
import { ref, computed } from 'vue'
import { VDataTable } from 'vuetify/labs/VDataTable'

interface Props {
  userData: any
}

const props = defineProps<Props>()

const plantsPerPage = ref(10)
const plantPage = ref(1)

const plantStatusFilter = ref<string>('all')
const plantGovFilter = ref<string>('all')

const fullImageDialog = ref(false)
const timelineDialog = ref(false)
const selectedImage = ref('')
const selectedPlantLogs = ref<any[]>([])
const snackbar = ref(false)
const snackbarMessage = ref('')

const copyCoordinates = (lat: number, lng: number) => {
  if (lat && lng) {
    navigator.clipboard.writeText(`${lat},${lng}`)
    snackbarMessage.value = 'تم نسخ الإحداثيات بنجاح!'
    snackbar.value = true
  }
}

const openImage = (url: string) => {
  if (url) {
    selectedImage.value = url
    fullImageDialog.value = true
  }
}

const openTimeline = (item: any) => {
  selectedPlantLogs.value = item.raw.status_logs || []
  timelineDialog.value = true
}

const resolveStatusVariant = (stat: string) => {
  if (stat === 'approved') return { color: 'success', text: 'معتمد' }
  if (stat === 'rejected') return { color: 'error', text: 'مرفوض' }
  return { color: 'warning', text: 'معلق' }
}

const userGovernorates = computed(() => {
  if (!props.userData?.plants) return []
  const govMap = new Map<string, number>()
  
  props.userData.plants.forEach((plant: any) => {
    const govName = plant.governorate?.name_ar || 'غير محدد'
    govMap.set(govName, (govMap.get(govName) || 0) + 1)
  })
  
  return Array.from(govMap.entries())
    .map(([name, count]) => ({ name, count }))
    .sort((a, b) => b.count - a.count)
})

const filteredPlants = computed(() => {
  if (!props.userData?.plants) return []
  return props.userData.plants.filter((plant: any) => {
    const statusMatch = plantStatusFilter.value === 'all' || plant.status === plantStatusFilter.value
    const govMatch = plantGovFilter.value === 'all' || (plant.governorate?.name_ar || 'غير محدد') === plantGovFilter.value
    return statusMatch && govMatch
  })
})

const headers = [
  { title: '#', key: 'index', sortable: false },
  { title: 'النبتة والصورة', key: 'plant', sortable: false },
  { title: 'التصنيف', key: 'type', sortable: false },
  { title: 'الموقع', key: 'location', sortable: false },
  { title: 'تاريخ الزراعة', key: 'date', sortable: false },
  { title: 'الحالة', key: 'status', sortable: false },
  { title: 'ملاحظات', key: 'notes', sortable: false, align: 'center' },
  { title: 'الإجراءات', key: 'actions', sortable: false, align: 'center' },
]
</script>

<template>
  <VCard title="سجل الزراعة">
    <VCardText>
      <!-- 👉 Governorates Summary (Clickable Filters) -->
      <div v-if="userGovernorates.length > 0" class="d-flex flex-wrap gap-2 mb-6">
        <VChip
          color="secondary"
          :variant="plantGovFilter === 'all' ? 'elevated' : 'tonal'"
          size="small"
          class="font-weight-medium cursor-pointer"
          @click="plantGovFilter = 'all'; plantPage = 1"
        >
          الكل
        </VChip>
        <VChip
          v-for="gov in userGovernorates"
          :key="gov.name"
          color="primary"
          :variant="plantGovFilter === gov.name ? 'elevated' : 'tonal'"
          size="small"
          class="font-weight-medium cursor-pointer"
          @click="plantGovFilter = gov.name; plantPage = 1"
        >
          <VIcon icon="mdi-map-marker-outline" start size="16" />
          {{ gov.name }}
          <VBadge
            color="primary"
            :content="gov.count"
            inline
            class="ms-1"
          />
        </VChip>
      </div>

      <VDivider v-if="userGovernorates.length > 0" class="mb-4" />

      <!-- 👉 Advanced Filters -->
      <div class="d-flex flex-wrap align-center justify-space-between gap-4 mb-6 bg-grey-100 pa-3 rounded">
        <h6 class="text-subtitle-1 mb-0 font-weight-medium d-flex align-center">
          <VIcon icon="mdi-filter-variant" class="me-2" />
          تصفية السجل ({{ filteredPlants.length }} نبتة)
        </h6>
        
        <div class="d-flex flex-wrap gap-4">
          <VSelect
            v-model="plantGovFilter"
            :items="[{ title: 'كل المحافظات', value: 'all' }, ...userGovernorates.map(g => ({ title: `${g.name} (${g.count})`, value: g.name }))]"
            label="المحافظة"
            density="compact"
            hide-details
            bg-color="surface"
            style="min-width: 180px;"
            @update:model-value="plantPage = 1"
          />

          <VSelect
            v-model="plantStatusFilter"
            :items="[
              { title: 'كل الحالات', value: 'all' },
              { title: 'معتمد', value: 'approved' },
              { title: 'معلق', value: 'pending' },
              { title: 'مرفوض', value: 'rejected' }
            ]"
            label="حالة النبتة"
            density="compact"
            hide-details
            bg-color="surface"
            style="min-width: 150px;"
            @update:model-value="plantPage = 1"
          />
        </div>
      </div>
    </VCardText>

    <VDivider />

    <VDataTable
      v-model:items-per-page="plantsPerPage"
      v-model:page="plantPage"
      :headers="headers"
      :items="filteredPlants"
      class="text-no-wrap"
    >
      <!-- Index -->
      <template #item.index="{ index }">
        <span class="text-body-2 font-weight-medium text-high-emphasis">
          {{ (plantPage - 1) * plantsPerPage + index + 1 }}
        </span>
      </template>

      <!-- Plant & Image -->
      <template #item.plant="{ item }">
        <div class="d-flex align-center gap-3 py-2">
          <VAvatar
            size="42"
            variant="tonal"
            :color="!item.raw.image ? 'secondary' : undefined"
            class="cursor-pointer rounded"
            @click="openImage(item.raw.image)"
          >
            <VImg v-if="item.raw.image" :src="item.raw.image" cover />
            <VIcon v-else icon="mdi-image-off-outline" size="20" />
          </VAvatar>
          <div class="d-flex flex-column">
            <h6 class="text-body-1 font-weight-medium text-high-emphasis mb-0">{{ item.raw.name }}</h6>
            <span
              v-if="item.raw.image"
              class="text-caption text-primary cursor-pointer d-flex align-center mt-1 font-weight-medium"
              @click="openImage(item.raw.image)"
            >
              <VIcon icon="mdi-magnify-plus-outline" size="14" class="me-1" />
              تكبير الصورة
            </span>
          </div>
        </div>
      </template>

      <!-- Type & Age -->
      <template #item.type="{ item }">
        <div class="d-flex flex-column gap-1">
          <span class="text-body-2 d-flex align-center font-weight-medium text-high-emphasis">
            <VIcon icon="mdi-leaf" size="14" class="me-1 text-success" />
            {{ item.raw.type || 'غير محدد' }}
          </span>
          <span class="text-caption text-medium-emphasis d-flex align-center">
            <VIcon icon="mdi-sprout-outline" size="14" class="me-1 text-info" />
            العمر: {{ item.raw.age || 'غير محدد' }}
          </span>
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
            {{ item.raw.created_at?.split(' ')[0] }}
          </span>
          <span class="text-caption text-medium-emphasis d-flex align-center">
            <VIcon icon="mdi-clock-outline" size="14" class="me-1 text-secondary" />
            <span dir="ltr">{{ item.raw.created_at?.split(' ')[1] || '' }}</span>
          </span>
        </div>
      </template>

      <!-- Status -->
      <template #item.status="{ item }">
        <VChip
          :color="resolveStatusVariant(item.raw.status).color"
          size="small"
          class="font-weight-medium"
        >
          {{ resolveStatusVariant(item.raw.status).text }}
        </VChip>
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
        <div class="d-flex gap-2 justify-center">
          <VTooltip location="top">
            <template #activator="{ props }">
              <IconBtn v-bind="props" color="primary" @click="openTimeline(item)">
                <VIcon icon="mdi-history" />
              </IconBtn>
            </template>
            <span>سجل الحالات</span>
          </VTooltip>

          <VTooltip location="top">
            <template #activator="{ props }">
              <VBtn
                v-if="item.raw.latitude && item.raw.longitude"
                icon="mdi-map-marker-radius-outline"
                variant="tonal"
                size="small"
                color="primary"
                :href="`https://www.google.com/maps?q=${item.raw.latitude},${item.raw.longitude}`"
                target="_blank"
                v-bind="props"
              />
            </template>
            <span>فتح في خرائط جوجل</span>
          </VTooltip>

          <VTooltip location="top">
            <template #activator="{ props }">
              <VBtn
                v-if="item.raw.latitude && item.raw.longitude"
                icon="mdi-content-copy"
                variant="tonal"
                size="small"
                color="secondary"
                @click="copyCoordinates(item.raw.latitude, item.raw.longitude)"
                v-bind="props"
              />
            </template>
            <span>نسخ الإحداثيات</span>
          </VTooltip>
        </div>
      </template>
      
      <!-- Bottom Pagination Options -->
      <template #bottom>
        <VDivider />
        <div class="d-flex justify-end gap-x-6 pa-2 flex-wrap">
          <div class="d-flex align-center gap-x-2 text-sm">
            صفوف لكل صفحة:
            <VSelect
              v-model="plantsPerPage"
              class="per-page-select text-high-emphasis"
              variant="plain"
              density="compact"
              :items="[10, 20, 50, 100]"
              @update:model-value="plantPage = 1"
            />
          </div>

          <span class="d-flex align-center text-sm me-2 text-high-emphasis">
            عرض {{ filteredPlants.length > 0 ? (plantPage - 1) * plantsPerPage + 1 : 0 }} إلى {{ Math.min(plantPage * plantsPerPage, filteredPlants.length) }} من أصل {{ filteredPlants.length }} نبتة
          </span>

          <div class="d-flex gap-x-2 align-center me-2">
            <VBtn
              icon="mdi-chevron-left"
              class="flip-in-rtl"
              variant="text"
              density="comfortable"
              color="default"
              :disabled="plantPage <= 1"
              @click="plantPage--"
            />

            <VBtn
              icon="mdi-chevron-right"
              class="flip-in-rtl"
              variant="text"
              density="comfortable"
              color="default"
              :disabled="plantPage >= Math.ceil(filteredPlants.length / plantsPerPage)"
              @click="plantPage++"
            />
          </div>
        </div>
      </template>
    </VDataTable>
  </VCard>

  <!-- 👉 Full Image Lightbox -->
  <VDialog
    v-model="fullImageDialog"
    max-width="800px"
  >
    <VCard class="bg-transparent shadow-none" elevation="0">
      <div class="d-flex justify-end mb-2">
        <IconBtn color="white" class="bg-black" style="opacity: 0.7;" @click="fullImageDialog = false">
          <VIcon icon="mdi-close" />
        </IconBtn>
      </div>
      <VImg :src="selectedImage" class="rounded" max-height="80vh" contain />
    </VCard>
  </VDialog>

  <!-- 👉 Status Timeline Dialog -->
  <VDialog v-model="timelineDialog" max-width="500px">
    <VCard>
      <VCardTitle class="pa-4 d-flex align-center justify-space-between">
        <div class="d-flex align-center">
          <VIcon icon="mdi-history" color="primary" class="me-2" />
          <span class="text-h6">سجل الحالات</span>
        </div>
        <IconBtn @click="timelineDialog = false">
          <VIcon icon="mdi-close" />
        </IconBtn>
      </VCardTitle>
      
      <VDivider />

      <VCardText class="pa-5">
        <div v-if="!selectedPlantLogs || selectedPlantLogs.length === 0" class="text-center text-medium-emphasis py-8">
          <VIcon icon="mdi-information-outline" size="48" class="mb-2 opacity-50" />
          <p>لا توجد حركات سابقة مسجلة لهذه النبتة.</p>
        </div>

        <VTimeline v-else density="compact" align="start" truncate-line="both">
          <VTimelineItem
            v-for="log in selectedPlantLogs"
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
      </VCardText>
    </VCard>
  </VDialog>

  <!-- 👉 Snackbar for copy feedback -->
  <VSnackbar
    v-model="snackbar"
    color="success"
    location="top"
    :timeout="3000"
  >
    <div class="d-flex align-center gap-2">
      <VIcon icon="mdi-check-circle-outline" />
      {{ snackbarMessage }}
    </div>
  </VSnackbar>
</template>
