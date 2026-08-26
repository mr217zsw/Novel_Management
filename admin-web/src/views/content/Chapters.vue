<template>
  <div class="page-container">
    <el-card shadow="never">
      <template #header>
        <div class="card-header">
          <span>章节管理</span>
          <el-button type="primary" @click="openDialog()">新增章节</el-button>
        </div>
      </template>

      <el-table :data="list" v-loading="loading">
        <el-table-column prop="chapter_no" label="章号" width="70" />
        <el-table-column prop="title" label="章节标题" />
        <el-table-column prop="word_count" label="字数" width="90" align="right" />
        <el-table-column label="是否免费" width="90">
          <template #default="{ row }">
            <el-tag :type="row.is_free === 1 ? 'success' : 'info'">
              {{ row.is_free === 1 ? '免费' : '付费' }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="price" label="价格(分)" width="90" align="right" />
        <el-table-column label="状态" width="80">
          <template #default="{ row }">
            <el-tag :type="row.status === 1 ? 'success' : 'info'">
              {{ row.status === 1 ? '已发布' : '草稿' }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="审核状态" width="90">
          <template #default="{ row }">
            <el-tag :type="auditType(row.audit_status)">{{ auditText(row.audit_status) }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column label="操作" width="150">
          <template #default="{ row }">
            <el-button v-if="row.audit_status === 0" link type="success" @click="audit(row, 'pass')">通过</el-button>
            <el-button link type="primary" @click="preview(row)">预览</el-button>
          </template>
        </el-table-column>
      </el-table>
    </el-card>

    <!-- 新增章节弹窗 -->
    <el-dialog v-model="dialogVisible" title="新增章节" width="700px">
      <el-form ref="formRef" :model="form" :rules="rules" label-width="80px">
        <el-form-item label="章节序号" prop="chapter_no">
          <el-input-number v-model="form.chapter_no" :min="1" style="width: 100%" />
        </el-form-item>
        <el-form-item label="章节标题" prop="title">
          <el-input v-model="form.title" />
        </el-form-item>
        <el-form-item label="正文内容" prop="content">
          <el-input v-model="form.content" type="textarea" :rows="12" placeholder="章节正文" />
        </el-form-item>
        <el-form-item label="是否免费">
          <el-radio-group v-model="form.is_free">
            <el-radio :value="1">免费</el-radio>
            <el-radio :value="0">付费</el-radio>
          </el-radio-group>
        </el-form-item>
        <el-form-item label="价格(分)" v-if="form.is_free === 0">
          <el-input-number v-model="form.price" :min="0" style="width: 100%" />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="dialogVisible = false">取消</el-button>
        <el-button type="primary" :loading="saving" @click="handleSave">保存</el-button>
      </template>
    </el-dialog>

    <!-- 预览弹窗 -->
    <el-dialog v-model="previewVisible" title="章节预览" width="700px">
      <pre class="preview-content">{{ previewContent }}</pre>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { ElMessage } from 'element-plus'
import request from '@/utils/request'

const route = useRoute()
const bookId = route.params.bookId
const list = ref([])
const loading = ref(false)
const dialogVisible = ref(false)
const saving = ref(false)
const previewVisible = ref(false)
const previewContent = ref('')
const formRef = ref()

const form = reactive({
  chapter_no: 1, title: '', content: '', is_free: 1, price: 0
})

const rules = {
  title: [{ required: true, message: '请输入章节标题', trigger: 'blur' }],
  content: [{ required: true, message: '请输入正文内容', trigger: 'blur' }]
}

const auditText = (s) => ['待审', '通过', '驳回'][s] || '-'
const auditType = (s) => ['warning', 'success', 'danger'][s] || 'info'

const loadList = async () => {
  if (!bookId) return
  loading.value = true
  try {
    const res = await request.get(`/admin/books/${bookId}/chapters`, {
      params: { per_page: 100 }
    })
    list.value = res.data.data
  } finally {
    loading.value = false
  }
}

const openDialog = () => {
  Object.assign(form, { chapter_no: list.value.length + 1, title: '', content: '', is_free: 1, price: 0 })
  dialogVisible.value = true
}

const handleSave = async () => {
  await formRef.value.validate()
  saving.value = true
  try {
    await request.post(`/admin/books/${bookId}/chapters`, form)
    ElMessage.success('保存成功')
    dialogVisible.value = false
    loadList()
  } finally {
    saving.value = false
  }
}

const audit = async (row, action) => {
  await request.post(`/admin/chapters/${row.id}/audit`, { action })
  ElMessage.success('审核通过')
  loadList()
}

const preview = async (row) => {
  const res = await request.get(`/admin/audit/chapters/${row.id}/preview`)
  previewContent.value = res.data.content
  previewVisible.value = true
}

onMounted(loadList)
</script>

<style scoped>
.preview-content {
  white-space: pre-wrap;
  word-wrap: break-word;
  line-height: 1.8;
  font-family: inherit;
}
</style>
