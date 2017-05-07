<?php
//session_start();
if (!isset($_SESSION['kepribadian_c45_id'])) {
    header("location:index.php?menu=forbidden");
}

include_once "database.php";
include_once "fungsi.php";
include_once "import/excel_reader2.php";
?>
<div class="content-wrapper">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h4 class="page-head-line">Data Latih</h4>
            </div>
        </div>
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

        if (isset($_POST['submit'])) {
            // if(!$input_error){
            $data = new Spreadsheet_Excel_Reader($_FILES['file_data_latih']['tmp_name']);

            $baris = $data->rowcount($sheet_index = 0);
            $column = $data->colcount($sheet_index = 0);
            //import data excel dari baris kedua, karena baris pertama adalah nama kolom
            // $temp_date = $temp_produk = "";
            for ($i=2; $i<=$baris; $i++) {
//                for($c=1; $c<=$column; $c++){
//                    $value[$c] = $data->val($i, $c);
//                }
                $value = "(\"".$data->val($i, 2)."\", '".$data->val($i, 3)."', "
                        .$data->val($i, 4).", '".$data->val($i, 5)."', "
                        .$data->val($i, 6).", ".$data->val($i, 7).", "
                        .$data->val($i, 8).", ".$data->val($i, 9).", '".$data->val($i, 10)."')";
                $sql = "INSERT INTO data_latih "
                    . " (nama, jenis_kelamin, usia, sekolah, jawaban_a, jawaban_b, jawaban_c, jawaban_d, kelas_asli)"
                    . " VALUES ".$value;
                $result = $db_object->db_query($sql);
            }
            //$values = implode(",", $value);
            
            if($result){
                ?>
                <script> location.replace("?menu=data_latih&pesan_success=Data berhasil disimpan");</script>
                <?php
            }
            else{
                ?>
                <script> location.replace("?menu=data_latih&pesan_error=Data gagal disimpan");</script>
                <?php
            }
        }

        if (isset($_POST['delete'])) {
            $sql = "TRUNCATE data_latih";
            $db_object->db_query($sql);
            ?>
            <script> location.replace("?menu=data_latih&pesan_success=Data latih berhasil dihapus");</script>
            <?php
        }

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
                </form>

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
                } 
                else {
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
                ?>
            </div>
        </div>
    </div>
</div>

