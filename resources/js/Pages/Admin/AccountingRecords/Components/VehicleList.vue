<template>
  <div class="card">
    <div class="card-header">
      <h3 class="card-title">車輛列表</h3>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-hover">
          <thead>
            <tr>
              <th style="width: 50px;">選擇</th>
              <th>車牌號碼</th>
              <th>車隊編號</th>
              <th>車主名稱</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="vehicle in vehicles"
              :key="vehicle.id"
              :class="{ 'table-active': vehicle.id === selectedId }"
              @click="selectVehicle(vehicle.id)"
              style="cursor: pointer;"
            >
              <td>
                <input
                  type="radio"
                  :checked="vehicle.id === selectedId"
                  @change="selectVehicle(vehicle.id)"
                >
              </td>
              <td>{{ vehicle.license_number }}</td>
              <td>{{ vehicle.fleet_number || '-' }}</td>
              <td>{{ vehicle.owner_name || '-' }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  vehicles: { type: Array, required: true },
  selectedId: { type: Number, default: null }
})

const emit = defineEmits(['select'])

const selectVehicle = (vehicleId) => {
  emit('select', vehicleId)
}
</script>
