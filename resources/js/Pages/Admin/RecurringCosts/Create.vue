<template>
    <AdminLayout :user="$page.props.auth.user">
        <template #header>
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">新增經常性費用組合</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item">
                            <Link :href="route('admin.dashboard')">儀表板</Link>
                        </li>
                        <li class="breadcrumb-item">
                            <Link :href="route('admin.recurring-costs.index')">經常性費用管理</Link>
                        </li>
                        <li class="breadcrumb-item active">新增組合</li>
                    </ol>
                </div>
            </div>
        </template>

        <div class="card">
            <div class="card-header" style="background-color:#B3D9D9;">
                <h3 class="card-title">組合資訊</h3>
            </div>
            <div class="card-body">
                <form @submit.prevent="submit">
                    <!-- 基本資訊 -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label required">組合名稱</label>
                            <input
                                v-model="form.name"
                                type="text"
                                class="form-control"
                                :class="{ 'is-invalid': form.errors.name }"
                                required
                            >
                            <div v-if="form.errors.name" class="invalid-feedback">{{ form.errors.name }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">狀態</label>
                            <select v-model="form.is_active" class="form-select">
                                <option :value="true">啟用</option>
                                <option :value="false">停用</option>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label class="form-label">說明</label>
                            <textarea
                                v-model="form.description"
                                class="form-control"
                                rows="2"
                                :class="{ 'is-invalid': form.errors.description }"
                            ></textarea>
                            <div v-if="form.errors.description" class="invalid-feedback">{{ form.errors.description }}</div>
                        </div>
                    </div>

                    <!-- 費用項目 -->
                    <hr>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">費用項目</h5>
                        <button type="button" @click="addItem" class="btn btn-sm btn-success">
                            <i class="bi bi-plus-circle"></i> 新增項目
                        </button>
                    </div>

                    <div v-if="form.errors.items" class="alert alert-danger mb-3">
                        {{ form.errors.items }}
                    </div>

                    <div v-if="form.items.length === 0" class="alert alert-warning">
                        請至少新增一個費用項目
                    </div>

                    <div v-for="(item, index) in form.items" :key="index" class="card mb-3">
                        <div class="card-body">
                            <div class="row align-items-end">
                                <div class="col-md-4">
                                    <label class="form-label required">會計科目</label>
                                    <select
                                        v-model="item.account_detail_id"
                                        class="form-select"
                                        :class="{ 'is-invalid': form.errors[`items.${index}.account_detail_id`] }"
                                        required
                                    >
                                        <option value="">請選擇</option>
                                        <option
                                            v-for="account in accountDetails"
                                            :key="account.id"
                                            :value="account.id"
                                        >
                                            {{ account.account_code }} - {{ account.account_name }}
                                        </option>
                                    </select>
                                    <div v-if="form.errors[`items.${index}.account_detail_id`]" class="invalid-feedback">
                                        {{ form.errors[`items.${index}.account_detail_id`] }}
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label required">金額</label>
                                    <input
                                        v-model.number="item.amount"
                                        type="number"
                                        class="form-control"
                                        :class="{ 'is-invalid': form.errors[`items.${index}.amount`] }"
                                        min="0"
                                        required
                                    >
                                    <div v-if="form.errors[`items.${index}.amount`]" class="invalid-feedback">
                                        {{ form.errors[`items.${index}.amount`] }}
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">備註</label>
                                    <input
                                        v-model="item.note"
                                        type="text"
                                        class="form-control"
                                        :class="{ 'is-invalid': form.errors[`items.${index}.note`] }"
                                    >
                                    <div v-if="form.errors[`items.${index}.note`]" class="invalid-feedback">
                                        {{ form.errors[`items.${index}.note`] }}
                                    </div>
                                </div>
                                <div class="col-md-1">
                                    <button
                                        type="button"
                                        @click="removeItem(index)"
                                        class="btn btn-danger w-100"
                                        title="刪除"
                                    >
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 總金額顯示 -->
                    <div class="alert alert-info d-flex justify-content-between align-items-center">
                        <span class="fw-bold">總金額：</span>
                        <span class="h4 mb-0">{{ formatCurrency(totalAmount) }}</span>
                    </div>

                    <!-- 按鈕 -->
                    <div class="d-flex justify-content-end gap-2">
                        <Link :href="route('admin.recurring-costs.index')" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> 取消
                        </Link>
                        <button type="submit" class="btn btn-primary" :disabled="form.processing">
                            <i class="bi bi-check-circle"></i> 儲存
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
    accountDetails: Array,
})

const form = useForm({
    name: '',
    description: '',
    is_active: true,
    items: [],
})

const addItem = () => {
    form.items.push({
        account_detail_id: '',
        amount: 0,
        note: '',
    })
}

const removeItem = (index) => {
    form.items.splice(index, 1)
}

const totalAmount = computed(() => {
    return form.items.reduce((sum, item) => sum + (Number(item.amount) || 0), 0)
})

const submit = () => {
    form.post(route('admin.recurring-costs.store'))
}

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('zh-TW', {
        style: 'currency',
        currency: 'TWD',
        minimumFractionDigits: 0,
    }).format(amount || 0)
}
</script>

<style scoped>
.required::after {
    content: ' *';
    color: red;
}
</style>
