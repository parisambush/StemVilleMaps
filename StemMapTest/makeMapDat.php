<?php
    $mapArray = array();
    $currentRegionCode;

    // This function parses an xml map file from stem containing gml data and creates an associative array
    // containing the iso-3166-2 region name paired with SVG path data for that region.
    function makeMapData(){
        global $mapArray;
        // Input file for map.
        $INPUT_FILE = 'SAU_1_MAP.xml';
        $parser = xml_parser_create();

        //Function to use at the start of an element. Used to get the ISO3166-2 name of each region.
        function start($parser,$element_name,$element_attrs)
          {
          global $mapArray;
          global $currentRegionCode;
          switch($element_name)
            {
            case "GML:POLYGON":
                $mapArray[$element_attrs["GML:ID"]] = 0;
                $currentRegionCode = $element_attrs["GML:ID"];
            break;
            }
          }

        //Function to use when finding character data. Converts the lat/lon data from GML to SVG.
        function char($parser,$data)
          {
              global $mapArray;
              global $currentRegionCode;
              if (strlen($data) > 100){
                  /* Construct SVG path data (ex. M10 15L20 25Z). The M and the 10 15 mean "move to point (10, 15)",
                   * the L and the 20 25 means "draw a line from current point to this point, (10 15) to (20 25)" and
                   * the Z means close off the path.
                   */
                  $data = "M".trim($data)."Z";
                  $whiteSpaceCt = 0;
                  $strpos = 0;
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
              }
          }

          //Function to use at the end of an element
        function stop($parser,$element_name)
          {
              //
          }

        //Specify element handler
        xml_set_element_handler($parser,"start","stop");

        //Specify data handler
        xml_set_character_data_handler($parser,"char");

        //Open xml file
        $mapFile = fopen($INPUT_FILE, 'r');

        //Read data
        while ($data=fread($mapFile,131072))
          {
          xml_parse($parser,$data,feof($mapFile)) or
          die (sprintf("XML Error: %s at line %d",
          xml_error_string(xml_get_error_code($parser)),
          xml_get_current_line_number($parser)));
          }

        //Free the XML parser
        xml_parser_free($parser);

        //echo "****NOW PRINTING MAP ARRAY DAT**** <br />";
        //foreach ($mapArray as $key => $val){
            //echo $key . ": " . $val. "<br /><br />";
        //}

        fclose($mapFile);
    }
    //echo json_encode($mapArray);
?>
