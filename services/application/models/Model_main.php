<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Model_main extends CI_Model {

	public function selectAll($table)
	{
		$q = $this->db->get($table);
		return $q->result();
	}

	public function addRecord($table, $data)
	{
		$this->db->insert($table, $data);
		return true;
	}
	
	public function updateRecord($table, $data, $id)
	{
		$this->db->update($table, $data, $id);
		return true;
	}

	public function deleteRecord($table, $id)
	{
		$this->db->delete($table, $id);
		return true;
	}

	
}
