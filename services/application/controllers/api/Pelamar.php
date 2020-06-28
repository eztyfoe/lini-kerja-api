<?php

defined('BASEPATH') OR exit('No direct script access allowed');
use \Firebase\JWT\JWT;

class Pelamar extends BD_Controller {

    function __construct($config = 'rest') {
        parent::__construct($config);
        // $this->auth();
    }

    var $tbl_pengguna = 'tbl_pengguna';
    var $tbl_pelamar = 'tbl_pelamar';
    var $tbl_perusahaan = 'tbl_perusahaan';
    var $tbl_lowongan = 'tbl_lowongan';
    var $tbl_lamaran = 'tbl_lamaran';
    var $tbl_sertifikat_keahlian = 'tbl_sertifikat_keahlian';
    var $tbl_pendidikan_terakhir = 'tbl_pendidikan_terakhir';
    var $tbl_nilai = 'tbl_nilai';
    var $tbl_pengalaman_kerja = 'tbl_pengalaman_kerja';

    function index_get() {
        $pil = $this->get('pil');
        if($pil === '1'){
            $id_lowongan = $this->get('id_lowongan');
            $id_pelamar = $this->get('id_pelamar');
            $perusahaan = $this->M_perusahaan->getPerusahaanByIdLowongan($id_lowongan);
            $get_status = $this->M_pelamar->checkStatus($id_lowongan, $id_pelamar);
            $result_log = $perusahaan['0'];
            if(!empty($get_status)){
                $result_log['status_apply'] = $get_status[0]->nama;
            }else{
                $result_log['status_apply'] = '0';
            }
            $result[0] = $result_log;
            $invalid = ['status' => 'not found']; //Respon if not data found

            if(count($perusahaan) == 0){ 
                $this->set_response($invalid, 404); //This is the respon if failed   
            }else{
                $this->response($result, 200);
            }
        } 
        else if($pil === '2'){
            $id_pelamar = $this->get('id_pelamar');
            $sertifikat = $this->M_pelamar->getSertifikatByIdPelamar($id_pelamar);
            
            $invalid = ['status' => 'not found']; //Respon if not data found

            if(count($sertifikat) == 0){ 
                $this->set_response($invalid, 404); //This is the respon if failed   
            }else{
                $this->response($sertifikat, 200);
            }
        } 
        else if($pil === '3'){
            $id_pelamar = $this->get('id_pelamar');
            $pelamar = $this->M_pelamar->getPelamarById($id_pelamar);
            
            $invalid = ['status' => 'not found']; //Respon if not data found

            if(count($pelamar) == 0){ 
                $this->set_response($invalid, 404); //This is the respon if failed   
            }else{
                $this->response($pelamar, 200);
            }
        } 
        else if($pil === '4'){
            $id_pelamar = $this->get('id_pelamar');
            $pengalaman = $this->M_pelamar->getPengalamanByIdPelamar($id_pelamar);
            
            $invalid = ['status' => 'not found']; //Respon if not data found

            if(count($pengalaman) == 0){ 
                $this->set_response($invalid, 404); //This is the respon if failed   
            }else{
                $this->response($pengalaman, 200);
            }
        } 
        else if($pil === '5'){
            $id_pelamar = $this->get('id_pelamar');
            $nilai = $this->M_pelamar->getNilaiByIdPelamar($id_pelamar);
            
            $invalid = ['status' => 'not found']; //Respon if not data found

            if(count($nilai) == 0){ 
                $this->set_response($invalid, 404); //This is the respon if failed   
            }else{
                $this->response($nilai, 200);
            }
        } 
        else if($pil === '6'){
            $id_pelamar = $this->get('id_pelamar');
            $count = $this->M_pelamar->getCountLamaran($id_pelamar);
            
            $invalid = ['status' => 'not found']; //Respon if not data found

            if(count($count) == 0){ 
                $this->set_response($invalid, 404); //This is the respon if failed   
            }else{
                $this->response($count, 200);
            }
        } 
        else if($pil === '7'){
            $id_pelamar = $this->get('id_pelamar');
            $status = $this->get('status');
            if ($status == '45') {
                $lowongan = $this->M_pelamar->getLowonganByIdPelamarAndDone($id_pelamar, $status);
            } else {
                $lowongan = $this->M_pelamar->getLowonganByIdPelamarAndStatus($id_pelamar, $status);
            }
            

            // $lowongan = $this->M_pelamar->getLowonganByIdPelamarAndStatus($id_pelamar, $status);
            
            $invalid = ['status' => 'not found']; //Respon if not data found

            if(count($lowongan) == 0){ 
                $this->set_response($invalid, 404); //This is the respon if failed   
            }else{
                $this->response($lowongan, 200);
            }
        } 
        else if($pil === '8'){
            $tipe_dokumen = $this->get('tipe_dokumen');
            if($tipe_dokumen == 1){
                $tipe_dokumen = 'tbl_nilai';
            }elseif($tipe_dokumen == 2){
                $tipe_dokumen = 'tbl_pengalaman_kerja';
            }elseif($tipe_dokumen == 3){
                $tipe_dokumen = 'tbl_sertifikat_keahlian';
            }elseif($tipe_dokumen == 4){
                $tipe_dokumen = 'tbl_sertifikat_perusahaan';
            }
            
            $query = "";
            if($tipe_dokumen == 'tbl_sertifikat_perusahaan'){
                $query = $this->db->select('t1.*,t2.id_perusahaan, t2.id_pengguna, t2.nama, t2.alamat, t2.telepon, t2.jenis, t2.foto as foto_perusahaan, t2.sertifikat, t2.is_verified')
                    ->from($tipe_dokumen . ' as t1')
                    ->join('tbl_perusahaan as t2', 't1.id_perusahaan = t2.id_perusahaan', 'LEFT')
                    ->get();
            }
            else{
                $query = $this->db->select('*')
                    ->from($tipe_dokumen . ' as t1')
                    ->join('tbl_pelamar as t2', 't1.id_pelamar = t2.id_pelamar', 'LEFT')
                    ->get();
            }
                
                
            $data = $query->result_array();
            $invalid = ['status' => 'not found']; //Respon if not data found

            if(count($data) == 0){ 
                $this->set_response($invalid, 404); //This is the respon if failed   
            }else{
                $this->response($data, 200);     
            }  
        } 
    }

