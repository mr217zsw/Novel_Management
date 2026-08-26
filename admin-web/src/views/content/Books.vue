<template>
  <div class="page-container">
    <el-card shadow="never">
      <template #header>
        <div class="card-header">
          <span>书籍管理</span>
          <el-button type="primary" @click="openDialog()">新增书籍</el-button>
        </div>
      </template>

      <el-form :inline="true" class="filter-bar">
        <el-form-item label="关键词">
          <el-input v-model="filters.keyword" placeholder="书名" clearable @keyup.enter="loadList" />
        </el-form-item>
        <el-form-item>
          <el-button @click="loadList">查询</el-button>
        </el-form-item>
      </el-form>

      <el-table :data="list" v-loading="loading">
        <el-table-column prop="id" label="ID" width="60" />
        <el-table-column label="封面" width="80">
          <template #default="{ row }">
            <el-image
              v-if="row.cover_url"
              :src="row.cover_url"
              fit="cover"
              style="width: 40px; height: 55px"
            />
            <el-icon v-else><Picture /></el-icon>
          </template>
        </el-table-column>
        <el-table-column prop="title" label="书名" />
        <el-table-column label="分类" width="100">
          <template #default="{ row }">{{ row.category?.name || '-' }}</template>
        </el-table-column>
        <el-table-column label="作者" width="100">
          <template #default="{ row }">{{ row.author?.pen_name || '-' }}</template>
        </el-table-column>
        <el-table-column prop="total_chapters" label="章节数" width="80" align="right" />
        <el-table-column prop="total_views" label="阅读量" width="90" align="right" />
        <el-table-column label="状态" width="80">
          <template #default="{ row }">
            <el-tag :type="statusType(row.status)">{{ statusText(row.status) }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column label="操作" width="200">
          <template #default="{ row }">
            <el-button link type="primary" @click="$router.push('/chapters/' + row.id)">章节</el-button>
            <el-button v-if="row.status === 1" link type="success" @click="audit(row, 'pass')">审核通过</el-button>
            <el-button link type="primary" @click="openDialog(row)">编辑</el-button>
            <el-button link type="danger" @click="handleDelete(row)">删除</el-button>
          </template>
        </el-table-column>
      </el-table>
    </el-card>

    <!-- 新增/编辑弹窗 -->
    <el-dialog v-model="dialogVisible" :title="form.id ? '编辑书籍' : '新增书籍'" width="600px">
      <el-form ref="formRef" :model="form" :rules="rules" label-width="90px">
        <el-form-item label="书名" prop="title">
          <el-input v-model="form.title" />
        </el-form-item>
        <el-form-item label="封面URL">
          <el-input v-model="form.cover_url" placeholder="OSS/CDN 地址" />
        </el-form-item>
        <el-form-item label="简介">
          <el-input v-model="form.description" type="textarea" :rows="3" />
        </el-form-item>
        <el-form-item label="分类">
          <el-select v-model="form.category_id" clearable style="width: 100%">
            <el-option v-for="c in categories" :key="c.id" :label="c.name" :value="c.id" />
          </el-select>
        </el-form-item>
        <el-form-item label="版权类型">
          <el-select v-model="form.copyright_type" style="width: 100%">
            <el-option label="买断" :value="1" />
            <el-option label="分成" :value="2" />
            <el-option label="独家" :value="3" />
            <el-option label="非独家" :value="4" />
          </el-select>
        </el-form-item>
        <el-form-item label="分成比例(%)" v-if="form.copyright_type === 2">
          <el-input-number v-model="form.royalty_rate" :min="0" :max="100" style="width: 100%" />
        </el-form-item>
        <el-form-item label="最低价格(分)">
          <el-input-number v-model="form.min_price" :min="0" style="width: 100%" />
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
import { Picture } from '@element-plus/icons-vue'

const list = ref([])
const categories = ref([])
const loading = ref(false)
const dialogVisible = ref(false)
const saving = ref(false)
const formRef = ref()
const filters = reactive({ keyword: '' })

const form = reactive({
  id: null, title: '', cover_url: '', description: '', category_id: null,
  copyright_type: 1, royalty_rate: 50, min_price: 0
})

const rules = {
  title: [{ required: true, message: '请输入书名', trigger: 'blur' }]
}

const statusText = (s) => ['草稿', '待审核', '已上架', '已下架'][s] || '-'
const statusType = (s) => ['info', 'warning', 'success', 'danger'][s] || 'info'

const loadList = async () => {
  loading.value = true
  try {
    const res = await request.get('/admin/books', {
      params: { ...filters, per_page: 100 }
    })
    list.value = res.data.data
  } finally {
    loading.value = false
  }
}

const loadCategories = async () => {
  const res = await request.get('/admin/dashboard/book-ranking', { params: { limit: 100 } }).catch(() => null)
  // 分类从分类接口获取
  categories.value = []
}

const openDialog = (row) => {
  if (row) {
    Object.assign(form, row)
  } else {
    Object.assign(form, {
      id: null, title: '', cover_url: '', description: '', category_id: null,
      copyright_type: 1, royalty_rate: 50, min_price: 0
    })
  }
  dialogVisible.value = true
}

const handleSave = async () => {
  await formRef.value.validate()
  saving.value = true
  try {
    if (form.id) {
      await request.put(`/admin/books/${form.id}`, form)
    } else {
      await request.post('/admin/books', form)
    }
    ElMessage.success('保存成功')
    dialogVisible.value = false
    loadList()
  } finally {
    saving.value = false
  }
}

const audit = async (row, action) => {
  await request.post(`/admin/books/${row.id}/audit`, { action })
  ElMessage.success('审核通过')
  loadList()
}

const handleDelete = (row) => {
  ElMessageBox.confirm(`确定删除书籍「${row.title}」吗？`, '提示', { type: 'warning' })
    .then(async () => {
      await request.delete(`/admin/books/${row.id}`)
      ElMessage.success('删除成功')
      loadList()
    })
    .catch(() => {})
}

onMounted(() => {
  loadCategories()
  loadList()
})
</script>

<style scoped>
.filter-bar {
  margin-bottom: 8px;
}
</style>
