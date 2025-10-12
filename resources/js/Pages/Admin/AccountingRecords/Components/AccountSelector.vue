<template>
  <div class="position-relative" ref="inputContainer">
    <div class="input-group input-group-sm">
      <input
        v-model="searchKeyword"
        type="text"
        class="form-control"
        placeholder="輸入科目編號"
        @input="handleInput"
        @focus="handleFocus"
      >
      <button
        @click="openModal"
        class="btn btn-outline-secondary"
        type="button"
      >
        <i class="bi bi-search"></i>
      </button>
    </div>

    <!-- 自動完成下拉選單 - 使用 Teleport 渲染到 body -->
    <Teleport to="body">
      <div
        v-if="showDropdown && filteredAccounts.length > 0"
        class="dropdown-menu show position-fixed"
        :style="dropdownStyle"
      >
        <a
          v-for="account in filteredAccounts"
          :key="account.id"
          href="#"
          class="dropdown-item"
          @click.prevent="selectAccount(account)"
        >
          <strong>{{ account.account_code }}</strong> - {{ account.account_name }}
          <br>
          <small class="text-muted">
            {{ account.main_category_name }} / {{ account.sub_category_name }}
          </small>
        </a>
      </div>
    </Teleport>

    <!-- 模態框選擇器 -->
    <div
      v-if="showModal"
      class="modal fade show d-block"
      tabindex="-1"
      style="background-color: rgba(0,0,0,0.5);"
      @click.self="closeModal"
    >
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">選擇會計科目</h5>
            <button
              type="button"
              class="btn-close"
              @click="closeModal"
            ></button>
          </div>
          <div class="modal-body">
            <input
              v-model="modalSearchKeyword"
              type="text"
              class="form-control mb-3"
              placeholder="搜尋科目編號或名稱"
              @input="searchInModal"
            >
            <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
              <table class="table table-hover">
                <thead>
                  <tr>
                    <th>科目編號</th>
                    <th>科目名稱</th>
                    <th>總類</th>
                    <th>子分類</th>
                  </tr>
                </thead>
                <tbody>
                  <tr
                    v-for="account in modalAccounts"
                    :key="account.id"
                    @click="selectAccount(account)"
                    style="cursor: pointer;"
                  >
                    <td>{{ account.account_code }}</td>
                    <td>{{ account.account_name }}</td>
                    <td>{{ account.main_category_name }}</td>
                    <td>{{ account.sub_category_name }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, computed, onMounted, onUnmounted, nextTick } from 'vue'
import axios from 'axios'

const props = defineProps({
  modelValue: { type: Number, default: null }
})

const emit = defineEmits(['update:modelValue', 'select'])

const inputContainer = ref(null)
const searchKeyword = ref('')
const filteredAccounts = ref([])
const showDropdown = ref(false)
const dropdownPosition = ref({ top: 0, left: 0, width: 0 })

const showModal = ref(false)
const modalSearchKeyword = ref('')
const modalAccounts = ref([])

// 計算下拉選單的位置和樣式
const dropdownStyle = computed(() => {
  return {
    top: `${dropdownPosition.value.top}px`,
    left: `${dropdownPosition.value.left}px`,
    width: `${dropdownPosition.value.width}px`,
    maxHeight: '300px',
    overflowY: 'auto',
    zIndex: '1050'
  }
})

// 更新下拉選單位置
const updateDropdownPosition = () => {
  if (inputContainer.value) {
    const rect = inputContainer.value.getBoundingClientRect()

    // 使用 position: fixed，所以不需要加上 scrollY/scrollX
    dropdownPosition.value = {
      top: rect.bottom,
      left: rect.left,
      width: rect.width
    }
  }
}

// 監聽 modelValue 變化，當被清空時也清空輸入框
watch(() => props.modelValue, (newValue) => {
  if (newValue === null || newValue === undefined) {
    searchKeyword.value = ''
    filteredAccounts.value = []
    showDropdown.value = false
  }
})

// 處理 focus 事件
const handleFocus = () => {
  updateDropdownPosition()
  if (filteredAccounts.value.length > 0) {
    showDropdown.value = true
  }
}

// 處理輸入 - 自動完成
const handleInput = async () => {
  if (searchKeyword.value.length === 0) {
    filteredAccounts.value = []
    showDropdown.value = false
    return
  }

  try {
    const response = await axios.get(
      route('admin.accounting.api.account-details.search'),
      { params: { keyword: searchKeyword.value } }
    )
    filteredAccounts.value = response.data
    showDropdown.value = true

    // 等待 DOM 更新後再計算位置
    await nextTick()
    updateDropdownPosition()
  } catch (error) {
    console.error('搜尋失敗:', error)
  }
}

// 模態框搜尋
const searchInModal = async () => {
  if (modalSearchKeyword.value.length === 0) {
    modalAccounts.value = []
    return
  }

  try {
    const response = await axios.get(
      route('admin.accounting.api.account-details.search'),
      { params: { keyword: modalSearchKeyword.value } }
    )
    modalAccounts.value = response.data
  } catch (error) {
    console.error('搜尋失敗:', error)
  }
}

// 選擇科目
const selectAccount = (account) => {
  searchKeyword.value = account.account_code
  emit('update:modelValue', account.id)
  emit('select', account)
  showDropdown.value = false
  closeModal()
}

// 開啟模態框
const openModal = () => {
  showModal.value = true
  modalSearchKeyword.value = ''
  modalAccounts.value = []
}

// 關閉模態框
const closeModal = () => {
  showModal.value = false
}

// 點擊外部關閉下拉選單
const handleClickOutside = (e) => {
  if (inputContainer.value && !inputContainer.value.contains(e.target)) {
    // 檢查是否點擊在下拉選單內
    const dropdownElement = document.querySelector('.dropdown-menu.show.position-fixed')
    if (!dropdownElement || !dropdownElement.contains(e.target)) {
      showDropdown.value = false
    }
  }
}

// 滾動和視窗大小改變時更新位置
const handleScrollOrResize = () => {
  if (showDropdown.value) {
    updateDropdownPosition()
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
  window.addEventListener('scroll', handleScrollOrResize, true)
  window.addEventListener('resize', handleScrollOrResize)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
  window.removeEventListener('scroll', handleScrollOrResize, true)
  window.removeEventListener('resize', handleScrollOrResize)
})
</script>