    function index_post() {
        $pil = $this->post('pil');
        if($pil === '1'){
            $id_lamaran = $this->M_pelamar->getIdLamaran();
            $id_lowongan = $this->post('id_lowongan');
            $id_pelamar = $this->post('id_pelamar');

            $data_lamaran = array(
                'id_lamaran' => $id_lamaran,
                'id_lowongan' => $id_lowongan,
                'id_pelamar' => $id_pelamar,
                'status' => '1'
            );

            $insert_lowongan = $this->Model_main->addRecord($this->tbl_lamaran, $data_lamaran);

            $success = array(
                'status' => 'success',
                'data_lowongan' => $data_lamaran
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
            $id_pelamar = $this->post('id_pelamar');
            $nama = $this->post('nama');
            $jenis_kelamin = $this->post('jenis_kelamin');
            $alamat = $this->post('alamat');
            $tempat_lahir = $this->post('tempat_lahir');
            $tanggal_lahir = $this->post('tanggal_lahir');
            $agama = $this->post('agama');
            $status = $this->post('status');
            //$foto = $this->post('foto');
        
            $id = array(
                'id_pelamar' => $id_pelamar,
            );

            
            $data_pelamar = array(
                'nama' => $nama,
                'jenis_kelamin' => $jenis_kelamin,
                'alamat' => $alamat,
                'tempat_lahir' => $tempat_lahir,
                'tanggal_lahir' => $tanggal_lahir,
                'agama' => $agama,
                'status' => $status,
                //'foto' => $foto
            );

            $update_sertifikat = $this->Model_main->updateRecord($this->tbl_pelamar, $data_pelamar, $id);

            $success = array(
                'status' => 'success',
                'data_pelamar' => $data_pelamar
            );

            $fail = array(
                'status' => 'failed'
            );
            if ($update_sertifikat){
                $this->response($success, 201);
            }else{
                $this->response($fail, 502);
            }
        }
        else if($pil === '3'){
            $id_sertifikat = $this->M_pelamar->getIdSertifikat();
            $now = date("Ymd_his");
            $foto = 'data:image/jpeg;base64,'.$this->post('foto');
            $id_pelamar = $this->post('id_pelamar');
            $nama_sertifikat_keahlian = $this->post('nama_sertifikat_keahlian');
            $nomor_seri_sertifikat_keahlian = $this->post('nomor_seri_sertifikat_keahlian');
            $data_sertifikat = array(
                'id_sertifikat_keahlian' => $id_sertifikat,
                'id_pelamar' => $id_pelamar,
                'nama_sertifikat_keahlian' => $nama_sertifikat_keahlian,
                'nomor_seri_sertifikat_keahlian' => $nomor_seri_sertifikat_keahlian,
                'foto' => $now,
                'is_verified' => 0
            );

            $insert_sertifikat = $this->Model_main->addRecord($this->tbl_sertifikat_keahlian, $data_sertifikat);

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
        else if($pil === '4'){
            $id_sertifikat = $this->post('id_sertifikat_keahlian');
            $data_sertifikat = array(
                'id_sertifikat_keahlian' => $id_sertifikat
            );

            $delete_sertifikat = $this->Model_main->deleteRecord($this->tbl_sertifikat_keahlian, $data_sertifikat);

            $success = array(
                'status' => 'success',
                'data_sertifikat' => $data_sertifikat
            );

            $fail = array(
                'status' => 'failed'
            );
            if ($delete_sertifikat){
                $this->response($success, 201);
            }else{
                $this->response($fail, 502);
            }
        }
        else if($pil === '5'){
            $id_pendidikan = $this->M_pelamar->getIdPendidikan();
            $now = date("Ymd_his");
            $foto = 'data:image/jpeg;base64,'.$this->post('foto');
            $id_nilai = $this->M_pelamar->getIdNilai();
            $id_pelamar = $this->post('id_pelamar');
            $id_jenis_pendidikan = $this->post('id_jenis_pendidikan');
            $nilai = $this->post('nilai');
            
            $data_pendidikan = array(
                'id_pendidikan_terakhir' => $id_pendidikan,
                'id_pelamar' => $id_pelamar,
                'id_jenis_pendidikan' => $id_jenis_pendidikan
            );
            
            $data_nilai = array(
                'id_nilai' => $id_nilai,
                'id_pelamar' => $id_pelamar,
                'nilai' => $nilai,
                'foto' => $now,
                'is_verified' => 0
            );

            $check_pendidikan = $this->M_pelamar->getPendidikan($id_pelamar);
            
            if(empty($check_pendidikan)){
                $insert_pendidikan = $this->Model_main->addRecord($this->tbl_pendidikan_terakhir, $data_pendidikan);
                
                $insert_nilai = $this->Model_main->addRecord($this->tbl_nilai, $data_nilai);
                
                $success = array(
                    'status' => 'success',
                    'data_pendidikan_terakhir' => $data_pendidikan,
                    'data_nilai' => $data_nilai
                );
    
                $fail = array(
                    'status' => 'failed'
                );
                if ($insert_pendidikan && $insert_nilai){
                $this->M_user->saveSurat($foto, $now);
                    $this->response($success, 201);
                }else{
                    $this->response($fail, 502);
                }
                
            }else{
                $insert_nilai = $this->Model_main->addRecord($this->tbl_nilai, $data_nilai);
                
                $success = array(
                    'status' => 'success',
                    'data_pendidikan_terakhir' => $data_pendidikan,
                    'data_nilai' => $data_nilai
                );
    
                $fail = array(
                    'status' => 'failed'
                );
                if ($insert_nilai){
                $this->M_user->saveSurat($foto, $now);
                    $this->response($success, 201);
                }else{
                    $this->response($fail, 502);
                }
            }
        }
        else if($pil === '6'){
            $id_pengalaman = $this->M_pelamar->getIdPengalaman();
            $now = date("Ymd_his");
            $foto = 'data:image/jpeg;base64,'.$this->post('foto');
            $id_pelamar = $this->post('id_pelamar');
            $nama_pengalaman_kerja = $this->post('nama_pengalaman_kerja');
            $waktu_pengalaman_kerja = $this->post('waktu_pengalaman_kerja');
            
            $data_pengalaman = array(
                'id_pengalaman_kerja' => $id_pengalaman,
                'id_pelamar' => $id_pelamar,
                'nama_pengalaman_kerja' => $nama_pengalaman_kerja,
                'waktu_pengalaman_kerja' => $waktu_pengalaman_kerja,
                'foto' => $now,
                'is_verified' => 0
            );

            $insert_pengalaman = $this->Model_main->addRecord($this->tbl_pengalaman_kerja, $data_pengalaman);

            $success = array(
                'status' => 'success',
                'data_sertifikat' => $data_pengalaman
            );

            $fail = array(
                'status' => 'failed'
            );
            if ($insert_pengalaman){
                $this->M_user->saveSurat($foto, $now);
                $this->response($success, 201);
            }else{
                $this->response($fail, 502);
            }
        }
        else if($pil === '7'){
            $id_pengalaman_kerja = $this->post('id_pengalaman_kerja');
            $data_pengalaman = array(
                'id_pengalaman_kerja' => $id_pengalaman_kerja
            );

            $delete_pengalaman = $this->Model_main->deleteRecord($this->tbl_pengalaman_kerja, $data_pengalaman);

            $success = array(
                'status' => 'success',
                'data_sertifikat' => $data_pengalaman
            );

            $fail = array(
                'status' => 'failed'
            );
            if ($delete_pengalaman){
                $this->response($success, 201);
            }else{
                $this->response($fail, 502);
            }
        }
        else if($pil === '8'){
            $id_nilai = $this->post('id_nilai');
            $data_nilai = array(
                'id_nilai' => $id_nilai
            );

            $delete_nilai = $this->Model_main->deleteRecord($this->tbl_nilai, $data_nilai);

            $success = array(
                'status' => 'success',
                'data_nilai' => $data_nilai
            );

            $fail = array(
                'status' => 'failed'
            );
            if ($delete_nilai){
                $this->response($success, 201);
            }else{
                $this->response($fail, 502);
            }
        }
        else if($pil === '9'){
            $id_dokumen = $this->post('id_dokumen');
            $tipe_dokumen = $this->post('tipe_dokumen');
        

            
            $data_dokumen = array(
                'is_verified' => 1,
            );
            
            if($tipe_dokumen == 1){
                $tipe_dokumen = 'tbl_nilai';
                $id = array(
                    'id_nilai' => $id_dokumen,
                );
            }elseif($tipe_dokumen == 2){
                $tipe_dokumen = 'tbl_pengalaman_kerja';
                $id = array(
                    'id_pengalaman_kerja' => $id_dokumen,
                );
            }elseif($tipe_dokumen == 3){
                $tipe_dokumen = 'tbl_sertifikat_keahlian';
                $id = array(
                    'id_sertifikat_keahlian' => $id_dokumen,
                );
            }

            $update_dokumen = $this->Model_main->updateRecord($tipe_dokumen, $data_dokumen, $id);

            $success = array(
                'status' => 'success',
            );

            $fail = array(
                'status' => 'failed'
            );
            
            if ($update_dokumen){
                $this->response($success, 201);
            }else{
                $this->response($fail, 502);
            }
        }
    }

    function index_put() {
    }

}
