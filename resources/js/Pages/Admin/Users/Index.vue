<template>
    <AdminLayout :user="$page.props.auth.user">
        <template #header>
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">使用者管理</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><Link :href="route('admin.dashboard')">儀表板</Link></li>
                        <li class="breadcrumb-item active">使用者管理</li>
                    </ol>
                </div>
            </div>
        </template>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">使用者列表</h3>
                        <div class="card-tools">
                            <Link :href="route('admin.users.create')" class="btn btn-primary btn-sm">
                                <i class="bi bi-plus"></i> 新增使用者
                            </Link>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- 搜尋列 -->
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <div class="input-group">
                                    <input
                                        v-model="searchForm.search"
                                        type="text"
                                        class="form-control"
                                        placeholder="搜尋使用者名稱或電子郵件..."
                                        @keyup.enter="search"
                                    >
                                    <div class="input-group-append">
                                        <button @click="search" class="btn btn-outline-secondary" type="button">
                                            <i class="bi bi-search"></i>
                                        </button>
                                        <button @click="clearSearch" class="btn btn-outline-secondary" type="button" v-if="searchForm.search">
                                            <i class="bi bi-x"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 使用者表格 -->
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>姓名</th>
                                        <th>電子郵件</th>
                                        <th>角色</th>
                                        <th>建立時間</th>
                                        <th>狀態</th>
                                        <th>操作</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="user in users.data" :key="user.id">
                                        <td>{{ user.id }}</td>
                                        <td>{{ user.name }}</td>
                                        <td>{{ user.email }}</td>
                                        <td>
                                            <span v-for="role in user.roles" :key="role.id" class="badge bg-primary me-1">
                                                {{ role.name }}
                                            </span>
                                            <span v-if="!user.roles.length" class="text-muted">無角色</span>
                                        </td>
                                        <td>{{ formatDate(user.created_at) }}</td>
                                        <td>
                                            <span v-if="user.email_verified_at" class="badge bg-success">已驗證</span>
                                            <span v-else class="badge bg-warning">未驗證</span>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <Link :href="route('admin.users.show', user.id)" class="btn btn-info btn-sm">
                                                    <i class="bi bi-eye"></i>
                                                </Link>
                                                <Link :href="route('admin.users.edit', user.id)" class="btn btn-warning btn-sm">
                                                    <i class="bi bi-pencil"></i>
                                                </Link>
                                                <button
                                                    @click="confirmDelete(user)"
                                                    class="btn btn-danger btn-sm"
                                                    :disabled="user.id === $page.props.auth.user.id"
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
                        <div class="d-flex justify-content-between align-items-center mt-3" v-if="users.last_page > 1">
                            <div>
                                顯示第 {{ users.from }} 到 {{ users.to }} 項，共 {{ users.total }} 項結果
                            </div>
                            <nav>
                                <ul class="pagination mb-0">
                                    <li class="page-item" :class="{ disabled: !users.prev_page_url }">
                                        <Link class="page-link" :href="users.prev_page_url || '#'" preserve-state>上一頁</Link>
                                    </li>
                                    <li
                                        v-for="page in paginationRange"
                                        :key="page"
                                        class="page-item"
                                        :class="{ active: page === users.current_page }"
                                    >
                                        <Link
                                            v-if="page !== '...'"
                                            class="page-link"
                                            :href="getPageUrl(page)"
                                            preserve-state
                                        >
                                            {{ page }}
                                        </Link>
                                        <span v-else class="page-link">{{ page }}</span>
                                    </li>
                                    <li class="page-item" :class="{ disabled: !users.next_page_url }">
                                        <Link class="page-link" :href="users.next_page_url || '#'" preserve-state>下一頁</Link>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 刪除確認模態視窗 -->
        <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">確認刪除</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>您確定要刪除使用者 <strong>{{ deleteUser?.name }}</strong> 嗎？</p>
                        <p class="text-danger">此操作無法恢復！</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                        <button type="button" class="btn btn-danger" @click="deleteUserAction">確認刪除</button>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link, router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'

defineProps({
    users: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
})

const searchForm = ref({
    search: props.filters.search || '',
})

const deleteUser = ref(null)

const paginationRange = computed(() => {
    const range = []
    const current = users.current_page
    const last = users.last_page
    
    // 簡單的分頁邏輯
    for (let i = Math.max(1, current - 2); i <= Math.min(last, current + 2); i++) {
        range.push(i)
    }
    
    return range
})

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleString('zh-TW', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
    })
}

const search = () => {
    router.get(route('admin.users.index'), searchForm.value, {
        preserveState: true,
        replace: true,
    })
}

const clearSearch = () => {
    searchForm.value.search = ''
    search()
}

const getPageUrl = (page) => {
    const url = new URL(window.location.href)
    url.searchParams.set('page', page)
    if (searchForm.value.search) {
        url.searchParams.set('search', searchForm.value.search)
    }
    return url.toString()
}

const confirmDelete = (user) => {
    deleteUser.value = user
    // 這裡需要使用 Bootstrap 的 JavaScript 來顯示模態視窗
    // 在實際實作中，您可能想使用 Vue 的模態元件
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'))
    modal.show()
}

const deleteUserAction = () => {
    if (deleteUser.value) {
        router.delete(route('admin.users.destroy', deleteUser.value.id), {
            onSuccess: () => {
                deleteUser.value = null
                // 關閉模態視窗
                const modal = bootstrap.Modal.getInstance(document.getElementById('deleteModal'))
                modal.hide()
            }
        })
    }
}
</script>

<style scoped>
.table th {
    border-top: none;
    font-weight: 600;
}

.btn-group .btn {
    margin-right: 2px;
}

.btn-group .btn:last-child {
    margin-right: 0;
}
</style>