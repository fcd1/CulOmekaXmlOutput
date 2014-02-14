<?php

  // fcd1, 02/14/14:
  // This class is based on application/libraries/Omeka/Output/OmekaXml/AbstractOmekaXml.php
  // Renamed class Omeka_Output_OmekaXml_AbstractOmekaXml to Output_AbstractCulOmekaXml.
  // Modified some of the original functions (kept the orginal names) and created new
  // function(s).

/**
 * Omeka
 * 
 * @copyright Copyright 2007-2012 Roy Rosenzweig Center for History and New Media
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */

/**
 * Abstract base class for creating omeka-xml output formats.
 * 
 * @package Omeka\Output
 */
abstract class Output_AbstractCulOmekaXml
{
    /**
     * XML Schema instance namespace URI.
     */
    const XMLNS_XSI            = 'http://www.w3.org/2001/XMLSchema-instance';
    
    /**
     * Omeka-XML namespace URI.
     */
    const XMLNS                = 'http://omeka.org/schemas/omeka-xml/v5';
    
    /**
     * Omeka-XML XML Schema URI.
     */
    const XMLNS_SCHEMALOCATION = 'http://omeka.org/schemas/omeka-xml/v5/omeka-xml-5-0.xsd';
    
    /**
     * This class' contextual record(s).
     * @var array|Omeka_Record_AbstractRecord
     */
    protected $_record;

    /**
     * The context of this DOMDocument. Determines how buildNode() builds the 
     * elements. Valid contexts include: item, file.
     * 
     * @var string
     */
    protected $_context;
    
    /**
     * The final document object.
     * @var DOMDocument
     */
    protected $_doc;
    
    /**
     * The node built and set in child::_buildNode()
     * @var DOMNode
     */
    protected $_node;
    
    /**
     * Abstract method. child::_buildNode() should set self::$_node.
     */
    abstract protected function _buildNode();
    
    /**
     * @param Omeka_Record_AbstractRecord|array $record
     * @param string $context The context of this DOM document.
     */
    public function __construct($record, $context)
    {
        $this->_record = $record;
        $this->_context = $context;
        $this->_doc = new DOMDocument('1.0', 'UTF-8');
        $this->_doc->formatOutput = true;
        $this->_buildNode();
    }
    
    /**
     * Get the document object.
     * 
     * @return DOMDocument
     */
    public function getDoc()
    {
        $this->_doc->appendChild($this->_setRootElement($this->_node));
        return $this->_doc;
    }
    
    /**
     * Set an element as root.
     * 
     * @param DOMElement $rootElement
     * @return DOMElement The root element, including required attributes.
     */
    // fcd1, 02/10/14:
    // Remove all the attributes of the root element
    protected function _setRootElement($rootElement)
    {
      /*
        $rootElement->setAttribute('xmlns', self::XMLNS);
        $rootElement->setAttribute('xmlns:xsi', self::XMLNS_XSI);
        $rootElement->setAttribute('xsi:schemaLocation', self::XMLNS . ' ' . self::XMLNS_SCHEMALOCATION);
        $rootElement->setAttribute('uri', $this->_buildUrl());
        $rootElement->setAttribute('accessDate', date('c'));
      */
        return $rootElement;
    }
    
    /**
     * Create a DOM element.
     * 
     * @param string $name The name of the element.
     * @param null|string The value of the element.
     * @param null|int The id attribute of the element.
     * @param null|DOMElement The parent element.
     * @return DOMElement
     */
    protected function _createElement($name, $value = null, $id = null, $parentElement = null)
    {
        $element = $this->_doc->createElement($name);
        
        // Append the value, if given.
        if ($value) {
            $textNode = $this->_doc->createTextNode($value);
            $element->appendChild($textNode);
        }
        
        // Set the @id attribute, if given.
        if ($id) {
            $element->setAttribute("{$name}Id", $id);
        }
        
        // Append to the parent element, if given.
        if ($parentElement) {
            $parentElement->appendChild($element);
        }
        
        return $element;
    }
    
