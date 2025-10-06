<template>
    <AdminLayout :user="$page.props.auth.user">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                編輯文件
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="mb-4">
                            <Link :href="route('admin.documents.index')" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-1"></i>
                                返回列表
                            </Link>
                        </div>

                        <form @submit.prevent="submit" enctype="multipart/form-data">
                            <!-- 第一區：關聯對象選擇 -->
                            <div class="card mb-4">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0">選擇關聯對象（駕駛或車輛二選一）</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <!-- 搜尋駕駛 -->
                                        <div class="col-md-6 mb-3">
                                            <label for="driver_search" class="form-label">
                                                駕駛
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
                                                    :disabled="!!form.vehicle_id"
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
                                            <div v-if="errors.driver_id" class="invalid-feedback d-block">
                                                {{ errors.driver_id }}
                                            </div>
                                            <div v-if="selectedDriver" class="mt-2">
                                                <div class="alert alert-success p-2">
                                                    <small>
                                                        <i class="bi bi-check-circle me-1"></i>
                                                        已選擇：{{ selectedDriver.name }} ({{ selectedDriver.id_number }})
                                                        <button type="button" class="btn btn-sm btn-link text-danger p-0 ms-2" @click="clearDriver">
                                                            <i class="bi bi-x-circle"></i> 清除
                                                        </button>
                                                    </small>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- 搜尋車輛 -->
                                        <div class="col-md-6 mb-3">
                                            <label for="vehicle_search" class="form-label">
                                                車輛
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
                                                    :disabled="!!form.driver_id"
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
                                            <div v-if="errors.vehicle_id" class="invalid-feedback d-block">
                                                {{ errors.vehicle_id }}
                                            </div>
                                            <div v-if="selectedVehicle" class="mt-2">
                                                <div class="alert alert-success p-2">
                                                    <small>
                                                        <i class="bi bi-check-circle me-1"></i>
                                                        已選擇：{{ selectedVehicle.license_number }} - {{ selectedVehicle.brand }} {{ selectedVehicle.vehicle_model }}
                                                        <button type="button" class="btn btn-sm btn-link text-danger p-0 ms-2" @click="clearVehicle">
                                                            <i class="bi bi-x-circle"></i> 清除
                                                        </button>
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 第二區：文件基本資料 -->
                            <div class="card mb-4">
                                <div class="card-header bg-info text-white">
                                    <h5 class="mb-0">文件基本資料</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">
                                                文件類別 <span class="text-danger">*</span>
                                            </label>
                                            <select
                                                v-model="form.document_category"
                                                class="form-select"
                                                :class="{ 'is-invalid': errors.document_category }"
                                            >
                                                <option value="">請選擇類別</option>
                                                <option value="identity">身分證件</option>
                                                <option value="insurance">保險證件</option>
                                                <option value="vehicle">車輛證件</option>
                                            </select>
                                            <div v-if="errors.document_category" class="invalid-feedback">
                                                {{ errors.document_category }}
                                            </div>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">
                                                文件名稱 <span class="text-danger">*</span>
                                            </label>
                                            <input
                                                v-model="form.document_name"
                                                type="text"
                                                class="form-control"
                                                :class="{ 'is-invalid': errors.document_name }"
                                                placeholder="例如：駕照、行照、強制險"
                                            >
                                            <div v-if="errors.document_name" class="invalid-feedback">
                                                {{ errors.document_name }}
                                            </div>
                                        </div>

                                        <div class="col-md-12 mb-3">
                                            <label class="form-label">文件號碼</label>
                                            <input
                                                v-model="form.document_number"
                                                type="text"
                                                class="form-control"
                                                :class="{ 'is-invalid': errors.document_number }"
                                                placeholder="證件號碼或保單號碼"
                                            >
                                            <div v-if="errors.document_number" class="invalid-feedback">
                                                {{ errors.document_number }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 第三區：期限設定 -->
                            <div class="card mb-4">
                                <div class="card-header bg-warning text-dark">
                                    <h5 class="mb-0">期限設定（選填）</h5>
                                </div>
                                <div class="card-body">
                                    <div class="alert alert-info">
                                        <i class="bi bi-info-circle me-2"></i>
                                        若不需要追蹤期限，可不填寫日期
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">文件開始日</label>
                                            <input
                                                v-model="form.start_date"
                                                type="date"
                                                class="form-control"
                                                :class="{ 'is-invalid': errors.start_date }"
                                            >
                                            <div v-if="errors.start_date" class="invalid-feedback">
                                                {{ errors.start_date }}
                                            </div>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">文件到期日</label>
                                            <input
                                                v-model="form.expiry_date"
                                                type="date"
                                                class="form-control"
                                                :class="{ 'is-invalid': errors.expiry_date }"
                                            >
                                            <div v-if="errors.expiry_date" class="invalid-feedback">
                                                {{ errors.expiry_date }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 第四區：保險資訊（當選擇保險證件時顯示） -->
                            <div v-if="form.document_category === 'insurance'" class="card mb-4">
                                <div class="card-header bg-success text-white">
                                    <h5 class="mb-0">保險資訊</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">保險等級係數</label>
                                            <input
                                                v-model="form.insurance_level"
                                                type="number"
                                                step="0.01"
                                                class="form-control"
                                                :class="{ 'is-invalid': errors.insurance_level }"
                                                placeholder="例如：1.5"
                                            >
                                            <div v-if="errors.insurance_level" class="invalid-feedback">
                                                {{ errors.insurance_level }}
                                            </div>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">保險費用</label>
                                            <input
                                                v-model="form.insurance_fee"
                                                type="number"
                                                step="0.01"
                                                class="form-control"
                                                :class="{ 'is-invalid': errors.insurance_fee }"
                                                placeholder="例如：12000"
                                            >
                                            <div v-if="errors.insurance_fee" class="invalid-feedback">
                                                {{ errors.insurance_fee }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 第五區：已上傳的檔案 -->
                            <div v-if="document.files && document.files.length > 0" class="card mb-4">
                                <div class="card-header bg-dark text-white">
                                    <h5 class="mb-0">已上傳的檔案 ({{ document.files.length }})</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div v-for="file in document.files" :key="file.id" class="col-md-4 mb-3">
                                            <div class="card">
                                                <div class="card-body">
                                                    <div class="text-center mb-2">
                                                        <i v-if="file.file_type === 'pdf'" class="bi bi-file-pdf text-danger" style="font-size: 2rem;"></i>
                                                        <i v-else class="bi bi-file-image text-primary" style="font-size: 2rem;"></i>
                                                    </div>
                                                    <p class="card-text small text-truncate" :title="file.file_name">
                                                        {{ file.file_name }}
                                                    </p>
                                                    <p class="card-text small text-muted">
                                                        {{ file.file_size_human }}
                                                    </p>
                                                    <div class="d-grid gap-1">
                                                        <a :href="route('admin.documents.files.download', file.id)" class="btn btn-sm btn-outline-primary" target="_blank">
                                                            <i class="bi bi-download me-1"></i>下載
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 第六區：新增檔案上傳 -->
                            <div class="card mb-4">
                                <div class="card-header bg-secondary text-white">
                                    <h5 class="mb-0">新增檔案（選填）</h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label">選擇新檔案（可選擇多個）</label>
                                        <input
                                            type="file"
                                            class="form-control"
                                            :class="{ 'is-invalid': errors['files.0'] }"
                                            multiple
                                            accept=".pdf,.jpg,.jpeg,.png"
                                            @change="handleFileUpload"
                                        >
                                        <div class="form-text">
                                            支援格式：PDF, JPG, JPEG, PNG（單檔最大 10MB）
                                        </div>
                                        <div v-if="errors['files.0']" class="invalid-feedback">
                                            {{ errors['files.0'] }}
                                        </div>
                                    </div>

                                    <!-- 已選擇的檔案列表 -->
                                    <div v-if="selectedFiles.length > 0" class="mt-3">
                                        <h6>已選擇的檔案：</h6>
                                        <ul class="list-group">
                                            <li v-for="(file, index) in selectedFiles" :key="index" class="list-group-item d-flex justify-content-between align-items-center">
                                                <div>
                                                    <i class="bi bi-file-earmark me-2"></i>
                                                    {{ file.name }}
                                                    <small class="text-muted">({{ formatFileSize(file.size) }})</small>
                                                </div>
                                                <button type="button" class="btn btn-sm btn-danger" @click="removeFile(index)">
                                                    <i class="bi bi-x"></i>
                                                </button>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <!-- 第六區：備註 -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">備註</h5>
                                </div>
                                <div class="card-body">
                                    <textarea
                                        v-model="form.notes"
                                        class="form-control"
                                        :class="{ 'is-invalid': errors.notes }"
                                        rows="3"
                                        placeholder="請輸入備註（選填）"
                                        maxlength="2000"
                                    ></textarea>
                                    <div v-if="errors.notes" class="invalid-feedback">
                                        {{ errors.notes }}
                                    </div>
                                    <div class="form-text">
                                        {{ form.notes ? form.notes.length : 0 }}/2000 字元
                                    </div>
                                </div>
                            </div>

                            <!-- 提交按鈕區 -->
                            <div class="d-flex justify-content-end gap-2 mt-4">
                                <Link :href="route('admin.documents.index')" class="btn btn-secondary">
                                    取消
                                </Link>
                                <button
                                    type="submit"
                                    class="btn btn-primary"
                                    :disabled="form.processing"
                                >
                                    <span v-if="form.processing" class="spinner-border spinner-border-sm me-2"></span>
                                    {{ form.processing ? '更新中...' : '確認更新' }}
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
import { ref, onMounted } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
    document: Object,
    drivers: Array,
    vehicles: Array,
    errors: Object,
})

const form = useForm({
    driver_id: props.document.driver_id || '',
    vehicle_id: props.document.vehicle_id || '',
    document_category: props.document.document_category || '',
    document_name: props.document.document_name || '',
    document_number: props.document.document_number || '',
    insurance_level: props.document.insurance_level || '',
    insurance_fee: props.document.insurance_fee || '',
    start_date: props.document.start_date || '',
    expiry_date: props.document.expiry_date || '',
    notes: props.document.notes || '',
    files: [],
})

const selectedFiles = ref([])

// 搜尋相關狀態
const driverSearch = ref('')
const vehicleSearch = ref('')
const showDriverDropdown = ref(false)
const showVehicleDropdown = ref(false)
const selectedDriver = ref(null)
const selectedVehicle = ref(null)
const filteredDrivers = ref([])
const filteredVehicles = ref([])

// 初始化選中的駕駛和車輛
const initializeSelections = () => {
    if (props.document.driver_id) {
        selectedDriver.value = props.drivers.find(driver => driver.id == props.document.driver_id)
        if (selectedDriver.value) {
            driverSearch.value = `${selectedDriver.value.name} (${selectedDriver.value.id_number})`
        }
    }

    if (props.document.vehicle_id) {
        selectedVehicle.value = props.vehicles.find(vehicle => vehicle.id == props.document.vehicle_id)
        if (selectedVehicle.value) {
            vehicleSearch.value = `${selectedVehicle.value.license_number} - ${selectedVehicle.value.brand} ${selectedVehicle.value.vehicle_model}`
        }
    }
}

// 搜尋駕駛
const searchDrivers = () => {
    if (driverSearch.value.trim().length < 1) {
        filteredDrivers.value = []
        showDriverDropdown.value = false
        return
    }

    const searchTerm = driverSearch.value.trim().toUpperCase()
    filteredDrivers.value = props.drivers.filter(driver => {
        const name = (driver.name || '').trim().toUpperCase()
        const idNumber = (driver.id_number || '').trim().toUpperCase()

        return name.includes(searchTerm) || idNumber.includes(searchTerm)
    }).slice(0, 10) // 限制顯示 10 筆結果

    showDriverDropdown.value = filteredDrivers.value.length > 0
}

// 搜尋車輛
const searchVehicles = () => {
    console.log('=== 開始搜尋車輛 (Edit) ===')
    console.log('輸入的搜尋關鍵字:', vehicleSearch.value)

    if (vehicleSearch.value.trim().length < 1) {
        filteredVehicles.value = []
        showVehicleDropdown.value = false
        return
    }

    const searchTerm = vehicleSearch.value.trim().toUpperCase()
    console.log('處理後的搜尋關鍵字 (大寫+去空白):', searchTerm)
    console.log('可搜尋的車輛總數:', props.vehicles?.length || 0)

    filteredVehicles.value = props.vehicles.filter(vehicle => {
        const licenseNumber = (vehicle.license_number || '').trim().toUpperCase()
        const brand = (vehicle.brand || '').trim().toUpperCase()
        const model = (vehicle.vehicle_model || '').trim().toUpperCase()

        const matchLicense = licenseNumber.includes(searchTerm)
        const matchBrand = brand.includes(searchTerm)
        const matchModel = model.includes(searchTerm)

        if (matchLicense || matchBrand || matchModel) {
            console.log('✓ 找到配對:', {
                license: licenseNumber,
                brand,
                model,
                matchedBy: matchLicense ? 'license' : (matchBrand ? 'brand' : 'model')
            })
        }

        return matchLicense || matchBrand || matchModel
    }).slice(0, 10) // 限制顯示 10 筆結果

    console.log('篩選後的結果數量:', filteredVehicles.value.length)
    console.log('篩選結果:', filteredVehicles.value)
    console.log('=========================')

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

// 清除駕駛選擇
const clearDriver = () => {
    selectedDriver.value = null
    form.driver_id = ''
    driverSearch.value = ''
    filteredDrivers.value = []
}

// 清除車輛選擇
const clearVehicle = () => {
    selectedVehicle.value = null
    form.vehicle_id = ''
    vehicleSearch.value = ''
    filteredVehicles.value = []
}

// 點擊外部關閉下拉選單
const handleClickOutside = (event) => {
    if (!event.target.closest('.position-relative')) {
        showDriverDropdown.value = false
        showVehicleDropdown.value = false
    }
}

const handleFileUpload = (event) => {
    const files = Array.from(event.target.files)
    selectedFiles.value = files
    form.files = files
}

const removeFile = (index) => {
    selectedFiles.value.splice(index, 1)
    form.files = selectedFiles.value
}

const formatFileSize = (bytes) => {
    if (bytes === 0) return '0 Bytes'
    const k = 1024
    const sizes = ['Bytes', 'KB', 'MB', 'GB']
    const i = Math.floor(Math.log(bytes) / Math.log(k))
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i]
}

const submit = () => {
    // 使用 transform 來處理表單資料並加入 _method
    form.transform((data) => ({
        ...data,
        _method: 'PUT'
    })).post(route('admin.documents.update', props.document.id), {
        onSuccess: () => {
            // 成功後會自動跳轉到列表頁
        },
        forceFormData: true
    })
}

onMounted(() => {
    // 除錯：檢查傳入的車輛資料
    console.log('=== 車輛資料除錯 (Edit) ===')
    console.log('vehicles 數量:', props.vehicles?.length || 0)
    console.log('所有 vehicles:', props.vehicles)
    if (props.vehicles && props.vehicles.length > 0) {
        console.log('第一筆車輛範例:', props.vehicles[0])
        // 列出所有車牌號碼
        console.log('所有車牌號碼:', props.vehicles.map(v => v.license_number))
    }
    console.log('============================')

    initializeSelections()
    document.addEventListener('click', handleClickOutside)
})
</script>
