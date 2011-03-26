<?php

    global $INPUT_FILE;
    global $bool;			//NEED THIS GLOBAL BOOLEAN VAR SO THAT WE DON'T RESET $maxNlat, $maxSlat, etc EVERY TIME FUNCTION IS CALLED
    // Mercator projection test vars
    $maxNlat = 0;
    $maxSlat = 0;
    $maxElon = 0;
    $maxWlon = 0;

    function makeBaselineData(){

        global $INPUT_FILE;
        $parser = xml_parser_create();

        //Specify data handler
        $bool = 0;
        xml_set_character_data_handler($parser,"getBaselineLatLon");

        //Open xml file
        $mapFile = fopen($INPUT_FILE, 'r');

        // Gets the highest and lowest lats and eastern and western most longs. Stores these in the max/min lat lon vars.
        function getBaselineLatLon($parser, $data){
            global $maxNlat;
            global $maxSlat;
            global $maxElon;
            global $maxWlon;
            global $bool;


            if (strlen($data) > 50){
                $dataArray = explode(" ", $data);
            if ($bool == 0)				//ONLY SET THESE THE FIRST TIME
            {
            $maxNlat = $dataArray[0];
            $maxSlat = $maxNlat;
            $maxElon = $dataArray[1];
                $maxWlon = $maxElon;
            $bool = 1;
            }
            for ($i = 0; $i < count($dataArray); $i++){
                if (is_numeric($dataArray[$i])){ //SOMETIMES THIS HAPPENS APPARENTLY WHICH IS F'D UP?
                if (fmod($i, 2) == 0){
                               if ($dataArray[$i]> $maxNlat)
                                        $maxNlat = $dataArray[$i];
                               else if ($dataArray[$i] < $maxSlat)
                                            $maxSlat = $dataArray[$i];
                                } else {
                                if ($dataArray[$i] >= $maxElon)
                                        $maxElon = $dataArray[$i];
                                else if ($dataArray[$i] < $maxWlon)
                                        $maxWlon = $dataArray[$i];
                                }
                }
            //echo "Baseline Data (NSEW): ".$maxNlat.", ".$maxSlat.", ".$maxWlon.", ".$maxElon."<br />";
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
