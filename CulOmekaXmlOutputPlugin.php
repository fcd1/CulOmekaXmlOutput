<?php

class CulOmekaXmlOutputPlugin extends Omeka_Plugin_AbstractPlugin
{

  protected $_filters = array('response_contexts',
                              'action_contexts',
			      'admin_navigation_main');

  protected $_hooks = array('define_acl','admin_items_browse');

  public function filterResponseContexts($contexts)
  {
    $contexts['cul-omeka-xml'] = array(
				 'suffix'  => 'cul-omeka-xml',
				 'headers' => array('Content-Type' => 'text/xml')
				 );

    return $contexts;
  }

  public function filterActionContexts($contexts, $controller)
  {
    if ($controller['controller'] instanceof ItemsController) {
      $contexts['show'][] = 'cul-omeka-xml';
      $contexts['browse'][] = 'cul-omeka-xml';
    }

    return $contexts;
  }

  /**                                                                                 
   * Define this plugin's ACL.                                                        
   */
  public function hookDefineAcl($args)
  {
    // Restrict access to super and admin users.                                    
    $args['acl']->addResource('ModsOutput_Index');
  }

  public function hookAdminItemsBrowse($args)
  {
    echo '<h1>Hi, Fred!</h1>';
  }

  /**                                                                                 
   * Add the Simple Vocab navigation link.                                            
   */
  public function filterAdminNavigationMain($nav)
  {
    if(is_allowed('ModsOutput_Index', 'index')) {
      $nav[] = array('label' => __('MODS Xml'), 'uri' => url('mods-output'));
    }
    return $nav;
  }

}



?>