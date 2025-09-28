<template>
  <AdminLayout :user="$page.props.auth.user">
    <div class="content-wrapper">
      <div class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h1>編輯會計子分類</h1>
            </div>
            <div class="col-sm-6">
              <div class="float-right">
                <Link :href="route('admin.accounts.sub-categories.index')"
                      class="btn btn-secondary">
                  <i class="bi bi-arrow-left"></i> 返回列表
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
                  <h3 class="card-title">子分類資料</h3>
                </div>
                <form @submit.prevent="submit">
                  <div class="card-body">
                    <div class="row">
                      <div class="col-md-6">
                        <div class="form-group mb-3">
                          <label for="main_category_id">所屬總類 <span class="text-danger">*</span></label>
                          <select id="main_category_id"
                                  v-model="form.main_category_id"
                                  class="form-select"
                                  :class="{ 'is-invalid': errors.main_category_id }">
                            <option value="">請選擇總類</option>
                            <option v-for="category in mainCategories"
                                    :key="category.id"
                                    :value="category.id">
                              {{ category.category_code }} - {{ category.category_name }}
                            </option>
                          </select>
                          <div v-if="errors.main_category_id" class="invalid-feedback">
                            {{ errors.main_category_id }}
                          </div>
                        </div>
                      </div>

                      <div class="col-md-6">
                        <div class="form-group mb-3">
                          <label for="sub_category_code">子分類代碼 <span class="text-danger">*</span></label>
                          <input id="sub_category_code"
                                 v-model="form.sub_category_code"
                                 type="text"
                                 class="form-control"
                                 :class="{ 'is-invalid': errors.sub_category_code }"
                                 placeholder="請輸入子分類代碼">
                          <div v-if="errors.sub_category_code" class="invalid-feedback">
                            {{ errors.sub_category_code }}
                          </div>
                          <small class="form-text text-muted">建議使用兩位數字，如：01, 02, 03 等</small>
                        </div>
                      </div>
                    </div>

                    <div class="row">
                      <div class="col-md-6">
                        <div class="form-group mb-3">
                          <label for="sub_category_name">子分類名稱 <span class="text-danger">*</span></label>
                          <input id="sub_category_name"
                                 v-model="form.sub_category_name"
                                 type="text"
                                 class="form-control"
                                 :class="{ 'is-invalid': errors.sub_category_name }"
                                 placeholder="請輸入子分類名稱">
                          <div v-if="errors.sub_category_name" class="invalid-feedback">
                            {{ errors.sub_category_name }}
                          </div>
                        </div>
                      </div>

                      <div class="col-md-3">
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
                        </div>
                      </div>

                      <div class="col-md-3">
                        <div class="form-group mb-3">
                          <label for="is_active">狀態</label>
                          <div class="form-check">
                            <input id="is_active"
                                   v-model="form.is_active"
                                   type="checkbox"
                                   class="form-check-input">
                            <label for="is_active" class="form-check-label">啟用此子分類</label>
                          </div>
                        </div>
                      </div>
                    </div>

                    <div class="row">
                      <div class="col-12">
                        <div class="form-group mb-3">
                          <label for="description">說明</label>
                          <textarea id="description"
                                    v-model="form.description"
                                    class="form-control"
                                    :class="{ 'is-invalid': errors.description }"
                                    rows="3"
                                    placeholder="請輸入子分類說明">
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
                      {{ processing ? '儲存中...' : '更新' }}
                    </button>
                    <Link :href="route('admin.accounts.sub-categories.show', subCategory.id)"
                          class="btn btn-secondary ms-2">
                      <i class="bi bi-eye"></i> 查看
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
  subCategory: {
    type: Object,
    required: true
  },
  mainCategories: {
    type: Array,
    default: () => []
  },
  errors: {
    type: Object,
    default: () => ({})
  }
})

const form = useForm({
  main_category_id: props.subCategory.main_category_id || '',
  sub_category_code: props.subCategory.sub_category_code || '',
  sub_category_name: props.subCategory.sub_category_name || '',
  description: props.subCategory.description || '',
  sort_order: props.subCategory.sort_order ?? 0,
  is_active: Boolean(props.subCategory.is_active ?? true)
})

const submit = () => {
  form.put(route('admin.accounts.sub-categories.update', props.subCategory.id), {
    preserveScroll: true
  })
}

const processing = ref(false)

watch(() => form.processing, (value) => {
  processing.value = value
})
</script>

<style scoped>
.form-group label {
  font-weight: 600;
}
</style>
