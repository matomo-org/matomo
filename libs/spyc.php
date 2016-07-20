<?php
/**
   * Spyc -- A Simple PHP YAML Class
   * @version 0.5
   * @author Vlad Andersen <vlad.andersen@gmail.com>
   * @author Chris Wanstrath <chris@ozmm.org>
   * @link http://code.google.com/p/spyc/
   * @copyright Copyright 2005-2006 Chris Wanstrath, 2006-2011 Vlad Andersen
   * @license http://www.opensource.org/licenses/mit-license.php MIT License
   * @package Spyc
   */

if (!function_exists('spyc_load')) {
  /**
   * Parses YAML to array.
   * @param string $string YAML string.
   * @return array
   */
  function spyc_load ($string) {
    return Spyc::YAMLLoadString($string);
  }
}

if (!function_exists('spyc_load_file')) {
  /**
   * Parses YAML to array.
   * @param string $file Path to YAML file.
   * @return array
   */
  function spyc_load_file ($file) {
    return Spyc::YAMLLoad($file);
  }
}

/**
   * The Simple PHP YAML Class.
   *
   * This class can be used to read a YAML file and convert its contents
   * into a PHP array.  It currently supports a very limited subsection of
   * the YAML spec.
   *
   * Usage:
   * <code>
   *   $Spyc  = new Spyc;
   *   $array = $Spyc->load($file);
   * </code>
   * or:
   * <code>
   *   $array = Spyc::YAMLLoad($file);
   * </code>
   * or:
   * <code>
   *   $array = spyc_load_file($file);
   * </code>
   * @package Spyc
   */
class Spyc {

  // SETTINGS

  const REMPTY = "\0\0\0\0\0";

  /**
   * Setting this to true will force YAMLDump to enclose any string value in
   * quotes.  False by default.
   * 
   * @var bool
   */
  public $setting_dump_force_quotes = false;

  /**
   * Setting this to true will forse YAMLLoad to use syck_load function when
   * possible. False by default.
   * @var bool
   */
  public $setting_use_syck_is_possible = false;



  /**#@+
  * @access private
  * @var mixed
  */
  private $_dumpIndent;
  private $_dumpWordWrap;
  private $_containsGroupAnchor = false;
  private $_containsGroupAlias = false;
  private $path;
  private $result;
  private $LiteralPlaceHolder = '___YAML_Literal_Block___';
  private $SavedGroups = array();
  private $indent;
  /**
   * Path modifier that should be applied after adding current element.
   * @var array
   */
  private $delayedPath = array();

  /**#@+
  * @access public
  * @var mixed
  */
  public $_nodeId;

/**
 * Load a valid YAML string to Spyc.
 * @param string $input
 * @return array
 */
  public function load ($input) {
    return $this->__loadString($input);
  }

 /**
 * Load a valid YAML file to Spyc.
 * @param string $file
 * @return array
 */
  public function loadFile ($file) {
    return $this->__load($file);
  }

  /**
     * Load YAML into a PHP array statically
     *
     * The load method, when supplied with a YAML stream (string or file),
     * will do its best to convert YAML in a file into a PHP array.  Pretty
     * simple.
     *  Usage:
     *  <code>
     *   $array = Spyc::YAMLLoad('lucky.yaml');
     *   print_r($array);
     *  </code>
     * @access public
     * @return array
     * @param string $input Path of YAML file or string containing YAML
     */
  public static function YAMLLoad($input) {
    $Spyc = new Spyc;
    return $Spyc->__load($input);
  }

  /**
     * Load a string of YAML into a PHP array statically
     *
     * The load method, when supplied with a YAML string, will do its best 
     * to convert YAML in a string into a PHP array.  Pretty simple.
     *
     * Note: use this function if you don't want files from the file system
     * loaded and processed as YAML.  This is of interest to people concerned
     * about security whose input is from a string.
     *
     *  Usage:
     *  <code>
     *   $array = Spyc::YAMLLoadString("---\n0: hello world\n");
     *   print_r($array);
     *  </code>
     * @access public
     * @return array
     * @param string $input String containing YAML
     */
  public static function YAMLLoadString($input) {
    $Spyc = new Spyc;
    return $Spyc->__loadString($input);
  }

