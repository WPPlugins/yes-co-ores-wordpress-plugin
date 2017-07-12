<?php
/**
* @desc YogMapWidget
* @author Stefan van Zanden - Yes-co Nederland
*/
class YogMapWidget extends WP_Widget
{
  const NAME                = 'Yes-co Map';
  const DESCRIPTION         = 'Map van je eigen objecten / vestiging.';
  const CLASSNAME           = 'yog-map';
  const WIDGET_ID_PREFIX    = 'yogmapwidget-';

  /**
  * @desc Constructor
  *
  * @param void
  * @return YogRecentObjectsWidget
  */
  public function __construct()
  {
    $options = array( 'classsname'  => self::CLASSNAME,
                      'description' => self::DESCRIPTION);

    parent::__construct(false, $name = self::NAME, $options);
  }

  /**
  * @desc Display widget
  *
  * @param array $args
  * @param array $instance
  * @return void
  */
  public function widget($args, $instance)
  {
		// Enqueue style
		wp_enqueue_style('yog-map-css', YOG_PLUGIN_URL . '/inc/css/svzmaps.css', array(), YOG_PLUGIN_VERSION);

		// Render map
    $shortcode      = empty($instance['shortcode']) ? '' : $instance['shortcode'];

    echo do_shortcode($shortcode);
  }

  /**
   * @desc Method shortcodeToSettings
   *
   * @param {String} $shortcode
   * @return {}
   */
  public function shortcodeToSettings($shortcode)
  {
     $shortcode = str_replace(array('[yog-map ', ']', '\"', '"'), '', $shortcode);
     $shortcode = str_replace('"', '', $shortcode);
     $atts      = shortcode_parse_atts( $shortcode );

     $settings  = $this->shortcodeAttributesToSettings($atts);

     return $settings;
  }

  /**
   * @desc Method shortcodeAttributesToSettings
   *
   * @param {Array} $atts
   * @return {
   */
  public function shortcodeAttributesToSettings($atts)
  {
    $settings               = array();

    $width                  = 100;
    $widthUnit              = 'px';
    $height                 = 100;
    $heightUnit             = 'px';

    $latitude               = '52.02';
    $longitude              = '5.5496';
    $mapType                = 'hybrid';
    $zoomLevel              = 2;
    $controlZoomPosition    = 'top_left';
    $controlPanPosition     = 'top_left';
    $controlMapTypePosition = 'top_right';
    $postTypes              = yog_getAllPostTypes();

    // PostTypes
    if (!empty($atts['post_types']))
      $postTypes = explode(',', $atts['post_types']);

    // Width
    if (!empty($atts['width']))
      $width = (int)$atts['width'];

    // WidthUnit
    if (!empty($atts['width_unit']))
      $widthUnit = $atts['width_unit'];

    // Height
    if (!empty($atts['height']))
      $height = (int)$atts['height'];

    // HeightUnit
    if (!empty($atts['height_unit']))
      $heightUnit = $atts['height_unit'];

    // MapType
    if (!empty($atts['map_type']))
      $mapType = $atts['map_type'];

    // Zoomlevel
    if (!empty($atts['zoomlevel']))
      $zoomLevel = (int)$atts['zoomlevel'];

    // Latitude
    if (isset($atts['center_latitude']) && strlen(trim($atts['center_latitude'])) > 0)
      $latitude = $atts['center_latitude'];

    // Longitude
    if (isset($atts['center_longitude']) && strlen(trim($atts['center_longitude'])) > 0)
      $longitude = $atts['center_longitude'];

    // ControlZoom
    if (isset($atts['control_zoom_position']) && strlen(trim($atts['control_zoom_position'])) > 0)
    {
      $settings['control_zoom'] = array( 'position' => $atts['control_zoom_position'] );
    }

    // ControlPan
    if (isset($atts['control_pan_position']) && strlen(trim($atts['control_pan_position'])) > 0)
    {
      $settings['control_pan'] = array( 'position' => $atts['control_pan_position'] );
    }

    // ControlMapType
    if (isset($atts['control_map_type_position']) && strlen(trim($atts['control_map_type_position'])) > 0)
    {
      $settings['control_map_type'] = array( 'position' => $atts['control_map_type_position'] );
    }

    // Disable scroll wheel
    if (isset($atts['disable_scroll_wheel']) && $atts['disable_scroll_wheel'] == 'true')
      $settings['disable_scroll_wheel'] = true;

    $settings['postTypes']  = $postTypes;
    $settings['width']      = $width;
    $settings['widthUnit']  = $widthUnit;
    $settings['height']     = $height;
    $settings['heightUnit'] = $heightUnit;
    $settings['latitude']   = $latitude;
    $settings['longitude']  = $longitude;
    $settings['mapType']    = $mapType;
    $settings['zoomLevel']  = $zoomLevel;

    return $settings;
  }

