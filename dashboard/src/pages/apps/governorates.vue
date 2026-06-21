<script setup lang="ts">
import { VDataTable } from 'vuetify/labs/VDataTable'
import { useSettingStore } from '@/stores/useSettingStore'

interface Governorate {
  id: number
  name_ar: string
  name_en: string
  is_covered: boolean
  plants_count: number
}

const settingStore = useSettingStore()

const governorateList = ref<Governorate[]>([])
const currentPage = ref(1)
const itemsPerPage = ref(18)
const coverageMode = ref('all')
const saving = ref(false)
const snackbar = ref(false)
const snackbarMsg = ref('')

const headers = [
  { title: '#', key: 'id' },
  { title: 'المحافظة (عربي)', key: 'name_ar' },
  { title: 'المحافظة (إنجليزي)', key: 'name_en' },
  { title: 'عدد النباتات', key: 'plants_count' },
  { title: 'التغطية', key: 'is_covered' },
  { title: 'إجراءات', key: 'actions' },
]

const coverageModeItems = [
  { title: 'كل العراق — جميع المحافظات مفعلة', value: 'all' },
  { title: 'محافظات مخصصة — اختر المحافظات يدوياً', value: 'custom' },
]

const fetchGovernorates = () => {
  settingStore.fetchGovernorates().then(response => {
    governorateList.value = response.data.data
  }).catch(error => {
    console.error(error)
  })
}

onMounted(() => {
  fetchGovernorates()

  settingStore.fetchSettings().then(response => {
    coverageMode.value = response.data.data?.coverage_mode || 'all'
  })
})

const toggleCoverage = (item: Governorate) => {
  settingStore.toggleGovernorateCoverage(item.id).then(() => {
    fetchGovernorates()
    snackbarMsg.value = item.is_covered ? `تم تعطيل التغطية عن ${item.name_ar}` : `تم تفعيل التغطية لـ ${item.name_ar}`
    snackbar.value = true
  })
}

const saveCoverageMode = () => {
  saving.value = true

  const coveredIds = governorateList.value
    .filter(g => g.is_covered)
    .map(g => g.id)

  settingStore.updateCoverageMode({
    coverage_mode: coverageMode.value,
    governorate_ids: coverageMode.value === 'custom' ? coveredIds : undefined,
  }).then(() => {
    snackbarMsg.value = 'تم حفظ إعدادات التغطية بنجاح'
    snackbar.value = true
  }).catch(() => {
    snackbarMsg.value = 'حدث خطأ أثناء الحفظ'
    snackbar.value = true
  }).finally(() => {
    saving.value = false
  })
}
</script>

<template>
  <section>
    <!-- 👉 Coverage Mode -->
    <VCard
      title="نطاق التغطية"
      class="mb-6"
    >
      <VCardText>
        <VRow>
          <VCol
            cols="12"
            md="6"
          >
            <VSelect
              v-model="coverageMode"
              label="وضع التغطية"
              :items="coverageModeItems"
            />
          </VCol>
          <VCol
            cols="12"
            md="6"
            class="d-flex align-center"
          >
            <VBtn
              :loading="saving"
              @click="saveCoverageMode"
            >
              حفظ وضع التغطية
            </VBtn>
          </VCol>
        </VRow>

        <VAlert
          v-if="coverageMode === 'all'"
          type="info"
          variant="tonal"
          class="mt-4"
        >
          جميع المحافظات الـ 18 مفعلة — التطبيق يعمل في كل العراق.
        </VAlert>

        <VAlert
          v-if="coverageMode === 'custom'"
          type="warning"
          variant="tonal"
          class="mt-4"
        >
          المحافظات المعطلة سيظهر للمستخدم رسالة: <strong>"محافظتك خارج التغطية حالياً"</strong>
        </VAlert>
      </VCardText>
    </VCard>

    <!-- 👉 Governorates Datatable -->
    <VCard>
      <VDataTable
        :headers="headers"
        :items="governorateList"
        :items-per-page="itemsPerPage"
        :page="currentPage"
      >
        <!-- is_covered -->
        <template #item.is_covered="{ item }">
          <VChip
            :color="coverageMode === 'all' || item.raw.is_covered ? 'success' : 'error'"
            density="comfortable"
          >
            {{ coverageMode === 'all' || item.raw.is_covered ? 'مغطاة' : 'خارج التغطية' }}
          </VChip>
        </template>

        <!-- Actions -->
        <template #item.actions="{ item }">
          <div class="d-flex gap-1">
            <VBtn
              v-if="coverageMode === 'custom'"
              icon
              variant="text"
              size="small"
              :color="item.raw.is_covered ? 'error' : 'success'"
              @click="toggleCoverage(item.raw)"
            >
              <VIcon :icon="item.raw.is_covered ? 'mdi-close-circle-outline' : 'mdi-check-circle-outline'" />
              <VTooltip activator="parent">
                {{ item.raw.is_covered ? 'تعطيل التغطية' : 'تفعيل التغطية' }}
              </VTooltip>
            </VBtn>

            <span
              v-else
              class="text-sm text-medium-emphasis"
            >—</span>
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
                :length="Math.ceil(governorateList.length / itemsPerPage)"
                prev-icon="mdi-menu-left"
                next-icon="mdi-menu-right"
              />
            </div>
          </VCardText>
        </template>
      </VDataTable>
    </VCard>

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
  subject: governorates
</route>
