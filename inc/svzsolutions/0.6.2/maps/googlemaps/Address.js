/**
 * Copyright (c) 2010, SVZ Solutions All Rights Reserved.
 * Available via BSD license, see license file included for details.
 *
 * @title:                SVZ Solutions Address
 * @authors:           Stefan van Zanden <info@svzsolutions.nl>
 * @company:          SVZ Solutions
 * @contributers:
 * @version:          0.4
 * @versionDate:    2010-03-07
 * @date:             2010-03-07
 */
define("svzsolutions/maps/googlemaps/Address", [], function() {

/**
 * SVZ Loader class
 *
 */
dojo.declare('svzsolutions.maps.googlemaps.Address', null,
/** @lends svzsolutions.maps.googlemaps.Address.prototype */
{

    /**
   * Constructor
   *
   * @constructs
   * @param {String} address
   * @return {svzsolutions.maps.googlemaps.Address}
   */
    constructor: function(address)
  {
        this._address     = address;
        this._geocoder     = new google.maps.Geocoder();
  },

  /**
   * Tries to find a geocode with the provided address
   *
   * @param {Void}
   * @return {Void}
   */
  findGeocode: function()
  {
      var geocoderRequest = { 'address': this._address };

      this._geocoder.geocode(geocoderRequest, dojo.hitch(this, 'onGeocodeResult'));
  },

  /**
   * Method is fired whenever the find geocode has some results, needs to be overriden or connected to
   *
   * @event
   * @param {Object} results
   * @param {String} status
   * @return {Void}
   */
  onGeocodeResult: function(results, status)
  {
  }

});

});