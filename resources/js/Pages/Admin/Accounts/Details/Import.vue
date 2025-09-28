<template>
  <AdminLayout :user="$page.props.auth.user">
    <div class="content-wrapper">
      <div class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h1>匯入會計科目</h1>
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
            <div class="col-12 col-lg-8">
              <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                  <h3 class="card-title mb-0">匯入設定</h3>
                  <a :href="route('admin.accounts.account.details.template')"
                     class="btn btn-outline-primary">
                    <i class="bi bi-download"></i> 下載匯入範本
                  </a>
                </div>
                <form @submit.prevent="submit">
                  <div class="card-body">
                    <div class="mb-3">
                      <label for="import_file" class="form-label">選擇匯入檔案 <span class="text-danger">*</span></label>
                      <input id="import_file"
                             ref="fileInput"
                             type="file"
                             class="form-control"
                             accept=".xlsx,.xls,.csv"
                             @change="onFileChange">
                      <div v-if="errors.import_file" class="invalid-feedback d-block">
                        {{ errors.import_file }}
                      </div>
                      <small class="form-text text-muted">支援 CSV 或 Excel 檔案，請使用系統提供之範本。</small>
                    </div>

                    <div class="mb-3">
                      <label class="form-label">匯入選項</label>
                      <div class="form-check">
                        <input id="overwrite_existing"
                               v-model="form.overwrite_existing"
                               type="checkbox"
                               class="form-check-input">
                        <label class="form-check-label" for="overwrite_existing">
                          若編號重複則覆寫既有科目資料
                        </label>
                      </div>
                      <div class="form-check">
                        <input id="skip_inactive"
                               v-model="form.skip_inactive"
                               type="checkbox"
                               class="form-check-input">
                        <label class="form-check-label" for="skip_inactive">
                          略過停用的科目資料
                        </label>
                      </div>
                    </div>

                    <div v-if="importSummary" class="alert" :class="importSummary.success ? 'alert-success' : 'alert-danger'">
                      <h6 class="mb-1">匯入結果</h6>
                      <p class="mb-0">{{ importSummary.message }}</p>
                      <ul v-if="importSummary.details?.length" class="mt-2 mb-0">
                        <li v-for="(detail, index) in importSummary.details" :key="index">{{ detail }}</li>
                      </ul>
                    </div>
                  </div>

                  <div class="card-footer">
                    <button type="submit"
                            class="btn btn-primary"
                            :disabled="processing">
                      <i class="bi bi-upload"></i>
                      {{ processing ? '匯入中...' : '開始匯入' }}
                    </button>
                    <button type="button"
                            class="btn btn-outline-secondary ms-2"
                            @click="resetForm"
                            :disabled="processing">
                      <i class="bi bi-arrow-clockwise"></i> 重設
                    </button>
                  </div>
                </form>
              </div>
            </div>

            <div class="col-12 col-lg-4">
              <div class="card">
                <div class="card-header">
                  <h3 class="card-title">匯入注意事項</h3>
                </div>
                <div class="card-body">
                  <ul class="mb-0 small">
                    <li>請先下載範本並依欄位格式填寫資料。</li>
                    <li>科目編號需符合系統編碼規則，並維持唯一性。</li>
                    <li>若勾選覆寫選項，系統將以匯入資料更新既有科目。</li>
                    <li>匯入完成後請至列表確認資料是否正確。</li>
                  </ul>
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
import { ref, watch } from 'vue'
import { useForm, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
  importSummary: {
    type: Object,
    default: null
  },
  errors: {
    type: Object,
    default: () => ({})
  }
})

const form = useForm({
  import_file: null,
  overwrite_existing: false,
  skip_inactive: false
})

const fileInput = ref(null)
const processing = ref(false)

const onFileChange = (event) => {
  const file = event.target.files?.[0]
  form.import_file = file || null
}

const resetForm = () => {
  form.reset()
  if (fileInput.value) {
    fileInput.value.value = ''
  }
}

const submit = () => {
  if (!form.import_file) {
    alert('請先選擇匯入檔案')
    return
  }

  form.post(route('admin.accounts.account.details.import.store'), {
    forceFormData: true,
    preserveScroll: true
  })
}

watch(() => form.processing, (value) => {
  processing.value = value
})
</script>

<style scoped>
.card-title {
  font-weight: 600;
}
</style>
