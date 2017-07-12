<?php

  /**
   * Copyright (c) 2009, SVZ Solutions All Rights Reserved.
   * Available via BSD license, see license file included for details.
   *
   * @title:        SVZ Solutions Generic Marker Cluster file
   * @authors:      Stefan van Zanden <info@svzsolutions.nl>
   * @company:      SVZ Solutions
   * @contributers:
   * @version:      0.1
   * @versionDate:  2009-10-17
   * @date:         2009-10-17
   */

  require_once('Marker.php');
  require_once('MarkerStack.php');

  /**
   * SVZ_Solutions_Generic_Marker_Cluster class
   *
   */
  class SVZ_Solutions_Generic_Marker_Cluster extends SVZ_Solutions_Generic_Marker
  {
    private $markers                    = null;
    private $smartNavigationCalculated  = false;
    private $zoomToLevel                = null;
    private $bounds                     = null;
    private $count                      = false;

    /**
     * Constructor
     *
     * @param void
     * @return void
     */
    public function __construct()
    {
      parent::__construct('cluster');

      $this->markers                = new SVZ_Solutions_Generic_Marker_Stack();
      $this->bounds                 = new SVZ_Solutions_Generic_Bounds();
    }

    /**
     * Method setCount which manually set's the number of markers this cluster contains
     *
     * @param {Integer} $count
     * @return {Void}
     */
    public function setCount($count)
    {
        if (!is_integer($count))
            throw new Exception(__METHOD__ . '; Invalid $count, not an integer.');

        $this->count = $count;
    }

    /**
     * Method thats adds a marker to the marker stack
     *
     * @param SVZ_Solutions_Generic_Marker $marker
     * @return void
     */
    public function addMarker(SVZ_Solutions_Generic_Marker $marker)
    {
      if (is_null($this->getBounds()->getGeocodeSouthWest()))
      {
        // Will only come here 1 time to register the first geocodes
        $geocodeNorthEast = new SVZ_Solutions_Generic_Geocode($marker->getGeocode()->getLatitude(), $marker->getGeocode()->getLongitude());
        $geocodeSouthEast = new SVZ_Solutions_Generic_Geocode($marker->getGeocode()->getLatitude(), $marker->getGeocode()->getLongitude());
        $geocodeSouthWest = new SVZ_Solutions_Generic_Geocode($marker->getGeocode()->getLatitude(), $marker->getGeocode()->getLongitude());
        $geocodeNorthWest = new SVZ_Solutions_Generic_Geocode($marker->getGeocode()->getLatitude(), $marker->getGeocode()->getLongitude());

        $this->getBounds()->setGeocodeNorthEast($geocodeNorthEast);
        $this->getBounds()->setGeocodeSouthEast($geocodeSouthEast);
        $this->getBounds()->setGeocodeSouthWest($geocodeSouthWest);
        $this->getBounds()->setGeocodeNorthWest($geocodeNorthWest);
      }
      else
      {
        // Calculate the bound of the North East and South East Latitude
        if ($marker->getGeocode()->getLatitude() > $this->getBounds()->getGeocodeNorthEast()->getLatitude())
        {
          $this->getBounds()->getGeocodeNorthEast()->setLatitude($marker->getGeocode()->getLatitude());
          $this->getBounds()->getGeocodeSouthEast()->setLatitude($marker->getGeocode()->getLatitude());
        }

        // Calculate the bound of the North East and North West Longitude
        if ($marker->getGeocode()->getLongitude() > $this->getBounds()->getGeocodeNorthEast()->getLongitude())
        {
          $this->getBounds()->getGeocodeNorthEast()->setLongitude($marker->getGeocode()->getLongitude());
          $this->getBounds()->getGeocodeNorthWest()->setLongitude($marker->getGeocode()->getLongitude());
        }

        // Calculate the bound of the South East and South West Longitude
        if ($marker->getGeocode()->getLongitude() < $this->getBounds()->getGeocodeSouthEast()->getLongitude())
        {
          $this->getBounds()->getGeocodeSouthEast()->setLongitude($marker->getGeocode()->getLongitude());
          $this->getBounds()->getGeocodeSouthWest()->setLongitude($marker->getGeocode()->getLongitude());
        }

        // Calculate the bound of the South West and North West Latitude
        if ($marker->getGeocode()->getLatitude() < $this->getBounds()->getGeocodeSouthWest()->getLatitude())
        {
          $this->getBounds()->getGeocodeSouthWest()->setLatitude($marker->getGeocode()->getLatitude());
          $this->getBounds()->getGeocodeNorthWest()->setLatitude($marker->getGeocode()->getLatitude());
        }
      }

      $this->markers->push($marker);
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
     * Method thats returns the marker defined by the position
     *
     * @param integer position
     * @return mixed
     */
    public function getMarkerByPosition($position)
    {
      return $this->markers->getByPosition($position);
    }

    /**
     * Method that counts the number of markers in the stack
     *
     * @param void
     * @return integer
     */
    public function count()
    {
        if ($this->count !== false)
            return $this->count;

        return $this->markers->count();
    }

    /**
     * Method that returns the entity ids of all the markers
     *
     * @param void
     * @return integer
     */
    public function getEntityIds()
    {
      $entityIds = array();

      foreach ($this->markers as $key => $marker)
      {
        $entityIds[] = $marker->getEntityId();
      }

      return $entityIds;
    }

    /**
     * Method that calculates the smart navigation values
     *
     * @param void
     * @return void
     */
    private function calculateSmartNavigation()
    {
      if (!$this->smartNavigationCalculated)
      {
        $debug = false;

        $viewPort       = SVZ_Solutions_Generic_ViewPort::getInstance();

        $numZoomLevels  = 20 - ($viewPort->getZoomLevel() + 1);

        //if ($this->count() == 5)
          //$debug = true;

        if ($debug)
          echo '[' . $this->count() . ']';

        // It will always zoom 1 level at the least so no need to check it
        $zoomToLevel    = $viewPort->getZoomLevel() + 1;
        $checkZoomLevel = $zoomToLevel;

        if ($debug)
          echo '==' . $zoomToLevel . '==';

        for ($i = 1; $i <= $numZoomLevels; $i++)
        {
          $zoomLevelWidth = SVZ_Solutions_Generic_Math::getDistanceInPixels(
                                          $this->getBounds()->getGeocodeNorthEast(),
                                          $this->getBounds()->getGeocodeNorthWest(),
                                          $checkZoomLevel);

          if ($debug)
            echo '{' . $zoomLevelWidth . ' on zoom level ' . $checkZoomLevel . '}';

          $viewPortWidth  = $viewPort->getWidth();
          $viewPortHeight = $viewPort->getHeight();

          $compareSize    = $viewPortWidth;

          // Compare to the smallest value
          if ($viewPortWidth > $viewPortHeight)
            $compareSize = $viewPortHeight;

          if ($zoomLevelWidth > $compareSize)
            break;

          if ($debug)
            echo 'Accepting zoom level' . $checkZoomLevel;

          $zoomToLevel = $checkZoomLevel;
          $checkZoomLevel++;
        }

        $this->zoomToLevel = $zoomToLevel;

        $this->smartNavigationCalculated = true;

        if ($debug)
          echo  $this->zoomToLevel . '<br />';

      }
    }

    /**
     * Method that calculates the smart navigation values
     *
     * @param void
     * @return array
     */
    public function getSmartNavigationValuesAsArray()
    {
      $this->calculateSmartNavigation();

      $array                = array();
      $array['zoomToLevel'] = $this->zoomToLevel;

      return $array;
    }

    /**
     * Method which returns an array with the count per marker type
     *
     * @param void
     * @return array
     */
    public function getCountPerMarkerType()
    {
      $countPerType = array();

      foreach ($this->markers as $marker)
      {
        $typeName = $marker->getTypeName();

        if (isset($countPerType[$typeName]))
          $countPerType[$typeName]++;
        else
          $countPerType[$typeName] = 1;

      }

      return $countPerType;
    }

    /**
     * Method which returns a new MarkerStack with the x number of closest markers
     *
     * @param integer $numberOfClosestsMarkers
     * @return SVZ_Solutions_Generic_Marker_Stack
     */
    public function getClosestsMarkers($numberOfClosestsMarkers)
    {
      if (!is_int($numberOfClosestsMarkers) || !$numberOfClosestsMarkers > 0)
        throw new Exception(__METHOD__ . '; Invalid $numberOfClosestsMarkers, not an integer or empty');

      $geocodeCenter  = $this->bounds->getGeocodeCenter();

      $markers        = new SVZ_Solutions_Generic_Marker_Stack();

      $tempMarkers    = array();
      $tempMarkers2   = array();

      foreach ($this->markers as $marker)
      {
        if (count($tempMarkers) == 0)
        {
          $tempMarkers[] = $marker;
          continue;
        }

        $tempMarkers2     = array();
        $matchedPosition  = false;

        foreach ($tempMarkers as $tempMarker)
        {
          if (!$matchedPosition && $geocodeCenter->isCloser($tempMarker->getGeocode(), $marker->getGeocode()))
          {
            $tempMarkers2[]   = $marker;
            $tempMarkers2[]   = $tempMarker;
            $matchedPosition  = true;
          }
          else
          {
            $tempMarkers2[] = $tempMarker;
          }
        }

        if (!$matchedPosition)
          $tempMarkers2[] = $marker;

        $tempMarkers  = $tempMarkers2;
      }

      // Return the markers that the implementer has asked for
      for ($i = 0; $i < $numberOfClosestsMarkers; $i++)
      {
        if (!isset($tempMarkers[$i]))
          break;

        $markers->push($tempMarkers[$i]);
      }

      return $markers;
    }

    /**
     * Method toArray
     *
     * @param {Void}
     * @return {Array}
     */
    public function toArray()
    {
        $array                     = parent::toArray();
        $array['count']            = $this->count();
        $array['label']            = $this->count();

        $array['title']            = ($this->hasTitle() ? $this->getTitle() . ': ' : '') . $this->count();

        $array['entityIds']        = $this->getEntityIds();

        $geocodeCenter             = $this->getBounds()->getGeocodeCenter();

        $array['geoLat']  = $geocodeCenter->getLatitude();
        $array['geoLng']  = $geocodeCenter->getLongitude();

        // $array['geoLat']  = $this->getGeocode()->getLatitude();
        // $array['geoLng']  = $this->getGeocode()->getLongitude();

        if ($this->hasAddress())
            $array['address'] = $this->getAddress()->toArray();

        return $array;
    }
  }

?>