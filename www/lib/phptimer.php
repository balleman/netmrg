<?php

// begin class PHP_timer
class PHP_timer {

    // array to store the information that we collect during the script
    // this array will be manipulated by the functions within our object
    var $points = array();
    
    // call this function at the beginning of the script
    function start() {
        // see the addmarker() function later on
        $this->addmarker("Start");
    }
    // end function start()

    // call this function at the end of the script
    function stop() {
        // see the addmarker() function later on
        $this->addmarker("Stop");
    }
    // end function stop()

    // this function is called to add a marker during the scripts execution
    // it requires a descriptive name
    function addmarker($name) {
        // call the jointime() function and pass it the output of the microtime() function
        //  as an argument
        $markertime = $this->jointime(microtime());
        // $ae (stands for Array Elements) will contain the number of elements 
        // currently in the $points array
        $ae = count($this->points);
        // store the timestamp and the descriptive name in the array
        $this->points[$ae][0] = $markertime;
        $this->points[$ae][1] = $name;
    }
    // end function addmarker()

    // this function manipulates the string that we get back from the microtime() function
    function jointime($mtime) {
        // split up the output string from microtime() that has been passed
        // to the function
        $timeparts = explode(" ",$mtime);
        // concatenate the two bits together, dropping the leading 0 from the
        // fractional part
        $finaltime = $timeparts[1].substr($timeparts[0],1);
        // return the concatenated string
        return $finaltime;
    }
    // end function jointime()
    
    // this function simply give the difference in seconds betwen the start of the script and
    // the end of the script
    function showtime() {
        return round( (($this->points[count($this->points)-1][0]) - ($this->points[0][0])), 6);
    }
    // end function showtime()
    
    // this function displays all of the information that was collected during the 
    // course of the script
    function debug() {
        echo "Script execution debug information:";
        echo "<table border=0 cellspacing=5 cellpadding=5>\n";
        // the format of our table will be 3 columns:
        // Marker name, Timestamp, difference
        echo "<tr><td><b>Marker</b></td><td><b>Time</b></td><td><b>Diff</b></td></tr>\n";
        // the first row will have no difference since it is the first timestamp
        echo "<tr>\n";
        echo "<td>".$this->points[0][1]."</td>";
        echo "<td>".$this->points[0][0]."</td>";
        echo "<td>-</td>\n";
        echo "</tr>\n";
        // our loop through the $points array must start at 1 rather than 0 because we have
        // already written out the first row
        for ($i = 1; $i < count($this->points);$i++) {
            echo "<tr>\n";
            echo "<td>".$this->points[$i][1]."</td>";
            echo "<td>".$this->points[$i][0]."</td>";
            echo "<td>";
            // write out the difference between this row and the previous row
            echo round( ($this->points[$i][0] - $this->points[$i-1][0]), 6);
            echo "</td>";
            echo "</tr>\n";
        }
        echo '<tr><td colspan="2">Start to Stop</td>'."\n";
        echo '<td>'.round( ($this->points[count($this->points)-1][0] - $this->points[0][0]), 6)."</td>\n";
        echo "</tr>\n";
        echo "</table>";
    }
    // end function debug()
}
// end class PHP_timer

?>
