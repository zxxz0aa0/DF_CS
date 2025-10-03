<template>
  <div
    class="modal fade"
    id="editRecordModal"
    tabindex="-1"
    aria-labelledby="editRecordModalLabel"
    aria-hidden="true"
    :class="{ 'show': show }"
    :style="{ display: show ? 'block' : 'none' }"
  >
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="editRecordModalLabel">
            <i class="bi bi-pencil-square me-2"></i>編輯帳務記錄
          </h5>
          <button type="button" class="btn-close" @click="close" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <form @submit.prevent="handleSubmit">
            <!-- 交易日期 -->
            <div class="mb-3">
              <label for="transaction_date" class="form-label">
                交易日期 <span class="text-danger">*</span>
              </label>
              <input
                type="date"
                class="form-control"
                id="transaction_date"
                v-model="form.transaction_date"
                required
              >
            </div>

            <!-- 會計科目（唯讀顯示） -->
            <div class="mb-3">
              <label class="form-label">會計科目</label>
              <div class="form-control bg-light" readonly>
                {{ record.account_detail?.account_code }} - {{ record.account_detail?.account_name }}
              </div>
              <small class="text-muted">會計科目不可修改</small>
            </div>

            <!-- 駕駛/車輛資訊（唯讀顯示） -->
            <div class="mb-3">
              <label class="form-label">關聯資訊</label>
              <div class="form-control bg-light" readonly>
                <span v-if="record.driver_name">
                  <i class="bi bi-person me-1"></i>{{ record.driver_name }}
                </span>
                <span v-if="record.driver_name && record.vehicle_license_number"> | </span>
                <span v-if="record.vehicle_license_number">
                  <i class="bi bi-car-front me-1"></i>{{ record.vehicle_license_number }}
                </span>
              </div>
            </div>

            <!-- 借方金額 -->
            <div class="mb-3">
              <label for="debit_amount" class="form-label">借方金額</label>
              <div class="input-group">
                <input
                  type="number"
                  class="form-control"
                  id="debit_amount"
                  v-model.number="form.debit_amount"
                  min="0"
                  step="1"
                  placeholder="0"
                >
                <span class="input-group-text">元</span>
              </div>
            </div>

            <!-- 貸方金額 -->
            <div class="mb-3">
              <label for="credit_amount" class="form-label">貸方金額</label>
              <div class="input-group">
                <input
                  type="number"
                  class="form-control"
                  id="credit_amount"
                  v-model.number="form.credit_amount"
                  min="0"
                  step="1"
                  placeholder="0"
                >
                <span class="input-group-text">元</span>
              </div>
              <small class="text-muted">借方或貸方至少需要填寫一個</small>
            </div>

            <!-- 備註 -->
            <div class="mb-3">
              <label for="note" class="form-label">備註</label>
              <textarea
                class="form-control"
                id="note"
                v-model="form.note"
                rows="3"
                placeholder="輸入備註..."
              ></textarea>
            </div>

            <!-- 驗證錯誤訊息 -->
            <div v-if="errors.length > 0" class="alert alert-danger">
              <ul class="mb-0">
                <li v-for="(error, index) in errors" :key="index">{{ error }}</li>
              </ul>
            </div>
          </form>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" @click="close">
            <i class="bi bi-x-circle me-1"></i>取消
          </button>
          <button type="button" class="btn btn-primary" @click="handleSubmit">
            <i class="bi bi-check-circle me-1"></i>儲存變更
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal 背景遮罩 -->
  <div
    v-if="show"
    class="modal-backdrop fade show"
    @click="close"
  ></div>
</template>

<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
  show: { type: Boolean, default: false },
  record: { type: Object, required: true }
})

const emit = defineEmits(['close', 'submit'])

const form = ref({
  transaction_date: '',
  debit_amount: null,
  credit_amount: null,
  note: ''
})

const errors = ref([])

// 當 Modal 打開時，初始化表單資料
watch(() => props.show, (newVal) => {
  if (newVal && props.record) {
    // 格式化日期為 YYYY-MM-DD
    let formattedDate = ''
    if (props.record.transaction_date) {
      const dateStr = props.record.transaction_date
      if (dateStr.includes('T')) {
        // ISO 格式: "2025-10-01T16:00:00.000000Z"
        formattedDate = dateStr.split('T')[0]
      } else if (dateStr.includes('-')) {
        // 已經是 YYYY-MM-DD 格式
        formattedDate = dateStr
      } else {
        // 其他格式，嘗試解析
        const date = new Date(dateStr)
        if (!isNaN(date.getTime())) {
          formattedDate = date.toISOString().split('T')[0]
        }
      }
    }

    // 將金額轉換為整數
    const debitAmount = props.record.debit_amount ? Math.round(Number(props.record.debit_amount)) : null
    const creditAmount = props.record.credit_amount ? Math.round(Number(props.record.credit_amount)) : null

    form.value = {
      transaction_date: formattedDate,
      debit_amount: debitAmount,
      credit_amount: creditAmount,
      note: props.record.note || ''
    }

    errors.value = []
  }
})

const close = () => {
  emit('close')
}

const validateForm = () => {
  errors.value = []

  if (!form.value.transaction_date) {
    errors.value.push('請選擇交易日期')
  }

  // 檢查借方或貸方至少有一個有值
  const hasDebit = form.value.debit_amount && form.value.debit_amount > 0
  const hasCredit = form.value.credit_amount && form.value.credit_amount > 0

  if (!hasDebit && !hasCredit) {
    errors.value.push('借方或貸方金額至少需要填寫一個')
  }

  return errors.value.length === 0
}

const handleSubmit = () => {
  if (!validateForm()) {
    return
  }

  // 發送更新資料
  emit('submit', {
    id: props.record.id,
    transaction_date: form.value.transaction_date,
    debit_amount: form.value.debit_amount || null,
    credit_amount: form.value.credit_amount || null,
    note: form.value.note
  })
}
</script>

<style scoped>
.modal.show {
  display: block;
}

.modal-backdrop {
  background-color: rgba(0, 0, 0, 0.5);
}
</style>
