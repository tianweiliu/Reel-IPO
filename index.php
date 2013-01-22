<?php 
  /*
  Reel IPO JSONP Output Page
  This page will redirect normal traffic to "admin.php", or output IPO info as JSONP if a called by a valid post request.
    -- by Tianwei Liu (tianwei.liu89@gmail.com)
  */
  include("ipo.php");
	if (empty($_GET["semester"]))
		$semester = "f12";
	else
		$semester = $_GET["semester"];
	$STOCK_URL = "http://www2.tech.purdue.edu/cgt/Courses/cgt411/COGENT/".$semester."IPO_files/sheet001.htm";
	$mode = "auto";
	if (!empty($_GET["key"])) {
		$key = $_GET["key"];
		$mode = "manual";
	}
	if (empty($_GET["group_id"]) || empty($_GET["jsonp"]))
		header("Location:admin.php");
	else {
		echo $_GET["jsonp"]."(".json_encode(RetrieveIPO($_GET["group_id"], $semester, $key, $mode)).")";
	}
?>
