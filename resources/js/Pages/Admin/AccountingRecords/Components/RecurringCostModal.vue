<template>
  <!-- Modal -->
  <div
    v-if="show"
    class="modal fade show d-block"
    tabindex="-1"
    style="background-color: rgba(0,0,0,0.5);"
    @click.self="closeModal"
  >
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">
            <i class="bi bi-cash-stack"></i>
            {{ driver?.name }} - 經常性費用
          </h5>
          <button
            type="button"
            class="btn-close"
            @click="closeModal"
          ></button>
        </div>
        <div class="modal-body">
          <div v-if="loading" class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">載入中...</span>
            </div>
          </div>

          <div v-else-if="recurringCost">
            <!-- 組合資訊 -->
            <!--<div class="card mb-3">
              <div class="card-header">
                <h6 class="mb-0">組合資訊</h6>
              </div>
              <div class="card-body">
                <div class="row">
                  <div class="col-md-6">
                    <dl class="row mb-2">
                      <dt class="col-sm-4">組合名稱</dt>
                      <dd class="col-sm-8">{{ recurringCost.name }}</dd>
                    </dl>
                  </div>
                  <div class="col-md-6">
                    <dl class="row mb-2">
                      <dt class="col-sm-4">組合總金額</dt>
                      <dd class="col-sm-8">
                        <span class="badge bg-primary">
                          ${{ recurringCost.total_amount?.toLocaleString() || '0' }}
                        </span>
                      </dd>
                    </dl>
                  </div>
                </div>
                <div v-if="recurringCost.description">
                  <strong>說明：</strong>{{ recurringCost.description }}
                </div>
              </div>
            </div>-->

            <!-- 費用明細 -->
            <div class="card">
              <div class="card-header">
                <h6 class="mb-0">費用明細</h6>
              </div>
              <div class="card-body">
                <div v-if="recurringCost.items && recurringCost.items.length > 0" class="table-responsive">
                  <table class="table table-sm table-hover mb-0">
                    <thead>
                      <tr>
                        <th>會計科目</th>
                        <th>金額</th>
                        <th>備註</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="item in recurringCost.items" :key="item.id">
                        <td>{{ item.account_detail?.account_name || '-' }}</td>
                        <td>${{ item.amount?.toLocaleString() || '0' }}</td>
                        <td>{{ item.note || '-' }}</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
                <div v-else class="text-center text-muted py-3">
                  此組合尚無費用明細
                </div>
              </div>
            </div>
          </div>

          <div v-else class="alert alert-info">
            此駕駛尚未設定經常性費用組合
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" @click="closeModal">
            關閉
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import axios from 'axios'

const props = defineProps({
  show: { type: Boolean, default: false },
  driver: { type: Object, default: null }
})

const emit = defineEmits(['close'])

const loading = ref(false)
const recurringCost = ref(null)

// 載入經常性費用資料
const loadRecurringCost = async (templateId) => {
  loading.value = true
  try {
    const response = await axios.get(route('admin.accounting.api.recurring-costs.show', templateId))
    recurringCost.value = response.data
  } catch (error) {
    console.error('載入經常性費用失敗:', error)
    recurringCost.value = null
  } finally {
    loading.value = false
  }
}

// 監聽 driver 變化，載入經常性費用資料
watch(() => props.driver, async (newDriver) => {
  if (newDriver && newDriver.recurring_cost_id) {
    await loadRecurringCost(newDriver.recurring_cost_id)
  } else {
    recurringCost.value = null
  }
}, { immediate: true })

const closeModal = () => {
  emit('close')
}
</script>
