<template>
  <AdminLayout :user="$page.props.auth.user">
    <div class="content-wrapper">
      <div class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h1 class="m-0">支出款項管理</h1>
            </div>
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item">
                  <Link :href="route('admin.dashboard')">儀表板</Link>
                </li>
                <li class="breadcrumb-item active">支出款項管理</li>
              </ol>
            </div>
          </div>
        </div>
      </div>

      <section class="content">
        <div class="">
          <div class="card">
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center" style="background-color:#B3D9D9;">
              <h3 class="card-title mb-2 mb-md-0">
                <i class="bi bi-cash-coin me-2"></i>支出款項列表
              </h3>

              <div class="btn-group btn-group-sm ms-auto">
                <button
                  v-if="permissions.canCreate"
                  type="button"
                  class="btn btn-primary"
                  @click="openCreateModal"
                >
                  <i class="bi bi-plus-circle"></i> 新增
                </button>

                <button
                  v-if="permissions.canExport"
                  type="button"
                  class="btn btn-outline-success"
                  :disabled="isExporting"
                  @click="exportData"
                >
                  <i class="bi bi-download"></i>
                  <span v-if="isExporting"> 匯出中...</span>
                  <span v-else> 匯出</span>
                </button>

                <a
                  v-if="permissions.canImport"
                  :href="route('admin.expense-payments.template')"
                  class="btn btn-outline-secondary"
                >
                  <i class="bi bi-file-earmark-arrow-down"></i> 範本
                </a>

                <button
                  v-if="permissions.canImport"
                  type="button"
                  class="btn btn-outline-info"
                  :disabled="importForm.processing"
                  @click="triggerImport"
                >
                  <i class="bi bi-upload"></i>
                  <span v-if="importForm.processing"> 匯入中...</span>
                  <span v-else> 匯入</span>
                </button>

                <button
                  v-if="permissions.canExport"
                  type="button"
                  class="btn btn-outline-primary"
                  :disabled="selectedIds.length === 0"
                  @click="printSelectedData"
                  title="列印選中的支出款項"
                >
                  <i class="bi bi-printer"></i> 列印選中資料
                </button>
              </div>
            </div>

            <div class="card-body">
              <div v-if="flash.success" class="alert alert-success">
                {{ flash.success }}
              </div>
              <div v-if="flash.error" class="alert alert-danger">
                {{ flash.error }}
              </div>
              <div v-if="importForm.errors && Object.keys(importForm.errors).length" class="alert alert-danger">
                <ul class="mb-0">
                  <li v-for="(message, field) in importForm.errors" :key="field">{{ message }}</li>
                </ul>
              </div>
              <div v-if="flash.warning" class="alert alert-warning">
                {{ flash.warning }}
              </div>
              <div v-if="flashFailures.length" class="alert alert-warning">
                <strong>匯入失敗 {{ flashFailures.length }} 筆</strong>
                <ul class="mb-0 mt-2">
                  <li v-for="(failure, index) in flashFailures" :key="index">
                    第 {{ failure.row ?? '—' }} 列：
                    <span v-if="Array.isArray(failure.errors)">
                      {{ failure.errors.join('；') }}
                    </span>
                    <span v-else>
                      {{ failure.errors }}
                    </span>
                  </li>
                </ul>
              </div>

              <form class="bg-light border rounded p-3 mb-3" @submit.prevent="submitFilters">
                <div class="row g-3">
                  <div class="col-lg-3 col-md-4 col-sm-6">
                    <!--<label class="form-label">關鍵字</label>-->
                    <input
                      v-model="filterForm.keyword"
                      type="text"
                      class="form-control"
                      placeholder="隊員編號 / 姓名 / 車牌 / 款項名稱"
                    >
                  </div>

                  <div class="col-lg-2 col-md-4 col-sm-6">
                    <!--<label class="form-label">狀態</label>-->
                    <select v-model="filterForm.status" class="form-select">
                      <option value="">全部</option>
                      <option value="pending">未支付</option>
                      <option value="paid">已支付</option>
                    </select>
                  </div>

                  <div class="col-lg-2 col-md-4 col-sm-6">
                    <!--<label class="form-label">每頁筆數</label>-->
                    <select v-model.number="filterForm.per_page" class="form-select">
                      <option v-for="option in perPageOptions" :key="option" :value="option">
                       顯示 {{ option }}筆
                      </option>
                    </select>
                  </div>

                  <div class="col-lg-4 col-md-8 col-sm-12 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-outline-primary">
                      <i class="bi bi-search"></i> 套用
                    </button>
                    <button type="button" class="btn btn-outline-secondary" @click="resetFilters">
                      清除
                    </button>
                  </div>
                </div>
              </form>

              <div class="row mb-3">
                <div class="col-md-2">
                    <div class="col-md-12 d-flex justify-content-center align-items-center">
                        <div class="btn-group btn-group-lg" role="group">
                            <button
                            v-if="permissions.canEdit"
                            type="button"
                            class="btn btn-outline-success mt-1"
                            :disabled="selectedIds.length === 0"
                            @click="openBulkModal('paid')"
                            >
                            <i class="bi bi-check2-circle"></i> 標記已支付
                            </button>
                            <!--<button
                            v-if="permissions.canEdit"
                            type="button"
                            class="btn btn-outline-secondary"
                            :disabled="selectedIds.length === 0"
                            @click="openBulkModal('pending')"
                            >
                            <i class="bi bi-arrow-counterclockwise"></i> 改為未支付
                            </button>-->
                        </div>
                     </div>
                </div>
                <div class="col-md-10">
                  <div class="alert alert-info mb-0 h5">
                    <strong>統計：</strong>
                    未支付 {{ totals.pending.toLocaleString(undefined, currencyFormat) }} 元 ·
                    已支付 {{ totals.paid.toLocaleString(undefined, currencyFormat) }} 元
                  </div>
                </div>
              </div>

              <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                  <thead class="table-light">
                    <tr>
                      <th style="width: 40px;">
                        <input
                          type="checkbox"
                          :checked="isCurrentPageAllSelected"
                          @change="toggleSelectAll($event)"
                        >
                      </th>
                      <th>交易日期</th>
                      <th>時間</th>
                      <!--<th>公司種類</th>-->
                      <th>隊員編號</th>
                      <th>隊員姓名</th>
                      <th>車牌</th>
                      <th>款項</th>
                      <th class="text-end">支付金額</th>
                      <th class="text-end">應扣款</th>
                      <th class="text-end">實付金額</th>
                      <th>狀態</th>
                      <th>支付日期</th>
                      <th>建立日期</th>
                      <th>操作</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-if="payments.data.length === 0">
                      <td colspan="15" class="text-center text-muted">暫無資料</td>
                    </tr>
                    <tr v-for="payment in payments.data" :key="payment.id">
                      <td>
                        <input
                          type="checkbox"
                          :value="payment.id"
                          :checked="selectedIds.includes(payment.id)"
                          @change="toggleSelection(payment.id, $event.target.checked)"
                        >
                      </td>
                      <td>{{ formatDate(payment.record_date) }}</td>
                      <td>{{ formatTime(payment.record_time) }}</td>
                      <!--<td>{{ payment.driver?.company_category?.name || '—' }}</td>-->
                      <td>{{ payment.member_code || '—' }}</td>
                      <td>{{ payment.member_name }}</td>
                      <td>{{ payment.vehicle_license_number || '—' }}</td>
                      <td>{{ payment.item_name }}</td>
                      <td class="text-end">{{ formatCurrency(payment.gross_amount) }}</td>
                      <td class="text-end">{{ formatCurrency(payment.deduction) }}</td>
                      <td class="text-end fw-bold">{{ formatCurrency(payment.net_amount) }}</td>
                      <td>
                        <span :class="payment.status === 'paid' ? 'badge bg-success' : 'badge bg-warning text-dark'">
                          {{ payment.status === 'paid' ? '已支付' : '未支付' }}
                        </span>
                      </td>
                      <td>{{ formatDate(payment.payment_date) }}</td>
                      <td>{{ formatTimestamp(payment.created_at) }}</td>
                      <td>
                        <div class="btn-group btn-group-sm">
                          <button
                            v-if="permissions.canEdit"
                            type="button"
                            class="btn btn-warning"
                            @click="openEditModal(payment)"
                          >
                            <i class="bi bi-pencil-square"></i>
                          </button>
                          <button
                            v-if="permissions.canDelete"
                            type="button"
                            class="btn btn-danger"
                            @click="confirmDelete(payment)"
                          >
                            <i class="bi bi-trash"></i>
                          </button>
                        </div>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <div v-if="payments.links" class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                  顯示第 {{ payments.from }} - {{ payments.to }} 筆，總計 {{ payments.total }} 筆
                </div>
                <nav>
                  <ul class="pagination mb-0">
                    <li
                      v-for="link in payments.links"
                      :key="link.label"
                      class="page-item"
                      :class="{ active: link.active, disabled: !link.url }"
                    >
                      <Link v-if="link.url" class="page-link" :href="link.url" preserve-scroll v-html="link.label" />
                      <span v-else class="page-link" v-html="link.label" />
                    </li>
                  </ul>
                </nav>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>

    <input ref="importInput" type="file" class="d-none" accept=".xlsx,.csv" @change="handleImport" />

    <Modal :show="showCreateModal" max-width="2xl" @close="closeCreateModal">
      <div class="p-4">
        <h5 class="mb-3">
          <i class="bi bi-plus-circle me-2"></i>新增支出款項
        </h5>
        <form @submit.prevent="submitCreate">
          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label">紀錄日期<span class="text-danger">*</span></label>
              <input v-model="createForm.record_date" type="date" class="form-control" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">紀錄時間<span class="text-danger">*</span></label>
              <input v-model="createForm.record_time" type="time" class="form-control" required step="60">
            </div>
            <div class="col-md-4">
              <label class="form-label">狀態<span class="text-danger">*</span></label>
              <select v-model="createForm.status" class="form-select">
                <option value="pending">未支付</option>
                <option value="paid">已支付</option>
              </select>
            </div>

            <div class="col-md-6">
              <label class="form-label">隊員<span class="text-danger">*</span></label>
              <!-- 搜尋輸入框 -->
              <input
                v-model="driverSearchCreate"
                type="text"
                class="form-control"
                placeholder="搜尋隊員姓名或編號..."
                @focus="showDriverListCreate = true"
              >
              <!-- 點選式隊員列表 -->
              <div
                v-if="showDriverListCreate && driverSearchCreate.trim()"
                class="driver-search-results"
              >
                <div v-if="filteredDriversCreate.length === 0" class="list-group-item text-muted text-center">
                  找不到符合的隊員
                </div>
                <div
                  v-for="driver in filteredDriversCreate"
                  :key="driver.id"
                  class="list-group-item list-group-item-action"
                  :class="{ active: createForm.driver_id === driver.id }"
                  @click="selectDriverForCreate(driver)"
                >
                  <div class="d-flex align-items-center">
                    <i
                      class="bi me-2"
                      :class="createForm.driver_id === driver.id ? 'bi-check-circle-fill' : 'bi-circle'"
                    ></i>
                    <div>
                      <div class="fw-bold">{{ driver.name }}</div>
                      <small class="text-muted">{{ driver.id_number }}</small>
                    </div>
                  </div>
                </div>
                <div v-if="filteredDriversCreate.length > 0" class="list-group-item text-muted text-center small">
                  找到 {{ filteredDriversCreate.length }} 位隊員
                </div>
              </div>
            </div>

            <div class="col-md-6">
              <label class="form-label">車輛</label>
              <select v-model="createForm.vehicle_id" class="form-select" @change="syncVehicleToForm(createForm)">
                <option value="">未指定</option>
                <option v-for="vehicle in vehicles" :key="vehicle.id" :value="vehicle.id">
                  {{ vehicle.license_number }}
                </option>
              </select>
            </div>

            <div class="col-md-6">
              <label class="form-label">隊員姓名<span class="text-danger">*</span></label>
              <input v-model="createForm.member_name" type="text" class="form-control" required>
            </div>

            <div class="col-md-6">
              <label class="form-label">車牌號碼</label>
              <input v-model="createForm.vehicle_license_number" type="text" class="form-control">
            </div>

            <div class="col-md-6">
              <label class="form-label">隊員編號</label>
              <input v-model="createForm.member_code" type="text" class="form-control">
            </div>

            <div class="col-md-6">
              <label class="form-label">款項名稱<span class="text-danger">*</span></label>
              <input v-model="createForm.item_name" type="text" class="form-control" required>
            </div>

            <div class="col-md-4">
              <label class="form-label">支付金額<span class="text-danger">*</span></label>
              <input v-model.number="createForm.gross_amount" type="number" step="0.01" min="0" class="form-control" required @input="recalcNet(createForm)">
            </div>
            <div class="col-md-4">
              <label class="form-label">應扣款</label>
              <input v-model.number="createForm.deduction" type="number" step="0.01" min="0" class="form-control" @input="recalcNet(createForm)">
            </div>
            <div class="col-md-4">
              <label class="form-label">實付金額<span class="text-danger">*</span></label>
              <input v-model.number="createForm.net_amount" type="number" step="0.01" min="0" class="form-control" required>
            </div>

            <div class="col-md-6" v-if="createForm.status === 'paid'">
              <label class="form-label">支付日期<span class="text-danger">*</span></label>
              <input v-model="createForm.payment_date" type="date" class="form-control" required>
            </div>
            <div class="col-md-6" v-if="createForm.status === 'paid'">
              <label class="form-label">支付方式</label>
              <input v-model="createForm.payment_method" type="text" class="form-control" placeholder="如：匯款 / 現金">
            </div>

            <div class="col-12">
              <label class="form-label">備註</label>
              <textarea v-model="createForm.note" rows="3" class="form-control"></textarea>
            </div>
          </div>

          <div class="mt-4 d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-outline-secondary" @click="closeCreateModal">取消</button>
            <button type="submit" class="btn btn-primary" :disabled="createForm.processing">
              <i class="bi bi-save"></i> 儲存
            </button>
          </div>

          <div v-if="createForm.errors && Object.keys(createForm.errors).length" class="alert alert-danger mt-3">
            <ul class="mb-0">
              <li v-for="(message, field) in createForm.errors" :key="field">{{ message }}</li>
            </ul>
          </div>
        </form>
      </div>
    </Modal>

    <Modal :show="showEditModal" max-width="2xl" @close="closeEditModal">
      <div class="p-4">
        <h5 class="mb-3">
          <i class="bi bi-pencil-square me-2"></i>編輯支出款項
        </h5>
        <form @submit.prevent="submitEdit">
          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label">紀錄日期<span class="text-danger">*</span></label>
              <input v-model="editForm.record_date" type="date" class="form-control" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">紀錄時間<span class="text-danger">*</span></label>
              <input v-model="editForm.record_time" type="time" class="form-control" required step="60">
            </div>
            <div class="col-md-4">
              <label class="form-label">狀態<span class="text-danger">*</span></label>
              <select v-model="editForm.status" class="form-select">
                <option value="pending">未支付</option>
                <option value="paid">已支付</option>
              </select>
            </div>

            <div class="col-md-6">
              <label class="form-label">隊員</label>
              <!-- 搜尋輸入框 -->
              <input
                v-model="driverSearchEdit"
                type="text"
                class="form-control"
                placeholder="搜尋隊員姓名或編號..."
                @focus="showDriverListEdit = true"
              >
              <!-- 點選式隊員列表 -->
              <div
                v-if="showDriverListEdit && driverSearchEdit.trim()"
                class="driver-search-results"
              >
                <div v-if="filteredDriversEdit.length === 0" class="list-group-item text-muted text-center">
                  找不到符合的隊員
                </div>
                <div
                  v-for="driver in filteredDriversEdit"
                  :key="driver.id"
                  class="list-group-item list-group-item-action"
                  :class="{ active: editForm.driver_id === driver.id }"
                  @click="selectDriverForEdit(driver)"
                >
                  <div class="d-flex align-items-center">
                    <i
                      class="bi me-2"
                      :class="editForm.driver_id === driver.id ? 'bi-check-circle-fill' : 'bi-circle'"
                    ></i>
                    <div>
                      <div class="fw-bold">{{ driver.name }}</div>
                      <small class="text-muted">{{ driver.id_number }}</small>
                    </div>
                  </div>
                </div>
                <div v-if="filteredDriversEdit.length > 0" class="list-group-item text-muted text-center small">
                  找到 {{ filteredDriversEdit.length }} 位隊員
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <label class="form-label">隊員編號</label>
              <input v-model="editForm.member_code" type="text" class="form-control">
            </div>

            <div class="col-md-6">
              <label class="form-label">隊員姓名<span class="text-danger">*</span></label>
              <input v-model="editForm.member_name" type="text" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">車輛</label>
              <select v-model="editForm.vehicle_id" class="form-select" @change="syncVehicleToForm(editForm)">
                <option value="">未指定</option>
                <option v-for="vehicle in vehicles" :key="vehicle.id" :value="vehicle.id">
                  {{ vehicle.license_number }}
                </option>
              </select>
            </div>

            <div class="col-md-6">
              <label class="form-label">車牌號碼</label>
              <input v-model="editForm.vehicle_license_number" type="text" class="form-control">
            </div>
            <div class="col-md-6">
              <label class="form-label">款項名稱<span class="text-danger">*</span></label>
              <input v-model="editForm.item_name" type="text" class="form-control" required>
            </div>

            <div class="col-md-4">
              <label class="form-label">支付金額<span class="text-danger">*</span></label>
              <input v-model.number="editForm.gross_amount" type="number" step="0.01" min="0" class="form-control" required @input="recalcNet(editForm)">
            </div>
            <div class="col-md-4">
              <label class="form-label">應扣款</label>
              <input v-model.number="editForm.deduction" type="number" step="0.01" min="0" class="form-control" @input="recalcNet(editForm)">
            </div>
            <div class="col-md-4">
              <label class="form-label">實付金額<span class="text-danger">*</span></label>
              <input v-model.number="editForm.net_amount" type="number" step="0.01" min="0" class="form-control" required>
            </div>

            <div class="col-md-6" v-if="editForm.status === 'paid'">
              <label class="form-label">支付日期<span class="text-danger">*</span></label>
              <input v-model="editForm.payment_date" type="date" class="form-control" required>
            </div>
            <div class="col-md-6" v-if="editForm.status === 'paid'">
              <label class="form-label">支付方式</label>
              <input v-model="editForm.payment_method" type="text" class="form-control">
            </div>

            <div class="col-12">
              <label class="form-label">備註</label>
              <textarea v-model="editForm.note" rows="3" class="form-control"></textarea>
            </div>
          </div>

          <div class="mt-4 d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-outline-secondary" @click="closeEditModal">取消</button>
            <button type="submit" class="btn btn-primary" :disabled="editForm.processing">
              <i class="bi bi-save"></i> 更新
            </button>
          </div>

          <div v-if="editForm.errors && Object.keys(editForm.errors).length" class="alert alert-danger mt-3">
            <ul class="mb-0">
              <li v-for="(message, field) in editForm.errors" :key="field">{{ message }}</li>
            </ul>
          </div>
        </form>
      </div>
    </Modal>

    <Modal :show="showBulkModal" max-width="lg" @close="closeBulkModal">
      <div class="p-4">
        <h5 class="mb-3">
          <i class="bi bi-arrow-repeat me-2"></i>批次更新狀態
        </h5>
        <form @submit.prevent="submitBulk">
          <div class="mb-3">
            <label class="form-label">更新筆數</label>
            <div class="form-control bg-light">{{ bulkForm.ids.length }} 筆</div>
          </div>

          <div class="mb-3">
            <label class="form-label">狀態</label>
            <select v-model="bulkForm.status" class="form-select">
              <option value="pending">未支付</option>
              <option value="paid">已支付</option>
            </select>
          </div>

          <div class="row" v-if="bulkForm.status === 'paid'">
            <div class="col-md-6 mb-3">
              <label class="form-label">支付日期<span class="text-danger">*</span></label>
              <input v-model="bulkForm.payment_date" type="date" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">支付方式</label>
              <input v-model="bulkForm.payment_method" type="text" class="form-control" placeholder="如：匯款 / 現金">
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">備註</label>
            <textarea v-model="bulkForm.note" rows="3" class="form-control"></textarea>
          </div>

          <div class="d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-outline-secondary" @click="closeBulkModal">取消</button>
            <button type="submit" class="btn btn-primary" :disabled="bulkForm.processing">
              <i class="bi bi-check-circle"></i> 套用
            </button>
          </div>

          <div v-if="bulkForm.errors && Object.keys(bulkForm.errors).length" class="alert alert-danger mt-3">
            <ul class="mb-0">
              <li v-for="(message, field) in bulkForm.errors" :key="field">{{ message }}</li>
            </ul>
          </div>
        </form>
      </div>
    </Modal>
  </AdminLayout>
