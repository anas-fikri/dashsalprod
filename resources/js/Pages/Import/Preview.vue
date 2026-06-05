<template>
  <div class="flex min-h-screen bg-slate-50 text-slate-800 dark:bg-slate-950 dark:text-slate-100 font-sans transition-colors duration-300">
    <!-- Sidebar -->
    <Sidebar />

    <!-- Main Content Area -->
    <main class="ml-[260px] flex-1 p-12 flex flex-col gap-8">
      <!-- Header -->
      <header class="flex justify-between items-center border-b border-slate-200 dark:border-slate-800/60 pb-6">
        <div class="flex flex-col gap-1">
          <h1 class="font-display font-bold text-2xl tracking-tight text-slate-900 dark:text-white">Pratinjau & Verifikasi Payroll</h1>
          <p class="text-xs text-slate-500 dark:text-slate-400">
            Periksa hasil parsing, rekonsiliasi total gaji, dan kecocokan roster karyawan sebelum disimpan.
          </p>
        </div>
      </header>

      <!-- Warning & Reconciliation Card -->
      <div v-if="reconciliation.warnings.length > 0" class="bg-amber-500/5 dark:bg-amber-500/10 border border-amber-500/20 text-amber-600 dark:text-amber-300 text-xs px-6 py-5 rounded-2xl flex flex-col gap-3">
        <div class="flex items-center gap-2 font-bold text-amber-600 dark:text-amber-400">
          <AlertTriangle class="w-5 h-5" />
          <span>Ditemukan Selisih Rekonsiliasi & Validasi Roster:</span>
        </div>
        <ul class="list-disc pl-5 flex flex-col gap-1.5 text-slate-700 dark:text-slate-300">
          <li v-for="warning in reconciliation.warnings" :key="warning" v-html="warning"></li>
        </ul>
      </div>

      <div v-else class="bg-emerald-500/5 dark:bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400 text-xs px-6 py-4 rounded-2xl flex items-center gap-3">
        <CheckCircle2 class="w-5 h-5" />
        <span class="font-semibold">Reconciliation & Roster Match Sempurna! Semua data cocok tanpa selisih.</span>
      </div>

      <!-- File Aggregates -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white dark:bg-slate-900/40 border border-slate-200 dark:border-slate-800/50 rounded-2xl p-6 shadow-xs">
          <span class="text-slate-500 dark:text-slate-400 text-[10px] uppercase font-semibold">Nama File</span>
          <span class="font-semibold text-slate-800 dark:text-slate-200 mt-2 block truncate text-sm">{{ file_name }}</span>
        </div>
        <div class="bg-white dark:bg-slate-900/40 border border-slate-200 dark:border-slate-800/50 rounded-2xl p-6 shadow-xs">
          <span class="text-slate-500 dark:text-slate-400 text-[10px] uppercase font-semibold">Periode</span>
          <span class="font-semibold text-slate-800 dark:text-slate-200 mt-2 block text-sm">{{ formatPeriodLabel(period) }}</span>
        </div>
        <div class="bg-white dark:bg-slate-900/40 border border-slate-200 dark:border-slate-800/50 rounded-2xl p-6 shadow-xs">
          <span class="text-slate-500 dark:text-slate-400 text-[10px] uppercase font-semibold">Jumlah Karyawan</span>
          <span class="font-semibold text-slate-800 dark:text-slate-200 mt-2 block text-sm">{{ total_rows }} Karyawan</span>
        </div>
        <div class="bg-white dark:bg-slate-900/40 border border-slate-200 dark:border-slate-800/50 rounded-2xl p-6 shadow-xs">
          <span class="text-slate-500 dark:text-slate-400 text-[10px] uppercase font-semibold">Total Gaji Nett</span>
          <span class="font-semibold text-slate-800 dark:text-slate-200 mt-2 block text-sm">{{ formatCurrency(total_net_salary) }}</span>
        </div>
      </div>

      <!-- Rekap Comparison Details -->
      <div class="bg-white dark:bg-slate-900/40 border border-slate-200 dark:border-slate-800/50 rounded-2xl p-6 flex flex-col gap-4 shadow-xs" v-if="reconciliation.rekap_checked">
        <h3 class="font-semibold text-slate-800 dark:text-slate-200 text-sm">Rincian Perbandingan Rekap Pembayaran Gaji</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div 
            v-for="(comp, company) in reconciliation.rekap_comparison" 
            :key="company"
            class="bg-slate-50/50 dark:bg-slate-950/50 border rounded-xl p-4 flex flex-col gap-3"
            :class="comp.status === 'matched' ? 'border-emerald-500/20' : 'border-red-500/20'"
          >
            <div class="flex justify-between items-center border-b border-slate-200 dark:border-slate-800/50 pb-2">
              <span class="font-bold text-sm" :class="comp.status === 'matched' ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-500 dark:text-red-400'">
                PT NOBI {{ company }}
              </span>
              <span 
                class="px-2 py-0.5 rounded text-[9px] font-semibold uppercase"
                :class="comp.status === 'matched' ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400' : 'bg-red-500/10 text-red-500 dark:text-red-400'"
              >
                {{ comp.status }}
              </span>
            </div>
            
            <div class="grid grid-cols-3 gap-2 text-[11px] text-slate-500 dark:text-slate-400">
              <div class="flex flex-col">
                <span>Total Rekap:</span>
                <span class="font-mono text-slate-800 dark:text-slate-200 mt-1">{{ formatCurrency(comp.rekap_total) }}</span>
              </div>
              <div class="flex flex-col">
                <span>Total Upload:</span>
                <span class="font-mono text-slate-800 dark:text-slate-200 mt-1">{{ formatCurrency(comp.uploaded_total) }}</span>
              </div>
              <div class="flex flex-col">
                <span>Selisih:</span>
                <span class="font-mono mt-1" :class="comp.diff > 0 ? 'text-red-500 dark:text-red-400' : 'text-slate-600 dark:text-slate-400'">
                  {{ formatCurrency(comp.diff) }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Preview Employee Table -->
      <div class="bg-white dark:bg-slate-900/40 border border-slate-200 dark:border-slate-800/50 rounded-2xl p-6 flex flex-col gap-4 shadow-xs">
        <h3 class="font-semibold text-slate-800 dark:text-slate-200 text-sm">Pratinjau Data Detail Karyawan</h3>
        
        <div class="overflow-x-auto max-h-[400px]">
          <table class="w-full border-collapse text-left text-[11px]">
            <thead class="sticky top-0 bg-slate-100 dark:bg-slate-900 z-10">
              <tr class="border-b border-slate-200 dark:border-slate-800 text-slate-500 dark:text-slate-400">
                <th class="py-2.5 px-3 font-semibold">NIK</th>
                <th class="py-2.5 px-3 font-semibold">Nama</th>
                <th class="py-2.5 px-3 font-semibold">Entitas</th>
                <th class="py-2.5 px-3 font-semibold">Departemen</th>
                <th class="py-2.5 px-3 font-semibold">Struktural</th>
                <th class="py-2.5 px-3 font-semibold">Fungsi</th>
                <th class="py-2.5 px-3 font-semibold text-right">Gaji Pokok</th>
                <th class="py-2.5 px-3 font-semibold text-right">Gaji Nett</th>
                <th class="py-2.5 px-3 font-semibold">Status Validasi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-800/30">
              <tr v-for="emp in data" :key="emp.nik" class="hover:bg-slate-50 dark:hover:bg-slate-900/20">
                <td class="py-2 px-3 font-mono text-slate-600 dark:text-slate-300">{{ emp.nik }}</td>
                <td class="py-2 px-3 text-slate-900 dark:text-slate-200 font-medium">{{ emp.nama }}</td>
                <td class="py-2 px-3 text-slate-500 dark:text-slate-400">{{ emp.company_code }}</td>
                <td class="py-2 px-3 text-slate-500 dark:text-slate-400">{{ emp.sub_department || '-' }}</td>
                <td class="py-2 px-3 text-slate-500 dark:text-slate-400">{{ emp.structural_status }}</td>
                <td class="py-2 px-3">
                  <span
                    class="px-2 py-0.5 rounded text-[9px] font-semibold"
                    :class="emp.is_production ? 'bg-blue-500/10 text-blue-600 dark:text-blue-400' : 'bg-purple-500/10 text-purple-600 dark:text-purple-400'"
                  >
                    {{ emp.is_production ? 'Production' : 'Non-Prod' }}
                  </span>
                </td>
                <td class="py-2 px-3 text-right text-slate-600 dark:text-slate-400 font-mono">{{ formatCurrency(emp.basic_salary) }}</td>
                <td class="py-2 px-3 text-right text-slate-800 dark:text-slate-300 font-mono">{{ formatCurrency(emp.net_salary) }}</td>
                <td class="py-2 px-3">
                  <span v-if="emp.validation_warnings.length === 0" class="text-emerald-600 dark:text-emerald-400 flex items-center gap-1 font-semibold">
                    <Check class="w-3.5 h-3.5" /> Valid
                  </span>
                  <div v-else class="flex flex-col gap-0.5">
                    <span v-for="w in emp.validation_warnings" :key="w" class="text-amber-600 dark:text-amber-400 font-semibold flex items-center gap-1">
                      <AlertCircle class="w-3 h-3 flex-shrink-0" /> {{ w }}
                    </span>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="flex justify-end gap-4 border-t border-slate-200 dark:border-slate-800/60 pt-6">
        <Link
          href="/import"
          class="bg-white hover:bg-slate-100 border border-slate-200 text-slate-700 hover:text-slate-900 dark:bg-slate-900 dark:hover:bg-slate-800 dark:border-slate-800 dark:hover:border-slate-700 dark:text-slate-300 dark:hover:text-white px-5 py-2.5 rounded-xl text-xs font-semibold cursor-pointer transition-all"
        >
          Batal
        </Link>
        <button
          @click="confirmSave"
          :disabled="isSubmitting"
          class="px-6 py-2.5 bg-gradient-to-r from-blue-500 to-violet-500 hover:from-blue-600 hover:to-violet-600 text-white rounded-xl text-xs font-semibold hover:shadow-[0_0_15px_rgba(139,92,246,0.3)] disabled:opacity-50 transition-all cursor-pointer"
        >
          {{ isSubmitting ? 'Menyimpan...' : 'Konfirmasi & Simpan Data' }}
        </button>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import Sidebar from '@/Components/Sidebar.vue';
import { 
  AlertTriangle, 
  CheckCircle2, 
  Check, 
  AlertCircle 
} from 'lucide-vue-next';

const props = defineProps({
  file_name: String,
  period: String,
  total_rows: Number,
  entity_counts: Object,
  total_net_salary: Number,
  reconciliation: Object,
  data: Array,
});

const isSubmitting = ref(false);

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

const confirmSave = () => {
  isSubmitting.value = true;
  router.post('/import/payroll/save', {
    file_name: props.file_name,
    period: props.period,
    data: props.data,
  }, {
    onFinish: () => {
      isSubmitting.value = false;
    }
  });
};
</script>
