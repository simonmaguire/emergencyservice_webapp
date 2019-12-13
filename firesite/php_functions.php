<?php


function createChartsArray($arrayAsString){
    //Takes the information about reports sent through POST and creates a string from data
    //   in MySQL

    $charts_info_array = array();
    $test_arr = explode(']', $arrayAsString); //delimeter between reports
    
    //Create multiD-array where sub-array contains one report's params
    for($i=0; $i < count($test_arr)-1; $i++ ){
        $charts_info_array[$i] =  explode(', ', $test_arr[$i]);
    }

    $data_tables = createSetOfTables($charts_info_array);
    return $data_tables;
}


function createSetOfTables($charts_info_array){
    //Takes the array of arrays from createChartsArray and passes each 
    // sub array to createTable to create the Data Table
    // Then concatenates them into a string which can be parsed in JS

    $chart_tables = "";
    for($i=0; $i < count($charts_info_array);$i++){
        $chart_tables .= createTable($charts_info_array[$i]) . "-";
    }
    return $chart_tables;
}


function createTable($chart_info){
    //Take one set of report params and query a MySQL database, send results back as string

    $report_type = $chart_info[0];
    $bat_or_type = $chart_info[1];
    $start = $chart_info[3];
    $end = $chart_info[4];
    $bats = batString($chart_info[2]);
    
    $con = mysqli_connect('localhost', 'root', '','firedb');
    if (!$con) {
        die("Connection failed: " . mysqli_connect_error());
    }
    
    //Create a threshold so that only the 8 most common Call Types for the dates/battalions are included
    $type_threshold = 0;
    $threshold_query = "SELECT `Count` FROM 
        (SELECT `Call Type`, COUNT(id) as 'Count' FROM `table 1` 
        WHERE `Received DtTm` >=  '{$start}' and `Received DtTm` < '{$end}' {$bats}
        GROUP BY `Call Type`) as firstTable 
        ORDER BY `Count` DESC LIMIT 7,1";
    $threshold_exec = mysqli_query($con, $threshold_query) or die(mysqli_error($con));
    $row_thresh = mysqli_fetch_assoc($threshold_exec) or die(mysqli_error($con));
    $type_threshold = $row_thresh['Count'];

    $chart_table = "";
    //Query the DB and set the results to $chart_table
    switch($report_type){
        case "Breakdown by Call Type":
            $query = "SELECT `Call Type`, `Count` 
                FROM (SELECT count(id) AS `Count`, `Call Type` 
                        FROM `table 1` WHERE `Received DtTm` >=  '{$start}' and `Received DtTm` < '{$end}' {$bats}
                        GROUP BY `Call Type`) AS firstTable
                HAVING `Count` >= {$type_threshold} AND `Call Type` != 'Other'
                UNION
                SELECT 'Other', sum(`Count`)
                FROM (SELECT count(id) AS `Count`, `Call Type` 
                        FROM `table 1` WHERE `Received DtTm` >=  '{$start}' and `Received DtTm` < '{$end}' {$bats}
                        GROUP BY `Call Type`) AS firstTable
                WHERE `Count` < {$type_threshold} OR `Call Type`='Other'
                GROUP BY 'Other'";

            
            $exec = mysqli_query($con,$query) or die(mysqli_error($exec));
            while($row = mysqli_fetch_array($exec)){
                $chart_table .=   "[".$row['Call Type'].",".$row['Count']."]:";
            }

            break;
        case "Time Until Dispatch":
            //If-Else checks if it should compare across Call Types or Battalions
            if($bat_or_type == 'b'){
                $query = "SELECT `Battalion`, AVG(TIMESTAMPDIFF(second, `Received DtTm`,`Dispatch DtTm`))/60 AS `Minutes Elapsed` 
                FROM `table 1` 
                WHERE `Received DtTm` >=  '{$start}' and `Received DtTm` < '{$end}'
                GROUP BY `Battalion`";

                $exec = mysqli_query($con,$query);
                while($row = mysqli_fetch_array($exec)){
                    $chart_table .=  "[".$row['Battalion'].",".$row['Minutes Elapsed']."]:";
                }
            } else{
                $query = "SELECT count(id) AS `Count`, `Call Type`,
                AVG(TIMESTAMPDIFF(second,`Received DtTm`, `Dispatch DtTm`))/60 AS `Minutes Elapsed` 
                FROM `table 1` WHERE `Received DtTm` >=  '{$start}' and `Received DtTm` < '{$end}' {$bats} 
                GROUP BY `Call Type`
                HAVING `Count` >= {$type_threshold}";

                $exec = mysqli_query($con,$query);
                while($row = mysqli_fetch_array($exec)){
                    $chart_table .=  "[".$row['Call Type'].",".$row['Minutes Elapsed']."]:";
                }
            }
            
            break;
        case "Time Until Available":
            //If-Else checks if it should compare across Call Types or Battalions
            if($bat_or_type == 'b'){
                $query = "SELECT `Battalion`, AVG(TIMESTAMPDIFF(second, `Dispatch DtTm`, `Available DtTm`))/60 AS `Minutes Elapsed` 
                FROM `table 1` 
                WHERE `Received DtTm` >=  '{$start}' and `Received DtTm` < '{$end}'
                GROUP BY `Battalion`";
                $exec = mysqli_query($con,$query);
                while($row = mysqli_fetch_array($exec)){
                    $chart_table .=  "[".$row['Battalion'].",".$row['Minutes Elapsed']."]:";
                }
            } else{
                $query = "SELECT count(id) AS `Count`, `Call Type`, AVG(TIMESTAMPDIFF(second,`Dispatch DtTm`, `Available DtTm`))/60 AS `Minutes Elapsed` 
                        FROM `table 1` WHERE `Received DtTm` >=  '{$start}' and `Received DtTm` < '{$end}' {$bats} 
                        GROUP BY `Call Type`
                HAVING `Count` >= {$type_threshold}";

                $exec = mysqli_query($con,$query);
                while($row = mysqli_fetch_array($exec)){
                    $chart_table .=  "[".$row['Call Type'].",".$row['Minutes Elapsed']."]:";
                }
            }
            break;
        case "Time Until On Scene":
            //If-Else checks if it should compare across Call Types or Battalions
            if($bat_or_type == 'b'){
                $query = "SELECT `Battalion`, AVG(TIMESTAMPDIFF(second, `Dispatch DtTm`, `On Scene DtTm`))/60 AS `Minutes Elapsed` 
                FROM `table 1` 
                WHERE `Received DtTm` >=  '{$start}' and `Received DtTm` < '{$end}'
                GROUP BY `Battalion`";
                $exec = mysqli_query($con,$query);
                while($row = mysqli_fetch_array($exec)){
                    $chart_table .= "[".$row['Battalion'].",".$row['Minutes Elapsed']."]:";
                }
            } else{
                $query = "SELECT count(id) AS `Count`, `Call Type`, AVG(TIMESTAMPDIFF(second,`Dispatch DtTm`, `On Scene DtTm`))/60 AS `Minutes Elapsed` 
                        FROM `table 1` WHERE `Received DtTm` >=  '{$start}' and `Received DtTm` < '{$end}' {$bats} 
                        GROUP BY `Call Type`
                HAVING `Count` >= {$type_threshold}
                ";
                $exec = mysqli_query($con,$query);
                while($row = mysqli_fetch_array($exec)){
                    $chart_table .= "[".$row['Call Type'].",".$row['Minutes Elapsed']."]:";
                }
            }
            break;
        default:
            break;
    }
    return $chart_table;
}    


function batString($b){
    //Takes the battalion info and creates the string to impute into the SQL query
    if ($b == "All"){
        $bats = " ";
    } else {
        if($b == "10"){
            $bats = "and `Battalion` = 'B10' ";
        } else {
            $bat_num = $b;
            $bats = " and `Battalion` = 'B0{$bat_num}' ";
        }
    }
    return $bats;
}

?>