<?php
error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
include_once "database.php";
include_once "fungsi.php";
include_once "proses_mining.php";
?>

<div class="super_sub_content">
    <div class="container">
        <div class="row">
            <?php
            $query = $db_object->db_query("SELECT * FROM data_uji");
            $id_rule = array();
            $it = 0;
            while ($bar = $db_object->db_fetch_array($query)) {
                //ambil data uji
                $n_jenis_kelamin = $bar['jenis_kelamin'];
                $n_usia = $bar['usia'];
                $n_sekolah = $bar['sekolah'];
                $n_jawaban_a = $bar['jawaban_a'];
                $n_jawaban_b = $bar['jawaban_b'];
                $n_jawaban_c = $bar['jawaban_c'];
                $n_jawaban_d = $bar['jawaban_d'];
                $n_kelas_asli = $bar['kelas_asli'];

                $hasil = klasifikasi($db_object, $n_jenis_kelamin, $n_usia, $n_sekolah, 
                        $n_jawaban_a, $n_jawaban_b, $n_jawaban_c, $n_jawaban_d);
                
                $keputusan = $hasil['keputusan'];
                $id_rule_keputusan = $hasil['id_rule'];
                $it++;
                $db_object->db_query("UPDATE data_uji SET kelas_hasil='$keputusan', id_rule='$id_rule_keputusan' WHERE id=$bar[0]");
            }//end loop data uji


//menampilkan data uji dengan hasil prediksi
            $sql = $db_object->db_query("SELECT * FROM data_uji");
            ?>

            <table class='table table-bordered table-striped  table-hover'>
                <tr align='center'>
                    <th>No</th>
                    <th>Nama</th>
                    <th>L/P</th>
                    <th>Usia</th>
                    <th>Sekolah</th>
                    <th>Jawaban A</th>
                    <th>Jawaban B</th>
                    <th>Jawaban C</th>
                    <th>Jawaban D</th>
                    <th><b>Kelas asli</b></th>
                    <th><b>Kelas hasil</b></th>
                    <th><b>ID Rule Terpilih</b></th>
                    <th><b>Ketepatan</b></th>
                </tr>
                <?php
                $no = 1;
                while ($row = $db_object->db_fetch_array($sql)) {
                    if ($row['kelas_asli'] == $row['kelas_hasil']) {
                        $ketepatan = "benar";
                    } else {
                        $ketepatan = "salah";
                    }
                    echo "<tr>";
                        echo "<td>" . $no . "</td>";
                        echo "<td>" . $row['nama'] . "</td>";
                        echo "<td>" . $row['jenis_kelamin'] . "</td>";
                        echo "<td>" . $row['usia'] . "</td>";
                        echo "<td>" . $row['sekolah'] . "</td>";
                        echo "<td>" . $row['jawaban_a'] . "</td>";
                        echo "<td>" . $row['jawaban_b'] . "</td>";
                        echo "<td>" . $row['jawaban_c'] . "</td>";
                        echo "<td>" . $row['jawaban_d'] . "</td>";
                        echo "<td>" . $row['kelas_asli'] . "</td>";
                        echo "<td>" . $row['kelas_hasil'] . "</td>";
                        echo "<td>" . $row['id_rule'] . "</td>";
                        echo "<td>" . ($ketepatan=='benar'?"<b>".$ketepatan."</b>":$ketepatan) . "</td>";
                    echo "</tr>";
                    $no++;
                }
            ?>
            </table>

            <?php
