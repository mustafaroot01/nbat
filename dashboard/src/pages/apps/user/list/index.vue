<script setup lang="ts">
import { VDataTableServer } from 'vuetify/labs/VDataTable'
import type { UserProperties } from '@/@fake-db/types'
import { paginationMeta } from '@/@fake-db/utils'
import AddNewUserDrawer from '@/views/apps/user/list/AddNewUserDrawer.vue'
import { useUserListStore } from '@/views/apps/user/useUserListStore'
import type { Options } from '@core/types'
import { avatarText } from '@core/utils/formatters'

// 👉 Store
const userListStore = useUserListStore()
const searchQuery = ref('')
const selectedRole = ref()
const selectedPlan = ref()
const selectedStatus = ref()
const totalPage = ref(1)
const totalUsers = ref(0)
const users = ref<UserProperties[]>([])

const options = ref<Options>({
  page: 1,
  itemsPerPage: 10,
  sortBy: [],
  groupBy: [],
  search: undefined,
})

// Headers
const headers = [
  { title: 'المستخدم', key: 'name' },
  { title: 'رقم الهاتف / الايميل', key: 'contact' },
  { title: 'المحافظة', key: 'governorate' },
  { title: 'عدد النبتات', key: 'plants_count' },
  { title: 'الحالة', key: 'is_active' },
  { title: 'موثق؟', key: 'is_trusted' },
  { title: 'الإجراءات', key: 'actions', sortable: false },
]

// 👉 Fetching users
const fetchUsers = () => {
  userListStore.fetchUsers({
    q: searchQuery.value,
    status: selectedStatus.value,
    plan: selectedPlan.value,
    role: selectedRole.value,
    options: options.value,
  }).then(response => {
    users.value = response.data.users
    totalPage.value = response.data.totalPage
    totalUsers.value = response.data.totalUsers
    options.value.page = response.data.page
  }).catch(error => {
    console.error(error)
  })
}

watchEffect(fetchUsers)

// 👉 search filters
const roles = [
  { title: 'Admin', value: 'admin' },
  { title: 'Author', value: 'author' },
  { title: 'Editor', value: 'editor' },
  { title: 'Maintainer', value: 'maintainer' },
  { title: 'Subscriber', value: 'subscriber' },
]

const plans = [
  { title: 'Basic', value: 'basic' },
  { title: 'Company', value: 'company' },
  { title: 'Enterprise', value: 'enterprise' },
  { title: 'Team', value: 'team' },
]

const status = [
  { title: 'Pending', value: 'pending' },
  { title: 'Active', value: 'active' },
  { title: 'Inactive', value: 'inactive' },
]

const resolveUserRoleVariant = (role: string) => {
  const roleLowerCase = role.toLowerCase()

  if (roleLowerCase === 'subscriber')
    return { color: 'primary', icon: 'mdi-account-outline' }
  if (roleLowerCase === 'author')
    return { color: 'warning', icon: 'mdi-cog-outline' }
  if (roleLowerCase === 'maintainer')
    return { color: 'success', icon: 'mdi-chart-donut' }
  if (roleLowerCase === 'editor')
    return { color: 'info', icon: 'mdi-pencil-outline' }
  if (roleLowerCase === 'admin')
    return { color: 'error', icon: 'mdi-laptop' }

  return { color: 'primary', icon: 'mdi-account-outline' }
}

const resolveUserStatusVariant = (stat: string) => {
  const statLowerCase = stat.toLowerCase()
  if (statLowerCase === 'pending')
    return 'warning'
  if (statLowerCase === 'active')
    return 'success'
  if (statLowerCase === 'inactive')
    return 'secondary'

  return 'primary'
}

const isAddNewUserDrawerVisible = ref(false)

// 👉 Add new user
const addNewUser = (userData: UserProperties) => {
  userListStore.addUser(userData)

  // refetch User
  fetchUsers()
}

// 👉 Delete user
const deleteUser = (id: number) => {
  userListStore.deleteUser(id)

  // refetch User
  fetchUsers()
}
</script>

