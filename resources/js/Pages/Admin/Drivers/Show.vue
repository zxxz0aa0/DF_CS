<template>
    <AdminLayout :user="$page.props.auth.user">
        <template #header>
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">駕駛詳情</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><Link :href="route('admin.dashboard')">儀表板</Link></li>
                        <li class="breadcrumb-item"><Link :href="route('admin.drivers.index')">駕駛管理</Link></li>
                        <li class="breadcrumb-item active">駕駛詳情</li>
                    </ol>
                </div>
            </div>
        </template>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">{{ driver.name }} 的詳細資料</h3>
                        <div class="card-tools">
                            <Link :href="route('admin.drivers.edit', driver.id)" class="btn btn-warning btn-sm me-2">
                                <i class="bi bi-pencil"></i> 編輯
                            </Link>
                            <Link :href="route('admin.drivers.index')" class="btn btn-secondary btn-sm">
                                <i class="bi bi-arrow-left"></i> 返回列表
                            </Link>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- 基本資料 -->
                            <div class="col-md-6">
                                <h5 class="border-bottom pb-2 mb-3">
                                    <i class="bi bi-person-fill text-primary"></i> 基本資料
                                </h5>
                                
                                <table class="table table-sm">
                                    <tbody>
                                        <tr>
                                            <th style="width: 30%">姓名:</th>
                                            <td>{{ driver.name }}</td>
                                        </tr>
                                        <tr>
                                            <th>身分證字號:</th>
                                            <td>{{ driver.id_number }}</td>
                                        </tr>
                                        <tr>
                                            <th>公司類別:</th>
                                            <td>{{ driver.company_category?.name || '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>生日:</th>
                                            <td>{{ formatDate(driver.birthday) }}</td>
                                        </tr>
                                        <tr>
                                            <th>狀態:</th>
                                            <td>
                                                <span :class="'badge ' + (driver.status === 'open' ? 'bg-success' : 'bg-secondary')">
                                                    {{ driver.status === 'open' ? '在籍中' : '已退籍' }}
                                                </span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- 聯絡資訊 -->
                            <div class="col-md-6">
                                <h5 class="border-bottom pb-2 mb-3">
                                    <i class="bi bi-telephone-fill text-success"></i> 聯絡資訊
                                </h5>
                                
                                <table class="table table-sm">
                                    <tbody>
                                        <tr>
                                            <th style="width: 30%">通訊地址:</th>
                                            <td>{{ driver.contact_address || '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>戶籍地址:</th>
                                            <td>{{ driver.residence_address || '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>住家電話:</th>
                                            <td>{{ driver.home_phone || '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>手機號碼1:</th>
                                            <td>{{ driver.mobile_phone1 || '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>手機號碼2:</th>
                                            <td>{{ driver.mobile_phone2 || '-' }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- 緊急聯絡人 -->
                            <div class="col-md-6">
                                <h5 class="border-bottom pb-2 mb-3">
                                    <i class="bi bi-person-exclamation text-danger"></i> 緊急聯絡人
                                </h5>
                                
                                <table class="table table-sm">
                                    <tbody>
                                        <tr>
                                            <th style="width: 30%">緊急聯絡人:</th>
                                            <td>{{ driver.emergency_contact || '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>聯絡電話:</th>
                                            <td>{{ driver.emergency_phone || '-' }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- 日期資訊 -->
                            <div class="col-md-6">
                                <h5 class="border-bottom pb-2 mb-3">
                                    <i class="bi bi-calendar-fill text-info"></i> 日期資訊
                                </h5>
                                
                                <table class="table table-sm">
                                    <tbody>
                                        <tr>
                                            <th style="width: 30%">入籍日期:</th>
                                            <td>{{ formatDate(driver.registration_date) }}</td>
                                        </tr>
                                        <tr>
                                            <th>退籍日期:</th>
                                            <td>{{ formatDate(driver.deregistration_date) }}</td>
                                        </tr>
                                        <tr>
                                            <th>加入車隊日期:</th>
                                            <td>{{ formatDate(driver.fleet_join_date) }}</td>
                                        </tr>
                                        <tr>
                                            <th>退出車隊日期:</th>
                                            <td>{{ formatDate(driver.fleet_leave_date) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- 證照資訊 -->
                            <div class="col-md-12">
                                <h5 class="border-bottom pb-2 mb-3">
                                    <i class="bi bi-card-list text-warning"></i> 證照資訊
                                </h5>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="card border-left-warning">
                                            <div class="card-body">
                                                <h6 class="card-title">駕照</h6>
                                                <p class="card-text">
                                                    <strong>到期日:</strong> 
                                                    <span v-if="driver.license_expire_date" 
                                                          :class="getLicenseExpireClass(driver.license_days_remaining)">
                                                        {{ formatDate(driver.license_expire_date) }}
                                                    </span>
                                                    <span v-else class="text-muted">未設定</span>
                                                </p>
                                                <p v-if="driver.license_days_remaining !== null" class="card-text">
                                                    <strong>剩餘天數:</strong>
                                                    <span :class="getLicenseExpireClass(driver.license_days_remaining)">
                                                        {{ driver.license_days_remaining }} 天
                                                    </span>
                                                    <span v-if="driver.license_days_remaining <= 30 && driver.license_days_remaining >= 0" 
                                                          class="badge bg-warning ms-2">即將到期</span>
                                                    <span v-if="driver.license_days_remaining < 0" 
                                                          class="badge bg-danger ms-2">已過期</span>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="card border-left-info">
                                            <div class="card-body">
                                                <h6 class="card-title">職業駕駛執照</h6>
                                                <p class="card-text">
                                                    <strong>到期日:</strong>
                                                    <span v-if="driver.professional_license_expire_date" 
                                                          :class="getLicenseExpireClass(driver.professional_license_days_remaining)">
                                                        {{ formatDate(driver.professional_license_expire_date) }}
                                                    </span>
                                                    <span v-else class="text-muted">未設定</span>
                                                </p>
                                                <p v-if="driver.professional_license_days_remaining !== null" class="card-text">
                                                    <strong>剩餘天數:</strong>
                                                    <span :class="getLicenseExpireClass(driver.professional_license_days_remaining)">
                                                        {{ driver.professional_license_days_remaining }} 天
                                                    </span>
                                                    <span v-if="driver.professional_license_days_remaining <= 30 && driver.professional_license_days_remaining >= 0" 
                                                          class="badge bg-warning ms-2">即將到期</span>
                                                    <span v-if="driver.professional_license_days_remaining < 0" 
                                                          class="badge bg-danger ms-2">已過期</span>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 備註資訊 -->
                            <div class="col-md-12 mt-3" v-if="driver.notes">
                                <h5 class="border-bottom pb-2 mb-3">
                                    <i class="bi bi-chat-text-fill text-info"></i> 備註
                                </h5>
                                
                                <div class="card">
                                    <div class="card-body">
                                        <p class="card-text">{{ driver.notes }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- 系統資訊 -->
                            <div class="col-md-12 mt-3">
                                <h5 class="border-bottom pb-2 mb-3">
                                    <i class="bi bi-info-circle-fill text-secondary"></i> 系統資訊
                                </h5>
                                
                                <div class="row">
                                    <div class="col-md-4">
                                        <small class="text-muted">建立時間:</small><br>
                                        <span>{{ formatDateTime(driver.created_at) }}</span>
                                    </div>
                                    <div class="col-md-4">
                                        <small class="text-muted">最後更新:</small><br>
                                        <span>{{ formatDateTime(driver.updated_at) }}</span>
                                    </div>
                                    <div class="col-md-4">
                                        <small class="text-muted">駕駛編號:</small><br>
                                        <span>#{{ driver.id }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="btn-group">
                            <Link :href="route('admin.drivers.edit', driver.id)" class="btn btn-warning">
                                <i class="bi bi-pencil"></i> 編輯資料
                            </Link>
                            <button @click="confirmDelete" class="btn btn-danger">
                                <i class="bi bi-trash"></i> 刪除駕駛
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 刪除確認 Modal -->
        <div class="modal fade" id="deleteModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">確認刪除</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>確定要刪除駕駛 <strong>{{ driver.name }}</strong> 嗎？</p>
                        <p class="text-muted small">此操作無法復原。</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                        <button type="button" class="btn btn-danger" @click="deleteDriver">確認刪除</button>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { onMounted } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
    driver: Object
})

let deleteModal = null

const formatDate = (dateString) => {
    if (!dateString) return '-'
    return new Date(dateString).toLocaleDateString('zh-TW')
}

const formatDateTime = (dateTimeString) => {
    if (!dateTimeString) return '-'
    return new Date(dateTimeString).toLocaleString('zh-TW')
}

const getLicenseExpireClass = (daysRemaining) => {
    if (daysRemaining === null) return ''
    if (daysRemaining < 0) return 'text-danger fw-bold'
    if (daysRemaining <= 30) return 'text-warning fw-bold'
    return 'text-success'
}

const confirmDelete = () => {
    deleteModal.show()
}

const deleteDriver = () => {
    router.delete(route('admin.drivers.destroy', props.driver.id), {
        onSuccess: () => {
            deleteModal.hide()
        }
    })
}

onMounted(() => {
    setTimeout(() => {
        const modalElement = document.getElementById('deleteModal')
        if (modalElement && window.bootstrap) {
            deleteModal = new window.bootstrap.Modal(modalElement)
        }
    }, 100)
})
</script>

<style scoped>
.border-left-warning {
    border-left: 4px solid #ffc107;
}

.border-left-info {
    border-left: 4px solid #17a2b8;
}

.card-body h6 {
    color: #495057;
    margin-bottom: 1rem;
}
</style>