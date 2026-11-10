<?php

namespace App\Casts;

use App\Enums\DestinationCluster as DestinationClusterEnum;
use App\Models\DestinationCluster as DestinationClusterModel;
use BackedEnum;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use JsonSerializable;
use Stringable;

class DestinationClusterCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if ($value === null) {
            return null;
        }

        $code = $value instanceof BackedEnum ? (string) $value->value : (string) $value;

        return new class($code) implements Stringable, JsonSerializable
        {
            public readonly string $value;

            public function __construct(string $code)
            {
                $this->value = $code;
            }

            public function label(): string
            {
                return DestinationClusterModel::resolveLabel($this->value);
            }

            public function __toString(): string
            {
                return $this->value;
            }

            public function jsonSerialize(): string
            {
                return $this->value;
            }
        };
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof BackedEnum) {
            return (string) $value->value;
        }

        if (is_object($value) && isset($value->value)) {
            return (string) $value->value;
        }

        return strtoupper(trim((string) $value));
    }
}

