@SET_MAKE@

include $(top_srcdir)/Make.inc

# options to install files in the etc directory
netmrg_etcdir=$(sysconfdir)
netmrg_etc_DATA = netmrg.xml netmrg.conf

# override the default netmrg_etc install
install-netmrg_etcDATA: $(netmrg_etc_DATA)
	@$(NORMAL_INSTALL)
	$(mkinstalldirs) $(DESTDIR)$(netmrg_etcdir)
	@list='$(netmrg_etc_DATA)'; for p in $$list; do \
          if test -f "$$p"; then d=; else d="$(srcdir)/"; fi; \
          f="`echo $$p | sed -e 's|^.*/||'`"; \
	  if test -e "$(DESTDIR)$(netmrg_etcdir)/$$f"; then \
	    echo " Config file $$p exists...not overwriting."; \
	  else \
            echo "  INST   $$d$$p"; \
            $(netmrg_etcDATA_INSTALL) $$d$$p $(DESTDIR)$(netmrg_etcdir)/$$f; \
	  fi; \
        done

