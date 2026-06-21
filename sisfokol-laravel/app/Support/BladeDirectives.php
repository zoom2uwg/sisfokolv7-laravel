<?php

namespace App\Support;

use Illuminate\Support\Facades\Blade;

class BladeDirectives
{
    public static function register(): void
    {
        /**
         * @field('siswa.telepon')
         *   <input ...>
         * @endfield
         *
         * visible  → render as-is
         * readonly → render with disabled attribute injected on inputs
         * hidden   → render empty (anti-DOM-inspect: input hidden value KOSONG)
         */
        Blade::if('field', function (string $kode) {
            return \App\Support\FieldAcl::visible($kode) !== 'hidden';
        });

        Blade::directive('fieldAttr', function (string $kode) {
            return "<?php echo \App\Support\FieldAcl::visible({$kode}) === 'readonly' ? 'disabled' : ''; ?>";
        });
    }
}
