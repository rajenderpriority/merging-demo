<?php

namespace Drupal\efx_app_management\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\efx_oauth_token\Controller\EfxOauthTokenController;
use GuzzleHttp\Exception\ClientException;

class AppManagementController
{

    public function getCurrentUserDetails(){
        
        $currentUserId = \Drupal::currentUser()->id();
        $account = \Drupal\user\Entity\User::load($currentUserId);
        $developerId = $account->get('field_developer_apigee_id')->getValue()[0]['value'];
        $developerEmail = $account->get('mail')->getValue()[0]['value'];
        $fn = $account->get('first_name')->value;
     	$ln  = $account->get('last_name')->value;
     	$fullName = $fn.' '.$ln;
        $apigeeOauthToken = EfxOauthTokenController::get_oauth_token();

        return [$developerId, $apigeeOauthToken, $developerEmail, $fullName];  
    }

    
}


