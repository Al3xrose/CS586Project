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


	echo "<h2>Weather Data for " . $_POST["city"] . "</h2>";
	echo "<br>";

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
	echo ".  It reached a high temperature of " . $maxTemp . "°F on these day(s).<br><br>";
	pg_free_result($result);

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
	echo ".  It reached a low temperature of " . $minTemp . "°F on these day(s).<br><br>";


	pg_free_result($result);

	//MONTHLY WEATHER

	$hottestMonthQuery = 
		"SELECT EXTRACT(Year from weather_date), 
		EXTRACT(Month from weather_date), 
		AVG(temp_max)
		FROM CityInfo.Weather NATURAL JOIN CityInfo.Location
		WHERE city = '" . $_POST["city"] . "'
		GROUP BY EXTRACT(Year from weather_date), 
		EXTRACT(Month from weather_date)
		HAVING AVG(temp_max) = (SELECT MAX(avg_monthly_high) 
		FROM (SELECT AVG(b.temp_max) avg_monthly_high
		FROM CityInfo.Weather b NATURAL JOIN CityInfo.Location
		WHERE city = '" . $_POST["city"] ."' 
		GROUP BY EXTRACT(Year from b.weather_date), 
		EXTRACT(Month from b.weather_date)) c)";

	$result= pg_query($connection, $hottestMonthQuery);
	$numrows = pg_num_rows($result);
	$hotMonths = array();
	$hotYears = array();
	$i = 0;
	while ($row = pg_fetch_row($result)) {
		$hotYears[$i] = $row[0];	
		$hotMonths[$i] = $row[1];
		$hotTemp = $row[2];
		$i = $i + 1;
	}
	echo "The hottest month was ";

	for($j = 0; $j < $i; $j = $j + 1){
		echo date('M', mktime(0, 0, 0, $hotMonths[$j])).  " " . $hotYears[$j] . " ";
	}
	echo ".  The average high temperature was " . round($hotTemp, 2) . "°F during this month.<br><br>";

	pg_free_result($result);

	//Coldest month

	$coldestMonthQuery = 
 	  "SELECT EXTRACT(Year from weather_date), 
		  EXTRACT(Month from weather_date), 
		  AVG(temp_max)
	          FROM CityInfo.Weather NATURAL JOIN CityInfo.Location
	    WHERE city = '" . $_POST["city"] . "'
         GROUP BY EXTRACT(Year from weather_date), 
                  EXTRACT(Month from weather_date)
           HAVING AVG(temp_max) = (SELECT MIN(avg_monthly_high) 
                                     FROM (SELECT AVG(b.temp_max) avg_monthly_high
                                             FROM CityInfo.Weather b NATURAL JOIN CityInfo.Location
                                            WHERE city = '" . $_POST["city"] ."' 
					 GROUP BY EXTRACT(Year from b.weather_date), 
                                                  EXTRACT(Month from b.weather_date)) c)";
 
	$result= pg_query($connection, $coldestMonthQuery);
	$numrows = pg_num_rows($result);
	$coldMonths = array();
	$coldYears = array();
	$i = 0;
	while ($row = pg_fetch_row($result)) {
	        $coldYears[$i] = $row[0];	
		$coldMonths[$i] = $row[1];
		$coldTemp = $row[2];
		$i = $i + 1;
	}
	echo "The coldest month was ";

	for($j = 0; $j < $i; $j = $j + 1){
		echo date('M', mktime(0, 0, 0, $coldMonths[$j])).  " " . $coldYears[$j] . " ";
	}
	echo ".  The average high temperature was " . round($coldTemp, 2) . "°F during this month.<br><br>";

	pg_free_result($result);

	//AIR QUALITY

	echo "<h2>Air Quality Data for " . $_POST["city"] . "</h2>";
	echo "<br>";

	$highestPMQuery =
		"SELECT TO_CHAR(airquality_date, 'Month DD, YYYY'),
		        measurement
		   FROM CityInfo.AirQuality NATURAL JOIN CityInfo.Location
		  WHERE city = '" . $_POST["city"] . "'
                    AND pollutant = 'PM2.5'
                    AND measurement = (SELECT MAX(b.measurement)
                                         FROM CityInfo.AirQuality b NATURAL JOIN CityInfo.Location
					WHERE city = '" . $_POST["city"] . "'
                                          AND POLLUTANT = 'PM2.5');";

	$result= pg_query($connection, $highestPMQuery);
	$numrows = pg_num_rows($result);
	$maxDates = array();
	$i = 0;
	while ($row = pg_fetch_row($result)) {

	$maxPMDates[$i] = $row[0];
	$maxPM = $row[1];
	$i = $i + 1;
	}
	echo "The day(s) with the highest recorded level of PM2.5 were ";

	foreach($maxPMDates as &$date){
		echo $date . " ";
	}
	echo "The recorded level of PM2.5 was " . $maxPM . "μg/m³ on these day(s).<br><br>";
        pg_free_result($result);

	$highestPMMonthQuery = 
		"SELECT EXTRACT(Year from airquality_date), 
		EXTRACT(Month from airquality_date), 
		AVG(measurement)
		FROM CityInfo.AirQuality NATURAL JOIN CityInfo.Location
		WHERE city = '" . $_POST["city"] . "'
                AND pollutant = 'PM2.5'
		GROUP BY EXTRACT(Year from airquality_date), 
		EXTRACT(Month from airquality_date)
		HAVING AVG(measurement) = (SELECT MAX(avg_monthly_measurement) 
		FROM (SELECT AVG(b.measurement) avg_monthly_measurement
		FROM CityInfo.AirQuality b NATURAL JOIN CityInfo.Location
		WHERE city = '" . $_POST["city"] ."' 
                AND pollutant = 'PM2.5'
		GROUP BY EXTRACT(Year from b.airquality_date), 
		EXTRACT(Month from b.airquality_date)) c)";

	$result= pg_query($connection, $highestPMMonthQuery);
	$numrows = pg_num_rows($result);
	$highPMMonths = array();
	$highPMYears = array();
	$i = 0;
	while ($row = pg_fetch_row($result)) {
		$highPMYears[$i] = $row[0];	
		$highPMMonths[$i] = $row[1];
		$highPM = $row[2];
		$i = $i + 1;
	}
	echo "The month with the highest average recorded level of PM2.5 was  ";

	for($j = 0; $j < $i; $j = $j + 1){
		echo date('M', mktime(0, 0, 0, $highPMMonths[$j])).  " " . $highPMYears[$j] . " ";
	}
	echo ".  The average level of PM2.5 was " . round($highPM, 2) . "μg/m³ during this month.<br><br>";

	pg_free_result($result);

	$highestOzoneQuery =
		"SELECT TO_CHAR(airquality_date),
		        measurement
		   FROM CityInfo.AirQuality NATURAL JOIN CityInfo.Location
		  WHERE city = '" . $_POST["city"] . "'
                    AND pollutant = 'Ozone'
                    AND measurement = (SELECT MAX(b.measurement)
                                         FROM CityInfo.AirQuality b NATURAL JOIN CityInfo.Location
					WHERE city = '" . $_POST["city"] . "'
                                          AND POLLUTANT = 'Ozone');";

	$result= pg_query($connection, $highestOzoneQuery);
	$numrows = pg_num_rows($result);
	$maxDates = array();
	$i = 0;
	while ($row = pg_fetch_row($result)) {

	$maxOzoneDates[$i] = $row[0];
	$maxOzone = $row[1];
	$i = $i + 1;
	}
	echo "The day(s) with the highest recorded level of Ozone were ";

	foreach($maxOzoneDates as &$date){
		echo $date . " ";
	}
	echo "The recorded level of Ozone was " . $maxOzone . "ppm on these day(s).<br><br>";
        pg_free_result($result);

	$highestOzoneMonthQuery = 
		"SELECT EXTRACT(Year from airquality_date), 
		EXTRACT(Month from airquality_date), 
		AVG(measurement)
		FROM CityInfo.AirQuality NATURAL JOIN CityInfo.Location
		WHERE city = '" . $_POST["city"] . "'
                AND pollutant = 'Ozone'
		GROUP BY EXTRACT(Year from airquality_date), 
		EXTRACT(Month from airquality_date)
		HAVING AVG(measurement) = (SELECT MAX(avg_monthly_measurement) 
		FROM (SELECT AVG(b.measurement) avg_monthly_measurement
		FROM CityInfo.AirQuality b NATURAL JOIN CityInfo.Location
		WHERE city = '" . $_POST["city"] ."' 
                AND pollutant = 'Ozone'
		GROUP BY EXTRACT(Year from b.airquality_date), 
		EXTRACT(Month from b.airquality_date)) c)";

	$result= pg_query($connection, $highestOzoneMonthQuery);
	$numrows = pg_num_rows($result);
	$highOzoneMonths = array();
	$highOzoneYears = array();
	$i = 0;
	while ($row = pg_fetch_row($result)) {
		$highOzoneYears[$i] = $row[0];	
		$highOzoneMonths[$i] = $row[1];
		$highOzone = $row[2];
		$i = $i + 1;
	}
	echo "The month with the highest average recorded level of Ozone was  ";

	for($j = 0; $j < $i; $j = $j + 1){
		echo date('M', mktime(0, 0, 0, $highOzoneMonths[$j])).  " " . $highOzoneYears[$j] . " ";
	}
	echo ".  The average level of Ozone was " . round($highOzone, 2) . "ppm during this month.<br><br>";

	pg_free_result($result);

	//Crime Data
	
	echo "<h2>Crime Data for " . $_POST["city"] . " from 2010-2017</h2>";
	echo "<br>";

	$yearlyAvgCrimeQuery = 
		"SELECT AVG(violent_crimes), AVG(property_crimes)
		   FROM CityInfo.Crime NATURAL JOIN CityInfo.Location
		  WHERE city = '" . $_POST["city"] . "';";


        $result = pg_query($connection, $yearlyAvgCrimeQuery);
        $resultRow = pg_fetch_row($result);

	$yearlyAvgViolent = $resultRow[0];
	$yearlyAvgProperty = $resultRow[1];

	echo "The average number of violent crimes is " . round($yearlyAvgViolent) . " per year.<br>";

	echo "The average number of property crimes is " . round($yearlyAvgProperty) . " per year.<br><br>";

	pg_free_result($result);

	//Home Price Data

	echo "<h2>Home Price Data for " . $_POST["city"] . " from 2010-2018</h2>";
	echo "<br>";

	$homePriceQuery =
		"SELECT ten.avg_price,
		        eighteen.avg_price,
		        eighteen.avg_price - ten.avg_price diff
		   FROM CityInfo.HomePrice ten, 
                        CityInfo.HomePrice eighteen,
                        CityInfo.Location 
		  WHERE ten.loc_id = location.loc_id
                    AND eighteen.loc_id = location.loc_id 
                    AND city = '" . $_POST["city"] . "'
                    AND ten.year = '2010'
                    AND eighteen.year = '2018';";

        $result = pg_query($connection, $homePriceQuery);

	$resultRow = pg_fetch_row($result);

	$tenAvgPrice = $resultRow[0];
	$eighteenAvgPrice = $resultRow[1];
	$priceDiff = $resultRow[2];
	$avgYearlyChange = $priceDiff / 8;

	echo "The average home price in 2010 was $" . $tenAvgPrice . "<br>";
	echo "The average home price in 2018 was $" . $eighteenAvgPrice . "<br>";
	echo "On average, home prices ";
	if($priceDiff > 0)
		echo "increased by ";
	else
		echo "decreased by ";
	echo "$" . $avgYearlyChange . " per year.<br>";

	pg_free_result($result);

	//Population data
	
	echo "<h2>Population data for " . $_POST["city"] . " from 2010-2017</h2>";
	echo "<br>";

	$populationQuery =
		"SELECT ten.population,
		        seventeen.population,
		        seventeen.population - ten.population diff
		   FROM CityInfo.CityPopulation ten, 
                        CityInfo.CityPopulation seventeen,
                        CityInfo.Location 
		  WHERE ten.loc_id = location.loc_id
                    AND seventeen.loc_id = location.loc_id 
                    AND city = '" . $_POST["city"] . "'
                    AND ten.year = '2010'
                    AND seventeen.year = '2017';";

        $result = pg_query($connection, $populationQuery);

	$resultRow = pg_fetch_row($result);

	$tenPop = $resultRow[0];
	$seventeenPop = $resultRow[1];
	$popDiff = $resultRow[2];
	$avgYearlyChange = $popDiff / 7;

	echo "The population in 2010 was " . $tenPop . "<br>";
	echo "The population in 2017 was " . $seventeenPop . "<br>";
	echo "On average, the population ";
	if($priceDiff > 0)
		echo "increased by ";
	else
		echo "decreased by ";
	echo round($avgYearlyChange) . " per year.";

	pg_free_result($result);

	echo "<h2>Places of Interest in or near " . $_POST["city"] . "</h2>";

	echo "<h3>Natural Attractions</h3>";

	$natAttQuery =
		"SELECT PlaceOfInterest.name,
		        distance,
			description
		   FROM CityInfo.PlaceOfInterest, 
			CityInfo.NaturalAttraction,
                        CityInfo.Location
                  WHERE PlaceOfInterest.loc_id = NaturalAttraction.loc_id
                    AND PlaceOfInterest.name = NaturalAttraction.name
                    AND PlaceOfInterest.loc_id = Location.loc_id
                    AND city = '" . $_POST["city"] . "';"; 
         
	$result= pg_query($connection, $natAttQuery);
	$numrows = pg_num_rows($result);
	$names = array();
	$distances = array();
	$descriptions = array();
	$i = 0;
	while ($row = pg_fetch_row($result)) {
                $names[$i] = $row[0];
		$distances[$i] = $row[1];
		$descriptions[$i] = $row[2];
		
		$i = $i + 1;
	}

	for($j = 0; $j < $i; $j = $j + 1)
	{
		echo "<b>" . $names[$j] . "</b> : ";

		if($distances[$j] == 0)
			echo "Within the city of " . $_POST["city"];
		else
		{
			echo $distances[$j] . " miles from " . $_POST["city"];
		}
		echo "<br>";
		echo $descriptions[$j];
		echo "<br>";
	}

	echo "<h3>Colleges</h3>";

	$universityQuery =
		"SELECT PlaceOfInterest.name,
			enrollment,
			acceptance_rate
		   FROM CityInfo.PlaceOfInterest, 
			CityInfo.University,
                        CityInfo.Location
                  WHERE PlaceOfInterest.loc_id = University.loc_id
                    AND PlaceOfInterest.name = University.name
                    AND PlaceOfInterest.loc_id = Location.loc_id
                    AND city = '" . $_POST["city"] . "';"; 
         
	$result= pg_query($connection, $universityQuery);
	$numrows = pg_num_rows($result);
	$names = array();
	$enrollments = array();
	$acceptances = array();
	$i = 0;
	while ($row = pg_fetch_row($result)) {
                $names[$i] = $row[0];
		$enrollments[$i] = $row[1];
		$acceptances[$i] = $row[2];
		
		$i = $i + 1;
	}

	for($j = 0; $j < $i; $j = $j + 1)
	{
		echo "<b>" . $names[$j] . "</b> : has an enrollment of " . $enrollments[$j] . " and an acceptance rate of " . $acceptances[$j] . "%";

		echo "<br>";
	}

	echo "<h3>Hotels</h3>";

	$hotelsQuery =
		"SELECT PlaceOfInterest.name,
		        distance,
			stars
		   FROM CityInfo.PlaceOfInterest, 
			CityInfo.Hotel,
                        CityInfo.Location
                  WHERE PlaceOfInterest.loc_id = Hotel.loc_id
                    AND PlaceOfInterest.name = Hotel.name
                    AND PlaceOfInterest.loc_id = Location.loc_id
                    AND city = '" . $_POST["city"] . "';"; 
         
	$result= pg_query($connection, $hotelsQuery);
	$numrows = pg_num_rows($result);
	$names = array();
	$distances = array();
	$stars = array();
	$i = 0;
	while ($row = pg_fetch_row($result)) {
                $names[$i] = $row[0];
		$distances[$i] = $row[1];
		$stars[$i] = $row[2];
		
		$i = $i + 1;
	}

	for($j = 0; $j < $i; $j = $j + 1)
	{
		echo "<b>" . $names[$j] . "</b> : A " . $stars[$j] . " star hotel ";

		if(distances[$j] == 0)
			echo "within the city of " . $_POST["city"];
		else
			echo $distances[$j] . " miles from " . $_POST["city"];
			
		echo "<br>";
	}

	pg_free_result($result);
 	pg_close($connection);


}
?>
  </body>
</html>
