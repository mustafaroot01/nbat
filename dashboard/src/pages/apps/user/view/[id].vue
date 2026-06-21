<script setup lang="ts">
import UserBioPanel from '@/views/apps/user/view/UserBioPanel.vue'
import UserTabPlants from '@/views/apps/user/view/UserTabPlants.vue'
import { useAdminUserStore } from '@/stores/useAdminUserStore'

const route = useRoute()
const userStore = useAdminUserStore()

const userData = ref<any>(null)
const userTab = ref(null)
const isFetching = ref(true)

const tabs = [
  { icon: 'mdi-tree-outline', title: 'سجل الزراعة' },
]

const fetchUserData = () => {
  const userId = Number(route.params.id)
  if (userId) {
    userStore.fetchUser(userId)
      .then(res => {
        userData.value = res.data.data
      })
      .catch(e => {
        console.error('Failed to fetch user:', e)
      })
      .finally(() => {
        isFetching.value = false
      })
  }
}

onMounted(() => {
  fetchUserData()
})
</script>

<template>
  <div v-if="isFetching" class="d-flex justify-center align-center h-100 py-10">
    <VProgressCircular indeterminate color="primary" size="64" />
  </div>

  <VRow v-else-if="userData">
    <VCol cols="12">
      <UserBioPanel :user-data="userData" @user-updated="fetchUserData" />
    </VCol>

    <VCol cols="12" class="mt-6">
      <VTabs
        v-model="userTab"
        class="v-tabs-pill mb-6"
      >
        <VTab
          v-for="tab in tabs"
          :key="tab.icon"
        >
          <VIcon
            start
            :icon="tab.icon"
          />
          <span>{{ tab.title }}</span>
        </VTab>
      </VTabs>

      <VWindow
        v-model="userTab"
        class="disable-tab-transition"
        :touch="false"
      >
        <VWindowItem>
          <UserTabPlants :user-data="userData" />
        </VWindowItem>
      </VWindow>
    </VCol>
  </VRow>
  
  <div v-else class="text-center py-10">
    <VIcon icon="mdi-account-off-outline" size="64" color="medium-emphasis" class="mb-4 opacity-50" />
    <h2 class="text-h4 text-medium-emphasis">المستخدم غير موجود</h2>
  </div>
</template>

<route lang="yaml">
meta:
  action: read
  subject: Admin
</route>
