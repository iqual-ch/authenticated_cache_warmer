# Authenticated cache warmer
This module allows to warm cache on routes that need authentication. It achieves this by impersonating a user in an EventSubscriber using the `account_switcher` service.

## Usage
The module only provides the API to create your own cache warmer service. You need to implement your own drush command or cron job to actually run the cache warming.

To use the API, you need the following data:
- The route you want to call (e.g. `entity.node.canonical`)
- The entity ids to request
- The user ids the impersonate
- (Optional) Http options for the GuzzleHttpClient

You then create one or more CacheWarmUrl and provide them to the `authenticated_cache_warmer.cache_warmer` service. The service will call all the provided urls with the corresponding user account, limited to 10 parallel calls.

### Example
The following snippet will warm both nodes 1 and 2 simulating users 2 and 3.
```php
use Drupal\authenticated_cache_warmer\CacheWarmerUrl;

// Set up data.
$route = 'entity.node.canonical';
$entity_ids = [1, 2];
$user_ids = [2, 3];

// Set up urls.
$urls = [];
foreach($user_ids as $user_id) {
  foreach($entity_ids as $entity_id) {
    $urls[] = CacheWarmerUrl::create($route, ['node' => $entity_id], $user_id);
  }
}

// Run cache warming (use proper DI for service).
$cacheWarmer = \Drupal::service('authenticated_cache_warmer.cache_warmer');
$cacheWarmer->seturls($urls);
$cacheWarmer->warm();
```

## Tips on CacheWarmerUrl
- CacheWarmerUrl extends Drupal/Core/Url, so you can use all the `from*` methods. You then need to set the user account using `setAccountId()`.
- You can pass options to the GuzzleHttpClient used to do the call using `setHttpOptions()` or via the fourth argument on the create method. See `GuzzleHttp\ClientInterface::requestAsync` for details.
