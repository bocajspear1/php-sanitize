<?php

class output_sanitize extends sanitize
{
		/* 
		 * Function: sanitize_attribute [Protected]
		 * 
		 * Description: sanatizes the string based on the needs of an atrributes, built for the usage -> attr="CLEANED_STRING_GOES_HERE"
		 * 		No HTML tags
		 * 		No style attributes in the attribute
		 * 		No quotes (by default)
		 * 		No Meta tags
		 * 		No javascript
		 * 		No brackets and parenthesis (by default)
		 * 		Characters Removed: =\`;?*^$/,
		 * 
		 * Input:
		 * 		$data (string) - String to be cleaned by the class
		 * 		$options (array) - options for function
		 * 			- quotes_on : if set, quotes are not removed
		 *  		- brackets_on : if set, brackets and parenthesises are not removed
		 * 			- allow_chars (array) : characters in array are not removed
		 * 			- remove_chars (array) : characters in array are removed alongside the defaults
		 * 
		 * Output:
		 * 		None
		 * 
		 * Global:
		 * 		Uses class global $toclean
		 * 
		 * Usage:
		 * 		$cleaned_string = $class->sanitize_attribute($unclean_string, $options_array);
		 * 
		 */
		public function sanitize_attribute($data, array $options = array())
			{
				// Init sanitize script
				$this->init($data);
				
				// Remove style attributes
				$this->remove_strings(array("style="));
				
				// Remove meta tags
				$this->remove_meta_tags();
				
				// Remove iframe tags
				$this->remove_frame_tags();
				
				// Remove all javscript, tags and attributes
				$this->remove_javascript_attributes();
				$this->remove_javascript_tags();
				
				// If 'quotes_on' is not set, remove quotes
				if (!in_array('quotes_on',$options))
					{
						$this->remove_quotes(array());
					}
				
				// Remove equal signs, backslashes, backticks, semicolons, question marks, aterisks, carots, dollar signs, forward slashes and commas
				$chars_to_remove = array("=","\\","`",";","?", "*", "^", '$', "/", ",");
				
				// If there are characters that the user also wants removed, add them to the remove array
				if (array_key_exists("remove_chars",$options))
					{
						$chars_to_remove = array_merge($chars_to_remove,$options['remove_chars']);
					}

				if (array_key_exists("allowed_chars",$options))
					{
						$this->filter_remove_chars($chars_to_remove,$options["allowed_chars"]);
					}else{
						$this->filter_remove_chars($chars_to_remove);
					}	
				
				
				// Remove all HTML tags
				$this->remove_html_tags();
				
				// If 'brackets_on' is not set, remove brackets and parenthesis
				if (!in_array('brackets_on',$options))
					{
						
						$this->remove_chars(array("{","}","[","]","(",")","<",">"));
					}
					
				
				// Return cleaned string
				return $this->get();
			}
		
		
		/* 
		 * Function: sanitize_data [Protected]
		 * 
		 * Description: sanitizes the string based on the needs of an data or inner html, built for the usage -> <tag>CLEANED_STRING_GOES_HERE</tag> within <body>
		 * 
		 * Input:
		 * 
		 * Output:
		 * 		None
		 * 
		 * Global:
		 * 		Uses class global $toclean
		 * 
		 * Usage:
		 * 		$cleaned_string = $class->sanitize_data($unclean_string, $options_array);
		 * 
		 */	
		public function sanitize_data($data, array $options = array())
			{
				// Exempt <code> and <pre> from rigourous filtering, but give them special filtering
				
				
				// Remove meta tags
				$this->remove_meta_tags();
				
				// Remove iframe tags
				$this->remove_frame_tags();
				
				// Remove all javscript, tags and attributes
				$this->remove_javascript_attributes();
				$this->remove_javascript_tags();
			}
		
		/* 
		 * Function: sanitize_link [Protected]
		 * 
		 * Description: sanatizes the string based on the needs of a link, built for the usage -> <a href="CLEANED_STRING_GOES_HERE"></a>
		 * 		No HTML tags
		 * 		Np frame tags
		 * 		No quotes (by default)
		 * 		No javascript
		 * 		No brackets and parenthesis (by default)
		 * 
		 * Input:
		 * 		$data (string) - String to be cleaned by the class
		 * 		$options (array) - options for function
		 * 			- quotes_on : if set, quotes are not removed
		 *  		- brackets_on : if set, brackets and parenthesises are not removed
		 * 			- allow_chars (array) : characters in array are not removed
		 * 			- remove_chars (array) : characters in array are removed alongside the defaults
		 * 
		 * Output:
		 * 		None
		 * 
		 * Global:
		 * 		Uses class global $toclean
		 * 
		 * Usage:
		 * 		$cleaned_string = $class->sanitize_link($unclean_string, $options_array);
		 * 
		 */
		public function sanitize_link($data, array $options = array())
			{
				// Init sanitize script
				$this->init($data);

				// Remove meta tags
				$this->remove_meta_tags();
				
				// Remove iframe tags
				$this->remove_frame_tags();
				
				// Remove all javscript, tags and attributes
				$this->remove_javascript_attributes();
				$this->remove_javascript_tags();
				
				// Remove all quotes
				$this->remove_quotes(array());
				
				// Needed symbols % - : ? & # / . ~ = + @ _
				// Semi-needed symbols ; , (){}[]
				// Remove backticks, exclamation points, dollar signs, carots, asterisks, pipe, backslashes, semicolons and commas
				
				$chars_to_remove = array("`","!", "$", "^", '*', '|', '\\', ";", ",");
				
				// If there are characters that the user also wants removed, add them to the remove array
				if (array_key_exists("remove_chars",$options))
					{
						$chars_to_remove = array_merge($chars_to_remove,$options['remove_chars']);
					}
				
				if (array_key_exists("allowed_chars",$options))
					{
						$this->filter_remove_chars($chars_to_remove,$options["allowed_chars"]);
					}else{
						$this->filter_remove_chars($chars_to_remove);
					}	

				
				// Remove all HTML tags
				$this->remove_html_tags();
				
				// If 'brackets_on' is not set, remove brackets and parenthesis
				if (!in_array('brackets_on',$options))
					{
						$this->remove_chars(array("{","}","[","]","(",")","<",">"));
					}
					
				
				// Return cleaned string
				return $this->get();
			}
			
