<template>
    <AdminLayout :user="$page.props.auth.user">
        <div class="content-wrapper">
            <!-- Content Header -->
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1 class="m-0">車輛詳情</h1>
                            <small class="text-muted">車牌號碼: {{ vehicle.license_number }}</small>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item">
                                    <Link :href="route('admin.dashboard')">儀表板</Link>
                                </li>
                                <li class="breadcrumb-item">
                                    <Link :href="route('admin.vehicles.index')">車輛管理</Link>
                                </li>
                                <li class="breadcrumb-item active">{{ vehicle.license_number }}</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main content -->
            <section class="content">
                <div class="container-fluid">
                    <!-- 操作按鈕 -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="btn-group">
                                <Link
                                    v-if="$page.props.auth.permissions?.includes('edit vehicles')"
                                    :href="route('admin.vehicles.edit', vehicle.id)"
                                    class="btn btn-warning"
                                >
                                    <i class="fas fa-edit"></i> 編輯車輛
                                </Link>

                                <button
                                    v-if="$page.props.auth.permissions?.includes('manage vehicle status')"
                                    type="button"
                                    :class="vehicle.vehicle_status === 'active' ? 'btn btn-secondary' : 'btn btn-success'"
                                    @click="toggleVehicleStatus"
                                >
                                    <i :class="vehicle.vehicle_status === 'active' ? 'fas fa-user-times' : 'fas fa-user-check'"></i>
                                    {{ vehicle.vehicle_status === 'active' ? '車輛退籍' : '車輛復籍' }}
                                </button>

                                <button
                                    v-if="$page.props.auth.permissions?.includes('delete vehicles')"
                                    type="button"
                                    class="btn btn-danger"
                                    @click="deleteVehicle"
                                >
                                    <i class="fas fa-trash"></i> 刪除車輛
                                </button>

                                <button
                                    v-if="$page.props.auth.permissions?.includes('view vehicles')"
                                    type="button"
                                    class="btn btn-info"
                                    @click="showAuditLogs"
                                >
                                    <i class="fas fa-history"></i> 操作日誌
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- 基本資訊 -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">基本資訊</h3>
                                    <div class="card-tools">
                                        <span
                                            :class="vehicle.vehicle_status === 'active' ? 'badge bg-success' : 'badge bg-secondary'"
                                        >
                                            {{ vehicle.status_text }}
                                        </span>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td class="font-weight-bold" width="30%">車牌號碼</td>
                                            <td>{{ vehicle.license_number }}</td>
                                        </tr>
                                        <tr v-if="vehicle.replacement_license">
                                            <td class="font-weight-bold">替補車號</td>
                                            <td>{{ vehicle.replacement_license }}</td>
                                        </tr>
                                        <tr>
                                            <td class="font-weight-bold">車主名稱</td>
                                            <td>{{ vehicle.owner_name }}</td>
                                        </tr>
                                        <tr>
                                            <td class="font-weight-bold">公司類別</td>
                                            <td>{{ vehicle.company_category?.name || '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="font-weight-bold">公司名稱</td>
                                            <td>{{ vehicle.company?.name || '-' }}</td>
                                        </tr>
                                        <tr v-if="vehicle.vehicle_type">
                                            <td class="font-weight-bold">車輛類型</td>
                                            <td>{{ vehicle.vehicle_type }}</td>
                                        </tr>
                                        <tr v-if="vehicle.address">
                                            <td class="font-weight-bold">地址</td>
                                            <td>{{ vehicle.address }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- 車輛製造資訊 -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">車輛製造資訊</h3>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless">
                                        <tr v-if="vehicle.brand">
                                            <td class="font-weight-bold" width="30%">車輛廠牌</td>
                                            <td>{{ vehicle.brand }}</td>
                                        </tr>
                                        <tr v-if="vehicle.manufacture_year || vehicle.manufacture_month">
                                            <td class="font-weight-bold">出廠年月</td>
                                            <td>
                                                {{ formatManufactureDate(vehicle.manufacture_year, vehicle.manufacture_month) }}
                                            </td>
                                        </tr>
                                        <tr v-if="vehicle.vehicle_form">
                                            <td class="font-weight-bold">車輛形式</td>
                                            <td>{{ vehicle.vehicle_form }}</td>
                                        </tr>
                                        <tr v-if="vehicle.engine_displacement">
                                            <td class="font-weight-bold">排氣量</td>
                                            <td>{{ vehicle.engine_displacement }} cc</td>
                                        </tr>
                                        <tr v-if="vehicle.fuel_type">
                                            <td class="font-weight-bold">燃料種類</td>
                                            <td>{{ vehicle.fuel_type }}</td>
                                        </tr>
                                        <tr v-if="vehicle.vehicle_model">
                                            <td class="font-weight-bold">車輛款式</td>
                                            <td>{{ vehicle.vehicle_model }}</td>
                                        </tr>
                                        <tr v-if="vehicle.vehicle_style">
                                            <td class="font-weight-bold">車輛樣式</td>
                                            <td>{{ vehicle.vehicle_style }}</td>
                                        </tr>
                                        <tr v-if="vehicle.vehicle_color">
                                            <td class="font-weight-bold">車輛顏色</td>
                                            <td>{{ vehicle.vehicle_color }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- 車輛識別資訊 -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">車輛識別資訊</h3>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless">
                                        <tr v-if="vehicle.engine_number">
                                            <td class="font-weight-bold" width="30%">引擎號碼</td>
                                            <td>{{ vehicle.engine_number }}</td>
                                        </tr>
                                        <tr v-if="vehicle.chassis_number">
                                            <td class="font-weight-bold">車身號碼</td>
                                            <td>{{ vehicle.chassis_number }}</td>
                                        </tr>
                                        <tr v-if="vehicle.passenger_capacity">
                                            <td class="font-weight-bold">載運人數</td>
                                            <td>{{ vehicle.passenger_capacity }}人</td>
                                        </tr>
                                        <tr v-if="vehicle.property_type">
                                            <td class="font-weight-bold">產權類別</td>
                                            <td>{{ vehicle.property_type }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- 重要日期 -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">重要日期</h3>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless">
                                        <tr v-if="vehicle.license_issue_date">
                                            <td class="font-weight-bold" width="30%">發照日期</td>
                                            <td>{{ formatDate(vehicle.license_issue_date) }}</td>
                                        </tr>
                                        <tr v-if="vehicle.inspection_date">
                                            <td class="font-weight-bold">檢驗到期日</td>
                                            <td>
                                                {{ formatDate(vehicle.inspection_date) }}
                                                <span
                                                    v-if="vehicle.inspection_days_remaining !== null"
                                                    :class="getExpirationClass(vehicle.inspection_days_remaining)"
                                                    class="ml-2"
                                                >
                                                    ({{ vehicle.inspection_days_remaining > 0 ? '剩餘' : '逾期' }}{{ Math.abs(vehicle.inspection_days_remaining) }}天)
                                                </span>
                                            </td>
                                        </tr>
                                        <tr v-if="vehicle.registration_date">
                                            <td class="font-weight-bold">入籍日期</td>
                                            <td>{{ formatDate(vehicle.registration_date) }}</td>
                                        </tr>
                                        <tr v-if="vehicle.deregistration_date">
                                            <td class="font-weight-bold">退籍日期</td>
                                            <td>{{ formatDate(vehicle.deregistration_date) }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 備註 -->
                    <div v-if="vehicle.notes" class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">備註</h3>
                                </div>
                                <div class="card-body">
                                    <p class="mb-0">{{ vehicle.notes }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 系統資訊 -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">系統資訊</h3>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <table class="table table-borderless table-sm">
                                                <tr>
                                                    <td class="font-weight-bold" width="30%">建立時間</td>
                                                    <td>{{ formatDateTime(vehicle.created_at) }}</td>
                                                </tr>
                                                <tr v-if="vehicle.creator">
                                                    <td class="font-weight-bold">建立者</td>
                                                    <td>{{ vehicle.creator.name }}</td>
                                                </tr>
                                            </table>
                                        </div>
                                        <div class="col-md-6">
                                            <table class="table table-borderless table-sm">
                                                <tr>
                                                    <td class="font-weight-bold" width="30%">更新時間</td>
                                                    <td>{{ formatDateTime(vehicle.updated_at) }}</td>
                                                </tr>
                                                <tr v-if="vehicle.updater">
                                                    <td class="font-weight-bold">更新者</td>
                                                    <td>{{ vehicle.updater.name }}</td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <!-- 操作日誌 Modal -->
        <div id="auditLogsModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="auditLogsModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 id="auditLogsModalLabel" class="modal-title">
                            操作日誌 - {{ vehicle.license_number }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <!-- 載入狀態 -->
                        <div v-if="auditLogs.loading" class="text-center py-4">
                            <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
                            <p class="mt-2 text-muted">載入日誌中...</p>
                        </div>

                        <!-- 錯誤狀態 -->
                        <div v-else-if="auditLogs.error" class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle"></i>
                            載入日誌失敗：{{ auditLogs.error }}
                        </div>

                        <!-- 日誌內容 -->
                        <div v-else-if="auditLogs.data && auditLogs.data.data">
                            <!-- 無資料 -->
                            <div v-if="auditLogs.data.data.length === 0" class="text-center py-4 text-muted">
                                <i class="fas fa-inbox fa-2x"></i>
                                <p class="mt-2">尚無操作日誌記錄</p>
                            </div>

                            <!-- 日誌列表 -->
                            <div v-else>
                                <div class="table-responsive">
                                    <table class="table table-hover table-sm">
                                        <thead class="table-light">
                                            <tr>
                                                <th width="15%">時間</th>
                                                <th width="15%">操作類型</th>
                                                <th width="25%">描述</th>
                                                <th width="15%">操作者</th>
                                                <th width="15%">IP位址</th>
                                                <th width="15%">變更詳情</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="log in auditLogs.data.data" :key="log.id">
                                                <td>
                                                    <small>{{ formatDateTime(log.created_at) }}</small>
                                                </td>
                                                <td>
                                                    <span
                                                        class="badge"
                                                        :class="getActionBadgeClass(log.action)"
                                                    >
                                                        {{ log.action_text || log.action }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <small>{{ log.description || '-' }}</small>
                                                </td>
                                                <td>
                                                    <small>{{ log.user?.name || '系統' }}</small>
                                                </td>
                                                <td>
                                                    <small>{{ log.ip_address || '-' }}</small>
                                                </td>
                                                <td>
                                                    <button
                                                        v-if="log.changes && log.changes.length > 0"
                                                        type="button"
                                                        class="btn btn-sm btn-outline-info"
                                                        @click="showChangeDetails(log)"
                                                    >
                                                        <i class="fas fa-eye"></i> 詳情
                                                    </button>
                                                    <span v-else class="text-muted">-</span>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- 分頁 -->
                                <nav v-if="auditLogs.data.links" class="mt-3">
                                    <ul class="pagination pagination-sm justify-content-center">
                                        <li v-for="link in auditLogs.data.links" :key="link.label" class="page-item" :class="{ active: link.active, disabled: !link.url }">
                                            <button
                                                v-if="link.url"
                                                type="button"
                                                class="page-link"
                                                v-html="link.label"
                                                @click="loadAuditLogs(link.url)"
                                            />
                                            <span v-else class="page-link" v-html="link.label" />
                                        </li>
                                    </ul>
                                </nav>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            關閉
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- 變更詳情 Modal -->
        <div id="changeDetailsModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="changeDetailsModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 id="changeDetailsModalLabel" class="modal-title">變更詳情</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div v-if="selectedLog">
                            <div class="mb-3">
                                <strong>操作時間：</strong>{{ formatDateTime(selectedLog.created_at) }}<br>
                                <strong>操作類型：</strong>{{ selectedLog.action_text || selectedLog.action }}<br>
                                <strong>操作者：</strong>{{ selectedLog.user?.name || '系統' }}<br>
                                <strong>描述：</strong>{{ selectedLog.description || '-' }}
                            </div>

                            <!-- 變更欄位 -->
                            <div v-if="selectedLog.changes && selectedLog.changes.length > 0">
                                <h6 class="mb-3">變更欄位：</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered">
                                        <thead class="table-light">
                                            <tr>
                                                <th width="30%">欄位</th>
                                                <th width="35%">變更前</th>
                                                <th width="35%">變更後</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="field in selectedLog.changes" :key="field">
                                                <td><strong>{{ getFieldDisplayName(field) }}</strong></td>
                                                <td>
                                                    <code class="text-muted">
                                                        {{ getFieldValue(selectedLog.old_values, field) }}
                                                    </code>
                                                </td>
                                                <td>
                                                    <code class="text-success">
                                                        {{ getFieldValue(selectedLog.new_values, field) }}
                                                    </code>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            關閉
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref, reactive, onMounted, nextTick } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
    vehicle: Object,
})

// Modal 實例
let auditLogsModal = null
let changeDetailsModal = null

// 日誌相關狀態
const auditLogs = reactive({
    loading: false,
    error: null,
    data: null,
})

const selectedLog = ref(null)

// 欄位中文對應
const fieldDisplayNames = {
    license_number: '車牌號碼',
    replacement_license: '替補車號',
    owner_name: '車主名稱',
    company_category_id: '公司類別',
    company_id: '公司',
    vehicle_type: '車輛類型',
    address: '地址',
    brand: '車輛廠牌',
    manufacture_year: '出廠年份',
    manufacture_month: '出廠月份',
    vehicle_form: '車輛形式',
    engine_displacement: '排氣量',
    fuel_type: '燃料種類',
    vehicle_model: '車輛款式',
    vehicle_style: '車輛樣式',
    engine_number: '引擎號碼',
    chassis_number: '車身號碼',
    passenger_capacity: '載運人數',
    vehicle_color: '車輛顏色',
    license_issue_year: '發照年份',
    license_issue_month: '發照月份',
    license_issue_day: '發照日',
    inspection_year: '檢驗年份',
    inspection_month: '檢驗月份',
    inspection_day: '檢驗日',
    registration_year: '入籍年份',
    registration_month: '入籍月份',
    registration_day: '入籍日',
    deregistration_year: '退籍年份',
    deregistration_month: '退籍月份',
    deregistration_day: '退籍日',
    property_type: '產權類別',
    notes: '備註',
    vehicle_status: '車輛狀態',
}

// 格式化日期
const formatDate = (dateString) => {
    if (!dateString) return '-'
    const date = new Date(dateString)
    return date.toLocaleDateString('zh-TW')
}

// 格式化日期時間
const formatDateTime = (dateString) => {
    if (!dateString) return '-'
    const date = new Date(dateString)
    return date.toLocaleDateString('zh-TW') + ' ' + date.toLocaleTimeString('zh-TW')
}

// 格式化出廠日期
const formatManufactureDate = (year, month) => {
    if (!year && !month) return '-'
    if (year && month) return `${year}年${month}月`
    if (year) return `${year}年`
    if (month) return `${month}月`
    return '-'
}

// 取得到期狀態樣式
const getExpirationClass = (days) => {
    if (days < 0) return 'text-danger font-weight-bold'
    if (days <= 7) return 'text-danger'
    if (days <= 30) return 'text-warning'
    return 'text-success'
}

// 切換車輛狀態
const toggleVehicleStatus = () => {
    const action = props.vehicle.vehicle_status === 'active' ? 'deregister' : 'reactivate'
    const message = props.vehicle.vehicle_status === 'active' ? '確定要將此車輛退籍嗎？' : '確定要將此車輛復籍嗎？'

    if (confirm(message)) {
        router.post(route(`admin.vehicles.${action}`, props.vehicle.id), {}, {
            preserveScroll: true,
        })
    }
}

// 刪除車輛
const deleteVehicle = () => {
    if (confirm(`確定要刪除車輛 ${props.vehicle.license_number} 嗎？此操作可以復原。`)) {
        router.delete(route('admin.vehicles.destroy', props.vehicle.id))
    }
}

// 初始化 Bootstrap Modals
onMounted(() => {
    nextTick(() => {
        setTimeout(() => {
            const auditLogsModalElement = document.getElementById('auditLogsModal')
            const changeDetailsModalElement = document.getElementById('changeDetailsModal')

            if (auditLogsModalElement && window.bootstrap) {
                auditLogsModal = new window.bootstrap.Modal(auditLogsModalElement)
            }

            if (changeDetailsModalElement && window.bootstrap) {
                changeDetailsModal = new window.bootstrap.Modal(changeDetailsModalElement)
            }
        }, 100)
    })
})

// 顯示操作日誌
const showAuditLogs = () => {
    if (auditLogsModal) {
        auditLogsModal.show()
        loadAuditLogs()
    }
}

// 載入操作日誌
const loadAuditLogs = async (url = null) => {
    try {
        auditLogs.loading = true
        auditLogs.error = null

        const apiUrl = url || route('admin.vehicles.audit-logs', props.vehicle.id)

        const response = await fetch(apiUrl, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
        })

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`)
        }

        const data = await response.json()
        auditLogs.data = data

    } catch (error) {
        console.error('Load audit logs error:', error)
        auditLogs.error = error.message || '載入日誌時發生錯誤'
    } finally {
        auditLogs.loading = false
    }
}

// 顯示變更詳情
const showChangeDetails = (log) => {
    selectedLog.value = log
    if (changeDetailsModal) {
        changeDetailsModal.show()
    }
}

// 取得操作類型的徽章樣式
const getActionBadgeClass = (action) => {
    switch (action) {
        case 'create':
            return 'bg-success'
        case 'update':
            return 'bg-warning'
        case 'delete':
            return 'bg-danger'
        case 'deregister':
            return 'bg-secondary'
        case 'reactivate':
            return 'bg-info'
        case 'import':
            return 'bg-primary'
        case 'restore':
            return 'bg-success'
        default:
            return 'bg-light text-dark'
    }
}

// 取得欄位顯示名稱
const getFieldDisplayName = (field) => {
    return fieldDisplayNames[field] || field
}

// 取得欄位值
const getFieldValue = (values, field) => {
    if (!values || !values[field]) {
        return '-'
    }

    const value = values[field]

    // 特殊欄位處理
    switch (field) {
        case 'vehicle_status':
            return value === 'active' ? '在籍' : '退籍'
        case 'company_category_id':
        case 'company_id':
            // 這些是 ID，通常需要查詢對應的名稱，暫時顯示 ID
            return value
        default:
            return value
    }
}
</script>

<style scoped>
.table td {
    padding: 0.5rem 0.75rem;
    border: none;
}

.table-borderless td {
    border: none;
}

.btn-group {
    margin-bottom: 1rem;
}

.btn-group .btn + .btn {
    margin-left: 0.25rem;
}

.card + .card {
    margin-top: 1rem;
}

.badge {
    font-size: 0.8rem;
}
</style>