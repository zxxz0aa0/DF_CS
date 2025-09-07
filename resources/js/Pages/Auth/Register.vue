<script setup>
import { ref, computed } from "vue";
import GuestLayout from "@/Layouts/GuestLayout.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";

const form = useForm({
    // 基本資訊
    name: "",
    username: "",
    email: "",
    password: "",
    password_confirmation: "",
    
    // 身分資訊
    id_number: "",
    birth_date: "",
    gender: "",
    
    // 聯絡資訊
    mobile_phone: "",
    home_phone: "",
    address: "",
    
    // 工作資訊
    department: "",
    position: "",
    
    // 緊急聯絡人
    emergency_contact: "",
    emergency_phone: "",
});

// 多步驟表單狀態
const currentStep = ref(1);
const totalSteps = 4;
const isLoading = ref(false);

// 密碼顯示狀態
const showPassword = ref(false);
const showPasswordConfirmation = ref(false);

// 步驟導航
const steps = [
    { number: 1, title: "基本資訊", description: "帳號與密碼設定" },
    { number: 2, title: "身分資訊", description: "個人身分證明" },
    { number: 3, title: "聯絡資訊", description: "聯絡方式與地址" },
    { number: 4, title: "工作資訊", description: "部門職務與緊急聯絡人" },
];

// 計算進度百分比
const progressPercentage = computed(() => {
    return (currentStep.value / totalSteps) * 100;
});

// 檢查當前步驟是否完成
const isStepValid = computed(() => {
    switch (currentStep.value) {
        case 1:
            return form.name && form.username && form.email && 
                   form.password && form.password_confirmation;
        case 2:
            return form.id_number && form.birth_date && form.gender;
        case 3:
            return form.mobile_phone;
        case 4:
            return true; // 最後一步驟的欄位都是選填
        default:
            return false;
    }
});

// 步驟導航函數
const nextStep = () => {
    if (currentStep.value < totalSteps && isStepValid.value) {
        currentStep.value++;
    }
};

const prevStep = () => {
    if (currentStep.value > 1) {
        currentStep.value--;
    }
};

// 切換密碼顯示
const togglePassword = () => {
    showPassword.value = !showPassword.value;
};

const togglePasswordConfirmation = () => {
    showPasswordConfirmation.value = !showPasswordConfirmation.value;
};

// 提交表單
const submit = () => {
    if (currentStep.value === totalSteps) {
        isLoading.value = true;
        form.post(route("register"), {
            onFinish: () => {
                form.reset("password", "password_confirmation");
                isLoading.value = false;
            },
        });
    }
};

// 性別選項
const genderOptions = [
    { value: "male", label: "男性" },
    { value: "female", label: "女性" },
    { value: "other", label: "其他" },
];

// 常見部門選項
const departmentOptions = [
    "IT部門", "人事部門", "財務部門", "行政部門", "業務部門", 
    "客服部門", "行銷部門", "研發部門", "生產部門", "其他"
];
</script>

