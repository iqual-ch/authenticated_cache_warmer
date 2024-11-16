<?php

namespace Drupal\authenticated_cache_warmer;

use Drupal\Core\Url;

/**
 * A cache warming url.
 */
class CacheWarmerUrl extends Url {

  /**
   * The uid of the user.
   *
   * @var int
   */
  protected $uid = 0;

  /**
   * The cookies to send with the request.
   *
   * @var array
   */
  protected $cookies = [];

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
   * @param array $options
   *   The options for the url.
   * @param array $cookies
   *   The cookies to send with the request.
   * @param int $uid
   *   The uid of the user.
   * @param array $http_options
   *   The http options for the client.
   *
   * @return CacheWarmerUrl
   *   The new url object.
   */
  public static function create(string $route_name, array $route_parameters, array $options, array $cookies, int $uid, array $http_options = []) : CacheWarmerUrl {
    $url = parent::fromRoute($route_name, $route_parameters, $options);
    $url->setAccountId($uid);
    $url->setCookies($cookies);
    $url->setHttpOptions($http_options);
    return $url;
  }

  /**
   * Get the uid of the user.
   *
   * @return int
   *   The uid.
   */
  public function getAccountId() {
    return $this->uid;
  }

  /**
   * Set the uid of the user.
   *
   * @param int $uid
   *   The uid.
   */
  public function setAccountId($uid) {
    $this->uid = $uid;
  }

  /**
   * Set the cookies to send with the request.
   *
   * @param array $cookies
   *   The cookies.
   */
  public function setCookies($cookies) {
    $this->cookies = $cookies;
  }

  /**
   * Get the cookies to send with the request.
   *
   * @return array
   *   The cookies.
   */
  public function getCookies() {
    return $this->cookies;
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
