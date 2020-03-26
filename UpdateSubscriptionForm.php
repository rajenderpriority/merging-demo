<?php

namespace Drupal\efx_app_management_ui\Form;

use Drupal\Core\Form;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\efx_products\Controller\EFXProductsController;
use Drupal\efx_app_management\Controller\AppManagementController;
use Drupal\efx_app_management\Controller\ProductSubscriptionsController;
use Symfony\Component\HttpFoundation\RedirectResponse;

class UpdateSubscriptionForm extends FormBase
{
     /**
     * {@inheritdoc}
     */
    public function getFormId()
    {

        return 'update_subscription_form';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state) {

        $EFXProductsController = new EFXProductsController();
        $response = EFXProductsController::getProductsbyrole();

        $appManagementController = new AppManagementController();
        $keys = \Drupal::service('key.repository');
        $tiers = $keys->getKey('promotion_tiers')->getKeyValues();

        $currentUserId = $appManagementController->getCurrentUserDetails();
        $developerId = $currentUserId[0];
        $apigeeOauthToken = $currentUserId[1];
        $developerEmail = $currentUserId[2];
        $fullName = $currentUserId[3];

        $current_uri = \Drupal::request()->getRequestUri();
        $uri_parts = parse_url($current_uri);
        $path = explode('/', $uri_parts['path']);
        $clientAppId = $path[3];
        $productSubscriptionId = $tiers['tier1'] . '-' . $clientAppId;
        $form['#theme'] = 'update_subscriptions_template';
        $form['products'] = $response;

        $productSubscriptionController = new ProductSubscriptionsController();
        $productSubscription = $productSubscriptionController->get_individual_subscription_details($developerId, $productSubscriptionId, $apigeeOauthToken);

        $productResponse = $productSubscription['credentials'][0]['apiProducts'];

        $productSelected = array();
        if(count($productResponse)>0){
          foreach ($productResponse as $key => $value) {
            $productSelected[] = $value['apiproduct'];
          }
        }


        foreach ($response as $key => $value) {
        $checked = FALSE;
        if(in_array($key,$productSelected)){
          $checked = TRUE;
        }

        $form['checkboxproduct'][str_replace(" ","_",$key)] =  array(
                        '#options' => $value["title"],
                        '#type' => 'checkbox',
                        '#title' => '<span class="heading-text"><a style="color: #333E48;" href="'.$value["url"].'" target="_blank">'.$value["title"].'</a></span>',
                        '#prefix' => '<div class="col-sm-4 margin-left_1-5">
                                        <div class="p-l-5">
                                      <div class="checkbox">',
                        '#suffix' => '</div>

                      <p class="p-l-20 f-s-12"><span style="font: Italic 12px/17px Open Sans; letter-spacing: 0; color: #333E48; opacity: 1;">'.$value["grid_header"].'</span></br></br><span style="font: Regular 12px/24px Open Sans; letter-spacing: 0; color: #333E48; opacity: 1;">'.$value["grid_content"].'</span></p>
                    </div>
                    </div>',
                    '#attributes' => array('class'=>array('checkboxproduct')),
                    '#default_value' => $checked,
                    '#disabled' => $checked,

                  );
                }

        $form['addProductButton'] = array(
          '#value' => t('Add'),
          '#type' => 'submit',

          );

        return $form;
    }

    public function validateForm(array &$form, FormStateInterface $form_state){

    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state){
      $res = $form_state->getValues();
      $selected_products = $this->getSelectedProducts($res);

      $selectedProducts_csv = implode(",",$selected_products);
      $EFXProductsController = new EFXProductsController();
      $response = $EFXProductsController->apiProductsDetails($selectedProducts_csv);
      $products_array = $response;

      $appManagementController = new AppManagementController();
      $currentUserId = $appManagementController->getCurrentUserDetails();

      $current_uri = \Drupal::request()->getRequestUri();
      $uri_parts = parse_url($current_uri);
      $path = explode('/', $uri_parts['path']);

      $clientAppId = $path[3];

      $keys = \Drupal::service('key.repository');
      $tier1 = $keys->getKey('promotion_tiers')->getKeyValues()['tier1'];

      $productSubscriptionName = $tier1 . '-' . $clientAppId;

      $payload = new \StdClass();
      $payload->name = $productSubscriptionName;
      $payload->attributes = new \StdClass();
      $payload->attributes = [['name' => 'clientAppName', 'value' => $clientAppId],['name' => 'displayName', 'value' => $productSubscriptionName],['name' => 'environment', 'value' => $tier1],['name' => 'developerEmail', 'value' => $currentUserId[2]]];
      $payload->apiProducts = [$products_array];
      $payload->targetDate = date('m/d/Y');

      $productSubscriptionsController = new ProductSubscriptionsController();
      $createSubscription = $productSubscriptionsController->updateProductSubscription($currentUserId[0], $payload, $currentUserId[1], $productSubscriptionName);

      $tempstore = \Drupal::service('user.private_tempstore')->get('app_management');
      $tempstore->set($productSubscriptionName, $createSubscription);

      $redirect_path = '/user/applications/'.$clientAppId;
      $response = new RedirectResponse($redirect_path);
      $response->send();

    }

  public function getSelectedProducts($res){
       unset($res["addProductButton"],$res["form_build_id"],$res["form_id"],$res["op"],$res["form_token"]);
       $keys = array_keys($res,1);
       $selectedProducts = array();
       if(count($keys) > 0){
         foreach ($keys as $key => $value) {
           $selectedProducts[] = str_replace("_"," ", $value);
         }
       }
       return $selectedProducts;
  }
}
