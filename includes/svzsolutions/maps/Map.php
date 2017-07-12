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

  require_once(dirname(__FILE__) . '/../generic/Address.php');
  require_once(dirname(__FILE__) . '/../generic/Geocode.php');
  require_once(dirname(__FILE__) . '/../generic/MarkerType.php');
  require_once(dirname(__FILE__) . '/../generic/MarkerManager.php');

  require_once('MapAbstract.php');

  /**
   * SVZ_Solutions_Google_Maps_Map main class
   *
   */
  class SVZ_Solutions_Maps_Map
  {
    const APP_VERSION                             = '0.7.2';
    const MAP_TYPE_GOOGLE_MAPS                    = 'googlemaps';
    const MAP_TYPE_GOOGLE_MAPS_CLASS              = 'Google_Maps_Map';
    private static $instances                     = array();
    private $supportedMaps                        = array();

    /**
     * Constructor
     *
     * @param string map type
     * @return void
     */
    private function __construct($mapType, $mapVersion, $mapMode, $instanceName)
    {
      $this->supportedMaps[self::MAP_TYPE_GOOGLE_MAPS] = array('class' => self::MAP_TYPE_GOOGLE_MAPS_CLASS);

      if (!array_key_exists($mapType, $this->supportedMaps) || empty($mapType))
        throw new Exception(__METHOD__ . '; Invalid map type, empty or not defined in list.');

      if (empty($mapVersion) || !is_string($mapVersion))
        throw new Exception(__METHOD__ . '; Invalid map version, empty or not a string.');

      require_once(dirname(__FILE__) . '/' . $mapType . '/Map.php');

      $className = 'SVZ_Solutions_Maps_' . $this->supportedMaps[self::MAP_TYPE_GOOGLE_MAPS]['class'];

      self::$instances[$instanceName] = new $className($mapVersion, $mapMode);
    }

    /**
     * Constructor get map instance
     *
     * @param string map type
     * @param string map version
     * @return void
     */
    public static function getInstance($mapType = '', $mapVersion = '', $mapMode = 'dynamic', $instanceName = 'default')
    {
      if (!is_string($instanceName) || empty($instanceName))
        throw new Exception(__METHOD__ . '; Invalid $instanceName, not a string or empty.');

      if (empty(self::$instanceName))
        new self($mapType, $mapVersion, $mapMode, $instanceName);

      return self::$instances[$instanceName];
    }
  }