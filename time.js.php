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
		var countString = Math.floor(gap/year)+\" \"+\"" . getString("time_year") . "\"+\" \"+Math.floor((gap%year)/month)+\" \"+\"" . getString("time_month") . "\";
	}else if(gap>=month){
		var countString = Math.floor(gap/month)+\" \"+\"" . getString("time_month") . "\"+\" \"+Math.floor((gap%month)/day)+\" \"+\"" . getString("time_day") . "\";
	}else if(gap>=day){
		var countString = Math.floor(gap/day)+\" \"+\"" . getString("time_day") . "\"+\" \"+Math.floor((gap%day)/hour)+\" \"+\"" . getString("time_hour") . "\";
	}else if(gap>=hour){
		var countString = Math.floor(gap/hour)+\":\"+(\"0\"+Math.floor((gap%hour)/minute)).slice(-2)+\":\"+(\"0\"+Math.floor((gap%minute)/second)).slice(-2);
	}else if(gap>=minute){
		var countString = Math.floor(gap/minute)+\":\"+(\"0\"+Math.floor((gap%minute)/second)).slice(-2);
	}else{
		var countString = \":\"+Math.floor(gap/second);
	}
	return countString;
}

function display(date, abbrID){
	abbr = document.getElementById(abbrID);

	abbr.innerText = getTimeLeft(date);

	setTimeout(display, second, date, abbrID);
}
</script>";