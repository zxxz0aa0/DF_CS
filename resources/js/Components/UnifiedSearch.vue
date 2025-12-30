<template>
  <div class="unified-search-wrapper">
    <div class="d-flex gap-2">
      <!-- 搜尋類型選擇器 -->
      <select v-model="searchType" class="form-select" style="max-width: 150px;">
        <option value="driver">隊員</option>
        <option value="vehicle">車輛</option>
        <option value="fleet">隊編</option>
      </select>

      <!-- 搜尋輸入框 -->
      <div class="flex-grow-1 position-relative">
        <input
          v-model="searchKeyword"
          type="text"
          class="form-control"
          :placeholder="placeholderText"
          @focus="showResults = true"
          @input="handleInput"
        >

        <!-- 搜尋結果列表 -->
        <div
          v-if="showResults && searchKeyword.trim() && filteredResults.length > 0"
          class="search-results-dropdown"
        >
          <div
            v-for="result in filteredResults"
            :key="getResultKey(result)"
            class="list-group-item list-group-item-action"
            :class="{ active: isSelected(result) }"
            @click="selectResult(result)"
          >
            <div class="d-flex align-items-center">
              <i
                class="bi me-2"
                :class="isSelected(result) ? 'bi-check-circle-fill' : 'bi-circle'"
              ></i>
              <div class="flex-grow-1">
                <div class="fw-bold">{{ getResultTitle(result) }}</div>
                <small class="text-muted">{{ getResultSubtitle(result) }}</small>
              </div>
            </div>
          </div>
          <div class="list-group-item text-muted text-center small">
            找到 {{ filteredResults.length }} 筆資料
          </div>
        </div>

        <!-- 空結果提示 -->
        <div
          v-if="showResults && searchKeyword.trim() && filteredResults.length === 0"
          class="search-results-dropdown"
        >
          <div class="list-group-item text-muted text-center">
            找不到符合的資料
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
// 統一搜尋元件 - 支援隊員、車輛、隊編搜尋並自動填入關聯資料
import { ref, computed } from 'vue'

const props = defineProps({
  // 駕駛-車輛關聯資料
  assignments: { type: Array, required: true },
  // 駕駛資料
  drivers: { type: Array, required: true },
  // 車輛資料
  vehicles: { type: Array, required: true },
  // 當前選中的駕駛 ID
  selectedDriverId: { type: [String, Number], default: '' },
  // 當前選中的車輛 ID
  selectedVehicleId: { type: [String, Number], default: '' }
})

const emit = defineEmits(['select'])

// 搜尋類型 (driver/vehicle/fleet)
const searchType = ref('driver')
// 搜尋關鍵字
const searchKeyword = ref('')
// 是否顯示結果列表
const showResults = ref(false)

// 根據搜尋類型動態顯示提示文字
const placeholderText = computed(() => {
  const placeholders = {
    driver: '搜尋隊員姓名或身分證字號...',
    vehicle: '搜尋車牌號碼...',
    fleet: '搜尋隊編或隊名...'
  }
  return placeholders[searchType.value] || '請輸入搜尋關鍵字...'
})

// 過濾搜尋結果
const filteredResults = computed(() => {
  const keyword = searchKeyword.value.toLowerCase().trim()
  if (!keyword) return []

  if (searchType.value === 'driver') {
    // 搜尋隊員:從 assignments 中過濾
    return props.assignments.filter(item => {
      return item.driver_name.toLowerCase().includes(keyword) ||
             (item.driver_id_number && item.driver_id_number.toLowerCase().includes(keyword))
    })
  } else if (searchType.value === 'vehicle') {
    // 搜尋車輛:從 assignments 中過濾
    return props.assignments.filter(item => {
      return item.vehicle_license_number &&
             item.vehicle_license_number.toLowerCase().includes(keyword)
    })
  } else if (searchType.value === 'fleet') {
    // 搜尋隊編:從 assignments 中過濾
    return props.assignments.filter(item => {
      return (item.fleet_number && item.fleet_number.toLowerCase().includes(keyword)) ||
             (item.fleet_name && item.fleet_name.toLowerCase().includes(keyword))
    })
  }

  return []
})

// 取得結果的唯一 key
function getResultKey(result) {
  return `${result.driver_id}-${result.vehicle_id}`
}

// 取得結果的主標題
function getResultTitle(result) {
  if (searchType.value === 'driver') {
    return result.driver_name
  } else if (searchType.value === 'vehicle') {
    return result.vehicle_license_number
  } else if (searchType.value === 'fleet') {
    return result.fleet_number || result.fleet_name
  }
  return ''
}

// 取得結果的副標題
function getResultSubtitle(result) {
  if (searchType.value === 'driver') {
    return `${result.driver_id_number} | 車牌: ${result.vehicle_license_number} | 隊編: ${result.fleet_number || '無'}`
  } else if (searchType.value === 'vehicle') {
    return `隊員: ${result.driver_name} | 隊編: ${result.fleet_number || '無'}`
  } else if (searchType.value === 'fleet') {
    return `隊員: ${result.driver_name} | 車牌: ${result.vehicle_license_number}`
  }
  return ''
}

// 判斷是否為選中項目
function isSelected(result) {
  return result.driver_id == props.selectedDriverId &&
         result.vehicle_id == props.selectedVehicleId
}

// 選擇結果
function selectResult(result) {
  // 發送完整的關聯資料給父元件
  emit('select', {
    driver_id: result.driver_id,
    driver_name: result.driver_name,
    driver_id_number: result.driver_id_number,
    vehicle_id: result.vehicle_id,
    vehicle_license_number: result.vehicle_license_number,
    fleet_number: result.fleet_number,
    fleet_name: result.fleet_name
  })

  // 更新搜尋框顯示文字
  searchKeyword.value = getResultTitle(result)
  showResults.value = false
}

// 處理輸入事件
function handleInput() {
  showResults.value = true
}

// 重置搜尋
function reset() {
  searchKeyword.value = ''
  showResults.value = false
  searchType.value = 'driver'
}

// 暴露方法給父元件
defineExpose({
  reset,
  setKeyword(keyword) {
    searchKeyword.value = keyword
  }
})
</script>

<style scoped>
/* 統一搜尋元件樣式 */
.unified-search-wrapper {
  position: relative;
}

.search-results-dropdown {
  position: absolute;
  z-index: 1050;
  width: 100%;
  max-height: 350px;
  overflow-y: auto;
  background: white;
  border: 1px solid #dee2e6;
  border-radius: 0.375rem;
  box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
  margin-top: 0.25rem;
}

.search-results-dropdown .list-group-item {
  border-left: none;
  border-right: none;
  cursor: pointer;
  transition: background-color 0.15s ease-in-out;
}

.search-results-dropdown .list-group-item:first-child {
  border-top: none;
  border-top-left-radius: 0.375rem;
  border-top-right-radius: 0.375rem;
}

.search-results-dropdown .list-group-item:last-child {
  border-bottom: none;
  border-bottom-left-radius: 0.375rem;
  border-bottom-right-radius: 0.375rem;
}

.search-results-dropdown .list-group-item:hover:not(.text-muted) {
  background-color: #f8f9fa;
}

.search-results-dropdown .list-group-item.active {
  background-color: #0d6efd;
  color: white;
  border-color: #0d6efd;
}

.search-results-dropdown .list-group-item.active .text-muted {
  color: rgba(255, 255, 255, 0.75) !important;
}
</style>
