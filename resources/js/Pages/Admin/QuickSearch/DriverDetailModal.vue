<script setup>
import { watch, onMounted } from 'vue'

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    driver: {
        type: Object,
        default: null,
    },
})

const emit = defineEmits(['update:show'])

let modalInstance = null

const closeModal = () => {
    emit('update:show', false)
}

// 格式化日期
const formatDate = (date) => {
  if (!date) return '-'
  const d = new Date(date)
  if (isNaN(d)) return '-'
  const rocYear = d.getFullYear() - 1911
  const month = String(d.getMonth() + 1).padStart(2, '0')
  const day = String(d.getDate()).padStart(2, '0')
  return `${rocYear}-${month}-${day}`
}

// 取得狀態文字
const getStatusText = (status) => {
    const statusMap = {
        'open': '在籍中',
        'close': '已退籍',
        'bad_debt': '呆帳'
    }
    return statusMap[status] || '未知'
}

// 取得狀態 Badge 類別
const getStatusBadgeClass = (status) => {
    const classMap = {
        'open': 'badge bg-success',
        'close': 'badge bg-secondary',
        'bad_debt': 'badge bg-danger'
    }
    return classMap[status] || 'badge bg-secondary'
}

watch(() => props.show, (newVal) => {
    setTimeout(() => {
        const modalElement = document.getElementById('driverDetailModal')
        if (!modalElement) return

        if (!modalInstance && window.bootstrap) {
            modalInstance = new window.bootstrap.Modal(modalElement)
        }

        if (newVal && modalInstance) {
            modalInstance.show()
        } else if (!newVal && modalInstance) {
            modalInstance.hide()
        }
    }, 100)
})

onMounted(() => {
    setTimeout(() => {
        const modalElement = document.getElementById('driverDetailModal')
        if (modalElement && window.bootstrap) {
            modalInstance = new window.bootstrap.Modal(modalElement)

            modalElement.addEventListener('hidden.bs.modal', () => {
                closeModal()
            })
        }
    }, 100)
})



</script>

