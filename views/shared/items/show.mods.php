<?php
$modsXml = new Output_ItemModsXml($item, 'item');
echo $omekaXml->getDoc()->saveXML();
