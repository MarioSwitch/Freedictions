<?php
include_once "functions.php";
echo print_r($_REQUEST);
echo "<br>";
echo NOW;
echo "<br>";
echo displayUser("MarioSwitch", true);
echo "<br>";
echo displayInt(-1234567, true, true);
echo "<br>";
echo displayRank(2);
echo "<br>";
echo displayFloat(-150.9);