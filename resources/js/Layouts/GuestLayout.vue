<script setup>
// 現代化登入版面，不需要額外的 imports
</script>

<template>
    <!-- 現代化登入版面設計 -->
    <div
        class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-indigo-100 flex items-center justify-center p-4"
    >
        <!-- 主要容器 -->
        <div class="w-full max-w-md">
            <!-- Logo 區域 -->
            <div class="text-center mb-8">
                <h1 class="text-2xl font-bold text-gray-900 mb-2">歡迎回來</h1>
                <p class="text-gray-600">請登入您的帳號以繼續</p>
            </div>

            <!-- 登入卡片 -->
            <div
                class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl border border-white/20 p-8"
            >
                <slot />
            </div>

            <!-- 底部資訊 -->
            <div class="text-center mt-6">
                <p class="text-sm text-gray-500">
                    © {{ new Date().getFullYear() }} DF管理系統. 保留所有權利.
                </p>
            </div>
        </div>

        <!-- 背景裝飾元素 -->
        <div class="fixed inset-0 -z-10">
            <!-- 裝飾圓圈 -->
            <div
                class="absolute top-20 right-20 w-64 h-64 bg-blue-200/30 rounded-full mix-blend-multiply filter blur-xl animate-pulse"
            ></div>
            <div
                class="absolute bottom-20 left-20 w-64 h-64 bg-indigo-200/30 rounded-full mix-blend-multiply filter blur-xl animate-pulse"
                style="animation-delay: 2s"
            ></div>
            <div
                class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-purple-200/20 rounded-full mix-blend-multiply filter blur-2xl"
            ></div>
        </div>
    </div>
</template>