  /**
     * Dump YAML from PHP array statically
     *
     * The dump method, when supplied with an array, will do its best
     * to convert the array into friendly YAML.  Pretty simple.  Feel free to
     * save the returned string as nothing.yaml and pass it around.
     *
     * Oh, and you can decide how big the indent is and what the wordwrap
     * for folding is.  Pretty cool -- just pass in 'false' for either if
     * you want to use the default.
     *
     * Indent's default is 2 spaces, wordwrap's default is 40 characters.  And
     * you can turn off wordwrap by passing in 0.
     *
     * @access public
     * @return string
     * @param array $array PHP array
     * @param int $indent Pass in false to use the default, which is 2
     * @param int $wordwrap Pass in 0 for no wordwrap, false for default (40)
     */
  public static function YAMLDump($array,$indent = false,$wordwrap = false) {
    $spyc = new Spyc;
    return $spyc->dump($array,$indent,$wordwrap);
  }


  /**
     * Dump PHP array to YAML
     *
     * The dump method, when supplied with an array, will do its best
     * to convert the array into friendly YAML.  Pretty simple.  Feel free to
     * save the returned string as tasteful.yaml and pass it around.
     *
     * Oh, and you can decide how big the indent is and what the wordwrap
     * for folding is.  Pretty cool -- just pass in 'false' for either if
     * you want to use the default.
     *
     * Indent's default is 2 spaces, wordwrap's default is 40 characters.  And
     * you can turn off wordwrap by passing in 0.
     *
     * @access public
     * @return string
     * @param array $array PHP array
     * @param int $indent Pass in false to use the default, which is 2
     * @param int $wordwrap Pass in 0 for no wordwrap, false for default (40)
     */
  public function dump($array,$indent = false,$wordwrap = false) {
    // Dumps to some very clean YAML.  We'll have to add some more features
    // and options soon.  And better support for folding.

    // New features and options.
    if ($indent === false or !is_numeric($indent)) {
      $this->_dumpIndent = 2;
    } else {
      $this->_dumpIndent = $indent;
    }

    if ($wordwrap === false or !is_numeric($wordwrap)) {
      $this->_dumpWordWrap = 40;
    } else {
      $this->_dumpWordWrap = $wordwrap;
    }

    // New YAML document
    $string = "---\n";

    // Start at the base of the array and move through it.
    if ($array) {
      $array = (array)$array; 
      $previous_key = -1;
      foreach ($array as $key => $value) {
        if (!isset($first_key)) $first_key = $key;
        $string .= $this->_yamlize($key,$value,0,$previous_key, $first_key, $array);
        $previous_key = $key;
      }
    }
    return $string;
  }

  /**
     * Attempts to convert a key / value array item to YAML
     * @access private
     * @return string
     * @param $key The name of the key
     * @param $value The value of the item
     * @param $indent The indent of the current node
     */
  private function _yamlize($key,$value,$indent, $previous_key = -1, $first_key = 0, $source_array = null) {
    if (is_array($value)) {
      if (empty ($value))
        return $this->_dumpNode($key, array(), $indent, $previous_key, $first_key, $source_array);
      // It has children.  What to do?
      // Make it the right kind of item
      $string = $this->_dumpNode($key, self::REMPTY, $indent, $previous_key, $first_key, $source_array);
      // Add the indent
      $indent += $this->_dumpIndent;
      // Yamlize the array
      $string .= $this->_yamlizeArray($value,$indent);
    } elseif (!is_array($value)) {
      // It doesn't have children.  Yip.
      $string = $this->_dumpNode($key, $value, $indent, $previous_key, $first_key, $source_array);
    }
    return $string;
  }

  /**
     * Attempts to convert an array to YAML
     * @access private
     * @return string
     * @param $array The array you want to convert
     * @param $indent The indent of the current level
     */
  private function _yamlizeArray($array,$indent) {
    if (is_array($array)) {
      $string = '';
      $previous_key = -1;
      foreach ($array as $key => $value) {
        if (!isset($first_key)) $first_key = $key;
        $string .= $this->_yamlize($key, $value, $indent, $previous_key, $first_key, $array);
        $previous_key = $key;
      }
      return $string;
    } else {
      return false;
    }
  }

