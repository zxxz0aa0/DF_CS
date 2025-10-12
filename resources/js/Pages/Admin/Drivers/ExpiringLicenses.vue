<template>
    <AdminLayout :user="$page.props.auth.user">
        <template #header>
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">證照到期提醒</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><Link :href="route('admin.dashboard')">儀表板</Link></li>
                        <li class="breadcrumb-item"><Link :href="route('admin.drivers.index')">駕駛管理</Link></li>
                        <li class="breadcrumb-item active">證照到期提醒</li>
                    </ol>
                </div>
            </div>
        </template>

        <div class="row">
            <div class="col-12">
                <!-- 統計卡片 -->
                <div class="row mb-4">
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-danger">
                            <div class="inner">
                                <h3>{{ expiredCount }}</h3>
                                <p>已逾期</p>
                            </div>
                            <div class="icon">
                                <i class="bi bi-exclamation-triangle"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-warning">
                            <div class="inner">
                                <h3>{{ expiringCount }}</h3>
                                <p>即將到期</p>
                            </div>
                            <div class="icon">
                                <i class="bi bi-clock"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-info">
                            <div class="inner">
                                <h3>{{ totalDrivers }}</h3>
                                <p>總駕駛數</p>
                            </div>
                            <div class="icon">
                                <i class="bi bi-people"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-success">
                            <div class="inner">
                                <h3>{{ validCount }}</h3>
                                <p>證照有效</p>
                            </div>
                            <div class="icon">
                                <i class="bi bi-check-circle"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header" style="background-color:#B3D9D9;">
                        <h3 class="card-title">證照到期清單</h3>
                        <div class="card-tools">
                            <button @click="refreshData" class="btn btn-primary btn-sm">
                                <i class="bi bi-arrow-clockwise"></i> 重新整理
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- 篩選控制 -->
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label class="form-label">提醒天數範圍</label>
                                <select v-model="filters.days" @change="applyFilters" class="form-control form-control-sm">
                                    <option value="7">7天內</option>
                                    <option value="15">15天內</option>
                                    <option value="30">30天內</option>
                                    <option value="60">60天內</option>
                                    <option value="90">90天內</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">證照類型</label>
                                <select v-model="filters.licenseType" @change="applyFilters" class="form-control form-control-sm">
                                    <option value="all">全部</option>
                                    <option value="license">駕照</option>
                                    <option value="professional">執業登記證</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">狀態篩選</label>
                                <select v-model="filters.status" @change="applyFilters" class="form-control form-control-sm">
                                    <option value="all">全部</option>
                                    <option value="expired">已逾期</option>
                                    <option value="expiring">即將到期</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">公司篩選</label>
                                <select v-model="filters.company" @change="applyFilters" class="form-control form-control-sm">
                                    <option value="">全部公司</option>
                                    <option v-for="company in companies" :key="company" :value="company">
                                        {{ company }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <!-- 證照列表 -->
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>駕駛姓名</th>
                                        <th>身分證字號</th>
                                        <th>公司類別</th>
                                        <th>聯絡電話</th>
                                        <th>證照類型</th>
                                        <th>到期日</th>
                                        <th>剩餘天數</th>
                                        <th>狀態</th>
                                        <th>操作</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template v-for="driver in filteredDrivers" :key="driver.id">
                                        <!-- 駕照記錄 -->
                                        <tr v-if="shouldShowLicense(driver, 'license')">
                                            <td>{{ driver.name }}</td>
                                            <td>{{ driver.id_number }}</td>
                                            <td>{{ driver.company_category || '-' }}</td>
                                            <td>{{ driver.mobile_phone1 || driver.home_phone || '-' }}</td>
                                            <td>
                                                <span class="badge bg-primary">駕照</span>
                                            </td>
                                            <td>{{ formatDate(driver.license_expire_date) }}</td>
                                            <td>
                                                <span :class="getDaysRemainingClass(driver.license_days_remaining)">
                                                    {{ getDaysRemainingText(driver.license_days_remaining) }}
                                                </span>
                                            </td>
                                            <td>
                                                <span :class="getStatusBadgeClass(driver.license_days_remaining)">
                                                    {{ getStatusText(driver.license_days_remaining) }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <Link :href="route('admin.drivers.show', driver.id)"
                                                          class="btn btn-info btn-sm"
                                                          title="查看詳情">
                                                        <i class="bi bi-eye"></i>
                                                    </Link>
                                                    <Link :href="route('admin.drivers.edit', driver.id)"
                                                          class="btn btn-warning btn-sm"
                                                          title="編輯資料">
                                                        <i class="bi bi-pencil"></i>
                                                    </Link>
                                                    <!--<button @click="callDriver(driver)"
                                                            class="btn btn-success btn-sm"
                                                            title="撥打電話">
                                                        <i class="bi bi-telephone"></i>
                                                    </button>-->
                                                </div>
                                            </td>
                                        </tr>
                                        <!-- 執業登記證記錄 -->
                                        <tr v-if="shouldShowLicense(driver, 'professional')">
                                            <td>{{ driver.name }}</td>
                                            <td>{{ driver.id_number }}</td>
                                            <td>{{ driver.company_category || '-' }}</td>
                                            <td>{{ driver.mobile_phone1 || driver.home_phone || '-' }}</td>
                                            <td>
                                                <span class="badge bg-secondary">執業登記證</span>
                                            </td>
                                            <td>{{ formatDate(driver.professional_license_expire_date) }}</td>
                                            <td>
                                                <span :class="getDaysRemainingClass(driver.professional_license_days_remaining)">
                                                    {{ getDaysRemainingText(driver.professional_license_days_remaining) }}
                                                </span>
                                            </td>
                                            <td>
                                                <span :class="getStatusBadgeClass(driver.professional_license_days_remaining)">
                                                    {{ getStatusText(driver.professional_license_days_remaining) }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <Link :href="route('admin.drivers.show', driver.id)"
                                                          class="btn btn-info btn-sm"
                                                          title="查看詳情">
                                                        <i class="bi bi-eye"></i>
                                                    </Link>
                                                    <Link :href="route('admin.drivers.edit', driver.id)"
                                                          class="btn btn-warning btn-sm"
                                                          title="編輯資料">
                                                        <i class="bi bi-pencil"></i>
                                                    </Link>
                                                    <!--<button @click="callDriver(driver)"
                                                            class="btn btn-success btn-sm"
                                                            title="撥打電話">
                                                        <i class="bi bi-telephone"></i>
                                                    </button>-->
                                                </div>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>

                        <!-- 無資料提示 -->
                        <div v-if="filteredDrivers.length === 0" class="text-center py-4">
                            <i class="bi bi-check-circle" style="font-size: 3rem; color: #28a745;"></i>
                            <p class="text-muted mt-2">太好了！目前沒有證照即將到期的駕駛</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
    drivers: Array,
    total: Number,
    filters: Object
})

const filters = ref({
    days: props.filters?.days || 30,
    licenseType: 'all',
    status: 'all',
    company: ''
})

// 計算統計數據
const expiredCount = computed(() => {
    let count = 0
    props.drivers.forEach(driver => {
        if (driver.license_days_remaining !== null && driver.license_days_remaining < 0) count++
        if (driver.professional_license_days_remaining !== null && driver.professional_license_days_remaining < 0) count++
    })
    return count
})

const expiringCount = computed(() => {
    let count = 0
    props.drivers.forEach(driver => {
        if (driver.license_days_remaining !== null && driver.license_days_remaining >= 0 && driver.license_days_remaining <= 30) count++
        if (driver.professional_license_days_remaining !== null && driver.professional_license_days_remaining >= 0 && driver.professional_license_days_remaining <= 30) count++
    })
    return count
})

const totalDrivers = computed(() => props.drivers.length)

const validCount = computed(() => {
    let count = 0
    props.drivers.forEach(driver => {
        const hasValidLicense = driver.license_days_remaining === null || driver.license_days_remaining > 30
        const hasValidProfessional = driver.professional_license_days_remaining === null || driver.professional_license_days_remaining > 30
        if (hasValidLicense && hasValidProfessional) count++
    })
    return count
})

// 取得公司列表
const companies = computed(() => {
    const companiesSet = new Set()
    props.drivers.forEach(driver => {
        if (driver.company_category) {
            companiesSet.add(driver.company_category)
        }
    })
    return Array.from(companiesSet).sort()
})

// 篩選後的駕駛列表
const filteredDrivers = computed(() => {
    return props.drivers.filter(driver => {
        // 公司篩選
        if (filters.value.company && driver.company_category !== filters.value.company) {
            return false
        }

        // 檢查是否有符合條件的證照
        const hasMatchingLicense = shouldShowLicense(driver, 'license') || shouldShowLicense(driver, 'professional')

        return hasMatchingLicense
    })
})

// 判斷是否應該顯示特定類型的證照
const shouldShowLicense = (driver, type) => {
    if (filters.value.licenseType !== 'all' && filters.value.licenseType !== type) {
        return false
    }

    const daysRemaining = type === 'license' ? driver.license_days_remaining : driver.professional_license_days_remaining
    const expireDate = type === 'license' ? driver.license_expire_date : driver.professional_license_expire_date

    // 如果沒有到期日，不顯示
    if (!expireDate) return false

    // 如果天數為 null，不顯示
    if (daysRemaining === null) return false

    // 天數範圍篩選
    if (daysRemaining > filters.value.days) return false

    // 狀態篩選
    if (filters.value.status === 'expired' && daysRemaining >= 0) return false
    if (filters.value.status === 'expiring' && daysRemaining < 0) return false

    return true
}

const formatDate = (dateString) => {
    if (!dateString) return '-'
    return new Date(dateString).toLocaleDateString('zh-TW')
}

const getDaysRemainingText = (days) => {
    if (days === null) return '-'
    if (days < 0) return `逾期 ${Math.abs(days)} 天`
    return `${days} 天`
}

const getDaysRemainingClass = (days) => {
    if (days === null) return ''
    if (days < 0) return 'text-danger fw-bold'
    if (days <= 7) return 'text-danger fw-bold'
    if (days <= 30) return 'text-warning fw-bold'
    return 'text-success'
}

const getStatusText = (days) => {
    if (days === null) return '-'
    if (days < 0) return '已逾期'
    if (days <= 7) return '緊急'
    if (days <= 30) return '即將到期'
    return '正常'
}

const getStatusBadgeClass = (days) => {
    if (days === null) return 'badge bg-light'
    if (days < 0) return 'badge bg-danger'
    if (days <= 7) return 'badge bg-danger'
    if (days <= 30) return 'badge bg-warning'
    return 'badge bg-success'
}

const applyFilters = () => {
    router.get(route('admin.drivers.expiring-licenses'), filters.value, {
        preserveState: true,
        replace: true
    })
}

const refreshData = () => {
    router.reload()
}

const callDriver = (driver) => {
    const phone = driver.mobile_phone1 || driver.home_phone
    if (phone) {
        window.open(`tel:${phone}`)
    } else {
        alert('該駕駛沒有聯絡電話')
    }
}
</script>
