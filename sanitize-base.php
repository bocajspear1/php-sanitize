<?php


// Using S.C. Chen and et all's Simple HTML parser
require_once dirname(__FILE__) . '/simple_html_dom.php';

// Include config for defaults
require_once dirname(__FILE__) . '/defaults-config.php';

// Include data for violation logger
require_once dirname(__FILE__) . '/violation_logger.php';


/*
 * Class: sanitize
 * 
 * Description: Used to sanitize strings
 * 
 */
class sanitize
{
	private $javascript_attributes = array(
'onafterprint',
'onbeforeprint',
'onbeforeunload',
'onerror',
'onhaschange',
'onload',
'onmessage',
'onoffline',
'ononline',
'onpagehide',
'onpageshow',
'onpopstate',
'onredo',
'onresize',
'onstorage',
'onundo',
'onunload',
'onblur',
'onchange',
'oncontextmenu',
'onfocus',
'onformchange',
'onforminput',
'oninput',
'oninvalid',
'onreset',
'onselect',
'onsubmit',
'onkeydown',
'onkeypress',
'onkeyup',
'onclick',
'ondblclick',
'ondrag',
'ondragend',
'ondragenter',
'ondragleave',
'ondragover',
'ondragstart',
'ondrop',
'onmousedown',
'onmousemove',
'onmouseout',
'onmouseover',
'onmouseup',
'onmousewheel',
'onscroll',
'onabort',
'oncanplay',
'oncanplaythrough',
'ondurationchange',
'onemptied',
'onended',
'onerror',
'onloadeddata',
'onloadedmetadata',
'onloadstart',
'onpause',
'onplay',
'onplaying',
'onprogress',
'onratechange',
'onreadystatechange',
'onseeked',
'onseeking',
'onstalled',
'onsuspend',
'ontimeupdate',
'onvolumechange ',
'onwaiting'
);
	
	private $valid_html_tags = array('!DOCTYPE', 'a', 'abbr', 'acronym', 'address', 'applet', 'area', 'article', 'aside', 'audio', 'b', 'base', 'basefont', 'bdi', 'bdo', 'big', 'blockquote', 'body', 'br', 'button', 'canvas', 'caption', 'center', 'cite', 'code', 'col', 'colgroup', 'command', 'datalist', 'dd', 'del', 'details', 'dfn', 'dialog', 'dir', 'div', 'dl', 'dt', 'em', 'embed', 'fieldset', 'figcaption', 'figure', 'font', 'footer', 'form', 'frame', 'frameset', 'h1 to h6', 'head', 'header', 'hgroup', 'hr', 'html', 'i', 'iframe', 'img', 'input', 'ins', 'kbd', 'keygen', 'label', 'legend', 'li', 'link', 'map', 'mark', 'menu', 'meta', 'meter', 'nav', 'noframes', 'noscript', 'object', 'ol', 'optgroup', 'option', 'output', 'p', 'param', 'pre', 'progress', 'q', 'rp', 'rt', 'ruby', 's', 'samp', 'script', 'section', 'select', 'small', 'source', 'span', 'strike', 'strong', 'style', 'sub', 'summary', 'sup', 'table', 'tbody', 'td', 'textarea', 'tfoot', 'th', 'thead', 'time', 'title', 'tr', 'track', 'tt', 'u', 'ul', 'var', 'video', 'wbr');

	
	protected $toclean = ''; // Try to change to private
	
	// The string before one pass of the sanitization, used for recursive cleaning
	protected $before = '';
	
	// Stores the options for the init, used for reursive cleaning
	protected $options = array();
	
	// Stores the number of times the recursive cleaning has run
	protected $calltimes = 0;
	
	// Used to store values
	protected $storage = array();
	
	// Indicates if $this->toclean is a parsed html document or not 
	protected $parsed = false;
	
	// Indicates if the logger has run
	protected $logger_run = false;
	
	// Holds the logger class
	protected $logger;
	
