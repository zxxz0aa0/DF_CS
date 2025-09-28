<template>
  <AdminLayout :user="$page.props.auth.user">
    <div class="content-wrapper">
      <!-- 頁面標題 -->
      <div class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h1>會計科目管理</h1>
            </div>
            <div class="col-sm-6">
              <div class="float-right d-flex gap-2">
                <Link :href="route('admin.accounts.account.details.import')"
                      class="btn btn-success">
                  <i class="bi bi-upload"></i> 匯入
                </Link>
                <Link :href="route('admin.accounts.account.details.create')"
                      class="btn btn-primary">
                  <i class="bi bi-plus"></i> 新增科目
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
                    <div class="col-md-3">
                      <label>總類</label>
                      <select v-model="filters.main_category_id"
                              class="form-select"
                              @change="onMainCategoryChange">
                        <option value="">全部總類</option>
                        <option v-for="category in mainCategories"
                                :key="category.id"
                                :value="category.id">
                          {{ category.category_name }}
                        </option>
                      </select>
                    </div>
                    <div class="col-md-3">
                      <label>子分類</label>
                      <select v-model="filters.sub_category_id"
                              class="form-select"
                              :disabled="!filters.main_category_id">
                        <option value="">全部子分類</option>
                        <option v-for="subCategory in filteredSubCategories"
                                :key="subCategory.id"
                                :value="subCategory.id">
                          {{ subCategory.sub_category_name }}
                        </option>
                      </select>
                    </div>
                    <div class="col-md-2">
                      <label>科目編號/名稱</label>
                      <input v-model="filters.search"
                             type="text"
                             class="form-control"
                             placeholder="輸入關鍵字">
                    </div>
                    <div class="col-md-2">
                      <label>科目性質</label>
                      <select v-model="filters.account_type" class="form-select">
                        <option value="">全部性質</option>
                        <option value="asset">資產</option>
                        <option value="liability">負債</option>
                        <option value="equity">權益</option>
                        <option value="revenue">收入</option>
                        <option value="expense">費用</option>
                      </select>
                    </div>
                    <div class="col-md-2">
                      <label>狀態</label>
                      <select v-model="filters.is_active" class="form-select">
                        <option value="">全部狀態</option>
                        <option value="1">啟用</option>
                        <option value="0">停用</option>
                      </select>
                    </div>
                  </div>
                  <div class="row mt-1">
                    <div class="col-md-12">
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

          <!-- 科目列表 -->
          <div class="row">
            <div class="col-12">
              <div class="card">
                <div class="card-header">
                  <h3 class="card-title">科目列表</h3>
                  <div class="card-tools">
                    <span class="badge bg-info">
                      共 {{ accounts.total }} 筆資料
                    </span>
                  </div>
                </div>
                <div class="card-body table-responsive p-0">
                  <table class="table table-striped table-hover">
                    <thead>
                      <tr>
                        <th>科目編號</th>
                        <th>科目名稱</th>
                        <th>總類</th>
                        <th>子分類</th>
                        <th>科目性質</th>
                        <th>借貸性質</th>
                        <th>狀態</th>
                        <th>操作</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-if="accounts.data.length === 0">
                        <td colspan="8" class="text-center">暫無資料</td>
                      </tr>
                      <tr v-for="account in accounts.data" :key="account.id">
                        <td>
                          <code>{{ account.account_code }}</code>
                        </td>
                        <td>
                          <strong>{{ account.account_name }}</strong>
                          <small v-if="account.account_name_en" class="text-muted d-block">
                            {{ account.account_name_en }}
                          </small>
                        </td>
                        <td>{{ account.main_category?.category_name }}</td>
                        <td>{{ account.sub_category?.sub_category_name }}</td>
                        <td>
                          <span class="badge"
                                :class="getAccountTypeBadge(account.account_type)">
                            {{ getAccountTypeText(account.account_type) }}
                          </span>
                        </td>
                        <td>
                          <span class="badge"
                                :class="account.debit_credit === 'debit' ? 'bg-info' : 'bg-warning'">
                            {{ account.debit_credit === 'debit' ? '借方' : '貸方' }}
                          </span>
                        </td>
                        <td>
                          <button @click="toggleStatus(account)"
                                  class="btn btn-sm"
                                  :class="account.is_active ? 'btn-success' : 'btn-secondary'">
                            {{ account.is_active ? '啟用' : '停用' }}
                          </button>
                        </td>
                        <td>
                          <div class="btn-group">
                            <Link :href="account?.id ? route('admin.accounts.account.details.show', { detail: account.id }) : '#'"
                                  class="btn btn-sm btn-info"
                                  title="查看">
                              <i class="bi bi-eye"></i>
                            </Link>
                            <Link :href="route('admin.accounts.account.details.edit', { detail: account.id })"
                                  class="btn btn-sm btn-warning"
                                  title="編輯">
                              <i class="bi bi-pencil"></i>
                            </Link>
                            <button @click="deleteAccount(account)"
                                    class="btn btn-sm btn-danger"
                                    title="刪除"
                                    :disabled="account.has_children">
                              <i class="bi bi-trash"></i>
                            </button>
                          </div>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
                <div class="card-footer" v-if="accounts.links">
                  <nav>
                    <template v-for="link in accounts.links" :key="link.label">
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
import { ref, reactive, computed, onMounted } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
  accounts: Object,
  mainCategories: Array,
  subCategories: Array,
  filters: Object
})

