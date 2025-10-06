<template>
    <AdminLayout :user="$page.props.auth.user">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                文件詳情
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
                <!-- 操作按鈕 -->
                <div class="mb-4 d-flex justify-content-between">
                    <Link :href="route('admin.documents.index')" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i>
                        返回列表
                    </Link>
                    <div class="btn-group">
                        <Link
                            v-if="can('edit documents')"
                            :href="route('admin.documents.edit', document.id)"
                            class="btn btn-primary"
                        >
                            <i class="bi bi-pencil me-1"></i>
                            編輯文件
                        </Link>
                        <button
                            v-if="can('delete documents')"
                            @click="confirmDelete"
                            class="btn btn-danger"
                        >
                            <i class="bi bi-trash me-1"></i>
                            刪除文件
                        </button>
                    </div>
                </div>

                <!-- 文件資訊卡片 -->
                <div class="card mb-4">
                    <div class="card-header" :class="`bg-${document.status_color} text-white`">
                        <div class="d-flex justify-content-between align-items-center">
                            <h3 class="mb-0">{{ document.document_name }}</h3>
                            <span class="badge bg-light text-dark fs-6">
                                {{ document.status_text }}
                            </span>
                        </div>
                        <div v-if="document.days_until_expiry !== null" class="mt-2">
                            <h5 class="mb-0">{{ getDaysText(document.days_until_expiry) }}</h5>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <th width="150">文件類別：</th>
                                        <td><span class="badge bg-info">{{ document.category_text }}</span></td>
                                    </tr>
                                    <tr>
                                        <th>關聯對象：</th>
                                        <td>
                                            <span v-if="document.driver">
                                                <i class="bi bi-person me-1"></i>{{ document.driver.name }}
                                                <small class="text-muted">({{ document.driver.id_number }})</small>
                                            </span>
                                            <span v-if="document.vehicle">
                                                <i class="bi bi-car-front me-1"></i>{{ document.vehicle.license_number }}
                                                <small class="text-muted">({{ document.vehicle.brand }} {{ document.vehicle.vehicle_model }})</small>
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>文件號碼：</th>
                                        <td>{{ document.document_number || '—' }}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <th width="150">開始日期：</th>
                                        <td>{{ document.start_date ? formatDate(document.start_date) : '—' }}</td>
                                    </tr>
                                    <tr>
                                        <th>到期日期：</th>
                                        <td>{{ document.expiry_date ? formatDate(document.expiry_date) : '—' }}</td>
                                    </tr>
                                    <tr v-if="document.document_category === 'insurance'">
                                        <th>保險資訊：</th>
                                        <td>
                                            等級係數：{{ document.insurance_level || '—' }}<br>
                                            保險費用：{{ document.insurance_fee ? `$${document.insurance_fee}` : '—' }}
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <!-- 備註 -->
                        <div v-if="document.notes" class="mt-3">
                            <h6>備註：</h6>
                            <p class="text-muted">{{ document.notes }}</p>
                        </div>

                        <!-- 建立/更新資訊 -->
                        <div class="mt-4 pt-3 border-top">
                            <div class="row text-muted small">
                                <div class="col-md-6">
                                    <strong>建立者：</strong>{{ document.created_by?.name || '系統' }}<br>
                                    <strong>建立時間：</strong>{{ formatDateTime(document.created_at) }}
                                </div>
                                <div class="col-md-6">
                                    <strong>最後更新者：</strong>{{ document.updated_by?.name || '—' }}<br>
                                    <strong>更新時間：</strong>{{ formatDateTime(document.updated_at) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 檔案列表 -->
                <div class="card">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-file-earmark me-2"></i>
                            上傳的檔案 ({{ document.files.length }})
                        </h5>
                    </div>
                    <div class="card-body">
                        <div v-if="document.files.length === 0" class="text-center py-4 text-muted">
                            <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                            <p class="mt-2">尚無上傳檔案</p>
                        </div>

                        <div v-else class="row">
                            <div v-for="file in document.files" :key="file.id" class="col-md-4 mb-3">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <!-- 圖片預覽：顯示縮圖，可點擊放大 -->
                                        <div v-if="isImage(file)" class="text-center mb-3" style="cursor: pointer;" @click="openPreview(file)">
                                            <img
                                                :src="route('admin.documents.files.download', file.id)"
                                                :alt="file.file_name"
                                                class="img-thumbnail"
                                                style="max-height: 150px; object-fit: cover; width: 100%;"
                                            >
                                        </div>
                                        <!-- PDF 預覽：顯示圖示，可點擊開啟 -->
                                        <div v-else-if="file.file_type === 'pdf'" class="text-center mb-3" style="cursor: pointer;" @click="openPreview(file)">
                                            <i class="bi bi-file-pdf text-danger" style="font-size: 3rem;"></i>
                                            <p class="small text-muted mt-2">點擊預覽</p>
                                        </div>
                                        <!-- 其他檔案：顯示圖示 -->
                                        <div v-else class="text-center mb-3">
                                            <i class="bi bi-file-earmark text-secondary" style="font-size: 3rem;"></i>
                                        </div>

                                        <h6 class="card-title text-truncate" :title="file.file_name">
                                            {{ file.file_name }}
                                        </h6>
                                        <p class="card-text small text-muted">
                                            <i class="bi bi-hdd me-1"></i>{{ file.file_size_human }}<br>
                                            <i class="bi bi-calendar me-1"></i>{{ formatDateTime(file.created_at) }}
                                        </p>
                                        <div class="d-grid gap-2">
                                            <a
                                                :href="route('admin.documents.files.download', file.id)"
                                                class="btn btn-sm btn-primary"
                                                target="_blank"
                                            >
                                                <i class="bi bi-download me-1"></i>
                                                下載
                                            </a>
                                            <button
                                                v-if="can('delete documents')"
                                                @click="confirmDeleteFile(file)"
                                                class="btn btn-sm btn-danger"
                                            >
                                                <i class="bi bi-trash me-1"></i>
                                                刪除
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 刪除文件確認 Modal -->
        <div class="modal fade" id="deleteDocumentModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">確認刪除文件</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>確定要刪除此文件嗎？</p>
                        <div class="alert alert-warning">
                            <strong>文件名稱：</strong>{{ document.document_name }}<br>
                            <strong>檔案數量：</strong>{{ document.files.length }} 個
                        </div>
                        <p class="text-danger">此操作將刪除文件及所有關聯的檔案，且無法復原！</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                        <button type="button" class="btn btn-danger" @click="deleteDocument">確認刪除</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- 刪除檔案確認 Modal -->
        <div class="modal fade" id="deleteFileModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">確認刪除檔案</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>確定要刪除此檔案嗎？</p>
                        <div v-if="fileToDelete" class="alert alert-warning">
                            <strong>檔案名稱：</strong>{{ fileToDelete.file_name }}<br>
                            <strong>檔案大小：</strong>{{ fileToDelete.file_size_human }}
                        </div>
                        <p class="text-danger">此操作無法復原！</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                        <button type="button" class="btn btn-danger" @click="deleteFile">確認刪除</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- 檔案預覽 Modal -->
        <div class="modal fade" id="previewModal" tabindex="-1">
            <div class="modal-dialog modal-xl modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ previewingFile?.file_name }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center" style="max-height: 70vh; overflow: auto;">
                        <!-- 圖片預覽 -->
                        <img
                            v-if="previewingFile && isImage(previewingFile)"
                            :src="route('admin.documents.files.download', previewingFile.id)"
                            :alt="previewingFile.file_name"
                            class="img-fluid"
                            style="max-width: 100%;"
                        >
                        <!-- PDF 預覽 -->
                        <iframe
                            v-else-if="previewingFile && previewingFile.file_type === 'pdf'"
                            :src="route('admin.documents.files.download', previewingFile.id)"
                            style="width: 100%; height: 70vh; border: none;"
                        ></iframe>
                    </div>
                    <div class="modal-footer">
                        <a
                            v-if="previewingFile"
                            :href="route('admin.documents.files.download', previewingFile.id)"
                            class="btn btn-primary"
                            target="_blank"
                        >
                            <i class="bi bi-download me-1"></i>
                            下載檔案
                        </a>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">關閉</button>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
    document: Object,
})