	// Holds the table of html entities
	protected $html_entities_table = array();
 	
	
	public function __construct()
		{
			$table = get_html_translation_table (HTML_ENTITIES,ENT_QUOTES|ENT_HTML5, 'UTF-8');;
			foreach ($table as $id=>$table_item)
				{
					if (!strlen(strstr($table_item,";"))>0) {
						$table[$id] = $table_item . ";";
					}
				}
			$this->html_entities_table = $table; 
		}
	/*
	 * Function: init [Protected]
	 * 
	 * Description: Sets up sanitize class and sets the data to be cleaned 
	 * 
	 * Input:
	 * 		$input (string) - data to be cleaned by the class
	 * 		$options (array) - Options for init
	 * 
	 * Output:
	 * 		None
	 * 
	 * Global:
	 * 		Uses class global $toclean
	 * 
	 * Usage:
	 * 		$this->init($string);
	 * 
	 */
	protected function init($input, $options = array())
		{
			// Set the input to the 'toclean' string
			$this->toclean = $input;
			
			// Store the options given
			$this->options = $options;
			
			// Set the 'before' value so we know what 'toclean' looked like before this run
			$this->before = $this->toclean;
			
			// If we have too many calls, kill it all
			if ($this->calltimes==SANITIZE_LIMIT)
				{
					// We default to blank, because is this insane!
					$this->toclean='';
				}else{
					$this->calltimes++;
				}
			
			// Setup logger
			if (array_key_exists('logger',$options)&&array_key_exists('logger_options',$options))
				{
					$this->logger_setup($options['logger'],$options['logger_options']);
				}
		 
		 
			$init_before = $this->toclean;
			$init_after = '';
			
			while ($init_before != $init_after)
				{
					$init_before = $this->toclean;
					
					// Remove null characters
					$this->remove_null_characters();
					
					// Decode all dec encoded characters
					$this->decode_dec();
					
					// Decode all hex encoded characters
					$this->decode_hex();

					// Make sure all values have been decoded from url format
					$this->decode_url();
					
					// Convert tabs to spaces
					$this->convert_tabs_spaces();
					
					// Turn all HTML special characters into their real values for analysis
					if (SANTITIZE_INIT_SPECIAL_HTML_CONVERT==true)
						{
							$this->decode_html_entities();
						}
					
					$this->parse();
					
					// Remove javascript in IMG tags
					$this->check_img_tags();
					
					$this->remove_all_attributes_with_value('javascript:');
					
					$this->unparse();
					
					$init_after = $this->toclean;
				}
			
			
		}
	
	
	/*
	 * Function: update [Protected]
	 * 
	 * Description:  
	 * 
	 * Input:
	 * 		$input (string) - data to be cleaned by the class
	 * 
	 * Output:
	 * 		None
	 * 
	 * Global:
	 * 		Uses class global $toclean
	 * 
	 * Usage:
	 * 		$this->init($string);
	 */
	private function update($new_value)
		{
			if ($this->is_parsed())
				{
					$this->toclean = str_get_html($new_value);
				}else{
					$this->toclean = $new_value;
				}
		}

	
	private function logger_setup($logger_type,$options)
		{
			$this->logger = new violation_logger($logger_type,$options);
		}

	/*
	 * BASIC FUNCTIONS: 
	 *
	 * These functions are the basis for many other functions in the class
	 * 
	 */  
	
		
	/*
	 * Function: replace_chars [Protected]
	 * 
	 * Description: Replaces characters designated in $chars from the string being cleaned to their replacement also designated in chars
	 * 
	 * Input:
	 * 		$chars (array) - Characters to replace in string
	 * 			Format: array( "CHAR_TO_REPLACE" => "REPLACEMENT CHAR")
	 * 		$options (array) - options for removing (currently not implemented)
	 * 
	 * Output:
	 * 		None
	 * 
	 * Global:
	 * 		Uses class global $toclean
	 * 
	 * Usage:
	 * 		$this->replace_chars($chars_to_remove_array, $options_array);
	 */	
	protected function replace_chars(array $chars = array(), array $options = array())
		{
					
			// Loop through each character in the $char array
			foreach ($chars as $char=>$replace)
				{ 
					// Check if the array item is actually character
					if (ctype_graph($char)&&strlen($char)==1)
						{
							// Replace the character
							$this->update(str_ireplace($char,$replace,$this->toclean));
						}else{
							// Die if character is invalid
							die("Invalid chars input!");
						}
				}
		}
	
	/*
	 * Function: replace_strings [Protected]
	 * 
	 * Description: Replaces string. 
	 * 
	 * Input:
	 * 		$strings (array) - Substrings to remove from string
	 * 			Format: array( "STRING_TO_REPLACE" => "REPLACEMENT STRING")
	 * 		$options (array) - options for removing (currently not implemented)
	 * 
	 * Output:
	 * 		None
	 * 
	 * Global:
	 * 		Uses class global $toclean
	 * 
	 * Usage:
	 * 		$this->replace_strings($strings_to_replace_array, $options_array);
	 */	
	protected function replace_strings(array $strings = array(), array $options = array())
		{
			// Loop through each string in $strings array
			foreach ($strings as $string=>$replace)
				{
					// Following code used to make sure all instances are removed, even if using a trick like ffoooo (foo inside foo)
					
					// Get current value of string to be cleaned
					$before = "$this->toclean";
					
					// Setup after string
					$after = '';
					
					// Check to see if the strings are the same, after the first run, if the string does not change, all instances are removed and the function continues
					while ($before != $after)
						{
							// Get current value of string ot be cleaned
							$before = "$this->toclean";
							
							// Remove all currently known instances of substring from string to be cleaned
							$this->update(str_ireplace($string,$replace,"$this->toclean"));
							
							
							
							// Set new value of string to be compared
							$after = "$this->toclean";
							
							
							
						}
					
				}
		}
		
		
	/*
	 * Function: replace_string_regex [Protected]
	 * 
	 * Description: Does a preg_replace on string and replaces matched to pattern with inputed replacement string (PHP preg style). 
	 * 
	 * Input:
	 * 		$regex_string (string) - regex string to match substrings to replace in string to be cleaned
	 * 		$replace - string to replace matches to the pattern with
	 * 		$options (array) - options for removing (currently not implemented)
	 * 
	 * Output:
	 * 		None
	 * 
	 * Global:
	 * 		Uses class global $toclean
	 * 
	 * Usage:
	 * 		$this->remove_string_regex($regex_string, $options_array);
	 */	
	protected function replace_string_regex($regex_string, $replace, array $options = array())
		{
			// Following code used to make sure all instances are removed, even if using a trick like ffoooo (foo inside foo)
			
			// Get current value of string to be cleaned
			$before = "$this->toclean";
			
			// Setup after string
			$after = '';
			
			// Check to see if the strings are the same, after the first run, if the string does not change, all instances are removed and the function continues
			while ($before != $after)
				{
					// Get current value of string to be cleaned
					$before = "$this->toclean";
					
					// Remove all currently known instances of regex string from string to be cleaned (make it case insensitive)
					$this->update(preg_replace($regex_string . "i", $replace, "$this->toclean"));
					
					// Set new value of string to be compared
					$after = "$this->toclean";
					
				}
						
			
		}
	
