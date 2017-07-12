/**
 * Copyright (c) 2009, SVZ Solutions All Rights Reserved.
 * Available via BSD license, see license file included for details.
 *
 * @title:                SVZ Solutions GoogleMaps
 * @authors:           Stefan van Zanden <info@svzsolutions.nl>
 * @company:          SVZ Solutions
 * @contributers:
 * @version:          0.2
 * @versionDate:    2010-02-06
 * @date:             2010-02-06
 */
define("svzsolutions/generic/RequestManager", [], function() {

/**
 * SVZ Request manager class
 *
 */
dojo.declare('svzsolutions.generic.RequestManager', null,
/** @lends svzsolutions.generic.RequestManager.prototype */
{
    /**
   * Constructor
   *
   * @constructs
   * @param {Void}
   * @return {svzsolutions.generic.RequestManager}
   */
    constructor: function()
    {
        this._requests = [];
    },

    /**
   * Method get which does a xhrGet request or cancels a previous one
   *
   * @param {Object} xhrArgs
   * @param {String} name
   * @return {Object} Returned from dojo.xhrGet
   */
    get: function(xhrArgs, name)
    {
        if (!name || !xhrArgs)
            return;

        this.cancel(name);

        return (this._requests[name] = dojo.xhrGet(xhrArgs));
    },

    /**
     * Method which cancels a request
     *
     * @param {String} name
     * @return {Void}
     */
    cancel: function(name)
    {
        if (!name)
            return;

        if (this._requests[name])
            this._requests[name].cancel();

    }

});

});