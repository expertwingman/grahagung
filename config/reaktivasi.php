<?php

/*
|--------------------------------------------------------------------------
| Template pesan reaktivasi cold lead
|--------------------------------------------------------------------------
| Placeholder: {nama} {proyek} {sales}
| Dipilih berdasarkan status lead. Ubah di sini tanpa menyentuh kode.
*/

return [
    'templates' => [

        // Sudah survei tapi belum lanjut — paling hangat
        'survey' => "Halo {nama}, saya {sales} dari Graha Agung Kencana Group.\n\nTerima kasih sudah sempat survei ke {proyek}. Bulan ini ada penawaran khusus untuk yang sudah survei, dan beberapa unit pilihan mulai terbatas.\n\nBoleh saya kirim detailnya?",

        // Pernah merespon
        'respon' => "Halo {nama}, saya {sales} dari Graha Agung Kencana Group.\n\nSebelumnya Bapak/Ibu sempat menanyakan {proyek}. Kami ada update stok dan promo bulan ini.\n\nMasih dipertimbangkan? Saya bisa kirim price list terbaru dan bantu jadwalkan survei kapan pun Bapak/Ibu longgar.",

        // Sudah dapat price list
        'kirim_pl' => "Halo {nama}, saya {sales} dari Graha Agung Kencana Group.\n\nBeberapa waktu lalu kami kirim price list {proyek}. Ada beberapa unit yang harganya akan disesuaikan bulan depan.\n\nKalau masih dipertimbangkan, saya bisa bantu cek unit yang paling sesuai dan jadwalkan survei.",

        // Tidak pernah merespon — paling dingin, pesan paling ringan
        'no_respon' => "Halo {nama}, saya {sales} dari Graha Agung Kencana Group.\n\nSebelumnya Bapak/Ibu pernah tertarik dengan {proyek}. Boleh saya kirim info unit dan harga terbaru?\n\nKalau sudah tidak mencari rumah, tidak apa-apa — cukup balas \"tidak\" dan saya tidak akan menghubungi lagi.",

        'default' => "Halo {nama}, saya {sales} dari Graha Agung Kencana Group. Boleh saya kirim info terbaru {proyek}?",
    ],
];
