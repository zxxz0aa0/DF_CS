<template>
  <div class="card">
    <div class="card-header">
      <h3 class="card-title">帳務資訊列表</h3>
      <div class="card-tools">
        <button
          v-if="showDriverButton"
          @click="$emit('switch-view', 'driver')"
          class="btn btn-sm btn-outline-secondary me-2"
        >
          <i class="bi bi-person"></i> 駕駛帳務
        </button>
        <button
          v-if="showVehicleButton"
          @click="$emit('switch-view', 'vehicle')"
          class="btn btn-sm btn-outline-secondary"
        >
          <i class="bi bi-car-front"></i> 車輛帳務
        </button>
      </div>
    </div>

    <!-- 統計資訊 -->
    <div v-if="statistics" class="card-body border-bottom">
      <div class="row text-center">
        <div class="row col-md-4">
            <div class="col-md-6 d-flex align-items-center justify-content-center">
                <h6 class="mb-0">借方加總</h6>
            </div>
            <div class="col-md-6">
                 <h1 class="text-primary mt-0">{{ formatAmount(statistics.debit_total) }}</h1>
            </div>
        </div>
        <div class="row col-md-4">
            <div class="col-md-6 d-flex align-items-center justify-content-center">
                 <h6 class="mb-0">貸方加總</h6>
            </div>
            <div class="col-md-6">
                 <h1 class="text-primary mb-0">{{ formatAmount(statistics.credit_total) }}</h1>
            </div>
        </div>
        <div class="row col-md-4">
            <div class="col-md-6 d-flex align-items-center justify-content-center">
                 <h6 class="mb-0">餘額（借-貸）</h6>
            </div>
            <div class="col-md-6">
                <h1 :class="statistics.balance_is_negative ? 'text-danger' : 'text-success'" class="mb-0">
                    {{ formatAmount(statistics.balance) }}
                </h1>
             </div>
        </div>
      </div>
    </div>

    <!-- 帳務列表 -->
    <div class="card-body">
      <div class="table-responsive">
        <DataTable
          ref="dataTableRef"
          id="accounting-records-table"
          class="table table-striped table-hover align-middle"
          :data="tableData"
          :columns="columns"
          :options="tableOptions"
        />
      </div>
    </div>
  </div>
</template>

<style>
/* 隱藏所有排序圖示，確保標題和內容對齊 */
#accounting-records-table thead th span.dt-column-order,
#accounting-records-table thead td span.dt-column-order {
  display: none !important;
}

</style>



<script setup>
import { ref, computed, onMounted, onBeforeUnmount, nextTick, watch } from 'vue'
import DataTable from 'datatables.net-vue3'
import DataTablesCore from 'datatables.net-bs5'

DataTable.use(DataTablesCore)

// 📐 欄位寬度配置（集中管理，方便手動調整）
const COLUMN_WIDTHS = {
  date: '130px',
  accountCode: '130px',
  accountName: '120px',
  driverName: '120px',
  vehicleLicense: '120px',
  debitAmount: '130px',
  creditAmount: '130px',
  note: 'auto',
  actions: '200px'
}

const props = defineProps({
  records: { type: Object, required: true },
  statistics: { type: Object, default: null },
  showDriverButton: { type: Boolean, default: false },
  showVehicleButton: { type: Boolean, default: false },
  canEdit: { type: Boolean, default: false },
  canDelete: { type: Boolean, default: false }
})

const emit = defineEmits(['edit', 'delete', 'switch-view'])

const dataTableRef = ref(null)
const tableElement = ref(null)

const ACTION_BUTTON_SELECTOR = '.record-edit, .record-delete'
const amountFormatter = new Intl.NumberFormat('zh-TW', {
  minimumFractionDigits: 0,
  maximumFractionDigits: 0
})
const dateFormatter = new Intl.DateTimeFormat('zh-TW')

const tableData = computed(() => props.records?.data ?? [])

const tableOptions = computed(() => ({
  dom:
    "<'row mb-3'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
    "<'row'<'col-sm-12'tr>>" +
    "<'row mt-3'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
  language: {
    search: '快速搜尋：',
    lengthMenu: '每頁顯示 _MENU_ 筆',
    info: '顯示第 _START_ 至 _END_ 筆，共 _TOTAL_ 筆',
    infoEmpty: '沒有符合的資料',
    infoFiltered: '（自 _MAX_ 筆資料中篩選）',
    zeroRecords: '找不到符合的資料',
    emptyTable: '目前沒有帳務記錄',
    paginate: {
      first: '第一頁',
      last: '最後一頁',
      next: '下一頁',
      previous: '上一頁'
    }
  },
  autoWidth: false,
  responsive: true,
  order: [[0, 'desc']]
}))

