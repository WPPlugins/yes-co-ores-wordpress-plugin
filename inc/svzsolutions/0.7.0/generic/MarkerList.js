/**
 * Copyright (c) 2010, SVZ Solutions All Rights Reserved.
 * Available via BSD license, see license file included for details.
 *
 * @title:                SVZ Solutions MarkerList
 * @authors:           Stefan van Zanden <info@svzsolutions.nl>
 * @company:          SVZ Solutions
 * @contributers:
 * @version:          0.6
 * @versionDate:    2010-07-25
 * @date:             2010-07-25
 */
define("svzsolutions/generic/MarkerList", ["svzsolutions/generic/MarkerCluster"], function() {

/**
 * SVZ MarkerList class
 *
 */
dojo.declare('svzsolutions.generic.MarkerList', svzsolutions.generic.MarkerCluster,
/** @lends svzsolutions.generic.MarkerList.prototype */
{

    /**
   * Constructor
   *
   * @constructs
   * @augments svzsolutions.generic.MarkerCluster
   * @param {Void}
   * @return {svzsolutions.generic.MarkerList}
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
        var config                  = this._getConfig(libraryType);
        var markerCount             = new String(this._config.count);

        config.className            = this._config.markerType.className;

        if (markerCount.length < 5)
            config.className += ' sg-marker-list-size-' + markerCount.length;
        else
            config.className += ' sg-marker-list-size-5';

        config.typeConfig           = this._config.markerType;
        config.label                = this._config.label;
        config.entityIds            = this._config.entityIds;
        config.closestsMarkers      = this._config.closestsMarkers;

        /* BEGIN Temporary should be fixed in something more smart */
        this._config.dataLoadUrl += '?entityIds=';

        if (this._config.entityIds && this._config.entityIds.length > 0)
        {
            for (var j = 0; j < this._config.entityIds.length; j++)
            {
                this._config.dataLoadUrl += this._config.entityIds[j] + ',';
            }

            // Remove final ,
            this._config.dataLoadUrl = this._config.dataLoadUrl.replace(/,$/, '');
        }
        /* END Temporary should be fixed in something more smart */

        config.dataLoadUrl                = this._config.dataLoadUrl;

        return config;
    }

});

});