<template>
    <GuestLayout>
        <Head title="註冊 - DF管理系統" />

        <!-- 進度指示器 -->
        <div class="mb-8">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold text-gray-900">
                    {{ steps[currentStep - 1].title }}
                </h2>
                <span class="text-sm text-gray-600">
                    {{ currentStep }} / {{ totalSteps }}
                </span>
            </div>
            <p class="text-gray-600 text-sm mb-4">
                {{ steps[currentStep - 1].description }}
            </p>
            
            <!-- 進度條 -->
            <div class="w-full bg-gray-200 rounded-full h-2 mb-6">
                <div
                    class="bg-gradient-to-r from-blue-600 to-indigo-600 h-2 rounded-full transition-all duration-500 ease-out"
                    :style="{ width: progressPercentage + '%' }"
                ></div>
            </div>

            <!-- 步驟指示點 -->
            <div class="flex justify-between">
                <div
                    v-for="step in steps"
                    :key="step.number"
                    class="flex flex-col items-center"
                >
                    <div
                        :class="[
                            'w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium transition-all duration-300',
                            step.number <= currentStep
                                ? 'bg-blue-600 text-white'
                                : 'bg-gray-200 text-gray-600'
                        ]"
                    >
                        <i
                            v-if="step.number < currentStep"
                            class="bi bi-check-lg text-xs"
                        ></i>
                        <span v-else>{{ step.number }}</span>
                    </div>
                    <span
                        :class="[
                            'text-xs mt-2 text-center',
                            step.number <= currentStep
                                ? 'text-blue-600 font-medium'
                                : 'text-gray-500'
                        ]"
                    >
                        {{ step.title }}
                    </span>
                </div>
            </div>
        </div>

        <!-- 註冊表單 -->
        <form @submit.prevent="submit" class="space-y-6">
            <!-- 步驟 1: 基本資訊 -->
            <div v-if="currentStep === 1" class="space-y-6">
                <!-- 姓名 -->
                <div class="space-y-2">
                    <label for="name" class="block text-sm font-semibold text-gray-700">
                        姓名 <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="bi bi-person text-gray-400"></i>
                        </div>
                        <input
                            id="name"
                            type="text"
                            v-model="form.name"
                            class="block w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-gray-50 focus:bg-white"
                            placeholder="請輸入您的姓名"
                            required
                            autofocus
                        />
                    </div>
                    <div v-if="form.errors.name" class="text-sm text-red-600 flex items-center">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        {{ form.errors.name }}
                    </div>
                </div>

                <!-- 帳號 -->
                <div class="space-y-2">
                    <label for="username" class="block text-sm font-semibold text-gray-700">
                        帳號 <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="bi bi-at text-gray-400"></i>
                        </div>
                        <input
                            id="username"
                            type="text"
                            v-model="form.username"
                            class="block w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-gray-50 focus:bg-white"
                            placeholder="請輸入帳號"
                            required
                        />
                    </div>
                    <div v-if="form.errors.username" class="text-sm text-red-600 flex items-center">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        {{ form.errors.username }}
                    </div>
                    <p class="text-xs text-gray-500">帳號將用於登入系統</p>
                </div>

                <!-- 電子郵件 -->
                <div class="space-y-2">
                    <label for="email" class="block text-sm font-semibold text-gray-700">
                        電子郵件 <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="bi bi-envelope text-gray-400"></i>
                        </div>
                        <input
                            id="email"
                            type="email"
                            v-model="form.email"
                            class="block w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-gray-50 focus:bg-white"
                            placeholder="請輸入電子郵件"
                            required
                        />
                    </div>
                    <div v-if="form.errors.email" class="text-sm text-red-600 flex items-center">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        {{ form.errors.email }}
                    </div>
                </div>

                <!-- 密碼 -->
                <div class="space-y-2">
                    <label for="password" class="block text-sm font-semibold text-gray-700">
                        密碼 <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="bi bi-lock text-gray-400"></i>
                        </div>
                        <input
                            id="password"
                            :type="showPassword ? 'text' : 'password'"
                            v-model="form.password"
                            class="block w-full pl-10 pr-12 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-gray-50 focus:bg-white"
                            placeholder="請輸入密碼"
                            required
                        />
                        <button
                            type="button"
                            @click="togglePassword"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none"
                        >
                            <i :class="showPassword ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
                        </button>
                    </div>
                    <div v-if="form.errors.password" class="text-sm text-red-600 flex items-center">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        {{ form.errors.password }}
                    </div>
                </div>

                <!-- 確認密碼 -->
                <div class="space-y-2">
                    <label for="password_confirmation" class="block text-sm font-semibold text-gray-700">
                        確認密碼 <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="bi bi-shield-check text-gray-400"></i>
                        </div>
                        <input
                            id="password_confirmation"
                            :type="showPasswordConfirmation ? 'text' : 'password'"
                            v-model="form.password_confirmation"
                            class="block w-full pl-10 pr-12 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-gray-50 focus:bg-white"
                            placeholder="請再次輸入密碼"
                            required
                        />
                        <button
                            type="button"
                            @click="togglePasswordConfirmation"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none"
                        >
                            <i :class="showPasswordConfirmation ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
                        </button>
                    </div>
                    <div v-if="form.errors.password_confirmation" class="text-sm text-red-600 flex items-center">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        {{ form.errors.password_confirmation }}
                    </div>
                </div>
            </div>

            <!-- 步驟 2: 身分資訊 -->
            <div v-if="currentStep === 2" class="space-y-6">
                <!-- 身分證字號 -->
                <div class="space-y-2">
                    <label for="id_number" class="block text-sm font-semibold text-gray-700">
                        身分證字號 <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="bi bi-card-text text-gray-400"></i>
                        </div>
                        <input
                            id="id_number"
                            type="text"
                            v-model="form.id_number"
                            class="block w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-gray-50 focus:bg-white uppercase"
                            placeholder="請輸入身分證字號（例：A123456789）"
                            maxlength="10"
                            required
                        />
                    </div>
                    <div v-if="form.errors.id_number" class="text-sm text-red-600 flex items-center">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        {{ form.errors.id_number }}
                    </div>
                    <p class="text-xs text-gray-500">
                        <i class="bi bi-info-circle me-1"></i>
                        身分證字號將加密保存，請確保正確性
                    </p>
                </div>

                <!-- 出生日期 -->
                <div class="space-y-2">
                    <label for="birth_date" class="block text-sm font-semibold text-gray-700">
                        出生日期 <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="bi bi-calendar text-gray-400"></i>
                        </div>
                        <input
                            id="birth_date"
                            type="date"
                            v-model="form.birth_date"
                            class="block w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-gray-50 focus:bg-white"
                            required
                        />
                    </div>
                    <div v-if="form.errors.birth_date" class="text-sm text-red-600 flex items-center">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        {{ form.errors.birth_date }}
                    </div>
                </div>

                <!-- 性別 -->
                <div class="space-y-2">
                    <label for="gender" class="block text-sm font-semibold text-gray-700">
                        性別 <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="bi bi-people text-gray-400"></i>
                        </div>
                        <select
                            id="gender"
                            v-model="form.gender"
                            class="block w-full pl-10 pr-10 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-gray-50 focus:bg-white appearance-none"
                            required
                        >
                            <option value="">請選擇性別</option>
                            <option
                                v-for="option in genderOptions"
                                :key="option.value"
                                :value="option.value"
                            >
                                {{ option.label }}
                            </option>
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <i class="bi bi-chevron-down text-gray-400"></i>
                        </div>
                    </div>
                    <div v-if="form.errors.gender" class="text-sm text-red-600 flex items-center">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        {{ form.errors.gender }}
                    </div>
                </div>
            </div>

            <!-- 步驟 3: 聯絡資訊 -->
            <div v-if="currentStep === 3" class="space-y-6">
                <!-- 手機號碼 -->
                <div class="space-y-2">
                    <label for="mobile_phone" class="block text-sm font-semibold text-gray-700">
                        手機號碼 <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="bi bi-phone text-gray-400"></i>
                        </div>
                        <input
                            id="mobile_phone"
                            type="tel"
                            v-model="form.mobile_phone"
                            class="block w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-gray-50 focus:bg-white"
                            placeholder="請輸入手機號碼（例：0912345678）"
                            pattern="09[0-9]{8}"
                            maxlength="10"
                            required
                        />
                    </div>
                    <div v-if="form.errors.mobile_phone" class="text-sm text-red-600 flex items-center">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        {{ form.errors.mobile_phone }}
                    </div>
                    <p class="text-xs text-gray-500">
                        <i class="bi bi-info-circle me-1"></i>
                        請輸入台灣地區手機號碼格式
                    </p>
                </div>

                <!-- 家用電話 -->
                <div class="space-y-2">
                    <label for="home_phone" class="block text-sm font-semibold text-gray-700">
                        家用電話
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="bi bi-telephone text-gray-400"></i>
                        </div>
                        <input
                            id="home_phone"
                            type="tel"
                            v-model="form.home_phone"
                            class="block w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-gray-50 focus:bg-white"
                            placeholder="請輸入家用電話（例：02-12345678）"
                        />
                    </div>
                    <div v-if="form.errors.home_phone" class="text-sm text-red-600 flex items-center">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        {{ form.errors.home_phone }}
                    </div>
                </div>

                <!-- 通訊地址 -->
                <div class="space-y-2">
                    <label for="address" class="block text-sm font-semibold text-gray-700">
                        通訊地址
                    </label>
                    <div class="relative">
                        <div class="absolute top-3 left-0 pl-3 pointer-events-none">
                            <i class="bi bi-geo-alt text-gray-400"></i>
                        </div>
                        <textarea
                            id="address"
                            v-model="form.address"
                            rows="3"
                            class="block w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-gray-50 focus:bg-white resize-none"
                            placeholder="請輸入通訊地址"
                        ></textarea>
                    </div>
                    <div v-if="form.errors.address" class="text-sm text-red-600 flex items-center">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        {{ form.errors.address }}
                    </div>
                </div>
            </div>

            <!-- 步驟 4: 工作與緊急聯絡資訊 -->
            <div v-if="currentStep === 4" class="space-y-6">
                <!-- 部門 -->
                <div class="space-y-2">
                    <label for="department" class="block text-sm font-semibold text-gray-700">
                        部門
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="bi bi-building text-gray-400"></i>
                        </div>
                        <select
                            id="department"
                            v-model="form.department"
                            class="block w-full pl-10 pr-10 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-gray-50 focus:bg-white appearance-none"
                        >
                            <option value="">請選擇部門</option>
                            <option
                                v-for="dept in departmentOptions"
                                :key="dept"
                                :value="dept"
                            >
                                {{ dept }}
                            </option>
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <i class="bi bi-chevron-down text-gray-400"></i>
                        </div>
                    </div>
                    <div v-if="form.errors.department" class="text-sm text-red-600 flex items-center">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        {{ form.errors.department }}
                    </div>
                </div>

                <!-- 職務 -->
                <div class="space-y-2">
                    <label for="position" class="block text-sm font-semibold text-gray-700">
                        職務
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="bi bi-briefcase text-gray-400"></i>
                        </div>
                        <input
                            id="position"
                            type="text"
                            v-model="form.position"
                            class="block w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-gray-50 focus:bg-white"
                            placeholder="請輸入職務"
                        />
                    </div>
                    <div v-if="form.errors.position" class="text-sm text-red-600 flex items-center">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        {{ form.errors.position }}
                    </div>
                </div>

                <!-- 緊急聯絡人 -->
                <div class="space-y-2">
                    <label for="emergency_contact" class="block text-sm font-semibold text-gray-700">
                        緊急聯絡人姓名
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="bi bi-person-heart text-gray-400"></i>
                        </div>
                        <input
                            id="emergency_contact"
                            type="text"
                            v-model="form.emergency_contact"
                            class="block w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-gray-50 focus:bg-white"
                            placeholder="請輸入緊急聯絡人姓名"
                        />
                    </div>
                    <div v-if="form.errors.emergency_contact" class="text-sm text-red-600 flex items-center">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        {{ form.errors.emergency_contact }}
                    </div>
                </div>

                <!-- 緊急聯絡人電話 -->
                <div class="space-y-2">
                    <label for="emergency_phone" class="block text-sm font-semibold text-gray-700">
                        緊急聯絡人電話
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="bi bi-phone-vibrate text-gray-400"></i>
                        </div>
                        <input
                            id="emergency_phone"
                            type="tel"
                            v-model="form.emergency_phone"
                            class="block w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-gray-50 focus:bg-white"
                            placeholder="請輸入緊急聯絡人手機號碼"
                            pattern="09[0-9]{8}"
                            maxlength="10"
                        />
                    </div>
                    <div v-if="form.errors.emergency_phone" class="text-sm text-red-600 flex items-center">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        {{ form.errors.emergency_phone }}
                    </div>
                </div>
            </div>

            <!-- 導航按鈕 -->
            <div class="flex items-center justify-between pt-6">
                <button
                    v-if="currentStep > 1"
                    type="button"
                    @click="prevStep"
                    class="inline-flex items-center px-6 py-3 border border-gray-300 rounded-xl text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all duration-200"
                >
                    <i class="bi bi-chevron-left mr-2"></i>
                    上一步
                </button>
                <div v-else></div>

                <div class="flex items-center space-x-4">
                    <Link
                        :href="route('login')"
                        class="text-sm text-gray-600 hover:text-gray-900 transition-colors"
                    >
                        已有帳號？立即登入
                    </Link>

                    <button
                        v-if="currentStep < totalSteps"
                        type="button"
                        @click="nextStep"
                        :disabled="!isStepValid"
                        class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-medium rounded-xl transition-all duration-200 transform hover:scale-[1.02] focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none"
                    >
                        下一步
                        <i class="bi bi-chevron-right ml-2"></i>
                    </button>

                    <button
                        v-if="currentStep === totalSteps"
                        type="submit"
                        :disabled="form.processing || isLoading"
                        class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white font-medium rounded-xl transition-all duration-200 transform hover:scale-[1.02] focus:outline-none focus:ring-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none"
                    >
                        <span v-if="form.processing || isLoading" class="flex items-center">
                            <svg
                                class="animate-spin -ml-1 mr-3 h-5 w-5 text-white"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                            >
                                <circle
                                    class="opacity-25"
                                    cx="12"
                                    cy="12"
                                    r="10"
                                    stroke="currentColor"
                                    stroke-width="4"
                                ></circle>
                                <path
                                    class="opacity-75"
                                    fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                ></path>
                            </svg>
                            註冊中...
                        </span>
                        <span v-else class="flex items-center">
                            <i class="bi bi-check-circle mr-2"></i>
                            完成註冊
                        </span>
                    </button>
                </div>
            </div>
        </form>
    </GuestLayout>
</template>