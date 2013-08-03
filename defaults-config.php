<?php
// PHP Sanitize Defaults Configuration

// Default Javascript attribute array


	


/* 
 * DEFAULT_IMG_SRC_LOCAL_ONLY
 * 		Indicates if the src attribute for image tags should be urls to local hosting only
 * 
 * 		Default: true
 * 
 * 		Status: Recommended, see this link: 
 * 		
 */
define('DEFAULT_IMG_SRC_LOCAL_ONLY',true);

/* 
 * SANITIZE_LIMIT
 * 		The amount of times the sanitize class will loop
 * 
 * 		Default: 200
 * 
 * 		Status: What ever you would think would be manageable by your host 
 * 		
 */
define ("SANITIZE_LIMIT",200);

/* 
 * SANITIZE_VALID_IMAGE_TYPES
 * 		Indicates if the file extension of the img src tag is a certain type
 * 
 * 		Default: true
 * 
 * 		Status: Recommended, see this link: 
 * 		
 */
define ("SANITIZE_VALID_IMAGE_TYPES",'jpeg|jpg|gif|png');

/* 
 * SANTITIZE_IMG_SRC_LOCAL_ONLY
 * 		Indicates if the src attribute for image tags should be urls to local hosting only
 * 
 * 		Default: true
 * 
 * 		Status: Recommended, see this link: 
 * 		
 */
define('SANTITIZE_IMG_SRC_LOCAL_ONLY',true);

/* 
 * SANTITIZE_INIT_SPECIAL_HTML_CONVERT
 * 		Indicates if HTML entities should be converted to the actual characters when the santitizing is started
 * 
 * 		Default: true
 * 
 * 		Status: Recommended, see this link: 
 * 		
 */
define('SANTITIZE_INIT_SPECIAL_HTML_CONVERT',false);

?>
