<template>
  <div class="page-container">
    <el-card shadow="never">
      <template #header>
        <div class="card-header">
          <span>订单管理</span>
          <div>
            <el-select v-model="filters.platform" clearable placeholder="平台" style="width: 120px; margin-right: 8px">
              <el-option label="微信" value="wechat" />
              <el-option label="抖音" value="douyin" />
              <el-option label="苹果" value="apple" />
            </el-select>
            <el-button type="primary" @click="loadList">查询</el-button>
          </div>
        </div>
      </template>

      <el-table :data="list" v-loading="loading" stripe>
        <el-table-column prop="order_no" label="订单号" width="200" />
        <el-table-column label="用户" width="120">
          <template #default="{ row }">{{ row.user?.nickname || '-' }}</template>
        </el-table-column>
        <el-table-column label="平台" width="80">
          <template #default="{ row }">{{ platformText(row.platform) }}</template>
        </el-table-column>
        <el-table-column label="类型" width="90">
          <template #default="{ row }">{{ typeText(row.product_type) }}</template>
        </el-table-column>
        <el-table-column prop="pay_amount" label="实付(分)" width="100" align="right" />
        <el-table-column label="状态" width="90">
          <template #default="{ row }">
            <el-tag :type="statusType(row.status)">{{ statusText(row.status) }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="pay_time" label="支付时间" width="180" />
        <el-table-column label="操作" width="100">
          <template #default="{ row }">
            <el-button
              v-if="row.status === 1"
              link
              type="danger"
              @click="handleRefund(row)"
            >退款</el-button>
          </template>
        </el-table-column>
      </el-table>

      <el-pagination
        v-model:current-page="page"
        :page-size="20"
        :total="total"
        layout="total, prev, pager, next"
        class="pagination"
        @current-change="loadList"
      />
    </el-card>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import request from '@/utils/request'

const list = ref([])
const loading = ref(false)
const page = ref(1)
const total = ref(0)
const filters = reactive({ platform: null })

const platformText = (p) => ({ wechat: '微信', douyin: '抖音', apple: '苹果' }[p] || p)
const typeText = (t) => ({ recharge: '充值', chapter: '章节', vip: 'VIP' }[t] || t)
const statusText = (s) => ['待付', '已付', '已取消', '已退款'][s] || '-'
const statusType = (s) => ['warning', 'success', 'info', 'danger'][s] || 'info'

const loadList = async () => {
  loading.value = true
  try {
    const res = await request.get('/admin/orders', {
      params: { page: page.value, per_page: 20, ...filters }
    })
    list.value = res.data.data
    total.value = res.data.total
  } finally {
    loading.value = false
  }
}

const handleRefund = (row) => {
  ElMessageBox.confirm(`确定退款订单「${row.order_no}」吗？`, '提示', { type: 'warning' })
    .then(async () => {
      await request.post(`/admin/orders/${row.id}/refund`)
      ElMessage.success('退款成功')
      loadList()
    })
    .catch(() => {})
}

onMounted(loadList)
</script>

<style scoped>
.pagination {
  margin-top: 16px;
  justify-content: flex-end;
}
</style>
