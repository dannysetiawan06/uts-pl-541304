import { defineStore } from 'pinia';
import apiClient from '../api/axios';

export const useAuthStore = defineStore('auth', {
  state: () => ({
    token: localStorage.getItem('auth_token') || null,
    user: JSON.parse(localStorage.getItem('auth_user') || 'null'),
    loading: false,
    error: null,
  }),

  getters: {
    isAuthenticated: (state) => !!state.token,
  },

  actions: {
    async login(email, password) {
      this.loading = true;
      this.error = null;
      try {
        const response = await apiClient.post('/login', { email, password });
        this.token = response.data.token;
        this.user = response.data.user;

        localStorage.setItem('auth_token', this.token);
        localStorage.setItem('auth_user', JSON.stringify(this.user));

        return { success: true };
      } catch (err) {
        this.error = err.response?.data?.message || 'Login gagal, periksa kredensial Anda.';
        return { success: false, message: this.error };
      } finally {
        this.loading = false;
      }
    },

    async logout() {
      try {
        await apiClient.post('/logout');
      } catch (err) {
        console.warn('Logout API error:', err);
      } finally {
        this.token = null;
        this.user = null;
        localStorage.removeItem('auth_token');
        localStorage.removeItem('auth_user');
      }
    },
  },
});