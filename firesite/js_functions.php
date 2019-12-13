<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script type="text/javascript">
 <?php
    require("php_functions.php");
  ?>

function storeVars(){
    // Collects the current values from report_params form,
    // sets the value of r_array to string  previous and current params and submits its form using POST
    // Is called onClick of Create Report
    var report_choice = document.getElementById('report_choice').value;
    var b_or_t = document.getElementById('b_or_t').value;
    var bats = document.getElementById('bats').value;

    //Looks to global var date_tab for which tab is active, then collects the appropriate values
    switch(date_tab){
        //Takes info from the specified Date Tab and sets year, start_month, end_month, start_day and end_day
        case 1:
            var year =  document.getElementById('year1').value;
            var start_month =  document.getElementById('month1').value;
            var end_month = start_month;
            var start_day =  document.getElementById('day1').value;
            var end_day = start_day;
            break;
        case 2:
            var year =  document.getElementById('year2').value
            var start_month =  document.getElementById('month2').value;
            var end_month = start_month;
            var start_day = 1
            var end_day = daysOfMonth(Number(end_month), Number(year));
            break;
        case 3:
            var q = document.getElementById('quarter3').value
            var year = document.getElementById('year3').value;
            var start_month = 2 * (q - 1) + Number(q);
            var end_month = 3 * q;
            var start_day = 1;
            var end_day = daysOfMonth(end_month, Number(year));
            break;
        case 4:
            var year = document.getElementById('year4').value;
            var start_month = 1;
            var end_month = 12;
            var start_day = 1;
            var end_day = 31;
            break;
        default:
            var year = 2019;
            var start_month = 1;
            var end_month = 12;
            var start_day = 1;
            var end_day = 31;
            break;
    }

    var start = `${year}-${start_month}-${start_day}`;
    var end = `${year}-${end_month}-${end_day}`;

    //concatenates prev_info with current params and delimeter ']'
    document.getElementById("r_array").value = `${prev_info}${report_choice}, ${b_or_t}, ${bats}, ${start}, ${end}]`;
    document.getElementById("reports_array").submit();
}


function reportInfoToArray(info_string){
    // Takes string of params for 1+ reports and creates multidimensional array
    // where each sub-array is an array of one reports parameters
    var first_array = info_string.split("]"); // ']' delimeter between reports
    var final_array = [];
    for (var i = 0; i < first_array.length-1; i++){
        final_array[i] = first_array[i].split(", "); // delimeter between params
    }

    return final_array;
}

function reportDescription(choice){
    switch(choice){
        case "Breakdown by Call Type":
            var disc = "<h5>The eight most common Call Types' percentage of Total Calls.</h5>";
            break;
        case "Time Until Dispatch":
            var disc = "<h5>The time between recieving the call and a unit being dispatched.</h5>";
            break;
        case "Time Until Available":
            var disc = "<h5>The time between a unit being dispatch and becoming available.</h5>";
            break;
        case "Time Until On Scene":
            var disc = "<h5>The time between a unit being dispatch and ariving on scene.</h5>";
            break;
        default:
            var disc = "<h5></h5>";
            break;        
    }
    return disc;
}

var reportId = 0; // used by the addReport() function to keep track of IDs
function addReport(chart_info, chart_table) {
    // Creates the <div> element w/ id = "report-" + reportID , then adds the title and header
    // what the report is showing, also adds <div> w/ id "chart" + reportId to add the chart later
    var report_choice = chart_info[0];
    var bats = chart_info[2];
    var start = chart_info[3];
    var end = chart_info[4];
    var discription = reportDescription(report_choice);

    addElement('containerR', 'div', 'report-' + reportId, "<div></div>"); 
    var html_title = '<h3>' + report_choice + " for Date Range</h3>";
    addElement("report-" + reportId, 'h3', "title" + reportId, html_title);
    addElement("report-" + reportId, 'h4', "disc" + reportId, discription);
    var html_params = `<h4>Batallion(s):  ${bats} | Start Date: ${start}  | End Date: ${end} </h4>`;
    addElement("report-" + reportId, 'h4', "params" + reportId, html_params);
    addElement("report-" + reportId, 'div', "chart" + reportId, "<div'></div>");
    addElement("report-" + reportId, 'div', "chartborder", "<div'></div>");


    reportId++; // increment reportId to get a unique ID for the new element
}

function addReportCharts(report_info, chart_tables) {
    // Iterates over report info and adds the chart for each report
    google.charts.load('current', {'packages':['corechart']});

    for(var i = 0; i < report_info.length; i++){
        switch(report_info[i][0]){
            case "Breakdown by Call Type":
                google.charts.setOnLoadCallback( (function(){
                    var j=i; // Passing in the report #
                    var table = chart_tables[j]; // The data for this report
                    return function() {
                        drawPieChart(j, table);
                    }
                })() );
                break;
            
            default:
                // The other three possible reports are all bar charts
                // Could have used if else stmnt but the switch makes it easier embelish later
                google.charts.setOnLoadCallback( (function(){
                    var j=i; 
                    var table = chart_tables[j]; 
                    return function() {
                        drawBarChart(j, table, report_info[j][1]);
                    }
                })() );
                break;
        }
    }    
}

