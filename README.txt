This is a MySQL PHP PERL solution for getting data from digitemp or other sensors to a MySQL
Database with a script and show all with the status.php .

This is a tool to dump temperature data to a mysql database.
The initial script was original from Moritz Fuchs <fr1tz@gmx.de> under GPL2.

I added some features for mail and multiple sensor accebilities.


For gentoo some things must be installed to be able to work:


echo "app-misc/digitemp ds9097" >> /etc/portage/package.use

emerge app-misc/digitemp

emerge dev-perl/DBD-mysql dev-perl/DateManip
# perl-core/Time-Local is now in perl included 



The Database should contain the entries from temp1.sql


Things to copy:
temp2mysql.pl to /root/bin/ and digitemprc to /root/.digitemprc
 or 
vcgencmd-db to /root/bin/


Change the email addresses in temp2mysql.pl:
user1\@domain.org,user2\@domain.org


Create a cron Job for every minute check.

*/1 * * * *     root    /root/bin/temp2mysql.pl

/etc/init.d/vixie-cron restart


To get the sensor ID:
digitemp -i -c /etc/digitemp.conf -s /dev/ttyS0

Copy the entry to the .digitemprc

Wrong Temperature:
digitemp -a = only 85 ° (this is the testmode)
digitemp -a -r750  = real ° (uses an other waittime)

ToDO:
graphical output with gd.

Send all 60 minutes a SMS with sms-gateway if Temperature is over 33°C.
