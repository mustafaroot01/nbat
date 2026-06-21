<script setup lang="ts">
const shortcuts = [
  {
    icon: 'mdi-view-dashboard-outline',
    title: 'الإحصائيات',
    subtitle: 'لوحة التحكم',
    to: { name: 'dashboards-analytics' },
  },
  {
    icon: 'mdi-account-group-outline',
    title: 'المستخدمون',
    subtitle: 'إدارة المستخدمين',
    to: { name: 'apps-users' },
  },
  {
    icon: 'mdi-tree-outline',
    title: 'النباتات',
    subtitle: 'مراجعة الزرعات',
    to: { name: 'apps-plants' },
  },
  {
    icon: 'mdi-alert-octagon-outline',
    title: 'الإبلاغات',
    subtitle: 'التبليغات عن النباتات',
    to: { name: 'apps-reports' },
  },
  {
    icon: 'mdi-flag-variant-outline',
    title: 'الحملات',
    subtitle: 'حملات التشجير',
    to: { name: 'apps-campaigns' },
  },
  {
    icon: 'mdi-cog-outline',
    title: 'الإعدادات',
    subtitle: 'إعدادات النظام العامة',
    to: { name: 'apps-settings' },
  },
]
</script>

<template>
  <Shortcuts :shortcuts="shortcuts" />
</template>
