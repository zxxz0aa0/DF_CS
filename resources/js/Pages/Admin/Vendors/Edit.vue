<template>
    <AdminLayout :user="$page.props.auth.user">
        <div class="content-wrapper">
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1 class="m-0">編輯廠商</h1>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item">
                                    <Link :href="route('admin.dashboard')">管理後台</Link>
                                </li>
                                <li class="breadcrumb-item">
                                    <Link :href="route('admin.vendors.index')">廠商管理</Link>
                                </li>
                                <li class="breadcrumb-item active">編輯廠商</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <section class="content">
                <div class="container-fluid">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">編輯廠商資料 - {{ vendor.name || '未命名廠商' }}</h3>
                            <div class="card-tools">
                                <small class="text-muted">
                                    最後更新：{{ formatDate(vendor.updated_at) }}
                                </small>
                            </div>
                        </div>

                        <form @submit.prevent="submit">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="name" class="form-label">廠商名稱</label>
                                            <input
                                                id="name"
                                                v-model="form.name"
                                                type="text"
                                                class="form-control"
                                                :class="{ 'is-invalid': form.errors.name }"
                                                placeholder="請輸入廠商名稱"
                                            />
                                            <div v-if="form.errors.name" class="invalid-feedback">
                                                {{ form.errors.name }}
                                            </div>
                                        </div>

                                        <div class="form-group mb-3">
                                            <label for="phone" class="form-label">廠商電話</label>
                                            <input
                                                id="phone"
                                                v-model="form.phone"
                                                type="text"
                                                class="form-control"
                                                :class="{ 'is-invalid': form.errors.phone }"
                                                placeholder="請輸入廠商電話"
                                            />
                                            <div v-if="form.errors.phone" class="invalid-feedback">
                                                {{ form.errors.phone }}
                                            </div>
                                        </div>

                                        <div class="form-group mb-3">
                                            <label for="address" class="form-label">廠商地址</label>
                                            <textarea
                                                id="address"
                                                v-model="form.address"
                                                class="form-control"
                                                :class="{ 'is-invalid': form.errors.address }"
                                                rows="3"
                                                placeholder="請輸入廠商地址"
                                            ></textarea>
                                            <div v-if="form.errors.address" class="invalid-feedback">
                                                {{ form.errors.address }}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="service_content" class="form-label">服務內容</label>
                                            <textarea
                                                id="service_content"
                                                v-model="form.service_content"
                                                class="form-control"
                                                :class="{ 'is-invalid': form.errors.service_content }"
                                                rows="5"
                                                placeholder="請輸入服務內容"
                                            ></textarea>
                                            <div v-if="form.errors.service_content" class="invalid-feedback">
                                                {{ form.errors.service_content }}
                                            </div>
                                        </div>

                                        <div class="form-group mb-3">
                                            <label for="note" class="form-label">備註</label>
                                            <textarea
                                                id="note"
                                                v-model="form.note"
                                                class="form-control"
                                                :class="{ 'is-invalid': form.errors.note }"
                                                rows="5"
                                                placeholder="請輸入備註"
                                            ></textarea>
                                            <div v-if="form.errors.note" class="invalid-feedback">
                                                {{ form.errors.note }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card-footer">
                                <div class="d-flex justify-content-between">
                                    <Link
                                        :href="route('admin.vendors.index')"
                                        class="btn btn-secondary"
                                    >
                                        <i class="bi bi-arrow-left"></i>
                                        返回列表
                                    </Link>
                                    <button
                                        type="submit"
                                        class="btn btn-primary"
                                        :disabled="form.processing"
                                    >
                                        <i class="bi bi-check"></i>
                                        <span v-if="form.processing">更新中...</span>
                                        <span v-else>更新</span>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </section>
        </div>
    </AdminLayout>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
    vendor: Object,
})

const form = useForm({
    name: props.vendor.name || '',
    phone: props.vendor.phone || '',
    address: props.vendor.address || '',
    service_content: props.vendor.service_content || '',
    note: props.vendor.note || '',
})

const formatDate = (dateString) => {
    if (!dateString) return '-'
    return new Date(dateString).toLocaleDateString('zh-TW', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
    })
}

const submit = () => {
    form.put(route('admin.vendors.update', props.vendor.id))
}
</script>