function UTCtoLocal(UTCString, paragraph){ // UTCString: "YYYY-MM-DD HH:MM:SS"
    var year = parseInt(UTCString.substring(0, 4));
    var month = parseInt(UTCString.substring(5, 7));
    var day = parseInt(UTCString.substring(8, 10));
    var hour = parseInt(UTCString.substring(11, 13));
    var minute = parseInt(UTCString.substring(14, 16));
    var second = parseInt(UTCString.substring(17, 19));
    var UTCDate = new Date(Date.UTC(year, month-1, day, hour, minute, second));
    var localDate = new Date(UTCDate.getTime() - UTCDate.getTimezoneOffset()*60000);
    var timezoneName = Intl.DateTimeFormat().resolvedOptions().timeZone;
    var timezoneAbbr = localDate.toLocaleTimeString('en-us', { timeZoneName: 'short' }).split(' ').pop();
    
    var fullString = localDate.toLocaleString() + " Heure locale (" + timezoneName + " | " + timezoneAbbr + ")";
    
    // Find the <abbr> element within this <p> tag
    const abbrElement = paragraph.querySelector('abbr');

    // Check if the <abbr> element exists to avoid errors
    if (abbrElement) {
        // Append text to the title attribute
        abbrElement.title += "\n" + fullString;
    }
}