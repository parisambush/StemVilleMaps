<!DOCTYPE html>
<html>
  <?php
    include_once 'makeMapDat.php';
    makeMapData();
    //echo json_encode($mapArray);
  ?>
  <head>
    <title>StemVille Map Render Tester</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <script src="raphael-min.js" type="text/javascript" charset="UTF-8"></script>
    <script type="text/javascript" charset="UTF-8">
        var sau = {}, sauObj = <?php echo json_encode($mapArray); ?>;
        window.onload = function() {
            var map = Raphael(0, 0, 1000, 1000);
            var attr = {
                stroke: "#666",
                "stroke-width": 1,
                "stroke-linejoin": "round"
            };
            
            for (var region in sauObj) {
                sau[region] = map.path(sauObj[region]);
            }
            //sau["SA-02"] = map.path(sauObj["SA-02"]);
            //sau["SA-4"] = map.path(sauObj["SA-4"]);
            //sau["SA-07"] = map.path(sauObj["SA-07"]);
            //sau["SA-08"] = map.path(sauObj["SA-08"]);
            for (var region in sau) {
                //sau[region].translate(500, 300);
                //sau[region].scale(2, 2);
                //sau[region].rotate(-90);
            }
        };
        
    </script>
  </head>
  <body>
    
  </body>
</html>
