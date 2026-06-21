<?php

return [
    ['kode' => 'kurikulum.index',    'label' => 'Kurikulum',          'route' => 'kurikulum.index',   'permission_required' => 'kurikulum.view',   'urutan' => 70, 'group' => 'Akademik'],
    ['kode' => 'kurikulum.struktur', 'label' => 'Struktur Kurikulum','route' => 'kurikulum.struktur.index', 'permission_required' => 'kurikulum.view', 'urutan' => 71, 'group' => 'Akademik'],
    ['kode' => 'kurikulum.komponen', 'label' => 'Komponen Kompetensi','route' => 'kurikulum.komponen.index', 'permission_required' => 'kurikulum.view', 'urutan' => 72, 'group' => 'Akademik'],
];
