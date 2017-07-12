<?php

  /**
   * Copyright (c) 2010, SVZ Solutions All Rights Reserved.
   * Available via BSD license, see license file included for details.
   *
   * @title:        SVZ Solutions Generic Bounds file
   * @authors:      Stefan van Zanden <info@svzsolutions.nl>
   * @company:      SVZ Solutions
   * @contributers:
   * @version:      0.5
   * @versionDate:  2010-05-29
   * @date:         2010-05-29
   */

  /**
   * SVZ_Solutions_Generic_Bounds class
   *
   */
  class SVZ_Solutions_Generic_Bounds
  {
    private $geocodeSouthWest = null;
    private $geocodeNorthEast = null;
    private $geocodeSouthEast = null;
    private $geocodeNorthWest = null;
    private $geocodeCenter    = null;

    /**
     * Constructor
     *
     * @param void
     * @return void
     */
    public function __construct($geocodeSouthWest = null, $geocodeNorthEast = null, $geocodeSouthEast = null, $geocodeNorthWest = null, $geocodeCenter = null)
    {
      if (!is_null($geocodeSouthWest))
        $this->setGeocodeSouthWest($geocodeSouthWest);

      if (!is_null($geocodeNorthEast))
        $this->setGeocodeNorthEast($geocodeNorthEast);

      if (!is_null($geocodeSouthEast))
        $this->setGeocodeSouthEast($geocodeSouthEast);

      if (!is_null($geocodeNorthWest))
        $this->setGeocodeNorthWest($geocodeNorthWest);

      if (!is_null($geocodeCenter))
        $this->setGeocodeCenter($geocodeCenter);

    }

    /**
     * Method which returns the south west geocode
     *
     * @param void
     * @return SVZ_Solutions_Generic_Geocode
     */
    public function getGeocodeSouthWest()
    {
      return $this->geocodeSouthWest;
    }

    /**
     * Method which sets the south west geocode
     *
     * @param void
     * @return SVZ_Solutions_Generic_Geocode
     */
    public function setGeocodeSouthWest(SVZ_Solutions_Generic_Geocode $geocodeSouthWest)
    {
      $this->geocodeSouthWest = $geocodeSouthWest;
    }

    /**
     * Method which returns the north east geocode
     *
     * @param void
     * @return SVZ_Solutions_Generic_Geocode
     */
    public function getGeocodeNorthEast()
    {
      return $this->geocodeNorthEast;
    }

    /**
     * Method which sets the north east geocode
     *
     * @param SVZ_Solutions_Generic_Geocode $geocodeNorthEast
     * @return void
     */
    public function setGeocodeNorthEast(SVZ_Solutions_Generic_Geocode $geocodeNorthEast)
    {
      $this->geocodeNorthEast = $geocodeNorthEast;
    }

    /**
     * Method which returns the south east geocode
     *
     * @param void
     * @return SVZ_Solutions_Generic_Geocode
     */
    public function getGeocodeSouthEast()
    {
      // Calculate the north west point based on other points if it is not set
      if (is_null($this->geocodeSouthEast))
      {
        if (is_null($this->geocodeSouthWest) || is_null($this->geocodeNorthEast))
          throw new Exception(__METHOD__ . '; cannot calculate NorthWest geocode, missing SouthWest and or NorthEast geocodes');

        $this->geocodeSouthEast = new SVZ_Solutions_Generic_Geocode($this->geocodeSouthWest->getLatitude(), $this->geocodeNorthEast->getLongitude());
      }


      return $this->geocodeSouthEast;
    }

    /**
     * Method which sets the south east geocode
     *
     * @param SVZ_Solutions_Generic_Geocode $geocodeSouthEast
     * @return void
     */
    public function setGeocodeSouthEast(SVZ_Solutions_Generic_Geocode $geocodeSouthEast)
    {
      $this->geocodeSouthEast = $geocodeSouthEast;
    }

    /**
     * Method which returns the south east geocode
     *
     * @param void
     * @return SVZ_Solutions_Generic_Geocode
     */
    public function getGeocodeNorthWest()
    {
      // Calculate the north west point based on other points if it is not set
      if (is_null($this->geocodeNorthWest))
      {
        if (is_null($this->geocodeSouthWest) || is_null($this->geocodeNorthEast))
          throw new Exception(__METHOD__ . '; cannot calculate NorthWest geocode, missing SouthWest and or NorthEast geocodes');

        $this->geocodeNorthWest = new SVZ_Solutions_Generic_Geocode($this->geocodeNorthEast->getLatitude(), $this->geocodeSouthWest->getLongitude());
      }

      return $this->geocodeNorthWest;
    }

    /**
     * Method which sets the north west geocode
     *
     * @param SVZ_Solutions_Generic_Geocode $geocodeNorthWest
     * @return void
     */
    public function setGeocodeNorthWest(SVZ_Solutions_Generic_Geocode $geocodeNorthWest)
    {
      $this->geocodeNorthWest = $geocodeNorthWest;
    }

    /**
     * Method which sets the center geocode
     *
     * @param SVZ_Solutions_Generic_Geocode $geocodeCenter
     * @return void
     */
    public function setGeocodeCenter(SVZ_Solutions_Generic_Geocode $geocodeCenter)
    {
      $this->geocodeCenter = $geocodeCenter;
    }

    /**
     * Method which returns the center geocode
     *
     * @param void
     * @return SVZ_Solutions_Generic_Geocode
     */
    public function getGeocodeCenter()
    {
      if (is_null($this->geocodeCenter))
      {
        $geocodeCenter    = new SVZ_Solutions_Generic_Geocode();

        // @TODO: Find a way to calculate the proper center geocode when overlapping like australia to amerika happens
        // Apparantly we are crossing some lines between the southwest point and the north east point
        if ($this->getGeocodeSouthWest()->getLongitude() > $this->getGeocodeNorthEast()->getLongitude())
        {
          $centerLongitude  = (($this->getGeocodeNorthEast()->getLongitude() - $this->getGeocodeNorthWest()->getLongitude()) / 2) + $this->getGeocodeNorthWest()->getLongitude();
          $centerLatitude   = (($this->getGeocodeNorthEast()->getLatitude() - $this->getGeocodeSouthWest()->getLatitude()) / 2) + $this->getGeocodeSouthWest()->getLatitude();
        }
        else
        {
          $centerLongitude  = (($this->getGeocodeNorthEast()->getLongitude() - $this->getGeocodeSouthWest()->getLongitude()) / 2) + $this->getGeocodeSouthWest()->getLongitude();
          $centerLatitude   = (($this->getGeocodeNorthEast()->getLatitude() - $this->getGeocodeSouthWest()->getLatitude()) / 2) + $this->getGeocodeSouthWest()->getLatitude();
        }

        $geocodeCenter->setLongitude($centerLongitude);
        $geocodeCenter->setLatitude($centerLatitude);

        $this->geocodeCenter = $geocodeCenter;
      }

/*      echo $centerLatitude;

      echo '<br />Center<br />';
      echo $this->getGeocodeNorthEast()->getLongitude() . '<br />';
      echo $this->getGeocodeSouthEast()->getLongitude() . '<br />';
      echo $this->getGeocodeNorthWest()->getLatitude() . '<br />';
      echo $this->getGeocodeNorthEast()->getLatitude() . '<br />';
*/
      return $this->geocodeCenter;
    }

    /**
     * Method that returns an array representation of the bounds
     *
     * @param void
     * @return array
     */
    public function toArray()
    {
      $bounds         = array();
      $bounds['ne']   = $this->getGeocodeNorthEast()->toArray();
      $bounds['nw']   = $this->getGeocodeNorthWest()->toArray();
      $bounds['se']   = $this->getGeocodeSouthEast()->toArray();
      $bounds['sw']   = $this->getGeocodeSouthWest()->toArray();
      $bounds['ce']   = $this->getGeocodeCenter()->toArray();

      return $bounds;
    }

  }

?>