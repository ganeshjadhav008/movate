<?php

namespace Drupal\custom_health_portal\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use \Drupal\Core\Datetime\DrupalDateTime;
use GuzzleHttp\Client;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Theme\ThemeManagerInterface;
use Drupal\Core\Render\Markup;

/**
 * Class ViewClaimsForm.
 */
class ViewClaimsForm extends FormBase {

  public function getFormId() {
    return 'custom_health_portal_view_claims_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $patients = $this->getPatientsList();
    $patient_list = ['' => $this->t('--SELECT--')];

    foreach($patients as $item) {
      $patient_list[$item['patient_name']] =$item['patient_name']; 
    }

    $form['patient_name'] = [
      '#type' => 'select',
      '#title' => $this->t('Patient Name'),
      '#options' => $patient_list,
    ];

    $form['claims_number'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Claims number'),
      '#autocomplete_route_name' => 'custom_health_portal.autocomplete',
      '#autocomplete_route_parameters' => ['_format' => 'json', 'search' => ''],
    ];

    $form['service_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Service Type'),
      '#options' => [
        '' => $this->t('--SELECT--'),
        'medical' => $this->t('Medical'),
        'dental' => $this->t('Dental'),
      ]
    ];

    $form['start_date'] = [
      '#type' => 'date',
      '#title' => $this->t('Submission Date'),
      '#date_timezone' => date_default_timezone_get(),
    ];

    $form['end_date'] = [
      '#type' => 'date',
      '#title' => $this->t('Submission Date'),
      '#date_timezone' => date_default_timezone_get(),
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Apply Filters'),
    ];

    $form['reset'] = [
      '#type' => 'submit',
      '#value' => $this->t('RESET Filters'),
    ];

    $form['export'] = [
      '#type' => 'submit',
      '#value' => $this->t('Export Record'),
      '#submit' => ['::exportRecords'],
    ];

    // Display the claims table.
    $header = [
      'claims_number' => $this->t('Claim Number'),
      'patient_name' => $this->t('Patient Name'),
      'service_type' => $this->t('Service Type'),
      'provider_name' => $this->t('Provider Name'),
      'claim_value' => $this->t('Claims Value'),
      'submission_date' => $this->t('Submission Date'),
    ];

    $rows = $this->getClaimsData();

    // Check if there is a submitted filter value
    $form['claims_table'] = [
      '#type' => 'tableselect',
      '#header' => $header,
      '#options' => $rows,
      '#empty' => $this->t('No claims found')
    ];