const showSearch = ref(true)

const filters = reactive({
  main_category_id: props.filters?.main_category_id || '',
  sub_category_id: props.filters?.sub_category_id || '',
  search: props.filters?.search || '',
  account_type: props.filters?.account_type || '',
  is_active: props.filters?.is_active || ''
})

const filteredSubCategories = computed(() => {
  if (!filters.main_category_id) return []
  return props.subCategories.filter(sub =>
    sub.main_category_id == filters.main_category_id
  )
})

const toggleSearchPanel = () => {
  showSearch.value = !showSearch.value
}

const onMainCategoryChange = async () => {
  filters.sub_category_id = ''

  if (filters.main_category_id) {
    // 載入對應的子分類
    try {
      const response = await axios.get(
        route('admin.accounts.api.sub-categories.by-main', filters.main_category_id)
      )
      // 更新 subCategories (這裡需要用 Inertia 的方式更新)
      router.reload({
        only: ['subCategories'],
        data: { main_category_id: filters.main_category_id }
      })
    } catch (error) {
      console.error('載入子分類失敗:', error)
    }
  }
}

const applyFilters = () => {
  router.get(route('admin.accounts.account.details.index'), filters, {
    preserveState: true,
    preserveScroll: true
  })
}

const resetFilters = () => {
  Object.keys(filters).forEach(key => filters[key] = '')
  applyFilters()
}

const toggleStatus = (account) => {
  if (!confirm(`確定要${account.is_active ? '停用' : '啟用'}此科目嗎？`)) {
    return
  }

  router.put(route('admin.accounts.details.toggle-status', account.id), {}, {
    preserveState: true,
    onSuccess: () => {
      // 成功處理
    },
    onError: (errors) => {
      alert('操作失敗')
    }
  })
}

const deleteAccount = (account) => {
  if (account.has_children) {
    alert('此科目底下還有子科目，無法刪除')
    return
  }

  if (!confirm('確定要刪除此會計科目嗎？此操作無法復原！')) {
    return
  }

  router.delete(route('admin.accounts.account.details.destroy', { detail: account.id }), {
    preserveState: true,
    onSuccess: () => {
      // 成功處理
    },
    onError: (errors) => {
      alert('刪除失敗')
    }
  })
}

const getAccountTypeBadge = (type) => {
  const badges = {
    asset: 'bg-primary',
    liability: 'bg-danger',
    equity: 'bg-success',
    revenue: 'bg-info',
    expense: 'bg-warning'
  }
  return badges[type] || 'bg-secondary'
}

const getAccountTypeText = (type) => {
  const texts = {
    asset: '資產',
    liability: '負債',
    equity: '權益',
    revenue: '收入',
    expense: '費用'
  }
  return texts[type] || type
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
