#!/usr/bin/perl


########################################################################################
## temperature 2 mysql alam script
## by Joerg Neikes
## v 0.5
########################################################################################

####
# ToD:  gnokii connection  for alarm
####

####
# ToDo: make $tempmax for single sensors 
####

# needs to be installeed on gentoo: emerge  dev-perl/DBI dev-perl/DBD-mysql dev-perl/DateManip

use DBI;
use DBD::mysql;
# install MySQL Module
# perl -MCPAN -e shell
# install DBI
# install DBD::mysql
use Date::Manip;
use Time::Local;

# # MySQL config

# Login
$host = "localhost";
$mysql_database = "temperature";
$mysql_table_temp = "temp";
$mysql_table_alarm = "alarm";
$mysql_user = "temperature";
$mysq_password = "temperature";
# DATA SOURCE NAME
$dsn = "dbi:mysql:$mysql_database:localhost:3306";
# PERL DBI CONNECT
my $connect = DBI->connect($dsn, $mysql_user, $mysq_password);
# Time in minutes how long you want to keep your temperature data. One day = 1440
# Set 0 to keep data forever
$mysql_max_keep="0";
#$mysql__max_keep="2635200;" # 5 Years
# time after next message would be send in minutes
# Please keep both times in same range!
$mysql_alarm="60";
$mysql_epoch_alarm="300";
# Max temp before action is taken
$tempmax="70.00";

# # digitemp settings
# Serial port where sensors are connected
$dev_tty="/dev/ttyS0";
# Path to digitemp binary
$dt_bin="/usr/bin/digitemp";
# Path to configuration file
$dt_config="/root/.digitemprc";

# # non digitemp settings
$ndt_bin="/root/bin/vcgencmd-db";


# # for mail settings have a look at the Alarm setting


# # digitemp entries
# my @tempresult = `$dt_bin -s$dev_tty $1 -a -q -c$dt_config -H" " -O" " -o"%s, %.2C, %Y-%m-%d %H:%M:%S"`;

# # result for non digitemp entries
my @tempresult = `$ndt_bin`;

my $count = @tempresult;
foreach $element(@tempresult) {
	$wert=$tempresult[$element];
$wert =~ /(.*), (.*), (.*)/;
$sensor = "$1";
$temperature = "$2";
$createtime = "$3";


# MySQL insert of digitemp results to $mysql_table_temp
$mysql_result = "INSERT INTO $mysql_table_temp (sensor, value, created) VALUES (\"$sensor\",\"$temperature\",\"$createtime\")";

my $mysql_result_handle = $connect->prepare($mysql_result);
  if (!$mysql_result_handle) {
     die "Error:" . $->errstr . "\n";
 }
# execute the insert
$mysql_result_handle->execute();


# Alarm System
# Entries are removed after 60 Minutes and the action is done again


# 85.00 is a test entry from the digtemp sensor
if ($temperature gt $tempmax and $temperature ne "85.00") {


# See if $mysql_table_alarm is empty make alarm
$alarm_check = "SELECT count(*) FROM $mysql_table_alarm";
my $alarm_check_handle = $connect->prepare($alarm_check);
  if (!$alarm_check_handle) {
            die "Error:" . $->errstr . "\n";
}
$alarm_check_handle->execute();
my ($total_rows) = $alarm_check_handle->fetchrow_array();

if ($total_rows eq 0) {

# # Sendig mails with mailx
my $to = "user1\@domain.org,user2\@domain.org";
# my $bcc = "bccuser\@domain.org";
# my $cc = "ccuser1\@domain.org,ccuser2\@domain.org";
# To escape in pipe use \"\" befor variable
my $subj = "Temperature alarm sensor $sensor";
$grade = chr(0x00b0);
$alarm_text = "Sensor $sensor has a temperature of $temperature $grade C at $createtime.\n\n         Maximum temperature should be $tempmax $grade C.\n" ;
#open(MAIL, "| /bin/mailx -s \"$subj\" \"$to\" -b \"$bcc\" ") || die "mailx failed: $!\n";
open(MAIL, "| /bin/mailx -s \"$subj\" \"$to\" ") || die "mailx failed: $!\n";
print MAIL $alarm_text ;
close MAIL;

}


# Insert new entry
$alarm_insert = "INSERT INTO $mysql_table_alarm (alarmno, sensor, date, valuemaxalarm, value) VALUES (NULL,\"$sensor\",\"$createtime\",\"$tempmax\", \"$temperature\")";
my $alarm_insert_handle = $connect->prepare($alarm_insert);
  if (!$alarm_insert_handle) {
          die "Error:" . $->errstr . "\n";
	   }
$alarm_insert_handle->execute();


# Get last alarm entry
$alarm_query = "SELECT * FROM $mysql_table_alarm WHERE date < DATE_SUB(NOW(), INTERVAL \"$mysql_alarm\" MINUTE) ";
my $alarm_query_handle = $connect->prepare($alarm_query);
  if (!$alarm_query_handle) {
      die "Error:" . $->errstr . "\n";
 }
$alarm_query_handle->execute();

# Bind table colums to variables
$alarm_query_handle->bind_columns(undef, \$alarmno, \$sensor, \$date, \$valuemaxalarm, \$value);


# Loop through results
while($alarm_query_handle->fetch()) {
    if ($valuemaxalarm ne "85.00") {
    print "Sensor $sensor was over $valuemaxalarm at $date\n";
    # calculate the time difference
    print $date;
    ($yyyy, $mm, $dd, $HH, $MM, $SS) = ($date =~ /(\d+)-(\d+)-(\d+)\ (\d+):(\d+):(\d+)/);
    # calculate epoch seconds from mysql entry
    # fixed bug in Time::Local which calculates from 1-12 and localtime from 0-11 in month.
    $time1 = timelocal($SS,$MM,$HH,$dd,$mm-1,$yyyy);
    $time2=timelocal(localtime);
    $timediff = ($time2 -$time1);
    if ($timediff gt $mysql_epoch_alarm)    {
    $secondsover = ($timediff - $mysql_epoch_alarm);
    # Take action and send SMS and/or mail.
    print "$secondsover\n";

    # Delete enties over 60 minutes
    $alarm_delete = "TRUNCATE TABLE $mysql_table_alarm";
    my $alarm_delete_handle = $connect->prepare($alarm_delete);
      if (!$alarm_delete_handle) {
            die "Error:" . $->errstr . "\n";
     }
    $alarm_delete_handle->execute();

    }
    }
  }

 }
}


# Delete old values from database 
if ($mysql_max_keep gt "0" ) {
$mysql_max_keep_result="DELETE FROM $mysql_table_temp WHERE created < DATE_SUB(NOW(), INTERVAL \"$mysql_max_keep\" MINUTE)";

my $mysql_max_keep_result_handle = $connect->prepare($mysql_max_keep_result);
  if (!$mysql_max_keep_result) {
     die "Error:" . $->errstr . "\n";
  }

# execute delete
$mysql_max_keep_result_handle->execute();
}