    /**
     * Set the pagination node for container elements
     *
     * @param DOMElement The parent container element.
     * @return void
     */
    // fcd1, 02/10/14:
    // We don't want pagination info; furthermore, we want all the elements in one
    // page, so the "Results per page (admin)" will be set to a large enough number that
    // there is no pagination.
    // Original code will be commented out, and we will just return
    protected function _setContainerPagination(DOMElement $parentElement)
    {
      /*
        // Return if the pagination data is not registered.
        if (!Zend_Registry::isRegistered('pagination')) {
            return;
        }
        $pagination = Zend_Registry::get('pagination');
        $miscellaneousContainerElement = $this->_createElement('miscellaneousContainer', null, null, $parentElement);
        $paginationElement = $this->_createElement('pagination', null, null, $miscellaneousContainerElement);
        $this->_createElement('pageNumber',   $pagination['page'],          null, $paginationElement);
        $this->_createElement('perPage',      $pagination['per_page'],      null, $paginationElement);
        $this->_createElement('totalResults', $pagination['total_results'], null, $paginationElement);
      */
      return;
    }
    
    /**
     * Get all element sets, elements, and element texts associated with the 
     * provided record.
     * 
     * @param Omeka_Record_AbstractRecord $record The record from which to 
     * extract metadata.
     * @param bool $getItemType Whether to get the item type metadata.
     * @return stdClass A list of element sets or an item type.
     */
    // fcd1, 02/10/14:
    // Commented out original function _getElemetSetsByElementTexts(), and
    // make changes on a copy.
    // Original function is below
    /*
    protected function _getElemetSetsByElementTexts(Omeka_Record_AbstractRecord $record, $getItemType = false)
    {
        $elementSets = array();
        $itemType = array();
        
        // Get all element texts associated with the provided record.
        $elementTexts = $record->getAllElementTexts();

        foreach ($elementTexts as $elementText) {
            
            // Get associated element and element set records.
            $element = get_db()->getTable('Element')->find($elementText->element_id);
            $elementSet = get_db()->getTable('ElementSet')->find($element->element_set_id);
            
            // Differenciate between the element sets and the "Item Type 
            // Metadata" pseudo element set.
            if (ElementSet::ITEM_TYPE_NAME == $elementSet->name) {
                $itemType['elements'][$element->id]['name'] = $element->name;
                $itemType['elements'][$element->id]['description'] = $element->description;
                $itemType['elements'][$element->id]['elementTexts'][$elementText->id]['text'] = $elementText->text;
            } else {
                $elementSets[$elementSet->id]['name'] = $elementSet->name;
                $elementSets[$elementSet->id]['description'] = $elementSet->description;
                $elementSets[$elementSet->id]['elements'][$element->id]['name'] = $element->name;
                $elementSets[$elementSet->id]['elements'][$element->id]['description'] = $element->description;
                $elementSets[$elementSet->id]['elements'][$element->id]['elementTexts'][$elementText->id]['text'] = $elementText->text;
            }
        }
        
        // Return the item type metadata.
        if ($getItemType) {
            $itemType['id'] = $record->Type->id;
            $itemType['name'] = $record->Type->name;
            $itemType['description'] = $record->Type->description;
            return $itemType;
        }
        
        // Return the element sets metadata.
        return $elementSets;
    }
    */
    // fcd1, 02/10/14:
    // This is the copy of the function where the changes will be made
    protected function _getElemetSetsByElementTexts(Omeka_Record_AbstractRecord $record, $getItemType = false)
    {
        $elementSets = array();
        $itemType = array();
        
        // Get all element texts associated with the provided record.
        $elementTexts = $record->getAllElementTexts();

        foreach ($elementTexts as $elementText) {
            
            // Get associated element and element set records.
            $element = get_db()->getTable('Element')->find($elementText->element_id);
            $elementSet = get_db()->getTable('ElementSet')->find($element->element_set_id);
            
            // Differenciate between the element sets and the "Item Type 
            // Metadata" pseudo element set.
            if (ElementSet::ITEM_TYPE_NAME == $elementSet->name) {
                $itemType['elements'][$element->id]['name'] = $element->name;
                $itemType['elements'][$element->id]['description'] = $element->description;
                $itemType['elements'][$element->id]['elementTexts'][$elementText->id]['text'] = $elementText->text;
            } else {
                $elementSets[$elementSet->id]['name'] = $elementSet->name;
                $elementSets[$elementSet->id]['description'] = $elementSet->description;
                $elementSets[$elementSet->id]['elements'][$element->id]['name'] = $element->name;
                $elementSets[$elementSet->id]['elements'][$element->id]['description'] = $element->description;
                $elementSets[$elementSet->id]['elements'][$element->id]['elementTexts'][$elementText->id]['text'] = $elementText->text;
            }
        }
        
        // Return the item type metadata.
        if ($getItemType) {
            $itemType['id'] = $record->Type->id;
            $itemType['name'] = $record->Type->name;
            $itemType['description'] = $record->Type->description;
            return $itemType;
        }
        
        // Return the element sets metadata.
        return $elementSets;
    }

    
    /**
     * Build an elementSetContainer element in a record (item or file) context.
     * 
     * @param Omeka_Record_AbstractRecord $record The record from which to build 
     * element sets.
     * @param DOMElement $parentElement The element set container will append to 
     * this element.
     * @return void|null
     */
    // fcd1, 02/10/14:
    // Commented out original function _buildElementSetContainerForRecord(), and
    // make changes on a copy.
    // Original function is below
    /*
    protected function _buildElementSetContainerForRecord(Omeka_Record_AbstractRecord $record, DOMElement $parentElement)
    {
        $elementSets = $this->_getElemetSetsByElementTexts($record);
        
        // Return if there are no element sets.
        if (!$elementSets) {
            return null;
        }
        
        // elementSetContainer
        $elementSetContainerElement = $this->_createElement('elementSetContainer');
        foreach ($elementSets as $elementSetId => $elementSet) {
             // elementSet
            $elementSetElement = $this->_createElement('elementSet', null, $elementSetId);
            $nameElement = $this->_createElement('name', $elementSet['name'], null, $elementSetElement);
            $descriptionElement = $this->_createElement('description', $elementSet['description'], null, $elementSetElement);
            // elementContainer
            $elementContainerElement = $this->_createElement('elementContainer');
            foreach ($elementSet['elements'] as $elementId => $element) {
                // Exif data may contain invalid XML characters. Avoid encoding 
                // errors by skipping relevent elements.
                if ('Omeka Image File' == $elementSet['name'] && ('Exif Array' == $element['name'] || 'Exif String' == $element['name'])) {
                    continue;
                }
                // element
                $elementElement = $this->_createElement('element', null, $elementId);
                $nameElement = $this->_createElement('name', $element['name'], null, $elementElement);
                $descriptionElement = $this->_createElement('description', $element['description'], null, $elementElement);
                // elementTextContainer
                $elementTextContainerElement = $this->_createElement('elementTextContainer');
                foreach ($element['elementTexts'] as $elementTextId => $elementText) {
                    // elementText
                    $elementTextElement = $this->_createElement('elementText', null, $elementTextId);
                    $textElement = $this->_createElement('text', $elementText['text'], null, $elementTextElement);
                    $elementTextContainerElement->appendChild($elementTextElement);
                }
                $elementElement->appendChild($elementTextContainerElement);
                $elementContainerElement->appendChild($elementElement);
            }
            $elementSetElement->appendChild($elementContainerElement);
            $elementSetContainerElement->appendChild($elementSetElement);
        }
        $parentElement->appendChild($elementSetContainerElement);
    }
    */
    // fcd1, 02/10/14:
    // This is the copy of the function where the changes will be made
    protected function _buildElementSetContainerForRecord(Omeka_Record_AbstractRecord $record, DOMElement $parentElement)
    {
        $elementSets = $this->_getElemetSetsByElementTexts($record);
        
        // Return if there are no element sets.
        if (!$elementSets) {
            return null;
        }
        
        // elementSetContainer
	// fcd1, 02/10/14:
	// Get rid of elementSetContainer. So its direct children, the elementset,
	// will now be direct children of $parentElement
        // $elementSetContainerElement = $this->_createElement('elementSetContainer');
        foreach ($elementSets as $elementSetId => $elementSet) {
             // elementSet
	  // fcd1, 02/10/14:
	  // Instead of creating elementSet, create XML element using the name of the set
	  /* 
            $elementSetElement = $this->_createElement('elementSet', null, $elementSetId);
            $nameElement = $this->_createElement('name', $elementSet['name'], null, $elementSetElement);
	    $descriptionElement = $this->_createElement('description', $elementSet['description'], null, $elementSetElement);
	  */
	    // fcd1, 02/10/14:
	    // remove space from name, and create an element using the resulting string
	    $elementSetNameNoSpaces = str_replace(" ","",$elementSet['name']);
            $elementSetElement = $this->_createElement($elementSetNameNoSpaces);

            // elementContainer
	    // fcd1, 02/10/14:
	    // Get rid of elementContainer.
            // $elementContainerElement = $this->_createElement('elementContainer');
            foreach ($elementSet['elements'] as $elementId => $element) {
                // Exif data may contain invalid XML characters. Avoid encoding 
                // errors by skipping relevent elements.
                if ('Omeka Image File' == $elementSet['name'] && ('Exif Array' == $element['name'] || 'Exif String' == $element['name'])) {
                    continue;
                }
                // element
		// fcd1, 02/10/14:
		// Instead of creating element, create element using the name of the set
		/* 
		 $elementElement = $this->_createElement('element', null, $elementId);
		 $nameElement = $this->_createElement('name', $element['name'], null, $elementElement);
		 $descriptionElement = $this->_createElement('description', $element['description'], null, $elementElement);
		*/
		// fcd1, 02/10/14:
		// remove space and '/' from name, and create an element using the resulting string
		$elementNameNoSpaces = str_replace(" ","",$element['name']);
		$elementNameNoSpaces = str_replace("/","",$elementNameNoSpaces);
		$elementElement = $this->_createElement($elementNameNoSpaces);
		
		// elementTextContainer
		// fcd1, 02/10/14:
		// Get rid of elementTextContainer.
                // $elementTextContainerElement = $this->_createElement('elementTextContainer');
                foreach ($element['elementTexts'] as $elementTextId => $elementText) {
		  // elementText
		  // fcd1, 02/10/14:
		  // Get rid of elementText.
		  // $elementTextElement = $this->_createElement('elementText', null, $elementTextId);
                    $textElement = $this->_createElement('text', $elementText['text'], null, $elementElement);
		    // fcd1, 02/10/14:
		    // Get rid of elementContainer, so append directly to the element
                    // $elementTextContainerElement->appendChild($elementTextElement);
		    // fcd1, 02/10/14:
		    // Get rid of elementText. Element text is appended directly to
		    // $elementElement at time of creation
		    // $elementElement->appendChild($elementTextElement);
                }
		// fcd1, 02/10/14:
		// Get rid of elementContainer.
                // $elementElement->appendChild($elementTextContainerElement);
		// fcd1, 02/10/14:
		// Get rid of elementContainer.
                // $elementContainerElement->appendChild($elementElement)
		// Each element will now be a directy child of the element set
		$elementSetElement->appendChild($elementElement);;
            }
	    // fcd1, 02/10/14:
	    // Get rid of elementContainer.
            // $elementSetElement->appendChild($elementContainerElement);
	    // fcd1, 02/10/14:
	    // Get rid of elementSetContainer. So its direct children, the elementset,
	    // will now be direct children of $parentElement
            // $elementSetContainerElement->appendChild($elementSetElement);
	    $parentElement->appendChild($elementSetElement);
        }
	// fcd1, 02/10/14:
	// Get rid of elementSetContainer. So its direct children, the elementset,
	// will now be direct children of $parentElement
        // $parentElement->appendChild($elementSetContainerElement);
    }

