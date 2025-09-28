<template>
  <div class="row g-3 account-selector">
    <div class="col-md-6">
      <label class="form-label" :for="mainId">
        {{ mainLabel }}
        <span v-if="required" class="text-danger">*</span>
      </label>
      <select :id="mainId"
              class="form-select"
              :class="{ 'is-invalid': mainError }"
              :value="mainValue"
              :disabled="disabled"
              @change="onMainChange">
        <option value="">{{ mainPlaceholder }}</option>
        <option v-for="option in mainCategories"
                :key="option.id"
                :value="option.id">
          {{ option.category_code }} - {{ option.category_name }}
        </option>
      </select>
      <div v-if="mainError" class="invalid-feedback">{{ mainError }}</div>
    </div>

    <div class="col-md-6">
      <label class="form-label" :for="subId">
        {{ subLabel }}
        <span v-if="required" class="text-danger">*</span>
      </label>
      <select :id="subId"
              class="form-select"
              :class="{ 'is-invalid': subError }"
              :value="subValue"
              :disabled="disabled || !mainValue || loadingSubCategories"
              @change="onSubChange">
        <option value="">{{ subPlaceholder }}</option>
        <option v-for="option in subCategories"
                :key="option.id"
                :value="option.id">
          {{ option.sub_category_code }} - {{ option.sub_category_name }}
        </option>
      </select>
      <div v-if="subError" class="invalid-feedback">{{ subError }}</div>
      <small v-if="loadingSubCategories" class="text-muted">載入中...</small>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  mainValue: {
    type: [String, Number],
    default: ''
  },
  subValue: {
    type: [String, Number],
    default: ''
  },
  mainCategories: {
    type: Array,
    default: () => []
  },
  subCategories: {
    type: Array,
    default: () => []
  },
  mainLabel: {
    type: String,
    default: '會計科目總類'
  },
  subLabel: {
    type: String,
    default: '會計科目子分類'
  },
  mainPlaceholder: {
    type: String,
    default: '請選擇總類'
  },
  subPlaceholder: {
    type: String,
    default: '請選擇子分類'
  },
  mainError: {
    type: String,
    default: ''
  },
  subError: {
    type: String,
    default: ''
  },
  disabled: {
    type: Boolean,
    default: false
  },
  loadingSubCategories: {
    type: Boolean,
    default: false
  },
  required: {
    type: Boolean,
    default: false
  },
  mainId: {
    type: String,
    default: 'main_category_id'
  },
  subId: {
    type: String,
    default: 'sub_category_id'
  }
})

const emit = defineEmits(['update:mainValue', 'update:subValue'])

const onMainChange = (event) => {
  emit('update:mainValue', event.target.value)
}

const onSubChange = (event) => {
  emit('update:subValue', event.target.value)
}
</script>

<style scoped>
.form-label {
  font-weight: 600;
}
</style>
