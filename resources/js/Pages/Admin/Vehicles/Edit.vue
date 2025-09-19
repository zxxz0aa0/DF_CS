<template>
    <AdminLayout :user="$page.props.auth.user">
        <div class="content-wrapper">
            <!-- Content Header -->
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1 class="m-0">編輯車輛</h1>
                            <small class="text-muted">車牌號碼: {{ vehicle.license_number }}</small>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item">
                                    <Link :href="route('admin.dashboard')">儀表板</Link>
                                </li>
                                <li class="breadcrumb-item">
                                    <Link :href="route('admin.vehicles.index')">車輛管理</Link>
                                </li>
                                <li class="breadcrumb-item">
                                    <Link :href="route('admin.vehicles.show', vehicle.id)">{{ vehicle.license_number }}</Link>
                                </li>
                                <li class="breadcrumb-item active">編輯</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main content -->
            <section class="content">
                <div class="container-fluid">
                    <VehicleForm
                        :vehicle="vehicle"
                        :company-categories="companyCategories"
                        :companies="companies"
                        :errors="$page.props.errors"
                        :processing="processing"
                        submit-text="更新車輛"
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
    vehicle: Object,
    companyCategories: Array,
    companies: Array,
})

const processing = ref(false)

const handleSubmit = (form) => {
    processing.value = true

    router.put(route('admin.vehicles.update', props.vehicle.id), form, {
        onFinish: () => {
            processing.value = false
        },
        onSuccess: () => {
            // 成功後會自動跳轉到詳情頁面
        },
        onError: (errors) => {
            console.error('更新車輛失敗:', errors)
        }
    })
}
</script>