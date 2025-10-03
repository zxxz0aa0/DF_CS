<template>
  <AdminLayout :user="$page.props.auth.user">
    <div class="content-wrapper">
      <!-- 頁面標題 -->
      <div class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h1>帳務管理</h1>
            </div>
          </div>
        </div>
      </div>

      <!-- 主要內容 -->
      <div class="content">
        <div class="container-fluid">

          <!-- 1. 搜尋功能卡片 -->
          <SearchPanel
            :filters="filters"
            @search="handleSearch"
          />

          <!-- 2. 駕駛列表卡片 -->
          <DriverList
            v-if="drivers.length > 0"
            :drivers="drivers"
            :selected-id="selectedDriverId"
            @select="handleDriverSelect"
            @view-detail="showDriverDetail"
          />

          <!-- 3. 車輛列表卡片 -->
          <VehicleList
            v-if="vehicles.length > 0"
            :vehicles="vehicles"
            :selected-id="selectedVehicleId"
            @select="handleVehicleSelect"
            @view-detail="showVehicleDetail"
          />

          <!-- 4. 帳務新增表格卡片 -->
          <AccountingForm
            v-if="(selectedDriverId || selectedVehicleId) && permissions.canCreate"
            :selected-driver="selectedDriver"
            :selected-vehicle="selectedVehicle"
            @submit="handleSubmit"
          />

          <!-- 5. 帳務資訊列表卡片 -->
          <AccountingRecordList
            v-if="(selectedDriverId || selectedVehicleId) && records.data !== undefined"
            :records="records"
            :statistics="statistics"
            :show-driver-button="!!(drivers.length > 0 && selectedVehicleId)"
            :show-vehicle-button="!!(vehicles.length > 0 && selectedDriverId)"
            :can-edit="permissions.canEdit"
            :can-delete="permissions.canDelete"
            @edit="handleEdit"
            @delete="handleDelete"
            @switch-view="handleSwitchView"
          />

          <!-- 駕駛詳細資料 Modal -->
          <DriverDetailModal
            v-if="selectedDriverForView"
            :show="showDriverModal"
            :driver="selectedDriverForView"
            @close="closeDriverModal"
          />

          <!-- 車輛詳細資料 Modal -->
          <VehicleDetailModal
            v-if="selectedVehicleForView"
            :show="showVehicleModal"
            :vehicle="selectedVehicleForView"
            @close="closeVehicleModal"
          />

          <!-- 編輯帳務記錄 Modal -->
          <AccountingRecordEditModal
            v-if="selectedRecordForEdit"
            :show="showEditModal"
            :record="selectedRecordForEdit"
            @close="closeEditModal"
            @submit="handleEditSubmit"
          />

        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed, watch, nextTick } from 'vue'
