<?php
defined( 'ABSPATH' ) or die( 'forbidden' );

# Set this WordPress as Master or as a Slave 
# false = master (The master WP where the user will have to login or should result to be logged)
# true = slave (The slave WP where the user request to login)

define('THIS_WPW3NET_IS_SLAVE', false);

// --> SITE 0 // Set the db connection data to the slave
define('WPW3NET_0_URL', 'https://virtual.subdomain.localhost/wordpress_virtual-subdomain');
define('WPW3NET_0_DB_NAME', 'wordpress_virtual-subdomain');
define('WPW3NET_0_DB_USER', 'root');
define('WPW3NET_0_DB_PASSWORD', 'pass');
define('WPW3NET_0_DB_HOST', 'localhost');
#define('WPW3NET_0_DB_PORT', '3306');
define('WPW3NET_0_DB_TABPREFIX', 'wp_');

#define('WPW3NET_0_DB_CHARSET', 'utf8');
#define('WPW3NET_0_DB_COLLATE', '');
