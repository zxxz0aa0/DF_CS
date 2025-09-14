<template>
  <AdminLayout :user="$page.props.auth.user">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        車輛牌照詳細資訊
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <!-- 基本資訊 -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
          <div class="p-6">
            <div class="flex justify-between items-start mb-6">
              <h3 class="text-lg font-medium text-gray-900">基本資訊</h3>
              <div class="flex space-x-2">
                <StatusBadge :status="license.status" />
                <Link
                  v-if="can.edit && license.id"
                  :href="route('admin.vehicle-licenses.edit', { vehicle_license: license.id })"
                  class="bg-green-500 hover:bg-green-700 text-white font-bold py-1 px-3 rounded text-sm"
                >
                  編輯
                </Link>
                <button
                  v-if="can.edit && license.id && license.status === 'active'"
                  @click="revokeLicense"
                  class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-3 rounded text-sm"
                >
                  繳銷
                </button>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700">公司</label>
                <p class="mt-1 text-sm text-gray-900">{{ license.company?.name || '未指定' }}</p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700">狀態</label>
                <p class="mt-1 text-sm text-gray-900">{{ license.status_label }}</p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700">縣市</label>
                <p class="mt-1 text-sm text-gray-900">{{ license.county || '未指定' }}</p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700">車牌號碼</label>
                <p class="mt-1 text-sm text-gray-900">{{ license.license_number || '未指定' }}</p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700">使用者名稱</label>
                <p class="mt-1 text-sm text-gray-900">{{ license.holder_name || '未指定' }}</p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700">車牌日期</label>
                <p class="mt-1 text-sm text-gray-900">{{ license.license_date || '未指定' }}</p>
              </div>

              <div v-if="license.replacement_date">
                <label class="block text-sm font-medium text-gray-700">替補日期</label>
                <p class="mt-1 text-sm text-gray-900">{{ license.replacement_date }}</p>
              </div>

              <div v-if="license.revocation_date">
                <label class="block text-sm font-medium text-gray-700">繳銷日期</label>
                <p class="mt-1 text-sm text-gray-900">{{ license.revocation_date }}</p>
              </div>

              <div v-if="license.notes" class="col-span-2">
                <label class="block text-sm font-medium text-gray-700">備註</label>
                <p class="mt-1 text-sm text-gray-900 whitespace-pre-wrap">{{ license.notes }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- 前車牌資訊 -->
        <div v-if="license.previous_license_info" class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
          <div class="p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">前車牌資訊</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700">前車牌號碼</label>
                <p class="mt-1 text-sm text-gray-900">{{ license.previous_license_info.number }}</p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700">前使用者名稱</label>
                <p class="mt-1 text-sm text-gray-900">{{ license.previous_license_info.holder || '未指定' }}</p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700">前車牌日期</label>
                <p class="mt-1 text-sm text-gray-900">{{ license.previous_license_info.date || '未指定' }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- 審計記錄 -->
        <div v-if="can.viewAuditLogs && auditLogs.length > 0" class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
          <div class="p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">操作記錄</h3>
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      操作類型
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      操作者
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      操作時間
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      IP位址
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="log in auditLogs" :key="log.id">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      {{ log.action_label }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      {{ log.user?.name || '系統' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      {{ log.created_at }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      {{ log.ip_address || '-' }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- 系統資訊 -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
          <div class="p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">系統資訊</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700">建立者</label>
                <p class="mt-1 text-sm text-gray-900">{{ license.creator?.name || '未知' }}</p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700">更新者</label>
                <p class="mt-1 text-sm text-gray-900">{{ license.updater?.name || '未知' }}</p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700">建立時間</label>
                <p class="mt-1 text-sm text-gray-900">{{ license.created_at }}</p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700">更新時間</label>
                <p class="mt-1 text-sm text-gray-900">{{ license.updated_at }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- 返回按鈕 -->
        <div class="flex justify-start">
          <Link
            :href="route('admin.vehicle-licenses.index')"
            class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded"
          >
            返回列表
          </Link>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import StatusBadge from './Components/StatusBadge.vue'

const props = defineProps({
  license: Object,
  auditLogs: Array,
  can: Object
})

// 繳銷牌照
const revokeLicense = () => {
  if (!props.license.id) {
    console.error('License ID is missing')
    return
  }
  
  if (confirm(`確定要繳銷牌照 "${props.license.license_number || '未知'}" 嗎？此操作無法復原。`)) {
    router.post(route('admin.vehicle-licenses.revoke', { vehicle_license: props.license.id }))
  }
}
</script>
