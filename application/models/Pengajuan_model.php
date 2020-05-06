<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pengajuan_model extends CI_Model {
	public function __construct()
	{
		parent::__construct();
		date_default_timezone_set('Asia/Jakarta');
	}

	private $_tabel = 'pengajuan';
	private $_idPengajuan;

	public function insert_pengajuan()
	{
		$data = array(
			'id_mahasiswa'			=> $this->session->userdata('id'),
			'judul'			=> $this->input->post('judul'),
			'latarbelakang'			=> $this->input->post('latarbelakang'),
			'tujuan'			=> $this->input->post('tujuan'),
			'status'		=> 'belum',
			'tglpengajuan'	=> date("Y-m-d H:i:s")
			);

		return $this->db->insert($this->_tabel,$data);

	}
	public function get_pengajuanmhs()
	{
		$id = $this->session->userdata('id');
		return $this->db->query("SELECT pengajuan.status as status,pengajuan.tglpengajuan as tglpengajuan, pengajuan.tglditerima as tglditerima, pengajuan.id as id_pengajuan, mahasiswa.nama as nama, mahasiswa.username as username,pengajuan.id_mahasiswa as id_mahasiswa, pengajuan.latarbelakang as latarbelakang,pengajuan.tujuan as tujuan ,pengajuan.judul as judul FROM pengajuan INNER JOIN mahasiswa ON mahasiswa.id=pengajuan.id_mahasiswa WHERE pengajuan.id_mahasiswa=$id ORDER BY id_pengajuan DESC")->result();
	}
	public function get_pengajuan()
	{
		return $this->db->query("SELECT pengajuan.status as status,pengajuan.tglpengajuan as tglpengajuan, pengajuan.tglditerima as tglditerima, pengajuan.id as id_pengajuan, mahasiswa.nama as nama, mahasiswa.username as username,pengajuan.id_mahasiswa as id_mahasiswa, mahasiswa.prodi as prodi,mahasiswa.konsentrasi as konsentrasi, pengajuan.judul as judul FROM pengajuan INNER JOIN mahasiswa ON mahasiswa.id=pengajuan.id_mahasiswa ORDER BY id_pengajuan DESC")->result();
	}

	public function delete_pengajuan($id)
	{
		$this->db->where('id',$id);
		return $this->db->delete($this->_tabel);
	}
	public function get_select_penentuan($id)
	{
		return $this->db->query("SELECT pengajuan.id as id_pengajuan,pengajuan.judul as judul,pengajuan.latarbelakang as latarbelakang, pengajuan.tujuan as tujuan, pengajuan.status as status_pengajuan, pengajuan.tglpengajuan as tglpengajuan, pengajuan.tglditerima as tglditerima, pengajuan.keterangan as alasan,pengajuan.id_pembimbing1 as pembimbing1, pengajuan.id_pembimbing2 as pembimbing2, mahasiswa.nama as nama, mahasiswa.username as nim,pengajuan.id_mahasiswa as id_mahasiswa, mahasiswa.konsentrasi as konsentrasi, prodi.nama as nama_prodi, prodi.sebutan as sebutan_prodi, proposal.acc_seminar as acc_seminar FROM pengajuan INNER JOIN mahasiswa ON mahasiswa.id=pengajuan.id_mahasiswa INNER JOIN prodi ON prodi.sebutan=mahasiswa.prodi INNER JOIN proposal ON proposal.id_pengajuan=pengajuan.id WHERE pengajuan.id=$id")->result_array();
	}
	public function get_select_pengajuan($id)
	{
		return $this->db->query("SELECT pengajuan.id as id_pengajuan,pengajuan.judul as judul,pengajuan.latarbelakang as latarbelakang, pengajuan.tujuan as tujuan, pengajuan.status as status_pengajuan, pengajuan.tglpengajuan as tglpengajuan, pengajuan.tglditerima as tglditerima, pengajuan.keterangan as alasan,pengajuan.id_pembimbing1 as pembimbing1, pengajuan.id_pembimbing2 as pembimbing2, mahasiswa.nama as nama, mahasiswa.username as nim,pengajuan.id_mahasiswa as id_mahasiswa, mahasiswa.konsentrasi as konsentrasi, prodi.nama as nama_prodi, prodi.sebutan as sebutan_prodi FROM pengajuan INNER JOIN mahasiswa ON mahasiswa.id=pengajuan.id_mahasiswa INNER JOIN prodi ON prodi.sebutan=mahasiswa.prodi WHERE pengajuan.id=$id")->result_array();
	}
	public function get_select_pengajuan2($id)
	{
		return $this->db->query("SELECT pengajuan.id as id_pengajuan,pengajuan.judul as judul,pengajuan.latarbelakang as latarbelakang, pengajuan.tujuan as tujuan, pengajuan.status as status_pengajuan, pengajuan.tglpengajuan as tglpengajuan, pengajuan.tglditerima as tglditerima, pengajuan.keterangan as alasan,pengajuan.status as status,pengajuan.id_pembimbing1 as pembimbing1, pengajuan.id_pembimbing2 as pembimbing2, mahasiswa.nama as nama, mahasiswa.username as nim,pengajuan.id_mahasiswa as id_mahasiswa, mahasiswa.konsentrasi as konsentrasi, prodi.nama as nama_prodi FROM pengajuan INNER JOIN mahasiswa ON mahasiswa.id=pengajuan.id_mahasiswa INNER JOIN prodi ON prodi.sebutan=mahasiswa.prodi WHERE pengajuan.id=$id")->row_array();
	}
	public function get_pengajuan_by($dosen)
	{
		return $this->db->query("SELECT pengajuan.id as id_pengajuan,pengajuan.judul as judul,pengajuan.latarbelakang as latarbelakang, pengajuan.tujuan as tujuan, pengajuan.status as status_pengajuan, pengajuan.tglpengajuan as tglpengajuan, pengajuan.tglditerima as tglditerima, pengajuan.keterangan as alasan,pengajuan.status as status,pengajuan.id_pembimbing1 as pembimbing1, pengajuan.id_pembimbing2 as pembimbing2, mahasiswa.nama as nama, mahasiswa.username as nim,pengajuan.id_mahasiswa as id_mahasiswa, mahasiswa.konsentrasi as konsentrasi, prodi.nama as nama_prodi FROM pengajuan INNER JOIN mahasiswa ON mahasiswa.id=pengajuan.id_mahasiswa INNER JOIN prodi ON prodi.sebutan=mahasiswa.prodi WHERE pengajuan.id_pembimbing1=$dosen OR pengajuan.id_pembimbing2=$dosen")->result_array();
	}
	public function update($id,$data)
	{
		$this->db->where('id',$id);
		return $this->db->update($this->_tabel,$data);
	}
	public function get_dosen($id)
	{
		$this->db->where('id',$id);
		return $this->db->get('dosen')->row_array();
	}
	public function tolak_pengajuan($id)
	{
		$this->db->set('status','tolak');
		$this->db->where('id',$id);
		return $this->db->update('pengajuan');

	}
	public function insert_proposal($id)
	{
		$IDProposal = uniqid();
		$data = array(
			'id'			=> $IDProposal,
			'id_pengajuan'	=> $id,
			'last_update'	=> date("Y-m-d H:i:s"),
			'file'			=> $this->_uploadProposal($IDProposal)
		);

		return $this->db->insert('proposal',$data);
	}

	private function _uploadProposal($id)
	{
		$config['upload_path']          = './uploads/proposal/';
        $config['allowed_types']        = 'pdf';
        $config['file_name']            = $id;
        $config['overwrite']			= true;
        $config['max_size']             = 50000;
        
        $this->load->library('upload', $config);
        if ($this->upload->do_upload('proposal')) {
        	$this->session->set_flashdata('proposal_uploaded','Berhasil Mengirim Proposal!');
        	return $this->upload->data("file_name");
        }else{
        	$this->session->set_flashdata('proposal_failed','Gagal Mengirim Proposal!');
        }
	}
	public function is_there_proposal($id){
		$this->db->where('id_pengajuan',$id);
		return $this->db->get('proposal')->row_array();
	}
	public function update_proposal($propID)
	{
		
		$data = array(
			'id_penguji1'	=> NULL,
			'id_penguji2'	=> NULL,
			'id_penguji3'	=> NULL,
			'tgl_seminar'	=> NULL,
			'acc_seminar'	=> NULL,
			'nilai_1'		=> NULL,
			'nilai_2'		=> NULL,
			'nilai_3'		=> NULL,
			'last_update'	=> date("Y-m-d H:i:s"),
			'revisi'		=> NULL,
			'file'			=> $this->_uploadProposal($propID)
		);
		$this->db->where('id',$propID);
		return $this->db->update('proposal',$data);
	}
	public function searchDosenBy($id)
	{
		return $this->db->query("SELECT nama, username FROM dosen WHERE id=$id")->row_array();
	}
	public function getProposalBy($dosen)
	{
		return $this->db->query("SELECT proposal.id as id_proposal, proposal.id_pengajuan as id_pengajuan, proposal.tgl_seminar as tgl_seminar, proposal.id_penguji1 as id_penguji1, proposal.id_penguji2 AS id_penguji2, proposal.id_penguji3 as id_penguji3, proposal.nilai_1 as nilai_1, proposal.nilai_2 as nilai_2, proposal.nilai_3 as nilai_3, mahasiswa.id as id_mahasiswa, mahasiswa.nama as nama_mahasiswa, mahasiswa.username as nim_mahasiswa, prodi.nama as prodi, pengajuan.judul as judul FROM proposal INNER JOIN pengajuan ON pengajuan.id = proposal.id_pengajuan INNER JOIN mahasiswa ON mahasiswa.id = pengajuan.id_mahasiswa INNER JOIN prodi ON prodi.sebutan=mahasiswa.prodi WHERE proposal.id_penguji1=$dosen OR proposal.id_penguji2=$dosen OR proposal.id_penguji3=$dosen ORDER BY proposal.last_update DESC")->result_array();
	}
	public function getNilaiProposal($id)
	{
		return $this->db->query("SELECT SUM(nilai_1+nilai_2+nilai_2)/3 AS nilai FROM proposal WHERE id_pengajuan='$id'")->row_array();
	}
	public function getProposalDone()
	{
		return $this->db->query("SELECT id_pengajuan FROM proposal WHERE revisi='tidak' AND nilai_1 IS NOT NULL AND nilai_2 IS NOT NULL AND nilai_3 GROUP BY id_pengajuan HAVING SUM(nilai_1+nilai_2+nilai_3) >= 75")->result_array();
	}	

}