		public function santitize_javascript($data,array $options = array())
			{
				
			} 


		private function filter_remove_chars(array $remove,array $notremove = array())
			{
				// Loop through all values in array of 'allow_char' to get all characters to not remove
				foreach ($notremove as $allowed)
					{
						foreach ($remove as $id=>$check)
							{
								// If a character in not-to-remove array is found in removing array, delete value from removing array
								if ($allowed == $check)
									{
										unset($remove[$id]);
									}
							}
					}
					
				
				// Remove characters given in array
				$this->remove_chars($remove);
			}
}
/*
 * Class: sanitize
 * 
 * Description: Used to sanitize strings
 * 
 */
class sanitize
{
	protected $toclean = '';
	
	/*
	 * Function: init [Protected]
	 * 
	 * Description: Sets up sanitize class and sets the data to be cleaned 
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
	 * 
	 */
	protected function init($input)
		{
			$this->toclean = $input;
			
			// Decode all dec encoded characters
			$this->decode_dec();
			
			// Decode all hex encoded characters
			$this->decode_hex();

			// Make sure all values have been decoded from url format
			$this->decode_url();
			
			// Turn all HTML special characters into their real values for analysis
			$this->decode_html_entities();
		}
	
	
	
	// Basic Functions: These functions are the basis for other functions
	
		
	/*
	 * Function: replace_chars [Protected]
	 * 
	 * Description: Replaces characters designated in $chars from the string being cleaned to their replacement also designated in chars
	 * 
	 * Input:
	 * 		$chars (array) - Characters to remove from string
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
							$this->toclean = str_ireplace($char,$replace,$this->toclean);
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
					$before = $this->toclean;
					
					// Setup after string
					$after = '';
					
					// Check to see if the strings are the same, after the first run, if the string does not change, all instances are removed and the function continues
					while ($before != $after)
						{
							// Get current value of string ot be cleaned
							$before = $this->toclean;
							
							// Remove all currently known instances of substring from string to be cleaned
							$this->toclean = str_ireplace($string,$replace,$this->toclean);
							
							// Set new value of string to be compared
							$after = $this->toclean;
							
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
			$before = $this->toclean;
			
			// Setup after string
			$after = '';
			
			// Check to see if the strings are the same, after the first run, if the string does not change, all instances are removed and the function continues
			while ($before != $after)
				{
					// Get current value of string to be cleaned
					$before = $this->toclean;
					
					// Remove all currently known instances of regex string from string to be cleaned (make it case insensitive)
					$this->toclean = preg_replace($regex_string . "i", $replace, $this->toclean);
					
					// Set new value of string to be compared
					$after = $this->toclean;
					
				}
						
			
		}
	
	
	
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
	
	// End Basic Functions
	
	
	
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
			$pattern = '#<script>(.+?)</script>#';
			$this->remove_string_regex($pattern); 
			
			// Remove any stray tags
			$this->remove_strings(array("<script>","</script>"));
			
			
		}
	
	/*
	 * Function: remove_javascript_attributes [Protected]
	 * 
	 * Description: Removes all javascript attributes of the form on[something] (e.g. onmouseover). 
	 * 
	 * Input:
	 * 		$options (array) - options for removing (currently not implemented)
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
			
			// Format on<action>="<code>", does account for spaces around = sign
			$this->remove_string_regex('#on[a-z ]+=[ ]*\"(.+?)\"#'); 
			
			//Format on<action>=<code><space>. does account for spaces around = sign
			$this->remove_string_regex('#on[a-z ]+=[ ]*(.+?)[ ]#'); 
			
			// Backup in case of failure in previous, just removes on<action>=, does account for spaces before = sign
			$this->remove_string_regex("#on[a-z ]+=#");
			
			// Remove other javascript stuff
			$this->remove_strings(array("javascript:","FSCommand","seekSegmentTime"));
			
		}
	
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
			$pattern = '#<meta(.+?)>#';
			$this->remove_string_regex($pattern); 
			
			// Remove any stray tags
			$this->remove_strings(array("<meta"));
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
			// Remove Iframe tags
			$this->remove_string_regex('#<iframe(.+?)>#'); 
			
			// Remove frameset tags
			$this->remove_string_regex('#<frameset(.+?)>#');

			// Remove frame tags
			$this->remove_string_regex('#<frame(.+?)>#');

			// Remove any stray tags
			$this->remove_strings(array("<frame","<frameset","<iframe"));

		}
	
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
			$this->toclean = strtolower ($this->toclean);
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
			$this->toclean = html_entity_decode($this->toclean);
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
			$this->toclean = urldecode($this->toclean);
			
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
			$this->toclean = htmlentities($this->toclean, ENT_QUOTES);
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
			$this->set_to_html_entities();
			return $this->toclean;
		}
}
	
?>
