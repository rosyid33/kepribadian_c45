<?php
//session_start();
if (!isset($_SESSION['kepribadian_c45_id'])) {
    header("location:index.php?menu=forbidden");
}

include_once "database.php";
include_once "fungsi.php";
include_once "import/excel_reader2.php";
include_once 'proses_mining.php';

//object database class
$db_object = new database();

if (isset($_POST['submit'])) {
    $data = new Spreadsheet_Excel_Reader($_FILES['file_data_latih']['tmp_name']);

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
            $sql = "INSERT INTO data_latih "
                    . " (nama, jenis_kelamin, usia, sekolah, jawaban_a, jawaban_b, jawaban_c, jawaban_d, kelas_asli)"
                    . " VALUES " . $value;
            $result = $db_object->db_query($sql);
        }
    }
    if ($result) {
        ?>
        <script> location.replace("?menu=mining&pesan_success=Data berhasil disimpan");</script>
        <?php
    } 
    else {
        ?>
        <script> location.replace("?menu=mining&pesan_error=Data gagal disimpan");</script>
        <?php
    }
}

if (isset($_POST['delete'])) {
    $sql = "TRUNCATE data_latih";
    $db_object->db_query($sql);
    ?>
    <script> location.replace("?menu=mining&pesan_success=Data latih berhasil dihapus");</script>
    <?php
}
?>
<div class="content">
    <!--typography-page -->
    <div class="typo-w3">
        <div class="container">
            <h2 class="tittle">Mining</h2>

            <?php
            $pesan_error = $pesan_success = "";
            if (isset($_GET['pesan_error'])) {
                $pesan_error = $_GET['pesan_error'];
            }
            if (isset($_GET['pesan_success'])) {
                $pesan_success = $_GET['pesan_success'];
            }

            if(!isset($_POST['proses_mining'])){//tidak muncul jika diklik proses mining
            $sql = "SELECT * FROM data_latih";
            $query = $db_object->db_query($sql);
            $jumlah = $db_object->db_num_rows($query);
            ?>
            <div class="row">
                <div class="col-md-12">
                    <!--UPLOAD EXCEL FORM-->
                    <form method="post" enctype="multipart/form-data" action="">
                        <div class="form-group">
                            <div class="input-group">
                                <label>Import data from excel</label>
                                <input name="file_data_latih" type="file" class="form-control">
                            </div>
                        </div>
                        <div class="form-group">
                            <input name="submit" type="submit" value="Upload Data" class="btn btn-success">
                            <button name="delete" type="submit"  class="btn btn-danger" onclick="">
                                <i class="fa fa-trash-o"></i> Delete All Data Latih
                            </button>
                        </div>
                        <div class="form-group">
                            <!--<input name="submit" type="submit" value="Upload Data" class="btn btn-success">-->
                            <button name="proses_mining" type="submit"  class="btn btn-default" onclick="">
                                <i class="fa fa-check"></i> Proses Mining
                            </button>
                        </div>
                    </form>

<?php
            }
if (!empty($pesan_error)) {
    display_error($pesan_error);
}
if (!empty($pesan_success)) {
    display_success($pesan_success);
}

if(!isset($_POST['proses_mining'])){//tidak muncul jika diklik proses mining
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
                        
                        if(isset($_POST['proses_mining'])){
                            $awal = microtime(true);
                            
                            $db_object->db_query("TRUNCATE t_keputusan");
                            pembentukan_tree($db_object, "","");
                            echo "<br><h3><center>---PROSES SELESAI---</center></h3>";
                            //echo "<center><a href='index.php?menu=pohon_keputusan' accesskey='5' "
                            //. "title='pohon keputusan'>Lihat pohon keputusan yang terbentuk</a></center>";

                            $akhir = microtime(true);
                            $lama = $akhir - $awal;
                            //echo "<br>Lama eksekusi script adalah: ".$lama." detik";

                        }
                        ?>
                </div>
            </div>
        </div>
    </div>
    <!-- //typography-page -->

</div>

