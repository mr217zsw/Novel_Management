<template>
  <div class="page-container">
    <el-tabs v-model="activeTab">
      <!-- 书籍审核 -->
      <el-tab-pane label="待审书籍" name="books">
        <el-table :data="books" v-loading="loadingBooks" stripe>
          <el-table-column prop="id" label="ID" width="60" />
          <el-table-column prop="title" label="书名" />
          <el-table-column label="作者" width="120">
            <template #default="{ row }">{{ row.author?.pen_name || '-' }}</template>
          </el-table-column>
          <el-table-column label="操作" width="180">
            <template #default="{ row }">
              <el-button link type="success" @click="auditBook(row, 'pass')">通过</el-button>
              <el-button link type="danger" @click="auditBook(row, 'reject')">驳回</el-button>
            </template>
          </el-table-column>
        </el-table>
      </el-tab-pane>

      <!-- 章节审核 -->
      <el-tab-pane label="待审章节" name="chapters">
        <el-table :data="chapters" v-loading="loadingChapters" stripe>
          <el-table-column prop="id" label="ID" width="60" />
          <el-table-column label="书籍" width="150">
            <template #default="{ row }">{{ row.novel?.title || '-' }}</template>
          </el-table-column>
          <el-table-column prop="chapter_no" label="章号" width="70" />
          <el-table-column prop="title" label="章节标题" />
          <el-table-column prop="word_count" label="字数" width="90" align="right" />
          <el-table-column label="操作" width="180">
            <template #default="{ row }">
              <el-button link type="primary" @click="previewChapter(row)">预览</el-button>
              <el-button link type="success" @click="auditChapter(row, 'pass')">通过</el-button>
              <el-button link type="danger" @click="auditChapter(row, 'reject')">驳回</el-button>
            </template>
          </el-table-column>
        </el-table>
      </el-tab-pane>
    </el-tabs>

    <!-- 预览弹窗 -->
    <el-dialog v-model="previewVisible" title="章节预览" width="700px">
      <pre class="preview-content">{{ previewContent }}</pre>
      <template #footer>
        <el-button @click="previewVisible = false">关闭</el-button>
        <el-button type="success" @click="doAudit(previewRow, 'pass')">通过</el-button>
        <el-button type="danger" @click="doAudit(previewRow, 'reject')">驳回</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import request from '@/utils/request'

const activeTab = ref('books')
const books = ref([])
const chapters = ref([])
const loadingBooks = ref(false)
const loadingChapters = ref(false)
const previewVisible = ref(false)
const previewContent = ref('')
const previewRow = ref(null)

const loadBooks = async () => {
  loadingBooks.value = true
  try {
    const res = await request.get('/admin/audit/books', { params: { per_page: 100 } })
    books.value = res.data.data
  } finally {
    loadingBooks.value = false
  }
}

const loadChapters = async () => {
  loadingChapters.value = true
  try {
    const res = await request.get('/admin/audit/chapters', { params: { per_page: 100 } })
    chapters.value = res.data.data
  } finally {
    loadingChapters.value = false
  }
}

const auditBook = (row, action) => {
  ElMessageBox.confirm(`确定${action === 'pass' ? '通过' : '驳回'}书籍「${row.title}」吗？`, '提示', { type: 'warning' })
    .then(async () => {
      await request.post(`/admin/books/${row.id}/audit`, { action })
      ElMessage.success('操作成功')
      loadBooks()
    })
    .catch(() => {})
}

const previewChapter = async (row) => {
  const res = await request.get(`/admin/audit/chapters/${row.id}/preview`)
  previewContent.value = res.data.content
  previewRow.value = row
  previewVisible.value = true
}

const auditChapter = (row, action) => {
  ElMessageBox.confirm(`确定${action === 'pass' ? '通过' : '驳回'}章节「${row.title}」吗？`, '提示', { type: 'warning' })
    .then(async () => {
      await request.post(`/admin/chapters/${row.id}/audit`, { action })
      ElMessage.success('操作成功')
      loadChapters()
    })
    .catch(() => {})
}

const doAudit = async (row, action) => {
  await request.post(`/admin/chapters/${row.id}/audit`, { action })
  ElMessage.success('操作成功')
  previewVisible.value = false
  loadChapters()
}

onMounted(() => {
  loadBooks()
  loadChapters()
})
</script>

<style scoped>
.preview-content {
  white-space: pre-wrap;
  word-wrap: break-word;
  line-height: 1.8;
  max-height: 400px;
  overflow-y: auto;
}
</style>
