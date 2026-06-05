<template>
  <aside class="w-[260px] bg-slate-100 border-r border-slate-200 dark:bg-slate-950/95 dark:border-slate-800/60 flex flex-col p-8 fixed h-screen z-10 transition-colors duration-300">
    <!-- Logo -->
    <div class="flex items-center gap-3 mb-12">
      <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-violet-500 rounded-lg flex items-center justify-center font-bold text-white shadow-[0_0_15px_rgba(59,130,246,0.4)] font-display">
        N
      </div>
      <span class="font-display font-bold text-lg bg-gradient-to-r from-slate-800 to-slate-500 dark:from-white dark:to-slate-400 bg-clip-text text-transparent">
        Nobi SDM
      </span>
    </div>

    <!-- Navigation Links -->
    <nav class="flex-1">
      <ul class="flex flex-col gap-2 list-none p-0 m-0">
        <li class="group">
          <Link
            href="/"
            class="flex items-center gap-3 text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white px-4 py-3 rounded-lg text-sm font-medium transition-all duration-300"
            :class="{ 'text-slate-900 bg-slate-200/60 border-l-3 border-blue-500 pl-[13px] dark:text-white dark:bg-slate-900': $page.component === 'Dashboard/Index' }"
          >
            <LayoutDashboard class="w-4.5 h-4.5" />
            <span>Dashboard Tren</span>
          </Link>
        </li>
        
        <!-- Admin only links -->
        <template v-if="isAdmin">
          <li class="group">
            <Link
              href="/import"
              class="flex items-center gap-3 text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white px-4 py-3 rounded-lg text-sm font-medium transition-all duration-300"
              :class="{ 'text-slate-900 bg-slate-200/60 border-l-3 border-blue-500 pl-[13px] dark:text-white dark:bg-slate-900': $page.component.startsWith('Import/') }"
            >
              <UploadCloud class="w-4.5 h-4.5" />
              <span>Import Data</span>
            </Link>
          </li>
          
          <li class="group">
            <Link
              href="/history"
              class="flex items-center gap-3 text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white px-4 py-3 rounded-lg text-sm font-medium transition-all duration-300"
              :class="{ 'text-slate-900 bg-slate-200/60 border-l-3 border-blue-500 pl-[13px] dark:text-white dark:bg-slate-900': $page.component === 'History/Index' }"
            >
              <History class="w-4.5 h-4.5" />
              <span>Histori Upload</span>
            </Link>
          </li>
        </template>
      </ul>
    </nav>

    <!-- Sidebar Footer / User Profile -->
    <div class="mt-auto flex flex-col gap-4">
      <!-- Theme Toggle -->
      <button
        @click="toggleTheme"
        class="w-full flex items-center justify-between text-slate-600 hover:text-slate-900 bg-slate-200/50 hover:bg-slate-200/80 border border-slate-200 dark:text-slate-400 dark:hover:text-white dark:bg-slate-900/40 dark:hover:bg-slate-900/80 dark:border-slate-800/50 rounded-xl px-4 py-2.5 text-xs font-medium cursor-pointer transition-all duration-300"
      >
        <span class="flex items-center gap-2">
          <Sun v-if="isDark" class="w-4 h-4 text-amber-500" />
          <Moon v-else class="w-4 h-4 text-blue-600" />
          <span>{{ isDark ? 'Mode Terang' : 'Mode Gelap' }}</span>
        </span>
        <span class="text-[10px] text-slate-500 uppercase tracking-wider">Ubah</span>
      </button>

      <div class="flex items-center gap-3 p-3 bg-slate-200/50 rounded-xl border border-slate-200 dark:bg-slate-900/40 dark:border-slate-800/50 transition-colors duration-300">
        <div class="w-9 h-9 rounded-full bg-violet-600 flex items-center justify-center font-bold text-sm text-white">
          {{ userInitials }}
        </div>
        <div class="flex flex-col min-w-0">
          <span class="font-semibold text-xs text-slate-700 dark:text-slate-200 truncate">{{ userName }}</span>
          <span class="text-[10px] text-slate-500 dark:text-slate-400 capitalize">{{ userRole }}</span>
        </div>
      </div>

      <Link
        href="/logout"
        method="post"
        as="button"
        class="w-full flex items-center justify-center gap-2 text-slate-600 hover:text-red-600 hover:bg-red-500/10 px-4 py-2.5 rounded-lg text-xs font-medium border border-transparent hover:border-red-500/20 dark:text-slate-400 dark:hover:text-red-400 cursor-pointer transition-all duration-300"
      >
        <LogOut class="w-3.5 h-3.5" />
        <span>Keluar</span>
      </Link>
    </div>
  </aside>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { usePage, Link } from '@inertiajs/vue3';
import { 
  LayoutDashboard, 
  UploadCloud, 
  History, 
  LogOut,
  Sun,
  Moon
} from 'lucide-vue-next';

const page = usePage();

const isDark = ref(localStorage.theme !== 'light');

onMounted(() => {
  if (localStorage.theme === 'light') {
    document.documentElement.classList.remove('dark');
  } else {
    document.documentElement.classList.add('dark');
  }
});

const toggleTheme = () => {
  isDark.value = !isDark.value;
  if (isDark.value) {
    localStorage.theme = 'dark';
    document.documentElement.classList.add('dark');
  } else {
    localStorage.theme = 'light';
    document.documentElement.classList.remove('dark');
  }
  window.dispatchEvent(new Event('theme-changed'));
};

const userName = computed(() => page.props.auth.user?.name || 'User');
const userRole = computed(() => page.props.auth.user?.role || 'operator');
const isAdmin = computed(() => userRole.value === 'admin');

const userInitials = computed(() => {
  return userName.value
    .split(' ')
    .map(word => word[0])
    .join('')
    .substring(0, 2)
    .toUpperCase();
});
</script>
