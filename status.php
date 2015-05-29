<?php
##########################################################################################
## PHP MySQL data view script for Digium Term History
## by Jörg Neikes at aixtema (dot) de
## V 0.2 29.06.2010
##  Original from Moritz Fuchs <fr1tz@gmx.de> under 
## License: GNU GPL, v2 or later <http://www.fsf.org/licenses/gpl.txt>
##
##  
###########################################################################################

date_default_timezone_set('Europe/Berlin');

## Website things

$host=$_SERVER["HTTP_HOST"];
$server=$_SERVER["SERVER_NAME"];
$remote=$_SERVER["REMOTE_ADDR"];
setlocale(LC_TIME, "de_DE"); 

$gray = <<<gray
<span style="color: #F0F0F0; background-color: #F0F0F0">
gray;

$white = <<<white
<span style="color: #FFFFFF">
white;

$red = <<<red
<span style="color: #FF0000; background-color: #F0F0F0">
red;

$green = <<<green
<span style="color: #00C000; background-color: #F0F0F0">
green;

$blue =  <<<blue
<span style="color: #0000FF; background-color: #F0F0F0">
blue;


## MySQL Settings
$MYSQL_HOST = '127.0.0.1';
$MYSQL_DATABASE = 'temperature';
$MYSQL_TABLE_TEMP = 'temp';
$MYSQL_USER = 'temperature';
$MYSQL_PASSWORD = 'temperature';

## base interval in minutes with which data will be fed into the database
$INTERVAL = 1;

## Sensor Settings
$SENSOR[0] = 'Sensor #0:';
$SENSOR[1] = 'Sensor #1:';
# $SENSOR[2] = 'Sensor #2:';
#$SENSOR[3] = 'Sensor #3:';
#$SENSOR[4] = 'Sensor #4:';
#$SENSOR[5] = 'Sensor #5:';
#$SENSOR[6] = 'Sensor #6:';
#$SENSOR[7] = 'Sensor #7:';

## No more configuration parameters

# connect to mysqli
$link = mysqli_connect($MYSQL_HOST, $MYSQL_USER, $MYSQL_PASSWORD, $MYSQL_DATABASE);

/* check connection */
if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
        exit();
}

# # use mysql database
# $db = mysql_select_db($MYSQL_DATABASE) or die (mysql_error());

# # use mysqli database
# $db = mysqli_select_db($mysqli_connect, $MYSQL_DATABASE) or die (mysqli_error($mysqli_connect));

# mysql statement to get last inserted values of each sensor
$sql1 = "SELECT sensor AS sensor_id, value
          FROM temp 
         WHERE created > DATE_SUB(NOW(), INTERVAL " .($INTERVAL+1). " MINUTE)
	 AND value != 85.0
      ORDER BY created DESC
         LIMIT " .count($SENSOR). "";

# mysql statement to get hottest day of year
 $sql2 = "SELECT sensor AS sensor_id, value
          FROM temp
 	 where YEAR(created) = YEAR(NOW())
 	 AND value != 85.0
	 AND value=(SELECT MAX(value))
	 ORDER BY value DESC
	 LIMIT 1";
	
		
# mysql statement to get coldest day of year
 $sql3 = "SELECT sensor AS sensor_id, value
          FROM temp
	  where YEAR(created) = YEAR(NOW())
	  AND value != 85.0
	  AND value=(SELECT MAX(value))
          ORDER BY value ASC
          LIMIT 1";

# mysql statement to get hottest day temperature
 $sql4 = "SELECT sensor AS sensor_id, value
           FROM temp
	   where DATE(created) = DATE(NOW())
	   AND value != 85.0
	   ORDER BY value DESC
	   LIMIT 1";

# mysql statement to get coldest day temperature
 $sql5 = "SELECT sensor AS sensor_id, value
           FROM temp
	   where DATE(created) = DATE(NOW())
	   AND value != 85.0
	   ORDER BY value ASC
	   LIMIT 1";

# mysql statement arithmetisches Mittel des Tages
 $sql6 = "SELECT  sensor AS sensor_id, format(AVG(value),2)
           FROM temp
	   where DATE(created) = DATE(NOW())
	   AND value != 85.0";

