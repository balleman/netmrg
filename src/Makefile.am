@SET_MAKE@

include $(top_srcdir)/Make.inc

SUBDIRS = .

netmrg_gatherer_SOURCES = db.cpp devices.cpp events.cpp locks.cpp mappings.cpp monitors.cpp netmrg.cpp rrd.cpp settings.cpp snmp.cpp utils.cpp

noinst_HEADERS = include/common.h include/db.h include/devices.h include/events.h include/locks.h include/mappings.h include/monitors.h include/netmrg.h include/rrd.h include/settings.h include/snmp.h include/types.h include/utils.h

bin_PROGRAMS = netmrg-gatherer

INCLUDES = -I./include


.cpp.o:
	@echo "  CXX    $@"
@am__fastdepCXX_TRUE@	if $(CXXCOMPILE) -MT $@ -MD -MP -MF "$(DEPDIR)/$*.Tpo" -c -o $@ $<; \
@am__fastdepCXX_TRUE@	then mv -f "$(DEPDIR)/$*.Tpo" "$(DEPDIR)/$*.Po"; else rm -f "$(DEPDIR)/$*.Tpo"; exit 1; fi
@AMDEP_TRUE@@am__fastdepCXX_FALSE@	source='$<' object='$@' libtool=no @AMDEPBACKSLASH@
@AMDEP_TRUE@@am__fastdepCXX_FALSE@	depfile='$(DEPDIR)/$*.Po' tmpdepfile='$(DEPDIR)/$*.TPo' @AMDEPBACKSLASH@
@AMDEP_TRUE@@am__fastdepCXX_FALSE@	$(CXXDEPMODE) $(depcomp) @AMDEPBACKSLASH@
@am__fastdepCXX_FALSE@	$(CXXCOMPILE) -c -o $@ $<

netmrg-gatherer$(EXEEXT): $(netmrg_gatherer_OBJECTS) $(netmrg_gatherer_DEPENDENCIES) 
	@echo "  LINK   $@"
	@rm -f netmrg-gatherer$(EXEEXT)
	$(CXXLINK) $(netmrg_gatherer_LDFLAGS) $(netmrg_gatherer_OBJECTS) $(netmrg_gatherer_LDADD) $(LIBS)

mostlyclean-compile:
	@echo "  RM     *.$(OBJEXT)"
	-rm -f *.$(OBJEXT)

clean-binPROGRAMS:
	-test -z "$(bin_PROGRAMS)" || ( \
	echo "  RM     $(bin_PROGRAMS)"; \
	rm -f $(bin_PROGRAMS) )

install-binPROGRAMS: $(bin_PROGRAMS)
	@$(NORMAL_INSTALL)
	test -z "$(bindir)" || $(mkdir_p) "$(DESTDIR)$(bindir)"
	@echo "  INST   $(bin_PROGRAMS)"
	@list='$(bin_PROGRAMS)'; for p in $$list; do \
	  p1=`echo $$p|sed 's/$(EXEEXT)$$//'`; \
	  if test -f $$p \
	  ; then \
	    f=`echo "$$p1" | sed 's,^.*/,,;$(transform);s/$$/$(EXEEXT)/'`; \
	   $(INSTALL_PROGRAM_ENV) $(binPROGRAMS_INSTALL) "$$p" "$(DESTDIR)$(bindir)/$$f" || exit 1; \
	  else :; fi; \
	done