	/*
	 * TIER 2 FUNCTIONS: 
	 *
	 * These functions are based on the basic functions, but also are the basis for many other functions in the class
	 * 
	 */ 



	
	/*
	 * Function: remove_chars [Protected]
	 * 
	 * Description: Removes characters designated in $chars from the string being cleaned 
	 * 
	 * Input:
	 * 		$chars (array) - Characters to remove from string
	 * 		$options (array) - options for removing (currently not implemented)
	 * 
	 * Output:
	 * 		None
	 * 
	 * Global:
	 * 		Uses class global $toclean
	 * 
	 * Usage:
	 * 		$this->remove_chars($chars_to_remove_array, $options_array);
	 */	
	protected function remove_chars(array $chars = array(), array $options = array())
		{
			// Turn the current array into keys and set all values to "" so we make the format "VALUE"=>"", 
			// This, in the replace_chars function, will replace "VALUE" with ""
			$remove_array = array_fill_keys($chars,"");
			
			$this->replace_chars($remove_array,$options);
		}
	
	
	/*
	 * Function: remove_strings [Protected]
	 * 
	 * Description: Like remove_chars, but removes strings designated in $strings instead. 
	 * 
	 * Input:
	 * 		$strings (array) - Substrings to remove from string
	 * 		$options (array) - options for removing (currently not implemented)
	 * 
	 * Output:
	 * 		None
	 * 
	 * Global:
	 * 		Uses class global $toclean
	 * 
	 * Usage:
	 * 		$this->remove_strings($strings_to_remove_array, $options_array);
	 */	
	protected function remove_strings(array $strings = array(), array $options = array())
		{
			// Turn the current array into keys and set all values to "" so we make the format "VALUE"=>"", 
			// This, in the replace_chars function, will replace "VALUE" with ""
			$remove_array = array_fill_keys($strings,"");
			
			$this->replace_strings($remove_array,$options);
		}
	
	/*
	 * Function: remove_string_regex [Protected]
	 * 
	 * Description: Like remove_strings, but removes string based on regex string inputed (PHP preg style). 
	 * 
	 * Input:
	 * 		$regex_string (string) - regex string to match substrings to remove from string to be cleaned
	 * 		$options (array) - options for removing (currently not implemented)
	 * 
	 * Output:
	 * 		None
	 * 
	 * Global:
	 * 		Uses class global $toclean
	 * 
	 * Usage:
	 * 		$this->remove_string_regex($regex_string, $options_array);
	 */	
	protected function remove_string_regex($regex_string,array $options = array())
		{
			$this->replace_string_regex($regex_string, "", $options);
		}
	
	
	
	
	
	/*
	 * BASIC HTML FUNCTIONS: 
	 *
	 * These functions are basic functions for alertations based in HTML
	 * 
	 */ 	
	
	protected function remove_tag($tag, $options = array())
		{
			if (in_array($tag,$this->valid_html_tags))
				{
					if (!$this->is_parsed())
						{
								
								// Removal for if the tag is used in pairs, with no attributes
								$pattern = '#<[ ]*' . $tag . '[ ]*>(.+?)</' . $tag . '>#';
								$this->remove_string_regex($pattern); 
								
								// Removal for if the tag is used in pairs, with attributes
								$pattern = '#<[ ]*' . $tag . '(.+?)>(.+?)</' . $tag . '>#';
								$this->remove_string_regex($pattern); 
							
								// Removal for if the tag is used alone
								$pattern = '#<[ ]*' . $tag . '(.+?)>#';
								$this->remove_string_regex($pattern); 
							
								$this->remove_strings(array("<" . $tag . ">","</" . $tag . ">"));
							
							
						}else{
							$tags_to_remove = $this->toclean->find($tag);
							foreach ($tags_to_remove as $remove_item)
								{
									$remove_item->outertext = '';
									
								}
						}
				}else{
					throw new HTMLInvalidTagException("Invalid HTML tag of $tag");
				}
			
		
		}
	
	protected function remove_attribute($attr,$options = array())
		{
			if ($this->is_parsed()) 
				{
					$attributes_to_remove = $this->toclean->find('[' . $attr . ']');
					foreach ($attributes_to_remove as $remove_item)
						{
							$remove_item->$attr = null;
							
						}
				}else{
					throw new Exception("This command must be used only after the string is parsed.");
				}
		}
	
	protected function remove_all_attributes_with_value($value,$options = array())
		{
			if ($this->is_parsed()) 
				{
					$this->attr_loop($this->toclean->find('root',0),$value);
				}else{
					throw new Exception("This command must be used only after the string is parsed.");
				}
			
		}
	