  /**
     * Returns YAML from a key and a value
     * @access private
     * @return string
     * @param $key The name of the key
     * @param $value The value of the item
     * @param $indent The indent of the current node
     */
  private function _dumpNode($key, $value, $indent, $previous_key = -1, $first_key = 0, $source_array = null) {
    // do some folding here, for blocks
    if (is_string ($value) && ((strpos($value,"\n") !== false || strpos($value,": ") !== false || strpos($value,"- ") !== false ||
      strpos($value,"*") !== false || strpos($value,"#") !== false || strpos($value,"<") !== false || strpos($value,">") !== false || strpos ($value, '  ') !== false ||
      strpos($value,"[") !== false || strpos($value,"]") !== false || strpos($value,"{") !== false || strpos($value,"}") !== false) || strpos($value,"&") !== false || strpos($value, "'") !== false || strpos($value, "!") === 0 ||
      substr ($value, -1, 1) == ':')
    ) {
      $value = $this->_doLiteralBlock($value,$indent);
    } else {
      $value  = $this->_doFolding($value,$indent);
    }

    if ($value === array()) $value = '[ ]';
    if (in_array ($value, array ('true', 'TRUE', 'false', 'FALSE', 'y', 'Y', 'n', 'N', 'null', 'NULL'), true)) {
       $value = $this->_doLiteralBlock($value,$indent);
    }
    if (trim ($value) != $value)
       $value = $this->_doLiteralBlock($value,$indent);

    if (is_bool($value)) {
       $value = ($value) ? "true" : "false";
    }
    
    if ($value === null) $value = 'null';
    if ($value === "'" . self::REMPTY . "'") $value = null;

    $spaces = str_repeat(' ',$indent);

    //if (is_int($key) && $key - 1 == $previous_key && $first_key===0) {
    if (is_array ($source_array) && array_keys($source_array) === range(0, count($source_array) - 1)) {
      // It's a sequence
      $string = $spaces.'- '.$value."\n";
    } else {
      // if ($first_key===0)  throw new Exception('Keys are all screwy.  The first one was zero, now it\'s "'. $key .'"');
      // It's mapped
      if (strpos($key, ":") !== false || strpos($key, "#") !== false) { $key = '"' . $key . '"'; }
      $string = rtrim ($spaces.$key.': '.$value)."\n";
    }
    return $string;
  }

  /**
     * Creates a literal block for dumping
     * @access private
     * @return string
     * @param $value
     * @param $indent int The value of the indent
     */
  private function _doLiteralBlock($value,$indent) {
    if ($value === "\n") return '\n';
    if (strpos($value, "\n") === false && strpos($value, "'") === false) {
      return sprintf ("'%s'", $value);
    }
    if (strpos($value, "\n") === false && strpos($value, '"') === false) {
      return sprintf ('"%s"', $value);
    }
    $exploded = explode("\n",$value);
    $newValue = '|';
    $indent  += $this->_dumpIndent;
    $spaces   = str_repeat(' ',$indent);
    foreach ($exploded as $line) {
      $newValue .= "\n" . $spaces . ($line);
    }
    return $newValue;
  }

  /**
     * Folds a string of text, if necessary
     * @access private
     * @return string
     * @param $value The string you wish to fold
     */
  private function _doFolding($value,$indent) {
    // Don't do anything if wordwrap is set to 0

    if ($this->_dumpWordWrap !== 0 && is_string ($value) && strlen($value) > $this->_dumpWordWrap) {
      $indent += $this->_dumpIndent;
      $indent = str_repeat(' ',$indent);
      $wrapped = wordwrap($value,$this->_dumpWordWrap,"\n$indent");
      $value   = ">\n".$indent.$wrapped;
    } else {
      if ($this->setting_dump_force_quotes && is_string ($value) && $value !== self::REMPTY)
        $value = '"' . $value . '"';
    }


    return $value;
  }

// LOADING FUNCTIONS

  private function __load($input) {
    $Source = $this->loadFromSource($input);
    return $this->loadWithSource($Source);
  }

