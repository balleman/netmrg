@SET_MAKE@

# options to install files in the www directory
netmrg_wwwdir=$(localstatedir)/www/$(PACKAGE)
nobase_netmrg_www_DATA = ./*

# override the default netmrg_www install
install-nobase_netmrg_wwwDATA: $(nobase_netmrg_www_DATA)
	@$(NORMAL_INSTALL)
	$(mkinstalldirs) $(DESTDIR)$(netmrg_wwwdir)
	@list='$(nobase_netmrg_www_DATA)'; for p in `find $$list -name 'CVS' -prune -o -name '*\.in' -prune -o -name 'Makefile*' -prune -o -type f -print`; do \
	  if test -f "$$p"; then d=; else d="$(srcdir)/"; fi; \
	  if test -f $$d$$p; then \
	    f=`echo "$$p" | sed 's|^.*/||;$(transform)'`; \
	    f=`echo "$$p" | sed 's|[^/]*$$||'`"$$f"; \
	    echo " $(nobase_netmrg_wwwDATA_INSTALL) $$d$$p $(DESTDIR)$(netmrg_wwwdir)/$$f"; \
	    $(nobase_netmrg_wwwDATA_INSTALL) $$d$$p $(DESTDIR)$(netmrg_wwwdir)/$$f; \
	  else :; fi; \
	done

