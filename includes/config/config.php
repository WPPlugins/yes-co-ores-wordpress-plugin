<?php
  define('MCP3_USERNAME',           'motivowpplugin');
  define('MCP3_PASSWORD',           'Piwr39qp3@');
  define('MCP3_VERSIONS',           '1.4');
  define('MCP3_FEED_URL',           'https://webservice.yes-co.com/3mcp/collection/%s/%s/feed/%s.xml');

  // Development url
  //define('MCP3_FEED_URL',           'http://devel.cloud.yes-co.com/3mcp/collection/%s/%s/feed/%s.xml');


  define('ATOM_NAMESPACE',          'http://www.w3.org/2005/Atom');
  define('MCP_ATOM_NAMESPACE',      'http://webservice.yes-co.nl/3mcp/%s/atom-extension');
  define('PROJECT_NAMESPACE',       'http://webservice.yesco.nl/mcp/%s/Project');
  define('RELATION_NAMESPACE',      'http://webservice.yesco.nl/mcp/%s/Relation');

  define('YOG_PLUGIN_INSTALLED',    true);
  define('YOG_DEBUG_MODE',          false);

  define('POST_TYPE_WONEN',         'huis');
  define('POST_TYPE_BOG',           'bedrijf');
  define('POST_TYPE_NBPR',          'yog-nbpr');
  define('POST_TYPE_NBTY',          'yog-nbty');
  define('POST_TYPE_NBBN',          'yog-nbbn');
  define('POST_TYPE_BBPR',          'yog-bbpr');
  define('POST_TYPE_BBTY',          'yog-bbty');
  define('POST_TYPE_RELATION',      'relatie');
  define('POST_TYPE_ATTACHMENT',    'attachment');

  define('YOG_PLUGIN_VERSION',      '1.3.32');
  define('YOG_PLUGIN_DOJO_VERSION', '1.12.2'); // Old 1.9.3

  if (get_option('yog_google_maps_api_key'))
    define('SVZ_GOOGLE_MAPS_API_KEY', get_option('yog_google_maps_api_key'));

?>