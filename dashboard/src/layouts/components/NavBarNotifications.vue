<script lang="ts" setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from '@axios'
import type { Notification } from '@layouts/types'

const router = useRouter()
const notifications = ref<Notification[]>([])
const unreadCount = ref(0)
const pollingInterval = ref<any>(null)

const fetchNotifications = async () => {
  try {
    const response = await axios.get('/admin/admin-notifications')
    const { notifications: data, unreadCount: count } = response.data.data
    
    notifications.value = data.map((n: any) => ({
      id: n.id,
      title: n.title,
      subtitle: n.subtitle,
      time: n.time,
      isSeen: n.isSeen,
      color: n.type === 'new_report' ? 'error' : 'success',
      icon: n.type === 'new_report' ? 'mdi-alert-circle-outline' : 'mdi-leaf',
      plant_id: n.plant_id,
      report_id: n.report_id,
    }))
    unreadCount.value = count
  } catch (error) {
    console.error('Failed to fetch notifications', error)
  }
}

const removeNotification = async (notificationId: any) => {
  try {
    await axios.delete(`/admin/admin-notifications/${notificationId}`)
    notifications.value = notifications.value.filter(n => n.id !== notificationId)
  } catch (error) {
    console.error('Failed to delete notification', error)
  }
}

const markRead = async (notificationIds: any[]) => {
  try {
    await axios.post('/admin/admin-notifications/mark-read', { ids: notificationIds })
    notifications.value.forEach(item => {
      if (notificationIds.includes(item.id)) {
        item.isSeen = true
      }
    })
    fetchNotifications() // Update unread count
  } catch (error) {
    console.error('Failed to mark read', error)
  }
}

const markUnRead = (notificationIds: any[]) => {
  // Not supported by backend yet, could be added later
}

const handleNotificationClick = (notification: Notification) => {
  if (!notification.isSeen) {
    markRead([notification.id])
  }
  
  // Navigate to appropriate page based on notification type
  if (notification.icon === 'mdi-alert-circle-outline') {
    router.push('/apps/reports')
  } else {
    router.push('/apps/plants/list')
  }
}

onMounted(() => {
  fetchNotifications()
  pollingInterval.value = setInterval(fetchNotifications, 60000) // Poll every 60 seconds
})

onUnmounted(() => {
  if (pollingInterval.value) {
    clearInterval(pollingInterval.value)
  }
})
</script>

<template>
  <Notifications
    :notifications="notifications"
    :badge-props="{ content: unreadCount, color: 'error', offsetX: 2, offsetY: 2 }"
    @remove="removeNotification"
    @read="markRead"
    @unread="markUnRead"
    @click:notification="handleNotificationClick"
  />
</template>
