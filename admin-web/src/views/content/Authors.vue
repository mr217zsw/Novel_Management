<template>
  <div class="page-container">
    <el-card shadow="never">
      <template #header>
        <div class="card-header">
          <span>作者管理</span>
          <el-button type="primary" @click="openDialog()">签约作者</el-button>
        </div>
      </template>

      <el-table :data="list" v-loading="loading">
        <el-table-column prop="id" label="ID" width="60" />
        <el-table-column prop="pen_name" label="笔名" />
        <el-table-column prop="real_name" label="真实姓名" />
        <el-table-column prop="phone" label="手机号" />
        <el-table-column prop="royalty_rate" label="分成比例(%)" width="110" align="right" />
        <el-table-column label="书籍数" width="90" align="right">
          <template #default="{ row }">{{ row.books_count }}</template>
        </el-table-column>
        <el-table-column label="操作" width="120">
          <template #default="{ row }">
            <el-button link type="primary" @click="openDialog(row)">编辑</el-button>
          </template>
        </el-table-column>
      </el-table>
    </el-card>

    <!-- 签约/编辑弹窗 -->
    <el-dialog v-model="dialogVisible" :title="form.id ? '编辑作者' : '签约作者'" width="500px">
      <el-form ref="formRef" :model="form" :rules="rules" label-width="90px">
        <el-form-item label="笔名" prop="pen_name">
          <el-input v-model="form.pen_name" />
        </el-form-item>
        <el-form-item label="真实姓名">
          <el-input v-model="form.real_name" />
        </el-form-item>
        <el-form-item label="手机号">
          <el-input v-model="form.phone" />
        </el-form-item>
        <el-form-item label="分成比例(%)" prop="royalty_rate">
          <el-input-number v-model="form.royalty_rate" :min="0" :max="100" style="width: 100%" />
        </el-form-item>
        <el-form-item label="合同开始">
          <el-date-picker v-model="form.contract_start" type="date" value-format="YYYY-MM-DD" style="width: 100%" />
        </el-form-item>
        <el-form-item label="合同到期">
          <el-date-picker v-model="form.contract_end" type="date" value-format="YYYY-MM-DD" style="width: 100%" />
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
import { ElMessage } from 'element-plus'
import request from '@/utils/request'

const list = ref([])
const loading = ref(false)
const dialogVisible = ref(false)
const saving = ref(false)
const formRef = ref()

const form = reactive({
  id: null, pen_name: '', real_name: '', phone: '',
  royalty_rate: 50, contract_start: null, contract_end: null
})

const rules = {
  pen_name: [{ required: true, message: '请输入笔名', trigger: 'blur' }],
  royalty_rate: [{ required: true, message: '请输入分成比例', trigger: 'blur' }]
}

const loadList = async () => {
  loading.value = true
  try {
    const res = await request.get('/admin/authors', { params: { per_page: 100 } })
    list.value = res.data.data
  } finally {
    loading.value = false
  }
}

const openDialog = (row) => {
  if (row) {
    Object.assign(form, row)
  } else {
    Object.assign(form, {
      id: null, pen_name: '', real_name: '', phone: '',
      royalty_rate: 50, contract_start: null, contract_end: null
    })
  }
  dialogVisible.value = true
}

const handleSave = async () => {
  await formRef.value.validate()
  saving.value = true
  try {
    if (form.id) {
      await request.put(`/admin/authors/${form.id}`, form)
    } else {
      await request.post('/admin/authors', form)
    }
    ElMessage.success('保存成功')
    dialogVisible.value = false
    loadList()
  } finally {
    saving.value = false
  }
}

onMounted(loadList)
</script>
