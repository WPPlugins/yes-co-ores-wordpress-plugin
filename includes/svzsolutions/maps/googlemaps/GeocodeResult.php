<?php

  /**
   * Copyright (c) 2010, SVZ Solutions All Rights Reserved.
   * Available via BSD license, see license file included for details.
   *
   * @title:        SVZ Solutions Google Maps Geocode Result file
   * @authors:      Stefan van Zanden <info@svzsolutions.nl>
   * @company:      SVZ Solutions
   * @contributers:
   * @version:      0.4
   * @versionDate:  2010-03-06
   * @date:         2010-03-06
   */

  require_once(dirname(__FILE__) . '/GeocodePlaceMarkStack.php');

  /**
   * SVZ_Solutions_Maps_Google_Maps_Geocode_Result class
   *
   */
  class SVZ_Solutions_Maps_Google_Maps_Geocode_Result
  {
    const RESULT_CODE_OK                = 'OK';
    const RESULT_CODE_ZERO_RESULTS      = 'ZERO_RESULTS';
    const RESULT_CODE_OVER_QUERY_LIMIT  = 'OVER_QUERY_LIMIT';
    const RESULT_CODE_REQUEST_DENIED    = 'REQUEST_DENIED';
    const RESULT_CODE_INVALID_REQUEST   = 'INVALID_REQUEST';
    private $result       = null;
    private $resultCode   = null;
    private $searchQuery  = '';
    private $placeMarkers = null;

    /**
     * Constructor
     *
     * @param StdClass $result
     * @return void
     */
    public function __construct()
    {
      $this->placeMarkers = new SVZ_Solutions_Maps_Google_Maps_Geocode_Place_Mark_Stack();
    }

    /**
     * Method which sets the result that comes from the Google Geocoding service
     *
     * @param StdClass $result
     * @return void
     */
    public function setResult($result)
    {
      if (!is_object($result))
        throw new Exception(__METHOD__ . '; Invalid result, not an object');

      $this->result     = $result;

      $this->resultCode = $result->status;

      if ($this->resultCode == self::RESULT_CODE_OK)
      {
        if ($result->results)
        {
          foreach ($result->results as $placeMarkTemp)
          {
            $placeMark      = new SVZ_Solutions_Maps_Google_Maps_Geocode_Place_Mark();

            if (!empty($placeMarkTemp->geometry))
            {
              $geometry       = $placeMarkTemp->geometry;

              if (!empty($geometry->location))
              {
                $longitude      = $geometry->location->lng;
                $latitude       = $geometry->location->lat;

                $geocode        = new SVZ_Solutions_Generic_Geocode($latitude, $longitude);

                $placeMark->setGeocode($geocode);
              }
            }

            $address        = new SVZ_Solutions_Generic_Address();

            if (!empty($placeMarkTemp->address_components))
            {
              foreach ($placeMarkTemp->address_components as $addressComponent)
              {
                foreach ($addressComponent->types as $type)
                {
                  switch ($type)
                  {
                    case 'street_number':


                        $thoroughFareName = $addressComponent->long_name;

                        $explode          = explode(' ', $thoroughFareName);

                        foreach ($explode as $part)
                        {
                          $grepMatches = array();

                          if (preg_match('/^(\d+)\-(\d+)$/', $part, $grepMatches))
                          {
                            $address->setHouseNumberStart((int)$grepMatches[1]);
                            $address->setHouseNumberEnd((int)$grepMatches[2]);
                          }
                          else if (is_numeric($part))
                          {
                            $address->setHouseNumber((int)$part);
                          }
                          else
                          {
                            $street = $address->getStreet();
                            $street .= $street . ' ' . $part;
                            $address->setStreet($street);
                          }
                        }

                      break;

                   case 'street_address':

                        $address->setStreet($addressComponent->long_name);

                      break;

                   case 'route':

                        if ($address->hasStreet())
                          $address->setStreet($addressComponent->long_name);

                      break;

                   case 'locality':

                        $address->setCity($addressComponent->long_name);

                      break;

                   case 'neighbourhood':

                        $address->setNeighbourhood($addressComponent->long_name);

                      break;

                   case 'administrative_area_level_1':

                        $address->setState($addressComponent->long_name);

                      break;

                   case 'country':

                        $address->setCountry($addressComponent->long_name);
                        $address->setCountryCode($addressComponent->short_name);

                      break;

                   case 'postal_code':

                        $address->setZipcode($addressComponent->long_name);

                      break;

                  }
                }
              }
            }

            $placeMark->setAddress($address);
            //$placeMark->setAccuracy((int)$placeMarkTemp->AddressDetails->Accuracy);

            $this->placeMarkers->add($placeMark);

          }
        }
      }
    }

    /**
     * Method which returns if the result has any place markers
     *
     * @param void
     * @return boolean
     */
    public function hasPlaceMarks()
    {
      return ($this->placeMarkers->count() > 0);
    }

    /**
     * Method which returns a stack of place marks found in the result
     *
     * @param void
     * @return SVZ_Solutions_Maps_Google_Maps_Geocode_Place_Marks_Stack
     */
    public function getPlaceMarks()
    {
      return $this->placeMarkers;
    }

    /**
     * Method which sets the search query
     *
     * @param string $searchQuery
     * @return void
     */
    public function setSearchQuery($searchQuery)
    {
      if (!is_string($searchQuery) || empty($searchQuery))
        throw new Exception(__METHOD__ . '; Invalid searchQuery, not an string or empty');

      $this->searchQuery = $searchQuery;
    }

    /**
     * Method which gets the search query
     *
     * @param void
     * @return string
     */
    public function getSearchQuery()
    {
     return $this->searchQuery;
    }

    /**
     * Method which returns the result code
     *
     * @param void
     * @return integer
     */
    public function getResultCode()
    {
      return $this->resultCode;
    }

    /**
     * Method which returns the result message
     *
     * @param void
     * @return integer
     */
    public function getResultMessage()
    {
      $message = '';

      switch ($this->resultCode)
      {
        case self::RESULT_CODE_OK:

          $message  = 'No errors occurred; the address was successfully parsed and its geocode was returned.';

        break;

        case self::RESULT_CODE_ZERO_RESULTS:

          $message  = 'Geocode was succesfully but there where no results found.';

        break;

        case self::RESULT_CODE_OVER_QUERY_LIMIT:

          $message  = 'Geocoding failed, query limit reached.';

        break;

        case self::RESULT_CODE_REQUEST_DENIED:

          $message  = 'Geocoding failed, request was denied.';

        break;

        case self::RESULT_CODE_INVALID_REQUEST:

          $message  = 'Geocoding failed, the provided query is missing.'; // Should never come here btw..

        break;
      }

      return $message;
    }

  }

?>