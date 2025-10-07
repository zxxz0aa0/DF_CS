<template>
    <AdminLayout :user="$page.props.auth.user">
        <template #header>
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">催帳管理</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item">
                            <Link :href="route('admin.dashboard')">儀表板</Link>
                        </li>
                        <li class="breadcrumb-item active">催帳管理</li>
                    </ol>
                </div>
            </div>
        </template>

        <!-- 統計卡片 -->
        <div class="row mb-3">
            <div class="col-md-6">
                <div class="info-box">
                    <span class="info-box-icon bg-danger">
                        <i class="bi bi-exclamation-triangle"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">欠款人數</span>
                        <span class="info-box-number">{{ statistics.total_debtors }} 人</span>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="info-box">
                    <span class="info-box-icon bg-warning">
                        <i class="bi bi-currency-dollar"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">總欠款金額</span>
                        <span class="info-box-number">
                            {{ formatCurrency(Math.abs(statistics.total_debt_amount ?? 0)) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- 搜尋與列表 -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">欠款駕駛列表</h3>
            </div>
            <div class="card-body">
                <!-- 搜尋列 -->
                <div class="row mb-3 align-items-center">
                    <div class="col-md-6">
                        <div class="input-group">
                            <input
                                v-model="searchForm.search"
                                type="text"
                                class="form-control"
                                placeholder="搜尋姓名、身分證..."
                                @keyup.enter="search"
                            >
                            <button @click="search" class="btn btn-primary" type="button">
                                <i class="bi bi-search"></i> 搜尋
                            </button>
                        </div>
                    </div>
                    <div class="col-md-2 ms-auto" v-if="hasSearch">
                        <button @click="clearSearch" class="btn btn-outline-secondary w-100" type="button">
                            <i class="bi bi-x-circle"></i> 清除搜尋
                        </button>
                    </div>
                </div>

                <!-- 表格 -->
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th class="text-center">姓名</th>
                                <th class="text-center">身分證字號</th>
                                <th class="text-center">聯絡電話</th>
                                <th class="text-center">借方總額</th>
                                <th class="text-center">貸方總額</th>
                                <th class="text-center">餘額</th>
                                <th class="text-center">狀態</th>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="debtor in debtors.data" :key="debtor.driver_id">
                                <td class="text-center">{{ debtor.driver_name }}</td>
                                <td class="text-center">{{ debtor.driver_id_number }}</td>
                                <td class="text-center">{{ debtor.driver?.mobile_phone1 || '-' }}</td>
                                <td class="text-center">{{ formatCurrency(debtor.total_debit) }}</td>
                                <td class="text-center">{{ formatCurrency(debtor.total_credit) }}</td>
                                <td class="text-center">
                                    <span class="badge bg-danger">
                                        {{ formatCurrency(Math.abs(debtor.balance)) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span :class="['badge', debtor.driver?.status === 'open' ? 'bg-success' : 'bg-secondary']">
                                        {{ debtor.driver?.status === 'open' ? '在籍' : '已退籍' }}
                                    </span>
                                </td>
                                <td>
                                    <Link
                                        :href="route('admin.collections.show', debtor.driver_id)"
                                        class="btn btn-sm btn-info"
                                    >
                                        <i class="bi bi-list"></i> 查看明細
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- 無資料提示 -->
                <div v-if="debtors.data.length === 0" class="text-center py-4">
                    <i class="bi bi-check-circle" style="font-size: 3rem; color: #28a745;"></i>
                    <p class="text-muted mt-2">
                        <template v-if="hasSearch">
                            找不到符合搜尋條件的記錄
                        </template>
                        <template v-else>
                            目前沒有欠款中的駕駛
                        </template>
                    </p>
                </div>

                <!-- 分頁 -->
                <div v-if="debtors.last_page > 1" class="d-flex justify-content-center mt-3">
                    <nav>
                        <ul class="pagination">
                            <li class="page-item" :class="{ disabled: !debtors.prev_page_url }">
                                <Link class="page-link" :href="debtors.prev_page_url" :preserve-state="true">
                                    上一頁
                                </Link>
                            </li>
                            <li
                                v-for="page in paginationPages"
                                :key="page"
                                class="page-item"
                                :class="{ active: page === debtors.current_page }"
                            >
                                <Link
                                    class="page-link"
                                    :href="getPageUrl(page)"
                                    :preserve-state="true"
                                >
                                    {{ page }}
                                </Link>
                            </li>
                            <li class="page-item" :class="{ disabled: !debtors.next_page_url }">
                                <Link class="page-link" :href="debtors.next_page_url" :preserve-state="true">
                                    下一頁
                                </Link>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { computed, ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
    debtors: {
        type: Object,
        required: true
    },
    statistics: {
        type: Object,
        required: true
    },
    filters: {
        type: Object,
        default: () => ({})
    }
})

const searchForm = ref({
    search: props.filters.search || ''
})

const hasSearch = computed(() => {
    return Boolean(searchForm.value.search)
})

const paginationPages = computed(() => {
    const pages = []
    const current = props.debtors.current_page ?? 1
    const last = props.debtors.last_page ?? 1

    let start = Math.max(1, current - 2)
    let end = Math.min(last, current + 2)

    if (end - start < 4) {
        if (start === 1) {
            end = Math.min(last, start + 4)
        } else {
            start = Math.max(1, end - 4)
        }
    }

    for (let i = start; i <= end; i += 1) {
        pages.push(i)
    }

    return pages
})

const search = () => {
    router.get(route('admin.collections.index'), {
        search: searchForm.value.search || undefined
    }, {
        preserveState: true,
        replace: true
    })
}

const clearSearch = () => {
    if (!searchForm.value.search) {
        return
    }

    searchForm.value.search = ''
    search()
}

const getPageUrl = (page) => {
    const url = new URL(props.debtors.path, window.location.origin)
    url.searchParams.set('page', page)

    if (searchForm.value.search) {
        url.searchParams.set('search', searchForm.value.search)
    }

    return url.toString()
}

const formatCurrency = (amount) => {
    const numeric = Number(amount ?? 0)

    return new Intl.NumberFormat('zh-TW', {
        style: 'currency',
        currency: 'TWD',
        minimumFractionDigits: 0
    }).format(numeric)
}

const formatDate = (date) => {
    if (!date) {
        return '-'
    }

    return new Date(date).toLocaleDateString('zh-TW')
}
</script>
