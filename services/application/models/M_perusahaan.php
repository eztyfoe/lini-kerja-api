<?php if(!defined('BASEPATH')) exit('No direct script allowed');

class M_perusahaan extends CI_Model{

	function getByJenis($id_jenis) {
        if($id_jenis == ''){
            $query = $this->db->select('*')
            ->from('tbl_perusahaan as t1')
            ->join('tbl_jenis_perusahaan as t2', 't1.jenis = t2.id_jenis_perusahaan', 'LEFT')
            ->get();
            
            return $query->result_array();
        }else{
            $query = $this->db->select('*')
            ->from('tbl_perusahaan as t1')
            ->where('t2.id_jenis_perusahaan', $id_jenis)
            ->join('tbl_jenis_perusahaan as t2', 't1.jenis = t2.id_jenis_perusahaan', 'LEFT')
            ->get();
            
            return $query->result_array();
        }
    }	
    
	public function getPerusahaanById($id_perusahaan){
		$query = $this->db->select('*')
		->from('tbl_perusahaan as t1')
		->where('t1.id_perusahaan', $id_perusahaan)
            ->join('tbl_jenis_perusahaan as t2', 't1.jenis = t2.id_jenis_perusahaan', 'LEFT')
		->get();

        return $query->result_array();
	}
	
    function search($search) {
        $query = $this->db->select('*,t3.nama as nama,t1.nama as nama_perusahaan, t4.nama as nama_jenis_pekerjaan')
        ->from('tbl_lowongan as t3')
        ->like('t1.nama', $search, 'both')
        ->or_like('t3.nama', $search, 'both')
        ->or_like('t2.jenis_perusahaan', $search, 'both')
        ->join('tbl_perusahaan as t1', 't1.id_perusahaan = t3.id_perusahaan', 'LEFT')
        ->join('tbl_jenis_perusahaan as t2', 't1.jenis = t2.id_jenis_perusahaan', 'LEFT')
        ->join('tbl_jenis_pekerjaan as t4', 't3.jenis_pekerjaan = t4.id_jenis_pekerjaan', 'LEFT')
        ->get();
        
        return $query->result_array();
    }	
    
    function getPerusahaanByIdLowongan($id_lowongan) {
        $query = $this->db->select('*,t3.nama as nama,t1.nama as nama_perusahaan, t4.nama as nama_jenis_pekerjaan')
        ->from('tbl_lowongan as t3')
        ->where('t3.id_lowongan', $id_lowongan)
        ->join('tbl_perusahaan as t1', 't1.id_perusahaan = t3.id_perusahaan', 'LEFT')
        ->join('tbl_jenis_perusahaan as t2', 't1.jenis = t2.id_jenis_perusahaan', 'LEFT')
        ->join('tbl_jenis_pekerjaan as t4', 't3.jenis_pekerjaan = t4.id_jenis_pekerjaan', 'LEFT')
        ->get();
        
        return $query->result_array();
	}	
	
    function getLowonganByIdPerusahaan($id_perusahaan) {
        $query = $this->db->select('*,t3.nama as nama,t1.nama as nama_perusahaan, t4.nama as nama_jenis_pekerjaan')
        ->from('tbl_lowongan as t3')
        ->where('t1.id_perusahaan', $id_perusahaan)
        ->join('tbl_perusahaan as t1', 't1.id_perusahaan = t3.id_perusahaan', 'LEFT')
        ->join('tbl_jenis_perusahaan as t2', 't1.jenis = t2.id_jenis_perusahaan', 'LEFT')
        ->join('tbl_jenis_pekerjaan as t4', 't3.jenis_pekerjaan = t4.id_jenis_pekerjaan', 'LEFT')
        ->get();
        
        return $query->result_array();
	}	

    function getPelamarByIDLowongan($id_lowongan) {
        $query = $this->db->select('t1.id_lamaran,t1.id_lowongan,t1.id_pelamar,t1.status as "status_lamaran", t2.*,t3.*')
        ->from('tbl_lamaran as t1')
        ->where('t1.id_lowongan', $id_lowongan, 'both')
        ->join('tbl_pelamar as t2', 't1.id_pelamar = t2.id_pelamar', 'LEFT')
        ->join('tbl_alternatif as t3', 't2.id_pelamar = t3.id_pelamar', 'LEFT')
        ->get();
        
        return $query->result_array();
	}
	
