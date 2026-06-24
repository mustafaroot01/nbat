<script setup lang="ts">
import { usePlantStore } from '@/stores/usePlantStore'
import { useSettingStore } from '@/stores/useSettingStore'
import L from 'leaflet'
import 'leaflet/dist/leaflet.css'

const plantStore = usePlantStore()
const settingStore = useSettingStore()

interface PlantMarker {
  id: number
  name: string
  type: string
  age: string
  latitude: number
  longitude: number
  status: string
  user: any
  governorate: any
}

const mapContainer = ref<HTMLElement | null>(null)
const plants = ref<PlantMarker[]>([])
const governorates = ref<{ id: number; name_ar: string }[]>([])
const selectedGovernorate = ref<number | null>(null)
const selectedStatus = ref('')
const loading = ref(true)

const stats = computed(() => ({
  total: plants.value.length,
  approved: plants.value.filter(p => p.status === 'approved').length,
  pending: plants.value.filter(p => p.status === 'pending').length,
  rejected: plants.value.filter(p => p.status === 'rejected').length,
}))

let map: L.Map | null = null
let markersLayer: L.LayerGroup | null = null

const statusItems = [
  { title: 'الكل', value: '' },
  { title: 'معتمد', value: 'approved' },
  { title: 'معلق', value: 'pending' },
  { title: 'مرفوض', value: 'rejected' },
]

const resolveColor = (status: string) => {
  if (status === 'approved') return '#28C76F'
  if (status === 'pending') return '#FF9F43'
  if (status === 'rejected') return '#EA5455'
  return '#7367F0'
}

const resolveText = (status: string) => {
  if (status === 'approved') return 'معتمد'
  if (status === 'pending') return 'معلق'
  if (status === 'rejected') return 'مرفوض'
  return status
}

const createIcon = (status: string) => {
  const color = resolveColor(status)
  return L.divIcon({
    className: 'custom-marker',
    html: `
      <div style="position:relative;display:flex;align-items:center;justify-content:center;">
        <svg viewBox="0 0 24 24" width="38" height="38" style="filter:drop-shadow(0 3px 6px rgba(0,0,0,.4))">
          <path fill="${color}" stroke="white" stroke-width="0.8" d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"/>
        </svg>
        <svg viewBox="0 0 24 24" width="16" height="16" style="position:absolute;top:3px;">
          <path fill="white" d="M17,8C8,10 5.9,16.17 3.82,21.34L5.71,22L6.66,19.7C7.14,19.87 7.64,20 8,20C19,20 22,3 22,3C21,5 14,5.25 9,6.25C4,7.25 7,14 7,14C10,9 12,8 17,8Z"/>
        </svg>
      </div>`,
    iconSize: [38, 38],
    iconAnchor: [19, 38],
    popupAnchor: [0, -40],
  })
}

const initMap = () => {
  if (!mapContainer.value) return

  map = L.map(mapContainer.value, {
    center: [33.2232, 43.6793],
    zoom: 6,
    minZoom: 5,
    maxZoom: 18,
    zoomControl: false,
  })

  L.control.zoom({ position: 'bottomright' }).addTo(map)

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://openstreetmap.org">OpenStreetMap</a> contributors',
    maxZoom: 19,
  }).addTo(map)

  markersLayer = L.layerGroup().addTo(map)
}

