/**
 * Copyright (c) 2009, SVZ Solutions All Rights Reserved.
 * Available via BSD license, see license file included for details.
 *
 * @title:                SVZ Solutions - Google Maps Custom Overlay
 * @authors:           Stefan van Zanden <info@svzsolutions.nl>
 * @company:          SVZ Solutions
 * @contributers:
 * @version:          0.1
 * @versionDate:    2009-10-17
 * @date:             2009-10-17
 */
define("svzsolutions/maps/googlemaps/CustomOverlay", [], function() {

/**
 * SVZ Solutions GoogleMaps CustomOverlay class
 *
 */
dojo.declare('svzsolutions.maps.googlemaps.CustomOverlay', null,
/** @lends svzsolutions.maps.googlemaps.CustomOverlay.prototype */
{
    /**
   * Constructor
   *
   * @constructs
   * @param {Array} config
   * @return {Void}
   */
    constructor: function(config)
    {
        // Because of cross domain problems we cannot extend from this object directly
        this._googleOverlay                 = new google.maps.OverlayView();
        this._googleOverlay.draw            = this._draw;
        this._googleOverlay.remove          = this._remove;
        this._googleOverlay.getPosition     = this._getPosition;
        this._googleOverlay._config         = config;

        this._config                        = config;

        this._googleOverlay.latlng_         = config.position;
        this._googleOverlay.getDom          = this.getDom;
        google.maps.event.addListener(this._googleOverlay, 'click', dojo.hitch(this, 'onClick'));
        google.maps.event.addListener(this._googleOverlay, 'mouseover', dojo.hitch(this, 'onMouseOver'));

      // Once the LatLng and text are set, add the overlay to the map.  This will
      // trigger a call to panes_changed which should in turn call draw.
    },

    /**
     * Method setMap
     *
     * @param {Object} map
     * @return {Void}
     */
    setMap: function(map)
    {
        this._googleOverlay.setMap(map);
    },

    /**
     * Method onClick which is fired when the overlay is being clicked
     *
     * @event
     * @param {Object} event
     * @return {Void}
     */
    onClick: function(event)
    {
    },

    /**
   * Method onMouseOver which is fired when the overlay is being entered
   *
   * @event
   * @param {Object} event
   * @return {Void}
   */
  onMouseOver: function(event)
  {
  },

    /**
   * Method that draws the overlay on the map
   *
   * @param {Void}
   * @return {Void}
   */
    _draw: function()
    {
        // Get the div from the parent object
        var overlay = this.div_;

        if (!overlay)
        {
            overlay = this.div_ = dojo.create('div', { className: this._config.className, title: this._config.title });

            if (this._config.label && this._config.label != 'undefined')
                dojo.create('div', { className: 'sg-label', innerHTML: this._config.label }, overlay );

            google.maps.event.addDomListener(overlay, "click", dojo.hitch(this, function(event)
            {
                google.maps.event.trigger(this, "click");
            })
            );

            google.maps.event.addDomListener(overlay, "mouseover", dojo.hitch(this, function(event)
            {
                google.maps.event.trigger(this, "mouseover");
            })
            );

            // Then add the overlay to the DOM
            var panes = this.getPanes();
            panes.overlayImage.appendChild(overlay);
        }

        // Position the overlay
        var point           = this.getProjection().fromLatLngToDivPixel(this.getPosition());
        var overlayStyles   = {};

        if (point)
        {
            var correctionX = 0;
            var correctionY = 0;
            
            var iconWidth   = (this._config.typeConfig.iconSize ? this._config.typeConfig.iconSize.width : false);
            var iconHeight  = (this._config.typeConfig.iconSize ? this._config.typeConfig.iconSize.height : false);

            // For performance reasons retrieving the client width and height should be avoided when using large amounts of markers
            if (this._config.typeConfig.autoCenter || 
                this._config.typeConfig.autoCenterY || 
                this._config.typeConfig.autoCenterX)
            {
                if (iconWidth === false)
                    iconWidth = overlay.clientWidth;
                
                if (iconHeight === false)
                    iconHeight = overlay.clientHeight;

            }
            
            if (this._config.typeConfig.autoCenter)
            {
                correctionX = (iconWidth / 2);
                correctionY = (iconHeight / 2);
            }
            else if (this._config.typeConfig.autoCenterY)
            {
                correctionX = iconWidth;
                correctionY = (iconHeight / 2);
            }
            else if (this._config.typeConfig.autoCenterX)
            {
                correctionX = (iconWidth / 2);
                correctionY = iconHeight;
            }
            else
            {
                correctionX = this._config.typeConfig.correctionX;
                correctionY = this._config.typeConfig.correctionY;
            }

            overlayStyles.left = (point.x - correctionX) + 'px';
            overlayStyles.top  = (point.y - correctionY) + 'px';
        }

        overlayStyles.position = 'absolute';

        dojo.style(overlay, overlayStyles);

        return overlay;
    },

    /**
   * Method that removes the overlay from the map
   *
   * @param {Void}
   * @return {Void}
   */
  remove: function()
  {
    // Check if the overlay is on the map and needs to be removed.
    if (this._googleOverlay.div_)
    {
      dojo.destroy(this._googleOverlay.div_);
    }
  },

    /**
   * Method that removes the overlay from the map
   *
   * @param {Void}
   * @return {Void}
   */
  _remove: function()
  {
    // Check if the overlay is on the map and needs to be removed.
    if (this.div_)
    {
      dojo.destroy(this.div_);
    }
  },

    /**
   * Method that returns the position of the overlay on the map
   *
   * @param {Object} point
   * @return {Void}
   */
  setPosition: function(point)
  {
        if (svzsolutions.global.mapManager.isDebugMode())
        console.log('CustomOverlay: Setting a new position');

      this._googleOverlay.setPosition(point);
  },

    /**
   * Method that returns the position of the overlay on the map
   *
   * @param {Void}
   * @return {Object}
   */
  getPosition : function()
  {
   return this._googleOverlay.getPosition();
  },

    /**
   * Method that returns the position of the overlay on the map
   *
   * @param {Void}
   * @return {Object}
   */
  _getPosition : function()
  {
   return this.latlng_;
  }

});

});