    /**
     * Build an itemType element in an item context.
     * 
     * @param Item $item The item from which to build the item type.
     * @param DOMElement $parentElement The item type will append to this element.
     * @return void|null
     */
    // fcd1, 02/10/14:
    // Commented out original function _buildItemTypeForItem(), and
    // make changes on a copy.
    // Original function is below
    /*
    protected function _buildItemTypeForItem(Item $item, DOMElement $parentElement)
    {
        // Return if the item does not have an item type.
        if (!$item->Type) {
            return null;
        }
        
        $itemType = $this->_getElemetSetsByElementTexts($item, true);
        
        // itemType
        $itemTypeElement = $this->_createElement('itemType', null, $itemType['id']);
        $nameElement = $this->_createElement('name', $itemType['name'], null, $itemTypeElement);
        $descriptionElement = $this->_createElement('description', $itemType['description'], null, $itemTypeElement);
        
        // Do not append elements if no element texts exist for this item type.
        if (isset($itemType['elements'])) {
            // elementContainer
            $elementContainerElement = $this->_createElement('elementContainer');
            foreach ($itemType['elements'] as $elementId => $element) {
                // element
                $elementElement = $this->_createElement('element', null, $elementId);
                $nameElement = $this->_createElement('name', $element['name'], null, $elementElement);
                $descriptionElement = $this->_createElement('description', $element['description'], null, $elementElement);
                // elementTextContainer
                $elementTextContainerElement = $this->_createElement('elementTextContainer');
                foreach ($element['elementTexts'] as $elementTextId => $elementText) {
                    // elementText
                    $elementTextElement = $this->_createElement('elementText', null, $elementTextId);
                    $textElement = $this->_createElement('text', $elementText['text'], null, $elementTextElement);
                    $elementTextContainerElement->appendChild($elementTextElement);
                }
                $elementElement->appendChild($elementTextContainerElement);
                $elementContainerElement->appendChild($elementElement);
            }
            $itemTypeElement->appendChild($elementContainerElement);
        }
        $parentElement->appendChild($itemTypeElement);
    }
    */
    // fcd1, 02/10/14:
    // This is the copy of the function where the changes will be made
    // fcd1, 02/12/14:
    // Gonna make a copy of what I have so far (in case I want to go back),
    // and I'm gonna use the copy to do the following: each ItemType contains the
    // item type, as well as any other metadata provided for that item type, as
    // content, with the metadata given a field: value
    /*
    protected function _buildItemTypeForItem(Item $item, DOMElement $parentElement)
    {
        // Return if the item does not have an item type.
        if (!$item->Type) {
            return null;
        }
        
        $itemType = $this->_getElemetSetsByElementTexts($item, true);
        
        // itemType
	// fcd1, 02/11/14:
	// rename itemType to ItemType, remove attribute
        // $itemTypeElement = $this->_createElement('itemType', null, $itemType['id']);
        $itemTypeElement = $this->_createElement('ItemType');
	// fcd1, 02/10/14:
	// remove space and '/' from name, and create an element using 
	//the resulting string , instead of a <name> element 
	$elementNameNoSpaces = str_replace(" ","",$itemType['name']);
	$elementNameNoSpaces = str_replace("/","",$elementNameNoSpaces);
	$typeElement = $this->_createElement($elementNameNoSpaces,
					     null, null, $itemTypeElement);
	// $nameElement = $this->_createElement('name', $itemType['name'], null, $itemTypeElement);
	// fcd1, 02/11/14:
	// Remove the description element
        // $descriptionElement = $this->_createElement('description', $itemType['description'], null, $itemTypeElement);
        
        // Do not append elements if no element texts exist for this item type.
        if (isset($itemType['elements'])) {
            // elementContainer
	  // fcd1, 02/11/14:
	  // Remove the description element
	  // $elementContainerElement = $this->_createElement('elementContainer');
            foreach ($itemType['elements'] as $elementId => $element) {
                // element
	      // fcd1, 02/11/14:
	      // Remove the description element
	      // $elementElement = $this->_createElement('element', null, $elementId);
		// fcd1, 02/10/14:
		// remove space and '/' from name, and create an element using 
		//the resulting string , instead of a <name> element 
		$elementNameNoSpaces = str_replace(" ","",$element['name']);
		$elementNameNoSpaces = str_replace("/","",$elementNameNoSpaces);
                $nameElement = $this->_createElement($elementNameNoSpaces,
						     null,null,$typeElement);
		// fcd1, 02/11/14:
		// Remove the description element
                // $descriptionElement = $this->_createElement('description', $element['description'], null, $elementElement);
                // elementTextContainer
		// fcd1, 02/11/14:
		// Remove the text container element
                // $elementTextContainerElement = $this->_createElement('elementTextContainer');
                foreach ($element['elementTexts'] as $elementTextId => $elementText) {
                    // elementText
		  // fcd1, 02/11/14:
		  // Remove the description element
		  // $elementTextElement = $this->_createElement('elementText', null, $elementTextId);
		  // $textElement = $this->_createElement('text', $elementText['text'], null, $elementTextElement);
		  $textElement = $this->_createElement('text', $elementText['text'], null, $nameElement);
                }
		// fcd1, 02/11/14:
		// Got rid of element text container and element container
                // $elementElement->appendChild($elementTextContainerElement);
                // $elementContainerElement->appendChild($elementElement);
                $typeElement->appendChild($nameElement);
            }
            // $itemTypeElement->appendChild($elementContainerElement);
        }
        $parentElement->appendChild($itemTypeElement);
    }
    */