</template>

<script setup>
// 控制頁面資料與彈窗狀態
import { computed, reactive, ref, watch, onMounted } from 'vue'
import { Link, router, useForm, usePage } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Modal from '@/Components/Modal.vue'

const props = defineProps({
  payments: { type: Object, required: true },
  filters: { type: Object, required: true },
  statistics: { type: Object, required: true },
  drivers: { type: Array, default: () => [] },
  vehicles: { type: Array, default: () => [] },
  permissions: { type: Object, required: true }
})

const page = usePage()
const flash = computed(() => page.props.flash || {})
const flashFailures = computed(() => Array.isArray(flash.value.importFailures) ? flash.value.importFailures : [])

const filterForm = reactive({
  keyword: props.filters.keyword || '',
  status: props.filters.status || 'pending',
  record_date_from: props.filters.record_date_from || '',
  record_date_to: props.filters.record_date_to || '',
  payment_date_from: props.filters.payment_date_from || '',
  payment_date_to: props.filters.payment_date_to || '',
  driver_id: props.filters.driver_id || '',
  vehicle_id: props.filters.vehicle_id || '',
  per_page: Number(props.filters.per_page) || 20
})

const perPageOptions = [10, 20, 50, 100]
const currencyFormat = { minimumFractionDigits: 0, maximumFractionDigits: 2 }

