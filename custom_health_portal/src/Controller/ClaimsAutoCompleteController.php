<?php

namespace Drupal\custom_health_portal\Controller;

use Drupal\Component\Utility\Tags;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use GuzzleHttp\Client;
use \Drupal\Core\Datetime\DrupalDateTime;

/**
 * Class CustomAutocompleteController.
 */
class ClaimsAutoCompleteController extends ControllerBase {

  /**
   * Autocomplete callback for the custom autocomplete field.
   */
  public function handleAutocomplete($search) {
    $matches = [];
    $search = $search ?? '';
    $results = $this->getClaimsData();

    foreach($results as $item) {
        if (stripos($item['claims_number'], $search) !== false) {
            $matches[] = ['value' => $item['claims_number']];
        }
    }

    return new JsonResponse($matches);
  }

  // Method to get claims data.
  private function getClaimsData() {
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
    // Add more rows as needed.
    return $rows;
  }

}
