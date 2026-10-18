<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import apiClient from '../api/axios';

const router = useRouter();
const authStore = useAuthStore();

const items = ref([]);
const loading = ref(false);
const searchQuery = ref('');
const errorAlert = ref('');
const successAlert = ref('');

// Modal State
const isModalOpen = ref(false);
const isEditing = ref(false);
const isSubmitting = ref(false);
const formErrors = ref({});
const activeItem = ref({
  id: null,
  name: '',
  description: '',
  price: 0,
  stock: 0,
});

// Delete Confirmation Modal
const isDeleteModalOpen = ref(false);
const itemToDelete = ref(null);

const fetchItems = async () => {
  loading.value = true;
  errorAlert.value = '';
  try {
    const res = await apiClient.get('/items');
    items.value = res.data.data || [];
  } catch (err) {
    errorAlert.value = err.response?.data?.message || 'Gagal memuat data barang.';
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchItems();
});

const filteredItems = computed(() => {
  if (!searchQuery.value) return items.value;
  const q = searchQuery.value.toLowerCase();
  return items.value.filter(
    (i) =>
      i.name.toLowerCase().includes(q) ||
      (i.description && i.description.toLowerCase().includes(q))
  );
});

// Statistics
const totalItemsCount = computed(() => items.value.length);
const totalStockCount = computed(() =>
  items.value.reduce((acc, curr) => acc + Number(curr.stock || 0), 0)
);
const totalAssetValue = computed(() =>
  items.value.reduce(
    (acc, curr) => acc + Number(curr.price || 0) * Number(curr.stock || 0),
    0
  )
);

const formatCurrency = (val) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
  }).format(val || 0);
};

const openAddModal = () => {
  isEditing.value = false;
  activeItem.value = { id: null, name: '', description: '', price: 0, stock: 0 };
  formErrors.value = {};
  isModalOpen.value = true;
};

const openEditModal = (item) => {
  isEditing.value = true;
  activeItem.value = { ...item };
  formErrors.value = {};
  isModalOpen.value = true;
};

const closeModal = () => {
  isModalOpen.value = false;
  formErrors.value = {};
};

const handleSubmit = async () => {
  isSubmitting.value = true;
  formErrors.value = {};
  errorAlert.value = '';
  successAlert.value = '';

  try {
    if (isEditing.value) {
      await apiClient.put(`/items/${activeItem.value.id}`, {
        name: activeItem.value.name,
        description: activeItem.value.description,
        price: activeItem.value.price,
        stock: activeItem.value.stock,
      });
      successAlert.value = 'Data barang berhasil diperbarui.';
    } else {
      await apiClient.post('/items', {
        name: activeItem.value.name,
        description: activeItem.value.description,
        price: activeItem.value.price,
        stock: activeItem.value.stock,
      });
      successAlert.value = 'Data barang baru berhasil ditambahkan.';
    }
    closeModal();
    await fetchItems();
  } catch (err) {
    if (err.response && err.response.status === 422) {
      formErrors.value = err.response.data.errors || {};
    } else {
      errorAlert.value = err.response?.data?.message || 'Terjadi kesalahan sistem.';
    }
  } finally {
    isSubmitting.value = false;
  }
};

const confirmDelete = (item) => {
  itemToDelete.value = item;
  isDeleteModalOpen.value = true;
};

const handleDelete = async () => {
  if (!itemToDelete.value) return;
  try {
    await apiClient.delete(`/items/${itemToDelete.value.id}`);
    successAlert.value = `Barang "${itemToDelete.value.name}" berhasil dihapus.`;
    isDeleteModalOpen.value = false;
    itemToDelete.value = null;
    await fetchItems();
  } catch (err) {
    errorAlert.value = err.response?.data?.message || 'Gagal menghapus barang.';
  }
};

const handleLogout = async () => {
  await authStore.logout();
  router.push('/login');
};
</script>

