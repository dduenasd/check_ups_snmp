<?php
#
# check_ups_snmp.sh PNP4Nagios template
#
# v1.0 2012-01-14 GS <dduenasd@gmail.com>
#

$_WARNRULE = '#FFFF00';
$_CRITRULE = '#FF0000';
$_AREA     = '#256aef';
$_LINE     = '#000000';
$arrayColor = array("#F7BE81","#58FAD0","#58FAF4","#58D3F7","#58ACFA","#5882FA","#5858FA","#8258FA","#8258FA","#D358F7");

#
# Initial Logic ...
#
#output_load template
if (preg_match ('/output_load/',$NAGIOS_SERVICECHECKCOMMAND)) {
   $opt[1] = "--title \"Statistics for $servicedesc on $hostname\" -l 0 -u $MAX[1] ";

   $def[1] = "";

   foreach($DS as $i) {
      $def[1] .= "DEF:A$i=$RRDFILE[$i]:$DS[$i]:AVERAGE ";
      $def[1] .= "AREA:A$i".$arrayColor[0].":\"\" ";
   }
   
   foreach($DS as $i) {
   $color=$i;
   if ($i>9) {
         $color=1;
      }
      $def[1] .= "DEF:L$i=$RRDFILE[$i]:$DS[$i]:AVERAGE ";
      $def[1] .= "LINE2:L$i".$arrayColor[$color].":\"$NAME[$i]\t\" ";
      $def[1] .= "GPRINT:L$i:LAST:\"%2.2lf ".$UNIT[$i]." curr\" ";
      $def[1] .= "GPRINT:L$i:MAX:\"%2.2lf ".$UNIT[$i]." max\" ";
      $def[1] .= "GPRINT:L$i:MIN:\"%2.2lf ".$DS[$i]." min\\n\" "; 
   } 

   $def[1] .= "HRULE:$WARN[1]$_WARNRULE:\"Warning on $WARN[1]% \" ";
   $def[1] .= "HRULE:$CRIT[1]$_CRITRULE:\"Critical on $CRIT[1]% \\n\" ";
}

#input_voltage template
else if (preg_match ('/input_voltage/',$NAGIOS_SERVICECHECKCOMMAND)) {
   $opt[1] = "-l $CRIT_MIN[1] -u $CRIT_MAX[1] -J -M --title \"Statistics for $servicedesc on $hostname\" --vertical-label 'voltage (V)'";
   $def[1] = "";
   $targetValue = ($CRIT_MIN[1]+$CRIT_MAX[1])/2;

      foreach($DS as $i) {
         $color=$i;
         if ($i>9) {
            $color=1;
         }
         $def[1] .= "DEF:L$i=$RRDFILE[$i]:$DS[$i]:AVERAGE ";
         $def[1] .= "LINE2:L$i".$arrayColor[$color].":\"$NAME[$i]\t\" ";
         $def[1] .= "GPRINT:L$i:LAST:\"%2.2lf V curr\" ";
         $def[1] .= "GPRINT:L$i:MAX:\"%2.2lf V max\" ";
         $def[1] .= "GPRINT:L$i:MIN:\"%2.2lf V min\\n\" "; 
      } 

   $def[1] .= "HRULE:".$targetValue."$_LINE:\"\" ";
   $def[1] .= "HRULE:$WARN_MAX[1]$_WARNRULE:\"\" ";
   $def[1] .= "HRULE:$WARN_MIN[1]$_WARNRULE:\"Warning\: less $WARN_MIN[1]V high $WARN_MAX[1]V\" ";
   $def[1] .= "HRULE:$CRIT_MAX[1]$_CRITRULE:\"\" ";
   $def[1] .= "HRULE:$CRIT_MIN[1]$_CRITRULE:\"Critical\: less $CRIT_MIN[1]V high $CRIT_MAX[1]V\\n\" ";
} 
#by default   
else { 
   foreach ($this->DS as $KEY=>$VAL) {

        $maximum  = "";
        $minimum  = "";
        $critical = "";
        $crit_min = "";
        $crit_max = "";
        $warning  = "";
        $warn_max = "";
        $warn_min = "";
        $vlabel   = " ";
        $lower    = "";
        $upper    = "";

        if ($VAL['WARN'] != "") {
                $warning = $VAL['WARN'];
        }
        if ($VAL['WARN_MAX'] != "") {
                $warn_max = $VAL['WARN_MAX'];
        }
        if ($VAL['WARN_MIN'] != "") {
                $warn_min = $VAL['WARN_MIN'];
        }
        if ($VAL['CRIT'] != "") {
                $critical = $VAL['CRIT'];
        }
        if ($VAL['CRIT_MAX'] != "") {
                $crit_max = $VAL['CRIT_MAX'];
        }
        if ($VAL['CRIT_MIN'] != "") {
                $crit_min = $VAL['CRIT_MIN'];
        }
        if ($VAL['MIN'] != "") {
                $lower = " --lower=" . $VAL['MIN'];
                $minimum = $VAL['MIN'];
        }
        if ($VAL['MAX'] != "") {
                $maximum = $VAL['MAX'];
        }
        if ($VAL['UNIT'] == "%%") {
                $vlabel = "%";
                $upper = " --upper=101 ";
                $lower = " --lower=0 ";
        }
        else {
                $vlabel = $VAL['UNIT'];
        }

        $opt[$KEY] = '--vertical-label "' . $vlabel . '" --title "' . $this->MACRO['DISP_HOSTNAME'] . ' / ' . $this->MACRO['DISP_SERVICEDESC'] . '"' . $upper . $lower;
        $ds_name[$KEY] = $VAL['LABEL'];
        $def[$KEY]  = rrd::def     ("var1", $VAL['RRDFILE'], $VAL['DS'], "AVERAGE");
        $def[$KEY] .= rrd::gradient("var1", "3152A5", "BDC6DE", rrd::cut($VAL['NAME'],16), 20);
        $def[$KEY] .= rrd::line1   ("var1", $_LINE );
        $def[$KEY] .= rrd::gprint  ("var1", array("LAST","MAX","AVERAGE"), "%3.4lf %S".$VAL['UNIT']);
		if ($warning != "") {
                $def[$KEY] .= rrd::hrule($warning, $_WARNRULE, "Warning  $warning \\n");
        }
        if ($warn_min != "") {
                $def[$KEY] .= rrd::hrule($warn_min, $_WARNRULE, "Warning  (min)  $warn_min \\n");
        }
        if ($warn_max != "") {
                $def[$KEY] .= rrd::hrule($warn_max, $_WARNRULE, "Warning  (max)  $warn_max \\n");
        }
        if ($critical != "") {
                $def[$KEY] .= rrd::hrule($critical, $_CRITRULE, "Critical $critical \\n");
        }
        if ($crit_min != "") {
                $def[$KEY] .= rrd::hrule($crit_min, $_CRITRULE, "Critical (min)  $crit_min \\n");
        }
        if ($crit_max != "") {
                $def[$KEY] .= rrd::hrule($crit_max, $_CRITRULE, "Critical (max)  $crit_max \\n");
        }
        $def[$KEY] .= rrd::comment("Default Template\\r");
        $def[$KEY] .= rrd::comment("Command " . $VAL['TEMPLATE'] . "\\r");
   }
}


?>
