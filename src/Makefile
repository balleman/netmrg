CC 	= g++
CFLAGS 	= -Wall -c -g 
LDFLAGS	= -lmysqlclient -lsnmp -lcrypto -lpthread

all : clean netmrg 

netmrg : 
		$(OBJS)
		$(CC) $(CFLAGS) utils.cpp
		$(CC) $(CFLAGS) locks.cpp
		$(CC) $(CFLAGS) snmp.cpp
		$(CC) $(CFLAGS) db.cpp -I/usr/include/mysql -I./
		$(CC) $(CFLAGS) netmrg.cc -I/usr/include/mysql -I./
		$(CC) utils.o locks.o snmp.o db.o netmrg.o -o netmrg -L/usr/lib/mysql $(OBJS) $(LDFLAGS)


clean :
		rm -f netmrg
