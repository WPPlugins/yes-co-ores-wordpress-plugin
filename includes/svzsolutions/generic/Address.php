<?php

  /**
   * Copyright (c) 2009, SVZ Solutions All Rights Reserved.
   * Available via BSD license, see license file included for details.
   *
   * @title:        SVZ Solutions Generic Address File
   * @authors:      Stefan van Zanden <info@svzsolutions.nl>
   * @company:      SVZ Solutions
   * @contributers:
   * @version:      0.1
   * @versionDate:  2009-10-17
   * @date:         2009-10-17
   */

  /**
   * SVZ_Solutions_Generic_Address class
   *
   */
  class SVZ_Solutions_Generic_Address
  {
    private $country              = '';
    private $countryCode          = '';
    private $municipality         = '';
    private $state                = '';
    private $city                 = '';
    private $area                 = '';
    private $neighbourhood        = '';
    private $street               = '';
    private $zipCode              = '';
    private $houseNumber          = '';
    private $houseNumberStart     = '';
    private $houseNumberEnd       = '';
    private $houseNumberAddition  = '';

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
     * Method for setting the Country
     *
     * @param string $country
     * @return void
     */
    public function setCountry($country)
    {
      if (!is_string($country))
        throw new Exception(__METHOD__ . '; Invalid $country, not a string');

      $this->country = $country;
    }

    /**
     * Method for getting the Country
     *
     * @param void
     * @return string
     */
    public function getCountry()
    {
      return $this->country;
    }

    /**
     * Method for checking if the Country is set
     *
     * @param void
     * @return boolean
     */
    public function hasCountry()
    {
      return !empty($this->country) ? true : false;
    }

    /**
     * Method for setting the CountryCode
     *
     * @param string $countryCode
     * @return void
     */
    public function setCountryCode($countryCode)
    {
      if (!is_string($countryCode))
        throw new Exception(__METHOD__ . '; Invalid $countryCode, not a string');

      $this->countryCode = $countryCode;
    }

    /**
     * Method for getting the CountryCode
     *
     * @param void
     * @return string
     */
    public function getCountryCode()
    {
      return $this->countryCode;
    }

    /**
     * Method for checking if the CountryCode is set
     *
     * @param void
     * @return boolean
     */
    public function hasCountryCode()
    {
      return !empty($this->countryCode) ? true : false;
    }

    /**
     * Method for setting the Municipality
     *
     * @param string $municipality
     * @return void
     */
    public function setMunicipality($municipality)
    {
      if (!is_string($municipality))
        throw new Exception(__METHOD__ . '; Invalid $municipality, not a string');

      $this->municipality = $municipality;
    }

    /**
     * Method for getting the Municipality
     *
     * @param void
     * @return string
     */
    public function getMunicipality()
    {
      return $this->municipality;
    }

    /**
     * Method for checking if the Municipality is set
     *
     * @param void
     * @return boolean
     */
    public function hasMunicipality()
    {
      return !empty($this->municipality) ? true : false;
    }

    /**
     * Method for setting the State
     *
     * @param string $state
     * @return void
     */
    public function setState($state)
    {
      if (!is_string($state))
        throw new Exception(__METHOD__ . '; Invalid $state, not a string');

      $this->state = $state;
    }

    /**
     * Method for getting the State
     *
     * @param void
     * @return string
     */
    public function getState()
    {
      return $this->state;
    }

    /**
     * Method for checking if the State is set
     *
     * @param void
     * @return boolean
     */
    public function hasState()
    {
      return !empty($this->state) ? true : false;
    }

    /**
     * Method for setting the City
     *
     * @param string $city
     * @return void
     */
    public function setCity($city)
    {
      if (!is_string($city))
        throw new Exception(__METHOD__ . '; Invalid $city, not a string');

      $this->city = $city;
    }

    /**
     * Method for getting the City
     *
     * @param void
     * @return string
     */
    public function getCity()
    {
      return $this->city;
    }

    /**
     * Method for checking if the City is set
     *
     * @param void
     * @return boolean
     */
    public function hasCity()
    {
      return !empty($this->city) ? true : false;
    }

    /**
     * Method for setting the Area
     *
     * @param string $area
     * @return void
     */
    public function setArea($area)
    {
      if (!is_string($area))
        throw new Exception(__METHOD__ . '; Invalid $area, not a string');

      $this->area = $area;
    }

    /**
     * Method for getting the Area
     *
     * @param void
     * @return string
     */
    public function getArea()
    {
      return $this->area;
    }

    /**
     * Method for checking if the Area is set
     *
     * @param void
     * @return boolean
     */
    public function hasArea()
    {
      return !empty($this->area) ? true : false;
    }

    /**
     * Method for setting the Neighbourhood
     *
     * @param string $neighbourhood
     * @return void
     */
    public function setNeighbourhood($neighbourhood)
    {
      if (!is_string($neighbourhood))
        throw new Exception(__METHOD__ . '; Invalid $neighbourhood, not a string');

      $this->neighbourhood = $neighbourhood;
    }

    /**
     * Method for getting the Neighbourhood
     *
     * @param void
     * @return string
     */
    public function getNeighbourhood()
    {
      return $this->neighbourhood;
    }

    /**
     * Method for checking if the Neighbourhood is set
     *
     * @param void
     * @return boolean
     */
    public function hasNeighbourhood()
    {
      return !empty($this->neighbourhood) ? true : false;
    }

    /**
     * Method for setting the Street
     *
     * @param string $street
     * @return void
     */
    public function setStreet($street)
    {
      if (!is_string($street))
        throw new Exception(__METHOD__ . '; Invalid $street, not a string');

      $this->street = $street;
    }

    /**
     * Method for getting the Street
     *
     * @param void
     * @return string
     */
    public function getStreet()
    {
      return $this->street;
    }

    /**
     * Method for checking if the Street is set
     *
     * @param void
     * @return boolean
     */
    public function hasStreet()
    {
      return !empty($this->street) ? true : false;
    }

    /**
     * Method for setting the ZipCode
     *
     * @param string $zipCode
     * @return void
     */
    public function setZipCode($zipCode)
    {
      if (!is_string($zipCode))
        throw new Exception(__METHOD__ . '; Invalid $zipCode, not a string');

      $this->zipCode = $zipCode;
    }

    /**
     * Method for getting the ZipCode
     *
     * @param void
     * @return string
     */
    public function getZipCode()
    {
      return $this->zipCode;
    }

    /**
     * Method for checking if the ZipCode is set
     *
     * @param void
     * @return boolean
     */
    public function hasZipCode()
    {
      return !empty($this->zipCode) ? true : false;
    }

    /**
     * Method for setting the HouseNumber
     *
     * @param int $houseNumber
     * @return void
     */
    public function setHouseNumber($houseNumber)
    {
      if (!is_int($houseNumber))
        throw new Exception(__METHOD__ . '; Invalid $houseNumber, not a integer');

      $this->houseNumber = $houseNumber;
    }

    /**
     * Method for getting the HouseNumber
     *
     * @param void
     * @return integer
     */
    public function getHouseNumber()
    {
      return $this->houseNumber;
    }

    /**
     * Method for checking if the HouseNumber is set
     *
     * @param void
     * @return boolean
     */
    public function hasHouseNumber()
    {
      return ($this->houseNumber != '') ? true : false;
    }

    /**
     * Method for setting the HouseNumberStart
     *
     * @param int $houseNumberStart
     * @return void
     */
    public function setHouseNumberStart($houseNumberStart)
    {
      if (!is_int($houseNumberStart))
        throw new Exception(__METHOD__ . '; Invalid $houseNumberStart, not a integer');

      $this->houseNumberStart = $houseNumberStart;
    }

    /**
     * Method for getting the HouseNumberStart
     *
     * @param void
     * @return integer
     */
    public function getHouseNumberStart()
    {
      return $this->houseNumberStart;
    }

    /**
     * Method for checking if the HouseNumberStart is set
     *
     * @param void
     * @return boolean
     */
    public function hasHouseNumberStart()
    {
      return ($this->houseNumberStart != '') ? true : false;
    }

    /**
     * Method for setting the HouseNumberEnd
     *
     * @param int $houseNumberEnd
     * @return void
     */
    public function setHouseNumberEnd($houseNumberEnd)
    {
      if (!is_int($houseNumberEnd))
        throw new Exception(__METHOD__ . '; Invalid $houseNumberEnd, not a integer');

      $this->houseNumberEnd = $houseNumberEnd;
    }

    /**
     * Method for getting the HouseNumberEnd
     *
     * @param void
     * @return integer
     */
    public function getHouseNumberEnd()
    {
      return $this->houseNumberEnd;
    }

    /**
     * Method for checking if the HouseNumberEnd is set
     *
     * @param void
     * @return boolean
     */
    public function hasHouseNumberEnd()
    {
      return ($this->houseNumberEnd != '') ? true : false;
    }

    /**
     * Method for setting the HouseNumberAddition
     *
     * @param string $houseNumberAddition
     * @return void
     */
    public function setHouseNumberAddition($houseNumberAddition)
    {
      if (!is_string($houseNumberAddition))
        throw new Exception(__METHOD__ . '; Invalid $houseNumberAddition, not a string');

      $this->houseNumberAddition = $houseNumberAddition;
    }

    /**
     * Method for getting the HouseNumberAddition
     *
     * @param void
     * @return string
     */
    public function getHouseNumberAddition()
    {
      return $this->houseNumberAddition;
    }

    /**
     * Method for checking if the HouseNumberAddition is set
     *
     * @param void
     * @return boolean
     */
    public function hasHouseNumberAddition()
    {
      return !empty($this->houseNumberAddition) ? true : false;
    }

    /**
     * Method thats sets the object retrieving information from an array
     *
     * @param void
     * @return array
     */
    public function fromArray($array)
    {
      $result = false;

      if (is_array($array))
      {
        if (isset($array['country']))
          $this->setCountry($array['country']);

        if (isset($array['countryCode']))
          $this->setCountryCode($array['countryCode']);

        if (isset($array['municipality']))
          $this->setMunicipality($array['municipality']);

        if (isset($array['state']))
          $this->setState($array['state']);

        if (isset($array['city']))
          $this->setCity($array['city']);

        if (isset($array['area']))
          $this->setArea($array['area']);

        if (isset($array['neighbourhood']))
          $this->setNeighbourhood($array['neighbourhood']);

        if (isset($array['street']))
          $this->setStreet($array['street']);

        if (isset($array['zipcode']))
          $this->setZipCode($array['zipcode']);

        if (isset($array['housenumber']))
          $this->setHouseNumber((int)$array['housenumber']);

        if (isset($array['housenumberStart']))
          $this->setHouseNumberStart((int)$array['housenumberStart']);

        if (isset($array['housenumberEnd']))
          $this->setHouseNumberEnd((int)$array['housenumberEnd']);

        if (isset($array['housenumber_addition']))
          $this->setHouseNumberAddition($array['housenumber_addition']);

        $result = true;
      }


      return $result;
    }

    /**
     * Method thats gives back an array with address information
     *
     * @param void
     * @return array
     */
    public function toArray()
    {
      $array = array();

      if ($this->hasCountry())
        $array['country']               = $this->getCountry();

      if ($this->hasCountryCode())
        $array['countryCode']           = $this->getCountryCode();

      if ($this->hasMunicipality())
        $array['municipality']          = $this->getMunicipality();

      if ($this->hasState())
        $array['state']                 = $this->getState();

      if ($this->hasCity())
        $array['city']                  = $this->getCity();

      if ($this->hasArea())
        $array['area']                  = $this->getArea();

      if ($this->hasNeighbourhood())
        $array['neighbourhood']         = $this->getNeighbourhood();

      if ($this->hasStreet())
        $array['street']                = $this->getStreet();

      if ($this->hasZipCode())
        $array['zipcode']               = $this->getZipCode();

      if ($this->hasHouseNumber())
        $array['housenumber']           = $this->getHouseNumber();

      if ($this->hasHouseNumberStart())
        $array['housenumberStart']      = $this->getHouseNumberStart();

      if ($this->hasHouseNumberEnd())
        $array['housenumberEnd']        = $this->getHouseNumberEnd();

      if ($this->hasHouseNumberAddition())
        $array['housenumber_addition']  = $this->getHouseNumberAddition();

      return $array;
    }

    /**
     * Method thats provides a template with the right variables
     *
     * @param string $template
     * @return string
     */
    public function parseTemplate($template)
    {
      if (!is_string($template))
        throw new Exception(__METHOD__ . '; Invalid template, not a string');

      $addressArray = $this->toArray();

      preg_match_all('(%(/?[^%]+)%)', $template, $templateParts);

      if ($templateParts[1])
      {
        foreach ($templateParts[1] as $key => $value)
        {
          if (preg_match('/@/', $value))
          {
            $exploded = explode('@', $value);

            $replaceValue = '';

            if (isset($addressArray[$exploded[1]]))
              $replaceValue = $exploded[0] . $addressArray[$exploded[1]];

            $template = str_replace('%' . $exploded[0] . '@' . $exploded[1] . '%', $replaceValue, $template);
          }
          else
          {
            $replaceValue = '';

            if (isset($addressArray[$value]))
              $replaceValue = $addressArray[$value];

            $template = str_replace('%' . $value . '%', $replaceValue, $template);
          }
        }
      }

      return $template;
    }

  }

?>