import { defineStore } from 'pinia'
import request from '@/utils/request'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    token: localStorage.getItem('token') || '',
    user: null
  }),
  actions: {
    // 登录
    async login(account, password) {
      const res = await request.post('/admin/login', { account, password })
      this.token = res.data.token
      this.user = res.data.user
      localStorage.setItem('token', this.token)
      return res.data
    },
    // 获取当前用户
    async fetchUser() {
      const res = await request.get('/v1/auth/me')
      this.user = res.data
    },
    // 退出
    logout() {
      this.token = ''
      this.user = null
      localStorage.removeItem('token')
    }
  }
})
