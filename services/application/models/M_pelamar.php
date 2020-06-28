<?php if(!defined('BASEPATH')) exit('No direct script allowed');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
class M_pelamar extends CI_Model{
	
	public function getIdLamaran(){
		$this->db->select('*');
		$this->db->from('tbl_lamaran');
		$this->db->order_by('id_lamaran', "desc"); 
		$this->db->limit(1);

		$res_log = $this->db->get();
		if($res_log->result()){
			$result_log = $res_log->result();

			$char_id = substr($result_log[0]->id_lamaran, 0, 3);
			$id = substr($result_log[0]->id_lamaran, 3);

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
			return 'LAM001';
		}
	}
	
	public function getIdAlternatif(){
		$this->db->select('*');
		$this->db->from('tbl_alternatif');
		$this->db->order_by('id_alternatif', "desc"); 
		$this->db->limit(1);

		$res_log = $this->db->get();
		if($res_log->result()){
			$result_log = $res_log->result();

			$char_id = substr($result_log[0]->id_alternatif, 0, 5);
			$id = substr($result_log[0]->id_alternatif, 5);

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
			return 'ALTER001';
		}
	}
	
	public function getPengalamanByIdPelamar($id_pelamar){
		$query = $this->db->select('*')
		->from('tbl_pengalaman_kerja')
		->where('id_pelamar', $id_pelamar)
		->get();

        return $query->result_array();
	}
	
	
	public function getCountLamaran($id_pelamar){
		$query = $this->db->select('COUNT(*) as "menunggu"')
		->from('tbl_lamaran')
		->where('id_pelamar', $id_pelamar)
		->where('status', 1)
		->get();
        
        $result_log = $query->result();
        $count_menunggu = $result_log[0]->menunggu;
        
		$query = $this->db->select('COUNT(*) as "selesai"')
		->from('tbl_lamaran')
// 		->where('id_pelamar', $id_pelamar)
// 		->where('status', '4')
// 		->where('status', '5')
		->where("id_pelamar='$id_pelamar' AND (status='4' OR status='5')", NULL, FALSE)
		->get();

        $result_log = $query->result();
        $count_selesai = $result_log[0]->selesai;
        
		$query = $this->db->select('foto')
		->from('tbl_pelamar')
		->where('id_pelamar', $id_pelamar)
		->get();

        $result_log = $query->result();
        $foto_pelamar = $result_log[0]->foto;
        
        $result = array(
            "menunggu" => $count_menunggu,
            "selesai" => $count_selesai,
            "foto" => $foto_pelamar
            );
            
        return array($result);
	}
	
	public function getNilaiByIdPelamar($id_pelamar){
		$query = $this->db->select('t2.id_pendidikan_terakhir, t1.id_pelamar, t2.id_jenis_pendidikan, t1.id_nilai, t1.nilai')
		->from('tbl_nilai as t1')
		->join('tbl_pendidikan_terakhir as t2', 't1.id_pelamar = t2.id_pelamar', 'LEFT')
		->where('t1.id_pelamar', $id_pelamar)
		->get();

        return $query->result_array();
	}
	
	public function getLowonganByIdPelamarAndStatus($id_pelamar, $status){
		$query = $this->db->select('t1.*,t3.nama as nama_perusahaan, t3.foto')
		->from('tbl_lowongan as t1')
		->where('t2.id_pelamar', $id_pelamar)
		->where('t2.status', $status)
		->join('tbl_lamaran as t2', 't1.id_lowongan = t2.id_lowongan', 'LEFT')
		->join('tbl_perusahaan as t3', 't1.id_perusahaan = t3.id_perusahaan', 'LEFT')
		->get();

        return $query->result_array();
	}
	
