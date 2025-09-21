<template>
    <AdminLayout :user="$page.props.auth.user">
        <div class="content-wrapper">
            <!-- Content Header -->
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1 class="m-0">車輛管理</h1>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item">
                                    <Link :href="route('admin.dashboard')">儀表板</Link>
                                </li>
                                <li class="breadcrumb-item active">車輛管理</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main content -->
            <section class="content">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">車輛列表</h3>


                                    <div class="card-tools">
                                        <div class="btn-group btn-group-sm mr-2">
                                            <button
                                                v-if="$page.props.auth.permissions?.includes('view vehicles')"
                                                type="button"
                                                class="btn btn-success"
                                                @click="exportVehicles"
                                                :disabled="isExporting"
                                            >
                                                <i class="fas fa-download"></i>
                                                <span v-if="isExporting">匯出中...</span>
                                                <span v-else>匯出Excel</span>
                                            </button>

                                            <button
                                                v-if="$page.props.auth.permissions?.includes('create vehicles')"
                                                type="button"
                                                class="btn btn-info"
                                                @click="showImportModal"
                                            >
                                                <i class="fas fa-upload"></i> 匯入Excel
                                            </button>

                                            <a
                                                v-if="$page.props.auth.permissions?.includes('create vehicles')"
                                                :href="route('admin.vehicles.template')"
                                                class="btn btn-secondary"
                                                target="_blank"
                                            >
                                                <i class="fas fa-file-download"></i> 下載範本
                                            </a>
                                        </div>

                                        <Link
                                            v-if="$page.props.auth.permissions?.includes('create vehicles')"
                                            :href="route('admin.vehicles.create')"
                                            class="btn btn-primary btn-sm"
                                        >
                                            <i class="fas fa-plus"></i> 新增車輛
                                        </Link>
                                    </div>
                                </div>

                                <!-- 搜尋與篩選 -->
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="search">搜尋</label>
                                                <input
                                                    id="search"
                                                    v-model="searchForm.search"
                                                    type="text"
                                                    class="form-control"
                                                    placeholder="車牌號碼、車主名稱..."
                                                    @input="debounceSearch"
                                                />
                                            </div>
                                        </div>

                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label for="status">狀態</label>
                                                <select
                                                    id="status"
                                                    v-model="searchForm.status"
                                                    class="form-control"
                                                    @change="search"
                                                >
                                                    <option value="">全部</option>
                                                    <option value="active">在籍</option>
                                                    <option value="inactive">退籍</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="company_category">公司類別</label>
                                                <select
                                                    id="company_category"
                                                    v-model="searchForm.company_category_id"
                                                    class="form-control"
                                                    @change="search"
                                                >
                                                    <option value="">全部</option>
                                                    <option
                                                        v-for="category in companyCategories"
                                                        :key="category.id"
                                                        :value="category.id"
                                                    >
                                                        {{ category.name }}
                                                    </option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label>&nbsp;</label>
                                                <div class="form-check">
                                                    <input
                                                        id="expiring_inspection"
                                                        v-model="searchForm.expiring_inspection"
                                                        type="checkbox"
                                                        class="form-check-input"
                                                        @change="search"
                                                    />
                                                    <label for="expiring_inspection" class="form-check-label">
                                                        即將到期檢驗
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label>&nbsp;</label>
                                                <div class="d-block">
                                                    <button
                                                        type="button"
                                                        class="btn btn-secondary btn-sm"
                                                        @click="clearFilters"
                                                    >
                                                        清除篩選
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- 車輛列表 -->
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped">
                                            <thead>
                                                <tr>
                                                    <th>車牌號碼</th>
                                                    <th>車主名稱</th>
                                                    <th>公司類別</th>
                                                    <th>車輛類型</th>
                                                    <th>檢驗到期日</th>
                                                    <th>剩餘天數</th>
                                                    <th>狀態</th>
                                                    <th>操作</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-if="vehicles.data.length === 0">
                                                    <td colspan="8" class="text-center">無車輛資料</td>
                                                </tr>
                                                <tr v-for="vehicle in vehicles.data" :key="vehicle.id">
                                                    <td>
                                                        <strong>{{ vehicle.license_number }}</strong>
                                                    </td>
                                                    <td>{{ vehicle.owner_name }}</td>
                                                    <td>{{ vehicle.company_category?.name }}</td>
                                                    <td>{{ vehicle.vehicle_type || '-' }}</td>
                                                    <td>
                                                        <span v-if="vehicle.inspection_date">
                                                            {{ formatDate(vehicle.inspection_date) }}
                                                        </span>
                                                        <span v-else class="text-muted">未設定</span>
                                                    </td>
                                                    <td>
                                                        <span
                                                            v-if="vehicle.inspection_days_remaining !== null"
                                                            :class="getExpirationClass(vehicle.inspection_days_remaining)"
                                                        >
                                                            {{ vehicle.inspection_days_remaining }}天
                                                        </span>
                                                        <span v-else class="text-muted">-</span>
                                                    </td>
                                                    <td>
                                                        <span
                                                            :class="vehicle.vehicle_status === 'active' ? 'badge bg-success' : 'badge bg-secondary'"
                                                        >
                                                            {{ vehicle.status_text }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group btn-group-sm">
                                                            <Link
                                                                :href="route('admin.vehicles.show', vehicle.id)"
                                                                class="btn btn-info"
                                                                title="查看"
                                                            >
                                                                <i class="fas fa-eye"></i>檢視
                                                            </Link>

                                                            <Link
                                                                v-if="$page.props.auth.permissions?.includes('edit vehicles')"
                                                                :href="route('admin.vehicles.edit', vehicle.id)"
                                                                class="btn btn-warning"
                                                                title="編輯"
                                                            >
                                                                <i class="fas fa-edit"></i>編輯
                                                            </Link>

                                                            <button
                                                                v-if="$page.props.auth.permissions?.includes('manage vehicle status')"
                                                                type="button"
                                                                :class="vehicle.vehicle_status === 'active' ? 'btn btn-secondary' : 'btn btn-success'"
                                                                :title="vehicle.vehicle_status === 'active' ? '退籍' : '復籍'"
                                                                @click="toggleVehicleStatus(vehicle)"
                                                            >
                                                                <i :class="vehicle.vehicle_status === 'active' ? 'fas fa-user-times' : 'fas fa-user-check'"></i>
                                                                {{ vehicle.vehicle_status === 'active' ? '退籍' : '復籍' }}
                                                            </button>

                                                            <button
                                                                v-if="$page.props.auth.permissions?.includes('delete vehicles')"
                                                                type="button"
                                                                class="btn btn-danger"
                                                                title="刪除"
                                                                @click="deleteVehicle(vehicle)"
                                                            >
                                                                <i class="fas fa-trash"></i>刪除
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- 分頁 -->
                                    <div v-if="vehicles.links" class="d-flex justify-content-center">
                                        <nav>
                                            <ul class="pagination">
                                                <li v-for="link in vehicles.links" :key="link.label" class="page-item" :class="{ active: link.active, disabled: !link.url }">
                                                    <Link
                                                        v-if="link.url"
                                                        :href="link.url"
                                                        class="page-link"
                                                        v-html="link.label"
                                                    />
                                                    <span v-else class="page-link" v-html="link.label" />
                                                </li>
                                            </ul>
                                        </nav>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <!-- 匯入 Modal -->
        <div id="importModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="importModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 id="importModalLabel" class="modal-title">匯入車輛資料</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form @submit.prevent="importVehicles" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="import_file" class="form-label">選擇Excel檔案</label>
                                <input
                                    id="import_file"
                                    ref="fileInput"
                                    type="file"
                                    class="form-control"
                                    accept=".xlsx,.xls,.csv"
                                    required
                                    @change="handleFileChange"
                                />
                                <div class="form-text">
                                    支援格式：Excel (.xlsx, .xls) 或 CSV (.csv)，檔案大小限制 10MB
                                </div>
                            </div>

                            <!-- 匯入進度 -->
                            <div v-if="importProgress.show" class="mb-3">
                                <div class="d-flex justify-content-between">
                                    <small class="text-muted">匯入進度</small>
                                    <small class="text-muted">{{ importProgress.percent }}%</small>
                                </div>
                                <div class="progress">
                                    <div
                                        class="progress-bar"
                                        role="progressbar"
                                        :style="{ width: importProgress.percent + '%' }"
                                        :aria-valuenow="importProgress.percent"
                                        aria-valuemin="0"
                                        aria-valuemax="100"
                                    ></div>
                                </div>
                            </div>

                            <!-- 匯入結果 -->
                            <div v-if="importResult.show" class="alert" :class="importResult.type">
                                <h6 v-if="importResult.type === 'alert-success'">
                                    <i class="fas fa-check-circle"></i> 匯入成功
                                </h6>
                                <h6 v-else-if="importResult.type === 'alert-warning'">
                                    <i class="fas fa-exclamation-triangle"></i> 匯入完成（部分失敗）
                                </h6>
                                <h6 v-else>
                                    <i class="fas fa-times-circle"></i> 匯入失敗
                                </h6>
                                <p class="mb-1">{{ importResult.message }}</p>

                                <!-- 失敗詳情 -->
                                <div v-if="importResult.failures && importResult.failures.length > 0" class="mt-2">
                                    <details>
                                        <summary class="mb-2">查看失敗詳情 ({{ importResult.failures.length }} 筆)</summary>
                                        <div class="table-responsive" style="max-height: 200px; overflow-y: auto;">
                                            <table class="table table-sm table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>行數</th>
                                                        <th>欄位</th>
                                                        <th>錯誤訊息</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr v-for="(failure, index) in importResult.failures" :key="index">
                                                        <td>{{ failure.row }}</td>
                                                        <td>{{ failure.attribute }}</td>
                                                        <td>{{ failure.errors.join(', ') }}</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </details>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                    取消
                                </button>
                                <button
                                    type="submit"
                                    class="btn btn-primary"
                                    :disabled="isImporting || !selectedFile"
                                >
                                    <i v-if="isImporting" class="fas fa-spinner fa-spin"></i>
                                    <i v-else class="fas fa-upload"></i>
                                    <span v-if="isImporting">匯入中...</span>
                                    <span v-else>開始匯入</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref, reactive, onMounted, nextTick } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
    vehicles: Object,
    companyCategories: Array,
    companies: Array,
    filters: Object,
})

