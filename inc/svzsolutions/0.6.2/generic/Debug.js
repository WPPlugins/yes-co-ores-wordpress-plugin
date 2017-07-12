/**
 * Copyright (c) 2010, SVZ Solutions All Rights Reserved.
 * Available via BSD license, see license file included for details.
 *
 * @title:                SVZ Solutions Geocode
 * @author:             Stefan van Zanden <info@svzsolutions.nl>
 * @company:          SVZ Solutions
 * @contributers:
 * @version:          0.6.2
 * @versionDate:    2010-11-15
 * @date:             2010-11-15
 */
define("svzsolutions/generic/Debug", [], function() {

/**
 * SVZ Debug class
 */
dojo.declare('svzsolutions.generic.Debug', null,
/**
 * @lends svzsolutions.generic.Debug.prototype
 */
{
    /**
   * Constructor
   *
   * @constructs
   * @param {Void}
   * @return {Void}
   */
    constructor: function()
  {

  },

    /**
     * Method log which will log the message to the console in case debug mode is on and console is available
     *
     * @param {mixed} message
     * @param {String} messageType
     * @return {Void}
     */
  log: function(message, messageType)
    {
        if (typeof console != 'undefined')
        {
            switch (messageType)
            {
                case 'error':

                  console.error(message);

                break;

                default:

                  console.log(message);

                break;
            }
        }
    }

});

});