<?php

defined('BASEPATH') OR exit('No direct script access allowed');
use \Firebase\JWT\JWT;

class Asd extends BD_Controller {

    function __construct($config = 'rest') {
        parent::__construct($config);
        // $this->auth();
    }

    function index_get() {
        $id = $this->get('id_pengguna');
        if ($id == '') {
            $pengguna = $this->db->get('tbl_pengguna')->result();
        } else {
            $this->db->where('id_pengguna', $id);
            $pengguna = $this->db->get('tbl_pengguna')->result();
        }
        $this->response($pengguna, 200);
    }

    function index_post() {        
        $pil = $this->post('pil');
        //1 = Login, 2 = Registrasi Pelamar, 3 = Registrasi Perusahaan
        if($pil == 1){
            // $email = $this->post('email');
            // $password = md5($this->post('password'));
    
            // $data = array(
            //     'email' => $email
            // );
    
            // $auth = $this->M_user->getPengguna($data)->row();
            
            // $invalid = array(
            //     'status' => 'error'
            // );
    
            // if($this->M_user->getPengguna($data)->num_rows() == 0){
            //     $this->set_response($invalid, 404);
            // }
    
            // if ($password == $auth->password) {
            //     $this->response($this->M_user->getPengguna($data)->result(), 200);
            // } else {
            //     $this->set_response($invalid, 404);
            // }

            $u = $this->post('email'); //email Posted
            $p = md5($this->post('password')); //Pasword Posted
            $q = array('email' => $u); //For where query condition
            $tkey = $this->config->item('thekey');
            $invalidLogin = ['status' => 'Invalid Login']; //Respon if login invalid
            $val = $this->M_user->getPengguna($q)->row(); //Model to get single data row from database base on email
            if($this->M_user->getPengguna($q)->num_rows() == 0){$this->response($invalidLogin, REST_Controller::HTTP_NOT_FOUND);}
            $match = $val->password;   //Get password for user from database
            if($p == $match){  //Condition if password matched
                $token['id'] = $val->id_pengguna;  //From here
                // $token['id'] = $val->id;  //From here
                $token['email'] = $u;
                $date = new DateTime();
                $token['iat'] = $date->getTimestamp();
                $token['exp'] = $date->getTimestamp() + 60*60*5; //To here is to generate token
                $output['token'] = JWT::encode($token, $tkey ); //This is the output token
                $this->set_response($output, REST_Controller::HTTP_OK); //This is the respon if success
            }
            else {
                $this->set_response($invalidLogin, REST_Controller::HTTP_NOT_FOUND); //This is the respon if failed
            }

        }else if($pil == '2'){
            $id_pengguna = 'PGN002';
            $username = $this->post('username');
            $email = $this->post('email');
            $password = md5($this->post('password'));
            $role = $this->post('role');
            $nama = $this->post('nama');
            $jk = $this->post('jk');
            $alamat = $this->post('alamat');
            $tempat_lahir = $this->post('tempat_lahir');
            $tanggal_lahir = $this->post('tanggal_lahir');
            $agama = $this->post('agama');
            $status = $this->post('status');

            $data_pengguna = array(
                'id_pengguna' => $id_pengguna,
                'username' => $username,
                'email' => $email,
                'password' => $password,
                'role' => $role
            );

            $data_pelamar = array(
                'id_pengguna' => $id_pengguna,
                'nama' => $nama,
                'jk' => $jk,
                'alamat' => $alamat,
                'tempat_lahir' => $tempat_lahir,
                'tanggal_lahir' => $tanggal_lahir,
                'agama' => $agama,
                'status' => $status
            );

            $insert_pengguna = $this->M_user->registrasiPengguna($id_pengguna, $username, $email, $password, $role);
            $insert_pelamar = $this->M_user->registrasiPelamar($id_pengguna, $nama, $jk, $alamat, $tempat_lahir, $tanggal_lahir, $agama, $status);

            $success = array(
                'data_pengguna' => $data_pengguna,
                'data_pelamar' => $data_pelamar,
                'status' => 'success'
            );
    
            $fail = array(
                'status' => 'failed'
            );
    
            if ($insert_pengguna && $insert_pelamar) {
                $this->response($success, 200);
            } else {
                $this->response($fail, 502);
            }
        }else if($pil == 3){
            $id_pengguna = 'PGN003';
            $username = $this->post('username');
            $email = $this->post('email');
            $password = md5($this->post('password'));
            $role = $this->post('role');
            $nama = $this->post('nama');
            $alamat = $this->post('alamat');
            $telepon = $this->post('telepon');
            $jenis = $this->post('jenis');

            $data_pengguna = array(
                'id_pengguna' => $id_pengguna,
                'username' => $username,
                'email' => $email,
                'password' => $password,
                'role' => $role
            );

            $data_perusahaan = array(
                'id_pengguna' => $id_pengguna,
                'nama' => $nama,
                'alamat' => $alamat,
                'telepon' => $telepon,
                'jenis' => $jenis
            );

            $insert_pengguna = $this->M_user->registrasiPengguna($id_pengguna, $username, $email, $password, $role);
            $insert_pekerja = $this->M_user->registrasiPerusahaan($id_pengguna, $nama, $alamat, $telepon, $jenis);

            $success = array(
                'data_pengguna' => $data_pengguna,
                'data_perusahaan' => $data_perusahaan,
                'status' => 'success'
            );
    
            $fail = array(
                'status' => 'failed'
            );
    
            if ($insert_pengguna && $insert_pekerja) {
                $this->response($success, 200);
            } else {
                $this->response($fail, 502);
            }
        }
    }

}