	function getDSS($id_lowongan, $nilai, $sertifikat, $pendidikan, $jarak, $pengalaman){
	    
        $query = $this->db->select('t1.*')
        ->from('tbl_alternatif as t1')
        ->where('t2.id_lowongan', $id_lowongan)
        ->join('tbl_lamaran as t2', 't1.id_pelamar = t2.id_pelamar', 'LEFT')
        ->get();
        
        $alternatif = $query->result_array();
        
        //Normalisasi R
        $tempNilai = 1;
        $tempSertifikat = 1;
        $tempPendidikan = 1;
        $tempJarak = 1;
        $tempPengalaman = 1;
        
        for($i = 0; $i < count($alternatif); $i++)
        {
            $tempNilai += $alternatif[$i]["nilai"] * $alternatif[$i]["nilai"];
            $tempSertifikat += $alternatif[$i]["sertifikat_keahlian"] * $alternatif[$i]["sertifikat_keahlian"];
            $tempPendidikan += $alternatif[$i]["pendidikan_terakhir"] * $alternatif[$i]["pendidikan_terakhir"];
            $tempJarak += $alternatif[$i]["jarak"] * $alternatif[$i]["jarak"];
            $tempPengalaman += $alternatif[$i]["pengalaman_kerja"] * $alternatif[$i]["pengalaman_kerja"];
        }
        
        $tempNilai = sqrt($tempNilai);
        $tempSertifikat = sqrt($tempSertifikat);
        $tempPendidikan = sqrt($tempPendidikan);
        $tempJarak = sqrt($tempJarak);
        $tempPengalaman = sqrt($tempPengalaman);
        
        for($i = 0; $i < count($alternatif); $i++)
        {
            $alternatif[$i]["nilai"] = $alternatif[$i]["nilai"]+1 / $tempNilai;
            $alternatif[$i]["sertifikat_keahlian"] = $alternatif[$i]["sertifikat_keahlian"]+1 / $tempSertifikat;
            $alternatif[$i]["pendidikan_terakhir"] = $alternatif[$i]["pendidikan_terakhir"]+1 / $tempPendidikan;
            $alternatif[$i]["jarak"] = $alternatif[$i]["jarak"]+1 / $tempJarak;
            $alternatif[$i]["pengalaman_kerja"] = $alternatif[$i]["pengalaman_kerja"]+1 / $tempPengalaman;
        }
        
        //Normalisasi Y
        for($i = 0; $i < count($alternatif); $i++)
        {
            $alternatif[$i]["nilai"] = $alternatif[$i]["nilai"] * $nilai;
            $alternatif[$i]["sertifikat_keahlian"] = $alternatif[$i]["sertifikat_keahlian"] * $sertifikat;
            $alternatif[$i]["pendidikan_terakhir"] = $alternatif[$i]["pendidikan_terakhir"] * $pendidikan;
            $alternatif[$i]["jarak"] = $alternatif[$i]["jarak"] * $jarak;
            $alternatif[$i]["pengalaman_kerja"] = $alternatif[$i]["pengalaman_kerja"] * $pengalaman;
        }
        
        //Solusi Ideal
        $positif = array(max(array_column($alternatif, 'nilai')),max(array_column($alternatif, 'sertifikat_keahlian')),max(array_column($alternatif, 'pendidikan_terakhir')),min(array_column($alternatif, 'jarak')),max(array_column($alternatif, 'pengalaman_kerja')));
        $negatif = array(min(array_column($alternatif, 'nilai')),min(array_column($alternatif, 'sertifikat_keahlian')),min(array_column($alternatif, 'pendidikan_terakhir')),max(array_column($alternatif, 'jarak')),min(array_column($alternatif, 'pengalaman_kerja')));
        
        //Mencari Jarak (D)
        $jarak_positif = array();
        $jarak_negatif = array();

        for($i = 0; $i < count($alternatif); $i++)
        {
            $jarak_positif[$i] = 
            sqrt(
                pow($positif[0] - $alternatif[$i]["nilai"], 2) + 
                pow($positif[1] - $alternatif[$i]["sertifikat_keahlian"], 2) +
                pow($positif[2] - $alternatif[$i]["pendidikan_terakhir"], 2) +
                pow($positif[3] - $alternatif[$i]["jarak"], 2) +
                pow($positif[4] - $alternatif[$i]["pengalaman_kerja"], 2)
            );
            
            $jarak_negatif[$i] = 
            sqrt(
                pow($negatif[0] - $alternatif[$i]["nilai"], 2) + 
                pow($negatif[1] - $alternatif[$i]["sertifikat_keahlian"], 2) +
                pow($negatif[2] - $alternatif[$i]["pendidikan_terakhir"], 2) +
                pow($negatif[3] - $alternatif[$i]["jarak"], 2) +
                pow($negatif[4] - $alternatif[$i]["pengalaman_kerja"], 2)
            );
        }
        
        //Mencari nilai alternatif
	    $result = array();

        for($i = 0; $i < count($alternatif); $i++)
        {
            $result[$i]["id_alternatif"] = $alternatif[$i]["id_alternatif"];
            $result[$i]["nilai"] = $jarak_negatif[$i] / ($jarak_negatif[$i] + $jarak_positif[$i]);
        }
        return $result;
	}
	
