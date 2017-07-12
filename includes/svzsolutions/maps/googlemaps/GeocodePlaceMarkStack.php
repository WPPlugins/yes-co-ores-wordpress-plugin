<?php

  /**
   * Copyright (c) 2010, SVZ Solutions All Rights Reserved.
   * Available via BSD license, see license file included for details.
   *
   * @title:        SVZ Solutions Google Maps Geocode Place Mark Stack file
   * @authors:      Stefan van Zanden <info@svzsolutions.nl>
   * @company:      SVZ Solutions
   * @contributers:
   * @version:      0.4
   * @versionDate:  2010-03-06
   * @date:         2010-03-06
   */

  require_once(dirname(__FILE__) . '/GeocodePlaceMark.php');

  /**
   * SVZ_Solutions_Maps_Google_Maps_Geocode_Place_Mark_Stack class
   *
   */
  class SVZ_Solutions_Maps_Google_Maps_Geocode_Place_Mark_Stack implements Iterator
  {
    private $placeMarks           = array();
    private $reindex              = false;
    private $_iterationUnsetFlag  = false;

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
     * Method which returns if there are any placemarks
     *
     * @param SVZ_Solutions_Maps_Google_Maps_Geocode_Place_Mark $placeMark
     * @return void
     */
    public function add(SVZ_Solutions_Maps_Google_Maps_Geocode_Place_Mark $placeMark)
    {
      $this->placeMarks[] = $placeMark;
    }

    public function __unset($key)
    {
      unset($this->placeMarks[$key]);
      $this->_iterationUnsetFlag = true;
    }

    /**
     * Method thats adds a placemark to the placemarker stack
     *
     * @param SVZ_Solutions_Maps_Google_Maps_Geocode_Place_Mark $placeMark
     * @return void
     */
    public function push(SVZ_Solutions_Maps_Google_Maps_Geocode_Place_Mark $placeMark)
    {
      $this->placeMarks[] = $placeMark;
    }

    /**
     * Method thats returns and removes a placeMark from the placeMark stack
     *
     * @param void
     * @return SVZ_Solutions_Maps_Google_Maps_Geocode_Place_Mark
     */
    public function pop()
    {
      $placeMark = array_pop($this->placeMarks);

      return $placeMark;
    }

    /**
     * Method thats returns the placeMark from the specified position
     *
     * @param integer $position
     * @return void
     */
    public function getByPosition($position)
    {
      return $this->placeMarks[$position];
    }

    /**
     * Method thats removes a certain placemark from the specified position
     *
     * @param integer $position
     * @return void
     */
    public function removeByPosition($position)
    {
      $this->__unset($position);
      $this->reindex = true;
    }

    public function rewind()
    {
      if ($this->reindex)
      {
        $this->placeMarks = array_values($this->placeMarks);
        $this->reindex    = false;
      }

      reset($this->placeMarks);
    }

    public function valid()
    {
      return key($this->placeMarks) !== null;
    }

    public function current()
    {
      return current($this->placeMarks);
    }

    public function key()
    {
      return key($this->placeMarks);
    }

    public function next()
    {
      if ($this->_iterationUnsetFlag)
      {
        $this->_iterationUnsetFlag = false;
        return;
      }

      $next = next($this->placeMarks);

      return $next;
    }

    public function prev()
    {
      return prev($this->placeMarks);
    }

    public function count()
    {
      return count($this->placeMarks);
    }

  }

?>