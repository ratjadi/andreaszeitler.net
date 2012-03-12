<?php
/**
 * Convert an xml file to an associative array (including the tag attributes):
 *
	1. Will always return an array.
	2. Includes the content from all elements.
	3. Includes all multiple elements in the same array.
	4. Has been thoroughly tested before being posted.
	
	Notes:
	1. Ignores content that is not in between it's own set of tags.
	2. 'attrib' and 'cdata' are keys added to the array when the element contains both attributes and content.
	
	Usage:
	  $domObj = new xmlToArrayParser($xml);
	  $domArr = $domObj->array;
	
	eg.
	  <?xml version="1.0" encoding="UTF-8" standalone="no"?>
	  <top>
	    <element1>element content 1</element1>
	    <element2 var2="val2" />
	    <element3 var3="val3" var4="val4">element content 3</element3>
	    <element3 var5="val5">element content 4</element3>
	    <element3 var6="val6" />
	    <element3>element content 7</element3>
	  </top>
	 
	  $domArr['top']['element1'] => element content 1
	  $domArr['top']['element2']['attrib']['var2'] => val2
	  $domArr['top']['element3']['0']['attrib']['var3'] => val3
	  $domArr['top']['element3']['0']['attrib']['var4'] => val4
	  $domArr['top']['element3']['0']['cdata'] => element content 3
	  $domArr['top']['element3']['1']['attrib']['var5'] => val5
	  $domArr['top']['element3']['1']['cdata'] => element content 4
	  $domArr['top']['element3']['2']['attrib']['var6'] => val6
	  $domArr['top']['element3']['3'] => element content 7
	
	Note: If your output contains something like this "Ã©" which is supposed to be the letter "é" make sure your character set is UTF-8.
	eg. <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" /> 

 * @param Str $xml file/string.
 */
class xmlToArrayParser {
  /**
   * The array created by the parser which can be assigned to a variable with: $varArr = $domObj->array.
   *
   * @var Array
   */
  public  $array;
  private $parser;
  private $pointer;

  /**
   * $domObj = new xmlToArrayParser($xml);
   *
   * @param Str $xml file/string
   */
  public function __construct($xml) {
    $this->pointer =& $this->array;
    $this->parser = xml_parser_create("UTF-8");
    xml_set_object($this->parser, $this);
    xml_parser_set_option($this->parser, XML_OPTION_CASE_FOLDING, false);
    xml_set_element_handler($this->parser, "tag_open", "tag_close");
    xml_set_character_data_handler($this->parser, "cdata");
    xml_parse($this->parser, ltrim($xml));
  }

  private function tag_open($parser, $tag, $attributes) {
    $this->convert_to_array($tag, '_');
    $idx=$this->convert_to_array($tag, 'cdata');
    if(isset($idx)) {
      $this->pointer[$tag][$idx] = Array('@idx' => $idx,'@parent' => &$this->pointer);
      $this->pointer =& $this->pointer[$tag][$idx];
    }else {
      $this->pointer[$tag] = Array('@parent' => &$this->pointer);
      $this->pointer =& $this->pointer[$tag];
    }
    if (!empty($attributes)) { $this->pointer['_'] = $attributes; }
  }

  /**
   * Adds the current elements content to the current pointer[cdata] array.
   */
  private function cdata($parser, $cdata) {
    if(isset($this->pointer['cdata'])) { $this->pointer['cdata'] .= $cdata;}
    else { $this->pointer['cdata'] = $cdata;}
  }

  private function tag_close($parser, $tag) {
    $current = & $this->pointer;
    if(isset($this->pointer['@idx'])) {unset($current['@idx']);}
    $this->pointer = & $this->pointer['@parent'];
    unset($current['@parent']);
    if(isset($current['cdata']) && count($current) == 1) { $current = $current['cdata'];}
    else if(empty($current['cdata'])) { unset($current['cdata']); }
  }

  /**
   * Converts a single element item into array(element[0]) if a second element of the same name is encountered.
   */
  private function convert_to_array($tag, $item) {
    if(isset($this->pointer[$tag][$item])) {
      $content = $this->pointer[$tag];
      $this->pointer[$tag] = array((0) => $content);
      $idx = 1;
    }else if (isset($this->pointer[$tag])) {
      $idx = count($this->pointer[$tag]);
      if(!isset($this->pointer[$tag][0])) {
        foreach ($this->pointer[$tag] as $key => $value) {
            unset($this->pointer[$tag][$key]);
            $this->pointer[$tag][0][$key] = $value;
    }}}else $idx = null;
    return $idx;
  }
} 

//EOF