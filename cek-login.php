<?php
session_start(); // harus ada di bagian paling atas kode
$path_to_root = "";
include $path_to_root . 'database.php';
include_once $path_to_root.'fungsi.php';
//object database class
$db = new database();

$user = strip_tags(trim($_POST['username'])); #echo $user;
$pass = strip_tags(trim($_POST['password'])); #echo $pass;

$sql = get_sql_login_admin_page($user, $pass);

$result = $db->db_query($sql);
$num_rows = $db->db_num_rows($result);

if ($num_rows > 0) {
    $rows = $db->db_fetch_array($result);
    
        unset($_POST); // hapus post form
        $_SESSION['kepribadian_c45_id'] = $rows['id_user']; // mengisi session
        $_SESSION['kepribadian_c45_nama'] = $rows['nama'];
        $_SESSION['kepribadian_c45_username'] = $rows['username'];
        $_SESSION['kepribadian_c45_level'] = $rows['level'];
        $_SESSION['kepribadian_c45_id_siswa'] = get_id_siswa_by_id_user($db, $rows['id_user']);

        $level_name = ($_SESSION['kepribadian_c45_level']==1)?"guru":"siswa";
        $_SESSION['kepribadian_c45_level_name'] = $level_name;
        $_SESSION['kepribadian_c45_key'] = sha1(date("Y-m-d H:i:s") . $rows['id']);
        $_SESSION['kepribadian_c45_last_login'] = date("d-m-Y H:i:s");
        header("location:index.php");
} else {
     header("location:login.php?login=1");
}


/**
 * query get login 
 * @param string $user username
 * @param string $pass password
 * @return string
 */
function get_sql_login_admin_page($user, $pass){
    $sql = "SELECT * FROM users"
        . " WHERE username = '" . $user . "' AND password = MD5('" . $pass . "')";
        echo $sql;
    return $sql;
}

?>
