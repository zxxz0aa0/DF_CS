<template>
    <div class="date-input-wrapper">
        <input
            :id="id"
            v-model="displayValue"
            type="text"
            :class="inputClass"
            :placeholder="placeholder"
            :required="required"
            :disabled="disabled"
            @input="handleInput"
            @blur="handleBlur"
            @focus="handleFocus"
            maxlength="10"
        >
        <small v-if="westernYear && displayValue" class="form-text text-muted">
            西元年：{{ westernYear }}
        </small>
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'

const props = defineProps({
    modelValue: {
        type: String,
        default: ''
    },
    id: {
        type: String,
        default: ''
    },
    class: {
        type: [String, Object, Array],
        default: 'form-control'
    },
    placeholder: {
        type: String,
        default: '111/01/01'
    },
    required: {
        type: Boolean,
        default: false
    },
    disabled: {
        type: Boolean,
        default: false
    }
})

const emit = defineEmits(['update:modelValue'])

const displayValue = ref('')
const isFocused = ref(false)

const inputClass = computed(() => {
    if (typeof props.class === 'string') {
        return props.class
    }
    return props.class
})

// 計算西元年
const westernYear = computed(() => {
    if (!displayValue.value || displayValue.value.length < 8) return null
    
    const parts = displayValue.value.split('/')
    if (parts.length !== 3) return null
    
    const rocYear = parseInt(parts[0])
    const month = parts[1].padStart(2, '0')
    const day = parts[2].padStart(2, '0')
    
    if (isNaN(rocYear) || rocYear <= 0) return null
    
    const westernYearValue = rocYear + 1911
    return `${westernYearValue}/${month}/${day}`
})

// 將西元年轉為民國年顯示格式
const formatToROCDisplay = (westernDate) => {
    if (!westernDate) return ''
    
    const date = new Date(westernDate)
    if (isNaN(date.getTime())) return ''
    
    const rocYear = date.getFullYear() - 1911
    const month = (date.getMonth() + 1).toString()
    const day = date.getDate().toString()
    
    return `${rocYear}/${month}/${day}`
}

// 將民國年顯示格式轉為西元年ISO格式
const formatToWesternISO = (rocDisplay) => {
    if (!rocDisplay || rocDisplay.trim() === '') return null
    
    const parts = rocDisplay.split('/')
    if (parts.length !== 3) return null
    
    const rocYear = parseInt(parts[0])
    const month = parseInt(parts[1])
    const day = parseInt(parts[2])
    
    if (isNaN(rocYear) || isNaN(month) || isNaN(day)) return null
    if (rocYear <= 0 || month < 1 || month > 12 || day < 1 || day > 31) return null
    
    const westernYear = rocYear + 1911
    const date = new Date(westernYear, month - 1, day)
    
    // 驗證日期有效性
    if (date.getFullYear() !== westernYear || 
        date.getMonth() !== month - 1 || 
        date.getDate() !== day) {
        return null
    }
    
    return date.toISOString().split('T')[0]
}

// 自動格式化輸入
const formatInput = (value) => {
    // 移除所有非數字字符
    const numbers = value.replace(/\D/g, '')
    
    if (numbers.length === 0) return ''
    
    // 自動插入斜線
    if (numbers.length <= 3) {
        return numbers
    } else if (numbers.length <= 5) {
        return numbers.substring(0, 3) + '/' + numbers.substring(3)
    } else {
        return numbers.substring(0, 3) + '/' + 
               numbers.substring(3, 5) + '/' + 
               numbers.substring(5, 7)
    }
}

const handleInput = (event) => {
    const formatted = formatInput(event.target.value)
    displayValue.value = formatted
    event.target.value = formatted
    
    // 如果格式完整，發送西元年格式給父組件
    const westernDate = formatToWesternISO(formatted)
    emit('update:modelValue', westernDate)
}

const handleFocus = () => {
    isFocused.value = true
}

const handleBlur = () => {
    isFocused.value = false
    
    // 補齊位數
    if (displayValue.value) {
        const parts = displayValue.value.split('/')
        if (parts.length === 3) {
            const rocYear = parts[0]
            const month = parts[1].padStart(2, '0')
            const day = parts[2].padStart(2, '0')
            
            displayValue.value = `${rocYear}/${month}/${day}`
            
            const westernDate = formatToWesternISO(displayValue.value)
            emit('update:modelValue', westernDate)
        }
    }
}

// 監聽外部值變化
watch(() => props.modelValue, (newValue) => {
    if (!isFocused.value) {
        if (newValue) {
            displayValue.value = formatToROCDisplay(newValue)
        } else {
            displayValue.value = ''
        }
    }
}, { immediate: true })

onMounted(() => {
    if (props.modelValue) {
        displayValue.value = formatToROCDisplay(props.modelValue)
    }
})
</script>

<style scoped>
.date-input-wrapper {
    position: relative;
}

.form-text {
    font-size: 0.75rem;
    margin-top: 0.25rem;
}
</style>