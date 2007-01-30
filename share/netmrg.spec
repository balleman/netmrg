%define _localdatadir %{_var}

Summary: Network Monitoring package using PHP, MySQL, and RRDtool
Name: netmrg
Version: 0.20
Release: 1
#Epoch: 1
License: GPLv2
Group: Application/System
Source0: netmrg-%{version}.tar.gz
#Source1: 
#Patch0: 
#Patch1: 
URL: http://www.netmrg.net
BuildRoot: %{_tmppath}/%{name}-root
Requires: php, php-mysql, mysql, webserver, rrdtool, libxml2
BuildRequires: mysql-devel, libxml2-devel
%{!?_with_ucd:Requires: net-snmp}
%{!?_with_ucd:BuildRequires: net-snmp-devel}
%{?_with_ucd:Requires: ucd-snmp}
%{?_with_ucd:BuildRequires: ucd-snmp-devel}


%description
NetMRG is a tool for network monitoring, reporting, and graphing. Based 
on RRDTOOL, the best of open source graphing systems, NetMRG is capable 
of creating graphs of any parameter of your network.

%prep
%define _wwwdir %{_localstatedir}/www/%{name}
%setup -q
%configure \
	--with-wwwdir=%{_wwwdir}

%build
make %{_smp_mflags}

%install
rm -rf %{buildroot}
make \
	prefix=%{?buildroot:%{buildroot}}%{_prefix} \
	exec_prefix=%{?buildroot:%{buildroot}}%{_exec_prefix} \
	bindir=%{?buildroot:%{buildroot}}%{_bindir} \
	sbindir=%{?buildroot:%{buildroot}}%{_sbindir} \
	sysconfdir=%{?buildroot:%{buildroot}}%{_sysconfdir} \
	datadir=%{?buildroot:%{buildroot}}%{_datadir} \
	includedir=%{?buildroot:%{buildroot}}%{_includedir} \
	libdir=%{?buildroot:%{buildroot}}%{_libdir} \
	libexecdir=%{?buildroot:%{buildroot}}%{_libexecdir} \
	localstatedir=%{?buildroot:%{buildroot}}%{_localstatedir} \
	sharedstatedir=%{?buildroot:%{buildroot}}%{_sharedstatedir} \
	mandir=%{?buildroot:%{buildroot}}%{_mandir} \
	infodir=%{?buildroot:%{buildroot}}%{_infodir} \
	netmrg_wwwdir=%{?buildroot:%{buildroot}}%{_wwwdir} \
	install

install -d %{buildroot}/%{_sysconfdir}/cron.d
install -m 644 etc/cron.d-netmrg %{buildroot}/%{_sysconfdir}/cron.d/netmrg
install -d %{buildroot}/%{_sysconfdir}/rc.d/init.d
install -m 755 etc/init.d-netmrg %{buildroot}/%{_sysconfdir}/rc.d/init.d/netmrg
rm -f %{buildroot}/${_datadir}/doc/%{name}-%{version}/cron.d-netmrg
rm -f %{buildroot}/${_datadir}/doc/%{name}-%{version}/init.d-netmrg

%clean
rm -rf %{buildroot}

%pre
if [ $1 = 1 ]; then
	useradd -d %{_localstatedir}/lib/netmrg netmrg > /dev/null 2>&1 || true
	chmod 755 %{_localstatedir}/lib/netmrg
fi

%post
if [ $1 = 1 ]; then
	chkconfig --add netmrg
fi

%preun
if [ $1 = 0 ]; then
	service netmrg stop > /dev/null 2>&1
	/sbin/chkconfig --del netmrg
fi

%postun
if [ $1 = 0 ]; then
	userdel netmrg
fi

%files
%defattr(-, root, root)
%doc %{_datadir}/doc/*
%config(noreplace) %{_sysconfdir}/%{name}.xml
%config(noreplace) %{_sysconfdir}/%{name}.conf
%config %{_sysconfdir}/cron.d/netmrg
%config %{_sysconfdir}/rc.d/init.d/netmrg
%{_bindir}/*
%{_datadir}/%{name}/*
%{_mandir}/man1/*
%{_localstatedir}/www/*
%attr(-, netmrg, netmrg) %dir %{_localstatedir}/log/netmrg
%attr(-, netmrg, netmrg) %{_localstatedir}/lib/netmrg/*
%{_libexecdir}/*

%changelog
* Thu Oct 14 2004 Douglas E. Warner <silfreed@netmrg.net>
- fix homedir for netmrg

* Fri May 28 2004 Douglas E. Warner <silfreed@netmrg.net>
- added new init and cron scripts

* Sat May 19 2004 Kevin Bonner <keb@pa.net>
- added build option '--with ucd' to allow building on older RH boxes

* Sat Oct 06 2003 Douglas E. Warner <silfreed@netmrg.net>
- Initial RPM release.

