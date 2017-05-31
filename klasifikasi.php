<?php
//session_start();
if (!isset($_SESSION['kepribadian_c45_id'])) {
    header("location:index.php?menu=forbidden");
}

include_once "database.php";
include_once "fungsi.php";
include_once "proses_mining.php";
//include_once "fungsi_proses.php";
?>
<div class="content">
    <!--typography-page -->
    <div class="typo-w3">
        <div class="container">
            <h2 class="tittle">Klasifikasi</h2>
            <?php
            //object database class
            $db_object = new database();
            $sql = "SELECT * FROM data_soal";
            $query = $db_object->db_query($sql);
            $jumlah = $db_object->db_num_rows($query);

            $pesan_error = $pesan_success = "";
            if (isset($_GET['pesan_error'])) {
                $pesan_error = $_GET['pesan_error'];
            }
            if (isset($_GET['pesan_success'])) {
                $pesan_success = $_GET['pesan_success'];
            }

            if (isset($_POST['submit'])) {
                $success = true;
                $lihat_hasil = false;
                $pesan_gagal = $pesan_sukses = "";
                $idSiswa = $_SESSION['kepribadian_c45_id_siswa'];
                if ($idSiswa <= 0) {
                    $success = false;
                    $pesan_gagal = "Anda bukan siswa";
                }

                if (sudah_klasifikasi($db_object, $idSiswa)) {
                    $success = false;
                    $lihat_hasil = true;
                    $pesan_gagal = "Anda sudah melakukan klasifikasi";
                }

                if ($success) {
                    $val_in = $di_jawab_a = $di_jawab_b = $di_jawab_c = $di_jawab_d = array();
                    foreach ($_POST['soal'] as $key => $value) {
                        if (empty($value)) {
                            $success = false;
                            $pesan_gagal = "Ada yang belum diisi";
                            break;
                        }
                        //key = id_soal, value=jawaban A/B/C/D
                        $val_in[] = "(" . $_SESSION['kepribadian_c45_id'] . "," . $idSiswa .
                                "," . $key . ",'" . $value . "')";
                        if ($value == 'A') {
                            $di_jawab_a[] = $key;
                        }
                        if ($value == 'B') {
                            $di_jawab_b[] = $key;
                        }
                        if ($value == 'C') {
                            $di_jawab_c[] = $key;
                        }
                        if ($value == 'D') {
                            $di_jawab_d[] = $key;
                        }
                    }
                    //insert ke jawaban_kuisioner
                    if ($idSiswa > 0) {
                        $value_sql_to_in = implode(",", $val_in);
                        $sql_in_jawaban = "INSERT INTO jawaban_kuisioner
                                            (id_user, id_siswa, id_soal, jawaban)
                                            VALUES " . $value_sql_to_in;
                        $db_object->db_query($sql_in_jawaban);

                        //hitung naive bayes
                        $siswa = get_data_siswa($db_object, $idSiswa);
                        $jawaban_a = count($di_jawab_a);
                        $jawaban_b = count($di_jawab_b);
                        $jawaban_c = count($di_jawab_c);
                        $jawaban_d = count($di_jawab_d);

                        $hasil = klasifikasi($db_object, $siswa['jenis_kelamin'], $siswa['usia'], $siswa['sekolah'], 
                        $jawaban_a, $jawaban_b, $jawaban_c, $jawaban_d);

                        //simpan ke table hasil
                        $sql_in_hasil = "INSERT INTO data_hasil_klasifikasi
                                    (id_siswa, jenis_kelamin, usia, sekolah, jawaban_a, jawaban_b, jawaban_c, jawaban_d, 
                                    kelas_hasil, id_rule)
                                    VALUES
                                    ($idSiswa, '" . $siswa['jenis_kelamin'] . "', " . $siswa['usia'] . ", '" . $siswa['sekolah'] . "', "
                                . $jawaban_a . ", " . $jawaban_b . ", " . $jawaban_c . ", " . $jawaban_d . ", "
                                . "'" . $hasil['keputusan'] . "', '" . $hasil['id_rule'] . "')";
                        $db_object->db_query($sql_in_hasil);
                    }
                }


                if ($success) {
                    echo "<br>";
                    echo "<br>";
                    echo "<br>";
                    echo "<center>"
                    . "<h3 class='typoh2'>"
                            . "Klasifikasi karakteristik kepribadian Anda: " 
                    . "</h3>"
                            . "<h2 class='typoh2'>"
                            . $hasil['keputusan']
                            . "</h2>"
                    . "</center>";
                    
                } else {
                    display_error($pesan_gagal);
                    if ($lihat_hasil) {
                        $hasilSiswa = get_hasil_klasifikasi($db_object, $idSiswa);
                        //echo "<center><h3 class='typoh2'>Klasifikasi karakteristik kepribadian Anda: " . $hasilSiswa['kelas_hasil']."</h3></center>";
                        echo "<br>";
                        echo "<br>";
                        echo "<br>";
                        echo "<center>"
                        . "<h3 class='typoh2'>"
                                . "Klasifikasi karakteristik kepribadian Anda: " 
                        . "</h3>"
                                . "<h2 class='typoh2'>"
                                .$hasilSiswa['kelas_hasil']
                                . "</h2>"
                        . "</center>";
                    }
                }
            }


//                if (!empty($pesan_error)) {
//                    display_error($pesan_error);
//                }
//                if (!empty($pesan_success)) {
//                    display_success($pesan_success);
//                }

            if (!isset($_POST['submit'])) {
                if (sudah_klasifikasi($db_object, $_SESSION['kepribadian_c45_id_siswa'])) {
                    $hasilSiswa = get_hasil_klasifikasi($db_object, $_SESSION['kepribadian_c45_id_siswa']);
                    echo "<br>";
                    echo "<br>";
                    echo "<br>";
                    echo "<center>"
                    . "<h3 class='typoh2'>"
                            . "Anda sudah melakukan klasifikasi sebelumnya."
                            . "<br>"
                            . "<br>"
                            . "Klasifikasi karakteristik kepribadian Anda: " 
                    . "</h3>"
                            . "<h2 class='typoh2'>"
                            .$hasilSiswa['kelas_hasil']
                            . "</h2>"
                    . "</center>";
                    //echo "<br>";
                } 
                else {

                    if($jumlah <= 0){
                        echo "<br>";
                        echo "<br>";
                        echo "<br>";
                        echo "<center>"
                        . "<h3 class='typoh2'>"
                                . "Soal Kuisioner belum ada"
                        . "</h3>"
                        . "</center>";
                    }
                    else{
                    ?>
                    <!--UPLOAD EXCEL FORM-->
                    <form method="post" action="">
                    <?php
                    while ($row = $db_object->db_fetch_array($query)) {
                        ?>
                            <label>No. <?php echo $row['id']; ?></label>
                            <div class="radio">
                                <label>
                                    <input type="radio" name="soal[<?php echo $row['id']; ?>]" value="A" required=""/>
                                    <?php echo $row['pilihan_a']; ?>
                                </label>
                            </div>
                            <div class="radio">
                                <label>
                                    <input type="radio" name="soal[<?php echo $row['id']; ?>]" value="B" required=""/>
                                    <?php echo $row['pilihan_b']; ?>
                                </label>
                            </div>
                            <div class="radio">
                                <label>
                                    <input type="radio" name="soal[<?php echo $row['id']; ?>]" value="C" required=""/>
                                    <?php echo $row['pilihan_c']; ?>
                                </label>
                            </div>
                            <div class="radio">
                                <label>
                                    <input type="radio" name="soal[<?php echo $row['id']; ?>]" value="D" required=""/>
                                    <?php echo $row['pilihan_d']; ?>
                                </label>
                            </div>
                        <?php
                    }
                    ?>

                        <div class="form-group">
                            <input name="submit" type="submit" value="Submit" class="btn btn-success">
                        </div>
                    </form>
                        <?php
                    }
                    }
                }
                ?>
        </div>
    </div>
</div>