	private function attr_loop($current,$contains)
		{
			if (count($current->attr)>0)
				{
					foreach ($current->attr as $attr_name=>$attr_value)
						{
							
							if (stripos($attr_value, $contains)!==false)
								{
									// String is in there!
									$current->$attr_name = null;
									
								}else{
									// String is not present
								}
								
						}
				}else{
					
				}
				
				if (count($current->children)==0)
					{

					}else{
						
						foreach ($current->children as $child)
							{
								$this->attr_loop($child,$contains);
							}
							
					}
		}
	
	/*
	 * Function: remove_html_tags [Protected]
	 * 
	 * Description: Removes all html tags from the string being cleaned.
	 * 
	 * Input:
	 * 		$options (array) - options for removing\
	 * 			allowed_tags  - String of allowed tags
	 * 
	 * Output:
	 * 		None
	 * 
	 * Global:
	 * 		Uses class global $toclean
	 * 
	 * Usage:
	 * 		$this->remove_html_tags($options_array);
	 */	
	protected function remove_html_tags(array $options = array())
		{
			if (array_key_exists("allowed_tags",$options))
				{
					$this->toclean = strip_tags($this->toclean,$options['allowed_tags']);
				}else{
					$this->toclean = strip_tags($this->toclean);
				}
			
		}
	
		
	/*
	 * HTML TAG/ATTRIBUTE REMOVAL FUNCTIONS:
	 *
	 * These functions each remove a particular type of tag or attribute from the string
	 * 
	 */ 	
	
	
	
	
	/*
	 * Function: remove_object_tags [Protected]
	 * 
	 * Description: Directly removes all <object> tags. 
	 * 
	 * Input:
	 * 		$options (array) - options for removing (currently not implemented)
	 * 
	 * Output:
	 * 		None
	 * 
	 * Global:
	 * 		Uses class global $toclean
	 * 
	 * Usage:
	 * 		$this->remove_object_tags($options_array);
	 */	
	protected function remove_object_tags(array $options = array())
		{			
			$this->remove_tag('object');		
		}


	/*
	 * Function: remove_embed_tags [Protected]
	 * 
	 * Description: Directly removes all <embed> tags. 
	 * 
	 * Input:
	 * 		$options (array) - options for removing (currently not implemented)
	 * 
	 * Output:
	 * 		None
	 * 
	 * Global:
	 * 		Uses class global $toclean
	 * 
	 * Usage:
	 * 		$this->remove_embed_tags($options_array);
	 */	
	protected function remove_embed_tags(array $options = array())
		{		
			$this->remove_tag('embed');	
		}
		
	/*
	 * Function: remove_base_tags [Protected]
	 * 
	 * Description: Directly removes all <base> tags. 
	 * 
	 * Input:
	 * 		$options (array) - options for removing (currently not implemented)
	 * 
	 * Output:
	 * 		None
	 * 
	 * Global:
	 * 		Uses class global $toclean
	 * 
	 * Usage:
	 * 		$this->remove_base_tags($options_array);
	 */	
	protected function remove_base_tags(array $options = array())
		{
			$this->remove_tag('base');
		}
		
	/*
	 * Function: remove_javascript_tags [Protected]
	 * 
	 * Description: Directly removes all <script> tags. 
	 * 
	 * Input:
	 * 		$options (array) - options for removing (currently not implemented)
	 * 
	 * Output:
	 * 		None
	 * 
	 * Global:
	 * 		Uses class global $toclean
	 * 
	 * Usage:
	 * 		$this->remove_javascript_tags($options_array);
	 */	
	protected function remove_javascript_tags(array $options = array())
		{			
			$this->remove_tag('script');			
		}
	
	/*
	 * Function: remove_javascript_attributes [Protected]
	 * 
	 * Description: Removes all javascript attributes of the form on[something] (e.g. onmouseover). 
	 * 
	 * Input:
	 * 		$options (array) - options or removing (currently not implemented)
	 * 
	 * Output:
	 * 		None
	 * 
	 * Global:
	 * 		Uses class global $toclean
	 * 		Calls class functions remove_string_regex and remove_strings
	 * 
	 * Usage:
	 * 		$this->remove_javascript_attributes($options_array);
	 */	
	protected function remove_javascript_attributes(array $options = array())
		{
			
			foreach($this->javascript_attributes as $attribute)
				{
					$this->remove_attribute($attribute);
				}
			
			
			
			// Remove other javascript stuff
			//$this->remove_strings(array("javascript:","FSCommand","seekSegmentTime"));
			
		}
	
	//protected function remove_javascript_in_attributes
	
	
	
	/*
	 * Function: remove_meta_tags [Protected]
	 * 
	 * Description: Directly removes all <meta> tags. 
	 * 
	 * Input:
	 * 		$options (array) - options for removing (currently not implemented)
	 * 
	 * Output:
	 * 		None
	 * 
	 * Global:
	 * 		Uses class global $toclean
	 * 
	 * Usage:
	 * 		$this->remove_meta_tags($options_array);
	 */	
	protected function remove_meta_tags(array $options = array())
		{		
			$this->remove_tag('meta');	
		}

	/*
	 * Function: remove_frame_tags [Protected]
	 * 
	 * Description: Directly removes all <iframe>, <frameset> and <frame> tags. 
	 * 
	 * Input:
	 * 		$options (array) - options for removing (currently not implemented)
	 * 
	 * Output:
	 * 		None
	 * 
	 * Global:
	 * 		Uses class global $toclean
	 * 
	 * Usage:
	 * 		$this->remove_iframe_tags($options_array);
	 */	
	protected function remove_frame_tags (array $options = array())
		{			
			$this->remove_tag('iframe');
			$this->remove_tag('frame');
			$this->remove_tag('frameset');
		}
	
	
	
