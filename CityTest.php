<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
City Info
<?php
$connection= pg_connect("host=dbclass.cs.pdx.edu user=s19wdb31 dbname=s19wdb31 password=Alexdbcla$$");
$query = "SELECT city, weather_date, precipitation, temp_max, temp_min
	    FROM CityInfo.Location NATURAL JOIN CityInfo.Weather
           WHERE city = 'Portland';";
$result= pg_query($connection, $query);
$numrows = pg_num_rows($result);
$numfields = pg_num_fields($result);

echo '<table class="table">
	<thead>
          <tr>
            <th scope="col">City</th>
            <th scope="col">Date</th>
            <th scope="col">Precipitation (in.)</th>
            <th scope="col">Max Temp (°F)</th>
            <th scope="col">Min Temp (°F)</th>
          </tr>
        </thead>
        <tbody>';
while ($row = pg_fetch_row($result)) {
        $i = 0;
	echo '<tr>';
        while ($i < $numfields){
                $current_value = $row[$i];
                echo '<td>' . $current_value . '</td>';
                $i = $i + 1;
        }
	echo '</tr>';
}
echo '</tbody></table>';
pg_free_result($result);
pg_close($connection);

?>
  </body>
</html>
