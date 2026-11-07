<script setup>
import { ref } from 'vue';

const props = defineProps({
  variant: {
    type: String,
    default: 'default', // 'default' (full color), 'white' (white for dark backgrounds), 'icon' (mark icon)
  },
  className: {
    type: String,
    default: '',
  },
});

const imageError = ref(false);
</script>

<template>
  <div class="inline-flex items-center gap-2 select-none">
    <img
      v-if="!imageError && variant === 'white'"
      src="/images/logo-white.png"
      alt="BetaTudia"
      :class="[className || 'h-9 w-auto', 'object-contain']"
      @error="imageError = true"
    />
    <img
      v-else-if="!imageError && variant === 'icon'"
      src="/images/logo-icon.svg"
      alt="BetaTudia"
      :class="[className || 'h-9 w-9', 'object-contain']"
      @error="imageError = true"
    />
    <img
      v-else-if="!imageError"
      src="/images/logo.png"
      alt="BetaTudia"
      :class="[className || 'h-10 w-auto', 'object-contain']"
      @error="imageError = true"
    />

    <!-- Graceful Fallback if image not rendered -->
    <div v-if="imageError" class="flex items-center gap-2">
      <div class="w-9 h-9 rounded-xl bg-primary flex items-center justify-center text-white font-black text-xl shadow-xs">
        B
      </div>
      <div class="flex flex-col">
        <span class="text-lg font-black tracking-tight text-primary leading-none">BetaTudia</span>
        <span class="text-[10px] font-bold text-accent tracking-widest uppercase">Sumatera Utara</span>
      </div>
    </div>
  </div>
</template>
