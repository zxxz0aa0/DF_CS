<template>
    <AdminLayout :user="$page.props.auth.user">
        <template #header>
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">駕駛管理</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><Link :href="route('admin.dashboard')">儀表板</Link></li>
                        <li class="breadcrumb-item active">駕駛管理</li>
                    </ol>
                </div>
            </div>
        </template>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header" style="background-color:#B3D9D9;">
                        <h3 class="card-title">駕駛列表</h3>
                        <div class="card-tools">
                            <Link :href="route('admin.drivers.create')" class="btn btn-primary btn-sm">
                                <i class="bi bi-plus"></i> 新增駕駛
                            </Link>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- 搜尋與篩選 -->
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <div class="input-group">
                                    <input
                                        v-model="searchForm.search"
                                        type="text"
                                        class="form-control form-control-sm"
                                        placeholder="搜尋姓名、身分證字號..."
                                        @keyup.enter="search"
                                    >
                                    <div class="input-group-append">
                                        <button @click="search" class="btn btn-outline-secondary btn-sm" type="button">
                                            <i class="bi bi-search"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <select v-model="searchForm.status" @change="search" class="form-control form-control-sm">
                                    <option value="">全部狀態</option>
                                    <option value="open">在籍中</option>
                                    <option value="close">已退籍</option>
                                    <option value="bad_debt">呆帳</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select v-model="searchForm.company_category_id" @change="search" class="form-control form-control-sm">
                                    <option value="">全部公司</option>
                                    <option v-for="category in companyCategories" :key="category.id" :value="category.id">
                                        {{ category.name }}
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <div class="form-check">
                                    <input
                                        v-model="searchForm.license_expiring"
                                        @change="search"
                                        class="form-check-input"
                                        type="checkbox"
                                        id="licenseExpiring"
                                    >
                                    <label class="form-check-label" for="licenseExpiring">
                                        證照即將到期
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <button @click="clearFilters" class="btn btn-outline-secondary btn-sm me-2" v-if="hasFilters">
                                    <i class="bi bi-x"></i> 清除篩選
                                </button>
                                <button @click="exportDrivers" class="btn btn-success btn-sm">
                                    <i class="bi bi-download"></i> 匯出
                                </button>
                            </div>
                        </div>

                        <!-- 駕駛表格 -->
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>公司類別</th>
                                        <th>姓名</th>
                                        <th>身分證字號</th>
                                        <th>生日</th>
                                        <th>年齡</th>
                                        <th>通訊地址</th>
                                        <th>聯絡電話1</th>
                                        <th>駕照到期</th>
                                        <th>執登到期</th>
                                        <th>狀態</th>
                                        <th>操作</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="driver in drivers.data" :key="driver.id">
                                        <td>{{ driver.company_category?.name || '-' }}</td>
                                        <td>{{ driver.name }}</td>
                                        <td>{{ driver.id_number }}</td>
                                        <td>{{ formatBthDate(driver.birthday) }}</td>
                                        <td :class="Number(calculateAge(driver.birthday)) >= 69 ? 'text-danger' : ''">
                                            {{ calculateAge(driver.birthday) }}
                                        </td>
                                        <td>{{ driver.contact_address }}</td>
                                        <td>{{ driver.mobile_phone1 || driver.home_phone || '-' }}</td>
                                        <td>
                                            <span v-if="driver.license_expire_date"
                                                  :class="getLicenseExpireClass(driver.license_days_remaining)">
                                                {{ formatDate(driver.license_expire_date) }}
                                                <small v-if="driver.license_days_remaining !== null">
                                                    ({{ driver.license_days_remaining }}天)
                                                </small>
                                            </span>
                                            <span v-else>-</span>
                                        </td>
                                        <td>
                                            <span v-if="driver.professional_license_expire_date"
                                                  :class="getLicenseExpireClass(driver.professional_license_days_remaining)">
                                                {{ formatDate(driver.professional_license_expire_date) }}
                                                <small v-if="driver.professional_license_days_remaining !== null">
                                                    ({{ driver.professional_license_days_remaining }}天)
                                                </small>
                                            </span>
                                            <span v-else>-</span>
                                        </td>
                                        <td>
                                            <span :class="'badge ' + getStatusBadgeClass(driver.status)">
                                                {{ getStatusText(driver.status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <Link :href="route('admin.drivers.show', driver.id)"
                                                      class="btn btn-info btn-sm">
                                                    <i class="bi bi-eye"></i>
                                                </Link>
                                                <Link :href="route('admin.drivers.edit', driver.id)"
                                                      class="btn btn-warning btn-sm">
                                                    <i class="bi bi-pencil"></i>
                                                </Link>
                                                <button
                                                    @click="confirmToggleStatus(driver)"
                                                    class="btn btn-sm"
                                                    :class="driver.status === 'open' ? 'btn-outline-secondary' : 'btn-outline-success'"
                                                    :title="driver.status === 'open' ? '退籍' : '復籍'"
                                                >
                                                    <i :class="driver.status === 'open' ? 'bi bi-person-dash' : 'bi bi-person-check'"></i>
                                                </button>
                                                <button @click="confirmDelete(driver)"
                                                        class="btn btn-danger btn-sm">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- 無資料提示 -->
                        <div v-if="drivers.data.length === 0" class="text-center py-4">
                            <i class="bi bi-inbox" style="font-size: 3rem; color: #6c757d;"></i>
                            <p class="text-muted mt-2">目前沒有駕駛資料</p>
                        </div>

                        <!-- 分頁 -->
                        <div v-if="drivers.last_page > 1" class="d-flex justify-content-center mt-3">
                            <nav>
                                <ul class="pagination">
                                    <li class="page-item" :class="{ disabled: drivers.current_page === 1 }">
                                        <Link class="page-link" :href="drivers.prev_page_url" :preserve-state="true">
                                            上一頁
                                        </Link>
                                    </li>
                                    <li v-for="page in paginationPages"
                                        :key="page"
                                        class="page-item"
                                        :class="{ active: page === drivers.current_page }">
                                        <Link class="page-link"
                                              :href="getPageUrl(page)"
                                              :preserve-state="true">
                                            {{ page }}
                                        </Link>
                                    </li>
                                    <li class="page-item" :class="{ disabled: drivers.current_page === drivers.last_page }">
                                        <Link class="page-link" :href="drivers.next_page_url" :preserve-state="true">
                                            下一頁
                                        </Link>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 刪除確認 Modal -->
        <div class="modal fade" id="deleteModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">確認刪除</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>確定要刪除駕駛 <strong>{{ driverToDelete?.name }}</strong> 嗎？</p>
                        <p class="text-muted small">此操作無法復原。</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                        <button type="button" class="btn btn-danger" @click="deleteDriver">確認刪除</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- 狀態切換確認 Modal -->
        <div class="modal fade" id="statusModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            {{ driverToToggle?.status === 'open' ? '確認退籍' : '確認復籍' }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>
                            確定要將駕駛 <strong>{{ driverToToggle?.name }}</strong>
                            {{ driverToToggle?.status === 'open' ? '設為退籍狀態' : '恢復為在籍狀態' }}嗎？
                        </p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                        <button
                            type="button"
                            class="btn"
                            :class="driverToToggle?.status === 'open' ? 'btn-warning' : 'btn-success'"
                            @click="toggleDriverStatus"
                        >
                            {{ driverToToggle?.status === 'open' ? '確認退籍' : '確認復籍' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
    drivers: Object,
    companyCategories: Array,
    filters: Object
})

const searchForm = ref({
    search: props.filters.search || '',
    status: props.filters.status || '',
    company_category_id: props.filters.company_category_id || '',
    license_expiring: props.filters.license_expiring || false
})

const driverToDelete = ref(null)
const driverToToggle = ref(null)
let deleteModal = null
let statusModal = null

const hasFilters = computed(() => {
    return searchForm.value.search ||
           searchForm.value.status ||
           searchForm.value.company_category_id ||
           searchForm.value.license_expiring
})

const paginationPages = computed(() => {
    const pages = []
    const current = props.drivers.current_page
    const last = props.drivers.last_page

    let start = Math.max(1, current - 2)
    let end = Math.min(last, current + 2)

    if (end - start < 4) {
        if (start === 1) {
            end = Math.min(last, start + 4)
        } else {
            start = Math.max(1, end - 4)
        }
    }

    for (let i = start; i <= end; i++) {
        pages.push(i)
    }

    return pages
})

// 只送出有效篩選條件
const buildSearchParams = () => {
    const params = {}
    Object.entries(searchForm.value).forEach(([key, value]) => {
        if (key === 'license_expiring') {
            if (value) {
                params[key] = 1
            }
            return
        }

        if (value !== '' && value !== null && value !== undefined) {
            params[key] = value
        }
    })

    return params
}

// 送出篩選條件查詢
const search = () => {
    router.get(route('admin.drivers.index'), buildSearchParams(), {
        preserveState: true,
        replace: true
    })
}

// 清除篩選並重新查詢
const clearFilters = () => {
    searchForm.value = {
        search: '',
        status: '',
        company_category_id: '',
        license_expiring: false
    }
    search()
}

// 產生含篩選條件的分頁連結
const getPageUrl = (page) => {
    const url = new URL(props.drivers.path, window.location.origin)
    url.searchParams.set('page', page)

    const params = buildSearchParams()
    Object.entries(params).forEach(([key, value]) => {
        url.searchParams.set(key, value)
    })

    return url.toString()
}

const formatDate = (dateString) => {
    if (!dateString) return '-'
    return new Date(dateString).toLocaleDateString('zh-TW')
}

const formatBthDate = (date) => {
  if (!date) return '-'
  const d = new Date(date)
  if (isNaN(d)) return '-'
  const rocYear = d.getFullYear() - 1911
  const month = String(d.getMonth() + 1).padStart(2, '0')
  const day = String(d.getDate()).padStart(2, '0')
  return `${rocYear}/${month}/${day}`
}

const calculateAge = (birthday) => {
  if (!birthday) return '-'
  const diff = Date.now() - new Date(birthday).getTime()
  const ageDate = new Date(diff)
  return Math.abs(ageDate.getUTCFullYear() - 1970)
}

const getLicenseExpireClass = (daysRemaining) => {
    if (daysRemaining === null) return ''
    if (daysRemaining < 0) return 'text-danger fw-bold'
    if (daysRemaining <= 30) return 'text-warning fw-bold'
    return 'text-success'
}

// 取得狀態顯示文字
const getStatusText = (status) => {
    const statusMap = {
        'open': '在籍中',
        'close': '已退籍',
        'bad_debt': '呆帳'
    }
    return statusMap[status] || '未知'
}

// 取得狀態 badge 的 CSS class
const getStatusBadgeClass = (status) => {
    const classMap = {
        'open': 'bg-success',
        'close': 'bg-secondary',
        'bad_debt': 'bg-danger'
    }
    return classMap[status] || 'bg-secondary'
}

const confirmDelete = (driver) => {
    driverToDelete.value = driver
    deleteModal.show()
}

const deleteDriver = () => {
    if (driverToDelete.value) {
        router.delete(route('admin.drivers.destroy', driverToDelete.value.id), {
            onSuccess: () => {
                deleteModal.hide()
                driverToDelete.value = null
            }
        })
    }
}

const confirmToggleStatus = (driver) => {
    driverToToggle.value = driver
    statusModal.show()
}

const toggleDriverStatus = () => {
    if (driverToToggle.value) {
        const newStatus = driverToToggle.value.status === 'open' ? 'close' : 'open'

        router.put(route('admin.drivers.toggle-status', driverToToggle.value.id), {
            status: newStatus
        }, {
            onSuccess: () => {
                statusModal.hide()
                driverToToggle.value = null
            },
            onError: (errors) => {
                console.error('狀態切換失敗:', errors)
            }
        })
    }
}

// 依目前篩選條件匯出
const exportDrivers = () => {
    window.open(route('admin.drivers.export', buildSearchParams()))
}

onMounted(() => {
    setTimeout(() => {
        const deleteModalElement = document.getElementById('deleteModal')
        if (deleteModalElement && window.bootstrap) {
            deleteModal = new window.bootstrap.Modal(deleteModalElement)
        }

        const statusModalElement = document.getElementById('statusModal')
        if (statusModalElement && window.bootstrap) {
            statusModal = new window.bootstrap.Modal(statusModalElement)
        }
    }, 100)
})
</script>
