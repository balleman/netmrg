SUFFIXES = .rrd .xml
RRDS = zero.rrd

all : $(RRDS)

.xml.rrd :
	$(RRDTOOL) restore $*.xml $@

# options to install files in the var/lib directory
netmrg_var_libdir=$(localstatedir)/lib/$(PACKAGE)/rrd
netmrg_var_lib_DATA = $(RRDS)

