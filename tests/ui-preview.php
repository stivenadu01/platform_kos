<?php
// CLI-only synthetic visual fixture. Never authenticates a visitor or changes data.
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
define('ROOT_PATH',dirname(__DIR__)); define('BASE_URL','http://localhost/BetaKos');
$_SERVER['REQUEST_URI']='/BetaKos/admin/verifikasi';
$_SESSION['user']=['id_user'=>0,'role'=>'admin','nama'=>'Admin contoh'];
require ROOT_PATH.'/app/helpers/helper.php';
$pengajuan=[['id_verifikasi'=>1,'nama_kos'=>'Kos contoh','alamat'=>'Alamat contoh untuk pengujian tampilan','status_verifikasi'=>'menunggu','nama_pemilik'=>'Pemilik contoh','jenis'=>'campur','jumlah_kamar'=>10,'kamar_tersedia'=>3,'tanggal_pengajuan'=>'2026-10-13 08:00:00']];
ob_start(); ?>
<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Pratinjau admin — data contoh</title><link rel="stylesheet" href="<?= uiAsset('assets/css/app.css') ?>"><link rel="stylesheet" href="<?= uiAsset('assets/css/workspace-redesign.css') ?>">
<script>window.BASE_URL=<?= json_encode_safe(BASE_URL) ?>;
const fixture=<?= json_encode_safe($pengajuan) ?>;
const API={async get(path){if(path.match(/verifikasi\/\d/))return {data:{...fixture[0],nama_pemilik:'Pemilik contoh',email_pemilik:'contoh@example.com',alamat:fixture[0].alamat,deskripsi:'Contoh informasi kos. Seluruh data di halaman ini dibuat khusus untuk pengujian tampilan.',foto:[],kamar:[]}};return {data:path.includes('disetujui')||path.includes('ditolak')?[]:fixture,summary:{menunggu:1,disetujui:4,ditolak:2}};},async post(){throw Error('Pratinjau tidak menyimpan data');}};
document.addEventListener('alpine:init',()=>{Alpine.store('navigation',{counts:{verification:1,reports:2,payments:3}});Alpine.store('auth',{logout(){}});Alpine.store('ui',{toast(){},async confirm(){return false;}});});</script>
<script src="<?= uiAsset('assets/js/ui-components.js') ?>"></script><script defer src="<?= uiAsset('assets/js/vendor/alpine-3.14.9.min.js') ?>"></script></head><body class="admin-ui"><div data-layout-shell="admin" x-data="{sidebarOpen:false,sidebarCollapsed:false}"><?php include ROOT_PATH.'/app/views/partials/admin/sidebar.php'; ?><div class="admin-main-shell"><header class="p-5 bg-white border-b border-slate-200 text-sm">Pratinjau admin · data contoh</header><main class="p-5 sm:p-8"><?php include ROOT_PATH.'/app/views/pages/admin/verifikasi.php'; ?></main></div></div></body></html>
<?php file_put_contents(ROOT_PATH.'/public/betakos-ui-preview.html',ob_get_clean()); echo "Synthetic UI preview generated\n";
