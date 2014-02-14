<?php

class CulOmekaXmlOutputPlugin extends Omeka_Plugin_AbstractPlugin
{

  protected $_filters = array('response_contexts',
                              'action_contexts');

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

}

?>