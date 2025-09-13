<template>
    <AdminLayout :user="$page.props.auth.user">
        <template #header>
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">職務詳情</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><Link :href="route('admin.dashboard')">儀表板</Link></li>
                        <li class="breadcrumb-item"><Link :href="route('admin.positions.index')">職務管理</Link></li>
                        <li class="breadcrumb-item active">{{ position.name }}</li>
                    </ol>
                </div>
            </div>
        </template>

        <div class="row">
            <!-- 基本資訊 -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">基本資訊</h3>
                        <div class="card-tools">
                            <Link :href="route('admin.positions.edit', position.id)" class="btn btn-warning btn-sm">
                                <i class="bi bi-pencil"></i> 編輯職務
                            </Link>
                        </div>
                    </div>
                    <div class="card-body">
                        <dl class="row">
                            <dt class="col-sm-3">職務名稱</dt>
                            <dd class="col-sm-9">{{ position.name }}</dd>
                            
                            <dt class="col-sm-3">職務代碼</dt>
                            <dd class="col-sm-9"><code>{{ position.code }}</code></dd>
                            
                            <dt class="col-sm-3">所屬角色</dt>
                            <dd class="col-sm-9">
                                <span class="badge bg-primary">{{ position.role?.name }}</span>
                            </dd>
                            
                            <dt class="col-sm-3">狀態</dt>
                            <dd class="col-sm-9">
                                <span :class="'badge ' + (position.is_active ? 'bg-success' : 'bg-secondary')">
                                    {{ position.is_active ? '啟用' : '停用' }}
                                </span>
                            </dd>
                            
                            <dt class="col-sm-3">排序</dt>
                            <dd class="col-sm-9">{{ position.sort_order }}</dd>
                            
                            <dt class="col-sm-3">描述</dt>
                            <dd class="col-sm-9">{{ position.description || '無描述' }}</dd>
                            
                            <dt class="col-sm-3">建立時間</dt>
                            <dd class="col-sm-9">{{ new Date(position.created_at).toLocaleString() }}</dd>
                            
                            <dt class="col-sm-3">更新時間</dt>
                            <dd class="col-sm-9">{{ new Date(position.updated_at).toLocaleString() }}</dd>
                        </dl>
                    </div>
                </div>

                <!-- 使用者列表 -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">使用此職務的使用者 ({{ position.users?.length || 0 }})</h3>
                    </div>
                    <div class="card-body">
                        <div v-if="position.users && position.users.length > 0">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>姓名</th>
                                            <th>帳號</th>
                                            <th>電子信箱</th>
                                            <th>部門</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="user in position.users" :key="user.id">
                                            <td>{{ user.name }}</td>
                                            <td>{{ user.username }}</td>
                                            <td>{{ user.email }}</td>
                                            <td>{{ user.department || '未設定' }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div v-else class="text-center text-muted py-3">
                            <i class="bi bi-person-x"></i>
                            目前沒有使用者使用此職務
                        </div>
                    </div>
                </div>
            </div>

            <!-- 權限資訊 -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">權限設定 ({{ position.permissions?.length || 0 }})</h3>
                    </div>
                    <div class="card-body">
                        <div v-if="position.permissions && position.permissions.length > 0">
                            <div class="mb-2" v-for="permission in position.permissions" :key="permission.id">
                                <span class="badge bg-light text-dark">{{ label(permission.name) }}</span>
                            </div>
                        </div>
                        <div v-else class="text-center text-muted py-3">
                            <i class="bi bi-shield-x"></i>
                            此職務沒有設定權限
                        </div>
                    </div>
                </div>

                <!-- 統計資訊 -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">統計資訊</h3>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="border-right">
                                    <strong class="d-block h4 text-primary">{{ position.users?.length || 0 }}</strong>
                                    <span class="text-muted small">使用者數量</span>
                                </div>
                            </div>
                            <div class="col-6">
                                <strong class="d-block h4 text-warning">{{ position.permissions?.length || 0 }}</strong>
                                <span class="text-muted small">權限數量</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { usePermissionLabels } from '@/Composables/usePermissionLabels'

const props = defineProps({
    position: {
        type: Object,
        required: true
    }
})

const { label } = usePermissionLabels()
</script>

<style scoped>
.border-right {
    border-right: 1px solid #dee2e6;
}

.card {
    margin-bottom: 1.5rem;
}
</style>
