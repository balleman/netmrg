CC 	= g++
CFLAGS 	= -Wall -g -pthread
LDFLAGS	= -lmysqlclient -lsnmp -lcrypto -lpthread -lz -ldl -lstdc++ 

all : clean netmrg

netmrg :
		$(OBJS)
		$(CC) $(CFLAGS) -c utils.cpp
		$(CC) $(CFLAGS) -c locks.cpp
		$(CC) $(CFLAGS) -c snmp.cpp
		$(CC) $(CFLAGS) -c db.cpp -I/usr/include/mysql -I./
		$(CC) $(CFLAGS) -c netmrg.cpp -I/usr/include/mysql -I./
		$(CC) $(CFLAGS) utils.o locks.o snmp.o db.o netmrg.o -o netmrg -L/usr/lib/mysql $(OBJS) $(LDFLAGS)


clean :
		rm -f netmrg
