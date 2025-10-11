<template>
    <AdminLayout :user="$page.props.auth.user">
        <div class="content-wrapper">
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1 class="m-0">廠商管理</h1>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item">
                                    <Link :href="route('admin.dashboard')">管理後台</Link>
                                </li>
                                <li class="breadcrumb-item active">廠商管理</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <section class="content">
                <div class="container-fluid">
                    <div class="card">
                        <div class="card-header">
                            <div class="row">
                                <div class="col-md-6">
                                    <h3 class="card-title">廠商列表</h3>
                                </div>
                                <div class="col-md-6 text-right">
                                    <Link
                                        v-if="$page.props.auth.permissions.includes('create vendors')"
                                        :href="route('admin.vendors.create')"
                                        class="btn btn-primary"
                                    >
                                        <i class="bi bi-plus"></i>
                                        新增廠商
                                    </Link>
                                </div>
                            </div>
                        </div>

                        <div class="card-body">
                            <!-- 搜尋區域 -->
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="input-group">
                                        <input
                                            v-model="form.search"
                                            type="text"
                                            class="form-control"
                                            placeholder="搜尋廠商名稱..."
                                            @keyup.enter="search"
                                        />
                                        <button class="btn btn-outline-secondary" @click="search">
                                            <i class="bi bi-search"></i>
                                        </button>
                                        <button
                                            v-if="form.search"
                                            class="btn btn-outline-secondary"
                                            @click="clearSearch"
                                        >
                                            <i class="bi bi-x"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- 資料表格 -->
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>編號</th>
                                            <th>廠商名稱</th>
                                            <th>電話</th>
                                            <th>地址</th>
                                            <th>服務內容</th>
                                            <th>操作</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-if="vendors.data.length === 0">
                                            <td colspan="7" class="text-center text-muted">
                                                暫無廠商資料
                                            </td>
                                        </tr>
                                        <tr v-for="vendor in vendors.data" :key="vendor.id">
                                            <td>{{ vendor.id }}</td>
                                            <td>{{ vendor.name || '-' }}</td>
                                            <td>{{ vendor.phone || '-' }}</td>
                                            <td>{{ vendor.address || '-' }}</td>
                                            <td>{{ vendor.service_content || '-' }}</td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <Link
                                                        v-if="$page.props.auth.permissions.includes('edit vendors')"
                                                        :href="route('admin.vendors.edit', vendor.id)"
                                                        class="btn btn-sm btn-outline-primary"
                                                    >
                                                        <i class="bi bi-pencil"></i>
                                                    </Link>
                                                    <button
                                                        v-if="$page.props.auth.permissions.includes('delete vendors')"
                                                        @click="deleteVendor(vendor)"
                                                        class="btn btn-sm btn-outline-danger"
                                                    >
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- 分頁 -->
                            <div v-if="vendors.links.length > 3" class="d-flex justify-content-center">
                                <nav>
                                    <ul class="pagination">
                                        <li
                                            v-for="link in vendors.links"
                                            :key="link.label"
                                            :class="['page-item', {
                                                active: link.active,
                                                disabled: !link.url
                                            }]"
                                        >
                                            <Link
                                                v-if="link.url"
                                                :href="link.url"
                                                class="page-link"
                                                v-html="link.label"
                                            />
                                            <span v-else class="page-link" v-html="link.label" />
                                        </li>
                                    </ul>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
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
                        <p>確定要刪除廠商「{{ deletingVendor?.name || '此廠商' }}」嗎？</p>
                        <p class="text-danger">此操作無法復原！</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            取消
                        </button>
                        <button type="button" class="btn btn-danger" @click="confirmDelete">
                            確認刪除
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
    vendors: Object,
    filters: Object,
})

const form = reactive({
    search: props.filters.search || '',
})

const deletingVendor = ref(null)
let deleteModal = null

const formatDate = (dateString) => {
    if (!dateString) return '-'
    return new Date(dateString).toLocaleDateString('zh-TW', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
    })
}

const search = () => {
    router.get(route('admin.vendors.index'), {
        search: form.search,
    }, {
        preserveState: true,
        replace: true,
    })
}

const clearSearch = () => {
    form.search = ''
    search()
}

const deleteVendor = (vendor) => {
    deletingVendor.value = vendor
    deleteModal.show()
}

const confirmDelete = () => {
    if (deletingVendor.value) {
        router.delete(route('admin.vendors.destroy', deletingVendor.value.id), {
            onSuccess: () => {
                deleteModal.hide()
                deletingVendor.value = null
            }
        })
    }
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
