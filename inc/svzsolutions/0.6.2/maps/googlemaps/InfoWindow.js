/**
 * Copyright (c) 2009, SVZ Solutions All Rights Reserved.
 * Available via BSD license, see license file included for details.
 *
 * @title:                SVZ Solutions - Google Maps Info Window
 * @authors:           Stefan van Zanden <info@svzsolutions.nl>
 * @company:          SVZ Solutions
 * @contributers:
 * @version:          0.1
 * @versionDate:    2009-10-17
 * @date:             2009-10-17
 */
define("svzsolutions/maps/googlemaps/InfoWindow", ["svzsolutions/generic/Loader"], function() {

/**
 * SVZ GoogleMaps InfoWindow class
 *
 * TODO:
 * - Fix dojo.query using the infoWindowContent HTML element as path to search in instead of the entire body
 */
dojo.declare('svzsolutions.maps.googlemaps.InfoWindow', null,
/** @lends svzsolutions.maps.googlemaps.InfoWindow.prototype */
{
    /**
     * @constant
     * @default sg-component-tabs-holder
     */
    COMPONENT_TAB_HOLDER_CLASSNAME          : 'sg-component-tabs-holder',

    /**
     * @constant
     * @default sg-component-tab-links-holder
     */
  COMPONENT_TAB_LINKS_HOLDER_CLASSNAME      : 'sg-component-tab-links-holder',

    /**
     * @constant
     * @default sg-component-tab-link-load-dynamic
     */
  COMPONENT_TAB_LINK_LOAD_DYNAMIC_CLASSNAME : 'sg-component-tab-link-load-dynamic',

    /**
     * @constant
     * @default sg-component-tab-contents-holder
     */
  COMPONENT_TAB_CONTENTS_HOLDER_CLASSNAME   : 'sg-component-tab-contents-holder',

    /**
     * @constant
     * @default sg-component-tab-content-holder
     */
  COMPONENT_TAB_CONTENT_HOLDER_CLASSNAME    : 'sg-component-tab-content-holder',

    /**
     * @constant
     * @default sg-component-list-holder
     */
  COMPONENT_LIST_HOLDER_CLASSNAME           : 'sg-component-list-holder',

    /**
     * @constant
     * @default sg-component-list-item-holder
     */
  COMPONENT_LIST_ITEM_HOLDER_CLASSNAME      : 'sg-component-list-item-holder',

    /**
     * @constant
     * @default sg-info-window-content-main-holder
     */
  MAIN_HOLDER_CLASSNAME                     : 'sg-info-window-content-main-holder',

  /**
   * @constant
   * @default sg-info-window-content-config
   */
  CONFIG_CLASSNAME                          : 'sg-info-window-content-config',

  _tabContent                               : false,
  _tabLinks                                 : false,

  /**
   * Constructor
   *
   * @constructs
   * @param {String} infoWindowContent
   * @return {svzsolutions.maps.googlemaps.InfoWindow}
   */
  constructor: function(infoWindowContent, googleInfoWindowRef)
  {
      if (svzsolutions.global.mapManager.isDebugMode())
        console.log('InfoWindow: Constructor');

      var infoWindowContentHolder = dojo.create('div');

      dojo.place(infoWindowContent, infoWindowContentHolder);

      this._googleInfoWindowRef   = googleInfoWindowRef;
      this._tabLinks              = false;
      this._tabContent            = false;
      this._loader                = false;
      this._loaderTab             = false;
      this._mainHolder            = false;
      this._content               = infoWindowContentHolder;
      this._requestManager        = new svzsolutions.generic.RequestManager();
      this._currentTabContent     = false;
      this._marker                = false;
      this._config                = false;

      var mainHolder              = dojo.query('.' + this.MAIN_HOLDER_CLASSNAME, this._content);

      if (mainHolder && mainHolder[0])
        this._mainHolder = mainHolder[0];

      var config                  = dojo.query('.' + this.CONFIG_CLASSNAME, this._content);

      if (config && config[0])
        this._config              = dojo.fromJson(config[0].innerHTML);

      this.init();
  },

  /**
   * Method getConfig which returns the config if it is available
   *
   * @param {Void}
   * @return {Mixed}
   */
  getConfig: function()
  {
    return this._config;
  },

  /**
   * Method getElem which returns the base element for this info window
   *
   * @param {Void}
   * @return {HTMLDomElement} elem
   */
  getElem: function()
  {
      return this._content;
  },

    /**
   * Method Init which init the created content
   *
   * @param {Void}
   * @return {Void}
   */
  init: function()
  {
      if (svzsolutions.global.mapManager.isDebugMode())
        console.log('InfoWindow: Init');

        var tabComponentElem = dojo.query('.' + this.COMPONENT_TAB_HOLDER_CLASSNAME, this._content);

        if (tabComponentElem && tabComponentElem[0])
            this.initTabComponent(tabComponentElem[0]);

        var listComponentElem = dojo.query('.' + this.COMPONENT_LIST_HOLDER_CLASSNAME, this._content);

        if (listComponentElem && listComponentElem[0])
            this.initListComponent(listComponentElem[0]);

        this.initCustomContent();
  },

  /**
   * Method that instantiates the html for an tab component
   *
   * TODO:
   * - Create a tab manager class to work handle this
   *
   * @param {HTMLDomElement} elem
   * @return {Void}
   */
  initTabComponent : function(elem)
  {
        this._tabLinks         = dojo.query('.' + this.COMPONENT_TAB_LINKS_HOLDER_CLASSNAME + ' a', elem);
        this._tabContent     = dojo.query('.' + this.COMPONENT_TAB_CONTENTS_HOLDER_CLASSNAME + ' .' + this.COMPONENT_TAB_CONTENT_HOLDER_CLASSNAME, elem);

        // Iterate through all the tab links
        for (var i = 0; i < this._tabLinks.length; i++)
        {
            this._tabLinks[i].linkIndex = i;

            dojo.connect(this._tabLinks[i], 'onclick', this, function(event)
              {
                    dojo.stopEvent(event);

                    if (event.target)
                        this.activateTab(event.target.linkIndex);

              });
        }
  },

  /**
   * Method that instantiates the html for an list component
   *
   * TODO:
   * - Create a list manager class to work handle this
   *
   * @param {HTMLDomElement} elem
   * @return {Void}
   */
  initListComponent : function(elem)
  {
        this.listLinks         = dojo.query('.' + this.COMPONENT_LIST_ITEM_HOLDER_CLASSNAME + ' a', elem);

        // Iterate through all the list links
        for (var i = 0; i < this.listLinks.length; i++)
        {
            dojo.connect(this.listLinks[i], 'onclick', this, function(event)
                {
                    dojo.stopEvent(event);

                    var dataLoadUrl = dojo.attr(event.target, 'href');

                    this.loadData(dataLoadUrl);
                });
        }
  },

  /**
   * Method that is fired when the content has been loaded and initialised, may be overriden or connected to.
   *
   * @event
   * @param {Void}
   * @return {Void}
   */
  initCustomContent : function()
  {

  },

  /**
   * Method that is fired when the content tab has been loaded and initialised, may be overriden or connected to.
   *
   * @event
   * @param {Void}
   * @return {Void}
   */
  initCustomTabContent : function()
  {

  },

  /**
   * Method that activates the current selected tab
   *
   * @param {Integer} index
   * @return {Void}
   */
  activateTab: function(index)
  {
        if (svzsolutions.global.mapManager.isDebugMode())
        console.log('InfoWindow: activating tab with index [', index, ']');

      var activeHref = '';
      var loadAjax   = false;

      // Iterate through all the tab links
      for (var i = 0; i < this._tabLinks.length; i++)
        {
          dojo.removeClass(this._tabLinks[i], 'active');

          if (i == index)
          {
              dojo.addClass(this._tabLinks[i], 'active');

              activeHref = dojo.attr(this._tabLinks[i], 'href');

              if (dojo.hasClass(this._tabLinks[i], this.COMPONENT_TAB_LINK_LOAD_DYNAMIC_CLASSNAME))
                  loadAjax = true;

          }
        }

      // Iterate through all the tab content
      for (var i = 0; i < this._tabContent.length; i++)
        {
          dojo.removeClass(this._tabContent[i], 'active');

          if (i == index)
          {
              dojo.addClass(this._tabContent[i], 'active');
              this._currentTabContent = this._tabContent[i];

          }

        }

      if (loadAjax)
          this.loadTabData(activeHref);
        else
            this._requestManager.cancel('infoWindowLoadTabData');

  },

  /**
   * Method that loads additional content in the info window
   *
   * @param {String} dataLoadUrl
   * @return {Void}
   */
  loadData: function(dataLoadUrl)
  {
      if (!dataLoadUrl)
          return false;

    var xhrArgs =
    {
      url: dataLoadUrl,
      handleAs: "json",
      load: dojo.hitch(this, this.loadDataCallback),
      error: dojo.hitch(this, function(error)
      {
              this._loader.destroy();
              this._loader = null;

              if (error.dojoType == 'cancel')
                  return;

              if (svzsolutions.global.mapManager.isDebugMode())
          console.log("An unexpected error occurred: " + error);

      })
    };

    this._loader = new svzsolutions.generic.Loader('infowindow-load-data');
    this._loader.onCancel                             = dojo.hitch(this, function(event)
          {
              this._requestManager.cancel('infoWindowLoadData');
          });

    this._loader.placeAt(this._mainHolder, 'first');

    this._loader.show();

    // Call the asynchronous xhrGet
    this._requestManager.get(xhrArgs, 'infoWindowLoadData');
  },

  /**
   * Method that executes when the request for data has been finished
   *
   * @param {Object} data JSON object
   * @return {Void}
   */
  loadDataCallback: function(data)
  {
       if (this._mainHolder)
      {
        if (data && data.content)
        {
            var domElem = dojo._toDom(data.content);

            dojo.place(domElem, this._mainHolder, 'only');

            this.initTabComponent(domElem);

            this.initCustomContent();
        }
      }

  },

  /**
   * Method that loads additional content in the info window
   *
   * @param {String} dataLoadUrl
   * @return {Void}
   */
  loadTabData: function(dataLoadUrl)
  {
        if (svzsolutions.global.mapManager.isDebugMode())
        console.log('InfoWindow: load tab data from url [', dataLoadUrl, ']');

      if (!dataLoadUrl)
          return false;

    var xhrArgs =
    {
      url: dataLoadUrl,
      handleAs: "json",
      load: dojo.hitch(this, this.loadTabDataCallback),
      error: dojo.hitch(this, function(error)
      {
          if (this._loaderTab)
          {
              this._loaderTab.destroy();
                this._loaderTab = null;
          }

            if (error.dojoType == 'cancel')
                return;

        console.log("An unexpected error occurred: " + error);
      })
    };

    if (!this._loaderTab)
    {
        this._loaderTab                                                 = new svzsolutions.generic.Loader('infowindow-load-tab-data', this._currentTabContent, 'first');
      this._loaderTab.onCancel                                 = dojo.hitch(this, function(event)
          {
              this._requestManager.cancel('infoWindowLoadTabData');
          });
    }

    this._loaderTab.show();

    // Call the asynchronous xhrGet
    this._requestManager.get(xhrArgs, 'infoWindowLoadTabData');
  },

  /**
   * Method that executes when the request for data has been finished
   *
   * @param {Object} data JSON Object
   * @return {Void}
   */
  loadTabDataCallback: function(data)
  {
       if (this._currentTabContent)
      {
        if (data && data.content)
        {
            var domElem = dojo._toDom(data.content);

            dojo.place(domElem, this._currentTabContent, 'only');

            this.initCustomTabContent();
        }
      }

       this._loaderTab.destroy();
       this._loaderTab = null;
  },

  /**
   * Method setMarker which sets the marker attached to this info window
   *
   * @param {Object} marker
   * @return {Void}
   */
  setMarker: function(marker)
  {
        if (svzsolutions.global.mapManager.isDebugMode())
        console.log('InfoWindow: setting marker to [', marker, ']');

      this._marker = marker;
  },

  /**
   * Method getMarker which gets the marker attached to this info window
   *
   * @param {Void}
   * @return {Object} marker
   */
  getMarker: function()
  {
      return this._marker;
  },

  /**
   * Method destroy which will close and remove the infowindow from the map and cleanup
   *
   * @param void
   * @return void
   */
  destroy: function()
  {
    if (this._googleInfoWindowRef)
      this._googleInfoWindowRef.close();

  }

});

});