# WordPressEnvironment

To spin a local copy of the WordPress website:
Install WampServer:- https://www.wampserver.com/en/
After successful installation, navigate to the drive WampServer is installed.
Navigate to directory "wamp64/www".
Create a new directory naming it to preference.
Navigate into the directory and clone this repository inside.
This can be done via an IDE using “git clone https://github.com/WordPressDeployment/WordPressEnvironment.git” within directory.
Another requirement is the website’s SQL file. 
Clone the SQL file from the repo “https://github.com/WordPressDeployment/WordPress-SQL-file.git” onto any directory that is easily accessible from your system.
Left click on the WampServer widget and access the phpMyAdmin interface.
The localhost phpMyAdmin will open on the browser. (default username “root” and no password)
On the databases tab, create new database utf8. Name it to preference.
On the newly created database, navigate to the import tab and select the previously cloned SQL file.
Upload SQL file using upload interface and save changes.
Navigate to the WordPress local installation at "wamp64/www/[chosen file name]” and edit the wp-config.php file using an IDE.
define( 'DB_NAME', 'database_name_here' );
define( 'DB_USER', 'username_here' );
define( 'DB_PASSWORD', 'password_here' );
define( 'DB_HOST', 'localhost' );
Edit the wp-config file to reflect the actions when importing the SQL file. DB_NAME must match the name chosen for the imported database and username should be set to “root” and password should be empty.
To access WordPress, left click the WampServer Widget and select “localhost”.
From there, a WampServer interface will open in the browser.
Edit the URL such that localhost/ [name of the directory WordPress files is installed] to access website.
