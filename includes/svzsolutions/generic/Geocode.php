<?php

  /**
   * Copyright (c) 2009, SVZ Solutions All Rights Reserved.
   * Available via BSD license, see license file included for details.
   *
   * @title:        SVZ Solutions Generic Geocode file
   * @authors:      Stefan van Zanden <info@svzsolutions.nl>
   * @company:      SVZ Solutions
   * @contributers:
   * @version:      0.1
   * @versionDate:  2009-10-17
   * @date:         2009-10-17
   */

  /**
   * SVZ_Solutions_Generic_Geocode class
   *
   */
  class SVZ_Solutions_Generic_Geocode
  {
    const DMS_DEG_SIGN_HTML     = '&#176;'; // deg
    const DMS_MIN_SIGN_HTML     = '&#8242;'; // prime
    const DMS_SEC_SIGN_HTML     = '&#8243;'; // Prime
    const DMS_DEFAULT_FORMAT    = '%deg%%deg-sign-html% %min%%min-sign% %sec%%sec-sign% %car-dir%';
    private $latitude   = null;
    private $longitude  = null;

    /**
     * Constructor
     *
     * @param float $latitude
     * @param float $longitude
     * @return void
     */
    public function __construct($latitude = null, $longitude = null)
    {
      if (!is_null($latitude))
        $this->setLatitude($latitude);

      if (!is_null($longitude))
        $this->setLongitude($longitude);

    }

    /**
     * Set the latitude position of the marker
     *
     * @param float $latitude
     * @return void
     */
    public function setLatitude($latitude)
    {
      if (!is_float($latitude) && !is_int($latitude))
        throw new Exception(__METHOD__ . '; Invalid $latitude, not a float or int.');

      $this->latitude = $latitude;
    }

    /**
     * Get the geocode latitude position of the marker
     *
     * @param void
     * @return float
     */
    public function getLatitude()
    {
      return $this->latitude;
    }

    /**
     * Set the geocode longitude position of the marker
     *
     * @param float $longitude
     * @return void
     */
    public function setlongitude($longitude)
    {
      if (!is_float($longitude) && !is_int($longitude))
        throw new Exception(__METHOD__ . '; Invalid $longitude, not a float or int.');

      $this->longitude = $longitude;
    }

    /**
     * Get the geocode longitude position of the marker
     *
     * @param void
     * @return float
     */
    public function getlongitude()
    {
      return $this->longitude;
    }

    /**
     * Converts a decimal value to a degree / minutes / seconds,
     * method inspired from http://andrew.hedges.name/experiments/convert_lat_long/
     *
     * @param float $decimal
     * @return array
     */
    public function toDMS($decimal)
    {
      if (!is_float($decimal))
        throw new Exception(__METHOD__ . '; Invalid $decimal, not a float.');

      $dms = array();

      $parts = explode('.', $decimal);

      // First part is the degree
      $dms['degree'] = $parts[0];

      // Minutes
      $dmsRemainder = (float)('0.' . $parts[1]) * 60;
      $dmsRemainderParts = explode('.', $dmsRemainder);
      $dms['minutes'] = $dmsRemainderParts[0];

      // Seconds
      $dmsRemainder = (float)('0.' . $dmsRemainderParts[1]) * 60;
      $dms['seconds'] = round($dmsRemainder);

      return $dms;
    }

    /**
     * Returns the DMS of the latitude
     *
     * @param string $format
     * @return string
     */
    public function getLatitudeInDMS($format = self::DMS_DEFAULT_FORMAT)
    {
      $cardinalDirection = 'N';

      if (substr(0, 1, $this->getLatitude()) == '-')
        $cardinalDirection = 'S';

      $dmsArray = $this->toDMS($this->getLatitude());

      $dms = str_replace('%deg%', $dmsArray['degree'], $format);
      $dms = str_replace('%deg-sign-html%', self::DMS_DEG_SIGN_HTML, $dms);
      $dms = str_replace('%min%', $dmsArray['minutes'], $dms);
      $dms = str_replace('%min-sign%', self::DMS_MIN_SIGN_HTML, $dms);
      $dms = str_replace('%sec%', $dmsArray['seconds'], $dms);
      $dms = str_replace('%sec-sign%', self::DMS_SEC_SIGN_HTML, $dms);
      $dms = str_replace('%car-dir%', $cardinalDirection, $dms);

      return $dms;
    }

    /**
     * Returns the DMS of the latitude
     *
     * @param string $format
     * @return string
     */
    public function getLongitudeInDMS($format = self::DMS_DEFAULT_FORMAT)
    {
      $cardinalDirection = 'E';

      if (substr(0, 1, $this->getLongitude()) == '-')
        $cardinalDirection = 'W';

      $dmsArray = $this->toDMS($this->getLongitude());

      $dms = str_replace('%deg%', $dmsArray['degree'], $format);
      $dms = str_replace('%deg-sign-html%', self::DMS_DEG_SIGN_HTML, $dms);
      $dms = str_replace('%min%', $dmsArray['minutes'], $dms);
      $dms = str_replace('%min-sign%', self::DMS_MIN_SIGN_HTML, $dms);
      $dms = str_replace('%sec%', $dmsArray['seconds'], $dms);
      $dms = str_replace('%sec-sign%', self::DMS_SEC_SIGN_HTML, $dms);
      $dms = str_replace('%car-dir%', $cardinalDirection, $dms);

      return $dms;
    }

    /**
     * Method which will check if the 2nd provided geocode is closer to this geocode then the first provided
     *
     * @param $geocode1 SVZ_Solutions_Generic_Geocode
     * @param $geocode2 SVZ_Solutions_Generic_Geocode
     * @return boolean
     */
    public function isCloser(SVZ_Solutions_Generic_Geocode $geocode1, SVZ_Solutions_Generic_Geocode $geocode2)
    {
      $distance1 = $this->getDistance($geocode1);
      $distance2 = $this->getDistance($geocode2);

      if ($distance1 > $distance2)
        return true;

      return false;
    }

    /**
     * Method which calculates the distance between 2 points
     *
     * method inspired from http://www.marketingtechblog.com/technology/calculate-distance/
     *
     * @param $geocode1 SVZ_Solutions_Generic_Geocode
     * @param $geocode2 SVZ_Solutions_Generic_Geocode
     * @return float
     */
    public function getDistance(SVZ_Solutions_Generic_Geocode $geocode2, $unit = 'Mi')
    {
      $theta    = $this->getLongitude() - $geocode2->getLongitude();
      $distance = (sin(deg2rad($this->getLatitude())) * sin(deg2rad($geocode2->getLatitude()))) +
      (cos(deg2rad($this->getLatitude())) * cos(deg2rad($geocode2->getLatitude())) *
      cos(deg2rad($theta)));
      $distance = acos($distance);
      $distance = rad2deg($distance);
      $distance = $distance * 60 * 1.1515;

      switch($unit)
      {
        case 'Mi': break;
        case 'Km' : $distance = $distance * 1.609344;
      }

      return (round($distance,2));
    }

    /**
     * Method that returns an array representation of the bounds
     *
     * @param void
     * @return array
     */
    public function toArray()
    {
      $geocode             = array();
      $geocode['geoLat']   = $this->getLatitude();
      $geocode['geoLng']   = $this->getLongitude();

      return $geocode;
    }

  }

?>