// 匯入匯出相關狀態
const isExporting = ref(false)
const isImporting = ref(false)
const selectedFile = ref(null)
const fileInput = ref(null)

// 匯入相關狀態
const importProgress = reactive({
    show: false,
    percent: 0,
})

const importResult = reactive({
    show: false,
    type: '',
    message: '',
    failures: [],
})

// Modal 實例
let importModal = null

// 搜尋表單
const searchForm = reactive({
    search: props.filters.search || '',
    status: props.filters.status || 'active',
    company_category_id: props.filters.company_category_id || '',
    company_id: props.filters.company_id || '',
    expiring_inspection: props.filters.expiring_inspection || false,
})

// 防抖搜尋
let searchTimeout = null
const debounceSearch = () => {
    clearTimeout(searchTimeout)
    searchTimeout = setTimeout(() => {
        search()
    }, 500)
}

// 執行搜尋
const search = () => {
    const params = Object.fromEntries(
        Object.entries(searchForm).filter(([key, value]) => value !== '' && value !== false)
    )

    router.get(route('admin.vehicles.index'), params, {
        preserveState: true,
        preserveScroll: true,
    })
}

// 清除篩選
const clearFilters = () => {
    searchForm.search = ''
    searchForm.status = 'active'
    searchForm.company_category_id = ''
    searchForm.company_id = ''
    searchForm.expiring_inspection = false
    search()
}

