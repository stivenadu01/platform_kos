<?php

/**
 * Payment destination configuration for manual subscription payments.
 *
 * Values come from .env so real account numbers are never hard-coded into
 * application source. A method is shown to owners only when all required
 * destination fields are configured.
 */
function getSubscriptionPaymentMethods()
{
  $bankName = trim((string)($_ENV['SUBSCRIPTION_BANK_NAME'] ?? ''));
  $bankAccount = trim((string)($_ENV['SUBSCRIPTION_BANK_ACCOUNT'] ?? ''));
  $bankHolder = trim((string)($_ENV['SUBSCRIPTION_BANK_HOLDER'] ?? ''));

  $ewalletName = trim((string)($_ENV['SUBSCRIPTION_EWALLET_NAME'] ?? ''));
  $ewalletAccount = trim((string)($_ENV['SUBSCRIPTION_EWALLET_ACCOUNT'] ?? ''));
  $ewalletHolder = trim((string)($_ENV['SUBSCRIPTION_EWALLET_HOLDER'] ?? ''));

  return [
    'transfer_bank' => [
      'label' => 'Transfer Bank',
      'enabled' => $bankName !== '' && $bankAccount !== '' && $bankHolder !== '',
      'provider' => $bankName,
      'account' => $bankAccount,
      'holder' => $bankHolder,
    ],
    'e_wallet' => [
      'label' => 'E-Wallet',
      'enabled' => $ewalletName !== '' && $ewalletAccount !== '' && $ewalletHolder !== '',
      'provider' => $ewalletName,
      'account' => $ewalletAccount,
      'holder' => $ewalletHolder,
    ],
  ];
}
