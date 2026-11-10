import { ref } from 'vue';

/**
 * Reusable clipboard copy composable with timeout reset.
 */
export function useClipboard(timeout = 2000) {
  const copied = ref(false);
  const error = ref(null);

  const copy = async (text) => {
    try {
      if (navigator?.clipboard?.writeText) {
        await navigator.clipboard.writeText(text);
      } else {
        const textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.style.position = 'fixed';
        textarea.style.opacity = '0';
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
      }
      copied.value = true;
      error.value = null;
      setTimeout(() => {
        copied.value = false;
      }, timeout);
      return true;
    } catch (err) {
      error.value = err;
      copied.value = false;
      return false;
    }
  };

  return { copied, error, copy };
}

