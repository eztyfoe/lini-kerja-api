<?php if(!defined('BASEPATH')) exit('No direct script allowed');

class M_user extends CI_Model{

	function getPengguna($data) {
		return $this->db->get_where('tbl_pengguna', $data);
	}	
	
	function getPenggunaDetail($username, $password, $role) {
		if($role == 1){
			$query = $this->db->select('*')
			->from('tbl_pengguna as t1')
			->where('t1.username', $username)
			->where('t1.password', $password)
			->join('tbl_pelamar as t2', 't1.id_pengguna = t2.id_pengguna', 'LEFT')
			->get();
			
			return $query->result_array();
		}elseif($role == 2){
			$query = $this->db->select('*')
			->from('tbl_pengguna as t1')
			->where('t1.username', $username)
			->where('t1.password', $password)
			->join('tbl_perusahaan as t2', 't1.id_pengguna = t2.id_pengguna', 'LEFT')
			->get();
			
			return $query->result_array();
		}elseif($role == 0){
			$query = $this->db->select('*')
			->from('tbl_pengguna as t1')
			->where('t1.username', $username)
			->where('t1.password', $password)
			->get();
			
			return $query->result_array();
		}
	}	

	public function registrasiPengguna($id_pengguna, $username, $email, $password, $role){
		$data_pengguna = array(
			'id_pengguna' => $id_pengguna,
			'username' => $username,
			'email' => $email,
			'password' => $password,
			'role' => $role,
			'is_active' => 'true'
		);
		return $this->db->insert('tbl_pengguna', $data_pengguna);
	}

	public function registrasiPerusahaan($id_perusahaan, $id_pengguna, $nama, $alamat, $telepon, $jenis){
		$data_perusahaan = array(
			'id_perusahaan' =>$id_perusahaan,
			'id_pengguna' => $id_pengguna,
			'nama' => $nama,
			'alamat' => $alamat,
			'telepon' => $telepon,
			'jenis' => $jenis
		);
		
		return $this->db->insert('tbl_perusahaan', $data_perusahaan);
	}

	public function registrasiPelamar($id_pelamar, $id_pengguna, $nama, $jk, $alamat, $tempat_lahir, $tanggal_lahir, $agama, $status){
		$data_pekerja = array(
			'id_pelamar' => $id_pelamar,
			'id_pengguna' => $id_pengguna,
			'nama' => $nama,
			'jk' => $jk,
			'alamat' => $alamat,
			'tempat_lahir' => $tempat_lahir,
			'tanggal_lahir' => $tanggal_lahir,
			'agama' => $agama,
			'status' => $status
		);
		return $this->db->insert('tbl_pelamar', $data_pekerja);
	}

	public function getIdPengguna(){
		$this->db->select('*');
		$this->db->from('tbl_pengguna');
		$this->db->order_by('id_pengguna', "desc"); 
		$this->db->limit(1);

		$res_log = $this->db->get();
		if($res_log->result()){
			$result_log = $res_log->result();

			$char_id = substr($result_log[0]->id_pengguna, 0, 3);
			$id = substr($result_log[0]->id_pengguna, 3);

			$current_last_id = $id + 1;

			if ($current_last_id < 10) {
				$temp_id = 0;
				$last_id = $char_id . '00' . $current_last_id;
			} elseif ($current_last_id < 100) {
				$temp_id = 0;
				$last_id = $char_id . '0' . $current_last_id;
			} elseif ($current_last_id < 1000) {
				$temp_id = 0;
				$last_id = $char_id . '' . $current_last_id;
			}

			return $last_id;
		}else{
			return 'PGN001';
		}
	}
	public function getIdPelamar(){
		$this->db->select('*');
		$this->db->from('tbl_pelamar');
		$this->db->order_by('id_pelamar', "desc"); 
		$this->db->limit(1);

		$res_log = $this->db->get();
		if($res_log->result()){
			$result_log = $res_log->result();

			$char_id = substr($result_log[0]->id_pelamar, 0, 3);
			$id = substr($result_log[0]->id_pelamar, 3);

			$current_last_id = $id + 1;

			if ($current_last_id < 10) {
				$temp_id = 0;
				$last_id = $char_id . '00' . $current_last_id;
			} elseif ($current_last_id < 100) {
				$temp_id = 0;
				$last_id = $char_id . '0' . $current_last_id;
			} elseif ($current_last_id < 1000) {
				$temp_id = 0;
				$last_id = $char_id . '' . $current_last_id;
			}

			return $last_id;
		}else{
			return 'PEL001';
		}
	}
	public function getIdPerusahaan(){
		$this->db->select('*');
		$this->db->from('tbl_perusahaan');
		$this->db->order_by('id_perusahaan', "desc"); 
		$this->db->limit(1);

		$res_log = $this->db->get();
		if($res_log->result()){
			$result_log = $res_log->result();

			$char_id = substr($result_log[0]->id_perusahaan, 0, 3);
			$id = substr($result_log[0]->id_perusahaan, 3);

			$current_last_id = $id + 1;

			if ($current_last_id < 10) {
				$temp_id = 0;
				$last_id = $char_id . '00' . $current_last_id;
			} elseif ($current_last_id < 100) {
				$temp_id = 0;
				$last_id = $char_id . '0' . $current_last_id;
			} elseif ($current_last_id < 1000) {
				$temp_id = 0;
				$last_id = $char_id . '' . $current_last_id;
			}

			return $last_id;
		}else{
			return 'PER001';
		}
	}

	public function saveFoto($base64Image, $nama){
		$data = str_replace('data:image/jpeg;base64,', '', $base64Image);
		
		$data = str_replace(' ', '+', $data);
		
		$data = base64_decode($data);
		
		$file = '../static/contents/' . $nama . '.jpeg';
		
		$success = file_put_contents($file, $data);
	}	
	
	public function saveSurat($base64Image, $nama){
		$data = str_replace('data:image/jpeg;base64,', '', $base64Image);
		
		$data = str_replace(' ', '+', $data);
		
		$data = base64_decode($data);
		
		$file = '../static/contents/surat/' . $nama . '.jpeg';
		
		$success = file_put_contents($file, $data);
	}
}