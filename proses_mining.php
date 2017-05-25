<?php
function format_decimal($value){
    return round($value, 3);
}

//fungsi utama
function proses_DT($db_object, $parent, $kasus_cabang1, $kasus_cabang2) {
    echo "cabang 1<br>";
    pembentukan_tree($db_object, $parent, $kasus_cabang1);
    echo "cabang 2<br>";
    pembentukan_tree($db_object, $parent, $kasus_cabang2);
}

//fungsi proses dalam suatu kasus data
function pembentukan_tree($db_object, $N_parent, $kasus) {
    //mengisi kondisi
    if ($N_parent != '') {
        $kondisi = $N_parent . " AND " . $kasus;
    } else {
        $kondisi = $kasus;
    }
    echo $kondisi . "<br>";
    //cek data heterogen / homogen???
    $cek = cek_heterohomogen($db_object, 'kelas_asli', $kondisi);
    if ($cek == 'homogen') {
        echo "<br>LEAF ||";
        $sql_keputusan = $db_object->db_query("SELECT DISTINCT(kelas_asli) FROM "
                . "data_latih WHERE $kondisi");
        $row_keputusan = $db_object->db_fetch_array($sql_keputusan);
        $keputusan = $row_keputusan['0'];
        //insert atau lakukan pemangkasan cabang
        pangkas($db_object, $N_parent, $kasus, $keputusan);
    }//jika data masih heterogen
    else if ($cek == 'heterogen') {
        //cek jumlah data
        // $jumlah = jumlah_data($kondisi);
        // if($jumlah<=3){
        //     echo "<br>LEAF ";
        //     $Nsanguin = $kondisi." AND kelas_asli='baik'";
        //     $Nkoleris = $kondisi." AND kelas_asli='kurang'";
        //     $jumlahsanguin = jumlah_data("$Nsanguin");
        //     $jumlahkoleris = jumlah_data("$Nkoleris");
        //     if($jumlahsanguin <= $jumlahkoleris){
        //         $keputusan = 'kurang';
        //     }else{
        //         $keputusan = 'baik';
        //     }
        //     //insert atau lakukan pemangkasan cabang
        //     pangkas($N_parent , $kasus , $keputusan);
        // }
        // //lakukan perhitungan
        // else{
        //jika kondisi tidak kosong kondisi_kelas_asli=tambah and
        $kondisi_kelas_asli = '';
        if ($kondisi != '') {
            $kondisi_kelas_asli = $kondisi . " AND ";
        }
        $jml_sanguin = jumlah_data($db_object, "$kondisi_kelas_asli kelas_asli='Sanguin'");
        $jml_koleris = jumlah_data($db_object, "$kondisi_kelas_asli kelas_asli='Koleris'");
        $jml_melankolis = jumlah_data($db_object, "$kondisi_kelas_asli kelas_asli='Melankolis'");
        $jml_plegmatis = jumlah_data($db_object, "$kondisi_kelas_asli kelas_asli='Plegmatis'");
        
        $jml_total = $jml_sanguin + $jml_koleris + $jml_melankolis + $jml_plegmatis;
        echo "Jumlah data = " . $jml_total . "<br>";
        echo "Jumlah Sanguin = " . $jml_sanguin . "<br>";
        echo "Jumlah Koleris = " . $jml_koleris . "<br>";
        echo "Jumlah Melankolis = " . $jml_melankolis . "<br>";
        echo "Jumlah Plegmatis = " . $jml_plegmatis . "<br>";

        //hitung entropy semua
        $entropy_all = hitung_entropy($jml_sanguin, $jml_koleris, $jml_melankolis, $jml_plegmatis);
        echo "Entropy All = " . $entropy_all . "<br>";

        $nilai_usia = array();
        $nilai_usia = cek_nilaiAtribut($db_object, 'usia',$kondisi);
        $jmlUsia = count($nilai_usia);

        echo "<table class='table table-bordered table-striped  table-hover'>";
        echo "<tr><th>Nilai Atribut</th> <th>Jumlah data</th> <th>Jumlah Sanguin</th> <th>Jumlah Koleris</th> "
        . "<th>Jumlah Melankolis</th> <th>Jumlah Plegmatis</th> <th>Entropy</th> <th>Gain</th><tr>";

        $db_object->db_query("TRUNCATE gain");
        //hitung gain atribut KATEGORIKAL
        hitung_gain($db_object, $kondisi, "jenis_kelamin", $entropy_all, "jenis_kelamin='L'", "jenis_kelamin='P'", "", "", "");
        
        //hitung gain atribut KATEGORIKAL
        if($jmlUsia!=1){
            $NA1Usia="usia='$nilai_usia[0]'";
            $NA2Usia="";
            $NA3Usia="";
            if($jmlUsia==2){
                    $NA2Usia="usia='$nilai_usia[1]'";
            }else if ($jmlUsia==3){
                    $NA2Usia="usia='$nilai_usia[1]'";
                    $NA3Usia="usia='$nilai_usia[2]'";
            }				
            hitung_gain($db_object, $kondisi , "usia", $entropy_all , $NA1Usia, $NA2Usia, $NA3Usia, "" , "");	
        }
        

        //hitung gain atribut KATEGORIKAL
        hitung_gain($db_object, $kondisi, "sekolah", $entropy_all, "sekolah='Negeri'", "sekolah='Swasta'", "", "", "");

        //hitung gain atribut Numerik
        //JAWABAN A
        hitung_gain($db_object, $kondisi, "Jawaban A v=5", $entropy_all, "jawaban_a<=5", "jawaban_a>5", "", "", "");
        hitung_gain($db_object, $kondisi, "Jawaban A v=10", $entropy_all, "jawaban_a<=10", "jawaban_a>10", "", "", "");
        hitung_gain($db_object, $kondisi, "Jawaban A v=15", $entropy_all, "jawaban_a<=15", "jawaban_a>15", "", "", "");
        hitung_gain($db_object, $kondisi, "Jawaban A v=20", $entropy_all, "jawaban_a<=20", "jawaban_a>20", "", "", "");
        
        //JAWABAN B
        hitung_gain($db_object, $kondisi, "Jawaban B v=5", $entropy_all, "jawaban_b<=5", "jawaban_b>5", "", "", "");
        hitung_gain($db_object, $kondisi, "Jawaban B v=10", $entropy_all, "jawaban_b<=10", "jawaban_b>10", "", "", "");
        hitung_gain($db_object, $kondisi, "Jawaban B v=15", $entropy_all, "jawaban_b<=15", "jawaban_b>15", "", "", "");
        hitung_gain($db_object, $kondisi, "Jawaban B v=20", $entropy_all, "jawaban_b<=20", "jawaban_b>20", "", "", "");
        
        //JAWABAN C
        hitung_gain($db_object, $kondisi, "Jawaban C v=5", $entropy_all, "jawaban_c<=5", "jawaban_c>5", "", "", "");
        hitung_gain($db_object, $kondisi, "Jawaban C v=10", $entropy_all, "jawaban_c<=10", "jawaban_c>10", "", "", "");
        hitung_gain($db_object, $kondisi, "Jawaban C v=15", $entropy_all, "jawaban_c<=15", "jawaban_c>15", "", "", "");
        hitung_gain($db_object, $kondisi, "Jawaban C v=20", $entropy_all, "jawaban_c<=20", "jawaban_c>20", "", "", "");
        
        //JAWABAN D
        hitung_gain($db_object, $kondisi, "Jawaban D v=5", $entropy_all, "jawaban_d<=5", "jawaban_d>5", "", "", "");
        hitung_gain($db_object, $kondisi, "Jawaban D v=10", $entropy_all, "jawaban_d<=10", "jawaban_d>10", "", "", "");
        hitung_gain($db_object, $kondisi, "Jawaban D v=15", $entropy_all, "jawaban_d<=15", "jawaban_d>15", "", "", "");
        hitung_gain($db_object, $kondisi, "Jawaban D v=20", $entropy_all, "jawaban_d<=20", "jawaban_d>20", "", "", "");

        echo "</table>";
        //ambil nilai gain terBesar
        $sql_max = $db_object->db_query("SELECT MAX(gain) FROM gain");
        $row_max = $db_object->db_fetch_array($sql_max);
        $max_gain = $row_max[0];
        $sql = $db_object->db_query("SELECT * FROM gain WHERE gain=$max_gain");
        $row = $db_object->db_fetch_array($sql);
        $atribut = $row[2];
        echo "Atribut terpilih = " . $atribut . ", dengan nilai gain = " . $max_gain . "<br>";
        echo "<br>================================<br>";

        //jika max gain = 0 perhitungan dihentikan dan mengambil keputusan
        if ($max_gain == 0) {
            echo "<br>LEAF ";
            $Nsanguin = $kondisi . " AND kelas_asli='Sanguin'";
            $Nkoleris = $kondisi . " AND kelas_asli='Koleris'";
            $Nmelankolis = $kondisi . " AND kelas_asli='Melankolis'";
            $Nplegmatis = $kondisi . " AND kelas_asli='Plegmatis'";
            $jumlahsanguin = jumlah_data($db_object, "$Nsanguin");
            $jumlahkoleris = jumlah_data($db_object, "$Nkoleris");
            $jumlahmelankolis = jumlah_data($db_object, "$Nmelankolis");
            $jumlahplegmatis = jumlah_data($db_object, "$Nplegmatis");
            if($jumlahsanguin >= $jumlahkoleris && 
                    $jumlahsanguin >= $jumlahmelankolis && 
                    $jumlahsanguin >= $jumlahplegmatis) {
                $keputusan = 'Sanguin';
            }
            elseif($jumlahkoleris >= $jumlahsanguin && 
                    $jumlahkoleris >= $jumlahmelankolis && 
                    $jumlahkoleris >= $jumlahplegmatis) {
                $keputusan = 'Koleris';
            }
            elseif($jumlahmelankolis >= $jumlahsanguin && 
                    $jumlahmelankolis >= $jumlahkoleris && 
                    $jumlahmelankolis >= $jumlahplegmatis) {
                $keputusan = 'Melankolis';
            }
            else {
                $keputusan = 'Plegmatis';
            }
            //insert atau lakukan pemangkasan cabang
            pangkas($db_object, $N_parent, $kasus, $keputusan);
        }
        //jika max_gain >0 lanjut..
        else {
            //jenis kelamin terpilih
            if ($atribut == "jenis_kelamin") {
                proses_DT($db_object, $kondisi, "($atribut='L')", "($atribut='P')");
            }
            
            //usia terpilih
            if ($atribut == "usia") {
                //jika nilai atribut 3
                if($jmlUsia==3){
                    //hitung rasio
                    $cabang = array();
                    $cabang = hitung_rasio($db_object, $kondisi , 'usia',$max_gain,$nilai_usia[0],$nilai_usia[1],$nilai_usia[2],'','');
                    $exp_cabang = explode(" , ",$cabang[1]);						
                    proses_DT($db_object, $kondisi , "($atribut='$cabang[0]')","($atribut='$exp_cabang[0]' OR $atribut='$exp_cabang[1]')");						
                }
                //jika nilai atribut 2
                else if($jmlUsia==2){
                    proses_DT($db_object, $kondisi , "($atribut='$nilai_usia[0]')" , "($atribut='$nilai_usia[1]')");
                }
            }
            
            //sekolah terpilih
            if ($atribut == "sekolah") {
                proses_DT($db_object, $kondisi, "($atribut='Negeri')", "($atribut='Swasta')");
            }


            //Jawaban A Terpilih
            if ($atribut == "Jawaban A v=5") {
                proses_DT($db_object, $kondisi, "(jawaban_a<=5)", "(jawaban_a>5)");
            } else if ($atribut == "Jawaban A v=10") {
                proses_DT($db_object, $kondisi, "(jawaban_a<=10)", "(jawaban_a>10)");
            } else if ($atribut == "Jawaban A v=15") {
                proses_DT($db_object, $kondisi, "(jawaban_a<=15)", "(jawaban_a>15)");
            } else if ($atribut == "Jawaban A v=20") {
                proses_DT($db_object, $kondisi, "(jawaban_a<=20)", "(jawaban_a>20)");
            }
            
            //Jawaban B Terpilih
            if ($atribut == "Jawaban B v=5") {
                proses_DT($db_object, $kondisi, "(jawaban_b<=5)", "(jawaban_b>5)");
            } else if ($atribut == "Jawaban B v=10") {
                proses_DT($db_object, $kondisi, "(jawaban_b<=10)", "(jawaban_b>10)");
            } else if ($atribut == "Jawaban B v=15") {
                proses_DT($db_object, $kondisi, "(jawaban_b<=15)", "(jawaban_b>15)");
            } else if ($atribut == "Jawaban B v=20") {
                proses_DT($db_object, $kondisi, "(jawaban_b<=20)", "(jawaban_b>20)");
            }
            
            //Jawaban C Terpilih
            if ($atribut == "Jawaban C v=5") {
                proses_DT($db_object, $kondisi, "(jawaban_c<=5)", "(jawaban_c>5)");
            } else if ($atribut == "Jawaban C v=10") {
                proses_DT($db_object, $kondisi, "(jawaban_c<=10)", "(jawaban_c>10)");
            } else if ($atribut == "Jawaban C v=15") {
                proses_DT($db_object, $kondisi, "(jawaban_c<=15)", "(jawaban_c>15)");
            } else if ($atribut == "Jawaban C v=20") {
                proses_DT($db_object, $kondisi, "(jawaban_c<=20)", "(jawaban_c>20)");
            }
            
            //Jawaban D Terpilih
            if ($atribut == "Jawaban D v=5") {
                proses_DT($db_object, $kondisi, "(jawaban_d<=5)", "(jawaban_d>5)");
            } else if ($atribut == "Jawaban D v=10") {
                proses_DT($db_object, $kondisi, "(jawaban_d<=10)", "(jawaban_d>10)");
            } else if ($atribut == "Jawaban D v=15") {
                proses_DT($db_object, $kondisi, "(jawaban_d<=15)", "(jawaban_d>15)");
            } else if ($atribut == "Jawaban D v=20") {
                proses_DT($db_object, $kondisi, "(jawaban_d<=20)", "(jawaban_d>20)");
            }

            
        }//end else jika max_gain>0
        // }// end jumlah<3
    }//end else if($cek=='heterogen'){
}

