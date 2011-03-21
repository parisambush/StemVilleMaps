<?php
    $mapArray = array();
    $isoCountryCode;
    $currentRegionCode;

    // This function parses an xml map file from stem containing gml data and creates an associative array
    // containing the iso-3166-2 region name paired with SVG path data for that region.
    function makeMapData(){
        global $mapArray;
        global $isoCountryCode;
        // Input file for map.
        $INPUT_FILE = 'SAU_2_MAP.xml';
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
        function char($parser,$data) {
              global $mapArray;
              global $currentRegionCode;
              global $isoCountryCode;
              if (strlen($data) > 100){
                  /* Construct SVG path data (ex. M10 15L20 25Z). The M and the 10 15 mean "move to point (10, 15)",
                   * the L and the 20 25 means "draw a line from current point to this point, (10 15) to (20 25)" and
                   * the Z means close off the path.
                   */
                  $data = latLonToXY($data);
                  $data = "M".trim($data)."Z";
                  $whiteSpaceCt = 0;
                  $strpos = 0;
                  $latLonToXY = "";
                  while($strpos < strlen($data)){
                      if (substr($data, $strpos, 1) == " " && $whiteSpaceCt == 1){
                          $data = substr_replace($data, "L", $strpos, 1);
                          $whiteSpaceCt = 0;
                      } else if (substr($data, $strpos, 1) == " "){
                          $whiteSpaceCt++;
                      }
                      $strpos++;
                  }
                  $mapArray[$currentRegionCode] = $data;
              } else if (substr($data, 4, 5) == "Level"){
                  $isoCountryCode = substr($data, 0, 3);
              }
        }

        // Converts the lat and lon coords to cartesian xy coords. GML data is of form (lat, lon).
        // @return returns a string with the converted lat lon values.
        function latLonToXY($data) {
            
            $latLonToXY = "";
            $lat = 0;
            $lon = 0;
            // Because the pos data in the gml files aren't always the same length, need a buffer to read diff lenghts.
            $strBuffer = "";
            $strpos = 0;
            $whiteSpaceCt = 0;
            while($strpos < strlen($data)){
                      if (substr($data, $strpos, 1) == " " && $whiteSpaceCt == 1){
                          // Even num of spaces delimit lon values.
                          $lon = $strBuffer;
                          $strBuffer = "";
                          // Conversion to xy coords, rounded to 6 decimal precision. The 1000 is a map scale factor.
                          // For sure there are better ways to do this.
                          $x = (180 + $lon) * (1000/360);
                          $x = round($x, 6);
                          $y = (90 - $lat) * (1000/180);
                          $y = round($y, 6);
                          
                          // Attempt to normalize coords so that only positive values result.
                          if ($x < 0)
                              $x = abs($x);
                          if ($y < 0)
                              $y = abs($y);
                          $latLonToXY .= $x." ".$y." ";
                          $whiteSpaceCt = 0;
                      } else if (substr($data, $strpos, 1) == " "){
                          // Odd num of spaces delimits lat values.
                          $lat = $strBuffer;
                          $whiteSpaceCt++;
                          $strBuffer = "";
                      } else {
                          $strBuffer .= substr($data, $strpos, 1);
                      }
                      //echo "STRBUFF: ".$strBuffer."<br />";
                      $strpos++;
                  }
             //echo "STR: ".$latLonToXY."<br />";
             return $latLonToXY;
        }

        //Function to use at the end of an element
        function stop($parser,$element_name) {
              //
        }

        //Specify element handler
        xml_set_element_handler($parser,"start","stop");

        //Specify data handler
        xml_set_character_data_handler($parser,"char");

        //Open xml file
        $mapFile = fopen($INPUT_FILE, 'r');

        //Read data. The size value has to be big enought to read the massive map data.
        while ($data=fread($mapFile, 1048576)) {
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
