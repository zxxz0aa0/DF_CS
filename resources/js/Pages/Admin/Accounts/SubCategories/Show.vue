<template>
  <AdminLayout :user="$page.props.auth.user">
    <div class="content-wrapper">
      <div class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h1>子分類詳細</h1>
            </div>
            <div class="col-sm-6">
              <div class="float-right">
                <Link :href="route('admin.accounts.sub-categories.index')"
                      class="btn btn-secondary">
                  <i class="bi bi-arrow-left"></i> 返回列表
                </Link>
                <Link :href="route('admin.accounts.sub-categories.edit', subCategory.id)"
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
                  <h3 class="card-title">子分類資訊</h3>
                </div>
                <div class="card-body">
                  <div class="row mb-3">
                    <div class="col-md-6">
                      <label class="form-label text-muted">所屬總類</label>
                      <div class="fw-bold">
                        {{ subCategory.main_category?.category_code }} - {{ subCategory.main_category?.category_name }}
                      </div>
                    </div>
                    <div class="col-md-3">
                      <label class="form-label text-muted">子分類代碼</label>
                      <div class="fw-bold">
                        <code>{{ subCategory.sub_category_code }}</code>
                      </div>
                    </div>
                    <div class="col-md-3">
                      <label class="form-label text-muted">排序</label>
                      <div class="fw-bold">{{ subCategory.sort_order }}</div>
                    </div>
                  </div>

                  <div class="row mb-3">
                    <div class="col-md-6">
                      <label class="form-label text-muted">子分類名稱</label>
                      <div class="fw-bold">{{ subCategory.sub_category_name }}</div>
                    </div>
                    <div class="col-md-3">
                      <label class="form-label text-muted">狀態</label>
                      <div>
                        <span class="badge" :class="subCategory.is_active ? 'bg-success' : 'bg-secondary'">
                          {{ subCategory.is_active ? '啟用' : '停用' }}
                        </span>
                      </div>
                    </div>
                    <div class="col-md-3">
                      <label class="form-label text-muted">科目數量</label>
                      <div class="fw-bold">{{ subCategory.account_details_count ?? 0 }}</div>
                    </div>
                  </div>

                  <div class="row mb-3">
                    <div class="col-12">
                      <label class="form-label text-muted">說明</label>
                      <p class="mb-0">{{ subCategory.description || '－' }}</p>
                    </div>
                  </div>

                  <hr>

                  <div class="row text-muted small">
                    <div class="col-md-3">
                      建立者：{{ subCategory.created_by_user?.name || '－' }}
                    </div>
                    <div class="col-md-3">
                      建立時間：{{ formatDate(subCategory.created_at) }}
                    </div>
                    <div class="col-md-3">
                      最後更新者：{{ subCategory.updated_by_user?.name || '－' }}
                    </div>
                    <div class="col-md-3">
                      更新時間：{{ formatDate(subCategory.updated_at) }}
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
import { Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
  subCategory: {
    type: Object,
    required: true
  }
})

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
