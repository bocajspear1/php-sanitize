<?php
class violation_logger
{
	private $logger_engine;
	
	public function __construct($logger_engine,$options){
		$this->logger_engine = new $logger_engine($options);
		
		
	}
	
	public function __call($method, $args) {
		 if ($method=="on")
			{
				
			}elseif ($method=="off"){
				
			}
	}
	 
	public function add($caller_class,$caller_function, $code, $extra = array()){
		
		$info = '';
		foreach ($extra as $id=>$value)
			{
				$info .= $id . "::" . $value . "\n";
			}
		
		
		$message = "Violation at " . $caller_class . ":" . $caller_function . "\n Info:" . $info . "\n Code: (Base64 encoded) <START_CODE>" . base64_encode($code) ."</START_CODE>";
		$this->logger_engine->log($message);
	}
	 

}
?>

<?php
class file_logger
{
	public function __construct($options)
		{
			
		}
		
	public function log($message)
		{
			
		}
	
}

?>