const page = usePage()
const fileToDelete = ref(null)
const previewingFile = ref(null)
let deleteDocumentModal = null
let deleteFileModal = null
let previewModal = null

const can = (permission) => {
    return page.props.auth.permissions.includes(permission)
}

// 判斷是否為圖片檔案
const isImage = (file) => {
    return ['jpg', 'jpeg', 'png', 'gif'].includes(file.file_type?.toLowerCase())
}

// 開啟預覽
const openPreview = (file) => {
    previewingFile.value = file
    previewModal.show()
}

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('zh-TW')
}

const formatDateTime = (datetime) => {
    return new Date(datetime).toLocaleString('zh-TW')
}

const getDaysText = (days) => {
    if (days === null) return ''
    if (days < 0) return `已過期 ${Math.abs(days)} 天`
    return `剩餘 ${days} 天`
}

const confirmDelete = () => {
    deleteDocumentModal.show()
}

const deleteDocument = () => {
    router.delete(route('admin.documents.destroy', props.document.id), {
        onSuccess: () => {
            deleteDocumentModal.hide()
        }
    })
}

const confirmDeleteFile = (file) => {
    fileToDelete.value = file
    deleteFileModal.show()
}

const deleteFile = () => {
    if (fileToDelete.value) {
        router.delete(route('admin.documents.files.delete', fileToDelete.value.id), {
            onSuccess: () => {
                deleteFileModal.hide()
                fileToDelete.value = null
            }
        })
    }
}

onMounted(() => {
    setTimeout(() => {
        const deleteDocModalElement = document.getElementById('deleteDocumentModal')
        const deleteFileModalElement = document.getElementById('deleteFileModal')
        const previewModalElement = document.getElementById('previewModal')

        if (deleteDocModalElement && window.bootstrap) {
            deleteDocumentModal = new window.bootstrap.Modal(deleteDocModalElement)
        }
        if (deleteFileModalElement && window.bootstrap) {
            deleteFileModal = new window.bootstrap.Modal(deleteFileModalElement)
        }
        if (previewModalElement && window.bootstrap) {
            previewModal = new window.bootstrap.Modal(previewModalElement)
        }
    }, 100)
})
</script>
