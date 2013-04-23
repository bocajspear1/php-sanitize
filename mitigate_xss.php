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
		 * 		No javascript
		 * 		No brackets and parenthesis (by default)
		 * 
		 * Input:
		 * 		$data (string) - String to be cleaned by the class
		 * 		$options (array) - options for function
		 * 			- quotes_on : if set, quotes are not removed
		 *  		- brackets_on : if set, brackets and parenthesises are not removed
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
				
				// Remove equal signs, backslashes, backticks, semicolons, colons, question marks, aterisks, carots, dollar signs and forward slashes
				$this->remove_chars(array("=","\\","`",";",":", "?", "*", "^", '$', "/"));
				
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
		 * Description: sanatizes the string based on the needs of an data or inner html, built for the usage -> <tag>CLEANED_STRING_GOES_HERE</tag>
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
				
			}
			
		public function santitize_link($data, array $options = array())
			{
				
			}
			
		public function santitize_javascript($data,array $options = array())
			{
				
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
			$input = urldecode($input);
			
			// Turn all HTML special characters into their real values for analysis
			$input = html_entity_decode($input);
		}
	
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
					 
					$this->toclean = str_replace("'''","",$this->toclean);
					$this->toclean = str_replace("\"\"\"","",$this->toclean); 
					
					// Remove all quotes if the number of quotes is not even (means injection)
					
					if ((substr_count($this->toclean,"\"")%2)!=0||(substr_count($this->toclean,"'")%2)!=0)
						{
							$this->toclean = str_replace("'","",$this->toclean);
							$this->toclean = str_replace("\"","",$this->toclean);
						}
					
				}else{
					$this->toclean = str_replace("'","",$this->toclean);
					$this->toclean = str_replace("\"","",$this->toclean);
				}
			
			
		}
	
	/*
	 * Function: remove_html_tags [Protected]
	 * 
	 * Description: Removes all html tags from the string being cleaned.
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
	 * 		$this->remove_html_tags($options_array);
	 */	
	protected function remove_html_tags(array $options = array())
		{
			$this->toclean = strip_tags($this->toclean);
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
			// Loop through each character in the $char array
			foreach ($chars as $char)
				{ 
					// Check if the array item is actually character
					if (ctype_graph($char)&&strlen($char)==1)
						{
							// Replace the character
							$this->toclean = str_ireplace($char,"",$this->toclean);
						}else{
							// Die if character is invalid
							die("Invalid chars input!");
						}
				}
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
			// Loop through each string in $strings array
			foreach ($strings as $string)
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
							$this->toclean = str_ireplace($string,"",$this->toclean);
							
							// Set new value of string to be compared
							$after = $this->toclean;
							
						}
					
				}
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
	protected function remove_string_regex(string $regex_string,array $options = array())
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
					
					// Remove all currently known instances of regex string from string to be cleaned
					$this->toclean = preg_replace($regex_string, '', $this->toclean);
					
					// Set new value of string to be compared
					$after = $this->toclean;
					
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
			$pattern = '#<script>(.+?)</script>#i';
			$this->toclean = preg_replace($pattern, '', $this->toclean);
			
			// Remove any stray tags
			$this->toclean = str_ireplace("<script>","",$this->toclean);
			$this->toclean = str_ireplace("</script>","",$this->toclean);
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
	 * 
	 * Usage:
	 * 		$this->remove_javascript_attributes($options_array);
	 */	
	protected function remove_javascript_attributes(array $options = array())
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
					
					// Format on<action>="<code>", does account for spaces around = sign
					// Setup regex pattern
					$pattern = '#on[a-z ]+=[ ]*\"(.+?)\"#i';
					// Remove matches to regex string
					$this->toclean = preg_replace($pattern, '', $this->toclean);
					
					//Format on<action>=<code><space>. does account for spaces around = sign
					// Setup regex pattern
					$pattern = '#on[a-z ]+=[ ]*(.+?)[ ]#i';
					// Remove matches to regex string
					$this->toclean = preg_replace($pattern, '', $this->toclean);
					
					// Backup in case of failure in previous, just removes on<action>=, does account for spaces before = sign
					$this->toclean = preg_replace("#on[a-z ]+=#i", '', $this->toclean);
					
					// Also remove javascript in the form "javascript:"
					$this->toclean = str_ireplace("javascript:","",$this->toclean);
					
					// Remove other javascript stuff
					$this->toclean = str_ireplace("FSCommand","",$this->toclean);
					$this->toclean = str_ireplace("seekSegmentTime","",$this->toclean);
					
					
					// Set new value of string to be compared
					$after = $this->toclean;
					
				}
			
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
			$pattern = '#<meta(.+?)>#i';
			$this->toclean = preg_replace($pattern, '', $this->toclean);
			
			// Backup for incomlete tags
			$this->toclean = str_ireplace("<meta","",$this->toclean);

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
			
			
			$before = $this->toclean;
					
			// Setup after string
			$after = '';
			
			// Check to see if the strings are the same, after the first run, if the string does not change, all instances are removed and the function continues	
			
			while ($before!=$after)
				{
					
					// Get current value of string to be cleaned
					$before = $this->toclean;
					
					// Remove Iframe tags
					$pattern = '#<iframe(.+?)>#i';
					$this->toclean = preg_replace($pattern, '', $this->toclean);
					
					// Backup for incomlete tags
					$this->toclean = str_ireplace("<iframe","",$this->toclean);
					
					// Remove frameset tags
					$pattern = '#<frameset(.+?)>#i';
					$this->toclean = preg_replace($pattern, '', $this->toclean);
					
					// Backup for incomlete tags
					$this->toclean = str_ireplace("<frameset","",$this->toclean);
					
					// Remove frame tags
					$pattern = '#<frame(.+?)>#i';
					$this->toclean = preg_replace($pattern, '', $this->toclean);								

					// Backup for incomlete tags
					$this->toclean = str_ireplace("<frame","",$this->toclean);

					// Set new value of string to be compared
					$after = $this->toclean;
					
				}
			
			
			
			
			
			
		}
	//
	
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
					// Following code used to make sure all instances are removed, even if using a trick like ffoooo (foo inside foo)
			
					// Get current value of string to be cleaned
					$before = $this->toclean;
					
					// Setup after string
					$after = '';
					
					// Check to see if the strings are the same, after the first run, if the string does not change, all instances are removed and the function continues	
					
					while ($before!=$after)
						{
							
							// Get current value of string to be cleaned
							$before = $this->toclean;
							
							// Convert all hex codes to actual values
							$this->toclean = preg_replace('/([\\&]*)#([XxUu]{0,1})([0]*)([0-9a-fA-F]{2})/ei', 'chr(hexdec(\'$4\'))', $this->toclean);
																

							// Set new value of string to be compared
							$after = $this->toclean;
							
						}
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
					// Following code used to make sure all instances are removed, even if using a trick like ffoooo (foo inside foo)
			
					// Get current value of string to be cleaned
					$before = $this->toclean;
					
					// Setup after string
					$after = '';
					
					// Check to see if the strings are the same, after the first run, if the string does not change, all instances are removed and the function continues	
					
					while ($before!=$after)
						{
							
							// Get current value of string to be cleaned
							$before = $this->toclean;
							
							// Convert all hex codes to actual values
							$this->toclean = preg_replace('/([\\&]*)#([0]*)([0-9a-fA-F]{2,3})/ei', 'chr(\'$3\')', $this->toclean);
																

							// Set new value of string to be compared
							$after = $this->toclean;
							
						}
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
