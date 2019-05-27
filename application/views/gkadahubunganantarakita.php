while ($loop) {
			$pembagiancluster2 = array();
			$hasilcluster2 = array();
			$clusterbaruArr = array();
			$yiTemp = array();
			$xiTemp = array();
			
			for ($i=0; $i < $cluster ; $i++) { 
			
			$clusterbaru = $this->db
								->select('AVG(a) as Meninggal_Dunia')
								->select('AVG(b) as Luka_Berat')
								->select('AVG(c) as Luka_Ringan')
								->select('AVG(nilai_kota) as nilai_kota')
								->from('normalisasi')
								->where('cluster','Cluster'.$i)
								->get();
			
			foreach ($clusterbaru->result_array() as $row)
					{
						$value = array($row['Meninggal_Dunia'], $row['Luka_Berat'], $row['Luka_Ringan'], $row['nilai_kota']);
						array_push($clusterbaruArr, $value);

					}

			}
			$jumlahiterasi++;

			for ($datacluster2=1; $datacluster2 <= $jmlData ; $datacluster2++) { 

				$iterasi2 = $this->db
		            ->select('Nama_jalan,a,b,c,nilai_kota')
		            ->from('normalisasi')
		            ->where('kode',$datacluster2)
		            ->get();

			        foreach ($iterasi2->result_array() as $row)
					{
						for ($i = 0; $i < sizeof($clusterbaruArr); $i++) {
							$data9 = abs($row['a'] - $clusterbaruArr[$i][0]);
						  	$data10 = abs($row['b'] - $clusterbaruArr[$i][1]);
						  	$data11 = abs($row['c'] - $clusterbaruArr[$i][2]);
						  	$datajalan2 = abs($row['nilai_kota'] - $clusterbaruArr[$i][3]);
						  	$data12 = MAX($data9,$data10,$data11,$datajalan2);
						  	
							$hasilcluster2['Cluster'.$i] = $data12;
							
							if ($i == 0){
								$dataTemp += $data9;
								array_push($xiTemp, $data9);
							}
								
						}
					}

					$data13 = min($hasilcluster2);
					
					array_search($data13,$hasilcluster2);
					array_push($pembagiancluster2,array_search($data13,$hasilcluster2));
					$simpan_hasil[$cl][$z] = $pembagiancluster2;
					$hasilcluster2 = array();
					unset($hasilcluster2);

					$clusterTemp += $data13;
					array_push($yiTemp, $data13);

			}	

			$dbcluster = $this->db
								->select('Cluster')
								->from('normalisasi')	
								->get();

			$position = 0;
			$found = false;
			foreach ($dbcluster->result_array() as $row) {
				if ($position < $jmlData) {
					if ($row['Cluster'] == $pembagiancluster2[$position]) {
					} else {
						
						$found = true;
						break;
						
					}
					$position++;
				}
			}

			$loop = $found;
			if ($found) {
				$dataTemp = 0;
				$clusterTemp = 0;
				unset($xiTemp);
				for ($i=0; $i < $jmlData ; $i++) { 
					$ii = $i + 1;
					$this->db->set('Cluster',$pembagiancluster2[$i]);
					$this->db->where('kode',$ii);
					$this->db->update('normalisasi');
				}
			} else {
				$avgData = $dataTemp / $jmlData;
				
				$avgCluster = $clusterTemp / $jmlData;
				

				// -------------------------------------------END METODE K-MEANS------------------------------------

				// -------------------------------------------METODE ELBOW------------------------------------------
				$xiaksenArr = array();
				$yiaksenArr = array();
				$xikurangyiArr = array();
				$xiaksenkuadratArr = array();

				for ($i=0; $i < $jmlData; $i++) { 
					$xixaksen = $xiTemp[$i] - $avgData;
					array_push($xiaksenArr, $xixaksen);

					$yiaksen = $yiTemp[$i] - $avgCluster;
					array_push($yiaksenArr, $yiaksen);

					$xikurangyi = $xiaksenArr[$i] - $yiaksenArr[$i];
					array_push($xikurangyiArr, $xikurangyi);

					$xiaksenkuadrat = pow($xiaksenArr[$i],2);
					array_push($xiaksenkuadratArr, $xiaksenkuadrat);
	
				}
				
				$totalxikurangyi = array_sum($xikurangyiArr);
				
				$totalxiaksenkuadrat = array_sum($xiaksenkuadratArr);
				
				$b1 = $totalxikurangyi / $totalxiaksenkuadrat;
				
				$b0 = $avgCluster - ($b1 * $avgData);
				

				$ypetikArr = array();
				$errorArr = array();
				$errorkuadratisArr = array();
				for ($i=0; $i < $jmlData; $i++) { 
					$ypetik = $b0 + ($b1 * $xiTemp[$i]);
					array_push($ypetikArr, $ypetik);
					$error = $yiTemp[$i] - $ypetikArr[$i];
					array_push($errorArr, $error);
					$errorkuadratis = pow($errorArr[$i],2);
					array_push($errorkuadratisArr, $errorkuadratis);
				}
				$totalerrorkuadratis = array_sum($errorkuadratisArr);
				$totalerror[$cl] = $totalerrorkuadratis;
				//array_push($totalerror, $totalerrorkuadratis);

				$insertelbow = array(
						'kode'	=> $cl,
				        'nilai_elbow' => $totalerrorkuadratis
				);

				$this->db->insert('elbow', $insertelbow);

				// ------------------------------------------- END METODE ELBOW------------------------------------------
		
			}	
				
			$z++;	
		}