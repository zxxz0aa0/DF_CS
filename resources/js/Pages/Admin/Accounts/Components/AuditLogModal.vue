<template>
  <div class="modal fade" :class="{ show: show }" style="display: block;" v-if="show" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">{{ title }}</h5>
          <button type="button" class="btn-close" @click="emit('close')"></button>
        </div>
        <div class="modal-body">
          <div v-if="logs.length === 0" class="text-center text-muted py-4">
            尚無審計紀錄
          </div>
          <div v-else class="list-group">
            <div v-for="log in logs"
                 :key="log.id"
                 class="list-group-item">
              <div class="d-flex justify-content-between">
                <div>
                  <strong>{{ log.event }}</strong>
                  <span class="text-muted ms-2">{{ log.user?.name || '系統' }}</span>
                </div>
                <small class="text-muted">{{ formatDate(log.created_at) }}</small>
              </div>
              <div class="mt-2">
                <details>
                  <summary class="small text-muted">檢視變更內容</summary>
                  <pre class="bg-light p-2 mt-2"><code>{{ stringifyDiff(log) }}</code></pre>
                </details>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" @click="emit('close')">關閉</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  title: {
    type: String,
    default: '審計紀錄'
  },
  logs: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['close'])

const formatDate = (value) => {
  if (!value) return '－'
  return new Date(value).toLocaleString()
}

const stringifyDiff = (log) => {
  try {
    const payload = {
      before: log.old_values || {},
      after: log.new_values || {}
    }
    return JSON.stringify(payload, null, 2)
  } catch (error) {
    console.error('格式化審計資料失敗:', error)
    return '無法解析變更內容'
  }
}
</script>

<style scoped>
pre {
  white-space: pre-wrap;
  word-break: break-word;
}
</style>
