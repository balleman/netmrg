CXXFLAGS= -Wall -g -pthread -I/usr/include/mysql -I./
LDFLAGS	= -lmysqlclient -lsnmp -lcrypto -lpthread -lz -ldl -lstdc++ -L/usr/lib/mysql
OBJS	= utils.o locks.o snmp.o db.o rrd.o netmrg.o

.PHONY: all

netmrg : $(OBJS)
	$(CXX) $(CXXFLAGS) $(OBJS) -o $@ $(LDFLAGS)

clean :
	$(RM) netmrg *.o
