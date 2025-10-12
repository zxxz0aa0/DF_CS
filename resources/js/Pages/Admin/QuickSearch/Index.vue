<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import DriverDetailModal from './DriverDetailModal.vue'
import VehicleDetailModal from './VehicleDetailModal.vue'

// Props
const props = defineProps({
    drivers: {
        type: Array,
        default: () => [],
    },
    vehicles: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
})

// State
const searchForm = ref({
    keyword: props.filters?.keyword || '',
    type: props.filters?.type || 'all',
})

const modals = ref({
    driverDetail: false,
    vehicleDetail: false,
})

const selectedDriver = ref(null)
const selectedVehicle = ref(null)

// Computed
const hasResults = computed(() => {
    return (props.drivers?.length > 0) || (props.vehicles?.length > 0)
})

const showDriverList = computed(() => {
    return (searchForm.value.type === 'all' || searchForm.value.type === 'driver') && props.drivers?.length > 0
})

const showVehicleList = computed(() => {
    return (searchForm.value.type === 'all' || searchForm.value.type === 'vehicle') && props.vehicles?.length > 0
})

// Methods
const handleSearch = () => {
    router.get(route('admin.quick-search.index'), searchForm.value, {
        preserveState: true,
        preserveScroll: true,
    })
}

const showDriverDetail = async (driverId) => {
    try {
        const response = await fetch(route('admin.quick-search.driver', driverId))
        selectedDriver.value = await response.json()
        modals.value.driverDetail = true
    } catch (error) {
        console.error('Error fetching driver details:', error)
    }
}

const showVehicleDetail = async (vehicleId) => {
    try {
        const response = await fetch(route('admin.quick-search.vehicle', vehicleId))
        selectedVehicle.value = await response.json()
        modals.value.vehicleDetail = true
    } catch (error) {
        console.error('Error fetching vehicle details:', error)
    }
}

