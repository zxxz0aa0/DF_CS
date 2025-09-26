<template>
  <AdminLayout :user="$page.props.auth.user">
    <div class="content-wrapper">
      <!-- 頁面標題 -->
      <div class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h1>新增會計科目</h1>
            </div>
            <div class="col-sm-6">
              <div class="float-right">
                <Link :href="route('admin.accounts.account.details.index')"
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
                  <h3 class="card-title">科目資料</h3>
                </div>
                <form @submit.prevent="submit">
                  <div class="card-body">
                    <div class="row">
                      <!-- 總類選擇 -->
                      <div class="col-md-6">
                        <div class="form-group mb-3">
                          <label for="main_category_id">會計科目總類 <span class="text-danger">*</span></label>
                          <select id="main_category_id"
                                  v-model="form.main_category_id"
                                  class="form-select"
                                  :class="{ 'is-invalid': errors.main_category_id }"
                                  @change="onMainCategoryChange">
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

                      <!-- 子分類選擇 -->
                      <div class="col-md-6">
                        <div class="form-group mb-3">
                          <label for="sub_category_id">會計科目子分類 <span class="text-danger">*</span></label>
                          <select id="sub_category_id"
                                  v-model="form.sub_category_id"
                                  class="form-select"
                                  :class="{ 'is-invalid': errors.sub_category_id }"
                                  :disabled="!form.main_category_id || loadingSubCategories"
                                  @change="onSubCategoryChange">
                            <option value="">請選擇子分類</option>
                            <option v-for="subCategory in subCategories"
                                    :key="subCategory.id"
                                    :value="subCategory.id">
                              {{ subCategory.sub_category_code }} - {{ subCategory.sub_category_name }}
                            </option>
                          </select>
                          <div v-if="errors.sub_category_id" class="invalid-feedback">
                            {{ errors.sub_category_id }}
                          </div>
                          <small v-if="loadingSubCategories" class="text-muted">載入中...</small>
                        </div>
                      </div>
                    </div>

                    <div class="row">
                      <!-- 科目編號 -->
                      <div class="col-md-6">
                        <div class="form-group mb-3">
                          <label for="account_code">科目編號 <span class="text-danger">*</span></label>
                          <div class="input-group">
                            <input id="account_code"
                                   v-model="form.account_code"
                                   type="text"
                                   class="form-control"
                                   :class="{ 'is-invalid': errors.account_code }"
                                   placeholder="請輸入科目編號">
                            <button type="button"
                                    class="btn btn-outline-secondary"
                                    @click="generateNextCode"
                                    :disabled="!form.main_category_id || !form.sub_category_id || generating"
                                    title="自動產生編號">
                              <i class="bi" :class="generating ? 'bi-arrow-clockwise' : 'bi-gear'"></i>
                              {{ generating ? '產生中...' : '自動產生' }}
                            </button>
                          </div>
                          <div v-if="errors.account_code" class="invalid-feedback">
                            {{ errors.account_code }}
                          </div>
                          <small v-if="codeGuidance" class="form-text text-muted">
                            {{ codeGuidance }}
                          </small>
                        </div>
                      </div>

                      <!-- 科目名稱 -->
                      <div class="col-md-6">
                        <div class="form-group mb-3">
                          <label for="account_name">科目名稱 <span class="text-danger">*</span></label>
                          <input id="account_name"
                                 v-model="form.account_name"
                                 type="text"
                                 class="form-control"
                                 :class="{ 'is-invalid': errors.account_name }"
                                 placeholder="請輸入科目名稱">
                          <div v-if="errors.account_name" class="invalid-feedback">
                            {{ errors.account_name }}
                          </div>
                        </div>
                      </div>
                    </div>

                    <div class="row">
                      <!-- 英文科目名稱 -->
                      <div class="col-md-6">
                        <div class="form-group mb-3">
                          <label for="account_name_en">英文科目名稱</label>
                          <input id="account_name_en"
                                 v-model="form.account_name_en"
                                 type="text"
                                 class="form-control"
                                 :class="{ 'is-invalid': errors.account_name_en }"
                                 placeholder="請輸入英文科目名稱">
                          <div v-if="errors.account_name_en" class="invalid-feedback">
                            {{ errors.account_name_en }}
                          </div>
                        </div>
                      </div>

                      <!-- 科目性質 -->
                      <div class="col-md-3">
                        <div class="form-group mb-3">
                          <label for="account_type">科目性質 <span class="text-danger">*</span></label>
                          <select id="account_type"
                                  v-model="form.account_type"
                                  class="form-select"
                                  :class="{ 'is-invalid': errors.account_type }"
                                  @change="onAccountTypeChange">
                            <option value="">請選擇</option>
                            <option value="asset">資產</option>
                            <option value="liability">負債</option>
                            <option value="equity">權益</option>
                            <option value="revenue">收入</option>
                            <option value="expense">費用</option>
                          </select>
                          <div v-if="errors.account_type" class="invalid-feedback">
                            {{ errors.account_type }}
                          </div>
                        </div>
                      </div>

                      <!-- 借貸性質 -->
                      <div class="col-md-3">
                        <div class="form-group mb-3">
                          <label for="debit_credit">借貸性質 <span class="text-danger">*</span></label>
                          <select id="debit_credit"
                                  v-model="form.debit_credit"
                                  class="form-select"
                                  :class="{ 'is-invalid': errors.debit_credit }">
                            <option value="">請選擇</option>
                            <option value="debit">借方</option>
                            <option value="credit">貸方</option>
                          </select>
                          <div v-if="errors.debit_credit" class="invalid-feedback">
                            {{ errors.debit_credit }}
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- 科目說明 -->
                    <div class="row">
                      <div class="col-12">
                        <div class="form-group mb-3">
                          <label for="description">科目說明</label>
                          <textarea id="description"
                                    v-model="form.description"
                                    class="form-control"
                                    :class="{ 'is-invalid': errors.description }"
                                    rows="3"
                                    placeholder="請輸入科目說明">
                          </textarea>
                          <div v-if="errors.description" class="invalid-feedback">
                            {{ errors.description }}
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- 備註 -->
                    <div class="row">
                      <div class="col-12">
                        <div class="form-group mb-3">
                          <label for="notes">備註</label>
                          <textarea id="notes"
                                    v-model="form.notes"
                                    class="form-control"
                                    :class="{ 'is-invalid': errors.notes }"
                                    rows="2"
                                    placeholder="請輸入備註">
                          </textarea>
                          <div v-if="errors.notes" class="invalid-feedback">
                            {{ errors.notes }}
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
                      {{ processing ? '儲存中...' : '儲存' }}
                    </button>
                    <Link :href="route('admin.accounts.account.details.index')"
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
import { ref, reactive, watch } from 'vue'
import { useForm, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
  mainCategories: Array,
  errors: {
    type: Object,
    default: () => ({})
  }
})

