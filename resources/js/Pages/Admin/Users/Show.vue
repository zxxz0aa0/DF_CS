<template>
    <AdminLayout :user="$page.props.auth.user">
        <template #header>
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">檢視使用者：{{ user.name }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><Link :href="route('admin.dashboard')">儀表板</Link></li>
                        <li class="breadcrumb-item"><Link :href="route('admin.users.index')">使用者管理</Link></li>
                        <li class="breadcrumb-item active">檢視使用者</li>
                    </ol>
                </div>
            </div>
        </template>

        <div class="row">
            <!-- 基本資訊卡片 -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="bi bi-person"></i> 基本資訊
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-4"><strong>姓名：</strong></div>
                            <div class="col-8">{{ user.name }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-4"><strong>使用者名稱：</strong></div>
                            <div class="col-8">{{ user.username }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-4"><strong>電子郵件：</strong></div>
                            <div class="col-8">{{ user.email }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-4"><strong>帳號狀態：</strong></div>
                            <div class="col-8">
                                <span v-if="user.email_verified_at" class="badge bg-success">已驗證</span>
                                <span v-else class="badge bg-warning">未驗證</span>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-4"><strong>建立時間：</strong></div>
                            <div class="col-8">{{ formatDate(user.created_at) }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-4"><strong>最後更新：</strong></div>
                            <div class="col-8">{{ formatDate(user.updated_at) }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 個人資料卡片 -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="bi bi-card-text"></i> 個人資料
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-4"><strong>身分證字號：</strong></div>
                            <div class="col-8">
                                <code>{{ user.id_number_display || '未提供' }}</code>
                                <small class="text-muted d-block">已加密儲存，部分遮蔽顯示</small>
                                <button 
                                    v-if="user.id_number_full && !showFullIdNumber"
                                    @click="showFullIdNumber = true"
                                    class="btn btn-link btn-sm p-0 mt-1"
                                >
                                    <i class="bi bi-eye"></i> 顯示完整身分證字號
                                </button>
                                <div v-if="showFullIdNumber && user.id_number_full" class="mt-1">
                                    <code class="text-danger">{{ user.id_number_full }}</code>
                                    <button 
                                        @click="showFullIdNumber = false"
                                        class="btn btn-link btn-sm p-0 ms-2"
                                    >
                                        <i class="bi bi-eye-slash"></i> 隱藏
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-4"><strong>出生日期：</strong></div>
                            <div class="col-8">{{ user.birth_date || '未提供' }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-4"><strong>性別：</strong></div>
                            <div class="col-8">
                                <span v-if="user.gender === 'male'" class="badge bg-primary">男性</span>
                                <span v-else-if="user.gender === 'female'" class="badge bg-pink">女性</span>
                                <span v-else-if="user.gender === 'other'" class="badge bg-secondary">其他</span>
                                <span v-else class="text-muted">未提供</span>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-4"><strong>地址：</strong></div>
                            <div class="col-8">{{ user.address || '未提供' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 聯絡資訊卡片 -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="bi bi-telephone"></i> 聯絡資訊
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-4"><strong>手機號碼：</strong></div>
                            <div class="col-8">
                                <a :href="'tel:' + user.mobile_phone" class="text-decoration-none">
                                    {{ user.mobile_phone }}
                                </a>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-4"><strong>市話：</strong></div>
                            <div class="col-8">
                                <a v-if="user.home_phone" :href="'tel:' + user.home_phone" class="text-decoration-none">
                                    {{ user.home_phone }}
                                </a>
                                <span v-else class="text-muted">未提供</span>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-4"><strong>緊急聯絡人：</strong></div>
                            <div class="col-8">{{ user.emergency_contact }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-4"><strong>緊急聯絡人電話：</strong></div>
                            <div class="col-8">
                                <a :href="'tel:' + user.emergency_phone" class="text-decoration-none">
                                    {{ user.emergency_phone }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 工作資訊卡片 -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="bi bi-briefcase"></i> 工作資訊
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-4"><strong>部門：</strong></div>
                            <div class="col-8">
                                <span class="badge bg-info">{{ user.department }}</span>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-4"><strong>職位：</strong></div>
                            <div class="col-8">{{ user.position }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 角色權限資訊卡片 -->
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="bi bi-shield-lock"></i> 角色與權限
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-muted">分配的角色</h6>
                                <div v-if="user.roles && user.roles.length > 0">
                                    <span 
                                        v-for="role in user.roles" 
                                        :key="role.id" 
                                        class="badge bg-primary me-2 mb-2"
                                        style="font-size: 0.9em; padding: 0.5em 0.8em;"
                                    >
                                        <i class="bi bi-person-badge me-1"></i>
                                        {{ role.name }}
                                    </span>
                                </div>
                                <div v-else class="text-muted">
                                    <i class="bi bi-exclamation-circle"></i> 尚未分配任何角色
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted">擁有的權限</h6>
                                <div v-if="userPermissions.length > 0">
                                    <div class="permission-tags">
                                        <span 
                                            v-for="permission in userPermissions.slice(0, showAllPermissions ? userPermissions.length : 6)" 
                                            :key="permission" 
                                            class="badge bg-success me-1 mb-1"
                                            style="font-size: 0.75em;"
                                        >
                                            {{ permission }}
                                        </span>
                                        <button 
                                            v-if="userPermissions.length > 6"
                                            @click="showAllPermissions = !showAllPermissions"
                                            class="btn btn-link btn-sm p-0 ms-2"
                                        >
                                            {{ showAllPermissions ? '收合' : `...還有 ${userPermissions.length - 6} 項` }}
                                        </button>
                                    </div>
                                </div>
                                <div v-else class="text-muted">
                                    <i class="bi bi-shield-x"></i> 無任何權限
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 操作按鈕 -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-footer">
                        <div class="d-flex justify-content-between">
                            <Link :href="route('admin.users.index')" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> 返回列表
                            </Link>
                            <div>
                                <Link :href="route('admin.users.edit', user.id)" class="btn btn-warning me-2">
                                    <i class="bi bi-pencil"></i> 編輯使用者
                                </Link>
                                <button
                                    @click="confirmDelete"
                                    class="btn btn-danger"
                                    :disabled="user.id === $page.props.auth.user.id"
                                >
                                    <i class="bi bi-trash"></i> 刪除使用者
                                </button>
                            </div>
                        </div>
                        <div v-if="user.id === $page.props.auth.user.id" class="form-text text-warning mt-2">
                            <i class="bi bi-exclamation-triangle"></i> 無法刪除自己的帳號
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
                        <h5 class="modal-title">確認刪除使用者</h5>
                        <button type="button" class="btn-close" @click="showDeleteModal = false"></button>
                    </div>
                    <div class="modal-body">
                        <p>您確定要刪除使用者 <strong>{{ user.name }}</strong> 嗎？</p>
                        <p class="text-danger">此操作無法恢復！</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="showDeleteModal = false">取消</button>
                        <button type="button" class="btn btn-danger" @click="deleteUser">確認刪除</button>
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
import { Link, router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'

const props = defineProps({
    user: {
        type: Object,
        required: true,
    },
})

const showDeleteModal = ref(false)
const showAllPermissions = ref(false)
const showFullIdNumber = ref(false)


const userPermissions = computed(() => {
    const permissions = []
    if (props.user.roles) {
        props.user.roles.forEach(role => {
            if (role.permissions) {
                role.permissions.forEach(permission => {
                    if (!permissions.includes(permission.name)) {
                        permissions.push(permission.name)
                    }
                })
            }
        })
    }
    return permissions.sort()
})

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleString('zh-TW', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    })
}

const confirmDelete = () => {
    showDeleteModal.value = true
}

const deleteUser = () => {
    router.delete(route('admin.users.destroy', props.user.id), {
        onSuccess: () => {
            showDeleteModal.value = false
        }
    })
}
</script>

<style scoped>
.card-header h3 {
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.card-header i {
    color: #6c757d;
}

.badge {
    font-size: 0.875em;
}

.permission-tags {
    max-height: 200px;
    overflow-y: auto;
}

.modal.show {
    display: block !important;
}

code {
    font-size: 0.875em;
    color: #e83e8c;
    background-color: #f8f9fa;
    padding: 0.125rem 0.25rem;
    border-radius: 0.25rem;
}

.bg-pink {
    background-color: #e91e63 !important;
}

.row > div {
    margin-bottom: 0.5rem;
}

.text-decoration-none:hover {
    text-decoration: underline !important;
}
</style>