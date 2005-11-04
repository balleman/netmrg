@SET_MAKE@

include $(top_srcdir)/Make.inc

SUFFIXES = .rrd
RRDS = zero.rrd

# options to install files in the var/lib directory
netmrg_var_libdir=$(localstatedir)/lib/$(PACKAGE)/rrd
netmrg_var_lib_DATA = $(RRDS)

$(RRDS) :
	@echo "  RRD    $@"
	@$(RRDTOOL) create zero.rrd DS:mon_25:GAUGE:600:U:U RRA:AVERAGE:0:1:1 RRA:LAST:0:1:1 RRA:MAX:0:1:1

clean : clean-am clean-rrd

distclean-am: clean-am clean-rrd distclean-generic

clean-rrd :
	@echo "  RM     $(netmrg_var_lib_DATA)"
	@rm -f $(netmrg_var_lib_DATA)

install-netmrg_var_libDATA: $(netmrg_var_lib_DATA)
	@$(NORMAL_INSTALL)
	test -z "$(netmrg_var_libdir)" || $(mkdir_p) "$(DESTDIR)$(netmrg_var_libdir)"
	@list='$(netmrg_var_lib_DATA)'; for p in $$list; do \
	  if test -f "$$p"; then d=; else d="$(srcdir)/"; fi; \
	  f="`echo $$p | sed -e 's|^.*/||'`"; \
	  echo "  INST   $$d$$p"; \
	  $(netmrg_var_libDATA_INSTALL) "$$d$$p" "$(DESTDIR)$(netmrg_var_libdir)/$$f"; \
	done

