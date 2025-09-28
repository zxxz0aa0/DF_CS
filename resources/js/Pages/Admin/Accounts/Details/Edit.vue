<template>
  <AdminLayout :user="$page.props.auth.user">
    <div class="content-wrapper">
      <div class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h1>編輯會計科目</h1>
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
                          <div v-if="errors.account_code" class="invalid-feedback d-block">
                            {{ errors.account_code }}
                          </div>
                          <small v-if="codeGuidance" class="form-text text-muted">{{ codeGuidance }}</small>
                        </div>
                      </div>

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

                    <div class="row">
                      <div class="col-md-6">
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

                      <div class="col-md-6">
                        <div class="form-group mb-3">
                          <label for="notes">備註</label>
                          <textarea id="notes"
                                    v-model="form.notes"
                                    class="form-control"
                                    :class="{ 'is-invalid': errors.notes }"
                                    rows="3"
                                    placeholder="請輸入備註">
                          </textarea>
                          <div v-if="errors.notes" class="invalid-feedback">
                            {{ errors.notes }}
                          </div>
                        </div>
                      </div>
                    </div>

                    <div class="row">
                      <div class="col-md-3">
                        <div class="form-group mb-3">
                          <label for="is_active">狀態</label>
                          <div class="form-check">
                            <input id="is_active"
                                   v-model="form.is_active"
                                   type="checkbox"
                                   class="form-check-input">
                            <label for="is_active" class="form-check-label">啟用此科目</label>
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
                    <Link :href="account?.id ? route('admin.accounts.account.details.show', { detail: account.id }) : '#'"
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
import { ref, watch, watchEffect } from 'vue'
import { useForm, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
  account: {
    type: Object,
    required: true
  },
  mainCategories: {
    type: Array,
    default: () => []
  },
  subCategories: {
    type: Array,
    default: () => []
  },
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
  notes: '',
  is_active: true
})

const subCategories = ref([...props.subCategories])
const loadingSubCategories = ref(false)
const generating = ref(false)
const codeGuidance = ref('')

const fetchSubCategories = async (mainCategoryId) => {
  if (!mainCategoryId) {
    subCategories.value = []
    return
  }

  loadingSubCategories.value = true
  try {
    const response = await axios.get(
      route('admin.accounts.api.sub-categories.by-main', mainCategoryId)
    )
    subCategories.value = response.data
  } catch (error) {
    console.error('載入子分類失敗:', error)
  } finally {
    loadingSubCategories.value = false
  }
}

const onMainCategoryChange = async () => {
  form.sub_category_id = ''
  await fetchSubCategories(form.main_category_id)
  codeGuidance.value = ''
}

const onSubCategoryChange = () => {
  codeGuidance.value = ''
}

const onAccountTypeChange = () => {
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
  form.put(route('admin.accounts.account.details.update', { detail: props.account.id }), {
    preserveScroll: true
  })
}

const processing = ref(false)

watch(() => form.processing, (value) => {
  processing.value = value
})

// 初始化表單資料
watchEffect(async () => {
  if (!props.account) {
    return
  }

  // 先設置表單資料
  form.main_category_id = props.account.main_category_id || ''
  form.sub_category_id = props.account.sub_category_id || ''
  form.account_code = props.account.account_code || ''
  form.account_name = props.account.account_name || ''
  form.account_name_en = props.account.account_name_en || ''
  form.description = props.account.description || ''
  form.account_type = props.account.account_type || ''
  form.debit_credit = props.account.debit_credit || ''
  form.notes = props.account.notes || ''
  form.is_active = Boolean(props.account.is_active)

  // 如果有主分類ID且子分類列表為空，則載入子分類
  if (form.main_category_id && subCategories.value.length === 0) {
    await fetchSubCategories(form.main_category_id)
  }
})

// 監聽 props.account 變化，確保資料正確載入
watch(() => props.account, (newAccount) => {
  if (newAccount) {
    // 直接設置所有表單欄位
    Object.assign(form, {
      main_category_id: newAccount.main_category_id || '',
      sub_category_id: newAccount.sub_category_id || '',
      account_code: newAccount.account_code || '',
      account_name: newAccount.account_name || '',
      account_name_en: newAccount.account_name_en || '',
      description: newAccount.description || '',
      account_type: newAccount.account_type || '',
      debit_credit: newAccount.debit_credit || '',
      notes: newAccount.notes || '',
      is_active: Boolean(newAccount.is_active)
    })
  }
}, { immediate: true, deep: true })
</script>

<style scoped>
.form-group label {
  font-weight: 600;
}

.btn-outline-secondary:disabled {
  opacity: 0.6;
}
</style>
