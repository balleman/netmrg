@SET_MAKE@

include $(top_srcdir)/Make.inc

# options to install files in the www directory
netmrg_wwwdir=@WWWDIR@
nobase_netmrg_www_DATA = include lib webfiles

# override the default netmrg_www install
install-nobase_netmrg_wwwDATA: $(nobase_netmrg_www_DATA)
	@$(NORMAL_INSTALL)
	$(mkinstalldirs) $(DESTDIR)$(netmrg_wwwdir)
	@echo "  INST   $(nobase_netmrg_www_DATA)"
	@list='$(nobase_netmrg_www_DATA)'; for p in `find $$list -name 'CVS' -prune -o -name '*\.in' -prune -o -name 'Makefile*' -prune -o -type f -print`; do \
	  if test -f "$$p"; then d=; else d="$(srcdir)/"; fi; \
	  if test -f $$d$$p; then \
	    f=`echo "$$p" | sed 's|^.*/||;$(transform)'`; \
	    f=`echo "$$p" | sed 's|[^/]*$$||'`"$$f"; \
	    $(nobase_netmrg_wwwDATA_INSTALL) $$d$$p $(DESTDIR)$(netmrg_wwwdir)/$$f; \
	  else :; fi; \
	done