const totals = computed(() => ({
  pending: Number(props.statistics?.pending_total ?? 0),
  paid: Number(props.statistics?.paid_total ?? 0)
}))

const isExporting = ref(false)
const selectedIds = ref([])
const showCreateModal = ref(false)
const showEditModal = ref(false)
const showBulkModal = ref(false)
const importInput = ref(null)

// 隊員搜尋過濾關鍵字
const driverSearchCreate = ref('')
const driverSearchEdit = ref('')

// 控制隊員列表顯示
const showDriverListCreate = ref(false)
const showDriverListEdit = ref(false)

// 新增表單的過濾後隊員列表
const filteredDriversCreate = computed(() => {
  if (!driverSearchCreate.value.trim()) {
    return props.drivers
  }
  const keyword = driverSearchCreate.value.toLowerCase()
  return props.drivers.filter(driver => {
    return driver.name.toLowerCase().includes(keyword) ||
           driver.id_number.toLowerCase().includes(keyword)
  })
})

// 編輯表單的過濾後隊員列表
const filteredDriversEdit = computed(() => {
  if (!driverSearchEdit.value.trim()) {
    return props.drivers
  }
  const keyword = driverSearchEdit.value.toLowerCase()
  return props.drivers.filter(driver => {
    return driver.name.toLowerCase().includes(keyword) ||
           driver.id_number.toLowerCase().includes(keyword)
  })
})

