<template>
    <AdminLayout :user="$page.props.auth.user">
        <template #header>
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">編輯角色：{{ role.name }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><Link :href="route('admin.dashboard')">儀表板</Link></li>
                        <li class="breadcrumb-item"><Link :href="route('admin.roles.index')">角色權限管理</Link></li>
                        <li class="breadcrumb-item active">編輯角色</li>
                    </ol>
                </div>
            </div>
        </template>

        <div class="row">
            <!-- 角色資訊 -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">角色基本資訊</h3>
                    </div>
                    <form @submit.prevent="updateRole">
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="roleName" class="form-label">角色名稱</label>
                                <input
                                    id="roleName"
                                    v-model="form.name"
                                    type="text"
                                    class="form-control"
                                    :class="{ 'is-invalid': form.errors.name }"
                                    :disabled="['admin', 'user'].includes(role.name)"
                                    required
                                >
                                <div v-if="form.errors.name" class="invalid-feedback">
                                    {{ form.errors.name }}
                                </div>
                                <div v-if="['admin', 'user'].includes(role.name)" class="form-text text-warning">
                                    <i class="bi bi-exclamation-triangle"></i> 系統預設角色無法修改名稱
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle"></i>
                                    <strong>權限管理說明：</strong>
                                    <p class="mb-0 mt-2">角色權限現在統一由「職務管理」來設定。角色僅作為職務的分組工具，不再直接分配權限。</p>
                                    <p class="mb-0 mt-1">
                                        <Link :href="route('admin.positions.index')" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-arrow-right"></i> 前往職務管理
                                        </Link>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <div class="d-flex justify-content-between">
                                <Link :href="route('admin.roles.index')" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left"></i> 返回列表
                                </Link>
                                <button type="submit" class="btn btn-primary" :disabled="form.processing">
                                    <i class="bi bi-check2"></i> {{ form.processing ? '儲存中...' : '儲存變更' }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- 側邊資訊 -->
            <div class="col-md-4">
                <!-- 角色統計 -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">角色統計</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6 text-center">
                                <div class="description-block">
                                    <h5 class="description-header text-info">{{ role.users?.length || 0 }}</h5>
                                    <span class="description-text">使用者數量</span>
                                </div>
                            </div>
                            <div class="col-6 text-center">
                                <div class="description-block">
                                    <h5 class="description-header text-warning">{{ currentPermissionCount }}</h5>
                                    <span class="description-text">已選權限</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 使用此角色的使用者 -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">使用此角色的使用者</h3>
                    </div>
                    <div class="card-body">
                        <div v-if="role.users && role.users.length > 0">
                            <div v-for="user in role.users" :key="user.id" class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <strong>{{ user.name }}</strong>
                                    <br>
                                    <small class="text-muted">{{ user.email }}</small>
                                </div>
                                <Link :href="route('admin.users.edit', user.id)" class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-pencil"></i>
                                </Link>
                            </div>
                        </div>
                        <div v-else class="text-muted text-center py-3">
                            <i class="bi bi-person-x"></i>
                            <br>
                            尚無使用者使用此角色
                        </div>
                    </div>
                </div>

                <!-- 快速操作 -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">快速操作</h3>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <Link :href="route('admin.users.index')" class="btn btn-outline-info btn-sm">
                                <i class="bi bi-people"></i> 管理使用者
                            </Link>
                            <Link :href="route('admin.permissions.index')" class="btn btn-outline-warning btn-sm">
                                <i class="bi bi-shield-lock"></i> 管理權限
                            </Link>
                            <button
                                @click="confirmDelete"
                                class="btn btn-outline-danger btn-sm"
                                :disabled="['admin', 'user'].includes(role.name) || (role.users && role.users.length > 0)"
                            >
                                <i class="bi bi-trash"></i> 刪除角色
                            </button>
                        </div>
                        <div v-if="['admin', 'user'].includes(role.name)" class="form-text text-warning mt-2">
                            <i class="bi bi-exclamation-triangle"></i> 系統預設角色無法刪除
                        </div>
                        <div v-else-if="role.users && role.users.length > 0" class="form-text text-warning mt-2">
                            <i class="bi bi-exclamation-triangle"></i> 此角色仍有使用者使用，無法刪除
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 刪除確認模態視窗 -->
        <div class="modal fade" :class="{ show: showDeleteModal }" :style="{ display: showDeleteModal ? 'block' : 'none' }" @click.self="showDeleteModal = false">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">確認刪除角色</h5>
                        <button type="button" class="btn-close" @click="showDeleteModal = false"></button>
                    </div>
                    <div class="modal-body">
                        <p>您確定要刪除角色 <strong>{{ role.name }}</strong> 嗎？</p>
                        <p class="text-danger">此操作無法恢復！</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="showDeleteModal = false">取消</button>
                        <button type="button" class="btn btn-danger" @click="deleteRole">確認刪除</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- 背景遮罩 -->
        <div v-if="showDeleteModal" class="modal-backdrop fade show"></div>
    </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link, router, useForm } from '@inertiajs/vue3'
import { ref, computed } from 'vue'

const props = defineProps({
    role: {
        type: Object,
        required: true,
    },
})

const showDeleteModal = ref(false)

const form = useForm({
    name: props.role.name,
    errors: {}
})

const updateRole = () => {
    form.put(route('admin.roles.update', props.role.id), {
        onError: (errors) => {
            form.errors = errors
        }
    })
}

const confirmDelete = () => {
    showDeleteModal.value = true
}

const deleteRole = () => {
    router.delete(route('admin.roles.destroy', props.role.id), {
        onSuccess: () => {
            showDeleteModal.value = false
        }
    })
}
</script>

<style scoped>
.description-block {
    margin: 0;
    padding: 0;
}

.description-header {
    font-size: 2rem;
    font-weight: bold;
    margin: 0;
}

.description-text {
    font-size: 0.875rem;
    text-transform: uppercase;
}

.form-check {
    padding: 0.5rem;
    border: 1px solid transparent;
    border-radius: 0.25rem;
    transition: all 0.15s ease-in-out;
}

.form-check:hover {
    background-color: #f8f9fa;
    border-color: #dee2e6;
}

.form-check-input:checked + .form-check-label {
    font-weight: 600;
    color: #0d6efd;
}

.modal.show {
    display: block !important;
}

.btn-group .btn {
    margin-right: 5px;
}

.btn-group .btn:last-child {
    margin-right: 0;
}
</style>