<template>
    <AdminLayout :user="$page.props.auth.user">
        <template #header>
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">管理員儀表板</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item active">儀表板</li>
                    </ol>
                </div>
            </div>
        </template>

        <!-- 統計卡片 -->
        <div class="row">
            <div class="col-lg-4 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ stats.total_users }}</h3>
                        <p>總使用者數</p>
                    </div>
                    <div class="icon">
                        <i class="bi bi-people"></i>
                    </div>
                    <Link :href="route('admin.users.index')" class="small-box-footer">
                        詳細資訊 <i class="bi bi-arrow-right-circle"></i>
                    </Link>
                </div>
            </div>

            <div class="col-lg-4 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ stats.active_users }}</h3>
                        <p>已驗證使用者</p>
                    </div>
                    <div class="icon">
                        <i class="bi bi-person-check"></i>
                    </div>
                    <div class="small-box-footer" style="height: 29px;"></div>
                </div>
            </div>

            <div class="col-lg-4 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ stats.recent_users }}</h3>
                        <p>本週新用戶</p>
                    </div>
                    <div class="icon">
                        <i class="bi bi-person-plus"></i>
                    </div>
                    <div class="small-box-footer" style="height: 29px;"></div>
                </div>
            </div>
        </div>

        <!-- 系統資訊卡片 -->
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="bi bi-info-circle me-2"></i>
                            系統資訊
                        </h3>
                    </div>
                    <div class="card-body">
                        <dl class="row">
                            <dt class="col-sm-4">Laravel 版本</dt>
                            <dd class="col-sm-8">{{ $page.props.app?.laravel_version || '11.x' }}</dd>
                            
                            <dt class="col-sm-4">PHP 版本</dt>
                            <dd class="col-sm-8">{{ $page.props.app?.php_version || '8.2+' }}</dd>
                            
                            <dt class="col-sm-4">當前時間</dt>
                            <dd class="col-sm-8">{{ currentTime }}</dd>
                        </dl>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="bi bi-activity me-2"></i>
                            系統狀態
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex justify-content-between">
                                    <span>資料庫連線</span>
                                    <span class="badge bg-success">正常</span>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between">
                                    <span>快取系統</span>
                                    <span class="badge bg-success">正常</span>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between">
                                    <span>佇列處理</span>
                                    <span class="badge bg-success">正常</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 快速操作 -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="bi bi-lightning me-2"></i>
                            快速操作
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 col-6">
                                <Link :href="route('admin.users.create')" class="btn btn-primary btn-block">
                                    <i class="bi bi-person-plus me-2"></i>新增使用者
                                </Link>
                            </div>
                            <div class="col-md-3 col-6">
                                <Link :href="route('admin.users.index')" class="btn btn-info btn-block">
                                    <i class="bi bi-people me-2"></i>管理使用者
                                </Link>
                            </div>
                            <div class="col-md-3 col-6">
                                <Link :href="route('dashboard')" class="btn btn-success btn-block">
                                    <i class="bi bi-house me-2"></i>前台首頁
                                </Link>
                            </div>
                            <div class="col-md-3 col-6">
                                <Link :href="route('profile.edit')" class="btn btn-secondary btn-block">
                                    <i class="bi bi-gear me-2"></i>個人設定
                                </Link>
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
import { ref, onMounted } from 'vue'

defineProps({
    stats: {
        type: Object,
        required: true,
    },
})

const currentTime = ref('')

const updateTime = () => {
    currentTime.value = new Date().toLocaleString('zh-TW', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
    })
}

onMounted(() => {
    updateTime()
    setInterval(updateTime, 1000)
})
</script>

<style scoped>
.small-box {
    border-radius: 0.375rem;
    position: relative;
    display: block;
    margin-bottom: 20px;
    box-shadow: 0 1px 1px rgba(0,0,0,0.1);
}

.small-box > .inner {
    padding: 10px;
}

.small-box .icon {
    position: absolute;
    top: auto;
    bottom: 10px;
    right: 10px;
    z-index: 0;
    font-size: 70px;
    color: rgba(0,0,0,0.15);
}

.small-box .small-box-footer {
    color: rgba(255,255,255,0.8);
    display: block;
    padding: 3px 15px;
    background: rgba(0,0,0,0.1);
    text-decoration: none;
    position: relative;
    overflow: hidden;
}

.small-box:hover .small-box-footer {
    background: rgba(0,0,0,0.15);
}
</style>