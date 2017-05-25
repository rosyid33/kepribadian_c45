<?php
//session_start();
if (!isset($_SESSION['kepribadian_c45_id'])) {
    header("location:index.php?menu=forbidden");
}

include_once "database.php";
include_once "fungsi.php";
?>
<div class="content">
    <!--typography-page -->
    <div class="typo-w3">
        <div class="container">
            <h2 class="tittle">Hasil Klasifikasi</h2>

            <?php
            //object database class
            $db_object = new database();

            $pesan_error = $pesan_success = "";
            if (isset($_GET['pesan_error'])) {
                $pesan_error = $_GET['pesan_error'];
            }
            if (isset($_GET['pesan_success'])) {
                $pesan_success = $_GET['pesan_success'];
            }

            if (isset($_POST['delete'])) {
                $sql = "TRUNCATE data_hasil_klasifikasi";
                $db_object->db_query($sql);
                ?>
                <script> location.replace("?menu=hasil_klasifikasi&pesan_success=Data hasil berhasil dihapus");</script>
                <?php
            }

            $sql = "SELECT siswa.`nama_siswa`, hasil.* 
            FROM data_hasil_klasifikasi hasil,
            data_siswa siswa
            WHERE siswa.`id` = hasil.`id_siswa`";
            $query = $db_object->db_query($sql);
            $jumlah = $db_object->db_num_rows($query);
            ?>

            <div class="row">
                <div class="col-md-12">
                    <?php
                    if (!empty($pesan_error)) {
                        display_error($pesan_error);
                    }
                    if (!empty($pesan_success)) {
                        display_success($pesan_success);
                    }


                    echo "Jumlah data: " . $jumlah . "<br>";
                    if ($jumlah == 0) {
                        echo "Data kosong...";
                    } else {
                        ?>
                        <table class='table table-bordered table-striped  table-hover'>
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>Jenis Kelamin</th>
                                <th>Usia</th>
                                <th>Sekolah</th>
                                <th>Jawaban A</th>
                                <th>Jawaban B</th>
                                <th>Jawaban C</th>
                                <th>Jawaban D</th>
                                <th>Kelas Hasil</th>
                                <th>Id rule</th>
                            </tr>
                            <?php
                            $no = 1;
                            while ($row = $db_object->db_fetch_array($query)) {
                                echo "<tr>";
                                echo "<td>" . $no . "</td>";
                                echo "<td>" . $row['nama_siswa'] . "</td>";
                                echo "<td>" . $row['jenis_kelamin'] . "</td>";
                                echo "<td>" . $row['usia'] . "</td>";
                                echo "<td>" . $row['sekolah'] . "</td>";
                                echo "<td>" . $row['jawaban_a'] . "</td>";
                                echo "<td>" . $row['jawaban_b'] . "</td>";
                                echo "<td>" . $row['jawaban_c'] . "</td>";
                                echo "<td>" . $row['jawaban_d'] . "</td>";
                                echo "<td>" . $row['kelas_hasil'] . "</td>";
                                echo "<td>" . $row['id_rule'] . "</td>";
                                echo "</tr>";
                                $no++;
                            }
                            ?>
                        </table>
                            <?php
                        }
                        ?>
                </div>
            </div>
        </div>
    </div>
</div>