const renderMarkers = () => {
  if (!markersLayer) return
  markersLayer.clearLayers()

  plants.value.forEach(plant => {
    const lat = Number(plant.latitude)
    const lng = Number(plant.longitude)
    if (!lat || !lng) return

    const color = resolveColor(plant.status)
    const statusText = resolveText(plant.status)
    const userName = plant.user?.name || '—'
    const govName = plant.governorate?.name_ar || '—'

    const marker = L.marker([lat, lng], { icon: createIcon(plant.status) })

    marker.bindPopup(`
      <div dir="rtl" style="text-align:right;min-width:210px;font-family:sans-serif;">
        <div style="background:${color};color:white;padding:8px 12px;margin:-14px -20px 10px;border-radius:4px 4px 0 0;">
          <strong style="font-size:14px;">🌿 ${plant.name}</strong>
        </div>
        <table style="width:100%;font-size:12px;border-collapse:collapse;">
          <tr><td style="padding:3px 0;color:#888;">الصنف</td><td style="font-weight:600;">${plant.type || '—'}</td></tr>
          <tr><td style="padding:3px 0;color:#888;">العمر</td><td style="font-weight:600;">${plant.age || '—'}</td></tr>
          <tr><td style="padding:3px 0;color:#888;">المزارع</td><td style="font-weight:600;">${userName}</td></tr>
          <tr><td style="padding:3px 0;color:#888;">المحافظة</td><td style="font-weight:600;">${govName}</td></tr>
          <tr><td style="padding:3px 0;color:#888;">الحالة</td>
            <td><span style="background:${color};color:white;padding:2px 8px;border-radius:999px;font-size:11px;font-weight:700;">${statusText}</span></td>
          </tr>
        </table>
        <a href="/dashboard/apps/plants?plant_id=${plant.id}"
           style="display:block;margin-top:10px;text-align:center;background:#7367F0;color:white;padding:7px;border-radius:8px;text-decoration:none;font-size:12px;font-weight:700;">
          🔍 عرض تفاصيل النبتة
        </a>
      </div>
    `, { maxWidth: 250 })

    markersLayer!.addLayer(marker)
  })
}

const fetchPlants = () => {
  loading.value = true

  const params: Record<string, any> = { itemsPerPage: 1000 }
  if (selectedGovernorate.value) params.governorate_id = selectedGovernorate.value
  if (selectedStatus.value) params.status = selectedStatus.value

  plantStore.fetchAllPlantsForMap(params).then(response => {
    plants.value = response.data.data
    renderMarkers()

    if (map && plants.value.length > 0) {
      const valid = plants.value.filter(p => p.latitude && p.longitude)
      if (valid.length > 0) {
        const bounds = L.latLngBounds(
          valid.map(p => [Number(p.latitude), Number(p.longitude)] as [number, number]),
        )
        map.fitBounds(bounds, { padding: [40, 40], maxZoom: 12 })
      }
    }
  }).catch(() => {
    plants.value = []
  }).finally(() => {
    loading.value = false
  })
}

const fetchGovernorates = () => {
  settingStore.fetchGovernorates().then(r => {
    governorates.value = r.data.data
  }).catch(() => {
    governorates.value = []
  })
}

const fitToMarkers = () => {
  if (!map || plants.value.length === 0) return
  const valid = plants.value.filter(p => p.latitude && p.longitude)
  if (!valid.length) return
  const bounds = L.latLngBounds(valid.map(p => [Number(p.latitude), Number(p.longitude)] as [number, number]))
  map.fitBounds(bounds, { padding: [40, 40] })
}

watch([selectedGovernorate, selectedStatus], fetchPlants)

onMounted(() => {
  initMap()
  fetchPlants()
  fetchGovernorates()
})

onUnmounted(() => {
  if (map) { map.remove(); map = null }
})
</script>

