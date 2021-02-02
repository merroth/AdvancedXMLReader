<?php
class AdvancedXMLReader {
	public static $readmodes = array(
		'normal' => 0,
		'skipToNext' => 1,
		'break' => 2
	);
	public $file;
	public $callback;
	public $readmode;
	public $handle;
	public $path = array();
	public function __construct($file, $callback, $readmode = 0) {
		$this->file = $file;
		$this->callback = $callback;
		$this->readmode = $readmode;
		$this->handle = new XMLReader();
		$this->handle->open($file);
	}
	public function read() {
		$name = null;
		while(true) {
			if($this->readmode == AdvancedXMLReader::$readmodes["normal"]) {
				if(!$this->handle->read()) {
					break;
				}
			} elseif($this->readmode == AdvancedXMLReader::$readmodes["skipToNext"]) {
				if(!$this->handle->next($name)) {
					break;
				}
				array_splice($this->path,sizeof($this->path)-1,1);
			} elseif($this->readmode == AdvancedXMLReader::$readmodes["break"]) {
				break;
			}
			$name = $this->handle->name;
			$type = $this->handle->nodeType;
			$isEmpty = $this->handle->isEmptyElement;
			if($type == XMLReader::END_ELEMENT) {
				if($this->path[sizeof($this->path)-1] == $name) {
					array_splice($this->path,sizeof($this->path)-1,1);
				}
			} elseif ( $this->handle->nodeType == XMLReader::ELEMENT ) {
				$this->path[] = $name;
				
				$readmode = call_user_func($this->callback,implode("/",$this->path), $this->handle);
				if(!is_numeric($readmode)) { $readmode = 0; }
				$this->readmode = $readmode;

				if($isEmpty) {
					array_splice($this->path,sizeof($this->path)-1,1);
				}
			}
		}
	}
}