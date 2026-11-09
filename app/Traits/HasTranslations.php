<?php

namespace App\Traits;

trait HasTranslations
{
    /**
     * Get an attribute from the model, resolving JSON multilingual translations automatically.
     */
    public function getAttribute($key)
    {
        $value = parent::getAttribute($key);

        if ($this->isTranslatableAttribute($key)) {
            return $this->resolveLocalizedValue($value);
        }

        return $value;
    }

    /**
     * Set a given attribute on the model, automatically encoding array/plain translations to JSON.
     */
    public function setAttribute($key, $value)
    {
        if ($this->isTranslatableAttribute($key)) {
            if (is_array($value)) {
                $this->attributes[$key] = json_encode($value, JSON_UNESCAPED_UNICODE);

                return $this;
            }

            if (is_string($value)) {
                $decoded = json_decode($value, true);
                if (json_last_error() !== JSON_ERROR_NONE || ! is_array($decoded)) {
                    $this->attributes[$key] = json_encode(['id' => $value], JSON_UNESCAPED_UNICODE);

                    return $this;
                }
            }
        }

        return parent::setAttribute($key, $value);
    }

    /**
     * Check if the given attribute key is registered as translatable.
     */
    public function isTranslatableAttribute(string $key): bool
    {
        return property_exists($this, 'translatable') && is_array($this->translatable) && in_array($key, $this->translatable, true);
    }

    /**
     * Resolve localized string according to active app locale with graceful fallback.
     */
    public function resolveLocalizedValue(mixed $value, ?string $locale = null): ?string
    {
        if ($value === null) {
            return null;
        }

        // If stored as JSON string, decode first
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $value = $decoded;
            } else {
                // Legacy plain text value
                return $value;
            }
        }

        if (! is_array($value)) {
            return (string) $value;
        }

        $currentLocale = $locale ?: app()->getLocale() ?: 'id';

        // 1. Direct match with current locale
        if (! empty($value[$currentLocale])) {
            return (string) $value[$currentLocale];
        }

        // 2. Fallback to default Indonesian ('id')
        if (! empty($value['id'])) {
            return (string) $value['id'];
        }

        // 3. Fallback to English ('en')
        if (! empty($value['en'])) {
            return (string) $value['en'];
        }

        // 4. Fallback to first available language value
        foreach ($value as $langText) {
            if (! empty($langText)) {
                return (string) $langText;
            }
        }

        return '';
    }

    /**
     * Retrieve all translations for a given attribute.
     */
    public function getTranslations(string $attribute): array
    {
        $raw = parent::getAttribute($attribute);

        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
            return ['id' => $raw];
        }

        if (is_array($raw)) {
            return $raw;
        }

        return [];
    }

    /**
     * Retrieve specific translation without fallback.
     */
    public function getTranslation(string $attribute, string $locale): ?string
    {
        $translations = $this->getTranslations($attribute);
        return $translations[$locale] ?? null;
    }

    /**
     * Set translation for a specific locale.
     */
    public function setTranslation(string $attribute, string $locale, ?string $value): self
    {
        $translations = $this->getTranslations($attribute);
        $translations[$locale] = $value;
        $this->attributes[$attribute] = json_encode($translations, JSON_UNESCAPED_UNICODE);

        return $this;
    }

    /**
     * Set all translations for an attribute.
     */
    public function setTranslations(string $attribute, array $translations): self
    {
        $this->attributes[$attribute] = json_encode($translations, JSON_UNESCAPED_UNICODE);
        return $this;
    }
}

