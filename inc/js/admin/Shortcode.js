define("yog/admin/Shortcode", [], function() {

/**
 * YOG Admin Object class
 */
dojo.declare('yog.admin.Shortcode', null,
/**
 * @lends yog.admin.Object.prototype
 */
{
  /**
   * Constructor
   *
   * @constructs
   * @param {Void}
   * @return {yog.admin.Object}
   */
  constructor: function()
  {
    this._mapInstance     = yogMap;

    this._postTypesCheckboxes = dojo.query('input[name="shortcode_PostTypes[]"]');

    for (var i = 0; i < this._postTypesCheckboxes.length; i++)
    {
      dojo.connect(this._postTypesCheckboxes[i], 'onclick', this, '_onInputFieldsChange');
    }

    this._shortcodeElem   = dojo.byId('yogShortcode');
    this._inputLatitude   = dojo.byId('shortcode_Latitude');
    this._inputLongitude  = dojo.byId('shortcode_Longitude');
    this._inputWidth      = dojo.byId('shortcode_Width');
    this._inputWidthUnit  = dojo.byId('shortcode_WidthUnit');
    this._inputHeight     = dojo.byId('shortcode_Height');
    this._inputHeightUnit = dojo.byId('shortcode_HeightUnit');

    if (this._inputLatitude && this._inputLongitude)
    {
      dojo.connect(this._inputLatitude, 'onchange', this, '_onMarkerGeocodeInputFieldsChange');
      dojo.connect(this._inputLongitude, 'onchange', this, '_onMarkerGeocodeInputFieldsChange');
      dojo.connect(this._inputWidth, 'onchange', this, '_onInputFieldsChange');
      dojo.connect(this._inputWidthUnit, 'onchange', this, '_onInputFieldsChange');
      dojo.connect(this._inputHeight, 'onchange', this, '_onInputFieldsChange');
      dojo.connect(this._inputHeightUnit, 'onchange', this, '_onInputFieldsChange');

        // dojo.connect(this._inputAddressSearch, 'onchange', this, 'onMarkerGeocodeAddressSearchChange');
    }

    // Connecting to the marker dragend event
    dojo.connect(this._mapInstance, 'onMarkerDragEnd', this, '_onMarkerDragEnd');

    dojo.connect(this._mapInstance, 'onZoomLevelChanged', this, '_onZoomLevelChanged');

    dojo.connect(this._mapInstance, 'onMapLoaded', this, '_onMapLoaded');
  },

  /**
   * Method _onMapLoaded
   *
   * @param {Void}
   * @return {Void}
   */
  _onMapLoaded: function()
  {
    this.generateShortcode();
  },

  /**
   * Method which is called when the width or height field is changed
   *
   * @param {Void}
   * @return {Void}
   */
  _onInputFieldsChange: function()
  {
    this.generateShortcode();
  },

  /**
   * Method which is called when the zoomlevel has been changed
   *
   * @param {Void}
   * @return void
   */
  _onZoomLevelChanged: function()
    {
      console.log('SDSS', this._mapInstance.getMap());

        this.generateShortcode();
    },

  /**
   * Method which is called when a draggable marker is stopped being moved on the screen
   *
   * @param object marker
   * @return void
   */
    _onMarkerDragEnd: function(marker)
    {
        var position                     = marker.getPosition();

        this._mapInstance.setCenter(position);

        this._onMarkerPositionChanged();
    },

    /**
     * Method which takes the current map settings and generates a shortcode
     *
     * @param {Void}
     * @return {Void}
     */
    generateShortcode: function()
    {
      var newShortcode = '[yog-map ';

      var valuePostTypes = [];

      // Post types
      for (var i = 0; i < this._postTypesCheckboxes.length; i++)
      {
        var checked = dojo.attr(this._postTypesCheckboxes[i], 'checked');
        var value   = dojo.attr(this._postTypesCheckboxes[i], 'value');

        if (checked === true)
        {
          valuePostTypes.push(value);
        }
      }

      // Only render the post type tag in case 1 or more are checked and in case not all are checked
      if (valuePostTypes.length > 0 && valuePostTypes.length < this._postTypesCheckboxes.length)
      {
        newShortcode += ' post_types="' + valuePostTypes.join(',') + '"';
      }

      var markers = this._mapInstance.getMarkersStatic().getStaticByType('admin');

      // Center latitude / longitude
      if (markers[0]) // Assuming we only have 1 marker which will return
      {
          var geocode = markers[0].getPosition();

          newShortcode += ' center_latitude="' + geocode.getLatitude() + '"';
          newShortcode += ' center_longitude="' + geocode.getLongitude() + '"';
      }

      // Zoomlevel
      newShortcode += ' zoomlevel="' + this._mapInstance.getZoomLevel() + '"';

      // Map type
      newShortcode += ' map_type="' + this._mapInstance.getMap().getMapTypeId() + '"';

      // Width
      var width  = dojo.attr(this._inputWidth, 'value');

      // Height
      var height = dojo.attr(this._inputHeight, 'value');

      // Width and WidthUnit
      if (width > 0)
      {
        newShortcode += ' width="' + width + '"';

        var widthUnit = this._inputWidthUnit.options[this._inputWidthUnit.selectedIndex].value;

        // WidthUnit
        newShortcode += ' width_unit="' + widthUnit + '"';
      }

      // Height and HeightUnit
      if (height > 0)
      {
        newShortcode += ' height="' + height + '"';

        var heightUnit = this._inputHeightUnit.options[this._inputHeightUnit.selectedIndex].value;

        // HeightUnit
        newShortcode += ' height_unit="' + heightUnit + '"';
      }

      console.log('AAA', this._mapInstance);

      newShortcode += ']';

      this._shortcodeElem.innerHTML = newShortcode;
    },

  /**
   * Method which is called when either the latitude or longitude field is changed
   *
   * @param object event
   * @return void
   */
    _onMarkerGeocodeInputFieldsChange: function(event)
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

                    this._onMarkerPositionChanged();
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
    _onMarkerPositionChanged: function()
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

        this.generateShortcode();
    }

});

});