<template>
  <div class="dashboard-layout">
    <!-- Navbar -->
    <header class="navbar">
      <div class="nav-brand">
        <span class="brand-icon">âš¡</span>
        <div>
          <h2>UTS PL 541304</h2>
          <span class="subtext">Sistem Manajemen Aset & Inventaris</span>
        </div>
      </div>

      <div class="nav-user">
        <div class="user-badge">
          <span class="user-avatar">ðŸ‘¤</span>
          <span class="user-name">{{ authStore.user?.name || 'Administrator' }}</span>
        </div>
        <button @click="handleLogout" class="btn-logout">
          Keluar
        </button>
      </div>
    </header>

    <!-- Main Content -->
    <main class="content-container">
      <!-- Alerts -->
      <div v-if="successAlert" class="alert success">
        <span>âœ…</span> {{ successAlert }}
      </div>
      <div v-if="errorAlert" class="alert danger">
        <span>âš ï¸</span> {{ errorAlert }}
      </div>

      <!-- Stats Overview -->
      <section class="stats-grid">
        <div class="stat-card">
          <span class="stat-title">Total Jenis Barang</span>
          <span class="stat-value">{{ totalItemsCount }}</span>
        </div>
        <div class="stat-card">
          <span class="stat-title">Total Unit Stok</span>
          <span class="stat-value">{{ totalStockCount }}</span>
        </div>
        <div class="stat-card">
          <span class="stat-title">Total Estimasi Nilai</span>
          <span class="stat-value primary">{{ formatCurrency(totalAssetValue) }}</span>
        </div>
      </section>

      <!-- Action Bar -->
      <section class="action-bar">
        <div class="search-box">
          <span class="search-icon">ðŸ”</span>
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Cari berdasarkan nama atau deskripsi barang..."
          />
        </div>
        <button @click="openAddModal" class="btn-add">
          + Tambah Barang Baru
        </button>
      </section>

      <!-- Data Table Card -->
      <section class="table-card">
        <div v-if="loading" class="loading-state">
          <div class="spinner"></div>
          <p>Memuat data barang dari server...</p>
        </div>

        <div v-else-if="filteredItems.length === 0" class="empty-state">
          <span class="empty-icon">ðŸ“¦</span>
          <h3>Belum ada data barang</h3>
          <p>Tambahkan barang baru dengan menekan tombol Tambah Barang di atas.</p>
        </div>

        <div v-else class="table-responsive">
          <table class="data-table">
            <thead>
              <tr>
                <th>No</th>
                <th>Nama Barang</th>
                <th>Deskripsi</th>
                <th>Harga Satuan</th>
                <th>Stok</th>
                <th>Status</th>
                <th class="text-right">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(item, idx) in filteredItems" :key="item.id">
                <td>{{ idx + 1 }}</td>
                <td class="font-bold">{{ item.name }}</td>
                <td class="text-muted">{{ item.description || '-' }}</td>
                <td>{{ formatCurrency(item.price) }}</td>
                <td>{{ item.stock }} Unit</td>
                <td>
                  <span
                    class="badge"
                    :class="item.stock > 5 ? 'badge-success' : 'badge-warning'"
                  >
                    {{ item.stock > 5 ? 'Tersedia' : 'Stok Menipis' }}
                  </span>
                </td>
                <td class="text-right">
                  <div class="action-buttons">
                    <button @click="openEditModal(item)" class="btn-action edit" title="Edit">
                      âœï¸ Edit
                    </button>
                    <button @click="confirmDelete(item)" class="btn-action delete" title="Hapus">
                      ðŸ—‘ï¸ Hapus
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>
    </main>

    <!-- Modal Form (Tambah / Edit) -->
    <div v-if="isModalOpen" class="modal-backdrop">
      <div class="modal-card">
        <div class="modal-header">
          <h3>{{ isEditing ? 'Edit Data Barang' : 'Tambah Barang Baru' }}</h3>
          <button @click="closeModal" class="btn-close">Ã—</button>
        </div>

        <form @submit.prevent="handleSubmit" class="modal-form">
          <div class="form-group">
            <label for="item-name">Nama Barang *</label>
            <input
              id="item-name"
              v-model="activeItem.name"
              type="text"
              required
              placeholder="Contoh: Laptop ThinkPad X1"
            />
            <span v-if="formErrors.name" class="field-error">{{ formErrors.name[0] }}</span>
          </div>

          <div class="form-group">
            <label for="item-desc">Deskripsi</label>
            <textarea
              id="item-desc"
              v-model="activeItem.description"
              rows="3"
              placeholder="Spesifikasi atau catatan barang..."
            ></textarea>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label for="item-price">Harga (IDR) *</label>
              <input
                id="item-price"
                v-model="activeItem.price"
                type="number"
                min="0"
                step="500"
                required
              />
              <span v-if="formErrors.price" class="field-error">{{ formErrors.price[0] }}</span>
            </div>

            <div class="form-group">
              <label for="item-stock">Jumlah Stok *</label>
              <input
                id="item-stock"
                v-model="activeItem.stock"
                type="number"
                min="0"
                required
              />
              <span v-if="formErrors.stock" class="field-error">{{ formErrors.stock[0] }}</span>
            </div>
          </div>

          <div class="modal-footer">
            <button type="button" @click="closeModal" class="btn-cancel">
              Batal
            </button>
            <button type="submit" class="btn-submit" :disabled="isSubmitting">
              <span v-if="isSubmitting">Menyimpan...</span>
              <span v-else>{{ isEditing ? 'Perbarui Data' : 'Simpan Barang' }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal Delete Confirmation -->
    <div v-if="isDeleteModalOpen" class="modal-backdrop">
      <div class="modal-card small">
        <div class="modal-header">
          <h3>Konfirmasi Penghapusan</h3>
          <button @click="isDeleteModalOpen = false" class="btn-close">Ã—</button>
        </div>
        <div class="modal-body">
          <p>
            Apakah Anda yakin ingin menghapus barang
            <strong>"{{ itemToDelete?.name }}"</strong>?
          </p>
          <p class="text-danger-sm">Tindakan ini tidak dapat dibatalkan.</p>
        </div>
        <div class="modal-footer">
          <button type="button" @click="isDeleteModalOpen = false" class="btn-cancel">
            Batal
          </button>
          <button type="button" @click="handleDelete" class="btn-danger">
            Hapus Sekarang
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.dashboard-layout {
  min-height: 100vh;
  background-color: #f8fafc;
}

.navbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 36px;
  background: #ffffff;
  border-bottom: 1px solid #e2e8f0;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.nav-brand {
  display: flex;
  align-items: center;
  gap: 14px;
}

