<template>
  <div class="page-container">
    <!-- 概览卡片 -->
    <el-row :gutter="16">
      <el-col v-for="card in cards" :key="card.label" :span="4">
        <el-card shadow="hover" class="stat-card">
          <div class="stat-label">{{ card.label }}</div>
          <div class="stat-value">{{ card.value }}</div>
        </el-card>
      </el-col>
    </el-row>

    <!-- 图表 -->
    <el-row :gutter="16" class="mt-16">
      <el-col :span="16">
        <el-card shadow="hover">
          <template #header>
            <div class="card-header">
              <span>近 7 天收入趋势</span>
              <el-radio-group v-model="trendType" size="small" @change="loadTrend">
                <el-radio-button value="revenue">收入</el-radio-button>
                <el-radio-button value="dau">DAU</el-radio-button>
                <el-radio-button value="roi">ROI</el-radio-button>
              </el-radio-group>
            </div>
          </template>
          <div ref="trendChartRef" class="chart"></div>
        </el-card>
      </el-col>
      <el-col :span="8">
        <el-card shadow="hover">
          <template #header><span>书籍排行 TOP10</span></template>
          <el-table :data="bookRanking" size="small" :show-header="false">
            <el-table-column label="排名" width="60">
              <template #default="{ $index }">
                <span :class="{ 'rank-top': $index < 3 }">{{ $index + 1 }}</span>
              </template>
            </el-table-column>
            <el-table-column prop="title" label="书名" />
            <el-table-column prop="total_views" label="阅读量" width="90" align="right" />
          </el-table>
        </el-card>
      </el-col>
    </el-row>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import * as echarts from 'echarts'
import request from '@/utils/request'

const cards = ref([])
const trendChartRef = ref()
const trendType = ref('revenue')
const bookRanking = ref([])
let trendChart = null

// 加载概览
const loadOverview = async () => {
  const res = await request.get('/admin/dashboard/overview')
  const data = res.data
  cards.value = [
    { label: '今日 DAU', value: data.dau },
    { label: '今日新增用户', value: data.new_users },
    { label: '今日收入(分)', value: data.pay_amount },
    { label: '今日广告(分)', value: data.ad_revenue },
    { label: '今日订单数', value: data.orders }
  ]
}

// 加载趋势
const loadTrend = async () => {
  const res = await request.get('/admin/dashboard/trend', {
    params: { days: 7, type: trendType.value }
  })
  const dates = res.data.map((d) => d.date)
  const values = res.data.map((d) => d.value)
  renderTrend(dates, values)
}

// 渲染趋势图
const renderTrend = (dates, values) => {
  if (!trendChart) {
    trendChart = echarts.init(trendChartRef.value)
  }
  trendChart.setOption({
    tooltip: { trigger: 'axis' },
    grid: { left: 50, right: 20, top: 30, bottom: 30 },
    xAxis: { type: 'category', data: dates },
    yAxis: { type: 'value' },
    series: [
      {
        type: 'line',
        smooth: true,
        areaStyle: { opacity: 0.2 },
        data: values,
        itemStyle: { color: '#409EFF' }
      }
    ]
  })
}

// 加载排行
const loadRanking = async () => {
  const res = await request.get('/admin/dashboard/book-ranking', {
    params: { type: 'views', limit: 10 }
  })
  bookRanking.value = res.data
}

onMounted(() => {
  loadOverview()
  loadTrend()
  loadRanking()
  window.addEventListener('resize', () => trendChart && trendChart.resize())
})

onBeforeUnmount(() => {
  window.removeEventListener('resize', () => trendChart && trendChart.resize())
  trendChart && trendChart.dispose()
})
</script>

<style scoped>
.stat-card {
  text-align: center;
}
.stat-label {
  color: #909399;
  font-size: 13px;
  margin-bottom: 8px;
}
.stat-value {
  font-size: 24px;
  font-weight: bold;
  color: #303133;
}
.chart {
  height: 320px;
}
.mt-16 {
  margin-top: 16px;
}
.rank-top {
  color: #e6a23c;
  font-weight: bold;
}
</style>
