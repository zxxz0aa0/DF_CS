<template>
    <AdminLayout :user="$page.props.auth.user">
        <template #header>
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">新增使用者</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><Link :href="route('admin.dashboard')">儀表板</Link></li>
                        <li class="breadcrumb-item"><Link :href="route('admin.users.index')">使用者管理</Link></li>
                        <li class="breadcrumb-item active">新增使用者</li>
                    </ol>
                </div>
            </div>
        </template>

        <form @submit.prevent="createUser">
            <div class="row">
                <!-- 基本資訊 -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="bi bi-person"></i> 基本資訊
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="name" class="form-label">姓名 <span class="text-danger">*</span></label>
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

                            <div class="mb-3">
                                <label for="username" class="form-label">使用者名稱 <span class="text-danger">*</span></label>
                                <input
                                    id="username"
                                    v-model="form.username"
                                    type="text"
                                    class="form-control"
                                    :class="{ 'is-invalid': form.errors.username }"
                                    required
                                >
                                <div v-if="form.errors.username" class="invalid-feedback">
                                    {{ form.errors.username }}
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">電子郵件 </label>
                                <input
                                    id="email"
                                    v-model="form.email"
                                    type="email"
                                    class="form-control"
                                    :class="{ 'is-invalid': form.errors.email }"
                                >
                                <div v-if="form.errors.email" class="invalid-feedback">
                                    {{ form.errors.email }}
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">密碼 <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input
                                        id="password"
                                        v-model="form.password"
                                        :type="showPassword ? 'text' : 'password'"
                                        class="form-control"
                                        :class="{ 'is-invalid': form.errors.password }"
                                        required
                                    >
                                    <button
                                        type="button"
                                        class="btn btn-outline-secondary"
                                        @click="showPassword = !showPassword"
                                    >
                                        <i :class="showPassword ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
                                    </button>
                                </div>
                                <div v-if="form.errors.password" class="invalid-feedback">
                                    {{ form.errors.password }}
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="password_confirmation" class="form-label">確認密碼 <span class="text-danger">*</span></label>
                                <input
                                    id="password_confirmation"
                                    v-model="form.password_confirmation"
                                    :type="showPassword ? 'text' : 'password'"
                                    class="form-control"
                                    required
                                >
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 個人資料 -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="bi bi-card-text"></i> 個人資料
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="id_number" class="form-label">身分證字號 <span class="text-danger">*</span></label>
                                <input
                                    id="id_number"
                                    v-model="form.id_number"
                                    type="text"
                                    class="form-control"
                                    :class="{ 'is-invalid': form.errors.id_number }"
                                    maxlength="10"
                                    placeholder="A123456789"
                                    required
                                >
                                <div v-if="form.errors.id_number" class="invalid-feedback">
                                    {{ form.errors.id_number }}
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="birth_date" class="form-label">出生日期</label>
                                <input
                                    id="birth_date"
                                    v-model="form.birth_date"
                                    type="date"
                                    class="form-control"
                                    :class="{ 'is-invalid': form.errors.birth_date }"
                                    :max="maxBirthDate"
                                >
                                <div v-if="form.errors.birth_date" class="invalid-feedback">
                                    {{ form.errors.birth_date }}
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">性別 <span class="text-danger">*</span></label>
                                <div class="mt-2">
                                    <div class="form-check form-check-inline">
                                        <input
                                            id="gender_male"
                                            v-model="form.gender"
                                            type="radio"
                                            value="male"
                                            class="form-check-input"
                                        >
                                        <label for="gender_male" class="form-check-label">男性</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input
                                            id="gender_female"
                                            v-model="form.gender"
                                            type="radio"
                                            value="female"
                                            class="form-check-input"
                                        >
                                        <label for="gender_female" class="form-check-label">女性</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input
                                            id="gender_other"
                                            v-model="form.gender"
                                            type="radio"
                                            value="other"
                                            class="form-check-input"
                                        >
                                        <label for="gender_other" class="form-check-label">其他</label>
                                    </div>
                                </div>
                                <div v-if="form.errors.gender" class="invalid-feedback d-block">
                                    {{ form.errors.gender }}
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="address" class="form-label">地址</label>
                                <textarea
                                    id="address"
                                    v-model="form.address"
                                    class="form-control"
                                    :class="{ 'is-invalid': form.errors.address }"
                                    rows="3"
                                    placeholder="請輸入完整地址"
                                ></textarea>
                                <div v-if="form.errors.address" class="invalid-feedback">
                                    {{ form.errors.address }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 聯絡資訊 -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="bi bi-telephone"></i> 聯絡資訊
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="mobile_phone" class="form-label">手機號碼 <span class="text-danger">*</span></label>
                                <input
                                    id="mobile_phone"
                                    v-model="form.mobile_phone"
                                    type="tel"
                                    class="form-control"
                                    :class="{ 'is-invalid': form.errors.mobile_phone }"
                                    placeholder="09xxxxxxxx"
                                    required
                                >
                                <div v-if="form.errors.mobile_phone" class="invalid-feedback">
                                    {{ form.errors.mobile_phone }}
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="home_phone" class="form-label">市話</label>
                                <input
                                    id="home_phone"
                                    v-model="form.home_phone"
                                    type="tel"
                                    class="form-control"
                                    :class="{ 'is-invalid': form.errors.home_phone }"
                                    placeholder="區碼+號碼 (例: 02-12345678)"
                                >
                                <div v-if="form.errors.home_phone" class="invalid-feedback">
                                    {{ form.errors.home_phone }}
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="emergency_contact" class="form-label">緊急聯絡人</label>
                                <input
                                    id="emergency_contact"
                                    v-model="form.emergency_contact"
                                    type="text"
                                    class="form-control"
                                    :class="{ 'is-invalid': form.errors.emergency_contact }"
                                    placeholder="緊急聯絡人姓名"
                                >
                                <div v-if="form.errors.emergency_contact" class="invalid-feedback">
                                    {{ form.errors.emergency_contact }}
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="emergency_phone" class="form-label">緊急聯絡人電話</label>
                                <input
                                    id="emergency_phone"
                                    v-model="form.emergency_phone"
                                    type="tel"
                                    class="form-control"
                                    :class="{ 'is-invalid': form.errors.emergency_phone }"
                                    placeholder="09xxxxxxxx"
                                >
                                <div v-if="form.errors.emergency_phone" class="invalid-feedback">
                                    {{ form.errors.emergency_phone }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 工作資訊 -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="bi bi-briefcase"></i> 工作資訊
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="department" class="form-label">部門 <span class="text-danger">*</span></label>
                                <select
                                    id="department"
                                    v-model="form.department"
                                    class="form-select"
                                    :class="{ 'is-invalid': form.errors.department }"
                                    required
                                >
                                    <option value="">請選擇部門</option>
                                    <option v-for="dept in departmentOptions" :key="dept" :value="dept">
                                        {{ dept }}
                                    </option>
                                </select>
                                <div v-if="form.errors.department" class="invalid-feedback">
                                    {{ form.errors.department }}
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="position" class="form-label">職位 <span class="text-danger">*</span></label>
                                <input
                                    id="position"
                                    v-model="form.position"
                                    type="text"
                                    class="form-control"
                                    :class="{ 'is-invalid': form.errors.position }"
                                    placeholder="例：軟體工程師、行政專員"
                                    required
                                >
                                <div v-if="form.errors.position" class="invalid-feedback">
                                    {{ form.errors.position }}
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="position_id" class="form-label">職務 <span class="text-danger">*</span></label>
                                <select
                                    id="position_id"
                                    v-model="form.position_id"
                                    class="form-select"
                                    :class="{ 'is-invalid': form.errors.position_id }"
                                    required
                                >
                                    <option value="">請選擇職務</option>
                                    <optgroup v-for="role in roles" :key="role.id" :label="role.name + ' 角色'">
                                        <option 
                                            v-for="position in role.positions" 
                                            :key="position.id" 
                                            :value="position.id"
                                            :disabled="!position.is_active"
                                        >
                                            {{ position.name }} 
                                            <span v-if="!position.is_active">(已停用)</span>
                                        </option>
                                    </optgroup>
                                </select>
                                <div v-if="form.errors.position_id" class="invalid-feedback">
                                    {{ form.errors.position_id }}
                                </div>
                                <div class="form-text text-info">
                                    <i class="bi bi-info-circle"></i> 選擇職務後會自動分配對應角色和權限
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
                                    <button type="button" class="btn btn-outline-warning me-2" @click="resetForm">
                                        <i class="bi bi-arrow-clockwise"></i> 重設表單
                                    </button>
                                    <button type="submit" class="btn btn-primary" :disabled="form.processing">
                                        <i class="bi bi-person-plus"></i> {{ form.processing ? '建立中...' : '建立使用者' }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link, useForm } from '@inertiajs/vue3'
import { ref, computed } from 'vue'

const props = defineProps({
    roles: {
        type: Array,
        required: true,
    },
    departmentOptions: {
        type: Array,
        required: true,
    },
})

const showPassword = ref(false)

const form = useForm({
    name: '',
    username: '',
    email: '',
    password: '',
    password_confirmation: '',
    id_number: '',
    birth_date: '',
    gender: '',
    mobile_phone: '',
    home_phone: '',
    address: '',
    department: '',
    position: '',
    emergency_contact: '',
    emergency_phone: '',
    position_id: '', // 職務ID
    errors: {}
})

const maxBirthDate = computed(() => {
    const today = new Date()
    const eighteenYearsAgo = new Date(today.getFullYear() - 18, today.getMonth(), today.getDate())
    return eighteenYearsAgo.toISOString().split('T')[0]
})

const createUser = () => {
    form.post(route('admin.users.store'), {
        onError: (errors) => {
            form.errors = errors
        },
        onSuccess: () => {
            // 成功後會自動跳轉到使用者列表頁面
        }
    })
}

const resetForm = () => {
    form.reset()
}
</script>

<style scoped>
.form-check {
    padding: 0.75rem;
    border: 1px solid transparent;
    border-radius: 0.375rem;
    transition: all 0.15s ease-in-out;
    margin-bottom: 0.5rem;
}

.form-check:hover {
    background-color: #f8f9fa;
    border-color: #dee2e6;
}

.form-check-input:checked + .form-check-label {
    font-weight: 600;
}

.card-header h3 {
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.card-header i {
    color: #6c757d;
}

.btn-group .btn {
    margin-right: 5px;
}

.btn-group .btn:last-child {
    margin-right: 0;
}

.form-text {
    font-size: 0.875em;
    margin-top: 0.25rem;
}

.text-danger {
    color: #dc3545 !important;
}

.text-info {
    color: #0dcaf0 !important;
}
</style>