.brand-icon {
  font-size: 28px;
  background: #eef2ff;
  padding: 8px 12px;
  border-radius: 12px;
}

.nav-brand h2 {
  font-size: 18px;
  font-weight: 800;
  color: #0f172a;
}

.subtext {
  font-size: 12px;
  color: #64748b;
}

.nav-user {
  display: flex;
  align-items: center;
  gap: 16px;
}

.user-badge {
  display: flex;
  align-items: center;
  gap: 8px;
  background: #f1f5f9;
  padding: 6px 14px;
  border-radius: 50px;
  font-size: 13px;
  font-weight: 600;
  color: #334155;
}

.btn-logout {
  padding: 8px 16px;
  background: #fee2e2;
  color: #dc2626;
  font-size: 13px;
  font-weight: 700;
  border-radius: 8px;
}

.btn-logout:hover {
  background: #fecaca;
}

.content-container {
  max-width: 1200px;
  margin: 32px auto;
  padding: 0 24px;
}

.alert {
  padding: 12px 18px;
  border-radius: 10px;
  font-size: 14px;
  margin-bottom: 24px;
  display: flex;
  align-items: center;
  gap: 10px;
}

.alert.success {
  background: #ecfdf5;
  color: #065f46;
  border: 1px solid #a7f3d0;
}

.alert.danger {
  background: #fef2f2;
  color: #991b1b;
  border: 1px solid #fecaca;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
  gap: 20px;
  margin-bottom: 28px;
}

.stat-card {
  background: #ffffff;
  padding: 24px;
  border-radius: 16px;
  border: 1px solid #e2e8f0;
  display: flex;
  flex-direction: column;
  gap: 8px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
}

.stat-title {
  font-size: 13px;
  font-weight: 600;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.stat-value {
  font-size: 28px;
  font-weight: 800;
  color: #0f172a;
}

.stat-value.primary {
  color: #4f46e5;
}

.action-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  gap: 16px;
  flex-wrap: wrap;
}

