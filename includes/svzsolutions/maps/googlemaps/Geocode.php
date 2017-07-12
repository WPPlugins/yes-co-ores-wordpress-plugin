<?php

  /**
   * Copyright (c) 2010, SVZ Solutions All Rights Reserved.
   * Available via BSD license, see license file included for details.
   *
   * @title:        SVZ Solutions Google Maps Geocode file
   * @authors:      Stefan van Zanden <info@svzsolutions.nl>
   * @company:      SVZ Solutions
   * @contributers:
   * @version:      0.1
   * @versionDate:  2009-10-17
   * @date:         2009-10-17
   */

  require_once(dirname(__FILE__) . '/../../generic/Address.php');
  require_once(dirname(__FILE__) . '/../../generic/Geocode.php');
  require_once(dirname(__FILE__) . '/GeocodeResult.php');

  /**
   * SVZ_Solutions_Google_Maps_Geocode class
   *
   */
  class SVZ_Solutions_Maps_Google_Maps_Geocode
  {
    private $address = null;
    private $geocode = null;

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
     * Set the address to retrieve the geocode from
     *
     * @param SVZ_Solutions_Generic_Address $address
     * @return void
     */
    public function setAddress(SVZ_Solutions_Generic_Address $address)
    {
      $this->address = $address;
    }

    /**
     * Set the geocodes to retrieve the address from
     *
     * @param SVZ_Solutions_Generic_Geocode $geocode
     * @return void
     */
    public function setGeocode(SVZ_Solutions_Generic_Geocode $geocode)
    {
      $this->geocode = $geocode;
    }

    /**
     * Method which retrieves the info by address or geocodes
     *
     * @param void
     * @return mixed
     */
    public function retrieve()
    {
      $return = new SVZ_Solutions_Maps_Google_Maps_Geocode_Result();

      $query  = '';

      if (!is_null($this->address))
        $query = '&address=' . $this->getSearchQueryByAddress();
      else if (!is_null($this->geocode))
        $query = '&latlng=' . $this->getSearchQueryByGeocode();

      if (empty($query))
        throw new Exception(__METHOD__ . '; Invalid query, is empty');

      $return->setSearchQuery($query);

      $restUrl = 'http://maps.google.com/maps/api/geocode/json?sensor=false' . $query;

      $response = file_get_contents($restUrl);

      if ($response)
      {
        $result = json_decode($response);

        if ($result)
          $return->setResult($result);

      }

      return $return;
    }

    /**
     * Retrieve the search query of the provided address
     *
     * @param void
     * @return string
     */
    public function getSearchQueryByAddress()
    {
      $searchQuery = '';

      if (is_null($this->address))
        throw new Exception(__METHOD__ . '; Invalid request, no address are provided');

      $address = '';

      // Form the call
      if ($this->address->hasCountry())
      {
        $address .= urlencode($this->address->getCountry());

        if ($this->address->hasMunicipality())
          $address .= ',' . urlencode($this->address->getMunicipality());

        if ($this->address->hasState())
          $address .= ',' . urlencode($this->address->getState());

        if ($this->address->hasCity())
          $address .= ',' . urlencode($this->address->getCity());

        if ($this->address->hasStreet())
          $address .= ',' . urlencode($this->address->getStreet());

        if ($this->address->hasHouseNumber())
          $address .= ',' . $this->address->getHouseNumber();

        if ($this->address->hasHouseNumberAddition())
          $address .= '+' . $this->address->getHouseNumberAddition();

        if ($this->address->hasZipCode())
          $address .= ',' . urlencode($this->address->getZipCode());

        if ($this->address->hasNeighbourhood())
          $address .= ',' . urlencode($this->address->getNeighbourhood());

        if ($this->address->hasArea())
          $address .= ',' . urlencode($this->address->getArea());

      }

      if (!empty($address))
      {
        $address = str_replace(' ', '%20', $address);

        $searchQuery = $address;
      }

      return $searchQuery;
    }

    /**
     * Retrieve the search query of the provided address
     *
     * @param void
     * @return string
     */
    public function getSearchQueryByGeocode()
    {
      $searchQuery = '';

      if (is_null($this->geocode))
        throw new Exception(__METHOD__ . '; Invalid request, no geocodes are provided');

      $searchQuery = $this->geocode->getLatitude() . ',' . $this->geocode->getLongitude();

      return $searchQuery;
    }

  }

?>