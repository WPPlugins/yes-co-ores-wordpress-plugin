/**
 * Copyright (c) 2009, SVZ Solutions All Rights Reserved.
 * Available via BSD license, see license file included for details.
 *
 * @title:                SVZ Solutions Loader
 * @authors:           Stefan van Zanden <info@svzsolutions.nl>
 * @company:          SVZ Solutions
 * @contributers:
 * @version:          0.1
 * @versionDate:    2010-01-21
 * @date:             2010-01-21
 */
define("svzsolutions/generic/Loader", [], function() {

/**
 * SVZ Loader class
 *
 */
dojo.declare('svzsolutions.generic.Loader', null,
/** @lends svzsolutions.generic.Loader.prototype */
{
    /**
     * @constant
     * @default sg-loader-underlay-holder
     */
    LOADER_UNDERLAY_HOLDER_CLASS    : 'sg-loader-underlay-holder',

    /**
     * @constant
     * @default sg-loader-holder
     */
    LOADER_HOLDER_CLASS                        : 'sg-loader-holder',

    /**
     * @constant
     * @default sg-loader-cancel
     */
    LOADER_CANCEL_ELEM_CLASS            : 'sg-loader-cancel',

    /**
     * @constant
     * @default 3000
     */
    DEFAULT_CANCEL_DELAY                    : 3000,

    /**
   * Constructor
   *
   * @constructs
   * @param {String} type
   * @param {String|DomNode} refNode
   * @param {String|Number} position (optional)
   * @return {svzsolutions.generic.Loader}
   */
  constructor: function(type, refNode, position, config)
  {
        if (!config)
            config = {};

        this._config = config;

        loaderUnderlayHolderClassName = this.LOADER_UNDERLAY_HOLDER_CLASS;
        loaderHolderClassName                 =    this.LOADER_HOLDER_CLASS;

        this._cancelDelayHandler            = null;

        if (type)
        {
            loaderUnderlayHolderClassName += ' sg-' + type + '-underlay-holder';
            loaderHolderClassName                 += ' sg-' + type + '-holder';
        }

        this._underlayElem = dojo.create('div', { className: loaderUnderlayHolderClassName });
        this._elem                 = dojo.create('div', { className: loaderHolderClassName });

        if (refNode)
            this.placeAt(refNode, position);

  },

  /**
   * Private method _onCancel which is fired on cliking of the cancel element
   *
   * @param {Object} event
   * @return {Void}
   */
  _onCancel: function(event)
  {
      dojo.stopEvent(event);

      this.hide();

      this.onCancel(event);
  },

  /**
   * Method which places and shows the cancel element in the loader
   *
   * @param {Void}
   * @return {Void}
   */
  _showCancelElem: function()
  {
      if (this._cancelElem)
      {
          dojo.style(this._cancelElem, 'display', 'block');
      }
      else
      {
          var textCancel = 'Cancel';

          if (this._config.textCancel)
              textCancel = this._config.textCancel;

          this._cancelElem      = dojo.create('a', { className: this.LOADER_CANCEL_ELEM_CLASS, innerHTML: textCancel, href: '#' }, this._elem);
          this._cancelHandle    = dojo.connect(this._cancelElem, 'onclick', this, '_onCancel');

      }
  },

  /**
   * Method onCancel which is fired when the cancel element is clicked
   *
   * @event
   * @param {Object} event
   * @return {Void}
   */
  onCancel: function(event)
  {
      // Overwritable
  },

  /**
   * Method show which whill show the loader element
   *
   * @param {Void}
   * @return {Void}
   */
  show: function()
  {
      dojo.style(this._elem, 'display', 'block');
      dojo.style(this._underlayElem, 'display', 'block');

        var temp = dojo.hitch(this, function()
        {
            this._showCancelElem();
        });

        if (this._config.showCancelDelay || this._config.showCancelDelay >= 0)
            this._cancelDelayHandler = window.setTimeout(temp, this._config.showCancelDelay);
        else
            this._cancelDelayHandler = window.setTimeout(temp, this.DEFAULT_CANCEL_DELAY);


  },

  /**
   * Method hide which whill hide the loader element
   *
   * @param {Void}
   * @return {Void}
   */
  hide: function()
  {
      if (this._cancelDelayHandler)
      {
          window.clearTimeout(this._cancelDelayHandler);
      }

      if (this._cancelElem)
          dojo.style(this._cancelElem, 'display', 'none');

      dojo.style(this._elem, 'display', 'none');
      dojo.style(this._underlayElem, 'display', 'none');
  },

  /**
   * Method placeAt which places the loader into the provided placeholder, matches dojo.place params
   *
   * @param {String|DomNode} refNode
   * @param {String|Number} [position]
   */
  placeAt: function(refNode, position)
  {
      dojo.place(this._underlayElem, refNode, position);
      dojo.place(this._elem, refNode, position);
  },

  /**
   * Method destroy which cleans up the loader
   *
   * @param {Void}
   * @return {Boolean}
   */
  destroy: function()
  {
      if (this._cancelDelayHandler)
          window.clearTimeout(this._cancelDelayHandler);

      dojo.destroy(this._elem);
      dojo.destroy(this._underlayElem);

      if (this._cancelHandle)
          dojo.disconnect(this._cancelHandle);

      return true;
  }

});

});