//==============================================================================
//fungsi cek nilai atribut
function cek_nilaiAtribut($db_object, $field , $kondisi){
    //sql disticnt		
    $hasil = array();
    if($kondisi==''){
            $sql = $db_object->db_query("SELECT DISTINCT($field) FROM data_latih");					
    }else{
            $sql = $db_object->db_query("SELECT DISTINCT($field) FROM data_latih WHERE $kondisi");					
    }
    $a=0;
    while($row = $db_object->db_fetch_array($sql)){
            $hasil[$a] = $row['0'];
            $a++;
    }	
    return $hasil;
}

//fungsi cek heterogen data
function cek_heterohomogen($db_object, $field, $kondisi) {
    //sql disticnt
    if ($kondisi == '') {
        $sql = $db_object->db_query("SELECT DISTINCT($field) FROM data_latih");
    } else {
        $sql = $db_object->db_query("SELECT DISTINCT($field) FROM data_latih WHERE $kondisi");
    }
    //jika jumlah data 1 maka homogen
    if ($db_object->db_num_rows($sql) == 1) {
        $nilai = "homogen";
    } else {
        $nilai = "heterogen";
    }
    return $nilai;
}

//fungsi menghitung jumlah data
function jumlah_data($db_object, $kondisi) {
    //sql
    if ($kondisi == '') {
        $sql = "SELECT COUNT(*) FROM data_latih $kondisi";
    } else {
        $sql = "SELECT COUNT(*) FROM data_latih WHERE $kondisi";
    }

    $query = $db_object->db_query($sql);
    $row = $db_object->db_fetch_array($query);
    $jml = $row['0'];
    return $jml;
}

