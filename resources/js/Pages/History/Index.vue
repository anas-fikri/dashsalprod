<template>
  <div class="flex min-h-screen bg-slate-50 text-slate-800 dark:bg-slate-950 dark:text-slate-100 font-sans transition-colors duration-300">
    <!-- Sidebar -->
    <Sidebar />

    <!-- Main Content Area -->
    <main class="ml-[260px] flex-1 p-12 flex flex-col gap-8">
      <!-- Header -->
      <header class="flex justify-between items-center border-b border-slate-200 dark:border-slate-800/60 pb-6">
        <div class="flex flex-col gap-1">
          <h1 class="font-display font-bold text-2xl tracking-tight text-slate-900 dark:text-white">Histori Aktivitas Upload</h1>
          <p class="text-xs text-slate-500 dark:text-slate-400">
            Log data payroll terunggah dan aksi pengelolaan riwayat.
          </p>
        </div>
      </header>

      <!-- Alert Message -->
      <div v-if="$page.props.flash.success" class="bg-emerald-500/5 dark:bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400 text-xs px-4 py-3 rounded-lg flex items-center gap-2">
        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 dark:bg-emerald-400"></span>
        {{ $page.props.flash.success }}
      </div>
      <div v-if="$page.props.flash.error" class="bg-red-500/5 dark:bg-red-500/10 border border-red-500/20 text-red-600 dark:text-red-400 text-xs px-4 py-3 rounded-lg flex items-center gap-2">
        <span class="w-1.5 h-1.5 rounded-full bg-red-500 dark:bg-red-400"></span>
        {{ $page.props.flash.error }}
      </div>

      <!-- History Table Card -->
      <div class="bg-white dark:bg-slate-900/40 border border-slate-200 dark:border-slate-800/50 rounded-2xl p-6 flex flex-col gap-4 shadow-xs">
        <div class="overflow-x-auto">
          <table class="w-full border-collapse text-left text-xs">
            <thead>
              <tr class="border-b border-slate-200 dark:border-slate-800/60 text-slate-500 dark:text-slate-400">
                <th class="py-3 px-4 font-semibold">Waktu Upload</th>
                <th class="py-3 px-4 font-semibold">Nama File</th>
                <th class="py-3 px-4 font-semibold">Periode Payroll</th>
                <th class="py-3 px-4 font-semibold">Tipe</th>
                <th class="py-3 px-4 font-semibold text-right">Baris Data</th>
                <th class="py-3 px-4 font-semibold">Diunggah Oleh</th>
                <th class="py-3 px-4 font-semibold text-center">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800/20">
              <tr v-for="log in histories" :key="log.id" class="hover:bg-slate-50 dark:hover:bg-slate-900/20">
                <td class="py-4 px-4 text-slate-500 dark:text-slate-300">{{ formatTimestamp(log.created_at) }}</td>
                <td class="py-4 px-4 font-medium text-slate-800 dark:text-slate-200 truncate max-w-xs" :title="log.file_name">{{ log.file_name }}</td>
                <td class="py-4 px-4 text-slate-900 dark:text-slate-200 font-semibold">{{ formatPeriodLabel(log.period) }}</td>
                <td class="py-4 px-4">
                  <span class="px-2.5 py-0.5 rounded-full text-[9px] font-bold uppercase bg-blue-500/10 text-blue-600 dark:text-blue-400">
                    {{ log.type }}
                  </span>
                </td>
                <td class="py-4 px-4 text-right text-slate-500 dark:text-slate-300 font-mono">{{ log.row_count }} Baris</td>
                <td class="py-4 px-4 text-slate-500 dark:text-slate-300">{{ log.importer?.name || 'Sistem' }}</td>
                <td class="py-4 px-4 text-center">
                  <button
                    @click="deleteLog(log.id)"
                    class="text-red-650 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 hover:bg-red-500/10 border border-transparent hover:border-red-500/20 px-3 py-1.5 rounded-lg text-[10px] font-semibold cursor-pointer transition-all"
                  >
                    Hapus
                  </button>
                </td>
              </tr>
              <tr v-if="histories.length === 0">
                <td colspan="7" class="py-12 text-center text-slate-500">
                  Belum ada log aktivitas import yang tercatat.
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
import { router } from '@inertiajs/vue3';
import Sidebar from '@/Components/Sidebar.vue';

defineProps({
  histories: Array,
});

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

const formatTimestamp = (ts) => {
  if (!ts) return '';
  const d = new Date(ts);
  return d.toLocaleString('id-ID', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};

const deleteLog = (id) => {
  if (confirm('Apakah Anda yakin ingin menghapus data payroll untuk periode ini? Tindakan ini akan menghapus seluruh detail data transaksi terkait.')) {
    router.delete(`/history/${id}`);
  }
};
</script>