	/*
	 * OTHER REMOVAL FUNCTIONS:
	 * 
	 * These functions remove miscellaneous things
	 */
	

	
	/*
	 * Function: remove_quotes [Protected]
	 * 
	 * Description: Removes single and double quotes from string being cleaned.
	 * 
	 * Input:
	 * 		$options - options for removeable 
	 * 			- mal_quotes_only : if set, only removes malformed quotes
	 * 
	 * Output:
	 * 		None
	 * 
	 * Global:
	 * 		Uses class global $toclean
	 * 
	 * Usage:
	 * 		$this->remove_quotes($options_array);
	 */
	protected function remove_quotes(array $options = array())
		{
			if (in_array("mal_quotes_only",$options))
				{
					
					// Remove malformed quotes (three quotes in a row)
					
					$this->remove_strings("'''",'"""');
									
					// Remove all quotes if the number of quotes is not even (means injection)
					
						if ((substr_count($this->toclean,"\"")%2)!=0||(substr_count($this->toclean,"'")%2)!=0)
							{
								$this->remove_strings(array("'",'"'));
							}
					
				}else{
					$this->remove_strings(array("'",'"'));
				}
			
			
		}
	
	protected function remove_css_javascript(array $options = array())
		{
			$this->remove_string_regex("/:[ ]*expression[ ]*\(/");
		}
	 
	

	
	/*
	 * Function: remove_directory_traversal [Protected]
	 * 
	 * Description: Removes ../ and ./ from string
	 * 
	 * Input:
	 * 		$options (array) - options for removing\
	 * 			allowed_tags  - String of allowed tags
	 * 
	 * Output:
	 * 		None
	 * 
	 * Global:
	 * 		Uses class global $toclean
	 * 
	 * Usage:
	 * 		$this->remove_directory_traversal($options_array);
	 */	
	protected function remove_directory_traversal(array $options = array())
		{
		
			$this->remove_strings(array("../","./"));
			
		}
	
	

	/*
	 * INSPECTION FUNCTIONS:
	 *
	 * These functions inspect aspects of the string and make sure they are clean and will not do something evil
	 * 
	 */
	
	
	protected function check_img_tags()
		{
			$all_img_tags = $this->parser_find('img[src*=javascript:]');
			
			if (count($all_img_tags)!=0)
				{
					foreach ($all_img_tags as $img_tag)
						{
							$img_tag->src = null;
						}
				}
			
			
		} 
	
	protected function check_img_src($options = array())
		{
			// Get built in valid image file extensions
			$valid_filetypes = explode("|",SANITIZE_VALID_IMAGE_TYPES);
			
			// Check if 'toclean' is parsed
			if ($this->is_parsed())
				{
					// Get all nodes that are images with src attributes
					$results = $this->parser_find('img[src]');
					
					
					if ($results)
						{
							foreach($results as $result)
								{
									
									$parsed = $this->url_parse($result->src);
									
									// If there are file extensions given by user that they want to pass, add them into the array							
									if (array_key_exists('user_src_extensions',$options))
										{
											$valid_filetypes = array_merge($valid_filetypes,$options['user_src_extensions']);
										}
									
									// Check if the src attribute calls a valid image type
									if (!in_array($parsed['file_extension'],$valid_filetypes))
										{
											// If it does not, delete the attribute
											$result->src = null;
										}
									if((array_key_exists('local_only',$options)&&$options['local_only']===false)||SANTITIZE_IMG_SRC_LOCAL_ONLY===false)
										{
											
										}else{
											
											if ($parsed['host']!='localhost')
												{
													$result->src = null;
												}
										}
								}
						}
					
					
					
				}else{
					throw New Exception('Call to check_img_src, which can only be run if the cleaned string parsed.');
				}
		}

	protected function url_parse($url,$options = array())
		{
			$host = '';
			$file_path = '';
			$protocol = '';
			$filename = '';
			$file_path = '';
			$file_extension = '';
			$parameters = array();
			$parameter_string = '';
			
			$url = str_replace("//","/",$url);
			
			$protocol_array = explode(":/",$url);
			
		
			
			
			$full_path = '';
			if (count($protocol_array)>1)
				{
					$protocol = $protocol_array[0];
					
					
					if (count($protocol_array)==2)
						{
							$full_path = $protocol_array[1];
						}else{
							$full_path = $protocol_array[1];
							for($i= 2;$i <= count($protocol_array)-1;$i++)
								{
									$full_path .= "/" . $protocol_array[$i];
								}
						}
						
					$host_array = explode("/",$full_path);
					
					$host = $host_array[0];
					
					
					
					unset($host_array[0]);
					
					$full_path = implode("/",$host_array);
					
				}else{
					// This means this a local relevent url
					$host = 'localhost';
					$full_path = $protocol_array[0];
				}
			
			
			$param_array = explode("?",$full_path);
			$just_path = '';		
			if (count($param_array)==2)
				{
					$parameters = array();
					$parameter_string = $param_array[1];
					
					$iparam_array = explode('&',$parameter_string);
					foreach ($iparam_array as $item)
						{
							$item_array = explode("=",$item);
							$parameters[$item_array[0]] = $item_array[1];
						}
					
					
					$just_path = $param_array[0];
				}else if (count($param_array)==1){
					// There is no parameters
					$parameters = array();
					$parameter_string = '';
					$just_path = $param_array[0];
				}else{
					
				}
			
			
			$path_array = explode("/",$just_path);
			
			$filename = $path_array[count($path_array)-1];
			
			unset($path_array[count($path_array)-1]);
			
			$file_path = implode("/",$path_array) . "/";
			
			$extension_array = explode(".",$filename);
			
			if (count($extension_array)==2)
				{
					$file_extension = $extension_array[1];
				}
			
			return array('host'=>$host,'file_path'=>$file_path,'protocol'=>$protocol,'filename'=>$filename, 'file_extension'=>$file_extension,'parameters'=>$parameters, 'parameter_string'=>$parameter_string);
		}
	
