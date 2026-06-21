<script setup lang="ts">
import VueApexCharts from 'vue3-apexcharts'
import { useTheme } from 'vuetify'
import { useSettingStore } from '@/stores/useSettingStore'
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'

const vuetifyTheme = useTheme()
const settingStore = useSettingStore()
const router = useRouter()

const governorates = ref<any[]>([])
const loading = ref(true)
const search = ref('')

onMounted(() => {
  settingStore.fetchGovernorateStats().then(response => {
    governorates.value = response.data.data
  }).catch(error => {
    console.error(error)
  }).finally(() => {
    loading.value = false
  })
})

const totalTrees = computed(() => {
  return governorates.value.reduce((acc, curr) => acc + curr.plants_count, 0)
})

const chartOptions = computed(() => {
  const currentTheme = vuetifyTheme.current.value.colors

  return {
    chart: {
      type: 'donut',
      fontFamily: 'Tajawal, sans-serif', // assuming you use an Arabic font
    },
    labels: governorates.value.map(g => g.name_ar),
    colors: [
      currentTheme.success,
      currentTheme.primary,
      currentTheme.warning,
      currentTheme.info,
      currentTheme.error,
      currentTheme.secondary,
      '#FF9F43',
      '#826AF9',
      '#28C76F',
      '#00CFE8',
      '#EA5455',
    ],
    stroke: { width: 0 },
    dataLabels: {
      enabled: false,
    },
    legend: {
      position: 'bottom',
      markers: { offsetX: -3 },
      itemMargin: {
        vertical: 3,
        horizontal: 10,
      },
    },
    plotOptions: {
      pie: {
        donut: {
          labels: {
            show: true,
            name: {
              fontSize: '1.2rem',
            },
            value: {
              fontSize: '1.2rem',
              color: currentTheme['on-surface'],
            },
            total: {
              show: true,
              fontSize: '1.2rem',
              label: 'الإجمالي',
              formatter() {
                return totalTrees.value.toString()
              },
              color: currentTheme['on-surface'],
            },
          },
        },
      },
    },
  }
})

const chartSeries = computed(() => {
  return governorates.value.map(g => g.plants_count)
})

const filteredGovernorates = computed(() => {
  if (!search.value) return governorates.value
  const query = search.value.toLowerCase()
  return governorates.value.filter(g => g.name_ar.toLowerCase().includes(query))
})

const navigateToPlants = (id: number) => {
  router.push({ path: '/apps/plants', query: { governorate_id: id } })
}
</script>

<template>
  <section>
    <VRow>
      <VCol cols="12" md="4">
        <VCard class="mb-4 text-center bg-primary text-white position-relative overflow-hidden elevation-6">
          <VIcon
            icon="mdi-tree"
            class="position-absolute"
            style="font-size: 150px; right: -20px; bottom: -20px; opacity: 0.1; transform: rotate(-15deg);"
          />
          <VCardItem class="pb-2 pt-6">
            <VCardTitle class="text-h5 font-weight-bold text-white">
              إجمالي الزراعة الكلي
            </VCardTitle>
          </VCardItem>
          <VCardText class="pb-6">
            <div class="text-h2 font-weight-bold mb-2 text-white">
              {{ totalTrees }}
            </div>
            <div class="text-h6 font-weight-medium" style="opacity: 0.9;">
              شجرة موثقة في جميع المحافظات
            </div>
          </VCardText>
        </VCard>

        <VCard v-if="!loading && governorates.length > 0" class="text-center">
          <VCardItem class="pb-0">
            <VCardTitle class="text-h6">
              توزيع الأشجار
            </VCardTitle>
          </VCardItem>
          <VCardText class="pt-4">
            <VueApexCharts
              type="donut"
              height="350"
              :options="chartOptions"
              :series="chartSeries"
            />
          </VCardText>
        </VCard>
      </VCol>

      <VCol cols="12" md="8">
        <VCard title="إحصائيات الزراعة حسب المحافظة">
          <VCardText class="d-flex align-center flex-wrap gap-4">
            <VSpacer />
            <div style="width: 250px;">
              <VTextField
                v-model="search"
                density="compact"
                placeholder="ابحث عن محافظة..."
                append-inner-icon="mdi-magnify"
                hide-details
              />
            </div>
          </VCardText>

          <VCardText class="pt-0">
            <div v-if="loading" class="d-flex justify-center pa-8">
              <VProgressCircular indeterminate color="primary" />
            </div>
            
            <VRow v-else>
              <VCol
                v-for="(gov, index) in filteredGovernorates"
                :key="gov.id"
                cols="12"
                sm="6"
                lg="4"
              >
                <VCard
                  class="cursor-pointer transition-swing hover:elevation-8 h-100 position-relative overflow-hidden gov-card"
                  @click="navigateToPlants(gov.id)"
                  v-ripple
                  variant="elevated"
                >
                  <!-- Background Watermark Icon -->
                  <VIcon
                    icon="mdi-map-marker-radius-outline"
                    class="position-absolute"
                    style="font-size: 100px; left: -15px; bottom: -15px; opacity: 0.03; z-index: 0;"
                  />
                  
                  <VCardItem class="pb-2 pt-4 relative-z-1">
                    <template #prepend>
                      <VAvatar 
                        :color="index < 3 ? 'success' : 'primary'" 
                        variant="tonal" 
                        rounded="lg" 
                        size="42"
                        class="elevation-1"
                      >
                        <span class="text-h6 font-weight-bold">{{ index + 1 }}</span>
                      </VAvatar>
                    </template>
                    <VCardTitle class="text-h6 font-weight-bold text-high-emphasis">
                      {{ gov.name_ar }}
                    </VCardTitle>
                  </VCardItem>
                  
                  <VCardText class="d-flex align-center justify-space-between mt-auto pb-4 relative-z-1">
                    <div class="d-flex align-center gap-2 bg-success-lighten-4 pa-2 rounded-lg" style="background-color: rgba(var(--v-theme-success), 0.1);">
                      <VIcon icon="mdi-tree" color="success" size="24" />
                      <span class="text-h5 font-weight-bold text-success">{{ gov.plants_count }}</span>
                    </div>
                    <VBtn
                      icon="mdi-arrow-left"
                      variant="tonal"
                      color="secondary"
                      size="small"
                      class="flip-in-rtl"
                    />
                  </VCardText>
                </VCard>
              </VCol>

              <VCol v-if="filteredGovernorates.length === 0" cols="12">
                <VAlert type="info" variant="tonal" class="text-center">
                  لا توجد محافظات مطابقة للبحث
                </VAlert>
              </VCol>
            </VRow>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>
  </section>
</template>

<style lang="scss" scoped>
.relative-z-1 {
  position: relative;
  z-index: 1;
}

.gov-card {
  border-top: 4px solid transparent;
  transition: all 0.3s ease;
  
  &:hover {
    border-top-color: rgb(var(--v-theme-success));
    transform: translateY(-4px);
  }
}
</style>

<route lang="yaml">
meta:
  action: read
  subject: plants
</route>
