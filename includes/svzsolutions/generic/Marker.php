<?php

  /**
   * Copyright (c) 2009, SVZ Solutions All Rights Reserved.
   * Available via BSD license, see license file included for details.
   *
   * @title:        SVZ Solutions Generic Marker file
   * @authors:      Stefan van Zanden <info@svzsolutions.nl>
   * @company:      SVZ Solutions
   * @contributers:
   * @version:      0.1
   * @versionDate:  2009-10-17
   * @date:         2009-10-17
   */

  require_once('Address.php');

  /**
   * SVZ_Solutions_Generic_Marker class
   *
   */
  class SVZ_Solutions_Generic_Marker
  {
    private $typeName           = '';
    private $label              = '';
    private $type               = null;
    private $draggable          = false;
    private $showInfoWindow     = false;
    private $address            = null;
    private $title              = '';
    private $dataLoadUrl        = '';
    private $geocode            = null;
    private $content            = '';
    private $entityId           = '';

    /**
     * Constructor
     *
     * @param void
     * @return void
     */
    public function __construct($typeName, $latitude = null, $longitude = null)
    {
      $this->setTypeName($typeName);
      $this->geocode            = new SVZ_Solutions_Generic_Geocode($latitude, $longitude);
    }

    /**
     * Set the type of the marker
     *
     * @param SVZ_Solutions_Generic_Marker_Type $type
     * @return void
     */
    public function setType(SVZ_Solutions_Generic_Marker_Type $type)
    {
      $this->type = $type;
    }

    /**
     * Check if there is a type defined
     *
     * @param void
     * @return boolean
     */
    public function hasType()
    {
      return !is_null($this->type) && $this->type instanceof SVZ_Solutions_Generic_Marker_Type;
    }

    /**
     * Get the type of the marker
     *
     * @param void
     * @return SVZ_Solutions_Generic_Marker_Type
     */
    public function getType()
    {
      return $this->type;
    }

    /**
     * Set the name to identify the marker type with
     *
     * @param string $typeName
     * @return void
     */
    public function setTypeName($typeName)
    {
      if (!is_string($typeName))
        throw new Exception(__METHOD__ . '; Invalid $typeName, not a string.');

      $this->typeName = $typeName;
    }

    /**
     * Get the name to identify the marker type with
     *
     * @param void
     * @return string
     */
    public function getTypeName()
    {
      return $this->typeName;
    }

    /**
     * Set the marker draggable
     *
     * @param boolean $draggable
     * @return void
     */
    public function setDraggable($draggable)
    {
      if (!is_bool($draggable))
        throw new Exception(__METHOD__ . '; Invalid $draggable, not a boolean.');

      $this->draggable = $draggable;
    }

    /**
     * Check if the marker is draggable
     *
     * @param void
     * @return boolean
     */
    public function isDraggable()
    {
      return $this->draggable;
    }

  	/**
     * Set show the info window when the map is loaded
     *
     * @param boolean $showInfoWindow
     * @return void
     */
    public function setShowInfoWindow($showInfoWindow)
    {
      if (!is_bool($showInfoWindow))
        throw new Exception(__METHOD__ . '; Invalid $showInfoWindow, not a boolean.');

      $this->showInfoWindow = $showInfoWindow;
    }

    /**
     * Check if the marker is show info window
     *
     * @param void
     * @return boolean
     */
    public function hasShowInfoWindow()
    {
        return $this->showInfoWindow;
    }

    /**
     * Set the label of the marker
     *
     * @param string $label
     * @return void
     */
    public function setLabel($label)
    {
      if (!is_string($label))
        throw new Exception(__METHOD__ . '; Invalid $label, not a string.');

      $this->label = $label;
    }

    /**
     * Check if this marker has a label
     *
     * @param void
     * @return boolean
     */
    public function hasLabel()
    {
      return ($this->label != '') ? true : false;
    }

    /**
     * Get the label of the marker
     *
     * @param void
     * @return string
     */
    public function getLabel()
    {
      return $this->label;
    }

    /**
     * Set the title of the marker
     *
     * @param string $label
     * @return void
     */
    public function setTitle($title)
    {
      if (!is_string($title))
        throw new Exception(__METHOD__ . '; Invalid $title, not a string.');

      $this->title = $title;
    }

    /**
     * Has the title of the marker
     *
     * @param void
     * @return string
     */
    public function hasTitle()
    {
        return (!empty($this->title) ? true : false);
    }

    /**
     * Get the title of the marker
     *
     * @param void
     * @return string
     */
    public function getTitle()
    {
      return $this->title;
    }

    /**
     * Set the id of the entity of the marker
     *
     * @param integer $entityId
     * @return void
     */
    public function setEntityId($entityId)
    {
      if (!is_int($entityId))
        throw new Exception(__METHOD__ . '; Invalid $entityId, not a integer.');

      $this->entityId = $entityId;
    }

    /**
     * Check if this marker has an entityId
     *
     * @param void
     * @return integer
     */
    public function hasEntityId()
    {
        return (!empty($this->entityId) ? true : false);
    }

    /**
     * Get the id of the entity of the marker
     *
     * @param void
     * @return integer
     */
    public function getEntityId()
    {
      return $this->entityId;
    }

    /**
     * Set the url to load the content with
     *
     * @param string $dataLoadUrl
     * @return void
     */
    public function setDataLoadUrl($dataLoadUrl)
    {
      if (!is_string($dataLoadUrl))
        throw new Exception(__METHOD__ . '; Invalid $dataLoadUrl, not a string.');

      $this->dataLoadUrl = $dataLoadUrl;
    }

    /**
     * Get the url to load the content with
     *
     * @param void
     * @return string
     */
    public function getDataLoadUrl()
    {
      return $this->dataLoadUrl;
    }

    /**
     * Check if the marker has a dataLoadUrl
     *
     * @param void
     * @return boolean
     */
    public function hasDataLoadUrl()
    {
      return !empty($this->dataLoadUrl) ? true : false;
    }

    /**
     * Set the content
     *
     * @param string $content
     * @return void
     */
    public function setContent($content)
    {
      if (!is_string($content))
        throw new Exception(__METHOD__ . '; Invalid $content, not a string.');

      $this->content = $content;
    }

    /**
     * Get the content
     *
     * @param void
     * @return string
     */
    public function getContent()
    {
      return $this->content;
    }

    /**
     * Check if the marker has some default content
     *
     * @param void
     * @return boolean
     */
    public function hasContent()
    {
      return !empty($this->content) ? true : false;
    }

    /**
     * Set the geocode position of the marker
     *
     * @param SVZ_Solutions_Generic_Geocode $geocode
     * @return void
     */
    public function setGeocode(SVZ_Solutions_Generic_Geocode $geocode)
    {
      $this->geocode = $geocode;
    }

    /**
     * Get the geocode position of the marker
     *
     * @param void
     * @return SVZ_Solutions_Generic_Geocode $geocode
     */
    public function getGeocode()
    {
      return $this->geocode;
    }

    /**
     * Has a geocode position of the marker
     *
     * @param void
     * @return SVZ_Solutions_Generic_Geocode $geocode
     */
    public function hasGeocode()
    {
      return ($this->geocode instanceof SVZ_Solutions_Generic_Geocode) ? true : false;
    }

    /**
     * Set the address of the marker
     *
     * @param SVZ_Solutions_Generic_Address $address
     * @return void
     */
    public function setAddress(SVZ_Solutions_Generic_Address $address)
    {
      $this->address = $address;
    }

    /**
     * Get the address of the marker
     *
     * @param void
     * @return SVZ_Solutions_Generic_Address
     */
    public function getAddress()
    {
      return $this->address;
    }

    /**
     * Get the address of the marker
     *
     * @param void
     * @return SVZ_Solutions_Generic_Address
     */
    public function hasAddress()
    {
      return ($this->address instanceof SVZ_Solutions_Generic_Address) ? true : false;
    }

    /**
     * Method which returns this marker as an array fro JSON responses usage
     *
     * @param void
     * @return array
     */
    public function toArray()
    {
      $array                      = array();
      $array['type']              = $this->getTypeName();

      if ($this->hasTitle())
          $array['title']             = $this->getTitle();

      $array['geoLat']            = $this->getGeocode()->getLatitude();
      $array['geoLng']            = $this->getGeocode()->getLongitude();

      if ($this->hasDataLoadUrl())
          $array['dataLoadUrl']       = $this->getDataLoadUrl();

      if ($this->isDraggable())
          $array['draggable']         = $this->isDraggable();

      if ($this->hasShowInfoWindow())
          $array['showInfoWindow']    = $this->showInfoWindow;

      if ($this->hasContent())
          $array['content']           = $this->getContent();

      if ($this->hasEntityId())
          $array['entityId']          = $this->getEntityId();

      return $array;
    }
  }

?>