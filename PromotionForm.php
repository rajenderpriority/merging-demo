<?php

namespace Drupal\efx_app_management_ui\Form;

use Drupal\Core\Form;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\efx_app_management\Controller\ProductSubscriptionsController;
use Drupal\efx_app_management\Controller\AppManagementController;
use Drupal\efx_send_mail\Form\SendMail;
use Symfony\Component\HttpFoundation\RedirectResponse;


class PromotionForm extends FormBase
{
     /**
     * {@inheritdoc}
     */
    public function getFormId()
    {

        return 'promotion_form';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state) {
        
            $form['#theme'] = 'promotion_template';
            $form['#attached']['library'][] = 'efx_app_management_ui/promotionValidations';
            $form['#attached']['library'][] = 'drupal.ajax';
            
            $form['container']['ip_address'] = array(
                '#type' => 'textfield',
                '#prefix' => '<h4>IP address</h4>',

            );
            
  
            $form['container']['cidr'] = array(
                '#type' => 'textfield',
                '#prefix' => '<h4>CIDR</h4>',
            );
            
            $form['container']['ip_version'] = array(
                '#type' => 'select',
                '#options' => ['IPv4', 'IPv6'],
            );

            $form['container']['add'] = array(
                '#markup' => '<i class="far fa-plus-circle add-ip-address"></i>',
            );

            $form['container']['remove'] = array(
                '#markup' => '<i class="far fa-minus-circle remove-ip-address"></i>',
            );


            $form['release_date'] = array(
                '#type' => 'textfield',
                '#required' => true,
                
            );

            $promotion_environment = $form_state->getValue('promotionEnvironment');
            $promotion_environment_prefix = $form_state->getValue('promotionEnvironmentPrefix');
            $lower_environment_subscription = $form_state->getValue('subscriptionDetails');
            $current_environment_prefix = $form_state->getValue('currentEnvironmentPrefix');

            $form_state->set('promotionEnv', $promotion_environment);
            $form_state->set('promotionEnvPrefix', $promotion_environment_prefix);
            $form_state->set('lowerSubscriptionInfo', $lower_environment_subscription);
            $form_state->set('currentEnvPrefix', $current_environment_prefix);

           $form['test'] = $lower_environment_subscription;

            $form['promotion_environment'] = array(
                '#markup' => $promotion_environment,
            );

            $form['promotion_environment_prefix'] = array(
                '#markup' => $promotion_environment_prefix,
            );

            $form['promotion_submit'] = array(
                '#value' => t('Submit'),
                '#type' => 'submit',

            );


        return $form;
    }

