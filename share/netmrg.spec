Summary: Network Monitoring package using PHP, MySQL, and RRDtool
Name: NetMRG
Version: 0.10pre1
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
NetMRG is a tool for network monitoring, reporting, and graphing. Based on RRDTOOL, the best of open source graphing systems, NetMRG is capable of creating graphs of any parameter of your network.

%prep
%setup -q

%build
%configure
make %{_smp_mflags}

%install
rm -rf %{buildroot}
%makeinstall
%find_lang %{name}

%clean
rm -rf %{buildroot}

%post -p /sbin/ldconfig

%postun -p /sbin/ldconfig

%files -f %{name}.lang
%defattr(-, root, root)
%doc AUTHORS COPYING ChangeLog NEWS README TODO
%{_bindir}/*
%{_libdir}/*.so.*
%{_datadir}/%{name}
%{_mandir}/man8/*

%files devel
%defattr(-, root, root)
%doc HACKING
%{_libdir}/*.a
%{_libdir}/*.la
%{_libdir}/*.so
%{_mandir}/man3/*

%changelog
* Sat Jul 26 2003 Douglas E. Warner
- Initial RPM release.

