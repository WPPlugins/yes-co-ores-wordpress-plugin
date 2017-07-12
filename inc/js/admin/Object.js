define("yog/admin/Object", [], function() {

/**
 * YOG Admin Object class
 */
dojo.declare('yog.admin.Object', null,
/**
 * @lends yog.admin.Object.prototype
 */
{
  /**
   * Constructor
   *
   * @constructs
   * @param {String} postType
   * @return {yog.admin.Object}
   */
  constructor: function(postType)
  {
    this._mapInstance = yogMap;

    this._inputLatitude   = dojo.byId(postType + '_Latitude');
    this._inputLongitude  = dojo.byId(postType + '_Longitude');

    if (this._inputLatitude && this._inputLongitude)
    {
      dojo.connect(this._inputLatitude, 'onchange', this, 'onMarkerGeocodeInputFieldsChange');
      dojo.connect(this._inputLongitude, 'onchange', this, 'onMarkerGeocodeInputFieldsChange');

        // dojo.connect(this._inputAddressSearch, 'onchange', this, 'onMarkerGeocodeAddressSearchChange');
    }

    // Connecting to the marker dragend event
    dojo.connect(this._mapInstance, 'onMarkerDragEnd', this, 'onMarkerDragEnd');
  },

   /**
   * Method which is called when a draggable marker is stopped being moved on the screen
   *
   * @param object marker
   * @return void
   */
    onMarkerDragEnd: function(marker)
    {
        var position                     = marker.getPosition();

        this._mapInstance.setCenter(position);

        this.onMarkerPositionChanged();
    },

  /**
   * Method which is called when either the latitude or longitude field is changed
   *
   * @param object event
   * @return void
   */
    onMarkerGeocodeInputFieldsChange: function(event)
    {
        var markers = this._mapInstance.getMarkersStatic().getStaticByType('admin');

        if (markers[0]) // Assuming we only have 1 marker which will return
        {
            var inputLatitude     = parseFloat(dojo.attr(this._inputLatitude, 'value'));
            var inputLongitude     = parseFloat(dojo.attr(this._inputLongitude, 'value'));

            if (!isNaN(inputLatitude) && !isNaN(inputLongitude))
            {
                var geocode = new svzsolutions.generic.Geocode(inputLatitude, inputLongitude);

                if (geocode.isValid())
                {
                    var geocode = new svzsolutions.generic.Geocode(inputLatitude, inputLongitude);

                    this._mapInstance.setCenter(geocode);

                    markers[0].setPosition(geocode);

                    dojo.removeClass(this._inputLatitude, this.ERROR_CLASSNAME);
                    dojo.removeClass(this._inputLongitude, this.ERROR_CLASSNAME);

                    dojo.attr(this._inputLatitude, 'title', '');
                    dojo.attr(this._inputLongitude, 'title', '');

                    this.onMarkerPositionChanged();
                }
                else
                {
                    if (!geocode.isValidLatitude())
                    {
                        dojo.addClass(this._inputLatitude, this.ERROR_CLASSNAME);
                        dojo.attr(this._inputLatitude, 'title', 'This value needs to be between -90 and 90 degree');
                    }

                    if (!geocode.isValidLongitude())
                    {
                        dojo.addClass(this._inputLongitude, this.ERROR_CLASSNAME);
                        dojo.attr(this._inputLongitude, 'title', 'This value needs to be between -180 and 180 degree');
                    }

                }
            }
        }
    },

    /**
   * Method which is called whenever the position of the marker is changed to update data
   *
   * @param void
   * @return void
   */
    onMarkerPositionChanged: function()
    {
        var markers = this._mapInstance.getMarkersStatic().getStaticByType('admin');

        if (markers[0]) // Assuming we only have 1 marker which will return
        {
            var geocode = markers[0].getPosition();

            dojo.attr(this._inputLatitude, 'value', geocode.getLatitude());
          dojo.attr(this._inputLongitude, 'value', geocode.getLongitude());

          // Assuming it will be correct now because we get the info from mister Google himself
          dojo.removeClass(this._inputLatitude, this.ERROR_CLASSNAME);
            dojo.removeClass(this._inputLongitude, this.ERROR_CLASSNAME);

            dojo.attr(this._inputLatitude, 'title', '');
            dojo.attr(this._inputLongitude, 'title', '');

          //dojo.connect(geocode, 'onAddressResult', this, 'onMarkerGeocodeAddressResult');
            //geocode.findAddress();
        }
    }

});

});
