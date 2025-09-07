<template>
    <AdminLayout :user="$page.props.auth.user">
        <template #header>
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">權限管理</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><Link :href="route('admin.dashboard')">儀表板</Link></li>
                        <li class="breadcrumb-item active">權限管理</li>
                    </ol>
                </div>
            </div>
        </template>

        <!-- 統計卡片 -->
        <div class="row mb-4">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-primary">
                    <div class="inner">
                        <h3>{{ permissions.length }}</h3>
                        <p>總權限數</p>
                    </div>
                    <div class="icon">
                        <i class="bi bi-key"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ totalRoles }}</h3>
                        <p>總角色數</p>
                    </div>
                    <div class="icon">
                        <i class="bi bi-person-badge"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ Object.keys(groupedPermissions).length }}</h3>
                        <p>權限分組</p>
                    </div>
                    <div class="icon">
                        <i class="bi bi-folder"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ usedPermissions }}</h3>
                        <p>已使用權限</p>
                    </div>
                    <div class="icon">
                        <i class="bi bi-shield-check"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- 權限列表 -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">權限列表</h3>
                        <div class="card-tools">
                            <button @click="showCreateModal = true" class="btn btn-primary btn-sm">
                                <i class="bi bi-plus"></i> 新增權限
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- 分組顯示權限 -->
                        <div v-for="(groupPermissions, group) in groupedPermissions" :key="group" class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="text-primary mb-0">
                                    <i class="bi bi-folder2-open"></i> {{ formatGroupName(group) }}
                                    <span class="badge bg-light text-dark ms-2">{{ groupPermissions.length }} 項</span>
                                </h5>
                                <button 
                                    class="btn btn-outline-secondary btn-sm"
                                    @click="toggleGroup(group)"
                                >
                                    <i :class="collapsedGroups[group] ? 'bi bi-chevron-down' : 'bi bi-chevron-up'"></i>
                                </button>
                            </div>
                            
                            <div v-show="!collapsedGroups[group]" class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>權限名稱</th>
                                            <th>使用的角色數</th>
                                            <th>操作</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="permission in groupPermissions" :key="permission.id">
                                            <td>{{ permission.id }}</td>
                                            <td>
                                                <code>{{ permission.name }}</code>
                                            </td>
                                            <td>
                                                <span class="badge bg-info">{{ permission.roles_count || 0 }}</span>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button
                                                        @click="confirmDeletePermission(permission)"
                                                        class="btn btn-danger btn-sm"
                                                        :disabled="permission.roles_count > 0"
                                                    >
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <hr>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 側邊資訊 -->
            <div class="col-md-4">
                <!-- 快速操作 -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">快速操作</h3>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <Link :href="route('admin.roles.index')" class="btn btn-outline-primary">
                                <i class="bi bi-person-badge"></i> 管理角色
                            </Link>
                            <Link :href="route('admin.users.index')" class="btn btn-outline-info">
                                <i class="bi bi-people"></i> 管理使用者
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- 權限使用統計 -->
                <div class="card mt-3">
                    <div class="card-header">
                        <h3 class="card-title">權限使用統計</h3>
                    </div>
                    <div class="card-body">
                        <div v-for="(groupPermissions, group) in groupedPermissions" :key="group" class="mb-3">
                            <div class="d-flex justify-content-between">
                                <span>{{ formatGroupName(group) }}</span>
                                <span class="badge bg-secondary">{{ groupPermissions.length }}</span>
                            </div>
                            <div class="progress mt-1" style="height: 5px;">
                                <div 
                                    class="progress-bar" 
                                    :style="{ width: (groupPermissions.length / permissions.length * 100) + '%' }"
                                ></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 常用權限模板 -->
                <div class="card mt-3">
                    <div class="card-header">
                        <h3 class="card-title">常用權限模板</h3>
                    </div>
                    <div class="card-body">
                        <div class="list-group list-group-flush">
                            <div class="list-group-item px-0">
                                <strong>基礎權限</strong>
                                <div class="form-text">view dashboard, view users</div>
                            </div>
                            <div class="list-group-item px-0">
                                <strong>管理員權限</strong>
                                <div class="form-text">所有權限</div>
                            </div>
                            <div class="list-group-item px-0">
                                <strong>編輯者權限</strong>
                                <div class="form-text">edit users, create users, view users</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 新增權限模態視窗 -->
        <div class="modal fade" :class="{ show: showCreateModal }" :style="{ display: showCreateModal ? 'block' : 'none' }" @click.self="showCreateModal = false">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">新增權限</h5>
                        <button type="button" class="btn-close" @click="showCreateModal = false"></button>
                    </div>
                    <form @submit.prevent="createPermission">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="permissionName" class="form-label">權限名稱</label>
                                <input
                                    id="permissionName"
                                    v-model="createForm.name"
                                    type="text"
                                    class="form-control"
                                    :class="{ 'is-invalid': createForm.errors.name }"
                                    placeholder="例：edit users, view dashboard"
                                    required
                                >
                                <div v-if="createForm.errors.name" class="invalid-feedback">
                                    {{ createForm.errors.name }}
                                </div>
                                <div class="form-text">
                                    建議格式：動作 + 資源 (例：view users, edit posts)
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" @click="showCreateModal = false">取消</button>
                            <button type="submit" class="btn btn-primary" :disabled="createForm.processing">
                                {{ createForm.processing ? '建立中...' : '建立權限' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- 刪除確認模態視窗 -->
        <div class="modal fade" :class="{ show: showDeleteModal }" :style="{ display: showDeleteModal ? 'block' : 'none' }" @click.self="showDeleteModal = false">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">確認刪除</h5>
                        <button type="button" class="btn-close" @click="showDeleteModal = false"></button>
                    </div>
                    <div class="modal-body">
                        <p>您確定要刪除權限 <strong>{{ deletePermission?.name }}</strong> 嗎？</p>
                        <p class="text-danger">此操作無法恢復！</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="showDeleteModal = false">取消</button>
                        <button type="button" class="btn btn-danger" @click="deletePermissionAction">確認刪除</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- 背景遮罩 -->
        <div v-if="showCreateModal || showDeleteModal" class="modal-backdrop fade show"></div>
    </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link, router, useForm } from '@inertiajs/vue3'
import { ref, computed } from 'vue'

const props = defineProps({
    permissions: {
        type: Array,
        required: true,
    },
    groupedPermissions: {
        type: Object,
        required: true,
    },
    totalRoles: {
        type: Number,
        required: true,
    },
})

const showCreateModal = ref(false)
const showDeleteModal = ref(false)
const deletePermission = ref(null)
const collapsedGroups = ref({})

const usedPermissions = computed(() => {
    return props.permissions.filter(p => p.roles_count > 0).length
})

const createForm = useForm({
    name: '',
    errors: {}
})

const formatGroupName = (group) => {
    const groupNames = {
        'users': '使用者管理',
        'roles': '角色管理', 
        'dashboard': '儀表板',
        'permissions': '權限管理',
        'admin': '系統管理'
    }
    return groupNames[group] || `${group} 相關`
}

const toggleGroup = (group) => {
    collapsedGroups.value[group] = !collapsedGroups.value[group]
}

const createPermission = () => {
    createForm.post(route('admin.permissions.store'), {
        onSuccess: () => {
            showCreateModal.value = false
            createForm.reset()
        },
        onError: (errors) => {
            createForm.errors = errors
        }
    })
}

const confirmDeletePermission = (permission) => {
    deletePermission.value = permission
    showDeleteModal.value = true
}

const deletePermissionAction = () => {
    if (deletePermission.value) {
        router.delete(route('admin.permissions.destroy', deletePermission.value.id), {
            onSuccess: () => {
                showDeleteModal.value = false
                deletePermission.value = null
            }
        })
    }
}
</script>

<style scoped>
.small-box {
    border-radius: 0.25rem;
    box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
    display: block;
    margin-bottom: 20px;
    position: relative;
}

.small-box > .inner {
    padding: 10px;
}

.small-box > .icon {
    color: rgba(0,0,0,.15);
    z-index: 0;
}

.small-box > .icon > i {
    font-size: 70px;
    position: absolute;
    right: 15px;
    top: 15px;
    transition: transform .3s linear;
}

.small-box:hover > .icon > i {
    transform: scale(1.1);
}

.small-box h3 {
    font-size: 2.2rem;
    font-weight: bold;
    margin: 0 0 10px;
    white-space: nowrap;
}

.modal.show {
    display: block !important;
}

.btn-group .btn {
    margin-right: 2px;
}

.btn-group .btn:last-child {
    margin-right: 0;
}

code {
    font-size: 0.875em;
    color: #e83e8c;
    background-color: #f8f9fa;
    padding: 0.125rem 0.25rem;
    border-radius: 0.25rem;
}
</style>