<?php
function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function active_menu($page, $current) {
    return $page === $current ? 'bg-blue-600 text-white' : 'text-slate-600 hover:bg-slate-100';
}

function format_number($value, $decimal = 3) {
    return number_format((float)$value, $decimal, '.', '');
}

function euclidean_distance(array $siswa, array $centroid): float {
    $sum = pow($siswa['nilai_akademik'] - $centroid['centroid_akademik'], 2)
        + pow($siswa['nilai_ekstrakurikuler'] - $centroid['centroid_ekstrakurikuler'], 2)
        + pow($siswa['absensi'] - $centroid['centroid_absensi'], 2)
        + pow($siswa['nilai_tugas'] - $centroid['centroid_tugas'], 2);
    return sqrt($sum);
}

function cluster_label($kode) {
    return match ($kode) {
        'C1' => 'Kemajuan Belajar Rendah',
        'C2' => 'Kemajuan Belajar Sedang',
        'C3' => 'Kemajuan Belajar Tinggi',
        default => 'Tidak Diketahui'
    };
}
