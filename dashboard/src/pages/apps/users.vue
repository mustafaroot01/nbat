<script setup lang="ts">
import { ref, watch, onMounted } from 'vue'
import { useAdminUserStore } from '@/stores/useAdminUserStore'
import { avatarText } from '@core/utils/formatters'
import { VDataTableServer } from 'vuetify/labs/VDataTable'
import axios from '@axios'

interface User {
  id: number
  name: string
  phone: string
  email: string
  profile_photo: string
  governorate: any
  is_active: boolean
  is_trusted: boolean
  plants_count?: number
  created_at: string
}

const userStore = useAdminUserStore()

const userList = ref<User[]>([])
const stats = ref<any>({})
const governorates = ref<any[]>([])

const searchQuery = ref('')
const selectedGovernorate = ref<number | null>(null)
const selectedStatus = ref<string | null>(null)
const selectedTrusted = ref<string | null>(null)

const totalUsersCount = ref(0)
const options = ref({ page: 1, itemsPerPage: 15, sortBy: [], groupBy: [], search: undefined })

const headers = [
  { title: '#', key: 'index', sortable: false },
  { title: 'المستخدم', key: 'user', sortable: false },
  { title: 'رقم الهاتف', key: 'phone', sortable: false },
  { title: 'المحافظة', key: 'governorate', sortable: false },
  { title: 'الزراعات', key: 'plants', sortable: false },
  { title: 'تاريخ التسجيل', key: 'created_at', sortable: false },
  { title: 'الحالة', key: 'status', sortable: false },
  { title: 'الإجراءات', key: 'actions', sortable: false },
]

const formatPhone = (phone?: string) => {
  if (!phone) return '—'
  if (phone.startsWith('+964')) return phone.replace('+964', '0')
  return phone
}

const fetchGovernorates = async () => {
  try {
    const res = await axios.get('/admin/governorates')
    governorates.value = res.data.data
  } catch (err) {
    console.error(err)
  }
}

const fetchUsers = () => {
  userStore.fetchUsers({
    q: searchQuery.value,
    governorate_id: selectedGovernorate.value,
    status: selectedStatus.value,
    is_trusted: selectedTrusted.value,
    page: options.value.page,
    itemsPerPage: options.value.itemsPerPage,
  }).then(response => {
    userList.value = response.data.data.data
    totalUsersCount.value = response.data.data.total
    stats.value = response.data.stats || {}
  }).catch(error => {
    console.error(error)
  })
}

// Watch filters
watch([searchQuery, selectedGovernorate, selectedStatus, selectedTrusted], () => {
  options.value.page = 1
  fetchUsers()
}, { deep: true })

// Watch pagination
watch(() => options.value, fetchUsers, { deep: true })

onMounted(() => {
  fetchGovernorates()
  fetchUsers()
})

const toggleUser = (item: User) => {
  userStore.toggleUser(item.id).then(() => fetchUsers())
}

const toggleTrusted = (item: User) => {
  userStore.toggleTrustedUser(item.id).then(() => fetchUsers())
}
</script>

