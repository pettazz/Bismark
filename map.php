<?php
    
	require('jacked_configVars.php');
	require('jacked_functions.php');
	require('jacked_configbismark.php');
	require('jacked_bismarkfunctions.php');

?>
<html>
<head>
<title>Bismarked</title>
<style type="text/css">
	.thing{
		font-family: Helvetica, Arial, sans-serif;
		font-color: #333;
	}
</style>

<script src="http://maps.google.com/maps?file=api&amp;v=2&amp;sensor=false&amp;key=ABQIAAAAiUXaPfYEgZv16SbVhhne8RSwv2RzoYtH5D2BTNOAlva6ax9zchQZd9nQTrnoS9nxY0Mi99HIB_H34A" type="text/javascript"></script>
<script type="text/javascript">


function initialize() {
  var map = new GMap2(document.getElementById("map_canvas"));
  map.setCenter(new GLatLng(42.3576, -71.0588), 17);
 map.addControl(new GLargeMapControl());
map.addControl(new GMapTypeControl());

 
 
<?php
	$result = jackedDBQuery("SELECT * FROM " . JACKED_BISMARK_DB_MARKS);
	$lol = 1;
	while($row = mysql_fetch_array($result, MYSQL_ASSOC)){
		echo 'var point' . $lol .' = new GLatLng(' . $row['latitude'] . ', ' . $row['longitude'] . ');' . "\n";
		echo 'marker = new GMarker(point' . $lol .');'. "\n";
		echo 'GEvent.addListener(marker, "click", function() {'. "\n";
		echo 'var myHtml = \'<span class="thing">Altitude: ' . $row[altitude] . ' meters</span>\'' . "\n";
		echo 'map.openInfoWindowHtml(point' . $lol . ', myHtml);'. "\n";
		echo '});'. "\n";
		echo 'map.addOverlay(marker);'. "\n";
		$lol++;
	}

?>
	
}
</script>

</head>
  <body onload="initialize()" onunload="GUnload()">

<div id="map_canvas" style="width: 1000px; height: 800px"></div>

</body>
</html>