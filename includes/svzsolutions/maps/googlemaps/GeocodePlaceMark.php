<?php

  /**
   * Copyright (c) 2010, SVZ Solutions All Rights Reserved.
   * Available via BSD license, see license file included for details.
   *
   * @title:        SVZ Solutions Google Maps Geocode Place Mark file
   * @authors:      Stefan van Zanden <info@svzsolutions.nl>
   * @company:      SVZ Solutions
   * @contributers:
   * @version:      0.4
   * @versionDate:  2010-03-06
   * @date:         2010-03-06
   */

  /**
   * SVZ_Solutions_Maps_Google_Maps_Geocode_Place_Mark class
   *
   */
  class SVZ_Solutions_Maps_Google_Maps_Geocode_Place_Mark
  {
    private $address           = null;
    private $geocode           = null;
    private $accuracy          = 0;

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
     * Set the accuracy
     *
     * @param integer $accuracy
     * @return void
     */
    public function setAccuracy($accuracy)
    {
      if (!is_integer($accuracy))
        throw new Exception(__METHOD__ . '; Invalid accuracy, not a integer');

      $this->accuracy = $accuracy;
    }

    /**
     * Get the accuracy
     *
     * @param void
     * @return integer
     */
    public function getAccuracy()
    {
      return $this->accuracy;
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

  }

?>