<?php

  /**
   * Copyright (c) 2009, SVZ Solutions All Rights Reserved.
   * Available via BSD license, see license file included for details.
   *
   * @title:        SVZ Solutions Generic Marker Type file
   * @authors:      Stefan van Zanden <info@svzsolutions.nl>
   * @company:      SVZ Solutions
   * @contributers:
   * @version:      0.1
   * @versionDate:  2009-10-17
   * @date:         2009-10-17
   */

  require_once('MarkerLayer.php');
  require_once('MarkerImage.php');

  /**
   * SVZ_Solutions_Generic_Marker_Type class
   *
   */
  class SVZ_Solutions_Generic_Marker_Type
  {
    const MARKER_TYPE_CLUSTER     			= 'cluster';
    const MARKER_TYPE_LIST        			= 'list';
    private $name                 			= '';
    private $layer                			= null;
    private $className            			= '';
    private $overlayCorrectionX   			= 0;
    private $overlayCorrectionY   			= 0;
    private $autoCenter           			= false;
    private $autoCenterX          			= true;
    private $autoCenterY          			= false;
    private $clickAction          			= '';
    private $iconEnabled          			= false;
    private $icon                 			= null;
    private $iconShadow           			= null;
    private $color                			= '';
    private $size                 			= '';
    private $iconSize                       = false;
    private $enableDataLoadOnMouseOver 	= false;

    /**
     * Constructor
     *
     * @param string $name
     * @return void
     */
    public function __construct($name)
    {
      $this->setName($name);

      $this->layer = new SVZ_Solutions_Generic_Marker_Layer('default');

      $this->className = 'sg-marker sg-marker-' . strtolower($name);

      // Set some default settings
      if ($name == self::MARKER_TYPE_CLUSTER || $name == self::MARKER_TYPE_LIST)
      {
        $this->setAutoCenter(true);
        $this->setAutoCenterY(false);
        $this->setAutoCenterX(false);
      }

    }

    /**
     * Set the overlay correction x and y coordinate in pixels
     *
     * @param integer $x
     * @param integer $y
     * @return void
     */
    public function setOverlayCorrection($overlayCorrectionX, $overlayCorrectionY)
    {
      if (!is_int($overlayCorrectionX))
        throw new Exception(__METHOD__ . '; Invalid $overlayCorrectionX, not a integer.');

      if (!is_int($overlayCorrectionY))
        throw new Exception(__METHOD__ . '; Invalid $overlayCorrectionY, not a integer.');

      $this->overlayCorrectionX = $overlayCorrectionX;
      $this->overlayCorrectionY = $overlayCorrectionY;
    }

    /**
     * Get the overlay correction x coordinate in pixels
     *
     * @param void
     * @return integer
     */
    public function getOverlayCorrectionX()
    {
      return $this->overlayCorrectionX;
    }

    /**
     * Get the overlay correction y coordinate in pixels
     *
     * @param void
     * @return integer
     */
    public function getOverlayCorrectionY()
    {
      return $this->overlayCorrectionY;
    }

    /**
     * Method enableDataLoadOnMouseOver which will enable the data load when the marker is hovered
     * next to being clicked
     *
     * @param void
     * @return void
     */
    public function enableDataLoadOnMouseOver()
    {
    	$this->enableDataLoadOnMouseOver = true;
    }

    /**
     * Method getEnableDataLoadOnMouseOver
     *
     * @param void
     * @return void
     */
    public function getEnableDataLoadOnMouseOver()
    {
    	return $this->enableDataLoadOnMouseOver;
    }

    /**
     * Set if the overlay should be automaticly centered verticaly on top of the geocoordinate
     *
     * @param boolean $autoCenterY
     * @return void
     */
    public function setAutoCenterY($autoCenterY)
    {
      if (!is_bool($autoCenterY))
        throw new Exception(__METHOD__ . '; Invalid $autoCenterY, not a boolean.');

      $this->autoCenterY = $autoCenterY;
    }

    /**
     * Get if the overlay should be automaticly centered vertically on top of the geocoordinate
     *
     * @param void
     * @return boolean
     */
    public function getAutoCenterY()
    {
      return $this->autoCenterY;
    }

    /**
     * Set if the overlay should be automaticly centered horizontally on top of the geocoordinate
     *
     * @param boolean $autoCenterX
     * @return void
     */
    public function setAutoCenterX($autoCenterX)
    {
      if (!is_bool($autoCenterX))
        throw new Exception(__METHOD__ . '; Invalid $autoCenterX, not a boolean.');

      $this->autoCenterX = $autoCenterX;
    }

    /**
     * Get if the overlay should be automaticly centered horizontally on top of the geocoordinate
     *
     * @param void
     * @return boolean
     */
    public function getAutoCenterX()
    {
      return $this->autoCenterX;
    }

    /**
     * Set if the overlay should be automaticly centered on top of the geocoordinate
     *
     * @param boolean $autoCenter
     * @return void
     */
    public function setAutoCenter($autoCenter)
    {
      if (!is_bool($autoCenter))
        throw new Exception(__METHOD__ . '; Invalid $autoCenter, not a boolean.');

      $this->autoCenter = $autoCenter;
    }

    /**
     * Get if the overlay should be automaticly centered on top of the geocoordinate
     *
     * @param void
     * @return boolean
     */
    public function getAutoCenter()
    {
      return $this->autoCenter;
    }

    /**
     * Set the name to identify the marker type with
     *
     * @param string $name
     * @return void
     */
    public function setName($name)
    {
      if (!is_string($name))
        throw new Exception(__METHOD__ . '; Invalid $name, not a string.');

      $this->name = $name;
    }

    /**
     * Get the name to identify the marker type with
     *
     * @param void
     * @return string
     */
    public function getName()
    {
      return $this->name;
    }

    /**
     * Get the layer
     *
     * @param void
     * @return SVZ_Solutions_Generic_Marker_Layer
     */
    public function getLayer()
    {
      return $this->layer;
    }

    /**
     * Method that adds a class name to the element of the marker
     *
     * @param string $className
     * @return void
     */
    public function addClassName($className)
    {
      if (!is_string($className) || empty($className))
        throw new Exception(__METHOD__ . '; Invalid $className, not a string or empty');

      $this->className .= ' ' . $className;
    }

    /**
     * Method that returns the class name to the surrounding element of the info window
     *
     * @param void
     * @return string
     */
    public function getClassName()
    {
      return $this->className;
    }

    /**
     * Method which enables usage of an icon
     *
     * @param void
     * @return void
     */
    public function enableIcon()
    {
      $this->iconEnabled = true;
    }

    /**
     * Method which returns if an icon is enabled
     *
     * @param void
     * @return void
     */
    public function isIconEnabled()
    {
      return $this->iconEnabled;
    }

    /**
     * Set the icon
     *
     * @param SVZ_Solutions_Generic_Marker_Image $icon
     * @return void
     */
    public function setIcon(SVZ_Solutions_Generic_Marker_Image $icon)
    {
      $this->enableIcon();
      $this->icon = $icon;
    }

    /**
     * Check if icon is set
     *
     * @param void
     * @return boolean
     */
    public function hasIcon()
    {
      return !is_null($this->icon);
    }

    /**
     * Get the icon
     *
     * @param void
     * @return string
     */
    public function getIcon()
    {
      if (!$this->hasIcon())
        $this->setIcon(new SVZ_Solutions_Generic_Marker_Image());

      return $this->icon;
    }

    /**
     * Set the icon shadow
     *
     * @param SVZ_Solutions_Generic_Marker_Image $iconShadow
     * @return void
     */
    public function setIconShadow(SVZ_Solutions_Generic_Marker_Image $iconShadow)
    {
      $this->iconShadow = $iconShadow;
    }

    /**
     * Check if icon shadow is set
     *
     * @param void
     * @return boolean
     */
    public function hasIconShadow()
    {
      return !is_null($this->iconShadow);
    }

    /**
     * Get the icon shadow
     *
     * @param void
     * @return SVZ_Solutions_Generic_Marker_Image
     */
    public function getIconShadow()
    {
      if (!$this->hasIconShadow())
        $this->setIconShadow(new SVZ_Solutions_Generic_Marker_Image());

      return $this->iconShadow;
    }

    /**
     * Method hasIconSize which checks if the icon size is defined
     *
     * @param {Void}
     * @return {Boolean}
     */
    public function hasIconSize()
    {
        return ($this->iconSize !== false);
    }

    /**
     * Method getIconSize which returns the icon size defintions
     *
     * @param {Void}
     * @return {Array}
     */
    public function getIconSize()
    {
        return $this->iconSize;
    }

    /**
     * Method setIconSize this can be used in the calculations (auto detect is more performance heavy)
     *
     * @param {Integer} $width
     * @param {Integer} $height
     * @return {Void}
     */
    public function setIconSize($width, $height)
    {
        if (!is_integer($width))
            throw new Exception(__METHOD__ . '; Invalid width, not an integer');

        if (!is_integer($height))
            throw new Exception(__METHOD__ . '; Invalid height, not an integer');

        $this->iconSize = array('width' => $width, 'height' => $height);
    }

    /**
     * Set the icon click action to execute when clicked
     *
     * @param string $clickAction
     * @return void
     */
    public function setClickAction($clickAction)
    {
      if (!is_string($clickAction))
        throw new Exception(__METHOD__ . '; Invalid $clickAction, not a string.');

      $this->clickAction = $clickAction;
    }

    /**
     * Get the icon click action to execute when clicked
     *
     * @param void
     * @return string
     */
    public function getClickAction()
    {
      return $this->clickAction;
    }

    /**
     * Set the color of the marker
     *
     * @param string $color
     * @return void
     */
    public function setColor($color)
    {
      if (!is_string($color))
        throw new Exception(__METHOD__ . '; Invalid $color, not a string.');

      $this->color = $color;
    }

    /**
     * Check if this marker has a color
     *
     * @param void
     * @return boolean
     */
    public function hasColor()
    {
      return ($this->color != '') ? true : false;
    }

    /**
     * Get the color of the marker
     *
     * @param void
     * @return string
     */
    public function getColor()
    {
      return $this->color;
    }

    /**
     * Set the size of the marker
     *
     * @param string $size
     * @return void
     */
    public function setSize($size)
    {
      if (!is_string($size))
        throw new Exception(__METHOD__ . '; Invalid $size, not a string.');

      $this->size = $size;
    }

    /**
     * Check if this marker has a size
     *
     * @param void
     * @return boolean
     */
    public function hasSize()
    {
      return ($this->size != '') ? true : false;
    }

    /**
     * Get the size of the marker
     *
     * @param void
     * @return string
     */
    public function getSize()
    {
      return $this->size;
    }
  }

?>