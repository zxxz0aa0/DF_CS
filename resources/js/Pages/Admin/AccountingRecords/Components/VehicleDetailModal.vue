<template>
  <div
    v-if="show"
    class="modal fade show d-block"
    tabindex="-1"
    style="background-color: rgba(0,0,0,0.5);"
    @click.self="close"
  >
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">
            <i class="bi bi-car-front-fill text-primary"></i>
            車輛詳細資料 - {{ vehicle.license_number }}
          </h5>
          <button
            type="button"
            class="btn-close"
            @click="close"
          ></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <!-- 基本資訊 -->
            <div class="col-md-6">
              <h6 class="border-bottom pb-2 mb-3">
                <i class="bi bi-info-circle-fill text-primary"></i> 基本資訊
              </h6>
              <table class="table table-sm table-borderless">
                <tbody>
                  <tr>
                    <th style="width: 40%">車牌號碼:</th>
                    <td>{{ vehicle.license_number }}</td>
                  </tr>
                  <tr v-if="vehicle.replacement_license">
                    <th>替補車號:</th>
                    <td>{{ vehicle.replacement_license }}</td>
                  </tr>
                  <tr v-if="vehicle.fleet_number">
                    <th>車隊編號:</th>
                    <td>{{ vehicle.fleet_number }}</td>
                  </tr>
                  <tr v-if="vehicle.company_category">
                    <th>公司類別:</th>
                    <td>{{ vehicle.company_category?.name || '-' }}</td>
                  </tr>
                  <tr>
                    <th>車主名稱:</th>
                    <td>{{ vehicle.owner_name || '-' }}</td>
                  </tr>
                  <tr v-if="vehicle.vehicle_type">
                    <th>車輛類型:</th>
                    <td>{{ vehicle.vehicle_type }}</td>
                  </tr>
                  <tr v-if="vehicle.address">
                    <th>地址:</th>
                    <td>{{ vehicle.address }}</td>
                  </tr>
                  <tr v-if="vehicle.registration_date">
                    <th>入籍日期:</th>
                    <td>{{ vehicle.registration_date }}</td>
                  </tr>
                  <tr v-if="vehicle.vehicle_status">
                    <th>狀態:</th>
                    <td>
                      <span :class="'badge ' + (vehicle.vehicle_status === 'active' ? 'bg-success' : 'bg-secondary')">
                        {{ vehicle.status_text || (vehicle.vehicle_status === 'active' ? '在籍中' : '已退籍') }}
                      </span>
                    </td>
                  </tr>
                  <tr v-if="vehicle.deregistration_date">
                    <th>退籍日期:</th>
                    <td>{{ vehicle.deregistration_date }}</td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- 車輛製造資訊 -->
            <div class="col-md-6">
              <h6 class="border-bottom pb-2 mb-3">
                <i class="bi bi-wrench text-success"></i> 車輛製造資訊
              </h6>
              <table class="table table-sm table-borderless">
                <tbody>
                  <tr v-if="vehicle.brand">
                    <th style="width: 40%">車輛廠牌:</th>
                    <td>{{ vehicle.brand }}</td>
                  </tr>
                  <tr v-if="vehicle.vehicle_form">
                    <th>車輛款式:</th>
                    <td>{{ vehicle.vehicle_model}}</td>
                  </tr>
                  <tr v-if="vehicle.manufacture_year || vehicle.manufacture_month">
                    <th>出廠年月:</th>
                    <td>{{ formatManufactureDate(vehicle.manufacture_year, vehicle.manufacture_month) }}</td>
                  </tr>
                  <tr v-if="vehicle.engine_displacement">
                    <th>排氣量:</th>
                    <td>{{ Math.round(Number(vehicle.engine_displacement)) }} cc</td>
                  </tr>
                  <tr v-if="vehicle.fuel_type">
                    <th>燃料種類:</th>
                    <td>{{ vehicle.fuel_type }}</td>
                  </tr>
                  <tr v-if="vehicle.color">
                    <th>車身顏色:</th>
                    <td>{{ vehicle.color }}</td>
                  </tr>
                  <tr v-if="vehicle.engine_number">
                    <th>引擎號碼:</th>
                    <td>{{ vehicle.engine_number }}</td>
                  </tr>
                  <tr v-if="vehicle.chassis_number">
                    <th>車身號碼:</th>
                    <td>{{ vehicle.chassis_number }}</td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- 證照資訊 -->
            <div class="col-md-12 mt-3">
              <h6 class="border-bottom pb-2 mb-3">
                <i class="bi bi-card-list text-warning"></i> 證照資訊
              </h6>
              <div class="row">
                <div class="col-md-6" v-if="vehicle.vehicle_registration_date || vehicle.vehicle_license_number">
                  <div class="card border-start border-warning border-3">
                    <div class="card-body">
                      <h6 class="card-title">
                        <i class="bi bi-file-text"></i> 行照資訊
                      </h6>
                      <p class="card-text mb-1" v-if="vehicle.vehicle_license_number">
                        <strong>行照號碼:</strong> {{ vehicle.vehicle_license_number }}
                      </p>
                      <p class="card-text mb-0" v-if="vehicle.vehicle_registration_date">
                        <strong>登記日期:</strong> {{ formatDate(vehicle.vehicle_registration_date) }}
                      </p>
                    </div>
                  </div>
                </div>

                <div class="col-md-6" v-if="vehicle.insurance_expire_date">
                  <div class="card border-start border-info border-3">
                    <div class="card-body">
                      <h6 class="card-title">
                        <i class="bi bi-shield-check"></i> 保險資訊
                      </h6>
                      <p class="card-text mb-1">
                        <strong>保險到期日:</strong>
                        <span :class="getInsuranceExpireClass(vehicle.insurance_days_remaining)">
                          {{ formatDate(vehicle.insurance_expire_date) }}
                        </span>
                      </p>
                      <p v-if="vehicle.insurance_days_remaining !== null && vehicle.insurance_days_remaining !== undefined" class="card-text mb-0">
                        <strong>剩餘天數:</strong>
                        <span :class="getInsuranceExpireClass(vehicle.insurance_days_remaining)">
                          {{ vehicle.insurance_days_remaining }} 天
                        </span>
                        <span v-if="vehicle.insurance_days_remaining <= 30 && vehicle.insurance_days_remaining >= 0" class="badge bg-warning ms-2">即將到期</span>
                        <span v-if="vehicle.insurance_days_remaining < 0" class="badge bg-danger ms-2">已過期</span>
                      </p>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- 備註資訊 -->
            <div v-if="vehicle.notes" class="col-md-12 mt-3">
              <h6 class="border-bottom pb-2 mb-3">
                <i class="bi bi-chat-text-fill text-info"></i> 備註
              </h6>
              <div class="card bg-light">
                <div class="card-body">
                  <p class="card-text mb-0">{{ vehicle.notes }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" @click="close">
            <i class="bi bi-x-circle"></i> 關閉
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  show: { type: Boolean, default: false },
  vehicle: { type: Object, required: true }
})

const emit = defineEmits(['close'])

const close = () => {
  emit('close')
}

const formatDate = (dateString) => {
  if (!dateString) return '-'
  return new Date(dateString).toLocaleDateString('zh-TW')
}

const formatManufactureDate = (year, month) => {
  if (!year && !month) return '-'
  if (year && month) return `${year}年${month}月`
  if (year) return `${year}年`
  if (month) return `${month}月`
  return '-'
}

const getInsuranceExpireClass = (daysRemaining) => {
  if (daysRemaining === null || daysRemaining === undefined) return ''
  if (daysRemaining < 0) return 'text-danger fw-bold'
  if (daysRemaining <= 30) return 'text-warning fw-bold'
  return 'text-success'
}

// 支援 Esc 鍵關閉
document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape' && props.show) {
    close()
  }
})
</script>

<style scoped>
.modal {
  display: block;
}

.card-title {
  color: #495057;
  margin-bottom: 0.75rem;
  font-size: 0.95rem;
}

th {
  font-weight: 600;
  color: #6c757d;
}

.border-start {
  border-left-width: 3px !important;
}
</style>
