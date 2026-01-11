<template>
    <div class="print-preview">
        <!-- 報表標題 -->
        <div class="report-header text-center mb-4">
            <h2 class="report-title">每日交易明細報表</h2>
        </div>

        <!-- 查詢條件 -->
        <div style="font-size: 12px;">
            <div>
                <span>列印日期：{{ today }}</span>
            </div>
            <div>
                <span>日期範圍：{{ filters.start_date }} ~ {{ filters.end_date }}</span>
            </div>
        </div>

        <!-- 資料表格 -->
        <table class="report-table">
            <thead>
                <tr>
                    <th style="width: 17%;">交易日期</th>
                    <th style="width: 10%;">公司類別</th>
                    <th style="width: 10%;">車隊編號</th>
                    <th style="width: 11%;">車號</th>
                    <th style="width: 8%;">姓名</th>
                    <th style="width: 10%;">會計科目</th>
                    <th style="width: 10%;">借方金額</th>
                    <th style="width: 10%;">貸方金額</th>
                    <th>備註</th>
                </tr>
            </thead>
            <tbody>
                <tr v-if="records.length === 0">
                    <td colspan="9" class="text-center">查無資料</td>
                </tr>
                <tr v-for="record in records" :key="record.id">
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
            <tfoot>
                <tr class="total-row">
                    <td colspan="6" class="text-end"><strong>合計：</strong></td>
                    <td class="text-end"><strong>{{ formatAmount(total_debit) }}</strong></td>
                    <td class="text-end"><strong>{{ formatAmount(total_credit) }}</strong></td>
                    <td></td>
                </tr>
            </tfoot>
        </table>

        <!-- 列印按鈕（列印時隱藏） -->
        <div class="print-actions no-print mt-4 text-center">
            <button @click="print" class="btn btn-primary btn-lg">
                <i class="bi bi-printer"></i> 列印報表
            </button>
            <button @click="closePage" class="btn btn-secondary btn-lg ms-2">
                <i class="bi bi-x-circle"></i> 關閉
            </button>
        </div>
    </div>
</template>

<script setup>
// 接收後端傳來的資料
const props = defineProps({
    records: Array,
    total_debit: String,
    total_credit: String,
    filters: Object,
    today: String,
});

// 格式化金額 - 只顯示整數
const formatAmount = (amount) => {
    if (!amount || amount === '0.00' || parseFloat(amount) === 0) return '0';
    return Math.round(parseFloat(amount)).toLocaleString('zh-TW', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    });
};

// 列印功能
const print = () => {
    window.print();
};

// 關閉頁面
const closePage = () => {
    window.close();
};
</script>

<style scoped>
/* 頁面基礎樣式 */
.print-preview {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
    font-family: '微軟正黑體', 'Microsoft JhengHei', sans-serif;
}

/* 報表標題區 */
.report-header {
    margin-bottom: 30px;
}

.report-title {
    font-size: 24px;
    font-weight: bold;
    margin-bottom: 10px;
}

.report-date {
    font-size: 14px;
    color: #666;
}

/* 篩選條件資訊 */
.filter-info {
    background-color: #f8f9fa;
    padding: 15px;
    border-radius: 5px;
    font-size: 14px;
}

.filter-row {
    margin-bottom: 5px;
}

.filter-row:last-child {
    margin-bottom: 0;
}

/* 報表表格 */
.report-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 12px;
}

.report-table th,
.report-table td {
    border: 1px solid #000;
    padding: 8px;
}

.report-table thead th {
    background-color: #e9ecef;
    font-weight: bold;
    text-align: center;
}

.report-table tbody tr:hover {
    background-color: #f8f9fa;
}

.report-table .total-row {
    background-color: #e9ecef;
    font-weight: bold;
}

.text-center {
    text-align: center;
}

.text-end {
    text-align: right;
}

/* 列印按鈕區 */
.print-actions {
    margin-top: 30px;
}

/* 列印樣式 */
@media print {
    /* 隱藏列印按鈕 */
    .no-print {
        display: none !important;
    }

    /* 調整頁面邊距 */
    .print-preview {
        padding: 0;
        max-width: 100%;
    }

    /* 確保表格不會跨頁斷行 */
    .report-table {
        page-break-inside: auto;
    }

    .report-table tr {
        page-break-inside: avoid;
        page-break-after: auto;
    }

    .report-table thead {
        display: table-header-group;
    }

    .report-table tfoot {
        display: table-footer-group;
    }

    /* 移除背景色以節省墨水 */
    .filter-info {
        background-color: white;
        border: 1px solid #000;
    }

    .report-table thead th,
    .report-table .total-row {
        background-color: #f0f0f0;
    }
}
</style>