<template>
  <AdminLayout :user="$page.props.auth.user">
    <div class="content-wrapper">
      <!-- 頁面標題 -->
      <div class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h1>會計子分類管理</h1>
            </div>
            <div class="col-sm-6">
              <div class="float-right d-flex gap-2">
                <Link :href="route('admin.accounts.sub-categories.template')"
                      class="btn btn-outline-secondary">
                  <i class="bi bi-download"></i> 下載模板
                </Link>
                <Link :href="route('admin.accounts.sub-categories.import')"
                      class="btn btn-success">
                  <i class="bi bi-upload"></i> 匯入
                </Link>
                <Link :href="route('admin.accounts.sub-categories.export')"
                      class="btn btn-info">
                  <i class="bi bi-file-earmark-excel"></i> 匯出
                </Link>
                <Link :href="route('admin.accounts.sub-categories.create')"
                      class="btn btn-primary">
                  <i class="bi bi-plus"></i> 新增子分類
                </Link>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- 內容區域 -->
      <div class="content">
        <div class="container-fluid">
          <!-- 搜尋篩選 -->
          <div class="row">
            <div class="col-12">
              <div class="card">
                <div class="card-header" style="background-color:#B3D9D9;">
                  <h3 class="card-title">搜尋篩選</h3>
                  <div class="card-tools">
                    <button type="button" class="btn btn-tool" @click="toggleSearchPanel">
                      <i class="bi" :class="showSearch ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
                    </button>
                  </div>
                </div>
                <div class="card-body" v-show="showSearch">
                  <div class="row">
                    <div class="col-md-3">
                      <label>所屬總類</label>
                      <select v-model="filters.main_category_id" class="form-select">
                        <option value="">全部總類</option>
                        <option v-for="category in mainCategories"
                                :key="category.id"
                                :value="category.id">
                          {{ category.category_name }}
                        </option>
                      </select>
                    </div>
                    <div class="col-md-4">
                      <label>關鍵字搜尋</label>
                      <input v-model="filters.search"
                             type="text"
                             class="form-control"
                             placeholder="輸入子分類代碼、名稱或說明">
                    </div>
                    <div class="col-md-2">
                      <label>狀態</label>
                      <select v-model="filters.is_active" class="form-select">
                        <option value="">全部狀態</option>
                        <option value="1">啟用</option>
                        <option value="0">停用</option>
                      </select>
                    </div>
                    <div class="col-md-3">
                      <label>&nbsp;</label>
                      <div class="d-flex">
                        <button @click="applyFilters" class="btn btn-primary me-2">
                          <i class="bi bi-search"></i> 搜尋
                        </button>
                        <button @click="resetFilters" class="btn btn-secondary">
                          <i class="bi bi-arrow-clockwise"></i> 重設
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- 子分類列表 -->
          <div class="row">
            <div class="col-12">
              <div class="card">
                <div class="card-header" style="background-color:#B3D9D9;">
                  <h3 class="card-title">子分類列表</h3>
                  <div class="card-tools">
                    <span class="badge bg-success">
                      共 {{ subCategories.total }} 筆資料
                    </span>
                  </div>
                </div>
                <div class="card-body table-responsive p-0">
                  <table class="table table-striped table-hover">
                    <thead>
                      <tr>
                        <th>所屬總類</th>
                        <th>子分類代碼</th>
                        <th>子分類名稱</th>
                        <th>說明</th>
                        <th>排序</th>
                        <th>會計科目數量</th>
                        <th>狀態</th>
                        <th>操作</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-if="subCategories.data.length === 0">
                        <td colspan="8" class="text-center">暫無資料</td>
                      </tr>
                      <tr v-for="subCategory in subCategories.data" :key="subCategory.id">
                        <td>
                          <span class="badge bg-primary">
                            {{ subCategory.main_category?.category_name }}
                          </span>
                        </td>
                        <td>
                          <code>{{ subCategory.sub_category_code }}</code>
                        </td>
                        <td>
                          <strong>{{ subCategory.sub_category_name }}</strong>
                        </td>
                        <td>{{ subCategory.description || '-' }}</td>
                        <td>{{ subCategory.sort_order }}</td>
                        <td>
                          <span class="badge bg-secondary">
                            {{ subCategory.account_details_count }} 個
                          </span>
                        </td>
                        <td>
                          <button @click="toggleStatus(subCategory)"
                                  class="btn btn-sm"
                                  :class="subCategory.is_active ? 'btn-success' : 'btn-secondary'">
                            {{ subCategory.is_active ? '啟用' : '停用' }}
                          </button>
                        </td>
                        <td>
                          <div class="btn-group">
                            <Link :href="route('admin.accounts.sub-categories.show', subCategory.id)"
                                  class="btn btn-sm btn-info"
                                  title="查看">
                              <i class="bi bi-eye"></i>
                            </Link>
                            <Link :href="route('admin.accounts.sub-categories.edit', subCategory.id)"
                                  class="btn btn-sm btn-warning"
                                  title="編輯">
                              <i class="bi bi-pencil"></i>
                            </Link>
                            <button @click="deleteSubCategory(subCategory)"
                                    class="btn btn-sm btn-danger"
                                    title="刪除"
                                    :disabled="subCategory.account_details_count > 0">
                              <i class="bi bi-trash"></i>
                            </button>
                          </div>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
                <div class="card-footer" v-if="subCategories.links">
                  <nav>
                    <template v-for="link in subCategories.links" :key="link.label">
                      <Link v-if="link.url"
                            :href="link.url"
                            class="btn btn-sm me-1"
                            :class="link.active ? 'btn-primary' : 'btn-outline-primary'"
                            v-html="link.label">
                      </Link>
                      <span v-else
                            class="btn btn-sm btn-secondary me-1"
                            v-html="link.label">
                      </span>
                    </template>
                  </nav>
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
import { ref, reactive } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
  subCategories: Object,
  mainCategories: Array,
  filters: Object
})

