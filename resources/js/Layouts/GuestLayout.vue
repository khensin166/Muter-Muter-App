<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import LanguageSwitcher from '@/Components/LanguageSwitcher.vue';
import { Link } from '@inertiajs/vue3';
import { useTrans } from '@/Composables/useTrans';

const { t } = useTrans();
</script>

<template>
    <div
        class="flex min-h-screen flex-col items-center bg-gray-50 pt-6 sm:justify-center sm:pt-0"
    >
        <!-- Top bar with Back to Marketplace and Language Switcher -->
        <div class="w-full max-w-md px-4 mb-4 flex items-center justify-between">
            <Link
                href="/"
                class="text-xs font-semibold text-primary hover:text-primary-dark transition flex items-center gap-1"
            >
                {{ t('nav.back_to_marketplace') }}
            </Link>

            <LanguageSwitcher />
        </div>

        <div>
            <Link href="/">
                <ApplicationLogo class="h-16 w-auto fill-current text-primary" />
            </Link>
        </div>

        <div
            class="mt-6 w-full overflow-hidden bg-white px-6 py-6 shadow-md border border-slate-100 sm:max-w-md sm:rounded-2xl"
        >
            <slot />
        </div>
    </div>
</template>
