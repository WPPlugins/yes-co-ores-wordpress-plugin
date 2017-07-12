<?php

  /**
   * Copyright (c) 2010, SVZ Solutions All Rights Reserved.
   * Available via BSD license, see license file included for details.
   *
   * @title:        SVZ Solutions Generic Marker Manager file
   * @authors:      Stefan van Zanden <info@svzsolutions.nl>
   * @company:      SVZ Solutions
   * @contributers:
   * @version:      0.6
   * @versionDate:  2010-06-27
   * @date:         2009-10-17
   */

  require_once('Math.php');
  require_once('Address.php');
  require_once('Geocode.php');
  require_once('MarkerStack.php');
  require_once('MarkerList.php');
  require_once('Bounds.php');
  require_once('ViewPort.php');

  /**
   * SVZ_Solutions_Generic_Marker_Manager class
   *
   */
  class SVZ_Solutions_Generic_Marker_Manager
  {
    const CLUSTER_MODE_NONE               = 'none';
    const CLUSTER_MODE_DISTANCE           = 'distance';
    const CLUSTER_MODE_ADDRESS            = 'address';

    private $availableClusterModes        = array();
    private $markers                      = null;
    private $markerTitleTemplates         = array();
    private $clusterMode                  = self::CLUSTER_MODE_NONE;
    private $listDataLoadUrl              = '';
    private $disableListMarker            = false;
    private $enableSmartClusterNavigation = true;
    private $enableClusterBounds          = false;
    private $numberOfClosestsMarkers      = 0;
    private $numMarkers                   = 0;
    private $returnCountPerMarkerType     = false;
    private $useClusterForSingleMarker    = false;

    /**
     * Constructor
     *
     * @param integer $zoomLevel
     * @return void
     */
    public function __construct()
    {
      $this->availableClusterModes[]  = self::CLUSTER_MODE_NONE;
      $this->availableClusterModes[]  = self::CLUSTER_MODE_DISTANCE;
      $this->availableClusterModes[]  = self::CLUSTER_MODE_ADDRESS;

      $this->markers                  = new SVZ_Solutions_Generic_Marker_Stack();
    }

    /**
     * Method enableUseClusterForSingleMarker which enables that the list and cluster markers are also used when only 1 item is reported
     *
     * @param {Void}
     * @return {Void}
     */
    public function enableUseClusterForSingleMarker()
    {
        $this->useClusterForSingleMarker = true;
    }

    /**
     * Method getAvailableClusterModes which will return the available cluster modes
     *
     * @param void
     * @return array
     */
    public static function getAvailableClusterModes()
    {
    	return array(self::CLUSTER_MODE_NONE, self::CLUSTER_MODE_DISTANCE, self::CLUSTER_MODE_ADDRESS);
    }

    /**
     * Method which sets the number of closests markers to return
     *
     * @param integer $numMarkers
     * @return void
     */
    public function setNumberOfClosestsMarkers($numMarkers)
    {
      if (!is_int($numMarkers))
        throw new Exception(__METHOD__ . '; Invalid $numMarkers, not a integer.');

      $this->numberOfClosestsMarkers = $numMarkers;
    }

    /**
     * Method which will disable the list markers
     *
     * @param void
     * @return void
     */
    public function disableListMarker()
    {
      $this->disableListMarker = true;
    }

    /**
     * Method which disables the smart cluster navigation
     *
     */
    public function disableSmartClusterNavigation()
    {
      $this->enableSmartClusterNavigation = false;
    }

    /**
     * Method which enables the count per marker type on a cluster / list marker
     *
     * @param void
     * @return void
     */
    public function enableReturnCountPerMarkerType()
    {
      $this->returnCountPerMarkerType = true;
    }

    /**
     * Method that enables that the bounds are returned with a cluster marker
     *
     * @param void
     * @return void
     */
    public function enableClusterBounds()
    {
      $this->enableClusterBounds = true;
    }

    /**
     * Method that sets how the markers should be clustered
     *
     * @param string $clusterMode
     * @return void
     */
    public function setClusterMode($clusterMode)
    {
      if (!is_string($clusterMode))
        throw new Exception(__METHOD__ . '; Invalid $clusterMode, not a string.');

      if (!in_array($clusterMode, $this->availableClusterModes))
        throw new Exception(__METHOD__ . '; Invalid $clusterMode, not one of ' . implode(' / ', $this->availableClusterModes) . ', provided: "' . $clusterMode . '".');

      $this->clusterMode = $clusterMode;
    }

    /**
     * Method that gets the current cluster mode
     *
     * @param void
     * @return string
     */
    public function getClusterMode()
    {
      return $this->clusterMode;
    }

    /**
     * Method thats sets the data load url for the list marker
     *
     * @param string $listDataLoadUrl
     * @return void
     */
    public function setListDataLoadUrl($listDataLoadUrl)
    {
      if (!is_string($listDataLoadUrl) || empty($listDataLoadUrl))
        throw new Exception(__METHOD__ . '; Invalid $listDataLoadUrl, not a string or empty.');

      $this->listDataLoadUrl = $listDataLoadUrl;
    }

    /**
     * Method that sets a template to use for the title of a marker
     *
     * @param string $template
     * @param int $zoomLevel
     * @return void
     */
    public function setMarkerTitleTemplate($template, $zoomLevel)
    {
      if (!is_string($template))
        throw new Exception(__METHOD__ . '; Invalid $template, not a string.');

      if (!is_int($zoomLevel))
        throw new Exception(__METHOD__ . '; Invalid $zoomLevel, not a integer.');

      if ($zoomLevel > 22)
        throw new Exception(__METHOD__ . '; Invalid $zoomLevel, max is 22');

      $this->markerTitleTemplates[$zoomLevel] = $template;
    }

    /**
     * Method thats adds a marker to the marker manager
     *
     * @param SVZ_Solutions_Generic_Marker $marker
     * @return void
     */
    public function addMarker(SVZ_Solutions_Generic_Marker $marker)
    {
      if ($this->clusterMode == self::CLUSTER_MODE_ADDRESS && !$marker->hasAddress())
        throw new Exception(__METHOD__ . '; Invalid marker, an address is needed for the ADDRESS clustering mode.');

      $this->markers->push($marker);
    }

    /**
     * Method thats checks if the marker manager currently has any markers
     *
     * @param void
     * @return boolean
     */
    public function hasMarkers()
    {
      return $this->markers->count() > 0;
    }

    /**
     * Method thats imports a provided array (for example retrieved by the export function) into the manager
     *
     * @param array
     * @return bool
     */
    public function import($array)
    {
      $result = false;

      if (is_array($array))
      {
        foreach ($array as $markerArray)
        {
          $marker = new SVZ_Solutions_Generic_Marker($markerArray['type']);
          $marker->setEntityId($markerArray['entityId']);
          $marker->setDataLoadUrl($markerArray['dataLoadUrl']);
          $markerGeocode = new SVZ_Solutions_Generic_Geocode((float)$markerArray['geoLat'], (float)$markerArray['geoLng']);
          $marker->setGeocode($markerGeocode);
          $marker->setTitle($markerArray['title']);

          if (isset($markerArray['content']))
            $marker->setContent($markerArray['content']);

          if (isset($markerArray['address']))
          {
            $address = new SVZ_Solutions_Generic_Address();
            $address->fromArray($markerArray['address']);

            $marker->setAddress($address);
          }

          $this->addMarker($marker);
        }

        $result = true;
      }

      return $result;
    }

    /**
     * Method thats exports all the provided markers into a easy usable array
     *
     * @param void
     * @return array
     */
    public function export()
    {
      $array = array();

      foreach ($this->markers as $marker)
      {
        $markerArray                = array();
        $markerArray['type']        = $marker->getTypeName();
        $markerArray['entityId']    = $marker->getEntityId();
        $markerArray['label']       = $marker->getLabel();
        $markerArray['title']       = $marker->getTitle();
        $markerArray['geoLat']      = $marker->getGeocode()->getLatitude();
        $markerArray['geoLng']      = $marker->getGeocode()->getLongitude();
        $markerArray['dataLoadUrl'] = $marker->getDataLoadUrl();
        $markerArray['content']     = $marker->getContent();

        if ($marker->hasAddress())
          $markerArray['address'] = $marker->getAddress()->toArray();

        $array[] = $markerArray;
      }

      return $array;
    }

    /**
     * Method thats gives back a stack with markers
     *
     * @param void
     * @return SVZ_Solutions_Generic_Marker_Stack
     */
    public function getMarkers()
    {
      return $this->markers;
    }

    /**
     * Method thats gives back an array with markers
     *
     * @param void
     * @return array
     */
    public function toArray()
    {
      $array    = array();
      $markers  = array();

      $viewPort = SVZ_Solutions_Generic_ViewPort::getInstance();

      switch ($this->getClusterMode())
      {
        case self::CLUSTER_MODE_DISTANCE:

          if ($this->disableListMarker && in_array($viewPort->getZoomLevel(), array(17, 18, 19, 20)))
            $markers = $this->markers;
          else
            $markers = $this->clusterByDistance($this->markers, $viewPort->getRadiusInPixels());

          break;

        case self::CLUSTER_MODE_ADDRESS:

          if ($this->disableListMarker && in_array($viewPort->getZoomLevel(), array(17, 18, 19, 20)))
            $markers = $this->markers;
          else
            $markers = $this->clusterByAddress($this->markers);

          break;

        default:

          $markers = $this->markers;

          break;
      }


      /* echo '<pre>';
      print_r($this->markers);
      print_r($markers);
      exit; */

      // Convert all the markers from the stack to an array
      foreach ($markers as $key => $marker)
      {
        if ($marker instanceof SVZ_Solutions_Generic_Marker_List || $marker instanceof SVZ_Solutions_Generic_Marker_Cluster)
        {
            $numSubMarkers = $marker->count();

            /*echo '<pre>';
            print_r($marker);
            echo '--' . $numSubMarkers . '---';



             . '][';*/

            //echo $numSubMarkers . '--' . $marker->getTypeName() . ']';

          if ($numSubMarkers > 1 || $this->useClusterForSingleMarker)
          {

            $clusterMarker            = $marker->toArray();

            if ($marker instanceof SVZ_Solutions_Generic_Marker_Cluster)
            {
              // Calculate the smart navigation values
              if ($this->enableSmartClusterNavigation)
                $clusterMarker['smartNavigation'] = $marker->getSmartNavigationValuesAsArray();

              // Return the bounds for a cluster marker
              if ($this->enableClusterBounds)
                $clusterMarker['bounds']          = $marker->getBounds()->toArray();

            }

            if (!empty($this->numberOfClosestsMarkers))
              $clusterMarker['closestsMarkers'] = $marker->getClosestsMarkers($this->numberOfClosestsMarkers)->toArray();

            if ($this->returnCountPerMarkerType)
              $clusterMarker['countPerMarkerType'] = $marker->getCountPerMarkerType();

            $array[] = $clusterMarker;
          }
          else
          {
            $firstMarker                  = $marker->getMarkerByPosition(0);

            $array[]                      = $firstMarker->toArray();
          }

        }
        else if ($marker instanceof SVZ_Solutions_Generic_Marker)
        {
          $array[] = $marker->toArray();
        }
      }

      return $array;
    }

    /**
     * Method that clusters the markers based on the address depending on the current zoom level
     *
     * @param array $markers
     * @return array
     */
    private function clusterByAddress($markers)
    {
      $viewPort = SVZ_Solutions_Generic_ViewPort::getInstance();

      if (in_array($viewPort->getZoomLevel(), array(17, 18, 19, 20)))
        return $markers;

      $clustered           = array();

      $markerTitleTemplate = '';

      if (isset($this->markerTitleTemplates[$viewPort->getZoomLevel()]))
        $markerTitleTemplate = $this->markerTitleTemplates[$viewPort->getZoomLevel()];

      // Cluster it on different parts of the address depending of the zoomlevel
      // ZoomLevel 5 / 6 and higher = Country
      // ZoomLevel 7 = State
      // ZoomLevel 8 / 9 / 10 / 11 = City
      // ZoomLevel 12 / 13 = Area
      // ZoomLevel 14 = Neighbourhood
      // ZoomLevel 15 = Street
      // ZoomLevel 16 = ZipCode
      // ZoomLevel 17 / 18 / 19 / 20 = HouseNumber / HouseNumberAddition

      // First group all the markers
      while ($markers->valid())
      {
        $marker               = $markers->current();

        //$marker               = array_pop($markers);
        $markerAddress        = $marker->getAddress();

        $clusterMarkerAddress = new SVZ_Solutions_Generic_Address();

        switch ($viewPort->getZoomLevel())
        {
          case 1:
          case 2:
          case 3:
          case 4:
          case 5:
          case 6:

            $clusterMarkerAddress->setCountry($markerAddress->getCountry());
            $clusterMarkerAddress->setMunicipality($markerAddress->getMunicipality());

            $markerKey    = $markerAddress->getCountry();

            if (!empty($markerTitleTemplate))
              $markerTitle  = $markerAddress->parseTemplate($markerTitleTemplate);
            else
              $markerTitle  = $markerAddress->getCountry();

          break;

          case 7:

            $clusterMarkerAddress->setCountry($markerAddress->getCountry());
            $clusterMarkerAddress->setMunicipality($markerAddress->getMunicipality());
            $clusterMarkerAddress->setState($markerAddress->getState());

            $markerKey    = $markerAddress->getState();

            if (!empty($markerTitleTemplate))
              $markerTitle  = $markerAddress->parseTemplate($markerTitleTemplate);
            else
              $markerTitle  = $markerAddress->getState();

          break;

          case 8:
          case 9:
          case 10:
          case 11:

            $clusterMarkerAddress->setCountry($markerAddress->getCountry());
            $clusterMarkerAddress->setMunicipality($markerAddress->getMunicipality());
            $clusterMarkerAddress->setState($markerAddress->getState());
            $clusterMarkerAddress->setCity($markerAddress->getCity());

            $markerKey    = $markerAddress->getCity();

            if (!empty($markerTitleTemplate))
              $markerTitle  = $markerAddress->parseTemplate($markerTitleTemplate);
            else
              $markerTitle  = $markerAddress->getCity();

          break;

          case 12:
          case 13:

            $clusterMarkerAddress->setCountry($markerAddress->getCountry());
            $clusterMarkerAddress->setMunicipality($markerAddress->getMunicipality());
            $clusterMarkerAddress->setState($markerAddress->getState());
            $clusterMarkerAddress->setCity($markerAddress->getCity());
            $clusterMarkerAddress->setArea($markerAddress->getArea());

            $markerKey    = $markerAddress->getArea();

            if (!empty($markerTitleTemplate))
              $markerTitle  = $markerAddress->parseTemplate($markerTitleTemplate);
            else
              $markerTitle  = $markerAddress->getArea();

          break;

          case 14:

            $clusterMarkerAddress->setCountry($markerAddress->getCountry());
            $clusterMarkerAddress->setMunicipality($markerAddress->getMunicipality());
            $clusterMarkerAddress->setState($markerAddress->getState());
            $clusterMarkerAddress->setCity($markerAddress->getCity());
            $clusterMarkerAddress->setArea($markerAddress->getArea());
            $clusterMarkerAddress->setNeighbourhood($markerAddress->getNeighbourhood());

            $markerKey    = $markerAddress->getNeighbourhood();

            if (!empty($markerTitleTemplate))
              $markerTitle  = $markerAddress->parseTemplate($markerTitleTemplate);
            else
              $markerTitle  = $markerAddress->getNeighbourhood();

          break;

          case 15:

            $clusterMarkerAddress->setCountry($markerAddress->getCountry());
            $clusterMarkerAddress->setMunicipality($markerAddress->getMunicipality());
            $clusterMarkerAddress->setState($markerAddress->getState());
            $clusterMarkerAddress->setCity($markerAddress->getCity());
            $clusterMarkerAddress->setArea($markerAddress->getArea());
            $clusterMarkerAddress->setNeighbourhood($markerAddress->getNeighbourhood());
            $clusterMarkerAddress->setStreet($markerAddress->getStreet());

            $markerKey    = $markerAddress->getStreet();

            if (!empty($markerTitleTemplate))
              $markerTitle  = $markerAddress->parseTemplate($markerTitleTemplate);
            else
              $markerTitle  = $markerAddress->getStreet();

          break;

          case 16:

            $clusterMarkerAddress->setCountry($markerAddress->getCountry());
            $clusterMarkerAddress->setMunicipality($markerAddress->getMunicipality());
            $clusterMarkerAddress->setState($markerAddress->getState());
            $clusterMarkerAddress->setCity($markerAddress->getCity());
            $clusterMarkerAddress->setArea($markerAddress->getArea());
            $clusterMarkerAddress->setNeighbourhood($markerAddress->getNeighbourhood());
            $clusterMarkerAddress->setStreet($markerAddress->getStreet());
            $clusterMarkerAddress->setZipCode($markerAddress->getZipCode());

            $markerKey    = $markerAddress->getZipCode();

            if (!empty($markerTitleTemplate))
              $markerTitle  = $markerAddress->parseTemplate($markerTitleTemplate);
            else
              $markerTitle  = $markerAddress->getZipCode();

          break;

          case 17:
          case 18:
          case 19:
          case 20:

            // No clustering needed on this level (well maybe for later like multiple adressen in flats)

          break;

        }

        // Lowercase the markerKey in case we get different cases because if inconsistent databases.
        $markerKey        = strtolower($markerKey);

        if (!isset($clustered[$markerKey]))
        {
          $clusterMarker                = new SVZ_Solutions_Generic_Marker_Cluster();
          $clusterMarker->setTitle($markerTitle);
          $clusterMarker->setGeocode($marker->getGeocode());
          $clusterMarker->setAddress($clusterMarkerAddress);

          $clustered[$markerKey] = $clusterMarker;
        }

        $clustered[$markerKey]->addMarker($marker);

        $markers->next();
      }

      return $clustered;
    }

    /**
     * Method for clustering the current marker array by there distances
     *
     * @param array $markers
     * @param integer $distance
     * @return array
     */
    private function clusterByDistance($markers, $distance)
    {
      $viewPort = SVZ_Solutions_Generic_ViewPort::getInstance();

      $clustered = array();

      /* Loop until all markers have been compared. */

      $i = 1;

      $loopMarkers = clone($markers); // Clone the markers stack so we won't change the original

      while ($loopMarkers->valid())
      {
        //echo 'Loop number: ' . $i . ' markers left: ' . $markers->count() . '<br />';

        $marker = $loopMarkers->pop();

        $cluster = array();

        $listDataLoadUrl = $this->listDataLoadUrl;

        if ($marker->hasDataLoadUrl())
            $listDataLoadUrl = $marker->getDataLoadUrl(); // For marker list items, copy the right url

        if (in_array($viewPort->getZoomLevel(), array(17, 18, 19, 20)))
          $clusterMarker                = new SVZ_Solutions_Generic_Marker_List($listDataLoadUrl);
        else
          $clusterMarker                = new SVZ_Solutions_Generic_Marker_Cluster();

        $j = 1;

        foreach ($loopMarkers as $key => $value)
        {

          // echo '{' . $key;

          $pixels = SVZ_Solutions_Generic_Math::getDistanceInPixels($marker->getGeocode(), $value->getGeocode(), $viewPort->getZoomLevel());

          // echo '[' . $j . '] Pixelfs: ' . $pixels . '<br /><br />';

          // If two markers are closer than given distance remove
          // target marker from array and add it to cluster.
          if ($distance > $pixels)
          {
            $loopMarkers->removeByPosition($key);
            $clusterMarker->addMarker($value);
          }

          $j++;

          //echo '}';
        }

        //print_r($clusterMarker);

        $clusterMarker->setGeocode($marker->getGeocode());

        $clusterMarker->addMarker($marker);
        $clustered[] = $clusterMarker;

        /*
        echo '=====================<br />';
        echo 'Current main loop: [' . $i . '] Num searched in 2nd loop: [' . $j . '] Found and clustered: [' . $clusterMarker->count() . ']<br />';
        echo '=====================<br />';
        */

        $i++;

        $loopMarkers->rewind();
      }

      return $clustered;
    }
  }

?>