<template>
    <div
        id="driverDetailModal"
        class="modal fade"
        tabindex="-1"
        aria-labelledby="driverDetailModalLabel"
        aria-hidden="true"
    >
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 id="driverDetailModalLabel" class="modal-title">
                        <i class="bi bi-person-fill"></i> 駕駛詳細資料
                    </h5>
                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close"
                    ></button>
                </div>
                <div v-if="driver" class="modal-body">
                    <!-- 基本資料 -->
                    <div class="card mb-3">
                        <div class="card-header" style="background-color:#B3D9D9;">
                            <h6 class="mb-0">
                                <i class="bi bi-person-badge"></i> 基本資料
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <dl class="row mb-0">
                                        <dt class="col-sm-4">姓名</dt>
                                        <dd class="col-sm-8">{{ driver.name || '-' }}</dd>

                                        <dt class="col-sm-4">身分證字號</dt>
                                        <dd class="col-sm-8">{{ driver.id_number || '-' }}</dd>

                                        <dt class="col-sm-4">生日</dt>
                                        <dd class="col-sm-8">{{ formatDate(driver.birthday) }}</dd>
                                    </dl>
                                </div>
                                <div class="col-md-4">
                                    <dl class="row mb-0">
                                        <dt class="col-sm-3">聯絡地址</dt>
                                        <dd class="col-sm-9">{{ driver.contact_address || '-' }}</dd>

                                        <dt class="col-sm-3">戶籍地址</dt>
                                        <dd class="col-sm-9">{{ driver.residence_address || '-' }}</dd>
                                    </dl>
                                </div>
                                <div class="col-md-4">
                                    <dl class="row mb-0">
                                        <dt class="col-sm-5">公司類別</dt>
                                        <dd class="col-sm-7">{{ driver.company_category?.name || '-' }}</dd>

                                        <dt class="col-sm-5">狀態</dt>
                                        <dd class="col-sm-7">
                                            <span :class="getStatusBadgeClass(driver.status)">
                                                {{ getStatusText(driver.status) }}
                                            </span>
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 聯絡資訊 -->
                    <div class="card mb-3">
                        <div class="card-header" style="background-color:#B3D9D9;">
                            <h6 class="mb-0">
                                <i class="bi bi-telephone-fill"></i> 聯絡資訊
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <dl class="row mb-0">
                                        <dt class="col-sm-4">手機號碼1</dt>
                                        <dd class="col-sm-8">{{ driver.mobile_phone1 || '-' }}</dd>

                                        <dt class="col-sm-4">手機號碼2</dt>
                                        <dd class="col-sm-8">{{ driver.mobile_phone2 || '-' }}</dd>
                                    </dl>
                                </div>
                                <div class="col-md-4">
                                    <dl class="row mb-0">
                                        <dt class="col-sm-4">住家電話</dt>
                                        <dd class="col-sm-8">{{ driver.home_phone || '-' }}</dd>
                                    </dl>
                                </div>
                                <div class="col-md-4">
                                    <dl class="row mb-0">
                                        <dt class="col-sm-4">緊急聯絡人</dt>
                                        <dd class="col-sm-8">{{ driver.emergency_contact || '-' }}</dd>

                                        <dt class="col-sm-4">緊急聯絡電話</dt>
                                        <dd class="col-sm-8">{{ driver.emergency_phone || '-' }}</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 經常性費用 -->
                    <div v-if="driver.recurring_cost_template" class="card mb-3">
                        <div class="card-header" style="background-color:#B3D9D9;">
                            <h6 class="mb-0">
                                <i class="bi bi-cash-stack"></i> 經常性費用
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row mb-1">
                                <div class="col-md-4">
                                    <dl class="row mb-0">
                                        <dt class="col-sm-4">組合名稱：</dt>
                                        <dd class="col-sm-8">{{ driver.recurring_cost_template.name }}</dd>
                                    </dl>
                                </div>
                                <div class="col-md-4">
                                    <div v-if="driver.recurring_cost_template.description" class="mb-3">
                                        <strong>說明：</strong>{{ driver.recurring_cost_template.description }}
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <dl class="row mb-0">
                                        <dt class="col-sm-4">費用總和：</dt>
                                        <dd class="col-sm-8">
                                            <span class="badge bg-primary">
                                                ${{ driver.recurring_cost_template.total_amount?.toLocaleString() || '0' }}
                                            </span>
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                            <div v-if="driver.recurring_cost_template.items && driver.recurring_cost_template.items.length > 0">
                                <h6 class="mb-2">費用明細：</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th>會計科目</th>
                                                <th>金額</th>
                                                <th>備註</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="item in driver.recurring_cost_template.items" :key="item.id">
                                                <td>{{ item.account_detail?.account_name || '-' }}</td>
                                                <td>${{ item.amount?.toLocaleString() || '0' }}</td>
                                                <td>{{ item.note || '-' }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 日期資訊 -->
                    <div class="card mb-3">
                        <div class="card-header" style="background-color:#B3D9D9;">
                            <h6 class="mb-0">
                                <i class="bi bi-calendar-event"></i> 日期資訊
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <dl class="row mb-0">
                                        <dt class="col-sm-5">入籍日期</dt>
                                        <dd class="col-sm-7">{{ formatDate(driver.registration_date) }}</dd>

                                        <dt class="col-sm-5">退籍日期</dt>
                                        <dd class="col-sm-7">{{ formatDate(driver.deregistration_date) }}</dd>

                                        <dt class="col-sm-5">車隊加入日期</dt>
                                        <dd class="col-sm-7">{{ formatDate(driver.fleet_join_date) }}</dd>

                                        <dt class="col-sm-5">車隊離開日期</dt>
                                        <dd class="col-sm-7">{{ formatDate(driver.fleet_leave_date) }}</dd>
                                    </dl>
                                </div>
                                <div class="col-md-6">
                                    <dl class="row mb-0">
                                        <dt class="col-sm-5">駕照到期日</dt>
                                        <dd class="col-sm-7">
                                            {{ formatDate(driver.license_expire_date) }}
                                            <span v-if="driver.license_days_remaining !== null" class="ms-2">
                                                <span v-if="driver.license_days_remaining < 0" class="badge bg-danger">
                                                    已過期 {{ Math.abs(driver.license_days_remaining) }} 天
                                                </span>
                                                <span v-else-if="driver.license_days_remaining <= 30" class="badge bg-warning">
                                                    剩餘 {{ driver.license_days_remaining }} 天
                                                </span>
                                            </span>
                                        </dd>

                                        <dt class="col-sm-5">執登到期日</dt>
                                        <dd class="col-sm-7">
                                            {{ formatDate(driver.professional_license_expire_date) }}
                                            <span v-if="driver.professional_license_days_remaining !== null" class="ms-2">
                                                <span v-if="driver.professional_license_days_remaining < 0" class="badge bg-danger">
                                                    已過期 {{ Math.abs(driver.professional_license_days_remaining) }} 天
                                                </span>
                                                <span v-else-if="driver.professional_license_days_remaining <= 30" class="badge bg-warning">
                                                    剩餘 {{ driver.professional_license_days_remaining }} 天
                                                </span>
                                            </span>
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 綁定車輛 -->
                    <div v-if="driver.vehicles && driver.vehicles.length > 0" class="card mb-3">
                        <div class="card-header" style="background-color:#B3D9D9;">
                            <h6 class="mb-0">
                                <i class="bi bi-car-front-fill"></i> 綁定車輛
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>車牌號碼</th>
                                            <th>車主名稱</th>
                                            <th>車輛類型</th>
                                            <th>公司類別</th>
                                            <th>狀態</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="vehicle in driver.vehicles" :key="vehicle.id">
                                            <td>{{ vehicle.license_number }}</td>
                                            <td>{{ vehicle.owner_name || '-' }}</td>
                                            <td>{{ vehicle.vehicle_type || '-' }}</td>
                                            <td>{{ vehicle.company_category?.name || '-' }}</td>
                                            <td>
                                                <span :class="vehicle.vehicle_status === 'active' ? 'badge bg-success' : 'badge bg-secondary'">
                                                    {{ vehicle.vehicle_status === 'active' ? '在籍' : '退籍' }}
                                                </span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- 備註 -->
                    <div v-if="driver.notes" class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="bi bi-sticky"></i> 備註
                            </h6>
                        </div>
                        <div class="card-body">
                            <p class="mb-0">{{ driver.notes }}</p>
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
</template>
