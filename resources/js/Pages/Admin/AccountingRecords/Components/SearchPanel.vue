<template>
  <div class="card">
    <div class="card-header">
      <h3 class="card-title">搜尋功能</h3>
    </div>
    <div class="card-body">
      <div class="row">
        <div class="col-md-3">
          <label>搜尋類型</label>
          <select v-model="form.search_type" class="form-select">
            <option value="name">姓名</option>
            <option value="license">車牌號碼</option>
            <option value="fleet">車隊編號</option>
          </select>
        </div>
        <div class="col-md-6">
          <label>關鍵字</label>
          <input
            v-model="form.keyword"
            type="text"
            class="form-control"
            placeholder="請輸入搜尋關鍵字"
            @keyup.enter="search"
          >
        </div>
        <div class="col-md-3">
          <label>&nbsp;</label>
          <button @click="search" class="btn btn-primary d-block w-100">
            <i class="bi bi-search"></i> 搜尋
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive } from 'vue'

const props = defineProps({
  filters: { type: Object, default: () => ({}) }
})

const emit = defineEmits(['search'])

const form = reactive({
  search_type: props.filters.search_type || 'name',
  keyword: props.filters.keyword || ''
})

const search = () => {
  emit('search', { ...form })
}
</script>
