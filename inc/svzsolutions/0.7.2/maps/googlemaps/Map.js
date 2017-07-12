/**
 * Copyright (c) 2009, SVZ Solutions All Rights Reserved.
 * Available via BSD license, see license file included for details.
 *
 * @title:                SVZ Solutions - GoogleMaps
 * @authors:           Stefan van Zanden <info@svzsolutions.nl>
 * @company:          SVZ Solutions
 * @contributers:
 * @version:          0.1
 * @versionDate:    2009-10-17
 * @date:             2009-10-17
 */
define("svzsolutions/maps/googlemaps/Map", ["svzsolutions/generic/Debug", "svzsolutions/generic/Geocode", "svzsolutions/generic/Loader", 
"svzsolutions/generic/Marker", "svzsolutions/generic/MarkerCache", "svzsolutions/generic/MarkerCluster", "svzsolutions/generic/MarkerList", 
"svzsolutions/generic/MarkerManager", "svzsolutions/generic/Math", "svzsolutions/generic/RequestManager", 
"svzsolutions/maps/googlemaps/InfoWindow", "svzsolutions/maps/googlemaps/Address", "svzsolutions/maps/googlemaps/CustomOverlay"
], function() {

/**
 * SVZ Solutions Google Maps Map class
 *
 * @TODO:
 * - Need to find a way to render different areas for the clustering
 */
dojo.declare('svzsolutions.maps.googlemaps.Map', null,
/** @lends svzsolutions.maps.googlemaps.Map.prototype */
{
    INFO_WINDOW_CONTENT_CLASS: 'info-window-content',

    /**
   * Constructor
   *
   * @constructs
   * @param {Object} config
   * @return {svzsolutions.maps.googlemaps.Map}
   */
  constructor: function(config)
  {
    // Init vars in this object only
    this._config                        = config;
    this._map                           = null;
    this._markerCache                   = new svzsolutions.generic.MarkerCache();
    this._markerManager                 = new svzsolutions.generic.MarkerManager();
    this._markerManagerStatic           = new svzsolutions.generic.MarkerManager();
    this._requestManager                = new svzsolutions.generic.RequestManager();
    this._loadDataForm                  = null;
    this._loader                        = null;
    this._loaderInfoWindow              = null;
    this._loaded                        = false;
    this._eventHandlesGoogle            = [];
    this._cachingEnabled                = false;
  },

  /**
   * Method which sets the load data once params in the config
   *
   * @param {Boolean} loadDataOnce
   * @return {Void}
   */
  setLoadDataOnce: function(loadDataOnce)
  {
      if (loadDataOnce)
          this._config.loadDataOnce = true;
      else
          this._config.loadDataOnce = false;

      this._dataLoaded                    = false;
  },

    /**
     * Method setDataLoadUrl
     *
     * @param {String} url
     * @return {Void}
     */
    setDataLoadUrl: function(url)
    {
        this._dataLoaded            = false; // Reset because the data in the current map probably does not reflect the new url
        this._config.dataLoadUrl    = url;
    },

  /**
   * Returns this maps config
   *
   * @param {Void}
   * @return {Object}
   */
  getConfig: function()
  {
      return this._config;
  },

    /**
   * Method that loads and shows the map
   *
   * @param {Void}
   * @return {Void}
   */
  load: function()
  {
	  if (!this._config.centerGeoLat)
	  {
		  console.error('SVZ Solutions maps: Unable to load, no geocode latitude provided');
		  return;
	  }

      this._infoWindow                      = new google.maps.InfoWindow();

      var config                            = {};
      var options                           = {};
      config.center                         = new google.maps.LatLng(this._config.centerGeoLat, this._config.centerGeoLng);
      config.zoom                           = this._config.zoomLevel;
      config.mapTypeId                      = this._config.mapType;
      config.scrollwheel 					= (this._config.scrollwheel ? this._config.scrollwheel : false); // Toggle scrolling for pages
      
      if (!this._config.dragOnTouch)
          config.draggable                  = (this.isTouchDevice() ? false : true);

      if (this._config.caching)
        this._cachingEnabled                = true;

      var controls                          = this._config.controls;

      // MAP TYPE
      if (controls.mapType)
      {
        config.mapTypeControl        = true;
        options                      = {};
        options.mapTypeIds           = controls.mapType.mapTypeIds;

        options.position             = dojo.getObject(controls.mapType.position);
        options.style                = dojo.getObject(controls.mapType.style);

        config.mapTypeControlOptions = options;
      }
      else
      {
        config.mapTypeControl = false;
      }

      // STREET VIEW
    if (controls.streetView)
    {
      config.streetViewControl        = true;
      options                         = {};
      options.position                = (controls.streetView.position == '') ? '' : dojo.getObject(controls.streetView.position);
      config.streetViewControlOptions = options;
    }
    else
    {
      config.streetViewControl        = false;
    }

    // SCALE
    if (controls.scale)
    {
      config.scaleControl        = true;

      options                    = {};
      options.position           = dojo.getObject(controls.scale.position);
      options.style              = dojo.getObject(controls.scale.style);
      config.scaleControlOptions = options;
    }
    else
    {
      config.scaleControl        = false;
    }

    // Pan
    if (controls.pan)
    {
      options                    = {};
      options.position           = dojo.getObject(controls.pan.position);
      config.panControlOptions   = options;
    }
    else
    {
      config.panControl          = false;
    }

    // ZOOM
    if (controls.zoom)
    {
      options                    = {};
      options.position           = dojo.getObject(controls.zoom.position);
      options.style              = dojo.getObject(controls.zoom.style);
      config.zoomControlOptions  = options;
    }
    else
    {
      config.zoomControl         = false;
    }
    
    // Disable the POI generated by google maps and messing up sometimes with the clicks from custom POIS (figure out how to fix this later)
    config.styles                = [ { featureType: 'poi', elementType: 'labels', stylers: [ { visibility: 'off' } ] } ];

    if (svzsolutions.global.mapManager.isDebugMode())
        console.log('Generated the following Google Maps config [', config, ']');

    var containerElem;

    if (this._config.mapContainer)
        containerElem = this._config.mapContainer;
    else if (this._config.mapContainerId)
        containerElem = dojo.byId(this._config.mapContainerId);

    this._map                                                     = new google.maps.Map(containerElem, config);

    /*if (navigator.userAgent.indexOf('iPhone') != -1 || navigator.userAgent.indexOf('Android') != -1 )
    {
        dojo.style(this.getMapContainer(), 'width', '100%');
        dojo.style(this.getMapContainer(), 'height', '100%');
    }*/

    if (this._config.layers)
    {

        for (var i = 0; i < this._config.layers.length; i++)
        {
            var layer = new google.maps.Layer(this._config.layers[i]);
            this.getMap().addOverlay(layer);
        }
    }

    this._eventHandlesGoogle.push(google.maps.event.addListener(this.getMap(), 'idle', dojo.hitch(this, this._onIdle)));
    this._eventHandlesGoogle.push(google.maps.event.addListener(this.getMap(), 'zoom_changed', dojo.hitch(this, this._onZoomLevelChanged)));
    this._eventHandlesGoogle.push(google.maps.event.addListener(this.getMap(), 'drag', dojo.hitch(this, this._onDrag)));
    this._eventHandlesGoogle.push(google.maps.event.addListener(this.getMap(), 'dragend', dojo.hitch(this, this._onDragend)));
    this._eventHandlesGoogle.push(google.maps.event.addListener(this.getMap(), 'center_changed', dojo.hitch(this, this._onCenterChanged)));
    this._eventHandlesGoogle.push(google.maps.event.addListener(this.getMap(), 'dblclick', dojo.hitch(this, this._onMouseDoubleClick)));
    this._eventHandlesGoogle.push(google.maps.event.addListener(this.getMap(), 'rightclick', dojo.hitch(this, this._onMouseRightClick)));
    this._eventHandlesGoogle.push(google.maps.event.addListener(this.getMap(), 'maptypeid_changed', dojo.hitch(this, this._onMapTypeChanged)));

    if (this._config.markers)
    {
        var data = {};
        data.markers = this._config.markers;

        this._processMarkers(data.markers);
    }

    // Set the loaded flag
    this._loaded = true;

    this.onMapLoaded();
  },
  
  /**
   * Method isTouchDevice
   */
  isTouchDevice: function() 
  {
      return 'ontouchstart' in window        // works on most browsers 
          || navigator.maxTouchPoints;       // works on IE10/11 and Surface
  },

  /**
   * Method which sets the maps center and repositions it
   *
   * @param {svzsolutions.generic.Geocode}
   * @return {Void}
   * @return {Boolean} Returns false in case of nothing provided
   */
  setCenter: function(geocode)
  {
      if (!geocode)
          return false;

      var point     = new google.maps.LatLng(geocode.getLatitude(), geocode.getLongitude());

      this.getMap().panTo(point);
  },

    /**
   * Method which gets the maps center
   *
   * @param {Void}
   * @return {svzsolutions.generic.Geocode}
   */
  getCenter: function()
  {
        var center = this.getMap().getCenter();
        var geocode = new svzsolutions.generic.Geocode(center.lat(), center.lng());

        return geocode;
  },

  /**
   * Method which checks if this map is loaded
   *
   * @param {Void}
   * @return {Boolean}
   */
  isLoaded: function()
  {
      return this._loaded;
  },

  /**
   * Method which returns the current map instance
   *
   * @param {Void}
   * @return {Object} Instance of google.maps.Map
   */
  getMap: function()
  {
      return this._map;
  },

  /**
   * Method which returns the current map id
   *
   * @param {Void}
   * @return {String}
   */
  getId: function()
  {
    return this._config.mapId;
  },

  /**
   * Method which returns the current map instance container
   *
   * @param {Void}
   * @return {HTMLDomElement}
   */
  getMapContainer: function()
  {
      return this.getMap().getDiv();
  },
  
  /**
   * Method which returns the current map container id
   *
   * @param {Void}
   * @return {String}
   */
  getMapContainerId: function()
  {
      return this._config.mapContainerId;
  },

  /**
   * Method which is called when the map is in a idle state
   *
   * @event
   * @param {Object} event
   * @return {Void}
   */
  _onIdle: function(event)
  {
      this.loadData();
    this.onIdle(event);
  },

  /**
   * Method which is called when the map changes its zoom level
   *
   * @event
   * @param {Object} event
   * @return {Void}
   */
  _onZoomLevelChanged: function(event)
  {

    this._infoWindow.close();

    this.onZoomLevelChanged(event);
  },

  /**
   * Method which is called when the map is being dragged
   *
   * @event
   * @param {Object} event
   * @return {Void}
   */
  _onDrag: function(event)
  {
    this.onDrag();
  },

  /**
   * Method which is called when the map stopped being dragged
   *
   * @event
   * @param {Object} event
   * @return {Void}
   */
  _onDragend: function(event)
  {
      this.onDragend();
  },

  /**
   * Private method which is called when a draggable marker is stoped being moved on the screen
   *
   * @param {Object} event
   * @return {Void}
   */
  _onMarkerDragEnd: function(event)
  {
      this.instance.onMarkerDragEnd(this.marker2);
  },

  /**
   * Method which is called when a marker is hovered.
   *
   * @param {Object} event
   * @return {Void}
   */
  _onMarkerMouseOver: function(event)
  {
    this.onMarkerMouseOver(this._currentMarker);
  },

  /**
   * Method which is called when a marker on the map has been clicked.
   *
   * @param {Object} event
   * @return {Void}
   */
  _onMarkerClick: function(event)
  {
    this.onMarkerClick(this._currentMarker);
  },

  /**
   * Method which is called when a marker on the map has been clicked or hovered and info window data needs to be loaded.
   *
   * @param {Object} event
   * @return {Void}
   */
  _loadInfoWindow: function()
  {
    var dataLoadUrl = this._infoWindow.dataLoadUrl;

    if (dataLoadUrl)
    {
      var xhrArgs =
      {
        url: dataLoadUrl,
        handleAs: "json",
        load: dojo.hitch(this, '_loadInfoWindowCallback'),
        error: dojo.hitch(this, function(error)
        {
          if (this._loaderInfoWindow)
          {
            this._loaderInfoWindow.destroy();
            this._loaderInfoWindow = null;
          }

          if (error.dojoType == 'cancel')
            return;

          var messageHolder = dojo.create('div', { className: 'sg-message-holder sg-error' } );
          var message       = dojo.create('p', {}, messageHolder);
          message.innerHTML = 'An error occured trying to load the content, please try again.';

          this._infoWindow.setContent(messageHolder);

          console.error("MarkerClick: An unexpected error occurred: ", error);
        })
      };

      // Call the asynchronous xhrGet
      this._requestManager.get(xhrArgs, 'loadInfoWindowData');
    }
  },

  /**
   * Method which is called when the ajax request has finished on opening a info window.
   *
   * @param {Object} data JSON object
   * @return {Void}
   */
  _loadInfoWindowCallback: function(data)
  {
      if (svzsolutions.global.mapManager.isDebugMode())
        console.log('Map: InfoWindowCallback');

      if (this._loaderInfoWindow)
      {
          this._loaderInfoWindow.destroy();
          this._loaderInfoWindow = null;
      }

    if (data && typeof(data) == 'object')
    {
      if (data.content)
      {
          // Temp: Create a dom element from the provided html string, no good public way available yet in dojo 1.3.2 / 1.5
          // Follow: http://trac.dojotoolkit.org/ticket/8613
          var domElem = dojo._toDom(data.content);
          this._infoWindow.setContent(domElem);

          // Workaround for the map not showing the info window in the middle when first setting the content
          // Follow bug in topic I created: http://groups.google.com/group/google-maps-js-api-v3/browse_thread/thread/c3175c59c174f49f/e1dff9fc4453ef3d?lnk=gst&q=info+window+ajax#e1dff9fc4453ef3d
          var timeoutHandler = dojo.hitch(this, function()
          {
              var infoWindow = new svzsolutions.maps.googlemaps.InfoWindow(this._infoWindow.getContent(), this._infoWindow);
              infoWindow.setMarker(this._infoWindow._marker);

              this.onInfoWindowContentLoaded(infoWindow);

              dojo.connect(infoWindow, 'initCustomTabContent', this, function()
              {
                  this.onInfoWindowTabContentLoaded(infoWindow);
              });

              // We are opening twice so that the content put in using Ajax will influence that actual calculated size of the info window and auto pan accordingly
              // Put this method as the last to handle because else the width and height of the info window are done weird;
              this._infoWindow.open(this.getMap(), this._currentMarker);
          });

          setTimeout(timeoutHandler, 1);
      }
    }
  },

  /**
   * Method which checks if the given marker is in the viewport
   *
   * @param {Object} marker
   * @return {Boolean}
   */
  isMarkerInViewPort: function(marker)
  {
      if (marker)
      {
          var position = marker.getPosition().getGoogleMapsPoint();
          var bounds     = this.getMap().getBounds();

          return bounds.contains(position);
      }

      return false;
  },

  /**
   * Method which returns the current zoom level
   *
   * @param {Void}
   * @return {Integer}
   */
  getZoomLevel: function()
  {
      return this.getMap().getZoom();
  },

  /**
   * Method which sets the current zoom level
   *
   * @param {Integer} zoomLevel
   * @return {Void}
   */
  setZoomLevel: function(zoomLevel)
  {
    this.getMap().setZoom(zoomLevel);
  },

  /**
   * Method that returns all the viewport information like the zoom level / sw and ne latitude longitude
   *
   * @param {Void}
   * @return {Object}
   */
  getViewPortInfo: function()
  {
    // Get the lat / lon bounderies of the viewport
    var bounds = this.getMap().getBounds();

    // South west coordinates of viewport
    var swLatLng = bounds.getSouthWest();

    // North east coordinates of viewport
    var neLatLng = bounds.getNorthEast();

    // Get the current zoom level
    var zoomLevel = this.getMap().getZoom();

    var ceLatLng = this.getMap().getCenter();

    var returnObject = {
        zoom: zoomLevel,
        sw_lat: swLatLng.lat(),
        sw_lng: swLatLng.lng(),
        ne_lat: neLatLng.lat(),
        ne_lng: neLatLng.lng(),
        ce_lat: ceLatLng.lat(),
        ce_lng: ceLatLng.lng(),
        w: this.getConfig().width,
        h: this.getConfig().height
    };

    return returnObject;
  },

  /**
   * Method which will add a form to the query of loadData
   *
   * @param {Object} form
   * @return {Void}
   */
  setLoadDataForm: function(form)
  {
      this._loadDataForm = form;
  },

  /**
   * Method reloadData
   *
   * @param {Void}
   * @return {Void}
   */
  reloadData: function()
  {
      // Clear cache if enabled
      if (this._cachingEnabled)
          this._markerCache.clear();

      // Request for new markers
      this.loadData();
  },

  /**
   * Method that loads all the markers on the map
   *
   * @param {Void}
   * @return {Void}
   */
  loadData: function()
  {
      if (svzsolutions.global.mapManager.isDebugMode())
        console.log('Map: loading data, with option loadDataOnce [', this._config.loadDataOnce, '] / dataLoaded [', this._dataLoaded ,'] and dataLoadUrl [', this._config.dataLoadUrl, ']');

      if (this._config.loadDataOnce && this._dataLoaded)
          return;

    var viewPortInfo = this.getViewPortInfo();

    if (this._config.dataLoadUrl)
    {
      if (this._cachingEnabled && this._markerCache.hasByZoomLevel(viewPortInfo.zoom))
      {
          // Clear all the current dynamic markers
          //this._markerManager.hideDynamicMarkersFromMap();
            this._markerCache.hideAllButZoomLevel(viewPortInfo.zoom);

            var markerManager = this._markerCache.getByZoomLevel(viewPortInfo.zoom);

            if (svzsolutions.global.mapManager.isDebugMode())
              console.log('Showing markers from cache');

            this._showMarkers(markerManager);
      }
        else
        {
            var xhrArgs =
            {
              url: this._config.dataLoadUrl,
              failOk: true,
              content: viewPortInfo,
              form: this._loadDataForm,
              handleAs: "json",
              load: dojo.hitch(this, this._loadDataCallback),
              error: function(error)
              {
                    this._loader.hide();

                  if (error.dojoType == 'cancel')
                      return;

                    // @TODO write something back to the user
                console.log("An unexpected error occurred: " + error);
              }
            };

            if (!this._loader)
            {
                // Get the right layer to put the loader in
                // @TODO: place this loader so it won't block the info window
                var mapPanes = this.getMap().getDiv().childNodes[0];

                this._loader                                                 = new svzsolutions.generic.Loader('load-data', mapPanes, 'first');
              this._loader.onCancel                             = dojo.hitch(this, function(event)
                  {
                      this._requestManager.cancel('loadData' + this._config.mapIndex);
                  });
            }

            this._loader.show();

            // Call the asynchronous xhrGet
            this._requestManager.get(xhrArgs, 'loadData' + this._config.mapIndex);
        }
    }
    
    this.resize(true); // Fire to fix grey maps (but don't recenter)
  },

  /**
   * Method _processMarkers which will put the markers on the map
   *
   * @param {Array} markers
   * @return {Void}
   */
  _processMarkers: function(markers)
  {
    var markerConfig, markerType, marker, config, newTempMarker, markerThis; // Define outside of the loop for performance reasons
    var markerCount   = markers.length;
    var markerManager = new svzsolutions.generic.MarkerManager();

    for (var i = 0; i < markerCount; i++)
    {
        markerConfig                    = markers[i];

        if (!this._config.markerTypes[markerConfig.type])
        {
            console.error('Map: The marker type called "' + markerConfig.type + '" seems to be not registered.');
            continue;
        }
        else
        {
            try
            {
                markerType                              = this._config.markerTypes[markerConfig.type];
                markerConfig.markerType                 = markerType;

                newTempMarker                           = null;

                if (markerConfig.type == 'cluster')
                    newTempMarker         = new svzsolutions.generic.MarkerCluster(markerConfig, this.getMap());
                else if (markerConfig.type == 'list')
                    newTempMarker         = new svzsolutions.generic.MarkerList(markerConfig, this.getMap());
                else
                    newTempMarker         = new svzsolutions.generic.Marker(markerConfig, this.getMap());

                // Push the marker onto the marker manager stack
                if (markerType.layerType == 'static' || markerType.layerType == 'fixed')
                    this._markerManagerStatic.add(newTempMarker);
                else
                    markerManager.add(newTempMarker);

            }
            catch (e)
            {
                console.error('Map: loading marker failed [', e, ']');
            }
        }
    }

      // Registrer this bunch of markers to the marker cache in case it has been enabled
    if (this._cachingEnabled)
    {
        if (!this._markerCache.hasByZoomLevel(this.getZoomLevel()) && markerManager.getDynamic().length > 0)
            this._markerCache.setByZoomLevel(this.getZoomLevel(), markerManager);

    }
    else
    {
        this._markerManager = markerManager;
    }

    this._showMarkers(markerManager);
    this._showMarkers(this._markerManagerStatic);

    this.onMarkersShown();
  },

  /**
   * Method that will show all the markers on the map coming from the provided Marker Manager
   *
   * @param {svzsolutions.generic.MarkerManager}
   * @return void
   */
  _showMarkers: function(markerManager)
  {
      if (svzsolutions.global.mapManager.isDebugMode())
        console.log('Map: gonna show all the markers now');

      var allMarkers        = markerManager.getAll();
      var allMarkersCount   = allMarkers.length;
      var newTempMarker, marker, config, markerType, markerConfig, tempMarker;
      var i = 0;

      for (i; i < allMarkersCount; i++)
      {
          newTempMarker         = allMarkers[i];

          // Don't show it if it is already shown (will cause duplicate items to be shown on the map);
          if (newTempMarker.isShown())
              continue;

          config                    = newTempMarker.getConfig('googlemaps');
          markerType                = newTempMarker.getMarkerType();
          markerConfig              = newTempMarker._config;

          if (newTempMarker._config.type == 'cluster')
          {
            marker                        = new svzsolutions.maps.googlemaps.CustomOverlay(config);
          }
          else if (newTempMarker._config.type == 'list')
          {
            marker                        = new svzsolutions.maps.googlemaps.CustomOverlay(config);
          }
          else
          {
        	  console.log('@@@@@@@@@@', markerType);
            // @TODO: Implement anchor / scaled size / origin functionality
            if (markerType.iconEnabled)
            {
                if (markerType.icon)
                {
                    config.icon                 = new google.maps.MarkerImage(
                            markerType.icon.url,
                            new google.maps.Size(markerType.icon.size.width, markerType.icon.size.height)
                    );
                }

                if (markerType.shadow)
                {
                    config.shadow    = new google.maps.MarkerImage(
                            markerType.shadow.url,
                            new google.maps.Size(markerType.shadow.size.width, markerType.shadow.size.height)
                    );
                }

                marker                             = new google.maps.Marker(config);
            }
            else
            {
                config.className            = markerType.className;
                config.typeConfig           = markerType;
                config.label                = markerConfig.label;

                marker                      = new svzsolutions.maps.googlemaps.CustomOverlay(config);
            }
          }

          marker.type                 = markerType;
          marker.typeName            = markerConfig.type;

          if (markerConfig.entityId)
              marker.entityId = markerConfig.entityId;

          // Make the marker draggable
          if (markerConfig.draggable)
          {
              markerThis              = {};
              markerThis.instance     = this;
              markerThis.marker       = marker;
              markerThis.marker2      = newTempMarker;
              google.maps.event.addListener(marker, 'dragend', dojo.hitch(markerThis, this._onMarkerDragEnd));
          }

          // Registrer the overlay object to the marker
          newTempMarker.setOverlay(marker);

          this._bindMarker(marker, config.content, config.dataLoadUrl, newTempMarker);

          newTempMarker.show(); // Show the marker on the map
      }
  },

  /**
   * Method that executes when the request for data has been finished
   *
   * @param {Object} data JSON object
   * @return {Void}
   */
  _loadDataCallback: function(data)
  {
      if (svzsolutions.global.mapManager.isDebugMode())
        console.log('Map: load DataCallback fired with data [', data, ']');

      if (this._loader)
        this._loader.hide();

      // Clear all the current dynamic markers
      //this._markerManager.hideDynamicMarkersFromMap();
      if (this._cachingEnabled)
        this._markerCache.hideAllButZoomLevel(this.getZoomLevel());
      else
        this._markerManager.hideDynamicMarkersFromMap();

      if (!this._config.markerTypes)
      {
          console.error('No marker types seems to be registered.');
          return;
      }

      this._dataLoaded = true; // Set flag for data being loaded already

      if (data && typeof(data) == 'object')
      {
          // Process any markers returned
          if (data.markers)
              this._processMarkers(data.markers);

      }

      if (svzsolutions.global.mapManager.isDebugMode())
          console.log('Map: finished processing of returned data.');

  },

  /**
   * Method that binds events to the marker
   *
   * @param {Object} instance
   * @param {Object} marker
   * @param {String} dataLoadUrl
   * @param {Object} newTempMarker
   * @return {Void}
   */
  _bindMarker: function (marker, content, dataLoadUrl, newTempMarker)
  {
      if (marker.type.clickAction == 'zoom')
      {
          var onClickFunction = function()
          {
              var zoomLevel = 0;

              if (marker._config.smartNavigation && marker._config.smartNavigation.zoomToLevel)
                  zoomLevel = marker._config.smartNavigation.zoomToLevel;
              else
                  zoomLevel = this.getMap().getZoom() + 1;

              this.getMap().setCenter(marker._config.position);
              this.getMap().setZoom(zoomLevel);
          };

          if (marker.declaredClass && marker.declaredClass == 'svzsolutions.maps.googlemaps.CustomOverlay')
              dojo.connect(marker, 'onClick', dojo.hitch(this, onClickFunction));
          else
              google.maps.event.addListener(marker, 'click', dojo.hitch(this, onClickFunction));

      }
      else
      {
          var onClickFunction2 = function()
          {
              if (dataLoadUrl)
              {
                  // Set the loader img
                  var body      = dojo.create('div', { className: this.INFO_WINDOW_CONTENT_CLASS });

                  this._infoWindow.setContent(body);
                  this._infoWindow._marker = newTempMarker;

                  if (!this._loaderInfoWindow)
                  {
                      this._loaderInfoWindow                             = new svzsolutions.generic.Loader('load-info-window-data', body, 'first');
                      this._loaderInfoWindow.onCancel         = dojo.hitch(this, function(event)
                      {
                        this._requestManager.cancel('loadInfoWindowData');
                        this._infoWindow.close();
                      });
                  }

                  this._infoWindow.dataLoadUrl     = dataLoadUrl;

                  if (marker._googleOverlay)
                  {
                      this._currentMarker                         = marker._googleOverlay;
                      this._infoWindow.open(this.getMap(), marker._googleOverlay);
                  }
                  else
                  {
                      this._currentMarker                         = marker;
                      this._infoWindow.open(this.getMap(), marker);
                  }

              }
              else if (content)
              {
                  // Check if it is a string and not a DomDocument
                  if (typeof(content) == 'string')
                      content = dojo._toDom(content);

                  this._infoWindow.setContent(content);

                  var infoWindowContent = new svzsolutions.maps.googlemaps.InfoWindow(this._infoWindow.getContent());
                  infoWindowContent.setMarker(newTempMarker);

                  this._infoWindow.open(this.getMap(), marker);

                  this.onInfoWindowContentLoaded(this._infoWindow);
              }
          };

          if (marker.declaredClass && marker.declaredClass == 'svzsolutions.maps.googlemaps.CustomOverlay')
          {
              dojo.connect(marker, 'onClick', dojo.hitch(this, onClickFunction2));
              dojo.connect(marker, 'onClick', this, '_onMarkerClick');
              dojo.connect(marker, 'onMouseOver', this, '_onMarkerMouseOver');

              if (dataLoadUrl)
              {
                  dojo.connect(marker, 'onClick', this, '_loadInfoWindow');

                  if (marker.type.enableDataLoadOnMouseOver)
                  {
                    dojo.connect(marker, 'onMouseOver', dojo.hitch(this, onClickFunction2));
                    dojo.connect(marker, 'onMouseOver', this, '_loadInfoWindow');
                  }

              }
          }
          else
          {
              google.maps.event.addListener(marker, 'click', dojo.hitch(this, onClickFunction2));
              google.maps.event.addListener(marker, 'click', dojo.hitch(this, '_onMarkerClick'));
              google.maps.event.addListener(marker, 'mouseover', dojo.hitch(this, '_onMarkerMouseOver'));

              if (dataLoadUrl)
              {
                  google.maps.event.addListener(marker, 'click', dojo.hitch(this, '_loadInfoWindow'));

                  if (marker.type.enableDataLoadOnMouseOver)
                  {
                    google.maps.event.addListener(marker, 'mouseover', dojo.hitch(this, onClickFunction2));
                    google.maps.event.addListener(marker, 'mouseover', dojo.hitch(this, '_loadInfoWindow'));
                  }

              }

          }

          // Make sure that the info window is shown when the map is loaded
          if (marker.showInfoWindow)
            dojo.hitch(this, onClickFunction2)();

      }

  },

  /**
   * Method whichs add a marker programmaticly to the map
   *
   * @param {svzsolutions.generic.Marker}
   * @param {String} markerType
   * @return void
   */
  addMarker: function(marker, markerType)
  {
    if (svzsolutions.global.mapManager.isDebugMode())
      console.log('Map: Adding marker [', marker, '] with type [', markerType, ']');

    if (marker)
    {
      if (!markerType)
      {
        console.error('Map: add marker, no marker type specified.');
        return false;
      }

      if (!this._config.markerTypes[markerType])
      {
        console.error('Map: add marker, invalid marker type specified.');
        return false;
      }

      marker.setMarkerType(this._config.markerTypes[markerType]);

      this.getMarkers().add(marker);

      this._showMarkers(this.getMarkers());
    }

  },

  /**
   * Method that returns the marker manager
   *
   * @param {Void}
   * @return {svzsolutions.generic.MarkerManager}
   */
  getMarkers: function()
  {
      var zoomLevel = this.getZoomLevel();

      if (this._cachingEnabled && this._markerCache.hasByZoomLevel(zoomLevel))
        return this._markerCache.getByZoomLevel(zoomLevel);

      return this._markerManager;
  },

    /**
   * Method that returns the marker manager for the static items which are independent of the zoom level
   *
   * @param {Void}
   * @return {svzsolutions.generic.MarkerManager}
   */
  getMarkersStatic: function()
  {
    return this._markerManagerStatic;
  },

  /**
   * Method resize that fixes grey tiles when a map is rendered but not shown immediately
   *
   * @param {Boolean} preventRecenter
   * @return {Void}
   */
  resize: function(preventRecenter)
  {
      if (svzsolutions.global.mapManager.isDebugMode())
        console.log('Map: firing resize ' + (preventRecenter ? 'NO RECENTER' : 'RECENTER'));

      google.maps.event.trigger(this.getMap(), 'resize');

      if (!preventRecenter)
      {
          var centerCoordinates = new google.maps.LatLng(this._config.centerGeoLat, this._config.centerGeoLng);
          this.getMap().setCenter(centerCoordinates);
      }

  },

  /**
   * Method which is called when the map type is changed
   *
   * @event
   * @param {Void}
   * @return {Void}
   */
  _onMapTypeChanged: function()
  {
      this.onMapTypeChanged();
  },

  /**
   * Method which is called when the center of the map has changed
   *
   * @event
   * @param {Void}
   * @return {Void}
   */
  _onCenterChanged: function()
  {
      this.onCenterChanged();
  },

  /**
   * Method which is called when the user right clicks his mouse on the map
   *
   * @event
   * @param {Void}
   * @return {Void}
   */
  _onMouseRightClick: function()
  {
      this.onMouseRightClick();
  },

  /**
   * Method which is called when the user double clicks his mouse on the map
   *
   * @event
   * @param {Void}
   * @return {Void}
   */
  _onMouseDoubleClick: function()
  {
      this.onMouseDoubleClick();
  },



  /* BEGIN EVENT HOOKS */

  /**
   * Method which is called when the map is in a idle state
   *
   * @event
   * @param {Object} event
   * @return {Void}
   */
  onIdle: function(event)
  {

  },

  /**
   * Method which is called when the map changes its zoom level
   *
   * @event
   * @param {Object} event
   * @return {Void}
   */
  onZoomLevelChanged: function(event)
  {

  },

  /**
   * Method which is called when the map is being dragged
   *
   * @event
   * @param {Object} event
   * @return {Void}
   */
  onDrag: function(event)
  {

  },

  /**
   * Method which is called when the map stopped being dragged
   *
   * @event
   * @param {Object} event
   * @return {Void}
   */
  onDragend: function(event)
  {

  },

  /**
   * Method which is called when a draggable marker is stopped being moved on the screen
   * Can be overridden
   *
   * @event
   * @param {Object} marker
   * @return {Void}
   */
  onMarkerDragEnd: function(marker)
  {
  },

  /**
   * Method which is called when a marker is hovered
   * Can be overridden
   *
   * @event
   * @param {Object} marker
   * @return {Void}
   */
  onMarkerMouseOver: function(marker)
  {

  },

  /**
   * Method which is called when a marker is clicked
   * Can be overridden
   *
   * @event
   * @param {Object} marker
   * @return {Void}
   */
  onMarkerClick: function(marker)
  {

  },

  /**
   * Method onInfoWindowContentLoaded which is fired whenever a info windows is opened
   *
   * @event
   * @param {Object} infoWindow
   * @return {Void}
   */
  onInfoWindowContentLoaded: function(infoWindow)
  {

  },

  /**
   * Method onInfoWindowTabContentLoaded which is fired whenever a info windows dynamic tab is opened
   *
   * @event
   * @param {Object} infoWindow
   * @return {Void}
   */
  onInfoWindowTabContentLoaded: function(infoWindow)
  {

  },

  /**
   * Method which is called when the map is initialized
   * Can be overridden or connected to
   *
   * @event
   * @param {Void}
   * @return {Void}
   */
  onMapLoaded: function()
  {

  },

  /**
   * Method which is called when the markers are shown
   * Can be overridden or connected to
   *
   * @event
   * @param {Void}
   * @return {Void}
   */
  onMarkersShown: function()
  {

  },

  /**
   * Method which is called when the map type is changed
   *
   * @event
   * @param {Void}
   * @return {Void}
   */
  onMapTypeChanged: function()
  {

  },

  /**
   * Method which is called when the center of the map has changed
   *
   * @event
   * @param {Void}
   * @return {Void}
   */
  onCenterChanged: function()
  {

  },

  /**
   * Method which is called when the user right clicks his mouse on the map
   *
   * @event
   * @param {Void}
   * @return {Void}
   */
  onMouseRightClick: function()
  {
  },

  /**
   * Method which is called when the user double clicks his mouse on the map
   *
   * @event
   * @param {Void}
   * @return {Void}
   */
  onMouseDoubleClick: function()
  {
  },

  /* END EVENT HOOKS */


  /**
   * Method that cleanes up this map and removes it from the dom
   *
   * @param {Void}
   * @return {Void}
   */
  destroy: function()
  {
      this._requestManager.cancel('loadData' + this._config.mapIndex);

      this._markerManager.destroyDynamicMarkersFromMap();
      this._markerManager.destroyFixedMarkersFromMap();

      // Remove all event handles
      for (var i = 0; i < this._eventHandlesGoogle.length; i++)
      {
          this._eventHandlesGoogle[i].remove();
      }

      if (this.getMap() && this.getMap().getDiv())
          dojo.destroy(this.getMap().getDiv());

  }

})

});