//fungsi pemangkasan cabang
function pangkas($db_object, $PARENT, $KASUS, $LEAF) {
    //PEMANGKASAN CABANG
//    $sql_pangkas = $db_object->db_query("SELECT * FROM t_keputusan "
//            . "WHERE parent=\"$PARENT\" AND keputusan=\"$LEAF\"");
//    $row_pangkas = $db_object->db_fetch_array($sql_pangkas);
//    $jml_pangkas = $db_object->db_num_rows($sql_pangkas);
    //jika keputusan dan parent belum ada maka insert
//    if ($jml_pangkas == 0) {
        $sql_in = "INSERT INTO t_keputusan "
                . "(parent,akar,keputusan)"
                . " VALUES (\"$PARENT\" , \"$KASUS\" , \"$LEAF\")";
        $db_object->db_query($sql_in);
        // echo "1".$sql_in;
//    }
    //jika keputusan dan parent sudah ada maka delete
//    else {
//        $db_object->db_query("DELETE FROM t_keputusan WHERE id='$row_pangkas[0]'");
//        $exPangkas = explode(" AND ", $PARENT);
//        $jmlEXpangkas = count($exPangkas);
//        $temp = array();
//        for ($a = 0; $a < ($jmlEXpangkas - 1); $a++) {
//            $temp[$a] = $exPangkas[$a];
//        }
//        $imPangkas = implode(" AND ", $temp);
//        $akarPangkas = $exPangkas[$jmlEXpangkas - 1];
//        $que_pangkas = $db_object->db_query("SELECT * FROM t_keputusan "
//                . "WHERE parent=\"$imPangkas\" AND keputusan=\"$LEAF\"");
//        $baris_pangkas = $db_object->db_fetch_array($que_pangkas);
//        $jumlah_pangkas = $db_object->db_num_rows($que_pangkas);
//        if ($jumlah_pangkas == 0) {
//            $sql_in2 = "INSERT INTO t_keputusan "
//                    . "(parent,akar,keputusan)"
//                    . " VALUES (\"$imPangkas\" , \"$akarPangkas\" , \"$LEAF\")";
//            $db_object->db_query($sql_in2);
//            //echo "2".$sql_in2;
//        } else {
//            pangkas($db_object, $imPangkas, $akarPangkas, $LEAF);
//        }
//    }
    echo "Keputusan = " . $LEAF . "<br>================================<br>";
}

