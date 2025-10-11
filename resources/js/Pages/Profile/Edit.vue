<script setup>
import AuthenticatedLayout from '@/Layouts/AdminLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});
</script>

<template>
    <Head title="個人設定" />

    <AuthenticatedLayout :user="$page.props.auth.user">
        <template #header>
            <div class="d-flex align-items-center">
                <div class="me-3">
                    <div class="bg-primary rounded-circle p-3" style="width: 60px; height: 60px;">
                        <i class="bi bi-person-circle text-white fs-2 d-flex align-items-center justify-content-center"></i>
                    </div>
                </div>
                <div>
                    <h2 class="h3 mb-1 text-dark fw-bold">個人設定</h2>
                    <p class="text-muted mb-0 small">
                        <i class="bi bi-gear me-1"></i>
                        管理您的帳戶資訊、密碼及安全設定
                    </p>
                </div>
            </div>
        </template>

        <div class="container-fluid py-4">
            <div class="row justify-content-center">
                <div class="col-12 col-xl-11">
                    <!-- 導航標籤 -->
                    <div class="mb-4">
                        <ul class="nav nav-pills nav-fill bg-light rounded p-1 shadow-sm" id="profile-tabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active rounded" id="profile-tab" data-bs-toggle="pill" data-bs-target="#profile-pane" type="button" role="tab" aria-controls="profile-pane" aria-selected="true">
                                    <i class="bi bi-person me-2"></i>個人資料
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link rounded" id="password-tab" data-bs-toggle="pill" data-bs-target="#password-pane" type="button" role="tab" aria-controls="password-pane" aria-selected="false">
                                    <i class="bi bi-shield-lock me-2"></i>密碼安全
                                </button>
                            </li>
                            <!--<li class="nav-item" role="presentation">
                                <button class="nav-link rounded" id="danger-tab" data-bs-toggle="pill" data-bs-target="#danger-pane" type="button" role="tab" aria-controls="danger-pane" aria-selected="false">
                                    <i class="bi bi-exclamation-triangle me-2"></i>危險操作
                                </button>
                            </li>-->
                        </ul>
                    </div>

                    <!-- 標籤內容 -->
                    <div class="tab-content" id="profile-tabs-content">
                        <!-- 個人資料標籤 -->
                        <div class="tab-pane fade show active" id="profile-pane" role="tabpanel" aria-labelledby="profile-tab">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-primary text-white border-0">
                                    <h5 class="card-title mb-0 d-flex align-items-center">
                                        <i class="bi bi-person-lines-fill me-2"></i>
                                        個人資料設定
                                    </h5>
                                </div>
                                <div class="card-body p-4">
                                    <div class="row">
                                        <!--<div class="col-md-3 text-center mb-3">
                                            <div class="bg-light rounded-circle mx-auto p-4 mb-3" style="width: 120px; height: 120px;">
                                                <i class="bi bi-person text-primary" style="font-size: 4rem;"></i>
                                            </div>
                                            <small class="text-muted">頭像功能即將推出</small>
                                        </div>-->
                                        <div class="col-md-12">
                                            <UpdateProfileInformationForm
                                                :must-verify-email="mustVerifyEmail"
                                                :status="status"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 密碼安全標籤 -->
                        <div class="tab-pane fade" id="password-pane" role="tabpanel" aria-labelledby="password-tab">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-warning text-white border-0">
                                    <h5 class="card-title mb-0 d-flex align-items-center">
                                        <i class="bi bi-shield-check me-2"></i>
                                        密碼與安全設定
                                    </h5>
                                </div>
                                <div class="card-body p-4">
                                    <div class="row">
                                        <div class="col-md-3 text-center mb-3">
                                            <div class="bg-warning bg-opacity-10 rounded-circle mx-auto p-4 mb-3" style="width: 120px; height: 120px;">
                                                <i class="bi bi-shield-lock text-warning" style="font-size: 4rem;"></i>
                                            </div>
                                            <div class="alert alert-info small">
                                                <i class="bi bi-lightbulb me-1"></i>
                                                建議定期更新密碼以確保帳戶安全
                                            </div>
                                        </div>
                                        <div class="col-md-9">
                                            <UpdatePasswordForm />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 危險操作標籤 -->
                        <div class="tab-pane fade" id="danger-pane" role="tabpanel" aria-labelledby="danger-tab">
                            <div class="card border-danger shadow-sm">
                                <div class="card-header bg-danger text-white border-0">
                                    <h5 class="card-title mb-0 d-flex align-items-center">
                                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                        危險操作區域
                                    </h5>
                                </div>
                                <div class="card-body p-4">
                                    <div class="row">
                                        <div class="col-md-3 text-center mb-3">
                                            <div class="bg-danger bg-opacity-10 rounded-circle mx-auto p-4 mb-3" style="width: 120px; height: 120px;">
                                                <i class="bi bi-exclamation-triangle text-danger" style="font-size: 4rem;"></i>
                                            </div>
                                            <div class="alert alert-danger small">
                                                <i class="bi bi-exclamation-triangle me-1"></i>
                                                刪除帳戶後所有資料將無法復原
                                            </div>
                                        </div>
                                        <div class="col-md-9">
                                            <DeleteUserForm />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