	public function getLowonganByIdPelamarAndDone($id_pelamar, $status){
		$query = $this->db->select('t1.*,t3.nama as nama_perusahaan, t3.foto')
		->from('tbl_lowongan as t1')
		->where('t2.id_pelamar', $id_pelamar)
		->where("t2.id_pelamar='$id_pelamar' AND (t2.status='4' OR t2.status='5')", NULL, FALSE)
		->join('tbl_lamaran as t2', 't1.id_lowongan = t2.id_lowongan', 'LEFT')
		->join('tbl_perusahaan as t3', 't1.id_perusahaan = t3.id_perusahaan', 'LEFT')
		->get();

        return $query->result_array();
	}
	
	public function getPelamarById($id_pelamar){
		$query = $this->db->select('*')
		->from('tbl_pelamar')
		->where('id_pelamar', $id_pelamar)
		->get();

        return $query->result_array();
	}
	
	public function getPendidikan($id_pelamar){
		$this->db->select('*');
		$this->db->from('tbl_pendidikan_terakhir');
		$this->db->where('id_pelamar', $id_pelamar); 
		$this->db->limit(1);

		$res_log = $this->db->get();
		if($res_log->result()){
			$result = $res_log->result();

			return $result;
		}else{
			return '0';
		}
	}
	
	public function checkStatus($id_lowongan, $id_pelamar){
		$this->db->select('status as nama');
		$this->db->from('tbl_lamaran');
		$this->db->where('id_lowongan', $id_lowongan); 
		$this->db->where('id_pelamar', $id_pelamar); 
		$this->db->limit(1);

		$res_log = $this->db->get();
		if($res_log->result()){
			$result = $res_log->result();

			return $result;
		}else{
			return '0';
		}
	}
	
    public function getSertifikatByIdPelamar($id_pelamar) {
        $query = $this->db->select('*')
        ->from('tbl_sertifikat_keahlian as t1')
        ->where('t1.id_pelamar', $id_pelamar)
        ->get();
        
        return $query->result_array();
	}	
	
	
	public function getIdSertifikat(){
		$this->db->select('*');
		$this->db->from('tbl_sertifikat_keahlian');
		$this->db->order_by('id_sertifikat_keahlian', "desc"); 
		$this->db->limit(1);

		$res_log = $this->db->get();
		if($res_log->result()){
			$result_log = $res_log->result();

			$char_id = substr($result_log[0]->id_sertifikat_keahlian, 0, 4);
			$id = substr($result_log[0]->id_sertifikat_keahlian, 4);

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
			return 'SERT001';
		}
	}
	
	public function getIdPendidikan(){
		$this->db->select('*');
		$this->db->from('tbl_pendidikan_terakhir');
		$this->db->order_by('id_pendidikan_terakhir', "desc"); 
		$this->db->limit(1);

		$res_log = $this->db->get();
		if($res_log->result()){
			$result_log = $res_log->result();

			$char_id = substr($result_log[0]->id_pendidikan_terakhir, 0, 5);
			$id = substr($result_log[0]->id_pendidikan_terakhir, 5);

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
			return 'PENDT001';
		}
	}
	
	public function getIdNilai(){
		$this->db->select('*');
		$this->db->from('tbl_nilai');
		$this->db->order_by('id_nilai', "desc"); 
		$this->db->limit(1);

		$res_log = $this->db->get();
		if($res_log->result()){
			$result_log = $res_log->result();

			$char_id = substr($result_log[0]->id_nilai, 0, 3);
			$id = substr($result_log[0]->id_nilai, 3);

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
			return 'NIL001';
		}
	}
	
	public function getIdPengalaman(){
		$this->db->select('*');
		$this->db->from('tbl_pengalaman_kerja');
		$this->db->order_by('id_pengalaman_kerja', "desc"); 
		$this->db->limit(1);

		$res_log = $this->db->get();
		if($res_log->result()){
			$result_log = $res_log->result();

			$char_id = substr($result_log[0]->id_pengalaman_kerja, 0, 7);
			$id = substr($result_log[0]->id_pengalaman_kerja, 7);

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
			return 'PGLMKRJ001';
		}
	}
	
	public function sendMail($token, $username, $email){
            global $error;
	    
            
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
            $mail->Send();
            
	}
}