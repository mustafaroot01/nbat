import mock from '@/@fake-db/mock'

const plants = [
  {
    id: 1,
    name: 'نخلة التمر',
    type: 'نخيل',
    age: '5 سنوات',
    image: 'https://images.unsplash.com/photo-1597573726800-5760b86b0e5e?w=400',
    notes: 'نخلة مثمرة في حديقة المنزل',
    governorate: 'بغداد',
    user: 'أحمد علي',
    status: 'pending',
    rejection_reason: '',
    created_at: '2025-06-20 14:30',
  },
  {
    id: 2,
    name: 'شجرة الزيتون',
    type: 'زيتون',
    age: '10 سنوات',
    image: 'https://images.unsplash.com/photo-1601493700631-2b16ec4b4716?w=400',
    notes: 'شجرة زيتون معمرة في أرض زراعية',
    governorate: 'نينوى',
    user: 'محمد كريم',
    status: 'pending',
    rejection_reason: '',
    created_at: '2025-06-20 11:15',
  },
  {
    id: 3,
    name: 'شجرة الرمان',
    type: 'رمان',
    age: '3 سنوات',
    image: 'https://images.unsplash.com/photo-1601593768799-1a87e4b2b2b3?w=400',
    notes: 'شجرة رمان صغيرة تم زراعتها حديثاً',
    governorate: 'البصرة',
    user: 'سارة حسن',
    status: 'pending',
    rejection_reason: '',
    created_at: '2025-06-19 18:45',
  },
]

// 👉 Fetch plants
mock.onGet('/admin/plants').reply(config => {
  const { status = '' } = config.params ?? {}

  let filtered = plants

  if (status)
    filtered = plants.filter(p => p.status === status)

  return [200, {
    success: true,
    message: 'تم جلب النباتات',
    data: filtered,
  }]
})

// 👉 Approve plant
mock.onPatch(/\/admin\/plants\/\d+\/approve/).reply(config => {
  const id = Number(config.url?.match(/\d+/)?.[0])
  const plant = plants.find(p => p.id === id)

  if (plant) {
    plant.status = 'approved'

    return [200, { success: true, message: 'تم اعتماد النبتة', data: plant }]
  }

  return [404, { success: false, message: 'النبتة غير موجودة' }]
})

// 👉 Reject plant
mock.onPatch(/\/admin\/plants\/\d+\/reject/).reply(config => {
  const id = Number(config.url?.match(/\d+/)?.[0])
  const { rejection_reason } = JSON.parse(config.data)
  const plant = plants.find(p => p.id === id)

  if (plant) {
    plant.status = 'rejected'
    plant.rejection_reason = rejection_reason

    return [200, { success: true, message: 'تم رفض النبتة', data: plant }]
  }

  return [404, { success: false, message: 'النبتة غير موجودة' }]
})

// 👉 Delete plant
mock.onDelete(/\/admin\/plants\/\d+/).reply(config => {
  const id = Number(config.url?.match(/\d+/)?.[0])
  const index = plants.findIndex(p => p.id === id)

  if (index >= 0) {
    plants.splice(index, 1)

    return [200, { success: true, message: 'تم حذف النبتة' }]
  }

  return [404, { success: false, message: 'النبتة غير موجودة' }]
})
