<?php

  /**
   * Copyright (c) 2009, SVZ Solutions All Rights Reserved.
   * Available via BSD license, see license file included for details.
   *
   * @title:        SVZ Solutions Generic Marker Stack file
   * @authors:      Stefan van Zanden <info@svzsolutions.nl>
   * @company:      SVZ Solutions
   * @contributers:
   * @version:      0.1
   * @versionDate:  2009-10-17
   * @date:         2009-10-17
   */

  require_once('Marker.php');

  /**
   * SVZ_Solutions_Generic_Marker_Stack
   */
  class SVZ_Solutions_Generic_Marker_Stack implements Iterator
  {
    private $markers  = array();
    private $reindex  = false;
    private $_iterationUnsetFlag = false;

    /**
     * Constructor
     *
     * @param void
     * @return void
     */
    public function __construct()
    {
    }

    public function __unset($key)
    {
      unset($this->markers[$key]);
      //$this->_count = count($this->_data);
      $this->_iterationUnsetFlag = true;
    }

    /**
     * Method thats adds a marker to the marker stack
     *
     * @param SVZ_Solutions_Generic_Marker $marker
     * @return void
     */
    public function push(SVZ_Solutions_Generic_Marker $marker)
    {
      $this->markers[] = $marker;
    }

    /**
     * Method thats returns and removes a marker from the marker stack
     *
     * @param void
     * @return SVZ_Solutions_Generic_Marker
     */
    public function pop()
    {
      $marker = array_pop($this->markers);

      //echo 'Pop pointer @: [' . $this->key() . ']<br />';

      return $marker;
    }

    /**
     * Method thats returns the marker from the specified position
     *
     * @param integer $position
     * @return void
     */
    public function getByPosition($position)
    {
      return $this->markers[$position];
    }

    /**
     * Method thats removes a certain marker from the specified position
     *
     * @param integer $position
     * @return void
     */
    public function removeByPosition($position)
    {
      $this->__unset($position);
      $this->reindex = true;

      //$this->prev(); // Put the pointer back 1
    }

    public function rewind()
    {
      if ($this->reindex)
      {
        $this->markers = array_values($this->markers);
        $this->reindex = false;
      }

      reset($this->markers);

      //print_r(count($this->markers));
      //echo 'Rewind!';
    }

    public function valid()
    {
      /*$key = key($this->markers) !== null;
      echo 'Valid pointer @: [' . $key . ']<br />';
*/
      return key($this->markers) !== null;
    }

    public function current()
    {
      return current($this->markers);
    }

    public function key()
    {
      return key($this->markers);
    }

    public function next()
    {
      if ($this->_iterationUnsetFlag)
      {
        $this->_iterationUnsetFlag = false;
        return;
      }

      $next = next($this->markers);

      return $next;
    }

    public function prev()
    {
      return prev($this->markers);
    }

    public function count()
    {
        $total = 0;

        foreach ($this->markers as $marker)
        {
            if ($marker instanceof SVZ_Solutions_Generic_Marker_List || $marker instanceof SVZ_Solutions_Generic_Marker_Cluster)
                $total += $marker->count();
            else
                $total++;

        }

      return $total;
    }

    /**
     * Method which returns this marker as an array fro JSON responses usage
     *
     * @param void
     * @return array
     */
    public function toArray()
    {
      $array = array();

      foreach ($this->markers as $marker)
      {
        $array[] = $marker->toArray();
      }

      return $array;
    }
  }

?>