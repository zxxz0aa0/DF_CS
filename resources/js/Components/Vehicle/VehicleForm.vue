<template>
    <form @submit.prevent="submitForm">
        <!-- 基本資訊 -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">基本資訊</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="company_category_id">公司類別 <span class="text-danger">*</span></label>
                            <select
                                id="company_category_id"
                                v-model="form.company_category_id"
                                class="form-control"
                                :class="{ 'is-invalid': errors.company_category_id }"
                                @change="onCompanyCategoryChange"
                            >
                                <option value="">請選擇公司類別</option>
                                <option
                                    v-for="category in companyCategories"
                                    :key="category.id"
                                    :value="category.id"
                                >
                                    {{ category.name }}
                                </option>
                            </select>
                            <div v-if="errors.company_category_id" class="invalid-feedback">
                                {{ errors.company_category_id }}
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="company_id">公司名稱 <span class="text-danger">*</span></label>
                            <select
                                id="company_id"
                                v-model="form.company_id"
                                class="form-control"
                                :class="{ 'is-invalid': errors.company_id }"
                                :disabled="!form.company_category_id"
                            >
                                <option value="">請選擇公司</option>
                                <option
                                    v-for="company in filteredCompanies"
                                    :key="company.id"
                                    :value="company.id"
                                >
                                    {{ company.name }}
                                </option>
                            </select>
                            <div v-if="errors.company_id" class="invalid-feedback">
                                {{ errors.company_id }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="license_number">車牌號碼 <span class="text-danger">*</span></label>
                            <input
                                id="license_number"
                                v-model="form.license_number"
                                type="text"
                                class="form-control"
                                :class="{ 'is-invalid': errors.license_number }"
                                placeholder="例：ABC-1234"
                                required
                            />
                            <div v-if="errors.license_number" class="invalid-feedback">
                                {{ errors.license_number }}
                            </div>
                        </div>
                    </div>

                    <div v-if="showReplacementLicense" class="col-md-4">
                        <div class="form-group">
                            <label for="replacement_license">替補車號</label>
                            <select
                                id="replacement_license"
                                v-model="form.replacement_license"
                                class="form-control"
                                :class="{ 'is-invalid': errors.replacement_license }"
                            >
                                <option value="">請選擇替補車號</option>
                                <option
                                    v-for="license in replacementLicenses"
                                    :key="license.id"
                                    :value="license.id"
                                >
                                    {{ formatReplacementLicenseLabel(license) }}
                                </option>
                            </select>
                            <div v-if="errors.replacement_license" class="invalid-feedback">
                                {{ errors.replacement_license }}
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="vehicle_type">車輛類型</label>
                            <select
                                id="vehicle_type"
                                v-model="form.vehicle_type"
                                class="form-control"
                                :class="{ 'is-invalid': errors.vehicle_type }"
                            >
                                <option value="">請選擇車輛類型</option>
                                <option
                                    v-for="type in vehicleTypeOptions"
                                    :key="type"
                                    :value="type"
                                >
                                    {{ type }}
                                </option>
                                <option
                                    v-if="form.vehicle_type && !vehicleTypeOptions.includes(form.vehicle_type)"
                                    :value="form.vehicle_type"
                                >
                                    {{ form.vehicle_type }}
                                </option>
                            </select>
                            <div v-if="errors.vehicle_type" class="invalid-feedback">
                                {{ errors.vehicle_type }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="owner_name">車主名稱 <span class="text-danger">*</span></label>
                            <input
                                v-if="!showOwnerDropdown"
                                id="owner_name"
                                v-model="form.owner_name"
                                type="text"
                                class="form-control"
                                :class="{ 'is-invalid': errors.owner_name }"
                                required
                            />
                            <select
                                v-else
                                id="owner_name"
                                v-model="form.owner_name"
                                class="form-control"
                                :class="{ 'is-invalid': errors.owner_name }"
                            >
                                <option value="">請選擇車主</option>
                                <option
                                    v-for="(name, id) in companyOwners"
                                    :key="id"
                                    :value="name"
                                >
                                    {{ name }}
                                </option>
                            </select>
                            <div v-if="errors.owner_name" class="invalid-feedback">
                                {{ errors.owner_name }}
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="address">地址</label>
                            <textarea
                                id="address"
                                v-model="form.address"
                                class="form-control"
                                :class="{ 'is-invalid': errors.address }"
                                rows="1"
                            ></textarea>
                            <div v-if="errors.address" class="invalid-feedback">
                                {{ errors.address }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 車輛製造資訊 -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">車輛製造資訊</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="brand">車輛廠牌</label>
                            <select
                                id="brand"
                                v-model="selectedBrand"
                                class="form-control"
                                :class="{ 'is-invalid': errors.brand }"
                            >
                                <option value="">請選擇廠牌</option>
                                <option
                                    v-for="brand in brandOptions"
                                    :key="brand"
                                    :value="brand"
                                >
                                    {{ brand }}
                                </option>
                                <option :value="customBrandOptionValue">
                                    其他，請手動輸入
                                </option>
                            </select>
                            <input
                                v-if="useCustomBrand"
                                id="brand_custom"
                                v-model="form.brand"
                                type="text"
                                class="form-control mt-2"
                                :class="{ 'is-invalid': errors.brand }"
                                placeholder="例：Toyota"
                            />
                            <div v-if="errors.brand" class="invalid-feedback">
                                {{ errors.brand }}
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="manufacture_year">出廠年</label>
                            <input
                                id="manufacture_year"
                                v-model="form.manufacture_year"
                                type="number"
                                class="form-control"
                                :class="{ 'is-invalid': errors.manufacture_year }"
                                placeholder="輸入西元年"
                                :min="1900"
                                :max="new Date().getFullYear() + 1"
                            />
                            <div v-if="errors.manufacture_year" class="invalid-feedback">
                                {{ errors.manufacture_year }}
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="manufacture_month">出廠月</label>
                            <select
                                id="manufacture_month"
                                v-model="form.manufacture_month"
                                class="form-control"
                                :class="{ 'is-invalid': errors.manufacture_month }"
                            >
                                <option value="">請選擇月份</option>
                                <option v-for="month in 12" :key="month" :value="month">
                                    {{ month }}月
                                </option>
                            </select>
                            <div v-if="errors.manufacture_month" class="invalid-feedback">
                                {{ errors.manufacture_month }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="vehicle_form">車輛形式</label>
                            <input
                                id="vehicle_form"
                                v-model="form.vehicle_form"
                                type="text"
                                class="form-control"
                                :class="{ 'is-invalid': errors.vehicle_form }"
                                placeholder="依行照輸入"
                            />
                            <div v-if="errors.vehicle_form" class="invalid-feedback">
                                {{ errors.vehicle_form }}
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="engine_displacement">排氣量 (cc)</label>
                            <input
                                id="engine_displacement"
                                v-model="form.engine_displacement"
                                type="number"
                                step="1"
                                class="form-control"
                                :class="{ 'is-invalid': errors.engine_displacement }"
                                min="0"
                            />
                            <div v-if="errors.engine_displacement" class="invalid-feedback">
                                {{ errors.engine_displacement }}
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="fuel_type">燃料種類</label>
                            <select
                                id="fuel_type"
                                v-model="form.fuel_type"
                                class="form-control"
                                :class="{ 'is-invalid': errors.fuel_type }"
                            >
                                <option value="">請選擇燃料種類</option>
                                <option value="汽油">汽油</option>
                                <option value="柴油">柴油</option>
                                <option value="電動">電動</option>
                                <option value="油電混合">油電混合</option>
                                <option value="天然氣">天然氣</option>
                            </select>
                            <div v-if="errors.fuel_type" class="invalid-feedback">
                                {{ errors.fuel_type }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="vehicle_model">車輛款式</label>
                            <input
                                id="vehicle_model"
                                v-model="form.vehicle_model"
                                type="text"
                                class="form-control"
                                :class="{ 'is-invalid': errors.vehicle_model }"
                                placeholder="例：Altis、Camry、RAV4"
                            />
                            <div v-if="errors.vehicle_model" class="invalid-feedback">
                                {{ errors.vehicle_model }}
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="vehicle_style">車輛樣式</label>
                            <select
                                id="vehicle_style"
                                v-model="form.vehicle_style"
                                class="form-control"
                                :class="{ 'is-invalid': errors.vehicle_style }"
                            >
                                <option value="">請選擇</option>
                                <option value="轎式">轎式</option>
                                <option value="休旅式">休旅式</option>
                                <option value="廂式">廂式</option>
                            </select>
                            <div v-if="errors.vehicle_style" class="invalid-feedback">
                                {{ errors.vehicle_style }}
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="vehicle_color">車輛顏色</label>
                            <input
                                id="vehicle_color"
                                v-model="form.vehicle_color"
                                type="text"
                                class="form-control"
                                :class="{ 'is-invalid': errors.vehicle_color }"
                            />
                            <div v-if="errors.vehicle_color" class="invalid-feedback">
                                {{ errors.vehicle_color }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="engine_number">引擎號碼</label>
                            <input
                                id="engine_number"
                                v-model="form.engine_number"
                                type="text"
                                class="form-control"
                                :class="{ 'is-invalid': errors.engine_number }"
                            />
                            <div v-if="errors.engine_number" class="invalid-feedback">
                                {{ errors.engine_number }}
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="chassis_number">車身號碼</label>
                            <input
                                id="chassis_number"
                                v-model="form.chassis_number"
                                type="text"
                                class="form-control"
                                :class="{ 'is-invalid': errors.chassis_number }"
                            />
                            <div v-if="errors.chassis_number" class="invalid-feedback">
                                {{ errors.chassis_number }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="passenger_capacity">載運人數</label>
                            <input
                                id="passenger_capacity"
                                v-model="form.passenger_capacity"
                                type="number"
                                class="form-control"
                                :class="{ 'is-invalid': errors.passenger_capacity }"
                                min="4"
                            />
                            <div v-if="errors.passenger_capacity" class="invalid-feedback">
                                {{ errors.passenger_capacity }}
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="property_type">產權類別</label>
                            <select
                                iid="property_type"
                                v-model="form.property_type"
                                class="form-control"
                                :class="{ 'is-invalid': errors.property_type }"
                            >
                                <option value="">請選擇</option>
                                <option value="轎式">公司資產</option>
                                <option value="休旅式">靠行車</option>
                                <option value="廂式">衛星派遣</option>
                            </select>
                            <div v-if="errors.property_type" class="invalid-feedback">
                                {{ errors.property_type }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 日期資訊 -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">日期資訊</h3>
            </div>
            <div class="card-body">
                <!-- 發照資訊 -->
                <h5>發照資訊</h5>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="license_issue_year">發照年 (民國)</label>
                            <input
                                id="license_issue_year"
                                v-model="form.license_issue_year_republic"
                                type="number"
                                class="form-control"
                                :class="{ 'is-invalid': errors.license_issue_year_republic }"
                                min="1"
                                max="200"
                            />
                            <div v-if="errors.license_issue_year_republic" class="invalid-feedback">
                                {{ errors.license_issue_year_republic }}
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="license_issue_month">發照月</label>
                            <select
                                id="license_issue_month"
                                v-model="form.license_issue_month"
                                class="form-control"
                                :class="{ 'is-invalid': errors.license_issue_month }"
                            >
                                <option value="">請選擇月份</option>
                                <option v-for="month in 12" :key="month" :value="month">
                                    {{ month }}月
                                </option>
                            </select>
                            <div v-if="errors.license_issue_month" class="invalid-feedback">
                                {{ errors.license_issue_month }}
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="license_issue_day">發照日</label>
                            <select
                                id="license_issue_day"
                                v-model="form.license_issue_day"
                                class="form-control"
                                :class="{ 'is-invalid': errors.license_issue_day }"
                            >
                                <option value="">請選擇日期</option>
                                <option v-for="day in 31" :key="day" :value="day">
                                    {{ day }}日
                                </option>
                            </select>
                            <div v-if="errors.license_issue_day" class="invalid-feedback">
                                {{ errors.license_issue_day }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 檢驗資訊 -->
                <h5>檢驗資訊</h5>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="inspection_year">檢驗年 (民國)</label>
                            <input
                                id="inspection_year"
                                v-model="form.inspection_year_republic"
                                type="number"
                                class="form-control"
                                :class="{ 'is-invalid': errors.inspection_year_republic }"
                                min="1"
                                max="200"
                            />
                            <div v-if="errors.inspection_year_republic" class="invalid-feedback">
                                {{ errors.inspection_year_republic }}
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="inspection_month">檢驗月</label>
                            <select
                                id="inspection_month"
                                v-model="form.inspection_month"
                                class="form-control"
                                :class="{ 'is-invalid': errors.inspection_month }"
                            >
                                <option value="">請選擇月份</option>
                                <option v-for="month in 12" :key="month" :value="month">
                                    {{ month }}月
                                </option>
                            </select>
                            <div v-if="errors.inspection_month" class="invalid-feedback">
                                {{ errors.inspection_month }}
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="inspection_day">檢驗日</label>
                            <select
                                id="inspection_day"
                                v-model="form.inspection_day"
                                class="form-control"
                                :class="{ 'is-invalid': errors.inspection_day }"
                            >
                                <option value="">請選擇日期</option>
                                <option v-for="day in 31" :key="day" :value="day">
                                    {{ day }}日
                                </option>
                            </select>
                            <div v-if="errors.inspection_day" class="invalid-feedback">
                                {{ errors.inspection_day }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 入籍資訊 -->
                <h5>入籍資訊 <small class="text-muted">(預設為當前日期)</small></h5>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="registration_year">入籍年 (民國)</label>
                            <input
                                id="registration_year"
                                v-model="form.registration_year_republic"
                                type="number"
                                class="form-control"
                                :class="{ 'is-invalid': errors.registration_year_republic }"
                                min="1"
                                max="200"
                            />
                            <div v-if="errors.registration_year_republic" class="invalid-feedback">
                                {{ errors.registration_year_republic }}
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="registration_month">入籍月</label>
                            <select
                                id="registration_month"
                                v-model="form.registration_month"
                                class="form-control"
                                :class="{ 'is-invalid': errors.registration_month }"
                            >
                                <option value="">請選擇月份</option>
                                <option v-for="month in 12" :key="month" :value="month">
                                    {{ month }}月
                                </option>
                            </select>
                            <div v-if="errors.registration_month" class="invalid-feedback">
                                {{ errors.registration_month }}
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="registration_day">入籍日</label>
                            <select
                                id="registration_day"
                                v-model="form.registration_day"
                                class="form-control"
                                :class="{ 'is-invalid': errors.registration_day }"
                            >
                                <option value="">請選擇日期</option>
                                <option v-for="day in 31" :key="day" :value="day">
                                    {{ day }}日
                                </option>
                            </select>
                            <div v-if="errors.registration_day" class="invalid-feedback">
                                {{ errors.registration_day }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 備註與車隊資訊 -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">備註與車隊資訊</h3>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label for="notes">備註</label>
                    <textarea
                        id="notes"
                        v-model="form.notes"
                        class="form-control"
                        :class="{ 'is-invalid': errors.notes }"
                        rows="4"
                    ></textarea>
                    <div v-if="errors.notes" class="invalid-feedback">
                        {{ errors.notes }}
                    </div>
                </div>

                <!-- 車隊資訊 -->
                <h5>車隊資訊</h5>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="fleet_name">車隊名稱</label>
                            <select
                                id="fleet_name"
                                v-model="form.fleet_name"
                                class="form-control"
                                :class="{ 'is-invalid': errors.fleet_name }"
                            >
                                <option value="">請選擇車隊</option>
                                <option value="大豐衛星">大豐衛星</option>
                                <option value="桃園大豐衛星">桃園大豐衛星</option>
                                <option value="雙平台">雙平台</option>

                            </select>
                            <div v-if="errors.fleet_name" class="invalid-feedback">
                                {{ errors.fleet_name }}
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="fleet_category">車隊類別</label>
                            <select
                                id="fleet_category"
                                v-model="form.fleet_category"
                                class="form-control"
                                :class="{ 'is-invalid': errors.fleet_category }"
                            >
                                <option value="">請選擇車隊類別</option>
                                <option value="一般計程車">一般計程車</option>
                                <option value="多元計程車">多元計程車</option>
                                <option value="無障礙計程車">無障礙計程車</option>
                            </select>
                            <div v-if="errors.fleet_category" class="invalid-feedback">
                                {{ errors.fleet_category }}
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="fleet_number">車隊編號</label>
                            <input
                                id="fleet_number"
                                v-model="form.fleet_number"
                                type="text"
                                class="form-control"
                                :class="{ 'is-invalid': errors.fleet_number }"
                                placeholder="例：8000"
                            />
                            <div v-if="errors.fleet_number" class="invalid-feedback">
                                {{ errors.fleet_number }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 按鈕 -->
        <div class="card">
            <div class="card-footer">
                <div class="row">
                    <div class="col-md-12 text-right">
                        <Link
                            :href="route('admin.vehicles.index')"
                            class="btn btn-secondary mr-2"
                        >
                            取消
                        </Link>
                        <button
                            type="submit"
                            class="btn btn-primary"
                            :disabled="processing"
                        >
                            <i v-if="processing" class="fas fa-spinner fa-spin mr-1"></i>
                            {{ submitText }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</template>

<script setup>
import { reactive, ref, watch, onMounted, computed } from 'vue'
import { Link } from '@inertiajs/vue3'

const props = defineProps({
    vehicle: Object,
    companyCategories: Array,
    companies: Array,
    errors: Object,
    processing: Boolean,
    submitText: {
        type: String,
        default: '送出'
    }
})

const emit = defineEmits(['submit'])

// 表單資料
const form = reactive({
    company_category_id: props.vehicle?.company_category_id || '',
    company_id: props.vehicle?.company_id || '',
    license_number: props.vehicle?.license_number || '',
    replacement_license: props.vehicle?.replacement_license || '',
    vehicle_type: props.vehicle?.vehicle_type || '',
    owner_name: props.vehicle?.owner_name || '',
    address: props.vehicle?.address || '',
    brand: props.vehicle?.brand || '',
    manufacture_year: props.vehicle?.manufacture_year || '',
    manufacture_month: props.vehicle?.manufacture_month || '',
    vehicle_form: props.vehicle?.vehicle_form || '',
    engine_displacement: props.vehicle?.engine_displacement || '',
    fuel_type: props.vehicle?.fuel_type || '',
    vehicle_model: props.vehicle?.vehicle_model || '',
    vehicle_style: props.vehicle?.vehicle_style || '',
    engine_number: props.vehicle?.engine_number || '',
    chassis_number: props.vehicle?.chassis_number || '',
    passenger_capacity: props.vehicle?.passenger_capacity || '',
    vehicle_color: props.vehicle?.vehicle_color || '',
    license_issue_year_republic: props.vehicle?.license_issue_year ? (props.vehicle.license_issue_year - 1911) : '',
    license_issue_month: props.vehicle?.license_issue_month || '',
    license_issue_day: props.vehicle?.license_issue_day || '',
    inspection_year_republic: props.vehicle?.inspection_year ? (props.vehicle.inspection_year - 1911) : '',
    inspection_month: props.vehicle?.inspection_month || '',
    inspection_day: props.vehicle?.inspection_day || '',
    registration_year_republic: props.vehicle?.registration_year ? (props.vehicle.registration_year - 1911) : '',
    registration_month: props.vehicle?.registration_month || '',
    registration_day: props.vehicle?.registration_day || '',
    property_type: props.vehicle?.property_type || '',
    notes: props.vehicle?.notes || '',
    fleet_name: props.vehicle?.fleet_name || '',
    fleet_category: props.vehicle?.fleet_category || '',
    fleet_number: props.vehicle?.fleet_number || '',
})

// 動態選項
const replacementLicenses = ref([])
const companyOwners = ref({})
const showReplacementLicense = ref(false)
const showOwnerDropdown = ref(false)
const vehicleTypeOptions = ['營業小客車', '租賃小客車', '自用小客車']
const brandOptions = ['Toyota_豐田', 'Audi_奧迪', 'BMW_寶馬', 'Citroën_雪鐵龍', 'Ford_福特', 'Honda_本田', 'Hyundai_現代', 'Infiniti_英菲尼迪', 'Jaguar_捷豹', 'Kia_起亞', 'Land Rover_路虎', 'Lexus_凌志', 'Luxgen_納智捷', 'Mazda_馬自達', 'Mercedes-Benz_賓士', 'Mini_迷你', 'Mitsubishi_三菱', 'Nissan_日產', 'Peugeot_標緻', 'Porsche_保時捷', 'Renault_雷諾', 'Subaru_速霸陸', 'Suzuki_鈴木', 'Tesla_特斯拉', 'Volkswagen_福斯', 'Volvo_富豪']
const customBrandOptionValue = '__custom__'
const selectedBrand = ref('')
const useCustomBrand = ref(false)

watch(
    () => form.brand,
    (value) => {
        if (!value) {
            if (selectedBrand.value !== customBrandOptionValue) {
                selectedBrand.value = ''
                useCustomBrand.value = false
            }
            return
        }

        if (brandOptions.includes(value)) {
            if (selectedBrand.value !== value) {
                selectedBrand.value = value
            }
            useCustomBrand.value = false
        } else {
            useCustomBrand.value = true
            if (selectedBrand.value !== customBrandOptionValue) {
                selectedBrand.value = customBrandOptionValue
            }
        }
    },
    { immediate: true }
)

watch(selectedBrand, (value) => {
    if (!value) {
        if (!useCustomBrand.value) {
            form.brand = ''
        }
        return
    }

    if (value === customBrandOptionValue) {
        if (!useCustomBrand.value) {
            useCustomBrand.value = true
        }

        if (brandOptions.includes(form.brand)) {
            form.brand = ''
        }
        return
    }

    if (form.brand !== value) {
        form.brand = value
    }

    useCustomBrand.value = false
})

const formatReplacementLicenseLabel = (license) => {
    return license.holder_name
        ? `${license.replacement_number} (${license.holder_name})`
        : license.replacement_number
}

const filteredCompanies = computed(() => {
    if (!form.company_category_id) {
        return []
    }

    const selectedCategoryId = Number(form.company_category_id)
    return props.companies.filter(
        (company) => Number(company.category_id) === selectedCategoryId
    )
})

watch(
    () => form.company_category_id,
    () => {
        if (!form.company_category_id) {
            form.company_id = ''
            return
        }

        const isCompanyInCategory = filteredCompanies.value.some(
            (company) => Number(company.id) === Number(form.company_id)
        )

        if (!isCompanyInCategory) {
            form.company_id = ''
        }
    }
)

// 設定預設入籍日期 (只在新增時)
onMounted(() => {
    if (!props.vehicle) {
        const today = new Date()
        form.registration_year_republic = today.getFullYear() - 1911
        form.registration_month = today.getMonth() + 1
        form.registration_day = today.getDate()
    }
})

// 監聽公司類別變化
const onCompanyCategoryChange = async () => {
    form.replacement_license = ''
    replacementLicenses.value = []
    showReplacementLicense.value = false

    if (!form.company_category_id) {
        showOwnerDropdown.value = false
        return
    }

    try {
        const ownerResponse = await fetch(
            `/admin/api/vehicles/company-owners?company_category_id=${form.company_category_id}`
        )
        const ownerData = await ownerResponse.json()
        companyOwners.value = ownerData
        showOwnerDropdown.value = Object.keys(ownerData).length > 0

        if (form.company_id) {
            fetchReplacementLicenses(form.company_id)
        }
    } catch (error) {
        console.error('取得配置資料失敗:', error)
    }
}

const fetchReplacementLicenses = async (companyId) => {
    if (!companyId) {
        form.replacement_license = ''
        replacementLicenses.value = []
        showReplacementLicense.value = false
        showOwnerDropdown.value = Object.keys(companyOwners.value).length > 0
        return
    }

    try {
        const response = await fetch(
            `/admin/api/vehicles/replacement-licenses?company_id=${companyId}`
        )

        const data = await response.json()
        replacementLicenses.value = data
        showReplacementLicense.value = data.length > 0

        if (showReplacementLicense.value) {
            const company = props.companies.find(
                (item) => Number(item.id) === Number(form.company_id)
            )

            if (company) {
                form.owner_name = company.name
            }

            showOwnerDropdown.value = false
        } else {
            showOwnerDropdown.value = Object.keys(companyOwners.value).length > 0
        }

        const hasCurrentSelection = data.some(
            (item) => item.id === form.replacement_license
        )

        if (!hasCurrentSelection) {
            form.replacement_license = ''
        }
    } catch (error) {
        console.error('取得替補車號失敗:', error)
        form.replacement_license = ''
        replacementLicenses.value = []
        showReplacementLicense.value = false
        showOwnerDropdown.value = Object.keys(companyOwners.value).length > 0
    }
}

// 送出表單
const submitForm = () => {
    emit('submit', form)
}

// 初始化時檢查公司類別配置
if (form.company_category_id) {
    onCompanyCategoryChange()
}

watch(
    () => form.company_id,
    (companyId) => {
        fetchReplacementLicenses(companyId)
    },
    { immediate: true }
)
</script>

<style scoped>
.card + .card {
    margin-top: 1rem;
}

.text-danger {
    color: #dc3545 !important;
}

.form-group {
    margin-bottom: 1rem;
}

h5 {
    margin-top: 1.5rem;
    margin-bottom: 1rem;
    color: #495057;
    border-bottom: 1px solid #dee2e6;
    padding-bottom: 0.5rem;
}

h5:first-child {
    margin-top: 0;
}
</style>
