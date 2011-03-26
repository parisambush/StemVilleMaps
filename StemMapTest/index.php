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
        var mapCanvas, map = {}, mapObj = <?php echo json_encode($mapArray); ?>;
        // Map canvas dimensions
        var mapX = 100, mapY = 100, mapWidth = 1000, mapHeight = 1000;

        window.onload = function() {
            mapCanvas = Raphael(mapX, mapY, mapWidth, mapHeight);
            var attr = {
                stroke: "#666",
                "stroke-width": 2,
                "stroke-linejoin": "round"
            };
            
            for (var path in mapObj) {
                map[path] = mapCanvas.path(mapObj[path]);
            }

            for (var region in map) {
                // gotta wrap this or all sorts of bad shit is gonna happen!
                (function(r) {

                    map[r].attr({fill: "#fff"});
                    map[r].node.onmouseover = function() { map[r].animate({fill: "#FF0000"}, 5000, function() { map[r].animate({fill: "#FFF"}, 3000); }); };
					map[r].node.onclick = function(evt) {
					    console.log(r); 
					    console.log(evt);
                        var boomPath = "m 403.62449,546.96288 c 0,5.90164 -0.74055,14.24946 -36.91226,14.14402 -35.01349,-0.10205 -34.3513,-8.6539 -34.43471,-15.353 -0.27445,-22.04287 16.34364,-39.64304 36.04556,-39.30919 19.70192,0.33385 35.30141,18.47337 35.30141,40.51817 z";
					    mapCanvas.circle(0, 0, 20).attr({fill: "#FFFF00"}).animateAlong("M0 0L"+evt.clientX+" "+evt.clientY, 500, true, function()
                            {   //this.animate({cy: -20}, 800);
                                this.remove();
                                var flash = mapCanvas.rect(mapX, mapY, mapWidth, mapHeight).attr({fill: "white", "stroke-width": 0, "opacity": 0.7});
                                flash.animate({opacity: -0.1}, 1000, function() {flash.remove();});
                                var boom = mapCanvas.path(boomPath).attr({fill: "orange", "stroke-width": 0});
                                boom.translate(evt.clientX - boom.getBBox().x -110, evt.clientY - boom.getBBox().y -110);
                                var a = boom.animate({opacity: -0.2}, 2500);
                                boom.animateWith(a, {scale: 4.0}, 2500, function() {boom.remove();});
                            });
					};
                })(region);
            }
        };
        
    </script>
  </head>
  <body>
    
  </body>
</html>