  private function __loadString($input) {
    $Source = $this->loadFromString($input);
    return $this->loadWithSource($Source);
  }

  private function loadWithSource($Source) {
    if (empty ($Source)) return array();
    if ($this->setting_use_syck_is_possible && function_exists ('syck_load')) {
      $array = syck_load (implode ('', $Source));
      return is_array($array) ? $array : array();
    }

    $this->path = array();
    $this->result = array();

    $cnt = count($Source);
    for ($i = 0; $i < $cnt; $i++) {
      $line = $Source[$i];
      
      $this->indent = strlen($line) - strlen(ltrim($line));
      $tempPath = $this->getParentPathByIndent($this->indent);
      $line = self::stripIndent($line, $this->indent);
      if (self::isComment($line)) continue;
      if (self::isEmpty($line)) continue;
      $this->path = $tempPath;

      $literalBlockStyle = self::startsLiteralBlock($line);
      if ($literalBlockStyle) {
        $line = rtrim ($line, $literalBlockStyle . " \n");
        $literalBlock = '';
        $line .= $this->LiteralPlaceHolder;
        $literal_block_indent = strlen($Source[$i+1]) - strlen(ltrim($Source[$i+1]));
        while (++$i < $cnt && $this->literalBlockContinues($Source[$i], $this->indent)) {
          $literalBlock = $this->addLiteralLine($literalBlock, $Source[$i], $literalBlockStyle, $literal_block_indent);
        }
        $i--;
      }

      while (++$i < $cnt && self::greedilyNeedNextLine($line)) {
        $line = rtrim ($line, " \n\t\r") . ' ' . ltrim ($Source[$i], " \t");
      }
      $i--;



      if (strpos ($line, '#')) {
        if (strpos ($line, '"') === false && strpos ($line, "'") === false)
          $line = preg_replace('/\s+#(.+)$/','',$line);
      }

      $lineArray = $this->_parseLine($line);

      if ($literalBlockStyle)
        $lineArray = $this->revertLiteralPlaceHolder ($lineArray, $literalBlock);

      $this->addArray($lineArray, $this->indent);

      foreach ($this->delayedPath as $indent => $delayedPath)
        $this->path[$indent] = $delayedPath;

      $this->delayedPath = array();

    }
    return $this->result;
  }

  private function loadFromSource ($input) {
    if (!empty($input) && strpos($input, "\n") === false && file_exists($input))
    return file($input);

    return $this->loadFromString($input);
  }

  private function loadFromString ($input) {
    $lines = explode("\n",$input);
    foreach ($lines as $k => $_) {
      $lines[$k] = rtrim ($_, "\r");
    }
    return $lines;
  }

  /**
     * Parses YAML code and returns an array for a node
     * @access private
     * @return array
     * @param string $line A line from the YAML file
     */
  private function _parseLine($line) {
    if (!$line) return array();
    $line = trim($line);
    if (!$line) return array();

    $array = array();

    $group = $this->nodeContainsGroup($line);
    if ($group) {
      $this->addGroup($line, $group);
      $line = $this->stripGroup ($line, $group);
    }

    if ($this->startsMappedSequence($line))
      return $this->returnMappedSequence($line);

    if ($this->startsMappedValue($line))
      return $this->returnMappedValue($line);

    if ($this->isArrayElement($line))
     return $this->returnArrayElement($line);

    if ($this->isPlainArray($line))
     return $this->returnPlainArray($line); 
     
     
    return $this->returnKeyValuePair($line);

  }

