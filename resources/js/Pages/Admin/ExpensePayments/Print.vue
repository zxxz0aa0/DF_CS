<template>
  <div class="print-container">
    <!-- 錯誤提示（無資料時顯示） -->
    <div v-if="errorMessage" class="alert alert-danger m-5">
      <h4 class="alert-heading">無法載入列印資料</h4>
      <p>{{ errorMessage }}</p>
      <hr>
      <p class="mb-0">
        <button class="btn btn-secondary" @click="handleClose">關閉視窗</button>
      </p>
    </div>

    <!-- 列印內容 -->
    <div v-else class="print-content">
      <!-- 報表標題 -->
      <div class="print-header">
        <h2>大豐交通 - 支出款項報表</h2>
        <p class="print-date">列印時間：{{ formattedPrintDate }}</p>
      </div>

      <!-- 資料表格 -->
      <table class="table table-bordered print-table">
        <thead>
          <tr>
            <th>交易日期</th>
            <th>時間</th>
            <th>隊員編號</th>
            <th>隊員姓名</th>
            <th>款項</th>
            <th class="text-end">支付金額</th>
            <th class="text-end">應扣款</th>
            <th class="text-end">實付金額</th>
            <th>支付日期</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="payment in printData.payments" :key="payment.id">
            <td>{{ formatDate(payment.record_date) }}</td>
            <td>{{ formatTime(payment.record_time) }}</td>
            <td>{{ payment.member_code || '—' }}</td>
            <td>{{ payment.member_name }}</td>
            <td>{{ payment.item_name }}</td>
            <td class="text-end">{{ formatCurrency(payment.gross_amount) }}</td>
            <td class="text-end">{{ formatCurrency(payment.deduction) }}</td>
            <td class="text-end fw-bold">{{ formatCurrency(payment.net_amount) }}</td>
            <td>{{ formatDate(payment.payment_date) }}</td>
          </tr>
        </tbody>
      </table>

      <!-- 統計資訊 -->
      <div class="print-statistics">
        <table class="table table-bordered statistics-table">
          <tr>
            <th>總筆數</th>
            <td class="text-end">{{ printData.statistics.totalCount }} </td>
            <th>總支付金額</th>
            <td class="text-end">{{ formatCurrency(printData.statistics.totalGrossAmount) }}</td>
          </tr>
          <tr>
            <th>總應扣款</th>
            <td class="text-end">{{ formatCurrency(printData.statistics.totalDeduction) }}</td>
            <th>總實付金額</th>
            <td class="text-end fw-bold">{{ formatCurrency(printData.statistics.totalNetAmount) }}</td>
          </tr>
        </table>
      </div>

      <!-- 操作按鈕 -->
      <div class="print-actions no-print">
        <button class="btn btn-primary" @click="handlePrint">
          <i class="bi bi-printer"></i> 列印
        </button>
        <button class="btn btn-secondary ms-2" @click="handleClose">
          <i class="bi bi-x-circle"></i> 關閉
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'

// 列印資料
const printData = ref({
  payments: [],
  statistics: {
    totalCount: 0,
    totalGrossAmount: 0,
    totalDeduction: 0,
    totalNetAmount: 0
  },
  printedAt: null
})

// 錯誤訊息
const errorMessage = ref('')

// 格式化列印時間
const formattedPrintDate = computed(() => {
  if (!printData.value.printedAt) return ''
  const date = new Date(printData.value.printedAt)
  return date.toLocaleString('zh-TW', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit'
  })
})

// 從 localStorage 載入資料
onMounted(() => {
  try {
    const data = localStorage.getItem('expense_payments_print_data')

    if (!data) {
      errorMessage.value = '無列印資料，請返回列表頁重新選擇要列印的支出款項。'
      return
    }

    const parsed = JSON.parse(data)

    if (!parsed.payments || parsed.payments.length === 0) {
      errorMessage.value = '列印資料為空，請返回列表頁重新選擇要列印的支出款項。'
      return
    }

    printData.value = parsed

    // 立即清理 localStorage（避免重複使用舊資料）
    localStorage.removeItem('expense_payments_print_data')

  } catch (error) {
    console.error('載入列印資料失敗:', error)
    errorMessage.value = '載入列印資料失敗，請返回列表頁重新操作。'
  }
})

// 格式化日期
function formatDate(dateString) {
  if (!dateString) return '—'
  const date = new Date(dateString)
  return date.toLocaleDateString('zh-TW')
}

// 格式化時間
function formatTime(timeString) {
  if (!timeString) return '—'
  // 如果是 HH:MM:SS 格式，只取 HH:MM
  return timeString.substring(0, 5)
}

// 格式化金額
function formatCurrency(amount) {
  if (amount === null || amount === undefined) return '0'
  return parseFloat(amount).toLocaleString('zh-TW', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 2
  })
}

// 執行列印
function handlePrint() {
  window.print()
}

// 關閉視窗
function handleClose() {
  window.close()
}
</script>

<style scoped>
@import '../../../../css/print-expense-payments.css';
</style>
