<?php

namespace Drupal\efx_app_management\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\efx_oauth_token\Controller\EfxOauthTokenController;
use GuzzleHttp\Exception\ClientException;
use Drupal\user\Entity\User;
use Drupal\drupal_rest_api_util\Controller\DrupalRestAPIUtilController;

class ProductSubscriptionsController{
/*  GET call to retrieve individual subscription details from APIGEE  */
    public function get_individual_subscription_details($developerId, $productSubsId, $apigeeOauthToken){

        try {


            $client = \Drupal::httpClient();
            $keys = \Drupal::service('key.repository');
            $baseUrl = $keys->getKey('tier_promotion_base_url')->getKeyValues();

            $response = $client->get('https://'. $baseUrl[0] . '/product-subscription/v1/developers/'.$developerId.'/product-subscriptions/'.$productSubsId, [ 'headers' =>[
                'Content-Type' => ['application/json'], 'Authorization' => ['Bearer '.$apigeeOauthToken]]]
            );

            $data = $response->getBody()->getContents();
            $decode = json_decode($data, true);
            return $decode;
        }

        catch (ClientException $e) {

            $code = $e->getCode();
            $message = $e->getMessage();
            watchdog_exception('efx_app_management', $e);
            $tempString = explode(",", $message);
            $description = explode(":", $tempString[1]);
            $actualMessage = str_replace("}", "", $description[1]);
            $actualMessage = trim($actualMessage);
            return 'Error: '. $code .' Description: ' . $actualMessage;

        }

        catch (\Exception $e) {

            watchdog_exception('efx_app_management', $e);
            return 'This operation went wrong. Please try again later.';
        }

    }

    /*  POST call to promote individual subscription */
    public function promoteIndividualSubscription($developerId, $clientAppId, $payload, $apigeeOauthToken){

        try {

      

            $client = \Drupal::httpClient();
            $keys = \Drupal::service('key.repository');
            $baseUrl = $keys->getKey('tier_promotion_base_url')->getKeyValues();

            $response = $client->post('https://'. $baseUrl[0] . '/client-app/v1/developers/'.$developerId.'/apps/'.$clientAppId.'/promote', [
                'headers' =>['Content-Type' => ['application/json'], 'Authorization' => ['Bearer '.$apigeeOauthToken]],
                'json' => $payload]);
            
            $data = $response->getBody()->getContents();
            $decode = json_decode($data, true);
            return $decode;
        }

        catch (ClientException $e) {

            $code = $e->getCode();
            $message = $e->getMessage();
            watchdog_exception('efx_app_management', $e);
            $tempString = explode(",", $message);
            $description = explode(":", $tempString[1]);
            $actualMessage = str_replace("}", "", $description[1]);
            $actualMessage = trim($actualMessage);
            return 'Error: '. $code .' Description: ' . $actualMessage;

        }

        catch (\Exception $e) {

            watchdog_exception('efx_app_management', $e);
            return 'This operation went wrong. Please try again later.';
        }

    }


    /*  POST call to Create Product subscription for a developer based on developerId */
    public function createProductSubscription($developerId, $payload, $apigeeOauthToken){
        try {

          $client = \Drupal::httpClient();
          $keys = \Drupal::service('key.repository');
          $baseUrl = $keys->getKey('tier_promotion_base_url')->getKeyValues();

          $response = $client->post('https://'. $baseUrl[0] . '/product-subscription/v1/developers/'.$developerId.'/product-subscriptions', [
              'headers' =>['Content-Type' => 'application/json', 'Authorization' => 'Bearer '.$apigeeOauthToken],
              'json' => $payload ]
          );
          $data = $response->getBody()->getContents();
          $decode = json_decode($data, true);
          return $decode;
          
        }

        catch (ClientException $e) {

            $code = $e->getCode();
            $message = $e->getMessage();
            watchdog_exception('efx_app_management', $e);
            $tempString = explode(",", $message);
            $description = explode(":", $tempString[1]);
            $actualMessage = str_replace("}", "", $description[1]);
            $actualMessage = trim($actualMessage);
            return 'Error: '. $code .' Description: ' . $actualMessage;

        }

        catch (\Exception $e) {

            watchdog_exception('efx_app_management', $e);
            return 'This operation went wrong. Please try again later.';
        }

    }

    /*  PUT call to Update Product subscription for a developer */
    public function updateProductSubscription($developerId, $payload, $apigeeOauthToken, $productSubsId){
        try {

            $client = \Drupal::httpClient();
            $keys = \Drupal::service('key.repository');
            $baseUrl = $keys->getKey('tier_promotion_base_url')->getKeyValues();

          $response = $client->put('https://'. $baseUrl[0] . '/product-subscription/v1/developers/'.$developerId.'/product-subscriptions/'.$productSubsId, [
              'headers' =>['Content-Type' => 'application/json', 'Authorization' => 'Bearer '.$apigeeOauthToken],
              'json' => $payload ]
          );
          $data = $response->getBody()->getContents();
          $decode = json_decode($data, true);
          return $decode;
          
        }

        catch (ClientException $e) {

            $code = $e->getCode();
            $message = $e->getMessage();
            watchdog_exception('efx_app_management', $e);
            $tempString = explode(",", $message);
            $description = explode(":", $tempString[1]);
            $actualMessage = str_replace("}", "", $description[1]);
            $actualMessage = trim($actualMessage);
            return 'Error: '. $code .' Description: ' . $actualMessage;

        }

        catch (\Exception $e) {

            watchdog_exception('efx_app_management', $e);
            return $e->getMessage();
        }

    }
}