<template>
  <section>
    <VCard
      title="Filters"
      class="mb-6"
    >
      <VCardText>
        <VRow>
          <!-- 👉 Select Role -->
          <VCol
            cols="12"
            sm="4"
          >
            <VSelect
              v-model="selectedRole"
              label="Select Role"
              :items="roles"
              clearable
              clear-icon="mdi-close"
            />
          </VCol>

          <!-- 👉 Select Plan -->
          <VCol
            cols="12"
            sm="4"
          >
            <VSelect
              v-model="selectedPlan"
              label="Select Plan"
              :items="plans"
              clearable
              clear-icon="mdi-close"
            />
          </VCol>

          <!-- 👉 Select Status -->
          <VCol
            cols="12"
            sm="4"
          >
            <VSelect
              v-model="selectedStatus"
              label="Select Status"
              :items="status"
              clearable
              clear-icon="mdi-close"
            />
          </VCol>
        </VRow>
      </VCardText>
    </VCard>

    <VCard>
      <VCardText class="d-flex flex-wrap gap-4">
        <!-- 👉 Export button -->
        <VBtn
          variant="outlined"
          color="secondary"
          prepend-icon="mdi-tray-arrow-up"
        >
          Export
        </VBtn>

        <VSpacer />

        <div class="app-user-search-filter d-flex align-center gap-6">
          <!-- 👉 Search  -->
          <VTextField
            v-model="searchQuery"
            placeholder="Search User"
            density="compact"
          />

          <!-- 👉 Add user button -->
          <VBtn @click="isAddNewUserDrawerVisible = true">
            Add User
          </VBtn>
        </div>
      </VCardText>

      <!-- SECTION data table -->
      <VDataTableServer
        v-model:items-per-page="options.itemsPerPage"
        v-model:page="options.page"
        :items="users"
        :items-length="totalUsers"
        :headers="headers"
        show-select
        class="rounded-0"
        @update:options="options = $event"
      >
        <!-- User -->
        <template #item.name="{ item }">
          <div class="d-flex align-center">
            <VAvatar
              size="34"
              color="primary"
              variant="tonal"
              class="me-3"
            >
              <VImg
                v-if="item.raw.photo"
                :src="item.raw.photo"
              />
              <span v-else>{{ avatarText(item.raw.name) }}</span>
            </VAvatar>

            <div class="d-flex flex-column">
              <h6 class="text-sm font-weight-medium">
                {{ item.raw.name }}
              </h6>
            </div>
          </div>
        </template>

        <!-- Contact -->
        <template #item.contact="{ item }">
          <span class="text-sm">
            {{ item.raw.phone || item.raw.email || 'غير متوفر' }}
          </span>
        </template>

        <!-- Governorate -->
        <template #item.governorate="{ item }">
          <span class="text-sm">
            {{ item.raw.governorate?.name_ar || 'غير محدد' }}
          </span>
        </template>

        <!-- Plants Count -->
        <template #item.plants_count="{ item }">
          <VChip color="success" density="comfortable">
            {{ item.raw.plants_count }}
          </VChip>
        </template>

        <!-- Status -->
        <template #item.is_active="{ item }">
          <VChip
            :color="item.raw.is_active ? 'success' : 'secondary'"
            density="comfortable"
          >
            {{ item.raw.is_active ? 'نشط' : 'موقوف' }}
          </VChip>
        </template>

        <!-- Trusted -->
        <template #item.is_trusted="{ item }">
          <VChip
            :color="item.raw.is_trusted ? 'info' : 'warning'"
            density="comfortable"
          >
            {{ item.raw.is_trusted ? 'موثق' : 'عادي' }}
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
                <VListItem :to="{ name: 'apps-user-view-id', params: { id: item.raw.id } }">
                  <template #prepend>
                    <VIcon icon="mdi-eye-outline" />
                  </template>
                  <VListItemTitle>View</VListItemTitle>
                </VListItem>

                <VListItem link>
                  <template #prepend>
                    <VIcon icon="mdi-pencil-outline" />
                  </template>
                  <VListItemTitle>Edit</VListItemTitle>
                </VListItem>

                <VListItem @click="deleteUser(item.raw.id)">
                  <template #prepend>
                    <VIcon icon="mdi-delete-outline" />
                  </template>
                  <VListItemTitle>Delete</VListItemTitle>
                </VListItem>
              </VList>
            </VMenu>
          </VBtn>
        </template>

        <!-- Pagination -->
        <template #bottom>
          <VDivider />

          <div class="d-flex justify-end gap-x-6 pa-2 flex-wrap">
            <div class="d-flex align-center gap-x-2 text-sm">
              Rows Per Page:
              <VSelect
                v-model="options.itemsPerPage"
                class="per-page-select text-high-emphasis"
                variant="plain"
                density="compact"
                :items="[10, 20, 25, 50, 100]"
              />
            </div>

            <span class="d-flex align-center text-sm me-2 text-high-emphasis">{{ paginationMeta(options, totalUsers) }}</span>

            <div class="d-flex gap-x-2 align-center me-2">
              <VBtn
                icon="mdi-chevron-left"
                class="flip-in-rtl"
                variant="text"
                density="comfortable"
                color="default"
                :disabled="options.page <= 1"
                @click="options.page <= 1 ? options.page = 1 : options.page--"
              />

              <VBtn
                icon="mdi-chevron-right"
                class="flip-in-rtl"
                density="comfortable"
                variant="text"
                color="default"
                :disabled="options.page >= Math.ceil(totalUsers / options.itemsPerPage)"
                @click="options.page >= Math.ceil(totalUsers / options.itemsPerPage) ? options.page = Math.ceil(totalUsers / options.itemsPerPage) : options.page++ "
              />
            </div>
          </div>
        </template>
      </VDataTableServer>
      <!-- SECTION -->
    </VCard>

    <!-- 👉 Add New User -->
    <AddNewUserDrawer
      v-model:isDrawerOpen="isAddNewUserDrawerVisible"
      @user-data="addNewUser"
    />
  </section>
</template>

<style lang="scss">
.app-user-search-filter {
  inline-size: 24.0625rem;
}

.text-capitalize {
  text-transform: capitalize;
}

.user-list-name:not(:hover) {
  color: rgba(var(--v-theme-on-background), var(--v-high-emphasis-opacity));
}
</style>