const showSearch = ref(true)

const filters = reactive({
  search: props.filters?.search || '',
  main_category_id: props.filters?.main_category_id || '',
  is_active: props.filters?.is_active || ''
})

const toggleSearchPanel = () => {
  showSearch.value = !showSearch.value
}

const applyFilters = () => {
  router.get(route('admin.accounts.sub-categories.index'), filters, {
    preserveState: true,
    preserveScroll: true
  })
}

const resetFilters = () => {
  Object.keys(filters).forEach(key => filters[key] = '')
  applyFilters()
}

const toggleStatus = (subCategory) => {
  if (!confirm(`確定要${subCategory.is_active ? '停用' : '啟用'}此子分類嗎？`)) {
    return
  }

  router.put(route('admin.accounts.sub-categories.toggle-status', subCategory.id), {}, {
    preserveState: true,
    onSuccess: () => {
      // 成功處理
    },
    onError: (errors) => {
      alert('操作失敗')
    }
  })
}

const deleteSubCategory = (subCategory) => {
  if (subCategory.account_details_count > 0) {
    alert('此子分類底下還有會計科目，無法刪除')
    return
  }

  if (!confirm('確定要刪除此會計子分類嗎？此操作無法復原！')) {
    return
  }

  router.delete(route('admin.accounts.sub-categories.destroy', subCategory.id), {
    preserveState: true,
    onSuccess: () => {
      // 成功處理
    },
    onError: (errors) => {
      alert('刪除失敗')
    }
  })
}
</script>

<style scoped>
.table th {
  font-weight: 600;
  background-color: #f8f9fa;
}

.btn-group .btn {
  margin-right: 2px;
}

.btn-group .btn:last-child {
  margin-right: 0;
}

code {
  font-size: 0.875rem;
  color: #e83e8c;
}
</style>
