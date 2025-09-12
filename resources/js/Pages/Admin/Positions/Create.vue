<template>
    <AdminLayout :user="$page.props.auth.user">
        <template #header>
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">新增職務</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><Link :href="route('admin.dashboard')">儀表板</Link></li>
                        <li class="breadcrumb-item"><Link :href="route('admin.positions.index')">職務管理</Link></li>
                        <li class="breadcrumb-item active">新增職務</li>
                    </ol>
                </div>
            </div>
        </template>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">職務資料</h3>
                        <div class="card-tools">
                            <Link :href="route('admin.positions.index')" class="btn btn-secondary btn-sm">
                                <i class="bi bi-arrow-left"></i> 返回列表
                            </Link>
                        </div>
                    </div>
                    <form @submit.prevent="submit">
                        <div class="card-body">
                            <div class="row">
                                <!-- 基本資料 -->
                                <div class="col-md-6">
                                    <h5 class="border-bottom pb-2 mb-3">基本資料</h5>
                                    
                                    <div class="mb-3">
                                        <label for="name" class="form-label">
                                            職務名稱 <span class="text-danger">*</span>
                                        </label>
                                        <input
                                            id="name"
                                            v-model="form.name"
                                            type="text"
                                            class="form-control"
                                            :class="{ 'is-invalid': errors.name }"
                                            placeholder="例如: 行政助理"
                                            required
                                        >
                                        <div v-if="errors.name" class="invalid-feedback">
                                            {{ errors.name }}
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="code" class="form-label">
                                            職務代碼 <span class="text-danger">*</span>
                                        </label>
                                        <input
                                            id="code"
                                            v-model="form.code"
                                            type="text"
                                            class="form-control"
                                            :class="{ 'is-invalid': errors.code }"
                                            placeholder="例如: ADM_ASSIST"
                                            maxlength="50"
                                            required
                                        >
                                        <div v-if="errors.code" class="invalid-feedback">
                                            {{ errors.code }}
                                        </div>
                                        <small class="text-muted">用於系統識別的唯一代碼</small>
                                    </div>

                                    <div class="mb-3">
                                        <label for="role_id" class="form-label">
                                            所屬角色 <span class="text-danger">*</span>
                                        </label>
                                        <select
                                            id="role_id"
                                            v-model="form.role_id"
                                            class="form-control"
                                            :class="{ 'is-invalid': errors.role_id }"
                                            required
                                        >
                                            <option value="">請選擇角色</option>
                                            <option v-for="role in roles" :key="role.id" :value="role.id">
                                                {{ role.name }}
                                            </option>
                                        </select>
                                        <div v-if="errors.role_id" class="invalid-feedback">
                                            {{ errors.role_id }}
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="description" class="form-label">職務描述</label>
                                        <textarea
                                            id="description"
                                            v-model="form.description"
                                            class="form-control"
                                            :class="{ 'is-invalid': errors.description }"
                                            rows="3"
                                            placeholder="請輸入職務描述..."
                                            maxlength="1000"
                                        ></textarea>
                                        <div v-if="errors.description" class="invalid-feedback">
                                            {{ errors.description }}
                                        </div>
                                        <small class="text-muted">
                                            {{ (form.description || '').length }}/1000 字元
                                        </small>
                                    </div>
                                </div>

                                <!-- 設定 -->
                                <div class="col-md-6">
                                    <h5 class="border-bottom pb-2 mb-3">職務設定</h5>
                                    
                                    <div class="mb-3">
                                        <div class="form-check form-switch">
                                            <input
                                                id="is_active"
                                                v-model="form.is_active"
                                                type="checkbox"
                                                class="form-check-input"
                                                role="switch"
                                            >
                                            <label for="is_active" class="form-check-label">啟用此職務</label>
                                        </div>
                                        <small class="text-muted">停用後，使用者將無法被分配此職務</small>
                                    </div>

                                    <div class="mb-3">
                                        <label for="sort_order" class="form-label">排序順序</label>
                                        <input
                                            id="sort_order"
                                            v-model.number="form.sort_order"
                                            type="number"
                                            class="form-control"
                                            :class="{ 'is-invalid': errors.sort_order }"
                                            min="0"
                                            placeholder="0"
                                        >
                                        <div v-if="errors.sort_order" class="invalid-feedback">
                                            {{ errors.sort_order }}
                                        </div>
                                        <small class="text-muted">數字越小排序越前面</small>
                                    </div>
                                </div>

                                <!-- 權限設定 -->
                                <div class="col-12">
                                    <h5 class="border-bottom pb-2 mb-3">權限設定</h5>
                                    
                                    <div v-if="Object.keys(groupedPermissions).length === 0" class="alert alert-info">
                                        <i class="bi bi-info-circle"></i>
                                        目前系統中沒有可用的權限。
                                    </div>
                                    
                                    <div v-else class="row">
                                        <div 
                                            v-for="(permissions, group) in groupedPermissions" 
                                            :key="group" 
                                            class="col-md-6 col-lg-4 mb-3"
                                        >
                                            <div class="card">
                                                <div class="card-header py-2">
                                                    <h6 class="card-title mb-0">{{ group }} 權限</h6>
                                                </div>
                                                <div class="card-body">
                                                    <div 
                                                        v-for="permission in permissions" 
                                                        :key="permission.id"
                                                        class="form-check mb-1"
                                                    >
                                                        <input
                                                            :id="`permission_${permission.id}`"
                                                            v-model="form.permissions"
                                                            :value="permission.id"
                                                            type="checkbox"
                                                            class="form-check-input"
                                                        >
                                                        <label 
                                                            :for="`permission_${permission.id}`" 
                                                            class="form-check-label"
                                                        >
                                                            {{ permission.name }}
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary" :disabled="form.processing">
                                <span v-if="form.processing" class="spinner-border spinner-border-sm me-2"></span>
                                <i v-else class="bi bi-check"></i>
                                {{ form.processing ? '儲存中...' : '儲存' }}
                            </button>
                            <Link :href="route('admin.positions.index')" class="btn btn-secondary ms-2">
                                取消
                            </Link>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { Link, useForm, usePage } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
    roles: Array,
    permissions: Array,
    groupedPermissions: Object
})

const { errors } = usePage().props

const form = useForm({
    name: '',
    code: '',
    description: '',
    role_id: '',
    is_active: true,
    sort_order: 0,
    permissions: []
})

const submit = () => {
    form.post(route('admin.positions.store'))
}
</script>