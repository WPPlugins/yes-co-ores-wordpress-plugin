/**
 * Copyright (c) 2010, SVZ Solutions All Rights Reserved.
 * Available via BSD license, see license file included for details.
 *
 * @title:                SVZ Solutions Marker
 * @authors:           Stefan van Zanden <info@svzsolutions.nl>
 * @company:          SVZ Solutions
 * @contributers:
 * @version:          0.6
 * @versionDate:    2010-07-25
 * @date:             2010-07-25
 */
define("svzsolutions/generic/Marker", ["svzsolutions/generic/Geocode"], function() {

/**
 * Marker class
 */
dojo.declare('svzsolutions.generic.Marker', null,
/**
 * @lends svzsolutions.generic.Marker.prototype
 */
{

    /**
   * Constructor
   *
   * @constructs
   * @param {Object} config
   * @param {Object} map
   * @return {svzsolutions.generic.Marker}
   */
    constructor: function(config, map)
  {
        this._config    = config;
        this._map         = map;
        this._shown     = false;
  },

  /**
   * Method _getConfig which returns an object
   *
   * @param {String} libraryType Must be one off "googlemaps".
   * @return {Object}
   * @return {Boolean} In case of an invalid libraryType false is returned
   */
    _getConfig: function(libraryType)
    {
      if (libraryType == 'googlemaps')
      {
          var point              = new google.maps.LatLng(parseFloat(this._config.geoLat), parseFloat(this._config.geoLng));

          var config             = {};
          config.content         = (this._config.content ? this._config.content : '');
          config.map             = this._map;
          config.position        = point;
          config.title           = (this._config.title ? this._config.title : '');
          config.draggable       = (this._config.draggable && this._config.draggable === true ? true : false);
          config.dataLoadUrl     = (this._config.dataLoadUrl ? this._config.dataLoadUrl : '');
          config.showInfoWindow  = (this._config.showInfoWindow && this._config.showInfoWindow === true ? true : false);

          return config;
      }

      return false;
    },

    /**
   * Method getConfig which returns an object
   *
   * @param {String} libraryType Must be one off "googlemaps".
   * @return {Object}
   * @return {Boolean} In case of an invalid libraryType false is returned
   */
    getConfig: function(libraryType)
    {
        return this._getConfig(libraryType);
    },

    /**
   * Method setMarkerType which sets the marker type object
   *
   * @param {Object}
   * @return {Void}
   */
  setMarkerType: function(markerType)
  {
    this._config.markerType = markerType;
  },

    /**
   * Method getMarkerType which returns the marker type object
   *
   * @param {Void}
   * @return {Object}
   */
    getMarkerType: function()
    {
        return this._config.markerType;
    },

     /**
   * Method getMarkerTypeName which returns the marker type name
   *
   * @param {Void}
   * @return {String}
   */
  getMarkerTypeName: function()
  {
    return this._config.markerType.layerName;
  },

    /**
   * Method setPosition which sets the geo coordinates of the marker
   *
   * @param {svzsolutions.generic.Geocode} position
   * @return {Void}
   */
  setPosition: function(position)
  {
    this._config.geoLat = position.getLatitude();
    this._config.geoLng = position.getLongitude();

        var point   = new google.maps.LatLng(position.getLatitude(), position.getLongitude());

        if (this._overlay)
          this._overlay.setPosition(point);

  },

    /**
     * Method getPosition which returns the geo coordinates of the marker
     *
     * @param {Void}
     * @return {svzsolutions.generic.Geocode}
     */
    getPosition: function()
    {
        var point   = this._overlay.getPosition();
        var geocode = new svzsolutions.generic.Geocode(point.lat(), point.lng());

        return geocode;
    },

    /**
   * Method getEntityId which returns an the entityId attached to this marker
   *
   * @param {Void}
   * @return {Mixed}
   */
    getEntityId: function()
    {
        return (this._config.entityId ? this._config.entityId : '');
    },

    /**
     * Method which sets the overlay object of the map
     *
     * @param {Object} overlay Either of instance svzsolutions.maps.googlemaps.CustomOverlay or google.maps.Marker
     * @return {Void}
     */
    setOverlay: function(overlay)
    {
        this._overlay = overlay;
    },

    /**
   * Method which returns the overlay object
   *
   * @param {Void}
   * @return {Object} overlay Either of instance svzsolutions.maps.googlemaps.CustomOverlay or google.maps.Marker
   */
  getOverlay: function()
  {
    return this._overlay;
  },

    /**
     * Method which checks if this marker is shown on the map currently
     *
     * @param {Void}
     * @return {Boolean}
     */
    isShown: function()
    {
        return this._shown;
    },

    /**
     * Method which will show the marker on the map
     *
     * @param {Void}
     * @return {Void}
     */
    show: function()
    {
        if (!this.isShown() && this._overlay && this._overlay.setMap)
        {
            this._overlay.setMap(this._map);
            this._shown = true;
        }

    },

    /**
     * Method which will hide the marker from the map
     *
     * @param {Void}
     * @return {Void}
     */
    hide: function()
    {
        if (this._overlay && this._overlay.setMap)
        {
            this._overlay.setMap(null);
            this._shown = false;
        }
    },

    /**
     * Method which will destroy the marker from the map
     *
     * @param {Void}
     * @return {Void}
     */
    destroy: function()
    {
        this.hide();

        // Remove events etc..
    }

});

});