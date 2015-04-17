<?php
require_once(INCLUDE_DIR.'class.auth.php');
require_once(INCLUDE_DIR.'class.staff.php');

class StaffIFCLDAPAuthentication extends StaffAuthenticationBackend {
  static $name = "IFC LDAP Authentication";
  static $id   = "ifc-ldap-auth";
  public $config;
 
  function __construct($config) {
    $this->config = $config;
  }
    
  function supportsInteractiveAuthentication() {
    return false;
  }

  function authenticate($username, $password, $errors=array()) {
    if ($username == '' || $password == '') return false;
    
    $ldap_user = $this->ladpAuthenticate($username, $password);
    
    if ($ldap_user) {      
      if (($user = StaffSession::lookup($username)) && $user->getId()) {
        // Atualizar usuário
        if (!$user instanceof StaffSession) {
          $user = new StaffSession($user->getId());

          $userdata = array(
            'passwd1'     => $password,
            'passwd2'     => $password,
            'email'       => $ldap_user['privatemail'][0],
          );
          $errors = null;
          $user->update($userdata, $errors); 
        }
        return $user;
      } else {
        // Inserir usuário
        $userdata = array(
          'username'    => $ldap_user['uid'][0],
          'passwd1'     => $password,
          'passwd2'     => $password,
          'email'       => $ldap_user['privatemail'][0],
          'firstname'   => $ldap_user['givenname'][0],
          'lastname'    => $ldap_user['sn'][0],
          
          'dept_id'     => $this->config->get('dept_id'),
          'group_id'    => $this->config->get('group_id'),
          'timezone_id' => 11,
          'isadmin'     => 0,
          'isactive'    => 1,
        );
        
        $errors = null;
        $user = new Staff(null);        
        $user->create($userdata, $errors);        
        return $user;
      }
    } else {
      return false;
    }
  }

  function ldapGetConnection() {  
    $connection = @ldap_connect($this->config->get('host'), $this->config->get('port'));
    ldap_set_option($connection, LDAP_OPT_PROTOCOL_VERSION, 3);
    ldap_set_option($connection, LDAP_OPT_REFERRALS, 0);
    
    return $connection;
  }

  function ladpAuthenticate($username, $password) {
    $conn       = $this->ldapGetConnection();
    $base_dn    = $this->config->get('base_dn');
    $service_dn = $this->config->get('service_dn');    
    $filter     = "(&(uid=$username)(active=TRUE)(memberOf=$service_dn))";
  
    $result     = @ldap_search($conn, $base_dn, $filter);
    $entries    = @ldap_get_entries($conn, $result);
    
    if ($entries['count'] != 0) {
      $user = $entries[0];
      $ldap_bind = @ldap_bind($conn, $user['dn'], $password);
  
      if ($ldap_bind) {
        // Usuário autenticado com sucesso
        return $user;
      } else {
        // Senha inválida
        return false;
      }
      
    } else {
      // Usuário não encontrado
      return false;
    }
  }
}

require_once(INCLUDE_DIR.'class.plugin.php');
require_once('config.php');
class IFCLdapAuthPlugin extends Plugin {
  var $config_class = 'IFCAuthConfig';

  function bootstrap() {
    $config = $this->getConfig();
    StaffAuthenticationBackend::register(new StaffIFCLDAPAuthentication($config));
  }
}