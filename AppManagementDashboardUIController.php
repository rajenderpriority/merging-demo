<?php

namespace Drupal\efx_app_management_ui\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Form\FormState;
use Drupal\efx_app_management\Controller\ClientAppsController;
use Drupal\efx_app_management\Controller\AppManagementController;
use Drupal\efx_app_management\Controller\ProductSubscriptionsController;

class AppManagementDashboardUIController
{

    public function renderClientAppDashboard(){

        $appManagementController = new AppManagementController();
        $clientAppsController = new ClientAppsController();

        $clientAppFormState = new FormState();
        $tier1SubscriptionFormState = new FormState();
        $tier2SubscriptionFormState = new FormState();
        $tier3SubscriptionFormState = new FormState();
        $promotionFormState = new FormState();
        
        $clientAppFormState->setRebuild();
        $tier1SubscriptionFormState->setRebuild();
        $tier2SubscriptionFormState->setRebuild();
        $tier3SubscriptionFormState->setRebuild();
        $promotionFormState->setRebuild();

        $keys = \Drupal::service('key.repository');
        $tiers = $keys->getKey('promotion_tiers')->getKeyValues();

        $current_uri = \Drupal::request()->getRequestUri();
        $uri_parts = parse_url($current_uri);
        $path = explode('/', $uri_parts['path']);
        $clientAppId = $path[3];

        $currentUserId = $appManagementController->getCurrentUserDetails();
        $developerId = $currentUserId[0];
        $apigeeOauthToken = $currentUserId[1];
        $developerEmail = $currentUserId[2];
        $fullName = $currentUserId[3];

        try{
            $tempstore = \Drupal::service('user.private_tempstore')->get('app_management');
            $clientAppResponseArray = $tempstore->get($clientAppId);
        }

        catch(\Exception $e) {
            watchdog_exception('efx_app_management_ui', $e);
            $clientAppResponseArray = [];
        }

        if(empty($clientAppResponseArray)){
            $clientAppResponseArray = $clientAppsController->get_ind_client_apps($developerId, $apigeeOauthToken, $clientAppId);
        }

        $tier1SubscriptionId = $tiers['tier1'] . '-' . $clientAppId;
        $tier2SubscriptionId = $tiers['tier2'] . '-' . $clientAppId;
        $tier3SubscriptionId = $tiers['tier3'] . '-' . $clientAppId;

        $productSubscriptionId = strtolower($clientAppResponseArray['environment']) . '-' . $clientAppId;

        $productSubscriptionController = new ProductSubscriptionsController();

        if($clientAppResponseArray['status'] != $tiers['tier1']){
           
            try{
                $tempstore = \Drupal::service('user.private_tempstore')->get('app_management');
                $tier1SubscriptionDetails = $productSubscription = $tempstore->get($productSubscriptionId);
            }

            catch(\Exception $e) {
                watchdog_exception('efx_app_management_ui', $e);
                $productSubscription = [];
            }

            if(empty($productSubscription)){
                $productSubscriptionController = new ProductSubscriptionsController();
                if($productSubscriptionId == $tier1SubscriptionId){
                    $tier1SubscriptionDetails = $productSubscription = $productSubscriptionController->get_individual_subscription_details($developerId, $productSubscriptionId, $apigeeOauthToken);
                    $tier2SubscriptionDetails = [];
                    $tier3SubscriptionDetails = [];
                    
                }

                elseif($productSubscriptionId == $tier2SubscriptionId){
                    $tier1SubscriptionDetails = $productSubscriptionController->get_individual_subscription_details($developerId, $tier1SubscriptionId, $apigeeOauthToken);
                    $tier2SubscriptionDetails = $productSubscription = $productSubscriptionController->get_individual_subscription_details($developerId, $productSubscriptionId, $apigeeOauthToken);
                    $tier3SubscriptionDetails = [];
                    
                }

                else{
                    $tier1SubscriptionDetails = $productSubscriptionController->get_individual_subscription_details($developerId, $tier1SubscriptionId, $apigeeOauthToken);
                    $tier2SubscriptionDetails = $productSubscriptionController->get_individual_subscription_details($developerId, $tier2SubscriptionId, $apigeeOauthToken);
                    $tier3SubscriptionDetails = $productSubscription = $productSubscriptionController->get_individual_subscription_details($developerId, $productSubscriptionId, $apigeeOauthToken);
                    
                }

                // $tempstore->set($productSubscriptionId, []);
                
              
            }
            
            $tier1SubscriptionFormState->setValue('subscriptionDetails', $tier1SubscriptionDetails);
            $tier2SubscriptionFormState->setValue('subscriptionDetails', $tier2SubscriptionDetails);
            $tier3SubscriptionFormState->setValue('subscriptionDetails', $tier3SubscriptionDetails);
            $tier1SubscriptionFormState->setValue('clientAppEnvironment', strtolower($clientAppResponseArray['environment']));
            $tier2SubscriptionFormState->setValue('clientAppEnvironment', strtolower($clientAppResponseArray['environment']));
            $tier3SubscriptionFormState->setValue('clientAppEnvironment', strtolower($clientAppResponseArray['environment']));       
            $tier1SubscriptionFormState->setValue('currentEnvironmentPrefix', $tiers['tier1-prefix']);
            $tier2SubscriptionFormState->setValue('currentEnvironmentPrefix', $tiers['tier2-prefix']);
            $tier3SubscriptionFormState->setValue('currentEnvironmentPrefix', $tiers['tier3-prefix']);
                                    
            if(strcasecmp($productSubscription['environment'], $tiers['tier1']) ==0 ){
                $promotion_environment = $tiers['tier2'];
                $promotion_environment_prefix = $tiers['tier2-prefix'];
                $current_environment_prefix = $tiers['tier1-prefix'];
                $current_environment = $tiers['tier1'];
                $promotionFormState->setValue('subscriptionDetails', $tier1SubscriptionDetails);
            }
            elseif(strcasecmp($productSubscription['environment'], $tiers['tier2']) == 0){
                $promotion_environment = $tiers['tier3'];
                $promotion_environment_prefix = $tiers['tier3-prefix'];
                $current_environment_prefix = $tiers['tier2-prefix'];
                $current_environment = $tiers['tier2'];
                $promotionFormState->setValue('subscriptionDetails', $tier2SubscriptionDetails);
            }

            elseif(strcasecmp($productSubscription['environment'], $tiers['tier3']) == 0){
                $promotion_environment = '';
                $promotion_environment_prefix = '';
                $current_environment_prefix = $tiers['tier3-prefix'];
                $current_environment = $tiers['tier3'];
                $promotionFormState->setValue('subscriptionDetails', []);
            }

            $promotionFormState->setValue('promotionEnvironment', $promotion_environment);
            $promotionFormState->setValue('promotionEnvironmentPrefix', $promotion_environment_prefix);
            $promotionFormState->setValue('currentEnvironmentPrefix', $current_environment_prefix);
            $promotionFormState->setValue('currentEnvironment', $current_environment);            
            $clientAppFormState->setValue('currentEnvironment', $current_environment);
            $clientAppFormState->setValue('currentEnvironmentPrefix', $current_environment_prefix);
            $clientAppFormState->setValue('promotionEnvironment', $promotion_environment);
            $clientAppFormState->setValue('promotionEnvironmentPrefix', $promotion_environment_prefix);




        }

        else{
            $productSubscription = [];
        }


        $clientAppFormState->setValue('clientAppDetails', $clientAppResponseArray);
        
        $clientAppForm = \Drupal::formBuilder()->buildForm('Drupal\efx_app_management_ui\Form\ClientAppForm', $clientAppFormState);
        $tier1SubscriptionForm = \Drupal::formBuilder()->buildForm('Drupal\efx_app_management_ui\Form\ViewSubscriptionForm', $tier1SubscriptionFormState);
        $tier2SubscriptionForm = \Drupal::formBuilder()->buildForm('Drupal\efx_app_management_ui\Form\ViewSubscriptionForm', $tier2SubscriptionFormState);
        $tier3SubscriptionForm = \Drupal::formBuilder()->buildForm('Drupal\efx_app_management_ui\Form\ViewSubscriptionForm', $tier3SubscriptionFormState);
        $promotionForm = \Drupal::formBuilder()->buildForm('Drupal\efx_app_management_ui\Form\PromotionForm', $promotionFormState);

        $build = [$clientAppForm, $tier1SubscriptionForm, $tier2SubscriptionForm, $tier3SubscriptionForm, $promotionForm];
           
        return $build;
    }

    
}