const columns = computed(() => [
  {
    title: '日期',
    data: 'transaction_date',
    width: COLUMN_WIDTHS.date,
    className: 'text-center align-middle',
    render: (data, type) => renderDate(data, type)
  },
  {
    title: '科目編號',
    data: 'account_detail.account_code',
    width: COLUMN_WIDTHS.accountCode,
    className: 'text-center align-middle',
    defaultContent: '-',
    render: (_data, type, row) => renderPlain(row.account_detail?.account_code, type)
  },
  {
    title: '科目名稱',
    data: 'account_detail.account_name',
    width: COLUMN_WIDTHS.accountName,
    className: 'text-center align-middle',
    defaultContent: '-',
    render: (_data, type, row) => renderPlain(row.account_detail?.account_name, type)
  },
  {
    title: '姓名',
    data: 'driver_name',
    width: COLUMN_WIDTHS.driverName,
    className: 'text-center align-middle',
    defaultContent: '-',
    render: (_data, type, row) => renderPlain(row.driver_name, type)
  },
  {
    title: '車牌',
    data: 'vehicle_license_number',
    width: COLUMN_WIDTHS.vehicleLicense,
    className: 'text-center align-middle',
    defaultContent: '-',
    render: (_data, type, row) => renderPlain(row.vehicle_license_number, type)
  },
  {
    title: '借方',
    data: 'debit_amount',
    width: COLUMN_WIDTHS.debitAmount,
    className: 'text-center align-middle',
    render: (_data, type, row) => renderAmount(row.debit_amount, type)
  },
  {
    title: '貸方',
    data: 'credit_amount',
    width: COLUMN_WIDTHS.creditAmount,
    className: 'text-center align-middle',
    render: (_data, type, row) => renderAmount(row.credit_amount, type)
  },
  {
    title: '備註',
    data: 'note',
    className: 'align-middle',
    defaultContent: '-',
    render: (_data, type, row) => renderPlain(row.note, type)
  },
  {
    title: '操作',
    data: null,
    orderable: false,
    searchable: false,
    width: COLUMN_WIDTHS.actions,
    className: 'text-center align-middle',
    render: () => {
      const buttons = []

      if (props.canEdit) {
        buttons.push('<button type="button" class="btn btn-sm btn-warning record-edit"><i class="bi bi-pencil"></i></button>')
      }

      if (props.canDelete) {
        buttons.push('<button type="button" class="btn btn-sm btn-danger record-delete"><i class="bi bi-trash"></i></button>')
      }

      if (buttons.length === 0) {
        return '<span class="text-muted">無權限</span>'
      }

      return '<div class="btn-group" role="group">' + buttons.join('') + '</div>'
    }
  }
])

onMounted(async () => {
  await nextTick()
  // 延遲綁定事件，確保 DataTable 已完全初始化
  setTimeout(() => {
    attachActionListener()
  }, 500)
})

watch(
  () => dataTableRef.value?.dt,
  async (newVal) => {
    if (newVal) {
      await nextTick()
      setTimeout(() => {
        attachActionListener()
      }, 100)
    }
  }
)

watch(
  () => props.records?.data,
  async () => {
    await nextTick()
    setTimeout(() => {
      attachActionListener()
    }, 100)
  }
)

onBeforeUnmount(() => {
  detachActionListener()
})

function attachActionListener() {
  const dtInstance = dataTableRef.value?.dt
  if (!dtInstance) return

  const node = dtInstance.table().node()
  if (tableElement.value === node) return

  detachActionListener()

  tableElement.value = node
  tableElement.value.addEventListener('click', handleTableClick)
}

function detachActionListener() {
  if (!tableElement.value) return

  tableElement.value.removeEventListener('click', handleTableClick)
  tableElement.value = null
}

function handleTableClick(event) {
  let target = event.target
  if (!(target instanceof HTMLElement)) return

  // 如果點擊的是圖示，向上找到按鈕元素
  if (target.tagName === 'I') {
    target = target.parentElement
  }

  const actionButton = target.closest(ACTION_BUTTON_SELECTOR)
  if (!actionButton) return

  // 防止事件冒泡
  event.preventDefault()
  event.stopPropagation()

  const dtInstance = dataTableRef.value?.dt
  if (!dtInstance) return

  const rowElement = actionButton.closest('tr')
  if (!rowElement) return

  const rowData = dtInstance.row(rowElement).data()
  if (!rowData) return

  if (actionButton.classList.contains('record-delete')) {
    if (rowData.id != null) {
      emit('delete', [rowData.id])
    }
    return
  }

  if (actionButton.classList.contains('record-edit')) {
    emit('edit', rowData)
  }
}

function renderDate(value, type) {
  if (!value) {
    return type === 'display' ? '-' : ''
  }

  if (type === 'display') {
    return formatDate(value)
  }

  return value
}

function renderPlain(value, type) {
  if (value == null || value === '') {
    return type === 'display' ? '-' : ''
  }

  if (type === 'display') {
    return escapeHtml(value)
  }

  return value
}

function renderAmount(value, type) {
  if (value == null || value === '') {
    return type === 'display' ? '-' : 0
  }

  const numeric = Number(value)
  if (Number.isNaN(numeric)) {
    return type === 'display' ? '-' : 0
  }

  if (type === 'display' || type === 'filter') {
    return formatAmount(numeric)
  }

  return numeric
}

function formatDate(date) {
  const parsed = new Date(date)
  if (Number.isNaN(parsed.getTime())) {
    return '-'
  }

  return dateFormatter.format(parsed)
}

function formatAmount(amount) {
  const numericAmount = Number(amount)

  if (Number.isNaN(numericAmount)) {
    return '-'
  }

  return amountFormatter.format(Math.trunc(numericAmount))
}

function escapeHtml(value) {
  return String(value)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;')
}

</script>
