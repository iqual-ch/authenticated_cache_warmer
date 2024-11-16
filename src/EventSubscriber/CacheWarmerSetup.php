<?php

namespace Drupal\authenticated_cache_warmer\EventSubscriber;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Session\AccountSwitcherInterface;
use Drupal\authenticated_cache_warmer\Service\CacheWarmer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Changes active account on cache warming requests.
 */
class CacheWarmerSetup implements EventSubscriberInterface {

  /**
   * Create a new CacheWarmerSetup.
   *
   * @param \Drupal\Core\Session\AccountSwitcherInterface $accountSwitcher
   *   The account switcher.
   * @param \Drupal\Core\Session\AccountProxyInterface $currentUser
   *   The current user.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   */
  public function __construct(
    protected AccountSwitcherInterface $accountSwitcher,
    protected AccountProxyInterface $currentUser,
    protected EntityTypeManagerInterface $entityTypeManager,
  ) {
  }

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
   * Set the user account given in the cookie.
   *
   * @param \Symfony\Component\HttpKernel\Event\RequestEvent $event
   *   The request event.
   */
  public function setupAccount(RequestEvent $event) {
    if (CacheWarmer::isCacheWarmRequest($event->getRequest())) {
      $cookies = $event->getRequest()->cookies;
      $userId = (int) $cookies->get('auth_cache_warmer_uid');
      if ($userId != $this->currentUser->id()) {
        /** @var \Drupal\Core\Session\AccountInterface $account */
        $account = $this->entityTypeManager->getStorage('user')->load($userId);
        if ($account) {
          $this->accountSwitcher->switchTo($account);
        }
        else {
          throw new \UnexpectedValueException("No user account given");
        }
      }
    }
  }

}
