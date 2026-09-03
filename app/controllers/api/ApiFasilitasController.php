<?php

class ApiFasilitasController
{
  public function index()
  {
    model('Fasilitas');

    response([
      'success' => true,
      'data' => getAllFasilitas(query('kategori') ?? 'kos')
    ]);
  }
}
