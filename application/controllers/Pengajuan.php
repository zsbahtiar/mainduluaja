<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pengajuan extends CI_Controller{


	public function __construct()
	{
		parent::__construct();
		if($this->session->userdata('login') != true ) redirect('login');
		
		$this->load->model('Pengajuan_model');
		$this->load->library('form_validation');
		date_default_timezone_set('Asia/Jakarta');
	}

	public function judul()
	{
		if($this->session->userdata('level') == 1) redirect('../login');

		$model = $this->Pengajuan_model;
		$checkIsHave = $model->checkIsHave();
		if(!$checkIsHave){
			$validasi = $this->form_validation;
		
			$validasi->set_rules('nim','NIM','required|trim');
			$validasi->set_rules('nama','Nama','required|trim');
			$validasi->set_rules('judul','Judul','required|trim');
			$validasi->set_rules('latarbelakang','latarbelakang','required|trim');
			$validasi->set_rules('tujuan','tujuan','required|trim');

			if($validasi->run()){
				$model->insert_pengajuan();
				$this->session->set_flashdata('Success','Berhasil menyimpan data');
			}

			$data['nama'] = $this->session->userdata('nama');
			$data['title'] = 'Pengajuan Judul';
			$this->load->view('layout/mahasiswa/header',$data);
			$this->load->view('pengajuan/judul',$data);
		}else{
			redirect('pengajuan');
		}
		
	}
	public function index()
	{
		$model = $this->Pengajuan_model;
		$data['title'] = 'Daftar Pengajuan';
		$data['model'] = $model; 
		$data['nama'] = $this->session->userdata('nama');
		if($this->session->userdata('level')==0){
			$data['pengajuans'] = $model->get_pengajuan();
			$this->load->view('layout/admin/header',$data);
			$this->load->view('pengajuan/list',$data);
		}elseif($this->session->userdata('level')==1){
			$dosen = $this->session->userdata('id');
			$data['proposals'] = $model->getProposalBy($dosen);
			$data['pengajuans']=$model->get_pengajuan_by($dosen);
			$data['kompres'] = $model->getKompreByDosen();
			$data['thesisies'] = $model->getThesisByDosen();
			$this->load->view('layout/dosen/header',$data);
			$this->load->view('pengajuan/list_dosen',$data);
		}elseif($this->session->userdata('level')==2){
			$data['pengajuans'] = $model->get_pengajuanmhs();
			$this->load->view('layout/mahasiswa/header',$data);
			$this->load->view('pengajuan/list_mahasiswa',$data);
		}
	}
	
	public function hapus($id=null)
	{
		if($this->session->userdata('level') != '0'){
			redirect('pengajuan');
		}elseif(!isset($id)){
			redirect('pengajuan');
		}else{

			$model = $this->Pengajuan_model;
			$hapus = $model->delete_pengajuan($id);
			
			if($hapus){
				$this->session->set_flashdata('success_delete','Pengajuan Berhasil Dihapus!');
				redirect('pengajuan');
			}else{
				$this->session->set_flashdata('failed_delete','Pengajuan Gagal Dihapus!');
			}
		}
		

	}
	public function get_penentuan($id){
		$model = $this->Pengajuan_model;
		$data = $model->get_select_penentuan($id);

		echo json_encode($data);
	}
	public function get_judul($id){
		$model = $this->Pengajuan_model;
		$data = $model->get_select_pengajuan($id);

		echo json_encode($data);
	}
	public function get_pengajuan(){
		$model = $this->Pengajuan_model;
		$data = $model->get_pengajuan();

		echo json_encode($data);
	}
	public function update(){
		if($this->session->userdata('level') == 0){
			if($this->input->method()=="post"){
				$model = $this->Pengajuan_model;

				$id = $this->input->post('id');
				$status = $this->input->post('status');

				if($status == 'terima'){
					$data = array(
						'status'		=> $status,
						'keterangan'	=> NULL,
						'tglditerima'	=> date("Y-m-d H:i:s")
					);
				}elseif($status == 'tolak'){
					$data = array(
						'status'		=> $status,
						'keterangan'	=> $this->input->post('alasan'),
						'tglditerima'	=> date("Y-m-d H:i:s")
					);
				}elseif($status == 'belum'){
					$data = array(
						'status'		=> $status,
						'keterangan'	=> NULL,
						'tglditerima'	=> NULL
					);
				}elseif($status == 'penentuan'){
					$data = array(
						'id_pembimbing1' => $this->input->post('pembimbing1'),
						'id_pembimbing2' => $this->input->post('pembimbing2'),
					);
				}
				$update = $model->update($id,$data);

				if($update){
					$this->session->set_flashdata('success_upd','Berhasil Mengubah Data!');
				}else{
					$this->session->set_flashdata('failed_upd','Gagal Mengubah Data!');
				}
			}
		}
	}

	
	public function detail($id=null,$prop=null){
		$model = $this->Pengajuan_model;
		if(!isset($id)){
			redirect('pengajuan');
		}elseif (isset($id) && $this->session->userdata('level') == '2') {
			$data['pengajuans'] = $model->get_select_pengajuan2($id);
			if($this->input->method() == "post" && $prop == null){
				$model->insert_proposal($id);
			}
			if($this->input->method() == "post" && isset($prop)){
				$model->update_proposal($prop);
				redirect('pengajuan/detail/'.$id);
			}
			$ProposalCheck = $model->is_there_proposal($id);

			if(!$ProposalCheck){
				$data['status_proposal'] = 'belum';
			}else{

				$data['status_proposal'] = 'sudah';

				$data['proposal'] = $ProposalCheck;
				if($ProposalCheck["revisi"] != NULL){

					$data['penguji1'] = $model->searchDosenBy($ProposalCheck["id_penguji1"]);
					$data['penguji2'] = $model->searchDosenBy($ProposalCheck["id_penguji2"]);
					$data['penguji3'] = $model->searchDosenBy($ProposalCheck["id_penguji3"]);
					$data['nilai_total'] = $model->getNilaiProposal($id);
					
					$data['cariDosbing1'] = $model->get_dosen($data['pengajuans']['pembimbing1']);
					$data['cariDosbing2'] = $model->get_dosen($data['pengajuans']['pembimbing2']);
					$data["checkKompre"] = $this->_isHaveKompre($id);
					$data["kompreData"] = $this->_KompreMahasiswa();
					$cektesis = $model->isBimbinganDone();
					if(!$cektesis){
						$data['tesis_is'] = 'tidak';
					}else{
						$data['tesis_is'] = 'ada';
						$data["checkThesis"] = $this->_cekTesis($id);
					}
					$data["model"] = $model;
				}
			}
			$data['title'] = 'Detail Pengajuan';
			$data['pengajuan'] = $model->get_select_pengajuan2($id);
			if($prop =='cetak'){
				$body = $this->load->view('pengajuan/cetak_detail_mahasiswa',$data,TRUE);
				$style = file_get_contents(base_url('assets/dist/css/cetak.css'));
				$head = $this->load->view('layout/cetak',$data,TRUE);
				$users= new \Mpdf\Mpdf(['format' => 'Legal']);
		        $users->showImageErrors = true;
		        $users->WriteHTML($style,\Mpdf\HTMLParserMode::HEADER_CSS);
		        $users->WriteHtml($head,\Mpdf\HTMLParserMode::HTML_BODY);
		        $users->WriteHtml($body,\Mpdf\HTMLParserMode::HTML_BODY);
		        $users->Output($data['title'].'.pdf ', 'D');
			}
			
			$data['nama'] = $this->session->userdata('nama');
			$this->load->view('layout/mahasiswa/header',$data);
			$this->load->view('pengajuan/detail_mahasiswa',$data);
			if($data['pengajuan']['nim'] != $this->session->userdata('username')) redirect('pengajuan');
		}
		else{
			redirect('beranda');
		}
	}

	public function penentuan_pembimbing($cetak = null)
	{
		
		$data['pengajuans'] = $this->_ProposalComplete();
		$data['title'] = 'Penentuan Pembimbing';
		if($cetak == 'cetak'){
			$body = $this->load->view('penentuan/cetak',$data,TRUE);
			$style = file_get_contents(base_url('assets/dist/css/cetak.css'));
			$head = $this->load->view('layout/cetak',$data,TRUE);
			$users= new \Mpdf\Mpdf(['format' => 'Legal']);
	        $users->showImageErrors = true;
	        $users->WriteHTML($style,\Mpdf\HTMLParserMode::HEADER_CSS);
	        $users->WriteHtml($head,\Mpdf\HTMLParserMode::HTML_BODY);
	        $users->WriteHtml($body,\Mpdf\HTMLParserMode::HTML_BODY);
	        $users->Output($data['title'].'.pdf ', 'D');
		}else{
			$data['nama'] = $this->session->userdata('nama');
			$this->load->view('layout/admin/header',$data);
			$this->load->view('penentuan/list',$data);
		}

	}

	private function _ProposalComplete()
	{
		$model = $this->Pengajuan_model;
		$datas = $model->getProposalDone();
		$proposal =array();
		$pengajuanAccepted = array();
		foreach ($datas as $data) {
			$proposal[] += $data["id_pengajuan"]; 
		}
		for($i = 0; $i < count($proposal); $i++){

			$pengajuan = $model->get_select_pengajuan2($proposal[$i]);
			$cariDosbing1 = $model->get_dosen($pengajuan['pembimbing1']);
			$cariDosbing2 = $model->get_dosen($pengajuan['pembimbing2']);
			$pengajuanAccepted += array( $i =>array(
				'id_pengajuan' =>$pengajuan["id_pengajuan"],
				'judul' =>$pengajuan["judul"],
				'latarbelakang' =>$pengajuan["latarbelakang"],
				'tujuan' =>$pengajuan["tujuan"],
				'status_pengajuan' =>$pengajuan["status_pengajuan"],
				'tglpengajuan' =>$pengajuan["tglpengajuan"],
				'tglditerima' =>$pengajuan["tglditerima"],
				'alasan' =>$pengajuan["alasan"],
				'status' =>$pengajuan["status"],
				'nama' =>$pengajuan["nama"],
				'nim' =>$pengajuan["nim"],
				'id_mahasiswa' =>$pengajuan["id_mahasiswa"],
				'konsentrasi' =>$pengajuan["konsentrasi"],
				'nama_prodi' =>$pengajuan["nama_prodi"],
				'pembimbing1'=> $cariDosbing1["nama"],
				'pembimbing2'=> $cariDosbing2["nama"] 
				));


		}
		return $pengajuanAccepted;
		
	}

	public function getpenentuan(){
		echo json_encode($this->_ProposalComplete());
	}
	public function nyoba()
	{
		$html = '<bookmark content="Start of the Document" /><div>Section 1 text</div>';

		$mpdf = new \Mpdf\Mpdf();
		$mpdf->WriteHTML($html);
		$mpdf->Output();
	}
	public function cetak($type = null)
	{
		$model = $this->Pengajuan_model;
		$data['title'] = 'Daftar Pengajuan';
		if($this->session->userdata('level')==0){
			$data['pengajuans'] = $model->get_pengajuan();
			$cetak  = $this->load->view('pengajuan/cetak_list_admin',$data,TRUE);
		}elseif($this->session->userdata('level')==1){
			$data['model'] = $model;
			$dosen = $this->session->userdata('id');
			if($type == 'proposal'){
				$data['title'] = 'DAFTAR SEMINAR PROPOSAL';
				
				$data['proposals'] = $model->getProposalBy($dosen);
				if(!$data['proposals']){
					redirect('pengajuan');
				}else{
					$cetak  = $this->load->view('proposal/cetak_list_dosen',$data,TRUE);
				}
			}elseif($type == 'bimbingan'){
				$data['title'] = 'DAFTAR BIMBINGAN';
				$data['pengajuans']=$model->get_pengajuan_by($dosen);
				if(!$data['pengajuans']){
					redirect('pengajuan');
				}else{
					$cetak  = $this->load->view('pengajuan/cetak_list_dosen',$data,TRUE);
				}
				
			}elseif($type == "komprehensif"){
				$data['title'] = 'DAFTAR SIDANG KOMPREHENSIF';
				$data['kompres'] = $model->getKompreByDosen();
				if(!$data['kompres']){
					redirect('pengajuan');
				}else{
					$cetak  = $this->load->view('proposal/cetak_list_dosen_kompre',$data,TRUE);
				}

			}elseif($type == "thesis"){
				$data['title'] = 'DAFTAR SIDANG THESIS';
				$data['thesisies'] = $model->getThesisByDosen();
				if(!$data['thesisies']){
					redirect('pengajuan');
				}else{
					$cetak  = $this->load->view('proposal/cetak_list_dosen_thesis',$data,TRUE);
				}

			}
		}elseif($this->session->userdata('level')==2){
			$data['pengajuans'] = $model->get_pengajuanmhs();
			$cetak  = $this->load->view('pengajuan/cetak_list_mahasiswa',$data,TRUE);
		}


		$style = file_get_contents(base_url('assets/dist/css/cetak.css'));
		$cetak_head = $this->load->view('layout/cetak',$data,TRUE);
		$users= new \Mpdf\Mpdf(['format' => 'Legal']);
        $users->showImageErrors = true;
        $users->WriteHTML($style,\Mpdf\HTMLParserMode::HEADER_CSS);
        $users->WriteHtml($cetak_head,\Mpdf\HTMLParserMode::HTML_BODY);
        $users->WriteHtml($cetak,\Mpdf\HTMLParserMode::HTML_BODY);
        $users->Output($data['title'].'.pdf ', 'D');
	}

	private function _isHaveKompre($pengajuanID)
	{
		$model = $this->Pengajuan_model;
		$kompre = $model->isHaveKompre($pengajuanID);

		if(!$kompre){
			return 'belum';
		}

		return $kompre["status"];
	}
	private function _cekTesis($pengajuanID)
	{
		$model = $this->Pengajuan_model;
		$kompre = $model->cektesis($pengajuanID);

		if(!$kompre){
			return 'belum';
		}

		return $kompre["status"];
	}

	private function _KompreMahasiswa()
	{
		$model = $this->Pengajuan_model;
		$dataKompre = $model->getKompreMahasiswa();
		$newData = array(
			'id'	=> $dataKompre["id"],
			'id_pengajuan'	=> $dataKompre["id_pengajuan"],
			'status'	=> $dataKompre["status"],
			'tgl_daftar' => $dataKompre["tgl_daftar"],
			'tgl_terima' => $dataKompre["tgl_terima"],
			'tgl_sidang' => $dataKompre["tgl_sidang"],
			'id_penguji1' => $dataKompre["id_penguji1"],
			'id_penguji2' => $dataKompre["id_penguji2"],
			'id_penguji3' => $dataKompre["id_penguji3"],
			'penguji1' => $this->_getDosen($dataKompre["id_penguji1"])["nama"],
			'penguji2' => $this->_getDosen($dataKompre["id_penguji2"])["nama"],
			'penguji3' => $this->_getDosen($dataKompre["id_penguji3"])["nama"]
		);

		return $newData;
		
	}

	private function _getDosen($id){
		$model = $this->Pengajuan_model;
		$data = $model->searchDosenBy($id);

		return $data;
	}

}