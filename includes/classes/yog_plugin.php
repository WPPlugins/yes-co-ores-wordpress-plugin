<?php
  require_once(YOG_PLUGIN_DIR . '/includes/config/config.php');
  require_once(YOG_PLUGIN_DIR . '/includes/classes/yog_object_search_manager.php');
  require_once(YOG_PLUGIN_DIR . '/includes/classes/yog_system_link_manager.php');

  /**
  * @desc YogPlugin
  * @author Kees Brandenburg - Yes-co Nederland
  */
  class YogPlugin
  {
    static private $instance;

    protected $wpVersion;

    /**
    * @desc Constructor
    *
    * @param void
    * @return YogPlugin
    */
    private function __construct()
    {
      // Include widgets
      require_once(YOG_PLUGIN_DIR . '/includes/widgets/yog_address_search_form_widget.php');
      require_once(YOG_PLUGIN_DIR . '/includes/widgets/yog_recent_objects_widget.php');
      require_once(YOG_PLUGIN_DIR . '/includes/widgets/yog_linked_objects_widget.php');
      require_once(YOG_PLUGIN_DIR . '/includes/widgets/yog_linked_relations_widget.php');
      require_once(YOG_PLUGIN_DIR . '/includes/widgets/yog_contact_form_widget.php');
      require_once(YOG_PLUGIN_DIR . '/includes/widgets/yog_map_widget.php');
			
			if (!defined('YOG_PLUGIN_DISABLE_ATTACHMENT_WIDGET') || YOG_PLUGIN_DISABLE_ATTACHMENT_WIDGET === false)
				require_once(YOG_PLUGIN_DIR . '/includes/widgets/yog_object_attachments_widget.php');

			if (!defined('YOG_PLUGIN_DISABLE_SEARCH_WONEN_WIDGET') || YOG_PLUGIN_DISABLE_SEARCH_WONEN_WIDGET === false)
				require_once(YOG_PLUGIN_DIR . '/includes/widgets/yog_search_form_widget.php');
			if (!defined('YOG_PLUGIN_DISABLE_SEARCH_BOG_WIDGET') || YOG_PLUGIN_DISABLE_SEARCH_BOG_WIDGET === false)
				require_once(YOG_PLUGIN_DIR . '/includes/widgets/yog_search_form_bog_widget.php');
			if (!defined('YOG_PLUGIN_DISABLE_SEARCH_NBPR_WIDGET') || YOG_PLUGIN_DISABLE_SEARCH_NBPR_WIDGET === false)
				require_once(YOG_PLUGIN_DIR . '/includes/widgets/yog_search_form_nbpr_widget.php');
			if (!defined('YOG_PLUGIN_DISABLE_SEARCH_NBTY_WIDGET') || YOG_PLUGIN_DISABLE_SEARCH_NBTY_WIDGET === false)
				require_once(YOG_PLUGIN_DIR . '/includes/widgets/yog_search_form_nbty_widget.php');
			if (!defined('YOG_PLUGIN_DISABLE_SEARCH_BBPR_WIDGET') || YOG_PLUGIN_DISABLE_SEARCH_BBPR_WIDGET === false)
				require_once(YOG_PLUGIN_DIR . '/includes/widgets/yog_search_form_bbpr_widget.php');

      global $wp_version;
      $this->wpVersion = (float) $wp_version;

      $timeZone = get_option('timezone_string');
      if (!empty($timeZone))
        date_default_timezone_set($timeZone);
    }

    /**
    * @desc Get an instance of the YogPlugin
    *
    * @param void
    * @return YogPlugin
    */
    static public function getInstance()
    {
      if (is_null(self::$instance))
      {
      	// Check script name, because using is_admin() is causing fatal on wp 3.7
      	if (strpos($_SERVER['SCRIPT_NAME'], '/wp-admin/') !== false)
          self::$instance = new YogPluginAdmin();
        else
          self::$instance = new YogPluginPublic();
      }

      return self::$instance;
    }

    /**
    * @desc Initialize Wordpress plugin
    *
    * @param void
    * @return void
    */
    public function init()
    {
      add_theme_support('post-thumbnails');
      add_action('init', array($this, 'registerPostTypes'));

      add_action('widgets_init', array($this, 'registerWidgets'));
      add_filter('post_rewrite_rules', array($this, 'insertCustomRewriteRules'));
      add_filter('post_type_link', array($this, 'fixPermalinks'), 1, 3);

      register_deactivation_hook(YOG_PLUGIN_DIR . '/yesco-og.php', array($this, 'onDeactivation'));
    }

    /**
    * @desc Fix NBty/BBty permalinks
    *
    * @param string $permalink
    * @param StdClass $post
    * @param bool $leavename
    * @return string
    */
    public function fixPermalinks($permalink, $post, $leavename)
    {
      switch ($post->post_type)
      {
        case POST_TYPE_NBTY:

          if (!empty($post->post_parent))
          {
            $parent = get_post($post->post_parent);

            $permalink = str_replace('/nieuwbouw-type/', '/nieuwbouw/' . $parent->post_name . '/type/', $permalink);

            if (strpos($permalink, '%' . POST_TYPE_NBTY . '%') !== false && !empty($post->post_parent))
              $permalink = str_replace('%' . POST_TYPE_NBTY . '%', '%pagename%', $permalink);
          }

          break;
        case POST_TYPE_BBTY:

          if (!empty($post->post_parent))
          {
            $parent = get_post($post->post_parent);

            $permalink = str_replace('/yog-bbty/', '/complex/' . $parent->post_name . '/type/', $permalink);

            if (strpos($permalink, '%' . POST_TYPE_BBTY . '%') !== false && !empty($post->post_parent))
              $permalink = str_replace('%' . POST_TYPE_BBTY . '%', '%pagename%', $permalink);
          }

          break;
      }

	    return $permalink;
    }

    /**
    * @desc Add custom rewrite rules for NBty
    *
    * @param array $rules
    * @return array
    */
    public function insertCustomRewriteRules($rules)
    {
	    $newrules = array();
	    $newrules['nieuwbouw/(.+?)/type/(.+?)$']  = 'index.php?' . POST_TYPE_NBTY . '=$matches[2]';
      $newrules['complex/(.+?)/type/(.+?)$']    = 'index.php?' . POST_TYPE_BBTY . '=$matches[2]';

	    return $newrules + $rules;
    }

    public static function enqueueDojo()
    {
      add_action('wp_head', array(YogPlugin, 'loadDojo'));
      add_action('admin_head', array(YogPlugin, 'loadDojo'));
    }

    private static $dojoLoaded = false;

    public static function isDojoLoaded()
    {
      return self::$dojoLoaded;
    }

    public static function loadDojo()
    {
      self::$dojoLoaded = true;

      echo '<script type="text/javascript">
            // <![CDATA[
              var djConfig = {
              cacheBust: "' . YOG_PLUGIN_VERSION . '",
              async: true
              };

              delete define;

            // ]]>
            </script>';

      $dojoUrl = 'http://';

      if (is_ssl())
        $dojoUrl = 'https://';

        $dojoUrl .= 'ajax.googleapis.com/ajax/libs/dojo/' . YOG_PLUGIN_DOJO_VERSION. '/dojo/dojo.js';

      // Fix for jquery being loaded crashing whole interface
      if (get_option('yog_javascript_dojo_dont_enqueue'))
        echo '<script defer type="text/javascript" src="' . $dojoUrl . '"></script>';
      else
        wp_enqueue_script('dojo', $dojoUrl, false, YOG_PLUGIN_DOJO_VERSION);

    }

    /**
    * @desc Enqueue files
    *
    * @param void
    * @return void
    */
    public function enqueueFiles()
    {

    }

    /**
    * @desc Register post types
    *
    * @param void
    * @return void
    */
    public function registerPostTypes()
    {
      if (get_option('yog_cat_custom'))
      {
        register_taxonomy('yog_category',
                          array(POST_TYPE_WONEN, POST_TYPE_BOG, POST_TYPE_NBPR, POST_TYPE_NBTY,
                                POST_TYPE_NBBN, POST_TYPE_BBPR, POST_TYPE_BBTY),
                          array('hierarchical'      => true,
                                'show_ui'           => true,
                                'rewrite'           => array('slug' => 'objecten'),
                                'labels'            => array('name' => 'Object categori&euml;n'),
                                'capabilities'      => array('manage_terms', 'edit_terms', 'delete_terms', 'assign_terms'),
                                'show_in_menu'      => 'yog_posts_menu',
                                'query_var'         => 'objecten'
                                ));

        $taxonomies = array('yog_category', 'post_tag');
      }
      else
      {
        $taxonomies = array('category', 'post_tag');
      }

	    register_post_type(POST_TYPE_WONEN,
	                  array('labels'    => array('name'               => 'Wonen',
	                                            'singular_name'       => 'Woon object',
                                              'add_new'             => 'Toevoegen',
                                              'add_new_item'        => 'Object toevoegen',
                                              'search_items'        => 'Objecten zoeken',
                                              'not_found'           => 'Geen objecten gevonden',
                                              'not_found_in_trash'  => 'Geen objecten gevonden in de prullenbak',
                                              'edit_item'           => 'Object bewerken',
                                              'view_item'           => __('View')
                                              ),
                          'public'            => true,
	                        'show_ui'           => true, // UI in admin panel
                          'show_in_menu'      => 'yog_posts_menu',
	                        'show_in_nav_menus' => true,
	                        'capability_type'   => 'post',
                          'menu_icon'         => YOG_PLUGIN_URL . '/img/icon_yes-co.gif',
	                        'hierarchical'      => false,
	                        'rewrite'           => array('slug' => POST_TYPE_WONEN), // Permalinks format
	                        'supports'          => array('title','editor', 'thumbnail'),
	                        'taxonomies'        => $taxonomies
	                        )
	    );

	    register_post_type(POST_TYPE_BOG,
	                  array('labels'    => array('name'               => 'BOG',
	                                            'singular_name'       => 'BOG object',
                                              'add_new'             => 'BOG object toevoegen',
                                              'add_new_item'        => 'Object toevoegen',
                                              'search_items'        => 'Objecten zoeken',
                                              'not_found'           => 'Geen objecten gevonden',
                                              'not_found_in_trash'  => 'Geen objecten gevonden in de prullenbak',
                                              'edit_item'           => 'Object bewerken',
                                              'view_item'           => __('View')
                                              ),
                          'public'            => true,
	                        'show_ui'           => true, // UI in admin panel
                          'show_in_menu'      => 'yog_posts_menu',
	                        'show_in_nav_menus' => true,
	                        'capability_type'   => 'post',
                          'menu_icon'         => YOG_PLUGIN_URL . '/img/icon_yes-co.gif',
	                        'hierarchical'      => false,
	                        'rewrite'           => array('slug' => POST_TYPE_BOG), // Permalinks format
	                        'supports'          => array('title','editor', 'thumbnail'),
	                        'taxonomies'        => $taxonomies
	                        )
	    );

	    register_post_type(POST_TYPE_NBPR,
	                  array('labels'    => array('name'               => 'Nieuwbouw',
	                                            'singular_name'       => 'Nieuwbouw project',
                                              'add_new'             => 'Nieuwbouw project toevoegen',
                                              'add_new_item'        => 'Project toevoegen',
                                              'search_items'        => 'Projecten zoeken',
                                              'not_found'           => 'Geen nieuwbouw projecten gevonden',
                                              'not_found_in_trash'  => 'Geen nieuwbouw projecten gevonden in de prullenbak',
                                              'edit_item'           => 'Project bewerken',
                                              'view_item'           => __('View')
                                              ),
                          'public'            => true,
	                        'show_ui'           => true, // UI in admin panel
                          'show_in_menu'      => 'yog_posts_menu',
	                        'show_in_nav_menus' => true,
	                        'capability_type'   => 'post',
                          'menu_icon'         => YOG_PLUGIN_URL . '/img/icon_yes-co.gif',
	                        'hierarchical'      => false,
	                        'rewrite'           => array('slug' => 'nieuwbouw'), // Permalinks format
	                        'supports'          => array('title','editor', 'thumbnail'),
	                        'taxonomies'        => $taxonomies
	                        )
	    );

	    register_post_type(POST_TYPE_NBTY,
	                  array('labels'    => array('name'               => 'Nieuwbouw types',
	                                            'singular_name'       => 'Nieuwbouw type',
                                              'add_new'             => 'Nieuwbouw type toevoegen',
                                              'add_new_item'        => 'Type toevoegen',
                                              'search_items'        => 'Types zoeken',
                                              'not_found'           => 'Geen nieuwbouw types gevonden',
                                              'not_found_in_trash'  => 'Geen nieuwbouw types gevonden in de prullenbak',
                                              'edit_item'           => 'Type bewerken',
                                              'view_item'           => __('View')
                                              ),
                          'public'            => true,
	                        'show_ui'           => true, // UI in admin panel
                          'show_in_menu'      => 'yog_posts_menu',
	                        'show_in_nav_menus' => true,
	                        'capability_type'   => 'post',
                          'menu_icon'         => YOG_PLUGIN_URL . '/img/icon_yes-co.gif',
	                        'hierarchical'      => false,
	                        'rewrite'           => array('slug' => 'nieuwbouw-type', 'with_front' => false), // Permalinks format
	                        'supports'          => array('title','editor', 'thumbnail'),
	                        'taxonomies'        => $taxonomies
	                        )
	    );

	    register_post_type(POST_TYPE_NBBN,
	                  array('labels'    => array('name'               => 'Nieuwbouw bouwnummers',
	                                            'singular_name'       => 'Nieuwbouw bouwnummer',
                                              'add_new'             => 'Nieuwbouw bouwnummer toevoegen',
                                              'add_new_item'        => 'Bouwnummer toevoegen',
                                              'search_items'        => 'Bouwnummers zoeken',
                                              'not_found'           => 'Geen nieuwbouw bouwnummers gevonden',
                                              'not_found_in_trash'  => 'Geen nieuwbouw bouwnummers gevonden in de prullenbak',
                                              'edit_item'           => 'Bouwnummer bewerken'
                                              ),
                          'public'            => false,
	                        'show_ui'           => true, // UI in admin panel
                          'show_in_menu'      => 'yog_posts_menu',
	                        'show_in_nav_menus' => true,
	                        'capability_type'   => 'post',
                          'menu_icon'         => YOG_PLUGIN_URL . '/img/icon_yes-co.gif',
	                        'hierarchical'      => true,
	                        'rewrite'           => array('slug' => 'nieuwbouw-bouwnummer'), // Permalinks format
	                        'supports'          => array('title')
	                        )
	    );

	    register_post_type(POST_TYPE_BBPR,
	                  array('labels'    => array('name'               => 'Complex',
	                                            'singular_name'       => 'Complex (bestaande bouw project)',
                                              'add_new'             => 'Complex toevoegen',
                                              'add_new_item'        => 'Complex toevoegen',
                                              'search_items'        => 'Complexen zoeken',
                                              'not_found'           => 'Geen complexen (bestaande bouw projecten) gevonden',
                                              'not_found_in_trash'  => 'Geen complexen (bestaande bouw projecten) gevonden in de prullenbak',
                                              'edit_item'           => 'Project bewerken',
                                              'view_item'           => __('View')
                                              ),
                          'public'            => true,
	                        'show_ui'           => true, // UI in admin panel
                          'show_in_menu'      => 'yog_posts_menu',
	                        'show_in_nav_menus' => true,
	                        'capability_type'   => 'post',
                          'menu_icon'         => YOG_PLUGIN_URL . '/img/icon_yes-co.gif',
	                        'hierarchical'      => false,
	                        'rewrite'           => array('slug' => 'complex'), // Permalinks format
	                        'supports'          => array('title','editor', 'thumbnail'),
	                        'taxonomies'        => $taxonomies
	                        )
	    );

	    register_post_type(POST_TYPE_BBTY,
	                  array('labels'    => array('name'               => 'Complex types',
	                                            'singular_name'       => 'Complex type',
                                              'add_new'             => 'Complex type toevoegen',
                                              'add_new_item'        => 'Type toevoegen',
                                              'search_items'        => 'Types zoeken',
                                              'not_found'           => 'Geen complex (bestaande bouw) types gevonden',
                                              'not_found_in_trash'  => 'Geen complex (bestaande bouw) types gevonden in de prullenbak',
                                              'edit_item'           => 'Type bewerken',
                                              'view_item'           => __('View')
                                              ),
                          'public'            => true,
	                        'show_ui'           => true, // UI in admin panel
                          'show_in_menu'      => 'yog_posts_menu',
	                        'show_in_nav_menus' => true,
	                        'capability_type'   => 'post',
                          'menu_icon'         => YOG_PLUGIN_URL . '/img/icon_yes-co.gif',
	                        'hierarchical'      => false,
	                        //'rewrite'           => array('slug' => 'complex-type', 'with_front' => false), // Permalinks format
	                        'supports'          => array('title','editor', 'thumbnail'),
	                        'taxonomies'        => $taxonomies
	                        )
	    );

	    register_post_type('relatie',
	                  array('labels'    => array( 'name'                => 'Relaties',
	                                              'singular_name'       => 'Relatie',
                                                'add_new'             => 'Toevoegen',
                                                'add_new_item'        => 'Relatie toevoegen',
                                                'search_items'        => 'Relaties zoeken',
                                                'not_found'           => 'Geen relaties gevonden',
                                                'not_found_in_trash'  => 'Geen relaties gevonden in de prullenbak'
                                                ),
	                        'public'            => false,
	                        'show_ui'           => true, // UI in admin panel
                          'show_in_menu'      => 'yog_posts_menu',
	                        'show_in_nav_menus' => true,
	                        'capability_type'   => 'post',
                          'menu_icon'         => YOG_PLUGIN_URL . '/img/icon_yes-co.gif',
	                        'hierarchical'      => false,
	                        'rewrite'           => array('slug' => POST_TYPE_RELATION), // Permalinks format
	                        'supports'          => array('title')
	                        )
	    );
    }

    /**
    * @desc Register widgets
    *
    * @param void
    * @return void
    */
    public function registerWidgets()
    {
      register_widget('YogRecentObjectsWidget');
			if (!defined('YOG_PLUGIN_DISABLE_SEARCH_WONEN_WIDGET') || YOG_PLUGIN_DISABLE_SEARCH_WONEN_WIDGET === false)
				register_widget('YogSearchFormWonenWidget');
			if (!defined('YOG_PLUGIN_DISABLE_SEARCH_BOG_WIDGET') || YOG_PLUGIN_DISABLE_SEARCH_BOG_WIDGET === false)
				register_widget('YogSearchFormBogWidget');
			if (!defined('YOG_PLUGIN_DISABLE_SEARCH_NBPR_WIDGET') || YOG_PLUGIN_DISABLE_SEARCH_NBPR_WIDGET === false)
				register_widget('YogSearchFormNBprWidget');
			if (!defined('YOG_PLUGIN_DISABLE_SEARCH_NBTY_WIDGET') || YOG_PLUGIN_DISABLE_SEARCH_NBTY_WIDGET === false)
				register_widget('YogSearchFormNBtyWidget');
			if (!defined('YOG_PLUGIN_DISABLE_SEARCH_BBPR_WIDGET') || YOG_PLUGIN_DISABLE_SEARCH_BBPR_WIDGET === false)
				register_widget('YogSearchFormBBprWidget');
      register_widget('YogAddressSearchFormWidget');
      register_widget('YogContactFormWidget');
      register_widget('YogMapWidget');
			if (!defined('YOG_PLUGIN_DISABLE_ATTACHMENT_WIDGET') || YOG_PLUGIN_DISABLE_ATTACHMENT_WIDGET === false)
				register_widget('YogObjectAttachmentsWidget');
      register_widget('YogLinkedObjectsWidget');
      register_widget('YogLinkedRelationsWidget');
    }

    /**
    * Cleanup some things on deactivation
    */
    public function onDeactivation()
    {
      if (wp_next_scheduled('yog_cron_open_houses'))
        wp_clear_scheduled_hook('yog_cron_open_houses');
    }

    /**
     * List all theme php files (parent + child theme files)
     *
     * @param void
     * @return array
     */
    protected function listThemeFiles()
    {
      $parentFiles  = glob(get_template_directory() . '/*.php');
      $childFiles   = glob(get_stylesheet_directory() . '/*.php');
      $files        = array();

      foreach ($parentFiles as $parentFile)
      {
        $files[basename($parentFile)] = $parentFile;
      }

      foreach ($childFiles as $childFile)
      {
        $files[basename($childFile)] = $childFile;
      }

      return $files;
    }
  }

  /**
  * @desc YogPluginAdmin
  * @author Kees Brandenburg - Yes-co Nederland
  */
  class YogPluginPublic extends YogPlugin
  {
    /**
    * @desc Initialize Wordpress public
    *
    * @param void
    * @return void
    */
    public function init()
    {
      parent::init();

      add_filter('pre_get_posts',           array($this, 'extendPostQuery'));
      add_filter('the_content',             array($this, 'extendTheContent'));

      if (get_option('yog_javascript_dojo_dont_enqueue'))
        add_filter( 'wp_enqueue_scripts', array($this, 'enqueueFiles') , 0 );
      else
        add_action('init',                    array($this, 'enqueueFiles'));

      // Add shortcodes
      add_shortcode('yog-widget',         array($this, 'handleWidgetShortcode'));
      add_shortcode('yog-contact-widget', array($this, 'handleContactWidgetShortcode'));
      add_shortcode('yog-map',            array($this, 'handleMapShortcode'));
      add_shortcode('yog-objects',        array($this, 'handleObjectsShortcode'));
			
			// Add inline script to head
			add_action('wp_head',								array($this, 'printInlineScripts'));

      $searchManager = YogObjectSearchManager::getInstance();
     	$searchManager->extendSearch();
    }

    /**
    * @desc Enqueue files
    *
    * @param void
    * @return void
    */
    public function enqueueFiles()
    {
      parent::enqueueFiles();
			
      //$minifyExtension = (YOG_DEBUG_MODE === true) ? '' : '.min';
      //wp_enqueue_script('jquery-ui-touch-punch', YOG_PLUGIN_URL .'/inc/js/jquery.ui.touch-punch.min.js', array('jquery', 'jquery-ui-core'),YOG_PLUGIN_VERSION, true);
    }
		
		/**
		 * Print inline scripts in head section
		 */
		public function printInlineScripts()
		{
			$yogConfig = array('baseUrl' => home_url());
			
			echo '<script type=\'text/javascript\'>' . "\n";
			echo '/* <![CDATA[ */' . "\n";
			echo 'var YogConfig = ' . json_encode($yogConfig) . ';' . "\n";
			echo '/* ]]> */' . "\n";
			echo '</script>';
		}

    /**
    * @desc Extend the content, if theme contains no single-*.php template
    *
    * @param string $content
    * @return string
    */
    public function extendTheContent($content)
    {
      $postType = get_post_type();
      $prefix   = '';
      $suffix   = '';

      if (is_single() && in_array($postType, yog_getAllPostTypes()) && locate_template('single-' . $postType . '.php') == '')
      {
        // Add photo slider
        $prefix .= yog_retrievePhotoSlider();

        // Add prices
        $prices = yog_retrievePrices();
        if (count($prices) > 0)
          $prefix .= '<div class="yog-prices">' . implode('<br />', $prices) . '</div>';

        switch ($postType)
        {
          case POST_TYPE_WONEN:
            // Add open house
            if (yog_hasOpenHouse())
              $prefix .= '<div class="yog-open-house">' . yog_getOpenHouse() . '</div>';

            // Add location
            $suffix = yog_retrieveDynamicMap();
            break;
          case POST_TYPE_BOG:
            // Add location
            $suffix .= yog_retrieveDynamicMap();
            break;
          case POST_TYPE_NBPR:
          case POST_TYPE_BBPR:
            // Add location
            $suffix .= yog_retrieveDynamicMap();

            // Add types
            $childs = yog_retrieveChildObjects();
            if (is_array($childs) && count($childs) > 0)
            {
              $suffix .= '<h2>Types</h2>';

              foreach ($childs as $child)
              {
                $name   = $child->post_title;
                $image  = get_the_post_thumbnail($child->ID, 'thumbnail', array('alt' => $name, 'title' => $name));
                $url    = get_permalink($child->ID);

                $suffix .= '<div class="yog-post-child">';
                if (!empty($image))
                  $suffix .= '<a href="' . $url . '" title="' . $name . '">' . $image . '</a> ';

                  $suffix .= '<a href="' . $url . '" title="' . $name . '">' . $name . '</a>';
                $suffix .= '</div>';
              }
            }
            break;
          case POST_TYPE_NBTY:
            // Add NBbn
            $table = yog_retrieveNbbnTable();
            if (!empty($table))
            {
              $suffix .= '<h2>Bouwnummers</h2>';
              $suffix .= $table;
            }
            break;
          case POST_TYPE_BBTY:

            // Add child objects
            $childs = yog_retrieveChildObjects();

            if (is_array($childs) && count($childs) > 0)
            {
              $suffix .= '<h2>Objecten</h2>';

              foreach ($childs as $child)
              {
                $name   = $child->post_title;
                $image  = get_the_post_thumbnail($child->ID, 'thumbnail', array('alt' => $name, 'title' => $name));
                $url    = get_permalink($child->ID);

                $suffix .= '<div class="yog-post-child">';
                if (!empty($image))
                  $suffix .= '<a href="' . $url . '" title="' . $name . '">' . $image . '</a> ';

                  $suffix .= '<a href="' . $url . '" title="' . $name . '">' . $name . '</a>';
                $suffix .= '</div>';
              }
            }
            break;
        }
      }

      return $prefix . $content . $suffix;
    }

    /**
    * @desc Register the post types to use on several pages
    *
    * @param WP_Query $query
    * @return WP_Query
    */
    public function extendPostQuery($query)
    {
      if ($query->is_main_query())
      {
        $extendQuery    = true;
        $isYogCategory  = is_tax('yog_category');

        if ($isYogCategory)
          $extendQuery = false;
        else if (!(!isset($query->query_vars['suppress_filters']) || $query->query_vars['suppress_filters'] == false))
          $extendQuery = false;
        else if (!($query->is_archive || $query->is_category || $query->is_feed || $query->is_home))
          $extendQuery = false;
        else if ($query->is_archive && !$query->is_category && !$query->is_tag && !get_option('yog_objectsinarchief'))
          $extendQuery = false;
        else if ($query->is_home && !get_option('yog_huizenophome'))
          $extendQuery = false;

        // Make post types available
        if ($extendQuery === true)
        {
          $postTypes  = $query->get('post_type');
          if (empty($postTypes))
            $postTypes = array('post');
          else if (!is_array($postTypes))
            $postTypes = array($postTypes);

          if (!in_array(POST_TYPE_WONEN, $postTypes))
            $postTypes[] = POST_TYPE_WONEN;

          if (!in_array(POST_TYPE_BOG, $postTypes))
            $postTypes[] = POST_TYPE_BOG;

          if (!in_array(POST_TYPE_NBPR, $postTypes))
            $postTypes[] = POST_TYPE_NBPR;

          if (!in_array(POST_TYPE_NBTY, $postTypes))
            $postTypes[] = POST_TYPE_NBTY;

          if (!in_array(POST_TYPE_BBPR, $postTypes))
            $postTypes[] = POST_TYPE_BBPR;

          if (!in_array(POST_TYPE_BBTY, $postTypes))
            $postTypes[] = POST_TYPE_BBTY;

          $query->set('post_type', $postTypes);
        }

        // Set custom order
        if ($isYogCategory)
        {
          $defaultOrder = get_option('yog_order');

          if (!empty($defaultOrder))
          {
            switch ($defaultOrder)
            {
              case 'date_asc':
              case 'title_asc':
              case 'price_asc':
                $query->set('order', 'ASC');
                break;
              case 'title_desc':
              case 'price_desc';
                $query->set('order', 'DESC');
                break;
            }

            switch ($defaultOrder)
            {
              case 'date_asc':
                $query->set('orderby', 'date');
                break;
              case 'title_asc':
              case 'title_desc':
                $query->set('orderby', 'title');
                break;
              case 'price_asc':
              case 'price_desc';
                $query->set('orderby',  'meta_value_num');
                $query->set('meta_key', 'yog_price_order');
                break;
            }
          }
        }
      }
    }

    /**
     * Handle widget shortcodes like [yog-widget type=".." id=".."]
     *
     * @param array $attr
     * @return string
     */
    public function handleWidgetShortcode($attr)
    {
      if (!empty($attr['type']) && !empty($attr['id']))
      {
        global $wp_registered_widgets;

        // Check type
        switch ($attr['type'])
        {
          case 'contact':
            $widgetType = $attr['type'] . 'form';
            break;
          case 'searchwonen':
          case 'searchbog':
          case 'searchnbpr':
          case 'searchnbty':
          case 'searchbbpr':
            $widgetType = str_replace('search', 'searchform', $attr['type']);
            break;
          default:
            return '';
            break;
        }

        ///YogSearchFormNBtyWidget

        $widgetNr     = $attr['id'];
        $widgetClass  = 'widget_yog' . $widgetType . 'widget';
        $widgetId     = 'yog' . $widgetType . 'widget-' .  $widgetNr;

        // Widget not found, so return empty string
        if (empty($wp_registered_widgets[$widgetId]))
          return '';

        // Widget object not found
        if (empty($wp_registered_widgets[$widgetId]['callback']) || empty($wp_registered_widgets[$widgetId]['callback'][0]))
          return '';

        // Get widget object
        $widgetObject = $wp_registered_widgets[$widgetId]['callback'][0];

        // Determine args / settings
        $args         = array(
                          'before_widget' => '<div class="widget ' . $widgetClass . '" id="' . $widgetId . 'shortcode">',
                          'before_title'  => '<h2 class="widgettitle">',
                          'after_title'   => '</h2>',
                          'after_widget'  => '</div>'
                        );
        $settings     = $widgetObject->get_settings();

        // Catch widget output through output buffering
        ob_start();
        $widgetObject->widget($args, $settings[$widgetNr]);
        $html = ob_get_contents();
        ob_end_clean();

        // Return widget html
        return $html;
      }
    }

    /**
     * Handle depricated contact widget shortcode like [yog-contact-widget id=".."]
     *
     * @param array $attr
     * @return string
     */
    public function handleContactWidgetShortcode($attr)
    {
      $attr['type'] = 'contact';
      return $this->handleWidgetShortcode($attr);
    }

    /**
     * Handle map shortcode like [yog-map center_latitude=".." center_longitude=".." zoomlevel="9" map_type="terrain" width="100" width_unit="%" height="100" height_unit="%" control_map_type_position=".." control_pan_position=".." control_zoom_position=".."]
     * @param type $attr
     * @return type
     */
    public function handleMapShortcode($attr)
    {
      $mapWidget = new YogMapWidget();
      $settings  = $mapWidget->shortcodeAttributesToSettings($attr);

      return $mapWidget->generate($settings);
    }

    /**
     * Handle objects shortcode like [yob-objects type=".." num=".." cat=".." order=".."]
     * @param type $attr
     */
    public function handleObjectsShortcode($attr)
    {
      // Determine provided params
      $type           = !empty($attr['type']) ? explode(',', $attr['type']) : array(POST_TYPE_WONEN, POST_TYPE_BOG, POST_TYPE_NBPR, POST_TYPE_NBTY, POST_TYPE_BBPR, POST_TYPE_BBTY);

      // Determine query attributes
      $query = array(
        'post_type'       => $type,
        'posts_per_page'  => (!empty($attr['num']) ? $attr['num'] : 5000),
        'nopaging'        => (!empty($attr['num']) ? true : false)
      );

      // Add category to the query?
      if (!empty($attr['cat']))
      {
        $query['tax_query'] = array(
          array(
            'taxonomy'  => (get_option('yog_cat_custom') ? 'yog_category' : 'category'),
            'field'     => 'slug',
            'terms'     => $attr['cat']
          )
        );
      }

      // Add order to the query?
      if (!empty($attr['order']) && in_array($attr['order'], array('date_asc', 'date_desc', 'title_asc', 'title_desc', 'price_asc', 'price_desc')))
      {
        list($orderBy, $order) = explode('_', $attr['order']);

        if ($orderBy == 'price')
        {
          $query['orderby']   = 'meta_value_num';
          $query['meta_key']  = 'yog_price_order';
        }
        else
        {
          $query['orderby'] = $orderBy;
        }

        $query['order'] = strtoupper($order);
      }

      // Retrieve posts
      $posts  = new WP_Query($query);

      if ($posts->have_posts())
      {
        while ($posts->have_posts())
        {
          $posts->the_post();

          // Use template to show object
          if (!empty($attr['template']))
          {
            ob_start();
            get_template_part('object', $attr['template']);
            $output .= ob_get_contents();
            ob_end_clean();
          }
          // Show default output
          else
          {
            $title      = get_the_title();
            $permalink  = get_permalink();

            $output .= '<div class="yog-post post-' . get_post_type() . '">';
              $output .= '<h2><a href="' . $permalink . '" rel="bookmark" title="' . $title . '">' . yog_retrieveSpec('Naam') . '</a></h2>';
              if (has_post_thumbnail())
                $output .= '<a href="' . $permalink . '" rel="bookmark" title="' . $title . '">' . get_the_post_thumbnail() . '</a>';
            $output .= '</div>';
          }
        }
      }

      return $output;
    }
  }

  /**
  * @desc YogPluginAdmin
  * @author Kees Brandenburg - Yes-co Nederland
  */
  class YogPluginAdmin extends YogPlugin
  {
    private $optionGroup = 'yesco_OG';

    /**
    * @desc Initialize Wordpress admin
    *
    * @param void
    * @return void
    */
    public function init()
    {
      parent::init();

      add_action('admin_menu',              array($this, 'createAdminMenu'));
      add_action('init',                    array($this, 'enqueueFiles'));
      add_action('init',                    array($this, 'checkPluginVersion'));
      add_filter('editable_slug',           array($this, 'fixEditableparmalinkSlug'));
      add_action('wp_dashboard_setup',      array($this, 'initDashboardWidgets'));

      // Ajax callbacks
      add_action('wp_ajax_setsetting',      array($this, 'ajaxSetSetting'));
      add_action('wp_ajax_addkoppeling',    array($this, 'addSystemLink'));
      add_action('wp_ajax_removekoppeling', array($this, 'ajaxRemoveSystemLink'));
      //

      // Init custom post type admin pages
      if (!empty($_REQUEST['post_type']) || !empty($_REQUEST['post']))
      {
        require_once(YOG_PLUGIN_DIR . '/includes/classes/yog_wp_admin_object_ui.php');

        $postType  = empty($_REQUEST['post_type']) ? get_post_type((int) $_REQUEST['post']) : $_REQUEST['post_type'];
        $wpAdminUi = YogWpAdminUiAbstract::create($postType);
        if (!is_null($wpAdminUi))
          $wpAdminUi->initialize();
      }
    }

    /**
    * @desc Check the current plugin version
    *
    * @param void
    * @return void
    */
    public function checkPluginVersion()
    {
      // Check plugin version
      $currentVersion = get_option('yog_plugin_version');
      if (empty($currentVersion))
        $currentVersion = '0';

      if ($currentVersion != YOG_PLUGIN_VERSION)
      {
        // Make sure rewrite rules are up-to-date
        $this->registerPostTypes();
        flush_rewrite_rules();

        // Remove unused project images when updated from version 1.2.5 or smaller
        if (version_compare($currentVersion, '1.2.5', '<='))
          $this->removeUnusedProjectImages();

        // Register update open houses cron when not already active
        if (!wp_next_scheduled('yog_cron_open_houses'))
          wp_schedule_event(time(), 'hourly', 'yog_cron_open_houses');

        // Update projects order price when updated from version 1.3.9 or smaller
        if (version_compare($currentVersion, '1.3.9', '<='))
          $this->updateProjectsWithPriceOrder();

        // Update plugin version
        update_option('yog_plugin_version', YOG_PLUGIN_VERSION);
      }
    }

    /**
    * @desc Fix editable permalink slug for NBty/BBty
    *
    * @param string $slug
    * @return string
    */
    public function fixEditableparmalinkSlug($slug)
    {
      if (!empty($GLOBALS['post']))
      {
        $post = $GLOBALS['post'];
      }
      else if (!empty($_POST['post_id']))
      {
        $postId   = (int) $_POST['post_id'];
        $post     = get_post($postId);
      }

      if (isset($post) && in_array($post->post_type, array(POST_TYPE_NBTY, POST_TYPE_BBTY)) && $slug != $post->post_name && (empty($_POST['new_slug']) || $_POST['new_slug'] != $slug))
        $slug = $slug . '/type';

      return $slug;
    }

    /**
    * @desc Init the dashboard widgets
    *
    * @param void
    * @return void
    */
    public function initDashboardWidgets()
    {
      wp_add_dashboard_widget('yog-last-updated-objects', 'Laatst gewijzigde objecten', array($this, 'lastUpdatedProjectsDashboardWidget'));
    }

    public function lastUpdatedProjectsDashboardWidget()
    {
      $objects = get_posts(array( 'numberposts' => 5,
                                  'post_type'   => array(POST_TYPE_WONEN, POST_TYPE_BOG, POST_TYPE_NBPR, POST_TYPE_NBTY),
                                  'orderby'     => 'modified'));

	    // Display whatever it is you want to show
      if (is_array($objects) && count($objects) > 0)
      {
        $thumbnailWidth   = get_option('thumbnail_size_w', 0);
        $noImageHtml      = '<div class="no-image" style="width:' . $thumbnailWidth . 'px;"></div>';

        echo '<table class="wp-list-table widefat fixed posts">';
          echo '<tbody>';

          foreach ($objects as $object)
          {
            $thumbnail = get_the_post_thumbnail($object->ID, 'thumbnail');
            if (empty($thumbnail))
              $thumbnail = $noImageHtml;

            $scenario = yog_retrieveSpec('scenario', $object->ID);

            // Determine admin links
            $links = array();

            if ($object->post_status != 'trash')
              $links[] = '<a href="' . get_edit_post_link($object->ID) . '">' . __('Edit') . '</a>';
            if ($scenario != 'NBbn' && $object->post_status != 'trash')
              $links[] = '<a href="' . get_permalink($object->ID) . '">' . __('View') . '</a>';

            // Determine title
            $title = $object->post_title;
            if ($object->post_status != 'trash')
              $title = '<a href="' . get_edit_post_link($object->ID) . '">' . $title . '</a>';

            echo '<tr>';
            echo '<td style="width:' . ($thumbnailWidth + 10) . 'px;">' . $thumbnail . '</td>';
            echo '<td>';
              echo '<strong>' . $title . '</strong>';
              echo '<div class="row-actions"><span>' . implode(' | </span><span>', $links) . '</span></div>';
            echo '</td>';
            echo '</tr>';
          }

          echo '</tbody>';
        echo '</table>';
      }
      else
      {
        echo '<p>Er zijn nog geen objecten gepubliceerd</p>';
      }
    }

    /**
    * @desc Enqueue files
    *
    * @param void
    * @return void
    */
    public function enqueueFiles()
    {
      parent::enqueueFiles();

      $minifyExtension = (YOG_DEBUG_MODE === true) ? '' : '.min';

      wp_enqueue_script('yog-admin-js',   YOG_PLUGIN_URL .'/inc/js/admin' . $minifyExtension . '.js', array('jquery'), YOG_PLUGIN_VERSION);
      wp_enqueue_style('yog-admin-css',   YOG_PLUGIN_URL . '/inc/css/admin' . $minifyExtension . '.css', array(), YOG_PLUGIN_VERSION);
    }

    /**
    * @desc Create admin menu
    *
    * @param void
    * @return void
    */
    public function createAdminMenu()
    {
      add_object_page('Yes-co ORES', 'Yes-co ORES', 'edit_posts', 'yog_posts_menu', '', YOG_PLUGIN_URL . '/img/icon_yes-co.gif');
      remove_submenu_page('yog_posts_menu', 'edit.php?post_type=' . POST_TYPE_NBTY);
      remove_submenu_page('yog_posts_menu', 'edit.php?post_type=' . POST_TYPE_NBBN);
      remove_submenu_page('yog_posts_menu', 'edit.php?post_type=' . POST_TYPE_BBTY);

      if (get_option('yog_cat_custom'))
        add_submenu_page('yog_posts_menu', __('Categories'), __('Categories'), 'manage_options', 'edit-tags.php?taxonomy=yog_category');

      add_options_page('Yes-co ORES opties', 'Yes-co ORES', 'edit_plugins', 'yesco_OG', array($this, 'renderSettingsPage'));
      add_options_page('Map shortcode generator', 'Map shortcode generator', 'edit_plugins', 'yesco_OG_shortcode_map', array($this, 'renderShortcodeMapPage'));
      add_options_page('Objecten shortcode generator', 'Objecten shortcode generator', 'edit_plugins', 'yesco_OG_shortcode_objects', array($this, 'renderShortcodeObjectsPage'));

      remove_submenu_page('options-general.php', 'yesco_OG_shortcode_map');
      remove_submenu_page('options-general.php', 'yesco_OG_shortcode_objects');
    }

    /**
    * @desc Render plugin settings page
    *
    * @param void
    * @return void
    */
    public function renderSettingsPage()
    {
      require_once(YOG_PLUGIN_DIR . '/includes/classes/yog_system_link_manager.php');
      require_once(YOG_PLUGIN_DIR . '/includes/classes/yog_checks.php');

      // Checks
      $errors 	= YogChecks::checkForErrors();
      $warnings = YogChecks::checkForWarnings();

      if (empty($errors))
      {
        // Retrieve system links
        $systemLinkManager  = new YogSystemLinkManager();
        $systemLinks        = $systemLinkManager->retrieveAll();

        // Sort options
        $sortOptions  = array('date_asc' => 'datum oplopend', '' => 'datum aflopend',
                              'title_asc' => 'titel oplopend', 'title_desc' => 'titel aflopend',
                              'price_asc' => 'prijs oplopend', 'price_desc' => 'prijs aflopend');
        $sortOption   = get_option('yog_order');

        // Media size options
        $mediaSizeOptions = array('medium' => 'Medium (1280x1280)', 'large' => 'Large (1600x1600)');
        $mediaSizeOption  = get_option('yog_media_size', 'medium');
      }

      // Render html
      include(YOG_PLUGIN_DIR . '/includes/pages/settings.php');
    }

    /**
     * Render maps shortcode generator
     */
    public function renderShortcodeMapPage()
    {
      $shortcode = (!empty($_GET['shortcode']) ? $_GET['shortcode'] : '');

      $yogMapWidget = new YogMapWidget();
      $settings     = $yogMapWidget->shortcodeToSettings($shortcode);
      $postTypes    = yog_getAllPostTypes();

      include(YOG_PLUGIN_DIR . '/includes/pages/shortcode_map.php');
    }

    /**
     * Render objects shortcode generator
     */
    public function renderShortcodeObjectsPage()
    {
      $minifyExtension = (YOG_DEBUG_MODE === true) ? '' : '.min';

      wp_enqueue_script('yog-admin-objects-shortcode-js',   YOG_PLUGIN_URL .'/inc/js/admin_objects_shortcode' . $minifyExtension . '.js', array('jquery'), YOG_PLUGIN_VERSION);

      $postTypes      = array(POST_TYPE_WONEN, POST_TYPE_BOG, POST_TYPE_NBPR, POST_TYPE_NBTY, POST_TYPE_BBPR, POST_TYPE_BBTY);
      $sortOptions    = array('date_asc' => 'datum oplopend', '' => 'datum aflopend',
                              'title_asc' => 'titel oplopend', 'title_desc' => 'titel aflopend',
                              'price_asc' => 'prijs oplopend', 'price_desc' => 'prijs aflopend');
      $sortOption     = get_option('yog_order');
      $categories     = get_categories(array('taxonomy' => (get_option('yog_cat_custom') ? 'yog_category' : 'category')));

      $files          = $this->listThemeFiles();
      $templateFiles  = array();

      foreach ($files as $file)
      {
        $file = basename($file);
        if (strpos($file, 'object-') !== false)
          $templateFiles[] = str_replace(array('object-', '.php'), '', $file);
      }

      include(YOG_PLUGIN_DIR . '/includes/pages/shortcode_objects.php');
    }

    /**
     * @desc Method renderRow
     *
     * @param {String} $label
     * @param {String} $value
     * @return {String}
     */
    public function renderRow($label, $value)
    {
      $html = '';

      $html .= '<tr valign="top">';
	      $html .= '<th scope="row">' . $label . '</th>';
        $html .= '<td><div style="margin-bottom: 10px;">' . $value . '</div></td>';
      $html .= '</tr>';

      return $html;
    }

    /**
     * @desc Method section
     *
     * @param {Void}
     * @return {String}
     */
    public function section()
    {
      echo '<p>Stel hier je eigen gewenste plaatjes in voor de markers op de map:</p>';
    }

    /**
     * @desc Method inputFile
     *
     * @param {Array}
     * @return {Void}
     */
    public function inputFile($args)
    {
      $logoOptions = $args[0];
      $postType    = $args[1];
      $optionName  = $args[2];
      $filesKey    = 'marker_type_' . $postType;

      if (!empty($_FILES) && !empty($_FILES[$filesKey]) && !empty($_FILES[$filesKey]['tmp_name']))
      {
        $file = $_FILES[$filesKey];

        $response = wp_handle_upload($_FILES[$filesKey], array('test_form' => false));

        if (!empty($response))
        {
          $imageSize          = getimagesize($response['file']);
          $response['width']  = $imageSize[0];
          $response['height'] = $imageSize[1];

          // Remove old logo
          $options  = get_option($optionName);

          if (!($options === false || empty($options['file'])))
            @unlink($options['file']);

          // Update logo settings
          update_option($optionName, $response);
        }
      }

      $html = '';

      if ($logoOptions === false || empty($logoOptions['url']))
        $logoUrl = YOG_PLUGIN_URL . '/img/svzmaps/marker_type_' . $postType . '.png';
      else
        $logoUrl = $logoOptions['url'];

      $html .= '<div style="margin-bottom:10px;">';
      $html .= '<input style="float: left;" type="file" name="marker_type_' . $postType . '" />';
      $html .= '<img style="margin-left:80px;float: left;" src="' . $logoUrl . '" alt="" /><br />';
      $html .= '</div>';

      echo $html;
    }

    /**
    * @desc Ajax toggle disable link objects to normal wordpress categories
    *
    * @param void
    * @return void
    */
    public function ajaxSetSetting()
    {
      if (!empty($_POST['name']) && in_array($_POST['name'], array('yog_cat_custom', 'yog_objectsinarchief', 'yog_huizenophome', 'yog_javascript_dojo_dont_enqueue',
                                                                    'yog_noextratexts', 'yog_nochilds_searchresults', 'yog_order', 'yog_sync_disabled',
                                                                    'yog_media_size', 'yog_google_maps_api_key')))
      {
        // If not value provided, toggle settings
        if (!isset($_POST['value']))
          $value = !(get_option($_POST['name']));
        // Otherwise use provided value
        else
          $value = $_POST['value'];

        update_option($_POST['name'], $value);

        // Custom stuff for yog_cat_custom
        if ($_POST['name'] == 'yog_cat_custom')
        {
          // Flush rewrite rules
          $this->registerPostTypes();
          flush_rewrite_rules();

          // Clear yog_order if needed
          if ($value === false)
            delete_option('yog_order');
        }

        echo '&nbsp; instelling opgeslagen.';
      }

		  exit();
    }

    /**
    * @desc Add a system link
    *
    * @param void
    * @return void
    */
	  public function addSystemLink()
	  {
		  // geen activatiecode? Geen koppeling toevoegen
		  if (empty($_POST['activatiecode']))
			  exit();

      $systemLink         = new YogSystemLink(YogSystemLink::EMPTY_NAME, 'Nog niet geactiveerd', $_POST['activatiecode'], '-');

      $systemLinkManager  = new YogSystemLinkManager();
      $systemLinkManager->store($systemLink);

		  echo '<div class="system-link" id="yog-system-link-' . $systemLink->getActivationCode() . '">';
        echo '<div>';
          echo '<b>Naam:</b> ' . $systemLink->getName() .'<br />';
          echo '<b>Status:</b> ' . $systemLink->getState() .'<br />';
          echo '<b>Activatiecode:</b> ' . $systemLink->getActivationCode() .' <br />';
          echo '<a onclick="jQuery(this).next().show(); jQuery(this).hide();">Koppeling verwijderen</a>';
          echo '<span class="hide" id="yog-system-link-' . $systemLink->getActivationCode() . '-remove">Wilt u deze koppeling verbreken? <span><a onclick="jQuery(this).parent().hide();jQuery(this).parent().prev().show();">annuleren</a> | <a onclick="yogRemoveSystemLink(\'' . $systemLink->getActivationCode() .'\');">doorgaan</a></span></span>';
        echo '</div>';
		  echo '</div>';
		  exit();
	  }

    /**
    * @desc Remove a system link
    *
    * @param void
    * @return void
    */
	  public function ajaxRemoveSystemLink()
	  {
		  // geen activatiecode? Geen koppeling toevoegen
		  if (empty($_POST['activatiecode']))
			  exit();

      $systemLinkManager  = new YogSystemLinkManager();
      $systemLink         = $systemLinkManager->retrieveByActivationCode($_POST['activatiecode']);

      $systemLinkManager->remove($systemLink);

      echo $_POST['activatiecode'];
		  exit();
	  }

	  /**
	   * Try to remove images of deleted projects
	   *
	   * @param void
	   * @return void
	   */
	  private function removeUnusedProjectImages()
	  {
	  	$uploadDir 			= wp_upload_dir();

	  	// If wp_upload_dir returns errors, skip everything else
	  	if (!empty($uploadDir['error']))
				return;

	  	// Skip everything if projects upload dir does not exist
	  	if (!is_dir($uploadDir['basedir'] . '/projecten/'))
	  		return;

	  	// Skip everything if projects upload dir is not writeable
	  	if (!is_writeable($uploadDir['basedir'] . '/projecten/'))
	  		return;

	  	// Set variables
	  	$activePostIds 			= array();
	  	$projectsUploadDir	= $uploadDir['basedir'] . '/projecten/';

	  	// Retrieve existing YOG posts
	  	$posts = get_posts(array(
	  													'post_type' 			=> array(POST_TYPE_WONEN, POST_TYPE_BOG, POST_TYPE_NBPR, POST_TYPE_NBTY, POST_TYPE_NBBN),
	  													'post_status'			=> 'any',
	  													'posts_per_page'	=> -1
	  												));

	  	// Determine id's of extisting YOG posts
	  	foreach ($posts as $post)
	  	{
	  		$activePostIds[] = (int) $post->ID;
	  	}

	  	// Determine all project folders
	  	$projectFolders = glob($projectsUploadDir . '*');

	  	if (is_array($projectFolders))
	  	{
	  		foreach ($projectFolders as $projectFolder)
	  		{
	  			$postId = (int) basename($projectFolder);
	  			if (!in_array($postId, $activePostIds))
	  			{
	  				@array_map( "unlink", glob($projectFolder . '/*') );
	  				@rmdir($projectFolder);
	  			}
	  		}
	  	}
	  }

	  /**
	   * Set yog_price_order of projects that doesnt have it yet
	   *
	   * @param void
	   * @return void
	   */
	  private function updateProjectsWithPriceOrder()
	  {
	  	// Retrieve existing YOG posts
	  	$posts = get_posts(array(
	  													'post_type' 			=> array(POST_TYPE_WONEN, POST_TYPE_BOG, POST_TYPE_NBPR, POST_TYPE_NBTY, POST_TYPE_BBPR, POST_TYPE_BBTY),
	  													'post_status'			=> 'any',
	  													'posts_per_page'	=> -1
	  												));

	  	// Loop through posts
	  	foreach ($posts as $post)
	  	{
        $postId         = (int) $post->ID;
        $priceOrder     = get_post_meta($postId, 'yog_price_order', true);
        $priceMetaKeys  = null;
        $postType       = $post->post_type;

	  		if (empty($priceOrder) && $priceOrder != '0')
        {
          switch ($postType)
          {
            case POST_TYPE_WONEN:
            case POST_TYPE_BOG:
              $priceMetaKeys = array($postType . '_KoopPrijs', $postType . '_HuurPrijs');
              break;
            case POST_TYPE_NBPR:
              $priceMetaKeys = array($postType . '_KoopAanneemSomMin', $postType . '_HuurPrijsMin', $postType . '_KoopAanneemSomMax', $postType . '_HuurPrijsMax');
              break;
            case POST_TYPE_NBTY:
            case POST_TYPE_BBPR:
            case POST_TYPE_BBTY:
              $priceMetaKeys = array($postType . '_KoopPrijsMin', $postType . '_HuurPrijsMin', $postType . '_KoopPrijsMax', $postType . '_HuurPrijsMax');
              break;
          }

          if (!empty($priceMetaKeys))
          {
            // Determine price based on meta keys
            foreach ($priceMetaKeys as $priceMetaKey)
            {
              $price = get_post_meta($postId, $priceMetaKey, true);
              if (!empty($price))
                break;
            }

            // Set yog_price_order
            update_post_meta($postId, 'yog_price_order', empty($price) ? 0 : $price);
          }
        }
	  	}
	  }
  }