// 格式化日期
const formatDate = (dateString) => {
    if (!dateString) return '-'
    const date = new Date(dateString)
    return date.toLocaleDateString('zh-TW')
}

// 取得到期狀態樣式
const getExpirationClass = (days) => {
    if (days < 0) return 'text-danger font-weight-bold'
    if (days <= 7) return 'text-danger'
    if (days <= 30) return 'text-warning'
    return 'text-success'
}

// 切換車輛狀態
const toggleVehicleStatus = (vehicle) => {
    const action = vehicle.vehicle_status === 'active' ? 'deregister' : 'reactivate'
    const message = vehicle.vehicle_status === 'active' ? '確定要將此車輛退籍嗎？' : '確定要將此車輛復籍嗎？'

    if (confirm(message)) {
        router.post(route(`admin.vehicles.${action}`, vehicle.id), {}, {
            preserveScroll: true,
        })
    }
}

// 刪除車輛
const deleteVehicle = (vehicle) => {
    if (confirm(`確定要刪除車輛 ${vehicle.license_number} 嗎？此操作可以復原。`)) {
        router.delete(route('admin.vehicles.destroy', vehicle.id), {
            preserveScroll: true,
        })
    }
}

// 初始化 Bootstrap Modal
onMounted(() => {
    nextTick(() => {
        setTimeout(() => {
            const modalElement = document.getElementById('importModal')
            if (modalElement && window.bootstrap) {
                importModal = new window.bootstrap.Modal(modalElement, {
                    backdrop: 'static',
                    keyboard: false,
                })

                // 監聽 modal 關閉事件，重置狀態
                modalElement.addEventListener('hidden.bs.modal', () => {
                    resetImportState()
                })
            }
        }, 100)
    })
})