  /**
     * Finds the type of the passed value, returns the value as the new type.
     * @access private
     * @param string $value
     * @return mixed
     */
  private function _toType($value) {
    if ($value === '') return null;
    $first_character = $value[0];
    $last_character = substr($value, -1, 1);

    $is_quoted = false;
    do {
      if (!$value) break;
      if ($first_character != '"' && $first_character != "'") break;
      if ($last_character != '"' && $last_character != "'") break;
      $is_quoted = true;
    } while (0);

    if ($is_quoted)
      return strtr(substr ($value, 1, -1), array ('\\"' => '"', '\'\'' => '\'', '\\\'' => '\''));
    
    if (strpos($value, ' #') !== false && !$is_quoted)
      $value = preg_replace('/\s+#(.+)$/','',$value);

    if (!$is_quoted) $value = str_replace('\n', "\n", $value);

    if ($first_character == '[' && $last_character == ']') {
      // Take out strings sequences and mappings
      $innerValue = trim(substr ($value, 1, -1));
      if ($innerValue === '') return array();
      $explode = $this->_inlineEscape($innerValue);
      // Propagate value array
      $value  = array();
      foreach ($explode as $v) {
        $value[] = $this->_toType($v);
      }
      return $value;
    }

    if (strpos($value,': ')!==false && $first_character != '{') {
      $array = explode(': ',$value);
      $key   = trim($array[0]);
      array_shift($array);
      $value = trim(implode(': ',$array));
      $value = $this->_toType($value);
      return array($key => $value);
    }
    
    if ($first_character == '{' && $last_character == '}') {
      $innerValue = trim(substr ($value, 1, -1));
      if ($innerValue === '') return array();
      // Inline Mapping
      // Take out strings sequences and mappings
      $explode = $this->_inlineEscape($innerValue);
      // Propagate value array
      $array = array();
      foreach ($explode as $v) {
        $SubArr = $this->_toType($v);
        if (empty($SubArr)) continue;
        if (is_array ($SubArr)) {
          $array[key($SubArr)] = $SubArr[key($SubArr)]; continue;
        }
        $array[] = $SubArr;
      }
      return $array;
    }

    if ($value == 'null' || $value == 'NULL' || $value == 'Null' || $value == '' || $value == '~') {
      return null;
    }

    if ( is_numeric($value) && preg_match ('/^(-|)[1-9]+[0-9]*$/', $value) ){
      $intvalue = (int)$value;
      if ($intvalue != PHP_INT_MAX)
        $value = $intvalue;
      return $value;
    }

    if (in_array($value,
                 array('true', 'on', '+', 'yes', 'y', 'True', 'TRUE', 'On', 'ON', 'YES', 'Yes', 'Y'))) {
      return true;
    }

    if (in_array(strtolower($value),
                 array('false', 'off', '-', 'no', 'n'))) {
      return false;
    }

    if (is_numeric($value)) {
      if ($value === '0') return 0;
      if (rtrim ($value, 0) === $value)
        $value = (float)$value;
      return $value;
    }
    
    return $value;
  }

  /**
     * Used in inlines to check for more inlines or quoted strings
     * @access private
     * @return array
     */
  private function _inlineEscape($inline) {
    // There's gotta be a cleaner way to do this...
    // While pure sequences seem to be nesting just fine,
    // pure mappings and mappings with sequences inside can't go very
    // deep.  This needs to be fixed.

    $seqs = array();
    $maps = array();
    $saved_strings = array();

    // Check for strings
    $regex = '/(?:(")|(?:\'))((?(1)[^"]+|[^\']+))(?(1)"|\')/';
    if (preg_match_all($regex,$inline,$strings)) {
      $saved_strings = $strings[0];
      $inline  = preg_replace($regex,'YAMLString',$inline);
    }
    unset($regex);

    $i = 0;
    do {

    // Check for sequences
    while (preg_match('/\[([^{}\[\]]+)\]/U',$inline,$matchseqs)) {
      $seqs[] = $matchseqs[0];
      $inline = preg_replace('/\[([^{}\[\]]+)\]/U', ('YAMLSeq' . (count($seqs) - 1) . 's'), $inline, 1);
    }

    // Check for mappings
    while (preg_match('/{([^\[\]{}]+)}/U',$inline,$matchmaps)) {
      $maps[] = $matchmaps[0];
      $inline = preg_replace('/{([^\[\]{}]+)}/U', ('YAMLMap' . (count($maps) - 1) . 's'), $inline, 1);
    }

    if ($i++ >= 10) break;

    } while (strpos ($inline, '[') !== false || strpos ($inline, '{') !== false);

    $explode = explode(', ',$inline);
    $stringi = 0; $i = 0;

    while (1) {

    // Re-add the sequences
    if (!empty($seqs)) {
      foreach ($explode as $key => $value) {
        if (strpos($value,'YAMLSeq') !== false) {
          foreach ($seqs as $seqk => $seq) {
            $explode[$key] = str_replace(('YAMLSeq'.$seqk.'s'),$seq,$value);
            $value = $explode[$key];
          }
        }
      }
    }

    // Re-add the mappings
    if (!empty($maps)) {
      foreach ($explode as $key => $value) {
        if (strpos($value,'YAMLMap') !== false) {
          foreach ($maps as $mapk => $map) {
            $explode[$key] = str_replace(('YAMLMap'.$mapk.'s'), $map, $value);
            $value = $explode[$key];
          }
        }
      }
    }


    // Re-add the strings
    if (!empty($saved_strings)) {
      foreach ($explode as $key => $value) {
        while (strpos($value,'YAMLString') !== false) {
          $explode[$key] = preg_replace('/YAMLString/',$saved_strings[$stringi],$value, 1);
          unset($saved_strings[$stringi]);
          ++$stringi;
          $value = $explode[$key];
        }
      }
    }

    $finished = true;
    foreach ($explode as $key => $value) {
      if (strpos($value,'YAMLSeq') !== false) {
        $finished = false; break;
      }
      if (strpos($value,'YAMLMap') !== false) {
        $finished = false; break;
      }
      if (strpos($value,'YAMLString') !== false) {
        $finished = false; break;
      }
    }
    if ($finished) break;

    $i++;
    if ($i > 10) 
      break; // Prevent infinite loops.
    }

    return $explode;
  }

