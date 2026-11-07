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
  showText: {
    type: Boolean,
    default: true,
  },
  subtext: {
    type: String,
    default: '',
  },
  textClassName: {
    type: String,
    default: '',
  },
});

const imageError = ref(false);
</script>

<template>
  <div class="inline-flex items-center gap-2.5 select-none">
    <!-- Emblem Image -->
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
      :class="[className || 'h-9 sm:h-10 w-auto', 'object-contain']"
      @error="imageError = true"
    />

    <!-- Fallback if image fails to load -->
    <div
      v-if="imageError"
      class="w-9 h-9 rounded-xl bg-primary flex items-center justify-center text-white font-black text-xl shadow-xs"
    >
      B
    </div>

    <!-- Brand Typography "BetaTudia" beside logo -->
    <div v-if="showText" class="flex flex-col">
      <span
        :class="[
          textClassName || (variant === 'white' ? 'text-white' : 'text-primary'),
          'text-lg sm:text-xl font-black tracking-tight leading-none font-sans'
        ]"
      >
        BetaTudia
      </span>
      <span
        v-if="subtext"
        :class="[
          variant === 'white' ? 'text-slate-300' : 'text-accent',
          'text-[9px] sm:text-[10px] font-bold tracking-widest uppercase leading-tight mt-0.5'
        ]"
      >
        {{ subtext }}
      </span>
    </div>
  </div>
</template>
