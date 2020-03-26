<?php

namespace Drupal\efx_app_management_ui\Form;

use Drupal\Core\Form;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\efx_app_management\Controller\ClientAppsController;
use Drupal\efx_app_management\Controller\AppManagementController;
use Drupal\efx_app_management\Controller\ProductSubscriptionsController;


class ClientAppForm extends FormBase
{
     /**
     * {@inheritdoc}
     */
    public function getFormId()
    {

        return 'client_app_dashboard_form';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state) {

        $form['#attached']['library'][] = 'efx_app_management_ui/updateClientApp';
        $form['#attached']['library'][] = 'drupal.ajax';
        $form['#theme'] = 'client_app_template';

        $keys = \Drupal::service('key.repository');
        $tiers = $keys->getKey('promotion_tiers')->getKeyValues();

        $appManagementController = new AppManagementController();
        $currentUserId = $appManagementController->getCurrentUserDetails();

        $developerId = $currentUserId[0];
        $apigeeOauthToken = $currentUserId[1];

        $current_uri = \Drupal::request()->getRequestUri();
        $uri_parts = parse_url($current_uri);
        $path = explode('/', $uri_parts['path']);
        $clientAppId = $path[3];
        $base_path = \Drupal::request()->getSchemeAndHttpHost();
        
        $clientAppResponseArray = $form_state->getValue('clientAppDetails');

        $tempstore = \Drupal::service('user.private_tempstore')->get('app_management');
        $tempstore->set($developerId .'-clientApp', $clientAppResponseArray);
        
        $form['promotionTier1'] = $tiers['tier1-prefix'];
        $form['promotionTier2'] = $tiers['tier2-prefix'];
        $form['promotionTier3'] = $tiers['tier3-prefix'];

        $form['clientAppDisplayName'] = $clientAppResponseArray['clientAppDisplayName'];
        $form['environment'] = $clientAppResponseArray['environment'];

        if(strcasecmp($clientAppResponseArray['environment'], $tiers['tier1']) == 0){
            $form['environmentPrefix'] = $tiers['tier1-prefix'];
        }

        elseif(strcasecmp($clientAppResponseArray['environment'], $tiers['tier2']) == 0){
            $form['environmentPrefix'] = $tiers['tier2-prefix'];
        }

        elseif(strcasecmp($clientAppResponseArray['environment'], $tiers['tier3']) == 0){
            $form['environmentPrefix'] = $tiers['tier3-prefix'];
        }

        $form['description'] = $clientAppResponseArray['description'];
        $form['clientAppId'] = $clientAppResponseArray['clientAppId'];
        $form['clientAppOwnerName'] = $clientAppResponseArray['clientAppOwnerName'];
        $form['status'] = $clientAppResponseArray['status'];
        $form['memberRole'] = $clientAppResponseArray['clientAppMembers'][0]['memberRole'];
        $form['memberEmail'] = $clientAppResponseArray['clientAppMembers'][0]['memberEmail'];

        if((strcasecmp($clientAppResponseArray['status'], 'Approved-'.$tiers['tier1']) == 0)||(strcasecmp($clientAppResponseArray['status'], 'pending-'.$tiers['tier2']) == 0)){

            $tier1_tab_class = $tier2_tab_class = 'enabled-tier';
            $tier3_tab_class = 'disabled-tier';
        }

        elseif(strcasecmp($clientAppResponseArray['status'], $tiers['tier1']) == 0){
          
            $tier1_tab_class = 'enabled-tier';
            $tier2_tab_class = $tier3_tab_class = 'disabled-tier';
        }

        else{

            $tier1_tab_class = $tier2_tab_class = $tier3_tab_class = 'enabled-tier';
        }

            if(strcasecmp($clientAppResponseArray['environment'], $tiers['tier1']) == 0){
                $tier1_tab_class .= ' active-subscription';
            }

            else if(strcasecmp($clientAppResponseArray['environment'], $tiers['tier2']) == 0){
                $tier2_tab_class .= ' active-subscription';
            }

            else{
                $tier3_tab_class .= ' active-subscription';
            }

        $form['tier1'] = $tiers['tier1'];


        $form['tier1_tab'] = array(
                '#markup' => '<li class="'.$tier1_tab_class.'">'.$tiers['tier1-prefix'].'</li>',
        );

        $form['tier2_tab'] = array(
            '#markup' => '<li class="'.$tier2_tab_class.'">'.$tiers['tier2-prefix'].'</li>',
        );

        $form['tier3_tab'] = array(
            '#markup' => '<li class="'.$tier3_tab_class.'">'.$tiers['tier3-prefix'].'</li>',

        );

        if($clientAppResponseArray['status'] != $tiers['tier1']){

            $form['emptyClientApp'] = false;

        }

        else{
            $form['emptyClientApp'] = true;
        }

        if($clientAppResponseArray['status'] == 'pending-'.$tiers['tier2'] || $clientAppResponseArray['status'] == 'pending-'.$tiers['tier3']){
            $form['pendingSubscription'] = true;
        }

        else{
            $form['pendingSubscription'] = false;
        }

        $form['subscription_details'] = array(

            '#ajax' => [
                'callback' => '::showSubscription',
                'event' => 'click',
                'progress'=> '',
         
       
            ],
        );

        $form['DisplayNameInput'] = array(
            '#type' => 'textfield',
            '#required' => false,
            '#value' => $form['clientAppDisplayName'],
            '#prefix' => '<div class="displayNameInputDiv-hidden">',
            '#suffix' => '<span id="applicationName-result"></span></div>',
        );

        $form['DescriptionInput'] = array(
            '#type' => 'textfield',
            '#required' => false,
            '#value' => $form['description'],
            '#maxlength' => 120,
            '#prefix' => '<div class="descriptionInputDiv-hidden"><div class="character-count">'.strlen($form['description']).'/120</div>',
            '#suffix' => '</div>',
        );

        $form['addCollaborator'] = array(
            '#type' => 'textfield',
            '#required' => false,
            '#default_value' => '',
            '#placeholder' => 'Enter Email Address to Add Collaborator',
            '#suffix' => '<i class="far fa-plus-circle add-collaborator-plus"></i>',
        );

        $form['updateClientAppSubmit'] = array(
            '#type' => 'button',
            '#value' => $this->t('Submit'),
            '#attributes' => array('class' => array('updateClientAppSubmitButton')),
            '#ajax' => [
                'callback' => '::updateClientAppSubmit',
                'event' => 'click',
                'progress'=> '',
         
       
            ],
        );

            $promotion_environment = $form_state->getValue('promotionEnvironment');
            $promotion_environment_prefix = $form_state->getValue('promotionEnvironmentPrefix');

            $form_state->set('clientAppInfo', $clientAppResponseArray);

            $form['promotion_environment'] = array(
                '#markup' => $promotion_environment
            );

            $form['promotion_environment_prefix'] = array(
                '#markup' => $promotion_environment_prefix
            );


            $tempstore = \Drupal::service('user.private_tempstore')->get('app_management');
            $tempstore->delete($clientAppResponseArray['clientAppId']);

        return $form;
    }