<template>
  <section>
    <!-- 👉 Stats Cards -->
    <VRow class="mb-4 match-height">
      <VCol cols="6" sm="3">
        <VCard>
          <VCardText class="d-flex align-center gap-3 pa-4">
            <VAvatar color="primary" variant="tonal" rounded size="44">
              <VIcon icon="mdi-tree" size="22" />
            </VAvatar>
            <div>
              <div class="text-h5 font-weight-bold">{{ stats.total }}</div>
              <div class="text-caption text-medium-emphasis">إجمالي النباتات</div>
            </div>
          </VCardText>
        </VCard>
      </VCol>
      <VCol cols="6" sm="3">
        <VCard>
          <VCardText class="d-flex align-center gap-3 pa-4">
            <VAvatar color="success" variant="tonal" rounded size="44">
              <VIcon icon="mdi-check-circle-outline" size="22" />
            </VAvatar>
            <div>
              <div class="text-h5 font-weight-bold text-success">{{ stats.approved }}</div>
              <div class="text-caption text-medium-emphasis">معتمدة على الخريطة</div>
            </div>
          </VCardText>
        </VCard>
      </VCol>
      <VCol cols="6" sm="3">
        <VCard>
          <VCardText class="d-flex align-center gap-3 pa-4">
            <VAvatar color="warning" variant="tonal" rounded size="44">
              <VIcon icon="mdi-clock-outline" size="22" />
            </VAvatar>
            <div>
              <div class="text-h5 font-weight-bold text-warning">{{ stats.pending }}</div>
              <div class="text-caption text-medium-emphasis">معلقة</div>
            </div>
          </VCardText>
        </VCard>
      </VCol>
      <VCol cols="6" sm="3">
        <VCard>
          <VCardText class="d-flex align-center gap-3 pa-4">
            <VAvatar color="error" variant="tonal" rounded size="44">
              <VIcon icon="mdi-close-circle-outline" size="22" />
            </VAvatar>
            <div>
              <div class="text-h5 font-weight-bold text-error">{{ stats.rejected }}</div>
              <div class="text-caption text-medium-emphasis">مرفوضة</div>
            </div>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <!-- 👉 Map Card -->
    <VCard>
      <VCardItem class="pb-2">
        <VCardTitle class="d-flex align-center gap-2">
          <VIcon icon="mdi-map-marker-multiple-outline" color="primary" />
          خريطة النباتات
        </VCardTitle>
        <template #append>
          <VBtn
            icon="mdi-crosshairs-gps"
            variant="tonal"
            size="small"
            title="تمركز على النباتات"
            @click="fitToMarkers"
          />
        </template>
      </VCardItem>

      <VCardText class="pb-2">
        <VRow>
          <VCol cols="12" sm="4">
            <VSelect
              v-model="selectedStatus"
              label="حالة النبتة"
              :items="statusItems"
              density="compact"
              clearable
              clear-icon="mdi-close"
            />
          </VCol>
          <VCol cols="12" sm="4">
            <VSelect
              v-model="selectedGovernorate"
              label="تصفية بالمحافظة"
              :items="governorates"
              item-title="name_ar"
              item-value="id"
              density="compact"
              clearable
              clear-icon="mdi-close"
            />
          </VCol>
          <VCol cols="12" sm="4" class="d-flex align-center gap-4 flex-wrap">
            <div v-for="s in statusItems.slice(1)" :key="s.value" class="d-flex align-center gap-1">
              <div
                :style="`width:12px;height:12px;border-radius:50%;background:${resolveColor(s.value)}`"
              />
              <span class="text-caption">{{ s.title }}</span>
            </div>
          </VCol>
        </VRow>
      </VCardText>

      <VDivider />

      <!-- Map -->
      <div style="position:relative;">
        <div
          ref="mapContainer"
          style="height:580px;width:100%;z-index:0;"
        />

        <VOverlay
          v-model="loading"
          contained
          persistent
          class="align-center justify-center"
        >
          <VCard class="pa-6 text-center elevation-10">
            <VProgressCircular indeterminate color="primary" size="50" class="mb-3" />
            <div class="text-body-2 text-medium-emphasis">جاري تحميل النباتات على الخريطة...</div>
          </VCard>
        </VOverlay>
      </div>
    </VCard>
  </section>
</template>

<style>
.custom-marker {
  background: transparent !important;
  border: none !important;
}
.leaflet-popup-content-wrapper {
  border-radius: 12px !important;
  padding: 0 !important;
  overflow: hidden;
  box-shadow: 0 8px 24px rgba(0,0,0,0.15) !important;
}
.leaflet-popup-content {
  margin: 14px 20px 14px !important;
}
</style>

<route lang="yaml">
meta:
  action: read
  subject: plants
</route>
