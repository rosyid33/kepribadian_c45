<?php
error_reporting(E_ALL ^ (E_NOTICE | E_WARNING | E_DEPRECATED));
include_once "database.php";
include_once "import/excel_reader2.php";
include_once "fungsi.php";
//object database class
$db_object = new database();
?>
<div class="content">
    <!--typography-page -->
    <div class="typo-w3">
        <div class="container">
            <h2 class="tittle">Uji Pohon Keputusan</h2>

            <?php
            if(isset($_POST['upload'])){
                $data = new Spreadsheet_Excel_Reader($_FILES['file_data']['tmp_name']);

                $baris = $data->rowcount($sheet_index = 0);
                $column = $data->colcount($sheet_index = 0);
                //import data excel dari baris kedua, karena baris pertama adalah nama kolom
                // $temp_date = $temp_produk = "";
                for ($i = 2; $i <= $baris; $i++) {
                    if(!empty($data->val($i, 2))){
                        $value = "(\"" . $data->val($i, 2) . "\", '" . $data->val($i, 3) . "', "
                                . $data->val($i, 4) . ", '" . $data->val($i, 5) . "', "
                                . $data->val($i, 6) . ", " . $data->val($i, 7) . ", "
                                . $data->val($i, 8) . ", " . $data->val($i, 9) . ", '" . $data->val($i, 10) . "')";
                        $sql = "INSERT INTO data_uji "
                                . " (nama, jenis_kelamin, usia, sekolah, jawaban_a, jawaban_b, jawaban_c, jawaban_d, kelas_asli)"
                                . " VALUES " . $value;
                        $result = $db_object->db_query($sql);
                    }
                }
                if ($result) {
                    ?>
                    <script> location.replace("?menu=uji_rule&pesan_success=Data berhasil disimpan");</script>
                    <?php
                } 
                else {
                    ?>
                    <script> location.replace("?menu=uji_rule&pesan_error=Data gagal disimpan");</script>
                    <?php
                }
            }
            
            if (isset($_GET['act'])) {
                $action = $_GET['act'];
                //delete semua data
                if ($action == 'delete_all') {
                    $db_object->db_query("TRUNCATE data_uji");
                    //header('location:?menu=uji_rule');
                    ?>
                    <script> location.replace("?menu=uji_rule&pesan_success=Data uji berhasil dihapus");</script>
                    <?php
                }
            } 
            else {
                if (isset($_POST['submit'])) {
                    include "hitung_akurasi.php";
                } 
                else {
                    $query = $db_object->db_query("SELECT * FROM data_uji order by(id)");
                    $jumlah = $db_object->db_num_rows($query);
                    echo "<br><br>";
                    ?>

                    <form method="post" enctype="multipart/form-data" action="">
                        <div class="form-group">
                            <div class="input-group">
                                <label>Import data from excel</label>
                                <input name="file_data" type="file" class="form-control">
                            </div>
                        </div>
                        <div class="form-group">
                            <input name="upload" type="submit" value="Upload Data" class="btn btn-success">
                            <a href="?menu=uji_rule&act=delete_all" class="btn btn-danger"
                               onClick="return confirm('Anda yakin akan hapus semua data?')">
                                <i class="fa fa-trash"></i> Delete All Data uji
                            </a>
                        </div>
                    </form>
                    <?php
                    if ($jumlah == 0) {
                        echo "<center><h3>Data uji masih kosong...</h3></center>";
                    } 
                    else {
            ?>
                        <center>
                            <form method="POST" action=''>
                                <div class="form-group">
                                    <div class="input-group">
                                        <input type="submit" name="submit" value="HitungAkurasi" class="btn btn-success">
                                    </div>
                                </div>
                            </form>
                        </center>
                        Jumlah data uji: <?php echo $jumlah; ?>

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
                                <th>Kelas Asli</th>
                            </tr>
            <?php
    $no = 1;
    while ($row = $db_object->db_fetch_array($query)) {
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
        echo "</tr>";
        $no++;
    }
    ?>
            
                        </table>
                            <?php
                        }
                    }
                }
                ?>
        </div>
    </div>
</div>