function cleanDataTable(table){
    // Removes some chars that interfere and returns a multi-deminsional array
    // It's a bit "hacky", it'd definetly be worth fixing earlier parts of the pipeline
    //     so the ',,', '[', and  ']' are never created in the first place
    var tmp = table.replace(",,","");
    tmp = tmp.split(":"); //delimeter between data-points
    for(var i = 0; i < tmp.length; i++){
        tmp[i] = tmp[i].replace("[","");
        tmp[i] = tmp[i].replace("]","");
        tmp[i] = tmp[i].split(","); //delimeter btw label and value
    }
    return tmp;
}

function drawPieChart(chart_loc, table) {
    //Renders a GoogleCharts Pie Chart with the data given
    var data_clean = cleanDataTable(table); // create multiD-array of data

    var data = new google.visualization.DataTable();
      data.addColumn('string', 'Call Type');
      data.addColumn('number', 'Count');
      for(var k = 0; k < data_clean.length; k++){
          data.addRow([data_clean[k][0], parseInt(data_clean[k][1])]);
        }

    var options = {
        pieSliceText: 'none',
        sliceVisibilityThreshold: 0,
        backgroundColor: "rgb(250,250,230)",
        height: 400,
        is3D: true,
        legend: {position : 'labeled', textStyle : {fontSize: 12}}
    };

    //Create chart at previously created <div>
    var chart = new google.visualization.PieChart(document.getElementById("chart" + chart_loc));
    chart.draw(data,options);
}


function drawBarChart(chart_loc, table, b_or_t) {
    //Renders a GoogleCharts Bar Chart with the data given
    var data_clean = cleanDataTable(table);
    // Did the user want to compare Call Types or Battalions?
    if(b_or_t =='b'){
        var label = "Battallion";
    } else{
        var label = "Call Type";
    }
    
    var data = new google.visualization.DataTable();
    data.addColumn('string', label);
    data.addColumn('number', 'AVG Minutes Elapsed');
    for(var k = 0; k < data_clean.length; k++){
        data.addRow([data_clean[k][0], parseFloat(data_clean[k][1])]);
    }

    var options = {
        height: 400,
        legend: 'none',
        backgroundColor: "rgb(250,250,230)",
        vAxis: {title: label},
        hAxis: {title : 'AVG Minutes Elapsed'}
    };

    //Create chart at previously created <div>
    var chart = new google.visualization.BarChart(document.getElementById("chart" + chart_loc));
    chart.draw(data, options);
}


function timeBased(){
    // Called onChange of 'report_choice', if the report is comparing differences in time
    // display a html select element so user can decide what to compare across
    if(document.getElementById('report_choice').value != "Breakdown by Call Type"){
        document.getElementById("b_or_t_label").style.display = "block";
        document.getElementById("b_or_t").style.display = "block";
    } else{
        document.getElementById("b_or_t_label").style.display = "none";
        document.getElementById("b_or_t").style.display = "none";

        document.getElementById("bats_label").style.display = "block";
        document.getElementById("bats").style.display = "block";

    }
}


function comparedBy(){
    // Called onChange of 'b_or_t'. If comparing across Battalions selecting a Battalion becomes 
    // redundant so stop displaying its select element, otherwise show it
    if(document.getElementById('b_or_t').value == "t"){
        document.getElementById("bats_label").style.display = "block";
        document.getElementById("bats").style.display = "block";
    } else{
        document.getElementById("bats_label").style.display = "none";
        document.getElementById("bats").style.display = "none";
    }
}


function daysOfMonth(month, year){
    //Takes the month, year and returns how many days are in that month
    if (month === 2){ //If it's February check if it's a leap year
        if(year%4 === 0){
            return 29;
        } else{
            return 28;
        }
    } else{ //It's not Feb, so is it one of the months with 31 days or 30
        var days31 = [1,3,5,7,8,10,12];
        if(days31.indexOf(month) != -1){
            return 31;
        } else{
            return 30;
        }
    }
}


var date_tab = 4; // Indicator for which tab is active
function selectTab(tabIndex) {
    //Hide All Tabs
    document.getElementById('tab1Content').style.display="none";
    document.getElementById('tab2Content').style.display="none";
    document.getElementById('tab3Content').style.display="none";
    document.getElementById('tab4Content').style.display="none";

    //Show the Selected Tab
    document.getElementById('tab' + tabIndex + 'Content').style.display="block";
    //Change active tab
    date_tab = tabIndex;
}


function addElement(parentId, elementTag, elementId, html) {
    // Adds an element to the document
    var p = document.getElementById(parentId);
    var newElement = document.createElement(elementTag);
    newElement.setAttribute('id', elementId);
    newElement.innerHTML = html;
    p.appendChild(newElement);
}


var prev_info = "";  //r eport info from all created reports
var data_tables_string =""; //Data for reports as a string
function onLoadCreateReports(){
    // Called onLoad at <body> elmt.
    // If 'r_array' has been set to params for 1+ reports, send those params to create data from MySQL DB
    // Then use those params and data to create all reports
    
    <?php 
    if (isset($_POST['r_array'])) { 
        $prev_info = $_POST['r_array'];
        $data_tables_string = createChartsArray($prev_info);
        ?> 
        prev_info = "<?php echo $prev_info ?>";
        data_tables_string = "<?php echo $data_tables_string ?>";
    <?php } ?>

    if( prev_info != ""){
        var report_info = reportInfoToArray(prev_info);
        var data_tables = data_tables_string.split("-"); // '-' delimeter between data from different reports
        for(var i = 0; i < report_info.length; i++){
            addReport(report_info[i], data_tables[i]); //create header and <div> to place chart
        }
        addReportCharts(report_info, data_tables);
    }
}
</script>