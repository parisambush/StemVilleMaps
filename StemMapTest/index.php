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

            for (var region in sau) {
                sau[region].scale(6, 4, 600, 400);
            }
        };
        
    </script>
  </head>
  <body>
    
  </body>
</html>
