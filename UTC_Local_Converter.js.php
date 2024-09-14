<?php
echo "
<script>
function UTCtoLocal(UTCString, abbr){ // UTCString: \"YYYY-MM-DD HH:MM:SS\"
    var year = parseInt(UTCString.substring(0, 4));
    var month = parseInt(UTCString.substring(5, 7));
    var day = parseInt(UTCString.substring(8, 10));
    var hour = parseInt(UTCString.substring(11, 13));
    var minute = parseInt(UTCString.substring(14, 16));
    var second = parseInt(UTCString.substring(17, 19));
    var convertedDate = new Date(Date.UTC(year, month-1, day, hour, minute, second));
    var timezoneName = Intl.DateTimeFormat().resolvedOptions().timeZone;
    var timezoneAbbr = convertedDate.toLocaleTimeString('en-us', { timeZoneName: 'short' }).split(' ').pop();

    var fullString = convertedDate.toLocaleString() + \" – \" + \"" . getString("javascript_converter_local_time") . "\" + \" – \" + timezoneName + \" – \" + timezoneAbbr + \"\";

    abbr.title += \"\\n\" + fullString;
}
</script>";