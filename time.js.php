<?php
include_once "functions.php";
echo "
<script>
var year = 31556952000; //1 year = 365.2425 days = 31,556,952,000 ms
var month = year/12; //1 month = 30.436875 days = 2,629,746,000 ms
var day = 86400000; //1 day = 86,400,000 ms
var hour = day/24; //1 hour = 3,600,000 ms
var minute = hour/60; //1 minute = 60,000 ms
var second = minute/60; //1 second = 1,000 ms

function getCountdown(date){
    var nowDate = new Date();
    var goalDate = new Date(date);
    var gap = Math.abs(goalDate - nowDate);
    //Formatting count
    if(gap>=2*year){ //if more than 2 years
        var countString = Math.floor(gap/year)+\" \"+\"" . getString("time_years") . "\";
    }
    if(gap<2*year && gap>=year){ //if between 1 and 2 years
        var countString = Math.floor(gap/year)+\" \"+\"" . getString("time_year") . "\";
    }
    if(gap<year && gap>=2*month){ //if between 2 months and 1 year
        var countString = Math.floor(gap/month)+\" \"+\"" . getString("time_months") . "\";
    }
    if(gap<2*month && gap>=month){ //if between 1 and 2 months
        var countString = Math.floor(gap/month)+\" \"+\"" . getString("time_month") . "\";
    }
    if(gap<month && gap>=100*hour){ //if between 100 hours (4d 4h) and 1 month
        var countString = Math.floor(gap/day)+\" \"+\"" . getString("time_days") . "\";
    }
    if(gap<100*hour && gap>=hour){ //if between 1 hour and 100 hours (4d 4h)
        var countString = Math.floor(gap/hour)+\" \"+\"" . getString("time_h") . "\"+\" \"+(\"0\"+Math.floor(gap/minute)%60).slice(-2)+\" \"+\"" . getString("time_m") . "\";
    }
    if(gap<hour && gap>=minute){ //if between 1 min and 1 hour
        var countString = Math.floor(gap/minute)+\"" . getString("time_m_short") . "\"+\" \"+(\"0\"+Math.floor(gap/second)%60).slice(-2)+\"" . getString("time_s_short") . "\";
    }
    if(gap<minute && gap>=0){ //if less than 1 min
        var countString = Math.floor(gap/second)+\"" . getString("time_s_short") . "\";
    }
    //Choosing the right format
    formatBefore = \"" . getString("javascript_countdown_in") . "\";
    formatAfter = \"" . getString("javascript_countdown_ago") . "\";
    if(goalDate > nowDate){
        var finalString = formatBefore.replace('[TBR]',countString);
    }else{
        var finalString = formatAfter.replace('[TBR]',countString);
    }
    return finalString;
}

function getLocalTime(UTCString){ // UTCString: \"YYYY-MM-DD HH:MM:SS\"
    var year = parseInt(UTCString.substring(0, 4));
    var month = parseInt(UTCString.substring(5, 7));
    var day = parseInt(UTCString.substring(8, 10));
    var hour = parseInt(UTCString.substring(11, 13));
    var minute = parseInt(UTCString.substring(14, 16));
    var second = parseInt(UTCString.substring(17, 19));
    var convertedDate = new Date(Date.UTC(year, month-1, day, hour, minute, second));

    var finalString = convertedDate.toLocaleString();

    return finalString;
}

function displayDateTime(UTC, abbrID){ // UTC: \"YYYY-MM-DD HH:MM:SS\"
    var td = \"" . getSetting("td") . "\";

    var UTC_TZ = UTC.substring(0, 10);
    UTC_TZ += \"T\";
    UTC_TZ += UTC.substring(11, 19);
    UTC_TZ += \"Z\";

    abbr = document.getElementById(abbrID);

    var title_relative = getCountdown(UTC_TZ);
    var title_UTC = UTC_TZ;
    var title_local = getLocalTime(UTC) + \" – \" + \"" . getString("javascript_converter_local_time") . "\"

    if(td == \"relative\"){
        abbr.innerText = getCountdown(UTC_TZ);
        abbr.title = title_UTC + \"\\n\" + title_local;
    }
    if(td == \"local\"){
        abbr.innerText = getLocalTime(UTC);
        abbr.title = title_UTC + \"\\n\" + title_relative;
    }
    if(td == \"utc\"){
        abbr.innerText = UTC_TZ;
        abbr.title = title_relative + \"\\n\" + title_local;
    }

    setTimeout(displayDateTime, second, UTC, abbrID);
}
</script>";