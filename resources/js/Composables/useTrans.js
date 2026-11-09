import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

export function useTrans() {
  const page = usePage();
  const locale = computed(() => page.props.locale || 'id');
  const translations = computed(() => page.props.translations || {});

  const t = (key, replacements = {}) => {
    let text = translations.value[key] ?? key;

    Object.entries(replacements).forEach(([placeholder, val]) => {
      text = text.replace(new RegExp(`:${placeholder}`, 'g'), String(val));
    });

    return text;
  };

  return { t, locale, translations };
}
