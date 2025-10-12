<template>
  <AdminLayout :user="$page.props.auth.user">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        車輛牌照管理
      </h2>
    </template>

    <!-- 統計卡片區域 -->
    <div class="container-fluid mb-1">
      <div class="row">
        <!-- 總數量統計 -->
        <div class="col-lg-4 col-md-6 col-12 mb-3">
          <div class="card bg-primary text-white">
            <div class="card-body d-flex align-items-center">
              <div class="flex-grow-1">
                <p class="card-text mb-0 h4">牌照總數量</p>
              </div>
            <div class="flex-grow-1">
                <p class="card-text text-right mb-0 pe-24 h3">{{ stats?.total_licenses || 0 }}</p>
              </div>
              <div class="ms-3">
                <i class="bi bi-clipboard-data fs-1"></i>
              </div>
            </div>
          </div>
        </div>

        <!-- 使用中統計 -->
        <div class="col-lg-4 col-md-6 col-12 mb-3">
          <div class="card bg-success text-white">
            <div class="card-body d-flex align-items-center">
              <div class="flex-grow-1">
                <p class="card-text mb-0 h4">使用中數量</p>
              </div>
              <div class="flex-grow-1">
                <p class="card-text text-right mb-0 pe-24 h3">{{ stats?.active_licenses || 0 }}</p>
              </div>
              <div class="ms-3">
                <i class="bi bi-check-circle fs-1"></i>
              </div>
            </div>
          </div>
        </div>

        <!-- 已繳銷統計 -->
        <div class="col-lg-4 col-md-6 col-12 mb-3">
          <div class="card bg-danger text-white">
            <div class="card-body d-flex align-items-center">
              <div class="flex-grow-1">
                <p class="card-text mb-0 h4">繳銷數量</p>
              </div>
              <div class="flex-grow-1">
                <p class="card-text text-right mb-0 pe-24 h3">{{ stats?.revoked_licenses || 0 }}</p>
              </div>
              <div class="ms-3">
                <i class="bi bi-x-circle fs-1"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="">
      <div class="max-w-12xl mx-auto sm:px-6 lg:px-1">
        <!-- 搜尋和篩選區域 -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-2">
          <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
              <!-- 搜尋框 -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">搜尋</label>
                <input
                  v-model="searchForm.search"
                  type="text"
                  placeholder="車牌號碼、使用者名稱、縣市"
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                  @input="search"
                />
              </div>

              <!-- 公司篩選 -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">公司</label>
                <select
                  v-model="searchForm.company_id"
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                  @change="search"
                >
                  <option value="">全部公司</option>
                  <option v-for="company in companies" :key="company.id" :value="company.id">
                    {{ company.name }}
                  </option>
                </select>
              </div>

              <!-- 狀態篩選 -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">狀態</label>
                <select
                  v-model="searchForm.status"
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                  @change="search"
                >
                  <option value="">全部狀態</option>
                  <option value="active">使用中</option>
                  <option value="revoked">已繳銷</option>
                  <option value="transferred">已轉移</option>
                  <option value="replaced">已替補</option>
                </select>
              </div>

              <!-- 操作按鈕 -->
              <div class="flex items-end">
                <Link
                  v-if="can.create"
                  :href="route('admin.vehicle-licenses.create')"
                  class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
                >
                  新增牌照
                </Link>
              </div>
            </div>
          </div>
        </div>

        <!-- 牌照列表 -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="">
            <!-- 表格顯示 -->
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-blue-200">
                  <tr>
                    <th class="px-6 py-3 text-left text-base font-medium text-gray-500 uppercase tracking-wider">縣市</th>
                    <th class="px-6 py-3 text-left text-base font-medium text-gray-500 uppercase tracking-wider">公司/合作社</th>
                    <th class="px-6 py-3 text-left text-base font-medium text-gray-500 uppercase tracking-wider">車牌號碼</th>
                    <th class="px-6 py-3 text-left text-base font-medium text-gray-500 uppercase tracking-wider">使用者</th>
                    <th class="px-6 py-3 text-left text-base font-medium text-gray-500 uppercase tracking-wider">牌照年份</th>
                    <th class="px-6 py-3 text-left text-base font-medium text-gray-500 uppercase tracking-wider">狀態</th>
                    <th class="px-6 py-3 text-left text-base font-medium text-gray-500 uppercase tracking-wider">日期資訊</th>
                    <th class="px-6 py-3 text-left text-base font-medium text-gray-500 uppercase tracking-wider">操作</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="license in (licenses.data || [])" :key="license.id" class="hover:bg-gray-50">
                    <!-- 縣市 -->
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div>
                        <div class="text-base text-gray-900">{{ license.county || '未指定' }}</div>
                      </div>
                    </td>
                    <!-- 公司資訊 -->
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div>
                        <div class="text-base text-gray-900">{{ license.company?.name || '未指定' }}</div>
                      </div>
                    </td>
                    <!-- 牌照資訊 -->
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div>
                        <div class="text-base font-medium text-gray-900">
                          {{ license.license_number || license.previous_license_number || '無號碼' }}
                        </div>
                        <div v-if="license.status === 'revoked' && license.previous_license_number" class="text-xs text-red-500">
                          (前車號)
                        </div>
                      </div>
                    </td>

                    <!-- 使用者 -->
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div>
                        <div class="text-base text-gray-900">
                          {{ license.holder_name || license.previous_holder_name || '無使用者' }}
                        </div>
                        <div v-if="license.status === 'revoked' && license.previous_license_number" class="text-xs text-red-500">
                          (前使用者)
                        </div>
                      </div>
                    </td>

                    <!-- 車牌年月份 -->
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div>
                        <div class="text-base font-medium text-gray-900">
                          {{ license.license_date || license.previous_license_date || '無號碼' }}
                        </div>
                        <div v-if="license.status === 'revoked' && license.previous_license_date" class="text-xs text-red-500">
                          (前車年份)
                        </div>
                      </div>
                    </td>

                    <!-- 狀態 -->
                    <td class="px-6 py-4 whitespace-nowrap">
                      <StatusBadge :status="license.status" />
                    </td>

                    <!-- 日期資訊 -->
                    <td class="px-6 py-4 whitespace-nowrap text-base text-gray-500">
                        <div v-if="license.status === 'active'" class="mb-1">
                          <span class="font-medium">替補日期：</span>{{ license.replacement_date }}
                        </div>
                         <div v-if="license.status === 'revoked'" class="text-red-500">
                          <span >繳銷日期：</span>{{ license.revocation_date }}
                         </div>
                    </td>

                    <!-- 操作按鈕 -->
                    <td class="px-6 py-4 whitespace-nowrap text-right text-base font-medium">
                      <div class="flex justify-start space-x-2">
                        <Link
                          v-if="license.id"
                          :href="route('admin.vehicle-licenses.show', { vehicle_license: license.id })"
                          class="text-blue-600 hover:text-blue-800"
                        >
                          檢視
                        </Link>
                        <Link
                          v-if="can.edit && license.id"
                          :href="route('admin.vehicle-licenses.edit', { vehicle_license: license.id })"
                          class="text-green-600 hover:text-green-800"
                        >
                          編輯
                        </Link>
                        <button
                          v-if="can.edit && license.id && license.status === 'revoked'"
                          @click="replaceLicense(license)"
                          class="text-yellow-600 hover:text-yellow-800"
                        >
                          替補
                        </button>
                        <button
                          v-if="can.edit && license.id && license.status === 'active'"
                          @click="revokeLicense(license)"
                          class="text-red-600 hover:text-red-800"
                        >
                          繳銷
                        </button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- 無資料顯示 -->
            <div v-if="licenses.data.length === 0" class="text-center py-8 text-gray-500">
              目前沒有牌照資料
            </div>

            <!-- 分頁 -->
            <div v-if="licenses && licenses.links && licenses.links.length" class="mt-6">
              <nav class="flex justify-center">
                <div class="flex space-x-1">
                  <Link
                    v-for="(link, i) in licenses.links"
                    :key="i"
                    :href="link && link.url"
                    :class="{
                      'bg-blue-500 text-white': link && link.active,
                      'bg-gray-200 text-gray-700 hover:bg-gray-300': link && !link.active && link.url,
                      'bg-gray-100 text-gray-400 cursor-not-allowed': !link || !link.url
                    }"
                    class="px-3 py-2 text-sm rounded"
                    v-html="link && link.label"
                  />
                </div>
              </nav>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- 替補模態視窗 -->
    <div class="modal fade" id="replaceModal" tabindex="-1" role="dialog">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">替補牌照</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div v-if="replaceForm.currentLicense">
              <p class="mb-4">原牌照：<strong>{{ replaceForm.currentLicense.license_number || '無號碼' }}</strong></p>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- 車牌號碼 -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">新車牌號碼 <span class="text-red-500">*</span></label>
                  <input
                    v-model="replaceForm.license_number"
                    type="text"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    required
                  />
                </div>

                <!-- 使用者名稱 -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">新使用者名稱</label>
                  <input
                    v-model="replaceForm.holder_name"
                    type="text"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                  />
                </div>

                <!-- 車牌年份 -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">新車牌年份</label>
                  <input
                    v-model="replaceForm.license_year"
                    type="number"
                    min="1900"
                    :max="new Date().getFullYear() + 10"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                  />
                </div>

                <!-- 車牌月份 -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">新車牌月份</label>
                  <select
                    v-model="replaceForm.license_month"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                  >
                    <option value="">請選擇月份</option>
                    <option v-for="month in 12" :key="month" :value="month">
                      {{ month }} 月
                    </option>
                  </select>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
            <button type="button" class="btn btn-warning" @click="submitReplace">確認替補</button>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { reactive } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import StatusBadge from './Components/StatusBadge.vue'