  private function literalBlockContinues ($line, $lineIndent) {
    if (!trim($line)) return true;
    if (strlen($line) - strlen(ltrim($line)) > $lineIndent) return true;
    return false;
  }

  private function referenceContentsByAlias ($alias) {
    do {
      if (!isset($this->SavedGroups[$alias])) { echo "Bad group name: $alias."; break; }
      $groupPath = $this->SavedGroups[$alias];
      $value = $this->result;
      foreach ($groupPath as $k) {
        $value = $value[$k];
      }
    } while (false);
    return $value;
  }

  private function addArrayInline ($array, $indent) {
      $CommonGroupPath = $this->path;
      if (empty ($array)) return false;
      
      foreach ($array as $k => $_) {
        $this->addArray(array($k => $_), $indent);
        $this->path = $CommonGroupPath;
      }
      return true;
  }

  private function addArray ($incoming_data, $incoming_indent) {

   // print_r ($incoming_data);

    if (count ($incoming_data) > 1)
      return $this->addArrayInline ($incoming_data, $incoming_indent);
    
    $key = key ($incoming_data);
    $value = isset($incoming_data[$key]) ? $incoming_data[$key] : null;
    if ($key === '__!YAMLZero') $key = '0';

    if ($incoming_indent == 0 && !$this->_containsGroupAlias && !$this->_containsGroupAnchor) { // Shortcut for root-level values.
      if ($key || $key === '' || $key === '0') {
        $this->result[$key] = $value;
      } else {
        $this->result[] = $value; end ($this->result); $key = key ($this->result);
      }
      $this->path[$incoming_indent] = $key;
      return;
    }


    
    $history = array();
    // Unfolding inner array tree.
    $history[] = $_arr = $this->result;
    foreach ($this->path as $k) {
      $history[] = $_arr = $_arr[$k];
    }

    if ($this->_containsGroupAlias) {
      $value = $this->referenceContentsByAlias($this->_containsGroupAlias);
      $this->_containsGroupAlias = false;
    }


    // Adding string or numeric key to the innermost level or $this->arr.
    if (is_string($key) && $key == '<<') {
      if (!is_array ($_arr)) { $_arr = array (); }

      $_arr = array_merge ($_arr, $value);
    } else if ($key || $key === '' || $key === '0') {
      if (!is_array ($_arr))
        $_arr = array ($key=>$value);
      else
        $_arr[$key] = $value;
    } else {
      if (!is_array ($_arr)) { $_arr = array ($value); $key = 0; }
      else { $_arr[] = $value; end ($_arr); $key = key ($_arr); }
    }

    $reverse_path = array_reverse($this->path);
    $reverse_history = array_reverse ($history);
    $reverse_history[0] = $_arr;
    $cnt = count($reverse_history) - 1;
    for ($i = 0; $i < $cnt; $i++) {
      $reverse_history[$i+1][$reverse_path[$i]] = $reverse_history[$i];
    }
    $this->result = $reverse_history[$cnt];

    $this->path[$incoming_indent] = $key;

    if ($this->_containsGroupAnchor) {
      $this->SavedGroups[$this->_containsGroupAnchor] = $this->path;
      if (is_array ($value)) {
        $k = key ($value);
        if (!is_int ($k)) {
          $this->SavedGroups[$this->_containsGroupAnchor][$incoming_indent + 2] = $k;
        }
      }
      $this->_containsGroupAnchor = false;
    }

  }

