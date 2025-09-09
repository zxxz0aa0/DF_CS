<template>
    <AdminLayout :user="$page.props.auth.user">
        <template #header>
            <div class="row">
                <div class="col-sm-6">
                    <h1 class="m-0">公司詳細</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item">
                            <Link :href="route('admin.dashboard')">管理後台</Link>
                        </li>
                        <li class="breadcrumb-item">
                            <Link :href="route('admin.companies.index')">公司資料管理</Link>
                        </li>
                        <li class="breadcrumb-item active">公司詳細</li>
                    </ol>
                </div>
            </div>
        </template>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div class="row col-md-12">
                            <div class="col-6">
                                <h3 class="card-title">{{ company.name }}</h3>
                            </div>
                            <div class="col-6 text-end">
                                <div>
                                <Link :href="route('admin.companies.edit', company.id)" class="btn btn-primary me-2">
                                    <i class="bi bi-pencil-square me-1"></i> 編輯
                                </Link>
                                <Link :href="route('admin.companies.index')" class="btn btn-secondary">
                                    返回列表
                                </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <dl class="row mb-0">
                                    <dt class="col-sm-4">類別</dt>
                                    <dd class="col-sm-8">{{ company.category?.name || '-' }}</dd>

                                    <dt class="col-sm-4">統一編號</dt>
                                    <dd class="col-sm-8">{{ company.tax_id || '-' }}</dd>

                                    <dt class="col-sm-4">負責人</dt>
                                    <dd class="col-sm-8">{{ company.representative || '-' }}</dd>

                                    <dt class="col-sm-4">狀態</dt>
                                    <dd class="col-sm-8">
                                        <span class="badge" :class="company.status === 'active' ? 'bg-success' : 'bg-secondary'">
                                            {{ company.status === 'active' ? '啟用' : '停用' }}
                                        </span>
                                    </dd>
                                </dl>
                            </div>
                            <div class="col-md-6">
                                <dl class="row mb-0">
                                    <dt class="col-sm-4">電話</dt>
                                    <dd class="col-sm-8">{{ company.phone || '-' }}</dd>

                                    <dt class="col-sm-4">Email</dt>
                                    <dd class="col-sm-8">{{ company.email || '-' }}</dd>

                                    <dt class="col-sm-4">網站</dt>
                                    <dd class="col-sm-8">
                                        <a v-if="company.website" :href="company.website" target="_blank" rel="noopener">{{ company.website }}</a>
                                        <span v-else>-</span>
                                    </dd>

                                    <dt class="col-sm-4">地址</dt>
                                    <dd class="col-sm-8">{{ company.address || '-' }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>

</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link } from '@inertiajs/vue3'

defineProps({
    company: {
        type: Object,
        required: true,
    },
})
</script>

<style scoped>
dt { color: #6c757d; }
</style>

