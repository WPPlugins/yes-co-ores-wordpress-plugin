<?php
//if uninstall not called from WordPress exit
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) )
  exit();

$pluginOptions = array( 'yog_plugin_version', 'yog_3mcp_version', 'yog_koppelingen',
                        'yog_huizenophome', 'yog_objectsinarchief', 'yog_javascript_dojo_dont_enqueue',
                        'yog_cat_custom', 'yog_noextratexts', 'yog_order', 'yog_dossier_mimetypes',
                        'yog-last-sync', 'yog-sync-running', 'yog_sync_disabled', 'yog_nochilds_searchresults',
                        'yog_media_size', 'yog_google_maps_api_key');

foreach ($pluginOptions as $pluginOption)
{
  delete_option($pluginOption);
  delete_site_option($pluginOption);
}