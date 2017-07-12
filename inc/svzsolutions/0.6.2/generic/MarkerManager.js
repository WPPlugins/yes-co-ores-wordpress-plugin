/**
 * Copyright (c) 2010, SVZ Solutions All Rights Reserved.
 * Available via BSD license, see license file included for details.
 *
 * @title:                SVZ Solutions MarkerManager
 * @authors:           Stefan van Zanden <info@svzsolutions.nl>
 * @company:          SVZ Solutions
 * @contributers:
 * @version:          0.4
 * @versionDate:    2010-03-21
 * @date:             2010-03-21
 */
define("svzsolutions/generic/MarkerManager", [], function() {

/**
 * SVZ Loader class
 *
 */
dojo.declare('svzsolutions.generic.MarkerManager', null,
/** @lends svzsolutions.generic.MarkerManager.prototype */
{
    /**
   * Constructor
   *
   * @constructs
   * @param {Void}
   * @return {svzsolutions.generic.MarkerManager}
   */
    constructor: function()
  {
      this._staticLayers  = [];
        this._fixedLayers     = [];
        this._dynamicLayers = [];
  },

  /**
   * Method which adds a marker of a certain type onto the marker stack
   *
   * @param {Object} marker
   * @return {Void}
   */
  add: function(marker)
  {
      if (marker && marker._config.markerType && marker._config.markerType.layerName && marker._config.markerType.layerType)
      {
          var layerName = marker._config.markerType.layerName;
          var layerType = marker._config.markerType.layerType;

          switch (layerType)
          {
                case 'static':

            if (!this._staticLayers[layerName])
              this._staticLayers[layerName] = [];

            this._staticLayers[layerName].push(marker);

          break;

              case 'fixed':

                      if (!this._fixedLayers[layerName])
                        this._fixedLayers[layerName] = [];

                    this._fixedLayers[layerName].push(marker);

                  break;

              case 'dynamic':

                      if (!this._dynamicLayers[layerName])
                        this._dynamicLayers[layerName] = [];

                    this._dynamicLayers[layerName].push(marker);

                  break;
          }
      }
      else
      {
          console.error('MarkerManager: could not add marker to markerManager, no layerName or layerType provided.');
      }
  },

    /**
   * Method that returns all the markers in a single array
   *
   * @param {Void}
   * @return {Array}
   */
  getAll: function()
  {
    var markers = [];

    markers = this.getDynamic();
    markers = markers.concat(this.getFixed());
        markers = markers.concat(this.getStatic());

    return markers;
  },

  /**
   * Method that returns all the markers of a certain type
   *
   * @param {String} type Provide a markers type name
   * @return {Array}
   */
  getByType: function(type)
  {
      var markers = [];

      if (!type)
          return markers;

      markers = this.getDynamicByType(type);
      markers = dojo.mixin(this.getFixedByType(type));

      return markers;
  },

  /**
   * Method that returns all the markers in the dynamic layers
   *
   * @param {Void}
   * @return {Array}
   */
  getDynamic: function()
  {
      var markers = [];

        var dynamicLayer;

      for (var key in this._dynamicLayers)
      {
          dynamicLayer = this._dynamicLayers[key];

          for (var i = 0; i < dynamicLayer.length; i++)
          {
              var marker = dynamicLayer[i];

              markers.push(marker);
          }
      }

      return markers;
  },

  /**
   * Method that returns all the markers of a certain type within the dynamic layers
   *
   * @param {String} type Provide a markers type name
   * @return {Array}
   */
  getDynamicByType: function(type)
  {
      var markers = [];

      if (!type)
          return markers;

        var dynamicLayer;

      for (var key in this._dynamicLayers)
      {
          dynamicLayer = this._dynamicLayers[key];

          for (var i = 0; i < dynamicLayer.length; i++)
          {
              var marker = dynamicLayer[i];

              if (marker.getMarkerTypeName() == type)
                  markers.push(marker);

          }
      }

      return markers;
  },

  /**
   * Method that returns all the markers in the fixed layers
   *
   * @param {Void}
   * @return {Array}
   */
  getFixed: function()
  {
      var markers = [];

        var fixedLayer;

      for (var key in this._fixedLayers)
      {
          fixedLayer = this._fixedLayers[key];

          for (var i = 0; i < fixedLayer.length; i++)
          {
              var marker = fixedLayer[i];

              markers.push(marker);
          }
      }

      return markers;
  },

  /**
   * Method that returns all the markers of a certain type within the fixed layers
   *
   * @param {String} type Provide a markers type name
   * @return {Array}
   */
  getFixedByType: function(type)
  {
      var markers = [];

      if (!type)
          return markers;

        var fixedLayer;

      for (var key in this._fixedLayers)
      {
            fixedLayer = this._fixedLayers[key];

          for (var i = 0; i < fixedLayer.length; i++)
          {
              var marker = fixedLayer[i];

              if (marker.getMarkerTypeName() == type)
                  markers.push(marker);

          }
      }

      return markers;
  },

    /**
   * Method that returns all the markers in the static layers
   *
   * @param {Void}
   * @return {Array}
   */
  getStatic: function()
  {
    var markers = [];

    var staticLayer;

    for (var key in this._staticLayers)
    {
      staticLayer = this._staticLayers[key];

      for (var i = 0; i < staticLayer.length; i++)
      {
        var marker = staticLayer[i];

        markers.push(marker);
      }
    }

    return markers;
  },

    /**
   * Method that returns all the markers of a certain type within the static layers
   *
   * @param {String} type Provide a markers type name
   * @return {Array}
   */
  getStaticByType: function(type)
  {
    var markers = [];

    if (!type)
      return markers;

    var staticLayer;

    for (var key in this._staticLayers)
    {
      staticLayer = this._staticLayers[key];

      for (var i = 0; i < staticLayer.length; i++)
      {
        var marker = staticLayer[i];

        if (marker.getMarkerTypeName() == type)
          markers.push(marker);

      }
    }

    return markers;
  },

  /**
   * Method that hides all the markers residing in the dynamic layers
   *
   * @param {Void}
   * @return {Void}
   */
  hideDynamicMarkersFromMap: function()
  {
      if (svzsolutions.global.mapManager.isDebugMode())
        console.log('MarkerManager: hide dynamic markers from this map');

      var dynamicLayer;

      for (var key in this._dynamicLayers)
      {
          dynamicLayer = this._dynamicLayers[key];

            if (svzsolutions.global.mapManager.isDebugMode())
            console.log('MarkerManager: current number of markers in layer [', key ,'] is [', dynamicLayer.length , ']');

          for (var i = 0; i < dynamicLayer.length; i++)
          {
              dynamicLayer[i].hide();
          }
      }
  },

  /**
   * Method that destroys all the markers residing in the dynamic layers
   *
   * @param {Void}
   * @return {Void}
   */
  destroyDynamicMarkersFromMap: function()
  {
      var dynamicLayer;

      for (var key in this._dynamicLayers)
      {
          dynamicLayer = this._dynamicLayers[key];

            if (svzsolutions.global.mapManager.isDebugMode())
            console.log('MarkerManager: current number of markers in layer [', key ,'] is [', dynamicLayer.length , ']');

          while (marker = dynamicLayer.pop())
          {
              marker.destroy();
          }
      }
  },

  /**
   * Method that clears all the markers residing in the fixed layers
   *
   * @param {Void}
   * @return {Void}
   */
  hideFixedMarkersFromMap: function()
  {
      var fixedLayer;

      for (var key in this._fixedLayers)
      {
          fixedLayer = this._fixedLayers[key];

            if (svzsolutions.global.mapManager.isDebugMode())
            console.log('MarkerManager: current number of markers in layer [', key ,'] is [', fixedLayer.length , ']');

          for (var i = 0; i < fixedLayer.length; i++)
          {
              fixedLayer[i].hide();
          }
      }
  },

  /**
   * Method that destroys all the markers residing in the fixed layers
   *
   * @param {Void}
   * @return {Void}
   */
  destroyFixedMarkersFromMap: function()
  {
      var fixedLayer;

      for (var key in this._fixedLayers)
      {
          fixedLayer = this._fixedLayers[key];

            if (svzsolutions.global.mapManager.isDebugMode())
            console.log('MarkerManager: current number of markers in layer [', key ,'] is [', fixedLayer.length , ']');

          while (marker = fixedLayer.pop())
          {
              marker.destroy();
          }
      }
  }

});

});