@SET_MAKE@

SUBDIRS	= @subdirs@ www

# options to install files in the libexec directory
netmrg_libexecdir=$(libexecdir)/$(PACKAGE)
netmrg_libexec_SCRIPTS = libexec/*.php libexec/*.pl

# options to install files in the bin directory
bin_SCRIPTS = bin/*.pl bin/*.sh

# options to install files in the etc directory
netmrg_etcdir=$(sysconfdir)
netmrg_etc_SCRIPTS = etc/*.conf

# options to install files in the share/images directory
netmrg_share_imagesdir=$(pkgdatadir)/images
netmrg_share_images_SCRIPTS = share/images/default/*.svg

# options to install files in the share/db directory
netmrg_share_dbdir=$(pkgdatadir)/db
netmrg_share_db_SCRIPTS = 

# options to install files in the share/doc directory
netmrg_share_docdir=$(datadir)/doc/$(PACKAGE)
netmrg_share_doc_SCRIPTS = share/doc/*.sgml

# options to install files in the var/lib directory
netmrg_var_libdir=$(localstatedir)/lib/$(PACKAGE)/rrd
netmrg_var_lib_SCRIPTS = var/lib/rrd/*.rrd

# options to install files in the var/log directory
netmrg_var_logdir=$(localstatedir)/log/$(PACKAGE)
netmrg_var_log_SCRIPTS = 