// 強制關閉所有 dialog，避免殘留的 backdrop 造成整頁像 modal
function forceCloseDialogs() {
  showCreateModal.value = false
  showEditModal.value = false
  showBulkModal.value = false
  document.querySelectorAll('dialog[open]').forEach((dialog) => {
    try {
      dialog.close()
    } catch (e) {
      console.warn('關閉 dialog 失敗', e)
    }
  })
  document.body.style.overflow = ''
}

onMounted(() => {
  forceCloseDialogs()
})

const createForm = useForm({
  record_date: props.filters.record_date_from || new Date().toISOString().slice(0, 10),
  record_time: '09:00',
  driver_id: '',
  vehicle_id: '',
  member_code: '',
  member_name: '',
  vehicle_license_number: '',
  item_name: '',
  gross_amount: 0,
  deduction: 0,
  net_amount: 0,
  status: 'pending',
  payment_date: '',
  payment_method: '',
  note: ''
})

const editForm = useForm({
  id: null,
  record_date: '',
  record_time: '09:00',
  driver_id: '',
  vehicle_id: '',
  member_code: '',
  member_name: '',
  vehicle_license_number: '',
  item_name: '',
  gross_amount: 0,
  deduction: 0,
  net_amount: 0,
  status: 'pending',
  payment_date: '',
  payment_method: '',
  note: ''
})