//fungsi menghitung gain
function hitung_gain($db_object, $kasus, $atribut, $ent_all, $kondisi1, $kondisi2, $kondisi3, $kondisi4, $kondisi5) {
    $data_kasus = '';
    if ($kasus != '') {
        $data_kasus = $kasus . " AND ";
    }

    //untuk atribut 2 nilai atribut	
    if ($kondisi3 == '') {
        $j_sanguin1 = jumlah_data($db_object, "$data_kasus kelas_asli='Sanguin' AND $kondisi1");
        $j_koleris1 = jumlah_data($db_object, "$data_kasus kelas_asli='Koleris' AND $kondisi1");
        $j_melankolis1 = jumlah_data($db_object, "$data_kasus kelas_asli='Melankolis' AND $kondisi1");
        $j_plegmatis1 = jumlah_data($db_object, "$data_kasus kelas_asli='Plegmatis' AND $kondisi1");
        $jml1 = $j_sanguin1 + $j_koleris1 + $j_melankolis1 + $j_plegmatis1;
        
        $j_sanguin2 = jumlah_data($db_object, "$data_kasus kelas_asli='Sanguin' AND $kondisi2");
        $j_koleris2 = jumlah_data($db_object, "$data_kasus kelas_asli='Koleris' AND $kondisi2");
        $j_melankolis2 = jumlah_data($db_object, "$data_kasus kelas_asli='Melankolis' AND $kondisi2");
        $j_plegmatis2 = jumlah_data($db_object, "$data_kasus kelas_asli='Plegmatis' AND $kondisi2");
        $jml2 = $j_sanguin2 + $j_koleris2 + $j_melankolis2 + $j_plegmatis2;
        //hitung entropy masing-masing kondisi
        $jml_total = $jml1 + $jml2;
        $ent1 = hitung_entropy($j_sanguin1, $j_koleris1, $j_melankolis1, $j_plegmatis1);
        $ent2 = hitung_entropy($j_sanguin2, $j_koleris2, $j_melankolis2, $j_plegmatis2);

        $gain = $ent_all - ((($jml1 / $jml_total) * $ent1) + (($jml2 / $jml_total) * $ent2));
        //desimal 3 angka dibelakang koma
        $gain = format_decimal($gain);

        echo "<tr>";
        echo "<td>" . $kondisi1 . "</td>";
        echo "<td>" . $jml1 . "</td>";
        echo "<td>" . $j_sanguin1 . "</td>";
        echo "<td>" . $j_koleris1 . "</td>";
        echo "<td>" . $j_melankolis1 . "</td>";
        echo "<td>" . $j_plegmatis1 . "</td>";
        echo "<td>" . $ent1 . "</td>";
        echo "<td>&nbsp;</td>";
        echo "</tr>";

        echo "<tr>";
        echo "<td>" . $kondisi2 . "</td>";
        echo "<td>" . $jml2 . "</td>";
        echo "<td>" . $j_sanguin2 . "</td>";
        echo "<td>" . $j_koleris2 . "</td>";
        echo "<td>" . $j_melankolis2 . "</td>";
        echo "<td>" . $j_plegmatis2 . "</td>";
        echo "<td>" . $ent2 . "</td>";
        echo "<td>" . $gain . "</td>";
        echo "</tr>";

        echo "<tr><td colspan='8'></td></tr>";
    }
     //untuk atribut 3 nilai atribut
     else if($kondisi4==''){
     	$j_sanguin1 = jumlah_data($db_object, "$data_kasus kelas_asli='Sanguin' AND $kondisi1");
     	$j_koleris1 = jumlah_data($db_object, "$data_kasus kelas_asli='Koleris' AND $kondisi1");
        $j_melankolis1 = jumlah_data($db_object, "$data_kasus kelas_asli='Melankolis' AND $kondisi1");
        $j_plegmatis1 = jumlah_data($db_object, "$data_kasus kelas_asli='Plegmatis' AND $kondisi1");
     	$jml1 = $j_sanguin1 + $j_koleris1 + $j_melankolis1 + $j_plegmatis1;
        
     	$j_sanguin2 = jumlah_data($db_object, "$data_kasus kelas_asli='Sanguin' AND $kondisi2");
     	$j_koleris2 = jumlah_data($db_object, "$data_kasus kelas_asli='Koleris' AND $kondisi2");
        $j_melankolis2 = jumlah_data($db_object, "$data_kasus kelas_asli='Melankolis' AND $kondisi2");
        $j_plegmatis2 = jumlah_data($db_object, "$data_kasus kelas_asli='Plegmatis' AND $kondisi2");
     	$jml2 = $j_sanguin2 + $j_koleris2 + $j_melankolis2 + $j_plegmatis2;
        
     	$j_sanguin3 = jumlah_data($db_object, "$data_kasus kelas_asli='Sanguin' AND $kondisi3");
     	$j_koleris3 = jumlah_data($db_object, "$data_kasus kelas_asli='Koleris' AND $kondisi3");
        $j_melankolis3 = jumlah_data($db_object, "$data_kasus kelas_asli='Melankolis' AND $kondisi3");
        $j_plegmatis3 = jumlah_data($db_object, "$data_kasus kelas_asli='Plegmatis' AND $kondisi3");
     	$jml3 = $j_sanguin3 + $j_koleris3 + $j_melankolis3 + $j_plegmatis3;
        
     	//hitung entropy masing-masing kondisi
     	$jml_total = $jml1 + $jml2 + $jml3;
     	$ent1 = hitung_entropy($j_sanguin1 , $j_koleris1, $j_melankolis1, $j_plegmatis1);
     	$ent2 = hitung_entropy($j_sanguin2 , $j_koleris2, $j_melankolis2, $j_plegmatis2);
     	$ent3 = hitung_entropy($j_sanguin3 , $j_koleris3, $j_melankolis3, $j_plegmatis3);
     	$gain = $ent_all - ((($jml1/$jml_total)*$ent1) + (($jml2/$jml_total)*$ent2) 
     				+ (($jml3/$jml_total)*$ent3));							
     	//desimal 3 angka dibelakang koma
     	$gain = format_decimal($gain);				
     	echo "<tr>";
     	echo "<td>".$kondisi1."</td>";
     	echo "<td>".$jml1."</td>";
     	echo "<td>".$j_sanguin1."</td>";
     	echo "<td>".$j_koleris1."</td>";
        echo "<td>".$j_melankolis1."</td>";
        echo "<td>".$j_plegmatis1."</td>";
     	echo "<td>".$ent1."</td>";
     	echo "<td>&nbsp;</td>";
     	echo "</tr>";
     	echo "<tr>";
     	echo "<td>".$kondisi2."</td>";
     	echo "<td>".$jml2."</td>";
     	echo "<td>".$j_sanguin2."</td>";
     	echo "<td>".$j_koleris2."</td>";
        echo "<td>".$j_melankolis2."</td>";
        echo "<td>".$j_plegmatis2."</td>";
     	echo "<td>".$ent2."</td>";
     	echo "<td>&nbsp;</td>";
     	echo "</tr>";
     	echo "<tr>";
     	echo "<td>".$kondisi3."</td>";
     	echo "<td>".$jml3."</td>";
     	echo "<td>".$j_sanguin3."</td>";
     	echo "<td>".$j_koleris3."</td>";
        echo "<td>".$j_melankolis3."</td>";
        echo "<td>".$j_plegmatis3."</td>";
     	echo "<td>".$ent3."</td>";
     	echo "<td>".$gain."</td>";
     	echo "</tr>";
     	echo "<tr><td colspan='8'></td></tr>";
     }
    // //untuk atribut 4 nilai atribut
    // else if($kondisi5==''){
    // 	$j_baik1 = jumlah_data("$data_kasus kelas_asli='baik' AND $kondisi1");
    // 	$j_Tdkbaik1 = jumlah_data("$data_kasus kelas_asli='kurang' AND $kondisi1");
    // 	$jml1 = $j_baik1 + $j_Tdkbaik1;
    // 	$j_baik2 = jumlah_data("$data_kasus kelas_asli='baik' AND $kondisi2");
    // 	$j_Tdkbaik2 = jumlah_data("$data_kasus kelas_asli='kurang' AND $kondisi2");
    // 	$jml2 = $j_baik2 + $j_Tdkbaik2;
    // 	$j_baik3 = jumlah_data("$data_kasus kelas_asli='baik' AND $kondisi3");
    // 	$j_Tdkbaik3 = jumlah_data("$data_kasus kelas_asli='kurang' AND $kondisi3");
    // 	$jml3 = $j_baik3 + $j_Tdkbaik3;
    // 	$j_baik4 = jumlah_data("$data_kasus kelas_asli='baik' AND $kondisi4");
    // 	$j_Tdkbaik4 = jumlah_data("$data_kasus kelas_asli='kurang' AND $kondisi4");
    // 	$jml4 = $j_baik4 + $j_Tdkbaik4;
    // 	//hitung entropy masing-masing kondisi
    // 	$jml_total = $jml1 + $jml2 + $jml3+$jml4;
    // 	$ent1 = hitung_entropy($j_baik1 , $j_Tdkbaik1);
    // 	$ent2 = hitung_entropy($j_baik2 , $j_Tdkbaik2);
    // 	$ent3 = hitung_entropy($j_baik3 , $j_Tdkbaik3);
    // 	$ent4 = hitung_entropy($j_baik4 , $j_Tdkbaik4);
    // 	$gain = $ent_all - ((($jml1/$jml_total)*$ent1) + (($jml2/$jml_total)*$ent2)
    // 				+ (($jml3/$jml_total)*$ent3) + (($jml4/$jml_total)*$ent4));				
    // 	//desimal 3 angka dibelakang koma
    // 	$gain = format_decimal($gain);				
    // 	echo "<tr>";
    // 	echo "<td>".$kondisi1."</td>";
    // 	echo "<td>".$jml1."</td>";
    // 	echo "<td>".$j_baik1."</td>";
    // 	echo "<td>".$j_Tdkbaik1."</td>";
    // 	echo "<td>".$ent1."</td>";
    // 	echo "<td>&nbsp;</td>";
    // 	echo "</tr>";
    // 	echo "<tr>";
    // 	echo "<td>".$kondisi2."</td>";
    // 	echo "<td>".$jml2."</td>";
    // 	echo "<td>".$j_baik2."</td>";
    // 	echo "<td>".$j_Tdkbaik2."</td>";
    // 	echo "<td>".$ent2."</td>";
    // 	echo "<td>&nbsp;</td>";
    // 	echo "</tr>";
    // 	echo "<tr>";
    // 	echo "<td>".$kondisi3."</td>";
    // 	echo "<td>".$jml3."</td>";
    // 	echo "<td>".$j_baik3."</td>";
    // 	echo "<td>".$j_Tdkbaik3."</td>";
    // 	echo "<td>".$ent3."</td>";
    // 	echo "<td>&nbsp;</td>";
    // 	echo "</tr>";
    // 	echo "<tr>";
    // 	echo "<td>".$kondisi4."</td>";
    // 	echo "<td>".$jml4."</td>";
    // 	echo "<td>".$j_baik4."</td>";
    // 	echo "<td>".$j_Tdkbaik4."</td>";
    // 	echo "<td>".$ent4."</td>";
    // 	echo "<td>".$gain."</td>";
    // 	echo "</tr>";
    // 	echo "<tr><td colspan='8'></td></tr>";
    // }
    // //untuk atribut 5 nilai atribut	
    // else{
    // 	$j_baik1 = jumlah_data("$data_kasus kelas_asli='baik' AND $kondisi1");
    // 	$j_Tdkbaik1 = jumlah_data("$data_kasus kelas_asli='kurang' AND $kondisi1");
    // 	$jml1 = $j_baik1 + $j_Tdkbaik1;
    // 	$j_baik2 = jumlah_data("$data_kasus kelas_asli='baik' AND $kondisi2");
    // 	$j_Tdkbaik2 = jumlah_data("$data_kasus kelas_asli='kurang' AND $kondisi2");
    // 	$jml2 = $j_baik2 + $j_Tdkbaik2;
    // 	$j_baik3 = jumlah_data("$data_kasus kelas_asli='baik' AND $kondisi3");
    // 	$j_Tdkbaik3 = jumlah_data("$data_kasus kelas_asli='kurang' AND $kondisi3");
    // 	$jml3 = $j_baik3 + $j_Tdkbaik3;
    // 	$j_baik4 = jumlah_data("$data_kasus kelas_asli='baik' AND $kondisi4");
    // 	$j_Tdkbaik4 = jumlah_data("$data_kasus kelas_asli='kurang' AND $kondisi4");
    // 	$jml4 = $j_baik4 + $j_Tdkbaik4;
    // 	$j_baik5 = jumlah_data("$data_kasus kelas_asli='baik' AND $kondisi5");
    // 	$j_Tdkbaik5 = jumlah_data("$data_kasus kelas_asli='kurang' AND $kondisi5");
    // 	$jml5 = $j_baik5 + $j_Tdkbaik5;
    // 	//hitung entropy masing-masing kondisi
    // 	$jml_total = $jml1 + $jml2 + $jml3 + $jml4 + $jml5;
    // 	$ent1 = hitung_entropy($j_baik1 , $j_Tdkbaik1);
    // 	$ent2 = hitung_entropy($j_baik2 , $j_Tdkbaik2);
    // 	$ent3 = hitung_entropy($j_baik3 , $j_Tdkbaik3);
    // 	$ent4 = hitung_entropy($j_baik4 , $j_Tdkbaik4);
    // 	$ent5 = hitung_entropy($j_baik5 , $j_Tdkbaik5);
    // 	$gain = $ent_all - ((($jml1/$jml_total)*$ent1) + (($jml2/$jml_total)*$ent2) 
    // 				+ (($jml3/$jml_total)*$ent3) + (($jml4/$jml_total)*$ent4) + (($jml5/$jml_total)*$ent5));
    // 	//desimal 3 angka dibelakang koma
    // 	$gain = format_decimal($gain);				
    // 	echo "<tr>";
    // 	echo "<td>".$kondisi1."</td>";
    // 	echo "<td>".$jml1."</td>";
    // 	echo "<td>".$j_baik1."</td>";
    // 	echo "<td>".$j_Tdkbaik1."</td>";
    // 	echo "<td>".$ent1."</td>";
    // 	echo "<td>&nbsp;</td>";
    // 	echo "</tr>";
    // 	echo "<tr>";
    // 	echo "<td>".$kondisi2."</td>";
    // 	echo "<td>".$jml2."</td>";
    // 	echo "<td>".$j_baik2."</td>";
    // 	echo "<td>".$j_Tdkbaik2."</td>";
    // 	echo "<td>".$ent2."</td>";
    // 	echo "<td>&nbsp;</td>";
    // 	echo "</tr>";
    // 	echo "<tr>";
    // 	echo "<td>".$kondisi3."</td>";
    // 	echo "<td>".$jml3."</td>";
    // 	echo "<td>".$j_baik3."</td>";
    // 	echo "<td>".$j_Tdkbaik3."</td>";
    // 	echo "<td>".$ent3."</td>";
    // 	echo "<td>&nbsp;</td>";
    // 	echo "</tr>";
    // 	echo "<tr>";
    // 	echo "<td>".$kondisi4."</td>";
    // 	echo "<td>".$jml4."</td>";
    // 	echo "<td>".$j_baik4."</td>";
    // 	echo "<td>".$j_Tdkbaik4."</td>";
    // 	echo "<td>".$ent4."</td>";
    // 	echo "<td>&nbsp;</td>";
    // 	echo "</tr>";
    // 	echo "<tr>";
    // 	echo "<td>".$kondisi5."</td>";
    // 	echo "<td>".$jml5."</td>";
    // 	echo "<td>".$j_baik5."</td>";
    // 	echo "<td>".$j_Tdkbaik5."</td>";
    // 	echo "<td>".$ent5."</td>";
    // 	echo "<td>".$gain."</td>";
    // 	echo "</tr>";
    // 	echo "<tr><td colspan='8'></td></tr>";
    // }
    // //desimal 3 angka dibelakang koma
    // $gain = format_decimal($gain);	
    // if($gain>0){
    // 	echo "Gain ".$atribut." = ".$gain."<br><br>";
    // }		
    $db_object->db_query("INSERT INTO gain VALUES ('','1','$atribut','$gain')");
}

