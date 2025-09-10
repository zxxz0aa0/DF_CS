<script setup>
import InputError from '@/Components/InputError.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const user = usePage().props.auth.user;

const toYMD = (d) => {
    if (!d) return ''
    const date = typeof d === 'string' && d.length === 10 ? new Date(d + 'T00:00:00') : new Date(d)
    if (isNaN(date.getTime())) return ''
    return new Date(date.getTime() - date.getTimezoneOffset() * 60000).toISOString().slice(0, 10)
}

const form = useForm({
    name: user.name ?? '',
    username: user.username ?? '',
    email: user.email ?? '',
    birth_date: toYMD(user.birth_date) ?? '',
    gender: user.gender ?? 'other',
    mobile_phone: user.mobile_phone ?? '',
    home_phone: user.home_phone ?? '',
    address: user.address ?? '',
});
</script>

<template>
    <section>
        <form @submit.prevent="form.patch(route('profile.update'))">
            <div class="row g-3">
                <!-- 基本資訊 -->
                <div class="col-12">
                    <h6 class="text-primary mb-3">
                        <i class="bi bi-info-circle me-2"></i>基本資訊
                    </h6>
                </div>

                <div class="col-md-6">
                    <label for="name" class="form-label fw-bold">
                        <i class="bi bi-person me-1 text-primary"></i>姓名
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="bi bi-person"></i></span>
                        <TextInput id="name" type="text" class="form-control" v-model="form.name" required autocomplete="name" placeholder="請輸入您的姓名" />
                    </div>
                    <InputError class="invalid-feedback d-block" :message="form.errors.name" />
                </div>

                <div class="col-md-6">
                    <label for="username" class="form-label fw-bold">
                        <i class="bi bi-at me-1 text-primary"></i>使用者名稱
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="bi bi-at"></i></span>
                        <TextInput id="username" type="text" class="form-control" v-model="form.username" required autocomplete="username" placeholder="請輸入使用者名稱" />
                    </div>
                    <InputError class="invalid-feedback d-block" :message="form.errors.username" />
                </div>

                <div class="col-md-6">
                    <label for="email" class="form-label fw-bold">
                        <i class="bi bi-envelope me-1 text-primary"></i>電子信箱
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="bi bi-envelope"></i></span>
                        <TextInput id="email" type="email" class="form-control" v-model="form.email" required placeholder="請輸入電子信箱" />
                    </div>
                    <InputError class="invalid-feedback d-block" :message="form.errors.email" />
                    
                    <!-- 信箱驗證提醒 -->
                    <div v-if="mustVerifyEmail && user.email_verified_at === null" class="mt-2">
                        <div class="alert alert-warning alert-dismissible small">
                            <i class="bi bi-exclamation-triangle me-1"></i>
                            您的電子信箱尚未驗證。
                            <Link :href="route('verification.send')" method="post" as="button" class="btn btn-link btn-sm p-0 ms-1 text-decoration-underline">
                                點此重新寄送驗證信
                            </Link>
                        </div>
                        <div v-show="status === 'verification-link-sent'" class="alert alert-success small">
                            <i class="bi bi-check-circle me-1"></i>驗證信已重新寄送。
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <label for="birth_date" class="form-label fw-bold">
                        <i class="bi bi-calendar-date me-1 text-primary"></i>出生日期
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="bi bi-calendar-date"></i></span>
                        <TextInput id="birth_date" type="date" class="form-control" v-model="form.birth_date" />
                    </div>
                    <InputError class="invalid-feedback d-block" :message="form.errors.birth_date" />
                </div>

                <!-- 個人資訊 -->
                <div class="col-12 mt-4">
                    <h6 class="text-primary mb-3">
                        <i class="bi bi-person-lines-fill me-2"></i>個人資訊
                    </h6>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">
                        <i class="bi bi-gender-ambiguous me-1 text-primary"></i>性別
                    </label>
                    <div class="mt-2">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="gender" id="male" value="male" v-model="form.gender">
                            <label class="form-check-label" for="male">
                                <i class="bi bi-gender-male me-1"></i>男性
                            </label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="gender" id="female" value="female" v-model="form.gender">
                            <label class="form-check-label" for="female">
                                <i class="bi bi-gender-female me-1"></i>女性
                            </label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="gender" id="other" value="other" v-model="form.gender">
                            <label class="form-check-label" for="other">
                                <i class="bi bi-question-circle me-1"></i>其他
                            </label>
                        </div>
                    </div>
                    <InputError class="invalid-feedback d-block" :message="form.errors.gender" />
                </div>

                <!-- 聯絡資訊 -->
                <div class="col-12 mt-4">
                    <h6 class="text-primary mb-3">
                        <i class="bi bi-telephone me-2"></i>聯絡資訊
                    </h6>
                </div>

                <div class="col-md-6">
                    <label for="mobile_phone" class="form-label fw-bold">
                        <i class="bi bi-phone me-1 text-primary"></i>手機號碼
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="bi bi-phone"></i></span>
                        <TextInput id="mobile_phone" type="tel" class="form-control" v-model="form.mobile_phone" placeholder="09xxxxxxxx" />
                    </div>
                    <InputError class="invalid-feedback d-block" :message="form.errors.mobile_phone" />
                </div>

                <div class="col-md-6">
                    <label for="home_phone" class="form-label fw-bold">
                        <i class="bi bi-telephone me-1 text-primary"></i>市內電話
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="bi bi-telephone"></i></span>
                        <TextInput id="home_phone" type="tel" class="form-control" v-model="form.home_phone" placeholder="02xxxxxxx 或 0x-xxxxxxxx" />
                    </div>
                    <InputError class="invalid-feedback d-block" :message="form.errors.home_phone" />
                </div>

                <div class="col-12">
                    <label for="address" class="form-label fw-bold">
                        <i class="bi bi-geo-alt me-1 text-primary"></i>聯絡地址
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="bi bi-geo-alt"></i></span>
                        <textarea id="address" class="form-control" rows="3" v-model="form.address" placeholder="請輸入詳細地址..."></textarea>
                    </div>
                    <InputError class="invalid-feedback d-block" :message="form.errors.address" />
                </div>

                <!-- 操作按鈕 -->
                <div class="col-12 mt-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-3">
                            <button type="submit" class="btn btn-primary px-4" :disabled="form.processing">
                                <i class="bi bi-check-lg me-2"></i>
                                <span v-if="form.processing">儲存中...</span>
                                <span v-else>儲存變更</span>
                            </button>
                            <Transition enter-active-class="fade show" leave-active-class="fade">
                                <div v-if="form.recentlySuccessful || status === 'profile-updated'" class="alert alert-success py-2 px-3 mb-0 small">
                                    <i class="bi bi-check-circle me-1"></i>設定已成功儲存！
                                </div>
                            </Transition>
                        </div>
                        <small class="text-muted">
                            <i class="bi bi-info-circle me-1"></i>
                            最後更新：{{ new Date().toLocaleDateString('zh-TW') }}
                        </small>
                    </div>
                </div>
            </div>
        </form>
    </section>
</template>
