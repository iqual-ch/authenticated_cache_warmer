<?php

namespace Drupal\authenticated_cache_warmer;

use Drupal\Core\Url;

/**
 * A cache warming url.
 */
class CacheWarmerUrl extends Url {

  /**
   * The id of the account to emulate.
   *
   * @var int
   */
  protected $accountId = 0;

  /**
   * The parameters for the http call.
   *
   * @var array
   */
  protected $httpParameters = [];

  /**
   * Create a new url from the given parameters.
   *
   * @param string $route_name
   *   The route name.
   * @param array $route_parameters
   *   The route parameters.
   * @param int $account_id
   *   The account id.
   *
   * @return CacheWarmerUrl
   *   The new url object.
   */
  public static function create(string $route_name, array $route_parameters, int $account_id, array $http_parameters = []) : CacheWarmerUrl {
    $url = parent::fromRoute($route_name, $route_parameters);
    $url->setAccountId($account_id);
    $url->setParameters($http_parameters);
    return $url;
  }

  /**
   * Set the account id to emulate.
   *
   * @param int $id
   *   The account id.
   */
  public function setAccountId(int $id) {
    $this->accountId = $id;
  }

  /**
   * Get the account id.
   */
  public function getAccountId() {
    return $this->accountId;
  }

  /**
   * Set additional parameters for the http client.
   *
   * @param array $http_parameters
   *   The parameters.
   */
  public function setParameters($http_parameters) {
    $this->httpParameters = $http_parameters;
  }

  /**
   * Return the additional parameters for the http client.
   *
   * @return array
   *   The parameters.
   */
  public function getParameters() {
    return $this->httpParameters;
  }

}