    public function validateForm(array &$form, FormStateInterface $form_state){
      

        $currentUserId = AppManagementController::getCurrentUserDetails();
        $promotionController = new ProductSubscriptionsController();
        $ip_array = $form_state->getUserInput()['ip_array'];
        $target_date = $form_state->getUserInput()['release_date'];

        $promotion_environment = $form_state->get('promotionEnv');
    
        $lower_tier_subscription_details = $form_state->get('lowerSubscriptionInfo');
        $promotion_environment_prefix = $form_state->get('promotionEnvPrefix');
        $current_environment_prefix = $form_state->get('currentEnvPrefix');

        $lower_tier_apiProducts = $lower_tier_subscription_details['credentials'][0]['apiProducts'];

        foreach($lower_tier_apiProducts as $key){
            
            $productName = str_replace($current_environment_prefix, $promotion_environment_prefix, $key['apiproduct']);
            $apiProducts[$productName]['scope'] = $key['apiProductScope']; 
            $apiProducts[$productName]['reference'] = $key['apiProductReference'];
            $promotion_products[] = $productName;
        }

        $developerId = $currentUserId[0];
        $apigeeOauthToken = $currentUserId[1];
        $developerEmail = $currentUserId[2];

        $clientAppId = $lower_tier_subscription_details['clientAppName'];
        $subscriptionName = $promotion_environment . '-' . $clientAppId;

        $payload = new \StdClass();
        $payload->productSubscription = new \StdClass();
        $payload->productSubscription->name = $subscriptionName;        
        $payload->productSubscription->attributes = [["name" => "clientAppName", "value"=>$clientAppId],["name" => "displayName", "value"=>$subscriptionName],["name" => "environment", "value"=>$promotion_environment],["name" => "developerEmail", "value"=>$developerEmail]];
        $payload->productSubscription->apiProducts = [$apiProducts];
        $payload->productSubscription->targetDate = $target_date;
        $payload->whitelistedIPs = [$ip_array];

        // Fetching product owner emails
        $nids = \Drupal::entityQuery('node')
            ->condition('type', 'user_product_mapping')
            ->execute();

        $userids = array();
        
        foreach ($nids as $key => $value) {
            $noderes = \Drupal\node\Entity\Node::load($value);

            if(!empty($noderes)){
                $res = $noderes->toArray();
                if(!empty($res['field_selected_products'])){
                    foreach ($res['field_selected_products'] as $key => $value) {
                        $productres = node_load($value['target_id']);
                        if(!empty($productres)){
                            $respro = $productres->toArray();
                        }
                        if(in_array($respro['title']['0']['value'],$promotion_products)){

                            $userids[] = $res['field_userid']['0']['value'];
                            $userdata = user_load($res['field_userid']['0']['value'])->toArray();
                            $prdowners_emails[] = $userdata['mail']['0']['value'];

                        }

                    }
                }
            }


        }
        $promote_response_payload = $promotionController->promoteIndividualSubscription($developerId, $clientAppId, $payload, $apigeeOauthToken);
        

        //Send Email to POs




    if(is_array($promote_response_payload)){
    $keys = \Drupal::service('key.repository');
    $sendersname = $keys->getKey('senders_name')->getKeyValues();
    $preview = $sendersname[0];

    $current_user_id = \Drupal::currentUser()->id();
    $current_user_email = \Drupal::currentUser()->getEmail();
        $other_user_object = \Drupal\user\Entity\User::load($current_user_id);
        $firstName = $other_user_object->first_name->value;
        $lastName = $other_user_object->last_name->value;
        $email = $other_user_object->mail->value;
        $company_name = $other_user_object->field_company->value;

        $arrayMails = $prdowners_emails;
        $promotion_environment = strtoupper($promotion_environment);

        $sendmail = new SendMail();
        $fallback_font = "'Open Sans'";
        $current_comp_uri = \Drupal::request()->getSchemeAndHttpHost();
        $signin = $current_comp_uri.'/user/login';
        $content ='Dear Product Owner,<p style="font-family:sans-serif,'.$fallback_font.'; font-size: 10pt; font-weight: normal; margin: 0; margin-bottom: 15px;margin-top: 18px;">An account owner is requesting access to our '.$promotion_environment.' Environment.</p>
<p style="font-family:sans-serif,'.$fallback_font.'; font-size: 10pt; font-weight: normal; margin: 0; Margin-bottom: 15px;"><strong>Name:</strong> '.$firstName.' '.$lastName.'</p>
<p style="font-family:sans-serif,'.$fallback_font.'; font-size: 10pt; font-weight: normal; margin: 0; Margin-bottom: 15px;"><strong>Company:</strong> '.$company_name.'</p>
<p style="font-family:sans-serif,'.$fallback_font.'; font-size: 10pt; font-weight: normal; margin: 0; Margin-bottom: 15px;"><strong>Email:</strong> '.$email.'</p>

<p style="font-family:sans-serif,'.$fallback_font.'; font-size: 10pt; font-weight: normal; margin: 0; Margin-bottom: 15px;"><strong>App Name:</strong> '.$subscriptionName.'</p>
<p style="font-family:sans-serif,'.$fallback_font.'; font-size: 10pt; font-weight: normal; margin: 0; Margin-bottom: 15px;"><strong>API Product Request:</strong> '.implode(' , ',$promotion_products).'</p>
<p style="font-family:sans-serif,'.$fallback_font.'; font-size: 10pt; font-weight: normal; margin: 0; margin-bottom: 15px;margin-top: 18px;">To approve this request, please log into your admin account on the <a href="'.$signin.'">Equifax Developer Center</a>.</p>';

        $body = $sendmail->mailtemplate($content);
        $sendmail->commonsendmail($body,$arrayMails,null,null,$preview.' - Approval Needed For App Promotion Request To '.$promotion_environment.' Environment');

      $redirect_path = '/user/applications/'.$clientAppId;
      $response = new RedirectResponse($redirect_path);
      $response->send();

    }
}

  

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state){




 
    }
}