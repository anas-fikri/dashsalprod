<template>
  <div class="flex min-h-screen bg-slate-50 text-slate-800 dark:bg-slate-950 dark:text-slate-100 font-sans transition-colors duration-300 print:bg-white print:text-black">
    <!-- Sidebar (hidden in print) -->
    <Sidebar class="print:hidden" />

    <!-- Main Content Area -->
    <main class="ml-[260px] print:ml-0 flex-1 p-12 flex flex-col gap-8 print:p-0">
      <!-- Header (hidden in print) -->
      <header class="flex justify-between items-center border-b border-slate-200 dark:border-slate-800/60 pb-6 transition-colors duration-300 print:hidden">
        <div class="flex flex-col gap-1">
          <h1 class="font-display font-bold text-2xl tracking-tight text-slate-900 dark:text-white">Tren Bulanan & Analisis Gaji</h1>
          <p class="text-xs text-slate-500 dark:text-slate-400">
            Bandingkan pengeluaran gaji manpower dengan metrik penjualan dan biaya produksi.
          </p>
        </div>
        
        <!-- Top Actions -->
        <div class="flex items-center gap-4">
          <div class="flex items-center gap-2" v-if="availablePeriods.length > 0">
            <label for="period" class="text-xs text-slate-500 dark:text-slate-400 font-medium">Periode:</label>
            <select
              id="period"
              v-model="activePeriod"
              @change="changePeriod"
              class="bg-white border border-slate-200 dark:bg-slate-900 dark:border-slate-800 rounded-lg px-3 py-1.5 text-xs text-slate-800 dark:text-white outline-none cursor-pointer hover:border-slate-300 dark:hover:border-slate-700 transition-all"
            >
              <option v-for="p in availablePeriods" :key="p" :value="p">
                {{ formatPeriodLabel(p) }}
              </option>
            </select>
          </div>

          <a
            href="/export/trends"
            class="bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 dark:bg-slate-900 dark:hover:bg-slate-800 dark:border-slate-800 dark:text-slate-300 dark:hover:text-white rounded-lg px-4 py-1.5 text-xs font-semibold transition-all cursor-pointer flex items-center gap-1.5 shadow-xs"
          >
            <Download class="w-3.5 h-3.5 text-blue-500 dark:text-blue-400" />
            <span>Ekspor Tren</span>
          </a>

          <button
            @click="triggerPrint"
            class="bg-white hover:bg-slate-100 border border-slate-200 text-slate-700 hover:text-slate-900 dark:bg-slate-900 dark:hover:bg-slate-800 dark:border-slate-800 dark:hover:border-slate-700 dark:text-slate-300 dark:hover:text-white rounded-lg px-4 py-1.5 text-xs font-semibold cursor-pointer transition-all flex items-center gap-1.5 shadow-xs"
          >
            <Printer class="w-3.5 h-3.5 text-violet-500 dark:text-violet-400" />
            <span>Cetak Dashboard</span>
          </button>

          <Link
            :href="`/detail?period=${activePeriod}`"
            class="bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 dark:bg-slate-900 dark:hover:bg-slate-800 dark:border-slate-800 dark:text-slate-300 dark:hover:text-white rounded-lg px-4 py-1.5 text-xs font-semibold transition-all cursor-pointer flex items-center gap-1.5 shadow-xs"
          >
            <ListCollapse class="w-3.5 h-3.5" />
            <span>Lihat Detail Karyawan</span>
          </Link>

          <Link
            v-if="isAdmin"
            href="/import"
            class="bg-gradient-to-r from-blue-500 to-violet-500 hover:from-blue-600 hover:to-violet-600 text-white rounded-lg px-4 py-1.5 text-xs font-semibold hover:shadow-[0_0_15px_rgba(139,92,246,0.3)] transition-all cursor-pointer shadow-sm"
          >
            + Import Excel
          </Link>
        </div>
      </header>

      <!-- Print Title (only visible in print) -->
      <div class="hidden print:block text-center mb-8 border-b-2 border-black pb-4">
        <h1 class="text-2xl font-bold font-display uppercase">Laporan Analisis & Tren Penggajian Industri</h1>
        <p class="text-sm font-semibold mt-1">Periode Aktif: {{ formatPeriodLabel(activePeriod) }}</p>
        <p class="text-[10px] text-slate-500 mt-1">Tanggal Cetak: {{ formatTimestamp(new Date()) }}</p>
      </div>

      <!-- Alert Message -->
      <div v-if="$page.props.flash.success" class="bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400 text-xs px-4 py-3 rounded-lg flex items-center gap-2">
        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 dark:bg-emerald-400"></span>
        {{ $page.props.flash.success }}
      </div>
      <div v-if="$page.props.flash.error" class="bg-red-500/10 border border-red-500/20 text-red-600 dark:text-red-400 text-xs px-4 py-3 rounded-lg flex items-center gap-2">
        <span class="w-1.5 h-1.5 rounded-full bg-red-500 dark:bg-red-400"></span>
        {{ $page.props.flash.error }}
      </div>

      <!-- KPI Cards Grid -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white border border-slate-200/85 dark:bg-slate-900/40 dark:backdrop-blur-md dark:border-slate-800/50 rounded-2xl p-6 relative overflow-hidden pl-7 hover:-translate-y-1 transition-all duration-300 hover:shadow-lg shadow-xs">
          <div class="absolute left-0 top-0 w-1 h-full bg-violet-500"></div>
          <span class="text-slate-500 dark:text-slate-400 text-xs font-medium block">Total Beban Manpower</span>
          <span class="font-display font-bold text-xl text-slate-900 dark:text-white mt-2 block">{{ formatRupiah(currentTrend?.total_manpower_cost) }}</span>
          <span class="text-[10px] text-slate-400 mt-2 block">Akumulasi gaji take-home pay</span>
        </div>

        <div class="bg-white border border-slate-200/85 dark:bg-slate-900/40 dark:backdrop-blur-md dark:border-slate-800/50 rounded-2xl p-6 relative overflow-hidden pl-7 hover:-translate-y-1 transition-all duration-300 hover:shadow-lg shadow-xs">
          <div class="absolute left-0 top-0 w-1 h-full bg-blue-500"></div>
          <span class="text-slate-500 dark:text-slate-400 text-xs font-medium block">Manpower vs Penjualan</span>
          <span class="font-display font-bold text-xl text-slate-900 dark:text-white mt-2 block">{{ currentTrend?.labor_to_sales_ratio ?? 0 }}%</span>
          <span class="text-[10px] text-slate-400 mt-2 block flex items-center gap-1">
            Target aman industri: &lt; 15%
          </span>
        </div>

        <div class="bg-white border border-slate-200/85 dark:bg-slate-900/40 dark:backdrop-blur-md dark:border-slate-800/50 rounded-2xl p-6 relative overflow-hidden pl-7 hover:-translate-y-1 transition-all duration-300 hover:shadow-lg shadow-xs">
          <div class="absolute left-0 top-0 w-1 h-full bg-emerald-500"></div>
          <span class="text-slate-500 dark:text-slate-400 text-xs font-medium block">Gaji Prod vs Biaya Prod</span>
          <span class="font-display font-bold text-xl text-slate-900 dark:text-white mt-2 block">{{ currentTrend?.labor_to_prod_cost_ratio ?? 0 }}%</span>
          <span class="text-[10px] text-slate-400 mt-2 block">Efisiensi tenaga kerja lapangan</span>
        </div>

        <div class="bg-white border border-slate-200/85 dark:bg-slate-900/40 dark:backdrop-blur-md dark:border-slate-800/50 rounded-2xl p-6 relative overflow-hidden pl-7 hover:-translate-y-1 transition-all duration-300 hover:shadow-lg shadow-xs">
          <div class="absolute left-0 top-0 w-1 h-full bg-amber-500"></div>
          <span class="text-slate-500 dark:text-slate-400 text-xs font-medium block">Karyawan Aktif</span>
          <span class="font-display font-bold text-xl text-slate-900 dark:text-white mt-2 block">{{ currentTrend?.employee_count ?? 0 }} Orang</span>
          <span class="text-[10px] text-slate-400 mt-2 block">NES: {{ currentTrend?.prod_employee_count }} Prod | {{ currentTrend?.non_prod_employee_count }} Pendukung</span>
        </div>
      </div>

      <!-- Main Dashboard Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Chart Panel -->
        <div class="lg:col-span-2 bg-white border border-slate-200/80 dark:bg-slate-900/40 dark:border-slate-800/50 rounded-2xl p-6 flex flex-col gap-6 shadow-xs transition-colors duration-300">
          <div class="flex justify-between items-center">
            <span class="font-display font-semibold text-slate-800 dark:text-slate-200">Grafik Tren Kinerja vs Manpower Cost</span>
            <span class="text-[10px] text-slate-400 dark:text-slate-500">Januari - Desember</span>
          </div>
          <div class="h-[320px] w-full relative">
            <canvas id="monthlyTrendChart" ref="chartCanvas"></canvas>
          </div>
        </div>

        <!-- AI Executive Panel -->
        <div class="bg-gradient-to-br from-slate-100 to-white border border-slate-200/80 dark:from-slate-900/60 dark:to-slate-900/20 dark:border-violet-500/20 rounded-2xl p-6 flex flex-col gap-5 relative shadow-xs transition-all duration-300">
          <!-- AI Badge -->
          <div class="absolute -top-2.5 right-6 bg-violet-600 text-white text-[9px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wider shadow-[0_0_10px_rgba(139,92,246,0.3)]">
            AI Assistant
          </div>
          <div class="flex items-center gap-2 text-violet-600 dark:text-violet-400 font-semibold text-sm">
            <div class="w-2 h-2 bg-violet-600 dark:bg-violet-500 rounded-full animate-ping"></div>
            <span>AI Executive Insight</span>
          </div>
          
          <div class="flex flex-col gap-4 text-slate-700 dark:text-slate-300 text-xs leading-relaxed">
            <p class="font-bold text-slate-900 dark:text-white text-sm border-b border-slate-200 dark:border-slate-800/40 pb-2 transition-colors">
              {{ aiInsight.title }}
            </p>
            <p v-html="aiInsight.summary"></p>
            
            <div class="border-l-2 border-violet-500 pl-4 py-1 italic text-slate-500 dark:text-slate-400 bg-violet-500/10 dark:bg-violet-500/5 rounded-r-lg p-2.5">
              "{{ aiInsight.recommendation }}"
            </div>
          </div>
        </div>

        <!-- Department Breakdown Table -->
        <div class="lg:col-span-3 bg-white border border-slate-200/80 dark:bg-slate-900/40 dark:border-slate-800/50 rounded-2xl p-6 flex flex-col gap-4 shadow-xs transition-colors duration-300">
          <div class="flex justify-between items-center">
            <span class="font-display font-semibold text-slate-800 dark:text-slate-200">Rincian Distribusi Gaji Per Departemen ({{ formatPeriodLabel(selectedPeriod) }})</span>
            <span class="text-[10px] text-slate-400 dark:text-slate-500">Berdasarkan data payroll terunggah</span>
          </div>

          <div class="overflow-x-auto">
            <table class="w-full border-collapse text-left text-xs">
              <thead>
                <tr class="border-b border-slate-200 dark:border-slate-800/60 text-slate-500 dark:text-slate-400">
                  <th class="py-3 px-4 font-semibold">Departemen</th>
                  <th class="py-3 px-4 font-semibold">Fungsi Kerja</th>
                  <th class="py-3 px-4 font-semibold text-right">Jumlah Staff</th>
                  <th class="py-3 px-4 font-semibold text-right">Total Gaji Pokok</th>
                  <th class="py-3 px-4 font-semibold text-right">Gaji Diterima (Nett)</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 dark:divide-slate-800/20">
                <tr v-for="dept in departments" :key="dept.department" class="hover:bg-slate-50 dark:hover:bg-slate-900/20 transition-colors">
                  <td class="py-4 px-4 font-medium text-slate-800 dark:text-slate-200">{{ dept.department || 'Lain-lain' }}</td>
                  <td class="py-4 px-4">
                    <span
                      class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold"
                      :class="dept.is_production ? 'bg-blue-500/10 text-blue-600 dark:text-blue-400' : 'bg-purple-500/10 text-purple-600 dark:text-purple-400'"
                    >
                      {{ dept.is_production ? 'Production' : 'Non-Production' }}
                    </span>
                  </td>
                  <td class="py-4 px-4 text-right text-slate-600 dark:text-slate-300">{{ dept.staff_count }} Orang</td>
                  <td class="py-4 px-4 text-right text-slate-700 dark:text-slate-300 font-mono">{{ formatCurrency(dept.total_basic_salary) }}</td>
                  <td class="py-4 px-4 text-right text-slate-900 dark:text-slate-100 font-mono font-medium">{{ formatCurrency(dept.total_net_salary) }}</td>
                </tr>
                <tr v-if="departments.length === 0">
                  <td colspan="5" class="py-8 text-center text-slate-400 dark:text-slate-500">
                    Tidak ada data departemen untuk periode ini. Silakan unggah file payroll terlebih dahulu.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed, watch } from 'vue';
