<?

########################################################
#                                                      #
#           NetMRG Integrator                          #
#           Web Interface                              #
#                                                      #
#           Database Abstraction/Integration Module    #
#           database.php                               #
#                                                      #
#     Copyright (C) 2001-2002 Brady Alleman.           #
#     brady@pa.net - www.treehousetechnologies.com     #
#                                                      #
########################################################

### Configurable Variables

$database_host = "localhost";
$database_name = "netmrg";

$database_read_user = "netmrgread";
$database_read_password = "netmrgread";

$database_write_user = "netmrgwrite";
$database_write_password = "netmrgwrite";

function do_query($query_string)
{
        # Obtain data from a table

        global $database_host, $database_name, $database_read_user, $database_read_password;

        mysql_connect($database_host, $database_read_user, $database_read_password) or die('Cannot connect to the database server.');
        mysql_select_db($database_name) or die('Cannot connect to the database.');
        $query_handle = mysql_query($query_string);

        return $query_handle;

} # end do_query

function do_update($query_string)
{
        # Update data in table

        global $database_host, $database_name, $database_write_user, $database_write_password;

        mysql_connect($database_host, $database_write_user, $database_write_password) or die('Cannot connect to the database server.');
        mysql_select_db($database_name) or die('Cannot connect to the database.');
        $query_handle = mysql_query($query_string);

        return $query_handle;

} # end do_update


?>