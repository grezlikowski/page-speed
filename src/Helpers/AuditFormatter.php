<?php

namespace Grezlikowski\PageSpeed\Helpers;

class AuditFormatter
{
    public static function formatBytes(mixed $bytes): string
    {
        $n = (float) $bytes;
        if ($n === 0.0) {
            return '0 B';
        }
        if ($n < 1024) {
            return round($n).' B';
        }
        if ($n < 1024 * 1024) {
            return number_format($n / 1024, 1).' KiB';
        }

        return number_format($n / (1024 * 1024), 1).' MiB';
    }

    public static function formatMs(mixed $ms): string
    {
        $n = (float) $ms;
        if ($n < 1000) {
            return round($n).' ms';
        }

        return number_format($n / 1000, 1).' s';
    }

    public static function formatCellValue(mixed $value, string $valueType): string
    {
        if ($value === null) {
            return '—';
        }
        if ($valueType === 'bytes') {
            return self::formatBytes($value);
        }
        if (in_array($valueType, ['timespanMs', 'ms'])) {
            return self::formatMs($value);
        }
        if ($valueType === 'numeric') {
            return number_format((float) $value);
        }
        if (is_array($value)) {
            return $value['nodeLabel'] ?? $value['snippet'] ?? $value['selector'] ?? $value['value'] ?? '—';
        }

        return (string) $value;
    }
}
