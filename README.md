# PHP Account Creator
Custom tool to allow creation of user accounts by users who need to make users but who don't necessarilly need access to domain controllers. 

## Documentation
Using this tool is pretty simple, set up your constants to point to your local mail and LDAP server and enable PHP to use secure LDAP and you will be ready to go.
This tool assumes the user whose account you are creating already has an enabled email account, otherwise how can we send them their login credentials?


Here are some guides for setting up secure LDAP with PHP, it can be a bit cumbersome:
- ADLdap - a library I may implement in this tool later has a decent guide to setting up secure LDAP with PHP: [ADLdap doc wiki](http://adldap.sourceforge.net/wiki/doku.php?id=ldap_over_ssl)
- Enabling secure LDAP on Active Directory - A good guide from Petri.com on enabling secure AD: [Petri.com](https://www.petri.com/enable-secure-ldap-windows-server-2008-2012-dc)

### Note
Future versions of this tool won't send actual passwords and will instead send them to this tool via a unique link to "set" their password the first time around. 
This tool was initially created to meet an immediate demand but will be changed for the future, I know it's bad.  :-)


## Contributing

Please feel free to submit bug reports or pull requests using the github issue tracker: [GitHub issue tracker](https://github.com/srrobinson/pac/issues).

This is my first crack at a project like this so I wouldn't be surprised if issues were found. :-)

## Tests
Yes... that's coming later, definitely.


