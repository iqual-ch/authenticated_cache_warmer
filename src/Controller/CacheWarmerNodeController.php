<?php

namespace Drupal\authenticated_cache_warmer\Controller;

use Drupal\Core\Entity\EntityInterface;
use Drupal\node\Controller\NodeViewController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Undocumented class.
 */
class CacheWarmerNodeController extends NodeViewController {

  /**
   * {@inheritdoc}
   */
  public function view(EntityInterface $node, $view_mode = 'full', $langcode = NULL) {
    $sessionId = \Drupal::request()->get('auth_cache_warmer_id');
    if (\Drupal::state()->get($sessionId, '') == $sessionId) {
      return parent::view($node, $view_mode, $langcode);
    }
    throw new NotFoundHttpException();
  }

}
