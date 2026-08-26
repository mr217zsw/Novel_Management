<template>
  <div class="page-container">
    <el-card shadow="never">
      <template #header>
        <div class="card-header">
          <span>渠道管理</span>
          <el-button type="primary" @click="openDialog()">新增渠道</el-button>
        </div>
      </template>

      <el-table :data="list" v-loading="loading">
        <el-table-column prop="id" label="ID" width="60" />
        <el-table-column prop="name" label="渠道名称" />
        <el-table-column prop="code" label="渠道编码" />
        <el-table-column label="状态" width="100">
          <template #default="{ row }">
            <el-tag :type="row.status === 1 ? 'success' : 'danger'">
              {{ row.status === 1 ? '启用' : '禁用' }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="操作" width="180">
          <template #default="{ row }">
            <el-button link type="primary" @click="openDialog(row)">编辑</el-button>
            <el-button link type="danger" @click="handleDelete(row)">删除</el-button>
          </template>
        </el-table-column>
      </el-table>
    </el-card>

    <!-- 新增/编辑弹窗 -->
    <el-dialog v-model="dialogVisible" :title="form.id ? '编辑渠道' : '新增渠道'" width="500px">
      <el-form ref="formRef" :model="form" :rules="rules" label-width="100px">
        <el-form-item label="渠道名称" prop="name">
          <el-input v-model="form.name" />
        </el-form-item>
        <el-form-item label="渠道编码" prop="code">
          <el-input v-model="form.code" :disabled="!!form.id" />
        </el-form-item>
        <el-form-item label="状态">
          <el-switch v-model="form.status" :active-value="1" :inactive-value="0" />
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
const loading = ref(false)
const dialogVisible = ref(false)
const saving = ref(false)
const formRef = ref()
const form = reactive({ id: null, name: '', code: '', status: 1 })
const rules = {
  name: [{ required: true, message: '请输入渠道名称', trigger: 'blur' }],
  code: [{ required: true, message: '请输入渠道编码', trigger: 'blur' }]
}

const loadList = async () => {
  loading.value = true
  try {
    const res = await request.get('/admin/channels', { params: { per_page: 100 } })
    list.value = res.data.data
  } finally {
    loading.value = false
  }
}

const openDialog = (row) => {
  if (row) {
    Object.assign(form, row)
  } else {
    Object.assign(form, { id: null, name: '', code: '', status: 1 })
  }
  dialogVisible.value = true
}

const handleSave = async () => {
  await formRef.value.validate()
  saving.value = true
  try {
    if (form.id) {
      await request.put(`/admin/channels/${form.id}`, form)
    } else {
      await request.post('/admin/channels', form)
    }
    ElMessage.success('保存成功')
    dialogVisible.value = false
    loadList()
  } finally {
    saving.value = false
  }
}

const handleDelete = (row) => {
  ElMessageBox.confirm(`确定删除渠道「${row.name}」吗？`, '提示', { type: 'warning' })
    .then(async () => {
      await request.delete(`/admin/channels/${row.id}`)
      ElMessage.success('删除成功')
      loadList()
    })
    .catch(() => {})
}

onMounted(loadList)
</script>
