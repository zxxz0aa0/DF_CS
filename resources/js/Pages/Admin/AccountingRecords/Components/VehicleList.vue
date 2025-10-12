<template>
  <div class="card mt-2">
    <div class="card-header" style="background-color:#B3D9D9;">
      <h3 class="card-title">車輛列表</h3>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-hover">
          <thead>
            <tr>
              <th class="text-center" style="width: 4%;">選擇</th>
              <th style="width: 10%;">車隊編號</th>
              <th style="width: 10%;">車牌號碼</th>
              <th style="width: 15%;">車主名稱</th>
              <th style="width: 10%;">廠牌</th>
              <th style="width: 11%;">款式</th>
              <th style="width: 11%;">顏色</th>
              <th style="width: 11%;">出廠日</th>
              <th style="width: 15%;">入籍日期</th>
              <th style="width: 10%;">操作</th>
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
              <td class="text-center">
                <input
                  type="radio"
                  :checked="vehicle.id === selectedId"
                  @change="selectVehicle(vehicle.id)"
                >
              </td>
              <td>{{ vehicle.fleet_number || '-' }}</td>
              <td>{{ vehicle.license_number }}</td>
              <td>{{ vehicle.owner_name || '-' }}</td>
              <td>{{ vehicle.brand || '-' }}</td>
              <td>{{ vehicle.vehicle_model || '-' }}</td>
              <td>{{ vehicle.vehicle_color }}</td>
              <td>{{ formatManufactureDate(vehicle.manufacture_year, vehicle.manufacture_month) }}</td>
              <td>{{ vehicle.registration_date || '-' }}</td>
              <td>
                <button
                  @click.stop="viewDetail(vehicle)"
                  class="btn btn-sm btn-info"
                  title="檢視詳細資料"
                >
                  <i class="bi bi-eye"></i>
                </button>
              </td>
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

const emit = defineEmits(['select', 'view-detail'])

const selectVehicle = (vehicleId) => {
  emit('select', vehicleId)
}

const viewDetail = (vehicle) => {
  emit('view-detail', vehicle)
}

const formatManufactureDate = (year, month) => {
  if (!year && !month) return '-'
  if (year && month) return `${year}年${month}月`
  if (year) return `${year}年`
  if (month) return `${month}月`
  return '-'
}

</script>
