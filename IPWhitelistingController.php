<?php

namespace Drupal\efx_app_management\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\efx_oauth_token\Controller\EfxOauthTokenController;
use GuzzleHttp\Exception\ClientException;

class IPWhitelistingController
{
    /*Get call for Getting IP whitelists for Test/Live apps*/
    public function get_ip_whitelisting($developerId, $productSubsId, $apigeeOauthToken){

        try {
  
            $client = \Drupal::httpClient();
            $keys = \Drupal::service('key.repository');
            $baseUrl = $keys->getKey('tier_promotion_base_url')->getKeyValues(); 

            $response = $client->get('https://'. $baseUrl[0] . '/developers/'.$developerId.'/product-subscriptions/'.$productSubsId.'/whitelisted-ips', [ 'headers' =>[
                'Content-Type' => ['application/json'], 'Authorization' => ['Bearer '.$apigeeOauthToken]]]
            );

            $data = $response->getBody()->getContents();
            $decode = json_decode($data, true);
 
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

    /*Post call for Creating IP whitelisting for Test/Live apps*/
    public function create_ip_whitelisting($developerId, $productSubsId, $apigeeOauthToken){

        try {

            $whitelisted_ips = [];
            if(isset($payload['whitelisted-ips'])){
                foreach ($payload['whitelisted-ips'] as $key => $value) {
                    $whitelisted_ips[$key]['ip'] = $value['ip'];
                    $whitelisted_ips[$key]['cidr'] = $value['cidr'];
                    $whitelisted_ips[$key]['ipversion'] = $value['ipversion'];
                }
              }

  
            $client = \Drupal::httpClient();
            $keys = \Drupal::service('key.repository');
            $baseUrl = $keys->getKey('tier_promotion_base_url')->getKeyValues(); 

            $response = $client->post('https://'. $baseUrl[0] . '/developers/'.$developerId.'/product-subscriptions/'.$productSubsId.'/whitelisted-ips', [ 'headers' =>[
                'Content-Type' => ['application/json'], 'Authorization' => ['Bearer '.$apigeeOauthToken]],
                'json' => ['whitelisted-ips'=> $ip_array]]
            );

            $data = $response->getBody()->getContents();
            $decode = json_decode($data, true);
 
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

    /*Put call for Updating IP whitelisting for Test/Live apps*/
    public function update_ip_whitelisting($developerId, $productSubsId, $apigeeOauthToken){

        try {

            $whitelisted_ips = [];
            if(isset($payload['whitelisted-ips'])){
                foreach ($payload['whitelisted-ips'] as $key => $value) {
                    $whitelisted_ips[$key]['ip'] = $value['ip'];
                    $whitelisted_ips[$key]['cidr'] = $value['cidr'];
                    $whitelisted_ips[$key]['ipversion'] = $value['ipversion'];
                }
              }

  
            $client = \Drupal::httpClient();
            $keys = \Drupal::service('key.repository');
            $baseUrl = $keys->getKey('tier_promotion_base_url')->getKeyValues(); 

            $response = $client->put('https://'. $baseUrl[0] . '/developers/'.$developerId.'/product-subscriptions/'.$productSubsId.'/whitelisted-ips', [ 'headers' =>[
                'Content-Type' => ['application/json'], 'Authorization' => ['Bearer '.$apigeeOauthToken]],
                'json' => ['whitelisted-ips'=> $ip_array]]
            );

            $data = $response->getBody()->getContents();
            $decode = json_decode($data, true);
 
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