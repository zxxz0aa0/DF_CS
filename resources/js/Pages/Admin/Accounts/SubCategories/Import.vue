<template>
  <AdminLayout :user="$page.props.auth.user">
    <div class="content-wrapper">
      <!-- 頁面標題 -->
      <div class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h1>匯入會計子分類</h1>
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

      <!-- 內容區域 -->
      <div class="content">
        <div class="container-fluid">
          <div class="row">
            <div class="col-12">
              <!-- 匯入說明 -->
              <div class="card">
                <div class="card-header">
                  <h3 class="card-title">匯入說明</h3>
                </div>
                <div class="card-body">
                  <div class="alert alert-info">
                    <h5><i class="bi bi-info-circle"></i> 匯入須知</h5>
                    <ul class="mb-0">
                      <li>支援 CSV 和 Excel 格式檔案</li>
                      <li>檔案大小限制：10MB</li>
                      <li>必要欄位：主分類代碼、子分類代碼、子分類名稱</li>
                      <li>主分類代碼必須已存在於系統中</li>
                      <li>子分類代碼在同一主分類下必須唯一</li>
                      <li>可選擇是否更新已存在的子分類</li>
                    </ul>
                  </div>

                  <div class="row">
                    <div class="col-md-6">
                      <h6>欄位說明：</h6>
                      <table class="table table-sm table-bordered">
                        <thead>
                          <tr>
                            <th>欄位名稱</th>
                            <th>是否必要</th>
                            <th>說明</th>
                          </tr>
                        </thead>
                        <tbody>
                          <tr>
                            <td>主分類代碼</td>
                            <td><span class="badge bg-danger">必要</span></td>
                            <td>所屬主分類的代碼</td>
                          </tr>
                          <tr>
                            <td>子分類代碼</td>
                            <td><span class="badge bg-danger">必要</span></td>
                            <td>子分類的唯一代碼</td>
                          </tr>
                          <tr>
                            <td>子分類名稱</td>
                            <td><span class="badge bg-danger">必要</span></td>
                            <td>子分類的顯示名稱</td>
                          </tr>
                          <tr>
                            <td>說明</td>
                            <td><span class="badge bg-secondary">選填</span></td>
                            <td>子分類的詳細說明</td>
                          </tr>
                          <tr>
                            <td>排序</td>
                            <td><span class="badge bg-secondary">選填</span></td>
                            <td>顯示順序，預設為 0</td>
                          </tr>
                          <tr>
                            <td>狀態</td>
                            <td><span class="badge bg-secondary">選填</span></td>
                            <td>true/false 或 啟用/停用，預設為 true</td>
                          </tr>
                        </tbody>
                      </table>
                    </div>
                    <div class="col-md-6">
                      <h6>快速操作：</h6>
                      <div class="d-grid gap-2">
                        <Link :href="route('admin.accounts.sub-categories.template')"
                              class="btn btn-outline-primary">
                          <i class="bi bi-download"></i> 下載匯入模板
                        </Link>
                        <Link :href="route('admin.accounts.sub-categories.export')"
                              class="btn btn-outline-info">
                          <i class="bi bi-file-earmark-excel"></i> 匯出現有資料作為參考
                        </Link>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- 匯入表單 -->
              <div class="card">
                <div class="card-header">
                  <h3 class="card-title">選擇檔案</h3>
                </div>
                <form @submit.prevent="submit">
                  <div class="card-body">
                    <div class="row">
                      <div class="col-md-8">
                        <div class="form-group mb-3">
                          <label for="file">選擇匯入檔案 <span class="text-danger">*</span></label>
                          <input
                            id="file"
                            ref="fileInput"
                            type="file"
                            class="form-control"
                            :class="{ 'is-invalid': errors.file }"
                            accept=".csv,.xlsx,.xls"
                            @change="handleFileChange">
                          <div v-if="errors.file" class="invalid-feedback">
                            {{ errors.file }}
                          </div>
                          <small class="form-text text-muted">
                            支援格式：CSV, Excel (xlsx, xls)，檔案大小限制：10MB
                          </small>
                        </div>

                        <div class="form-group mb-3">
                          <div class="form-check">
                            <input
                              id="update_existing"
                              v-model="form.update_existing"
                              type="checkbox"
                              class="form-check-input">
                            <label for="update_existing" class="form-check-label">
                              更新已存在的子分類
                            </label>
                          </div>
                          <small class="form-text text-muted">
                            勾選此選項將會更新已存在的子分類，否則跳過重複的記錄
                          </small>
                        </div>

                        <!-- 檔案預覽 -->
                        <div v-if="selectedFile" class="alert alert-success">
                          <h6><i class="bi bi-file-check"></i> 已選擇檔案</h6>
                          <p class="mb-1"><strong>檔案名稱：</strong>{{ selectedFile.name }}</p>
                          <p class="mb-1"><strong>檔案大小：</strong>{{ formatFileSize(selectedFile.size) }}</p>
                          <p class="mb-0"><strong>檔案類型：</strong>{{ selectedFile.type || '未知' }}</p>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="card-footer">
                    <button
                      type="submit"
                      class="btn btn-primary"
                      :disabled="!selectedFile || processing">
                      <i class="bi" :class="processing ? 'bi-hourglass-split' : 'bi-upload'"></i>
                      {{ processing ? '匯入中...' : '開始匯入' }}
                    </button>
                    <Link :href="route('admin.accounts.sub-categories.index')"
                          class="btn btn-secondary ms-2">
                      取消
                    </Link>
                  </div>
                </form>
              </div>

              <!-- 匯入結果 -->
              <div v-if="importResults" class="card">
                <div class="card-header">
                  <h3 class="card-title">匯入結果</h3>
                </div>
                <div class="card-body">
                  <div class="row">
                    <div class="col-md-3">
                      <div class="info-box">
                        <span class="info-box-icon bg-primary"><i class="bi bi-file-text"></i></span>
                        <div class="info-box-content">
                          <span class="info-box-text">總計</span>
                          <span class="info-box-number">{{ importResults.total }}</span>
                        </div>
                      </div>
                    </div>
                    <div class="col-md-3">
                      <div class="info-box">
                        <span class="info-box-icon bg-success"><i class="bi bi-check-circle"></i></span>
                        <div class="info-box-content">
                          <span class="info-box-text">成功</span>
                          <span class="info-box-number">{{ importResults.success }}</span>
                        </div>
                      </div>
                    </div>
                    <div class="col-md-3">
                      <div class="info-box">
                        <span class="info-box-icon bg-danger"><i class="bi bi-x-circle"></i></span>
                        <div class="info-box-content">
                          <span class="info-box-text">錯誤</span>
                          <span class="info-box-number">{{ importResults.errors }}</span>
                        </div>
                      </div>
                    </div>
                    <div class="col-md-3">
                      <div class="info-box">
                        <span class="info-box-icon bg-warning"><i class="bi bi-skip-forward"></i></span>
                        <div class="info-box-content">
                          <span class="info-box-text">跳過</span>
                          <span class="info-box-number">{{ importResults.skipped }}</span>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- 錯誤詳情 -->
                  <div v-if="importResults.error_details && importResults.error_details.length > 0" class="mt-4">
                    <h6>錯誤詳情：</h6>
                    <div class="alert alert-danger">
                      <ul class="mb-0">
                        <li v-for="(error, index) in importResults.error_details" :key="index">
                          {{ error }}
                        </li>
                      </ul>
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
import { ref, reactive } from 'vue'
import { useForm, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
  mainCategories: {
    type: Array,
    default: () => []
  },
  errors: {
    type: Object,
    default: () => ({})
  },
  importResults: {
    type: Object,
    default: null
  }
})