import { router, Link, usePage } from '@inertiajs/vue3';
import Sidebar from '@/Components/Sidebar.vue';
import Chart from 'chart.js/auto';
import { ListCollapse, Download, Printer } from 'lucide-vue-next';

const page = usePage();
const props = defineProps({
  trends: Array,
  availablePeriods: Array,
  selectedPeriod: String,
  departments: Array,
  currentTrend: Object,
  aiInsight: Object,
});

const activePeriod = ref(props.selectedPeriod);
const chartCanvas = ref(null);
let chartInstance = null;

const isAdmin = computed(() => page.props.auth.user?.role === 'admin');

// Format period label to readable (e.g. 2026-03 -> Maret 2026)
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

const formatRupiah = (val) => {
  if (val === undefined || val === null) return 'Rp 0';
  if (val >= 1000000000) {
    return 'Rp ' + (val / 1000000000).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' M';
  }
  if (val >= 1000000) {
    return 'Rp ' + (val / 1000000).toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 }) + ' Jt';
  }
  return 'Rp ' + val.toLocaleString('id-ID');
};

const formatCurrency = (val) => {
  if (!val) return 'Rp 0';
  return 'Rp ' + parseFloat(val).toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
};

const changePeriod = () => {
  router.get('/', { period: activePeriod.value }, { preserveState: true });
};

