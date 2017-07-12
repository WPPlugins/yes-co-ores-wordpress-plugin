<?php

  /**
   * Copyright (c) 2009-2010, SVZ Solutions All Rights Reserved.
   * Available via BSD license, see license file included for details.
   *
   * @title:        SVZ Solutions Generic ViewPort File
   * @authors:      Stefan van Zanden <info@svzsolutions.nl>
   * @company:      SVZ Solutions
   * @contributers:
   * @version:      0.6
   * @versionDate:  2010-06-27
   * @date:         2010-02-14
   */

  /**
   * SVZ_Solutions_Generic_ViewPort class
   *
   */
  class SVZ_Solutions_Generic_ViewPort
  {
    private static $instance    = null;
    private $radiusInPixels     = 50;
    private $bounds             = null;
    private $zoomLevel          = 0;
    private $width              = 0;
    private $height             = 0;

    /**
     * Constructor
     *
     * @param array $params
     * @return void
     */
    private function __construct()
    {
      $params                   = $_GET;
      $geocodeSouthWest         = null;
      $geocodeNorthEast         = null;
      $geocodeCenter            = null;

      if (isset($params['sw_lat']) && isset($params['sw_lng']))
        $geocodeSouthWest         = new SVZ_Solutions_Generic_Geocode((float)$params['sw_lat'], (float)$params['sw_lng']);

      if (isset($params['ne_lat']) && isset($params['ne_lng']))
        $geocodeNorthEast         = new SVZ_Solutions_Generic_Geocode((float)$params['ne_lat'], (float)$params['ne_lng']);

      if (isset($params['ce_lat']) && isset($params['ce_lng']))
        $geocodeCenter         = new SVZ_Solutions_Generic_Geocode((float)$params['ce_lat'], (float)$params['ce_lng']);

      if (!is_null($geocodeSouthWest) && !is_null($geocodeNorthEast))
        $this->bounds             = new SVZ_Solutions_Generic_Bounds($geocodeSouthWest, $geocodeNorthEast);

      if (!is_null($geocodeCenter) && !is_null($this->bounds))
        $this->bounds->setGeocodeCenter($geocodeCenter);

      if (isset($params['zoom']))
        $this->zoomLevel          = (int)$params['zoom'];

      if (isset($params['w']))
        $this->width              = (int)$params['w'];

       if (isset($params['h']))
        $this->height             = (int)$params['h'];

    }

    /**
     * Method which returns a viewport instance
     *
     * @param boolean $forceNew
     * @return SVZ_Solutions_Generic_ViewPort
     */
    public static function getInstance($forceNew = false)
    {
      if (is_null(self::$instance) || $forceNew)
        self::$instance = new self();

      return self::$instance;
    }

    /**
     * Method which returns the width in pixels of the map
     *
     * @param void
     * @return integer
     */
    public function getWidth()
    {
      return $this->width;
    }

    /**
     * Method which returns the height in pixels of the map
     *
     * @param void
     * @return integer
     */
    public function getHeight()
    {
      return $this->height;
    }

    /**
     * Method which returns the bounds
     *
     * @param void
     * @return SVZ_Solutions_Generic_Bound
     */
    public function getBounds()
    {
      return $this->bounds;
    }

    /**
     * Method which will return the bounds based upon the zoom level provided
     *
     * @param integer zoomLevel
     * @return
     */
    public function getBoundsByZoomLevel($zoomLevel)
    {
      throw new Exception(__METHOD__ . '; This method is not yet completed');
      /*
      if (!is_integer($zoomLevel))
        throw new Exception(__METHOD__ . '; Invalid zoom level, not a integer');


      $currentZoomLevel = $this->getZoomLevel();

      $dividerZoomLevel9  = 0.989360363655;
      $dividerZoomLevel10 = 0.980453929235;
      $dividerZoomLevel11 = 0.978023962128;
      $dividerZoomLevel12 = 0.976056454817;
      $dividerZoomLevel13 = 0.975419714429;

      //echo 'Difference between divider 9 and 10 [' . ($dividerZoomLevel9 - $dividerZoomLevel10) . ']<br />';
      //echo 'Difference between divider 10 and 11 [' . ($dividerZoomLevel10 - $dividerZoomLevel11) . ']<br />';
      //echo 'Difference between divider 11 and 12 [' . ($dividerZoomLevel11 - $dividerZoomLevel12) . ']<br />';
      //echo 'Difference between divider 12 and 13 [' . ($dividerZoomLevel12 - $dividerZoomLevel13) . ']<br /><br />';

      // Wil calculate the bounds on a zoom level based on the current bounds and zoom level
      switch ($zoomLevel)
      {
        case 9:
          $divider          = $this->getBounds()->getGeocodeSouthWest()->getLatitude() / 51.054806949176594;
          $divider2          = $this->getBounds()->getGeocodeNorthEast()->getLatitude() / 52.07911453459811;
        break;

        case 10:
          $divider          = $this->getBounds()->getGeocodeSouthWest()->getLatitude() / 51.51858834302642;
        break;

        case 11:
          $divider          = $this->getBounds()->getGeocodeSouthWest()->getLatitude() / 51.64658978260384;
        break;

        case 12:
          $divider          = $this->getBounds()->getGeocodeSouthWest()->getLatitude() / 51.75069753422636;
        break;

        case 13:
          $divider          = $this->getBounds()->getGeocodeSouthWest()->getLatitude() / 51.7844796679424;
        break;

      }

      $divider = $dividerZoomLevel9;

      //echo $this->getBounds()->getGeocodeSouthWest()->getLatitude() . '<br />';

      echo 'Divider asked zoom level ' . $zoomLevel . ': [' . $divider . ']<br />';
      echo 'Divider asked zoom level ' . $zoomLevel . ': [' . $divider2 . ']<br />';

      $newBounds        = new SVZ_Solutions_Generic_Bounds();

      $newBounds->setGeocodeSouthWest($this->getBounds()->getGeocodeSouthWest());
      $newBounds->setGeocodeNorthEast($this->getBounds()->getGeocodeNorthEast());

      if ($currentZoomLevel == $zoomLevel)
      {
        // No need for calculations
      }
      else
      {
        if ($currentZoomLevel > $zoomLevel)
        {
          $numZoomLevelsBetween = $currentZoomLevel - $zoomLevel;

          for ($i = 0; $i < $numZoomLevelsBetween; $i++)
          {
            $newGeocodeSouthWest = new SVZ_Solutions_Generic_Geocode($newBounds->getGeocodeSouthWest()->getLatitude() / $divider, $newBounds->getGeocodeSouthWest()->getLongitude() / $divider);
            $newGeocodeNorthEast = new SVZ_Solutions_Generic_Geocode($newBounds->getGeocodeNorthEast()->getLatitude() / $divider, $newBounds->getGeocodeNorthEast()->getLongitude() / $divider);

            $newBounds->setGeocodeSouthWest($newGeocodeSouthWest);
            $newBounds->setGeocodeNorthEast($newGeocodeNorthEast);
          }
        }
        else
        {
          $numZoomLevelsBetween = $zoomLevel - $currentZoomLevel;

          for ($i = 0; $i < $numZoomLevelsBetween; $i++)
          {
            $newGeocodeSouthWest = new SVZ_Solutions_Generic_Geocode($newBounds->getGeocodeSouthWest()->getLatitude() / $divider, $newBounds->getGeocodeSouthWest()->getLongitude() / $divider);
            $newGeocodeNorthEast = new SVZ_Solutions_Generic_Geocode($newBounds->getGeocodeNorthEast()->getLatitude() / $divider, $newBounds->getGeocodeNorthEast()->getLongitude() / $divider);

            $newBounds->setGeocodeSouthWest($newGeocodeSouthWest);
            $newBounds->setGeocodeNorthEast($newGeocodeNorthEast);
          }
        }
      }

      return $newBounds;
      */
    }

    /**
     * Method which returns the zoom level
     *
     * @param void
     * @return integer
     */
    public function getZoomLevel()
    {
      return $this->zoomLevel;
    }

    /**
     * Method which sets the radius in pixels
     *
     * @param integer $radiusInPixels
     * @return void
     */
    public function setRadiusInPixels($radiusInPixels = 50)
    {
      if (!is_int($radiusInPixels) || empty($radiusInPixels))
        throw new Exception(__METHOD__ . '; Invalid $radiusInPixels, not a integer or empty');

      $this->radiusInPixels = $radiusInPixels;
    }

    /**
     * Method which returns the radius in pixels
     *
     * @param void
     * @return integer
     */
    public function getRadiusInPixels()
    {
      return $this->radiusInPixels;
    }

    /**
     * Method which returns a sql query based on the ViewPort
     *
     * @param boolean $excludeRadius
     * @return string
     */
    public function getSqlQueryPart($excludeRadius = false)
    {
      if (!is_bool($excludeRadius))
        throw new Exception(__METHOD__ . '; Invalid $excludeRadius, not a boolean');

      $sql = '';

      $southWestLatitude  = $this->getBounds()->getGeocodeSouthWest()->getLatitude();
      $southWestLongitude = $this->getBounds()->getGeocodeSouthWest()->getLongitude();
      $northEastLatitude  = $this->getBounds()->getGeocodeNorthEast()->getLatitude();
      $northEastLongitude = $this->getBounds()->getGeocodeNorthEast()->getLongitude();

      if (!$excludeRadius)
      {
        // We need to include extra latitude / longitude based on the radius in pixels
        /*echo $southWestLatitude . '<br />';
        echo $southWestLongitude . '<br />';
        echo $northEastLatitude . '<br />';
        echo $northEastLongitude . '<br />';*/

        //echo $this->getBounds()->getGeocodeNorthWest();
        //echo $this->getBounds()->getGeocodeNorthEast();

        $distanceInPixels = SVZ_Solutions_Generic_Math::getDistanceInPixels($this->getBounds()->getGeocodeNorthWest(), $this->getBounds()->getGeocodeNorthEast(), $this->getZoomLevel());

        if ($distanceInPixels > $this->getRadiusInPixels())
        {
          /*echo $distanceInPixels . '<br />';
          echo $this->getRadiusInPixels() . '<br />';

          echo $distanceInPixels / $this->getRadiusInPixels() . '<br />';*/

          $divider = $distanceInPixels / $this->getRadiusInPixels();

          $appendForRadius = (($this->getBounds()->getGeocodeSouthWest()->getLatitude() + $this->getBounds()->getGeocodeNorthEast()->getLatitude()) / 2) / $divider;

          /*echo 'Appending ' . $appendForRadius . '<br />';

          $southWestLatitude = ($southWestLatitude - $appendForRadius);
          $northEastLatitude = ($northEastLatitude + $appendForRadius);
          $southWestLongitude = ($southWestLongitude - $appendForRadius);
          $northEastLongitude = ($northEastLongitude + $appendForRadius);

          echo $southWestLatitude . '<br />';
          echo $southWestLongitude . '<br />';
          echo $northEastLatitude . '<br />';
          echo $northEastLongitude . '<br />';*/
        }


        //echo SVZ_Solutions_Generic_Math::getDistanceInPixels($this->getBounds()->getGeocodeSouthWest(), $this->getBounds()->getGeocodeSouthEast(), $this->getZoomLevel());

      }


      $sql = ' (lat BETWEEN ' . $southWestLatitude . ' AND ' . $northEastLatitude . ') ';

      // Apparantly we are crossing some lines between the southwest point and the north east point
      if ($southWestLongitude > $northEastLongitude)
      {
        $sql .= ' AND ((lng BETWEEN ' . $southWestLongitude . ' AND 180)';
        $sql .= ' OR (lng BETWEEN -180 AND ' . $northEastLongitude . '))';
      }
      else
      {
        $sql .= ' AND (lng BETWEEN ' . $southWestLongitude  . ' AND ' . $northEastLongitude . ') ';
      }

      return $sql;
    }
  }

?>