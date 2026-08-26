<template>
  <div class="page-container">
    <el-card shadow="never">
      <template #header>
        <div class="card-header">
          <span>归因 ROI 分析</span>
          <div>
            <el-date-picker
              v-model="dateRange"
              type="daterange"
              range-separator="至"
              start-placeholder="开始日期"
              end-placeholder="结束日期"
              :value-format="'YYYY-MM-DD'"
              style="margin-right: 8px"
            />
            <el-button type="primary" @click="loadROI">查询</el-button>
          </div>
        </div>
      </template>

      <!-- ROI 汇总表 -->
      <el-table :data="roiList" v-loading="loading" stripe>
        <el-table-column prop="channel_name" label="渠道" />
        <el-table-column prop="clicks" label="点击数" align="right" />
        <el-table-column prop="registrations" label="注册数" align="right" />
        <el-table-column prop="cost" label="投放成本(分)" align="right" />
        <el-table-column prop="revenue" label="收入(分)" align="right" />
        <el-table-column label="ROI" align="right" width="120">
          <template #default="{ row }">
            <el-tag :type="row.roi >= 1 ? 'success' : 'danger'">{{ row.roi }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column label="注册率" align="right" width="100">
          <template #default="{ row }">{{ row.cvr }}%</template>
        </el-table-column>
      </el-table>

      <el-divider content-position="left">归因明细</el-divider>

      <!-- 归因明细 -->
      <el-table :data="attributions" v-loading="detailLoading" stripe>
        <el-table-column prop="id" label="ID" width="60" />
        <el-table-column label="渠道" width="100">
          <template #default="{ row }">{{ row.channel?.name || '-' }}</template>
        </el-table-column>
        <el-table-column label="计划" width="120">
          <template #default="{ row }">{{ row.campaign?.name || '-' }}</template>
        </el-table-column>
        <el-table-column label="用户" width="120">
          <template #default="{ row }">{{ row.user?.nickname || '-' }}</template>
        </el-table-column>
        <el-table-column prop="click_time" label="点击时间" width="180" />
        <el-table-column prop="register_time" label="注册时间" width="180" />
        <el-table-column prop="pay_amount" label="首付金额(分)" align="right" width="120" />
      </el-table>
    </el-card>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import request from '@/utils/request'

const dateRange = ref([])
const roiList = ref([])
const attributions = ref([])
const loading = ref(false)
const detailLoading = ref(false)

const loadROI = async () => {
  if (!dateRange.value || dateRange.value.length !== 2) {
    dateRange.value = [today(-7), today(0)]
  }
  loading.value = true
  try {
    const res = await request.get('/admin/attributions/roi', {
      params: { start: dateRange.value[0], end: dateRange.value[1] }
    })
    roiList.value = res.data
  } finally {
    loading.value = false
  }
  loadDetails()
}

const loadDetails = async () => {
  detailLoading.value = true
  try {
    const res = await request.get('/admin/attributions', {
      params: { per_page: 50 }
    })
    attributions.value = res.data.data
  } finally {
    detailLoading.value = false
  }
}

const today = (offset) => {
  const d = new Date()
  d.setDate(d.getDate() + offset)
  return d.toISOString().split('T')[0]
}

onMounted(loadROI)
</script>
