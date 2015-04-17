<?php
require_once(INCLUDE_DIR.'/class.plugin.php');
require_once(INCLUDE_DIR.'/class.forms.php');

class IFCAuthConfig extends PluginConfig {

  // Provide compatibility function for versions of osTicket prior to
  // translation support (v1.9.4)
  function translate() {
    if (!method_exists('Plugin', 'translate')) {
      return array(
        function($x) { return $x; },
        function($x, $y, $n) { return $n != 1 ? $y : $x; },
      );
    }
    return Plugin::translate('ifc-auth');
  }

  function getGroups() {
    $sql = 'SELECT group_id, group_name, group_enabled as isactive FROM '.GROUP_TABLE.' ORDER BY group_name';
    $choices = array();
    if(($res=db_query($sql)) && db_num_rows($res)){
      while(list($id,$name,$isactive)=db_fetch_row($res)) {
        $choices[$id] = $name;
      }
    }
    return $choices;
  }
  
  function getDepartaments() {
    $sql = 'SELECT dept_id, dept_name FROM '.DEPT_TABLE.' ORDER BY dept_name';
    $choices = array();
    if(($res=db_query($sql)) && db_num_rows($res)){
      while(list($id,$name)=db_fetch_row($res)){
        $choices[$id] = $name;
      }
    }
    return $choices;
  }
  
  function getOptions() {
    list($__, $_N) = self::translate();
    return array(
      'host' => new TextboxField(array(
        'label' => 'Host',
        'hint' => 'Endereço do servidor LDAP ex.: ldap.araquari.ifc.edu.br',
        'configuration' => array('size'=>40, 'length'=>60),
      )),
      'port' => new TextboxField(array(
        'label' => 'Port',
        'hint' => 'Porta do servidor LDAP ex.: 389',
        'configuration' => array('size'=>40, 'length'=>60),
      )),
      'base_dn' => new TextboxField(array(
        'label' => 'Base DN',
        'hint' => 'Base do diretório ex.: dc=araquari,dc=ifc,dc=edu,dc=br',
        'configuration' => array('size'=>40, 'length'=>60),
      )),
      //'admin_dn' => new TextboxField(array(
      //  'label' => 'Admin DN',
      //  'hint' => 'DN do super usuário do LDAP ex.: cn=admin,dc=araquari,dc=ifc,dc=edu,dc=br',
      //)),
      //'admin_pass' => new TextboxField(array(
      //  'widget' => 'PasswordWidget',
      //  'label' => 'Senha do Admin',
      //  'hint' => 'Senha do super usuário do LDAP',
      //)),
      'service_dn' => new TextboxField(array(
        'label' => 'Membro do serviço',
        'hint' => 'DN do serviço que os usuários devem fazer parte ex.: cn=os-ticket,ou=services,dc=araquari,dc=ifc,dc=edu,dc=br',
        'configuration' => array('size'=>40, 'length'=>60),
      )),
      
      'group_id' => new ChoiceField(array(
        'label' => $__('Grupo'),
        'choices' => $this->getGroups(),
        'hint' => 'Novos usuários serão criados nesse grupo',
      )),
      
      'dept_id' => new ChoiceField(array(
        'label' => $__('Departamento'),
        'choices' => $this->getDepartaments(),
        'hint' => 'Novos usuários serão criados nesse departamento',
      ))
      
    );
  }

  function pre_save(&$config, &$errors) {
    global $msg;

    list($__, $_N) = self::translate();
    if (!$errors)
      $msg = $__('Configuration updated successfully');

    return true;
  }
}