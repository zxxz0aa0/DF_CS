<template>
    <AdminLayout :user="$page.props.auth.user">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <div class="mb-4">
                            <i class="bi bi-shield-exclamation text-warning" style="font-size: 4rem;"></i>
                        </div>
                        
                        <h1 class="h2 mb-3">權限不足</h1>
                        
                        <p class="text-muted mb-4">
                            抱歉，您沒有權限存取此頁面或執行此操作。
                            請聯繫系統管理員以獲得適當的權限。
                        </p>
                        
                        <div class="d-flex justify-content-center gap-3">
                            <button 
                                @click="goBack" 
                                class="btn btn-secondary"
                            >
                                <i class="bi bi-arrow-left me-2"></i>返回上頁
                            </button>
                            
                            <Link 
                                :href="route('admin.dashboard')" 
                                class="btn btn-primary"
                            >
                                <i class="bi bi-house me-2"></i>回到首頁
                            </Link>
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

const goBack = () => {
    window.history.back()
}
</script>