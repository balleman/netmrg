%define _localdatadir /var

Summary: Network Monitoring package using PHP, MySQL, and RRDtool
Name: netmrg
Version: 0.10
Release: 1
#Epoch: 1
License: MIT
Group: Application/System
Source0: netmrg-%{version}.tar.gz
#Source1: 
#Patch0: 
#Patch1: 
URL: http://www.netmrg.net
BuildRoot: %{_tmppath}/%{name}-root
Requires: php, php-mysql, mysql, webserver, rrdtool, libxml2
BuildRequires: mysql-devel, libxml2-devel

%description
NetMRG is a tool for network monitoring, reporting, and graphing. Based 
on RRDTOOL, the best of open source graphing systems, NetMRG is capable 
of creating graphs of any parameter of your network.

%prep
%setup -q

%build
%configure
make %{_smp_mflags}

%install
rm -rf %{buildroot}
%makeinstall

%clean
rm -rf %{buildroot}

%pre
if [ $1 = 1 ]; then
	useradd -d ${_localstatedir}/lib/netmrg netmrg > /dev/null 2>&1 || true
fi

%postun
if [ $1 = 0 ]; then
	userdel netmrg
fi

%files
%defattr(-, root, root)
%doc %{_datadir}/doc/*
%config %{_sysconfdir}/*
%{_bindir}/*
%{_datadir}/%{name}/*
%{_mandir}/man1/*
%{_localstatedir}/www/*
%attr(-, netmrg, netmrg) %dir %{_localstatedir}/log/netmrg
%attr(-, netmrg, netmrg) %{_localstatedir}/lib/netmrg/*
%{_libexecdir}/*

%changelog
* Sat Oct 06 2003 Douglas E. Warner
- Initial RPM release.