//fungsi menghitung entropy
function hitung_entropy($nilai1, $nilai2, $nilai3, $nilai4) {
    $total = $nilai1 + $nilai2 + $nilai3 + $nilai4;
    //jika salah satu nilai 0, maka entropy 0
//    if ($nilai1 == 0 || $nilai2 == 0 || $nilai3 == 0 || $nilai4 == 0) {
//        $entropy = 0;
//    }
//    else {
    $atribut1 = (-($nilai1 / $total) * (log(($nilai1 / $total), 2)));
    $atribut2 = (-($nilai2 / $total) * (log(($nilai2 / $total), 2)));
    $atribut3 = (-($nilai3 / $total) * (log(($nilai3 / $total), 2)));
    $atribut4 = (-($nilai4 / $total) * (log(($nilai4 / $total), 2)));
    
    $atribut1 = is_nan($atribut1)?0:$atribut1;
    $atribut2 = is_nan($atribut2)?0:$atribut2;
    $atribut3 = is_nan($atribut3)?0:$atribut3;
    $atribut4 = is_nan($atribut4)?0:$atribut4;
    
        $entropy = $atribut1 + 
                    $atribut2 + 
                    $atribut3 +
                    $atribut4;
//    }
    //desimal 3 angka dibelakang koma
    $entropy = format_decimal($entropy);
    return $entropy;
}