  /**
   * @desc Method generateDetailWindow
   *
   * @param {Integer} $postID
   * @return {Array}
   */
  public function generateDetailWindow($postID)
  {
    //YogPlugin::enqueueDojo();
    //YogPlugin::loadDojo();

    $post     = get_post($postID);

    // Add post to the globals so it can be used in the template
    $GLOBALS['post'] = $post;

    $postType = $post->post_type;

    $html     = '';

    $html .= '<div class="post-' . $postType . '">';

    $customThemeTemplateName = 'single-map-detail-window-' . $postType . '.php';

		if ( $overridden_template = locate_template(  'single-map-detail-window-all.php' ) )
		{
      // Load the template but capture it's output
        ob_start();

       // locate_template() returns path to file
       // if either the child theme or the parent theme have overridden the template
       load_template( $overridden_template );

       $html .= ob_get_contents();

       ob_end_clean();
		}
    else if ( $overridden_template = locate_template( $customThemeTemplateName ) )
    {
      // Load the template but capture it's output
        ob_start();

       // locate_template() returns path to file
       // if either the child theme or the parent theme have overridden the template
       load_template( $overridden_template );

       $html .= ob_get_contents();

       ob_end_clean();

    }
    else // Generate something generic
    {
      switch ($postType)
      {
        case POST_TYPE_WONEN:
        case POST_TYPE_BOG:
        case POST_TYPE_NBBN:
        case POST_TYPE_NBPR:
        case POST_TYPE_NBTY:

          $images     = yog_retrieveImages('thumbnail', 3, $postID);
          $title      = yog_retrieveSpec('Naam', $postID);
          $city       = yog_retrieveSpec('Plaats', $postID);
          $prices     = yog_retrievePrices('small', 'small', $postID);
          $status     = '';

          $permaLink  = get_permalink($postID);

          switch ($postType)
          {
            case POST_TYPE_WONEN:

              $status   = yog_retrieveSpec('Status', $postID);

              if (empty($status) || $status == 'beschikbaar')
                $status = yog_getOpenHouse('Open huis', $postID);

            break;

            case POST_TYPE_BOG:

              $status   = yog_retrieveSpec('Status', $postID);

            break;
          }

          // Determine state html
          $stateHtml = '';

          if (!empty($status) && $status != 'beschikbaar')
            $stateHtml = '<span class="post-object-state">' . $status . '</span>';

          // Images
          if (!empty($images))
          {
            $html .= '<a href="' . $permaLink . '" rel="bookmark" title="' . $title . '" class="main-image"><img src="' . $images[0][0] . '" width="' . $images[0][1] . '" height="' . $images[0][2] . '" alt="' . $title . '" />' . $stateHtml . '</a>';

            $html .= '<div class="extra-images">';

            if (!empty($images[1]))
              $html .= '<div class="extra-image"><a href="' . $permaLink . '"><img alt="' . $title . '" src="' . $images[1][0] . '" width="50" /></a></div>';

            if (!empty($images[2]))
              $html .= '<div class="extra-image"><a href="' . $permaLink . '"><img alt="' . $title . '" src="' . $images[2][0] . '" width="50" /></a></div>';

            $html .= '</div>';
          }

          $html .= '<div class="specs_object">';

          $html .= '<h2><a href="' . $permaLink . '">' . esc_attr($title) . '</a></h2>';

          if (!empty($city))
            $html .= '<h3 class="caps">' . $city. '</h3>';

          $html .= '<p>' . implode('<br />', $prices) . '</p>';

          $html .= '</div>';

        break;

        case POST_TYPE_RELATION:

          $html       = '';
          $title      = get_the_title($postID);
          $emailAdres = yog_retrieveSpec('Emailadres', $postID);
          $website    = yog_retrieveSpec('Website', $postID);

          $permaLink  = get_permalink($postID);

          $html       .= '<a href="' . $permaLink . '">' . $title . '</a><br />';

          $html       .= 'Email: ' . $emailAdres . '<br />';
          $html       .= 'Website: ' . $website . '<br />';

        break;
      }
    }

    $html .= '</div>';

    // Including of the SVZ Solutions library
    require_once(YOG_PLUGIN_DIR . '/includes/svzsolutions/generic/InfoWindowContent.php');

    $infoWindow = new SVZ_Solutions_Generic_Info_Window_Content();

    $infoWindow->addClassName('type-' . strtolower($postType));

    $output = array();

    if (empty($html))
    {
      $output['content'] = 'Could not find the project data.';
    }
    else
    {
      $infoWindow->setContent($html);

      $output['content'] = $infoWindow->getHTML();
    }

    return json_encode($output);
  }