    // fcd1, 02/12/14
    protected function _buildItemTypeForItem(Item $item, DOMElement $parentElement)
    {
        // Return if the item does not have an item type.
        if (!$item->Type) {
            return null;
        }
        
        $itemType = $this->_getElemetSetsByElementTexts($item, true);
        
        $itemTypeElement = $this->_createElement('ItemType', $itemType['name']);
        
        // Do not append elements if no element texts exist for this item type.
        if (isset($itemType['elements'])) {
            foreach ($itemType['elements'] as $elementId => $element) {

                foreach ($element['elementTexts'] as $elementTextId => $elementText) {

		  $textElement = $this->_createElement('text',
						       $element['name']. ': ' . $elementText['text'], null,
						       $itemTypeElement);
                }

            }

        }

        $parentElement->appendChild($itemTypeElement);

    }

    /**
     * Build a fileContainer element in an item context.
     * 
     * @param Item $item The item from which to build the file container.
     * @param DOMElement $parentElement The file container will append to this 
     * element.
     * @return void|null
     */
    // fcd1, 02/10/14:
    // We don't want the file information, so remove just return null.
    // existing code will be commented out
    protected function _buildFileContainerForItem(Item $item, DOMElement $parentElement)
    {
      /*
        // Return if the item has no files.
        if (!count($item->Files)) {
            return null;
        }
        
        // fileContainer
        $fileContainerElement = $this->_createElement('fileContainer');
        foreach ($item->Files as $file) {
            $fileOmekaXml = new Output_FileOmekaXml($file, $this->_context);
            $fileElement = $this->_doc->importNode($fileOmekaXml->_node, true);
            $fileContainerElement->appendChild($fileElement);
        }
        $parentElement->appendChild($fileContainerElement);
      */
      return null;
    }
    