//perhitungan akurasi
            $que = $db_object->db_query("SELECT * FROM data_uji");
            $jumlah_uji=$db_object->db_num_rows($que);
            //$TP=0; $FN=0; $TN=0; $FP=0; $kosong=0;
            $TA = $FB = $FC = $FD = 
            $FE = $TF = $FG = $FH = 
            $FI = $FJ = $TK = $FL = 
            $FM = $FN = $FO = $TP = 0;
            $kosong = 0;
            while ($row = $db_object->db_fetch_array($que)) {
                $asli = $row['kelas_asli'];
                $prediksi = $row['kelas_hasil'];
                if($asli=='Sanguin' & $prediksi=='Sanguin'){
                        $TA++;
                }
                else if($asli=='Sanguin' & $prediksi=='Koleris'){
                        $FB++;
                }
                else if($asli=='Sanguin' & $prediksi=='Melankolis'){
                        $FC++;
                }
                else if($asli=='Sanguin' & $prediksi=='Plegmatis'){
                        $FD++;
                }
                else if($asli=='Koleris' & $prediksi=='Sanguin'){
                        $FE++;
                }
                else if($asli=='Koleris' & $prediksi=='Koleris'){
                        $TF++;
                }
                else if($asli=='Koleris' & $prediksi=='Melankolis'){
                        $FG++;
                }
                else if($asli=='Koleris' & $prediksi=='Plegmatis'){
                        $FH++;
                }
                else if($asli=='Melankolis' & $prediksi=='Sanguin'){
                        $FI++;
                }
                else if($asli=='Melankolis' & $prediksi=='Koleris'){
                        $FJ++;
                }
                else if($asli=='Melankolis' & $prediksi=='Melankolis'){
                        $TK++;
                }
                else if($asli=='Melankolis' & $prediksi=='Plegmatis'){
                        $FL++;
                }
                else if($asli=='Plegmatis' & $prediksi=='Sanguin'){
                        $FM++;
                }
                else if($asli=='Plegmatis' & $prediksi=='Koleris'){
                        $FN++;
                }
                else if($asli=='Plegmatis' & $prediksi=='Melankolis'){
                        $FO++;
                }
                else if($asli=='Plegmatis' & $prediksi=='Plegmatis'){
                        $TP++;
                }
                else if($prediksi==''){
                        $kosong++;
                }
            }
//            $tepat = ($TP + $TN);
//            $tidak_tepat = ($FP + $FN + $kosong);
//            $akurasi = ($tepat / $jumlah) * 100;
//            $laju_error = ($tidak_tepat / $jumlah) * 100;
//            $sensitivitas = ($TP / ($TP + $FN)) * 100;
//            $spesifisitas = ($TN / ($FP + $TN)) * 100;
//
//            $akurasi = round($akurasi, 2);
//            $laju_error = round($laju_error, 2);
//            $sensitivitas = round($sensitivitas, 2);
//            $spesifisitas = round($spesifisitas, 2);
////echo "<center><h4>";
//            echo "Jumlah data: $jumlah<br>";
//            echo "Jumlah data yang tepat: $tepat<br>";
//            echo "Jumlah data yang tidak tepat: $tidak_tepat<br>";
//            if ($kosong != 0) {
//                echo "Jumlah data yang kosong: $kosong<br></h4>";
//            }
//            echo "<h2>AKURASI = $akurasi %<br>";
//            echo "LAJU ERROR = $laju_error %<br></h2>";
//            echo "<h4>TP: $TP | TN: $TN | FP: $FP | FN: $FN<br></h4>";
//            echo "<h2>SENSITIVITAS = $sensitivitas %<br>";
//            echo "SPESIFISITAS = $spesifisitas %<br>";
//echo "</h2></center>";
            $tepat=($TA+$TF+$TK+$TP);
                    $tidak_tepat=($FB+$FC+$FD+$FE+$FG+$FH+$FI+$FJ+$FL+$FM+$FN+$FO+$kosong);
                    $akurasi=($tepat/$jumlah_uji)*100;
                    $laju_error=($tidak_tepat/$jumlah_uji)*100;
//                        $sensitivitas=($TP/($TP+$FN))*100;
//                        $spesifisitas=($TN/($FP+$TN))*100;

                    $akurasi = round($akurasi,2);	
                    $laju_error = round($laju_error,2);
                    $sensitivitas = round($sensitivitas,2);	
                    $spesifisitas = round($spesifisitas,2);	


                    echo "<br><br>";
                    echo "<center><h4>";
                    echo "Jumlah prediksi: $jumlah_uji<br>";
                    echo "Jumlah tepat: $tepat<br>";
                    echo "Jumlah tidak tepat: $tidak_tepat<br>";
                    if($kosong!=0){ echo "Jumlah data yang prediksinya kosong: $kosong<br></h4>"; }
                    echo "<h2>AKURASI = $akurasi %<br>";
                    echo "LAJU ERROR = $laju_error %<br></h2>";
            ?>
        </div>
    </div>
</div>