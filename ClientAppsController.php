<?php

namespace Drupal\efx_app_management\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\efx_oauth_token\Controller\EfxOauthTokenController;
use GuzzleHttp\Exception\ClientException;
use ReflectionClass;

class ClientAppsController
{
  /*  GET call to retrieve all client apps from APIGEE  */
    public function get_client_apps($developerId, $apigeeOauthToken){

        try {

            $client = \Drupal::httpClient();
            $keys = \Drupal::service('key.repository');
            $baseUrl = $keys->getKey('tier_promotion_base_url')->getKeyValues();

            $response = $client->get('https://'. $baseUrl[0] . '/client-app/v1/developers/'.$developerId.'/apps', [ 'headers' =>[
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

    /*  GET call to retrieve client app from APIGEE  */
      public function get_ind_client_apps($developerId, $apigeeOauthToken, $clientAppId){

          try {

              $client = \Drupal::httpClient();
              $keys = \Drupal::service('key.repository');
              $baseUrl = $keys->getKey('tier_promotion_base_url')->getKeyValues();

              $response = $client->get('https://'. $baseUrl[0] . '/client-app/v1/developers/'.$developerId.'/apps/'.$clientAppId, [ 'headers' =>[
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

   /*  POST call to create client app */
    public function create_client_apps($developerId, $apigeeOauthToken, $payload){

        try {

            $client = \Drupal::httpClient();
            $keys = \Drupal::service('key.repository');
            $baseUrl = $keys->getKey('tier_promotion_base_url')->getKeyValues();

            $response = $client->post('https://'. $baseUrl[0] . '/client-app/v1/developers/'.$developerId.'/apps', [ 'headers' =>['Authorization' => 'Bearer '.$apigeeOauthToken,
                'Content-Type' => 'application/json'],'json' => $payload]);

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

 /*  PATCH call to update client app */
    public function update_client_apps($developerId, $apigeeOauthToken, $payload, $clientAppId){

        try {

            $client = \Drupal::httpClient();
            $keys = \Drupal::service('key.repository');
            $baseUrl = $keys->getKey('tier_promotion_base_url')->getKeyValues();

            $response = $client->patch('https://'. $baseUrl[0] . '/client-app/v1/developers/'.$developerId.'/apps/'.$clientAppId, [ 'headers' =>['Authorization' => 'Bearer '.$apigeeOauthToken],'json' => $payload]);

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




}
