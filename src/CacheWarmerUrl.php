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
   * The options for the http call.
   *
   * @var array
   */
  protected $httpOptions = [];

  /**
   * Create a new url from the given parameters.
   *
   * @param string $route_name
   *   The route name.
   * @param array $route_parameters
   *   The route parameters.
   * @param int $account_id
   *   The account id.
   * @param array $http_options
   *   The http options for the client.
   *
   * @return CacheWarmerUrl
   *   The new url object.
   */
  public static function create(string $route_name, array $route_parameters, int $account_id, array $http_options = []) : CacheWarmerUrl {
    $url = parent::fromRoute($route_name, $route_parameters);
    $url->setAccountId($account_id);
    $url->setHttpOptions($http_options);
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
   * Set additional options for the http client.
   *
   * @param array $http_options
   *   The options.
   */
  public function setHttpOptions($http_options) {
    $this->httpOptions = $http_options;
  }

  /**
   * Return the additional options for the http client.
   *
   * @return array
   *   The http client options.
   */
  public function getHttpOptions() {
    return $this->httpOptions;
  }

}
