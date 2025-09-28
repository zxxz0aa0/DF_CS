<template>
  <div class="account-code-input">
    <label v-if="label" class="form-label" :for="inputId">
      {{ label }}
      <span v-if="required" class="text-danger">*</span>
    </label>
    <div class="input-group">
      <input :id="inputId"
             :value="modelValue"
             type="text"
             class="form-control"
             :class="{ 'is-invalid': error }"
             :placeholder="placeholder"
             :disabled="disabled"
             @input="onInput">
      <button type="button"
              class="btn btn-outline-secondary"
              :disabled="disabled || generating"
              @click="emit('generate')">
        <i class="bi" :class="generating ? 'bi-arrow-clockwise' : 'bi-gear'"></i>
        {{ generating ? '產生中...' : generateText }}
      </button>
    </div>
    <div v-if="error" class="invalid-feedback d-block">{{ error }}</div>
    <small v-if="guidance" class="form-text text-muted">{{ guidance }}</small>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  modelValue: {
    type: [String, Number],
    default: ''
  },
  label: {
    type: String,
    default: ''
  },
  placeholder: {
    type: String,
    default: ''
  },
  error: {
    type: String,
    default: ''
  },
  disabled: {
    type: Boolean,
    default: false
  },
  generating: {
    type: Boolean,
    default: false
  },
  guidance: {
    type: String,
    default: ''
  },
  required: {
    type: Boolean,
    default: false
  },
  inputId: {
    type: String,
    default: 'account_code'
  },
  generateText: {
    type: String,
    default: '自動產生'
  }
})

const emit = defineEmits(['update:modelValue', 'generate'])

const onInput = (event) => {
  emit('update:modelValue', event.target.value)
}
</script>

<style scoped>
.form-label {
  font-weight: 600;
}
</style>
