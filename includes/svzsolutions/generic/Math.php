<?php

  /**
   * Copyright (c) 2010, SVZ Solutions All Rights Reserved.
   * Available via BSD license, see license file included for details.
   *
   * @title:        SVZ Solutions Generic General file
   * @description:  This file contains several usefull mathematical conversions.
   * @authors:      Stefan van Zanden <info@svzsolutions.nl>
   * @company:      SVZ Solutions
   * @contributers:
   * @version:      0.6
   * @versionDate:  2010-09-25
   * @date:         2010-09-25
   */

  /**
   * SVZ_Solutions_Generic_Math class
   *
   */
  class SVZ_Solutions_Generic_Math
  {
    const MILES_TO_KILOMETRES_EQUATION  = 1.609344;
    const OFFSET                        = 268435456;  // This is half of the earth circumference in pixels at zoom level 21
    const RADIUS                        = 85445659.4471; /* $offset / pi() */

    /**
     * Method that converts a value in kilometres to a value in miles
     *
     * @param float / int $kilometres
     * @return float / int $miles
     */
    public static function kilometresToMiles($kilometres)
    {
      if (!is_float($kilometres) && !is_int($kilometres))
        throw new Exception(__METHOD__ . '; Invalid $kilometres, not a float or integer');

      $miles = $kilometres / self::MILES_TO_KILOMETRES_EQUATION;

      return $miles;
    }

    /**
     * Method that converts a value in miles to a value in kilometres
     *
     * @param float / int $miles
     * @return float / int $kilometres
     */
    public static function milesToKilometres($miles)
    {
      if (!is_float($miles) && !is_int($miles))
        throw new Exception(__METHOD__ . '; Invalid $miles, not a float or integer');

      $kilometres = $miles * self::MILES_TO_KILOMETRES_EQUATION;

      return $kilometres;
    }

    /**
     * Method that returns a radius by a provided geocode and distance
     *
     * Created function from an already researched code by Federico Cargnelutti:
     * http://blog.fedecarg.com/2009/02/08/geo-proximity-search-the-haversine-equation/
     *
     * @param SVZ_Solutions_Generic_Geocode $geocode
     * @param float / int $radiusInMiles
     * @return array
     */
    public static function getRadiusByGeocode(SVZ_Solutions_Generic_Geocode $geocode, $radiusInMiles)
    {
      if (!is_float($radiusInMiles) && !is_int($radiusInMiles))
        throw new Exception(__METHOD__ . '; Invalid $radiusInMiles, not a float or integer');

      $someValue  = $radiusInMiles / abs(cos(deg2rad($geocode->getLatitude())) * 69);

      $lngMin     = $geocode->getLongitude() - $someValue;
      $lngMax     = $geocode->getLongitude() + $someValue;

      $latMin     = $geocode->getLatitude() - ($radiusInMiles / 69);
      $latMax     = $geocode->getLatitude() + ($radiusInMiles / 69);

      return array('lngMin' => $lngMin, 'lngMax' => $lngMax, 'latMin' => $latMin, 'latMax' => $latMax);
    }

    /**
     * Method that returns the distance in a unit asked between 2 points
     *
     * Created function from an already researched code by Kevin Waterson:
     * http://www.phpro.org/tutorials/Geo-Targetting-With-PHP-And-MySQL.html
     *
     * @param SVZ_Solutions_Generic_Geocode $point1
     * @param SVZ_Solutions_Generic_Geocode $point2
     * @param string $unit ('k' or 'm')
     * @return float / int
     */
    private static function getDistance(SVZ_Solutions_Generic_Geocode $point1, SVZ_Solutions_Generic_Geocode $point2, $unit = 'k')
    {
      switch ($unit)
      {
        case 'm':

          $unit = 3963;

          break;

        case 'k':

          $unit = 6371;

          break;

        default:

          throw new Exception(__METHOD__ . '; Invalid $unit, needs to be "k" or "m".');

          break;
      }

     $degreeRadius  = deg2rad(1);

     $latFrom       = $point1->getLatitude() * $degreeRadius;
     $longFrom      = $point1->getLongitude() * $degreeRadius;
     $latTo         = $point2->getLatitude() * $degreeRadius;
     $longTo        = $point2->getLongitude() * $degreeRadius;

     $dist          = sin($latFrom) * sin($latTo) + cos($latFrom) * cos($latTo) * cos($longFrom - $longTo);

     return ($unit * acos($dist));
    }

    /**
     * Method that returns the distance in miles between 2 points
     *
     * @param SVZ_Solutions_Generic_Geocode $point1
     * @param SVZ_Solutions_Generic_Geocode $point2
     * @return float / int
     */
    public static function getDistanceInMiles(SVZ_Solutions_Generic_Geocode $point1, SVZ_Solutions_Generic_Geocode $point2)
    {
      return self::getDistance($point1, $point2, 'm');
    }

    /**
     * Method that returns the distance in kilometre between 2 points
     *
     * @param SVZ_Solutions_Generic_Geocode $point1
     * @param SVZ_Solutions_Generic_Geocode $point2
     * @return float / int
     */
    public static function getDistanceInKilometres(SVZ_Solutions_Generic_Geocode $point1, SVZ_Solutions_Generic_Geocode $point2)
    {
      return self::getDistance($point1, $point2, 'k');

    }

    /* BEGIN
     *
     * Mathematical functions copied from
     * http://www.appelsiini.net/2008/11/introduction-to-marker-clustering-with-google-maps
     * licensed under the MIT license
     * will need some optimization / be replaced on large numbers of markers
     */

    /**
     * Method lonToX
     *
     * Created function from an already researched code by Mika Tuupola:
     * http://www.appelsiini.net/2008/11/introduction-to-marker-clustering-with-google-maps
     *
     * @param float $lon
     * @return float
     */
    private static function lonToX($lon)
    {
      return round(self::OFFSET + self::RADIUS * $lon * pi() / 180);
    }

    /**
     * Method latToY
     *
     * Created function from an already researched code by Mika Tuupola:
     * http://www.appelsiini.net/2008/11/introduction-to-marker-clustering-with-google-maps
     *
     * @param float $lat
     * @return float
     */
    public static function latToY($lat)
    {
      return round(self::OFFSET - self::RADIUS *
                  log((1 + sin($lat * pi() / 180)) /
                  (1 - sin($lat * pi() / 180))) / 2);

    }

    /**
     * Method that returns the distance in pixels between 2 points
     *
     * Created function from an already researched code by Mika Tuupola:
     * http://www.appelsiini.net/2008/11/introduction-to-marker-clustering-with-google-maps
     *
     * @param SVZ_Solutions_Generic_Geocode $point1
     * @param SVZ_Solutions_Generic_Geocode $point2
     * @param int $zoomLevel
     * @return int
     */
    public static function getDistanceInPixels(SVZ_Solutions_Generic_Geocode $point1, SVZ_Solutions_Generic_Geocode $point2, $zoomLevel)
    {
      if (!is_int($zoomLevel))
        throw new Exception(__METHOD__ . '; Invalid $zoomLevel, not an integer');

      $x1 = self::lonToX($point1->getLongitude());
      $y1 = self::latToY($point1->getLatitude());

      $x2 = self::lonToX($point2->getLongitude());
      $y2 = self::latToY($point2->getLatitude());

      return sqrt(pow(($x1 - $x2), 2) + pow(($y1 - $y2), 2)) >> (21 - $zoomLevel);
    }

  }

?>