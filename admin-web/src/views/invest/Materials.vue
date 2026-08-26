<template>
  <div class="page-container">
    <el-card shadow="never">
      <template #header>
        <div class="card-header">
          <span>素材管理</span>
        </div>
      </template>

      <el-table :data="list" v-loading="loading">
        <el-table-column prop="id" label="ID" width="60" />
        <el-table-column label="素材" width="120">
          <template #default="{ row }">
            <el-image
              v-if="row.type === 1"
              :src="row.cdn_url"
              fit="cover"
              style="width: 60px; height: 60px"
              :preview-src-list="[row.cdn_url]"
            />
            <el-icon v-else size="30"><VideoPlay /></el-icon>
          </template>
        </el-table-column>
        <el-table-column prop="name" label="素材名称" />
        <el-table-column label="类型" width="70">
          <template #default="{ row }">
            <el-tag :type="row.type === 1 ? 'info' : 'success'">
              {{ row.type === 1 ? '图片' : '视频' }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="所属计划" width="120">
          <template #default="{ row }">{{ row.campaign?.name || '-' }}</template>
        </el-table-column>
        <el-table-column label="文件大小" width="100">
          <template #default="{ row }">{{ formatSize(row.file_size) }}</template>
        </el-table-column>
        <el-table-column label="状态" width="90">
          <template #default="{ row }">
            <el-tag :type="statusType(row.status)">{{ statusText(row.status) }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column label="操作" width="150">
          <template #default="{ row }">
            <el-button v-if="row.status === 0" link type="success" @click="audit(row, 'pass')">通过</el-button>
            <el-button v-if="row.status === 0" link type="danger" @click="audit(row, 'reject')">驳回</el-button>
            <el-button link type="danger" @click="handleDelete(row)">删除</el-button>
          </template>
        </el-table-column>
      </el-table>
    </el-card>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import request from '@/utils/request'
import { VideoPlay } from '@element-plus/icons-vue'

const list = ref([])
const loading = ref(false)

const statusText = (s) => ['待审', '通过', '驳回', '已删除'][s] || '-'
const statusType = (s) => ['warning', 'success', 'danger', 'info'][s] || 'info'

const formatSize = (bytes) => {
  if (!bytes) return '-'
  if (bytes < 1024) return bytes + 'B'
  if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + 'KB'
  return (bytes / 1024 / 1024).toFixed(1) + 'MB'
}

const loadList = async () => {
  loading.value = true
  try {
    const res = await request.get('/admin/materials', { params: { per_page: 100 } })
    list.value = res.data.data
  } finally {
    loading.value = false
  }
}

const audit = async (row, action) => {
  await request.post(`/admin/materials/${row.id}/audit`, { action })
  ElMessage.success(action === 'pass' ? '已通过' : '已驳回')
  loadList()
}

const handleDelete = (row) => {
  ElMessageBox.confirm(`确定删除素材「${row.name}」吗？`, '提示', { type: 'warning' })
    .then(async () => {
      await request.delete(`/admin/materials/${row.id}`)
      ElMessage.success('删除成功')
      loadList()
    })
    .catch(() => {})
}

onMounted(loadList)
</script>
