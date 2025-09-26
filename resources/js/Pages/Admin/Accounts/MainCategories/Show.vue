<template>
  <AdminLayout :user="$page.props.auth.user">
    <div class="content-wrapper">
      <!-- 頁面標題 -->
      <div class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h1>會計總類詳情</h1>
            </div>
            <div class="col-sm-6">
              <div class="float-right">
                <Link :href="route('admin.accounts.main-categories.edit', category.id)"
                      class="btn btn-warning me-2">
                  <i class="bi bi-pencil"></i> 編輯
                </Link>
                <Link :href="route('admin.accounts.main-categories.index')"
                      class="btn btn-secondary">
                  <i class="bi bi-arrow-left"></i> 返回列表
                </Link>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- 內容區域 -->
      <div class="content">
        <div class="container-fluid">
          <div class="row">
            <!-- 基本資訊 -->
            <div class="col-md-8">
              <div class="card">
                <div class="card-header">
                  <h3 class="card-title">基本資訊</h3>
                </div>
                <div class="card-body">
                  <dl class="row">
                    <dt class="col-sm-3">總類代碼</dt>
                    <dd class="col-sm-9">
                      <code>{{ category.category_code }}</code>
                    </dd>

                    <dt class="col-sm-3">總類名稱</dt>
                    <dd class="col-sm-9">{{ category.category_name }}</dd>

                    <dt class="col-sm-3">說明</dt>
                    <dd class="col-sm-9">{{ category.description || '-' }}</dd>

                    <dt class="col-sm-3">排序順序</dt>
                    <dd class="col-sm-9">{{ category.sort_order }}</dd>

                    <dt class="col-sm-3">狀態</dt>
                    <dd class="col-sm-9">
                      <span class="badge" :class="category.is_active ? 'bg-success' : 'bg-secondary'">
                        {{ category.is_active ? '啟用' : '停用' }}
                      </span>
                    </dd>

                    <dt class="col-sm-3">子分類數量</dt>
                    <dd class="col-sm-9">
                      <span class="badge bg-info">{{ category.sub_categories_count }} 個</span>
                    </dd>
                  </dl>
                </div>
              </div>
            </div>

            <!-- 統計資訊 -->
            <div class="col-md-4">
              <div class="card">
                <div class="card-header">
                  <h3 class="card-title">統計資訊</h3>
                </div>
                <div class="card-body">
                  <div class="info-box">
                    <span class="info-box-icon bg-info">
                      <i class="bi bi-folder"></i>
                    </span>
                    <div class="info-box-content">
                      <span class="info-box-text">子分類數量</span>
                      <span class="info-box-number">{{ category.sub_categories_count }}</span>
                    </div>
                  </div>
                </div>
              </div>

              <!-- 系統資訊 -->
              <div class="card">
                <div class="card-header">
                  <h3 class="card-title">系統資訊</h3>
                </div>
                <div class="card-body">
                  <dl class="row">
                    <dt class="col-6">建立者</dt>
                    <dd class="col-6">{{ category.created_by?.name || '-' }}</dd>

                    <dt class="col-6">建立時間</dt>
                    <dd class="col-6">{{ formatDate(category.created_at) }}</dd>

                    <dt class="col-6">更新者</dt>
                    <dd class="col-6">{{ category.updated_by?.name || '-' }}</dd>

                    <dt class="col-6">更新時間</dt>
                    <dd class="col-6">{{ formatDate(category.updated_at) }}</dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>

          <!-- 子分類列表 -->
          <div class="row" v-if="category.sub_categories && category.sub_categories.length > 0">
            <div class="col-12">
              <div class="card">
                <div class="card-header">
                  <h3 class="card-title">子分類列表</h3>
                  <div class="card-tools">
                    <Link :href="route('admin.accounts.sub-categories.create', { main_category_id: category.id })"
                          class="btn btn-sm btn-primary">
                      <i class="bi bi-plus"></i> 新增子分類
                    </Link>
                  </div>
                </div>
                <div class="card-body table-responsive p-0">
                  <table class="table table-striped">
                    <thead>
                      <tr>
                        <th>子分類代碼</th>
                        <th>子分類名稱</th>
                        <th>說明</th>
                        <th>排序</th>
                        <th>狀態</th>
                        <th>操作</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="subCategory in category.sub_categories" :key="subCategory.id">
                        <td><code>{{ subCategory.sub_category_code }}</code></td>
                        <td>{{ subCategory.sub_category_name }}</td>
                        <td>{{ subCategory.description || '-' }}</td>
                        <td>{{ subCategory.sort_order }}</td>
                        <td>
                          <span class="badge" :class="subCategory.is_active ? 'bg-success' : 'bg-secondary'">
                            {{ subCategory.is_active ? '啟用' : '停用' }}
                          </span>
                        </td>
                        <td>
                          <Link :href="route('admin.accounts.sub-categories.show', subCategory.id)"
                                class="btn btn-sm btn-info">
                            <i class="bi bi-eye"></i>
                          </Link>
                          <Link :href="route('admin.accounts.sub-categories.edit', subCategory.id)"
                                class="btn btn-sm btn-warning ms-1">
                            <i class="bi bi-pencil"></i>
                          </Link>
                        </td>
                      </tr>
                    </tbody>
                  </table>
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
  category: Object
})

const formatDate = (dateString) => {
  if (!dateString) return '-'
  return new Date(dateString).toLocaleString('zh-TW')
}
</script>

<style scoped>
.info-box {
  display: flex;
  align-items: center;
  padding: 0.75rem;
  background: #f8f9fa;
  border-radius: 0.375rem;
}

.info-box-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 70px;
  height: 70px;
  border-radius: 50%;
  color: white;
  font-size: 1.5rem;
  margin-right: 1rem;
}

.info-box-content {
  flex: 1;
}

.info-box-text {
  display: block;
  font-size: 0.875rem;
  color: #6c757d;
}

.info-box-number {
  display: block;
  font-size: 1.5rem;
  font-weight: 600;
  color: #212529;
}

code {
  font-size: 0.875rem;
  color: #e83e8c;
}
</style>