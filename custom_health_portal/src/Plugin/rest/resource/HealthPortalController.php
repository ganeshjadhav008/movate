<?php

namespace Drupal\custom_health_portal\Plugin\rest\resource;

use Drupal\Core\Session\AccountProxyInterface;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides a resource to handle claims data.
 *
 * @RestResource(
 *   id = "claims_resource",
 *   label = @Translation("Claims Resource"),
 *   uri_paths = {
 *     "create" = "/api/claims",
 *     "canonical" = "/api/claims"
 *   }
 * )
 */
class HealthPortalController extends ResourceBase {

  /**
   * A logger instance.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Constructs a new \Drupal\custom_health_portal\Plugin\rest\resource\YourModuleRestResource object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param array $serializer_formats
   *   The available serialization formats.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    array $serializer_formats,
    LoggerInterface $logger
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->logger = $logger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('custom_health_portal')
    );
  }

  /**
   * Responds to GET requests.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The HTTP response object.
   */
  public function get(array $options = []) {
    $json_data = $this->readJsonData();
    return new ResourceResponse($json_data);
  }

  /**
   * Responds to POST requests.
   *
   * @param array $data
   *   The data received in the POST request.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The HTTP response object.
   */
  public function post($data) {

    $json_data = $this->readJsonData();

    // Append new data to the existing array.
    $json_data[] = $data;

    // Save the updated data back to the JSON file.
    $this->saveJsonData($json_data);

    return new ResourceResponse(['message' => 'Data stored successfully.']);
  }

  /**
   * Reads data from the JSON file.
   *
   * @return array
   *   The data read from the JSON file.
   */
  protected function readJsonData() {
    $json_file = DRUPAL_ROOT . '/modules/custom/custom_health_portal/claims.json';

    if (file_exists($json_file)) {
      $json_data = file_get_contents($json_file);
      return json_decode($json_data, TRUE);
    }

    return [];
  }

  /**
   * Saves data to the JSON file.
   *
   * @param array $data
   *   The data to be saved to the JSON file.
   */
  protected function saveJsonData(array $data) {
    $json_file = DRUPAL_ROOT . '/modules/custom/custom_health_portal/claims.json';
    $json_data = json_encode($data, JSON_PRETTY_PRINT);
    file_put_contents($json_file, $json_data);
  }

}
