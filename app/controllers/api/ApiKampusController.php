<?php

class ApiKampusController
{
  public function index()
  {
    model('Kampus');

    response([
      'success' => true,
      'data' => getSemuaKampus()
    ]);
  }
}
