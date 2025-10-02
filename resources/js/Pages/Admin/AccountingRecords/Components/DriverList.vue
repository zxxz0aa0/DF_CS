<template>
  <div class="card">
    <div class="card-header">
      <h3 class="card-title">駕駛列表</h3>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-hover">
          <thead>
            <tr>
              <th style="width: 50px;">選擇</th>
              <th>姓名</th>
              <th>身分證字號</th>
              <th>生日</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="driver in drivers"
              :key="driver.id"
              :class="{ 'table-active': driver.id === selectedId }"
              @click="selectDriver(driver.id)"
              style="cursor: pointer;"
            >
              <td>
                <input
                  type="radio"
                  :checked="driver.id === selectedId"
                  @change="selectDriver(driver.id)"
                >
              </td>
              <td>{{ driver.name }}</td>
              <td>{{ driver.id_number }}</td>
              <td>{{ formatDate(driver.birthday) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  drivers: { type: Array, required: true },
  selectedId: { type: Number, default: null }
})

const emit = defineEmits(['select'])

const selectDriver = (driverId) => {
  emit('select', driverId)
}

const formatDate = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('zh-TW')
}
</script>
