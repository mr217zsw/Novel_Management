<template>
  <div class="page-container">
    <el-card shadow="never">
      <template #header>
        <div class="card-header">
          <span>投放计划</span>
          <el-button type="primary" @click="openDialog()">新增计划</el-button>
        </div>
      </template>

      <el-form :inline="true" class="filter-bar">
        <el-form-item label="状态">
          <el-select v-model="filters.status" clearable placeholder="全部" style="width: 120px">
            <el-option label="草稿" :value="0" />
            <el-option label="投放中" :value="1" />
            <el-option label="暂停" :value="2" />
            <el-option label="结束" :value="3" />
          </el-select>
        </el-form-item>
        <el-form-item>
          <el-button @click="loadList">查询</el-button>
        </el-form-item>
      </el-form>

      <el-table :data="list" v-loading="loading">
        <el-table-column prop="id" label="ID" width="60" />
        <el-table-column prop="name" label="计划名称" />
        <el-table-column label="渠道" width="100">
          <template #default="{ row }">{{ row.channel?.name || '-' }}</template>
        </el-table-column>
        <el-table-column label="书籍" width="120">
          <template #default="{ row }">{{ row.book?.title || '-' }}</template>
        </el-table-column>
        <el-table-column label="日预算(分)" width="100" align="right">
          <template #default="{ row }">{{ row.daily_budget }}</template>
        </el-table-column>
        <el-table-column label="出价(分)" width="100" align="right">
          <template #default="{ row }">{{ row.bid_price }}</template>
        </el-table-column>
        <el-table-column label="累计消耗(分)" width="110" align="right">
          <template #default="{ row }">{{ row.cost }}</template>
        </el-table-column>
        <el-table-column label="状态" width="90">
          <template #default="{ row }">
            <el-tag :type="statusType(row.status)">{{ statusText(row.status) }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column label="操作" width="180">
          <template #default="{ row }">
            <el-button link type="primary" @click="toggleStatus(row)">
              {{ row.status === 1 ? '暂停' : '开始' }}
            </el-button>
            <el-button link type="primary" @click="openDialog(row)">编辑</el-button>
            <el-button link type="danger" @click="handleDelete(row)">删除</el-button>
          </template>
        </el-table-column>
      </el-table>
    </el-card>

    <!-- 新增/编辑弹窗 -->
    <el-dialog v-model="dialogVisible" :title="form.id ? '编辑计划' : '新增计划'" width="600px">
      <el-form ref="formRef" :model="form" :rules="rules" label-width="100px">
        <el-form-item label="计划名称" prop="name">
          <el-input v-model="form.name" />
        </el-form-item>
        <el-form-item label="所属渠道" prop="channel_id">
          <el-select v-model="form.channel_id" filterable style="width: 100%">
            <el-option v-for="c in channels" :key="c.id" :label="c.name" :value="c.id" />
          </el-select>
        </el-form-item>
        <el-form-item label="推广书籍" prop="book_id">
          <el-select v-model="form.book_id" filterable clearable style="width: 100%">
            <el-option v-for="b in books" :key="b.id" :label="b.title" :value="b.id" />
          </el-select>
        </el-form-item>
        <el-form-item label="日预算(分)" prop="daily_budget">
          <el-input-number v-model="form.daily_budget" :min="0" style="width: 100%" />
        </el-form-item>
        <el-form-item label="总预算(分)" prop="total_budget">
          <el-input-number v-model="form.total_budget" :min="0" style="width: 100%" />
        </el-form-item>
        <el-form-item label="出价策略" prop="bid_strategy">
          <el-radio-group v-model="form.bid_strategy">
            <el-radio :value="1">智能</el-radio>
            <el-radio :value="2">手动</el-radio>
          </el-radio-group>
        </el-form-item>
        <el-form-item label="出价(分)" prop="bid_price">
          <el-input-number v-model="form.bid_price" :min="0" style="width: 100%" />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="dialogVisible = false">取消</el-button>
        <el-button type="primary" :loading="saving" @click="handleSave">保存</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import request from '@/utils/request'

const list = ref([])
const channels = ref([])
const books = ref([])
const loading = ref(false)
const dialogVisible = ref(false)
const saving = ref(false)
const formRef = ref()
const filters = reactive({ status: null })

const form = reactive({
  id: null, name: '', channel_id: null, book_id: null,
  daily_budget: 1000, total_budget: 10000, bid_strategy: 1, bid_price: 100
})

const rules = {
  name: [{ required: true, message: '请输入计划名称', trigger: 'blur' }],
  channel_id: [{ required: true, message: '请选择渠道', trigger: 'change' }]
}

const statusText = (s) => ['草稿', '投放中', '暂停', '结束'][s] || '-'
const statusType = (s) => ['info', 'success', 'warning', 'danger'][s] || 'info'

const loadList = async () => {
  loading.value = true
  try {
    const res = await request.get('/admin/campaigns', {
      params: { ...filters, per_page: 100 }
    })
    list.value = res.data.data
  } finally {
    loading.value = false
  }
}

const loadOptions = async () => {
  const [cRes, bRes] = await Promise.all([
    request.get('/admin/channels', { params: { per_page: 100 } }),
    request.get('/admin/books', { params: { per_page: 100 } })
  ])
  channels.value = cRes.data.data
  books.value = bRes.data.data
}

const openDialog = (row) => {
  if (row) {
    Object.assign(form, row)
  } else {
    Object.assign(form, {
      id: null, name: '', channel_id: null, book_id: null,
      daily_budget: 1000, total_budget: 10000, bid_strategy: 1, bid_price: 100
    })
  }
  dialogVisible.value = true
}

const handleSave = async () => {
  await formRef.value.validate()
  saving.value = true
  try {
    if (form.id) {
      await request.put(`/admin/campaigns/${form.id}`, form)
    } else {
      await request.post('/admin/campaigns', form)
    }
    ElMessage.success('保存成功')
    dialogVisible.value = false
    loadList()
  } finally {
    saving.value = false
  }
}

const toggleStatus = async (row) => {
  await request.post(`/admin/campaigns/${row.id}/toggle`)
  ElMessage.success('操作成功')
  loadList()
}

const handleDelete = (row) => {
  ElMessageBox.confirm(`确定删除计划「${row.name}」吗？`, '提示', { type: 'warning' })
    .then(async () => {
      await request.delete(`/admin/campaigns/${row.id}`)
      ElMessage.success('删除成功')
      loadList()
    })
    .catch(() => {})
}

onMounted(() => {
  loadOptions()
  loadList()
})
</script>

<style scoped>
.filter-bar {
  margin-bottom: 8px;
}
</style>