    public function validateForm(array &$form, FormStateInterface $form_state){

    }

    public function updateClientAppSubmit(array $form, FormStateInterface $form_state) {

        $appManagementController = new AppManagementController();
        $clientAppsController = new ClientAppsController();

        $displayName = $form_state->getUserInput()['DisplayNameInput'];
        $description = $form_state->getUserInput()['DescriptionInput'];

        $clientAppResponseArray = $form_state->get('clientAppInfo');

                
        if(($displayName != $clientAppResponseArray['clientAppDisplayName'])||($description != $clientAppResponseArray['description'])){

            $currentUserId = $appManagementController->getCurrentUserDetails();

            $developerId = $currentUserId[0];
            $apigeeOauthToken = $currentUserId[1];
            
            $payload = new \StdClass();
            $payload->clientAppDisplayName = $displayName;
            $payload->description = $description;
            $payload->status = $clientAppResponseArray['status'];
            
            $response = $clientAppsController->update_client_apps($developerId, $apigeeOauthToken, $payload, $clientAppResponseArray['clientAppId']);
            
            $ajaxResponse = new AjaxResponse();
            $ajaxResponse->addCommand(new HtmlCommand('.clientApp-displayName-visible', $displayName.'<i class="fas fa-edit updateClientAppButton"></i>'));
            if(!empty($description)){
                $ajaxResponse->addCommand(new HtmlCommand('.clientApp-description-visible', $description));
            }

            else{
                $ajaxResponse->addCommand(new HtmlCommand('.clientApp-description-visible', "<i class='fal fa-info-circle'></i>Include information about your application; ex. who it's for, what are the use cases"));
            }

            return $ajaxResponse;

        }


   }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state){
       

    }
}