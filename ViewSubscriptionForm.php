<?php

namespace Drupal\efx_app_management_ui\Form;

use Drupal\Core\Form;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;


class ViewSubscriptionForm extends FormBase
{
     /**
     * {@inheritdoc}
     */
    public function getFormId()
    {

        return 'subscription_form';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state) {
        
        $productSubscription = $form_state->getValue('subscriptionDetails');
        $clientAppEnvironment = $form_state->getValue('clientAppEnvironment');

        if(empty($productSubscription)){
            $form = [];
        }

        else{
            $base_path = \Drupal::request()->getSchemeAndHttpHost();

            $keys = \Drupal::service('key.repository');
            $tier1 = $keys->getKey('promotion_tiers')->getKeyValues()['tier1'];

            $form['tier1'] = $tier1;
       
            $form['#theme'] = 'subscription_template';
            $form['subscription']['name'] = 'name';
            $form['subscription']['base_path'] = $base_path;
            $form['subscription']['clientAppName'] = $productSubscription['clientAppName'];

            
            $form['subscription']['consumerSecret'] = array(
                '#type' => 'textfield',
                '#default_value' => $productSubscription['credentials'][0]['consumerSecret'],
                '#attributes' => ['readonly' => 'readonly'],

            );
            
            $form['subscription']['consumerKey'] = array(
                '#type' => 'textfield',
                '#default_value' => $productSubscription['credentials'][0]['consumerKey'],
                '#attributes' => ['readonly' => 'readonly'],
            );

            $form['subscription']['access_token'] = array(
                '#type' => 'textfield',
                '#default_value' => $productSubscription['credentials'][0]['access_token'],
                '#attributes' => ['readonly' => 'readonly'],
            );

            $form['subscription']['apiProducts'] = $productSubscription['credentials'][0]['apiProducts'];
            $form['subscription']['status'] = $productSubscription['status'];
            

            $form['subscription']['expires_at'] = $productSubscription['credentials'][0]['expires_at'];

            $form['subscription']['environment'] = $productSubscription['environment'];
            $form['subscription']['currentEnvironmentPrefix'] = $form_state->getValue('currentEnvironmentPrefix');
            if(strcasecmp($clientAppEnvironment, $productSubscription['environment']) == 0){
                $form['defaultSubscription'] = 'active-subscription-info';                
            }

            else{
                $form['defaultSubscription'] = 'inactive-subscription-info';
            }

            $form['clientAppEnvironment'] = $clientAppEnvironment;


            $tempstore = \Drupal::service('user.private_tempstore')->get('app_management');
            $tempstore->delete($productSubscription['name']);

         
        }


        return $form;
    }

    public function validateForm(array &$form, FormStateInterface $form_state){

    }

    

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state){
       

    }
}