<?php

// Auth
// Session user
get('/api/auth/me', 'ApiAuthController@me', ['auth']);
// Login & Register
post('/api/auth/login', 'ApiAuthController@login');
post('/api/auth/register', 'ApiAuthController@register');
// Logout
post('/api/auth/logout', 'ApiAuthController@logout', ['auth']);
post('/api/auth/profile', 'ApiAuthController@updateProfile', ['auth']);
post('/api/auth/profile/foto', 'ApiAuthController@uploadFotoProfil', ['auth']);
post('/api/auth/password', 'ApiAuthController@changePassword', ['auth']);

// Password Reset
post('/api/auth/request-reset', 'ApiAuthController@requestReset');
post('/api/auth/reset-password', 'ApiAuthController@resetPassword');


get('/api/fasilitas', 'ApiFasilitasController@index');

// =====================================================
// PEMILIK - KOS
// =====================================================
get('/api/pemilik/kos', 'ApiKosController@index', ['auth', 'role:pemilik']);
get('/api/pemilik/kos/fasilitas', 'ApiKosController@fasilitas', ['auth', 'role:pemilik']);
get('/api/pemilik/kos/{id}', 'ApiKosController@show', ['auth', 'role:pemilik']);
post('/api/pemilik/kos', 'ApiKosController@store', ['auth', 'role:pemilik']);
put('/api/pemilik/kos/{id}', 'ApiKosController@update', ['auth', 'role:pemilik']);
delete('/api/pemilik/kos/{id}', 'ApiKosController@destroy', ['auth', 'role:pemilik']);

// dashboard
get('/api/pemilik/dashboard', 'ApiDashboardController@index', ['auth', 'role:pemilik']);

// =========================================================
// FOTO KOS - PEMILIK
// =========================================================
get('/api/pemilik/kos/foto/{id_kos}', 'ApiFotoController@index', ['auth', 'role:pemilik']);
post('/api/pemilik/kos/foto/{id_kos}', 'ApiFotoController@store', ['auth', 'role:pemilik']);
put('/api/pemilik/kos/foto/{id_kos}/{id_foto}/thumbnail', 'ApiFotoController@thumbnail', ['auth', 'role:pemilik']);
delete('/api/pemilik/kos/foto/{id_kos}/{id_foto}', 'ApiFotoController@destroy', ['auth', 'role:pemilik']);

// =========================================================
// PENGHUNI - PEMILIK
// =========================================================
get('/api/pemilik/penghuni', 'ApiPenghuniController@index', ['auth', 'role:pemilik']);
get('/api/pemilik/penghuni/kamar', 'ApiPenghuniController@kamar', ['auth', 'role:pemilik']);
get('/api/pemilik/penghuni/show', 'ApiPenghuniController@show', ['auth', 'role:pemilik']);
post('/api/pemilik/penghuni', 'ApiPenghuniController@store', ['auth', 'role:pemilik']);
put('/api/pemilik/penghuni', 'ApiPenghuniController@update', ['auth', 'role:pemilik']);
post('/api/pemilik/penghuni/update', 'ApiPenghuniController@update', ['auth', 'role:pemilik']);
put('/api/pemilik/penghuni/keluar', 'ApiPenghuniController@keluar', ['auth', 'role:pemilik']);
delete('/api/pemilik/penghuni', 'ApiPenghuniController@destroy', ['auth', 'role:pemilik']);

// =========================================================
// KAMAR - PEMILIK
// =========================================================
get('/api/pemilik/kamar', 'ApiKamarController@index', ['auth', 'role:pemilik']);
get('/api/pemilik/kamar/show', 'ApiKamarController@show', ['auth', 'role:pemilik']);
get('/api/pemilik/kamar/kos', 'ApiKamarController@kos', ['auth', 'role:pemilik']);
post('/api/pemilik/kamar', 'ApiKamarController@store', ['auth', 'role:pemilik']);
put('/api/pemilik/kamar', 'ApiKamarController@update', ['auth', 'role:pemilik']);
delete('/api/pemilik/kamar', 'ApiKamarController@destroy', ['auth', 'role:pemilik']);
put('/api/pemilik/kamar/status', 'ApiKamarController@status', ['auth', 'role:pemilik']);

// =========================================================
// TAGIHAN & PEMBAYARAN - PEMILIK
// =========================================================
get('/api/pemilik/tagihan', 'ApiTagihanController@index', ['auth', 'role:pemilik']);
get('/api/pemilik/tagihan/show', 'ApiTagihanController@show', ['auth', 'role:pemilik']);
post('/api/pemilik/tagihan/penyesuaian', 'ApiTagihanController@adjustment', ['auth', 'role:pemilik']);
post('/api/pemilik/tagihan/pembayaran', 'ApiTagihanController@payment', ['auth', 'role:pemilik']);



// =========================================================
// USER - LOKASI & PENCARIAN KOS
// =========================================================
get('/api/lokasi/search', 'ApiLokasiController@search');
get('/api/kos/search', 'ApiKosSearchController@index');
get('/api/kos/{id}', 'ApiKosSearchController@show');
