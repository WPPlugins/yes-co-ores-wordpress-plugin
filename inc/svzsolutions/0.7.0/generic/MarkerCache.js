/**
 * Copyright (c) 2010, SVZ Solutions All Rights Reserved.
 * Available via BSD license, see license file included for details.
 *
 * @title:                SVZ Solutions MarkerCache
 * @authors:           Stefan van Zanden <info@svzsolutions.nl>
 * @company:          SVZ Solutions
 * @contributers:
 * @version:          0.6.2
 * @versionDate:    2010-10-16
 * @date:             2010-10-16
 */
define("svzsolutions/generic/MarkerCache", [], function() {

/**
 * Marker Cache class
 */
dojo.declare('svzsolutions.generic.MarkerCache', null,
/** @lends svzsolutions.generic.MarkerCache.prototype */
{

  /**
   * Constructor
   *
   * @constructs
   * @param {Void}
   * @return {svzsolutions.generic.MarkerCache}
   */
  constructor: function()
  {
        this._zoomLevel = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21];
  },

  /**
   * Method hasByZoomLevel which checks if there is a marker cache available on this zoom level
   *
   * @param {Integer} zoomLevel
   * @return {Boolean}
   */
  hasByZoomLevel: function(zoomLevel)
  {
      if (this._zoomLevel[zoomLevel] && dojo.isObject(this._zoomLevel[zoomLevel]))
          return true;

      return false;
  },

  /**
   * Method getByZoomLevel which returns the marker cache on this zoom level
   *
   * @param {Integer} zoomLevel
   * @return {svzsolutions.generic.MarkerManager}
   */
  getByZoomLevel: function(zoomLevel)
  {
      return this._zoomLevel[zoomLevel];
  },

  /**
   * Method getByZoomLevel which returns the marker cache on this zoom level
   *
   * @param {Integer} zoomLevel
   * @param {svzsolutions.generic.MarkerManager}
   * @return {Void}
   */
  setByZoomLevel: function(zoomLevel, markerManager)
  {
      if (svzsolutions.global.mapManager.isDebugMode())
        console.log('Setting on the zoom level [', zoomLevel, ']');

      this._zoomLevel[zoomLevel] = markerManager;
  },

    /**
   * Method which will hide all the markers in all zoom levels
   *
   * @param {Void}
   * @return {Void}
   */
  hideAll: function()
  {
    console.log('MarkerCache: hiding all');

    for (var i = 1; i < 22; i++)
    {
      if (this.hasByZoomLevel(i))
      {
        if (i != zoomLevel)
        {
          console.log('Ok hiding for the markers on the zoom level [', i, ']');
          this.getByZoomLevel(i).hideDynamicMarkersFromMap();
        }
      }
    }

  },

  /**
   * Method which will hide all the markers in all zoom levels except for the current one
   *
   * @param {Integer} zoomLevel
   * @return {Void}
   */
  hideAllButZoomLevel: function(zoomLevel)
  {
      console.log('MarkerCache: hiding all but the provided zoom level [', zoomLevel, ']');

      for (var i = 1; i < 22; i++)
      {
          if (this.hasByZoomLevel(i))
          {
              if (i != zoomLevel)
              {
                  console.log('Ok hiding for the markers on the zoom level [', i, ']');
                  this.getByZoomLevel(i).hideDynamicMarkersFromMap();
              }
          }
      }

  },
  
  /**
   * Method clear which will clear the entire cache
   * 
   * @param {Void}
   * @return {Void}
   */
  clear: function()
  {
    for (var i = 1; i < 22; i++)
    {
      if (this.hasByZoomLevel(i))
      {
          this.getByZoomLevel(i).destroyDynamicMarkersFromMap();
          this.getByZoomLevel(i).destroyFixedMarkersFromMap();
          this.setByZoomLevel(i, null);
      }
    }
  }

});

});