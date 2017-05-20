<?php
//session_start();
if (!isset($_SESSION['kepribadian_naive_bayes_id'])) {
    header("location:index.php?menu=forbidden");
}

include_once "database.php";
include_once "fungsi.php";
//include_once "import/excel_reader2.php";
?>
<div class="content">
    <!--typography-page -->
    <div class="typo-w3">
        <div class="container">
            <h2 class="tittle">Data Siswa</h2>
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
                $sql1 = "INSERT INTO users "
                        . " (nama, username, password, level)"
                        . " VALUES (\"" . $_POST['nama'] . "\", \"" . $_POST['user_name'] . "\", md5(\"" . $_POST['user_name'] . "\"), 2)";
                $result1 = $db_object->db_query($sql1);
                $id_usernya = $db_object->db_insert_id();

                $sql = "INSERT INTO data_siswa "
                        . " (nama_siswa, jenis_kelamin, usia, sekolah, id_user)"
                        . " VALUES "
                        . " (\"" . $_POST['nama'] . "\", \"" . $_POST['jenis_kelamin'] . "\", \"" . $_POST['usia'] . "\","
                        . " \"" . $_POST['sekolah'] . "\", $id_usernya)";
                $result = $db_object->db_query($sql);


                if ($result && $result1) {
                    ?>
                    <script> location.replace("?menu=data_siswa&pesan_success=Data berhasil disimpan");</script>
                    <?php
                } else {
                    ?>
                    <script> location.replace("?menu=data_siswa&pesan_error=Data gagal disimpan");</script>
                    <?php
                }
            }

            if (isset($_GET['delete'])) {
                $id_delete = $_GET['delete'];
                $id_usere = get_id_user_siswa($db_object, $id_delete);
                $sql = "DELETE FROM data_siswa WHERE id=" . $id_delete;
                $db_object->db_query($sql);

                $sql = "DELETE FROM users WHERE id_user=" . $id_usere;
                $db_object->db_query($sql);
                ?>
                <script> location.replace("?menu=data_siswa&pesan_success=Data siswa berhasil dihapus");</script>
                <?php
            }

            $sql = "SELECT siswa.*, usr.username FROM data_siswa siswa, users usr
                WHERE siswa.`id_user` = usr.`id_user`";
            $query = $db_object->db_query($sql);
            $jumlah = $db_object->db_num_rows($query);
            ?>

            <div class="row">
                <div class="col-md-12">

                    <form method="post" action="">
                        <div class="form-group">
                            <div class="input-group">
                                <label>Nama</label>
                                <input name="nama" type="text" class="form-control" required="">
                            </div>
                            <div class="input-group">
                                <label>Username</label>
                                <input name="user_name" type="text" class="form-control" required="">
                            </div>
                            <div class="input-group">
                                <label>Jenis Kelamin</label>
                                <div class="radio">
                                    <label>
                                        <input name="jenis_kelamin" type="radio" value="L" required=""> Laki-laki
                                    </label>
                                </div>
                                <div class="radio">
                                    <label>
                                        <input name="jenis_kelamin" type="radio" value="P" required=""> Perempuan
                                    </label>
                                </div>
                            </div>
                            <div class="input-group">
                                <label>Usia</label>
                                <input name="usia" type="text" class="form-control" required="">
                            </div>
                            <div class="input-group">
                                <label>Sekolah</label>
                                <div class="radio">
                                    <label>
                                        <input name="sekolah" type="radio" value="Negeri" required=""> Negeri
                                    </label>
                                </div>
                                <div class="radio">
                                    <label>
                                        <input name="sekolah" type="radio" value="Swasta" required=""> Swasta
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <input name="submit" type="submit" value="Save" class="btn btn-success">
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
                                <th>Nama</th>
                                <th>Jenis Kelamin</th>
                                <th>Usia</th>
                                <th>Sekolah</th>
                                <th>Username</th>
                                <th></th>
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
        echo "<td>" . $row['username'] . "</td>";
        echo "<td><a href='?menu=data_siswa&delete=" . $row['id'] . "'>"
        . "<img src='images/icon/delete.gif'/></a></td>";
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
