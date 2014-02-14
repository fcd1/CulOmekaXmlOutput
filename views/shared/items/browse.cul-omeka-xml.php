<?php
$omekaXml = new Output_ItemContainerCulOmekaXml($items, 'itemContainer');
echo $omekaXml->getDoc()->saveXML();
?>