const bulkForm = useForm({
  ids: [],
  status: 'paid',
  payment_date: '',
  payment_method: '現金',
  note: ''
})

watch(() => createForm.status, (status) => {
  if (status !== 'paid') {
    createForm.payment_date = ''
    createForm.payment_method = ''
  }
})

watch(() => editForm.status, (status) => {
  if (status !== 'paid') {
    editForm.payment_date = ''
    editForm.payment_method = ''
  }
})

watch(() => bulkForm.status, (status) => {
  if (status !== 'paid') {
    bulkForm.payment_date = ''
    bulkForm.payment_method = ''
  } else if (!bulkForm.payment_method) {
    bulkForm.payment_method = '現金'
  }
})

const isCurrentPageAllSelected = computed(() => {
  if (props.payments.data.length === 0) return false
  return props.payments.data.every(item => selectedIds.value.includes(item.id))
})

function submitFilters() {
  router.get(route('admin.expense-payments.index'), filterForm, {
    preserveScroll: true,
    preserveState: true
  })
}

function resetFilters() {
  filterForm.keyword = ''
  filterForm.status = 'pending'
  filterForm.record_date_from = ''
  filterForm.record_date_to = ''
  filterForm.payment_date_from = ''
  filterForm.payment_date_to = ''
  filterForm.driver_id = ''
  filterForm.vehicle_id = ''
  filterForm.per_page = 20
  submitFilters()
}

