<?php 
$menu = '';
if(isset($_GET['menu'])){
    $menu = $_GET['menu'];
}

?>
<!--header-->
<div class="header">
    <div class="heder-bottom">
        <div class="container">
            <div class="logo-nav">
                <div class="logo-nav-left">
                    <h1><a href="index.php">Klasifikasi Karakteristik Kepribadian Manusia 
                            <span>(Metode Decision Tree C4.5)</span></a></h1>
                </div>
                <div class="logo-nav-left1 header-right2">
                    <nav class="navbar navbar-default">
                        <!-- Brand and toggle get grouped for better mobile display -->
                        <div class="collapse navbar-collapse" id="bs-megadropdown-tabs">
                            <ul class="nav navbar-nav">
                                <li class="active"><a href="index.php" class="act">Home</a></li>
                                <?php
                                if (empty($_SESSION['kepribadian_c45_id'])) {
                                ?>
                                    <li><a href="login.php">Login</a></li>
                                <?php
                                }
                                else{
                                    if(($_SESSION['kepribadian_c45_level'])==2){
                                    ?>
                                    <li class="active"><a href="index.php?menu=klasifikasi" class="act">Klasifikasi</a></li>
                                    <?php
                                    }
                                    else{
                                    ?>
                                        <li class="active"><a href="index.php?menu=data_siswa" class="act">Data siswa</a></li>
                                        <li class="active"><a href="index.php?menu=data_soal" class="act">Data Soal</a></li>
                                        <li class="dropdown">
                                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">C4.5<b class="caret"></b></a>
                                            <ul class="dropdown-menu ">
                                                <ul class="multi-column-dropdown">
                                                    <li><a href="index.php?menu=mining">Mining</a></li>
                                                    <li><a href="index.php?menu=pohon_keputusan">Pohon Keputusan</a></li>
                                                    
                                                </ul>
                                            </ul>
                                        </li>
                                        <li class="active"><a href="index.php?menu=hasil_klasifikasi" class="act">Hasil</a></li>
                                    <?php
                                    }
                                    ?>
                                    <li><a href="logout.php">Logout</a></li>
                                <?php
                                }
                                ?>
                            </ul>
                        </div>
                    </nav>
                </div>
                <div class="clearfix"> </div>
            </div>
        </div>
    </div>
</div>
<!--header-->