    // fcd1, 02/12/14:
    // Wrote the following
    protected function _getCollectionTitle(Omeka_Record_AbstractRecord $record, $getItemType = false)
    {
        $elementSets = array();
        $itemType = array();
        
        // Get all element texts associated with the provided record.
        $elementTexts = $record->getAllElementTexts();

        foreach ($elementTexts as $elementText) {
            
            // Get associated element and element set records.
            $element = get_db()->getTable('Element')->find($elementText->element_id);
            $elementSet = get_db()->getTable('ElementSet')->find($element->element_set_id);
            
	      if ( ($elementSet->name == 'Dublin Core')
		   &&
		   ($element->name == 'Title') )
		{
		  return $elementText->text;
		}
        }
        
	// fcd1, 02/12/14:
	// If got here, nothing to return, so just return null
        return null;
    }

    /**
     * Build a collection element in an item context.
     * 
     * @param Item $item The item from which to build the collection.
     * @param DOMElement $parentElement The collection will append to this 
     * element.
     * @return void|null
     */
    // fcd1, 02/10/14:
    // We don't want the file information, so remove just return null.
    // existing code will be commented out
    // fcd1, 02/12/14:
    // Actually, we want to know the Omeka Collection the item is in
    protected function _buildCollectionForItem(Item $item, DOMElement $parentElement)
    {

        // Return if the item has no collection.
        if (!$item->Collection) {
            return null;
        }

	$collectionName = $this->_getCollectionTitle($item->Collection);
        $collectionElement = $this->_createElement('OmekaCollection', $collectionName);
        // $this->_buildElementSetContainerForRecord($item->Collection, $collectionElement);
        $parentElement->appendChild($collectionElement);

      return null;
    }
    