function exportData() {
  if (isExporting.value) return
  isExporting.value = true
  const params = new URLSearchParams(filterForm).toString()
  window.open(`${route('admin.expense-payments.export')}?${params}`, '_blank')
  setTimeout(() => {
    isExporting.value = false
  }, 1200)
}

// 列印選中的資料
function printSelectedData() {
  if (selectedIds.value.length === 0) {
    alert('請先選擇要列印的交易資料')
    return
  }

  try {
    // 從當前頁面資料中過濾出選中的項目
    const selectedPayments = props.payments.data.filter(payment =>
      selectedIds.value.includes(payment.id)
    )

    // 計算統計資訊
    const statistics = {
      totalCount: selectedPayments.length,
      totalGrossAmount: selectedPayments.reduce((sum, p) => sum + parseFloat(p.gross_amount || 0), 0),
      totalDeduction: selectedPayments.reduce((sum, p) => sum + parseFloat(p.deduction || 0), 0),
      totalNetAmount: selectedPayments.reduce((sum, p) => sum + parseFloat(p.net_amount || 0), 0)
    }

    // 準備列印資料
    const printData = {
      payments: selectedPayments,
      statistics: statistics,
      printedAt: new Date().toISOString()
    }

    // 儲存到 localStorage
    localStorage.setItem('expense_payments_print_data', JSON.stringify(printData))

    // 開啟新視窗
    window.open(route('admin.expense-payments.print'), '_blank')

    // 設定 5 分鐘後自動清理（避免資料殘留）
    setTimeout(() => {
      localStorage.removeItem('expense_payments_print_data')
    }, 5 * 60 * 1000)

  } catch (error) {
    console.error('列印資料準備失敗:', error)
    alert('列印資料準備失敗，請稍後再試')
  }
}

