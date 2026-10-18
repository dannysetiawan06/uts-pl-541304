<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const router = useRouter();
const authStore = useAuthStore();

const email = ref('admin@example.com');
const password = ref('password123');
const errorMessage = ref('');

const handleLogin = async () => {
  errorMessage.value = '';
  if (!email.value || !password.value) {
    errorMessage.value = 'Silakan isi email dan kata sandi.';
    return;
  }

  const result = await authStore.login(email.value, password.value);
  if (result.success) {
    router.push('/');
  } else {
    errorMessage.value = result.message || 'Email atau kata sandi tidak valid.';
  }
};
</script>

<template>
  <div class="auth-wrapper">
    <div class="auth-card">
      <div class="auth-header">
        <div class="brand-badge">âš¡ UTS PL 541304</div>
        <h1>Selamat Datang</h1>
        <p>Silakan masuk ke sistem manajemen inventaris barang</p>
      </div>

      <div v-if="errorMessage" class="alert-box error">
        <span>âš ï¸</span> {{ errorMessage }}
      </div>

      <form @submit.prevent="handleLogin" class="auth-form">
        <div class="form-group">
          <label for="email">Alamat Email</label>
          <input
            id="email"
            v-model="email"
            type="email"
            placeholder="nama@domain.com"
            required
            autocomplete="email"
          />
        </div>

        <div class="form-group">
          <label for="password">Kata Sandi</label>
          <input
            id="password"
            v-model="password"
            type="password"
            placeholder="â€¢â€¢â€¢â€¢â€¢â€¢â€¢â€¢"
            required
            autocomplete="current-password"
          />
        </div>

        <button type="submit" class="btn-primary" :disabled="authStore.loading">
          <span v-if="authStore.loading" class="spinner"></span>
          <span v-else>Masuk ke Sistem</span>
        </button>
      </form>

      <div class="auth-footer">
        <p class="credential-tip">
          <strong>Demo Akun Default:</strong><br />
          Email: <code>admin@example.com</code><br />
          Password: <code>password123</code>
        </p>
      </div>
    </div>
  </div>
</template>

<style scoped>
.auth-wrapper {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 100vh;
  background: radial-gradient(circle at 10% 20%, #1e1b4b 0%, #0f172a 90%);
  padding: 24px;
}

.auth-card {
  background: rgba(255, 255, 255, 0.98);
  width: 100%;
  max-width: 440px;
  padding: 40px;
  border-radius: 20px;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35);
  backdrop-filter: blur(16px);
}

.brand-badge {
  display: inline-block;
  padding: 6px 14px;
  font-size: 13px;
  font-weight: 700;
  color: #4f46e5;
  background: #eef2ff;
  border-radius: 50px;
  margin-bottom: 16px;
}

.auth-header h1 {
  font-size: 26px;
  font-weight: 800;
  color: #0f172a;
  margin-bottom: 8px;
}

.auth-header p {
  color: #64748b;
  font-size: 14px;
  margin-bottom: 24px;
}

.alert-box {
  padding: 12px 16px;
  border-radius: 10px;
  font-size: 14px;
  margin-bottom: 20px;
  display: flex;
  align-items: center;
  gap: 10px;
}

.alert-box.error {
  background: #fef2f2;
  color: #991b1b;
  border: 1px solid #fee2e2;
}

.auth-form {
  display: flex;
  flex-direction: column;
  gap: 18px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.form-group label {
  font-size: 13px;
  font-weight: 600;
  color: #334155;
}

.form-group input {
  padding: 12px 16px;
  border: 1.5px solid #cbd5e1;
  border-radius: 10px;
  font-size: 14px;
  transition: all 0.2s ease;
}

.form-group input:focus {
  outline: none;
  border-color: #4f46e5;
  box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.12);
}

.btn-primary {
  padding: 14px;
  background: #4f46e5;
  color: #ffffff;
  font-size: 15px;
  font-weight: 700;
  border-radius: 10px;
  display: flex;
  justify-content: center;
  align-items: center;
}

.btn-primary:hover:not(:disabled) {
  background: #4338ca;
  transform: translateY(-1px);
}

.btn-primary:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.auth-footer {
  margin-top: 24px;
  padding-top: 20px;
  border-top: 1px dashed #e2e8f0;
}

.credential-tip {
  font-size: 12px;
  color: #64748b;
  background: #f8fafc;
  padding: 12px;
  border-radius: 8px;
  border: 1px solid #e2e8f0;
}

.credential-tip code {
  color: #4f46e5;
  font-weight: 600;
}

.spinner {
  width: 20px;
  height: 20px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top-color: #ffffff;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}
</style>