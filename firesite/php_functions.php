<?php


function createChartsArray($arrayAsString){
    //Takes the information about reports sent through POST and creates a string from data
    //   in MySQL

    $charts_info_array = array();
    $temp_ar = explode(']', $arrayAsString); //delimeter between reports
    
    //Create multiD-array where sub-array contains one report's params
    for($i=0; $i < count($temp_ar)-1; $i++ ){
        $charts_info_array[$i] =  explode(', ', $temp_ar[$i]);
    }

    $data_tables = createSetOfTables($charts_info_array);
    return $data_tables;
}


function createSetOfTables($charts_info_array){
    //Takes the array of arrays from createChartsArray and passes each 
    // sub array of params to createTable to create the Data Table
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
    $end = incrementDate($chart_info[4]);
    $bats = batString($chart_info[2]);
    
    $con = mysqli_connect('localhost', 'root', '','firedb');
    if (!$con) {
        die("Connection failed: " . mysqli_connect_error());
    }
    
    //Create a threshold so that only the 8 most common Call Types for the dates/battalions are included
    $type_threshold = 0;
    $threshold_query = "SELECT `Count` FROM 
        (SELECT `Call Type`, COUNT(id) as 'Count' FROM `table 1` 
        WHERE `Received DtTm` >=  '{$start}' and `Received DtTm` < '{$end}' 
        {$bats}
        GROUP BY `Call Type`) as firstTable 
        ORDER BY `Count` DESC LIMIT 7,1";
    $threshold_exec = mysqli_query($con, $threshold_query) or die(mysqli_error($con));
    
    // If no calls were in the date range, no data will be returned
    if(mysqli_num_rows($threshold_exec) < 1){
        return "empty";
    }

    $row_thresh = mysqli_fetch_assoc($threshold_exec) or die(mysqli_error($con));
    $type_threshold = $row_thresh['Count'];

    $chart_table = "";
    //Query the DB and set the results to $chart_table
    switch($report_type){
        case "Breakdown by Call Type":
            $query = "SELECT `Call Type`, `Count` 
                FROM (SELECT count(id) AS `Count`, `Call Type` 
                        FROM `table 1` WHERE `Received DtTm` >=  '{$start}' and `Received DtTm` < '{$end}'
                        {$bats}
                        GROUP BY `Call Type`) AS firstTable
                HAVING `Count` >= {$type_threshold} AND `Call Type` != 'Other'
                UNION
                SELECT 'Other', sum(`Count`)
                FROM (SELECT count(id) AS `Count`, `Call Type` 
                        FROM `table 1` WHERE `Received DtTm` >=  '{$start}' and `Received DtTm` < '{$end}'
                        {$bats}
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
                FROM `table 1` WHERE `Received DtTm` >=  '{$start}' and `Received DtTm` < '{$end}' 
                {$bats} 
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
                        FROM `table 1` WHERE `Received DtTm` >=  '{$start}' and `Received DtTm` < '{$end}' 
                        {$bats} 
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
                        FROM `table 1` WHERE `Received DtTm` >=  '{$start}' and `Received DtTm` < '{$end}' 
                        {$bats} 
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

function incrementDate($d){
    //Takes a string date in yyyy-mm-dd format and adds one day

    $d_parts = explode("-", $d);
    $d_parts[2] = (int)$d_parts[2];
    $d_parts[1] = (int)$d_parts[1];
    $d_parts[0] = (int)$d_parts[0];
    $day_max;

    if ($d_parts[1] === 2){ //If it's February check if it's a leap year
        if($d_parts[0]%4 === 0){
            $day_max = 29;
        } else{
            $day_max = 28;
        }
    } else{ //It's not Feb, so is it one of the months with 31 days or 30
        $days31 = [1,3,5,7,8,10,12];
        if(in_array($d_parts[1], $days31)){
            $day_max = 31;
        } else{
            $day_max = 30;
        }
    }

    //If not the last day of month
    if((int)$d_parts[2] < $day_max){
        $d_parts[2] = (int)$d_parts[2] + 1;
        $d_parts[1] = (int)$d_parts[1];
        $d_parts[0] = (int)$d_parts[0];
    }
    // If not the last month of year
    elseif((int)$d_parts[1] < 12){
        $d_parts[2] = 1;
        $d_parts[1] = (int)$d_parts[1] + 1;
        $d_parts[0] = (int)$d_parts[0];
    }
    else{
        $d_parts[2] = 1;
        $d_parts[1] = 1;
        $d_parts[0] = (int)$d_parts[0] + 1;
    }
    $d_parts = formatDate($d_parts);
    return join("-", $d_parts);
}

function formatDate($d_parts){
    //Changes date format from yyyy-m-d to yyyy-mm-dd

    $month_digits = strlen((string)$d_parts[1]);
    $day_digits = strlen((string)$d_parts[2]);

    if($day_digits < 2){
        $d_parts[2] = "0" . $d_parts[2];
    }
    if($month_digits < 2){
        $d_parts[1] = "0" . $d_parts[1];
    }
    return $d_parts;
}

?>
