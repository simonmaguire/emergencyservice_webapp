<?php
  require("header.php");
?>
<div id = 'reportH1'>
    <h2>Reports</h2>
</div>
<div id = 'reportH2'>
    <ul>
        <p>HOME</p>
        <p>FAVORITES</p>
        <p>TOP 20</p>
        <p>SEARCH</p>
    </ul>
</div>
<div id='container'>
    <div id='containerL'>
        <form action="" method="POST" name="report_params" id="report_params"> 
            <label>Which type of report would you like?</label>
            <select name="choice" id="report_choice" onchange="timeBased();">
                <option value="Breakdown by Call Type">Breakdown by Call Type</option>
                <option value="Time Until Dispatch">Time Until Dispatch</option>
                <option value="Time Until Available">Time Until Available</option>
                <option value="Time Until On Scene">Time Until Arrival</option>
            </select>
            </br>

            <label id="b_or_t_label">Compare times across Battalions 
or common Call Types?</label>
            <select name="b_or_t" id='b_or_t' onchange="comparedBy();">
                <option value="t">Call Types</option>
                <option value="b">Battalions</option>
            </select>


            <label id = "bats_label">Which Battalion(s) should be examined?</label>
            <select name="bats" id='bats'>
                <option value="All">All Battalions</option>
                <option value="1">Batallion 1</option>
                <option value="2">Batallion 2</option>
                <option value="3">Batallion 3</option>
                <option value="4">Batallion 4</option>
                <option value="5">Batallion 5</option>
                <option value="6">Batallion 6</option>
                <option value="7">Batallion 7</option>
                <option value="8">Batallion 8</option>
                <option value="9">Batallion 9</option>
                <option value="10">Batallion 10</option>
            </select>

            <div id="DateSelector">
                <div id="tab1" onClick="JavaScript:selectTab(1);">Date</div>
                <div id="tab2" onClick="JavaScript:selectTab(2);">Month</div>
                <div id="tab3" onClick="JavaScript:selectTab(3);">Quarter</div>
                <div id="tab4" onClick="JavaScript:selectTab(4);">Year</div>

                <br/>
                <div id="tab1Content">
                    <label>Year</label>
                        <select name="year" id="year1">
                            <option value="2019">2019</option>    
                            <option value="2018">2018</option>
                            <option value="2017">2017</option>
                        </select>
                    <label>Month</label>
                    <select name="month" id="month1">
                        <option value="1">January</option>    
                        <option value="2">February</option>
                        <option value="3">March</option>
                        <option value="4">April</option>    
                        <option value="5">May</option>
                        <option value="6">June</option>
                        <option value="7">July</option>    
                        <option value="8">August</option>
                        <option value="9">September</option>
                        <option value="10">October</option>    
                        <option value="11">November</option>
                        <option value="12">December</option>
                    </select>
                    <label>Day</lable>
                    <select name="day" id='day1'>
                        <?php for ($i = 1; $i <= 31; $i++) : ?>
                            <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div id="tab2Content">
                    <label>Year</label>
                    <select name="year" id="year2">
                        <option value="2019">2019</option>    
                        <option value="2018">2018</option>
                        <option value="2017">2017</option>
                    </select>
                    <label>Month</label>
                    <select name="month" id="month2">
                        <option value="1">January</option>    
                        <option value="2">February</option>
                        <option value="3">March</option>
                        <option value="4">April</option>    
                        <option value="5">May</option>
                        <option value="6">June</option>
                        <option value="7">July</option>    
                        <option value="8">August</option>
                        <option value="9">September</option>
                        <option value="10">October</option>    
                        <option value="11">November</option>
                        <option value="12">December</option>
                    </select>
                </div>
                <div id="tab3Content">
                    <label>Year</label>
                    <select name="year" id='year3'>
                        <option value="2019">2019</option>    
                        <option value="2018">2018</option>
                        <option value="2017">2017</option>
                    </select>
                    <label>Quarter</label>
                    <select name="quarter" id="quarter3">
                        <option value="1">Q1</option>    
                        <option value="2">Q2</option>
                        <option value="3">Q3</option>
                        <option value="4">Q4</option>    
                    </select>
                </div>
                <div id="tab4Content">
                    <label>Year</label>
                    <select name="year" id = "year4">
                        <option value="2019">2019</option>    
                        <option value="2018">2018</option>
                        <option value="2017">2017</option>
                    </select>
                </div>
            </div>
            <div class="button">
                <button type="button" onclick="storeVars();">Create Report</button>
            </div>
        </form>
        <form action="" method="POST" name="reports_array" id="reports_array">
            <input type = "hidden" id = "r_array" name = "r_array" value = "" />
        </form>
    </div>
    <div id='containerR'>
    </div>
</div>

 <?php 
  require("footer.php");
?>