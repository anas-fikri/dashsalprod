<template>
  <div class="flex min-h-screen bg-slate-50 text-slate-800 dark:bg-slate-950 dark:text-slate-100 font-sans transition-colors duration-300 print:bg-white print:text-black">
    <!-- Sidebar (hidden in print) -->
    <Sidebar class="print:hidden" />

    <!-- Main Content Area -->
    <main class="ml-[260px] print:ml-0 flex-1 p-12 flex flex-col gap-8 print:p-0">
      <!-- Header (hidden in print) -->
      <header class="flex justify-between items-center border-b border-slate-200 dark:border-slate-800/60 pb-6 print:hidden">
        <div class="flex flex-col gap-1">
          <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400 font-medium">
            <Link href="/" class="hover:text-slate-950 dark:hover:text-white transition-colors">Dashboard</Link>
            <span class="text-slate-300 dark:text-slate-700">/</span>
            <span class="text-slate-800 dark:text-slate-200">Detail Payroll</span>
          </div>
          <h1 class="font-display font-bold text-2xl tracking-tight text-slate-900 dark:text-white mt-1">Rincian Data Karyawan</h1>
          <p class="text-xs text-slate-500 dark:text-slate-400">
            Daftar rincian transaksi gaji individu karyawan periode {{ formatPeriodLabel(selectedPeriod) }}.
          </p>
        </div>
        
        <!-- Actions -->
        <div class="flex items-center gap-4">
          <a
            :href="exportUrl"
            class="bg-white hover:bg-slate-100 border border-slate-200 text-slate-700 hover:text-slate-900 dark:bg-slate-900 dark:hover:bg-slate-800 dark:border-slate-800 dark:hover:border-slate-700 dark:text-slate-300 dark:hover:text-white rounded-lg px-4 py-1.5 text-xs font-semibold cursor-pointer transition-all flex items-center gap-2 shadow-xs"
          >
            <Download class="w-3.5 h-3.5 text-blue-500 dark:text-blue-400" />
            <span>Ekspor Excel</span>
          </a>

          <button
            @click="triggerPrint"
            class="bg-white hover:bg-slate-100 border border-slate-200 text-slate-700 hover:text-slate-900 dark:bg-slate-900 dark:hover:bg-slate-800 dark:border-slate-800 dark:hover:border-slate-700 dark:text-slate-300 dark:hover:text-white rounded-lg px-4 py-1.5 text-xs font-semibold cursor-pointer transition-all flex items-center gap-2 shadow-xs"
          >
            <Printer class="w-3.5 h-3.5" />
            <span>Cetak Laporan</span>
          </button>
          
          <Link
            href="/"
            class="bg-white hover:bg-slate-100 border border-slate-200 text-slate-700 hover:text-slate-900 dark:bg-slate-900 dark:hover:bg-slate-800 dark:border-slate-800 dark:hover:border-slate-700 dark:text-slate-300 dark:hover:text-white rounded-lg px-4 py-1.5 text-xs font-semibold cursor-pointer transition-all flex items-center gap-2 shadow-xs"
          >
            <ArrowLeft class="w-3.5 h-3.5" />
            <span>Kembali</span>
          </Link>
        </div>
      </header>

      <!-- Print Title (only visible in print) -->
      <div class="hidden print:block text-center mb-8 border-b-2 border-black pb-4">
        <h1 class="text-2xl font-bold font-display uppercase">Laporan Rincian Gaji Karyawan</h1>
        <p class="text-sm font-semibold mt-1">Periode: {{ formatPeriodLabel(selectedPeriod) }}</p>
        <p class="text-[10px] text-slate-500 mt-1">Tanggal Cetak: {{ formatTimestamp(new Date()) }}</p>
      </div>

      <!-- Filters Panel (hidden in print) -->
      <div class="bg-white dark:bg-slate-900/30 border border-slate-200 dark:border-slate-800/50 rounded-2xl p-6 flex flex-col gap-4 print:hidden shadow-xs">
        <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Filter & Pencarian</span>
        
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
          <!-- Search input -->
          <div class="md:col-span-2">
            <input
              v-model="search"
              @input="debouncedSearch"
              type="text"
              placeholder="Cari NIK atau Nama Karyawan..."
              class="w-full bg-white dark:bg-slate-950/80 border border-slate-200 dark:border-slate-800 focus:border-blue-500/60 rounded-xl px-4 py-2 text-xs text-slate-800 dark:text-slate-200 placeholder-slate-400 dark:placeholder-slate-500 outline-none transition-all focus:ring-2 focus:ring-blue-500/10"
            />
          </div>

          <!-- Entity filter -->
          <div>
            <select
              v-model="company"
              @change="applyFilters"
              class="w-full bg-white dark:bg-slate-950/80 border border-slate-200 dark:border-slate-800 focus:border-blue-500/60 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-200 outline-none transition-all cursor-pointer"
            >
              <option value="">Semua Entitas</option>
              <option value="NES">PT NES</option>
              <option value="NPA">PT NPA</option>
            </select>
          </div>

          <!-- Structural filter -->
          <div>
            <select
              v-model="structural"
              @change="applyFilters"
              class="w-full bg-white dark:bg-slate-950/80 border border-slate-200 dark:border-slate-800 focus:border-blue-500/60 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-200 outline-none transition-all cursor-pointer"
            >
              <option value="">Semua Tingkat</option>
              <option value="STR1">STR1 (Manajemen Atas)</option>
              <option value="STR2">STR2 (Manajemen Lini)</option>
              <option value="NSTR">NSTR (Pelaksana)</option>
            </select>
          </div>

          <!-- Function filter -->
          <div>
            <select
              v-model="functionFilter"
              @change="applyFilters"
              class="w-full bg-white dark:bg-slate-950/80 border border-slate-200 dark:border-slate-800 focus:border-blue-500/60 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-200 outline-none transition-all cursor-pointer"
            >
              <option value="">Semua Fungsi</option>
              <option value="true">Production</option>
              <option value="false">Non-Production</option>
            </select>
          </div>
        </div>

        <div class="flex justify-between items-center border-t border-slate-100 dark:border-slate-800/40 pt-4 mt-2">
          <!-- Period select -->
          <div class="flex items-center gap-2">
            <span class="text-xs text-slate-500">Pilih Periode:</span>
            <select
              v-model="activePeriod"
              @change="applyFilters"
              class="bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg px-3 py-1.5 text-xs text-slate-800 dark:text-white outline-none cursor-pointer"
            >
              <option v-for="p in availablePeriods" :key="p" :value="p">
                {{ formatPeriodLabel(p) }}
              </option>
            </select>
          </div>

          <!-- Reset button -->
          <button
            @click="resetFilters"
            class="text-slate-500 hover:text-slate-800 dark:hover:text-slate-300 text-xs font-semibold cursor-pointer transition-colors"
          >
            Atur Ulang Filter
          </button>
        </div>
      </div>

      <!-- Table Card -->
      <div class="bg-white dark:bg-slate-900/40 border border-slate-200 dark:border-slate-800/50 rounded-2xl p-6 flex flex-col gap-4 print:border-none print:p-0 print:bg-transparent shadow-xs">
        <div class="flex justify-between items-center print:hidden">
          <span class="font-display font-semibold text-slate-800 dark:text-slate-200">Daftar Transaksi ({{ records.length }} Karyawan ditemukan)</span>
          <span class="text-[10px] text-slate-500">Mata Uang: IDR (Rupiah)</span>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full border-collapse text-left text-[11px] print:text-black">
            <thead>
              <tr class="border-b border-slate-200 dark:border-slate-800 text-slate-500 dark:text-slate-400 print:text-black print:border-black">
                <th class="py-3 px-3 font-semibold">NIK</th>
                <th class="py-3 px-3 font-semibold">Nama</th>
                <th class="py-3 px-3 font-semibold">Entitas</th>
                <th class="py-3 px-3 font-semibold">Departemen</th>
                <th class="py-3 px-3 font-semibold">Jabatan</th>
                <th class="py-3 px-3 font-semibold">Struktural</th>
                <th class="py-3 px-3 font-semibold">Fungsi Kerja</th>
                <th class="py-3 px-3 font-semibold text-right">Gaji Pokok</th>
                <th class="py-3 px-3 font-semibold text-right">Gaji Bruto</th>
                <th class="py-3 px-3 font-semibold text-right">Gaji Nett</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800/20 print:divide-black">
              <tr v-for="emp in records" :key="emp.nik" class="hover:bg-slate-50 dark:hover:bg-slate-900/20 print:hover:bg-transparent">
                <td class="py-2.5 px-3 font-mono text-slate-600 dark:text-slate-300 print:text-black">{{ emp.nik }}</td>
                <td class="py-2.5 px-3 text-slate-900 dark:text-slate-200 font-medium print:text-black">{{ emp.nama }}</td>
                <td class="py-2.5 px-3 text-slate-500 dark:text-slate-400 print:text-black">{{ emp.company_code }}</td>
                <td class="py-2.5 px-3 text-slate-500 dark:text-slate-400 print:text-black">{{ emp.sub_department || '-' }}</td>
                <td class="py-2.5 px-3 text-slate-500 dark:text-slate-400 print:text-black">{{ emp.job_title || '-' }}</td>
                <td class="py-2.5 px-3 text-slate-500 dark:text-slate-400 print:text-black">{{ emp.structural_status }}</td>
                <td class="py-2.5 px-3">
                  <span
                    class="px-2 py-0.5 rounded text-[9px] font-semibold print:border print:border-black"
                    :class="emp.is_production ? 'bg-blue-500/10 text-blue-600 dark:text-blue-400 print:bg-transparent print:text-black' : 'bg-purple-500/10 text-purple-600 dark:text-purple-400 print:bg-transparent print:text-black'"
                  >
                    {{ emp.is_production ? 'Production' : 'Non-Prod' }}
                  </span>
                </td>
                <td class="py-2.5 px-3 text-right text-slate-600 dark:text-slate-400 font-mono print:text-black">{{ formatCurrency(emp.basic_salary) }}</td>
                <td class="py-2.5 px-3 text-right text-slate-600 dark:text-slate-400 font-mono print:text-black">{{ formatCurrency(emp.gross_salary) }}</td>
                <td class="py-2.5 px-3 text-right text-slate-900 dark:text-slate-200 font-mono font-medium print:text-black">{{ formatCurrency(emp.net_salary) }}</td>
              </tr>
              <tr v-if="records.length === 0">
                <td colspan="10" class="py-12 text-center text-slate-500 print:text-black">
                  Tidak ada karyawan yang cocok dengan kriteria filter.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import Sidebar from '@/Components/Sidebar.vue';
