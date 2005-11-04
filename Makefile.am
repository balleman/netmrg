@SET_MAKE@

include $(top_srcdir)/Make.inc

SUBDIRS	= m4 po src www etc var .

ACLOCAL_AMFLAGS = -I m4

EXTRA_DIST = config.rpath ABOUT-NLS

install : all install-recursive

# options to install files in the libexec directory
netmrg_libexecdir=$(libexecdir)/$(PACKAGE)
netmrg_libexec_SCRIPTS = libexec/*.php libexec/*.pl

# options to install files in the bin directory
bin_SCRIPTS = bin/rrdedit

# options to install files in the share/images directory
netmrg_share_imagesdir=$(pkgdatadir)/images
netmrg_share_images_DATA = share/images/default/*.svg

# options to install files in the share/db directory
netmrg_share_dbdir=$(pkgdatadir)/db
netmrg_share_db_DATA = share/netmrg.mysql

# options to install files in the share/contrib directory
netmrg_share_bindstatsdir=$(pkgdatadir)/contrib/bindstats
netmrg_share_bindstats_DATA = contrib/bindstats/dnsstats*

# options to install files in the share/doc directory
netmrg_share_docdir=$(datadir)/doc/$(PACKAGE)-$(VERSION)
netmrg_share_doc_DATA = INSTALL README share/doc/*.sgml share/doc/ChangeLog share/doc/LICENSE share/doc/TODO share/doc/txt/netmrg.txt share/doc/txt/INSTALL share/doc/txt/RELEASE-NOTES share/doc/txt/UPGRADE etc/cron.d-netmrg etc/init.d-netmrg

# html documentation
netmrg_share_htmldir=$(datadir)/doc/$(PACKAGE)-$(VERSION)/html
netmrg_share_html_DATA = share/doc/html/*.html

# man1 pages
netmrg_man1dir=$(mandir)/man1
netmrg_man1_DATA = share/doc/man/man1/*.1

# options to install files in the var/log directory
netmrg_var_logdir=$(localstatedir)/log/$(PACKAGE)
netmrg_var_log_DATA = 


distclean: distclean-recursive
	@echo "  RM     $(am__CONFIG_DISTCLEAN_FILES)"
	@rm -f $(am__CONFIG_DISTCLEAN_FILES)
	@echo "  RM     Makefile"
	@rm -f Makefile

install-netmrg_libexecSCRIPTS: $(netmrg_libexec_SCRIPTS)
	@$(NORMAL_INSTALL)
	test -z "$(netmrg_libexecdir)" || $(mkdir_p) "$(DESTDIR)$(netmrg_libexecdir)"
	@echo "  INST   $(netmrg_libexec_SCRIPTS)"
	@list='$(netmrg_libexec_SCRIPTS)'; for p in $$list; do \
	  if test -f "$$p"; then d=; else d="$(srcdir)/"; fi; \
	  if test -f $$d$$p; then \
	    f=`echo "$$p" | sed 's|^.*/||;$(transform)'`; \
	    $(netmrg_libexecSCRIPT_INSTALL) "$$d$$p" "$(DESTDIR)$(netmrg_libexecdir)/$$f"; \
	  else :; fi; \
	done

install-binSCRIPTS: $(bin_SCRIPTS)
	@$(NORMAL_INSTALL)
	test -z "$(bindir)" || $(mkdir_p) "$(DESTDIR)$(bindir)"
	@echo "  INST   $(bin_SCRIPTS)"
	@list='$(bin_SCRIPTS)'; for p in $$list; do \
	  if test -f "$$p"; then d=; else d="$(srcdir)/"; fi; \
	  if test -f $$d$$p; then \
	    f=`echo "$$p" | sed 's|^.*/||;$(transform)'`; \
	    $(binSCRIPT_INSTALL) "$$d$$p" "$(DESTDIR)$(bindir)/$$f"; \
	  else :; fi; \
	done

