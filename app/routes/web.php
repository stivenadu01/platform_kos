<?php

get('/', 'UserController@home');

// AUTH
get('/login', 'AuthController@login');
get('/register', 'AuthController@register');
get('/verify', 'AuthController@verify');
get('/reset-password', 'AuthController@resetPassword');


// PEMILIK
get('/pemilik', 'PemilikController@dashboard', ['auth', 'role:pemilik']);
get('/pemilik/profil', 'PemilikController@profil', ['auth', 'role:pemilik']);
get('/pemilik/kos', 'PemilikController@kos', ['auth', 'role:pemilik']);
get('/pemilik/kos/tambah', 'PemilikController@tambahKos', ['auth', 'role:pemilik']);
get('/pemilik/kos/edit', 'PemilikController@editKos', ['auth', 'role:pemilik']);

get('/pemilik/kos/foto', 'PemilikController@fotoKos', ['auth', 'role:pemilik']);


get('/pemilik/pembayaran', 'PemilikController@pembayaran', ['auth', 'role:pemilik', 'pro']);
get('/pemilik/langganan', 'PemilikController@langganan', ['auth', 'role:pemilik']);
get('/pemilik/langganan/checkout', 'PemilikController@langgananCheckout', ['auth', 'role:pemilik']);
get('/pemilik/langganan/pembayaran', 'PemilikController@langgananPembayaran', ['auth', 'role:pemilik']);


// Kelola penghuni
get('/pemilik/penghuni', 'PemilikController@penghuni', ['auth', 'role:pemilik', 'pro']);
get('/pemilik/claim', 'PemilikController@claim', ['auth', 'role:pemilik']);
get('/pemilik/penghuni/tambah', 'PemilikController@tambahPenghuni', ['auth', 'role:pemilik', 'pro']);
get('/pemilik/penghuni/edit', 'PemilikController@editPenghuni', ['auth', 'role:pemilik', 'pro']);

// Kelola kamar
get('/pemilik/kamar', 'PemilikController@kamar', ['auth', 'role:pemilik']);
get('/pemilik/kamar/tambah', 'PemilikController@tambahKamar', ['auth', 'role:pemilik']);
get('/pemilik/kamar/edit', 'PemilikController@editKamar', ['auth', 'role:pemilik']);
get('/pemilik/kamar/harga', 'PemilikController@hargaKamar', ['auth', 'role:pemilik']);
get('/pemilik/tipe-kamar', 'PemilikController@tipeKamar', ['auth', 'role:pemilik']);
get('/pemilik/tipe-kamar/tambah', 'PemilikController@tambahTipeKamar', ['auth', 'role:pemilik']);
get('/pemilik/tipe-kamar/edit', 'PemilikController@editTipeKamar', ['auth', 'role:pemilik']);
get('/pemilik/tipe-kamar/foto', 'PemilikController@fotoTipeKamar', ['auth', 'role:pemilik']);

// USER FLOW (placeholder routes — implementation dilanjutkan pada fase berikutnya)
get('/cari-kos', 'UserController@search');
get('/kos/{id}', 'UserController@detailKos');
get('/user/favorit', 'UserController@favorit', ['auth', 'role:pelanggan']);
get('/user/profil', 'UserController@profil', ['auth', 'role:pelanggan']);
get('/user/laporan', 'UserController@laporan', ['auth', 'role:pelanggan']);
get('/user/riwayat-kos', 'UserController@riwayatKos', ['auth', 'role:pelanggan']);

// ADMIN
get('/admin', 'AdminController@dashboard', ['auth', 'role:admin']);
get('/admin/pengguna', 'AdminController@pengguna', ['auth', 'role:admin']);
get('/admin/verifikasi', 'AdminController@verifikasi', ['auth', 'role:admin']);
get('/admin/laporan', 'AdminController@laporan', ['auth', 'role:admin']);
get('/admin/langganan', 'AdminController@langganan', ['auth', 'role:admin']);
get('/admin/langganan/metode-pembayaran', 'AdminController@metodePembayaran', ['auth', 'role:admin']);