# mysql statement arithmetisches Mittel des Jahres
 $sql7 = "SELECT  sensor AS sensor_id, format(AVG(value),2)
           FROM temp
	   where YEAR(created) = YEAR(NOW())
	   AND value != 85.0";


# select values
$result1 = mysqli_query($link, $sql1 );
$result2 = mysqli_query($link, $sql2 );
$result3 = mysqli_query($link, $sql3 );
$result4 = mysqli_query($link, $sql4 );
$result5 = mysqli_query($link, $sql5 );
$result6 = mysqli_query($link, $sql6 );
$result7 = mysqli_query($link, $sql7 );


# error if no result
if(mysqli_num_rows($result1) <= 0)
{
  echo "<b>No temperature data available</b>";
  exit;
}

# error if no result
if(mysqli_num_rows($result2) <= 0)
{
  echo "<b>No temperature data available</b>";
    exit;
}

# error if no result
if(mysqli_num_rows($result3) <= 0)
{
  echo "<b>No temperature data available</b>";
    exit;
}

# error if no result
if(mysqli_num_rows($result4) <= 0)
{
  echo "<b>No temperature data available</b>";
    exit;
}

# error if no result
if(mysqli_num_rows($result5) <= 0)
{
  echo "<b>No temperature data available</b>";
    exit;
}

# error if no result
if(mysqli_num_rows($result6) <= 0)
{
  echo "<b>No temperature data available</b>";
    exit;
}

# error if no result
if(mysqli_num_rows($result7) <= 0)
{
  echo "<b>No temperature data available</b>";
    exit;
}


# now save all values as object
for($i=0; $i<mysqli_num_rows($result1); $i++)
{
  $rows1[$i] = mysqli_fetch_object($result1);
}

# now save all values as object
for($i=0; $i<mysqli_num_rows($result2); $i++)
{
  $rows2[$i] = mysqli_fetch_object($result2);
}

# now save all values as object
for($i=0; $i<mysqli_num_rows($result3); $i++)
{
  $rows3[$i] = mysqli_fetch_object($result3);
}

# now save all values as object
for($i=0; $i<mysqli_num_rows($result4); $i++)
{
  $rows4[$i] = mysqli_fetch_object($result4);
}

# now save all values as object
for($i=0; $i<mysqli_num_rows($result5); $i++)
{
  $rows5[$i] = mysqli_fetch_object($result5);
}

# now save all values as object
for($i=0; $i<mysqli_num_rows($result6); $i++)
{
  $rows6[$i] = mysqli_fetch_object($result6);
}

# now save all values as object
for($i=0; $i<mysqli_num_rows($result7); $i++)
{
  $rows7[$i] = mysqli_fetch_object($result7);
}

# finally, output a simple html table with label and temperature data of each sensor

echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\"
          \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\n";
echo "<html xmlns=\"http://www.w3.org/1999/xhtml\">\n";
echo "  <head>\n";
echo "    <meta charset=\"UTF-8\">\n";
echo "    <title>Temperatur</title>\n";
echo " <meta http-equiv=\"refresh\" content=\"60\">\n";
echo "    <style type=\"text/css\">#temp_now
                                   {
                                     font-family:Verdana,sans-serif;
                                     font-size:11px;
                                     background-color:#F0F0F0;
                                     border:1px solid black;
                                   }
                                   #temp_now th
                                   {
                                     padding:3px;
                                     border-bottom:1px solid black;
                                     background-color:#203D8B;
                                     text-align:left;
                                   }
                                   #temp_now td
                                   {
                                     padding:3px;
                                     background-color:#F0F0F0;
                                     text-align:right;
                                   }
           </style>\n";
echo "  </head>\n";
echo "  <body>\n";

// Tabelle 1
echo "    <table align=\"left\"  id=\"temp_now\" cellpadding=\"0\" cellspacing=\"0\">\n";
echo "      <tr>\n";
echo "        <th align=\"left\" colspan=\"2\">$white jetzige Temperatur</th>\n";
echo "      </tr>\n";

foreach($rows1 as $row1)
 {
echo "  <tr>\n";
 echo " <td align=\"left\" >" .$SENSOR[$row1->sensor_id]. "</td>\n";
 echo " <td align=\"left\"style=\"text-align:right\" >$red" .$row1->value. "</span>&deg;</td>\n";
 echo "      </tr>\n";
 }