  private static function startsLiteralBlock ($line) {
    $lastChar = substr (trim($line), -1);
    if ($lastChar != '>' && $lastChar != '|') return false;
    if ($lastChar == '|') return $lastChar;
    // HTML tags should not be counted as literal blocks.
    if (preg_match ('#<.*?>$#', $line)) return false;
    return $lastChar;
  }

  private static function greedilyNeedNextLine($line) {
    $line = trim ($line);
    if (!strlen($line)) return false;
    if (substr ($line, -1, 1) == ']') return false;
    if ($line[0] == '[') return true;
    if (preg_match ('#^[^:]+?:\s*\[#', $line)) return true;
    return false;
  }

  private function addLiteralLine ($literalBlock, $line, $literalBlockStyle, $indent = -1) {
    $line = self::stripIndent($line, $indent);
    if ($literalBlockStyle !== '|') {
        $line = self::stripIndent($line);
    }
    $line = rtrim ($line, "\r\n\t ") . "\n";
    if ($literalBlockStyle == '|') {
      return $literalBlock . $line;
    }
    if (strlen($line) == 0)
      return rtrim($literalBlock, ' ') . "\n";
    if ($line == "\n" && $literalBlockStyle == '>') {
      return rtrim ($literalBlock, " \t") . "\n";
    }
    if ($line != "\n")
      $line = trim ($line, "\r\n ") . " ";
    return $literalBlock . $line;
  }

   function revertLiteralPlaceHolder ($lineArray, $literalBlock) {
     foreach ($lineArray as $k => $_) {
      if (is_array($_))
        $lineArray[$k] = $this->revertLiteralPlaceHolder ($_, $literalBlock);
      else if (substr($_, -1 * strlen ($this->LiteralPlaceHolder)) == $this->LiteralPlaceHolder)
	       $lineArray[$k] = rtrim ($literalBlock, " \r\n");
     }
     return $lineArray;
   }

  private static function stripIndent ($line, $indent = -1) {
    if ($indent == -1) $indent = strlen($line) - strlen(ltrim($line));
    return substr ($line, $indent);
  }

  private function getParentPathByIndent ($indent) {
    if ($indent == 0) return array();
    $linePath = $this->path;
    do {
      end($linePath); $lastIndentInParentPath = key($linePath);
      if ($indent <= $lastIndentInParentPath) array_pop ($linePath);
    } while ($indent <= $lastIndentInParentPath);
    return $linePath;
  }


  private function clearBiggerPathValues ($indent) {


    if ($indent == 0) $this->path = array();
    if (empty ($this->path)) return true;

    foreach ($this->path as $k => $_) {
      if ($k > $indent) unset ($this->path[$k]);
    }

    return true;
  }


  private static function isComment ($line) {
    if (!$line) return false;
    if ($line[0] == '#') return true;
    if (trim($line, " \r\n\t") == '---') return true;
    return false;
  }

  private static function isEmpty ($line) {
    return (trim ($line) === '');
  }


  private function isArrayElement ($line) {
    if (!$line) return false;
    if ($line[0] != '-') return false;
    if (strlen ($line) > 3)
      if (substr($line,0,3) == '---') return false;
    
    return true;
  }

  private function isHashElement ($line) {
    return strpos($line, ':');
  }

  private function isLiteral ($line) {
    if ($this->isArrayElement($line)) return false;
    if ($this->isHashElement($line)) return false;
    return true;
  }


