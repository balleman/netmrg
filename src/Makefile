CC 	= g++
CFLAGS 	= -g -Wall -DHAVE_LIBPTHREAD=1 -DHAVE_LIBCRYPTO=1 -DHAVE_LIBSNMP=1 -DHAVE_LIBMYSQLCLIENT=1 -DHAVE_STRINGS_H=1 -DHAVE_SYS_TIME_H=1 -DHAVE_UNISTD_H=1 
LDFLAGS	= -lmysqlclient -lsnmp -lcrypto -lpthread

all : clean netmrg 

netmrg : 
		$(OBJS)
		$(CC) -Wall -g -c utils.cpp
		$(CC) -Wall -g -c locks.cpp
		$(CC) -Wall -g -c snmp.cpp
		$(CC) -Wall -g -c db.cpp -I/usr/include/mysql -L/usr/lib/mysql -I./
		$(CC) $(CFLAGS) utils.o locks.o snmp.o db.o netmrg.cc -o netmrg -L/usr/lib/mysql -I/usr/include/mysql -I./ -I/usr/include/ucd-snmp $(OBJS) $(LDFLAGS)


clean :
		rm -f netmrg