	protected function parser_find($find_string)
		{
			if ($this->is_parsed())
				{
					
					return $this->toclean->find($find_string);
				}else{
					throw New Exception("Call to 'parser_find', which can only be run if the cleaned string parsed.");
				}
			
		}
	
	protected function check_javascript_src(array $whitelist = array(), array $options = array())
		{
			// Check if javascript source is local, or whitelisted
			$results = $this->parser_find('script');
			
			foreach ($results as $result)
				{
					if ($result->src)
						{
							$parsed = $this->url_parse($result->src);
							if ($parsed['host']!='localhost'&&!in_array($parsed['host'],$whitelist))
								{
									$result->outertext = '';
								}
							
						}
				}
		}
	
	protected function check_embed_tags($whitelist = array())
		{
			// Check if embed is local or in whitelist and that allowScriptAccess="never" and allownetworking="internal"
		}
	

	protected function remove_eval()
		{
			//
			$this->remove_string_regex("/eval\((.+?)\)/");
		}

	/*
	 * Function: remove_php [Protected]
	 * 
	 * Description: 
	 * 
	 * Input:
	 * 		$options (array) - options for removing
	 * 
	 * Output:
	 * 		None
	 * 
	 * Global:
	 * 		Uses class global $toclean
	 * 
	 * Usage:
	 * 		$this->remove_html_tags($options_array);
	 */	
	protected function remove_php(array $options = array())
		{
		
			
			
		}
	
	
	/*
	 * Function: clean_src_attributes [Protected]
	 * 
	 * Description: 
	 * 
	 * Input:
	 * 		$options (array) - options for removing
	 * 
	 * Output:
	 * 		None
	 * 
	 * Global:
	 * 		Uses class global $toclean
	 * 
	 * Usage:
	 * 		$this->remove_html_tags($options_array);
	 */	
	protected function clean_src_attributes(array $options = array())
		{
			
			
			
		}
	
	
	protected function remove_null_characters()
		{
			$this->remove_strings(array("\0"));
		}
	
	
	
	/*
	 * HTML PARSING FUNCTIONS: 
	 *
	 * This section of functions are the basis for parsing the toclean string into a DOM object for easier HTML-based cleaning
	 * 
	 */  
	 
	 
	 
	/*
	 * Function: parse [Protected]
	 * 
	 * Description: Parses the string to a DOM object (simple_html_dom object)
	 * 
	 * Input:
	 * 		None
	 * 
	 * Output:
	 * 		None
	 * 
	 * Global:
	 * 		Uses class global $toclean
	 * 
	 * Usage:
	 * 		$this->parse();
	 */		
	protected function parse()
		{
			if (!$this->is_parsed())
				{
					$temp = str_get_html($this->toclean);
					$this->toclean = $temp;
					$this->parsed = true;
				}
			
		}
		
	/*
	 * Function: unparsed [Protected]
	 * 
	 * Description: Converts the simple_html_dom back to its string counterpart
	 * 
	 * Input:
	 * 		None
	 * 
	 * Output:
	 * 		None
	 * 
	 * Global:
	 * 		Uses class global $toclean
	 * 
	 * Usage:
	 * 		$this->unparse();
	 */		
	protected function unparse()
		{
			if ($this->is_parsed())
				{
					$temp_string = $this->toclean->save();
					$this->toclean = $temp_string;
					$this->parsed = false;
				}else{
					throw New Exception("Call to unparse when the the string is not already parsed");
				}
			
		}
	
	
	/*
	 * Function: is_parsed [Protected]
	 * 
	 * Description: Checks if $toclean is an simple_html_dom object
	 * 
	 * Input:
	 * 		None
	 * 
	 * Output:
	 * 		None
	 * 
	 * Global:
	 * 		Uses class global $toclean
	 * 
	 * Usage:
	 * 		$this->is_parsed;
	 */		
	protected function is_parsed()
		{
			if ($this->parsed==true)
				{
					return true;
				}else{
					return false;
				}
		}
	
	protected function back_up_tags($tag, $options = array())
		{
			// Check if $toclean is in DOMDocument format
			if ($this->is_parsed())
				{
					
					
					
					$results = $this->parser_find($tag);
					
					$counter = 0;
					
					foreach ($results as $result)	
						{
							$storage_item = array();
							
							$storage_item['text'] =  $result->innertext;
							$storage_item['attr'] =  $result->attr;
							
							$result->innertext = '';
							$result->attr = array();
							
							$this->storage['backup'][$tag][$counter] = $storage_item;
							
							$counter += 1;
						}
					
						
				}else{
					
				}
			
				
		}
	
