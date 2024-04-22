<?php

namespace Drupal\authenticated_cache_warmer\EventSubscriber;

use Drupal\authenticated_cache_warmer\Service\CacheWarmer;
use Drupal\user\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Changes active account on cache warming requests.
 */
class CacheWarmerSetup implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [];
    // Set a high level to switch account early.
    $events[KernelEvents::REQUEST][] = ['setupAccount', 100];
    return $events;
  }

  /**
   * Set the user account given in the .
   *
   * @param \Symfony\Component\HttpKernel\Event\RequestEvent $event
   *   The request event.
   */
  public function setupAccount(RequestEvent $event) {
    if (CacheWarmer::isCacheWarmRequest($event->getRequest())) {
      $cookies = $event->getRequest()->cookies;
      $userId = (int) $cookies->get('auth_cache_warmer_uid');
      $account = User::load($userId);
      if ($account) {
        /** @var \Drupal\Core\Session\AccountSwitcherInterface $account_switcher */
        $account_switcher = \Drupal::service('account_switcher');
        $account_switcher->switchTo($account);
        // \Drupal::currentUser()->setAccount($account);
      }
      else {
        throw new \UnexpectedValueException("No user account given");
      }
    }
  }

}
