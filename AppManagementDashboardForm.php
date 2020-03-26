<?php

namespace Drupal\efx_app_management_ui\Form;

use Drupal\Core\Form;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\efx_app_management\Controller\ClientAppsController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\efx_app_management\Controller\AppManagementController;

class AppManagementDashboardForm extends FormBase
{
     /**
     * {@inheritdoc}
     */
    public function getFormId()
    {

        return 'app_management_dashboard_form';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state) {

        $appManagementController = new AppManagementController();
        $clientAppsController = new ClientAppsController();

        $currentUserId = $appManagementController->getCurrentUserDetails(); 

        $clientApps = $clientAppsController->get_client_apps($currentUserId[0], $currentUserId[1]);

        if(is_string($clientApps)){
            $site = \Drupal::request()->getSchemeAndHttpHost();
            $error_message = "The server encountered a temporary error and couldn't complete your request. Please try again. If the error persists, <a href='".$site."/contact'>Contact Us</a> so we can help get it straightened out.";
            $clientApps = [];
            \Drupal::messenger()->addMessage(t($error_message), 'error app-management-errors');
        }
          
        $form["clientApps"] = $clientApps;

        $form['#theme'] = 'dashboard_template';
        $form['#attached']['library'][] = 'efx_app_management_ui/clientappValidations';
        $form['#attached']['library'][] = 'drupal.ajax';


        if(is_array($form["clientApps"])){

            if(empty($form["clientApps"])){
                    
                $form['dashboard']['apps'] = 'false';
            }

            else{
                $form['dashboard']['apps'] = 'true';
            }

        }

        else{
            $form['dashboard']['apps'] = 'error';
        }

        $form['createApplicationBlock'] = array(
            '#markup' => '<div class="app-dashboard-input-block-title">Create New Application</div>',
            '#prefix' => '<div class="app-dashboard-block2">',
        );


        $form['appDashboard']['applicationName'] = array(
            '#title' => t('Application Name') . '<i class="fal fa-info-circle app-dashboard-label-info"><div class="dashboard-input-label-tooltip">
                <p class="dahsboard-input-label-tooltip-text">Create a name for your application using characters and/or numbers</p>
                </div></i>',
            '#type' => 'textfield',
            '#required' => true,
            '#prefix' => '<div class="app-dashboard-input-block">',
            '#suffix' => '<span id="applicationName-result"></span>',
            '#default_value' => '',

        );

        $form['appDashboard']['description'] = array(
            '#title' => t('Description') . '<i class="fal fa-info-circle app-dashboard-label-info"><div class="dashboard-input-label-tooltip">
                <p class="dahsboard-input-label-tooltip-text">'."Information about your application provide context to what you're building; ex. who it's for, what are the use cases, etc".'</p>
                </div></i><p class="dashboard-label-optional">(optional)</p>',
            '#type' => 'textfield',
            '#required' => false,
            '#default_value' => '',
            '#prefix' => '<p class="character-count">0/120</p>',
            '#suffix' => '<span id="description-result"></span>',
            '#maxlength' => 120,
        );

        $form['appDashboard']['submit'] = array(
            '#value' => t('Next'),
            '#type' => 'submit',
            '#prefix' => '<div class="app-dashboard-btn">',
            '#suffix' => '</div></div></div>',
        );

   
    
        return $form;
    }

    public function validateForm(array &$form, FormStateInterface $form_state){

    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state){
        $applicationName = $form_state->getValue('applicationName');
        $displayName = preg_replace('!\s+!', ' ', $applicationName);
        $displayName = trim($displayName);
        $description = $form_state->getValue('description');
        $currentUserId = AppManagementController::getCurrentUserDetails();

        $developerId = $currentUserId[0];
        $apigeeOauthToken = $currentUserId[1];
        $developerEmail = $currentUserId[2];
        $fullName = $currentUserId[3];

        $clientAppId = strtolower(str_replace(' ', '-', $developerId.'-'.$displayName));
        
        $keys = \Drupal::service('key.repository');
        $base_tier = $keys->getKey('promotion_tiers')->getKeyValues()['tier1'];

        $payload = new \StdClass();
        $payload->clientAppDisplayName = $displayName;
        $payload->clientAppId = $clientAppId;
        $payload->description = $description;
        $payload->status = $base_tier;
        $payload->environment = $base_tier;
        $payload->clientAppOwnerName = $fullName;        
        $payload->clientAppMembers = new \StdClass();
        $payload->clientAppMembers = [["memberRole" => "Owner", "memberEmail" => $developerEmail]];

        $response = ClientAppsController::create_client_apps($developerId, $apigeeOauthToken, $payload);

        if(is_string($response)){
            
            $site = \Drupal::request()->getSchemeAndHttpHost();
            $error_message = "The server encountered a temporary error and couldn't complete your request. Please try again. If the error persists, <a href='".$site."/contact'>Contact Us</a> so we can help get it straightened out.";

            \Drupal::messenger()->addMessage(t($error_message), 'error app-management-errors');
        }

        else{

            $tempstore = \Drupal::service('user.private_tempstore')->get('app_management');
            $tempstore->set($clientAppId, $response);
            $form_state->setExecuted();

            $redirect_path = '/user/applications/'.$clientAppId;
            $response = new RedirectResponse($redirect_path);
            $response->send();
        }

    }
}


