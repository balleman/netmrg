@SET_MAKE@

SUBDIRS	= @subdirs@ www etc

# options to install files in the libexec directory
netmrg_libexecdir=$(libexecdir)/$(PACKAGE)
netmrg_libexec_SCRIPTS = libexec/*.php libexec/*.pl

# options to install files in the bin directory
bin_SCRIPTS = bin/*.pl bin/*.sh bin/rrdedit

# options to install files in the share/images directory
netmrg_share_imagesdir=$(pkgdatadir)/images
netmrg_share_images_DATA = share/images/default/*.svg

# options to install files in the share/db directory
netmrg_share_dbdir=$(pkgdatadir)/db
netmrg_share_db_DATA = share/netmrg.mysql

# options to install files in the share/doc directory
netmrg_share_docdir=$(datadir)/doc/$(PACKAGE)-$(VERSION)
netmrg_share_doc_DATA = INSTALL README share/doc/*.sgml share/doc/ChangeLog share/doc/LICENSE share/doc/TODO share/doc/txt/netmrg.txt share/doc/txt/INSTALL share/doc/txt/RELEASE-NOTES share/doc/txt/UPGRADE

# html documentation
netmrg_share_htmldir=$(datadir)/doc/$(PACKAGE)-$(VERSION)/html
netmrg_share_html_DATA = share/doc/html/*.html

# man1 pages
netmrg_man1dir=$(mandir)/man1
netmrg_man1_DATA = share/doc/man/man1/*.1

# options to install files in the var/lib directory
netmrg_var_libdir=$(localstatedir)/lib/$(PACKAGE)/rrd
netmrg_var_lib_DATA = var/lib/rrd/*.rrd

# options to install files in the var/log directory
netmrg_var_logdir=$(localstatedir)/log/$(PACKAGE)
netmrg_var_log_DATA = 

