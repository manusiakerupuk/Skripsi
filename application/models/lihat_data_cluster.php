		<?php
		defined('BASEPATH') OR exit('No direct script access allowed');

		class Lihat_data_cluster extends CI_Model {
			function lihatdata(){
			return	$this->db->query("SELECT a.ID_datasiap, a.Judul, a.Abstrak, b.cluster from cluster as b, datasiap as a WHERE a.ID_datasiap = b.ID_datasiap");
			}
			function getid($id){
				return $this->db->query("SELECT * FROM datasiap WHERE ID_datasiap ='".$id."'");
			}
		}