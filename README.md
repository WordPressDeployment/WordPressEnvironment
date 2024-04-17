# WordPressEnvironment

To spin a local copy of the WordPress website: </br>
Install WampServer:- https://www.wampserver.com/en/ </br>
After successful installation, navigate to the drive WampServer is installed. </br>
Navigate to directory "wamp64/www". </br>
Create a new directory naming it to preference. </br>
Navigate into the directory and clone this repository inside. </br>
This can be done via an IDE using “git clone https://github.com/WordPressDeployment/WordPressEnvironment.git” within directory. </br>
Another requirement is the website’s SQL file.  </br>
Clone the SQL file from the repo “https://github.com/WordPressDeployment/WordPress-SQL-file.git” onto any directory that is easily accessible from your system. </br>
Left click on the WampServer widget and access the phpMyAdmin interface. </br>
The localhost phpMyAdmin will open on the browser. (default username “root” and no password) </br>
On the databases tab, create new database utf8. Name it to preference. </br>
On the newly created database, navigate to the import tab and select the previously cloned SQL file. </br>
Upload SQL file using upload interface and save changes. </br>
Navigate to the WordPress local installation at "wamp64/www/[chosen file name]” and edit the wp-config.php file using an IDE. </br>
define( 'DB_NAME', 'database_name_here' ); </br>
define( 'DB_USER', 'username_here' ); </br>
define( 'DB_PASSWORD', 'password_here' );  </br>
define( 'DB_HOST', 'localhost' ); </br>
Edit the wp-config file to reflect the actions when importing the SQL file. DB_NAME must match the name chosen for the imported database and username should be set to “root” and password should be empty. </br>
To access WordPress, left click the WampServer Widget and select “localhost”. </br>
From there, a WampServer interface will open in the browser. </br>
Edit the URL such that localhost/ [name of the directory WordPress files is installed] to access website. </br>
