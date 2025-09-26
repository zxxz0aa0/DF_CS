<template>
  <AdminLayout :user="$page.props.auth.user">
    <div class="content-wrapper">
      <!-- 頁面標題 -->
      <div class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h1>編輯會計總類</h1>
            </div>
            <div class="col-sm-6">
              <div class="float-right">
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
            <div class="col-12">
              <div class="card">
                <div class="card-header">
                  <h3 class="card-title">總類資料</h3>
                </div>
                <form @submit.prevent="submit">
                  <div class="card-body">
                    <div class="row">
                      <!-- 總類代碼 -->
                      <div class="col-md-6">
                        <div class="form-group mb-3">
                          <label for="category_code">總類代碼 <span class="text-danger">*</span></label>
                          <input id="category_code"
                                 v-model="form.category_code"
                                 type="text"
                                 class="form-control"
                                 :class="{ 'is-invalid': errors.category_code }"
                                 placeholder="請輸入總類代碼">
                          <div v-if="errors.category_code" class="invalid-feedback">
                            {{ errors.category_code }}
                          </div>
                          <small class="form-text text-muted">
                            建議使用數字，如：1, 2, 3 等
                          </small>
                        </div>
                      </div>

                      <!-- 總類名稱 -->
                      <div class="col-md-6">
                        <div class="form-group mb-3">
                          <label for="category_name">總類名稱 <span class="text-danger">*</span></label>
                          <input id="category_name"
                                 v-model="form.category_name"
                                 type="text"
                                 class="form-control"
                                 :class="{ 'is-invalid': errors.category_name }"
                                 placeholder="請輸入總類名稱">
                          <div v-if="errors.category_name" class="invalid-feedback">
                            {{ errors.category_name }}
                          </div>
                        </div>
                      </div>
                    </div>

                    <div class="row">
                      <!-- 排序順序 -->
                      <div class="col-md-6">
                        <div class="form-group mb-3">
                          <label for="sort_order">排序順序</label>
                          <input id="sort_order"
                                 v-model="form.sort_order"
                                 type="number"
                                 min="0"
                                 class="form-control"
                                 :class="{ 'is-invalid': errors.sort_order }"
                                 placeholder="0">
                          <div v-if="errors.sort_order" class="invalid-feedback">
                            {{ errors.sort_order }}
                          </div>
                          <small class="form-text text-muted">
                            數字越小排序越前面，預設為 0
                          </small>
                        </div>
                      </div>

                      <!-- 狀態 -->
                      <div class="col-md-6">
                        <div class="form-group mb-3">
                          <label for="is_active">狀態</label>
                          <div class="form-check">
                            <input id="is_active"
                                   v-model="form.is_active"
                                   type="checkbox"
                                   class="form-check-input">
                            <label for="is_active" class="form-check-label">
                              啟用此總類
                            </label>
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- 說明 -->
                    <div class="row">
                      <div class="col-12">
                        <div class="form-group mb-3">
                          <label for="description">說明</label>
                          <textarea id="description"
                                    v-model="form.description"
                                    class="form-control"
                                    :class="{ 'is-invalid': errors.description }"
                                    rows="3"
                                    placeholder="請輸入總類說明">
                          </textarea>
                          <div v-if="errors.description" class="invalid-feedback">
                            {{ errors.description }}
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="card-footer">
                    <button type="submit"
                            class="btn btn-primary"
                            :disabled="processing">
                      <i class="bi bi-check"></i>
                      {{ processing ? '更新中...' : '更新' }}
                    </button>
                    <Link :href="route('admin.accounts.main-categories.index')"
                          class="btn btn-secondary ms-2">
                      <i class="bi bi-x"></i> 取消
                    </Link>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, watch } from 'vue'
import { useForm, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
  category: Object,
  errors: {
    type: Object,
    default: () => ({})
  }
})

const form = useForm({
  category_code: props.category.category_code,
  category_name: props.category.category_name,
  description: props.category.description || '',
  sort_order: props.category.sort_order || 0,
  is_active: props.category.is_active
})

const submit = () => {
  form.put(route('admin.accounts.main-categories.update', props.category.id), {
    onSuccess: () => {
      // 成功處理
    },
    onError: (errors) => {
      console.error('表單驗證失敗:', errors)
    }
  })
}

const processing = ref(false)

// 監聽 form.processing
watch(() => form.processing, (newValue) => {
  processing.value = newValue
})
</script>

<style scoped>
.form-group label {
  font-weight: 600;
}

.text-danger {
  color: #dc3545 !important;
}
</style>