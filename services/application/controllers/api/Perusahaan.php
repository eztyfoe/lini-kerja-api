<?php

defined('BASEPATH') OR exit('No direct script access allowed');
use \Firebase\JWT\JWT;

class Perusahaan extends BD_Controller {

    function __construct($config = 'rest') {
        parent::__construct($config);
        // $this->auth();
    }

    var $tbl_pengguna = 'tbl_pengguna';
    var $tbl_lamaran = 'tbl_lamaran';
    var $tbl_pelamar = 'tbl_pelamar';
    var $tbl_perusahaan = 'tbl_perusahaan';
    var $tbl_lowongan = 'tbl_lowongan';
    var $tbl_sertifikat_keahlian = 'tbl_sertifikat_keahlian';

    var $tbl_sertifikat_perusahaan = 'tbl_sertifikat_perusahaan';
    function index_get() {
        $pil = $this->get('pil');
        if($pil === '1'){
            $id = $this->get('id_perusahaan');
            if ($id == '') {
                $query = $this->db->select('*')
                ->from('tbl_perusahaan as t1')
                ->join('tbl_jenis_perusahaan as t2', 't1.jenis = t2.id_jenis_perusahaan', 'LEFT')
                ->order_by("RAND()")
                ->get();
                
                $perusahaan = $query->result_array();
            } else {
                $query = $this->db->select('*')
                ->from('tbl_perusahaan as t1')
                ->join('tbl_jenis_perusahaan as t2', 't1.jenis = t2.id_jenis_perusahaan', 'LEFT')
                ->where('t1.id_perusahaan', $id)
                ->get();
                
                $perusahaan = $query->result_array();
                $perusahaan = $perusahaan;
            }
            $this->response($perusahaan, 200);
        }elseif($pil === '2'){
            $search = $this->get('search');
            $perusahaan = $this->M_perusahaan->search($search);
            
            $invalid = ['status' => 'not found']; //Respon if not data found

            if(count($perusahaan) == 0){ 
                $this->set_response($invalid, 404); //This is the respon if failed   
            }else{
                $this->response($perusahaan, 200);     
            }
        } elseif($pil === '3'){
            $query = $this->db->select('*')
            ->from('tbl_jenis_perusahaan as t1')
            ->get();
            
            $jenis = $query->result_array();
            $invalid = ['status' => 'not found']; //Respon if not data found

            if(count($jenis) == 0){ 
                $this->set_response($invalid, 404); //This is the respon if failed   
            }else{
                $this->response($jenis, 200);     
            }  
        }elseif($pil === '4'){
            $query = $this->db->select('*')
                ->from('tbl_perusahaan as t1')
                ->join('tbl_jenis_perusahaan as t2', 't1.jenis = t2.id_jenis_perusahaan', 'LEFT')
                ->order_by("RAND()")
                ->limit(3)
                ->get();
                
            $perusahaan = $query->result_array();
            $invalid = ['status' => 'not found']; //Respon if not data found

            if(count($perusahaan) == 0){ 
                $this->set_response($invalid, 404); //This is the respon if failed   
            }else{
                $this->response($perusahaan, 200);     
            }  
        }elseif($pil === '5'){
            $id = $this->get('id_jenis_perusahaan');
            $perusahaan = $this->M_perusahaan->getByJenis($id);
  
            $invalid = ['status' => 'not found']; //Respon if not data found

            if(count($perusahaan) == 0){ 
                $this->set_response($invalid, 404); //This is the respon if failed   
            }else{
                $this->response($perusahaan, 200);     
            }   
        }elseif($pil === '6'){
            $id = $this->get('id_lowongan');
            $lowongan = $this->M_perusahaan->getPelamarByIDLowongan($id);

            $invalid = ['status' => 'not found']; //Respon if not data found

            if(count($lowongan) == 0){ 
                $this->response($lowongan, 200);  
            }else{
                $this->response($lowongan, 200);     
            }      
        }elseif($pil === '7'){
            $id_lowongan = $this->get('id_lowongan');
            $perusahaan = $this->M_perusahaan->getPerusahaanByIdLowongan($id_lowongan);
            
            $invalid = ['status' => 'not found']; //Respon if not data found

            if(count($perusahaan) == 0){ 
                $this->set_response($invalid, 404); //This is the respon if failed   
            }else{
                $this->response($perusahaan, 200);     
            }
        } elseif($pil === '8'){
            $query = $this->db->select('t1.id_perusahaan, t1.nama')
            ->from('tbl_perusahaan as t1')
            ->get();
            
            $perusahaan = $query->result_array();
            $this->response($perusahaan, 200);
        } elseif($pil === '9'){
            $query = $this->db->select('*')
            ->from('tbl_jenis_pekerjaan as t1')
            ->get();
            
            $jenis = $query->result_array();
            $invalid = ['status' => 'not found']; //Respon if not data found

            if(count($jenis) == 0){ 
                $this->set_response($invalid, 404); //This is the respon if failed   
            }else{
                $this->response($jenis, 200);     
            }  
        } elseif($pil === '10'){
            $id_perusahaan = $this->get('id_perusahaan');
            $perusahaan = $this->M_perusahaan->getLowonganByIdPerusahaan($id_perusahaan);
            
            $invalid = ['status' => 'not found']; //Respon if not data found

            if(count($perusahaan) == 0){ 
                $this->set_response($invalid, 404); //This is the respon if failed   
            }else{
                $this->response($perusahaan, 200);     
            }
        } elseif($pil === '11'){
                $query = $this->db->select('*')
                ->from('tbl_lowongan as t1')
                ->get();
                
                $lowongan = $query->result_array();
                
            if(count($lowongan) == 0){ 
                $this->set_response($invalid, 404); //This is the respon if failed   
            }else{
                $this->response($lowongan, 200);     
            }
        } elseif($pil === '12'){
            $id_perusahaan = $this->get('id_perusahaan');
            $perusahaan = $this->M_perusahaan->getPerusahaanById($id_perusahaan);
            
            $invalid = ['status' => 'not found']; //Respon if not data found

            if(count($perusahaan) == 0){ 
                $this->set_response($invalid, 404); //This is the respon if failed   
            }else{
                $this->response($perusahaan, 200);     
            }
            
        } elseif($pil === '13'){
            $id_perusahaan = $this->get('id_perusahaan');
            $perusahaan = $this->M_perusahaan->getPerusahaanById($id_perusahaan);
            $perusahaan = $perusahaan[0];
            $valid = ['status' => 'found'];
            $invalid = ['status' => 'not found']; //Respon if not data found

            if(in_array("-",$perusahaan)){ 
                    $this->set_response($valid, 404); //This is the respon if failed   
            }
            elseif(in_array(null,$perusahaan)){ 
                    $this->set_response($valid, 404); //This is the respon if failed   
            }else{
                $this->response($invalid, 200);     
            }
        }else if($pil === '14'){
            $id_perusahaan = $this->get('id_perusahaan');
            $count = $this->M_perusahaan->getCountPelamar($id_perusahaan);
            
            $invalid = ['status' => 'not found']; //Respon if not data found

            if(count($count) == 0){ 
                $this->set_response($invalid, 404); //This is the respon if failed   
            }else{
                $this->response($count, 200);
            }
        }else if($pil === '15'){
            $id_perusahaan = $this->get('id_perusahaan');
            $count = $this->M_perusahaan->getCountLowongan($id_perusahaan);
            
            $invalid = ['status' => 'not found']; //Respon if not data found

            if(count($count) == 0){ 
                $this->set_response($invalid, 404); //This is the respon if failed   
            }else{
                $this->response($count, 200);
            }
        }  elseif($pil === '16'){
            $id_perusahaan = $this->get('id_perusahaan');
            $perusahaan = $this->M_perusahaan->getLowonganTopFive($id_perusahaan);
            
            $invalid = ['status' => 'not found']; //Respon if not data found

            if(count($perusahaan) == 0){ 
                $this->response($perusahaan, 200);  
            }else{
                $this->response($perusahaan, 200);     
            }
        } elseif($pil === '17'){
            $id_perusahaan = $this->get('id_perusahaan');
            $perusahaan = $this->M_perusahaan->getPelamarTopFive($id_perusahaan);
            
            $invalid = ['status' => 'not found']; //Respon if not data found

            if(count($perusahaan) == 0){ 
                $this->response($perusahaan, 200);  
            }else{
                $this->response($perusahaan, 200);     
            }
        }elseif($pil === '18'){
            $id_lowongan = $this->get('id_lowongan');
            $perusahaan = $this->M_perusahaan->getLamaranByStatus($id_lowongan);
            
            $invalid = ['status' => 'not found']; //Respon if not data found

            if(count($perusahaan) == 0){ 
                $this->set_response($invalid, 404); //This is the respon if failed   
            }else{
                $this->response($perusahaan, 200);     
            }
        }
    }

