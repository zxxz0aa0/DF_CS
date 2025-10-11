<template>
    <AdminLayout :user="$page.props.auth.user">
        <template #header>
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">經常性費用管理</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item">
                            <Link :href="route('admin.dashboard')">儀表板</Link>
                        </li>
                        <li class="breadcrumb-item active">經常性費用管理</li>
                    </ol>
                </div>
            </div>
        </template>

        <div class="card">
            <div class="card-header" style="background-color:#B3D9D9;">
                <h3 class="card-title">費用組合列表</h3>
            </div>
            <div class="card-body">
                <!-- 搜尋與新增列 -->
                <div class="row mb-3 align-items-center">
                    <div class="col-md-6">
                        <div class="input-group">
                            <input
                                v-model="searchForm.search"
                                type="text"
                                class="form-control"
                                placeholder="搜尋組合名稱..."
                                @keyup.enter="search"
                            >
                            <button @click="search" class="btn btn-primary" type="button">
                                <i class="bi bi-search"></i> 搜尋
                            </button>
                        </div>
                    </div>
                    <div class="col-md-2" v-if="hasSearch">
                        <button @click="clearSearch" class="btn btn-outline-secondary w-100" type="button">
                            <i class="bi bi-x-circle"></i> 清除搜尋
                        </button>
                    </div>
                    <div class="col-md-2 ms-auto">
                        <Link :href="route('admin.recurring-costs.create')" class="btn btn-success w-100">
                            <i class="bi bi-plus-circle"></i> 新增組合
                        </Link>
                    </div>
                </div>

                <!-- 表格 -->
                <div class="table-responsive h5">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th class="text-center">組合名稱</th>
                                <th class="text-center">說明</th>
                                <th class="text-center">項目數</th>
                                <th class="text-center">總金額</th>
                                <th class="text-center">使用駕駛數</th>
                                <th class="text-center">狀態</th>
                                <th class="text-center">建立時間</th>
                                <th class="text-center">操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="templates.data.length === 0">
                                <td colspan="9" class="text-center text-muted">目前沒有資料</td>
                            </tr>
                            <tr v-for="template in templates.data" :key="template.id">
                                <td class="text-center">{{ template.name }}</td>
                                <td class="text-center">{{ template.description || '-' }}</td>
                                <td class="text-center">{{ template.items?.length || 0 }} 項</td>
                                <td class="text-center">{{ formatCurrency(template.total_amount) }}</td>
                                <td class="text-center">{{ template.drivers_count }} 人</td>
                                <td class="text-center">
                                    <span v-if="template.is_active" class="badge bg-success">啟用</span>
                                    <span v-else class="badge bg-secondary">停用</span>
                                </td>
                                <td class="text-center">{{ formatDate(template.created_at) }}</td>
                                <td class="text-center">
                                    <div class="btn-group" role="group">
                                        <Link
                                            :href="route('admin.recurring-costs.edit', template.id)"
                                            class="btn btn-sm btn-primary"
                                            title="編輯"
                                        >
                                            <i class="bi bi-pencil"></i>
                                        </Link>
                                        <button
                                            @click="confirmDelete(template)"
                                            class="btn btn-sm btn-danger"
                                            title="刪除"
                                        >
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- 分頁 -->
                <div v-if="templates.data.length > 0" class="d-flex justify-content-between align-items-center mt-3">
                    <div class="text-muted">
                        顯示 {{ templates.from }} 到 {{ templates.to }} 筆，共 {{ templates.total }} 筆
                    </div>
                    <nav>
                        <ul class="pagination mb-0">
                            <li v-for="(link, index) in templates.links" :key="index" class="page-item" :class="{ active: link.active, disabled: !link.url }">
                                <Link v-if="link.url" :href="link.url" class="page-link" v-html="link.label"></Link>
                                <span v-else class="page-link" v-html="link.label"></span>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>

        <!-- 刪除確認 Modal -->
        <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">確認刪除</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>確定要刪除組合「<strong>{{ templateToDelete?.name }}</strong>」嗎？</p>
                        <p class="text-danger">此操作無法復原，且會自動解除所有使用此組合的駕駛關聯。</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                        <button type="button" class="btn btn-danger" @click="deleteTemplate">確認刪除</button>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
    templates: Object,
    filters: Object,
})

const searchForm = ref({
    search: props.filters?.search || '',
})

const templateToDelete = ref(null)
let deleteModal = null

const hasSearch = computed(() => searchForm.value.search !== '')

onMounted(() => {
    setTimeout(() => {
        const modalElement = document.getElementById('deleteModal')
        if (modalElement && window.bootstrap) {
            deleteModal = new window.bootstrap.Modal(modalElement)
        }
    }, 100)
})

const search = () => {
    router.get(route('admin.recurring-costs.index'), {
        search: searchForm.value.search,
    }, {
        preserveState: true,
        preserveScroll: true,
    })
}

const clearSearch = () => {
    searchForm.value.search = ''
    search()
}

const confirmDelete = (template) => {
    templateToDelete.value = template
    deleteModal?.show()
}

const deleteTemplate = () => {
    if (!templateToDelete.value) return

    router.delete(route('admin.recurring-costs.destroy', templateToDelete.value.id), {
        onSuccess: () => {
            deleteModal?.hide()
            templateToDelete.value = null
        },
    })
}

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('zh-TW', {
        style: 'currency',
        currency: 'TWD',
        minimumFractionDigits: 0,
    }).format(amount || 0)
}

const formatDate = (dateString) => {
    if (!dateString) return '-'
    const date = new Date(dateString)
    return date.toLocaleDateString('zh-TW', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
    })
}
</script>
