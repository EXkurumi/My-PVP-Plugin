<?php
namespace nao20010128nao\pvp;

class BannableWordDetector{
	private $list=array(
		"start"=>array(),
		"end"=>array(),
		"contain"=>array(),
		);
	public function __construct($path){
		$this->appendData($path);
	}
	public function appendData($str,$isFile=true){
		$lines;
		if($isFile){
			$lines=file($str);
		}else{
			$str=str_replace("\r\n","\r",$str);
			$str=str_replace("\n","\r",$str);
			$str=str_replace("\r\r","\r",$str);
			$lines=explode("\r",$str);
		}
		foreach($lines as $line){
			$line=rtrim($line,"\t\n\r\0\x0B");
			if($line=="")continue;
			switch($line[0]){
			case "/":
				$a=$this->list["start"];
				$this->list["start"]=array_merge($a,array(substr($line,1)=>""));
				break;
			case "|":
				$a=$this->list["end"];
				$this->list["end"]=array_merge($a,array(substr($line,1)=>""));
				break;
			case "-":
				$a=$this->list["contain"];
				$this->list["contain"]=array_merge($a,array(substr($line,1)=>""));
				break;
			}
		}
	}
	public function test($str){
		foreach($this->list["start"] as $bad){
			if($this->startsWith($str,$bad)){
				return true;
			}
		}
		foreach($this->list["end"] as $bad){
			if($this->endsWith($str,$bad)){
				return true;
			}
		}
		foreach($this->list["contains"] as $bad){
			if(strpos($str,$bad)!==FALSE){
				return true;
			}
		}
		return false;
	}
	function startsWith($haystack, $needle) {
		// search backwards starting from haystack length characters from the end
		return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== FALSE;
    }
    function endsWith($haystack, $needle) {
    	// search forward starting from end minus needle length characters
    	return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== FALSE);
    }
}