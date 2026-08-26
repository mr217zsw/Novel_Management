import { createRouter, createWebHistory } from 'vue-router'

// 路由配置
const routes = [
  {
    path: '/login',
    name: 'Login',
    component: () => import('@/views/Login.vue'),
    meta: { title: '登录' }
  },
  {
    path: '/',
    component: () => import('@/layout/Layout.vue'),
    redirect: '/dashboard',
    children: [
      {
        path: 'dashboard',
        name: 'Dashboard',
        component: () => import('@/views/dashboard/Dashboard.vue'),
        meta: { title: '数据看板', icon: 'DataAnalysis' }
      },
      // 投流运营
      {
        path: 'channels',
        name: 'Channels',
        component: () => import('@/views/invest/Channels.vue'),
        meta: { title: '渠道管理', icon: 'Link' }
      },
      {
        path: 'campaigns',
        name: 'Campaigns',
        component: () => import('@/views/invest/Campaigns.vue'),
        meta: { title: '投放计划', icon: 'Promotion' }
      },
      {
        path: 'materials',
        name: 'Materials',
        component: () => import('@/views/invest/Materials.vue'),
        meta: { title: '素材管理', icon: 'Picture' }
      },
      {
        path: 'attributions',
        name: 'Attributions',
        component: () => import('@/views/invest/Attributions.vue'),
        meta: { title: '归因 ROI 分析', icon: 'TrendCharts' }
      },
      // 内容版权
      {
        path: 'books',
        name: 'Books',
        component: () => import('@/views/content/Books.vue'),
        meta: { title: '书籍管理', icon: 'Reading' }
      },
      {
        path: 'chapters/:bookId?',
        name: 'Chapters',
        component: () => import('@/views/content/Chapters.vue'),
        meta: { title: '章节管理', icon: 'Document' }
      },
      {
        path: 'audits',
        name: 'Audits',
        component: () => import('@/views/content/Audits.vue'),
        meta: { title: '内容审核', icon: 'CircleCheck' }
      },
      {
        path: 'authors',
        name: 'Authors',
        component: () => import('@/views/content/Authors.vue'),
        meta: { title: '作者管理', icon: 'User' }
      },
      // 财务
      {
        path: 'orders',
        name: 'Orders',
        component: () => import('@/views/finance/Orders.vue'),
        meta: { title: '订单管理', icon: 'List' }
      }
    ]
  },
  {
    path: '/:pathMatch(.*)*',
    redirect: '/dashboard'
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

// 路由守卫：未登录跳转
router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('token')
  if (to.path !== '/login' && !token) {
    next('/login')
  } else {
    next()
  }
})

// 设置页面标题
router.afterEach((to) => {
  document.title = to.meta.title ? `${to.meta.title} - 小说平台管理后台` : '小说平台管理后台'
})

export default router
