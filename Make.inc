
# These settings are pulled into Makefile.in by automake

AM_MAKEFLAGS = \
	` test "$(VERBOSE)" = "1" && echo $(ECHO_N) "" \
	|| echo $(ECHO_N) "-s" `

