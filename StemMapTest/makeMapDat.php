<?php
    $mapArray = array();
    $isoCountryCode;
    $currentRegionCode;
    $INPUT_FILE = 'MEX_2_MAP.xml';
    // Map scale constant
    $MAP_SCALE = 600;

    include 'getBaselineData.php';
    makeBaselineData();
    
    echo "FINAL Baseline Data (NSWE): ".$maxNlat.", ".$maxSlat.", ".$maxWlon.", ".$maxElon."<br />";

    //Mercator projection test vars
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
                $mapArray[$element_attrs["GML:ID"]] = array();
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
                // Changed to make $mapArray look like: {"region_name": [path1, path2,...]}
	            $mapArray[$currentRegionCode][] = $data;
	        } else if (substr($data, 4, 5) == "Level"){
	            $isoCountryCode = substr($data, 0, 3);
	        }
        }

        // Steve's function for scaling latitudes
        function scaleLatitude($degreesS, $degreesN, $degreesP){
            $yS = 180/pi() * (2 * atan(exp($degreesS * pi()/180)) - pi()/2);
            $yN = 180/pi() * (2 * atan(exp($degreesN * pi()/180)) - pi()/2);
            $yP = 180/pi() * (2 * atan(exp($degreesP * pi()/180)) - pi()/2);

            $spread = $yN - $yS;
            $yP = ($yP - $yS) * (1 / $spread);
            return $yP;
        }

        // Steve's function for scaling longitudes
        function scaleLongitude($degreesE, $degreesW, $degreesP){
        
		  
            $spread = $degreesE - $degreesW;
			       
            $degreesP = ($degreesP - $degreesW) * (1 / $spread);
            //echo "degreesP : ".$degreesP."<br />";
            return $degreesP;
        }

        // Converts the lat and lon coords to cartesian xy coords. GML data is of form (lat, lon).
        // @return returns a string with the converted lat lon values.
        function latLonToXY($data) {

            // Mercator projection data
            global $maxNlat;
            global $maxSlat;
            global $maxElon;
            global $maxWlon;
            global $MAP_SCALE;
            
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
                    $x = scaleLongitude($maxElon, $maxWlon, $latLonArray[$i]) * $MAP_SCALE;
		    // echo "x : ".$x."<br />";
                    $latLonToXY .= $x." ";
                    $i -= 2;
                } else {
                    // Latitude
                    $y = scaleLatitude($maxNlat, $maxSlat, $latLonArray[$i]) * $MAP_SCALE;  //REVERSEING THE N AND S solved the UPSIDEDOWN PROBLEM
		    // echo "y : ".$y."<br />";
                    $latLonToXY .= $y." ";
                    $i += 2;
                }
            }

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
