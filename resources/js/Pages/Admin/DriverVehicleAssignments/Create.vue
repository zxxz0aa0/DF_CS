<template>
    <AdminLayout :user="$page.props.auth.user">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                新增駕駛車輛綁定
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="mb-4">
                            <Link :href="route('admin.driver-vehicle-assignments.index')" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-1"></i>
                                返回列表
                            </Link>
                        </div>

                        <form @submit.prevent="submit">
                            <div class="row">
                                <!-- 搜尋駕駛 -->
                                <div class="col-md-6 mb-3">
                                    <label for="driver_search" class="form-label">
                                        駕駛 <span class="text-danger">*</span>
                                    </label>
                                    <div class="position-relative">
                                        <input
                                            id="driver_search"
                                            v-model="driverSearch"
                                            type="text"
                                            class="form-control"
                                            :class="{ 'is-invalid': errors.driver_id }"
                                            placeholder="請輸入駕駛姓名或身分證字號..."
                                            @input="searchDrivers"
                                            @focus="showDriverDropdown = true"
                                            autocomplete="off"
                                        />
                                        <div
                                            v-if="showDriverDropdown && filteredDrivers.length > 0"
                                            class="dropdown-menu show w-100"
                                            style="position: absolute; top: 100%; z-index: 1000; max-height: 200px; overflow-y: auto;"
                                        >
                                            <button
                                                v-for="driver in filteredDrivers"
                                                :key="driver.id"
                                                type="button"
                                                class="dropdown-item"
                                                @click="selectDriver(driver)"
                                            >
                                                <div>
                                                    <strong>{{ driver.name }}</strong>
                                                    <small class="text-muted d-block">{{ driver.id_number }}</small>
                                                </div>
                                            </button>
                                        </div>
                                    </div>
                                    <div v-if="errors.driver_id" class="invalid-feedback">
                                        {{ errors.driver_id }}
                                    </div>
                                    <div v-if="selectedDriver" class="mt-2">
                                        <div class="alert alert-success p-2">
                                            <small>
                                                <i class="bi bi-check-circle me-1"></i>
                                                已選擇：{{ selectedDriver.name }} ({{ selectedDriver.id_number }})
                                            </small>
                                        </div>
                                    </div>
                                </div>

                                <!-- 搜尋車輛 -->
                                <div class="col-md-6 mb-3">
                                    <label for="vehicle_search" class="form-label">
                                        車輛 <span class="text-danger">*</span>
                                    </label>
                                    <div class="position-relative">
                                        <input
                                            id="vehicle_search"
                                            v-model="vehicleSearch"
                                            type="text"
                                            class="form-control"
                                            :class="{ 'is-invalid': errors.vehicle_id }"
                                            placeholder="請輸入車牌號碼或車輛品牌..."
                                            @input="searchVehicles"
                                            @focus="showVehicleDropdown = true"
                                            autocomplete="off"
                                        />
                                        <div
                                            v-if="showVehicleDropdown && filteredVehicles.length > 0"
                                            class="dropdown-menu show w-100"
                                            style="position: absolute; top: 100%; z-index: 1000; max-height: 200px; overflow-y: auto;"
                                        >
                                            <button
                                                v-for="vehicle in filteredVehicles"
                                                :key="vehicle.id"
                                                type="button"
                                                class="dropdown-item"
                                                @click="selectVehicle(vehicle)"
                                            >
                                                <div>
                                                    <strong>{{ vehicle.license_number }}</strong>
                                                    <small class="text-muted d-block">{{ vehicle.brand }} {{ vehicle.vehicle_model }}</small>
                                                </div>
                                            </button>
                                        </div>
                                    </div>
                                    <div v-if="errors.vehicle_id" class="invalid-feedback">
                                        {{ errors.vehicle_id }}
                                    </div>
                                    <div v-if="selectedVehicle" class="mt-2">
                                        <div class="alert alert-success p-2">
                                            <small>
                                                <i class="bi bi-check-circle me-1"></i>
                                                已選擇：{{ selectedVehicle.license_number }} - {{ selectedVehicle.brand }} {{ selectedVehicle.vehicle_model }}
                                            </small>
                                        </div>
                                    </div>
                                </div>

                                <!-- 備註 -->
                                <div class="col-12 mb-3">
                                    <label for="notes" class="form-label">備註</label>
                                    <textarea
                                        id="notes"
                                        v-model="form.notes"
                                        class="form-control"
                                        :class="{ 'is-invalid': errors.notes }"
                                        rows="3"
                                        placeholder="請輸入備註（選填）"
                                        maxlength="1000"
                                    ></textarea>
                                    <div v-if="errors.notes" class="invalid-feedback">
                                        {{ errors.notes }}
                                    </div>
                                    <div class="form-text">
                                        {{ form.notes ? form.notes.length : 0 }}/1000 字元
                                    </div>
                                </div>
                            </div>

                            <!-- 預覽區域 -->
                            <div v-if="selectedDriver && selectedVehicle" class="mt-4">
                                <div class="alert alert-info">
                                    <h6><i class="bi bi-info-circle me-2"></i>綁定預覽</h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <strong>駕駛：</strong>{{ selectedDriver.name }}<br>
                                            <strong>身分證：</strong>{{ selectedDriver.id_number }}
                                        </div>
                                        <div class="col-md-6">
                                            <strong>車牌：</strong>{{ selectedVehicle.license_number }}<br>
                                            <strong>車型：</strong>{{ selectedVehicle.brand }} {{ selectedVehicle.vehicle_model }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 提交按鈕 -->
                            <div class="d-flex justify-content-end gap-2 mt-4">
                                <Link :href="route('admin.driver-vehicle-assignments.index')" class="btn btn-secondary">
                                    取消
                                </Link>
                                <button
                                    type="submit"
                                    class="btn btn-primary"
                                    :disabled="processing"
                                >
                                    <span v-if="processing" class="spinner-border spinner-border-sm me-2"></span>
                                    {{ processing ? '處理中...' : '確認綁定' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
    drivers: Array,
    vehicles: Array,
    errors: Object,
})

const form = useForm({
    driver_id: '',
    vehicle_id: '',
    notes: '',
})

// 搜尋相關狀態
const driverSearch = ref('')
const vehicleSearch = ref('')
const showDriverDropdown = ref(false)
const showVehicleDropdown = ref(false)
const selectedDriver = ref(null)
const selectedVehicle = ref(null)
const filteredDrivers = ref([])
const filteredVehicles = ref([])

// 搜尋駕駛
const searchDrivers = () => {
    if (driverSearch.value.length < 1) {
        filteredDrivers.value = []
        showDriverDropdown.value = false
        return
    }

    const searchTerm = driverSearch.value.toLowerCase()
    filteredDrivers.value = props.drivers.filter(driver =>
        driver.name.toLowerCase().includes(searchTerm) ||
        driver.id_number.toLowerCase().includes(searchTerm)
    ).slice(0, 10) // 限制顯示 10 筆結果

    showDriverDropdown.value = filteredDrivers.value.length > 0
}

// 搜尋車輛
const searchVehicles = () => {
    if (vehicleSearch.value.length < 1) {
        filteredVehicles.value = []
        showVehicleDropdown.value = false
        return
    }

    const searchTerm = vehicleSearch.value.toLowerCase().trim()
    console.log('Searching for:', searchTerm)
    console.log('Available vehicles:', props.vehicles)

    filteredVehicles.value = props.vehicles.filter(vehicle => {
        const licenseMatch = vehicle.license_number && vehicle.license_number.toLowerCase().includes(searchTerm)
        const brandMatch = vehicle.brand && vehicle.brand.toLowerCase().includes(searchTerm)
        const modelMatch = vehicle.vehicle_model && vehicle.vehicle_model.toLowerCase().includes(searchTerm)

        console.log(`Vehicle ${vehicle.license_number}: license=${licenseMatch}, brand=${brandMatch}, model=${modelMatch}`)

        return licenseMatch || brandMatch || modelMatch
    }).slice(0, 10) // 限制顯示 10 筆結果

    console.log('Filtered results:', filteredVehicles.value)
    showVehicleDropdown.value = filteredVehicles.value.length > 0
}

// 選擇駕駛
const selectDriver = (driver) => {
    selectedDriver.value = driver
    form.driver_id = driver.id
    driverSearch.value = `${driver.name} (${driver.id_number})`
    showDriverDropdown.value = false
    filteredDrivers.value = []
}

// 選擇車輛
const selectVehicle = (vehicle) => {
    selectedVehicle.value = vehicle
    form.vehicle_id = vehicle.id
    vehicleSearch.value = `${vehicle.license_number} - ${vehicle.brand} ${vehicle.vehicle_model}`
    showVehicleDropdown.value = false
    filteredVehicles.value = []
}

// 點擊外部關閉下拉選單
const handleClickOutside = (event) => {
    if (!event.target.closest('.position-relative')) {
        showDriverDropdown.value = false
        showVehicleDropdown.value = false
    }
}

const submit = () => {
    form.post(route('admin.driver-vehicle-assignments.store'), {
        onSuccess: () => {
            // 成功後會自動跳轉到列表頁
        }
    })
}

onMounted(() => {
    document.addEventListener('click', handleClickOutside)
})
</script>

<style scoped>
.spinner-border-sm {
    width: 1rem;
    height: 1rem;
}
</style>