<template>
  <section>
    <!-- 👉 Widgets -->
    <VRow class="mb-6">
      <VCol cols="12" sm="6" md="3">
        <VCard>
          <VCardText class="d-flex justify-space-between">
            <div>
              <span class="text-sm">إجمالي المستخدمين</span>
              <div class="d-flex align-center gap-2 my-1">
                <h6 class="text-h4">{{ stats.total || 0 }}</h6>
              </div>
              <span class="text-xs text-medium-emphasis">جميع المسجلين</span>
            </div>
            <VAvatar variant="tonal" color="primary" rounded size="42">
              <VIcon icon="mdi-account-outline" size="26" />
            </VAvatar>
          </VCardText>
        </VCard>
      </VCol>

      <VCol cols="12" sm="6" md="3">
        <VCard>
          <VCardText class="d-flex justify-space-between">
            <div>
              <span class="text-sm">مستخدمين موثوقين</span>
              <div class="d-flex align-center gap-2 my-1">
                <h6 class="text-h4">{{ stats.trusted || 0 }}</h6>
              </div>
              <span class="text-xs text-medium-emphasis">ينشرون بدون موافقة</span>
            </div>
            <VAvatar variant="tonal" color="info" rounded size="42">
              <VIcon icon="mdi-shield-check-outline" size="26" />
            </VAvatar>
          </VCardText>
        </VCard>
      </VCol>

      <VCol cols="12" sm="6" md="3">
        <VCard>
          <VCardText class="d-flex justify-space-between">
            <div>
              <span class="text-sm">حسابات نشطة</span>
              <div class="d-flex align-center gap-2 my-1">
                <h6 class="text-h4">{{ stats.active || 0 }}</h6>
              </div>
              <span class="text-xs text-medium-emphasis">غير موقوفين</span>
            </div>
            <VAvatar variant="tonal" color="success" rounded size="42">
              <VIcon icon="mdi-account-check-outline" size="26" />
            </VAvatar>
          </VCardText>
        </VCard>
      </VCol>

      <VCol cols="12" sm="6" md="3">
        <VCard>
          <VCardText class="d-flex justify-space-between">
            <div>
              <span class="text-sm">حسابات موقوفة</span>
              <div class="d-flex align-center gap-2 my-1">
                <h6 class="text-h4">{{ stats.suspended || 0 }}</h6>
              </div>
              <span class="text-xs text-medium-emphasis">ممنوعون من النشر</span>
            </div>
            <VAvatar variant="tonal" color="error" rounded size="42">
              <VIcon icon="mdi-account-off-outline" size="26" />
            </VAvatar>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <!-- 👉 Filters -->
    <VCard title="فلاتر البحث" class="mb-6">
      <VCardText>
        <VRow>
          <VCol cols="12" sm="4">
            <VAutocomplete
              v-model="selectedGovernorate"
              label="تصفية حسب المحافظة"
              :items="governorates"
              item-title="name_ar"
              item-value="id"
              clearable
              clear-icon="mdi-close"
            />
          </VCol>
          <VCol cols="12" sm="4">
            <VSelect
              v-model="selectedStatus"
              label="تصفية حسب الحالة"
              :items="[{ title: 'نشط', value: 'active' }, { title: 'موقوف', value: 'inactive' }]"
              clearable
              clear-icon="mdi-close"
            />
          </VCol>
          <VCol cols="12" sm="4">
            <VSelect
              v-model="selectedTrusted"
              label="حالة التوثيق"
              :items="[{ title: 'موثق', value: 'true' }, { title: 'غير موثق', value: 'false' }]"
              clearable
              clear-icon="mdi-close"
            />
          </VCol>
        </VRow>
      </VCardText>
    </VCard>

    <!-- 👉 Datatable -->
    <VCard>
      <VCardText class="d-flex flex-wrap gap-4">
        <VSpacer />
        <div class="app-user-search-filter d-flex align-center gap-6">
          <VTextField
            v-model="searchQuery"
            placeholder="بحث بالاسم، رقم الهاتف، أو الإيميل..."
            density="compact"
          />
        </div>
      </VCardText>

      <VDivider />

      <VDataTableServer
        v-model:items-per-page="options.itemsPerPage"
        v-model:page="options.page"
        :items="userList"
        :items-length="totalUsersCount"
        :headers="headers"
        class="rounded-0"
        @update:options="options = $event"
      >
        <!-- Index -->
        <template #item.index="{ index }">
          <span class="text-body-2 font-weight-medium">
            {{ index + 1 + (options.page - 1) * options.itemsPerPage }}
          </span>
        </template>

        <!-- User -->
        <template #item.user="{ item }">
          <div class="d-flex align-center">
            <VAvatar
              size="34"
              :variant="!item.raw.profile_photo ? 'tonal' : undefined"
              color="primary"
              class="me-3"
            >
              <VImg v-if="item.raw.profile_photo" :src="item.raw.profile_photo" />
              <span v-else class="text-xs">{{ avatarText(item.raw.name) }}</span>
            </VAvatar>

            <div class="d-flex flex-column">
              <h6 class="text-sm font-weight-medium mb-0 text-high-emphasis d-flex align-center">
                {{ item.raw.name }}
                <VIcon v-if="item.raw.is_trusted" icon="mdi-check-decagram" color="info" size="16" class="ms-1" />
              </h6>
              <span class="text-xs text-medium-emphasis">{{ item.raw.email || 'لا يوجد بريد إلكتروني' }}</span>
            </div>
          </div>
        </template>

        <!-- Phone -->
        <template #item.phone="{ item }">
          <span class="text-body-2" dir="ltr">{{ formatPhone(item.raw.phone) }}</span>
        </template>

        <!-- Governorate -->
        <template #item.governorate="{ item }">
          <VChip
            size="small"
            color="primary"
            variant="tonal"
            class="text-capitalize"
          >
            {{ item.raw.governorate?.name_ar || 'غير محدد' }}
          </VChip>
        </template>

        <!-- Plants Count -->
        <template #item.plants="{ item }">
          <div class="d-flex align-center gap-1">
            <VIcon icon="mdi-tree-outline" color="success" size="18" />
            <span class="font-weight-medium">{{ item.raw.plants_count || 0 }}</span>
          </div>
        </template>

        <!-- Created At -->
        <template #item.created_at="{ item }">
          <span class="text-body-2">{{ item.raw.created_at?.split('T')[0] || item.raw.created_at?.split(' ')[0] }}</span>
        </template>

        <!-- Status -->
        <template #item.status="{ item }">
          <VChip
            :color="item.raw.is_active ? 'success' : 'secondary'"
            size="small"
            class="text-capitalize font-weight-medium"
          >
            {{ item.raw.is_active ? 'نشط' : 'موقوف' }}
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
                    <VIcon icon="mdi-eye-outline" class="me-2" />
                  </template>
                  <VListItemTitle>عرض التفاصيل والسجل</VListItemTitle>
                </VListItem>

                <VListItem @click="toggleUser(item.raw)">
                  <template #prepend>
                    <VIcon :icon="item.raw.is_active ? 'mdi-account-off-outline' : 'mdi-account-check-outline'" class="me-2" />
                  </template>
                  <VListItemTitle>{{ item.raw.is_active ? 'إيقاف الحساب' : 'تفعيل الحساب' }}</VListItemTitle>
                </VListItem>

                <VListItem @click="toggleTrusted(item.raw)">
                  <template #prepend>
                    <VIcon :icon="item.raw.is_trusted ? 'mdi-shield-off-outline' : 'mdi-shield-check-outline'" class="me-2" :color="item.raw.is_trusted ? 'warning' : 'info'" />
                  </template>
                  <VListItemTitle :class="item.raw.is_trusted ? 'text-warning' : 'text-info'">{{ item.raw.is_trusted ? 'إلغاء التوثيق' : 'توثيق الحساب' }}</VListItemTitle>
                </VListItem>
              </VList>
            </VMenu>
          </VBtn>
        </template>
      </VDataTableServer>
    </VCard>

  </section>
</template>

<style lang="scss">
.app-user-search-filter {
  inline-size: 24.0625rem;
}
</style>

<route lang="yaml">
meta:
  action: read
  subject: users
</route>
