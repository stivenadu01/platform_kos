<?php



// Auth
// Session user
get('/api/auth/me', 'ApiAuthController@me', ['auth']);
// Login & Register
post('/api/auth/login', 'ApiAuthController@login');
post('/api/auth/register', 'ApiAuthController@register');
// Logout
post('/api/auth/logout', 'ApiAuthController@logout', ['auth']);
// Password Reset
post('/api/auth/request-reset', 'ApiAuthController@requestReset');
post('/api/auth/reset-password', 'ApiAuthController@resetPassword');


// =====================================================
// PEMILIK - KOS
// =====================================================
get('/api/pemilik/kos', 'ApiKosController@index', ['auth', 'role:pemilik']);
get('/api/pemilik/kos/fasilitas', 'ApiKosController@fasilitas', ['auth', 'role:pemilik']);
get('/api/pemilik/kos/{id}', 'ApiKosController@show', ['auth', 'role:pemilik']);
post('/api/pemilik/kos', 'ApiKosController@store', ['auth', 'role:pemilik']);
put('/api/pemilik/kos/{id}', 'ApiKosController@update', ['auth', 'role:pemilik']);
delete('/api/pemilik/kos/{id}', 'ApiKosController@destroy', ['auth', 'role:pemilik']);

// =========================================================
// FOTO KOS - PEMILIK
// =========================================================
get('/api/pemilik/kos/foto/{id_kos}', 'ApiFotoController@index', ['auth', 'role:pemilik']);
post('/api/pemilik/kos/foto/{id_kos}', 'ApiFotoController@store', ['auth', 'role:pemilik']);
put('/api/pemilik/kos/foto/{id_kos}/{id_foto}/thumbnail', 'ApiFotoController@thumbnail', ['auth', 'role:pemilik']);
delete('/api/pemilik/kos/foto/{id_kos}/{id_foto}', 'ApiFotoController@destroy', ['auth', 'role:pemilik']);

// =========================================================
// KAMAR - PEMILIK
// =========================================================s
get('/api/pemilik/kamar', 'ApiKamarController@index', ['auth', 'role:pemilik']);
get('/api/pemilik/kamar/show', 'ApiKamarController@show', ['auth', 'role:pemilik']);
get('/api/pemilik/kamar/kos', 'ApiKamarController@kos', ['auth', 'role:pemilik']);
post('/api/pemilik/kamar', 'ApiKamarController@store', ['auth', 'role:pemilik']);
put('/api/pemilik/kamar', 'ApiKamarController@update', ['auth', 'role:pemilik']);
delete('/api/pemilik/kamar', 'ApiKamarController@destroy', ['auth', 'role:pemilik']);
put('/api/pemilik/kamar/status', 'ApiKamarController@status', ['auth', 'role:pemilik']);


get('/api/pemilik/penghuni', 'ApiPenghuniController@index', ['auth', 'role:pemilik']);
get('/api/pemilik/penghuni/show', 'ApiPenghuniController@show', ['auth', 'role:pemilik']);
get('/api/pemilik/penghuni/kamar', 'ApiPenghuniController@kamar', ['auth', 'role:pemilik']);
post('/api/pemilik/penghuni', 'ApiPenghuniController@store', ['auth', 'role:pemilik']);
put('/api/pemilik/penghuni', 'ApiPenghuniController@update', ['auth', 'role:pemilik']);
put('/api/pemilik/penghuni/keluar', 'ApiPenghuniController@keluar', ['auth', 'role:pemilik']);
delete('/api/pemilik/penghuni', 'ApiPenghuniController@destroy', ['auth', 'role:pemilik']);
