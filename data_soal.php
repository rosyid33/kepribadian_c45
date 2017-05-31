<?php
//session_start();
if (!isset($_SESSION['kepribadian_c45_id'])) {
    header("location:index.php?menu=forbidden");
}

include_once "database.php";
include_once "fungsi.php";
include_once "import/excel_reader2.php";
?>
<div class="content">
    <!--typography-page -->
    <div class="typo-w3">
        <div class="container">
            <h2 class="tittle">Data Soal Kuisioner</h2>
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
                $data = new Spreadsheet_Excel_Reader($_FILES['file_data_soal']['tmp_name']);

                $baris = $data->rowcount($sheet_index = 0);
                $column = $data->colcount($sheet_index = 0);
                //import data excel dari baris kedua, karena baris pertama adalah nama kolom
                // $temp_date = $temp_produk = "";
                for ($i = 2; $i <= $baris; $i++) {
                    $value = "(\"" . $data->val($i, 2) . "\", \"" . $data->val($i, 3) . "\", ".
                            "\"". $data->val($i, 4) . "\", \"" . $data->val($i, 5) . "\")";
                    $sql = "INSERT INTO data_soal "
                            . " (pilihan_a, pilihan_b, pilihan_c, pilihan_d)"
                            . " VALUES " . $value;
                    $result = $db_object->db_query($sql);
                }
                if ($result) {
                    ?>
                    <script> location.replace("?menu=data_soal&pesan_success=Data berhasil disimpan");</script>
                    <?php
                } 
                else {
                    ?>
                     <script> location.replace("?menu=data_soal&pesan_error=Data gagal disimpan");</script> 
                    <?php
                }
            }

            if (isset($_POST['delete'])) {
                $sql = "TRUNCATE data_soal";
                $db_object->db_query($sql);
                ?>
                <script> location.replace("?menu=data_soal&pesan_success=Data soal berhasil dihapus");</script>
                <?php
            }

            $sql = "SELECT soal.* FROM data_soal soal ORDER BY id";
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
                                <input name="file_data_soal" type="file" class="form-control">
                            </div>
                        </div>
                        <div class="form-group">
                            <input name="submit" type="submit" value="Upload Data" class="btn btn-success">
                            <button name="delete" type="submit"  class="btn btn-danger" onclick="">
                                <i class="fa fa-trash-o"></i> Delete All Data Soal
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
                    } else {
                        ?>
                        <table class='table table-bordered table-striped  table-hover'>
                            <tr>
                                <th>No</th>
                                <th>Pilihan A</th>
                                <th>Pilihan B</th>
                                <th>Pilihan C</th>
                                <th>Pilihan D</th>
                            </tr>
    <?php
    $no = 1;
    while ($row = $db_object->db_fetch_array($query)) {
        echo "<tr>";
        echo "<td>" . $no . "</td>";
        echo "<td>" . $row['pilihan_a'] . "</td>";
        echo "<td>" . $row['pilihan_b'] . "</td>";
        echo "<td>" . $row['pilihan_c'] . "</td>";
        echo "<td>" . $row['pilihan_d'] . "</td>";
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
