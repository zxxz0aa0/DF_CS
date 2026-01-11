<template>
    <AdminLayout :user="$page.props.auth.user">
        <template #header>
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">每日交易明細報表</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item">
                            <Link :href="route('admin.dashboard')">儀表板</Link>
                        </li>
                        <li class="breadcrumb-item">報表管理</li>
                        <li class="breadcrumb-item active">每日交易明細報表</li>
                    </ol>
                </div>
            </div>
        </template>

        <!-- 統計卡片 -->
        <div class="row mb-2">
            <div class="col-md-6">
                <div class="info-box">
                    <span class="info-box-icon bg-success">
                        <i class="bi bi-arrow-down-circle"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">借方總額</span>
                        <span class="info-box-number">NT$ {{ formatAmount(total_debit) }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="info-box">
                    <span class="info-box-icon bg-danger">
                        <i class="bi bi-arrow-up-circle"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">貸方總額</span>
                        <span class="info-box-number">NT$ {{ formatAmount(total_credit) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- 主要內容卡片 -->
        <div class="card">
            <div class="card-header" style="background-color:#B3D9D9;">
                <div>
                    <h3 class="card-title">交易明細查詢</h3>
                </div>
                <div class="float-right">
                    <button @click="previewReport" class="btn btn-sm btn-primary">
                        <i class="bi bi-eye"></i> 預覽報表
                    </button>
                    <button v-if="permissions.canExport" @click="exportExcel" class="btn btn-sm btn-success ms-2" :disabled="exporting">
                        <i class="bi bi-file-earmark-excel"></i>
                        {{ exporting ? '匯出中...' : '匯出 Excel' }}
                    </button>
                    <button v-if="form.filter_config_id && permissions.canDeleteConfig" @click="deleteConfig" class="btn btn-sm btn-danger ms-2">
                        <i class="bi bi-trash"></i> 刪除目前組合
                    </button>
                </div>
            </div>
            <div class="card-body">
                <!-- 工具列 -->
                <div class="row mb-3">
                    <!-- 報表組合選擇 -->
                    <div class="col-md-3">
                        <label class="form-label">報表組合</label>
                        <select v-model="form.filter_config_id" class="form-select" @change="loadConfig">
                            <option :value="null">選擇已儲存的組合...</option>
                            <option v-for="config in report_configs" :key="config.id" :value="config.id">
                                {{ config.name }}
                            </option>
                        </select>
                    </div>

                    <!-- 日期範圍 -->
                    <div class="col-md-3">
                        <label class="form-label">開始日期</label>
                        <input type="date" v-model="form.start_date" class="form-control" @change="search">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">結束日期</label>
                        <input type="date" v-model="form.end_date" class="form-control" @change="search">
                    </div>

                    <!-- 快速按鈕 -->
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button @click="setToday" class="btn btn-outline-primary flex-fill" type="button">
                                <i class="bi bi-calendar-day"></i> 今日
                            </button>
                            <button
                                @click="toggleFilters"
                                class="btn btn-outline-secondary flex-fill"
                                type="button"
                            >
                                <i class="bi" :class="showFilters ? 'bi-funnel-fill' : 'bi-funnel'"></i> 篩選
                            </button>
                        </div>
                    </div>
                </div>

                <!-- 篩選區塊 (可收合) -->
                <div v-show="showFilters" class="border rounded p-3 mb-3 bg-light">
                    <div class="row">
                        <!-- 公司類別 -->
                        <div class="col-md-4">
                            <label class="form-label fw-bold">公司類別</label>
                            <div class="form-check" v-for="category in options.companyCategories" :key="category.id">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    :value="category.id"
                                    v-model="form.categories"
                                    :id="'category-' + category.id"
                                >
                                <label class="form-check-label" :for="'category-' + category.id">
                                    {{ category.name }}
                                </label>
                            </div>
                        </div>

                        <!-- 公司 -->
                        <div class="col-md-4">
                            <label class="form-label fw-bold">公司</label>
                            <div class="form-check">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    :value="-1"
                                    v-model="form.companies"
                                    id="company-none"
                                >
                                <label class="form-check-label" for="company-none">
                                    無所屬公司（駕駛/其他）
                                </label>
                            </div>
                            <div class="form-check" v-for="company in options.companies" :key="company.id">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    :value="company.id"
                                    v-model="form.companies"
                                    :id="'company-' + company.id"
                                >
                                <label class="form-check-label" :for="'company-' + company.id">
                                    {{ company.name }}
                                </label>
                            </div>
                        </div>

                        <!-- 會計科目 -->
                        <div class="col-md-4">
                            <label class="form-label fw-bold">會計科目</label>
                            <input
                                type="text"
                                v-model="accountSearch"
                                class="form-control form-control-sm mb-2"
                                placeholder="搜尋科目..."
                            >
                            <div style="max-height: 300px; overflow-y: auto;">
                                <div class="form-check" v-for="account in filteredAccounts" :key="account.id">
                                    <input
                                        class="form-check-input"
                                        type="checkbox"
                                        :value="account.id"
                                        v-model="form.accounts"
                                        :id="'account-' + account.id"
                                    >
                                    <label class="form-check-label small" :for="'account-' + account.id">
                                        {{ account.full_name }}
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 篩選操作按鈕 -->
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="d-flex gap-2 justify-content-end">
                                <button @click="clearFilters" class="btn btn-outline-secondary" type="button">
                                    <i class="bi bi-x-circle"></i> 清除篩選
                                </button>
                                <button @click="search" class="btn btn-primary" type="button">
                                    <i class="bi bi-search"></i> 套用篩選
                                </button>
                                <button
                                    v-if="permissions.canCreateConfig"
                                    @click="showSaveConfigModal = true"
                                    class="btn btn-success"
                                    type="button"
                                >
                                    <i class="bi bi-save"></i> 儲存為組合
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 資料表格 -->
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-sm mt-3">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center">交易日期</th>
                                <th class="text-center">公司類別</th>
                                <th class="text-center">車隊編號</th>
                                <th class="text-center">車牌號碼</th>
                                <th class="text-center">駕駛姓名</th>
                                <th class="text-center">會計科目</th>
                                <th class="text-center">借方金額</th>
                                <th class="text-center">貸方金額</th>
                                <th class="text-center">備註</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="records.data.length === 0">
                                <td colspan="9" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                                    <div class="mt-2">查無資料</div>
                                </td>
                            </tr>
                            <tr v-for="record in records.data" :key="record.id">
                                <td class="text-center">{{ record.created_at }}</td>
                                <td class="text-center">{{ record.company_category }}</td>
                                <td class="text-center">{{ record.vehicle_fleet_number }}</td>
                                <td class="text-center">{{ record.vehicle_license_number }}</td>
                                <td class="text-center">{{ record.driver_name }}</td>
                                <td class="text-center">{{ record.account_detail_name }}</td>
                                <td class="text-end">{{ formatAmount(record.debit_amount) }}</td>
                                <td class="text-end">{{ formatAmount(record.credit_amount) }}</td>
                                <td>{{ record.note }}</td>
                            </tr>
                        </tbody>
                        <tfoot class="table-secondary fw-bold">
                            <tr>
                                <td colspan="6" class="text-end">合計：</td>
                                <td class="text-end">{{ formatAmount(total_debit) }}</td>
                                <td class="text-end">{{ formatAmount(total_credit) }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- 分頁 -->
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="text-muted">
                        顯示第 {{ records.from || 0 }} 至 {{ records.to || 0 }} 筆，共 {{ records.total }} 筆
                    </div>
                    <nav v-if="records.links && records.links.length > 3">
                        <ul class="pagination mb-0">
                            <li
                                v-for="(link, index) in records.links"
                                :key="index"
                                class="page-item"
                                :class="{ active: link.active, disabled: !link.url }"
                            >
                                <Link
                                    v-if="link.url"
                                    :href="link.url"
                                    class="page-link"
                                    v-html="link.label"
                                    preserve-state
                                ></Link>
                                <span v-else class="page-link" v-html="link.label"></span>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>

        <!-- 儲存報表組合 Modal -->
        <div class="modal fade" :class="{ show: showSaveConfigModal, 'd-block': showSaveConfigModal }"
             tabindex="-1" style="background-color: rgba(0,0,0,0.5);" v-if="showSaveConfigModal">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">儲存報表組合</h5>
                        <button type="button" class="btn-close" @click="showSaveConfigModal = false"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">組合名稱 <span class="text-danger">*</span></label>
                            <input
                                type="text"
                                v-model="configName"
                                class="form-control"
                                placeholder="例如：計程車、車行、租賃"
                                @keyup.enter="saveConfig"
                            >
                            <div class="form-text">請不要重複名稱、若要覆蓋請先選舊名稱，刪除然後再新增</div>
                        </div>
                        <div class="alert alert-info mb-0">
                            <small>
                                <i class="bi bi-info-circle"></i>
                                將儲存目前的篩選條件（公司類別、公司、會計科目）
                            </small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="showSaveConfigModal = false">
                            取消
                        </button>
                        <button type="button" class="btn btn-primary" @click="saveConfig" :disabled="!configName">
                            <i class="bi bi-save"></i> 儲存
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

// Props
const props = defineProps({
    records: Object,
    total_debit: String,
    total_credit: String,
    report_configs: Array,
    options: Object,
    filters: Object,
    permissions: Object,
});

// 表單資料
const form = ref({
    start_date: props.filters.start_date,
    end_date: props.filters.end_date,
    filter_config_id: props.filters.filter_config_id,
    categories: props.filters.categories || [],
    companies: props.filters.companies || [],
    accounts: props.filters.accounts || [],
});

// UI 狀態
const showFilters = ref(false);
const showSaveConfigModal = ref(false);
const configName = ref('');
const accountSearch = ref('');
const exporting = ref(false);

// 計算屬性：過濾會計科目
const filteredAccounts = computed(() => {
    if (!accountSearch.value) {
        return props.options.accountDetails;
    }
    const keyword = accountSearch.value.toLowerCase();
    return props.options.accountDetails.filter(account =>
        account.full_name.toLowerCase().includes(keyword)
    );
});

// 設定今日
const setToday = () => {
    const today = new Date().toISOString().split('T')[0];
    form.value.start_date = today;
    form.value.end_date = today;
    search();
};

// 切換篩選區塊顯示
const toggleFilters = () => {
    showFilters.value = !showFilters.value;
};

// 清除篩選
const clearFilters = () => {
    form.value.categories = [];
    form.value.companies = [];
    form.value.accounts = [];
    search();
};

// 執行搜尋
const search = () => {
    router.get(route('admin.reports.daily-transaction.index'), {
        start_date: form.value.start_date,
        end_date: form.value.end_date,
        categories: form.value.categories,
        companies: form.value.companies,
        accounts: form.value.accounts,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

// 載入報表組合
const loadConfig = () => {
    if (!form.value.filter_config_id) {
        return;
    }

    const config = props.report_configs.find(c => c.id === form.value.filter_config_id);
    if (config) {
        form.value.categories = config.filters.categories || [];
        form.value.companies = config.filters.companies || [];
        form.value.accounts = config.filters.accounts || [];
        search();
    }
};

// 儲存報表組合
const saveConfig = () => {
    if (!configName.value) {
        alert('請輸入組合名稱');
        return;
    }

    router.post(route('admin.reports.configurations.store'), {
        name: configName.value,
        report_type: 'daily_transaction',
        filters: {
            categories: form.value.categories,
            companies: form.value.companies,
            accounts: form.value.accounts,
        },
    }, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            showSaveConfigModal.value = false;
            configName.value = '';
            alert('報表組合已儲存');
        },
        onError: (errors) => {
            console.error(errors);
            alert('儲存失敗，請稍後再試');
        },
    });
};

// 刪除報表組合
const deleteConfig = () => {
    if (!confirm('確定要刪除此報表組合嗎？')) {
        return;
    }

    router.delete(route('admin.reports.configurations.destroy', form.value.filter_config_id), {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            form.value.filter_config_id = null;
            alert('報表組合已刪除');
        },
    });
};

// 預覽報表 - 在新分頁開啟
const previewReport = () => {
    const params = new URLSearchParams({
        start_date: form.value.start_date,
        end_date: form.value.end_date,
        categories: JSON.stringify(form.value.categories),
        companies: JSON.stringify(form.value.companies),
        accounts: JSON.stringify(form.value.accounts),
    });

    window.open(route('admin.reports.daily-transaction.preview') + '?' + params.toString(), '_blank');
};

// 匯出 Excel
const exportExcel = () => {
    exporting.value = true;

    const params = new URLSearchParams({
        start_date: form.value.start_date,
        end_date: form.value.end_date,
        categories: JSON.stringify(form.value.categories),
        companies: JSON.stringify(form.value.companies),
        accounts: JSON.stringify(form.value.accounts),
    });

    window.location.href = route('admin.reports.daily-transaction.export') + '?' + params.toString();

    setTimeout(() => {
        exporting.value = false;
    }, 2000);
};

// 格式化金額 - 只顯示整數
const formatAmount = (amount) => {
    if (!amount || amount === '0.00' || parseFloat(amount) === 0) return '0';
    return Math.round(parseFloat(amount)).toLocaleString('zh-TW', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    });
};

// 初始化
onMounted(() => {
    // 如果有篩選條件，自動展開篩選區塊
    if (form.value.categories.length > 0 ||
        form.value.companies.length > 0 ||
        form.value.accounts.length > 0) {
        showFilters.value = true;
    }
});
</script>

<style scoped>
.info-box {
    min-height: 80px;
}

.info-box-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 80px;
    font-size: 2rem;
}

.modal.show {
    display: block;
}

.form-check {
    padding-left: 1.5rem;
}

.form-check-input {
    cursor: pointer;
}

.form-check-label {
    cursor: pointer;
    user-select: none;
}

.table-sm th,
.table-sm td {
    padding: 0.5rem;
    vertical-align: middle;
}
</style>