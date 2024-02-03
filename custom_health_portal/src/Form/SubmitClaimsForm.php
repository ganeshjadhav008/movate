<?php

namespace Drupal\custom_health_portal\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use \Drupal\Core\Datetime\DrupalDateTime;
use Symfony\Component\HttpFoundation\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

/**
 * Class SubmitClaimsForm.
 */
class SubmitClaimsForm extends FormBase {

  public function getFormId() {
    return 'custom_health_portal_submit_claims_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['patient_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Patient Name'),
      '#required' => TRUE,
    ];

    $form['service_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Service Type'),
      '#options' => [
        'medical' => $this->t('Medical'),
        'dental' => $this->t('Dental'),
      ],
      '#required' => TRUE,
    ];

    $form['provider_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Provider Name'),
      '#required' => TRUE,
    ];

    $form['claim_value'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Claims Value'),
      '#required' => TRUE,
    ];

    $form['submission_date'] = [
      '#type' => 'datetime',
      '#title' => $this->t('Submission Date'),
      '#date_timezone' => date_default_timezone_get(),
      '#default_value' => DrupalDateTime::createFromFormat('H:i', date('H:i'))
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit Claims'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if(!preg_match("/^([a-zA-Z' ]+)$/", $form_state->getValue('patient_name'))) {
      $form_state->setErrorByName('patient_name', $this->t('Please enter a valid Patient Name, Patient Name should be Alphabetical.'));
    }

    if(!is_numeric($form_state->getValue('claim_value'))) {
      $form_state->setErrorByName('claim_value', $this->t('Please enter a valid Claims Value'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    try {
      $client = new Client();
      $claims_number = mt_rand(100000000, 999999999);

      $data = [
        "claims_number" => $claims_number,
        "patient_name" => $form_state->getValue('patient_name') ?? null,
        "service_type" => $form_state->getValue('service_type') ?? null,
        "provider_name" => $form_state->getValue('provider_name') ?? null,
        "claim_value" => "$".$form_state->getValue('claim_value') ?? "$0",
        "submission_date" =>  date('Y-m-d H:i:s', strtotime($form_state->getValue('submission_date'))) ?? date('Y-m-d H:i:s')
      ];
    
      $response = $client->post('https://my-site.ddev.site/api/claims', [
          'headers' => [
              'Content-Type' => 'application/json',
          ],
          'json' => $data,
      ]);

      \Drupal::messenger()->addMessage(t("Claim Submitted Successfully. Claim Id: ". $claims_number));
    }
    catch (ClientException $e) {
      $response = $e->getResponse();

      if ($response->getStatusCode() == 404 || $response->getStatusCode() == 403) {
        \Drupal::messenger()->addMessage(t("The requested resource was not found/forbidden."));
      }
      else {
        \Drupal::messenger()->addMessage(t("Something wenr wrong"));
      }
  }
}
}