	protected function restore_tags($tag, $options)
		{
			if ($this->is_parsed())
				{
					
					$stored_values = $this->storage['backup'][$tag];
					
					$results = $this->parser_find($tag);
					
					foreach ($stored_values as $id=>$value)	
						{
							$result[$id]->innertext = $value['text'];
							$result[$id]->attr = $value['attr'];
						}
					
						
				}else{
					
				}
		}
	
	protected function get_stored_tags($tag)
		{
			return $this->storage['backup'][$tag];
		}
		
	protected function set_stored_tags($tag, $tag_update)
		{
			$this->get_from_storage('backup')[$tag] = $tag_update;
		}
	
	protected function has_backed_up_tags()
		{
			if (count($this->get_from_storage('backup'))>0)
				{
					return true;
				}else{
					return false;
				}
				
		}

	protected function get_tags($tag_name)
		{
			
		}
	
	
	/*
	 * ESCAPING FUNCTIONS: 
	 *
	 * This section of functions are for escaping parts of the string
	 * 
	 */  
	 
	
	protected function escape_quotes()
		{
			// Create a placeholder that would be extremely hard to inject into the string
			$placeholder = mt_rand() . '_DBL_SLASH_PLACEHOLDER_' . mt_rand();
			
			//Place placeholders in legit escaped backslashes
			$this->replace_strings(array('\\\\'=>$placeholder));
			
			// Replace all quotes with their escaped counterparts
			$this->replace_strings(array("'"=>"\'",'"'=>'\"'));
			
			// Replace attempts to escape by already escaping the quotes
			$this->replace_strings(array('\\\\'=>'\\'));
			
			$this->replace_strings(array($placeholder=>'\\\\'));
			
		}
		
	protected function escape_all()
		{
			
		}
	
	
	
	
	/*
	 * CONVERSION FUNCTIONS: 
	 *
	 * This section of functions are used to convert certain parts of the string to other formats
	 * 
	 */  
	 
		
	/*
	 * Function: lower_case [Protected]
	 * 
	 * Description: Sets all letters in the string to be cleaned to lowercase. 
	 * 
	 * Input:
	 * 		None
	 * 
	 * Output:
	 * 		None
	 * 
	 * Global:
	 * 		Uses class global $toclean
	 * 
	 * Usage:
	 * 		$this->lower_case();
	 */	
	protected function lower_case()
		{
			$this->update(strtolower($this->toclean));
		}
	
	/*
	 * Function: decode_html_entities [Protected]
	 * 
	 * Description: Changes all HTML special character values (e.g. '&lt;' ) to the real characters (e.g. '<'). 
	 * 
	 * Input:
	 * 		None
	 * 
	 * Output:
	 * 		None
	 * 
	 * Global:
	 * 		Uses class global $toclean
	 * 
	 * Usage:
	 * 		$this->decode_html_entities();
	 */	
	protected function decode_html_entities()
		{
			$this->update(html_entity_decode($this->toclean));
		}


	/*
	 * Function: decode_url [Protected]
	 * 
	 * Description: Changes all url encoded character valuesto the real characters. 
	 * 
	 * Input:
	 * 		None
	 * 
	 * Output:
	 * 		None
	 * 
	 * Global:
	 * 		Uses class global $toclean
	 * 
	 * Usage:
	 * 		$this->decode_html_entities();
	 */	
	protected function decode_url()
		{
			$this->update(urldecode($this->toclean));
			
		}

	/*
	 * Function: decode_html_entities [Protected]
	 * 
	 * Description: Changes all real characters (e.g. '<' ) to their corresponding HTML special character values (e.g. '&lt;'). 
	 * 
	 * Input:
	 * 		None
	 * 
	 * Output:
	 * 		None
	 * 
	 * Global:
	 * 		Uses class global $toclean
	 * 
	 * Usage:
	 * 		$this->decode_html_entities();
	 */	
	protected function set_to_html_entities(array $options = array())
		{
			// Check for incomplete html entities
			$this->update(htmlentities($this->toclean, ENT_QUOTES|ENT_HTML5, 'UTF-8'));
		}
	
	/*
	 * Function: decode_hex [Protected]
	 * 
	 * Description: Changes all hex values into their ASCII counterparts
	 * 
	 * Input:
	 * 		None
	 * 
	 * Output:
	 * 		None
	 * 
	 * Global:
	 * 		Uses class global $toclean
	 * 
	 * Usage:
	 * 		$this->decode_hex();
	 */	
	protected function decode_hex()
		{
			if (preg_match('/([\\&]*)#([XxUu]{0,1})([0]*)([0-9a-fA-F]{2})/ei',$this->toclean))
				{
					
					$this->replace_string_regex("/([\\&]*)#([XxUu]{0,1})([0]*)([0-9a-fA-F]{2})/e",'chr(hexdec(\'$4\'))');
					
				}
			
		
			
			
		}
				