//fungsi hitung rasio
function hitung_rasio($db_object, $kasus , $atribut , $gain , $nilai1 , $nilai2 , $nilai3 , $nilai4 , $nilai5){				
    $data_kasus = '';
    if($kasus!=''){
        $data_kasus = $kasus." AND ";
    }
    //menentukan jumlah nilai
    $jmlNilai=5;
    //jika nilai 5 kosong maka nilai atribut-nya 4
    if($nilai5==''){
        $jmlNilai=4;
    }
    //jika nilai 4 kosong maka nilai atribut-nya 3
    if($nilai4==''){
        $jmlNilai=3;
    }
    $db_object->db_query("TRUNCATE rasio_gain");		
    if($jmlNilai==3){
        $opsi11 = jumlah_data($db_object, "$data_kasus ($atribut='$nilai2' OR $atribut='$nilai3')");
        $opsi12 = jumlah_data($db_object, "$data_kasus $atribut='$nilai1'");
        $tot_opsi1=$opsi11+$opsi12;
        $opsi21 = jumlah_data($db_object, "$data_kasus ($atribut='$nilai3' OR $atribut='$nilai1')");
        $opsi22 = jumlah_data($db_object, "$data_kasus $atribut='$nilai2'");
        $tot_opsi2=$opsi21+$opsi22;
        $opsi31 = jumlah_data($db_object, "$data_kasus ($atribut='$nilai1' OR $atribut='$nilai2')");
        $opsi32 = jumlah_data($db_object, "$data_kasus $atribut='$nilai3'");
        $tot_opsi3=$opsi31+$opsi32;			
        //hitung split info
        $opsi1 = (-($opsi11/$tot_opsi1)*(log(($opsi11/$tot_opsi1),2))) + (-($opsi12/$tot_opsi1)*(log(($opsi12/$tot_opsi1),2)));
        $opsi2 = (-($opsi21/$tot_opsi2)*(log(($opsi21/$tot_opsi2),2))) + (-($opsi22/$tot_opsi2)*(log(($opsi22/$tot_opsi2),2)));
        $opsi3 = (-($opsi31/$tot_opsi3)*(log(($opsi31/$tot_opsi3),2))) + (-($opsi32/$tot_opsi3)*(log(($opsi32/$tot_opsi3),2)));
        //desimal 3 angka dibelakang koma
        $opsi1 = format_decimal($opsi1);
        $opsi2 = format_decimal($opsi2);
        $opsi3 = format_decimal($opsi3);										
        //hitung rasio
        $rasio1 = $gain/$opsi1;
        $rasio2 = $gain/$opsi2;
        $rasio3 = $gain/$opsi3;
        //desimal 3 angka dibelakang koma
        $rasio1 = format_decimal($rasio1);
        $rasio2 = format_decimal($rasio2);
        $rasio3 = format_decimal($rasio3);
            //cetak
            echo "Opsi 1 : <br>jumlah ".$nilai2."/".$nilai3." = ".$opsi11.
                    "<br>jumlah ".$nilai1." = ".$opsi12.
                    "<br>Split = ".$opsi1.
                    "<br>Rasio = ".$rasio1."<br>";
            echo "Opsi 2 : <br>jumlah ".$nilai3."/".$nilai1." = ".$opsi21.
                    "<br>jumlah ".$nilai2." = ".$opsi22.
                    "<br>Split = ".$opsi2.
                    "<br>Rasio = ".$rasio2."<br>";
            echo "Opsi 3 : <br>jumlah ".$nilai1."/".$nilai2." = ".$opsi31.
                    "<br>jumlah ".$nilai3." = ".$opsi32.
                    "<br>Split = ".$opsi3.
                    "<br>Rasio = ".$rasio3."<br>";

            //insert 
            $db_object->db_query("INSERT INTO rasio_gain VALUES 
                                    ('' , 'opsi1' , '$nilai1' , '$nilai2 , $nilai3' , '$rasio1'),
                                    ('' , 'opsi2' , '$nilai2' , '$nilai3 , $nilai1' , '$rasio2'),
                                    ('' , 'opsi3' , '$nilai3' , '$nilai1 , $nilai2' , '$rasio3')");
    }
    
    $sql_max = $db_object->db_query("SELECT MAX(rasio_gain) FROM rasio_gain");
    $row_max = $db_object->db_fetch_array($sql_max);	
    $max_rasio = $row_max['0'];
    $sql = $db_object->db_query("SELECT * FROM rasio_gain WHERE rasio_gain=$max_rasio");
    $row = $db_object->db_fetch_array($sql);	
    $opsiMax = array();
    $opsiMax[0] = $row[2];
    $opsiMax[1] = $row[3];		
    echo "<br>=========================<br>";
    return $opsiMax;		
}


function klasifikasi($db_object, $n_jenis_kelamin, $n_usia, $n_sekolah, $n_jawaban_a, $n_jawaban_b, $n_jawaban_c, $n_jawaban_d) {

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

    return array('keputusan' => $keputusan, 'id_rule' => $id_rule_keputusan);
}
