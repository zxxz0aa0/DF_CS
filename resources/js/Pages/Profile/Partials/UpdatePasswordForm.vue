<script setup>
import InputError from '@/Components/InputError.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const passwordInput = ref(null);
const currentPasswordInput = ref(null);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    form.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: () => {
            if (form.errors.password) {
                form.reset('password', 'password_confirmation');
                passwordInput.value.focus();
            }
            if (form.errors.current_password) {
                form.reset('current_password');
                currentPasswordInput.value.focus();
            }
        },
    });
};

// 密碼強度檢測
const passwordStrength = computed(() => {
    const password = form.password;
    if (!password) return 'weak';
    
    let strength = 0;
    
    // 長度檢查
    if (password.length >= 8) strength += 1;
    if (password.length >= 12) strength += 1;
    
    // 包含小寫字母
    if (/[a-z]/.test(password)) strength += 1;
    
    // 包含大寫字母
    if (/[A-Z]/.test(password)) strength += 1;
    
    // 包含數字
    if (/\d/.test(password)) strength += 1;
    
    // 包含特殊符號
    if (/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password)) strength += 1;
    
    if (strength <= 2) return 'weak';
    if (strength <= 4) return 'medium';
    return 'strong';
});

const passwordStrengthPercentage = computed(() => {
    switch (passwordStrength.value) {
        case 'weak': return 33;
        case 'medium': return 66;
        case 'strong': return 100;
        default: return 0;
    }
});

const passwordStrengthText = computed(() => {
    switch (passwordStrength.value) {
        case 'weak': return '弱';
        case 'medium': return '中等';
        case 'strong': return '強';
        default: return '';
    }
});
</script>

<template>
    <section>
        <form @submit.prevent="updatePassword">
            <div class="row g-4">
                <!-- 安全提醒 -->
                <div class="col-12">
                    <div class="alert alert-info d-flex align-items-center">
                        <i class="bi bi-shield-check fs-4 me-3 text-info"></i>
                        <div>
                            <h6 class="mb-1">密碼安全建議</h6>
                            <small class="mb-0">請使用至少 8 個字元，包含大小寫字母、數字和特殊符號的強密碼</small>
                        </div>
                    </div>
                </div>

                <!-- 目前密碼 -->
                <div class="col-12">
                    <label for="current_password" class="form-label fw-bold">
                        <i class="bi bi-key me-1 text-warning"></i>目前密碼
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-light">
                            <i class="bi bi-lock"></i>
                        </span>
                        <TextInput
                            id="current_password"
                            ref="currentPasswordInput"
                            v-model="form.current_password"
                            type="password"
                            class="form-control"
                            autocomplete="current-password"
                            placeholder="請輸入目前的密碼"
                        />
                    </div>
                    <InputError :message="form.errors.current_password" class="invalid-feedback d-block" />
                </div>

                <!-- 新密碼 -->
                <div class="col-md-6">
                    <label for="password" class="form-label fw-bold">
                        <i class="bi bi-key-fill me-1 text-warning"></i>新密碼
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-light">
                            <i class="bi bi-lock-fill"></i>
                        </span>
                        <TextInput
                            id="password"
                            ref="passwordInput"
                            v-model="form.password"
                            type="password"
                            class="form-control"
                            autocomplete="new-password"
                            placeholder="請輸入新密碼"
                        />
                    </div>
                    <InputError :message="form.errors.password" class="invalid-feedback d-block" />
                    
                    <!-- 密碼強度指示器 -->
                    <div v-if="form.password" class="mt-2">
                        <div class="progress" style="height: 4px;">
                            <div 
                                class="progress-bar" 
                                :class="{
                                    'bg-danger': passwordStrength === 'weak',
                                    'bg-warning': passwordStrength === 'medium', 
                                    'bg-success': passwordStrength === 'strong'
                                }"
                                :style="{ width: passwordStrengthPercentage + '%' }"
                            ></div>
                        </div>
                        <small class="text-muted">
                            密碼強度：{{ passwordStrengthText }}
                        </small>
                    </div>
                </div>

                <!-- 確認新密碼 -->
                <div class="col-md-6">
                    <label for="password_confirmation" class="form-label fw-bold">
                        <i class="bi bi-check2-square me-1 text-warning"></i>確認新密碼
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-light">
                            <i class="bi bi-check2-square"></i>
                        </span>
                        <TextInput
                            id="password_confirmation"
                            v-model="form.password_confirmation"
                            type="password"
                            class="form-control"
                            autocomplete="new-password"
                            placeholder="請再次輸入新密碼"
                        />
                    </div>
                    <InputError :message="form.errors.password_confirmation" class="invalid-feedback d-block" />
                    
                    <!-- 密碼匹配指示 -->
                    <div v-if="form.password_confirmation" class="mt-2">
                        <small v-if="form.password === form.password_confirmation" class="text-success">
                            <i class="bi bi-check-circle me-1"></i>密碼匹配
                        </small>
                        <small v-else class="text-danger">
                            <i class="bi bi-x-circle me-1"></i>密碼不匹配
                        </small>
                    </div>
                </div>

                <!-- 操作按鈕 -->
                <div class="col-12">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-3">
                            <button type="submit" class="btn btn-warning px-4" :disabled="form.processing">
                                <i class="bi bi-shield-check me-2"></i>
                                <span v-if="form.processing">更新中...</span>
                                <span v-else>更新密碼</span>
                            </button>
                            <Transition enter-active-class="fade show" leave-active-class="fade">
                                <div v-if="form.recentlySuccessful" class="alert alert-success py-2 px-3 mb-0 small">
                                    <i class="bi bi-check-circle me-1"></i>密碼已成功更新！
                                </div>
                            </Transition>
                        </div>
                        <small class="text-muted">
                            <i class="bi bi-clock me-1"></i>
                            建議每 3 個月更換一次密碼
                        </small>
                    </div>
                </div>
            </div>
        </form>
    </section>
</template>