    function index_post() {
        $pil = $this->post('pil');
        if($pil === '1'){
            $id_lowongan = $this->M_perusahaan->getIdLowongan();
            $id_perusahaan = $this->post('id_perusahaan');
            $nama = $this->post('nama');
            $deskripsi = $this->post('deskripsi');
            $gaji = $this->post('gaji');
            $jenis_pekerjaan = $this->post('jenis_pekerjaan');
            $limit_time = $this->post('limit_time');

            $data_lowongan = array(
                'id_lowongan' => $id_lowongan,
                'id_perusahaan' => $id_perusahaan,
                'nama' => $nama,
                'deskripsi' => $deskripsi,
                'gaji' => $gaji,
                'jenis_pekerjaan' => $jenis_pekerjaan,
                'limit_time' => $limit_time,
            );

            $insert_lowongan = $this->Model_main->addRecord($this->tbl_lowongan, $data_lowongan);

            $success = array(
                'status' => 'success',
                'data_lowongan' => $data_lowongan
            );

            $fail = array(
                'status' => 'failed'
            );
            if ($insert_lowongan){
                $this->response($success, 201);
            }else{
                $this->response($fail, 502);
            }
        }
        else if($pil === '2'){
            $id_perusahaan = $this->post('id_perusahaan');
            $nama = $this->post('nama');
            $alamat = $this->post('alamat');
            $telepon = $this->post('telepon');
            $jenis = $this->post('jenis');
            $email = $this->post('email');
            //$foto = $this->post('foto');
        
            $id = array(
                'id_perusahaan' => $id_perusahaan,
            );
            
            $id_pengguna = array(
                'id_pengguna' =>  $this->M_perusahaan->getPenggunaIDByIDPerusahaan($id_perusahaan),
            );

            
            $data_perusahaan = array(
                'nama' => $nama,
                'alamat' => $alamat,
                'telepon' => $telepon,
                'jenis' => $jenis,
                //'foto' => $foto
            );
            
            $data_pengguna = array(
                'email' => $email,
            );


            $update_perusahaan = $this->Model_main->updateRecord($this->tbl_perusahaan, $data_perusahaan, $id);
            
            $update_pengguna = $this->Model_main->updateRecord($this->tbl_pengguna, $data_pengguna, $id_pengguna);

            $success = array(
                'status' => 'success',
                'data_perusahaan' => $data_perusahaan,
                'data_pengguna' => $data_pengguna
            );

            $fail = array(
                'status' => 'failed'
            );
            if ($update_perusahaan && $update_pengguna){
                $this->response($success, 201);
            }else{
                $this->response($fail, 502);
            }
        }
        else if($pil === '3'){
            $id_lowongan = $this->post('id_lowongan');
            $nama = $this->post('nama');
            $deskripsi = $this->post('deskripsi');
            $gaji = $this->post('gaji');
            $jenis_pekerjaan = $this->post('jenis_pekerjaan');
            $limit_time = $this->post('limit_time');

            $data_lowongan = array(
                'nama' => $nama,
                'deskripsi' => $deskripsi,
                'gaji' => $gaji,
                'jenis_pekerjaan' => $jenis_pekerjaan,
                'limit_time' => $limit_time,
            );
            
            $id = array(
                'id_lowongan' => $id_lowongan,
            );

            $update_lowongan = $this->Model_main->updateRecord($this->tbl_lowongan, $data_lowongan, $id);

            $success = array(
                'status' => 'success',
                'data_lowongan' => $data_lowongan
            );

            $fail = array(
                'status' => 'failed'
            );
            if ($update_lowongan){
                $this->response($success, 201);
            }else{
                $this->response($fail, 502);
            }
        }if($pil === '4'){
            $id_lowongan = $this->post('id_lowongan');
            $id_pelamar = $this->post('id_pelamar');
            $status = $this->post('status');

            if(empty($id_pelamar)){
                $id = array(
                    'id_lowongan' => $id_lowongan,
                );
                
                $data_lamaran = array(
                    'status' => 3
                );
                
                $update_pelamar = $this->Model_main->updateRecord($this->tbl_lamaran, $data_lamaran,$id);
    
                $success = array(
                    'status' => 'success',
                );
    
                $fail = array(
                    'status' => 'failed'
                );
                if ($update_pelamar){
                    $this->response($success, 201);
                }else{
                    $this->response($fail, 502);
                }
                
            }else{
                $id = array(
                    'id_lowongan' => $id_lowongan,
                    'id_pelamar' =>  $id_pelamar
                );
                
                $data_lamaran = array(
                    'status' => $status
                );
                
                $update_pelamar = $this->Model_main->updateRecord($this->tbl_lamaran, $data_lamaran,$id);
    
                $success = array(
                    'status' => 'success',
                );
    
                $fail = array(
                    'status' => 'failed'
                );
                if ($update_pelamar){
                    $this->response($success, 201);
                }else{
                    $this->response($fail, 502);
                }
            }
        }
        else if($pil === '5'){
            $now = date("Ymd_his");
            $foto = 'data:image/jpeg;base64,'.$this->post('foto');
            $id_perusahaan = $this->post('id_perusahaan');
            $tabel = 'tbl_perusahaan';
            
            $id = array(
                'id_perusahaan' => $id_perusahaan,
            );

            
            $data_perusahaan = array(
                'sertifikat' => $now,
                'is_verified' => 0
            );

            $update_pengguna = $this->Model_main->updateRecord($tabel, $data_perusahaan, $id);

            $success = array(
                'status' => 'success',
            );

            $fail = array(
                'status' => 'failed'
            );
            
            if ($update_pengguna){
                $this->M_user->saveSurat($foto, $now);
                $this->response($success, 201);
            }else{
                $this->response($fail, 502);
            }
        }else if($pil === '6'){
            $id_sertifikat_perusahaan = $this->M_perusahaan->getIdSertifikasiPerusahaan();
            $id_perusahaan = $this->post('id_perusahaan');
            $now = date("Ymd_his");
            $foto = 'data:image/jpeg;base64,'.$this->post('foto');
            $id_pelamar = $this->post('id_pelamar');
            $nama_pengalaman_kerja = $this->post('nama_pengalaman_kerja');
            $waktu_pengalaman_kerja = $this->post('waktu_pengalaman_kerja');
            
            $data_sertifikat = array(
                'id_sertifikat_perusahaan' => $id_sertifikat_perusahaan,
                'id_perusahaan' => $id_perusahaan,
                'foto' => $now,
                'is_verified' => 0
            );

            $insert_sertifikat = $this->Model_main->addRecord($this->tbl_sertifikat_perusahaan, $data_sertifikat);

            $success = array(
                'status' => 'success',
                'data_sertifikat' => $data_sertifikat
            );

            $fail = array(
                'status' => 'failed'
            );
            if ($insert_sertifikat){
                $this->M_user->saveSurat($foto, $now);
                $this->response($success, 201);
            }else{
                $this->response($fail, 502);
            }
        }
    }

    function index_put() {
    }

}
