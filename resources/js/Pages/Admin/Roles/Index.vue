<template>
    <AdminLayout :user="$page.props.auth.user">
        <template #header>
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">角色權限管理</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><Link :href="route('admin.dashboard')">儀表板</Link></li>
                        <li class="breadcrumb-item active">角色權限管理</li>
                    </ol>
                </div>
            </div>
        </template>

        <!-- 統計卡片 -->
        <div class="row mb-4">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ roles.length }}</h3>
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
                        <h3>{{ totalUsers }}</h3>
                        <p>總使用者數</p>
                    </div>
                    <div class="icon">
                        <i class="bi bi-people"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ totalPermissions }}</h3>
                        <p>總權限數</p>
                    </div>
                    <div class="icon">
                        <i class="bi bi-shield-lock"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>{{ permissions.length }}</h3>
                        <p>可用權限</p>
                    </div>
                    <div class="icon">
                        <i class="bi bi-key"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- 角色管理 -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">角色列表</h3>
                        <div class="card-tools">
                            <button @click="showCreateModal = true" class="btn btn-primary btn-sm">
                                <i class="bi bi-plus"></i> 新增角色
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>角色名稱</th>
                                        <th>使用者數量</th>
                                        <th>權限數量</th>
                                        <th>操作</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="role in roles" :key="role.id">
                                        <td>{{ role.id }}</td>
                                        <td>
                                            <span class="badge bg-primary">{{ role.name }}</span>
                                        </td>
                                        <td>{{ role.users_count }}</td>
                                        <td>{{ role.permissions_count }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <Link :href="route('admin.roles.edit', role.id)" class="btn btn-warning btn-sm">
                                                    <i class="bi bi-pencil"></i>
                                                </Link>
                                                <button
                                                    @click="confirmDeleteRole(role)"
                                                    class="btn btn-danger btn-sm"
                                                    :disabled="['admin', 'user'].includes(role.name)"
                                                >
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 權限管理說明 -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">權限管理</h3>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i>
                            <strong>新的權限管理方式：</strong>
                            <p class="mb-2 mt-2">角色現在僅作為職務的分組工具，不再直接分配權限。</p>
                            <p class="mb-2">所有權限設定請前往「職務管理」頁面進行。</p>
                            <Link :href="route('admin.positions.index')" class="btn btn-primary btn-sm">
                                <i class="bi bi-arrow-right"></i> 前往職務管理
                            </Link>
                        </div>
                        
                        <div v-for="role in roles" :key="role.id" class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <strong>{{ role.name }}</strong>
                                <small class="text-muted">{{ role.users_count }} 位使用者</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-header">
                        <h3 class="card-title">權限管理</h3>
                        <div class="card-tools">
                            <Link :href="route('admin.permissions.index')" class="btn btn-info btn-sm">
                                <i class="bi bi-gear"></i> 管理權限
                            </Link>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="permission-list">
                            <div v-for="permission in permissions" :key="permission.id" class="mb-2">
                                <span class="badge bg-light text-dark">{{ permission.name }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 新增角色模態視窗 -->
        <div class="modal fade" :class="{ show: showCreateModal }" :style="{ display: showCreateModal ? 'block' : 'none' }" @click.self="showCreateModal = false">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">新增角色</h5>
                        <button type="button" class="btn-close" @click="showCreateModal = false"></button>
                    </div>
                    <form @submit.prevent="createRole">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="roleName" class="form-label">角色名稱</label>
                                <input
                                    id="roleName"
                                    v-model="createForm.name"
                                    type="text"
                                    class="form-control"
                                    :class="{ 'is-invalid': createForm.errors.name }"
                                    required
                                >
                                <div v-if="createForm.errors.name" class="invalid-feedback">
                                    {{ createForm.errors.name }}
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">選擇權限</label>
                                <div class="row">
                                    <div v-for="permission in permissions" :key="permission.id" class="col-md-6">
                                        <div class="form-check">
                                            <input
                                                :id="'perm-' + permission.id"
                                                v-model="createForm.permissions"
                                                type="checkbox"
                                                :value="permission.id"
                                                class="form-check-input"
                                            >
                                            <label :for="'perm-' + permission.id" class="form-check-label">
                                                {{ permission.name }}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" @click="showCreateModal = false">取消</button>
                            <button type="submit" class="btn btn-primary" :disabled="createForm.processing">
                                {{ createForm.processing ? '建立中...' : '建立角色' }}
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
                        <p>您確定要刪除角色 <strong>{{ deleteRole?.name }}</strong> 嗎？</p>
                        <p class="text-danger">此操作無法恢復！</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="showDeleteModal = false">取消</button>
                        <button type="button" class="btn btn-danger" @click="deleteRoleAction">確認刪除</button>
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
import { ref } from 'vue'

const props = defineProps({
    roles: {
        type: Array,
        required: true,
    },
    permissions: {
        type: Array,
        required: true,
    },
    totalUsers: {
        type: Number,
        required: true,
    },
    totalPermissions: {
        type: Number,
        required: true,
    },
})

const showCreateModal = ref(false)
const showDeleteModal = ref(false)
const deleteRole = ref(null)

const createForm = useForm({
    name: '',
    permissions: [],
    errors: {}
})

const createRole = () => {
    createForm.post(route('admin.roles.store'), {
        onSuccess: () => {
            showCreateModal.value = false
            createForm.reset()
        },
        onError: (errors) => {
            createForm.errors = errors
        }
    })
}

const confirmDeleteRole = (role) => {
    deleteRole.value = role
    showDeleteModal.value = true
}

const deleteRoleAction = () => {
    if (deleteRole.value) {
        router.delete(route('admin.roles.destroy', deleteRole.value.id), {
            onSuccess: () => {
                showDeleteModal.value = false
                deleteRole.value = null
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

.small-box > .small-box-footer {
    background: rgba(0,0,0,.1);
    color: rgba(255,255,255,.8);
    display: block;
    padding: 3px 0;
    position: relative;
    text-align: center;
    text-decoration: none;
    z-index: 10;
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

.small-box:hover {
    text-decoration: none;
    color: #fff;
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

.permission-tags .badge {
    font-size: 0.7em;
}

.permission-list .badge {
    margin-right: 5px;
    margin-bottom: 5px;
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
</style>