	/*
	 * Function: decode_dec [Protected]
	 * 
	 * Description: Changes all dec values into their ASCII counterparts
	 * 
	 * Input:
	 * 		None
	 * 
	 * Output:
	 * 		None
	 * 
	 * Global:
	 * 		Uses class global $toclean
	 * 
	 * Usage:
	 * 		$this->decode_dec();
	 */		
	protected function decode_dec()
		{
			if (preg_match('/([\\&]*)#([0]*)([0-9a-fA-F]{2,3})/ei',$this->toclean))
				{
					// Replace dec values with ASCII counterparts
					$this->replace_string_regex('/([\\&]*)#([0]*)([0-9a-fA-F]{2,3})/e','chr(\'$3\')');
					
				}
		}
				
	/*
	 * Function: convert_tabs_spaces [Protected]
	 * 
	 * Description: Converts all tabs in string to spaces
	 * 
	 * Input:
	 * 		None
	 * 
	 * Output:
	 * 		None
	 * 
	 * Global:
	 * 		Uses class global $toclean
	 * 
	 * Usage:
	 * 		$this->convert_tabs_spaces();
	 */			
	protected function convert_tabs_spaces()
		{
		 $spaces = "    ";
		 
		 $tab_regex = "/\t/";
		 $this->replace_string_regex($tab_regex,$spaces);
		 
		}
	
	
	
	
	
	
	
	
		
	/*
	 * Function: get [Protected] 
	 * 
	 * Description: 
	 * 
	 * Input:
	 * 		None
	 * 
	 * Output:
	 * 		$this->toclean - Value of (hopefully) cleaned string
	 * 
	 * Global:
	 * 		Uses class global $toclean
	 * 
	 * Usage:
	 * 		$cleaned_string = $this->get();
	 * 
	 */	
	protected function get()
		{
			
			return $this->toclean;
		}
	
	/*
	 * Function: clean_substring [Protected] 
	 * 
	 * Description: Creates a new class of sanitize or user provided class name, initializes it, and returns it for the sanitizing of substrings
	 * NOTE: Does check to make sure the loaded class is a child of class sanitize. If not, the function calls 'die'
	 * 
	 * Input:
	 * 		$string - String to initialize the new sanitize or sanitize child class to
	 * 		$class - Name of class to return 
	 * 
	 * Output:
	 * 		$substr_clean - Sanitize class returned for use for cleaning substrings
	 * 
	 * Global:
	 * 		None
	 * 
	 * Usage:
	 * 		$class_for_substring = $this->clean_substring();
	 * 
	 */		
	protected function clean_substring($class = 'sanitize')
		{
			// Check to see if class is a child of sanitize
			if (is_subclass_of($class,'sanitize'))
				{
					$substr_clean = new $class();
					return $substr_clean;
				}else{
					die ("Critical Error: Non-sanitize class was attempted to be loaded or sanitize itself!");
				}
			
		}
		
	/*
	 * Function: self [Protected] 
	 * 
	 * Description: Returns the name of the current class
	 * 
	 * Input:
	 * 		None
	 * 
	 * Output:
	 * 		get_class($this) - The name of the current class
	 * 
	 * Global:
	 * 		Uses class global $toclean
	 * 
	 * Usage:
	 * 		$class_name = $this->self();
	 * 
	 */	
	protected function self()
		{
			return get_class($this);
		}

	/*
	 * Function: self_parent [Protected] 
	 * 
	 * Description: Calls the name of the class's parent, if the class is sanitize, santize is returned
	 * 
	 * Input:
	 * 		None
	 * 
	 * Output:
	 * 		get_class($this) - The name of the current class's parent
	 * 
	 * Global:
	 * 		Uses class global $toclean
	 * 
	 * Usage:
	 * 		$class_parent = $this->self_parent();
	 * 
	 */			
	protected function self_parent()
		{
			if ($this->self()!='sanitize')
				{
					return get_parent_class($this);
				}else{
					return 'sanitize';
				}
			
		}
	

	
	/*
	 * Function: end [Protected] 
	 * 
	 * Description: Important function! Either returns cleaned string if no changes have been made to cleaned string, or calls the function caller again
	 * to clean again if their has been changes. Recursive.
	 * 
	 * Input:
	 * 		None
	 * 
	 * Output:
	 * 		$after - The cleaned string
	 * 
	 * Global:
	 * 		Uses class global $toclean
	 * 
	 * Usage:
	 * 		$this->end();
	 * 
	 */	
	protected function end()
		{
			
			$after = "$this->toclean";
			
			$trace = debug_backtrace();
						
			array_shift($trace);
			
			$calling_function = $trace[0]['function'];
			$calling_class = $trace[0]['class'];
			$trace = '';
			
			if ($this->before != $after && $this->logger_run==false && $this->logger)
				{
					
						$this->logger->add($calling_function,$calling_class,$this->before);
						$this->logger_run = true;
						
					
				}
			
			if ($this->before != $after)
				{
					return call_user_func_array(array($this, 'self::' . $calling_function), array($this->toclean, $this->options));
				}else{
					
					return $after;
				}
			
		}





}



class ValidHTML
{

	public function tag($name)
		{
			if (in_array($name,$sanitize_valid_html_tags))
				{
					return $name;
				}else{
					throw new HTMLInvalidTagException("Invalid HTML tag of $name");
				}
		}
		
	public function is_valid($name)
		{
			if (in_array($name, $sanitize_valid_html_tags))
				{
					return true;
				}else{
					return false;
				}
		}
}

class HTMLInvalidTagException extends Exception
  {
  
  }
  

?>
