<script setup>
defineProps({
  country: {
    type: String,
    required: true,
    validator: (v) => ['id', 'en', 'gb'].includes(v.toLowerCase()),
  },
  className: {
    type: String,
    default: 'w-4 h-2.5',
  },
});
</script>

<template>
  <!-- Indonesia Flag -->
  <svg
    v-if="country.toLowerCase() === 'id'"
    :class="[className, 'rounded-[2px] overflow-hidden shadow-2xs ring-1 ring-black/15 shrink-0 inline-block align-middle']"
    viewBox="0 0 600 400"
    xmlns="http://www.w3.org/2000/svg"
  >
    <rect width="600" height="200" fill="#E70011" />
    <rect y="200" width="600" height="200" fill="#FFFFFF" />
  </svg>

  <!-- United Kingdom / English Flag -->
  <svg
    v-else
    :class="[className, 'rounded-[2px] overflow-hidden shadow-2xs ring-1 ring-black/15 shrink-0 inline-block align-middle']"
    viewBox="0 0 60 40"
    xmlns="http://www.w3.org/2000/svg"
  >
    <clipPath id="uk-flag-clip-shared">
      <rect width="60" height="40" />
    </clipPath>
    <g clip-path="url(#uk-flag-clip-shared)">
      <rect width="60" height="40" fill="#012169" />
      <path d="M0,0 L60,40 M60,0 L0,40" stroke="#FFFFFF" stroke-width="8" />
      <path d="M0,0 L60,40 M60,0 L0,40" stroke="#C8102E" stroke-width="4" />
      <path d="M30,0 v40 M0,20 h60" stroke="#FFFFFF" stroke-width="14" />
      <path d="M30,0 v40 M0,20 h60" stroke="#C8102E" stroke-width="8" />
    </g>
  </svg>
</template>

