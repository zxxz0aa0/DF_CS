<template>
    <AdminLayout :user="$page.props.auth.user">
        <template #header>
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">編輯職務</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><Link :href="route('admin.dashboard')">儀表板</Link></li>
                        <li class="breadcrumb-item"><Link :href="route('admin.positions.index')">職務管理</Link></li>
                        <li class="breadcrumb-item active">編輯職務</li>
                    </ol>
                </div>
            </div>
        </template>

        <form @submit.prevent="updatePosition">
            <div class="row">
                <!-- 基本資訊 -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">基本資訊</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">職務名稱 *</label>
                                        <input
                                            id="name"
                                            v-model="form.name"
                                            type="text"
                                            class="form-control"
                                            :class="{ 'is-invalid': form.errors.name }"
                                            required
                                        >
                                        <div v-if="form.errors.name" class="invalid-feedback">
                                            {{ form.errors.name }}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="code" class="form-label">職務代碼 *</label>
                                        <input
                                            id="code"
                                            v-model="form.code"
                                            type="text"
                                            class="form-control"
                                            :class="{ 'is-invalid': form.errors.code }"
                                            required
                                        >
                                        <div v-if="form.errors.code" class="invalid-feedback">
                                            {{ form.errors.code }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="role_id" class="form-label">所屬角色 *</label>
                                        <select
                                            id="role_id"
                                            v-model="form.role_id"
                                            class="form-control"
                                            :class="{ 'is-invalid': form.errors.role_id }"
                                            required
                                        >
                                            <option value="">請選擇角色</option>
                                            <option v-for="role in roles" :key="role.id" :value="role.id">
                                                {{ role.name }}
                                            </option>
                                        </select>
                                        <div v-if="form.errors.role_id" class="invalid-feedback">
                                            {{ form.errors.role_id }}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="sort_order" class="form-label">排序</label>
                                        <input
                                            id="sort_order"
                                            v-model="form.sort_order"
                                            type="number"
                                            class="form-control"
                                            min="0"
                                        >
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <div class="form-check mt-4">
                                            <input
                                                id="is_active"
                                                v-model="form.is_active"
                                                type="checkbox"
                                                class="form-check-input"
                                            >
                                            <label for="is_active" class="form-check-label">
                                                啟用此職務
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">職務描述</label>
                                <textarea
                                    id="description"
                                    v-model="form.description"
                                    class="form-control"
                                    rows="3"
                                    placeholder="描述此職務的工作內容和職責..."
                                ></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 權限設定 -->
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">權限設定</h3>
                            <div class="card-tools">
                                <button type="button" @click="selectAllPermissions" class="btn btn-link btn-sm">
                                    全選
                                </button>
                                <button type="button" @click="deselectAllPermissions" class="btn btn-link btn-sm">
                                    全部取消
                                </button>
                            </div>
                        </div>
                        <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                            <div v-for="(permissionGroup, groupName) in groupedPermissions" :key="groupName" class="mb-3">
                                <h6 class="text-uppercase text-muted">{{ groupName }}</h6>
                                <div v-for="permission in permissionGroup" :key="permission.id" class="form-check">
                                    <input
                                        :id="'perm-' + permission.id"
                                        v-model="form.permissions"
                                        type="checkbox"
                                        :value="permission.id"
                                        class="form-check-input"
                                    >
                                    <label :for="'perm-' + permission.id" class="form-check-label">
                                        {{ label(permission.name) }}
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 操作按鈕 -->
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success" :disabled="form.processing">
                            <i class="bi bi-check-lg"></i>
                            {{ form.processing ? '更新中...' : '更新職務' }}
                        </button>
                        <Link :href="route('admin.positions.index')" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> 返回列表
                        </Link>
                    </div>
                </div>
            </div>
        </form>
    </AdminLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { usePermissionLabels } from '@/Composables/usePermissionLabels'

const props = defineProps({
    position: {
        type: Object,
        required: true
    },
    roles: {
        type: Array,
        required: true
    },
    permissions: {
        type: Array,
        required: true
    },
    groupedPermissions: {
        type: Object,
        required: true
    },
    positionPermissions: {
        type: Array,
        required: true
    }
})

const form = useForm({
    name: props.position.name,
    code: props.position.code,
    description: props.position.description,
    role_id: props.position.role_id,
    is_active: props.position.is_active,
    sort_order: props.position.sort_order,
    permissions: props.positionPermissions
})

const { label } = usePermissionLabels()

const updatePosition = () => {
    form.put(route('admin.positions.update', props.position.id), {
        onSuccess: () => {
            // 成功後會被重導向到 index 頁面
        }
    })
}

const selectAllPermissions = () => {
    form.permissions = props.permissions.map(p => p.id)
}

const deselectAllPermissions = () => {
    form.permissions = []
}
</script>

<style scoped>
.card {
    margin-bottom: 1.5rem;
}

.form-check {
    margin-bottom: 0.5rem;
}

.card-tools .btn-link {
    padding: 0;
    margin-left: 0.5rem;
}
</style>
