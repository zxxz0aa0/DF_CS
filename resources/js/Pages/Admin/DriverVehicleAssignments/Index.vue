<template>
    <AdminLayout :user="$page.props.auth.user">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                駕駛車輛綁定管理
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-12xl mx-auto sm:px-2 lg:px-0">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <!-- 功能按鈕區 -->
                        <div class="flex justify-between items-center mb-6">
                            <div class="flex space-x-2">
                                <Link
                                    v-if="can('assignment.create')"
                                    :href="route('admin.driver-vehicle-assignments.create')"
                                    class="btn btn-primary"
                                >
                                    <i class="bi bi-plus-lg me-1"></i>
                                    新增綁定
                                </Link>
                            </div>

                            <!-- 搜尋區 -->
                            <div class="flex space-x-2">
                                <input
                                    v-model="filters.driver_search"
                                    type="text"
                                    placeholder="搜尋駕駛..."
                                    class="form-control"
                                    style="width: 150px;"
                                    @keyup.enter="search"
                                >
                                <input
                                    v-model="filters.vehicle_search"
                                    type="text"
                                    placeholder="搜尋車牌..."
                                    class="form-control"
                                    style="width: 150px;"
                                    @keyup.enter="search"
                                >
                                <button @click="search" class="btn btn-outline-secondary">
                                    <i class="bi bi-search"></i>
                                </button>
                                <button @click="clearSearch" class="btn btn-outline-secondary">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>
                        </div>

                        <!-- 統計資訊 -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle me-2"></i>
                                    目前共有 {{ assignments.total }} 筆綁定記錄
                                </div>
                            </div>
                        </div>

                        <!-- 資料表格 -->
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>駕駛姓名</th>
                                        <th>身分證字號</th>
                                        <th>車牌號碼</th>
                                        <th>車輛廠牌</th>
                                        <th>車型</th>
                                        <th>綁定時間</th>
                                        <th>備註</th>
                                        <th width="150">操作</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="assignment in assignments.data" :key="assignment.id">
                                        <td>{{ assignment.driver.name }}</td>
                                        <td>{{ assignment.driver.id_number }}</td>
                                        <td>
                                            <span class="badge bg-primary">{{ assignment.vehicle.license_number }}</span>
                                        </td>
                                        <td>{{ assignment.vehicle.brand }}</td>
                                        <td>{{ assignment.vehicle.vehicle_model }}</td>
                                        <td>{{ formatDateTime(assignment.created_at) }}</td>
                                        <td>
                                            <span v-if="assignment.notes" class="text-muted">
                                                {{ assignment.notes.length > 20 ? assignment.notes.substring(0, 20) + '...' : assignment.notes }}
                                            </span>
                                            <span v-else class="text-muted">—</span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <Link
                                                    v-if="can('assignment.edit')"
                                                    :href="route('admin.driver-vehicle-assignments.edit', assignment.id)"
                                                    class="btn btn-outline-primary"
                                                    title="編輯"
                                                >
                                                    <i class="bi bi-pencil"></i>
                                                </Link>
                                                <button
                                                    v-if="can('assignment.delete')"
                                                    @click="confirmDelete(assignment)"
                                                    class="btn btn-outline-danger"
                                                    title="解除綁定"
                                                >
                                                    <i class="bi bi-x-lg"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- 如果沒有資料 -->
                        <div v-if="assignments.data.length === 0" class="text-center py-4">
                            <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mt-2">目前沒有綁定記錄</p>
                        </div>

                        <!-- 分頁 -->
                        <nav v-if="assignments.last_page > 1" class="mt-4">
                            <ul class="pagination justify-content-center">
                                <li class="page-item" :class="{ disabled: !assignments.prev_page_url }">
                                    <Link
                                        :href="assignments.prev_page_url || '#'"
                                        class="page-link"
                                        :data="{ ...filters }"
                                        preserve-scroll
                                    >
                                        上一頁
                                    </Link>
                                </li>
                                <li
                                    v-for="page in getPageNumbers()"
                                    :key="page"
                                    class="page-item"
                                    :class="{ active: page === assignments.current_page }"
                                >
                                    <Link
                                        :href="getPageUrl(page)"
                                        class="page-link"
                                        :data="{ ...filters }"
                                        preserve-scroll
                                    >
                                        {{ page }}
                                    </Link>
                                </li>
                                <li class="page-item" :class="{ disabled: !assignments.next_page_url }">
                                    <Link
                                        :href="assignments.next_page_url || '#'"
                                        class="page-link"
                                        :data="{ ...filters }"
                                        preserve-scroll
                                    >
                                        下一頁
                                    </Link>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <!-- 刪除確認 Modal -->
        <div class="modal fade" id="deleteModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">確認解除綁定</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>確定要解除以下綁定嗎？</p>
                        <div v-if="assignmentToDelete" class="alert alert-warning">
                            <strong>駕駛：</strong>{{ assignmentToDelete.driver.name }}<br>
                            <strong>車輛：</strong>{{ assignmentToDelete.vehicle.license_number }}
                        </div>
                        <p class="text-danger">此操作無法復原！</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                        <button type="button" class="btn btn-danger" @click="deleteAssignment">確認解除</button>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
    assignments: Object,
    filters: Object,
})

const page = usePage()
const filters = reactive({
    driver_search: props.filters.driver_search || '',
    vehicle_search: props.filters.vehicle_search || '',
})

const assignmentToDelete = ref(null)
let deleteModal = null

const can = (permission) => {
    return page.props.auth.permissions.includes(permission)
}

const formatDateTime = (datetime) => {
    return new Date(datetime).toLocaleString('zh-TW')
}

const search = () => {
    router.get(route('admin.driver-vehicle-assignments.index'), filters, {
        preserveState: true,
        replace: true,
    })
}

const clearSearch = () => {
    filters.driver_search = ''
    filters.vehicle_search = ''
    search()
}

const confirmDelete = (assignment) => {
    assignmentToDelete.value = assignment
    deleteModal.show()
}

const deleteAssignment = () => {
    if (assignmentToDelete.value) {
        router.delete(route('admin.driver-vehicle-assignments.destroy', assignmentToDelete.value.id), {
            onSuccess: () => {
                deleteModal.hide()
                assignmentToDelete.value = null
            }
        })
    }
}

const getPageNumbers = () => {
    const current = props.assignments.current_page
    const last = props.assignments.last_page
    const pages = []

    for (let i = Math.max(1, current - 2); i <= Math.min(last, current + 2); i++) {
        pages.push(i)
    }

    return pages
}

const getPageUrl = (page) => {
    return route('admin.driver-vehicle-assignments.index', { ...filters, page })
}

onMounted(() => {
    setTimeout(() => {
        const modalElement = document.getElementById('deleteModal')
        if (modalElement && window.bootstrap) {
            deleteModal = new window.bootstrap.Modal(modalElement)
        }
    }, 100)
})
</script>