const triggerPrint = () => {
  window.print();
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

// Render the double-Y-axis correlation chart
const renderChart = () => {
  if (!chartCanvas.value) return;

  if (chartInstance) {
    chartInstance.destroy();
  }

  const ctx = chartCanvas.value.getContext('2d');
  
  // Prepare datasets from trends prop
  const labels = props.trends.map(t => formatPeriodLabel(t.period));
  const manpowerCosts = props.trends.map(t => t.total_manpower_cost / 1000000); // in Millions
  const sales = props.trends.map(t => t.total_sales / 1000000); // in Millions
  const prodCosts = props.trends.map(t => t.production_cost / 1000000); // in Millions

  // Dynamic theme colors
  const isDarkTheme = document.documentElement.classList.contains('dark');
  const textColor = isDarkTheme ? '#9ca3af' : '#475569';
  const gridColor = isDarkTheme ? 'rgba(255, 255, 255, 0.03)' : 'rgba(0, 0, 0, 0.05)';
  const tooltipBg = isDarkTheme ? 'rgba(15, 23, 42, 0.95)' : 'rgba(255, 255, 255, 0.95)';
  const tooltipBorder = isDarkTheme ? 'rgba(255, 255, 255, 0.08)' : 'rgba(0, 0, 0, 0.08)';

  chartInstance = new Chart(ctx, {
    data: {
      labels: labels,
      datasets: [
        {
          label: 'Total Manpower (Juta Rp)',
          data: manpowerCosts,
          type: 'line',
          borderColor: '#8b5cf6', // Purple
          backgroundColor: isDarkTheme ? 'rgba(139, 92, 246, 0.1)' : 'rgba(139, 92, 246, 0.04)',
          yAxisID: 'yRight',
          borderWidth: 3,
          tension: 0.35,
          fill: true,
          pointBackgroundColor: '#8b5cf6',
          pointBorderColor: '#fff',
          pointHoverRadius: 6,
        },
        {
          label: 'Total Penjualan (Juta Rp)',
          data: sales,
          type: 'bar',
          backgroundColor: '#3b82f6', // Blue Accent
          yAxisID: 'yLeft',
          borderRadius: 6,
          barPercentage: 0.5,
          categoryPercentage: 0.8
        },
        {
          label: 'Biaya Produksi (Juta Rp)',
          data: prodCosts,
          type: 'bar',
          backgroundColor: '#10b981', // Green Accent
          yAxisID: 'yLeft',
          borderRadius: 6,
          barPercentage: 0.5,
          categoryPercentage: 0.8
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      interaction: {
        mode: 'index',
        intersect: false,
      },
      plugins: {
        legend: {
          position: 'top',
          labels: {
            color: textColor,
            font: { family: 'Plus Jakarta Sans', size: 10, weight: '500' },
            boxWidth: 12,
            boxHeight: 12,
            padding: 15
          }
        },
        tooltip: {
          padding: 12,
          bodyFont: { family: 'Plus Jakarta Sans', size: 11 },
          titleFont: { family: 'Plus Jakarta Sans', size: 12, weight: '600' },
          bodyColor: isDarkTheme ? '#f3f4f6' : '#1f2937',
          titleColor: isDarkTheme ? '#ffffff' : '#111827',
          backgroundColor: tooltipBg,
          borderColor: tooltipBorder,
          borderWidth: 1,
        }
      },
      scales: {
        yLeft: {
          type: 'linear',
          display: true,
          position: 'left',
          title: {
            display: true,
            text: 'Operational Performance (Juta Rp)',
            color: textColor,
            font: { size: 10 }
          },
          grid: { color: gridColor },
          ticks: { color: textColor, font: { size: 9 } }
        },
        yRight: {
          type: 'linear',
          display: true,
          position: 'right',
          title: {
            display: true,
            text: 'Manpower Cost (Juta Rp)',
            color: textColor,
            font: { size: 10 }
          },
          grid: { drawOnChartArea: false },
          ticks: { color: textColor, font: { size: 9 } }
        },
        x: {
          grid: { color: gridColor },
          ticks: { color: textColor, font: { size: 9 } }
        }
      }
    }
  });
};

watch(() => props.trends, () => {
  renderChart();
}, { deep: true });

onMounted(() => {
  renderChart();
  window.addEventListener('theme-changed', renderChart);
});

onUnmounted(() => {
  window.removeEventListener('theme-changed', renderChart);
});
</script>

<style scoped>
@media print {
  /* Avoid page-breaks inside grid blocks */
  .grid, .lg\:col-span-2, .lg\:col-span-3 {
    page-break-inside: avoid;
  }
}
</style>
