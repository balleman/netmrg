CC 	= g++
CFLAGS 	= -g -DHAVE_LIBPTHREAD=1 -DHAVE_LIBCRYPTO=1 -DHAVE_LIBSNMP=1 -DHAVE_LIBMYSQLCLIENT=1 -DHAVE_STRINGS_H=1 -DHAVE_SYS_TIME_H=1 -DHAVE_UNISTD_H=1 
LDFLAGS	= -lmysqlclient -lsnmp -lcrypto -lpthread 

all : clean netmrg 

netmrg : 
		$(OBJS)
		$(CC) $(CFLAGS) netmrg.cc -o netmrg -L/usr/lib/mysql -I/usr/include/mysql -I./ -I/usr/include/ucd-snmp $(OBJS) $(LDFLAGS)

clean :
		rm -f netmrg
