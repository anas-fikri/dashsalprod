<template>
  <div class="flex min-h-screen bg-slate-50 text-slate-800 dark:bg-slate-950 dark:text-slate-100 font-sans transition-colors duration-300">
    <!-- Sidebar -->
    <Sidebar />

    <!-- Main Content Area -->
    <main class="ml-[260px] flex-1 p-12 flex flex-col gap-8">
      <!-- Header -->
      <header class="flex justify-between items-center border-b border-slate-200 dark:border-slate-800/60 pb-6 transition-colors duration-300">
        <div class="flex flex-col gap-1">
          <h1 class="font-display font-bold text-2xl tracking-tight text-slate-900 dark:text-white">Import Data Bulanan</h1>
          <p class="text-xs text-slate-500 dark:text-slate-400">
            Unggah file Excel payroll atau masukkan metrik operasional bulanan.
          </p>
        </div>
      </header>

      <!-- Alert Message -->
      <div v-if="$page.props.flash.success" class="bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400 text-xs px-4 py-3 rounded-lg flex items-center gap-2">
        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 dark:bg-emerald-400"></span>
        {{ $page.props.flash.success }}
      </div>
      <div v-if="$page.props.flash.error" class="bg-red-500/10 border border-red-500/20 text-red-600 dark:text-red-400 text-xs px-4 py-3 rounded-lg flex items-center gap-2">
        <span class="w-1.5 h-1.5 rounded-full bg-red-500 dark:bg-red-400"></span>
        {{ $page.props.flash.error }}
      </div>

      <!-- Import Panels -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- 1. Payroll Excel Upload Card -->
        <div class="bg-white border border-slate-200/80 dark:bg-slate-900/40 dark:border-slate-800/50 rounded-2xl p-8 flex flex-col gap-6 shadow-xs">
          <div class="flex flex-col gap-1">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-white">1. Unggah Excel Payroll</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">Pilih file Excel berisi data gaji karyawan (Sheet 'TEMP').</p>
          </div>

          <!-- Drag & Drop Uploader -->
          <div 
            @dragover.prevent="dragOver = true"
            @dragleave.prevent="dragOver = false"
            @drop.prevent="handleFileDrop"
            class="border-2 border-dashed rounded-2xl p-10 flex flex-col items-center justify-center gap-4 transition-all duration-300 bg-slate-50/50 dark:bg-slate-950/40 cursor-pointer"
            :class="dragOver ? 'border-blue-500 bg-blue-500/5' : 'border-slate-200 hover:border-slate-300 dark:border-slate-800 dark:hover:border-slate-700'"
            @click="triggerFileInput"
          >
            <input 
              type="file" 
              ref="fileInput" 
              @change="handleFileSelect" 
              class="hidden" 
              accept=".xlsx,.xls,.csv"
            />
            
            <div class="w-12 h-12 rounded-full bg-slate-100 dark:bg-slate-900 flex items-center justify-center border border-slate-200 dark:border-slate-800 text-blue-500 dark:text-blue-400 shadow-xs">
              <UploadCloud class="w-6 h-6" />
            </div>

            <div class="text-center">
              <p class="text-sm font-semibold text-slate-700 dark:text-slate-200" v-if="!selectedFile">Tarik & lepas file di sini, atau cari file</p>
              <p class="text-sm font-semibold text-blue-500 dark:text-blue-400 truncate max-w-xs" v-else>{{ selectedFile.name }}</p>
              <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-1">Hanya mendukung file .xlsx, .xls, atau .csv</p>
            </div>
          </div>

          <form @submit.prevent="submitPayroll" class="flex flex-col gap-4">
            <button
              type="submit"
              :disabled="!selectedFile || uploadForm.processing"
              class="w-full bg-gradient-to-r from-blue-500 to-violet-500 hover:from-blue-600 hover:to-violet-600 text-white rounded-xl py-3 text-sm font-semibold cursor-pointer transition-all duration-300 hover:shadow-[0_0_15px_rgba(139,92,246,0.3)] disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
            >
              <span v-if="uploadForm.processing">Membaca file...</span>
              <span v-else>Proses & Validasi Data</span>
            </button>
          </form>

          <!-- Download Templates -->
          <div class="flex flex-col gap-2.5 mt-2 border-t border-slate-200 dark:border-slate-800/40 pt-4">
            <span class="text-[10px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Unduh Template Pendukung:</span>
            <div class="flex">
              <a
                href="/import/templates/payroll"
                class="w-full bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 dark:bg-slate-950/80 dark:hover:bg-slate-900 dark:border-slate-800 dark:text-slate-300 dark:hover:text-white rounded-xl py-2 px-3 text-[10px] font-semibold text-center transition-all cursor-pointer flex items-center justify-center gap-1.5 shadow-xs"
              >
                <Download class="w-3.5 h-3.5 text-blue-500 dark:text-blue-400" />
                <span>Unduh Template Payroll Excel</span>
              </a>
            </div>
          </div>
        </div>

        <!-- 2. Operational Metrics Input Card -->
        <div class="bg-white border border-slate-200/80 dark:bg-slate-900/40 dark:border-slate-800/50 rounded-2xl p-8 flex flex-col gap-6 shadow-xs">
          <div class="flex flex-col gap-1">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-white">2. Input Metrik Operasional</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">Masukkan metrik penjualan dan biaya produksi bulanan.</p>
          </div>

          <form @submit.prevent="submitMetrics" class="flex flex-col gap-5">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label for="period" class="block text-[10px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Bulan & Tahun</label>
                <input
                  id="period"
                  v-model="metricsForm.period"
                  type="month"
                  required
                  class="w-full bg-white border border-slate-200 text-slate-800 focus:border-blue-500/60 dark:bg-slate-950/80 dark:border-slate-800 dark:text-slate-200 rounded-xl px-4 py-2.5 text-xs outline-none transition-all focus:ring-2 focus:ring-blue-500/10"
                />
              </div>

              <div>
                <label for="sales" class="block text-[10px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Total Penjualan (Rupiah)</label>
                <input
                  id="sales"
                  v-model="metricsForm.total_sales"
                  type="number"
                  required
                  min="0"
                  placeholder="Contoh: 12500000000"
                  class="w-full bg-white border border-slate-200 text-slate-800 focus:border-blue-500/60 dark:bg-slate-950/80 dark:border-slate-800 dark:text-slate-200 rounded-xl px-4 py-2.5 text-xs outline-none transition-all focus:ring-2 focus:ring-blue-500/10"
                />
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label for="prod_cost" class="block text-[10px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Total Biaya Produksi (Rupiah)</label>
                <input
                  id="prod_cost"
                  v-model="metricsForm.production_cost"
                  type="number"
                  required
                  min="0"
                  placeholder="Contoh: 8500000000"
                  class="w-full bg-white border border-slate-200 text-slate-800 focus:border-blue-500/60 dark:bg-slate-950/80 dark:border-slate-800 dark:text-slate-200 rounded-xl px-4 py-2.5 text-xs outline-none transition-all focus:ring-2 focus:ring-blue-500/10"
                />
              </div>

              <div>
                <label for="volume" class="block text-[10px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Volume Produksi (Pcs/Unit)</label>
                <input
                  id="volume"
                  v-model="metricsForm.production_volume"
                  type="number"
                  required
                  min="0"
                  placeholder="Contoh: 45000"
                  class="w-full bg-white border border-slate-200 text-slate-800 focus:border-blue-500/60 dark:bg-slate-950/80 dark:border-slate-800 dark:text-slate-200 rounded-xl px-4 py-2.5 text-xs outline-none transition-all focus:ring-2 focus:ring-blue-500/10"
                />
              </div>
            </div>

            <button
              type="submit"
              :disabled="metricsForm.processing"
              class="w-full bg-gradient-to-r from-blue-500 to-violet-500 hover:from-blue-600 hover:to-violet-600 text-white rounded-xl py-3 text-sm font-semibold cursor-pointer transition-all duration-300 hover:shadow-[0_0_15px_rgba(139,92,246,0.3)] disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2 mt-2 shadow-xs"
            >
              <span v-if="metricsForm.processing">Menyimpan...</span>
              <span v-else>Simpan Metrik Operasional</span>
            </button>
          </form>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Sidebar from '@/Components/Sidebar.vue';
import { UploadCloud, Download } from 'lucide-vue-next';

const dragOver = ref(false);
const fileInput = ref(null);
const selectedFile = ref(null);

const uploadForm = useForm({
  file: null,
});

const metricsForm = useForm({
  period: '',
  total_sales: '',
  production_cost: '',
  production_volume: '',
});

const triggerFileInput = () => {
  fileInput.value.click();
};

const handleFileSelect = (e) => {
  const file = e.target.files[0];
  if (file) {
    selectedFile.value = file;
    uploadForm.file = file;
  }
};

const handleFileDrop = (e) => {
  dragOver.value = false;
  const file = e.dataTransfer.files[0];
  if (file && (file.name.endsWith('.xlsx') || file.name.endsWith('.xls') || file.name.endsWith('.csv'))) {
    selectedFile.value = file;
    uploadForm.file = file;
  }
};

const submitPayroll = () => {
  uploadForm.post('/import/payroll', {
    forceFormData: true,
  });
};

const submitMetrics = () => {
  metricsForm.post('/import/operational', {
    onSuccess: () => {
      metricsForm.reset();
    }
  });
};
</script>