import { ArrowLeft, Printer, Search, Download } from 'lucide-vue-next';

const props = defineProps({
  availablePeriods: Array,
  selectedPeriod: String,
  records: Array,
  filters: Object,
});

const search = ref(props.filters.search || '');
const company = ref(props.filters.company || '');
const structural = ref(props.filters.structural || '');
const functionFilter = ref(props.filters.function || '');
const activePeriod = ref(props.selectedPeriod);
const exportUrl = computed(() => {
  const params = new URLSearchParams();
  if (activePeriod.value) params.append('period', activePeriod.value);
  if (search.value) params.append('search', search.value);
  if (company.value) params.append('company', company.value);
  if (structural.value) params.append('structural', structural.value);
  if (functionFilter.value) params.append('function', functionFilter.value);
  return `/export/payroll?${params.toString()}`;
});

let searchTimeout = null;

const formatPeriodLabel = (period) => {
  if (!period) return '';
  const parts = period.split('-');
  const months = [
    'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
  ];
  const mIndex = parseInt(parts[1]) - 1;
  return `${months[mIndex] || parts[1]} ${parts[0]}`;
};

const formatCurrency = (val) => {
  if (!val) return 'Rp 0';
  return 'Rp ' + parseFloat(val).toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
};

const formatTimestamp = (d) => {
  return d.toLocaleString('id-ID', {
    day: '2-digit',
    month: 'long',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};

const applyFilters = () => {
  router.get('/detail', {
    period: activePeriod.value,
    search: search.value,
    company: company.value,
    structural: structural.value,
    function: functionFilter.value,
  }, { preserveState: true, replace: true });
};

const debouncedSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    applyFilters();
  }, 300);
};

const resetFilters = () => {
  search.value = '';
  company.value = '';
  structural.value = '';
  functionFilter.value = '';
  applyFilters();
};

const triggerPrint = () => {
  window.print();
};
</script>

<style scoped>
@media print {
  /* Hide scrollbars and reset layout sizes */
  body, main {
    background: white !important;
    color: black !important;
  }
}
</style>
