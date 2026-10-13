<?php
function consumeGoogleIdentityNonce(string $nonce): bool
{
  $expiry = $_SESSION['google_identity_nonces'][$nonce] ?? 0;
  unset($_SESSION['google_identity_nonces'][$nonce]);
  return $nonce !== '' && $expiry >= time();
}

function googleIdentityConfig(): array
{
  $now = time();
  $nonces = array_filter($_SESSION['google_identity_nonces'] ?? [], static fn($expiry) => $expiry > $now);
  $nonce = bin2hex(random_bytes(32));
  $nonces[$nonce] = $now + 600;
  $_SESSION['google_identity_nonces'] = array_slice($nonces, -8, null, true);
  return ['clientId'=>GOOGLE_CLIENT_ID,'nonce'=>$nonce,'prompt'=>!in_array(normalized_request_path(),['/register','/verify','/reset-password'],true)];
}
