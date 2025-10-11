<script setup>
import { watch, onMounted } from 'vue'

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    vehicle: {
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
const formatDate = (dateString) => {
    if (!dateString) return '-'
    return dateString
}

// 取得狀態文字
const getStatusText = (status) => {
    return status === 'active' ? '在籍' : '退籍'
}

// 取得狀態 Badge 類別
const getStatusBadgeClass = (status) => {
    return status === 'active' ? 'badge bg-success' : 'badge bg-secondary'
}

watch(() => props.show, (newVal) => {
    setTimeout(() => {
        const modalElement = document.getElementById('vehicleDetailModal')
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
        const modalElement = document.getElementById('vehicleDetailModal')
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
        id="vehicleDetailModal"
        class="modal fade"
        tabindex="-1"
        aria-labelledby="vehicleDetailModalLabel"
        aria-hidden="true"
    >
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 id="vehicleDetailModalLabel" class="modal-title">
                        <i class="bi bi-car-front-fill"></i> 車輛詳細資料
                    </h5>
                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close"
                    ></button>
                </div>
                <div v-if="vehicle" class="modal-body">
                    <!-- 基本資料 -->
                    <div class="card mb-3">
                        <div class="card-header" style="background-color:#B3D9D9;">
                            <h6 class="mb-0">
                                <i class="bi bi-card-text"></i> 基本資料
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <dl class="row mb-0">
                                        <dt class="col-sm-5">車牌號碼</dt>
                                        <dd class="col-sm-7">{{ vehicle.license_number || '-' }}</dd>

                                        <dt class="col-sm-5">車主名稱</dt>
                                        <dd class="col-sm-7">{{ vehicle.owner_name || '-' }}</dd>

                                        <dt class="col-sm-5">車輛類型</dt>
                                        <dd class="col-sm-7">{{ vehicle.vehicle_type || '-' }}</dd>
                                    </dl>
                                </div>
                                <div class="col-md-4">
                                    <dl class="row mb-0">
                                        <dt class="col-sm-5">地址</dt>
                                        <dd class="col-sm-7">{{ vehicle.address || '-' }}</dd>

                                        <dt class="col-sm-5">車輛顏色</dt>
                                        <dd class="col-sm-7">{{ vehicle.vehicle_color || '-' }}</dd>
                                    </dl>
                                </div>
                                <div class="col-md-4">
                                    <dl class="row mb-0">
                                        <dt class="col-sm-5">狀態</dt>
                                        <dd class="col-sm-7">
                                            <span :class="getStatusBadgeClass(vehicle.vehicle_status)">
                                                {{ getStatusText(vehicle.vehicle_status) }}
                                            </span>
                                        </dd>

                                        <dt class="col-sm-5">公司類別</dt>
                                        <dd class="col-sm-7">{{ vehicle.company_category?.name || '-' }}</dd>

                                        <dt class="col-sm-5">所屬公司</dt>
                                        <dd class="col-sm-7">{{ vehicle.company?.name || '-' }}</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 製造資訊 -->
                    <div class="card mb-3">
                        <div class="card-header" style="background-color:#B3D9D9;">
                            <h6 class="mb-0">
                                <i class="bi bi-gear-fill"></i> 製造資訊
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <dl class="row mb-0">
                                        <dt class="col-sm-5">廠牌</dt>
                                        <dd class="col-sm-7">{{ vehicle.brand || '-' }}</dd>

                                        <dt class="col-sm-5">車款</dt>
                                        <dd class="col-sm-7">{{ vehicle.vehicle_model || '-' }}</dd>

                                        <dt class="col-sm-5">出廠年月</dt>
                                        <dd class="col-sm-7">
                                            <span v-if="vehicle.manufacture_year && vehicle.manufacture_month">
                                                {{ vehicle.manufacture_year }}/{{ String(vehicle.manufacture_month).padStart(2, '0') }}
                                            </span>
                                            <span v-else>-</span>
                                        </dd>
                                    </dl>
                                </div>
                                <div class="col-md-4">
                                    <dl class="row mb-0">
                                        <dt class="col-sm-5">車輛形式</dt>
                                        <dd class="col-sm-7">{{ vehicle.vehicle_form || '-' }}</dd>

                                        <dt class="col-sm-5">排氣量</dt>
                                        <dd class="col-sm-7">{{ Math.round(Number(vehicle.engine_displacement)) ? `${Math.round(Number(vehicle.engine_displacement))} cc` : '-' }}</dd>

                                        <dt class="col-sm-5">燃料種類</dt>
                                        <dd class="col-sm-7">{{ vehicle.fuel_type || '-' }}</dd>
                                    </dl>
                                </div>
                                <div class="col-md-4">
                                    <dl class="row mb-0">
                                        <dt class="col-sm-5">載運人數</dt>
                                        <dd class="col-sm-7">{{ vehicle.passenger_capacity ? `${vehicle.passenger_capacity} 人` : '-' }}</dd>

                                        <dt class="col-sm-5">產權類別</dt>
                                        <dd class="col-sm-7">{{ vehicle.property_type || '-' }}</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 車身資訊 -->
                    <div class="card mb-3">
                        <div class="card-header" style="background-color:#B3D9D9;">
                            <h6 class="mb-0">
                                <i class="bi bi-123"></i> 車身資訊
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <dl class="row mb-0">
                                        <dt class="col-sm-4">引擎號碼</dt>
                                        <dd class="col-sm-8">{{ vehicle.engine_number || '-' }}</dd>

                                        <dt class="col-sm-4">車身號碼</dt>
                                        <dd class="col-sm-8">{{ vehicle.chassis_number || '-' }}</dd>
                                    </dl>
                                </div>
                                <div class="col-md-6">
                                    <dl class="row mb-0">
                                        <dt class="col-sm-4">車隊名稱</dt>
                                        <dd class="col-sm-8">{{ vehicle.fleet_name || '-' }}</dd>

                                        <dt class="col-sm-4">車隊編號</dt>
                                        <dd class="col-sm-8">{{ vehicle.fleet_number || '-' }}</dd>
                                    </dl>
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
                                        <dt class="col-sm-4">發照日期</dt>
                                        <dd class="col-sm-8">{{ formatDate(vehicle.license_issue_date) }}</dd>

                                        <dt class="col-sm-4">檢驗日期</dt>
                                        <dd class="col-sm-8">
                                            {{ formatDate(vehicle.inspection_date) }}
                                            <span v-if="vehicle.inspection_days_remaining !== null" class="ms-2">
                                                <span v-if="vehicle.inspection_days_remaining < 0" class="badge bg-danger">
                                                    已過期 {{ Math.abs(vehicle.inspection_days_remaining) }} 天
                                                </span>
                                                <span v-else-if="vehicle.inspection_days_remaining <= 30" class="badge bg-warning">
                                                    剩餘 {{ vehicle.inspection_days_remaining }} 天
                                                </span>
                                            </span>
                                        </dd>
                                    </dl>
                                </div>
                                <div class="col-md-6">
                                    <dl class="row mb-0">
                                        <dt class="col-sm-4">入籍日期</dt>
                                        <dd class="col-sm-8">{{ formatDate(vehicle.registration_date) }}</dd>

                                        <dt class="col-sm-4">退籍日期</dt>
                                        <dd class="col-sm-8">{{ formatDate(vehicle.deregistration_date) }}</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 綁定駕駛 -->
                    <div v-if="vehicle.drivers && vehicle.drivers.length > 0" class="card mb-3">
                        <div class="card-header" style="background-color:#B3D9D9;">
                            <h6 class="mb-0">
                                <i class="bi bi-person-fill"></i> 綁定駕駛
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>姓名</th>
                                            <th>身分證字號</th>
                                            <th>手機號碼</th>
                                            <th>公司類別</th>
                                            <th>狀態</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="driver in vehicle.drivers" :key="driver.id">
                                            <td>{{ driver.name }}</td>
                                            <td>{{ driver.id_number ? driver.id_number.substring(0, 3) + '****' + driver.id_number.substring(7) : '-' }}</td>
                                            <td>{{ driver.mobile_phone1 || '-' }}</td>
                                            <td>{{ driver.company_category?.name || '-' }}</td>
                                            <td>
                                                <span :class="driver.status === 'open' ? 'badge bg-success' : 'badge bg-secondary'">
                                                    {{ driver.status === 'open' ? '在籍' : '退籍' }}
                                                </span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- 備註 -->
                    <div v-if="vehicle.notes" class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="bi bi-sticky"></i> 備註
                            </h6>
                        </div>
                        <div class="card-body">
                            <p class="mb-0">{{ vehicle.notes }}</p>
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