echo "      <tr>\n";
echo "        <th align=\"center\" colspan=\"2\">$white he&szlig;ieste Tages Temperatur </th>\n";
echo "      </tr>\n";

foreach($rows4 as $row4)
{
  echo "      <tr>\n";
  echo "        <td >" .$SENSOR[$row4->sensor_id]. "</td>\n";
  echo "        <td style=\"text-align:right\" >$red" .$row4->value. "</span>&deg;</td>\n";
  echo "      </tr>\n";
}

echo "      <tr>\n";
echo "        <th align=\"center\" colspan=\"2\">$white k&auml;lteste Tages Temperatur</th>\n";
echo "      </tr>\n";

foreach($rows5 as $row5)
{
  echo "      <tr>\n";
  echo "        <td>" .$SENSOR[$row5->sensor_id]. "</td>\n";
  echo "        <td style=\"text-align:right\">$blue" .$row5->value. "</span>&deg;</td>\n";
  echo "      </tr>\n";
}

echo "      <tr>\n";
echo "        <th align=\"center\" colspan=\"2\">$white arithmetisches Mittel des Tages </th>\n";
echo "      </tr>\n";

foreach($rows6 as $row6)
{
  echo "      <tr>\n";
  echo "        <td style=\"text-align:right\">$green" .$row6->{'format(AVG(value),2)'}. "</span>&deg;</td>\n";
  echo "      </tr>\n";
}

// Zeitausgabe
echo "      <tr>\n";
echo "        <th colspan=\"2\">$white  jetzige Uhrzeit:</th>\n";
echo "      </tr>\n";
echo "      <tr>\n";
echo "		<td style=\"text-align:rigth\">" .strftime("%Rh %Ss"). "</td>\n";
echo "      </tr>\n";

echo "    </table>\n";


// Tabelle 2
 echo "    <table align=\"left\" id=\"temp_now\" cellpadding=\"0\" cellspacing=\"0\">\n";
echo "      <tr>\n";
echo "        <th align=\"left\" colspan=\"2\">$white Temperatur Info Tool </span></th>\n";
echo "      </tr>\n";

foreach($rows1 as $row1)
 {
  echo "  <tr>\n";
  echo " <td align=\"left\" >&nbsp;</td>\n";
  echo " <td align=\"left\"style=\"text-align:right\" ></td>\n";
  echo "      </tr>\n";
 }

 echo "      <tr>\n";
 echo "        <th align=\"center\" colspan=\"2\">$white hei&szlig;este Jahres Temperatur</th>\n";
 echo "      </tr>\n";



foreach($rows2 as $row2)
{
 echo "      <tr>\n";
 echo "        <td align=\"right\">" .$SENSOR[$row2->sensor_id]. "</td>\n";
 echo "        <td align=\"right\" style=\"text-align:right\">$red" .$row2->value. "</span>&deg;</td>\n";
 echo "      </tr>\n";
}
echo "      <tr>\n";
echo "        <th align=\"center\" colspan=\"2\">$white k&auml;lteste Jahres Temperatur </th>\n";
echo "      </tr>\n";

foreach($rows3 as $row3)
{
  echo "      <tr>\n";
  echo "        <td>" .$SENSOR[$row3->sensor_id]. "</td>\n";
  echo "        <td style=\"text-align:right\">$blue" .$row3->value. "</span>&deg;</td>\n";
  echo "      </tr>\n";
}

echo "      <tr>\n";
echo "        <th  align=\"center\" colspan=\"2\">$white arithmetisches Mittel des Jahres </th>\n";
echo "      </tr>\n";

foreach($rows7 as $row7)
  {
  echo "      <tr>\n";
  echo "        <td style=\"text-align:rigth\">$green" .$row7->{'format(AVG(value),2)'}. "</span>&deg;</td>\n";
  echo "      </tr>\n";
  }

// Zeitausgabe
echo "      <tr>\n";
echo "        <th colspan=\"2\">$white Heutiges Datum:</th>\n";
echo "      </tr>\n";
echo "      <tr>\n";
echo " 		<td style=\"text-align:rigth\">" .strftime("%d %B %Y"). "</td>\n";
echo "      </tr>\n";

echo "    </table>\n";

echo" </body>\n";
echo" </html>\n";

exit;

?>
