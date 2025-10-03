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
            <i class="bi bi-person-fill text-primary"></i>
            駕駛詳細資料 - {{ driver.name }}
          </h5>
          <button
            type="button"
            class="btn-close"
            @click="close"
          ></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <!-- 基本資料 -->
            <div class="col-md-6">
              <h6 class="border-bottom pb-2 mb-3">
                <i class="bi bi-person-fill text-primary"></i> 基本資料
              </h6>
              <table class="table table-sm table-borderless">
                <tbody>
                  <tr>
                    <th style="width: 40%">姓名:</th>
                    <td>{{ driver.name }}</td>
                  </tr>
                  <tr>
                    <th>身分證字號:</th>
                    <td>{{ driver.id_number || '-' }}</td>
                  </tr>
                  <tr>
                    <th>生日:</th>
                    <td>{{ formatDate(driver.birthday) }}</td>
                  </tr>
                  <tr>
                    <th>公司類別:</th>
                    <td>{{ driver.company_category?.name || '-' }}</td>
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
              <h6 class="border-bottom pb-2 mb-3">
                <i class="bi bi-telephone-fill text-success"></i> 聯絡資訊
              </h6>
              <table class="table table-sm table-borderless">
                <tbody>
                  <tr>
                    <th style="width: 40%">手機號碼1:</th>
                    <td>{{ driver.mobile_phone1 || '-' }}</td>
                  </tr>
                  <tr>
                    <th>手機號碼2:</th>
                    <td>{{ driver.mobile_phone2 || '-' }}</td>
                  </tr>
                  <tr>
                    <th>住家電話:</th>
                    <td>{{ driver.home_phone || '-' }}</td>
                  </tr>
                  <tr>
                    <th>通訊地址:</th>
                    <td>{{ driver.contact_address || '-' }}</td>
                  </tr>
                  <tr>
                    <th>戶籍地址:</th>
                    <td>{{ driver.residence_address || '-' }}</td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- 緊急聯絡人 -->
            <div class="col-md-6">
              <h6 class="border-bottom pb-2 mb-3">
                <i class="bi bi-person-exclamation text-danger"></i> 緊急聯絡人
              </h6>
              <table class="table table-sm table-borderless">
                <tbody>
                  <tr>
                    <th style="width: 40%">緊急聯絡人:</th>
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
              <h6 class="border-bottom pb-2 mb-3">
                <i class="bi bi-calendar-fill text-info"></i> 日期資訊
              </h6>
              <table class="table table-sm table-borderless">
                <tbody>
                  <tr>
                    <th style="width: 40%">入籍日期:</th>
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
            <div class="col-md-12 mt-3">
              <h6 class="border-bottom pb-2 mb-3">
                <i class="bi bi-card-list text-warning"></i> 證照資訊
              </h6>
              <div class="row">
                <div class="col-md-6">
                  <div class="card border-start border-warning border-3">
                    <div class="card-body">
                      <h6 class="card-title">
                        <i class="bi bi-credit-card"></i> 駕照
                      </h6>
                      <p class="card-text mb-1">
                        <strong>到期日:</strong>
                        <span v-if="driver.license_expire_date" :class="getLicenseExpireClass(driver.license_days_remaining)">
                          {{ formatDate(driver.license_expire_date) }}
                        </span>
                        <span v-else class="text-muted">未設定</span>
                      </p>
                      <p v-if="driver.license_days_remaining !== null && driver.license_days_remaining !== undefined" class="card-text mb-0">
                        <strong>剩餘天數:</strong>
                        <span :class="getLicenseExpireClass(driver.license_days_remaining)">
                          {{ driver.license_days_remaining }} 天
                        </span>
                        <span v-if="driver.license_days_remaining <= 30 && driver.license_days_remaining >= 0" class="badge bg-warning ms-2">即將到期</span>
                        <span v-if="driver.license_days_remaining < 0" class="badge bg-danger ms-2">已過期</span>
                      </p>
                    </div>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="card border-start border-info border-3">
                    <div class="card-body">
                      <h6 class="card-title">
                        <i class="bi bi-award"></i> 職業駕駛執照
                      </h6>
                      <p class="card-text mb-1">
                        <strong>到期日:</strong>
                        <span v-if="driver.professional_license_expire_date" :class="getLicenseExpireClass(driver.professional_license_days_remaining)">
                          {{ formatDate(driver.professional_license_expire_date) }}
                        </span>
                        <span v-else class="text-muted">未設定</span>
                      </p>
                      <p v-if="driver.professional_license_days_remaining !== null && driver.professional_license_days_remaining !== undefined" class="card-text mb-0">
                        <strong>剩餘天數:</strong>
                        <span :class="getLicenseExpireClass(driver.professional_license_days_remaining)">
                          {{ driver.professional_license_days_remaining }} 天
                        </span>
                        <span v-if="driver.professional_license_days_remaining <= 30 && driver.professional_license_days_remaining >= 0" class="badge bg-warning ms-2">即將到期</span>
                        <span v-if="driver.professional_license_days_remaining < 0" class="badge bg-danger ms-2">已過期</span>
                      </p>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- 備註資訊 -->
            <div v-if="driver.notes" class="col-md-12 mt-3">
              <h6 class="border-bottom pb-2 mb-3">
                <i class="bi bi-chat-text-fill text-info"></i> 備註
              </h6>
              <div class="card bg-light">
                <div class="card-body">
                  <p class="card-text mb-0">{{ driver.notes }}</p>
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
  driver: { type: Object, required: true }
})

const emit = defineEmits(['close'])

const close = () => {
  emit('close')
}

const formatDate = (dateString) => {
  if (!dateString) return '-'
  return new Date(dateString).toLocaleDateString('zh-TW')
}

const getLicenseExpireClass = (daysRemaining) => {
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
