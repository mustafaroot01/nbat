<template>
  <VRow class="match-height">
    <VCol
      v-for="card in statCards"
      :key="card.title"
      cols="12"
      sm="6"
      lg="3"
    >
      <VCard :to="card.to" :class="{ 'cursor-pointer': card.to }" :ripple="!!card.to">
        <VCardText class="d-flex align-center justify-space-between">
          <div>
            <span class="text-sm text-medium-emphasis">{{ card.title }}</span>
            <div class="text-h4 font-weight-bold mt-1">
              {{ card.stats }}
            </div>
            <span class="text-sm text-medium-emphasis mt-1">{{ card.subtitle }}</span>
          </div>
          <VAvatar
            :color="card.color"
            variant="tonal"
            size="48"
            rounded
          >
            <VIcon
              :icon="card.icon"
              size="28"
            />
          </VAvatar>
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
</template>

<script setup lang="ts">
import { useSettingStore } from '@/stores/useSettingStore'
import { ref, onMounted } from 'vue'

const settingStore = useSettingStore()

const statCards = ref([
  {
    title: 'إجمالي الأشجار',
    stats: '0',
    icon: 'mdi-tree-outline',
    color: 'success',
    subtitle: 'اضغط لرؤية المحافظات',
    to: '/apps/governorate-stats', // Makes the card clickable and navigates
  },
  {
    title: 'أشجار هذا الشهر',
    stats: '0',
    icon: 'mdi-calendar-month-outline',
    color: 'primary',
    subtitle: '',
  },
  {
    title: 'طلبات النباتات المعلقة',
    stats: '0',
    icon: 'mdi-clock-outline',
    color: 'warning',
    subtitle: '',
  },
  {
    title: 'إجمالي المستخدمين',
    stats: '0',
    icon: 'mdi-account-group-outline',
    color: 'info',
    subtitle: '',
  },
  {
    title: 'إجمالي الإبلاغات',
    stats: '0',
    icon: 'mdi-alert-octagon-outline',
    color: 'error',
    subtitle: '',
  },
  {
    title: 'إبلاغات معلقة',
    stats: '0',
    icon: 'mdi-alert-circle-outline',
    color: 'warning',
    subtitle: '',
  },
  {
    title: 'إبلاغات محلولة',
    stats: '0',
    icon: 'mdi-check-decagram-outline',
    color: 'success',
    subtitle: '',
  },
])

onMounted(() => {
  settingStore.fetchStatistics().then(response => {
    const data = response.data.data
    
    // Update the ref array
    statCards.value[0].stats = data.total_plants.toString()
    statCards.value[1].stats = data.plants_this_month.toString()
    statCards.value[2].stats = data.pending_requests.toString()
    statCards.value[3].stats = data.total_users.toString()
    
    // New Reports Stats
    statCards.value[4].stats = data.total_reports.toString()
    statCards.value[5].stats = data.pending_reports.toString()
    statCards.value[6].stats = data.resolved_reports.toString()
  }).catch(() => {
    // keep default zeros on error
  })
})
</script>

<route lang="yaml">
meta:
  action: read
  subject: Auth
</route>
