<template>
  <div class="min-h-screen w-full flex items-center justify-center bg-slate-950 relative overflow-hidden px-4">
    <!-- Background glowing orbs -->
    <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-blue-500/10 rounded-full blur-[120px] pointer-events-none"></div>
    <div class="absolute bottom-1/4 right-1/4 w-96 h-96 bg-violet-500/10 rounded-full blur-[120px] pointer-events-none"></div>

    <!-- Login Card -->
    <div class="w-full max-w-md bg-slate-900/40 backdrop-blur-xl border border-slate-800/80 rounded-2xl p-8 shadow-2xl relative z-10">
      <!-- Logo -->
      <div class="flex flex-col items-center mb-8">
        <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-violet-500 rounded-xl flex items-center justify-center font-bold text-2xl text-white shadow-[0_0_20px_rgba(59,130,246,0.3)] mb-4 font-display">
          N
        </div>
        <h1 class="text-2xl font-bold font-display text-white">Nobi SDM</h1>
        <p class="text-xs text-slate-400 mt-1">Sistem Perbandingan Biaya Manpower & Kinerja</p>
      </div>

      <!-- Form -->
      <form @submit.prevent="submit" class="flex flex-col gap-5">
        <div>
          <label for="email" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Alamat Email</label>
          <input
            id="email"
            v-model="form.email"
            type="email"
            required
            placeholder="nama@nobi.com"
            class="w-full bg-slate-950/80 border border-slate-800 focus:border-blue-500/60 rounded-xl px-4 py-3 text-sm text-slate-100 placeholder-slate-500 outline-none transition-all duration-300 focus:ring-2 focus:ring-blue-500/10"
            :class="{ 'border-red-500/50 focus:border-red-500/50': errors.email }"
          />
          <span v-if="errors.email" class="text-xs text-red-400 mt-1.5 block">{{ errors.email }}</span>
        </div>

        <div>
          <label for="password" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Kata Sandi</label>
          <input
            id="password"
            v-model="form.password"
            type="password"
            required
            placeholder="••••••••"
            class="w-full bg-slate-950/80 border border-slate-800 focus:border-blue-500/60 rounded-xl px-4 py-3 text-sm text-slate-100 placeholder-slate-500 outline-none transition-all duration-300 focus:ring-2 focus:ring-blue-500/10"
            :class="{ 'border-red-500/50 focus:border-red-500/50': errors.password }"
          />
          <span v-if="errors.password" class="text-xs text-red-400 mt-1.5 block">{{ errors.password }}</span>
        </div>

        <button
          type="submit"
          :disabled="form.processing"
          class="w-full bg-gradient-to-r from-blue-500 to-violet-500 hover:from-blue-600 hover:to-violet-600 text-white rounded-xl py-3 text-sm font-semibold cursor-pointer transition-all duration-300 hover:shadow-[0_0_20px_rgba(139,92,246,0.3)] disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2 mt-2"
        >
          <span v-if="form.processing">Memproses...</span>
          <span v-else>Masuk ke Akun</span>
        </button>
      </form>

      <!-- Credentials Info -->
      <div class="mt-8 pt-6 border-t border-slate-800/60 text-slate-400">
        <div class="bg-slate-900/60 border border-slate-800/40 rounded-xl p-4">
          <p class="text-[10px] font-semibold text-slate-300 uppercase tracking-wider mb-2">Akun Demo Default:</p>
          <div class="flex flex-col gap-1.5 text-xs">
            <div class="flex justify-between">
              <span class="text-slate-400">Admin:</span>
              <span class="font-mono text-slate-200">admin@nobi.com / admin123</span>
            </div>
            <div class="flex justify-between">
              <span class="text-slate-400">Operator:</span>
              <span class="font-mono text-slate-200">operator@nobi.com / operator123</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
  errors: Object,
});

const form = useForm({
  email: '',
  password: '',
});

const submit = () => {
  form.post('/login', {
    onFinish: () => form.reset('password'),
  });
};
</script>
