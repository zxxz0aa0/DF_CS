<template>
    <AdminLayout :user="$page.props.auth.user">
        <template #header>
            <div class="row">
                <div class="col-sm-6">
                    <h1 class="m-0">編輯公司類別</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item">
                            <Link :href="route('admin.dashboard')">管理後台</Link>
                        </li>
                        <li class="breadcrumb-item">
                            <Link :href="route('admin.company-categories.index')">公司類別管理</Link>
                        </li>
                        <li class="breadcrumb-item active">編輯類別</li>
                    </ol>
                </div>
            </div>
        </template>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">編輯公司類別</h3>
                    </div>

                    <form @submit.prevent="submit">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <!-- 類別名稱 -->
                                    <div class="mb-3">
                                        <label for="name" class="form-label">
                                            類別名稱 <span class="text-danger">*</span>
                                        </label>
                                        <input
                                            type="text"
                                            id="name"
                                            class="form-control"
                                            :class="{ 'is-invalid': form.errors.name }"
                                            v-model="form.name"
                                            placeholder="請輸入類別名稱"
                                            required
                                        >
                                        <div class="invalid-feedback" v-if="form.errors.name">
                                            {{ form.errors.name }}
                                        </div>
                                    </div>

                                    <!-- 描述 -->
                                    <div class="mb-3">
                                        <label for="description" class="form-label">描述</label>
                                        <textarea
                                            id="description"
                                            class="form-control"
                                            :class="{ 'is-invalid': form.errors.description }"
                                            v-model="form.description"
                                            rows="4"
                                            placeholder="請輸入類別描述（選填）"
                                        ></textarea>
                                        <div class="invalid-feedback" v-if="form.errors.description">
                                            {{ form.errors.description }}
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
                                    {{ form.processing ? '處理中...' : '更新類別' }}
                                </button>
                                <Link
                                    :href="route('admin.company-categories.index')"
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
    category: Object,
})

const form = useForm({
    name: props.category.name,
    description: props.category.description,
})

const submit = () => {
    form.put(route('admin.company-categories.update', props.category.id))
}
</script>
