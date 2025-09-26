<template>
  <AdminLayout :user="$page.props.auth.user">
    <div class="content-wrapper">
      <!-- 頁面標題 -->
      <div class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h1>會計總類管理</h1>
            </div>
            <div class="col-sm-6">
              <div class="float-right">
                <Link :href="route('admin.accounts.main-categories.create')"
                      class="btn btn-primary">
                  <i class="bi bi-plus"></i> 新增總類
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
                <div class="card-header">
                  <h3 class="card-title">搜尋篩選</h3>
                  <div class="card-tools">
                    <button type="button" class="btn btn-tool" @click="toggleSearchPanel">
                      <i class="bi" :class="showSearch ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
                    </button>
                  </div>
                </div>
                <div class="card-body" v-show="showSearch">
                  <div class="row">
                    <div class="col-md-6">
                      <label>關鍵字搜尋</label>
                      <input v-model="filters.search"
                             type="text"
                             class="form-control"
                             placeholder="輸入總類代碼、名稱或說明">
                    </div>
                    <div class="col-md-3">
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

          <!-- 總類列表 -->
          <div class="row">
            <div class="col-12">
              <div class="card">
                <div class="card-header">
                  <h3 class="card-title">總類列表</h3>
                  <div class="card-tools">
                    <span class="badge bg-info">
                      共 {{ categories.total }} 筆資料
                    </span>
                  </div>
                </div>
                <div class="card-body table-responsive p-0">
                  <table class="table table-striped table-hover">
                    <thead>
                      <tr>
                        <th>總類代碼</th>
                        <th>總類名稱</th>
                        <th>說明</th>
                        <th>排序</th>
                        <th>子分類數量</th>
                        <th>狀態</th>
                        <th>操作</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-if="categories.data.length === 0">
                        <td colspan="7" class="text-center">暫無資料</td>
                      </tr>
                      <tr v-for="category in categories.data" :key="category.id">
                        <td>
                          <code>{{ category.category_code }}</code>
                        </td>
                        <td>
                          <strong>{{ category.category_name }}</strong>
                        </td>
                        <td>{{ category.description || '-' }}</td>
                        <td>{{ category.sort_order }}</td>
                        <td>
                          <span class="badge bg-secondary">
                            {{ category.sub_categories_count }} 個
                          </span>
                        </td>
                        <td>
                          <button @click="toggleStatus(category)"
                                  class="btn btn-sm"
                                  :class="category.is_active ? 'btn-success' : 'btn-secondary'">
                            {{ category.is_active ? '啟用' : '停用' }}
                          </button>
                        </td>
                        <td>
                          <div class="btn-group">
                            <Link :href="route('admin.accounts.main-categories.show', category.id)"
                                  class="btn btn-sm btn-info"
                                  title="查看">
                              <i class="bi bi-eye"></i>
                            </Link>
                            <Link :href="route('admin.accounts.main-categories.edit', category.id)"
                                  class="btn btn-sm btn-warning"
                                  title="編輯">
                              <i class="bi bi-pencil"></i>
                            </Link>
                            <button @click="deleteCategory(category)"
                                    class="btn btn-sm btn-danger"
                                    title="刪除"
                                    :disabled="category.sub_categories_count > 0">
                              <i class="bi bi-trash"></i>
                            </button>
                          </div>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
                <div class="card-footer" v-if="categories.links">
                  <nav>
                    <template v-for="link in categories.links" :key="link.label">
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
  categories: Object,
  filters: Object
})

const showSearch = ref(true)

const filters = reactive({
  search: props.filters?.search || '',
  is_active: props.filters?.is_active || ''
})

const toggleSearchPanel = () => {
  showSearch.value = !showSearch.value
}

const applyFilters = () => {
  router.get(route('admin.accounts.main-categories.index'), filters, {
    preserveState: true,
    preserveScroll: true
  })
}

const resetFilters = () => {
  Object.keys(filters).forEach(key => filters[key] = '')
  applyFilters()
}

const toggleStatus = (category) => {
  if (!confirm(`確定要${category.is_active ? '停用' : '啟用'}此總類嗎？`)) {
    return
  }

  router.put(route('admin.accounts.main-categories.toggle-status', category.id), {}, {
    preserveState: true,
    onSuccess: () => {
      // 成功處理
    },
    onError: (errors) => {
      alert('操作失敗')
    }
  })
}

const deleteCategory = (category) => {
  if (category.sub_categories_count > 0) {
    alert('此總類底下還有子分類，無法刪除')
    return
  }

  if (!confirm('確定要刪除此會計總類嗎？此操作無法復原！')) {
    return
  }

  router.delete(route('admin.accounts.main-categories.destroy', category.id), {
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