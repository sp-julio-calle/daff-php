<?php

class coopy_CellInfo {
	public function __construct() {}
	public $raw;
	public $value;
	public $pretty_value;
	public $category;
	public $category_given_tr;
	public $separator;
	public $pretty_separator;
	public $updated;
	public $conflicted;
	public $pvalue;
	public $lvalue;
	public $rvalue;
	public function toString() {
		if(!php_Boot::$skip_constructor) {
		if(!$this->updated) {
			return $this->value;
		}
		if(!$this->conflicted) {
			return _hx_string_or_null($this->lvalue) . "::" . _hx_string_or_null($this->rvalue);
		}
		return _hx_string_or_null($this->pvalue) . "||" . _hx_string_or_null($this->lvalue) . "::" . _hx_string_or_null($this->rvalue);
	}}
	public function __call($m, $a) {
		if(isset($this->$m) && is_callable($this->$m))
			return call_user_func_array($this->$m, $a);
		else if(isset($this->__dynamics[$m]) && is_callable($this->__dynamics[$m]))
			return call_user_func_array($this->__dynamics[$m], $a);
		else if('toString' == $m)
			return $this->__toString();
		else
			throw new HException('Unable to call <'.$m.'>');
	}
	function __toString() { return $this->toString(); }
}
