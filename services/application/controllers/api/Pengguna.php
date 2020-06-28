<?php
defined('BASEPATH') OR exit('No direct script access allowed');
use \Firebase\JWT\JWT;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Pengguna extends BD_Controller {

    function __construct($config = 'rest') {
        parent::__construct($config);
        
        require APPPATH.'libraries/phpmailer/src/Exception.php';
        require APPPATH.'libraries/phpmailer/src/PHPMailer.php';
        require APPPATH.'libraries/phpmailer/src/SMTP.php';
        // $this->auth();
    }

    var $tbl_pengguna = 'tbl_pengguna';
    var $tbl_pelamar = 'tbl_pelamar';
    var $tbl_perusahaan = 'tbl_perusahaan';
    var $tbl_alternatif = 'tbl_alternatif';

    function index_get() {
        $pil = $this->get('pil');
        
        if ($pil == '1') {
            $id = $this->get('id_pengguna');
            if($id == ''){
                $query = $this->db->select('*')
                    ->from('tbl_pengguna as t1')
                    ->join('tbl_perusahaan as t2', 't1.id_pengguna = t2.id_pengguna', 'LEFT')
                    ->get();
            }
            else{
                $query = $this->db->select('*')
                    ->from('tbl_pengguna as t1')
                    ->join('tbl_perusahaan as t2', 't1.id_pengguna = t2.id_pengguna', 'LEFT')
                    ->where('t1.id_pengguna', $id)
                    ->get();
            }
                
            $pengguna = $query->result_array();
            $invalid = ['status' => 'not found']; //Respon if not data found

            if(count($pengguna) == 0){ 
                $this->set_response($invalid, 404); //This is the respon if failed   
            }else{
                $this->response($pengguna, 200);     
            }  
            
        }else if ($pil == '2') {
            $username = $this->get('username');

            if($username == ''){
                $query = $this->db->select('*')
                    ->from('tbl_pengguna as t1')
                    ->join('tbl_pelamar as t2', 't1.id_pengguna = t2.id_pengguna', 'LEFT')
                    ->get();
            }else{
                $query = $this->db->select('*')
                    ->from('tbl_pengguna as t1')
                    ->join('tbl_pelamar as t2', 't1.id_pengguna = t2.id_pengguna', 'LEFT')
                    ->where('t1.username', $username)
                    ->get();
            }
                
            $pengguna = $query->result_array();
            $invalid = ['status' => 'not found']; //Respon if not data found

            if(count($pengguna) == 0){ 
                $this->set_response($invalid, 404); //This is the respon if failed   
            }else{
                $this->response($pengguna, 200);     
            }  
            
        }else if($pil == '3'){
            
            $token = $this->get('token');
        
            $id = array(
                'token' => $token,
            );

            
            $data_pengguna = array(
                'is_active' => 1,
            );

            $update_pengguna = $this->Model_main->updateRecord($this->tbl_pengguna, $data_pengguna, $id);

            $success = array(
                'status' => 'success',
            );

            $fail = array(
                'status' => 'failed'
            );
            
            if ($update_pengguna){
                $this->response($success, 201);
            }else{
                $this->response($fail, 502);
            }
        }
    }

    function index_post() {
        $pil = $this->post('pil');
        $u = $this->post('username'); //email Posted
        $r = $this->post('email'); //email Posted
        $p = md5($this->post('password')); //Pasword Posted
        $q = array('username' => $u); //For where query condition
        $s = array('email' => $r); //For where query condition
        $now = date("Ymd_his");
        $this->load->helper('string');
        $this->load->helper('url');
            global $error;

        if ($pil == '1') {
            $tkey = $this->config->item('thekey');
            $val = $this->M_user->getPengguna($q)->row(); //Model to get single data row from database base on email
            $invalidLogin = ['status' => 'invalid']; //Respon if login invalid
            if($this->M_user->getPengguna($q)->num_rows() == 0){$this->response($invalidLogin, 404);}
            $match = $val->password;   //Get password for user from database
            if($p == $match && $val->is_active){  //Condition if password matched
                $token['id'] = $val->id_pengguna;  //From here
                $data_pengguna = $this->M_user->getPenggunaDetail($u, $p, $val->role); //Model to get data from database base on email  
                $successLogin = $data_pengguna[0];//Respon if login valid
                // $token['id'] = $val->id;  //From here
                $token['username'] = $u;
                $date = new DateTime();
                $token['iat'] = $date->getTimestamp();
                $token['exp'] = $date->getTimestamp() + 60*60*5; //To here is to generate token
                $output['token'] = JWT::encode($token, $tkey ); //This is the output token
                $this->set_response($successLogin, 200); //This is the respon if success
            }
            else {
                $this->set_response($invalidLogin, 404); //This is the respon if failed
            }
        } else if ($pil == '2'){
            $token = random_string('alnum', 50);
            $id_pengguna = $this->M_user->getIdPengguna();
            $id_pelamar = $this->M_user->getIdPelamar();
            $id_alternatif = $this->M_pelamar->getIdAlternatif();
            $username = $this->post('username');
            $email = $this->post('email');
            $password = md5($this->post('password'));

            $data_pengguna = array(
                'id_pengguna' => $id_pengguna,
                'username' => $username,
                'email' => $email,
                'password' => $password,
                'role' => '1',
                'is_active' => '0',
                'token' => $token
            );

            $data_pelamar = array(
                'id_pengguna' => $id_pengguna,
                'id_pelamar' => $id_pelamar,
                'nama' => $username,
                'jenis_kelamin' => '-',
                'alamat' => '-',
                'tempat_lahir' => '-',
                'tanggal_lahir' => '-',
                'agama' => '-',
                'status' => '1',
                'foto' => $now
            );
            
            $data_alternatif = array(
                'id_alternatif' => $id_alternatif,
                'id_pelamar' => $id_pelamar,
                'nilai' => 0,
                'sertifikat_keahlian' => 0,
                'pendidikan_terakhir' => 0,
                'jarak' => 0,
                'pengalaman_kerja' => 0
            );

            $insert_pengguna = null;
            $insert_pelamar = null;
            $insert_alternatif = null;
            if($this->M_user->getPengguna($q)->num_rows() == 0){
                if($this->M_user->getPengguna($s)->num_rows() == 0){
                    $insert_pengguna = $this->Model_main->addRecord($this->tbl_pengguna, $data_pengguna);
                    $insert_pelamar = $this->Model_main->addRecord($this->tbl_pelamar, $data_pelamar);
                    $insert_alternatif = $this->Model_main->addRecord($this->tbl_alternatif, $data_alternatif);
                }
            }


            $success = array(
                'status' => 'success',
                'data_pengguna' => $data_pengguna,
                'data_pelamar' => $data_pelamar
            );

            $fail = array(
                'status' => 'failed'
            );
            if ($insert_pengguna && $insert_pelamar && $insert_alternatif){
                $base64Image = 'data:image/jpeg;base64,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';'';
                $this->M_user->saveFoto($base64Image, $now);
                
            //Get Password
            $token = $this->M_perusahaan->getToken($email);
            $username = $this->M_perusahaan->getNama($email);
            $this->M_pelamar->sendMail($token,$username, $email);
                $this->response($success, 201);
            }else{
                $this->response($fail, 502);
            }
        } else if ($pil == '3' && $this->M_user->getPengguna($s)->num_rows() == 0) {
            $id_pengguna = $this->M_user->getIdPengguna();
            $id_perusahaan = $this->M_user->getIdPerusahaan();
            $username = $this->post('username');
            $email = $this->post('email');
            $password = md5($this->post('password'));
            $token = random_string('alnum', 50);

            $data_pengguna = array(
                'id_pengguna' => $id_pengguna,
                'username' => $username,
                'email' => $email,
                'password' => $password,
                'role' => '2',
                'is_active' => '0',
                'token' => $token
            );

            $data_perusahaan = array(
                'id_perusahaan' => $id_perusahaan,
                'id_pengguna' => $id_pengguna,
                'nama' => $username,
                'alamat' => '-',
                'telepon' => '-',
                'jenis' => null,
                'foto' => $now
            );
            $insert_pengguna = null;
            $insert_perusahaan = null;
            if($this->M_user->getPengguna($q)->num_rows() == 0){
                if($this->M_user->getPengguna($s)->num_rows() == 0){
                $insert_pengguna = $this->Model_main->addRecord($this->tbl_pengguna, $data_pengguna);
                $insert_perusahaan = $this->Model_main->addRecord($this->tbl_perusahaan, $data_perusahaan);
                }
            }


            $success = array(
                'status' => 'success',
                'data_pengguna' => $data_pengguna,
                'data_perusahaan' => $data_perusahaan
            );

            $fail = array(
                'status' => 'failed'
            );
            if ($insert_pengguna && $insert_perusahaan){
                $base64Image = 'data:image/jpeg;base64,/9j/4AAQSkZJRgABAQEAYABgAAD/2wBDAAMCAgMCAgMDAwMEAwMEBQgFBQQEBQoHBwYIDAoMDAsKCwsNDhIQDQ4RDgsLEBYQERMUFRUVDA8XGBYUGBIUFRT/2wBDAQMEBAUEBQkFBQkUDQsNFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBT/wAARCAHaAgADASIAAhEBAxEB/8QAHwAAAQUBAQEBAQEAAAAAAAAAAAECAwQFBgcICQoL/8QAtRAAAgEDAwIEAwUFBAQAAAF9AQIDAAQRBRIhMUEGE1FhByJxFDKBkaEII0KxwRVS0fAkM2JyggkKFhcYGRolJicoKSo0NTY3ODk6Q0RFRkdISUpTVFVWV1hZWmNkZWZnaGlqc3R1dnd4eXqDhIWGh4iJipKTlJWWl5iZmqKjpKWmp6ipqrKztLW2t7i5usLDxMXGx8jJytLT1NXW19jZ2uHi4+Tl5ufo6erx8vP09fb3+Pn6/8QAHwEAAwEBAQEBAQEBAQAAAAAAAAECAwQFBgcICQoL/8QAtREAAgECBAQDBAcFBAQAAQJ3AAECAxEEBSExBhJBUQdhcRMiMoEIFEKRobHBCSMzUvAVYnLRChYkNOEl8RcYGRomJygpKjU2Nzg5OkNERUZHSElKU1RVVldYWVpjZGVmZ2hpanN0dXZ3eHl6goOEhYaHiImKkpOUlZaXmJmaoqOkpaanqKmqsrO0tba3uLm6wsPExcbHyMnK0tPU1dbX2Nna4uPk5ebn6Onq8vP09fb3+Pn6/9oADAMBAAIRAxEAPwD8qqKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKkt7eW7nSGGNpZXOFRBkk0AR1f0rQNQ1uTZZWkk/qyjCj6k8CvQ/CvwqjhWO51n95L1Fqp+Vf94jr9Bx9a9Ct7eK0hWKCJIYlGFSNQoH4Cs3O2xLkeW6Z8H7uUBr+9jtxn7kILnH14H866K0+E2iwD961xcn/AG5Nv8gK7Sis3Jsm7OWHwy8Ogf8AHk5/7bv/AI1BcfCvQZkISOeAn+JJSSPzzXYUUrvuK7PM9S+DmFLWGoZPZLhev/Ah/hXG614O1bQctdWjGIf8tovmT8x0/GvfqQgMCCMg9jVKbQ7s+aKK9i8UfDKx1ZHn08LY3eMhVGInPuO31H5V5Pqel3Oj3j2t3E0MydVPceoPcVqpJlp3KtFFFUMKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKAJrO0mv7qK3t4zLNKwVUXqTXtfgvwVb+F7YSSBZtRkX95N2X/AGV9vfvWZ8MvCY0uwGp3Kf6XcL8gYcxp/iev0xXdVhKV9EQ2FFFFZkhRRRQAUUUUAFFFFABWL4p8LWnimx8mceXOnMU4HzIf6g9xW1RT2A+ctV0q50W/ls7uPy5ozg+hHYg9waqV7R8R/Cy65pLXcKZvrVSykdXTqV9/Uf8A168Xroi7o0TuFFFFUMKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKK9H8D/s7fEL4hCKTSfDV0to5A+2XgFvEAf4svjcP90GvffBv/AAT2u5fLl8U+KYrcEfNa6VCXbP8A10fA/wDHTXDWx2GoaTmr+Wr/AAPcwmSZjjbOjRdu70X3u34XPjunwwyXEqxxRtLIxwqICSfoBX6U+FP2N/hf4XEbPo0ut3CjBm1Wcy7vqg2p/wCO16noPgfw54WhEWjaDpmlR/3bO0ji/wDQQM15FTPKS/hwb/D/ADPq8PwTip2derGPpeT/AER+V2kfB/xzr8iJp/g/XLnf0ddPlCfixXA/E13ekfsdfFbVWw3hxbBSAQ15dxID+TE/nX6ZdqSuCeeVn8MEvvZ7tLgnBx/i1ZS+5f5n56Wn7BXxKuRmS50G09pr2Q/+gxtWxY/8E+fGMir9s8RaJA3O4Q+bKB6Yyi5r7zormec4t9V9x6EeEMqjvGT/AO3n/kfDH/DvPxF/0Nmmf+A8lVbz/gnx4uQN9l8SaNMduR5qyxgn04VuPf8ASvvGip/tfF/zL7kaPhPKWv4b/wDAmfntdfsD/Ei3XMd54fuj/divJAf/AB6IVzmr/safFbS8bNAi1D/rzvImx/30wr9LKK1jnWKW9n8v+Ccs+Dcskvdcl/29f80fkzrXwU8f+H5GS/8AButwhesi2Mjx/wDfagqfzrkLq0nsZ2huYZLeZeGjlQqw+oNfssDisjXPCGheJ7ZrfWNF0/VYG6x3tqko656MDXZDPZf8vKf3P/M8mtwPBr9xXfzS/Rn490V+m3ir9kT4XeKlYnw8NImIIEulSmDbnvtGUP4qa8X8X/8ABPRcNJ4W8VtwvFtq0OSx/wCukeMdv4K9KlnGFqaSbj6r/I+cxPCOZ0NaaU15PX7nb8z4xre8E6D/AMJD4gt7d1zbp+9m/wB0dvxOB+Ndr43/AGYfiT4D86S98Nz3tnGf+PvTSLmNh64X5gP95R0qX4RaUbbTr28kQrLLL5Q3DkBev6k/lXqxrQqR5qck/Q+SxGGr4WXJXg4vzTR34AAwBgegpaKQkAZJwB3NZnELRXffDP4E+Nvi20Uvh/RnOmOedWu28m1UeoY8v/wAN07V9N6D/wAE/wDRYvDVzHrPia8uPEEqfurmzjWO2t368RnLOOxywz2CmuCvjsPh3y1Ja9lqejh8vxOKXNThp3ei+V9/61Piaiuv+KHwn8SfCDxAdK8RWflbyfst7D81vdqP4o29s8qcEemME8hXbCcZxUou6ZwThKnJwmrNBRRRVEhRRRQAUUUUAFeD+O9FGheJbmFBiGX99GPRWJ4/A5H4V7xXnHxjsA1rp96B8yu0JPsRkfyNaQdmNbnl1FFFbmgUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUVr+FfCOs+ONZi0nQdNuNU1CUErBbrkgDqxPQDkcnA5pNqKu3oVCEpyUYK7fRGRWz4V8Ga7441JbDQNJu9WuzjMdrEX257seij3OBX138If2BFd7a78eXr3Ezsu3RdLc4JJ4V5cZOcgYTHsxr7l+Hv7Neo6LpkdjoHhi08NaaOQHVbcMf7xABZj7kZrxKuaJtwwsHN+W39fcfZ4XhqUYqtmdVUYdm1zP5dPxfkfn38N/2AtX1MR3XjTWE0iE8mx07Es5HoXPyqfoGr6h+H/7PngH4Z7ZNF8PW4vR1vrvM85PHRnzt6dFwK9V8RaHceGtcvdKuirXFpJ5bMn3W4BBHsQRWdXymJxuJrNxqSt5LRH6ll2TZbg4RqYamndJqT1evVN7fJIKKKK84+hCiiigAooooAKKKKACiiigAooooAKKKKACiiigBa+MvjXdi7+KniEqqqsc6wKFGOVRQfxJzX2ZXM6b8NvDuma/e62mmxXGrXc73D3dyPNdGbqEz9wduK9LA4qGEnKclfSyPluIMqrZxRp4elJRSldt9rNaJbu78j5k8EfAbxj48mtBa6cLCC4lSNZtRJhJDMAWVCNxwCT2zjrzX2r8Mf2OvAfw+eG8v7d/FWsR8i61MZiQ/wCxAPkHsW3H3q54Dh8/xdp4PO1mf8lOP1xXtFLFZniK/up8q8v89/yPlanD+DyycYq85Wu3Lv5LZfiIqhEVVAVVGAqjAA9AKWiivGOsw/GngnRPiH4dutD8QWEeo6bcDDRvkMp7OjDlWHZgQRXwB8fv2W9c+D80+rab5ut+ESci7VczWYJ+7OoH3Rx+8HB7he/6N02WJJ4njkRZI3BVkcAqwPUEHqK9HB46rg5e7rHqv62Z5WOy6ljo+9pJbP8Az7r+kfjvRX2H+0D+xaEFz4h+HFsFABe48OJhV4HW26AdP9WeP7uOh8L+C37PfiT42arew2LR6RpunymC+1C9jJEMo6xCPgtIO68be5HAP21LHUKtJ1lKyW9+n9fifn9XAYijWVBxu3tbr6f1p1PMKK+xdc/4J8Kmls2i+M5JNSVchNQs1EMh9Mody59fmr5R8W+EtW8C+I77Qtcs2sdTs32SxEggj+FlI4ZWHIPofXIq6GLoYm6pSu0RiMFiMJZ1o2T+a/AyKKKK6ziCuR+KcQk8IzMescqMPzx/WuurkfilKI/CMynq8qAfnn+lVHdAtzxWiiiuk1CiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKACxAAyT0Arf8C+A9c+JHiO30Pw/YyX9/NztXhY0BGXduiqMjJPqB1IFff/AMBf2SvD/wAKI7fVdYEWv+KQA32iRMwWrekSnuOm88+m3pXnYvHUsGve1l2/rY+hynJMVm0/3atBbye3y7vyXzaPnj4IfsV6945+z6t4uMvhzRCQ62rJi8uF/wB0/wCrB9WGfbvX2/4D+HPhz4Z6Mul+G9Kg0y24LmMZklbGNzueWPuTXS9aSviMVjq2LfvvTstv+CftOWZLg8qj+5jeXWT3/wCAvJfiSQXElncRXER2ywusiH0ZTkfqK+zdF1SPW9HsdQiOY7qBJh/wJQcfrXxdX0t8A9b/ALU8Bpau2ZdPnaD/AIAfmX+ZH4V6WS1eWrKm+q/L/gHzPGmF9phKeJS1g7P0l/wV+J59+0Ron2LxZZ6ki4S/t9rn/bj4/wDQSv5V5VX0r8fdF/tPwKbtFzLp86zf8APyt/MH8K+aq480peyxUn0lr/n+J7HC+K+s5ZTT3heL+W34P8AoooryT6wKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigDpPh2wXxdZ5/iVwP++TXsleF+F7wWHiPTZicKJ1Vj7E7T/Ovdcc81lPc+TzeNq0Zd1+pzfiz4j+FfAjwJ4i8Rabosk/MSXtysbOPUAnOPetrTdTs9ZsIL6wuob6ynQPFcW8gkjkU9CrDgivy6+Pl3ql78bPGj60ztqMeovEVkYny4R/qVGei+WUIA4+bPevpr/gnxdapJ4Y8XW0jO2hQ3kRtgxJVJ2UmZU7Djy2IHds9Sa9zEZZGhhVXU7vT017H59hc3liMY8O4WWqXfTv93yPrSiiivAPpQpkVvFbmQxRJGZHMj7FA3MerH1PA59qfRQAV8R/8FBdPsofFvg69jCrqM9jcRTYHLRo6GMn6F3A+p9K+3K/PP8AbH8JeObL4k3PiLxNGl1odxtttMvrIEW0MIZikDAklJOcknhicg9l9vJ0nik+a1k/n5Hz2eyawjSje7Wvbzf5HgNFFFfdH52Fec/GO+C2Wn2YPzPI0p+gGB/OvRq8K+IGtDWvE1w6HMMH7iM+oXqfxOauCuxrc5yiiiug0CiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAr1H4G/s/eIPjfrDR2SnT9EgbF3q00ZMcf+ygyN7+wPHUkcZ6P9mz9mXUPjNqC6rqYl0/wjbyYluRw90w6xxf1boOnJ6fop4b8NaX4Q0S10jRrGHTtOtV2RW8C7VHqfck8knkk5NfP5hmaw96VLWf5f8H+mfe5Bw1PMLYnFaUui6y/yXn16dzD+GXwr8O/CTw6mkeHrIW8f3prh/mmuH/vyN3Pt0HQAV11FFfEynKcnKTu2fs1KlCjBU6UUorZLYKKKKk1CvV/2dtb+xeKb7TXbCX1vvQH++hzx/wEt+VeUVseENb/AOEb8U6VqecLbXCs/wDuH5W/8dJrqwtX2FeFTs/w2Z5Wa4X67ga2HW7i7eq1X4r8T651vS49c0W/06UZju4HgPtuUjNfGM0D2s8sEo2yxO0bj0ZTg/qK+2+OxBHYjvXyr8YtEGh/ELU1VdsV0ReIB0+f73/jwavo87pXhCqumn3/APBPzngnFctathX9pKS9Vo/waOLooor5I/WwooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKAFBIOQcEcg17t4d1RdZ0WzuwRueMBwOzjhh+deEV3Pwx8QizvH0udsRXB3RE9BJ3H4j9RUSV0eRmdB1aPPHeOvy6/5mz48+Cngf4m3kF34l8O2up3kK7EuSzxS7f7pZGBK+xyK6Xw94c0vwlo9tpOi6fb6Xptsu2K1tkCIg+nr6k8mtGik6k5RUHJ2XS58TGjTjN1IxSk+ttQooorM1CiiigAqpq2k2WvaZc6dqVpDf2FyhjmtrhA6SKeoINW6KabTuhNJqzPg39oL9ju+8Crc6/wCCIrjVfDqKZJ9My0t1ZgDkoeWlTqf7w/2uo+ZgQwyDkV+xlfNXx5/Y10v4g3b634PltfDevTSbrqB48WdzubLSFVGVk5LEjhu/J3D6rA5vtTxL+f8An/n958ZmOSNXq4Resf8AL/L7ux+cPxA8TDw9orpE+Ly5BjiAPKju34fzNeH19Z/t0fsl6j8EdU0/xNpVzd614SvUS2kuZ8F7O5C8q2AAEcgspxwcgnOM/JlfWYarTr01Upu6Z8zWoVMNN06is0FFFFdJiFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFe8/sx/s0Xfxj1VdX1hJLTwfaSYlkBKveOP+WUZ9P7zDp0HPTF/Zv8AgFe/G3xX+/WW28M2LK1/eKMbu4hQ/wB9h/3yOT1Gf0u0PRLDw3pFppemWsdlp9pGIoYIlwqKBgD/AOv3r57M8x+rr2NJ+8932/4P5H3/AA1w/wDX5LF4pfulsv5n/wDIr8dtrj9K0qz0PTbbT9OtYrKxtkEUNvAgVI1HQADoKtUUV8S3fVn7Qkoqy2CiiikMKKKKACkZQylTyCMGlooA+uPhrrp8R+BdHvXbdN5IilP+2h2n+Wfxrzz9pDQ99ppGsIuTG7WspHoRuX9Q350/9m/W/N03VtIdsmGRbmMf7LDDfqo/Ou8+KWhnxD4C1e1Rd0yRefEB/fQ7h+YBH419w/8AbMu87fiv+G/E/D1/wjcQ22jz/wDks/8ALm/A+S6KAQwBHINFfDn7gFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAVoaBYDVNbsbQuYxLKAWU4IA5OPfis+rGn30mmX1vdw482Fw6g9DjtQRNScGo72Z9AUVh6X410jVLZZReRWz4+aGdwjKfx6/UVDJ4/wBEj1BbU3YII5nUZiB9C39elYWZ8F9XrXceR3XkdFRSRyLNGrxsHRhkMpyD+NLSOcKKKKACiiqN7q8NoCqnzZf7oPA+pppN7AW5phEB3ZjhV7k08ZAGeTWfpkck2buc5kcYQdlX2rQoemgjF8a+DNI+IfhPVfDevWi32kanA1vcQsSCVYdQRyrDqCOQQDX4oftM/s96v+zl8Srrw/feZdaVPuuNK1Jlwt1b7iBkjjevAZR0OD0Ir9yJJFijZ3YIigszMcAAdSTXx9+0z8QvDnxXn0/RrbS7TVbPSLv7THqlxGGPnAEfuf8AY9SeGwMdAa+q4ejiamIdOkrwfxdl5+vl1/E+K4pxeCwOFVbEStP7K6y8vTu9l87H5e694G1rwzpOm6jqVk9rbX4YwlxyMdmH8JI5APUVg190ePPB9r468L3ukXOFMq7oZSM+VKPuv+B6+oyK+I9Z0i60HVbvTr2MxXVrI0UiH1B7ex6g9xX6DiKHsWrbM/NspzNZhCSmrTXTy6f5P/glOiiiuM98KKKKACiiigAooooAKKKKACiiigAooooAKKKKACuw+FHwv1b4u+M7Pw9pK7HlO+e6dSUtoh96Rvp2HckDvXK2Njcane29naxNPdXEixRRIMs7scKo9ySBX6c/s2/A+2+CvgaOCZEk8Q6gFm1K4ABIbHESn+6mT9SSe9eXmGNWDpXXxPb/AD+R9PkGTyzbE2lpTjrJ/ovN/grvsd14A8CaR8NfClj4f0SDyLG0XAJ5eRjyzse7E8//AFq6Giivz2UnJuUnds/foQjSioQVktEvIKKKKksKKKKACiiigAooooA7n4La3/YvxC08M22K9DWbc8ZbBX/x4L+dfUuAeGG5TwQe4r4s0kTtq9gLX/j6+0R+Sf8Ab3jb+uK+0Y382NX67gDxX2GSTbpTg9k/z/4Y/H+NaEYYqlWT1lFp/wDbr0f42+R8b+KdFPhzxJqelnOLWdo1z/c6r/46RWXXqf7Q2iCw8XWuoouEv7cbiB1dPlP/AI6V/KvLK+ZxVL2FedPs/wAOn4H6blmK+u4KliOsoq/rs/xTCiiiuU9MKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAxmiiigDQ0vX9Q0Vs2V3JAM5KDlT/wE8V09p8V9QiAFxZ29z/tKTGf6iuIopWTOarhqNZ3nBNnoy/F1NvOktu9rgY/9Bqtc/Fu6cEW+nQxn+9JIW/QAVwVFLlRzrLsKnfk/F/5nQ3HjzWrq5SSW6zGpyYI1CIR3Bxz+ddx4e8vXljmjOYMZf1H+yfevJq6LwX4qbw1f4lJaxmIEyjnaezD3H8qey0OXHZfCrTvRjZrt1/roezAYGBwKgv7+20uymu7yeO1tYVLyzSsFRFHUknpVTXPEem+G9Fm1bUbyO20+JN7TMeCD0C+pPYDrXx78XfjPqPxNvTbRB7Hw/C+YbPOGmIPDy44J9F6D3PNevlWUVs0qe7pBby/Rd3+XXz/ACXiDiPDZDR973qr+GP6vtH8Xstdtj4z/Hq58etNo+itJZ+HQcO5ysl7/vDqqf7Pfv6V5DToo3nmjhiR5ZpXEcccalmdicBVA5JJ4AHWvqz4J/sZPepb618QfMgiOJItBhfDN/18MOn+4pzxye1fr1GjhssoKlTVkvvb7+b/AK02P51qTzDiHFyrVHzTe72jFdF5Lslq/N6nzx4E+GPij4mXxtvDejz6htOJLnGy3i/35T8o+mSfatz4q/8ABKDxJ8RbePW7fxjo2meI1h2PYtbSvBMR93dOCCCORkRnjHpX6RaTpFjoOnQafpllb6fYwLtitrWMRxoPQKOBVuvNr4yVZcqVkfbZbklPASVWUnKf3L7uvzP53/jn+y18S/2ddQEPjXw3PZ2Ttth1W2Pn2U5/2ZV4B/2Ww3tXk9f0zeIvDmleL9EvNG1zTbXV9JvIzFcWV7EssUqkYIZWGDX5I/t5/wDBOab4O2998QvhrBPf+ClLS6lpJJkn0kE58xDjL24zg5yydSSuSvGmfRWPgeiiiqEFFFFABRRRQAUUUUAFFFFABRRRQAUUV2/wZ+GF38XviFpnhy2LRQysZbu4Uf6iBeXf69APdhUTnGnFzk9EbUaM8RUjSpq8pOyXmz6P/Ya+BouZT8RNZtyY4i0OkRSAYZujz/hyq++4+hr7Uqno2j2Xh7SLPS9Ot0tbCziWCCFBgIijAFXK/NsXiZYqq6kvl5I/ovKsup5XhY4eG+7fd9X+i8kFFFFcZ64UUUUAFFFFABRRRQAUUUUAdx8H9G/tPxaty65hsYzNn/bPC/zJ/CvpjRpvMswp6oSPwryH4M6P9g8LNesuJL6UuCf7i/Kv67j+NekaTqsNrqkFjI+2a7VzEv8Ae2DJ/Q197ltJUMLFveWv37H4NxLipY3M6ihqoe6v+3dZP77/AHHL/tAaJ/aXgYXqLmXT7hZeP7jfK381P4V8219n6/pKa7oWo6bJ9y7t3h+hZSAfwOK+MpYXt5XilG2WNijj0YHBH5ivDzqly1o1F1X4r/gH2/BeK9phKmGb1g7r0l/wUxtFFFfPH6EFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAVQ1vXLPw/Ytd3kvlxjhVHLOfRR3NXpCwjYoAzgHapOAT2FeAeItZv9c1WaXUDtmiZohAPuw4OCoH1HJ7/lX0OTZV/adVqUrRja/d+S/wA+nqfnPGvFn+q2Eg6VPmq1bqN17qtu5Pra6tFavukmX/FvjvVfGRt4ryd10+1J+y2O7KRZ6n3b3PToPfN0Dw/qfirWbXSdHsZdR1O6bbDbQDLMe5yeAB3JwB3p/hvw3qfjDXbPRtGs3v8AU7t9kUEf6sx/hUdSx4Ar9DPgR8B9K+DGhZG2+8R3ca/btRIHXHMUXGVjB/E9T7fqkpUcBSVKlFJLZf1/w7P5Mw+HxnEGLnicTNtt3lJ9+y6X7JaJdOjxvgH+zRpXwkgj1XU/K1bxa6/Nd4zFaA9UhB/V+p7YHFe10UV4FSpKpLmk9T9Lw+GpYSmqVGNkv6u+7CiiioOkKZLFHcRPFKiyxOpV0cAqwPBBB6in0UAfiL/wUX/ZBX9nL4jx+IfDdssXw/8AEcrNZQxqcafcAZktiSTweXTp8uVx8mT8g1/RN+038E7L9oT4JeJ/BV0ifabu2aXT53B/cXiAtA/HON4AOOqlh3r+ePVtKu9C1W802/ga2vrOZ7e4gf70ciMVZT7ggirTuSyrRRRVCCiiigAooooAKKKKACiiigAr9EP2KvhGvgT4cDxHeRgav4iVLgE9Y7XGYl/4Fkuf94DtXxx+zx8MG+LXxU0nRnx/Z8Tfbb8kZ/0eNlLr/wACJCZ7bs89K/U+KJIIkjjUJGihVVRgKBwAK+WzrFcsVh49dX6dD9N4My3nnLMKi0jpH16v5LT5jqKKK+QP1sKKKKACiiigAooooAKKKKACpba1lvrmK2gG6aZxGg/2icCoq7f4Q6N/ani5LhhmKxQzH03H5V/mT+Fb0KTr1Y011Zw47FLBYWpiZfZTfz6fe7HuOnWEelafbWcIxFbxrEv0AxXmXjfxh/YvxT0KbfiDTNhl57SEh/8AxwivVeO/Ar5i8U6l/bniHVLwnck877f9wHC/oBX12bVvYUYRho7r/wAl1/yPyThPBrHYyrVrapRafrO6/K7Pss8Hg5HqK+U/i/og0L4g6rGq7YrlhdoPZ+T/AOPbq+iPhzrv/CR+CNIvmbdK0IjlP+2nyt+oz+NebftI6JmLRtYRfus1pKQOx+ZP5N+dGZxWIwaqx6Wfyf8Aw5PDFWWX5vLC1Ptc0H6p3X5P7zw6iiivij9rCiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAK8v8Aip4XeO6j1e0ieTzysM8US5YueEYAcknhfrivUK9J+BXhCPxF4ra/uohLZ6WFlCuMq0x+5+WC31Ar2snxFXDY2EqWt9Gu66/5/I+J4yyzC5rktajinblXNF9VJfDb1vytdU32O0/Zu+BkHwh8Krc38SSeKtRQNfTdfIXqIEPovcj7x9gK9hoor7yc5VJOUt2fhGHw9PC0o0aSsl/X3sKKKKg6AooooAKKKKACvw3/AOCnHwph+GP7VeuXVlb+Rp/iaCPXUCphPNkLLPg9yZEdz/v+4r9yK/NP/gtB4NWXw/8ADXxYuQ8F1daW+OhDosq54/6Zv37mmtxM/LOiiitCQooooAKKKKACiiigAoorb8EeE7vx34u0jw/Y8XWo3KW6tjIQE8sR6KMk/SplJRTk9kXCEqklCCu3ovVn3N+wp8NB4Y+HFz4ouYit/r8n7osCCttGSq/99NubPcba+mapaJo1p4d0ex0uwiEFlZQpbwxqMBUUAAfkKu1+ZYms8RWlVfV/8Mf0pl2DjgMJTw0fsrXzfV/N3CiiiuY9EKKKKACiiigAooooAKKKKACvcfgxo/2HwxJeuuJL2UsDjnYvyr+u4/jXidtbSXt1DbxDMs0ixIP9pjgfzr6h0rT49J0y0soh+7t4liX3wMZr6HJaPNVlVf2V+L/4B+ecZ4z2WEhhYvWbu/SP/Ba+4oeNNU/sbwpql2p2yLAyxn/bYbV/U180AYAFe0fG/VPs+hWVgrYa5n3sPVUH+JWvF6jOavPiFBfZX56/5G/B2G9lgJVnvOT+5aL8bnvX7OGt+dpWraS7Za3lW4jH+y4wf1X9a7b4r6GfEHgDVrdF3TRRi4iHfch3fqAR+NeF/BLW/wCxviFYozbYr5GtG9Mtyv8A48o/OvqFlV1KuoZGGGU9x3r2MuksTgvZS6Xj/l+f4Hx/EUJZbnKxVNb8s16rf8V+J8RA5GR0orS8SaM3h3xBqOltn/RJ2iGepUH5T+RFZtfFyi4txe6P2mE41YKpB6NJr0ev6hRRRUlhRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAV9O/AzQxpHw/tZ2XE1+7XTH1U8J/wCOgH8a+YtrP8qjLNwB719oaHp66Vomn2SDCW1vHCP+AqB/Svoslp81WVR9F+f/AAx+d8a4hwwlKgvtSu/SK/zZeooor7E/HgooooAKKKKACiiigAr4p/4K36FFqv7KsV665k0zXbW4jOehZZIj+khr7Wr5K/4KljP7G3if/r+0/wD9KUprcR+HlFFFaEhRRRQAUUUUAFFFFABX1B+wP4FXXPiLqfiWePdDoltshLLwJpsqCD6hA/8A31Xy/X6RfsWeCv8AhE/ghY3csZju9anfUHyc/IcJH+G1Af8AgVePm1b2WFaW8tP8/wAD67hbCfWszhJrSF5fdovxf4HvFFFFfn5+9BRRRQAUUUUAFFFFABRRRQAUUUUAdt8ItG/tTxfHO65hskM5J6buij8yT+Fe9V598FtH+w+GZr9lxJfSkqT/AHE+UfruNegllUFmOFHJPtX3uV0fZYaLe8tf8vwPwbijGfW8zmltD3V8t/xf4HhPxk1T7d4vNsrZSyhWPH+03zH+Y/KuFq7rOpnWtXvb9jk3MzSDPoTx+mKpV8ViKvtq06ndv/gfgftGXYb6ng6WH6xik/Xr+LZNZ3cmn3lvdxHEtvKsyEf3lII/UV9n6ZqEerabaX0J3RXMKTIR6MoP9a+Kq+m/gVrf9r+ALeB23S2ErWx/3R8yfowH4V7eS1eWrKk+qv8Ad/wGfFcaYXnw1LEreDs/SX/BX4nmn7Qmif2f4zg1BFxHqFuGJ/20+U/ptry+vo/9oPRP7Q8FRXyLmWwuFckddjfK36lT+FfOFcOZ0vZYqXZ6/f8A8E9zhjFfWsrp3esLxfy2/BoKKKK8o+qCiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiug8E+C77xzrSWFmNka/NPcsMrCnqfUnsO/51cISqSUIK7ZjWrU8PTlVqytGOrbL3w08DXvjXxDCIR5VlaSJLc3LLlVAOQo9WOOn419YHk1meG/Dlj4U0eDTdOi8q3iHU/edu7Me5NadffYDBrB07N3k9z8Ez3OJZviFNK0I6RXX1fm/wANu4UUUV6R82FFFFABRSAgjI6UtABRRRQAV8lf8FS/+TNfFH/X9p//AKUpX1rXyV/wVL/5M18Uf9f2n/8ApSlC3Ez8PKKKK1JCiiigAooooAKKKKALWlabNrOqWdhbANcXUyQRg9CzMFH6mv188LaDb+FfDGkaLarsttOtIrSNc5+VECjnv0r8zP2X/C48V/HXwlbOheG2uhfSYPQQgyD/AMeVR+NfqPXx+e1LzhS7K/3n65wRh+WjWxLW7UV6JXf4sKKKK+XP0wKKKKACiiigAooooAKKKKACpILeW8njghXdNKwjRfVicCo67X4R6N/avjCKdhmKxQzt6bvur+pz+Fb0KTrVY011ZxY3FRwWGqYmX2U38+n3ux7jpWnR6RptrYxf6u3iWJfwGM1kfEHVP7H8HapOpxI0XlJ/vOdv9Sfwroa8w+OeqeXp+m6crczSNM4/2VGB+rfpX3uMqKhhptaWVl+SPwPJ6EsfmdKE9byu/l7z/L8Tx4DAxRRRX52f0SFeu/s5a19l8Q6lpbt8l3AJo1/20PP/AI6x/KvIq3fAutjw54x0jUWbbHDOBIc8bG+Vs/gxP4V14Sr7GvCp2f4bHkZvhfruArUEtXF29VqvxX4n1h4j0hNf8P6jpr9Lq3eIH0JU4P4HFfGkkTwSPFINsiMUYehBwRX1Jrnxo8J6GWUaj/aMq/8ALOwXzcn/AHuF/WvmzxPqNrrHiPUr+ygktrW6nedIpcbl3HJBxx1Jr2s5nRqODhJOSunbsfG8HUcXh1VhWpyjCVmm1bXZ767eXQzKKKK+aP0kKKKKACiiigAoooJwKACjqcdT6V3nhX4bNexJd6qXiib5ktl4Yj/aPb6Dn6V6BYaPY6WgW0tIYAO6IMn6nqahySPGr5nSpPlguZ/h954Wmn3cgytpcMPVYmP9Kjktpof9ZDJH/voR/OvoUMR0JFIfmGDyPelznF/bEutP8f8AgHzvnNFe8Xvh3S9QB+0WFvIf7xjAP5jmmab8B9H1+CS5F1eaeu7aixFWU+p+YE/rXTh6U8TP2dNalVM+wtCHtMReK+/8tTwqivb7r9moc/ZfEBHoJ7bP6hh/Kse7/Zx8QRZ+z6jptx6BmkjP/oJrulluLj9j7rP9RU+JMpqbV0vVNfoeUUV3918CvGFsCRZW9zj/AJ4XKn/0LFY938MfFllnzfD98R6xoJP/AEEmuaWFrw+Km/uZ6VPNMBV+CvB/9vL9WjmKKv3OgapZEi40y9gx18y3df5ik0TRL3xHq0OmafCZ72U4CdAo7sx7AdzWHJK6jbVnd7amoOpzLlWrd1ZL1uWfC3he/wDGGsw6bp8e6V+Xkb7sSd3b2H69K+q/B3hCw8E6LFp9iucfNNOw+eZ8csf6DsKq+AfAll4C0YWlvia6kw1zdEYaVv6KOw/rXTV9vl+AWFjzz+N/h5f5n4hxDn0s0qexou1KO39593+i6b7vQooor2T44KKKKACoDJ58hjQ/Iv32Hr/dFQ3V00kotYD+9P3n/uD/ABqzDCsESxoOB+vvQBJ0ooooAKKKKACvkr/gqX/yZr4o/wCv7T//AEpSvrWvkr/gqX/yZr4o/wCv7T//AEpShbiZ+HlFFFakhRRRQAUUUUAFFFFAH1B/wT/0Br34pa1qxGYrDS2j+jyyKB/46j199V8hf8E8dHWPw/4x1Xb88t1BahvUKjNj/wAfH519e1+fZtPnxcvKy/D/AIJ+98K0vZZTSf8ANd/e/wDgBRRRXkH1oUUUUAFFFFABRRRQAUUUUAFe3fBXRvsXhye/dcSXsvyk9di8D9d1eK28D3VxFBEMyyusaD1YnA/U19Q6Rpsej6VZ2MX3LeJYh74GCfxr6HJqPPWlVf2V+L/4B+fcZ4z2WEhhYvWbu/SP/Ba+4t14F8XNT/tHxrcRqcpaRrbge/3m/Vv0r3qedLaCSaQ4SNS7E9gBk18tahetqWoXV25Ja4leU5/2mJ/rXfnVXlpRprq7/d/wWeBwVhufFVcS/sqy9ZP/ACRXooor48/YAooooAKKKKACiiigAooooAKKKKACut+HXh1dY1Vrmdd1raYbaejufuj8Ov5VyVer/Cwxnw7KFx5guG3+vQY/Spk7I87MKsqWHk47vT7zsutJRRWJ8QFFVtR1O10m1NxeTLBCDjc3c+gHemWGsWOqKDaXkNx7I4JH4daC+SXLzW07l5EaR1RRlmIUD1Jr0zT7NbCyht1/5ZqAT6nufzrjfCWn/atU81h8kA38/wB7t/U/hXdV9nkeH5acq766L0X/AAfyPh89xHNUjQXTV+r/AOB+YUUUV9QfLhRRRQAZJGO1MEMavvEaB8Y3BRnH1p9FAbBRRRQAUUUUAFUNR1DyP3MXzTNxx/D/APXpdS1EWi7E5mI4/wBn3qHSbI/8fMvLtyuf50AWrCz+yRfMd0rcs1WqKKACiiigAooooAK+Sv8AgqX/AMma+KP+v7T/AP0pSvrWvkr/AIKl/wDJmvij/r+0/wD9KUoW4mfh9HG00ixoCzsQqgdya7vRfBltaxrJeqLic87D91fb3rI8CWSz6lLOwz5CfL9T3/LNd5VNkMhWytkUBbeIAdggpsmn2syFXtomU9igqxRUknIeIPBkYie408FSoJaDqD9P8K4yvYq808V2K2GtzqgwkmJAPTPX9c1aZSMiiiiqGfoX+wXpRsPgrc3J5+3arNMOOwSOPH/jh/Ovo+vDf2K49n7PWhH+/cXbf+R3H9K9yr81xz5sVUfmz+jskgoZZh4r+Vfjr+oUUUVwntBRRRQAUUUUAFFFFABRRXYeHvhR4j8U6TFqWnwW8lpKWCtJOFOQcHjHqK0p0p1Xy01d+RzV8TRwsOevNRW127aj/hLo39q+MYJXXMNkhuGz03dFH5nP4V77XL/C/wCGmp+E9PvGv4oVvLmQZEcgYBFHAz9STXbf2Pdf3V/76r7rLKDw+HSkrN6v+vQ/DeJcfHH5hKVKV4RSimtu7a9W/wADiPidqn9leCtRYHDzqLZf+B8H9M188V9C/FLwF4g8U2lha6bBC8UcjSymSYJzjCj36tXnf/Ch/GH/AD62n/gUP8K8XNKVeviPcg2krbfNn2fC2LwOBwH76tGM5Sbab17L8PzPPqK9B/4UP4w/59bT/wACh/hWH4o+HXiDwfCs+pWBW2b/AJeIW8yNT6MR938a8SeFrwjzSg0vQ+1pZpga81TpV4uT2Sauc1RRRXMemFFFFABTgjFdwVivqAcU2vof9nRQ3g7UAyhh9ubqM/8ALNK7cHhvrVX2V7b/AIHjZvmP9lYV4nk5rNK17b/eeQ6B8N9X8TaSmoWLWpgZmULJKVbKnB7VLffCvxHp9tLPJawtFEpd2S4TgDknkivq3yYwMCNQPQKKjmsba4jeOW3ikjcFWVkBBHcGvplk1Dls27+vX0PzJ8Z472rkox5L7W1t2vdXfnY+N9G0HUPEMskem2r3bxqHdUIGBnGeSKvy+AvEcP39Fu/+AqG/ka+qdP8ACOh6TLJJZaTZ2ckihXaCIIWHocVdOmWp/wCWIH0JFYwySHL+8m7+W34nZX42re0fsKK5P717+ezsfGMtlcQXZtZIJEug2wwlTvDemOua09Iv9Z8M3TTW0FxETw8ckDbXHuMV9LSfCnw1Jri6ubOQX4mFx5gnfG8HIOM47V0B0eAjG6QD/erGGSN35527ddPPY6cRxpScYxhQ5k1713bXy3uvN2Z84R/Fm4jXE+lKX/2ZSn6EGobv4uXUilbaxggb+9JIXx+GBXtPjL4Q6Z40uLaa4vry1eBGRRBswcnJJyp9K07L4fabZ6baWZjjuFt4UhDzQIzNtGMnjrWKyOo5tcyS6Pv8uhm+JMsjShUVBub3jd2XzejPlXVNZvNbuPOvblp3HQE4VfoBwKqAlSCDgjuK+nfFXwisNe0iW1s0s9PuXZStytqMrg5PTHXpWT4Z+BFjpdjPDqos9WmaUskxjZCqYA29fUE/jWbyeuqnImrd+np3PYpcX5esN7RxcWnbkVr277KNvnc8e8OfEnxH4WbFjqTtETlobgCVG/PkfgRXpnh/9o+Jyset6U0R73Fk+4fijcj8Ca6Of4I6BIrY02FWxxsmkX+teT6p8EPFOk2VzeSx2TW9vG0rmO5yQqgk8FR2FbexzDAJezfMuy1S+/b5HLHF8P565e3goT01dot37Nb/ADXY+gPD/j/w94oCjTtVgllP/LFyY5P++Wwa6AjHWviDAbBx7iuq8P8AxP8AE3hratpqsssC/wDLvdfvY/15H4EVvRztbVofNf5P/M4sZwU1eWDq/KX+a/VH1rRXimgftIRMFTW9KeM9DPYNuH1KMQfyJr0rw/4/8PeJ8DT9Vt5ZiM+RI3lyj/gLYP5V7lHG4ev8E1fts/xPiMZk2PwN3XpO3dar71f8bHQ0UEY60V2nihRRRQAVT1HUBZphcGVug9Pc1g+M/iDp/hCWzs3YXGp3k0cUVqp5UMwG9vRRn8e1SRRSX90Vzl2OWY9hWcakZScYvVbm86FWnCFScbKV7edtHbyuT6dZtfTmWXJQHLE/xH0rf6VHDCtvEsaDCrUlaGAUUUUAFFFFABRRRQAV8lf8FS/+TNfFH/X9p/8A6UpX1rXyV/wVL/5M18Uf9f2n/wDpSlC3Ez8a/h79+9+if1rs64z4e/fvfon9a7Om9zNhRRRSEFcB48/5DUf/AFxX+bV39cB48/5DUf8A1xX+bU47jRzlFFFaFH6YfsbRNH+zr4X3DG57th9PtMte1V4l+xkc/s6+Guc4kux9P9Jkr22vzLGf7zU9X+Z/SWU/8i7D/wCCP5BRRRXIesFFFFABRXS6N4Vhv7CO4nlkBk5CxkDAz7g1f/4Qqx/563H/AH0v+FcMsZRhJxb28jyamaYanJwbd15HF0V2n/CFWP8Az1uP++l/wo/4Qqx/563H/fS/4VP16j3f3Ef2vhe7+44uvqH4G/8AJNNN/wCuk3/ow14l/wAIVY/89bj/AL6X/Cu+8J+MrvwdoUGlWcEMtvCzMrz5LncxJzggd/SvVy3N8LhqznUbta23ofLcR1o5ng40cNrJST100s/8z2yivK/+Fs6p/wA+ln/3y3/xVH/C2dU/59LP/vlv/iq+l/1ly7+Z/wDgL/zPzb+x8X2X3o9Uoryv/hbOqf8APpZ/98t/8VR/wtnVP+fSz/75b/4qj/WXLv5n/wCAv/MP7HxfZfej1SmyIssbI6q6MMMrDII9CK8t/wCFs6p/z6Wf/fLf/FUf8LZ1T/n0s/8Avlv/AIqj/WXLv5n/AOAv/MP7IxfZfeQeOfgHY6r5l34eZNNuzljaP/qJD6D+5/L2FeF63oOoeHL5rPU7SSzuV/gkHDD1Ujgj3Fe9/wDC2dU/59LP/vlv/iqzte8bt4nsWs9U0nT7yA8gOrZU+qndkH3FeFi8flNa8qUnGX+F2/4Hy+4+6yrNM0wdqWKSqQ/xLmXz6+j+88GortW8F2JYkSXCgngbxx+lJ/whVj/z1uP++l/wrwvr1Hu/uPuP7Xwvd/ccXXvHwD8Q6XpHhS+ivtStbOVr1mCTzKhI2JzgnpxXnf8AwhVj/wA9bj/vpf8ACkPgmwPWS4P/AAJf8K68Lm9LC1faxV9/xPJzStgs0wzw05yim07qPb1Z9EzfEbwpbyFJfEukxuOqtexg/wA6anxJ8JSOFXxPpDMTgAXseSfzr5h1f4R6Nq86zSTXcUoGCyMvI98rUOn/AAa0XT7pJxPeTMnKh2TAPrwtfQriihy3cdfRn5nPI7V+WM/cvv1t3t38j6u/4Tbw9/0HdO/8Ck/xo/4Tbw9/0HdO/wDApP8AGvm7/hCLD/npP/30v+FH/CEWH/PSf/vpf8K5/wDWqP8AJ+Z7X+r+W/8AQRP/AMBX+Z9I/wDCbeHv+g7p3/gUn+NH/CbeHv8AoO6d/wCBSf4183f8IRYf89J/++l/wo/4Qiw/56T/APfS/wCFH+tUf5PzD/V/Lf8AoIn/AOAr/M+kf+E28Pf9B3Tv/ApP8aP+E28Pf9B3Tv8AwKT/ABr5u/4Qiw/56T/99L/hR/whFh/z0n/76X/Cj/WqP8n5h/q/lv8A0ET/APAV/mfSP/CbeHv+g5p3/gUn+NVW+JXhJGKt4n0hWBwQb2PIP5188f8ACEWH/PSf/vpf8Kwb74MaJe3Uk/2i8hZzuZUZMZ7n7taw4ppN+/G33nBi8hw8Ip4Sq5PqmktPI+p4fiN4UuJNkXiXSZH67UvIyf51R8W+MNCuPCusxRazYSSvZTKiJcoSxKEAAZ6183aR8JdH0eVpY5rqSVht3Oy8D2wta3/CE2H/AD0n/wC+l/wqKvFFJ3jGOj9TowOSYWKjVxNWSmneySa0aa1+Rxg6Ciu0/wCEKsf+etx/30v+FH/CFWP/AD1uP++l/wAK+Z+vUe7+4/T/AO18L3f3HF0Y5B7jpXaf8IVY/wDPW4/76X/Cj/hCrH/nrcf99L/hR9eo+f3B/a+F7v7ip4f+J/ibw1tW01WWWBf+WF3++T6fNyPwIr0rQf2kImCx61pLRnHM9k+4H/gDYx+ZrgP+EKsf+etx/wB9L/hR/wAIVY/89bj/AL6X/Cu+jnkqGkJu3Zq6/E8LGUckx13Vpa90uV/hb8Uz1+T9oLwokZKG/kb+4LbBP4k4rjfE/wC0TfXsTwaHYDTwePtVwwkkH0XG0fjmuS/4Qqx/563H/fS/4Uf8IVY/89bj/vpf8K6KnEdSorc1vRHnYfLMhw8+fllJ/wB67X3aL7zB0i6uNR8WabPcTSXNzNfQl5ZWLM58xepNfX1hZCziIPMjcsf6V8pWujro/jPQESQyRyXtuy7uoxKuc19bnqa+jyGaqUpzT3a/JnjcZThUnhp0/hcZW/8AAkFFFFfUn50FFFFABRRRQAUUUUAFfJX/AAVL/wCTNfFH/X9p/wD6UpX1rXyV/wAFS/8AkzXxR/1/af8A+lKULcTPxr+Hv3736J/WuzrjPh79+9+if1rs6b3M2FFFFIQVwHjz/kNR/wDXFf5tXf1wPj1WGsRMQQphABxweTTjuNHN0UUVoUfpL+xRMJP2fNGUEEx3V0pHp++Y/wBa91r5m/YC1Nrv4QarauwJtNXkVVHZGijYfqWr6Zr81x8eXFVF5s/o3I5qplmHkv5V+F1+gUUUVwnthRRRQB6F4d/5Aln/ALn9TWjXE6V4rk06zW3a3Eyp91g20geh4q5/wnB/58v/ACL/APWr52rhKznJpdT4qvluKlVnKMbpt9UdVRXK/wDCcH/ny/8AIv8A9aorvx+ba1mmFjuMaM+PN64GfSs/qVd/Z/FHO8txUU24beaOvorw0ftJzkA/8I/H/wCBZ/8AiKX/AIaSn/6F+P8A8Cz/APEV2/2Pjf5PxX+Z859fw/8AN+DPcaK8/wDC/wAVW8RaSt42mCAl2TYs27ofXbWt/wAJwf8Any/8i/8A1q5JYHEQbi46rzR79HA4ivTjVpxvGSutVsdVRXK/8Jwf+fL/AMi//Wo/4Tg/8+X/AJF/+tU/U6/8v5Gv9mYv+T8V/mdVRXK/8Jwf+fL/AMi//Wo/4Tg/8+X/AJF/+tR9Tr/y/kH9mYv+T8V/mdVRXK/8Jwf+fL/yL/8AWo/4Tg/8+X/kX/61H1Ov/L+Qf2Zi/wCT8V/mdVRXK/8ACcH/AJ8v/Iv/ANaj/hOD/wA+X/kX/wCtR9Tr/wAv5B/ZmL/k/Ff5nVUVyv8AwnB/58v/ACL/APWo/wCE4P8Az5f+Rf8A61H1Ov8Ay/kH9mYv+T8V/mdVRXK/8Jwf+fL/AMi//Wo/4Tg/8+X/AJF/+tR9Tr/y/kH9mYv+T8V/mdVRXK/8Jwf+fL/yL/8AWo/4Tg/8+X/kX/61H1Ov/L+Qf2Zi/wCT8V/mdVRXK/8ACcH/AJ8v/Iv/ANaj/hOD/wA+X/kX/wCtR9Tr/wAv5B/ZmL/k/Ff5nVUVyv8AwnB/58v/ACL/APWo/wCE4P8Az5f+Rf8A61H1Ov8Ay/kH9mYv+T8V/mdVRXK/8Jwf+fL/AMi//Wo/4Tg/8+X/AJF/+tR9Tr/y/kH9mYv+T8V/mdVRXK/8Jwf+fL/yL/8AWo/4Tg/8+X/kX/61H1Ov/L+Qf2Zi/wCT8V/mdVRXK/8ACcH/AJ8v/Iv/ANaj/hOD/wA+X/kX/wCtR9Tr/wAv5B/ZmL/k/Ff5nVUV5P4r+OUnhvU1tE0ZLgGISb2uSvUkY+77Vjf8NJT/APQvx/8AgWf/AIiuqOU4ycVKMNH5o8KviaWHqSo1XaUdGe40V5B4d+PkuuavBZNoiQiTPzi5JIwCem2u1/4Tg/8APl/5F/8ArVlUy7E0nyzj+KPQwlGpjoOph1dJ27a/M6qiuV/4Tg/8+X/kX/61H/CcH/ny/wDIv/1qy+p1/wCX8jt/szF/yfiv8y/f/wDI5+GP+vyH/wBHJX1Mepr5E0/V31jxloUjoI1S+t1VAc4/er3r67PU1+l8NwdPDyjLdNfqfKcV0pUVhac91GX/AKUFFFFfXHwIUUUUAFFFFABRRRQAV8lf8FS/+TNfFH/X9p//AKUpX1rXyV/wVL/5M18Uf9f2n/8ApSlC3Ez8a/h79+9+if1rs64z4e/fvfon9a7Om9zNhRRRSEFU9V0qHV7RoJh7q/dT6irlFAHkt/Yy6bdyW8y4dD+BHYiq9eg+MtGF/Ym5jX9/AM8fxL3H9a8+rRO5R9p/8E79Y3WnjTSj/A9tdDn1Dqf5Cvsevz2/YL1w6d8ZrqxaTbFqGlyx7D/E6sjqfrgP+Zr9Ca+BzeHJi5PvZ/h/wD964Ure1yqmv5XJfjf9Qooorxj68KKKKACiiigAqtqYJ0y7A5Jhf/0E1ZqG+/48rj/rm38qcd0ZVf4cvR/kzwRbSfaP3MnT+6aX7JP/AM8ZP++TXSjoKWvqOc/nhI7L4bRtH4XjV1KnzpOCMd66isPwZ/yA0/66P/OtyvnK2tWXqfu2U/8AIvof4UFFFFYnrBRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUu0+h/KhgV6jH1oA8s+J0EsniKNkjZh9nUZAz3auR+yT/wDPGT/vk16N42IbV0wQf3S9Pqa5+voqErUo+h+GZyv+FGv/AIn+hD4Dt5Y/FVkzRuoG/kr/ALJr2CvMtG1Oz0bUY7y/uobK0iBMk9w4RF47k8Ct+X4v+B4ThvFujk+iXaMf0NcOKp1Kk04xb06Jv9D7ThnFYfD4OUa1SMXzPeUV0Xdo66iuIk+NvgaMH/io7V/+uYdv5LVCf9oTwNB/zFZZP+udpI39K5Fha72g/uZ9VLNsvh8WIh/4Ev8ANnrXhb/kadE/6/7f/wBGrX2Uepr82tJ/ai8DaZrem3bSalPDb3UMz+XZEHarhjgMR2FfQB/4KQfCQk/ufEn/AILV/wDjlfU5RSqUYTVSLV2t/Q/LOLsdhcZWovDVFNJO9nfqj6lor5Z/4eQfCT/nj4k/8Fq//HKP+HkHwk/54+JP/Bav/wAcr6C6PgLo+pqK+Wf+HkHwk/54+JP/AAWr/wDHKP8Ah5B8JP8Anj4k/wDBav8A8couguj6mor5Z/4eQfCT/nj4k/8ABav/AMco/wCHkHwk/wCePiT/AMFq/wDxyi6C6Pqaivln/h5B8JP+ePiT/wAFq/8Axyj/AIeQfCT/AJ4+JP8AwWr/APHKLoLo+pq+Sv8AgqX/AMma+KP+v7T/AP0pSr//AA8g+En/ADx8Sf8AgtX/AOOV89ft6ftnfD74yfs1a74W8Pxa0up3N3ZyIb2yEUeEnVmy289ge1NNXC6Pzh+Hv3736J/WuzrjPh79+9+if1rs6b3IYUUUUhBWNLqptfE62jn91PCpXPZgW/n/AIVs1wXjeRodehkQ7XWJWUjsQxprUZ3pGRg8ivLde07+zNVngAwmdyf7p6f4fhXplncreWkM6/dkQMPxrk/iBZgNa3QHXMbH9R/WhbgjR/Z+8THwj8aPB+o5xGNRigk+bA2Sny2J+gcn8K/Vmvxot55LWeOaFzHLGwdHXqrA5Br9dfhz4nTxr4B8O66g2/2hYQ3DLnO1mQFl/Bsj8K+Vz2n70Kvqv1P1rgjEXhXwzezUl+T/AEOiooor5U/UAooooAKKKKACob7/AI8rj/rm38qmqG+/48rj/rm38qa3RlV/hy9H+TPKh0FLSDoKWvpD+eUd94M/5Aaf9dH/AJ1uVh+DP+QGn/XR/wCdbleBW/iS9T91yn/kX0P8KCiiisT1gooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACoL/ULXSrKa7vbiO1tYV3STSttVB6k1OSFBJIAHJJ6CvkL4z/ABSn+IGuS2drIyeH7OQrBEDxOynHnN65/hHYc9Tx34PCSxdTlWiW7Pns7zmlk2H9pJc05aRXd935Lr9y1enf+Mv2oo42e38LWAuBjAv79WVT7rFwxH+8R9K8u1b4y+NdYdjL4hurdW/5Z2eIAPoVAP61xlFfY0sFh6KtGC9Xq/xPxHGZ7mWOk3VrNLtF8q+5W/Fs1JfFmvTZ8zXtVfJyc30v/wAVVR9W1CT/AFmpX0n/AF0upG/m1VqK61GK2S+5f5HiurUlvJv5v/M9u+DjtJ4TlZ2Z2+1PyxJPRa7quD+DP/IpSf8AX0/8lrvK8Cv/ABZHsUf4cTlfih/yI+o/8A/9DFeA1798T/8AkR9R/wCAf+hivAa9LB/w36nBi/jXoFFFFd5xBRRRQAUUUUAFFFFABRRRQAUUUUAFcn8UP+ROuv8ArpH/AOhCusrk/ih/yJ11/wBdI/8A0IVUd0C3OB+HrgTXq55KqR+ZrtK8s0LVDo+ox3GNyfdcDup616fb3MV3Ak0LiSNhkMpraRbJKKKKkQV5945kD62AOqRKp+vJ/rXb6lqUGlWjTzthR0Xux9BXl19ePf3k1xJ9+RixHp7VURo9A8GzeboEIJyUZl/XP9aj8bxCTQnY9Y3Vh+eP61F4C/5BEv8A12P8hVrxj/yL1z9U/wDQhS6h1PN6/Qf9hDxsPEHwmudClkDXWh3bIFLZbyZcuhPtu8wD/dr8+K+hP2IfHv8Awifxij0mZttnr8DWh6YEqgvGT+TL9XFedmlH22Flbda/d/wD6vhrGfU8zptvSfuv57fjY/Raiiivzw/fwooooAKKKKACob7/AI8rj/rm38qmqG+/48rj/rm38qa3RlV/hy9H+TPKh0FLSDoKWvpD+eUd94M/5Aaf9dH/AJ1uVh+DP+QGn/XR/wCdbleBW/iS9T91yn/kX0P8KCiiisT1gooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKAOW+Kl1LZfDbxPNBI0Uq6fNtdTgjKkcfnXxKAAABwBX2r8Xf8Akl/in/sHy/8AoNfFdfW5N/Cn6/ofjPHLf12iv7j/APSgooor6A/OAooooA9s+DP/ACKUn/X0/wDJa7yuD+DP/IpSf9fT/wAlrvK+er/xZep7lH+HE5X4n/8AIj6j/wAA/wDQxXgNe/fE/wD5EfUf+Af+hivAa9LB/wAN+pwYv416BRRRXecQUUUUAFFFFABRRRQAUUUUAFFFFABXJ/FD/kTrr/rpH/6EK6yvOfi5ryx2sGkxtmSQiWX2UfdH4nn8KqO41ueW1d03WrzSWJt5iqk5KHlT+FUqK6TQ6hPH94FAa3hY+oyKbL4+vWXCQwofUgmuZopWQrFi+1G51KXzLmVpW7Z6D6DtVeilRGkdUUbmY4AHc0xnovguHytBibGDIzN+uP6U3xtIE0GRSeXdVH55/pWvYWosrKC3HSNAtct8QLsbbW2BGcmQ/wAh/Ws1qyTjataVql1oep2mo2MzW17aSrPBMuMo6kFTzxwQKq0VbV9GWm4u63P188BeL7Xx/wCDNG8RWRH2fUbZJwv9xiPmU+6sCPwrer5C/YC+JgvNI1fwPdzZmtGN/YqxHMTECVR9GIb/AIGa+va/NMXQeGryp9tvTof0jlWOWY4KniVu1r6rR/jr8wooorjPWCiiigAqG+/48rj/AK5t/Kpqhvv+PK4/65t/Kmt0ZVf4cvR/kzyodBS0g6Clr6Q/nlHfeDP+QGn/AF0f+dblYfgz/kBp/wBdH/nW5XgVv4kvU/dcp/5F9D/CgooorE9YKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigDkfi7/AMkv8U/9g+X/ANBr4rr7U+Lv/JL/ABT/ANg+X/0Gviuvrsm/gz9f0Pxjjj/faP8Ag/8AbmFFFFe+fnIUUUUAe2fBn/kUpP8Ar6f+S13lcF8F3WTwhIVYMPtb8g57LXe18/X/AIsvU9yhrSj6HK/E/wD5EfUf+Af+hivAa9++J/8AyJGo/wDAP/QxXgOK9HB/w36nBi/jXoFFGKMV3nEFFGKMUAFFGKMUAFFGKMUAFFGKMUAFFQXV9bWMZkubiK3jHVpXCj9a47xD8VNP05Xi08fb7joHHEan69/w/Omk3sFjoPFHiW28Maa1xMQ0pBEUOeXb/D1NeEalqE+q3013cvvmlYsx/oPan6tq93rd691eTGWVvXoo9AOwqnW8Y8polYKKKKsYUUUUAFdF4L0k3uofaXH7m3556Fuw/Dr+VYun2E2p3aW8C7nY9eyjuT7V6hpmnRaVZR28Q4Ucnux7mpbEy10ry/xHqH9p6vPKDmNTsT6D/OfxrsvF+sDTdOMKNiecFRjqF7mvO6UUCCiiirGdX8K/H938MPH+jeJLQsTZTgzRKf8AWwniRPxUnr3we1frFoetWfiPRbDVdPmFxY30CXEEq9GRlDKfyNfjlX3J+wh8Xxquh3XgLUp83dhuudOLty8BPzxj/dY5+je1fN5zhfaU1XitY7+n/AP0Xg7M/YV5YKo/dnqv8S/zX4o+tqKKK+LP2MKKKKACob7/AI8rj/rm38qmqG+/48rj/rm38qa3RlV/hy9H+TPKh0FLSDoKWvpD+eUd94M/5Aaf9dH/AJ1uVh+DP+QGn/XR/wCdbleBW/iS9T91yn/kX0P8KCiiisT1gooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKAOR+Lv/JL/ABT/ANg+X/0GviuvtT4u/wDJL/FP/YPl/wDQa+K6+uyb+DP1/Q/GOOP99o/4P/bmFFFFe+fnIUnp9aWkYhRkkADkk9qYnsfYni+0gs9USO3git4zEDshjCDOTzgCsSug8b/8hdP+uK/zNc/XyFHWnE+7zhJZjXS/mf6HFfGaRovhvq7IxRsRjKnB++tfLPny/wDPWT/vs19SfGn/AJJrq/0j/wDRi18sV9Hgf4b9T5PFfGvQf58v/PWT/vs0efL/AM9ZP++zTKK9A4x/ny/89ZP++zR58v8Az1k/77NMooAf58v/AD1k/wC+zR58v/PWT/vs0yigB/ny/wDPWT/vs0efL/z1k/77NMooAf58v/PWT/vs0efL/wA9ZP8Avs0yigBjwRStueNHb+8ygmk+yw/88Y/++BUlFMCP7LD/AM8Y/wDvgUn2WA/8sY/++BUtFAGDrfhK11CB3t41t7kDKlBhW9iP61546NG7IwKspwQexr2GvP8AVPDl9fa3eG3tmETSkh2+Veec81SZSOdq5pmk3OrT+Vbpux95jwqj3NdRpngJEIe+m3/9M4uB+Jrqba1is4VigjWKMdFUU3ILlPRNDg0S32R/PK335SOW/wAB7VY1HUYdLtHuJ2wi9AOrH0FM1TVrbSLcy3D4/uoPvMfYV5zrWtT61c+ZL8sa8JGOij/GpSuLch1TUpdVvZLiXq3RR0UdgKq0UVoUFFFFABWz4N8Xal4D8U6b4g0iUQ6jYTCaJmGVPYqw7qQSCPQmsaik0pJxezLhOVOSnB2a1TP12+HXjzTviZ4L0vxJpbf6LfRbzGx+aJxw8be6sCPwrpK/PD9jb46L8NvFzeGtXm2eHtbmVVcji2ujhUcnsrDCse2FPABr9D6/OMdhHhKzh0e3p/wD+hskzSOa4SNX7a0kvP8Aye6+a6BRRRXnnvhUN9/x5XH/AFzb+VTVDff8eVx/1zb+VNboyq/w5ej/ACZ5UOgpaQdBS19IfzyjvvBn/IDT/ro/863Kw/Bn/IDT/ro/863K8Ct/El6n7rlP/Ivof4UFFFFYnrBRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAcj8Xf8Akl/in/sHy/8AoNfFdfanxd/5Jf4p/wCwfL/6DXxXX12TfwZ+v6H4xxx/vtH/AAf+3MKKKK98/OQqC/8A+PG4/wCubfyqeoL/AP48bj/rm38qqO6MqnwS9H+TPs/xv/yF4/8Ariv8zXP10Hjcg6tGRyPJX+Zrn6+Pofwo+h+gZz/yMa/+J/ocR8af+Sa6v9I//Ri18sV9T/Gn/kmur/SP/wBGLXyxX0eB/hv1PksV8a9Aooor0TjCiiigAooooAKKKKACiiigAooooAKKKKACiiuA8ReIdQh1W6t47lo4kbaFQAcfXrQlcZ3N1eQWUZeeVIl9XOK5jVfHcaApYJ5jf89ZBgfgK4yWV5nLyO0jnqzHJptWojsS3d5NfTGWeRpZD/ExqKiiqGFFFFABRRRQAUUUUAFfoP8AsdftAj4ieHV8J63OT4j0mECGaR8m9twMBsk5LrwG9QQfXH58Vo+HfEWpeEtcs9Y0i7ksNSs5BLBcR4yjfjwR1BB4IJBrgxuEjjKTg9+j8z3cmzWplOJVaOsXpJd1/mt1/wAE/YmivNvgP8bdL+N3g5NStdttqttiLUNPz80EmOo9UbqD9R1Br0mvzqpTlSm4TVmj+hKFeniqUa1GV4yV0wqG+/48rj/rm38qmqG+/wCPK4/65t/KoW6Kq/w5ej/JnlQ6ClpB0FLX0h/PKO+8Gf8AIDT/AK6P/OtysPwZ/wAgNP8Aro/863K8Ct/El6n7rlP/ACL6H+FBRRRWJ6wUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFAHI/F3/kl/in/sHy/wDoNfFdfanxd/5Jf4p/7B8v/oNfFdfXZN/Bn6/ofjHHH++0f8H/ALcwooor3z85CoL7/jyuP+ubfyqeoL7/AI8rj/rm38qqO6M6nwS9H+TPsrxb/wAftr/17J/WsStvxYc3tqRyPsyf1rEr5Cj/AA4n3ub/APIwrev6I4j40/8AJNdX+kf/AKMWvlivuBPDGm+MmOj6vbm60+5BEsQkZCccjDKQRyB0NR/8MqfDj/oFXn/gxn/+Kr0KWYUcLHkqJ330/wCHMsPw/jM1h7fDuNk7atrXfs+58R0V9uf8MqfDj/oFXn/gxn/+Ko/4ZU+HH/QKvP8AwYz/APxVbf2zhuz+7/gnV/qZmfeH/gT/APkT4jor7c/4ZU+HH/QKvP8AwYz/APxVH/DKnw4/6BV5/wCDGf8A+Ko/tnDdn93/AAQ/1MzPvD/wJ/8AyJ8R0V9VfFj4TfB/4QeDbrX9X067IQbLa1GpziS5mIO2NRu9uT2AJ7V8l6XfHU7JboxrCJmZ1iUkhFLHCgnk4GBk88V6OGxUMVFypp2XdHzmZ5VWyqcaeIlFyetou9l56K1+haoorK8TX82m6S89uwSQMoBIB6muw8Y1aK84/wCEz1X/AJ7r/wB+1/wo/wCEz1X/AJ7r/wB+1/wp8rHY9Horzj/hM9V/57r/AN+1/wAKP+Ez1X/nuv8A37X/AAo5WFj0eivOP+Ez1X/nuv8A37X/AAo/4TPVf+e6/wDftf8ACjlYWPQL29i0+2eeZgkaDP19h715Ve3TXt5NO3Bkctj0yelSX2qXWpMGuZ2lI6A8AfgKq1aVhoKKKKYwooooAKKKKACiiigAooooAKKKKAOx+FHxT1n4QeMLXXtHk3FPkuLVyRHcxZ+ZG/oexwa/Tz4X/E7RPi34Sttf0OffBJ8k0D/6y2lABaNx2IyPYggjINfklXf/AAW+M2tfBTxYmraYxns5cJfaezYS6jGcAnBwRkkMOQfYkHxcxy9YuPPD41+Pl/kfY8PZ9LKqnsa2tKW/9191+q6777/q3UN9/wAeVx/1zb+Vc58NviVoPxW8Lwa74fuxcWsh2SRNxLbyDrHIv8LD8iCCMgg10d9/x5XH/XNv5V8I4yhPlkrNH7c6kK1B1KbvFptNddGeVDoKWkHQUtfRH8+o77wZ/wAgNP8Aro/863Kw/Bn/ACA0/wCuj/zrcrwK38SXqfuuU/8AIvof4UFFFFYnrBRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAcj8Xf+SX+Kf+wfL/AOg18V19qfF3/kl/in/sHy/+g18V19dk38Gfr+h+Mccf77R/wf8AtzCiiivfPzkKhvf+POf/AK5t/KpqhvP+POf/AK5t/KqW6M6nwS9H+TPsXxJ/rbD/AK84v5VkVr+JDmWwxz/ocX8qyK+Qpfw0feZt/v1b1/SJreFP+Q9bf8C/9BNeiV534U/5D1t/wL/0E16JXm4z+IvQ/QeFP9yn/if5IKKKK4T7QKxfGXjLSPAHhu913XLtbLTbRN0kjdSeyqO7E8ADqad4v8X6R4E8PXmt65ex2Gm2qb5JZD19FUdWYngKOSa/NX9oP9oHVfjl4jDsJLDw7Zu32DTS3Tt5smODIR+CjgdyfUwOBnjJ9ord/ovM+ZzvO6WUUe9R/Cv1fl+ey6tZ3x1+NWp/G3xk+qXYe202AGKwsN+Vgjz1Pbe2AWPsB0Aqfwv/AMgGz/3P6mvMa9O8L/8AIBs/9z+pr76NONKChBWSPwPEV6mJqyrVneUnds1KwvGv/IBl/wB9f51u1heNf+QDL/vr/OmtznR51RRRWpQUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAd38IPjJr/wAGPEyaro0xkt3wt3p8rHybpM9GHZh2bqPoSD+kXw0+L3h/4x+DpdW0O4/eLEVurKQ4mtn2/dYenow4NflFXYfCLxRqvhL4i6Hd6RfS2M8t1HaytGeJIndVdGB4KkHoe4B6gGvHx+XwxS9otJLr39T6vJs/rZZehP3qUr6dr9V+q2fkz9Dx0FLQRg0V4hwnfeDP+QGn/XR/51uVh+DP+QGn/XR/51uV4Fb+JL1P3XKf+RfQ/wAKCiiisT1gooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKAOR+Lv/ACS/xT/2D5f/AEGviuvtT4u/8kv8U/8AYPl/9Br4rr67Jv4M/X9D8Y44/wB9o/4P/bmFFFFe+fnIVHcf8e8v+6f5VJUdx/x7y/7p/lTW5EvhZ9ga793TP+vGH/0GsutPXDlNM/68Yf8A0GsyvkqXwL+u591mv+/VfX9Imt4U/wCQ9bf8C/8AQTXoled+FP8AkPW3/Av/AEE16JXmYz+IvQ/QuFP9yn/if5IK5n4h/EbQfhb4bm1vxDeraWiHaijmSZ+yIvVm/kOTgCuT+N/7Qvhv4J6S5vJV1DXpE3WukQuPMcnOGc/wJkfePocAmvzo+KfxY8QfF/xI+sa/deYVytvax8Q20ZOdiL+WSeTjk12YHLZ4p889Ifn6f5l55xFRyuLpUrSq9ui85f5b97I6D48fH3W/jhr4mui1jodsx+xaWj5SPtvf+85Hft0HfPl1FFfdU6cKMFCCskfiGIxFXF1ZVq8uaT3f9fkFeneF/wDkA2f+5/U15jXp3hf/AJANn/uf1NVI5malYXjX/kAy/wC+v863awvGv/IBl/31/nULcSPOqKKK1KCiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigArc8Cf8jx4e/7CNv/AOjVrDrc8Cf8jx4e/wCwjb/+jVqZfCxrdH6SHqaKD1NFfHHuHfeDP+QGn/XR/wCdblfDHxa+Pfir4LfGlX0a6E+mS2UD3OlXOWgl+Z8kD+BsfxLzwM5AxX0J8HP2pvB3xcigtRcroXiBwA2l3sgBZsciJzgSDg9MHHUCuDFYGtBe2SvF66dPU/VshzzB1qMMHKXLUikrPrbs9vlv6nsdFFFeSfahRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAcj8Xf+SX+Kf+wfL/6DXxXX2p8Xf+SX+Kf+wfL/AOg18V19dk38Gfr+h+Mccf77R/wf+3MKKKK98/OQqOf/AFEn+6f5VJUc/wDqZP8AdP8AKmiZfCz681X/AI9tI/7B8H/oNZ9aGqnNrpGDkf2fB0/3az6+Sp/Av67n3Gaf77V9f0iafhuaO21iGWV1iijV2d3OFUBTkknoK8S+O/7bthoa3Gi/D9o9T1DlJNZYbreI558of8tD/tfd5GN1W/2lWK/BfxBtYqcQjKnHHnJxXwdXpYXAUsRL21XW2lunzM455icBhXhMN7vM7uXXVJWXbbfftYt6vrF94g1K41HU7ye/v7ht8tzcyF5HPqWPJqpRRX0iSSsj5Ntyd27sKKKKYgr07wv/AMgGz/3P6mvMa9O8L/8AIBs/9z+pqZCZqVheNf8AkAy/76/zrdrC8a/8gGX/AH1/nULcSPOqKKK1KCiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigArc8Cf8jx4e/7CNv8A+jVrDrc8Cf8AI8eHv+wjb/8Ao1amXwsa3R+kh6mig9TRXxx7h8V/td/8lZT/ALB0P/oT14mGKkEEgjkEdq9s/a7/AOSsp/2Dof8A0J68Tr6rDfwY+h49X42e+/CH9sjxl8OVgsNWkPirRVbBjvpWNzGueiSnJ47BsjsMCvsj4YftM+A/ioIobDVV07VX/wCYZqOIps46Lztf/gJNfl1RXBicqw+I95Lll3X+X/DH1OW8T47AJQk/aQXSW/ye/wB9z9myCDg8UlfmL8Nv2q/iF8NvIt4dWOsaVH8v2DVB5yhc/wAL/fXjgYbAz0OBX1B8Pv28fBviFY4PE1lc+GLw8GQA3NuTnH3lG4fivHqetfMV8pxNHWK5l5f5bn6VgeKcuxlozl7OXaW337ffY+mqKx/DXjLQfGVmLrQtYsdXt+m+znWTB9Dg8H61sV47Ti7NWProzjOKlB3T7ahRRRSKCiiigAooooAKKKKACiiigAoorivH/wAYvCvw2jK6vqSm+Iymn2o824b0+UfdHu2BVwpzqS5YK78jCviKWGpurXmoxXVuyND4maddav8AD3xFZWUD3V3PZSRxQx/ediOAK+Jrq1msrmW3uInguImKSRSKVZGHUEHoa6n4i/tQ+KfGYltNJJ8M6W2VK2z7rmVf9qXHy/RMf7xryGO5mikMiyuJGOWYsSWPqc9fxr7XLcJVw1NqpbXW39aH4bxPmeEzXEQnhrvlVrvRPW+i3+bt6HY0VhWviFlwJ03j+8nB/KtW2voLsfupAx/ung/lXrWsfFlikZd6lfUYpaB1pCPevhn8SG+Jfh97o2AsI9Nm/stF8zeZBEqgueBgkk8emK66vHf2X/8AkStb/wCw3c/+y17FXztWEadSUY7I+k9rOv8AvajvJ7/18jzD9pb/AJIt4g/7Y/8Ao5K+Dq+8f2lv+SLeIP8Atj/6OSvg9EMjBVBZicADua9rL/4T9TzMT8aEALEAAkngAVfj0DUZVDLZTEH1XFd34f8ADsOjwKzKr3bD55D29hWxXocxx3PLv+Ec1P8A58pv++aP+Ec1P/nym/75r1GijmFc8u/4RzU/+fKb/vmvQPD0Elto1rFKhjkVcFW6jmtGik3cLhWP4stZrzRpIoI2lkLKQqjnrWxRSA8u/wCEc1P/AJ8pv++aP+Ec1P8A58pv++a9RoquYLnl3/COan/z5Tf980ybQ9QgXc9nMF9dhNeqUUcwXPHaK9B8T+G4tQt5LiBAl2g3fL/GB2PvXn1UncoKKKKYBRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAVueBP+R48Pf9hG3/8ARq1h1ueBP+R48Pf9hG3/APRq1MvhY1uj9JD1NFB6mivjj3D4r/a7/wCSsp/2Dof/AEJ68Tr2z9rv/krKf9g6H/0J68Tr6rDfwY+h49X42FFFFdJkFFFFAFvStYv9DvFu9NvbnT7pRhZ7WVonH0ZSDXsXg79sb4neEIUgbWI9dgQBVXWYvPYD3cFXP1LGvEqKwqUKVZWqRT9UdmHxuJwjvh6jj6N/lt+B9r+F/wDgoZZOgXxH4SnhYdZNLnDg++19uPpur1fw5+2P8LPEAVX12TSZSMmPUbZ49vsWAK/rX5o0V5VTJ8LP4U4+j/zPqcPxfmdHSbU15r9VY/XfQviV4S8TqW0jxNpOpAdfs17G5HsQDxXRoRIgZTuU8hl5Br8Za0tJ8T6xoBzperX2mnOc2ly8X/oJFcE8iX2Kn3r/ACZ7tLjh/wDL3D/dL/NH7E4pK/KHTPjz8RdIXFt401kD/prdtL/6GTWxD+1L8VYMbPGd9x/eSJv5pXM8jrdJr8T0Y8bYNr3qUl/4C/1R+o1FfmB/w1j8Wf8Aocrr/wAB4P8A4ilt/wBpr4t6xdpbx+MrzzJOBhIkHAz2Sp/sOv8AzL8f8i3xrgf+fc//ACX/ADP0/wAVyHj74seGPhtb7tb1JI7orujsYf3lxL9EHQe5wPevg4/EXx7qCIdU8b61cspzst7toF/HZgn8ePasiWR7ieWeaR5p5W3SSysWdz6sx5J9zW9LJLO9Wenl/mzzMXxunBxwdGz7yasvkt/m7HtXxG/ao8SeLUms9CQ+GtMcFTJG+bxx7yDhP+A8/wC1XiskjzTSSyu8s0jbnkkYs7nuSTyT7mm0V9HRoUsPHlpRsfnGMx2JzCp7TFTcn+C9FsvkvmFFFFbnAFKDggjgjuKSigDQtdaubfAZvOT0fr+daKeJbBTGLiZbRnbavmnAJ9Aelc9XMePx/wASu3P/AE2H/oJpcqYWPqT9mAg+C9bIOQdbueR/wGvYa8O/ZA/5Jbc/9hKX/wBASvca+bxP8aXqe3S/ho8w/aW/5It4g/7Y/wDo5K+IvDEaya9ZqwyN+cfQE19u/tLf8kW8Qf8AbH/0clfCEcrwuHjdkcdGU4Ir18B/CfqcWJ+M9gorD8G3TXWiqXkaWRXYMWOT6/yNbldxwhRRXmGp6rdHUbryruYR+a+0LIcYycYppXGen0V5P/at7/z+XH/f1v8AGj+1b3/n8uP+/rf40+ULHrFFeT/2re/8/lx/39b/ABo/tW9/5/Lj/v63+NHKFj1iivJ/7Vvf+fy4/wC/rf41peG9UuG1y0We6leMsQQ8hIJIIH64o5QsejUUUVIgryfVYxDql4i/dWZ1H4Ma7PxzePa2VusUzRStJn5GIJAHPT6iuDd2kYsxLMxySTkk1cSkJRRRVDCiiigAooooAKKKKACiiigAooooAKKKKACiiigArX8H3UNj4u0S5uJBFBDfQSSSN0VRIpJP0ArIopNXVhrQ/Qc/HPwBk/8AFV6d/wB/D/hSf8Lz8Af9DXp3/fw/4V+fNFeX/Z9P+Z/gdf1mXY9Y/aZ8T6V4t+JK32j38Oo2YsYo/OhOV3AtkfqK8noor0acFTioLocspczbCiiitCQooooAKKKKACiiigAooooAKKKKACtPw1cxWmt20sziONd2WboPlIrMooA9Q/4SXS/+f2L86P8AhJdL/wCf2L868voqeUVj1D/hJdL/AOf2L86P+El0v/n9i/OvL6KOULHqH/CS6X/z+xfnR/wkul/8/sX515fRRyhY9Q/4SXS/+f2L86P+El0v/n9i/OvL6KOULHqH/CS6X/z+xfnWD4y1Wy1DTI0t7lJXWUNtX0wa42iiwWPqr9mL4l+FvCPw8nstZ1y0067a+kkEU7EMVKoAensa9c/4Xn4A/wChr07/AL+H/Cvz5orgqYGFSTm29TrjiJRSikfYvx7+K/hDxJ8Kda07TPEFle303leXBE5LNiVSccegNfHVFFdNGiqEeVMyqVHUd2avh/X5NDuSceZA/Dx/1HvXbQeLNLnjDfaRGe6uCCK80ordq5jY7PX/ABpE9u9vYFmZxtMxGMD275964yiihKwwooopgFFFFABQrFWDKSCDkEdqKKAO60bxtbzQrHfkxTDgyAZVvy6Gr914u0y2jLCfzm7JGCSf6V5tRU8qFYv61rEutXhmkG1QMJGDwoqhRRVDCiiigAooooAKKKKACiiigD//2Q==';'';
                $this->M_user->saveFoto($base64Image, $now);
                
            //Get Password
            $token = $this->M_perusahaan->getToken($email);
            $username = $this->M_perusahaan->getNama($email);
            
        $isi = '<!doctype html>
                <html>

                    <head>
                        <meta name="viewport" content="width=device-width" />
                        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
                        <title>Simple Transactional Email</title>
                        <style>
                            /* -------------------------------------
                                              GLOBAL RESETS
                                          ------------------------------------- */
                            /*All the styling goes here*/
                            
                            img {
                                border: none;
                                -ms-interpolation-mode: bicubic;
                                max-width: 100%;
                            }
                            
                            body {
                                background-color: #f6f6f6;
                                font-family: sans-serif;
                                -webkit-font-smoothing: antialiased;
                                font-size: 14px;
                                line-height: 1.4;
                                margin: 0;
                                padding: 0;
                                -ms-text-size-adjust: 100%;
                                -webkit-text-size-adjust: 100%;
                            }
                            
                            table {
                                border-collapse: separate;
                                mso-table-lspace: 0pt;
                                mso-table-rspace: 0pt;
                                width: 100%;
                            }
                            
                            table td {
                                font-family: sans-serif;
                                font-size: 14px;
                                vertical-align: top;
                            }
                            /* -------------------------------------
                                              BODY & CONTAINER
                                          ------------------------------------- */
                            
                            .body {
                                background-color: #f6f6f6;
                                width: 100%;
                            }
                            /* Set a max-width, and make it display as block so it will automatically stretch to that width, but will also shrink down on a phone or something */
                            
                            .container {
                                display: block;
                                margin: 0 auto !important;
                                /* makes it centered */
                                max-width: 580px;
                                padding: 10px;
                                width: 580px;
                            }
                            /* This should also be a block element, so that it will fill 100% of the .container */
                            
                            .content {
                                box-sizing: border-box;
                                display: block;
                                margin: 0 auto;
                                max-width: 580px;
                                padding: 10px;
                            }
                            /* -------------------------------------
                                              HEADER, FOOTER, MAIN
                                          ------------------------------------- */
                            
                            .main {
                                background: #ffffff;
                                border-radius: 3px;
                                width: 100%;
                            }
                            
                            .wrapper {
                                box-sizing: border-box;
                                padding: 20px;
                            }
                            
                            .content-block {
                                padding-bottom: 10px;
                                padding-top: 10px;
                            }
                            
                            .footer {
                                clear: both;
                                margin-top: 10px;
                                text-align: center;
                                width: 100%;
                            }
                            
                            .footer td,
                            .footer p,
                            .footer span,
                            .footer a {
                                color: #999999;
                                font-size: 12px;
                                text-align: center;
                            }
                            /* -------------------------------------
                                              TYPOGRAPHY
                                          ------------------------------------- */
                            
                            h1,
                            h2,
                            h3,
                            h4 {
                                color: #000000;
                                font-family: sans-serif;
                                font-weight: 400;
                                line-height: 1.4;
                                margin: 0;
                                margin-bottom: 30px;
                            }
                            
                            h1 {
                                font-size: 35px;
                                font-weight: 300;
                                text-align: center;
                                text-transform: capitalize;
                            }
                            
                            p,
                            ul,
                            ol {
                                font-family: sans-serif;
                                font-size: 14px;
                                font-weight: normal;
                                margin: 0;
                                margin-bottom: 15px;
                            }
                            
                            p li,
                            ul li,
                            ol li {
                                list-style-position: inside;
                                margin-left: 5px;
                            }
                            
                            a {
                                color: #3498db;
                                text-decoration: underline;
                            }
                            /* -------------------------------------
                                              BUTTONS
                                          ------------------------------------- */
                            
                            .btn {
                                box-sizing: border-box;
                                width: 100%;
                            }
                            
                            .btn > tbody > tr > td {
                                padding-bottom: 15px;
                            }
                            
                            .btn table {
                                width: auto;
                            }
                            
                            .btn table td {
                                background-color: #ffffff;
                                border-radius: 5px;
                                text-align: center;
                            }
                            
                            .btn a {
                                background-color: #ffffff;
                                border: solid 1px #3498db;
                                border-radius: 5px;
                                box-sizing: border-box;
                                color: #3498db;
                                cursor: pointer;
                                display: inline-block;
                                font-size: 14px;
                                font-weight: bold;
                                margin: 0;
                                padding: 12px 25px;
                                text-decoration: none;
                                text-transform: capitalize;
                            }
                            
                            .btn-primary table td {
                                background-color: #3498db;
                            }
                            
                            .btn-primary a {
                                background-color: #3498db;
                                border-color: #3498db;
                                color: #ffffff;
                            }
                            /* -------------------------------------
                                              OTHER STYLES THAT MIGHT BE USEFUL
                                          ------------------------------------- */
                            
                            .last {
                                margin-bottom: 0;
                            }
                            
                            .first {
                                margin-top: 0;
                            }
                            
                            .align-center {
                                text-align: center;
                            }
                            
                            .align-right {
                                text-align: right;
                            }
                            
                            .align-left {
                                text-align: left;
                            }
                            
                            .clear {
                                clear: both;
                            }
                            
                            .mt0 {
                                margin-top: 0;
                            }
                            
                            .mb0 {
                                margin-bottom: 0;
                            }
                            
                            .preheader {
                                color: transparent;
                                display: none;
                                height: 0;
                                max-height: 0;
                                max-width: 0;
                                opacity: 0;
                                overflow: hidden;
                                mso-hide: all;
                                visibility: hidden;
                                width: 0;
                            }
                            
                            .powered-by a {
                                text-decoration: none;
                            }
                            
                            hr {
                                border: 0;
                                border-bottom: 1px solid #f6f6f6;
                                margin: 20px 0;
                            }
                            /* -------------------------------------
                                              RESPONSIVE AND MOBILE FRIENDLY STYLES
                                          ------------------------------------- */
                            
                            @media only screen and (max-width: 620px) {
                                table[class=body] h1 {
                                    font-size: 28px !important;
                                    margin-bottom: 10px !important;
                                }
                                table[class=body] p,
                                table[class=body] ul,
                                table[class=body] ol,
                                table[class=body] td,
                                table[class=body] span,
                                table[class=body] a {
                                    font-size: 16px !important;
                                }
                                table[class=body] .wrapper,
                                table[class=body] .article {
                                    padding: 10px !important;
                                }
                                table[class=body] .content {
                                    padding: 0 !important;
                                }
                                table[class=body] .container {
                                    padding: 0 !important;
                                    width: 100% !important;
                                }
                                table[class=body] .main {
                                    border-left-width: 0 !important;
                                    border-radius: 0 !important;
                                    border-right-width: 0 !important;
                                }
                                table[class=body] .btn table {
                                    width: 100% !important;
                                }
                                table[class=body] .btn a {
                                    width: 100% !important;
                                }
                                table[class=body] .img-responsive {
                                    height: auto !important;
                                    max-width: 100% !important;
                                    width: auto !important;
                                }
                            }
                            /* -------------------------------------
                                              PRESERVE THESE STYLES IN THE HEAD
                                          ------------------------------------- */
                            
                            @media all {
                                .ExternalClass {
                                    width: 100%;
                                }
                                .ExternalClass,
                                .ExternalClass p,
                                .ExternalClass span,
                                .ExternalClass font,
                                .ExternalClass td,
                                .ExternalClass div {
                                    line-height: 100%;
                                }
                                .apple-link a {
                                    color: inherit !important;
                                    font-family: inherit !important;
                                    font-size: inherit !important;
                                    font-weight: inherit !important;
                                    line-height: inherit !important;
                                    text-decoration: none !important;
                                }
                                .btn-primary table td:hover {
                                    background-color: #34495e !important;
                                }
                                .btn-primary a:hover {
                                    background-color: #34495e !important;
                                    border-color: #34495e !important;
                                }
                            }
                        </style>
                    </head>

                    <body class="">
                        <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="body">
                            <tr>
                                <td>&nbsp;</td>
                                <td class="container">
                                    <div class="content">

                                        <!-- START CENTERED WHITE CONTAINER -->
                                        <span class="preheader">This is preheader text. Some clients will show this text as a preview.</span>
                                        <table role="presentation" class="main">

                                            <!-- START MAIN CONTENT AREA -->
                                            <tr>
                                                <td class="wrapper">
                                                    <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                                                        <tr>
                                                            <td>
                                                                <p>Hallo '.$username.'!</p>
                                                                <br>
                                                                <a href="http://pkyuk.com/jkt/services/api/pengguna?pil=3&token='.$token.'">
                                                                    <p>Verifikasikan akunmu disini ya!</p>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>

                                            <!-- END MAIN CONTENT AREA -->
                                        </table>

                                        <!-- START FOOTER -->
                                        <div class="footer">
                                            <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                                                <tr>
                                                    <td class="content-block powered-by">
                                                        <a href="http://htmlemail.io">PK YUK</a>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                        <!-- END FOOTER -->

                                        <!-- END CENTERED WHITE CONTAINER -->
                                    </div>
                                </td>
                                <td>&nbsp;</td>
                            </tr>
                        </table>
                    </body>

                    </html>';
            
            $mail = new PHPMailer();  // create a new object
            $mail->IsSMTP(); // enable SMTP
            $mail->SMTPDebug = 2;  // debugging: 1 = errors and messages, 2 = messages only
            $mail->SMTPAuth = true;  // authentication enabled
            $mail->SMTPSecure = 'ssl'; // secure transfer enabled REQUIRED for GMail
            $mail->SMTPAutoTLS = false;
            $mail->Host = 'smtp.gmail.com';
            $mail->Port = 465;
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );
            $mail->isHTML(true);
    
            $mail->Username = 'pkyuk115@gmail.com';  
            $mail->Password = 'kemalidris123';           
            $mail->SetFrom('pkyuk115@gmail.com', 'PK YUK');
            $mail->Subject = 'Verifikasi Email!';
            $mail->Body = $isi;
            $mail->AddAddress($email);
    
            
            //Send mail
            $success = array(
                'status' => 'success',
            );
    
            $fail = array(
                $error => 'Mail error: '.$mail->ErrorInfo,
            );
            
            if(!$mail->Send()) {
                $this->response($fail, 404);
            } else {
                $this->response($success, 200);
            }
            }else{
                $this->response($fail, 502);
            }
        }
        else if($pil === '4'){
            $username = $this->post('username');
            $password = md5($this->post('password'));
        
            $id = array(
                'username' => $username,
            );

            
            $data_pengguna = array(
                'password' => $password,
            );

            $update_pengguna = $this->Model_main->updateRecord($this->tbl_pengguna, $data_pengguna, $id);

            $success = array(
                'status' => 'success',
            );

            $fail = array(
                'status' => 'failed'
            );
            
            if ($update_pengguna){
                $this->response($success, 201);
            }else{
                $this->response($fail, 502);
            }
        }else if($pil == 5){
            $email = $this->post('email');
            //Get Password
            $token = $this->M_perusahaan->getToken($email);
            $username = $this->M_perusahaan->getNama($email);
        $verifurl = 'http://lini-kerja.pkyuk.com/forget?token='.$token;
            
        $isi = '
                <!doctype html>
                <html>

                    <head>
                        <meta name="viewport" content="width=device-width" />
                        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
                        <title>Simple Transactional Email</title>
                        <style>
                            /* -------------------------------------
                                              GLOBAL RESETS
                                          ------------------------------------- */
                            /*All the styling goes here*/
                            
                            img {
                                border: none;
                                -ms-interpolation-mode: bicubic;
                                max-width: 100%;
                            }
                            
                            body {
                                background-color: #f6f6f6;
                                font-family: sans-serif;
                                -webkit-font-smoothing: antialiased;
                                font-size: 14px;
                                line-height: 1.4;
                                margin: 0;
                                padding: 0;
                                -ms-text-size-adjust: 100%;
                                -webkit-text-size-adjust: 100%;
                            }
                            
                            table {
                                border-collapse: separate;
                                mso-table-lspace: 0pt;
                                mso-table-rspace: 0pt;
                                width: 100%;
                            }
                            
                            table td {
                                font-family: sans-serif;
                                font-size: 14px;
                                vertical-align: top;
                            }
                            /* -------------------------------------
                                              BODY & CONTAINER
                                          ------------------------------------- */
                            
                            .body {
                                background-color: #f6f6f6;
                                width: 100%;
                            }
                            /* Set a max-width, and make it display as block so it will automatically stretch to that width, but will also shrink down on a phone or something */
                            
                            .container {
                                display: block;
                                margin: 0 auto !important;
                                /* makes it centered */
                                max-width: 580px;
                                padding: 10px;
                                width: 580px;
                            }
                            /* This should also be a block element, so that it will fill 100% of the .container */
                            
                            .content {
                                box-sizing: border-box;
                                display: block;
                                margin: 0 auto;
                                max-width: 580px;
                                padding: 10px;
                            }
                            /* -------------------------------------
                                              HEADER, FOOTER, MAIN
                                          ------------------------------------- */
                            
                            .main {
                                background: #ffffff;
                                border-radius: 3px;
                                width: 100%;
                            }
                            
                            .wrapper {
                                box-sizing: border-box;
                                padding: 20px;
                            }
                            
                            .content-block {
                                padding-bottom: 10px;
                                padding-top: 10px;
                            }
                            
                            .footer {
                                clear: both;
                                margin-top: 10px;
                                text-align: center;
                                width: 100%;
                            }
                            
                            .footer td,
                            .footer p,
                            .footer span,
                            .footer a {
                                color: #999999;
                                font-size: 12px;
                                text-align: center;
                            }
                            /* -------------------------------------
                                              TYPOGRAPHY
                                          ------------------------------------- */
                            
                            h1,
                            h2,
                            h3,
                            h4 {
                                color: #000000;
                                font-family: sans-serif;
                                font-weight: 400;
                                line-height: 1.4;
                                margin: 0;
                                margin-bottom: 30px;
                            }
                            
                            h1 {
                                font-size: 35px;
                                font-weight: 300;
                                text-align: center;
                                text-transform: capitalize;
                            }
                            
                            p,
                            ul,
                            ol {
                                font-family: sans-serif;
                                font-size: 14px;
                                font-weight: normal;
                                margin: 0;
                                margin-bottom: 15px;
                            }
                            
                            p li,
                            ul li,
                            ol li {
                                list-style-position: inside;
                                margin-left: 5px;
                            }
                            
                            a {
                                color: #3498db;
                                text-decoration: underline;
                            }
                            /* -------------------------------------
                                              BUTTONS
                                          ------------------------------------- */
                            
                            .btn {
                                box-sizing: border-box;
                                width: 100%;
                            }
                            
                            .btn > tbody > tr > td {
                                padding-bottom: 15px;
                            }
                            
                            .btn table {
                                width: auto;
                            }
                            
                            .btn table td {
                                background-color: #ffffff;
                                border-radius: 5px;
                                text-align: center;
                            }
                            
                            .btn a {
                                background-color: #ffffff;
                                border: solid 1px #3498db;
                                border-radius: 5px;
                                box-sizing: border-box;
                                color: #3498db;
                                cursor: pointer;
                                display: inline-block;
                                font-size: 14px;
                                font-weight: bold;
                                margin: 0;
                                padding: 12px 25px;
                                text-decoration: none;
                                text-transform: capitalize;
                            }
                            
                            .btn-primary table td {
                                background-color: #3498db;
                            }
                            
                            .btn-primary a {
                                background-color: #3498db;
                                border-color: #3498db;
                                color: #ffffff;
                            }
                            /* -------------------------------------
                                              OTHER STYLES THAT MIGHT BE USEFUL
                                          ------------------------------------- */
                            
                            .last {
                                margin-bottom: 0;
                            }
                            
                            .first {
                                margin-top: 0;
                            }
                            
                            .align-center {
                                text-align: center;
                            }
                            
                            .align-right {
                                text-align: right;
                            }
                            
                            .align-left {
                                text-align: left;
                            }
                            
                            .clear {
                                clear: both;
                            }
                            
                            .mt0 {
                                margin-top: 0;
                            }
                            
                            .mb0 {
                                margin-bottom: 0;
                            }
                            
                            .preheader {
                                color: transparent;
                                display: none;
                                height: 0;
                                max-height: 0;
                                max-width: 0;
                                opacity: 0;
                                overflow: hidden;
                                mso-hide: all;
                                visibility: hidden;
                                width: 0;
                            }
                            
                            .powered-by a {
                                text-decoration: none;
                            }
                            
                            hr {
                                border: 0;
                                border-bottom: 1px solid #f6f6f6;
                                margin: 20px 0;
                            }
                            /* -------------------------------------
                                              RESPONSIVE AND MOBILE FRIENDLY STYLES
                                          ------------------------------------- */
                            
                            @media only screen and (max-width: 620px) {
                                table[class=body] h1 {
                                    font-size: 28px !important;
                                    margin-bottom: 10px !important;
                                }
                                table[class=body] p,
                                table[class=body] ul,
                                table[class=body] ol,
                                table[class=body] td,
                                table[class=body] span,
                                table[class=body] a {
                                    font-size: 16px !important;
                                }
                                table[class=body] .wrapper,
                                table[class=body] .article {
                                    padding: 10px !important;
                                }
                                table[class=body] .content {
                                    padding: 0 !important;
                                }
                                table[class=body] .container {
                                    padding: 0 !important;
                                    width: 100% !important;
                                }
                                table[class=body] .main {
                                    border-left-width: 0 !important;
                                    border-radius: 0 !important;
                                    border-right-width: 0 !important;
                                }
                                table[class=body] .btn table {
                                    width: 100% !important;
                                }
                                table[class=body] .btn a {
                                    width: 100% !important;
                                }
                                table[class=body] .img-responsive {
                                    height: auto !important;
                                    max-width: 100% !important;
                                    width: auto !important;
                                }
                            }
                            /* -------------------------------------
                                              PRESERVE THESE STYLES IN THE HEAD
                                          ------------------------------------- */
                            
                            @media all {
                                .ExternalClass {
                                    width: 100%;
                                }
                                .ExternalClass,
                                .ExternalClass p,
                                .ExternalClass span,
                                .ExternalClass font,
                                .ExternalClass td,
                                .ExternalClass div {
                                    line-height: 100%;
                                }
                                .apple-link a {
                                    color: inherit !important;
                                    font-family: inherit !important;
                                    font-size: inherit !important;
                                    font-weight: inherit !important;
                                    line-height: inherit !important;
                                    text-decoration: none !important;
                                }
                                .btn-primary table td:hover {
                                    background-color: #34495e !important;
                                }
                                .btn-primary a:hover {
                                    background-color: #34495e !important;
                                    border-color: #34495e !important;
                                }
                            }
                        </style>
                    </head>

                    <body class="">
                        <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="body">
                            <tr>
                                <td>&nbsp;</td>
                                <td class="container">
                                    <div class="content">

                                        <!-- START CENTERED WHITE CONTAINER -->
                                        <span class="preheader">This is preheader text. Some clients will show this text as a preview.</span>
                                        <table role="presentation" class="main">

                                            <!-- START MAIN CONTENT AREA -->
                                            <tr>
                                                <td class="wrapper">
                                                    <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                                                        <tr>
                                                            <td>
                                                                <p>Hallo '.$username.'!</p>
                                                                <br>
                                                                <a href="'.$verifurl.'">
                                                                    <p>Klik disini untuk Reset password!</p>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>

                                            <!-- END MAIN CONTENT AREA -->
                                        </table>

                                        <!-- START FOOTER -->
                                        <div class="footer">
                                            <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                                                <tr>
                                                    <td class="content-block powered-by">
                                                        <a href="http://htmlemail.io">PK YUK</a>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                        <!-- END FOOTER -->

                                        <!-- END CENTERED WHITE CONTAINER -->
                                    </div>
                                </td>
                                <td>&nbsp;</td>
                            </tr>
                        </table>
                    </body>

                    </html>';
            
            $mail = new PHPMailer();  // create a new object
            $mail->IsSMTP(); // enable SMTP
            $mail->SMTPDebug = 2;  // debugging: 1 = errors and messages, 2 = messages only
            $mail->SMTPAuth = true;  // authentication enabled
            $mail->SMTPSecure = 'ssl'; // secure transfer enabled REQUIRED for GMail
            $mail->SMTPAutoTLS = false;
            $mail->Host = 'smtp.gmail.com';
            $mail->Port = 465;
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );
            $mail->isHTML(true);
    
            $mail->Username = 'pkyuk115@gmail.com';  
            $mail->Password = 'kemalidris123';           
            $mail->SetFrom('pkyuk115@gmail.com', 'PK YUK');
            $mail->Subject = 'Forget Password!';
            $mail->Body = $isi;
            $mail->AddAddress($email);
    
            
            //Send mail
            $success = array(
                'status' => 'success',
            );
    
            $fail = array(
                $error => 'Mail error: '.$mail->ErrorInfo,
            );
            
            if(!$mail->Send()) {
                $this->response($fail, 404);
            } else {
                $this->response($success, 200);
            }

        }
        else if($pil === '6'){
            $token = $this->post('token');
            $password = md5($this->post('password'));
        
            $id = array(
                'token' => $token,
            );

            
            $data_pengguna = array(
                'password' => $password,
            );

            $update_pengguna = $this->Model_main->updateRecord($this->tbl_pengguna, $data_pengguna, $id);

            $success = array(
                'status' => 'success',
            );

            $fail = array(
                'status' => 'failed'
            );
            
            if ($update_pengguna){
                $this->response($success, 201);
            }else{
                $this->response($fail, 502);
            }
        }
        else if($pil === '7'){
            $now = date("Ymd_his");
            $foto = 'data:image/jpeg;base64,'.$this->post('foto');
            $id_pelamar = $this->post('id_pelamar');
        
            $id = array(
                'id_pelamar' => $id_pelamar,
            );

            
            $data_pengguna = array(
                'foto' => $now,
            );

            $update_pengguna = $this->Model_main->updateRecord($this->tbl_pelamar, $data_pengguna, $id);

            $success = array(
                'status' => 'success',
            );

            $fail = array(
                'status' => 'failed'
            );
            
            if ($update_pengguna){
                $this->M_user->saveFoto($foto, $now);
                $this->response($success, 201);
            }else{
                $this->response($fail, 502);
            }
        }
        else if($pil === '8'){
            $now = date("Ymd_his");
            $foto = 'data:image/jpeg;base64,'.$this->post('foto');
            $id_pengguna = $this->post('id_pengguna');
        
            $id = array(
                'id_pengguna' => $id_pengguna,
            );

            
            $data_pengguna = array(
                'foto' => $now,
            );

            $update_pengguna = $this->Model_main->updateRecord($this->tbl_perusahaan, $data_pengguna, $id);

            $success = array(
                'status' => 'success',
            );

            $fail = array(
                'status' => 'failed'
            );
            
            if ($update_pengguna){
                $this->M_user->saveFoto($foto, $now);
                $this->response($success, 201);
            }else{
                $this->response($fail, 502);
            }
        }
        
    }

    function index_put() {
    }

}
?>