<template>
    <AdminLayout :user="$page.props.auth.user">
        <div class="content-wrapper">
            <!-- Content Header -->
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1 class="m-0">新增車輛</h1>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item">
                                    <Link :href="route('admin.dashboard')">儀表板</Link>
                                </li>
                                <li class="breadcrumb-item">
                                    <Link :href="route('admin.vehicles.index')">車輛管理</Link>
                                </li>
                                <li class="breadcrumb-item active">新增車輛</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main content -->
            <section class="content">
                <div class="container-fluid">
                    <VehicleForm
                        :company-categories="companyCategories"
                        :companies="companies"
                        :errors="$page.props.errors"
                        :processing="processing"
                        submit-text="新增車輛"
                        @submit="handleSubmit"
                    />
                </div>
            </section>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import VehicleForm from '@/Components/Vehicle/VehicleForm.vue'

const props = defineProps({
    companyCategories: Array,
    companies: Array,
})

const processing = ref(false)

const handleSubmit = (form) => {
    processing.value = true

    router.post(route('admin.vehicles.store'), form, {
        onFinish: () => {
            processing.value = false
        },
        onSuccess: () => {
            // 成功後會自動跳轉到列表頁面
        },
        onError: (errors) => {
            console.error('新增車輛失敗:', errors)
        }
    })
}
</script>