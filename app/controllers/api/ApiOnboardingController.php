<?php
class ApiOnboardingController
{
  public function status()
  {
    model('Onboarding');
    response(['success'=>true,'data'=>getPemilikOnboardingStatus((int)($_SESSION['user']['id_user'] ?? 0))]);
  }
}
