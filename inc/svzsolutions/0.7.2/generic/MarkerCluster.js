/**
 * Copyright (c) 2010, SVZ Solutions All Rights Reserved.
 * Available via BSD license, see license file included for details.
 *
 * @title:                SVZ Solutions MarkerCluster
 * @authors:           Stefan van Zanden <info@svzsolutions.nl>
 * @company:          SVZ Solutions
 * @contributers:
 * @version:          0.6
 * @versionDate:    2010-07-25
 * @date:             2010-07-25
 */
define("svzsolutions/generic/MarkerCluster", ["svzsolutions/generic/Marker"], function() {

/**
 * Marker Cluster class
 */
dojo.declare('svzsolutions.generic.MarkerCluster', svzsolutions.generic.Marker,
/** @lends svzsolutions.generic.MarkerCluster.prototype */
{

    /**
   * Constructor
   *
   * @constructs
   * @augments svzsolutions.generic.Marker
   * @param {Void}
   * @return {svzsolutions.generic.MarkerCluster}
   */
    constructor: function()
  {
  },

    /**
   * Method getConfig which returns an object
   *
   * @param {String} libraryType
   * @return {Object}
   */
    getConfig: function(libraryType)
    {
      var config                             = this._getConfig(libraryType);

      var markerCount                 = new String(this._config.count);

        config.className                = this._config.markerType.className;

        if (markerCount.length < 5)
            config.className += ' sg-marker-cluster-size-' + markerCount.length;
        else
            config.className += ' sg-marker-cluster-size-5';

        config.typeConfig           = this._config.markerType;
        config.smartNavigation     = this._config.smartNavigation;
        config.bounds                        = this._config.bounds;
        config.label                        = this._config.label;
        config.closestsMarkers    = this._config.closestsMarkers;

        return config;
    }

});

});