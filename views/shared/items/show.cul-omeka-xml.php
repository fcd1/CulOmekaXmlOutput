<?php
$omekaXml = new Output_ItemCulOmekaXml($item, 'item');
echo $omekaXml->getDoc()->saveXML();
?>