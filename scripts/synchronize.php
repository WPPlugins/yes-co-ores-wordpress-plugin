#!/usr/bin/php5
<?php
function findWordpressBasePath()
{
  $dir = dirname(__FILE__) . '/../../';

  while ($dir = realpath($dir . '/..'))
  {
    if (file_exists($dir . '/wp-config.php'))
      return $dir;
  }
  
  return null;
}

set_time_limit(0);
    
try
{
  if (php_sapi_name() !== 'cli')
    throw new \Exception('Script should be run from command line');
  
  // Determine wordpress root path
  $wordpressBasePath = findWordpressBasePath();
  if (is_null($wordpressBasePath))
    throw new \Exception('Failed to determine wordpress base path');
  
  // Initialize wordpress
  define('BASE_PATH',     $wordpressBasePath . '/');
  define('WP_USE_THEMES', false);
  global $wp, $wp_query, $wp_the_query, $wp_rewrite, $wp_did_header;
  require(BASE_PATH . 'wp-load.php');
  
  // Require plugin files
  define('YOG_PLUGIN_DIR', dirname(__FILE__) . '/..');
  require_once(YOG_PLUGIN_DIR . '/includes/config/config.php');
  require_once(YOG_PLUGIN_DIR . '/includes/classes/yog_system_link_manager.php');
  require_once(YOG_PLUGIN_DIR . '/includes/classes/yog_http_manager.php');
  require_once(YOG_PLUGIN_DIR . '/includes/classes/yog_synchronization_manager.php');
  require_once(YOG_PLUGIN_DIR . '/includes/classes/yog_plugin.php');
  require_once(YOG_PLUGIN_DIR . '/includes/yog_cron.php');
  
  // Retrieve system link
  $yogSystemLinkManager       = new YogSystemLinkManager();
  $yogSystemLinks             = $yogSystemLinkManager->retrieveAll();
  
  // Initialize plugin
  $yogPlugin = YogPlugin::getInstance();
  $yogPlugin->init();
  
  // Synchronize each system link
  foreach ($yogSystemLinks as $yogSystemLink)
  {
    $yogSynchronizationManager  = new YogSynchronizationManager($yogSystemLink);
    $response                   = $yogSynchronizationManager->doSync(true);
    
    if (isset($response['errors']) && count($response['errors']) > 0)
      echo 'ERROR: ' . implode(', ', $response['errors']) . "\n";
    
    if (isset($response['warnings']) && count($response['warnings']) > 0)
      echo 'WARNING: ' . implode(', ', $response['warnings']) . "\n";
    
    if (isset($response['handledProjects']) && count($response['handledProjects']) > 0)
      echo 'INFO: handled ' . count($response['handledProjects']) . ' projects' . "\n";
    else
      echo 'INFO: All projects are up-to-date' . "\n";
  }
}
catch (Exception $ex)
{
  echo 'ERROR: ' . $ex->getMessage() . "\n";
}
