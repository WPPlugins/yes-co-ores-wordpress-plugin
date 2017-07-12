<?php

  /**
   * SVZ_Solutions_Maps_Google_Maps_MapAbstract class
   *
   */
  abstract class SVZ_Solutions_Maps_MapAbstract
  {
    const APP_VERSION                             = '0.7.2';
    private $containerId                          = '';
    private $id																		= '';
    private $centerGeocode                        = null;
    private $zoomLevel                            = 8;
    private $width                                = 0;
    private $widthUnit                            = 'px';
    private $height                               = 0;
    private $heightUnit                           = 'px';

    /**
     * Method thats sets the center geocode starting position
     *
     * @param SVZ_Solutions_Generic_Geocode $centerGeocode
     * @return void
     */
    final public function setCenterGeocode(SVZ_Solutions_Generic_Geocode $centerGeocode)
    {
      $this->centerGeocode = $centerGeocode;
    }

    /**
     * Method thats sets the center geocode starting position
     *
     * @param void
     * @exception
     * @return SVZ_Solutions_Generic_Geocode
     */
    final public function getCenterGeocode()
    {
      if (!$this->centerGeocode instanceof SVZ_Solutions_Generic_Geocode)
        throw new Exception(__METHOD__ . '; Invalid $centerGeocode, not off class SVZ_Solutions_Generic_Geocode');

      return $this->centerGeocode;
    }

    /**
     * Method thats sets the zoom level the map should start at
     *
     * @param int $zoomLevel
     * @return void
     */
    public function setZoomLevel($zoomLevel)
    {
      if (!is_int($zoomLevel))
        throw new Exception(__METHOD__ . '; Invalid $zoomLevel, not a integer.');

      if ($zoomLevel > 22)
        throw new Exception(__METHOD__ . '; Invalid $zoomLevel, max is 22');

      $this->zoomLevel = $zoomLevel;
    }

    /**
     * Method thats gets the zoom level the map should start at
     *
     * @param void
     * @return int
     */
    public function getZoomLevel()
    {
      return $this->zoomLevel;
    }

    /**
     * Method thats sets the width of the image
     *
     * @param integer $width
     * @return void
     */
    final public function setWidth($width)
    {
      if (!is_int($width) || empty($width))
        throw new Exception(__METHOD__ . '; Invalid $width, not a integer or empty.');

      if ($this->getMaxWidth() && $width > $this->getMaxWidth())
        throw new Exception(__METHOD__ . '; Invalid $width, exceeds max width of ' . $this->getMaxWidth() . '.');

      $this->width = $width;
    }

    /**
     * Method thats sets the width of the unit
     *
     * @param {String} $widthUnit
     * @return void
     */
    final public function setWidthUnit($widthUnit = 'px')
    {
        if (!in_array($widthUnit, array( 'px', '%', 'vw' )))
            throw new Exception(__METHOD__ . '; Invalid $widthUnit, not px / % or vw.');

        $this->widthUnit = $widthUnit;
    }

    /**
     * Method thats sets the width of the image
     *
     * @param void
     * @return integer
     */
    final public function getWidth()
    {
      if (empty($this->width))
        throw new Exception(__METHOD__ . '; Invalid $width, empty.');

      return $this->width;
    }

    /**
     * Method thats gets the width unit
     *
     * @param void
     * @return integer
     */
    final public function getWidthUnit()
    {
        if (empty($this->widthUnit))
            throw new Exception(__METHOD__ . '; Invalid $widthUnit, empty.');

        return $this->widthUnit;
    }

    /**
     * Method thats sets the height of the image
     *
     * @param integer $width
     * @return void
     */
    final public function setHeight($height)
    {
      if (!is_int($height) || empty($height))
        throw new Exception(__METHOD__ . '; Invalid $height, not a integer or empty.');

      if ($this->getMaxHeight() && $height > $this->getMaxHeight())
        throw new Exception(__METHOD__ . '; Invalid $height, exceeds max height of ' . $this->getMaxHeight() . '.');

      $this->height = $height;
    }

    /**
     * Method thats sets the height of the unit
     *
     * @param {String} $heightUnit
     * @return void
     */
    final public function setHeightUnit($heightUnit = 'px')
    {
        if (!in_array($heightUnit, array( 'px', '%', 'vh' )))
            throw new Exception(__METHOD__ . '; Invalid $heightUnit, not px / % or vh.');

        $this->heightUnit = $heightUnit;
    }

    /**
     * Method thats sets the height of the image
     *
     * @param void
     * @return integer
     */
    final public function getHeight()
    {
      if (empty($this->height))
        throw new Exception(__METHOD__ . '; Invalid $height, empty.');

      return $this->height;
    }

    /**
     * Method thats gets the height unit
     *
     * @param void
     * @return integer
     */
    final public function getHeightUnit()
    {
        if (empty($this->heightUnit))
            throw new Exception(__METHOD__ . '; Invalid $heightUnit, empty.');

        return $this->heightUnit;
    }

    /**
     * Method thats sets the id which can be used to identify the map in extensions
     *
     * @param string $id
     * @return void
     */
    final public function setId($id)
    {
      if (!is_string($id) || empty($id))
        throw new Exception(__METHOD__ . '; Invalid $id, not a string or empty.');

      $this->id = $id;
    }

    /**
     * Method thats gets the id which can be used to identift the map in extensions
     *
     * @param void
     * @return string
     */
    final public function getId()
    {
      return $this->id;
    }

    /**
     * Method thats sets the container id of where the map must be located
     *
     * @param string $containerId
     * @return void
     */
    final public function setContainerId($containerId)
    {
      if (!is_string($containerId) || empty($containerId))
        throw new Exception(__METHOD__ . '; Invalid $containerId, not a string or empty.');

      $this->containerId = $containerId;
    }

    /**
     * Method thats gets the container id of where the map must be located
     *
     * @param void
     * @return string
     */
    final public function getContainerId()
    {
      if (empty($this->containerId))
        throw new Exception(__METHOD__ . '; Invalid $containerId, empty.');

      return $this->containerId;
    }

    /**
     * Method thats returns the maps main configuration
     *
     * @param void
     * @return string
     */
    final public function getMainConfig()
    {
      $config               = new StdClass();
      $config->appVersion   = self::APP_VERSION;
      $config->width        = $this->getWidth();
      $config->height       = $this->getHeight();

      return $config;
    }

    abstract function getConfig();
    abstract function getStaticUrl();
    abstract function getMaxWidth();
    abstract function getMaxHeight();
  }

?>