    return $form;
  }

  /**
   * Submit handler for export.
   */
  public function exportRecords(array &$form, FormStateInterface $form_state) {
    $data = $this->getClaimsData();
    $filename =  time().".xls";      
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=\"$filename\"");
    $this->ExportFile($data);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $start_date = strtotime($form_state->getValue('start_date'));
    $end_date = strtotime($form_state->getValue('end_date'));

    if($start_date && empty($end_date)) {
      $form_state->setErrorByName('end_date', $this->t('Please selecr End date First'));
    }

    if ($start_date && $end_date && $start_date > $end_date) {
      $form_state->setErrorByName('end_date', $this->t('End date must be greater than or equal to start date.'));
    }

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $patient_name = $form_state->getValue('patient_name') ?? null;
    $claims_number = $form_state->getValue('claims_number') ?? null;
    $service_type = $form_state->getValue('service_type') ?? null;
    $provider_name = $form_state->getValue('provider_name') ?? null;
    $start_date = $form_state->getValue('start_date') ?? null;
    $end_date = $form_state->getValue('end_date') ?? null;

    $url = '/view-claims?patient_name=' . rawurlencode($patient_name). '&claims_number=' . rawurlencode($claims_number). '&service_type=' . rawurlencode($service_type) . '&provider_name=' . rawurlencode($provider_name) . '&start_date=' . rawurlencode($start_date) . '&end_date=' . rawurlencode($end_date);
    $response = new RedirectResponse($url);
    $response->send();
  }


  function ExportFile($records) {
    $heading = false;
        if(!empty($records))
          foreach($records as $row) {
            if(!$heading) {
              // display field/column names as a first row
              echo implode("\t", array_keys($row)) . "\n";
              $heading = true;
            }
            echo implode("\t", array_values($row)) . "\n";
        }
    exit;
}


  // Method to Filter Patients List.
  private function getPatientsList() {
    $rows = [];
    $client = new Client();
    try {
      $response = $client->get('https://my-site.ddev.site/api/claims');
      $result = json_decode($response->getBody(), TRUE);
      foreach($result as $item) {
        $rows[] = $item; 
      }
    }
    catch (RequestException $e) {
      $raw[] = "Error";
    }
    return $rows;
  }

  // Method to Filter claims data.
  private function getClaimsData() {
    $rows = [];
    $client = new Client();
    try {
      $response = $client->get('https://my-site.ddev.site/api/claims');
      $result = json_decode($response->getBody(), TRUE);
      foreach($result as $item) {
        $rows[] = $item; 
      }

      //Apply filter here
       if($this->getRequest()->query->get('patient_name')) {
        $rows = $this->getClaimsDataByFilter($rows, $this->getRequest()->query->get('patient_name'));
       }

       if($this->getRequest()->query->get('claims_number')) {
        $rows = $this->getClaimsDataByFilter($rows, $this->getRequest()->query->get('claims_number'));
       }

       if($this->getRequest()->query->get('service_type')) {
        $rows = $this->getClaimsDataByFilter($rows, $this->getRequest()->query->get('service_type'));
       }

       if($this->getRequest()->query->get('provider_name')) {
        $rows = $this->getClaimsDataByFilter($rows, $this->getRequest()->query->get('provider_name'));
       }

       if($this->getRequest()->query->get('start_date') && $this->getRequest()->query->get('end_date')) {
        $rows = $this->getClaimsDataByDate(
          $rows,
          $this->getRequest()->query->get('start_date'),
          $this->getRequest()->query->get('end_date'),
        );
       }

    }
    catch (RequestException $e) {
    }

    return $rows;
  }

  // Method to Filter claims data by Filter.
  private function getClaimsDataByFilter($claimsData, $searchValue) {
    $rows = [];
    // Filter array based on search value using keys
    $filteredClaims = array_filter($claimsData, function ($claim) use ($searchValue) {
        foreach ($claim as $key => $value) {
            // Check if the value is an array
            if (is_array($value)) {
                // If it's an array, check if the search value is in any of its elements
                foreach ($value as $element) {
                    if (stripos($element, $searchValue) !== false) {
                        return true;
                    }
                }
            } else {
                // If it's a string, check if the search value exists in the string
                if (stripos($value, $searchValue) !== false) {
                    return true;
                }
            }
        }
        return false;
    });

    foreach($filteredClaims as $item) {
      $rows[] = $item; 
    }
    return $rows;
  }

  // Method to Filter claims data by Date.
  private function getClaimsDataByDate($claimsData, $startDate, $endDate) {
    // Convert submission_date to a date format
    foreach ($claimsData as &$claim) {
      $claim['submission_date'] = date('Y-m-d', strtotime($claim['submission_date']));
    }

    //echo "<pre>";print_r($claimsData);echo "</pre>";die();
    // Filter claims between the specified range
    $filteredClaims = array_filter($claimsData, function ($claim) use ($startDate, $endDate) {
        $claimDate = strtotime($claim['submission_date']);
        $startDateTimestamp = strtotime($startDate);
        $endDateTimestamp = strtotime($endDate);

        return $claimDate >= $startDateTimestamp && $claimDate <= $endDateTimestamp;
    });

    foreach($filteredClaims as $item) {
      $rows[] = $item; 
    }

    return $rows;
  }

}
