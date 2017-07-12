<?php

  /**
   * Copyright (c) 2010, SVZ Solutions All Rights Reserved.
   * Available via BSD license, see license file included for details.
   *
   * @title:        SVZ Solutions Generic Marker Image file
   * @authors:      Stefan van Zanden <info@svzsolutions.nl>
   * @company:      SVZ Solutions
   * @contributers:
   * @version:      0.4
   * @versionDate:  2010-03-04
   * @date:         2010-03-04
   */

  /**
   * SVZ_Solutions_Generic_Marker_Type class
   *
   * @TODO: Implement anchor / scaledsize / origin functionality
   *
   */
  class SVZ_Solutions_Generic_Marker_Image
  {
    private $url                  = '';
    private $size                 = array('width' => 0, 'height' => 0);
    private $anchor               = '';
    private $scaledSize           = null;
    private $origin               = '';

    /**
     * Constructor
     *
     * @param void
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Set the url
     *
     * @param string $url
     * @return void
     */
    public function setUrl($url)
    {
      if (!is_string($url))
        throw new Exception(__METHOD__ . '; Invalid $url, not a string.');

      $this->url = $url;
    }

    /**
     * Get the url
     *
     * @param void
     * @return string
     */
    public function getUrl()
    {
      return $this->url;
    }

    /**
     * Set the size of the image
     *
     * @param string $size
     * @return void
     */
    public function setSize($width, $height)
    {
      if (!is_int($width) || empty($width))
        throw new Exception(__METHOD__ . '; Invalid $width, not a integer or empty.');

      if (!is_int($height) || empty($height))
        throw new Exception(__METHOD__ . '; Invalid $height, not a integer or empty.');

      $this->size = array('width' => $width, 'height' => $height);
    }

    /**
     * Check if this marker has a size
     *
     * @param void
     * @return boolean
     */
    public function hasSize()
    {
      return (is_null($this->size)) ? false : true;
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

    /**
     * Method thats generates a config object for this image
     *
     * @param void
     * @return StdClass
     */
    public function getConfig()
    {
      $config = new StdClass();
      $config->url = $this->getUrl();

      if ($this->hasSize())
        $config->size = $this->getSize();

      return $config;
    }
  }

?>