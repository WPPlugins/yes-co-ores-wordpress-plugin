<?php

  /**
   * Copyright (c) 2009, SVZ Solutions All Rights Reserved.
   * Available via BSD license, see license file included for details.
   *
   * @title:        SVZ Solutions Google Maps Map File
   * @authors:      Stefan van Zanden <info@svzsolutions.nl>
   * @company:      SVZ Solutions
   * @contributers:
   * @version:      0.1
   * @versionDate:  2009-10-17
   * @date:         2009-10-17
   */

  /**
   * SVZ_Solutions_Google_Maps_Map main class
   * Desc: Based upon Google Maps API V3
   *
   */
  class SVZ_Solutions_Maps_Google_Maps_Map extends SVZ_Solutions_Maps_MapAbstract
  {
    const MODES                                       = 'static;dynamic';
    const DEFAULT_MAP_TYPE                                                = 'hybrid';
    const CONTROL_MAP_TYPE_TYPES                  = 'roadmap;satellite;hybrid;terrain';
    const CONTROL_MAP_TYPE_STYLES            = 'default;horizontal_bar;dropdown_menu';
    const CONTROL_SCALE_STYLES                        = 'default';
    const CONTROL_ZOOM_STYLES                         = 'default;small;large';
    const CONTROL_POSITIONS                           = 'bottom_left;left_bottom;bottom_center;bottom_right;right_bottom;left_center;top_left;left_top;top_center;top_right;right_top;right_center';

    const CONTROL_DEFAULT_MAP_TYPE_ENABLED                = true;
    const CONTROL_DEFAULT_MAP_TYPE_POSITION                = 'top_left';
    const CONTROL_DEFAULT_MAP_TYPE_STYLE                    = 'default';
    const CONTROL_DEFAULT_MAP_TYPE_TYPES                    = 'roadmap;satellite;hybrid;terrain';

    const CONTROL_DEFAULT_PAN_ENABLED                            = true;
    const CONTROL_DEFAULT_PAN_POSITION                        = 'top_left';

    const CONTROL_DEFAULT_SCALE_ENABLED                        = false;
    const CONTROL_DEFAULT_SCALE_POSITION                    = 'bottom_left';
    const CONTROL_DEFAULT_SCALE_STYLE                            = 'default';

    const CONTROL_DEFAULT_STREET_VIEW_ENABLED            = true;
    const CONTROL_DEFAULT_STREET_VIEW_POSITION        = 'right_bottom';

    const CONTROL_DEFAULT_ZOOM_ENABLED                        = true;
    const CONTROL_DEFAULT_ZOOM_POSITION                        = 'right_bottom';
    const CONTROL_DEFAULT_ZOOM_STYLE                            = 'default';

    const AVAILABLE_ZOOM_LEVELS                                        = '0;1;2;3;4;5;6;7;8;9;10;11;12;13;14;15;16;17;18;19;20';

    const CACHING_DEFAULT_ENABLED                                                = true;
    const CACHING_DEFAULT_MINUTES_UNTIL_OUTDATED_CHECK     = 60;

    private $controls                                                            = array();
    private $caching                                                            = array();

    private $libraryConfig                        = array();
    private $loadDataOnce                         = false;
    private $markerType                           = array();
    private $dataLoadUrl                          = '';
    private $mapType                              = '';
    private $markerManager                        = null;
    private $scale                                = false;
    private $mode                                 = 'dynamic';
    private $scrollwheel                          = true;
    private $dragOnTouch                          = true;

    /**
     * Constructor
     *
     * @param string $version
     * @return void
     */
    public function __construct($version, $mode = 'dynamic')
    {
      $this->libraryConfig      = array('name' => SVZ_Solutions_Maps_Map::MAP_TYPE_GOOGLE_MAPS, 'version' => $version, 'mode' => $mode);
      $this->mode               = $mode;
      $this->centerGeocode      = new SVZ_Solutions_Generic_Geocode(50.5, 5);
      $this->markerManager      = new SVZ_Solutions_Generic_Marker_Manager();

      // Call the methods to set the defaults
      $this->setControlMapType();
      $this->setControlPan();
      $this->setControlScale();
      $this->setControlStreetView();
      $this->setControlZoom();
      $this->setMapType();
      $this->setCaching();
    }

    /**
     * Method disableScrollwheel
     *
     * @param {Void}
     * @return {Void}
     */
    public function disableScrollwheel()
    {
        $this->scrollwheel = false;
    }

    /**
     * Method disableDragOnTouch
     *
     * @param {Void}
     * @return {Void}
     */
    public function disableDragOnTouch()
    {
        $this->dragOnTouch = false;
    }

    /**
     * Method getAvailableZoomLevels which returns the available zoom levels
     *
     * @param void
     * @return array
     */
    public static function getAvailableZoomLevels()
    {
        return explode(';', self::AVAILABLE_ZOOM_LEVELS);
    }

    /**
     * Method setCaching
     *
     * @param boolean $enabled
     * @param integer $minutesUntilOutdatedCheck
     * @return void
     */
    public function setCaching($enabled = self::CACHING_DEFAULT_ENABLED, $minutesUntilOutdatedCheck = self::CACHING_DEFAULT_MINUTES_UNTIL_OUTDATED_CHECK)
    {
        if (!is_bool($enabled))
            throw new Exception(__METHOD__ . '; Invalid $enabled, not a bool.');

        if (!is_int($minutesUntilOutdatedCheck))
            throw new Exception(__METHOD__ . '; Invalid $minutesUntilOutdatedCheck, not a integer.');

        $this->caching = array(
            'enabled'                                        => $enabled,
            'minutesUntilOutdatedCheck' => $minutesUntilOutdatedCheck
        );
    }

    /**
     * Method that returns the supported max width
     *
     * @param void
     * @return integer
     */
    public function getMaxWidth()
    {
      if ($this->mode == 'static')
        return 640;

      return false;
    }

    /**
     * Method that returns the supported max height
     *
     * @param void
     * @return integer
     */
    public function getMaxHeight()
    {
      if ($this->mode == 'static')
        return 640;

      return false;
    }

    /**
     * Method thats sets if the map should load his marker / polygon etc.. data only one time
     *
     * @param boolean $loadDataOnce
     * @return void
     */
    public function setLoadDataOnce($loadDataOnce)
    {
      if (!is_bool($loadDataOnce))
        throw new Exception(__METHOD__ . '; Invalid $loadDataOnce, not a bool.');

      $this->loadDataOnce = $loadDataOnce;
    }

    /**
     * Method thats sets which map type is shown on initial load
     *
     * @param string $mapType
     * @return void
     */
    public function setMapType($mapType = self::DEFAULT_MAP_TYPE)
    {
        $this->isValidOption($mapType, self::CONTROL_MAP_TYPE_TYPES);

      $this->mapType = $mapType;
    }

    /**
     * Method thats sets the scale of which to size the static image
     *
     * @param Integer $scale
     * @return void
     */
    public function setScale($scale)
    {
        $this->scale = $scale;
    }

    /**
     * Method isValidOption which checks if the option provided is valid
     *
     * @param string $value
     * @param string $optionString
     * @throw Exceptions
     * @return boolean
     */
    private function isValidOption($value, $optionString)
    {
        if (!is_string($value))
        throw new Exception(__METHOD__ . '; Invalid $value, not a string.');

      if (!is_string($optionString))
        throw new Exception(__METHOD__ . '; Invalid $optionString, not a string.');

        $options = explode(';', $optionString);

        if (!in_array($value, $options))
            throw new Exception(__METHOD__ . '; Invalid $value, provided [' . (string)$value . '] is not one of ' . implode(' / ', $options) . '.');

        return true;
    }

    /**
     * Method setControlMapType which will configure the map type control
     *
     * @param boolean $enabled
     * @param string $position
     * @param string $style
     * @param array $types
     */
    public function setControlMapType($enabled = self::CONTROL_DEFAULT_MAP_TYPE_ENABLED, $position = self::CONTROL_DEFAULT_MAP_TYPE_POSITION, $style = self::CONTROL_DEFAULT_MAP_TYPE_STYLE, $types = null)
    {
        if ($types == null)
            $types = explode(';', self::CONTROL_DEFAULT_MAP_TYPE_TYPES);

      if (!is_array($types))
        throw new Exception(__METHOD__ . '; Invalid $types, not an array.');

      foreach ($types as $type)
      {
          $this->isValidOption($type, self::CONTROL_MAP_TYPE_TYPES);
      }

      $this->isValidOption($position, self::CONTROL_POSITIONS);
      $this->isValidOption($style, self::CONTROL_MAP_TYPE_STYLES);

      if (!isset($this->controls['mapType']))
          $this->controls['mapType'] = array();

      $this->controls['mapType'] = array(
          'enabled'     => $enabled,
          'position'     => $position,
          'style'         => $style,
          'types'         => $types
      );
    }

      /**
     * Method setControlPan which will configure the pan control
     *
     * @param boolean $enabled
     * @param string $position
     */
    public function setControlPan($enabled = self::CONTROL_DEFAULT_PAN_ENABLED, $position = self::CONTROL_DEFAULT_PAN_POSITION)
    {
        $this->isValidOption($position, self::CONTROL_POSITIONS);

      if (!isset($this->controls['pan']))
          $this->controls['pan'] = array();

      $this->controls['pan'] = array(
          'enabled'     => $enabled,
          'position'     => $position
      );
    }

       /**
     * Method setControlScale which will configure the scale control
     *
     * @param boolean $enabled
     * @param string $position
     * @param string $style
     */
    public function setControlScale($enabled = self::CONTROL_DEFAULT_SCALE_ENABLED, $position = self::CONTROL_DEFAULT_SCALE_POSITION, $style = self::CONTROL_DEFAULT_SCALE_STYLE)
    {
        $this->isValidOption($position, self::CONTROL_POSITIONS);
        $this->isValidOption($style, self::CONTROL_SCALE_STYLES);

      if (!isset($this->controls['scale']))
          $this->controls['scale'] = array();

      $this->controls['scale'] = array(
          'enabled'     => $enabled,
          'position'     => $position,
          'style'         => $style
      );
    }

      /**
     * Method setControlStreetView which will configure the streetview control
     *
     * @param boolean $enabled
     * @param string $position
     */
    public function setControlStreetView($enabled = self::CONTROL_DEFAULT_STREET_VIEW_ENABLED, $position = self::CONTROL_DEFAULT_STREET_VIEW_POSITION)
    {
        // Streetview default is an empty string
        if ($position !== '')
            $this->isValidOption($position, self::CONTROL_POSITIONS);

      if (!isset($this->controls['streetView']))
          $this->controls['streetView'] = array();

      $this->controls['streetView'] = array(
          'enabled'     => $enabled,
          'position'     => $position
      );
    }

   /**
     * Method setControlZoom which will configure the zoom control
     *
     * @param boolean $enabled
     * @param string $position
     * @param string $style
     */
    public function setControlZoom($enabled = self::CONTROL_DEFAULT_ZOOM_ENABLED, $position = self::CONTROL_DEFAULT_ZOOM_POSITION, $style = self::CONTROL_DEFAULT_ZOOM_STYLE)
    {
        $this->isValidOption($position, self::CONTROL_POSITIONS);
        $this->isValidOption($style, self::CONTROL_ZOOM_STYLES);

      if (!isset($this->controls['zoom']))
          $this->controls['zoom'] = array();

      $this->controls['zoom'] = array(
          'enabled'     => $enabled,
          'position'     => $position,
          'style'         => $style
      );
    }

    /**
     * Method thats returns the available map control types
     *
     * @param void
     * @return array
     */
    public function getTypes()
    {
      return explode(';', self::CONTROL_MAP_TYPE_TYPES);
    }

    /**
     * Method thats returns the available map type control styles
     *
     * @param void
     * @return array
     */
    public function getControlTypeStyles()
    {
      return explode(';', self::CONTROL_MAP_TYPE_STYLES);
    }

    /**
     * Method thats returns the available map navigation control styles
     *
     * @param void
     * @return array
     */
    public function getControlZoomStyles()
    {
      return explode(';', self::CONTROL_ZOOM_STYLES);
    }

    /**
     * Method thats returns the available map scale control styles
     *
     * @param void
     * @return array
     */
    public function getControlScaleStyles()
    {
      return explode(';', self::CONTROL_SCALE_STYLES);
    }

    /**
     * Method thats returns the available map navigation and type control positions
     *
     * @param void
     * @return array
     */
    public function getControlPositions()
    {
      return explode(';', self::CONTROL_POSITIONS);
    }

    /**
     * Method thats where the change of a map should load some markers from
     *
     * @param string $markerLoadUrl
     * @return void
     */
    public function setDataLoadUrl($dataLoadUrl)
    {
      if (!is_string($dataLoadUrl))
        throw new Exception(__METHOD__ . '; Invalid $dataLoadUrl, not a string.');

      $this->dataLoadUrl = $dataLoadUrl;
    }

    /**
     * Method thats adds a marker type to the map
     *
     * @param SVZ_Solutions_Generic_Marker_Type $markerType
     * @return void
     */
    public function addMarkerType(SVZ_Solutions_Generic_Marker_Type $markerType)
    {
      $this->markerTypes[] = $markerType;
    }

    /**
     * Method thats adds a marker to the map
     *
     * @param SVZ_Solutions_Generic_Marker $markerType
     * @return void
     */
    public function addMarker(SVZ_Solutions_Generic_Marker $marker)
    {
      $this->markerManager->addMarker($marker);
    }

    /**
     * Method thats adds a layer to the map
     *
     * @param string $layer
     * @return void
     */
    public function addLayer($layer)
    {
      if (empty($layer) && !is_string($layer))
        throw new Exception(__METHOD__ . '; Invalid $layer, not a string or empty.');

      $this->layers[] = $layer;
    }

    /**
     * Method thats generates a config object with the configuration
     *
     * @param void
     * @return StdClass
     */
    public function getConfig()
    {
      $config                           = $this->getMainConfig();
      $config->apiKey                   = (defined('SVZ_GOOGLE_MAPS_API_KEY') ? SVZ_GOOGLE_MAPS_API_KEY : false);
      $config->libraryConfig            = $this->libraryConfig;
      $config->mapId                    = $this->getId();
      $config->mapContainerId           = $this->getContainerId();
      $config->mapType                  = $this->mapType;
      $config->loadDataOnce             = $this->loadDataOnce;
      $config->zoomLevel                = $this->getZoomLevel();
      $config->centerGeoLat             = $this->getCenterGeocode()->getLatitude();
      $config->centerGeoLng             = $this->getCenterGeocode()->getLongitude();

      $config->controls                                    = array();

      // Caching
      if ($this->caching['enabled'])
      {
          $config->caching                                = array(
              'minutesUntilOutdatedCheck' => $this->caching['minutesUntilOutdatedCheck']
          );
      }

      // Controls
      if ($this->controls['mapType']['enabled'])
          $config->controls['mapType'] = array(
              'mapTypeIds'     => $this->controls['mapType']['types'],
              'style'             => 'google.maps.MapTypeControlStyle.' . strtoupper($this->controls['mapType']['style']),
              'position'         => 'google.maps.ControlPosition.' . strtoupper($this->controls['mapType']['position'])
          );

      if ($this->controls['zoom']['enabled'])
          $config->controls['zoom'] = array(
              'style'             => 'google.maps.ZoomControlStyle.' . strtoupper($this->controls['zoom']['style']),
              'position'         => 'google.maps.ControlPosition.' . strtoupper($this->controls['zoom']['position'])
          );

      if ($this->controls['pan']['enabled'])
          $config->controls['pan'] = array(
              'position'         => 'google.maps.ControlPosition.' . strtoupper($this->controls['pan']['position'])
          );

      if ($this->controls['scale']['enabled'])
          $config->controls['scale'] = array(
              'style'             => 'google.maps.ScaleControlStyle.' . strtoupper($this->controls['scale']['style']),
              'position'         => 'google.maps.ControlPosition.' . strtoupper($this->controls['scale']['position'])
          );

      if ($this->controls['streetView']['enabled'])
          $config->controls['streetView'] = array(
              'position'         => ($this->controls['streetView']['position'] !== '') ? 'google.maps.ControlPosition.' . strtoupper($this->controls['streetView']['position']) : ''
          );

      if ($this->markerManager->hasMarkers())
        $config->markers                = $this->markerManager->toArray();

      if (!empty($this->dataLoadUrl))
        $config->dataLoadUrl            = $this->dataLoadUrl;

      if (!empty($this->layers))
        $config->layers                 = $this->layers;

      $config->scrollwheel              = $this->scrollwheel;
      $config->dragOnTouch              = $this->dragOnTouch;

      if (!empty($this->markerTypes))
      {
        $config->markerTypes            = array();

        foreach ($this->markerTypes as $markerType)
        {
          $markerTypeConfig               = new StdClass();
          $markerTypeConfig->className    = $markerType->getClassName();
          $markerTypeConfig->iconEnabled  = $markerType->isIconEnabled();

          if ($markerType->isIconEnabled())
          {
            if ($markerType->hasIcon())
              $markerTypeConfig->icon         = $markerType->getIcon()->getConfig();

            if ($markerType->hasIconShadow())
              $markerTypeConfig->shadow       = $markerType->getIconShadow()->getConfig();

          }

          if ($markerType->hasIconSize())
              $markerTypeConfig->iconSize = $markerType->getIconSize();

          $markerTypeConfig->correctionX                              = $markerType->getOverlayCorrectionX();
          $markerTypeConfig->correctionY                              = $markerType->getOverlayCorrectionY();
          $markerTypeConfig->autoCenter                               = $markerType->getAutoCenter();
          $markerTypeConfig->autoCenterY                              = $markerType->getAutoCenterY();
          $markerTypeConfig->autoCenterX                              = $markerType->getAutoCenterX();
          $markerTypeConfig->clickAction                              = $markerType->getClickAction();
          $markerTypeConfig->layerName                                = $markerType->getLayer()->getName();
          $markerTypeConfig->layerType                                = $markerType->getLayer()->getType();
          $markerTypeConfig->enableDataLoadOnMouseOver                = $markerType->getEnableDataLoadOnMouseOver();

          $config->markerTypes[$markerType->getName()] = $markerTypeConfig;
        }
      }

      return $config;
    }

    /**
     * Method thats generates a url with the specified configuration
     *
     * @param void
     * @return string
     */
    public function getStaticUrl()
    {
      $url = 'http://maps.google.com/maps/api/staticmap?';

      $url .= 'center=' . $this->getCenterGeocode()->getLatitude() . ',' . $this->getCenterGeocode()->getLongitude();

      $url .= '&zoom=' . $this->getZoomLevel();

      $url .= '&size=' . $this->getWidth() . 'x' . $this->getHeight();

      $url .= '&maptype=' . $this->mapType;

      if (!empty($this->scale))
          $url .= '&scale=' . $this->scale;

      $url .= '&sensor=false';

      if ($this->markerManager->hasMarkers())
      {
        $markerStack = $this->markerManager->getMarkers();

        foreach ($markerStack as $marker)
        {
          $url .= '&markers=';

          $settings = array();

          if ($marker->hasType())
          {
            if ($marker->getType()->hasIcon())
              $settings[] = 'icon:' . $marker->getType()->getIcon()->getUrl();

            if ($marker->getType()->hasColor())
              $settings[] = 'color:' . $marker->getType()->getColor();

            if ($marker->getType()->hasSize())
              $settings[] = 'size:' . $marker->getType()->getSize();
          }

          if ($marker->hasLabel())
            $settings[] = '|label:' . $marker->getLabel();

          $settings[] = $marker->getGeocode()->getLatitude() . ',' . $marker->getGeocode()->getLongitude();

          $url .= implode('|', $settings);
        }
      }

      return $url;
    }

  }

?>