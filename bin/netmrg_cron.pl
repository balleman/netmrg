#!/usr/bin/perl
system("/var/www/netmrg/bin/netmrg");
if (-f "/var/www/netmrg/dat/lockfile") 
	{
		#@out = <"/var/www/netmrg/dat/output">
		#@err = <"/var/www/netmrg/dat/error">
		system("echo 'It appears that NetMRG died improperly on its last run.  I will remove the lockfile.' | mail -s 'NetMRG Died' brady\@pa.net");
		system("cp -f /var/www/netmrg/dat/output /var/www/netmrg/dat/output.died");
		unlink("/var/www/netmrg/dat/lockfile");
	}
		
