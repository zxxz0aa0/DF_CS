<template>
  <AdminLayout :user="$page.props.auth.user">
    <div class="content-wrapper">
      <div class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h1>科目詳細</h1>
            </div>
            <div class="col-sm-6">
              <div class="float-right">
                <Link :href="route('admin.accounts.account.details.index')"
                      class="btn btn-secondary">
                  <i class="bi bi-arrow-left"></i> 返回列表
                </Link>
                <Link :href="account?.id ? route('admin.accounts.account.details.edit', { detail: account.id }) : '#'"
                      class="btn btn-warning ms-2">
                  <i class="bi bi-pencil"></i> 編輯
                </Link>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="content">
        <div class="container-fluid">
          <div class="row">
            <div class="col-12">
              <div class="card">
                <div class="card-header">
                  <h3 class="card-title">科目信息</h3>
                </div>
                <div class="card-body">
                  <div class="row mb-3">
                    <div class="col-md-4">
                      <label class="form-label text-muted">科目編號</label>
                      <div class="fw-bold"><code>{{ displayValue(account?.account_code) }}</code></div>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label text-muted">科目名稱</label>
                      <div class="fw-bold">{{ displayValue(account?.account_name) }}</div>
                      <div class="text-muted small">{{ displayValue(account?.account_name_en) }}</div>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label text-muted">狀態</label>
                      <span class="badge" :class="account?.is_active ? 'bg-success' : 'bg-secondary'">
                        {{ account?.is_active ? '啟用' : '停用' }}
                      </span>
                    </div>
                  </div>

                  <div class="row mb-3">
                    <div class="col-md-4">
                      <label class="form-label text-muted">總類</label>
                      <div class="fw-bold">
                        {{ formattedMainCategory }}
                      </div>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label text-muted">子分類</label>
                      <div class="fw-bold">
                        {{ formattedSubCategory }}
                      </div>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label text-muted">借貸性質</label>
                      <span class="badge" :class="account?.debit_credit === 'debit' ? 'bg-info' : 'bg-warning'">
                        {{ account?.debit_credit === 'debit' ? '借方' : '貸方' }}
                      </span>
                    </div>
                  </div>

                  <div class="row mb-3">
                    <div class="col-md-4">
                      <label class="form-label text-muted">科目性質</label>
                      <div class="fw-bold">{{ accountTypeText(account?.account_type) }}</div>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label text-muted">上層科目</label>
                      <div class="fw-bold">
                        {{ formattedParent }}
                      </div>
                    </div>
                    <div class="col-md-2">
                      <label class="form-label text-muted">層級</label>
                      <div class="fw-bold">{{ account?.level ?? 1 }}</div>
                    </div>
                    <div class="col-md-2">
                      <label class="form-label text-muted">排序</label>
                      <div class="fw-bold">{{ account?.sort_order ?? 0 }}</div>
                    </div>
                  </div>

                  <div class="row mb-3">
                    <div class="col-md-6">
                      <label class="form-label text-muted">科目說明</label>
                      <p class="mb-0">{{ displayValue(account?.description) }}</p>
                    </div>
                    <div class="col-md-6">
                      <label class="form-label text-muted">備註</label>
                      <p class="mb-0">{{ displayValue(account?.notes) }}</p>
                    </div>
                  </div>

                  <hr>

                  <div class="row text-muted small">
                    <div class="col-md-3">
                      建立者：{{ account?.created_by?.name || '－' }}
                    </div>
                    <div class="col-md-3">
                      建立時間：{{ formatDate(account?.created_at) }}
                    </div>
                    <div class="col-md-3">
                      最後更新者：{{ account?.updated_by?.name || '－' }}
                    </div>
                    <div class="col-md-3">
                      更新時間：{{ formatDate(account?.updated_at) }}
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
  account: {
    type: Object,
    required: true
  }
})

const { account } = props

const formattedMainCategory = computed(() => {
  const code = account?.main_category?.category_code
  const name = account?.main_category?.category_name
  if (!code && !name) return '－'
  return [code, name].filter(Boolean).join(' - ')
})

const formattedSubCategory = computed(() => {
  const code = account?.sub_category?.sub_category_code
  const name = account?.sub_category?.sub_category_name
  if (!code && !name) return '－'
  return [code, name].filter(Boolean).join(' - ')
})

const formattedParent = computed(() => {
  const code = account?.parent?.account_code
  const name = account?.parent?.account_name
  if (!code && !name) return '－'
  return [code, name].filter(Boolean).join(' - ')
})

const displayValue = (value) => {
  if (value === null || value === undefined || value === '') {
    return '－'
  }
  return value
}

const accountTypeText = (type) => {
  const mapping = {
    asset: '資產',
    liability: '負債',
    equity: '權益',
    revenue: '收入',
    expense: '費用'
  }
  return mapping[type] || '－'
}

const formatDate = (value) => {
  if (!value) return '－'
  return new Date(value).toLocaleString()
}
</script>

<style scoped>
.form-label {
  font-weight: 600;
}
</style>
