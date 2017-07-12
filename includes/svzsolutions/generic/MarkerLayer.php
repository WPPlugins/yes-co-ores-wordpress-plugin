<?php

  /**
   * Copyright (c) 2010, SVZ Solutions All Rights Reserved.
   * Available via BSD license, see license file included for details.
   *
   * @title:        SVZ Solutions Generic Marker Layer file
   * @authors:      Stefan van Zanden <info@svzsolutions.nl>
   * @company:      SVZ Solutions
   * @contributers:
   * @version:      0.4
   * @versionDate:  2010-03-21
   * @date:         2010-03-21
   */

  /**
   * SVZ_Solutions_Generic_Marker_Layer class
   *
   */
  class SVZ_Solutions_Generic_Marker_Layer
  {
    private $name             = 'default';
    private $type             = 'dynamic';

    /**
     * Constructor
     *
     * @param string $name
     * @return void
     */
    public function __construct($name)
    {
      $this->setName($name);
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
     * Set if the layer should be static (meaning any markers living withing this layer won't get removed on loading of new data and shown on all zoom levels)
     *
     * @param void
     * @return void
     */
    public function setTypeStatic()
    {
      $this->type = 'static';
    }

    /**
     * Set if the layer should be fixed (meaning any markers living withing this layer won't get removed on loading of new data but only shown on this specific zoom level)
     *
     * @param void
     * @return void
     */
    public function setTypeFixed()
    {
      $this->type = 'fixed';
    }

    /**
     * Get the type of this layer
     *
     * @param void
     * @return string
     */
    public function getType()
    {
      return $this->type;
    }
  }

?>