const form = useForm({
  file: null,
  update_existing: false
})

const fileInput = ref(null)
const selectedFile = ref(null)
const processing = ref(false)

const handleFileChange = (event) => {
  const file = event.target.files[0]
  if (file) {
    selectedFile.value = file
    form.file = file
  } else {
    selectedFile.value = null
    form.file = null
  }
}

const formatFileSize = (bytes) => {
  if (bytes === 0) return '0 Bytes'

  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))

  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
}

const submit = () => {
  if (!selectedFile.value) {
    alert('請選擇要匯入的檔案')
    return
  }

  processing.value = true

  form.post(route('admin.accounts.sub-categories.import.store'), {
    preserveScroll: true,
    onSuccess: () => {
      // 清除表單
      fileInput.value.value = ''
      selectedFile.value = null
      form.file = null
      form.update_existing = false
    },
    onError: () => {
      // 錯誤處理
    },
    onFinish: () => {
      processing.value = false
    }
  })
}
</script>

<style scoped>
.info-box {
  display: block;
  min-height: 90px;
  background: #fff;
  width: 100%;
  box-shadow: 0 1px 3px rgba(0,0,0,0.12), 0 1px 2px rgba(0,0,0,0.24);
  border-radius: 2px;
  margin-bottom: 15px;
}

.info-box-icon {
  border-top-left-radius: 2px;
  border-top-right-radius: 0;
  border-bottom-right-radius: 0;
  border-bottom-left-radius: 2px;
  display: block;
  float: left;
  height: 90px;
  width: 90px;
  text-align: center;
  font-size: 45px;
  line-height: 90px;
  background: rgba(0,0,0,0.2);
  color: rgba(255,255,255,0.8);
}

.info-box-content {
  padding: 5px 10px;
  margin-left: 90px;
}

.info-box-text {
  text-transform: uppercase;
  font-weight: bold;
  font-size: 13px;
}

.info-box-number {
  display: block;
  font-weight: bold;
  font-size: 18px;
}

.bg-primary { background-color: #007bff !important; }
.bg-success { background-color: #28a745 !important; }
.bg-danger { background-color: #dc3545 !important; }
.bg-warning { background-color: #ffc107 !important; }
</style>