function triggerImport() {
  importInput.value?.click()
}

const importForm = useForm({
  file: null
})

async function handleImport(event) {
  const files = event.target.files
  if (!files || !files.length) {
    return
  }

  console.log('開始匯入檔案:', files[0].name)

  try {
    // 建立 FormData
    const formData = new FormData()
    formData.append('file', files[0])

    // 執行匯入
    const response = await fetch(route('admin.expense-payments.import'), {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
      },
      body: formData,
    })

    // 檢查 Content-Type
    const contentType = response.headers.get('content-type')
    console.log('Response Content-Type:', contentType)
    console.log('Response Status:', response.status)

    if (!contentType || !contentType.includes('application/json')) {
      // 不是 JSON 響應，可能是錯誤頁面
      const text = await response.text()
      console.error('非 JSON 響應:', text.substring(0, 500))
      alert('伺服器錯誤：返回的不是 JSON 格式。請查看瀏覽器控制台了解詳情。')
      return
    }

    const result = await response.json()

    if (response.ok && result.success) {
      // 匯入成功，重新載入頁面（後端已設定 flash 訊息）
      window.location.href = route('admin.expense-payments.index')
    } else {
      // 匯入失敗
      alert(result.message || '匯入失敗')
    }

  } catch (error) {
    console.error('匯入錯誤:', error)
    alert('匯入過程中發生錯誤：' + error.message)
  } finally {
    // 清空檔案輸入
    if (importInput.value) {
      importInput.value.value = ''
    }
  }
}

function toggleSelection(id, checked) {
  if (checked) {
    if (!selectedIds.value.includes(id)) {
      selectedIds.value.push(id)
    }
  } else {
    selectedIds.value = selectedIds.value.filter(item => item !== id)
  }
}

function toggleSelectAll(event) {
  const checked = event.target.checked
  if (!checked) {
    selectedIds.value = selectedIds.value.filter(id => !props.payments.data.some(item => item.id === id))
    return
  }
  const idsOnPage = props.payments.data.map(item => item.id)
  const combined = new Set([...selectedIds.value, ...idsOnPage])
  selectedIds.value = Array.from(combined)
}

function openCreateModal() {
  createForm.reset()
  createForm.record_date = new Date().toISOString().slice(0, 10)
  createForm.record_time = '09:00'
  createForm.status = 'pending'
  driverSearchCreate.value = '' // 重置搜尋關鍵字
  showDriverListCreate.value = false // 隱藏列表
  showCreateModal.value = true
}

function closeCreateModal() {
  showCreateModal.value = false
}

function submitCreate() {
  createForm.post(route('admin.expense-payments.store'), {
    preserveScroll: true,
    onSuccess: () => {
      closeCreateModal()
      selectedIds.value = []
    }
  })
}

function openEditModal(payment) {
  editForm.reset()
  editForm.id = payment.id
  editForm.record_date = formatDateForInput(payment.record_date)
  editForm.record_time = formatTimeForInput(payment.record_time)
  editForm.driver_id = payment.driver_id ? String(payment.driver_id) : ''
  editForm.vehicle_id = payment.vehicle_id ? String(payment.vehicle_id) : ''
  editForm.member_code = payment.member_code || ''
  editForm.member_name = payment.member_name || ''
  editForm.vehicle_license_number = payment.vehicle_license_number || ''
  editForm.item_name = payment.item_name || ''
  editForm.gross_amount = Number(payment.gross_amount || 0)
  editForm.deduction = Number(payment.deduction || 0)
  editForm.net_amount = Number(payment.net_amount || 0)
  editForm.status = payment.status
  editForm.payment_date = formatDateForInput(payment.payment_date)
  editForm.payment_method = payment.payment_method || ''
  editForm.note = payment.note || ''
  driverSearchEdit.value = payment.member_name || '' // 顯示已選擇的隊員姓名
  showDriverListEdit.value = false // 隱藏列表
  showEditModal.value = true
}

function closeEditModal() {
  showEditModal.value = false
}

function submitEdit() {
  if (!editForm.id) return
  editForm.put(route('admin.expense-payments.update', editForm.id), {
    preserveScroll: true,
    onSuccess: () => {
      closeEditModal()
      selectedIds.value = []
    }
  })
}

