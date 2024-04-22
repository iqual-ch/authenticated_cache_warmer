<?php

namespace Drupal\authenticated_cache_warmer\Service;

use Drupal\authenticated_cache_warmer\CacheWarmerUrl;
use Drupal\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\State\StateInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Promise\Utils;
use Symfony\Component\HttpFoundation\Request;

/**
 * The CacheWarmer service.
 */
class CacheWarmer {

  /**
   * The HTTP client.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient = NULL;

  /**
   * The urls to warm.
   *
   * @var \Drupal\authenticated_cache_warmer\CacheWarmerUrl[]
   */
  protected $urls = [];

  /**
   * The urls that returned a success.
   *
   * @var string[]
   */
  protected $resolved = [];

  /**
   * The urls that returned a fail.
   *
   * @var string[]
   */
  protected $failed = [];

  /**
   * Verbosity level.
   *
   * @var int
   */
  protected $vLevel = 1;

  /**
   * The session id of this cache warming.
   *
   * @var string
   */
  protected $sessionId = '';

  /**
   * The state service.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state = NULL;

  /**
   * Construct a new CacheWarmer.
   *
   * @param \GuzzleHttp\ClientInterface $client
   *   The HTTP client.
   * @param \Drupal\Core\State\StateInterface $state
   *   The urls to warm.
   */
  public function __construct(ClientInterface $client, StateInterface $state) {
    $this->httpClient = $client;
    $this->state = $state;
  }

  /**
   * The Ca.
   *
   * @param \Drupal\Component\DependencyInjection\ContainerInterface $container
   *   The container.
   *
   * @return CacheWarmer
   *   A new CacheWarmer instance.
   */
  public function create(ContainerInterface $container) {
    return new self(
      $container->get('http_client'),
      $container->get('state')
    );
  }

  /**
   * Deletes the authenticated_cache_warmer state.
   */
  public function __destruct() {
  }

  /**
   * Set the urls to warm.
   *
   * @param \Drupal\authenticated_cache_warmer\CacheWarmerUrl[] $urls
   *   The urls to warm.
   */
  public function setUrls(array $urls) {
    $this->urls = $urls;
  }

  /**
   * Set the urls to warm.
   */
  public function reset() {
    $this->urls = [];
    $this->resolved = [];
    $this->failed = [];
    $this->state->delete($this->sessionId);
    $this->sessionId = '';
  }

  /**
   * Set verbosity level (default 1).
   *
   * @param int $level
   *   The verbosity level.
   */
  public function setVerbosity($level = 1) {
    $this->vLevel = $level;
  }

  /**
   * Warm the prepared urls.
   */
  public function warm() {
    $this->sessionId = uniqid('authenticated_cache_warmer');
    $this->state->set($this->sessionId, $this->sessionId);
    reset($this->urls);
    $promises = [];
    for ($i = 0; $i < 10; $i++) {
      $promise = $this->warmCurrent();
      if (!$promise) {
        break;
      }
      $promises[] = $promise;
    }
    Utils::all($promises)->wait();
    Utils::all($promises)->then(function () {
      $this->state->delete($this->sessionId);
    });
  }

  /**
   * Warm the current url in the list.
   */
  protected function warmCurrent() {
    $url = current($this->urls);
    $index = key($this->urls) + 1;
    next($this->urls);
    if ($url) {
      if ($this->vLevel > 0) {
        echo "Processing " . $index . ' of ' . count($this->urls) . "\n";
      }
      $promise = $this->warmUrl($url);
      $self = $this;
      $promise->then(
        function () use ($self, $url) {
          $self->resolve($url);
        },
        function () use ($self, $url) {
          $self->fail($url);
        },
      );
      return $promise;
    }
  }

  /**
   * Warm a specific url.
   *
   * @param \Drupal\authenticated_cache_warmer\CacheWarmerUrl $url
   *   The url to warm.
   *
   * @return \GuzzleHttp\Promise\PromiseInterface
   *   The call promise.
   */
  protected function warmUrl(CacheWarmerUrl $url) {
    try {
      $url->setAbsolute(TRUE);
      $cookies = [];
      $cookies['auth_cache_warmer_uid'] = $url->getAccountId();
      $cookies['auth_cache_warmer_id'] = $this->sessionId;
      $cookieJar = CookieJar::fromArray($cookies, parse_url($url->toString(), PHP_URL_HOST));
      $promise = $this->httpClient->requestAsync(
        'GET',
        $url->toString(FALSE),
        [
          'cookies' => $cookieJar,
          'verify' => FALSE,
          'timeout' => 120,
          'curl' => [
            CURLOPT_CONNECT_TO => ['app.tg-sw-project.localdev.iqual.ch:443:host.docker.internal:443'],
          ],
        ]
        );
    }
    catch (\Exception $e) {
      $this->fail($url);
    }
    return $promise;
  }

  /**
   * Resolves a successful request and calls next url.
   *
   * @param \Drupal\authenticated_cache_warmer\CacheWarmerUrl $previous_url
   *   The previous url.
   */
  public function resolve(CacheWarmerUrl $previous_url = NULL) {
    if ($previous_url) {
      $this->resolved[] = $previous_url;
    }
    $promise = $this->warmCurrent();
    if ($promise) {
      $promise->wait();
    }
  }

  /**
   * Resolves a failed request and calls next url.
   *
   * @param \Drupal\authenticated_cache_warmer\CacheWarmerUrl $previous_url
   *   The previous url.
   */
  public function fail(CacheWarmerUrl $previous_url = NULL) {
    if ($previous_url) {
      $this->failed[] = $previous_url;
    }
    $promise = $this->warmCurrent();
    if ($promise) {
      $promise->wait();
    }
  }

  /**
   * Whether the given request is a cache warming request.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request.
   *
   * @return bool
   *   True, if the request is a cache warming request.
   */
  public static function isCacheWarmRequest(Request $request) {
    $sessionId = $request->cookies->get('auth_cache_warmer_id');
    return \Drupal::state()->get($sessionId, FALSE) === $sessionId;
  }

}
