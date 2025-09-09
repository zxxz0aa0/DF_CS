<template>
    <AdminLayout :user="$page.props.auth.user">
        <template #header>
            <div class="row">
                <div class="col-sm-6">
                    <h1 class="m-0">新增公司資料</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item">
                            <Link :href="route('admin.dashboard')">管理後台</Link>
                        </li>
                        <li class="breadcrumb-item">
                            <Link :href="route('admin.companies.index')">公司資料管理</Link>
                        </li>
                        <li class="breadcrumb-item active">新增公司</li>
                    </ol>
                </div>
            </div>
        </template>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">新增公司資料</h3>
                    </div>

                    <form @submit.prevent="submit">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <!-- 公司類別 -->
                                    <div class="mb-3">
                                        <label for="category_id" class="form-label">
                                            公司類別 <span class="text-danger">*</span>
                                        </label>
                                        <select
                                            id="category_id"
                                            class="form-select"
                                            :class="{ 'is-invalid': form.errors.category_id }"
                                            v-model="form.category_id"
                                            required
                                        >
                                            <option value="">請選擇公司類別</option>
                                            <option v-for="category in categories" :key="category.id" :value="category.id">
                                                {{ category.name }}
                                            </option>
                                        </select>
                                        <div class="invalid-feedback" v-if="form.errors.category_id">
                                            {{ form.errors.category_id }}
                                        </div>
                                    </div>

                                    <!-- 公司名稱 -->
                                    <div class="mb-3">
                                        <label for="name" class="form-label">
                                            公司名稱 <span class="text-danger">*</span>
                                        </label>
                                        <input
                                            type="text"
                                            id="name"
                                            class="form-control"
                                            :class="{ 'is-invalid': form.errors.name }"
                                            v-model="form.name"
                                            placeholder="請輸入公司名稱"
                                            required
                                        >
                                        <div class="invalid-feedback" v-if="form.errors.name">
                                            {{ form.errors.name }}
                                        </div>
                                    </div>

                                    <!-- 地址 -->
                                    <div class="mb-3">
                                        <label for="address" class="form-label">公司地址</label>
                                        <textarea
                                            id="address"
                                            class="form-control"
                                            :class="{ 'is-invalid': form.errors.address }"
                                            v-model="form.address"
                                            rows="3"
                                            placeholder="請輸入公司地址（選填）"
                                        ></textarea>
                                        <div class="invalid-feedback" v-if="form.errors.address">
                                            {{ form.errors.address }}
                                        </div>
                                    </div>

                                    <!-- 統一編號 -->
                                    <div class="mb-3">
                                        <label for="tax_id" class="form-label">統一編號</label>
                                        <input
                                            type="text"
                                            id="tax_id"
                                            class="form-control"
                                            :class="{ 'is-invalid': form.errors.tax_id }"
                                            v-model="form.tax_id"
                                            placeholder="請輸入統一編號（選填）"
                                        >
                                        <div class="invalid-feedback" v-if="form.errors.tax_id">
                                            {{ form.errors.tax_id }}
                                        </div>
                                    </div>

                                    <!-- 聯絡電話 -->
                                    <div class="mb-3">
                                        <label for="phone" class="form-label">聯絡電話</label>
                                        <input
                                            type="text"
                                            id="phone"
                                            class="form-control"
                                            :class="{ 'is-invalid': form.errors.phone }"
                                            v-model="form.phone"
                                            placeholder="請輸入聯絡電話（選填）"
                                        >
                                        <div class="invalid-feedback" v-if="form.errors.phone">
                                            {{ form.errors.phone }}
                                        </div>
                                    </div>

                                    <!-- 負責人 -->
                                    <div class="mb-3">
                                        <label for="representative" class="form-label">負責人</label>
                                        <input
                                            type="text"
                                            id="representative"
                                            class="form-control"
                                            :class="{ 'is-invalid': form.errors.representative }"
                                            v-model="form.representative"
                                            placeholder="請輸入負責人（選填）"
                                        >
                                        <div class="invalid-feedback" v-if="form.errors.representative">
                                            {{ form.errors.representative }}
                                        </div>
                                    </div>

                                    <!-- 電子信箱 -->
                                    <div class="mb-3">
                                        <label for="email" class="form-label">電子信箱</label>
                                        <input
                                            type="email"
                                            id="email"
                                            class="form-control"
                                            :class="{ 'is-invalid': form.errors.email }"
                                            v-model="form.email"
                                            placeholder="請輸入電子信箱（選填）"
                                        >
                                        <div class="invalid-feedback" v-if="form.errors.email">
                                            {{ form.errors.email }}
                                        </div>
                                    </div>

                                    <!-- 網站 -->
                                    <div class="mb-3">
                                        <label for="website" class="form-label">公司網站</label>
                                        <input
                                            type="url"
                                            id="website"
                                            class="form-control"
                                            :class="{ 'is-invalid': form.errors.website }"
                                            v-model="form.website"
                                            placeholder="請輸入公司網站（選填）"
                                        >
                                        <div class="invalid-feedback" v-if="form.errors.website">
                                            {{ form.errors.website }}
                                        </div>
                                    </div>

                                    <!-- 狀態 -->
                                    <div class="mb-3">
                                        <label for="status" class="form-label">
                                            狀態 <span class="text-danger">*</span>
                                        </label>
                                        <select
                                            id="status"
                                            class="form-select"
                                            :class="{ 'is-invalid': form.errors.status }"
                                            v-model="form.status"
                                            required
                                        >
                                            <option value="active">啟用</option>
                                            <option value="inactive">停用</option>
                                        </select>
                                        <div class="invalid-feedback" v-if="form.errors.status">
                                            {{ form.errors.status }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer">
                            <div class="d-flex gap-2">
                                <button
                                    type="submit"
                                    class="btn btn-primary"
                                    :disabled="form.processing"
                                >
                                    <span class="spinner-border spinner-border-sm me-1" v-if="form.processing"></span>
                                    <i class="bi bi-check-circle me-1" v-else></i>
                                    {{ form.processing ? '處理中...' : '建立公司' }}
                                </button>
                                <Link
                                    :href="route('admin.companies.index')"
                                    class="btn btn-secondary"
                                >
                                    <i class="bi bi-arrow-left me-1"></i>返回列表
                                </Link>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'
import { Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
    categories: Array,
})

const form = useForm({
    category_id: '',
    name: '',
    address: '',
    tax_id: '',
    phone: '',
    representative: '',
    email: '',
    website: '',
    status: 'active',
})

const submit = () => {
    form.post(route('admin.companies.store'))
}
</script>