// 匯出車輛資料
const exportVehicles = async () => {
    if (isExporting.value) return

    try {
        isExporting.value = true

        // 構建匯出參數，包含當前篩選條件
        const params = new URLSearchParams()
        Object.entries(searchForm).forEach(([key, value]) => {
            if (value !== '' && value !== false) {
                params.append(key, value)
            }
        })

        // 使用原生 fetch 來處理檔案下載
        const response = await fetch(route('admin.vehicles.export') + '?' + params.toString(), {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
        })

        if (!response.ok) {
            throw new Error('匯出失敗')
        }

        // 從 response headers 取得檔案名稱
        const contentDisposition = response.headers.get('content-disposition')
        let filename = 'vehicles_export.xlsx'
        if (contentDisposition) {
            const matches = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/.exec(contentDisposition)
            if (matches && matches[1]) {
                filename = matches[1].replace(/['"]/g, '')
            }
        }

        // 建立下載連結
        const blob = await response.blob()
        const url = window.URL.createObjectURL(blob)
        const link = document.createElement('a')
        link.href = url
        link.download = filename
        document.body.appendChild(link)
        link.click()
        document.body.removeChild(link)
        window.URL.revokeObjectURL(url)

        // 顯示成功訊息
        alert('車輛資料匯出成功！')

    } catch (error) {
        console.error('Export error:', error)
        alert('匯出失敗：' + error.message)
    } finally {
        isExporting.value = false
    }
}

// 顯示匯入對話框
const showImportModal = () => {
    if (importModal) {
        resetImportState()
        importModal.show()
    }
}

// 處理檔案選擇
const handleFileChange = (event) => {
    const file = event.target.files[0]
    selectedFile.value = file

    if (file) {
        // 驗證檔案類型
        const allowedTypes = ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                              'application/vnd.ms-excel',
                              'text/csv']
        if (!allowedTypes.includes(file.type)) {
            alert('請選擇有效的 Excel 或 CSV 檔案')
            selectedFile.value = null
            event.target.value = ''
            return
        }

        // 驗證檔案大小 (10MB)
        if (file.size > 10 * 1024 * 1024) {
            alert('檔案大小不能超過 10MB')
            selectedFile.value = null
            event.target.value = ''
            return
        }
    }

    // 重置匯入結果
    importResult.show = false
}

// 匯入車輛資料
const importVehicles = async () => {
    if (!selectedFile.value || isImporting.value) return

    try {
        isImporting.value = true
        importProgress.show = true
        importProgress.percent = 0
        importResult.show = false

        // 建立 FormData
        const formData = new FormData()
        formData.append('file', selectedFile.value)

        // 模擬進度更新
        const progressInterval = setInterval(() => {
            if (importProgress.percent < 90) {
                importProgress.percent += Math.random() * 20
            }
        }, 200)

        // 執行匯入
        const response = await fetch(route('admin.vehicles.import'), {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: formData,
        })

        clearInterval(progressInterval)
        importProgress.percent = 100

        const result = await response.json()

        if (response.ok) {
            // 處理成功或部分成功
            if (result.message) {
                importResult.show = true
                importResult.message = result.message

                if (result.import_failures && result.import_failures.length > 0) {
                    importResult.type = 'alert-warning'
                    importResult.failures = result.import_failures
                } else {
                    importResult.type = 'alert-success'
                }

                // 重新載入頁面資料
                setTimeout(() => {
                    router.reload({ only: ['vehicles'] })
                }, 1500)
            }
        } else {
            throw new Error(result.message || '匯入失敗')
        }

    } catch (error) {
        console.error('Import error:', error)
        importResult.show = true
        importResult.type = 'alert-danger'
        importResult.message = error.message || '匯入過程中發生錯誤'
        importResult.failures = []
    } finally {
        isImporting.value = false
        setTimeout(() => {
            importProgress.show = false
            importProgress.percent = 0
        }, 1000)
    }
}

// 重置匯入狀態
const resetImportState = () => {
    selectedFile.value = null
    if (fileInput.value) {
        fileInput.value.value = ''
    }

    importProgress.show = false
    importProgress.percent = 0

    importResult.show = false
    importResult.type = ''
    importResult.message = ''
    importResult.failures = []

    isImporting.value = false
}
</script>

<style scoped>
.btn-group-sm > .btn {
    margin-right: 2px;
}

.btn-group-sm > .btn:last-child {
    margin-right: 0;
}

.table th {
    white-space: nowrap;
}

.form-check-input {
    margin-top: 0.125rem;
}
</style>