function confirmDelete(payment) {
  if (!confirm(`確定要刪除「${payment.item_name}」嗎？`)) {
    return
  }
  router.delete(route('admin.expense-payments.destroy', payment.id), {
    preserveScroll: true,
    onSuccess: () => {
      selectedIds.value = selectedIds.value.filter(id => id !== payment.id)
    }
  })
}

function openBulkModal(status) {
  if (selectedIds.value.length === 0) {
    return
  }
  bulkForm.reset()
  bulkForm.ids = [...selectedIds.value]
  bulkForm.status = status
  bulkForm.payment_date = status === 'paid' ? new Date().toISOString().slice(0, 10) : ''
  bulkForm.payment_method = status === 'paid' ? '現金' : ''
  showBulkModal.value = true
}

function closeBulkModal() {
  showBulkModal.value = false
}

function submitBulk() {
  if (bulkForm.status !== 'paid') {
    bulkForm.payment_date = ''
    bulkForm.payment_method = ''
  }
  bulkForm.post(route('admin.expense-payments.bulk-status'), {
    preserveScroll: true,
    onSuccess: () => {
      closeBulkModal()
      selectedIds.value = []
    }
  })
}

// 選擇隊員 (新增表單)
function selectDriverForCreate(driver) {
  createForm.driver_id = driver.id
  createForm.member_name = driver.name
  driverSearchCreate.value = driver.name // 將選中的姓名顯示在搜尋框
  showDriverListCreate.value = false // 隱藏列表
}

// 選擇隊員 (編輯表單)
function selectDriverForEdit(driver) {
  editForm.driver_id = driver.id
  editForm.member_name = driver.name
  driverSearchEdit.value = driver.name // 將選中的姓名顯示在搜尋框
  showDriverListEdit.value = false // 隱藏列表
}

function syncDriverToForm(form) {
  if (!form.driver_id) {
    return
  }
  const driver = props.drivers.find(item => String(item.id) === String(form.driver_id))
  if (driver) {
    form.member_name = form.member_name || driver.name
  }
}

function syncVehicleToForm(form) {
  if (!form.vehicle_id) {
    return
  }
  const vehicle = props.vehicles.find(item => String(item.id) === String(form.vehicle_id))
  if (vehicle) {
    form.vehicle_license_number = form.vehicle_license_number || vehicle.license_number
  }
}

function recalcNet(form) {
  const gross = Number(form.gross_amount || 0)
  const deduction = Number(form.deduction || 0)
  if (deduction > gross) {
    return
  }
  form.net_amount = Number((gross - deduction).toFixed(2))
}

function formatDate(value) {
  if (!value) return '—'
  return value.split('T')[0]
}

function formatDateForInput(value) {
  if (!value) return ''
  return value.split('T')[0]
}

function formatTime(value) {
  if (!value) return '—'
  return value.slice(0, 5)
}

function formatTimeForInput(value) {
  if (!value) return '09:00'
  return value.slice(0, 5)
}

function formatTimestamp(value) {
  if (!value) return '—'
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) {
    return value
  }
  return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')} ${String(date.getHours()).padStart(2, '0')}:${String(date.getMinutes()).padStart(2, '0')}`
}

function formatCurrency(value) {
  const number = Number(value || 0)
  return number.toLocaleString(undefined, currencyFormat)
}
</script>

<style scoped>
.table-responsive {
  max-height: 65vh;
}

/* 隊員搜尋結果列表樣式 */
.driver-search-results {
  position: absolute;
  z-index: 1000;
  width: 100%;
  max-height: 300px;
  overflow-y: auto;
  background: white;
  border: 1px solid #dee2e6;
  border-radius: 0.375rem;
  box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
  margin-top: 0.25rem;
}

.driver-search-results .list-group-item {
  border-left: none;
  border-right: none;
  cursor: pointer;
  transition: background-color 0.15s ease-in-out;
}

.driver-search-results .list-group-item:first-child {
  border-top: none;
  border-top-left-radius: 0.375rem;
  border-top-right-radius: 0.375rem;
}

.driver-search-results .list-group-item:last-child {
  border-bottom: none;
  border-bottom-left-radius: 0.375rem;
  border-bottom-right-radius: 0.375rem;
}

.driver-search-results .list-group-item:hover:not(.text-muted) {
  background-color: #f8f9fa;
}

.driver-search-results .list-group-item.active {
  background-color: #0d6efd;
  color: white;
  border-color: #0d6efd;
}

.driver-search-results .list-group-item.active .text-muted {
  color: rgba(255, 255, 255, 0.75) !important;
}
</style>
