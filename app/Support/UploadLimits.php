<?php

namespace App\Support;

class UploadLimits
{
    public static function wordImportMaxKilobytes(): int
    {
        $configured = max(1, (int) config('uploads.word_import_max_kilobytes', 65536));

        return (int) min($configured, self::phpUploadKilobytes());
    }

    public static function wordImportMaxMegabytesLabel(): string
    {
        return self::kilobytesToMegabytesLabel(self::wordImportMaxKilobytes());
    }

    public static function phpPostMaxLabel(): string
    {
        $kilobytes = self::iniKilobytes((string) ini_get('post_max_size'));
        if ($kilobytes <= 0) {
            return 'illimitée';
        }

        return self::kilobytesToMegabytesLabel($kilobytes);
    }

    public static function phpUploadKilobytes(): int
    {
        $values = array_filter(
            [
                self::iniKilobytes((string) ini_get('upload_max_filesize')),
                self::iniKilobytes((string) ini_get('post_max_size')),
            ],
            fn (int $value): bool => $value > 0,
        );

        return $values === [] ? 65536 : (int) min($values);
    }

    public static function kilobytesToMegabytesLabel(int $kilobytes): string
    {
        return max(1, (int) round($kilobytes / 1024)).' Mo';
    }

    private static function iniKilobytes(string $value): int
    {
        $value = trim($value);
        if ($value === '' || $value === '0') {
            return 0;
        }

        if (is_numeric($value)) {
            return (int) ceil(((int) $value) / 1024);
        }

        $metric = strtoupper(substr($value, -1));
        $number = (float) $value;

        $bytes = match ($metric) {
            'G' => $number * 1073741824,
            'M' => $number * 1048576,
            'K' => $number * 1024,
            default => $number,
        };

        return (int) floor($bytes / 1024);
    }
}
