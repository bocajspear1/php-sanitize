<?php

// Get the base class
include_once("./sanitize-base.php");


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
				$this->init($data, $options);

				// Remove style attributes
				$this->remove_strings(array("style="));
				
				// Remove meta tags
				$this->remove_meta_tags();
				
				// Remove iframe tags
				$this->remove_frame_tags();
				
				// Remove object and embed tags
				$this->remove_object_tags();
				$this->remove_embed_tags();
				
				//Remove base tags
				$this->remove_base_tags();
				
				// Remove all javscript, tags and attributes
				$this->remove_javascript_attributes();
				$this->remove_javascript_tags();
				$this->remove_css_javascript();
				$this->remove_eval();
			
				
				
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
				//$this->remove_html_tags();
				
				// If 'brackets_on' is not set, remove brackets and parenthesis
				if (!in_array('brackets_on',$options))
					{
						
						$this->remove_chars(array("{","}","[","]","(",")","<",">"));
					}
					
				
				$this->set_to_html_entities();
				
				// Return cleaned string
				return $this->end();
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
				$this->init($data, $options);
				
				// Remove meta tags
				$this->remove_meta_tags();
				
				// Remove iframe tags
				$this->remove_frame_tags();
				
				// Remove all javscript, tags and attributes
				$this->remove_javascript_attributes();
				$this->remove_javascript_tags();
				
				
				$this->check_img_tags();
				// Parse the data being cleaned to HTML for easier modification
				$this->parse_to_html();
				
				
				
				$code_tags = array('pre');
				$code_storage = array();
				
				
				
				
			
				
				$this->parse_html_to_string();
				
				$this->remove_chars(array("{","}"));
				// Exempt <pre> and other tags given by user from rigourous filtering, but give them special code filtering
				
				
				$this->parse_to_html();
				
				foreach ($code_tags as $code_tag)
					{
						$tags = $this->toclean->getElementsByTagName($code_tag);
						
						$id = 0;
						foreach ($tags as $tag)
							{
								
							
								$tag->nodeValue = $code_storage[$code_tag][$id];

								
								$id+=1;
							}
					}
				
				
				$this->parse_html_to_string();
				
				return $this->end();
			}
		
		
		public function sanitize_code($data,  array $options = array())
			{
				$this->init($data, $options);
				$this->set_to_html_entities();
				return $this->end();
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
				$this->init($data, $options);

				$before = $this->get();
				
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
					
				
				$after = $this->get();
				
				if ($before != $after)
					{
						$this->sanitize_link($after, $options);
					}else{
						// Return cleaned string
						return $after;
					}
				
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
?>
