<?php

class ApiMidtransController
{
  public function notification()
  {
    require_once ROOT_PATH . '/app/services/MidtransService.php';

    try {
      $raw = file_get_contents('php://input');
      $payload = json_decode($raw ?: '', true);
      if (!is_array($payload)) throw new Exception('Payload Midtrans tidak valid.', 400);

      $service = new MidtransService();
      if (!$service->verifyNotificationSignature($payload)) {
        response(['success' => false, 'message' => 'Signature Midtrans tidak valid.'], 403);
        return;
      }

      $result = prosesNotifikasiMidtrans($payload);
      response(['success' => true, 'data' => $result]);
    } catch (Throwable $e) {
      $code = $e->getCode();
      $status = ($code >= 400 && $code < 500) ? $code : 500;
      response(['success' => false, 'message' => $e->getMessage()], $status);
    }
  }
}
