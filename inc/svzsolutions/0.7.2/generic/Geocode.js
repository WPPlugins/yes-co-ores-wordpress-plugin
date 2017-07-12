/**
 * Copyright (c) 2009, SVZ Solutions All Rights Reserved.
 * Available via BSD license, see license file included for details.
 *
 * @title:                SVZ Solutions Geocode
 * @author:             Stefan van Zanden <info@svzsolutions.nl>
 * @company:          SVZ Solutions
 * @contributers:
 * @version:          0.1
 * @versionDate:    2010-01-21
 * @date:             2010-01-21
 */
define("svzsolutions/generic/Geocode", [], function() {

/**
 * SVZ Geocode class
 */
dojo.declare('svzsolutions.generic.Geocode', null,
/**
 * @lends svzsolutions.generic.Geocode.prototype
 */
{
    /**
     * @constant
     * @default \u00B0
     */
    DMS_DEG_SIGN_UTF8: '\u00B0',

    /**
     * @constant
     * @default \u2032
     */
    DMS_MIN_SIGN_UTF8: '\u2032',

    /**
     * @constant
     * @default \u2033
     */
    DMS_SEC_SIGN_UTF8: '\u2033',

    /**
     * @constant
     * @default %deg%%deg-sign% %min%%min-sign% %sec%%sec-sign% %car-dir%
     */
    DMS_DEFAULT_FORMAT: '%deg%%deg-sign% %min%%min-sign% %sec%%sec-sign% %car-dir%',

    /**
   * Constructor
   *
   * @constructs
   * @param {Float} latitude
   * @param {Float} longitude
   * @return {svzsolutions.generic.Geocode}
   */
    constructor: function(latitude, longitude)
  {
        this._latitude     = latitude;
        this._longitude = longitude;
  },

  /**
   * Return the latitude
   *
   * @param {Void}
   * @return {Float}
   */
  getLatitude: function()
  {
      return this._latitude;
  },

  /**
   * Sets the latitude
   *
   * @param {Float}
   * @return {Void}
   */
  setLatitude: function(latitude)
  {
    this._latitude = latitude;
  },

  /**
   * Return the longitude
   *
   * @param {Void}
   * @return {Float}
   */
  getLongitude: function()
  {
      return this._longitude;
  },

  /**
   * Sets the longitude
   *
   * @param {Float}
   * @return {Void}
   */
  setLongitude: function(longitude)
  {
    this._longitude = longitude;
  },

  /**
   * Check if the latitude and longitude are both valid
   *
   * @param {Void}
   * @return {Boolean}
   */
  isValid: function()
  {
      if (this.isValidLatitude() && this.isValidLongitude())
          return true;

      return false;
  },

  /**
   * Check if the latitude is valid
   *
   * @param {Void}
   * @return {Boolean}
   */
  isValidLatitude: function()
  {
      if (isNaN(this._latitude))
          return false;

      if (this._latitude >= -90 && this._latitude <= 90)
          return true;

      return false;
  },

  /**
   * Check if the longitude is valid
   *
   * @param {Void}
   * @return {Boolean}
   */
  isValidLongitude: function()
  {
      if (isNaN(this._longitude))
          return false;

      if (this._longitude >= -180 && this._longitude <= 180)
          return true;

      return false;
  },

  /**
   * Converts a decimal value to a degree / minutes / seconds,
   * function inspired from http://andrew.hedges.name/experiments/convert_lat_long/
   *
   * @param {Float} decimal
   * @return {Array} Returns an array like this [degree: value, minutes: value, seconds: value]
   */
  toDMS: function(decimal)
  {
      if (decimal == '')
          return false;

      dms = [];

    parts = decimal.toString().split('.');

    // First part is the degree
    dms['degree'] = parts[0];

    // Minutes
    dmsRemainder = ('0.' + parts[1]) * 60;
    dmsRemainderParts = dmsRemainder.toString().split('.');
    dms['minutes'] = dmsRemainderParts[0];

    // Seconds
    dmsRemainder = ('0.' + dmsRemainderParts[1]) * 60;
    dms['seconds'] = Math.round(dmsRemainder);

      return dms;
  },

  /**
   * Returns the DMS of the latitude
   *
   * @param {String} format Can be formatted using the variables %deg% / %deg-sign% / %min% / %min-sign% / %sec% / %sec-sign% and or %car-dir%
   * @return {String} Will be using the format defined in the constant DMS_DEFAULT_FORMAT by default
   */
  getLatitudeInDMS: function(format)
  {
      if (!format)
          format = this.DMS_DEFAULT_FORMAT;

      var cardinalDirection = 'N';

    if (this._latitude.toString().substr(0, 1) == '-')
      cardinalDirection = 'S';

    var dmsArray = this.toDMS(this._latitude);

    dms = format.replace('%deg%', dmsArray['degree']);
    dms = dms.replace('%deg-sign%', this.DMS_DEG_SIGN_UTF8);
    dms = dms.replace('%min%', dmsArray['minutes']);
    dms = dms.replace('%min-sign%', this.DMS_MIN_SIGN_UTF8);
    dms = dms.replace('%sec%', dmsArray['seconds']);
    dms = dms.replace('%sec-sign%', this.DMS_SEC_SIGN_UTF8);
    dms = dms.replace('%car-dir%', cardinalDirection);

    return dms;
  },

  /**
   * Returns the DMS of the latitude
   *
   * @param {String} format Can be formatted using the variables %deg% / %deg-sign% / %min% / %min-sign% / %sec% / %sec-sign% and or %car-dir%
   * @return {String} Will be using the format defined in the constant DMS_DEFAULT_FORMAT by default
   */
  getLongitudeInDMS: function(format)
  {
      if (!format)
          format = this.DMS_DEFAULT_FORMAT;

      var cardinalDirection = 'E';

    if (this._longitude.toString().substr(0, 1) == '-')
      cardinalDirection = 'W';

    var dmsArray = this.toDMS(this._longitude);

    dms = format.replace('%deg%', dmsArray['degree']);
    dms = dms.replace('%deg-sign%', this.DMS_DEG_SIGN_UTF8);
    dms = dms.replace('%min%', dmsArray['minutes']);
    dms = dms.replace('%min-sign%', this.DMS_MIN_SIGN_UTF8);
    dms = dms.replace('%sec%', dmsArray['seconds']);
    dms = dms.replace('%sec-sign%', this.DMS_SEC_SIGN_UTF8);
    dms = dms.replace('%car-dir%', cardinalDirection);

    return dms;
  },

  /**
   * Method which extends this object with the users coordinates if they accept it and fires onRetrieveUserLocationResult.
   *
   * @param {Void}
   * @return {Void}
   */
  retrieveUserLocation: function()
  {
      if (navigator.geolocation)
        {
            navigator.geolocation.getCurrentPosition(
                dojo.hitch(this, function (position)
                {
                    this._latitude     = position.coords.latitude;
                    this._longitude = position.coords.longitude;
                    this.onRetrieveUserLocationResult();
                }),
                dojo.hitch(this, function (error)
                {
                    console.log('Geocode: error while retrieving user location: [', error, ']');
                    this.onRetrieveUserLocationError(error);
                })
            );
        }
  },

  /**
   * Fired after a users location has been succesfully retrieved
   *
   * @event
   * @param {Void}
   * @return {Void}
   */
  onRetrieveUserLocationResult: function()
  {

  },

  /**
   * Fired after a users location has not been succesfully retrieved
   *
   * @event
   * @param {string} error
   * @return {Void}
   */
  onRetrieveUserLocationError: function(error)
  {

  },

  /**
   * Tries to find a address with the provided geocode
   * @TODO: place in a seperate object withing the googlemaps directory
   *
   * @param {Void}
   * @return {Mixed}
   */
  findAddress: function()
  {
      if (!this._geocoder)
          this._geocoder     = new google.maps.Geocoder();

      var geocode = this.getGoogleMapsPoint();

      var geocoderRequest = { 'latLng': geocode };

      this._geocoder.geocode(geocoderRequest, dojo.hitch(this, 'onAddressResult'));
  },

  /**
   * Tries to find addresses within a given bound
   * @TODO: place in a seperate object withing the googlemaps directory
   *
   * @param {Void}
   * @return {Mixed}
   */
  findAddresses: function()
  {
      if (!this._geocoder)
          this._geocoder     = new google.maps.Geocoder();

      var geocode                 = this.getGoogleMapsPoint();
      var geocode2                 = new google.maps.LatLng(this._latitude + 10, this._longitude + 10);

      var bounds                     = new google.maps.LatLngBounds(geocode, geocode2);

      var geocoderRequest = { 'bounds': bounds };

        if (svzsolutions.global.mapManager.isDebugMode())
       console.log('GeocoderRequest [', geocoderRequest, ']');

      this._geocoder.geocode(geocoderRequest, dojo.hitch(this, 'onAddressesResult'));
  },

    /**
     * Method getGoogleMapsPoint which returns this geocode position in a google maps object
     *
     * @param {Void}
     * @return {google.maps.LatLng}
     */
    getGoogleMapsPoint: function()
    {
        var point   = new google.maps.LatLng(this.getLatitude(), this.getLongitude());

        return point;
    },

    /**
   * Method setGoogleMapsPoint which will set geocode position from a google maps object
   *
   * @param {google.maps.LatLng} point
   * @return {Void}
   */
  setGoogleMapsPoint: function(point)
  {
    this.setLatitude(point.lat());
    this.setLongitude(point.lng());
  },

  /**
   * Fired after findAddress receives a result
   *
   * @event
   * @param {Object} results
   * @param {String} status
   * @return {Mixed}
   */
  onAddressResult: function(results, status)
  {

  },

  /**
   * Fired after findAddresses receives a result
   *
   * @event
   * @param {Object} results
   * @param {String} status
   * @return {Mixed}
   */
  onAddressesResult: function(results, status)
  {

  }

});

});