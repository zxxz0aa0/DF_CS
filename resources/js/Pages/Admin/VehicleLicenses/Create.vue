<template>
  <AdminLayout :user="$page.props.auth.user">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        新增車輛牌照
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6">
            <form @submit.prevent="submit">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- 基本資訊 -->
                <div class="col-span-2">
                  <h3 class="text-lg font-medium text-gray-900 mb-4">基本資訊</h3>
                </div>

                <!-- 公司 -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    公司 <span class="text-red-500">*</span>
                  </label>
                  <select
                    v-model="form.company_id"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    :class="{ 'border-red-500': errors.company_id }"
                  >
                    <option value="">請選擇公司</option>
                    <option v-for="company in companies" :key="company.id" :value="company.id">
                      {{ company.name }}
                    </option>
                  </select>
                  <div v-if="errors.company_id" class="text-red-500 text-sm mt-1">
                    {{ errors.company_id }}
                  </div>
                </div>

                <!-- 狀態 -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    狀態 <span class="text-red-500">*</span>
                  </label>
                  <select
                    v-model="form.status"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    :class="{ 'border-red-500': errors.status }"
                  >
                    <option value="active">使用中</option>
                    <option value="revoked">已繳銷</option>
                    <option value="transferred">已轉移</option>
                  </select>
                  <div v-if="errors.status" class="text-red-500 text-sm mt-1">
                    {{ errors.status }}
                  </div>
                </div>

                <!-- 縣市 -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">縣市</label>
                  <input
                    v-model="form.county"
                    type="text"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    :class="{ 'border-red-500': errors.county }"
                  />
                  <div v-if="errors.county" class="text-red-500 text-sm mt-1">
                    {{ errors.county }}
                  </div>
                </div>

                <!-- 車牌號碼 -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">車牌號碼</label>
                  <input
                    v-model="form.license_number"
                    type="text"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    :class="{ 'border-red-500': errors.license_number }"
                  />
                  <div v-if="errors.license_number" class="text-red-500 text-sm mt-1">
                    {{ errors.license_number }}
                  </div>
                </div>

                <!-- 使用者名稱 -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">使用者名稱</label>
                  <input
                    v-model="form.holder_name"
                    type="text"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    :class="{ 'border-red-500': errors.holder_name }"
                  />
                  <div v-if="errors.holder_name" class="text-red-500 text-sm mt-1">
                    {{ errors.holder_name }}
                  </div>
                </div>

                <!-- 車牌年份 -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">車牌年份</label>
                  <input
                    v-model="form.license_year"
                    type="number"
                    min="1900"
                    :max="new Date().getFullYear() + 10"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    :class="{ 'border-red-500': errors.license_year }"
                  />
                  <div v-if="errors.license_year" class="text-red-500 text-sm mt-1">
                    {{ errors.license_year }}
                  </div>
                </div>

                <!-- 車牌月份 -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">車牌月份</label>
                  <select
                    v-model="form.license_month"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    :class="{ 'border-red-500': errors.license_month }"
                  >
                    <option value="">請選擇月份</option>
                    <option v-for="month in 12" :key="month" :value="month">
                      {{ month }} 月
                    </option>
                  </select>
                  <div v-if="errors.license_month" class="text-red-500 text-sm mt-1">
                    {{ errors.license_month }}
                  </div>
                </div>

                <!-- 前車牌資訊 -->
                <div class="col-span-2">
                  <h3 class="text-lg font-medium text-gray-900 mb-4 mt-6">前車牌資訊</h3>
                </div>

                <!-- 前車牌號碼 -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">前車牌號碼</label>
                  <input
                    v-model="form.previous_license_number"
                    type="text"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    :class="{ 'border-red-500': errors.previous_license_number }"
                  />
                  <div v-if="errors.previous_license_number" class="text-red-500 text-sm mt-1">
                    {{ errors.previous_license_number }}
                  </div>
                </div>

                <!-- 前使用者名稱 -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">前使用者名稱</label>
                  <input
                    v-model="form.previous_holder_name"
                    type="text"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    :class="{ 'border-red-500': errors.previous_holder_name }"
                  />
                  <div v-if="errors.previous_holder_name" class="text-red-500 text-sm mt-1">
                    {{ errors.previous_holder_name }}
                  </div>
                </div>

                <!-- 前車牌年份 -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">前車牌年份</label>
                  <input
                    v-model="form.previous_license_year"
                    type="number"
                    min="1900"
                    :max="new Date().getFullYear() + 10"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    :class="{ 'border-red-500': errors.previous_license_year }"
                  />
                  <div v-if="errors.previous_license_year" class="text-red-500 text-sm mt-1">
                    {{ errors.previous_license_year }}
                  </div>
                </div>

                <!-- 前車牌月份 -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">前車牌月份</label>
                  <select
                    v-model="form.previous_license_month"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    :class="{ 'border-red-500': errors.previous_license_month }"
                  >
                    <option value="">請選擇月份</option>
                    <option v-for="month in 12" :key="month" :value="month">
                      {{ month }} 月
                    </option>
                  </select>
                  <div v-if="errors.previous_license_month" class="text-red-500 text-sm mt-1">
                    {{ errors.previous_license_month }}
                  </div>
                </div>

                <!-- 其他資訊 -->
                <div class="col-span-2">
                  <h3 class="text-lg font-medium text-gray-900 mb-4 mt-6">其他資訊</h3>
                </div>

                <!-- 替補日期 -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">替補日期</label>
                  <input
                    v-model="form.replacement_date"
                    type="date"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    :class="{ 'border-red-500': errors.replacement_date }"
                  />
                  <div v-if="errors.replacement_date" class="text-red-500 text-sm mt-1">
                    {{ errors.replacement_date }}
                  </div>
                </div>

                <!-- 繳銷日期 -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">繳銷日期</label>
                  <input
                    v-model="form.revocation_date"
                    type="date"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    :class="{ 'border-red-500': errors.revocation_date }"
                  />
                  <div v-if="errors.revocation_date" class="text-red-500 text-sm mt-1">
                    {{ errors.revocation_date }}
                  </div>
                </div>

                <!-- 備註 -->
                <div class="col-span-2">
                  <label class="block text-sm font-medium text-gray-700 mb-2">備註</label>
                  <textarea
                    v-model="form.notes"
                    rows="3"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    :class="{ 'border-red-500': errors.notes }"
                  ></textarea>
                  <div v-if="errors.notes" class="text-red-500 text-sm mt-1">
                    {{ errors.notes }}
                  </div>
                </div>
              </div>

              <!-- 操作按鈕 -->
              <div class="flex justify-end space-x-3 mt-6 pt-6 border-t">
                <Link
                  :href="route('admin.vehicle-licenses.index')"
                  class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded"
                >
                  取消
                </Link>
                <button
                  type="submit"
                  :disabled="processing"
                  class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded disabled:opacity-50"
                >
                  {{ processing ? '建立中...' : '建立牌照' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { reactive } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
  companies: Array,
  errors: Object
})

const form = useForm({
  company_id: '',
  county: '',
  license_number: '',
  holder_name: '',
  license_year: '',
  license_month: '',
  previous_license_number: '',
  previous_holder_name: '',
  previous_license_year: '',
  previous_license_month: '',
  notes: '',
  replacement_date: '',
  revocation_date: '',
  status: 'active'
})

const submit = () => {
  form.post(route('admin.vehicle-licenses.store'))
}

const { processing } = form
</script>