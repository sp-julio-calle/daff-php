<?php

class haxe_io_Output {
	public function __construct(){}
	public function writeByte($c) {
		throw new HException("Not implemented");
	}
	public function writeBytes($s, $pos, $len) {
		$k = $len;
		$b = $s->b;
		if($pos < 0 || $len < 0 || $pos + $len > $s->length) {
			throw new HException(haxe_io_Error::$OutsideBounds);
		}
		while($k > 0) {
			$this->writeByte(ord($b[$pos]));
			$pos++;
			$k--;
		}
		return $len;
	}
	public function writeFullBytes($s, $pos, $len) {
		while($len > 0) {
			$k = $this->writeBytes($s, $pos, $len);
			$pos += $k;
			$len -= $k;
			unset($k);
		}
	}
	public function writeString($s) {
		$b = haxe_io_Bytes::ofString($s);
		$this->writeFullBytes($b, 0, $b->length);
	}
	function __toString() { return 'haxe.io.Output'; }
}