install-netmrg_share_imagesDATA: $(netmrg_share_images_DATA)
	@$(NORMAL_INSTALL)
	test -z "$(netmrg_share_imagesdir)" || $(mkdir_p) "$(DESTDIR)$(netmrg_share_imagesdir)"
	@echo "  INST   $(netmrg_share_images_DATA)"
	@list='$(netmrg_share_images_DATA)'; for p in $$list; do \
	  if test -f "$$p"; then d=; else d="$(srcdir)/"; fi; \
	  f="`echo $$p | sed -e 's|^.*/||'`"; \
	  $(netmrg_share_imagesDATA_INSTALL) "$$d$$p" "$(DESTDIR)$(netmrg_share_imagesdir)/$$f"; \
	done

install-netmrg_share_dbDATA: $(netmrg_share_db_DATA)
	@$(NORMAL_INSTALL)
	test -z "$(netmrg_share_dbdir)" || $(mkdir_p) "$(DESTDIR)$(netmrg_share_dbdir)"
	@echo "  INST   $(netmrg_share_db_DATA)"
	@list='$(netmrg_share_db_DATA)'; for p in $$list; do \
	  if test -f "$$p"; then d=; else d="$(srcdir)/"; fi; \
	  f="`echo $$p | sed -e 's|^.*/||'`"; \
	  $(netmrg_share_dbDATA_INSTALL) "$$d$$p" "$(DESTDIR)$(netmrg_share_dbdir)/$$f"; \
	done

install-netmrg_share_bindstatsDATA: $(netmrg_share_bindstats_DATA)
	@$(NORMAL_INSTALL)
	test -z "$(netmrg_share_bindstatsdir)" || $(mkdir_p) "$(DESTDIR)$(netmrg_share_bindstatsdir)"
	@echo "  INST   $(netmrg_share_bindstats_DATA)"
	@list='$(netmrg_share_bindstats_DATA)'; for p in $$list; do \
	  if test -f "$$p"; then d=; else d="$(srcdir)/"; fi; \
	  f="`echo $$p | sed -e 's|^.*/||'`"; \
	  $(netmrg_share_bindstatsDATA_INSTALL) "$$d$$p" "$(DESTDIR)$(netmrg_share_bindstatsdir)/$$f"; \
	done

install-netmrg_share_docDATA: $(netmrg_share_doc_DATA)
	@$(NORMAL_INSTALL)
	test -z "$(netmrg_share_docdir)" || $(mkdir_p) "$(DESTDIR)$(netmrg_share_docdir)"
	@echo "  INST   $(netmrg_share_doc_DATA)"
	@list='$(netmrg_share_doc_DATA)'; for p in $$list; do \
	  if test -f "$$p"; then d=; else d="$(srcdir)/"; fi; \
	  f="`echo $$p | sed -e 's|^.*/||'`"; \
	  $(netmrg_share_docDATA_INSTALL) "$$d$$p" "$(DESTDIR)$(netmrg_share_docdir)/$$f"; \
	done

install-netmrg_share_htmlDATA: $(netmrg_share_html_DATA)
	@$(NORMAL_INSTALL)
	test -z "$(netmrg_share_htmldir)" || $(mkdir_p) "$(DESTDIR)$(netmrg_share_htmldir)"
	@echo "  INST   $(netmrg_share_html_DATA)"
	@list='$(netmrg_share_html_DATA)'; for p in $$list; do \
	  if test -f "$$p"; then d=; else d="$(srcdir)/"; fi; \
	  f="`echo $$p | sed -e 's|^.*/||'`"; \
	  $(netmrg_share_htmlDATA_INSTALL) "$$d$$p" "$(DESTDIR)$(netmrg_share_htmldir)/$$f"; \
	done

install-netmrg_man1DATA: $(netmrg_man1_DATA)
	@$(NORMAL_INSTALL)
	test -z "$(netmrg_man1dir)" || $(mkdir_p) "$(DESTDIR)$(netmrg_man1dir)"
	@echo "  INST   $(netmrg_man1_DATA)"
	@list='$(netmrg_man1_DATA)'; for p in $$list; do \
	  if test -f "$$p"; then d=; else d="$(srcdir)/"; fi; \
	  f="`echo $$p | sed -e 's|^.*/||'`"; \
	  $(netmrg_man1DATA_INSTALL) "$$d$$p" "$(DESTDIR)$(netmrg_man1dir)/$$f"; \
	done

