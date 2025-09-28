<template>
  <div class="modal fade" :class="{ show: show }" style="display: block;" v-if="show" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">{{ title }}</h5>
          <button type="button" class="btn-close" @click="emit('close')"></button>
        </div>
        <form @submit.prevent="emit('submit')">
          <div class="modal-body">
            <slot name="body">
              <p class="text-muted mb-0">請於此放置匯入表單內容。</p>
            </slot>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="emit('close')">關閉</button>
            <button type="submit" class="btn btn-primary" :disabled="processing">
              <i class="bi bi-upload"></i>
              {{ processing ? '處理中...' : confirmText }}
            </button>
          </div>
        </form>
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
    default: '匯入資料'
  },
  confirmText: {
    type: String,
    default: '開始匯入'
  },
  processing: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['close', 'submit'])
</script>

<style scoped>
.modal {
  background-color: rgba(0, 0, 0, 0.5);
}
</style>
