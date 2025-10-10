<template>
  <div class="card mt-2">
    <div class="card-header" style="background-color:#B3D9D9;">
      <h3 class="card-title">帳務新增</h3>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-bordered">
          <thead>
            <tr>
              <th style="width: 100px;">日期</th>
              <th style="width: 150px;">科目編號</th>
              <th style="width: 120px;">科目名稱</th>
              <th style="width: 100px;">借方金額</th>
              <th style="width: 100px;">貸方金額</th>
              <th style="width: 300px;">備註</th>
              <th class="text-center" style="width: 60px;">操作</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(row, index) in rows" :key="index">
              <!-- 日期 -->
              <td>
                <input
                  v-model="row.transaction_date"
                  type="date"
                  class="form-control form-control-sm"
                >
              </td>

              <!-- 科目編號搜尋 -->
              <td>
                <AccountSelector
                  v-model="row.account_detail_id"
                  @select="(account) => handleAccountSelect(index, account)"
                />
              </td>

              <!-- 科目名稱 -->
              <td>
                <input
                  :value="row.account_name"
                  type="text"
                  class="form-control form-control-sm"
                  readonly
                  disabled
                >
              </td>

              <!-- 借方金額 -->
              <td>
                <input
                  v-model.number="row.debit_amount"
                  type="number"
                  step="0"
                  class="form-control form-control-sm text-end"
                >
              </td>

              <!-- 貸方金額 -->
              <td>
                <input
                  v-model.number="row.credit_amount"
                  type="number"
                  step="0"
                  class="form-control form-control-sm text-end"
                >
              </td>

              <!-- 備註 -->
              <td>
                <input
                  v-model="row.note"
                  type="text"
                  class="form-control form-control-sm"
                >
              </td>

              <!-- 操作 -->
              <td class="text-center">
                <button
                  @click="removeRow(index)"
                  class="btn btn-sm btn-danger"
                  :disabled="rows.length === 1"
                >
                  <i class="bi bi-trash"></i>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="row mt-0">
        <div class="col-md-8">
          <button @click="addRow" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> 新增列
          </button>
        </div>
        <div class="col-md-4">
          <button @click="submit" class="btn btn-success">
            <i class="bi bi-check-circle"></i> 確定新增
          </button>
        </div>
      </div>

      <!-- 錯誤訊息顯示 -->
      <div v-if="Object.keys(errors).length > 0" class="alert alert-danger mt-3">
        <ul class="mb-0">
          <li v-for="(error, key) in errors" :key="key">{{ error }}</li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, computed, onMounted, onUnmounted } from 'vue'
import { usePage, router } from '@inertiajs/vue3'
import AccountSelector from './AccountSelector.vue'

const props = defineProps({
  selectedDriver: { type: Object, default: null },
  selectedVehicle: { type: Object, default: null }
})

const emit = defineEmits(['submit'])

const page = usePage()
const errors = computed(() => page.props.errors || {})

// 表格列資料
const rows = ref([createNewRow()])

// 建立新列
function createNewRow() {
  const today = new Date().toISOString().split('T')[0]

  return {
    transaction_date: today,
    account_detail_id: null,
    account_code: '',
    account_name: '',
    driver_id: props.selectedDriver?.id || null,
    driver_name: props.selectedDriver?.name || '',
    driver_id_number: props.selectedDriver?.id_number || '',
    vehicle_id: props.selectedVehicle?.id || null,
    vehicle_license_number: props.selectedVehicle?.license_number || '',
    debit_amount: null,
    credit_amount: null,
    note: ''
  }
}

// 新增列
const addRow = () => {
  rows.value.push(createNewRow())
}

// 刪除列
const removeRow = (index) => {
  if (rows.value.length > 1) {
    rows.value.splice(index, 1)
  }
}

// 處理會計科目選擇
const handleAccountSelect = (index, account) => {
  rows.value[index].account_detail_id = account.id
  rows.value[index].account_code = account.account_code
  rows.value[index].account_name = account.account_name
}

// 監聽選擇的駕駛變更
watch(() => props.selectedDriver, (newDriver) => {
  rows.value.forEach(row => {
    row.driver_id = newDriver?.id || null
    row.driver_name = newDriver?.name || ''
    row.driver_id_number = newDriver?.id_number || ''
  })
})

// 監聽選擇的車輛變更
watch(() => props.selectedVehicle, (newVehicle) => {
  rows.value.forEach(row => {
    row.vehicle_id = newVehicle?.id || null
    row.vehicle_license_number = newVehicle?.license_number || ''
  })
})

// 提交表單
const submit = () => {
  // 驗證必填欄位
  let hasError = false

  rows.value.forEach((row, index) => {
    if (!row.account_detail_id) {
      alert(`第 ${index + 1} 列：會計科目為必填`)
      hasError = true
      return
    }

    if (!row.transaction_date) {
      alert(`第 ${index + 1} 列：日期為必填`)
      hasError = true
      return
    }

    if (!row.debit_amount && !row.credit_amount) {
      alert(`第 ${index + 1} 列：借方或貸方金額至少需要填寫一個`)
      hasError = true
      return
    }
  })

  if (hasError) return

  emit('submit', { records: rows.value })
}

// 清除表單（重置為單列空白表單）
const resetForm = () => {
  rows.value = [createNewRow()]
}

// 儲存事件監聽器的清理函數
let removeSuccessListener = null

// 監聽 Inertia 請求成功事件
const handleSuccess = (event) => {
  // 檢查是否為新增帳務記錄的成功回應
  if (event.detail.page.url.includes('/admin/accounting/records')) {
    // 重置表單
    resetForm()
  }
}

onMounted(() => {
  // router.on() 返回一個清理函數
  removeSuccessListener = router.on('success', handleSuccess)
})

onUnmounted(() => {
  // 呼叫清理函數移除監聽器
  if (removeSuccessListener) {
    removeSuccessListener()
  }
})

// 暴露方法給父組件使用（如果需要）
defineExpose({
  resetForm
})
</script>
