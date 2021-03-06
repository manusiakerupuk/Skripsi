		<?php
		defined('BASEPATH') OR exit('No direct script access allowed');

		class Home extends CI_Controller {
			public function __construct()
			{
				parent::__construct();
				$this->load->library(array('PHPExcel','PHPExcel/IOFactory'));
			}

			public function index()
			{
				$datasiap['data']=$this->db->query("SELECT * FROM datasiap");
				$this->load->view('index',$datasiap);
			}
			public function uploaddata(){
				$this->load->view('uploaddata');
			}
			public function excel()
			{
				$fileName = $this->input->post('file', TRUE);

				$config['upload_path'] = './assets/excel/'; 
				$config['file_name'] = $fileName;
				$config['allowed_types'] = '*';
				$config['encrypt_name']= TRUE;
				$config['max_size'] = 0;

				$this->load->library('upload', $config);
				$this->upload->initialize($config); 

				if (!$this->upload->do_upload('file')) {
					$error = $this->upload->display_errors();
					$this->session->set_flashdata('pesan',$error); 
					redirect(''); 
				} else {
					$media = $this->upload->data();
					$inputFileName = './assets/excel/'.$media['file_name'];

					try {
						$inputFileType = IOFactory::identify($inputFileName);
						$objReader = IOFactory::createReader($inputFileType);
						$objPHPExcel = $objReader->load($inputFileName);
					} catch(Exception $e) {
						die('Error loading file "'.pathinfo($inputFileName,PATHINFO_BASENAME).'": '.$e->getMessage());
					}

					$sheet = $objPHPExcel->getSheet(0);
					$highestRow = $sheet->getHighestRow();
					$highestColumn = $sheet->getHighestColumn();
					$this->db->query("TRUNCATE TABLE datasiap");
					for ($row = 1; $row <= $highestRow; $row++){  
						$rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row,
							NULL,
							TRUE,
							FALSE);
						$data = array(
							"Judul"=> $rowData[0][0],
							"Abstrak"=> $rowData[0][1]);

						// $this->db->insert("datasiap",$data);
						$this->db->insert("datasiap",$data);
					} 
					   unlink($inputFileName); // hapus file temp
					   $count = $highestRow;
					   $this->session->set_flashdata('pesan','Upload berhasil, Total: <b>'.$count.'</b> data.'); 
					   redirect('home/uploaddata');

						}
			}
			public function casefolding()
			{
				$datasiap['data']=$this->db->query("SELECT * FROM datasiap");
				foreach ($datasiap['data']->result() as $b) {
					echo $b->ID;
					echo "<br>";
					echo $dok = preg_replace('/[^A-Za-z\  ]/', '', $b->Abstrak);
					echo "<br>";

				}
			}
			public function stoplist(){
				$datasiap['data']=$this->db->query("SELECT * FROM datasiap");
				foreach ($datasiap['data']->result() as $b) {
					echo $b->ID;
					echo "<br>";
					$data = preg_replace('/[^A-Za-z\  ]/', '', $b->Abstrak);
					$q = explode(" ",$data);
					for($i=0;$i<count($q);$i++){
						$result = $this->db->query("SELECT * FROM stoplist WHERE kata = '$q[$i]'");
 						if($result->num_rows() > 0 ){// stopword removal
 							$y[$i] = '';
 						}else{
 							$y[$i] = $q[$i];
 						};
						}			
					$data = implode(" ",$y);
					echo $data;
					echo "<br>";
				}	
			}
			public function stemming(){
				$datasiap['data']=$this->db->query("SELECT * FROM datasiap ORDER BY ID_datasiap ASC");
				foreach ($datasiap['data']->result() as $b) {
					// echo $b->ID;
					// echo "<br>";
					$lowercase =  strtolower($b->Abstrak);
					$lowercase1 = strtolower($b->Judul);
					$data = preg_replace('/[^A-Za-z\  ]/', '', $lowercase1)." ".preg_replace('/[^A-Za-z\  ]/', '', $lowercase);
					$q = explode(" ",$data);
					for($i=0;$i<count($q);$i++){
						$result = $this->db->query("SELECT * FROM stoplist WHERE kata = '$q[$i]'");
 						if($result->num_rows() > 0 ){// stopword removal
 							$y[$i] = '';
 						}else{
 							$y[$i] = $q[$i];
 						};
						}			
					$data = implode(" ",$y);
					
					$path = __DIR__;
					$new_path = dirname($path, 2);
					require_once $new_path . '/vendor/autoload.php';
					$stemmerFactory = new \Sastrawi\Stemmer\StemmerFactory();
					$stemmer  = $stemmerFactory->createStemmer();
					$output   = $stemmer->stem($data);
					$output = preg_replace('/[^A-Za-z\  ]/', '', $output);
					$q = explode(" ",$output);
					for($i=0;$i<count($q);$i++){
						$result = $this->db->query("SELECT * FROM stoplist WHERE kata = '$q[$i]'");
 						if($result->num_rows() > 0 ){// stopword removal
 							$y[$i] = '';
 						}else{
 							$y[$i] = $q[$i];
 						};
						}			
					$output = implode(" ",$y);
					unset($y);
					$stemmerFactory = new \Sastrawi\Stemmer\StemmerFactory();
					$stemmer  = $stemmerFactory->createStemmer();
					$output   = $stemmer->stem($output);
					$hasil[] = $output;
					// echo $output;
					unset($output);					

					// echo "<br>";
			}
			$path = __DIR__;
			$new_path = dirname($path, 2);
			require_once $new_path . '/vendor/autoload.php';
			$vectorizer = new Phpml\FeatureExtraction\TokenCountVectorizer(new Phpml\Tokenization\WhitespaceTokenizer());
			$vectorizer->fit($hasil);
			$vectorizer->transform($hasil);
			$a = $vectorizer->getVocabulary($hasil);
			$transformer = new Phpml\FeatureExtraction\TfIdfTransformer($hasil);
			$transformer->transform($hasil);
			// echo sizeof($hasil);
			$jarak = array();
			for ($i=0; $i < sizeof($hasil); $i++) { 
				for ($j=$i+1; $j < sizeof($hasil); $j++) { 
					$euclidean = new Phpml\Math\Distance\Euclidean();
					$hasileucdistance = $euclidean->distance($hasil[$i], $hasil[$j]);
					$w  = array('C1' => $i,
								'C2' => $j,
									'hasil' => $hasileucdistance
									 );
					array_push($jarak, $w);					
				}
			}
			// print_r($jarak);
		
			// jarak = 18 jadi 6 cluster
			// jarak = 3 jadi 3 cluster
			// jarak = 6 jadi 4 cluster
			// jarak = 11 jadi 5 cluster
			// jarak = 15 jadi 6 cluster
			//PERHITUNGAN =================================================================================================================
			while (sizeof($jarak) > 11) {
			$hitung1 = array();	
			$hitung2 = array();
			$hitung3 = array();	
			$hasilsementara = array();
			$hasilakhir = array();
			$c1_sementara = array();			
			$minimum = array_column($jarak, 'hasil');
			$min = min($minimum);
			foreach ($jarak as $key ) {
				if ($key['hasil'] == $min) {
					$hasilminimum = $key;
					// echo "hasil minimum adalah ".$key['C1'].' '.$key['C2'];
					// echo '<hr>';
				}
			}
			$c2_sementara = $hasilminimum['C1']." ".$hasilminimum['C2'];
			$nu = sizeof(explode(" ", $hasilminimum['C1']));
			$nv = sizeof(explode(" ", $hasilminimum['C2']));
			$nuv = $nu + $nv;
			$iuv = $hasilminimum['hasil'];
				
			
			foreach ($jarak as $map) {
				if (($map['C1'] != $hasilminimum['C1'] and $map['C2'] != $hasilminimum['C2']) and ($map['C1'] != $hasilminimum['C2'] and $map['C2'] != $hasilminimum['C1'])) {
					$hasilsementara[] =  array(	'C1' => $map['C1'],
											'C2' => $map['C2'],
											'hasil' => $map['hasil']
									 );
				}
			}
			// echo "<br>";
			foreach ($jarak as $map) {
				if (($map['C1'] == $hasilminimum['C1'] or $map['C2'] == $hasilminimum['C2']) or ($map['C1'] == $hasilminimum['C2'] or $map['C2'] == $hasilminimum['C1'])) {
					if ($map['C1'] == $hasilminimum['C1'] AND $map['C2'] == $hasilminimum['C2']) {

					} elseif ($map['C1'] == $hasilminimum['C1'] or $map['C2'] == $hasilminimum['C1']) {
						// echo "C1 ".$map['C1']." C2 ".$map['C2']."<br>";
						// echo "<br>";
						if ($map['C1'] != $hasilminimum['C1']) {
							$hitung1[] = ($nu + sizeof(explode(" ", $map['C1']))) / ($nuv + sizeof(explode(" ", $map['C1']))) * $map['hasil'];
							$c1_sementara[] = $map['C1']; 
							// echo "<br> Map C1 ".$map['C1']."<br>";
						}else{
							$hitung1[] = ($nu + sizeof(explode(" ", $map['C2']))) / ($nuv + sizeof(explode(" ", $map['C2']))) * $map['hasil'];
							$c1_sementara[] = $map['C2'];
							// echo "<br> Map C2 ".$map['C2']."<br>";
						}

						// echo $map['C1']." ".$map['C2']."<br>";
					}
					
				}
				// print_r($hitung1);
			}
			// print_r($c1_sementara);
			// echo $hitung1['0'];
			// echo "<hr>";

			$tempData = 0;

			foreach ($jarak as $map) {
				if (($map['C1'] == $hasilminimum['C1'] or $map['C2'] == $hasilminimum['C2']) or ($map['C1'] == $hasilminimum['C2'] or $map['C2'] == $hasilminimum['C1'])) {
					if ($map['C1'] == $hasilminimum['C1'] AND $map['C2'] == $hasilminimum['C2']) {

					} elseif ($map['C1'] == $hasilminimum['C2'] or $map['C2'] == $hasilminimum['C2']) {
						$tempData++;
						// echo "C1 ".$map['C1']." C2 ".$map['C2']." = ".$map['hasil'];
						// echo "<br>";
						if ($map['C1'] != $hasilminimum['C1']) {
							$hitung2[] = (($nv + sizeof(explode(" ", $map['C1']))) / ($nuv + sizeof(explode(" ", $map['C1'])))) * $map['hasil'];
							// echo "<br>". $map['C1']." . ".$map['C2'];
						}else{
							$hitung2[] = (($nv + sizeof(explode(" ", $map['C2']))) / ($nuv + sizeof(explode(" ", $map['C2'])))) * $map['hasil'];
							// echo "<br>". $map['C1']." . ".$map['C2'];
						}
						// echo $map['C1']." ".$map['C2']."<br>";
					}
					
				}
				// print_r($hitung2);
			}
			// echo $hitung2['0'];
			// echo "<hr>";

			foreach ($jarak as $map) {
				if (($map['C1'] == $hasilminimum['C1'] or $map['C2'] == $hasilminimum['C2']) or ($map['C1'] == $hasilminimum['C2'] or $map['C2'] == $hasilminimum['C1'])) {
					if ($map['C1'] == $hasilminimum['C1'] AND $map['C2'] == $hasilminimum['C2']) {
					} elseif ($map['C1'] == $hasilminimum['C2'] or $map['C2'] == $hasilminimum['C2']) {
						if ($map['C1'] != $hasilminimum['C1']) {
							$hitung3[] = (sizeof(explode(" ", $map['C1']))) / ($nuv + sizeof(explode(" ", $map['C1']))) * $iuv;
							
						}else{
							$hitung3[] = (sizeof(explode(" ", $map['C2']))) / ($nuv + sizeof(explode(" ", $map['C2']))) * $iuv;
							
						}
						// echo $map['C1']." ".$map['C2']."<br>";
					}
					
				}
				// print_r($hitung3);
			}

			for ($i=0; $i < sizeof($hitung2); $i++) { 

				$hasilakhir[$i] = ($hitung1[$i] + $hitung2[$i]) - $hitung3[$i];
				$hasilsementara[] =  array(	'C1' => $c1_sementara[$i],
											'C2' => $c2_sementara,
											'hasil' => $hasilakhir[$i]
									 );
			}
			$jumlah_pusatcluster = 0;
			for ($i=0; $i < sizeof($hasilsementara) ; $i++) { 
				$jumlah_pusatcluster = $jumlah_pusatcluster + sizeof($hasilsementara[$i]['C1'])+sizeof($hasilsementara[$i]['C2']);
			}


			$jarak = $hasilsementara;
			}
// DIBAWAH HASIL DARI PUSAT CLUSTER 
			// 	echo "<TABLE border = 1>
			//  	<tr>
   //  				<th>C1</th>
   //  				<th>C2</th>
   //  				<th>hasil</th>
  	// 			</tr>";
			// foreach ($jarak as $key ) {
			// 	echo " <tr>
			// 				<td>".$key['C1']."</td>
			// 				<td>".$key['C2']."</td>
			// 				<td>".$key['hasil']."</td>
			// 				</tr>";
			// }
			// echo "</table>";

			foreach ($jarak as $key) {
				$dimensikegelapan[] = $key['C1'] ;
				$dimensikegelapan[] = $key['C2'] ;
			}
			// print_r(array_unique($dimensikegelapan));
			$mantabsoul = array_unique($dimensikegelapan);
			foreach ($mantabsoul as $key ) {
				echo $key."<hr>";
			}
			echo sizeof($mantabsoul);
			$cluster = 1;
			foreach ($mantabsoul as $key ) {
				// echo "cluster".$cluster." ".$key;
				$anggotadpr = explode(" ", $key);
				$pembagi=sizeof($anggotadpr);
				// echo " || ".$pembagi."<br>";
				for ($i=0; $i <sizeof($hasil['1']) ; $i++) { 
				$rata2[$i]=0;
				}
				foreach ($anggotadpr as $key) {
					$banyak = 0;
					foreach ($hasil[$key] as $dprd ) {
						$rata2[$banyak] += $dprd;
						$banyak++;
					}
					
					// for ($i=0; $i < sizeof($rata2) ; $i++) { 
						
					// 	$hasilterakhir[$i] = $rata2[$i] / $pembagi;
					// }
					// echo $hasilterakhir['0'];
				}
				for ($i=0; $i < sizeof($rata2) ; $i++) { 
						
						$hasilterakhir[$i] = $rata2[$i] / $pembagi;
					}
				// 	print_r($rata2);
				// 	echo "<hr>";
				// 	print_r($hasilterakhir);
				// echo "<hr>";
				$hasilpusatcluster[$cluster] = array('id' => $cluster, 'tfidf' => $hasilterakhir );

				$cluster++;
			}
			// print_r($jarak);
			// echo "<hr>";
			// print_r($hasilpusatcluster);
			// foreach ($hasilpusatcluster as $key ) {
			// 	print_r($key['tfidf']);
			// 	echo "<hr>";
			// };
			// print_r($hasilpusatcluster['1']['tfidf']);
			$angkaa = 1;
			$insertcluster = array();
			foreach ($hasil as $key) {
				$mencariclusterdokumen = array();
				for ($i=1; $i <= sizeof($hasilpusatcluster) ; $i++) { 
					$euclidean = new Phpml\Math\Distance\Euclidean();
					$mencariclusterdokumen[$i] = array('id' => $hasilpusatcluster[$i]['id'] ,
													'hasil' => $euclidean->distance($key, $hasilpusatcluster[$i]['tfidf'] ));
				}
				// foreach ($mencariclusterdokumen as $key ) {
				// 	if ($key == min($mencariclusterdokumen)) {
				// 		# code...
				// 	}
				// }
				// echo min(array_column($mencariclusterdokumen, 'hasil'));
				
					
				 // echo "Dokumen ".$angkaa." = ".min(array_column($mencariclusterdokumen, 'hasil'))."<br>";
				 // echo "<hr>";
				 // print_r((array_column($mencariclusterdokumen, 'hasil')));
				 // echo "<br>";
				 
				 foreach ($mencariclusterdokumen as $key ) {
				 	if ($key['hasil'] == min(array_column($mencariclusterdokumen, 'hasil'))) {
				 		$insertcluster[$angkaa] = array('ID_datasiap' => $angkaa ,
				 								'cluster' => 'C'.$key['id'] );
				 	}
				 }
				 
				 // echo "<hr>";
				 $angkaa++;
			}
			$this->db->truncate('cluster');
			for ($i=1; $i <=sizeof($insertcluster) ; $i++) { 
				$this->db->insert('cluster',$insertcluster[$i]);
			}
			// $this->hasilcluster();
			// print_r($insertcluster);
		}
		
		function hasilcluster(){
			$this->load->model('lihat_data_cluster');
			$data['lihat'] = $this->lihat_data_cluster->lihatdata()->result();
			$this->load->view('lihat_data_cluster',$data);
		}
		function getid(){
			$this->load->model('lihat_data_cluster');
			// echo $this->uri->segment(3);
			$data['lihat'] = $this->lihat_data_cluster->getid($this->uri->segment(3))->result();
			$this->load->view('detail',$data);
		}
































		function coba(){
			$hasil = array('game edukasi','pengembangan sistem peramalan','aplikasi peramalan','bangun game petualang edukasi');
			
			foreach ($hasil as $key ) {
				$nana[] = strtolower($key);
			}
			
			$hasil = preg_replace('/[^A-Za-z\  ]/', '', $nana);
			$path = __DIR__;
			$new_path = dirname($path, 2);
			require_once $new_path . '/vendor/autoload.php';
			$vectorizer = new Phpml\FeatureExtraction\TokenCountVectorizer(new Phpml\Tokenization\WhitespaceTokenizer());
			$vectorizer->fit($hasil);
			$vectorizer->transform($hasil);
			$a = $vectorizer->getVocabulary($hasil);
			$transformer = new Phpml\FeatureExtraction\TfIdfTransformer($hasil);
			$transformer->transform($hasil);
			// echo sizeof($hasil);
			$jarak = array();
			for ($i=0; $i < sizeof($hasil); $i++) { 
				for ($j=$i+1; $j < sizeof($hasil); $j++) { 
					$euclidean = new Phpml\Math\Distance\Euclidean();
					$hasileucdistance = $euclidean->distance($hasil[$i], $hasil[$j]);
					$w  = array('C1' => $i,
								'C2' => $j,
									'hasil' => $hasileucdistance
									 );
					array_push($jarak, $w);					
				}
			}
			// print_r($jarak);
		
				echo "<TABLE border = 1>
			 	<tr>
    				<th>C1</th>
    				<th>C2</th>
    				<th>hasil</th>
  				</tr>";
			foreach ($jarak as $key ) {
				echo " <tr>
							<td>".$key['C1']."</td>
							<td>".$key['C2']."</td>
							<td>".$key['hasil']."</td>
							</tr>";
			}
			echo "</table>";
			echo "<hr>";
		
			//PERHITUNGAN =================================================================================================================
			do {
			
				echo "<TABLE >
			 	<tr>
    				<th>C1</th>
    				<th>C2</th>
  				</tr>";
			foreach ($jarak as $key ) {
				echo " <tr>
							<td>".$key['C1']."</td>
							<td>".$key['C2']."</td>
							
							</tr>";
			}
			echo "</table>";
			$hitung1 = array();	
			$hitung2 = array();
			$hitung3 = array();	
			$hasilsementara = array();
			$hasilakhir = array();
			$c1_sementara = array();			
			$minimum = array_column($jarak, 'hasil');
			$min = min($minimum);
			foreach ($jarak as $key ) {
				if ($key['hasil'] == $min) {
					$hasilminimum = $key;
					// echo "hasil minimum adalah ".$key['C1'].' '.$key['C2'];
					// echo '<hr>';
				}
			}
			$c2_sementara = $hasilminimum['C1']." ".$hasilminimum['C2'];
			$nu = sizeof(explode(" ", $hasilminimum['C1']));
			$nv = sizeof(explode(" ", $hasilminimum['C2']));
			$nuv = $nu + $nv;
			$iuv = $hasilminimum['hasil'];
			// echo $iuv."<hr>";
			// echo "hasil sementara = ".$c2_sementara." ".$iuv;
			// echo "hasil minimum = ".$hasilminimum['C1']." | ".$hasilminimum['C2'];
				
			
			foreach ($jarak as $map) {
				if (($map['C1'] != $hasilminimum['C1'] and $map['C2'] != $hasilminimum['C2']) and ($map['C1'] != $hasilminimum['C2'] and $map['C2'] != $hasilminimum['C1'])) {
					$hasilsementara[] =  array(	'C1' => $map['C1'],
											'C2' => $map['C2'],
											'hasil' => $map['hasil']
									 );
				}
			}
			// echo "<br>";
			foreach ($jarak as $map) {
				if (($map['C1'] == $hasilminimum['C1'] or $map['C2'] == $hasilminimum['C2']) or ($map['C1'] == $hasilminimum['C2'] or $map['C2'] == $hasilminimum['C1'])) {
					if ($map['C1'] == $hasilminimum['C1'] AND $map['C2'] == $hasilminimum['C2']) {

					} elseif ($map['C1'] == $hasilminimum['C1'] or $map['C2'] == $hasilminimum['C1']) {
						// echo "C1 ".$map['C1']." C2 ".$map['C2']."<br>";
						// echo "<br>";
						if ($map['C1'] != $hasilminimum['C1']) {
							$hitung1[] = ($nu + sizeof(explode(" ", $map['C1']))) / ($nuv + sizeof(explode(" ", $map['C1']))) * $map['hasil'];
							$c1_sementara[] = $map['C1']; 
							// echo "<br> Map C1 ".$map['C1']."<br>";
						}else{
							$hitung1[] = ($nu + sizeof(explode(" ", $map['C2']))) / ($nuv + sizeof(explode(" ", $map['C2']))) * $map['hasil'];
							$c1_sementara[] = $map['C2'];
							// echo "<br> Map C2 ".$map['C2']."<br>";
						}

						// echo $map['C1']." ".$map['C2']."<br>";
					}
					
				}
				// print_r($hitung1);
			}
			// print_r($c1_sementara);
			// echo $hitung1['0'];
			// echo "<hr>";

			$tempData = 0;

			foreach ($jarak as $map) {
				if (($map['C1'] == $hasilminimum['C1'] or $map['C2'] == $hasilminimum['C2']) or ($map['C1'] == $hasilminimum['C2'] or $map['C2'] == $hasilminimum['C1'])) {
					if ($map['C1'] == $hasilminimum['C1'] AND $map['C2'] == $hasilminimum['C2']) {

					} elseif ($map['C1'] == $hasilminimum['C2'] or $map['C2'] == $hasilminimum['C2']) {
						$tempData++;
						// echo "C1 ".$map['C1']." C2 ".$map['C2']." = ".$map['hasil'];
						// echo "<br>";
						if ($map['C1'] != $hasilminimum['C1']) {
							$hitung2[] = (($nv + sizeof(explode(" ", $map['C1']))) / ($nuv + sizeof(explode(" ", $map['C1'])))) * $map['hasil'];
							// echo "<br>". $map['C1']." . ".$map['C2'];
						}else{
							$hitung2[] = (($nv + sizeof(explode(" ", $map['C2']))) / ($nuv + sizeof(explode(" ", $map['C2'])))) * $map['hasil'];
							// echo "<br>". $map['C1']." . ".$map['C2'];
						}
						// echo $map['C1']." ".$map['C2']."<br>";
					}
					
				}
				// print_r($hitung2);
			}
			// echo $hitung2['0'];
			// echo "<hr>";

			foreach ($jarak as $map) {
				if (($map['C1'] == $hasilminimum['C1'] or $map['C2'] == $hasilminimum['C2']) or ($map['C1'] == $hasilminimum['C2'] or $map['C2'] == $hasilminimum['C1'])) {
					if ($map['C1'] == $hasilminimum['C1'] AND $map['C2'] == $hasilminimum['C2']) {
					} elseif ($map['C1'] == $hasilminimum['C2'] or $map['C2'] == $hasilminimum['C2']) {
						if ($map['C1'] != $hasilminimum['C1']) {
							$hitung3[] = (sizeof(explode(" ", $map['C1']))) / ($nuv + sizeof(explode(" ", $map['C1']))) * $iuv;
							
						}else{
							$hitung3[] = (sizeof(explode(" ", $map['C2']))) / ($nuv + sizeof(explode(" ", $map['C2']))) * $iuv;
							
						}
						// echo $map['C1']." ".$map['C2']."<br>";
					}
					
				}
				// print_r($hitung3);
			}

			// print_r('$hitung1 '.sizeof($hitung1));
			// print_r($hitung1);
			// print_r('$hitung2 '.sizeof($hitung2));
			// print_r($hitung2);
			// print_r('$hitung3 '.sizeof($hitung3));
// echo "<br>";
			for ($i=0; $i < sizeof($hitung2); $i++) { 
				// print_r('1 '.$hitung1[$i]);
				// print_r('2 '.$hitung2[$i]);
				// print_r('3 '.$hitung3[$i]);
				// echo $c1_sementara[$i]."<BR>";
				$hasilakhir[$i] = ($hitung1[$i] + $hitung2[$i]) - $hitung3[$i];
				$hasilsementara[] =  array(	'C1' => $c1_sementara[$i],
											'C2' => $c2_sementara,
											'hasil' => $hasilakhir[$i]
									 );
			}
			$jumlah_pusatcluster = 0;
			for ($i=0; $i < sizeof($hasilsementara) ; $i++) { 
				$jumlah_pusatcluster = $jumlah_pusatcluster + sizeof($hasilsementara[$i]['C1'])+sizeof($hasilsementara[$i]['C2']);
			}
			// if ($jumlah_pusatcluster < 1) {
			// 	$pusatcluster = FALSE;
			// }

			$jarak = $hasilsementara;
			unset($hasilsementara);
			foreach ($jarak as $key) {
				$jumlahcluster[] = $key['C1'] ;
				$jumlahcluster[] = $key['C2'] ;
			}
			$mantulbos = array_unique($jumlahcluster);
			// echo "<hr>";
			// echo sizeof($mantulbos);
			// echo "<hr>";
			print_r(array_unique($jumlahcluster));
			} while (sizeof($jarak) > 1);
		foreach ($jarak as $key ) {
			echo "C1 = ".$key['C1']." C2 = ".$key['C2']." hasil = ".$key['hasil']."<br>";
		}
				echo "<TABLE border = 1>
			 	<tr>
    				<th>C1</th>
    				<th>C2</th>
    				<th>hasil</th>
  				</tr>";
			foreach ($jarak as $key ) {
				echo " <tr>
							<td>".$key['C1']."</td>
							<td>".$key['C2']."</td>
							<td>".$key['hasil']."</td>
							</tr>";
			}
			echo "</table>";
			foreach ($jarak as $key) {
				$dimensikegelapan[] = $key['C1'] ;
				$dimensikegelapan[] = $key['C2'] ;
			}

			$mantabsoul = array_unique($dimensikegelapan);
			print_r(array_unique($dimensikegelapan));
			echo sizeof($mantabsoul);
			$cluster = 1;
			foreach ($mantabsoul as $key ) {
				// echo "cluster".$cluster." ".$key;
				$anggotadpr = explode(" ", $key);
				$pembagi=sizeof($anggotadpr);
				// echo " || ".$pembagi."<br>";
				for ($i=0; $i <sizeof($hasil['1']) ; $i++) { 
				$rata2[$i]=0;
				}
				foreach ($anggotadpr as $key) {
					$banyak = 0;
					foreach ($hasil[$key] as $dprd ) {
						$rata2[$banyak] += $dprd;
						$banyak++;
					}
					
					
				}
				for ($i=0; $i < sizeof($rata2) ; $i++) { 
						
						$hasilterakhir[$i] = $rata2[$i] / $pembagi;
					}
				
				$hasilpusatcluster[$cluster] = array('id' => $cluster, 'tfidf' => $hasilterakhir );

				$cluster++;
			}
		
			$angkaa = 1;
			$insertcluster = array();
			foreach ($hasil as $key) {
				$mencariclusterdokumen = array();
				for ($i=1; $i <= sizeof($hasilpusatcluster) ; $i++) { 
					$euclidean = new Phpml\Math\Distance\Euclidean();
					$mencariclusterdokumen[$i] = array('id' => $hasilpusatcluster[$i]['id'] ,
													'hasil' => $euclidean->distance($key, $hasilpusatcluster[$i]['tfidf'] ));
				}
				
				 
				 foreach ($mencariclusterdokumen as $key ) {
				 	if ($key['hasil'] == min(array_column($mencariclusterdokumen, 'hasil'))) {
				 		$insertcluster[$angkaa] = array('ID_datasiap' => $angkaa ,
				 								'cluster' => 'C'.$key['id'] );
				 	}
				 }
				 
				 // echo "<hr>";
				 $angkaa++;
			}
			
		


		}
	}
?>