    /**
     * Build a tagContainer element in an item context.
     * 
     * @param Item $item The item from which to build the tag container.
     * @param DOMElement $parentElement The tag container will append to this 
     * element.
     * @return void|null
     */
    protected function _buildTagContainerForItem(Item $item, DOMElement $parentElement)
    {
        // Return if the item has no tags.
        if (!count($item->Tags)) {
            return null;
        }
        
        // tagContainer
        $tagContainerElement = $this->_createElement('tagContainer');
        foreach ($item->Tags as $tag) {
            // tag
            $tagElement = $this->_createElement('tag', null, $tag->id);
            $name = $this->_createElement('name', $tag->name, null, $tagElement);
            $tagContainerElement->appendChild($tagElement);
        }
        $parentElement->appendChild($tagContainerElement);
   }
   
    /**
    * Build an itemContainer element in a collection context.
    * 
    * @param Collection $collection The collection from which to build the item 
    * container.
    * @param DOMElement $parentElement The item container will append to this 
    * element.
    * @return void|null
    */
    protected function _buildItemContainerForCollection(Collection $collection, DOMElement $parentElement)
    {
        // Get items belonging to this collection.
        $items = get_db()->getTable('Item')->findBy(array('collection' => $collection->id));
        
        // Return if the collection has no items.
        if (!$items) {
            return null;
        }
        
        // itemContainer
        $collectionOmekaXml = new Output_ItemContainerOmekaXml($items, 'collection');
        $itemContainerElement = $this->_doc->importNode($collectionOmekaXml->_node, true);
        $parentElement->appendChild($itemContainerElement);
    }
   
   /**
    * Create a Tag URI to uniquely identify this Omeka XML instance.
    *
    * @return string
    */
   protected function _buildTagUri()
   {
       $uri = Zend_Uri::factory(absolute_url());
       $tagUri = 'tag:' . $uri->getHost() . ',' . date('Y-m-d') . ':' . $uri->getPath();
       return $tagUri;
   }
   
   /**
    * Create a absolute URI containing the current query string.
    *
    * @return string
    */
   protected function _buildUrl()
   {
       $uri = Zend_Uri::factory(absolute_url());
       $uri->setQuery($_GET);
       return $uri->getUri();
   }
}
