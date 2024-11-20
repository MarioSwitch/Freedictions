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

function getTimeLeft(date){
	var nowDate = new Date();
	var goalDate = new Date(date);
	var gap = Math.abs(goalDate - nowDate);
	//Formatting count
	if(gap>=year){
		var countString = Math.floor(gap/year)+\"" . getString("time_year_short") . "\"+\" \"+Math.floor((gap%year)/month)+\"" . getString("time_month_short") . "\";
	}else if(gap>=month){
		var countString = Math.floor(gap/month)+\"" . getString("time_month_short") . "\"+\" \"+Math.floor((gap%month)/day)+\"" . getString("time_day_short") . "\";
	}else if(gap>=day){
		var countString = Math.floor(gap/day)+\"" . getString("time_day_short") . "\"+\" \"+Math.floor((gap%day)/hour)+\"" . getString("time_hour_short") . "\";
	}else if(gap>=hour){
		var countString = Math.floor(gap/hour)+\":\"+(\"0\"+Math.floor((gap%hour)/minute)).slice(-2)+\":\"+(\"0\"+Math.floor((gap%minute)/second)).slice(-2);
	}else if(gap>=minute){
		var countString = Math.floor(gap/minute)+\":\"+(\"0\"+Math.floor((gap%minute)/second)).slice(-2);
	}else{
		var countString = \":\"+Math.floor(gap/second);
	}
	return countString;
}

function getLocalTime(UTCString){ // UTCString: \"YYYY-MM-DD HH:MM:SS\"
	var year = parseInt(UTCString.substring(0, 4));
	var month = parseInt(UTCString.substring(5, 7));
	var day = parseInt(UTCString.substring(8, 10));
	var hour = parseInt(UTCString.substring(11, 13));
	var minute = parseInt(UTCString.substring(14, 16));
	var second = parseInt(UTCString.substring(17, 19));

	var convertedDate = new Date(Date.UTC(year, month-1, day, hour, minute, second));

	return convertedDate.toLocaleString();
}

function display(date, abbrID){
	abbr = document.getElementById(abbrID);

	abbr.innerText = getTimeLeft(date);
	abbr.title = getLocalTime(date);

	setTimeout(display, second, date, abbrID);
}
</script>";