	public function getResultDSS($id_dss){
        $query = $this->db->select('t1.id_dss, t1.id_lowongan, t4.nama, t3.nilai, t3.sertifikat_keahlian, t3.pendidikan_terakhir, t3.jarak, t3.pengalaman_kerja, t2.nilai as hasil, t5.*')
        ->from('tbl_dss as t1')
        ->where('t1.id_dss', $id_dss)
        ->join('tbl_dss_detail as t2', 't1.id_dss = t2.id_dss', 'LEFT')
        ->join('tbl_alternatif as t3', 't2.id_alternatif = t3.id_alternatif', 'LEFT')
        ->join('tbl_pelamar as t4', 't3.id_pelamar = t4.id_pelamar', 'LEFT')
        ->join('tbl_lamaran as t5', 't1.id_lowongan = t5.id_lowongan AND t4.id_pelamar = t5.id_pelamar', 'Left')
		->order_by('t2.nilai', "desc")
        ->get();
        
        return $query->result_array();
	}
	
	public function insertBobot($id_bobot, $nilai, $sertifikat, $pendidikan, $jarak, $pengalaman){
		$data_bobot = array(
    		'id_bobot' => $id_bobot,
    		'nilai' => $nilai,
    		'sertifikat' => $sertifikat,
    		'pendidikan' => $pendidikan,
    		'jarak' => $jarak,
    		'pengalaman' => $pengalaman
    	);
    	return $this->db->insert('tbl_bobot', $data_bobot);
	}
	
	public function insertDSS($id_dss, $id_lowongan, $id_bobot){
		$data_dss = array(
    		'id_dss' => $id_dss,
    		'id_lowongan' => $id_lowongan,
    		'id_bobot' => $id_bobot
    	);
    	return $this->db->insert('tbl_dss', $data_dss);
	}
	
	public function insertDSSDetail($id_dss, $id_alternatif, $nilai){
		$data_dss_detail = array(
    		'id_dss' => $id_dss,
    		'id_alternatif' => $id_alternatif,
    		'nilai' => $nilai
    	);
    	return $this->db->insert('tbl_dss_detail', $data_dss_detail);
	}
	
	public function getIdSertifikasiPerusahaan(){
		$this->db->select('*');
		$this->db->from('tbl_sertifikat_perusahaan');
		$this->db->order_by('id_sertifikat_perusahaan', "desc"); 
		$this->db->limit(1);

		$res_log = $this->db->get();
		if($res_log->result()){
			$result_log = $res_log->result();

			$char_id = substr($result_log[0]->id_sertifikat_perusahaan, 0, 3);
			$id = substr($result_log[0]->id_sertifikat_perusahaan, 3);

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
			return 'SEP001';
		}
	}
	
	public function getIdLowongan(){
		$this->db->select('*');
		$this->db->from('tbl_lowongan');
		$this->db->order_by('id_lowongan', "desc"); 
		$this->db->limit(1);

		$res_log = $this->db->get();
		if($res_log->result()){
			$result_log = $res_log->result();

			$char_id = substr($result_log[0]->id_lowongan, 0, 3);
			$id = substr($result_log[0]->id_lowongan, 3);

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
			return 'LOW001';
		}
	}
	
	public function getIdBobot(){
		$this->db->select('*');
		$this->db->from('tbl_bobot');
		$this->db->order_by('id_bobot', "desc"); 
		$this->db->limit(1);

		$res_log = $this->db->get();
		if($res_log->result()){
			$result_log = $res_log->result();

			$char_id = substr($result_log[0]->id_bobot, 0, 3);
			$id = substr($result_log[0]->id_bobot, 3);

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
			return 'BOT001';
		}
	}
	
