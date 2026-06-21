<script setup lang="ts">
import RoleCards from '@/views/apps/roles/RoleCards.vue'
import UserList from '@/views/apps/roles/UserList.vue'
</script>

<template>
  <VRow>
    <VCol cols="12">
      <h5 class="text-h5 mb-1">
        قائمة الأدوار
      </h5>
      <p class="text-sm mb-0">
        يوفر الدور إمكانية الوصول إلى القوائم والميزات المحددة مسبقًا بحيث يمكن للمشرف الوصول إلى ما يحتاجه بناءً على دوره المخصص
      </p>
    </VCol>

    <!-- 👉 Roles Cards -->
    <VCol cols="12">
      <RoleCards />
    </VCol>

    <VCol cols="12">
      <h5 class="text-h5 mt-5 mb-1">
        المشرفون وأدوارهم
      </h5>
      <p class="text-sm mb-6">
        قائمة بجميع حسابات المشرفين والأدوار المرتبطة بهم.
      </p>

      <!-- 👉 User List  -->
      <UserList />
    </VCol>
  </VRow>
</template>