.search-box {
  display: flex;
  align-items: center;
  gap: 10px;
  background: #ffffff;
  border: 1px solid #cbd5e1;
  padding: 10px 16px;
  border-radius: 10px;
  flex: 1;
  max-width: 450px;
}

.search-box input {
  border: none;
  outline: none;
  width: 100%;
  font-size: 14px;
}

.btn-add {
  padding: 12px 20px;
  background: #4f46e5;
  color: #ffffff;
  font-weight: 700;
  font-size: 14px;
  border-radius: 10px;
  box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25);
}

.btn-add:hover {
  background: #4338ca;
}

.table-card {
  background: #ffffff;
  border-radius: 16px;
  border: 1px solid #e2e8f0;
  overflow: hidden;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
}

.table-responsive {
  overflow-x: auto;
}

.data-table {
  width: 100%;
  border-collapse: collapse;
  text-align: left;
  font-size: 14px;
}

.data-table th {
  background: #f8fafc;
  padding: 14px 20px;
  font-weight: 700;
  color: #475569;
  border-bottom: 1px solid #e2e8f0;
}

.data-table td {
  padding: 16px 20px;
  border-bottom: 1px solid #f1f5f9;
  color: #334155;
}

.font-bold {
  font-weight: 600;
  color: #0f172a;
}

.text-muted {
  color: #64748b;
}

.badge {
  display: inline-block;
  padding: 4px 10px;
  border-radius: 50px;
  font-size: 12px;
  font-weight: 700;
}

.badge-success {
  background: #dcfce7;
  color: #15803d;
}

.badge-warning {
  background: #fef3c7;
  color: #b45309;
}

.text-right {
  text-align: right;
}

.action-buttons {
  display: flex;
  justify-content: flex-end;
  gap: 8px;
}

.btn-action {
  padding: 6px 12px;
  font-size: 12px;
  font-weight: 600;
  border-radius: 6px;
}

.btn-action.edit {
  background: #eef2ff;
  color: #4f46e5;
}

.btn-action.edit:hover {
  background: #e0e7ff;
}

.btn-action.delete {
  background: #fef2f2;
  color: #dc2626;
}

.btn-action.delete:hover {
  background: #fee2e2;
}

.loading-state, .empty-state {
  text-align: center;
  padding: 60px 20px;
  color: #64748b;
}

.empty-icon {
  font-size: 48px;
  display: block;
  margin-bottom: 12px;
}

/* Modals */
.modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.6);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 999;
  padding: 20px;
}

.modal-card {
  background: #ffffff;
  width: 100%;
  max-width: 520px;
  border-radius: 18px;
  overflow: hidden;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
}

.modal-card.small {
  max-width: 420px;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 24px;
  border-bottom: 1px solid #e2e8f0;
}

.modal-header h3 {
  font-size: 18px;
  font-weight: 800;
  color: #0f172a;
}

.btn-close {
  background: none;
  font-size: 24px;
  color: #94a3b8;
}

.modal-form, .modal-body {
  padding: 24px;
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
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

.form-group input, .form-group textarea {
  padding: 10px 14px;
  border: 1.5px solid #cbd5e1;
  border-radius: 8px;
  font-size: 14px;
}

.form-group input:focus, .form-group textarea:focus {
  outline: none;
  border-color: #4f46e5;
}

.field-error {
  font-size: 12px;
  color: #dc2626;
  font-weight: 600;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  padding: 16px 24px;
  background: #f8fafc;
  border-top: 1px solid #e2e8f0;
}

.btn-cancel {
  padding: 10px 18px;
  background: #e2e8f0;
  color: #475569;
  font-weight: 600;
  border-radius: 8px;
}

.btn-submit {
  padding: 10px 20px;
  background: #4f46e5;
  color: #ffffff;
  font-weight: 700;
  border-radius: 8px;
}

.btn-danger {
  padding: 10px 20px;
  background: #dc2626;
  color: #ffffff;
  font-weight: 700;
  border-radius: 8px;
}

.text-danger-sm {
  font-size: 13px;
  color: #dc2626;
  margin-top: 6px;
}
</style>