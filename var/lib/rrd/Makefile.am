SUFFIXES = .rrd .xml
RRDS = zero.rrd

# options to install files in the var/lib directory
netmrg_var_libdir=$(localstatedir)/lib/$(PACKAGE)/rrd
netmrg_var_lib_DATA = $(RRDS)

.xml.rrd :
	@echo "  RRD    $@"
	$(RRDTOOL) restore $*.xml $@

clean : clean-am clean-rrd

distclean-am: clean-am clean-rrd distclean-generic

clean-rrd :
	@echo "  RM     $(netmrg_var_lib_DATA)"
	rm -f $(netmrg_var_lib_DATA)

install-netmrg_var_libDATA: $(netmrg_var_lib_DATA)
	@$(NORMAL_INSTALL)
	test -z "$(netmrg_var_libdir)" || $(mkdir_p) "$(DESTDIR)$(netmrg_var_libdir)"
	@list='$(netmrg_var_lib_DATA)'; for p in $$list; do \
	  if test -f "$$p"; then d=; else d="$(srcdir)/"; fi; \
	  f="`echo $$p | sed -e 's|^.*/||'`"; \
	  echo "  INST   $$d$$p"; \
	  $(netmrg_var_libDATA_INSTALL) "$$d$$p" "$(DESTDIR)$(netmrg_var_libdir)/$$f"; \
	done

