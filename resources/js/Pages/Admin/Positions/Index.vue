<template>
    <AdminLayout :user="$page.props.auth.user">
        <template #header>
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">職務管理</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><Link :href="route('admin.dashboard')">儀表板</Link></li>
                        <li class="breadcrumb-item active">職務管理</li>
                    </ol>
                </div>
            </div>
        </template>

        <!-- 統計卡片 -->
        <div class="row mb-4">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ positions.data.length }}</h3>
                        <p>目前職務數</p>
                    </div>
                    <div class="icon">
                        <i class="bi bi-person-badge"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ positions.data.filter(p => p.is_active).length }}</h3>
                        <p>啟用職務</p>
                    </div>
                    <div class="icon">
                        <i class="bi bi-check-circle"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ positions.data.reduce((sum, p) => sum + p.users_count, 0) }}</h3>
                        <p>總使用者數</p>
                    </div>
                    <div class="icon">
                        <i class="bi bi-people"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-secondary">
                    <div class="inner">
                        <h3>{{ roles.length }}</h3>
                        <p>可用角色</p>
                    </div>
                    <div class="icon">
                        <i class="bi bi-shield"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- 搜尋和篩選 -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <form @submit.prevent="search" class="row align-items-end">
                            <div class="col-md-4">
                                <label class="form-label">搜尋職務</label>
                                <input
                                    v-model="searchForm.search"
                                    type="text"
                                    class="form-control"
                                    placeholder="輸入職務名稱、代碼或描述..."
                                >
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">角色篩選</label>
                                <select v-model="searchForm.role_id" class="form-control">
                                    <option value="">全部角色</option>
                                    <option v-for="role in roles" :key="role.id" :value="role.id">
                                        {{ role.name }}
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">狀態篩選</label>
                                <select v-model="searchForm.is_active" class="form-control">
                                    <option value="">全部狀態</option>
                                    <option value="1">啟用</option>
                                    <option value="0">停用</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="bi bi-search"></i> 搜尋
                                </button>
                                <button type="button" @click="clearSearch" class="btn btn-outline-secondary">
                                    <i class="bi bi-x-circle"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- 職務列表 -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">職務列表</h3>
                        <div class="card-tools">
                            <Link :href="route('admin.positions.create')" class="btn btn-primary btn-sm">
                                <i class="bi bi-plus"></i> 新增職務
                            </Link>
                        </div>
                    </div>
                    <div class="card-body table-responsive p-0">
                        <table class="table table-hover text-nowrap">
                            <thead>
                                <tr>
                                    <th>職務名稱</th>
                                    <th>代碼</th>
                                    <th>所屬角色</th>
                                    <th>使用者數量</th>
                                    <th>權限數量</th>
                                    <th>狀態</th>
                                    <th>排序</th>
                                    <th width="200">操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="position in positions.data" :key="position.id">
                                    <td>
                                        <strong>{{ position.name }}</strong>
                                        <br>
                                        <small class="text-muted">{{ position.description || '無描述' }}</small>
                                    </td>
                                    <td>
                                        <code>{{ position.code }}</code>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary">{{ position.role?.name }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ position.users_count }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-warning">{{ position.permissions_count }}</span>
                                    </td>
                                    <td>
                                        <span :class="'badge ' + (position.is_active ? 'bg-success' : 'bg-secondary')">
                                            {{ position.is_active ? '啟用' : '停用' }}
                                        </span>
                                    </td>
                                    <td>{{ position.sort_order }}</td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <Link 
                                                :href="route('admin.positions.show', position.id)" 
                                                class="btn btn-info"
                                                title="檢視"
                                            >
                                                <i class="bi bi-eye"></i>
                                            </Link>
                                            <Link 
                                                :href="route('admin.positions.edit', position.id)" 
                                                class="btn btn-warning"
                                                title="編輯"
                                            >
                                                <i class="bi bi-pencil"></i>
                                            </Link>
                                            <button 
                                                @click="confirmDelete(position)" 
                                                class="btn btn-danger"
                                                :disabled="position.users_count > 0"
                                                title="刪除"
                                            >
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="positions.data.length === 0">
                                    <td colspan="8" class="text-center py-4 text-muted">
                                        <i class="bi bi-inbox"></i>
                                        沒有找到符合條件的職務
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer" v-if="positions.links">
                        <nav>
                            <ul class="pagination pagination-sm m-0 float-right">
                                <li v-for="link in positions.links" :key="link.label" 
                                    :class="'page-item ' + (link.active ? 'active' : '') + (link.url ? '' : ' disabled')">
                                    <Link v-if="link.url" :href="link.url" class="page-link" v-html="link.label"></Link>
                                    <span v-else class="page-link" v-html="link.label"></span>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <!-- 刪除確認 Modal -->
        <div class="modal fade" id="deleteModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">確認刪除</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>確定要刪除職務 <strong>{{ selectedPosition?.name }}</strong> 嗎？</p>
                        <p class="text-muted small">此操作無法復原。</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                        <button type="button" class="btn btn-danger" @click="deletePosition">確認刪除</button>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
    positions: Object,
    roles: Array,
    filters: Object
})

const page = usePage()
const selectedPosition = ref(null)
let deleteModal = null

const searchForm = reactive({
    search: props.filters.search || '',
    role_id: props.filters.role_id || '',
    is_active: props.filters.is_active || ''
})

const search = () => {
    router.get(route('admin.positions.index'), searchForm, {
        preserveState: true,
        preserveScroll: true
    })
}

const clearSearch = () => {
    searchForm.search = ''
    searchForm.role_id = ''
    searchForm.is_active = ''
    search()
}

const confirmDelete = (position) => {
    selectedPosition.value = position
    deleteModal.show()
}

const deletePosition = () => {
    router.delete(route('admin.positions.destroy', selectedPosition.value.id), {
        onSuccess: () => {
            deleteModal.hide()
            selectedPosition.value = null
        }
    })
}

onMounted(() => {
    setTimeout(() => {
        const modalElement = document.getElementById('deleteModal')
        if (modalElement && window.bootstrap) {
            deleteModal = new window.bootstrap.Modal(modalElement)
        }
    }, 100)
})
</script>

<style scoped>
.small-box {
    border-radius: 0.375rem;
    position: relative;
    display: block;
    margin-bottom: 20px;
    box-shadow: 0 1px 1px rgba(0,0,0,0.1);
}

.small-box > .inner {
    padding: 10px;
}

.small-box .icon {
    position: absolute;
    top: auto;
    bottom: 10px;
    right: 10px;
    z-index: 0;
    font-size: 70px;
    color: rgba(0,0,0,0.15);
}
</style>