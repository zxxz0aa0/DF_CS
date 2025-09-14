<template>
  <span :class="badgeClass" class="px-2 py-1 text-xs font-semibold rounded-full">
    {{ statusLabel }}
  </span>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  status: {
    type: String,
    required: true
  }
})

const statusLabel = computed(() => {
  switch (props.status) {
    case 'active':
      return '使用中'
    case 'revoked':
      return '已繳銷'
    case 'transferred':
      return '已轉移'
    case 'replaced':
      return '已替補'
    default:
      return '未知狀態'
  }
})

const badgeClass = computed(() => {
  switch (props.status) {
    case 'active':
      return 'bg-green-100 text-green-800'
    case 'revoked':
      return 'bg-red-100 text-red-800'
    case 'transferred':
      return 'bg-yellow-100 text-yellow-800'
    case 'replaced':
      return 'bg-blue-100 text-blue-800'
    default:
      return 'bg-gray-100 text-gray-800'
  }
})
</script>