<?php
error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
include_once "database.php";
include_once "fungsi.php";
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

                $sql = $db_object->db_query("SELECT * FROM t_keputusan");
                $keputusan = $id_rule_keputusan = "";
                while ($row = $db_object->db_fetch_array($sql)) {
                    //menggabungkan parent dan akar dengan kata AND
                    if ($row['parent'] != '') {
                        $rule = $row['parent'] . " AND " . $row['akar'];
                    } else {
                        $rule = $row['akar'];
                    }
                    //mengubah parameter
                    $rule = str_replace("<=", " k ", $rule);
                    $rule = str_replace("=", " s ", $rule);
                    $rule = str_replace(">", " l ", $rule);
                    //mengganti nilai
                    $rule = str_replace("jenis_kelamin", "'$n_jenis_kelamin'", $rule);
                    $rule = str_replace("usia", "'$n_usia'", $rule);
                    $rule = str_replace("sekolah", "'$n_sekolah'", $rule);
                    $rule = str_replace("jawaban_a", "'$n_jawaban_a'", $rule);
                    $rule = str_replace("jawaban_b", "$n_jawaban_b", $rule);
                    $rule = str_replace("jawaban_c", "$n_jawaban_c", $rule);
                    $rule = str_replace("jawaban_d", "$n_jawaban_d", $rule);
                    //menghilangkan '
                    $rule = str_replace("'", "", $rule);
                    //explode and
                    $explodeAND = explode(" AND ", $rule);
                    $jmlAND = count($explodeAND);
                    //menghilangkan ()
                    $explodeAND = str_replace("(", "", $explodeAND);
                    $explodeAND = str_replace(")", "", $explodeAND);
                    //deklarasi bol
                    $bolAND=array();
                    $n=0;
                    while($n<$jmlAND){
                        //explode or
                        $explodeOR = explode(" OR ",$explodeAND[$n]);
                        $jmlOR = count($explodeOR);	
                        //deklarasi bol
                        $bol=array();
                        $a=0;
                        while($a<$jmlOR){				
                            //pecah  dengan spasi
                            $exrule2 = explode(" ",$explodeOR[$a]);
                            $parameter = $exrule2[1];				
                            if($parameter=='s'){
                                //pecah  dengan s
                                $explodeRule = explode(" s ",$explodeOR[$a]);
                                //nilai true false						
                                if($explodeRule[0]==$explodeRule[1]){
                                        $bol[$a]="Benar";
                                }else if($explodeRule[0]!=$explodeRule[1]){
                                        $bol[$a]="Salah";
                                }
                            }else if($parameter=='k'){
                                //pecah  dengan k
                                $explodeRule = explode(" k ",$explodeOR[$a]);
                                //nilai true false
                                if($explodeRule[0]<=$explodeRule[1]){
                                        $bol[$a]="Benar";
                                }else{
                                        $bol[$a]="Salah";
                                }
                            }else if($parameter=='l'){
                                //pecah dengan s
                                $explodeRule = explode(" l ",$explodeOR[$a]);
                                //nilai true false
                                if($explodeRule[0]>$explodeRule[1]){
                                        $bol[$a]="Benar";
                                }else{
                                        $bol[$a]="Salah";
                                }
                            }				
                            $a++;
                        }
                        //isi false
                        $bolAND[$n]="Salah";
                        $b=0;			
                        while($b<$jmlOR){
                            //jika $bol[$b] benar bolAND benar
                            if($bol[$b]=="Benar"){
                                    $bolAND[$n]="Benar";
                            }
                            $b++;
                        }			
                        $n++;
                    }
                    //isi boolrule
                    $boolRule="Benar";
                    $a=0;
                    while($a<$jmlAND){			
                            //jika ada yang salah boolrule diganti salah
                            if($bolAND[$a]=="Salah"){
                                    $boolRule="Salah";
                                    break;
                            }						
                            $a++;
                    }		
                    if($boolRule=="Benar"){
                        $keputusan=$row['keputusan'];
                        $id_rule_keputusan=$row['id'];
                        break;
                    }
                    //jika tidak ada rule yang memenuhi kondisi data uji 
                    //maka ambil rule paling bawah(ambil konisi yg paling panjang)????....
                    if ($keputusan == '') {
                        $que = $db_object->db_query("SELECT parent FROM t_keputusan");
                        $jml = array();
                        $exParent = array();
                        $i = 0;
                        while ($row_baris = $db_object->db_fetch_array($que)) {
                            $exParent = explode(" AND ", $row_baris['parent']);
                            $jml[$i] = count($exParent);
                            $i++;
                        }
                        $maxParent = max($jml);
                        $sql_query = $db_object->db_query("SELECT * FROM t_keputusan");
                        while ($row_bar = $db_object->db_fetch_array($sql_query)) {
                            $explP = explode(" AND ", $row_bar['parent']);
                            $jmlT = count($explP);
                            if ($jmlT == $maxParent) {
                                $keputusan = $row_bar['keputusan'];
                                $id_rule[$it] = $row_bar['id'];
                                $id_rule_keputusan = $row_bar['id'];
                                break;
                            }
                        }
                    }
                }//end loop t_keputusan
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
            $jumlah = $db_object->db_num_rows($que);
            $TP = 0;
            $FN = 0;
            $TN = 0;
            $FP = 0;
            $kosong = 0;
            while ($row = $db_object->db_fetch_array($que)) {
                $asli = $row['kelas_asli'];
                $prediksi = $row['kelas_hasil'];
                if (($asli == 'baik' || $asli == 'Baik') & ($prediksi == 'baik') || $prediksi == 'Baik') {
                    $TP++;
                } else if (($asli == 'baik' || $asli == 'Baik') & ($prediksi == 'kurang' || $prediksi == 'Kurang')) {
                    $FN++;
                } else if (($asli == 'kurang' || $asli == 'Kurang') & ($prediksi == 'kurang') || $prediksi == 'Kurang') {
                    $TN++;
                } else if (($asli == 'kurang' || $asli == 'Kurang') & ($prediksi == 'baik') || $prediksi == 'Baik') {
                    $FP++;
                } else if ($prediksi == '') {
                    $kosong++;
                }
            }
            $tepat = ($TP + $TN);
            $tidak_tepat = ($FP + $FN + $kosong);
            $akurasi = ($tepat / $jumlah) * 100;
            $laju_error = ($tidak_tepat / $jumlah) * 100;
            $sensitivitas = ($TP / ($TP + $FN)) * 100;
            $spesifisitas = ($TN / ($FP + $TN)) * 100;

            $akurasi = round($akurasi, 2);
            $laju_error = round($laju_error, 2);
            $sensitivitas = round($sensitivitas, 2);
            $spesifisitas = round($spesifisitas, 2);
//echo "<center><h4>";
            echo "Jumlah data: $jumlah<br>";
            echo "Jumlah data yang tepat: $tepat<br>";
            echo "Jumlah data yang tidak tepat: $tidak_tepat<br>";
            if ($kosong != 0) {
                echo "Jumlah data yang kosong: $kosong<br></h4>";
            }
            echo "<h2>AKURASI = $akurasi %<br>";
            echo "LAJU ERROR = $laju_error %<br></h2>";
            echo "<h4>TP: $TP | TN: $TN | FP: $FP | FN: $FN<br></h4>";
            echo "<h2>SENSITIVITAS = $sensitivitas %<br>";
            echo "SPESIFISITAS = $spesifisitas %<br>";
//echo "</h2></center>";
            ?>
        </div>
    </div>
</div>