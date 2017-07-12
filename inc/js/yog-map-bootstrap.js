require({
    baseUrl: yogBaseUrl + "/",
    packages: [
      { name: "svzsolutions", location: yogLocationPrefix + "svzsolutions/0.7.2" },
      { name: "yog", location: yogLocationPrefix + "js/" }
    ]

}, [ "dojo/ready", "dojo" ], function(ready)
{
    require([ "svzsolutions/all" ], function() {

        ready(function() {

          if (yogJsOnLoad)
          {
            yogJsOnLoad(ready);
          }

          yogMapManager  = new svzsolutions.maps.MapManager();

          // The SVZ_Solutions_Maps_Google_Maps_Map php class will generate a config object depending on your settings for you,
          // this generated object can be encoded into a JSON string and can be put encoded into the svzsolutions.maps.MapManager object.
          yogMap             = yogMapManager.initByConfig(yogJsMapConfig);

          map                = yogMap; // 2013-02-01: Old reference for older themes

          // Startup all the maps (call after subscribing within your extensions)
          yogMapManager.startup();

          if (yogJsExtraAfterLoad)
          {
            yogJsExtraAfterLoad(ready);
          }

        });

    });

});