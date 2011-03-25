<?php

    global $INPUT_FILE;

    // Mercator projection test vars
    $maxNlat = 0;
    $minSlat = 0;
    $maxElon = 0;
    $minWlon = 0;
    function makeBaselineData(){

        global $INPUT_FILE;
        $parser = xml_parser_create();


        //Specify data handler
        xml_set_character_data_handler($parser,"getBaselineLatLon");

        //Open xml file
        $mapFile = fopen($INPUT_FILE, 'r');

        // Gets the highest and lowest lats and eastern and western most longs. Stores these in the max/min lat lon vars.
        function getBaselineLatLon($parser, $data){
            global $maxNlat;
            global $minSlat;
            global $maxElon;
            global $minWlon;

            if (strlen($data) > 50){
                $dataArray = explode(" ", $data);
                for ($i = 0; $i < count($dataArray); $i++){
                    // even i's are lats, odds are lons
                    if (fmod($i, 2) == 0){
                        if ($dataArray[$i] >= 0 && $dataArray[$i] >= $maxNlat)
                            $maxNlat = $dataArray[$i];
                        else if ($dataArray[$i] < 0 && $dataArray[$i] <= $minSlat)
                            $minSlat = $dataArray[$i];
                    } else {
                        if ($dataArray[$i] >= 0 && $dataArray[$i] >= $maxElon)
                            $maxElon = $dataArray[$i];
                        else if ($dataArray[$i] < 0 && $dataArray[$i] <= $minWlon)
                            $minWlon = $dataArray[$i];
                    }
                }
            }
        }

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

    }
?>
