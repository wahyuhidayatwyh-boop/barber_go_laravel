<?php

// 1. Set header agar browser tahu ini JSON
header('Content-Type: application/json');

// 2. Buat data sebagai array PHP
$profil = [
    'nama'      => 'Budi',
    'pekerjaan' => 'Web Developer',
    'lokasi'    => 'Jakarta'
];

// 3. Ubah array ke JSON lalu tampilkan
echo json_encode($profil);

?>
