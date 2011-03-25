<?php
    $mapArray = array();
    $isoCountryCode;
    $currentRegionCode;
    $INPUT_FILE = 'SAU_2_MAP.xml';

    include 'getBaselineData.php';
    makeBaselineData();
    
    echo "Baseline Data (NSEW): ".$maxNlat.", ".$minSlat.", ".$maxElon.", ".$minWlon."<br />";

    // Mercator projection test vars
    //$maxNlat = 0;
    //$minSlat = 0;
    //$maxElon = 0;
    //$minWlon = 0;
    
    // This function parses an xml map file from stem containing gml data and creates an associative array
    // containing the iso-3166-2 region name paired with SVG path data for that region.
    function makeMapData(){
        global $mapArray;
        global $isoCountryCode;
        // Input file for map.
        global $INPUT_FILE;
        $parser = xml_parser_create();

        //Function to use at the start of an element. Used to get the ISO3166-2 name of each region.
        function start($parser,$element_name,$element_attrs) {
          global $mapArray;
          global $currentRegionCode;
          switch($element_name) {
            case "GML:POLYGON":
                $mapArray[$element_attrs["GML:ID"]] = 0;
                $currentRegionCode = $element_attrs["GML:ID"];
            break;
          }
        }



        //Function to use when finding character data. Converts the lat/lon data from GML to SVG.
        function encodeSVGpath($parser,$data) {
	        global $mapArray;
	        global $currentRegionCode;
	        global $isoCountryCode;

	        if (strlen($data) > 50){
	            /* Construct SVG path data (ex. M10 15 20 25Z). The M and the 10 15 mean "move from point (10, 15)",
	             * to (20 25) and so on". The Z means close off the path.
	             */
                //getBaselineLatLon($data);
	            $data = latLonToXY($data);
	            $data = "M".trim($data)."Z";
	            $mapArray[$currentRegionCode] = $data;
	        } else if (substr($data, 4, 5) == "Level"){
	            $isoCountryCode = substr($data, 0, 3);
	        }
        }

        // Gets the highest and lowest lats and eastern and western most longs. Stores these in the max/min lat lon vars.
        /*function getBaselineLatLon($data){
            global $maxNlat;
            global $minSlat;
            global $maxElon;
            global $minWlon;

            $dataArray = explode(" ", $data);
            for ($i = 0; $i < count($dataArray); $i++){
                // even i's are lats, odds are lons
                if (fmod($i, 2) == 0){
                    if ($dataArray[$i] > 0 && $dataArray[$i] >= $maxNlat)
                        $maxNlat = $dataArray[$i];
                    else if ($dataArray[$i] < 0 && $dataArray[$i] <= $minSlat)
                        $minSlat = $dataArray[$i];
                } else {
                    if ($dataArray[$i] > 0 && $dataArray[$i] >= $maxElon)
                        $maxElon = $dataArray[$i];
                    else if ($dataArray[$i] < 0 && $dataArray[$i] <= $minWlon)
                        $minWlon = $dataArray[$i];
                }
            }
        }*/

        // Steve's function for scaling latitudes
        function scaleLatitude($degreesS, $degreesN, $degreesP){
            $yS = 180/pi() * (2 * atan(exp($degreesS * pi()/180)) - pi()/2);
            $yN = 180/pi() * (2 * atan(exp($degreesN * pi()/180)) - pi()/2);
            $yP = 180/pi() * (2 * atan(exp($degreesP * pi()/180)) - pi()/2);

            //Switched yS and yN below to fix upside down maps
            $spread = $yS - $yN;
            $yP = ($yP - $yN) * (1 / $spread);
            return round($yP, 5);
        }

        // Steve's function for scaling longitudes
        function scaleLongitude($degreesE, $degreesW, $degreesP){
            $spread = $degreesE - $degreesW;

            if ($spread >= 180) {
                $spread -= 360;
                $degreesP = ($degreesP - $degreesE) * (-1 / $spread);
                return round($degreesP, 5);
            } else if ($spread <= -180)
                $spread += 360;
            
            $degreesP = ($degreesP - $degreesW) * (1 / $spread);
            return round($degreesP, 5);
        }

        // Converts the lat and lon coords to cartesian xy coords. GML data is of form (lat, lon).
        // @return returns a string with the converted lat lon values.
        function latLonToXY($data) {
            //$totX = 0;
            //$xc = 0;
            //$totY = 0;
            //$yc = 0;

            // Mercator data
            global $maxNlat;
            global $minSlat;
            global $maxElon;
            global $minWlon;
            
            $latLonToXY = "";
            $latLonArray = explode(" ", $data);
            /*
             * Coord data in GML files is stored with latitude first, then longitude. The loop below starts at the first
             * longitude value at index 1, goes back to get the latitude value, then goes forward to get the next longitude value.
             * Abusing the fact that the num of elements in GML data is always even.
             */
            for ($i = 1; $i < count($latLonArray); $i++){
                if (fmod($i, 2) != 0){
                    // Longitude
                    $x = scaleLongitude($maxElon, $minWlon, $latLonArray[$i]) * 1000;
                    $latLonToXY .= $x." ";
                    $i -= 2;
                } else {
                    // Latitude
                    $y = scaleLatitude($minSlat, $maxNlat, $latLonArray[$i]) * 700;
                    $latLonToXY .= $y." ";
                    $i += 2;
                }
            }
				   //$totX = $totX + $x;
				   //$xc++;
				   //$totY = $totY + $y;
   				   //$yc++;

            //echo "STR: ".$latLonToXY."<br />";
            //echo "$totX, count: $xc == ".$totX/$xc."<br/>";
            //echo $totY/$yc . "+";
            return $latLonToXY;
        }

        //Function to use at the end of an element
        function stop($parser,$element_name) {
              //
        }

        //Specify element handler
        xml_set_element_handler($parser,"start","stop");

        //Specify data handler
        xml_set_character_data_handler($parser,"encodeSVGpath");

        //Open xml file
        $mapFile = fopen($INPUT_FILE, 'r');

        //Read data. The size value has to be big enought to read the massive map data.
        while ($data=fread($mapFile, filesize($INPUT_FILE))) {
          xml_parse($parser,$data,feof($mapFile)) or
          die (sprintf("XML Error: %s at line %d",
          xml_error_string(xml_get_error_code($parser)),
          xml_get_current_line_number($parser)));
        }

        //Free the XML parser
        xml_parser_free($parser);

        fclose($mapFile);
        echo "COUNTRYCODE: ".$isoCountryCode."<br />";
        
    }
    

?>