	public function getIdDss(){
		$this->db->select('*');
		$this->db->from('tbl_dss');
		$this->db->order_by('id_dss', "desc"); 
		$this->db->limit(1);

		$res_log = $this->db->get();
		if($res_log->result()){
			$result_log = $res_log->result();

			$char_id = substr($result_log[0]->id_dss, 0, 3);
			$id = substr($result_log[0]->id_dss, 3);

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
			return 'DSS001';
		}
	}
	
	
	public function getCountPelamar($id_perusahaan){
		$query = $this->db->select('COUNT(*) as "pelamar"')
		->from('tbl_lamaran as t1')
        ->join('tbl_lowongan as t2', 't1.id_lowongan = t2.id_lowongan', 'LEFT')
		->where('t2.id_perusahaan', $id_perusahaan)
		->get();
        
        $result_log = $query->result();
        $count_pelamar = $result_log[0]->pelamar;
        
        $result = array(
            "pelamar" => $count_pelamar,
            );
            
        return array($result);
	}
	
	public function getCountLowongan($id_perusahaan){
		$query = $this->db->select('COUNT(*) as "lowongan"')
		->from('tbl_lowongan')
		->where('id_perusahaan', $id_perusahaan)
		->get();
        
        $result_log = $query->result();
        $count_lowongan = $result_log[0]->lowongan;
        
        $result = array(
            "lowongan" => $count_lowongan,
            );
            
        return array($result);
	}
	
	function getLowonganTopFive($id_perusahaan){
		$query = $this->db->select('*')
		->from('tbl_lowongan as t1')
		->where('t1.id_perusahaan', $id_perusahaan)
		->limit(5)
		->get();

        return $query->result_array();
	}
	
	public function getPelamarTopFive($id_perusahaan){
		$query = $this->db->select('t1.*, t3.nama as "nama_lowongan"')
		->from('tbl_pelamar as t1')
        ->join('tbl_lamaran as t2', 't2.id_pelamar = t1.id_pelamar', 'LEFT')
        ->join('tbl_lowongan as t3', 't2.id_lowongan = t3.id_lowongan', 'LEFT')
		->where('t3.id_perusahaan', $id_perusahaan)
		->where('t2.status', 1)
		->order_by('t2.id_lamaran', "desc")
		->limit(5)
		->get();

        return $query->result_array();
	}
	
    function getPenggunaIDByIDPerusahaan($id_perusahaan) {
        $query = $this->db->select('t2.id_pengguna')
        ->from('tbl_perusahaan as t1')
        ->where('t1.id_perusahaan', $id_perusahaan, 'both')
        ->join('tbl_pengguna as t2', 't1.id_pengguna = t2.id_pengguna', 'LEFT')
        ->get();
        
        $result_log = $query->result();
        $id_pengguna = $result_log[0]->id_pengguna;
        
        
        return $id_pengguna;
	}
	
	function getLamaranByStatus($id_lowongan) {
	    $this->db->select('*');
		$this->db->from('tbl_dss');
		$this->db->where('id_lowongan',$id_lowongan);
		$this->db->order_by('id_dss', "desc"); 
		$this->db->limit(1);
		
		$res_log = $this->db->get();
		
			$result_log = $res_log->result();

        $query = $this->db->select('t1.id_dss, t1.id_lowongan, t4.nama, t3.nilai, t3.sertifikat_keahlian, t3.pendidikan_terakhir, t3.jarak, t3.pengalaman_kerja, t2.nilai as hasil, t5.*')
        ->from('tbl_dss as t1')
        ->where('t1.id_lowongan', $id_lowongan)
        ->where('t1.id_dss', $result_log[0]->id_dss)
        ->join('tbl_dss_detail as t2', 't1.id_dss = t2.id_dss', 'LEFT')
        ->join('tbl_alternatif as t3', 't2.id_alternatif = t3.id_alternatif', 'LEFT')
        ->join('tbl_pelamar as t4', 't3.id_pelamar = t4.id_pelamar', 'LEFT')
        ->join('tbl_lamaran as t5', 't1.id_lowongan = t5.id_lowongan AND t4.id_pelamar = t5.id_pelamar', 'Left')
		->order_by('t2.nilai', "desc")
        ->get();
        
        return $query->result_array();
	}
	
	public function getToken($email){
		$this->db->select('*');
		$this->db->from('tbl_pengguna');
		$this->db->where('email', $email); 
		$this->db->limit(1);

		$res_log = $this->db->get();
		if($res_log->result()){
			$result_log = $res_log->result();


			return $result_log[0]->token;
		}else{
			return 'LOW001';
		}
	}
	
	public function getNama($email){
		$this->db->select('*');
		$this->db->from('tbl_pengguna');
		$this->db->where('email', $email); 
		$this->db->limit(1);

		$res_log = $this->db->get();
		if($res_log->result()){
			$result_log = $res_log->result();


			return $result_log[0]->username;
		}else{
			return 'LOW001';
		}
	}
}