const form = useForm({
  main_category_id: '',
  sub_category_id: '',
  account_code: '',
  account_name: '',
  account_name_en: '',
  description: '',
  account_type: '',
  debit_credit: '',
  notes: ''
})

const subCategories = ref([])
const loadingSubCategories = ref(false)
const generating = ref(false)
const codeGuidance = ref('')

const onMainCategoryChange = async () => {
  form.sub_category_id = ''
  form.account_code = ''
  subCategories.value = []
  codeGuidance.value = ''

  if (form.main_category_id) {
    loadingSubCategories.value = true
    try {
      const response = await axios.get(
        route('admin.accounts.api.sub-categories.by-main', form.main_category_id)
      )
      subCategories.value = response.data
    } catch (error) {
      console.error('載入子分類失敗:', error)
    } finally {
      loadingSubCategories.value = false
    }
  }
}

const onSubCategoryChange = () => {
  form.account_code = ''
  codeGuidance.value = ''
}

const onAccountTypeChange = () => {
  // 根據科目性質自動設定借貸性質
  const debitTypes = ['asset', 'expense']
  const creditTypes = ['liability', 'equity', 'revenue']

  if (debitTypes.includes(form.account_type)) {
    form.debit_credit = 'debit'
  } else if (creditTypes.includes(form.account_type)) {
    form.debit_credit = 'credit'
  }
}

const generateNextCode = async () => {
  if (!form.main_category_id || !form.sub_category_id) {
    alert('請先選擇總類和子分類')
    return
  }

  generating.value = true
  try {
    const response = await axios.get(route('admin.accounts.api.details.next-code'), {
      params: {
        main_category_id: form.main_category_id,
        sub_category_id: form.sub_category_id
      }
    })

    form.account_code = response.data.code
    codeGuidance.value = response.data.guidance
  } catch (error) {
    console.error('產生編號失敗:', error)
    alert('產生編號失敗，請重試')
  } finally {
    generating.value = false
  }
}

const submit = () => {
  form.post(route('admin.accounts.account.details.store'), {
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

.btn-outline-secondary:disabled {
  opacity: 0.6;
}
</style>