  /**
   * @desc Method generate
   *
   * @param {Array} $settings
   * @param {String} $extraAfterOnLoad
   * @return {String}
   */
  public function generate($settings = array(), $extraAfterOnLoad = '', $adminMode = false)
  {
    //YogPlugin::enqueueDojo();
    YogPlugin::loadDojo();

    $postTypes  = $settings['postTypes'];
    $width      = $settings['width'];
    $widthUnit  = $settings['widthUnit'];
    $height     = $settings['height'];
    $heightUnit = $settings['heightUnit'];
    $mapType    = $settings['mapType'];
    $zoomLevel  = $settings['zoomLevel'];
    $latitude   = $settings['latitude'];
    $longitude  = $settings['longitude'];

    // Including of the SVZ Solutions library
    require_once(YOG_PLUGIN_DIR . '/includes/svzsolutions/maps/Map.php');

    // Create a new instance of Google Maps version 3
    $map                          = SVZ_Solutions_Maps_Map::getInstance(SVZ_Solutions_Maps_Map::MAP_TYPE_GOOGLE_MAPS, '3');
    $map->setWidth($width);
    $map->setWidthUnit($widthUnit);
    $map->setHeight($height);
    $map->setHeightUnit($heightUnit);

    // Sets the id of the container (HTMLDomElement) the map must be put on.
    $map->setContainerId('yesco-og-dynamic-map');

    // Sets the default map type to satellite
    $map->setMapType($mapType);

    // Sets the zoom level to start with to 18.
    $map->setZoomLevel($zoomLevel);

    // Sets the geocode the map should start at centered.
    $map->setCenterGeocode(new SVZ_Solutions_Generic_Geocode((float)$latitude, (float)$longitude));

    if ($adminMode)
    {
      // Add a single admin marker
      $marker     = new SVZ_Solutions_Generic_Marker('admin', (float)$latitude, (float)$longitude);
      $marker->setDraggable(true);

      $map->addMarker($marker);
    }

    if (!$adminMode)
    {
      // Make sure after the first load that the data of the markers is being cached
      $map->setLoadDataOnce(true);

      // Set the url to load all the markers from
      $dataLoadUrl = admin_url('admin-ajax.php') . '?action=loadmapdata&post_types=' . implode(',', $postTypes);
      $map->setDataLoadUrl($dataLoadUrl);
    }

    // ControlZoom
    if (!empty($settings['control_zoom']))
    {
      // @TODO: Implement enabled and style
      if (!empty($settings['control_zoom']['position']))
      {
        $map->setControlZoom(true, $settings['control_zoom']['position']);
      }
    }

    // ControlPan
    if (!empty($settings['control_pan']))
    {
      // @TODO: Implement enabled and style
      if (!empty($settings['control_pan']['position']))
      {
        $map->setControlPan(true, $settings['control_pan']['position']);
      }
    }

    // ControlMapType
    if (!empty($settings['control_map_type']))
    {
      // @TODO: Implement enabled and style
      if (!empty($settings['control_map_type']['position']))
      {
        $map->setControlMapType(true, $settings['control_map_type']['position']);
      }
    }

    // Disable scroll wheel
    if (isset($settings['disable_scroll_wheel']) && $settings['disable_scroll_wheel'] === true)
      $map->disableScrollwheel();

    $onLoad = '
                // Hide the static version
                var staticMapHolder = dojo.byId("yesco-og-static-map-holder");

                if (staticMapHolder)
                  dojo.style(staticMapHolder, "display", "none");

                // Show the dynamic version
                var dynamicMap = dojo.byId("yesco-og-dynamic-map");

                if (dynamicMap)
                  dojo.style(dynamicMap, "display", "block");

                ';

    return yog_generateMap($map, $onLoad, $extraAfterOnLoad, $adminMode);
  }

  /**
  * @desc Update widget settings
  *
  * @param array $new_instance
  * @param array $old_instance
  * @return array
  */
  public function update($new_instance, $old_instance)
  {
    $instance                     = $old_instance;

    $instance['shortcode']        = empty($new_instance['shortcode']) ? '' : $new_instance['shortcode'];

    return $instance;
  }

  /**
  * @desc Display widget form
  *
  * @param array $instance
  * @return void
  */
  public function form($instance)
  {
    // Don't escape it with extra quotes for the quick edit url
    $shortcode      = empty($instance['shortcode']) ? '' : $instance['shortcode'];

    echo '<p>';
      echo '<label for="' . $this->get_field_id('shortcode') . '">' . __('Shortcode') . ': </label>';
      echo '<input class="widefat" id="' . $this->get_field_id('shortcode') . '" name="' . $this->get_field_name('shortcode') . '" type="text" value="' . esc_attr($shortcode) . '" />';
    echo '</p>';

    echo '<p>Gebruik de <a href="' . get_admin_url() . 'options-general.php?page=yesco_OG&shortcode=' . esc_attr(urlencode($shortcode)) . '">shortcode generator</a> op de Instellingen pagina om snel een shortcode te genereren.</p>';
  }
}