const maskIdNumber = (idNumber) => {
    if (!idNumber || idNumber.length < 10) return idNumber
    return idNumber.substring(0, 3) + '****' + idNumber.substring(7)
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

const formatManufactureDate = (year, month) => {
    if (!year && !month) return '-'
    if (year && month) return `${year}年${month}月`
    if (year) return `${year}年`
    if (month) return `${month}月`
    return '-'
}
</script>

<template>
    <AdminLayout :user="$page.props.auth.user">
        <!-- 頁面標題 -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">快速搜尋</h1>
                    </div>
                </div>
            </div>
        </div>

        <!-- 主要內容 -->
        <div class="content">
            <div class="">
                <!-- 搜尋區域 -->
                <div class="card mb-3">
                    <div class="card-body">
                        <form @submit.prevent="handleSearch">
                            <div class="row g-3">
                                <div class="col-md-2">
                                    <input
                                        v-model="searchForm.keyword"
                                        type="text"
                                        class="form-control"
                                        placeholder="請輸入姓名、車牌號碼、身分證字號..."
                                    >
                                </div>
                                <div class="col-md-1">
                                    <select v-model="searchForm.type" class="form-select">
                                        <option value="all">全部</option>
                                        <option value="driver">駕駛</option>
                                        <option value="vehicle">車輛</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="bi bi-search"></i> 搜尋
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>



                <!-- 駕駛列表 -->
                <div v-if="showDriverList" class="card">
                    <div class="card-header" style="background-color:#B3D9D9;">
                        <h3 class="card-title">
                            <i class="bi bi-person-fill"></i> 駕駛列表
                        </h3>
                    </div>
                    <div class="card-body p-0 h6">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th style="width: 9%;">姓名</th>
                                        <th style="width: 11%;">身分證字號</th>
                                        <th style="width: 9%;">生日</th>
                                        <th style="width: 7%;">年齡</th>
                                        <th style="width: 11%;">手機號碼</th>
                                        <th style="width: 24%;">通訊地址</th>
                                        <th style="width: 12%;">公司類別</th>
                                        <th>狀態</th>
                                        <th width="150">操作</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="driver in drivers" :key="driver.id">
                                        <td>{{ driver.name }}</td>
                                        <td>{{ driver.id_number }}</td>
                                        <!--可以顯示遮身分證字號<td>{{ maskIdNumber(driver.id_number) }}</td>-->
                                        <td>{{ formatDate(driver.birthday) }}</td>
                                        <td>{{ calculateAge(driver.birthday) }}</td>
                                        <td>{{ driver.mobile_phone1 || '-' }}</td>
                                        <td>{{ driver.contact_address || '-' }}</td>
                                        <td>{{ driver.company_category?.name || '-' }}</td>
                                        <td>
                                            <span :class="driver.status === 'open' ? 'badge bg-success' : 'badge bg-secondary'">
                                                {{ driver.status === 'open' ? '在籍' : '退籍' }}
                                            </span>
                                        </td>
                                        <td>
                                            <button
                                                class="btn btn-sm btn-info"
                                                @click="showDriverDetail(driver.id)"
                                            >
                                                <i class="bi bi-eye"></i> 明細
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- 車輛列表 -->
                <div v-if="showVehicleList" class="card mt-3">
                    <div class="card-header" style="background-color:#B3D9D9;">
                        <h3 class="card-title">
                            <i class="bi bi-car-front-fill"></i> 車輛列表
                        </h3>
                    </div>
                    <div class="card-body p-0 h6">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th style="width: 10%;">車牌號碼</th>
                                        <th style="width: 9%;">車隊編號</th>
                                        <th style="width: 12%;">車主名稱</th>
                                        <th style="width: 9%;">廠牌</th>
                                        <th style="width: 6%;">車款</th>
                                        <th style="width: 6%;">顏色</th>
                                        <th style="width: 8%;">排氣量</th>
                                        <th style="width: 11%;">年份</th>
                                        <th style="width: 12%;">公司類別</th>
                                        <th>狀態</th>
                                        <th width="150">操作</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="vehicle in vehicles" :key="vehicle.id">
                                        <td>{{ vehicle.license_number }}</td>
                                        <td>{{ vehicle.fleet_number || '-' }}</td>
                                        <td>{{ vehicle.owner_name || '-' }}</td>
                                        <td>{{ vehicle.brand || '-' }}</td>
                                        <td>{{ vehicle.vehicle_model || '-' }}</td>
                                        <td>{{ vehicle.vehicle_color || '-' }}</td>
                                        <td>{{ vehicle.engine_displacement ? `${Math.round(Number(vehicle.engine_displacement))} cc` : '-' }}</td>
                                        <td>{{ formatManufactureDate(vehicle.manufacture_year, vehicle.manufacture_month) }}</td>
                                        <td>{{ vehicle.company_category?.name || '-' }}</td>
                                        <td>
                                            <span :class="vehicle.vehicle_status === 'active' ? 'badge bg-success' : 'badge bg-secondary'">
                                                {{ vehicle.vehicle_status === 'active' ? '在籍' : '退籍' }}
                                            </span>
                                        </td>
                                        <td>
                                            <button
                                                class="btn btn-sm btn-info"
                                                @click="showVehicleDetail(vehicle.id)"
                                            >
                                                <i class="bi bi-eye"></i> 明細
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- 空白狀態提示 -->
                <div v-if="!hasResults && filters?.keyword" class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle"></i> 查無符合條件的資料
                </div>

                <!-- 初始狀態提示 -->
                <div v-if="!filters?.keyword" class="alert alert-light text-center">
                    <i class="bi bi-search" style="font-size: 3rem; opacity: 0.3;"></i>
                    <p class="mb-0 mt-2">請輸入關鍵字開始搜尋</p>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <DriverDetailModal
            v-model:show="modals.driverDetail"
            :driver="selectedDriver"
        />

        <VehicleDetailModal
            v-model:show="modals.vehicleDetail"
            :vehicle="selectedVehicle"
        />
    </AdminLayout>
</template>
