<script setup>
import InputError from '@/Components/InputError.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';

const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);

const form = useForm({
    password: '',
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;

    nextTick(() => passwordInput.value.focus());
};

const deleteUser = () => {
    form.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;

    form.clearErrors();
    form.reset();
};
</script>

<template>
    <section>
        <div class="row g-4">
            <!-- 危險警告 -->
            <div class="col-12">
                <div class="alert alert-danger d-flex align-items-center">
                    <i class="bi bi-exclamation-triangle-fill fs-2 me-3"></i>
                    <div>
                        <h6 class="mb-1 text-danger">⚠️ 危險操作警告</h6>
                        <small class="mb-0">刪除帳號後所有資料將被永久清除，此操作無法復原！請謹慎考慮。</small>
                    </div>
                </div>
            </div>

            <!-- 刪除說明 -->
            <div class="col-12">
                <div class="card border-danger bg-light">
                    <div class="card-body">
                        <h6 class="text-danger mb-3">
                            <i class="bi bi-info-circle me-2"></i>刪除帳號將會：
                        </h6>
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2 text-muted">
                                <i class="bi bi-x-circle text-danger me-2"></i>永久刪除您的個人資料和設定
                            </li>
                            <li class="mb-2 text-muted">
                                <i class="bi bi-x-circle text-danger me-2"></i>移除您的所有相關紀錄
                            </li>
                            <li class="mb-2 text-muted">
                                <i class="bi bi-x-circle text-danger me-2"></i>取消所有訂閱和服務
                            </li>
                            <li class="text-muted">
                                <i class="bi bi-x-circle text-danger me-2"></i>清除快取和暫存資料
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- 備份提醒 -->
            <div class="col-12">
                <div class="alert alert-warning">
                    <i class="bi bi-cloud-download me-2"></i>
                    <strong>建議在刪除前：</strong>
                    <ul class="mb-0 mt-2">
                        <li>下載您的個人資料備份</li>
                        <li>匯出重要的設定或紀錄</li>
                        <li>確認已取消所有自動續費服務</li>
                    </ul>
                </div>
            </div>

            <!-- 刪除按鈕 -->
            <div class="col-12 text-center">
                <button type="button" class="btn btn-outline-danger px-4" @click="confirmUserDeletion">
                    <i class="bi bi-trash me-2"></i>
                    我了解風險，刪除我的帳號
                </button>
            </div>
        </div>

        <!-- Bootstrap Modal -->
        <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true" :class="{ show: confirmingUserDeletion }" :style="{ display: confirmingUserDeletion ? 'block' : 'none' }">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-danger">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title" id="deleteModalLabel">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            最後確認
                        </h5>
                        <button type="button" class="btn-close btn-close-white" aria-label="Close" @click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="text-center mb-4">
                            <div class="bg-danger bg-opacity-10 rounded-circle mx-auto p-3 mb-3" style="width: 80px; height: 80px;">
                                <i class="bi bi-exclamation-triangle text-danger" style="font-size: 2.5rem;"></i>
                            </div>
                            <h6 class="text-danger">您確定要刪除您的帳號嗎？</h6>
                            <p class="text-muted small">這個操作是不可逆的，所有資料都將被永久刪除。</p>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label fw-bold">
                                <i class="bi bi-key text-danger me-1"></i>
                                請輸入您的密碼以確認刪除
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">
                                    <i class="bi bi-lock"></i>
                                </span>
                                <TextInput
                                    id="password"
                                    ref="passwordInput"
                                    v-model="form.password"
                                    type="password"
                                    class="form-control"
                                    placeholder="請輸入您的密碼"
                                    @keyup.enter="deleteUser"
                                />
                            </div>
                            <InputError :message="form.errors.password" class="invalid-feedback d-block" />
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="closeModal">
                            <i class="bi bi-x-lg me-2"></i>取消
                        </button>
                        <button 
                            type="button" 
                            class="btn btn-danger" 
                            :disabled="form.processing || !form.password"
                            @click="deleteUser"
                        >
                            <i class="bi bi-trash me-2"></i>
                            <span v-if="form.processing">刪除中...</span>
                            <span v-else>確認刪除帳號</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal backdrop -->
        <div v-if="confirmingUserDeletion" class="modal-backdrop fade show" @click="closeModal"></div>
    </section>
</template>