import { router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import SearchPanel from './Components/SearchPanel.vue'
import DriverList from './Components/DriverList.vue'
import VehicleList from './Components/VehicleList.vue'
import AccountingForm from './Components/AccountingForm.vue'
import AccountingRecordList from './Components/AccountingRecordList.vue'
import DriverDetailModal from './Components/DriverDetailModal.vue'
import VehicleDetailModal from './Components/VehicleDetailModal.vue'
import AccountingRecordEditModal from './Components/AccountingRecordEditModal.vue'

const props = defineProps({
  drivers: { type: Array, default: () => [] },
  vehicles: { type: Array, default: () => [] },
  records: { type: Object, default: () => ({ data: [] }) },
  statistics: { type: Object, default: null },
  filters: { type: Object, default: () => ({}) },
  permissions: { type: Object, default: () => ({ canCreate: false, canEdit: false, canDelete: false, canExport: false }) }
})

// 選擇的駕駛和車輛（從後端傳來的 filters 取得，後端已處理自動選擇邏輯）
const selectedDriverId = ref(props.filters.driver_id ? Number(props.filters.driver_id) : null)
const selectedVehicleId = ref(props.filters.vehicle_id ? Number(props.filters.vehicle_id) : null)

// 監聽 props.filters 變化，當搜尋後更新選擇狀態
watch(() => props.filters, (newFilters) => {
  selectedDriverId.value = newFilters.driver_id ? Number(newFilters.driver_id) : null
  selectedVehicleId.value = newFilters.vehicle_id ? Number(newFilters.vehicle_id) : null
}, { deep: true })

const selectedDriver = computed(() => {
  if (!selectedDriverId.value) return null
  return props.drivers.find(d => d.id === selectedDriverId.value)
})

const selectedVehicle = computed(() => {
  if (!selectedVehicleId.value) return null
  return props.vehicles.find(v => v.id === selectedVehicleId.value)
})

// Modal 狀態管理
const showDriverModal = ref(false)
const showVehicleModal = ref(false)
const showEditModal = ref(false)
const selectedDriverForView = ref(null)
const selectedVehicleForView = ref(null)
const selectedRecordForEdit = ref(null)

// 顯示駕駛詳細資料
const showDriverDetail = (driver) => {
  selectedDriverForView.value = driver
  showDriverModal.value = true
}

// 關閉駕駛 Modal
const closeDriverModal = () => {
  showDriverModal.value = false
  setTimeout(() => {
    selectedDriverForView.value = null
  }, 300) // 等待動畫完成
}

// 顯示車輛詳細資料
const showVehicleDetail = (vehicle) => {
  selectedVehicleForView.value = vehicle
  showVehicleModal.value = true
}

// 關閉車輛 Modal
const closeVehicleModal = () => {
  showVehicleModal.value = false
  setTimeout(() => {
    selectedVehicleForView.value = null
  }, 300) // 等待動畫完成
}

// 處理搜尋
const handleSearch = (searchData) => {
  router.get(route('admin.accounting.records.index'), searchData, {
    preserveState: true,
    preserveScroll: false,  // 搜尋後捲軸回到頂部是合理的
    only: ['drivers', 'vehicles', 'records', 'statistics', 'filters']
  })
}

// 處理駕駛選擇（保留車輛選擇，但顯示駕駛帳務）
const handleDriverSelect = (driverId) => {
  selectedDriverId.value = driverId
  // 不清空 selectedVehicleId，保持車輛的選中狀態
  // 傳遞 view_type='driver' 告訴後端要顯示駕駛帳務
  router.get(route('admin.accounting.records.index'), {
    ...props.filters,
    driver_id: driverId,
    vehicle_id: selectedVehicleId.value, // 保留車輛選擇狀態
    view_type: 'driver' // 指定顯示駕駛帳務
  }, {
    preserveState: true,
    preserveScroll: true,
    only: ['records', 'statistics', 'filters']
  })
}

// 處理車輛選擇（保留駕駛選擇，但顯示車輛帳務）
const handleVehicleSelect = (vehicleId) => {
  selectedVehicleId.value = vehicleId
  // 不清空 selectedDriverId，保持駕駛的選中狀態
  // 傳遞 view_type='vehicle' 告訴後端要顯示車輛帳務
  router.get(route('admin.accounting.records.index'), {
    ...props.filters,
    driver_id: selectedDriverId.value, // 保留駕駛選擇狀態
    vehicle_id: vehicleId,
    view_type: 'vehicle' // 指定顯示車輛帳務
  }, {
    preserveState: true,
    preserveScroll: true,
    only: ['records', 'statistics', 'filters']
  })
}

// 處理新增提交
const handleSubmit = (formData) => {
  // 加入搜尋條件和選擇狀態，讓後端可以保留搜尋結果和選擇
  const submitData = {
    ...formData,
    search_type: props.filters.search_type,
    keyword: props.filters.keyword,
    driver_id: selectedDriverId.value,
    vehicle_id: selectedVehicleId.value,
    view_type: props.filters.view_type || 'driver'
  }

  router.post(route('admin.accounting.records.store'), submitData, {
    preserveState: true,
    preserveScroll: true,
    only: ['records', 'statistics', 'filters', 'drivers', 'vehicles']
  })
}

// 處理編輯
const handleEdit = (record) => {
  // 先設定資料
  selectedRecordForEdit.value = record

  // 使用 nextTick 確保 DOM 更新後再打開 Modal
  nextTick(() => {
    showEditModal.value = true
  })
}

// 關閉編輯 Modal
const closeEditModal = () => {
  showEditModal.value = false
  setTimeout(() => {
    selectedRecordForEdit.value = null
  }, 300)
}

// 提交編輯
const handleEditSubmit = (formData) => {
  router.put(route('admin.accounting.records.update', formData.id), {
    account_detail_id: selectedRecordForEdit.value.account_detail_id,
    transaction_date: formData.transaction_date,
    debit_amount: formData.debit_amount,
    credit_amount: formData.credit_amount,
    note: formData.note
  }, {
    preserveState: true,
    preserveScroll: true,
    only: ['records', 'statistics'],
    onSuccess: () => {
      closeEditModal()
    }
  })
}

// 處理刪除
const handleDelete = (ids) => {
  if (!confirm(`確定要刪除 ${ids.length} 筆帳務記錄嗎？`)) return

  router.delete(route('admin.accounting.records.batch-destroy'), {
    data: { ids },
    preserveState: true,
    preserveScroll: true,
    only: ['records', 'statistics']
  })
}

// 處理切換檢視（駕駛/車輛）
const handleSwitchView = (viewType) => {
  // 不清空選擇狀態，只切換顯示的帳務記錄
  router.get(route('admin.accounting.records.index'), {
    ...props.filters,
    driver_id: selectedDriverId.value,
    vehicle_id: selectedVehicleId.value,
    view_type: viewType // 'driver' 或 'vehicle'
  }, {
    preserveState: true,
    preserveScroll: true,
    only: ['records', 'statistics', 'filters']
  })
}
</script>
