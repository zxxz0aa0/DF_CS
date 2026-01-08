<template>
  <div class="card mt-2">
    <div class="card-header" style="background-color:#B3D9D9;">
      <h3 class="card-title">駕駛列表</h3>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-hover">
          <thead>
            <tr>
              <th class="text-center" style="width: 4%;">選擇</th>
              <th style="width: 10%;">姓名</th>
              <th style="width: 15%;">身分證字號</th>
              <th style="width: 12%;">生日</th>
              <th style="width: 8%;">年齡</th>
              <th style="width: 15%;">電話</th>
              <th style="width: 12%;">狀態</th>
              <th style="width: 10%;">操作</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="driver in drivers"
              :key="driver.id"
              :class="{ 'table-active': driver.id === selectedId }"
              @click="selectDriver(driver.id)"
              style="cursor: pointer;"
            >
              <td class="text-center">
                <input
                  type="radio"
                  :checked="driver.id === selectedId"
                  @change="selectDriver(driver.id)"
                >
              </td>
              <td>{{ driver.name }}</td>
              <td>{{ driver.id_number }}</td>
              <td>{{ formatDate(driver.birthday) }}</td>
              <td :class="Number(calculateAge(driver.birthday)) >= 69 ? 'text-danger' : ''">
                  {{ calculateAge(driver.birthday) }}
              </td>
              <td>{{ driver.mobile_phone1 }}</td>
              <td>
                <span :class="getStatusClass(driver.status)">
                {{ getStatusText(driver.status) }}
                </span>
              </td>
              <td>
                <button
                  @click.stop="viewDetail(driver)"
                  class="btn btn-sm btn-info me-1"
                  title="檢視詳細資料"
                >
                  <i class="bi bi-eye"></i>
                </button>
                <button
                  @click.stop="viewRecurringCost(driver)"
                  class="btn btn-sm btn-warning"
                  title="檢視經常性費用"
                  :disabled="!driver.recurring_cost_id"
                >
                  <i class="bi bi-cash-stack"></i>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  drivers: { type: Array, required: true },
  selectedId: { type: Number, default: null }
})

const emit = defineEmits(['select', 'view-detail', 'view-recurring-cost'])

const selectDriver = (driverId) => {
  emit('select', driverId)
}

const viewDetail = (driver) => {
  emit('view-detail', driver)
}

const viewRecurringCost = (driver) => {
  emit('view-recurring-cost', driver)
}

const formatDate = (date) => {
  if (!date) return '-'
  const d = new Date(date)
  if (isNaN(d)) return '-'
  const rocYear = d.getFullYear() - 1911
  const month = String(d.getMonth() + 1).padStart(2, '0')
  const day = String(d.getDate()).padStart(2, '0')
  return `${rocYear}-${month}-${day}`
}

const calculateAge = (birthday) => {
  if (!birthday) return '-'
  const diff = Date.now() - new Date(birthday).getTime()
  const ageDate = new Date(diff)
  return Math.abs(ageDate.getUTCFullYear() - 1970)
}

// 取得狀態顯示文字
const getStatusText = (status) => {
  const statusMap = {
    'open': '在籍中',
    'close': '已退籍',
    'bad_debt': '呆帳'
  }
  return statusMap[status] || '未知'
}

// 取得狀態顯示的 CSS class
const getStatusClass = (status) => {
  const classMap = {
    'open': 'text-success',
    'close': 'text-danger',
    'bad_debt': 'text-danger'
  }
  return classMap[status] || 'text-secondary'
}
</script>
