<?php

defined('BASEPATH') OR exit('No direct script access allowed');
use \Firebase\JWT\JWT;

class Dss extends BD_Controller {

    function __construct($config = 'rest') {
        parent::__construct($config);
        // $this->auth();
    }

    var $tbl_pengguna = 'tbl_pengguna';
    var $tbl_pelamar = 'tbl_pelamar';
    var $tbl_perusahaan = 'tbl_perusahaan';

    function index_get() {
        $pil = $this->get('pil');
        if($pil === '1'){
            $id = $this->get('id_bobot');
            if ($id == '') {
                $query = $this->db->select('*')
                ->from('tbl_bobot as t1')
                ->get();
                
                $bobot = $query->result_array();
                $this->response($bobot, 200);
            } else {
                $query = $this->db->select('*')
                ->from('tbl_bobot as t1')
                ->get();
                
                
                $bobot = $query->result_array();
                $this->response($bobot, 200);
            }
        }elseif($pil === '2'){
            $id_dss = $this->get('id_dss');
                            $result_dss = $this->M_perusahaan->getResultDSS($id_dss);
                            if(count($result_dss) == 0){ 
                                $this->set_response($invalid, 404); //This is the respon if failed   
                            }else{
                                $this->response($result_dss, 200); 
                            }
        }
    }

    function index_post() {
        $pil = $this->post('pil');
        
        if ($pil == '1') {
            $id_lowongan = $this->post('id_lowongan');
            $nilai = $this->post('nilai');
            $sertifikat = $this->post('sertifikat');
            $pendidikan = $this->post('pendidikan');
            $jarak = $this->post('jarak');
            $pengalaman = $this->post('pengalaman');
            $pelamar = $this->M_perusahaan->getDSS($id_lowongan,$nilai,$sertifikat,$pendidikan,$jarak,$pengalaman);
            
            $invalid = ['status' => 'not found']; //Respon if not data found

            if(count($pelamar) == 0){
                $this->set_response($invalid, 404); //This is the respon if failed   
            }else{
                $id_bobot = $this->M_perusahaan->getIdBobot();
                $insertBobot = $this->M_perusahaan->insertBobot($id_bobot, $nilai,$sertifikat,$pendidikan,$jarak,$pengalaman);
                if($insertBobot){
                    $id_dss = $this->M_perusahaan->getIdDss();
                    $insertDss = $this->M_perusahaan->insertDSS($id_dss,$id_lowongan, $id_bobot);
                    if($insertDss){
                        for($i = 0; $i < count($pelamar); $i++){
                        $insertDSSDetail = $this->M_perusahaan->insertDSSDetail($id_dss,$pelamar[$i]['id_alternatif'],$pelamar[$i]['nilai']);
                        }
                        
                        if($insertDSSDetail){
                            $result_dss = $id_dss;
                                $this->response($result_dss, 200); 
                        }else{
                            $this->set_response($invalid, 404); //This is the respon if failed   
                        }
                    }
                }
            }
        }
    }

}
