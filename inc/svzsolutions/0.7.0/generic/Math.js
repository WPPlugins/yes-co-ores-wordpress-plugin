/**
 * Copyright (c) 2010, SVZ Solutions All Rights Reserved.
 * Available via BSD license, see license file included for details.
 *
 * @title:                SVZ Solutions Math
 * @authors:           Stefan van Zanden <info@svzsolutions.nl>
 * @company:          SVZ Solutions
 * @contributers:
 * @version:          0.8
 * @versionDate:    2010-09-25
 * @date:             2010-09-25
 */
define("svzsolutions/generic/Math", [], function() {

/**
 * SVZ Loader class
 *
 */
dojo.declare('svzsolutions.generic.Math', null,
/** @lends svzsolutions.generic.Math.prototype */
{
    /**
     * @constant
     * @default 1.609344
     */
    MILES_TO_KILOMETRES_EQUATION: 1.609344,

    /**
   * Constructor
   *
   * @constructs
   * @param {Void}
   * @return {svzsolutions.generic.Math}
   */
    constructor: function()
  {
  },

  /**
   * Method that converts a value in metres to a value in kilometres
   *
   * @param {Float|Integer} number
   * @param {Integer} numberOfDecimals
   * @return {Float|Integer} kilometres
   */
  roundNumber: function(number, numberOfDecimals)
  {
      if (isNaN(numberOfDecimals) || numberOfDecimals < 1)
          numberOfDecimals = 0;

      var equation     = Math.pow(10, numberOfDecimals);
    var newNumber = Math.round(number * equation) / equation;

    return newNumber;
  },

  /**
   * Method that converts a value in metres to a value in kilometres
   *
   * @param {Float|Integer} metres
   * @return {Float|Integer} kilometres
   */
  metresToKilometres: function(metres)
  {
    var kilometres = metres / 1000;

    return kilometres;
  },

  /**
   * Method that converts a value in meters to a value in miles
   *
   * @param {Float|Integer} metres
   * @return {Float|Integer} miles
   */
  metresToMiles: function(metres)
  {
    var kilometres     = this.metresToKilometres(metres);
    var miles             = this.kilometresToMiles(kilometres);

    return miles;
  },

  /**
   * Method that converts a value in kilometres to a value in miles
   *
   * @param {Float|Integer} kilometres
   * @return {Float|Integer} miles
   */
  kilometresToMiles: function(kilometres)
  {
    var miles = kilometres / this.MILES_TO_KILOMETRES_EQUATION;

    return miles;
  },

  /**
   * Method that converts a value in miles to a value in kilometres
   *
   * @param {Float|Integer} miles
   * @return {Float|Integer} kilometres
   */
  milesToKilometres: function(miles)
  {
    var kilometres = miles * this.MILES_TO_KILOMETRES_EQUATION;

    return kilometres;
  }

});

});