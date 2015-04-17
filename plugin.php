<?php
set_include_path(get_include_path().PATH_SEPARATOR.dirname(__file__).'/include');
return array(
    'id' =>             'ifc:auth', # notrans
    'version' =>        '0.0.1',
    'name' =>           /* trans */ 'IFC LDAP Authentication',
    'author' =>         'IFC',
    'description' =>    /* trans */ 'Provides a configurable authentication backend
        which works against Microsoft Active Directory and OpenLdap
        servers',
    'url' =>            '',
    'plugin' =>         'authentication.php:IFCLdapAuthPlugin',
    'requires' => array(),
);

?>