  private static function unquote ($value) {
    if (!$value) return $value;
    if (!is_string($value)) return $value;
    if ($value[0] == '\'') return trim ($value, '\'');
    if ($value[0] == '"') return trim ($value, '"');
    return $value;
  }

  private function startsMappedSequence ($line) {
    return ($line[0] == '-' && substr ($line, -1, 1) == ':');
  }

  private function returnMappedSequence ($line) {
    $array = array();
    $key         = self::unquote(trim(substr($line,1,-1)));
    $array[$key] = array();
    $this->delayedPath = array(strpos ($line, $key) + $this->indent => $key);
    return array($array);
  }

  private function returnMappedValue ($line) {
    $array = array();
    $key         = self::unquote (trim(substr($line,0,-1)));
    $array[$key] = '';
    return $array;
  }

  private function startsMappedValue ($line) {
    return (substr ($line, -1, 1) == ':');
  }
  
  private function isPlainArray ($line) {
    return ($line[0] == '[' && substr ($line, -1, 1) == ']');
  }
  
  private function returnPlainArray ($line) {
    return $this->_toType($line); 
  }  

  private function returnKeyValuePair ($line) {
    $array = array();
    $key = '';
    if (strpos ($line, ':')) {
      // It's a key/value pair most likely
      // If the key is in double quotes pull it out
      if (($line[0] == '"' || $line[0] == "'") && preg_match('/^(["\'](.*)["\'](\s)*:)/',$line,$matches)) {
        $value = trim(str_replace($matches[1],'',$line));
        $key   = $matches[2];
      } else {
        // Do some guesswork as to the key and the value
        $explode = explode(':',$line);
        $key     = trim($explode[0]);
        array_shift($explode);
        $value   = trim(implode(':',$explode));
      }
      // Set the type of the value.  Int, string, etc
      $value = $this->_toType($value);
      if ($key === '0') $key = '__!YAMLZero';
      $array[$key] = $value;
    } else {
      $array = array ($line);
    }
    return $array;

  }


  private function returnArrayElement ($line) {
     if (strlen($line) <= 1) return array(array()); // Weird %)
     $array = array();
     $value   = trim(substr($line,1));
     $value   = $this->_toType($value);
     $array[] = $value;
     return $array;
  }


  private function nodeContainsGroup ($line) {    
    $symbolsForReference = 'A-z0-9_\-';
    if (strpos($line, '&') === false && strpos($line, '*') === false) return false; // Please die fast ;-)
    if ($line[0] == '&' && preg_match('/^(&['.$symbolsForReference.']+)/', $line, $matches)) return $matches[1];
    if ($line[0] == '*' && preg_match('/^(\*['.$symbolsForReference.']+)/', $line, $matches)) return $matches[1];
    if (preg_match('/(&['.$symbolsForReference.']+)$/', $line, $matches)) return $matches[1];
    if (preg_match('/(\*['.$symbolsForReference.']+$)/', $line, $matches)) return $matches[1];
    if (preg_match ('#^\s*<<\s*:\s*(\*[^\s]+).*$#', $line, $matches)) return $matches[1];
    return false;

  }

  private function addGroup ($line, $group) {
    if ($group[0] == '&') $this->_containsGroupAnchor = substr ($group, 1);
    if ($group[0] == '*') $this->_containsGroupAlias = substr ($group, 1);
    //print_r ($this->path);
  }

  private function stripGroup ($line, $group) {
    $line = trim(str_replace($group, '', $line));
    return $line;
  }
}

// Enable use of Spyc from command line
// The syntax is the following: php spyc.php spyc.yaml

define ('SPYC_FROM_COMMAND_LINE', false);

do {
  if (!SPYC_FROM_COMMAND_LINE) break;
  if (empty ($_SERVER['argc']) || $_SERVER['argc'] < 2) break;
  if (empty ($_SERVER['PHP_SELF']) || $_SERVER['PHP_SELF'] != 'spyc.php') break;
  $file = $argv[1];
  printf ("Spyc loading file: %s\n", $file);
  print_r (spyc_load_file ($file));
} while (0);