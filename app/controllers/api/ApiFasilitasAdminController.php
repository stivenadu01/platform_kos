<?php
class ApiFasilitasAdminController
{
  public function __construct(){ model('Fasilitas'); }
  public function index(){ try{response(['success'=>true,'data'=>getFasilitasAdmin(trim((string)query('search','')),trim((string)query('kategori','')),trim((string)query('status','')))]);}catch(Throwable $e){response(['success'=>false,'message'=>'Gagal memuat fasilitas.'],500);} }
  public function show(){ $row=getFasilitasByIdAdmin((int)params('id')); if(!$row) response(['success'=>false,'message'=>'Fasilitas tidak ditemukan.'],404); response(['success'=>true,'data'=>$row]); }
  public function store(){ try{$id=createFasilitas(input());response(['success'=>true,'message'=>'Fasilitas berhasil ditambahkan.','data'=>['id_fasilitas'=>$id]],201);}catch(Throwable $e){response(['success'=>false,'message'=>$e->getMessage()],$e->getCode()?:500);} }
  public function update(){ try{updateFasilitas((int)params('id'),input());response(['success'=>true,'message'=>'Fasilitas berhasil diperbarui.']);}catch(Throwable $e){response(['success'=>false,'message'=>$e->getMessage()],$e->getCode()?:500);} }
  public function delete(){ try{deleteFasilitas((int)params('id'));response(['success'=>true,'message'=>'Fasilitas berhasil dihapus.']);}catch(Throwable $e){response(['success'=>false,'message'=>$e->getMessage()],$e->getCode()?:500);} }
}