const props = defineProps({
  licenses: Object,
  companies: Array,
  filters: Object,
  can: Object,
  stats: Object
})

// 搜尋表單
const searchForm = reactive({
  search: props.filters.search || '',
  company_id: props.filters.company_id || '',
  status: props.filters.status || '',
  county: props.filters.county || ''
})

// 搜尋功能
const search = () => {
  router.get(route('admin.vehicle-licenses.index'), searchForm, {
    preserveState: true,
    preserveScroll: true
  })
}

// 替補表單
const replaceForm = reactive({
  currentLicense: null,
  license_number: '',
  holder_name: '',
  license_year: '',
  license_month: ''
})

// 替補牌照
const replaceLicense = (license) => {
  if (!license.id) {
    console.error('License ID is missing')
    return
  }

  // 設定當前牌照資料
  replaceForm.currentLicense = license
  replaceForm.license_number = ''
  replaceForm.holder_name = ''
  replaceForm.license_year = ''
  replaceForm.license_month = ''

  // 顯示模態視窗
  if (window.bootstrap && window.bootstrap.Modal) {
    const modal = new window.bootstrap.Modal(document.getElementById('replaceModal'))
    modal.show()
  }
}

// 提交替補
const submitReplace = () => {
  if (!replaceForm.currentLicense.id) {
    console.error('License ID is missing')
    return
  }

  if (!replaceForm.license_number.trim()) {
    alert('請輸入新車牌號碼')
    return
  }

  const replaceData = {
    license_number: replaceForm.license_number,
    holder_name: replaceForm.holder_name,
    license_year: replaceForm.license_year,
    license_month: replaceForm.license_month
  }

  router.post(route('admin.vehicle-licenses.replace', { vehicle_license: replaceForm.currentLicense.id }), replaceData, {
    preserveScroll: true,
    onSuccess: () => {
      // 關閉模態視窗
      if (window.bootstrap && window.bootstrap.Modal) {
        const modal = window.bootstrap.Modal.getInstance(document.getElementById('replaceModal'))
        if (modal) {
          modal.hide()
        }
      }
      // 重置表單
      replaceForm.currentLicense = null
      replaceForm.license_number = ''
      replaceForm.holder_name = ''
      replaceForm.license_year = ''
      replaceForm.license_month = ''
    }
  })
}

// 繳銷牌照
const revokeLicense = (license) => {
  if (!license.id) {
    console.error('License ID is missing')
    return
  }

  if (confirm(`確定要繳銷牌照 "${license.license_number || '未知'}" 嗎？此操作無法復原。`)) {
    router.post(route('admin.vehicle-licenses.revoke', { vehicle_license: license.id }), {}, {
      preserveScroll: true
    })
  }
}
</script>
