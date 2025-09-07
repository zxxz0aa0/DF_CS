<script setup>
import { ref } from "vue";
import GuestLayout from "@/Layouts/GuestLayout.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    username: "",
    password: "",
    remember: false,
});

const showPassword = ref(false);
const isLoading = ref(false);

const submit = () => {
    isLoading.value = true;
    form.post(route("login"), {
        onFinish: () => {
            form.reset("password");
            isLoading.value = false;
        },
    });
};

const togglePassword = () => {
    showPassword.value = !showPassword.value;
};

// 預設帳號快速填入
const fillDemoAccount = (type) => {
    if (type === "admin") {
        form.username = "admin";
        form.password = "password123";
    } else {
        form.username = "user";
        form.password = "password123";
    }
};
</script>

<template>
    <GuestLayout>
        <Head title="登入 - DF管理系統" />

        <!-- 狀態訊息 -->
        <div
            v-if="status"
            class="mb-6 p-4 text-sm font-medium text-green-700 bg-green-50 border border-green-200 rounded-xl"
        >
            <div class="flex items-center">
                <i class="bi bi-check-circle me-2"></i>
                {{ status }}
            </div>
        </div>

        <!-- 登入表單 -->
        <form @submit.prevent="submit" class="space-y-6">
            <!-- 帳號 -->
            <div class="space-y-2">
                <label
                    for="username"
                    class="block text-sm font-semibold text-gray-700"
                >
                    帳號
                </label>
                <div class="relative">
                    <div
                        class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
                    >
                        <i class="bi bi-person text-gray-400"></i>
                    </div>
                    <input
                        id="username"
                        type="text"
                        v-model="form.username"
                        class="block w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-gray-50 focus:bg-white"
                        placeholder="請輸入您的帳號"
                        required
                        autofocus
                        autocomplete="username"
                    />
                </div>
                <div
                    v-if="form.errors.username"
                    class="text-sm text-red-600 flex items-center"
                >
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    {{ form.errors.username }}
                </div>
            </div>

            <!-- 密碼 -->
            <div class="space-y-2">
                <label
                    for="password"
                    class="block text-sm font-semibold text-gray-700"
                >
                    密碼
                </label>
                <div class="relative">
                    <div
                        class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
                    >
                        <i class="bi bi-lock text-gray-400"></i>
                    </div>
                    <input
                        id="password"
                        :type="showPassword ? 'text' : 'password'"
                        v-model="form.password"
                        class="block w-full pl-10 pr-12 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-gray-50 focus:bg-white"
                        placeholder="請輸入您的密碼"
                        required
                        autocomplete="current-password"
                    />
                    <button
                        type="button"
                        @click="togglePassword"
                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none"
                    >
                        <i
                            :class="
                                showPassword ? 'bi bi-eye-slash' : 'bi bi-eye'
                            "
                        ></i>
                    </button>
                </div>
                <div
                    v-if="form.errors.password"
                    class="text-sm text-red-600 flex items-center"
                >
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    {{ form.errors.password }}
                </div>
            </div>

            <!-- 記住我和忘記密碼 -->
            <div class="flex items-center justify-between">
                <label class="flex items-center cursor-pointer">
                    <input
                        type="checkbox"
                        name="remember"
                        v-model="form.remember"
                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded transition-colors"
                    />
                    <span class="ml-2 text-sm text-gray-600">記住我</span>
                </label>

                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="text-sm text-blue-600 hover:text-blue-800 font-medium transition-colors"
                >
                    忘記密碼？
                </Link>
            </div>

            <!-- 登入按鈕 -->
            <button
                type="submit"
                :disabled="form.processing || isLoading"
                class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold py-3 px-4 rounded-xl transition-all duration-200 transform hover:scale-[1.02] focus:ring-4 focus:ring-blue-500/50 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none flex items-center justify-center"
            >
                <span
                    v-if="form.processing || isLoading"
                    class="flex items-center"
                >
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
                    登入中...
                </span>
                <span v-else class="flex items-center">
                    <i class="bi bi-box-arrow-in-right mr-2"></i>
                    登入
                </span>
            </button>
        </form>

        <!-- 分隔線 -->
        <div class="my-6">
            <div class="relative">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-300"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="bg-white px-2 text-gray-500">快速體驗</span>
                </div>
            </div>
        </div>

        <!-- 示範帳號 -->
        <div class="grid grid-cols-2 gap-3">
            <button
                type="button"
                @click="fillDemoAccount('admin')"
                class="flex items-center justify-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors"
            >
                <i class="bi bi-shield-check me-2 text-blue-600"></i>
                管理員
            </button>
            <button
                type="button"
                @click="fillDemoAccount('user')"
                class="flex items-center justify-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors"
            >
                <i class="bi bi-person me-2 text-green-600"></i>
                使用者
            </button>
        </div>

        <!-- 註冊連結 -->
        <div class="text-center mt-6">
            <p class="text-sm text-gray-600">
                還沒有帳號？
                <Link
                    :href="route('register')"
                    class="font-medium text-blue-600 hover:text-blue-800 transition-colors"
                >
                    立即註冊
                </Link>
            </p>
        </div>
    </GuestLayout>
</template>
