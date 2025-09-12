<template>
    <AdminLayout :user="$page.props.auth.user">
        <template #header>
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">編輯駕駛</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><Link :href="route('admin.dashboard')">儀表板</Link></li>
                        <li class="breadcrumb-item"><Link :href="route('admin.drivers.index')">駕駛管理</Link></li>
                        <li class="breadcrumb-item active">編輯駕駛</li>
                    </ol>
                </div>
            </div>
        </template>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">編輯駕駛資料 - {{ driver.name }}</h3>
                        <div class="card-tools">
                            <Link :href="route('admin.drivers.index')" class="btn btn-secondary btn-sm">
                                <i class="bi bi-arrow-left"></i> 返回列表
                            </Link>
                        </div>
                    </div>
                    <form @submit.prevent="submit">
                        <div class="card-body">
                            <div class="row">
                                <!-- 基本資料 -->
                                <div class="col-md-6">
                                    <h5 class="border-bottom pb-2 mb-3">基本資料</h5>
                                    
                                    <div class="mb-3">
                                        <label for="name" class="form-label">
                                            姓名 <span class="text-danger">*</span>
                                        </label>
                                        <input
                                            id="name"
                                            v-model="form.name"
                                            type="text"
                                            class="form-control"
                                            :class="{ 'is-invalid': errors.name }"
                                            required
                                        >
                                        <div v-if="errors.name" class="invalid-feedback">
                                            {{ errors.name }}
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="id_number" class="form-label">
                                            身分證字號 <span class="text-danger">*</span>
                                        </label>
                                        <input
                                            id="id_number"
                                            v-model="form.id_number"
                                            type="text"
                                            class="form-control"
                                            :class="{ 'is-invalid': errors.id_number }"
                                            placeholder="A123456789"
                                            maxlength="10"
                                            style="text-transform: uppercase"
                                            required
                                        >
                                        <div v-if="errors.id_number" class="invalid-feedback">
                                            {{ errors.id_number }}
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="company_category_id" class="form-label">公司類別</label>
                                        <select
                                            id="company_category_id"
                                            v-model="form.company_category_id"
                                            class="form-control"
                                            :class="{ 'is-invalid': errors.company_category_id }"
                                        >
                                            <option :value="null">請選擇公司類別</option>
                                            <option v-for="category in companyCategories" 
                                                    :key="category.id" 
                                                    :value="category.id">
                                                {{ category.name }}
                                            </option>
                                        </select>
                                        <div v-if="errors.company_category_id" class="invalid-feedback">
                                            {{ errors.company_category_id }}
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="birthday" class="form-label">生日</label>
                                        <DateInput
                                            id="birthday"
                                            v-model="form.birthday"
                                            :class="{ 'is-invalid': errors.birthday }"
                                            placeholder="111/01/01"
                                        />
                                        <div v-if="errors.birthday" class="invalid-feedback">
                                            {{ errors.birthday }}
                                        </div>
                                    </div>
                                </div>

                                <!-- 聯絡資訊 -->
                                <div class="col-md-6">
                                    <h5 class="border-bottom pb-2 mb-3">聯絡資訊</h5>
                                    
                                    <div class="mb-3">
                                        <label for="contact_address" class="form-label">通訊地址</label>
                                        <textarea
                                            id="contact_address"
                                            v-model="form.contact_address"
                                            class="form-control"
                                            :class="{ 'is-invalid': errors.contact_address }"
                                            rows="2"
                                        ></textarea>
                                        <div v-if="errors.contact_address" class="invalid-feedback">
                                            {{ errors.contact_address }}
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="residence_address" class="form-label">戶籍地址</label>
                                        <textarea
                                            id="residence_address"
                                            v-model="form.residence_address"
                                            class="form-control"
                                            :class="{ 'is-invalid': errors.residence_address }"
                                            rows="2"
                                        ></textarea>
                                        <div v-if="errors.residence_address" class="invalid-feedback">
                                            {{ errors.residence_address }}
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="home_phone" class="form-label">住家電話</label>
                                        <input
                                            id="home_phone"
                                            v-model="form.home_phone"
                                            type="tel"
                                            class="form-control"
                                            :class="{ 'is-invalid': errors.home_phone }"
                                            placeholder="02-12345678"
                                        >
                                        <div v-if="errors.home_phone" class="invalid-feedback">
                                            {{ errors.home_phone }}
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="mobile_phone1" class="form-label">手機號碼1</label>
                                        <input
                                            id="mobile_phone1"
                                            v-model="form.mobile_phone1"
                                            type="tel"
                                            class="form-control"
                                            :class="{ 'is-invalid': errors.mobile_phone1 }"
                                            placeholder="0912345678"
                                        >
                                        <div v-if="errors.mobile_phone1" class="invalid-feedback">
                                            {{ errors.mobile_phone1 }}
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="mobile_phone2" class="form-label">手機號碼2</label>
                                        <input
                                            id="mobile_phone2"
                                            v-model="form.mobile_phone2"
                                            type="tel"
                                            class="form-control"
                                            :class="{ 'is-invalid': errors.mobile_phone2 }"
                                            placeholder="0912345678"
                                        >
                                        <div v-if="errors.mobile_phone2" class="invalid-feedback">
                                            {{ errors.mobile_phone2 }}
                                        </div>
                                    </div>
                                </div>

                                <!-- 緊急聯絡人 -->
                                <div class="col-md-6">
                                    <h5 class="border-bottom pb-2 mb-3">緊急聯絡人</h5>
                                    
                                    <div class="mb-3">
                                        <label for="emergency_contact" class="form-label">緊急聯絡人</label>
                                        <input
                                            id="emergency_contact"
                                            v-model="form.emergency_contact"
                                            type="text"
                                            class="form-control"
                                            :class="{ 'is-invalid': errors.emergency_contact }"
                                        >
                                        <div v-if="errors.emergency_contact" class="invalid-feedback">
                                            {{ errors.emergency_contact }}
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="emergency_phone" class="form-label">緊急聯絡人電話</label>
                                        <input
                                            id="emergency_phone"
                                            v-model="form.emergency_phone"
                                            type="tel"
                                            class="form-control"
                                            :class="{ 'is-invalid': errors.emergency_phone }"
                                        >
                                        <div v-if="errors.emergency_phone" class="invalid-feedback">
                                            {{ errors.emergency_phone }}
                                        </div>
                                    </div>
                                </div>

                                <!-- 日期與狀態資訊 -->
                                <div class="col-md-6">
                                    <h5 class="border-bottom pb-2 mb-3">日期與狀態</h5>
                                    
                                    <div class="mb-3">
                                        <label for="registration_date" class="form-label">
                                            入籍日期 <span class="text-danger">*</span>
                                        </label>
                                        <DateInput
                                            id="registration_date"
                                            v-model="form.registration_date"
                                            :class="{ 'is-invalid': errors.registration_date }"
                                            required
                                        />
                                        <div v-if="errors.registration_date" class="invalid-feedback">
                                            {{ errors.registration_date }}
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="deregistration_date" class="form-label">退籍日期</label>
                                        <DateInput
                                            id="deregistration_date"
                                            v-model="form.deregistration_date"
                                            :class="{ 'is-invalid': errors.deregistration_date }"
                                        />
                                        <div v-if="errors.deregistration_date" class="invalid-feedback">
                                            {{ errors.deregistration_date }}
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="fleet_join_date" class="form-label">加入車隊日期</label>
                                        <DateInput
                                            id="fleet_join_date"
                                            v-model="form.fleet_join_date"
                                            :class="{ 'is-invalid': errors.fleet_join_date }"
                                        />
                                        <div v-if="errors.fleet_join_date" class="invalid-feedback">
                                            {{ errors.fleet_join_date }}
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="fleet_leave_date" class="form-label">退出車隊日期</label>
                                        <DateInput
                                            id="fleet_leave_date"
                                            v-model="form.fleet_leave_date"
                                            :class="{ 'is-invalid': errors.fleet_leave_date }"
                                        />
                                        <div v-if="errors.fleet_leave_date" class="invalid-feedback">
                                            {{ errors.fleet_leave_date }}
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="license_expire_date" class="form-label">駕照到期日</label>
                                        <DateInput
                                            id="license_expire_date"
                                            v-model="form.license_expire_date"
                                            :class="{ 'is-invalid': errors.license_expire_date }"
                                        />
                                        <div v-if="errors.license_expire_date" class="invalid-feedback">
                                            {{ errors.license_expire_date }}
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="professional_license_expire_date" class="form-label">執登到期日</label>
                                        <DateInput
                                            id="professional_license_expire_date"
                                            v-model="form.professional_license_expire_date"
                                            :class="{ 'is-invalid': errors.professional_license_expire_date }"
                                        />
                                        <div v-if="errors.professional_license_expire_date" class="invalid-feedback">
                                            {{ errors.professional_license_expire_date }}
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="status" class="form-label">
                                            狀態 <span class="text-danger">*</span>
                                        </label>
                                        <select
                                            id="status"
                                            v-model="form.status"
                                            class="form-control"
                                            :class="{ 'is-invalid': errors.status }"
                                            required
                                        >
                                            <option value="">請選擇狀態</option>
                                            <option value="open">在籍中</option>
                                            <option value="close">已退籍</option>
                                        </select>
                                        <div v-if="errors.status" class="invalid-feedback">
                                            {{ errors.status }}
                                        </div>
                                    </div>
                                </div>

                                <!-- 備註 -->
                                <div class="col-md-12">
                                    <h5 class="border-bottom pb-2 mb-3">備註</h5>
                                    
                                    <div class="mb-3">
                                        <label for="notes" class="form-label">備註</label>
                                        <textarea
                                            id="notes"
                                            v-model="form.notes"
                                            class="form-control"
                                            :class="{ 'is-invalid': errors.notes }"
                                            rows="4"
                                            placeholder="請輸入備註資訊..."
                                            maxlength="2000"
                                        ></textarea>
                                        <div v-if="errors.notes" class="invalid-feedback">
                                            {{ errors.notes }}
                                        </div>
                                        <small class="form-text text-muted">
                                            最多可輸入 2000 個字元 (目前: {{ (form.notes || '').length }}/2000)
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary" :disabled="form.processing">
                                <span v-if="form.processing" class="spinner-border spinner-border-sm me-2"></span>
                                <i v-else class="bi bi-check"></i>
                                {{ form.processing ? '更新中...' : '更新' }}
                            </button>
                            <Link :href="route('admin.drivers.index')" class="btn btn-secondary ms-2">
                                取消
                            </Link>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { Link, useForm, usePage } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import DateInput from '@/Components/DateInput.vue'

const props = defineProps({
    driver: Object,
    companyCategories: Array
})

const { errors } = usePage().props

const form = useForm({
    name: props.driver.name || '',
    id_number: props.driver.id_number || '',
    company_category_id: props.driver.company_category_id || null,
    birthday: props.driver.birthday || null,
    contact_address: props.driver.contact_address || '',
    residence_address: props.driver.residence_address || '',
    home_phone: props.driver.home_phone || '',
    mobile_phone1: props.driver.mobile_phone1 || '',
    mobile_phone2: props.driver.mobile_phone2 || '',
    emergency_contact: props.driver.emergency_contact || '',
    emergency_phone: props.driver.emergency_phone || '',
    registration_date: props.driver.registration_date || '',
    deregistration_date: props.driver.deregistration_date || null,
    fleet_join_date: props.driver.fleet_join_date || null,
    fleet_leave_date: props.driver.fleet_leave_date || null,
    license_expire_date: props.driver.license_expire_date || null,
    professional_license_expire_date: props.driver.professional_license_expire_date || null,
    status: props.driver.status || 'open',
    notes: props.driver.notes || ''
})

const submit = () => {
    form.put(route('admin.drivers.update', props.driver.id))
}
</script>