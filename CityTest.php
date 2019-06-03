<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap JS-->
<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
City Info
<form method="POST">
  <div class="form-group">
    <label for="exampleFormControlSelect1">Select a city:</label>
    <select class="form-control" id="exampleFormControlSelect1" name="city">
      <option>Portland</option>
      <option>Seattle</option>
      <option>Anchorage</option>
      <option>Honolulu</option>
      <option>New York</option>
      <option>Los Angeles</option>
    </select>
  </div>
<input class="btn btn-primary" type="submit" value="Submit">
</form>

<?php
if(isset($_POST["city"]))
{
	$connection= pg_connect("host=dbclass.cs.pdx.edu user=s19wdb31 dbname=s19wdb31 password=Alexdbcla$$");


	echo "<h1>Weather Data for " . $_POST["city"] . "</h1>";

	$maxTempQuery = "SELECT temp_max, TO_CHAR(weather_date, 'Month DD, YYYY')
	    FROM CityInfo.Location NATURAL JOIN CityInfo.Weather
	   WHERE city = '" . $_POST["city"] . "'
             AND temp_max = (SELECT MAX(b.temp_max)
			       FROM CityInfo.Weather b NATURAL JOIN CityInfo.Location
			      WHERE city = '" . $_POST["city"] . "');";
	$result= pg_query($connection, $maxTempQuery);
	$numrows = pg_num_rows($result);
	$maxDates = array();
	$i = 0;
	while ($row = pg_fetch_row($result)) {

	$maxTemp = $row[0];
	$maxDates[$i] = $row[1];
	$i = $i + 1;
	}
	echo "The hottest day(s) were ";

	foreach($maxDates as &$date){
		echo $date . " ";
	}
	echo ".  It was " . $maxTemp . "°F on these day(s).<br>";
	pg_free_result($result);

	echo "<br>";

	//MIN TEMP QUERY
	$minTempQuery = "SELECT temp_min, TO_CHAR(weather_date, 'Month DD, YYYY')
	    FROM CityInfo.Location NATURAL JOIN CityInfo.Weather
	   WHERE city = '" . $_POST["city"] . "'
             AND temp_min = (SELECT MIN(b.temp_min)
			       FROM CityInfo.Weather b NATURAL JOIN CityInfo.Location
			      WHERE city = '" . $_POST["city"] . "');";
	$result= pg_query($connection, $minTempQuery);
	$numrows = pg_num_rows($result);
	$minDates = array();
	$i = 0;
	while ($row = pg_fetch_row($result)) {

	$minTemp = $row[0];
	$minDates[$i] = $row[1];
	$i = $i + 1;
	}
	echo "The coldest day(s) were ";

	foreach($minDates as &$date){
		echo $date . " ";
	}
	echo ".  It was " . $minTemp . "°F on these day(s).<br>";




	//AIR QUALITY
	echo "<h1>Air Quality Data for " . $_POST["city"] . "</h1>";

	$maxPMQuery = "SELECT Measurement, TO_CHAR(airquality_date, 'Month DD, YYYY')
		         FROM CityInfo.AirQuality NATURAL JOIN CityInfo.Weather
			WHERE city = '" . $_POST["city"] . "'
			  AND pollutant = 'PM2.5'
			  AND Measurement = 

	pg_free_result($result);
	pg_close($connection);
}
?>
  </body>
</html>
