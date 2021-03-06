<?php

class coopy_SqlCompare {
	public function __construct($db, $local, $remote) {
		if(!php_Boot::$skip_constructor) {
		$this->db = $db;
		$this->local = $local;
		$this->remote = $remote;
	}}
	public $db;
	public $parent;
	public $local;
	public $remote;
	public $at0;
	public $at1;
	public $align;
	public function equalArray($a1, $a2) {
		if($a1->length !== $a2->length) {
			return false;
		}
		{
			$_g1 = 0;
			$_g = $a1->length;
			while($_g1 < $_g) {
				$i = $_g1++;
				if($a1[$i] !== $a2[$i]) {
					return false;
				}
				unset($i);
			}
		}
		return true;
	}
	public function validateSchema() {
		$all_cols1 = $this->local->getColumnNames();
		$all_cols2 = $this->remote->getColumnNames();
		if(!$this->equalArray($all_cols1, $all_cols2)) {
			return false;
		}
		$key_cols1 = $this->local->getPrimaryKey();
		$key_cols2 = $this->remote->getPrimaryKey();
		if(!$this->equalArray($key_cols1, $key_cols2)) {
			return false;
		}
		if($key_cols1->length === 0) {
			return false;
		}
		return true;
	}
	public function denull($x) {
		if($x === null) {
			return -1;
		}
		return $x;
	}
	public function link() {
		$i0 = $this->denull($this->db->get(0));
		$i1 = $this->denull($this->db->get(1));
		if($i0 === -3) {
			$i0 = $this->at0;
			$this->at0++;
		}
		if($i1 === -3) {
			$i1 = $this->at1;
			$this->at1++;
		}
		$factor = null;
		if($i0 >= 0 && $i1 >= 0) {
			$factor = 2;
		} else {
			$factor = 1;
		}
		$offset = $factor - 1;
		if($i0 >= 0) {
			$_g1 = 0;
			$_g = $this->local->get_width();
			while($_g1 < $_g) {
				$x = $_g1++;
				$this->local->setCellCache($x, $i0, $this->db->get(2 + $factor * $x));
				unset($x);
			}
		}
		if($i1 >= 0) {
			$_g11 = 0;
			$_g2 = $this->remote->get_width();
			while($_g11 < $_g2) {
				$x1 = $_g11++;
				$this->remote->setCellCache($x1, $i1, $this->db->get(2 + $factor * $x1 + $offset));
				unset($x1);
			}
		}
		$this->align->link($i0, $i1);
		$this->align->addToOrder($i0, $i1, null);
	}
	public function linkQuery($query, $order) {
		if($this->db->begin($query, null, $order)) {
			while($this->db->read()) {
				$this->link();
			}
			$this->db->end();
		}
	}
	public function apply() {
		if($this->db === null) {
			return null;
		}
		if(!$this->validateSchema()) {
			return null;
		}
		$rowid_name = $this->db->rowid();
		$this->align = new coopy_Alignment();
		$key_cols = $this->local->getPrimaryKey();
		$data_cols = $this->local->getAllButPrimaryKey();
		$all_cols = $this->local->getColumnNames();
		$this->align->meta = new coopy_Alignment();
		{
			$_g1 = 0;
			$_g = $all_cols->length;
			while($_g1 < $_g) {
				$i = $_g1++;
				$this->align->meta->link($i, $i);
				unset($i);
			}
		}
		$this->align->meta->range($all_cols->length, $all_cols->length);
		$this->align->tables($this->local, $this->remote);
		$this->align->range(999, 999);
		$sql_table1 = $this->local->getQuotedTableName();
		$sql_table2 = $this->remote->getQuotedTableName();
		$sql_key_cols = "";
		{
			$_g11 = 0;
			$_g2 = $key_cols->length;
			while($_g11 < $_g2) {
				$i1 = $_g11++;
				if($i1 > 0) {
					$sql_key_cols .= ",";
				}
				$sql_key_cols .= _hx_string_or_null($this->local->getQuotedColumnName($key_cols[$i1]));
				unset($i1);
			}
		}
		$sql_all_cols = "";
		{
			$_g12 = 0;
			$_g3 = $all_cols->length;
			while($_g12 < $_g3) {
				$i2 = $_g12++;
				if($i2 > 0) {
					$sql_all_cols .= ",";
				}
				$sql_all_cols .= _hx_string_or_null($this->local->getQuotedColumnName($all_cols[$i2]));
				unset($i2);
			}
		}
		$sql_key_match = "";
		{
			$_g13 = 0;
			$_g4 = $key_cols->length;
			while($_g13 < $_g4) {
				$i3 = $_g13++;
				if($i3 > 0) {
					$sql_key_match .= " AND ";
				}
				$n = $this->local->getQuotedColumnName($key_cols[$i3]);
				$sql_key_match .= _hx_string_or_null($sql_table1) . "." . _hx_string_or_null($n) . " IS " . _hx_string_or_null($sql_table2) . "." . _hx_string_or_null($n);
				unset($n,$i3);
			}
		}
		$sql_data_mismatch = "";
		{
			$_g14 = 0;
			$_g5 = $data_cols->length;
			while($_g14 < $_g5) {
				$i4 = $_g14++;
				if($i4 > 0) {
					$sql_data_mismatch .= " OR ";
				}
				$n1 = $this->local->getQuotedColumnName($data_cols[$i4]);
				$sql_data_mismatch .= _hx_string_or_null($sql_table1) . "." . _hx_string_or_null($n1) . " IS NOT " . _hx_string_or_null($sql_table2) . "." . _hx_string_or_null($n1);
				unset($n1,$i4);
			}
		}
		$sql_dbl_cols = "";
		$dbl_cols = (new _hx_array(array()));
		{
			$_g15 = 0;
			$_g6 = $all_cols->length;
			while($_g15 < $_g6) {
				$i5 = $_g15++;
				if($i5 > 0) {
					$sql_dbl_cols .= ",";
				}
				$n2 = $this->local->getQuotedColumnName($all_cols[$i5]);
				$buf = "__coopy_" . _hx_string_rec($i5, "");
				$sql_dbl_cols .= _hx_string_or_null($sql_table1) . "." . _hx_string_or_null($n2) . " AS " . _hx_string_or_null($buf);
				$dbl_cols->push($buf);
				$sql_dbl_cols .= ",";
				$sql_dbl_cols .= _hx_string_or_null($sql_table2) . "." . _hx_string_or_null($n2) . " AS " . _hx_string_or_null($buf) . "b";
				$dbl_cols->push(_hx_string_or_null($buf) . "b");
				unset($n2,$i5,$buf);
			}
		}
		$sql_order = "";
		{
			$_g16 = 0;
			$_g7 = $key_cols->length;
			while($_g16 < $_g7) {
				$i6 = $_g16++;
				if($i6 > 0) {
					$sql_order .= ",";
				}
				$n3 = $this->local->getQuotedColumnName($key_cols[$i6]);
				$sql_order .= _hx_string_or_null($n3);
				unset($n3,$i6);
			}
		}
		$sql_dbl_order = "";
		{
			$_g17 = 0;
			$_g8 = $key_cols->length;
			while($_g17 < $_g8) {
				$i7 = $_g17++;
				if($i7 > 0) {
					$sql_dbl_order .= ",";
				}
				$n4 = $this->local->getQuotedColumnName($key_cols[$i7]);
				$sql_dbl_order .= _hx_string_or_null($sql_table1) . "." . _hx_string_or_null($n4);
				unset($n4,$i7);
			}
		}
		$rowid = "-3";
		$rowid1 = "-3";
		$rowid2 = "-3";
		if($rowid_name !== null) {
			$rowid = $rowid_name;
			$rowid1 = _hx_string_or_null($sql_table1) . "." . _hx_string_or_null($rowid_name);
			$rowid2 = _hx_string_or_null($sql_table2) . "." . _hx_string_or_null($rowid_name);
		}
		$sql_inserts = "SELECT DISTINCT NULL, " . _hx_string_or_null($rowid) . " AS rowid, " . _hx_string_or_null($sql_all_cols) . " FROM " . _hx_string_or_null($sql_table2) . " WHERE NOT EXISTS (SELECT 1 FROM " . _hx_string_or_null($sql_table1) . " WHERE " . _hx_string_or_null($sql_key_match) . ")";
		$sql_inserts_order = _hx_deref((new _hx_array(array("NULL", "rowid"))))->concat($all_cols);
		$sql_updates = "SELECT DISTINCT " . _hx_string_or_null($rowid1) . " AS __coopy_rowid0, " . _hx_string_or_null($rowid2) . " AS __coopy_rowid1, " . _hx_string_or_null($sql_dbl_cols) . " FROM " . _hx_string_or_null($sql_table1) . " INNER JOIN " . _hx_string_or_null($sql_table2) . " ON " . _hx_string_or_null($sql_key_match) . " WHERE " . _hx_string_or_null($sql_data_mismatch);
		$sql_updates_order = _hx_deref((new _hx_array(array("__coopy_rowid0", "__coopy_rowid1"))))->concat($dbl_cols);
		$sql_deletes = "SELECT DISTINCT " . _hx_string_or_null($rowid) . " AS rowid, NULL, " . _hx_string_or_null($sql_all_cols) . " FROM " . _hx_string_or_null($sql_table1) . " WHERE NOT EXISTS (SELECT 1 FROM " . _hx_string_or_null($sql_table2) . " WHERE " . _hx_string_or_null($sql_key_match) . ")";
		$sql_deletes_order = _hx_deref((new _hx_array(array("rowid", "NULL"))))->concat($all_cols);
		$this->at0 = 1;
		$this->at1 = 1;
		$this->linkQuery($sql_inserts, $sql_inserts_order);
		$this->linkQuery($sql_updates, $sql_updates_order);
		$this->linkQuery($sql_deletes, $sql_deletes_order);
		return $this->align;
	}
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
	function __toString() { return 'coopy.SqlCompare'; }
}
