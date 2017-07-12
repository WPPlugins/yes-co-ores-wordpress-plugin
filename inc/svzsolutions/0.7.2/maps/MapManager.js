/**
 * Copyright (c) 2009, SVZ Solutions All Rights Reserved.
 * Available via BSD license, see license file included for details.
 *
 * @title:                SVZ Solutions Map Manager
 * @authors:           Stefan van Zanden <info@svzsolutions.nl>
 * @company:          SVZ Solutions
 * @contributers:
 * @version:          0.2
 * @versionDate:    2010-02-06
 * @date:             2010-02-06
 */
define("svzsolutions/maps/MapManager", ["svzsolutions/maps/googlemaps/Map"], function() {

/**
 * SVZ Solutions Map Manager
 *
 */
dojo.declare('svzsolutions.maps.MapManager', null,
/** @lends svzsolutions.maps.MapManager.prototype */
{
    /**
   * Constructor
   *
   * @constructs
   * @param {Object} config
   * @return {svzsolutions.maps.MapManager}
   */
    constructor: function(config)
    {
        if (config && config.debugMode === true)
            this._debugMode = true;
        else
            this._debugMode = false;

        this._maps                          = [];
        this._googleMapsLibraryLoaded       = false;
        this._googleMapsLibraryRequested    = false;
        this._googleMapsConfigs             = [];
        this._mapIndex                      = -1;

        // Check if google maps is already loaded or not
        if (svzsolutions.global && svzsolutions.global.mapManager && typeof(google) !== 'undefined' && typeof(google.maps) !== 'undefined')
            this._googleMapsLibraryLoaded = true;

        if (!svzsolutions.global)
            svzsolutions.global = {};

        svzsolutions.global.mapManager = this;

        // Fire destroying of everything on the actually refresh not on before the refresh
        // (in case of the people creating Ajax WebApps and want to show an abort message when they for example refresh);
        dojo.addOnWindowUnload(dojo.hitch(this, this.destroy));
    },

    /**
   * Method isDebugMode which returns if the messages from this library should be logged in the console
   *
   * @param {Void}
   * @return {Boolean}
   */
    isDebugMode: function()
    {
        return this._debugMode;
    },

    /**
     * Method getByIndex which returns the map specified by it's index
     *
     * @param {Integer} mapIndex
     * @return {svzsolutions.maps.googlemaps.Map} Returns an object in case of the map being of library "googlemaps"
     * @return {Boolean} Returns a boolean false in other cases
     */
    getByIndex: function(mapIndex)
    {
        if (mapIndex < 0)
        {
            console.error('MapManager getByIndex: No map index provided.');
            return false;
        }

        if (!this._maps[mapIndex])
        {
            console.error('MapManager getByIndex: Could not find a map with index: [', mapIndex, ']');
            return false;
        }

        return this._maps[mapIndex];
    },

    /**
     * Method: adds and initializes a map by a json encoded config string
     *
     * @param {Mixed} config (either an object or a string)
     * @return {svzsolutions.maps.googlemaps.Map} Returns an object in case of a library config of "googlemaps"
     * @return {Boolean} Returns a boolean false in other cases
     */
    initByConfig: function(config)
    {
        if (!config)
            return false;

        if (!dojo.isObject(config))
            config = dojo.fromJson(config);

        if (svzsolutions.global.mapManager.isDebugMode())
          console.log('MapManager: Adding and initializing config: ', config);

        if (config.libraryConfig)
        {
            switch (config.libraryConfig.name)
            {
              case 'googlemaps':

                  this._mapIndex++;

                    var map                                         = new svzsolutions.maps.googlemaps.Map(config);
                    map.index                                        = this._mapIndex;
                    this._maps[this._mapIndex]     = map;

                  return this._maps[this._mapIndex];

                  break;
            }

        }

        return false;
    },

    /**
     * Method which destroys and cleanups a map by the provided index
     *
     * @param {Integer} mapIndex
     * @return {Void}
     */
    destroyByIndex: function(mapIndex)
    {
        if (svzsolutions.global.mapManager.isDebugMode())
          console.log('MapManager: Trying to destroy the map by mapIndex', mapIndex);

        var map = this.getByIndex(mapIndex);

        map.destroy();
    },

    /**
     * Method which will startup all the maps, this method should be used after subscribing to the main objects in extensions
     *
     * @param {Void}
     * @return {Void}
     */
    startup: function()
    {
        for (var  i = 0; i < this._maps.length; i++)
        {
            var config = null;

            if (config = this._maps[i].getConfig())
            {
                if (config.libraryConfig)
                {
                    switch (config.libraryConfig.name)
                    {
                      case 'googlemaps':

                              this._loadGoogleMapsLibrary(config);

                          break;
                    }
                }
            }
        }
    },

    /**
     * Method which loads the google maps library
     *
     * @param {Object}
     * @return {Void}
     */
    _loadGoogleMapsLibrary: function(config)
    {
        if (!this._googleMapsLibraryLoaded)
            this._loadGoogleMaps(config);
        else
            this._loadGoogleBasedMaps(config);

    },

    /**
     * Method which loads the google maps library
     *
     * @param {Object}
     * @return {Void}
     */
    _loadGoogleMaps: function(config)
    {
        this._googleMapsConfigs.push(config);

        if (!this._googleMapsLibraryRequested)
        {
            this._googleMapsLibraryRequested = true;

            var apiKey = (config.apiKey ? config.apiKey : false);

            var script  = document.createElement("script");
            //script.src  = "http://maps.google.com/maps/api/js?sensor=false&callback=svzsolutions.global.mapManager._loadGoogleMapsCallback";
            script.src  = "https://maps.googleapis.com/maps/api/js?libraries=places" + (apiKey ? '&key=' + apiKey : '') + "&callback=svzsolutions.global.mapManager._loadGoogleMapsCallback";
            // &ver=4.4.1
            // v=3.exp&
            script.type = "text/javascript";
            document.getElementsByTagName("head")[0].appendChild(script);
        }
    },

    /**
     * Method which is called by google when the map is loaded
     *
     * @param {Void}
     * @return {Void}
     */
    _loadGoogleMapsCallback: function()
    {
        this._googleMapsLibraryLoaded = true;

        this._loadGoogleBasedMaps();
    },

    /**
     * Method _loadGoogleBasedMaps which loads all the google maps based maps
     *
     * @param {Void}
     * @return {Void}
     */
    _loadGoogleBasedMaps: function()
    {
        if (svzsolutions.global.mapManager.isDebugMode())
            console.log('MapManager: Load google based maps');

        if (this._maps)
        {
            for (var i = 0; i < this._maps.length; i++)
            {
                if (!this._maps[i].isLoaded())
                {
                    if (svzsolutions.global.mapManager.isDebugMode())
                        console.log('MapManager: Map is not loaded yet');

                    this._maps[i].load();
                }

            }
        }
    },

    /**
     * Method destroy which will clean up and destroy all available map instances
     *
     * @param {Void}
     * @return {Void}
     */
    destroy: function()
    {
        if (this._maps)
        {
            for (var i = 0; i < this._maps.length; i++)
            {
                this._maps[i].destroy();
            }
        }

        this._maps                        = null;
        this._googleMapsConfigs           = null;

        this._maps                        = [];
        this._googleMapsConfigs           = [];
        this._mapIndex                    = -1;
    }

});

});
