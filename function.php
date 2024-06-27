<?php
session_start();

$koneksi = mysqli_connect('localhost','root','','kasir');

if(isset($_POST['login'])){
    //initial variable
    $username = $_POST['username'];
    $password = $_POST['password'];

    $check = mysqli_query($koneksi, "SELECT * FROM user WHERE username='$username' AND password='$password' ");
    $hitung = mysqli_num_rows($check);
    
    if($hitung>0){
        //jika datanya ada, dan ditemukan
        //berhasil login
        $_SESSION['login'] = true;
        header('location:index.php');
    } else{
        //datanya g ada
        //gagal login
        echo '
        <script>
        alert("Username atau Password salah")
        window.location.href="login.php"
        </script>';
    }
}

if (isset($_POST['tambahproduk'])){
    //deskripsi initial variable
    $nama_produk = $_POST['nama_produk'];
    $deskripsi = $_POST['deskripsi'];
    $harga = $_POST['harga'];
    $stock = $_POST['stock'];

    $insert_produk = mysqli_query($koneksi, "INSERT INTO produk (nama_produk, deskripsi, harga, stock)
    VALUES ('$nama_produk','$deskripsi','$harga','$stock')");

    if ($insert_produk) {
        header('location:stock.php');
    } else{
        echo '
        <script>
        alert("Gagal tambah produk")
        window.location.href="stock.php"
        </script>';
    }

}

if (isset($_POST['tambahpelanggan'])){
    //deskripsi initial variable
    $nama_pelanggan = $_POST['nama_pelanggan'];
    $notelp = $_POST['notelp'];
    $alamat = $_POST['alamat'];

    $insert_pelanggan = mysqli_query($koneksi, "INSERT INTO pelanggan (nama_pelanggan, notelp, alamat)
    VALUES ('$nama_pelanggan','$notelp','$alamat')");

    if ($insert_pelanggan) {
        header('location:pelanggan.php');
    } else{
        echo '
        <script>
        alert("Gagal tambah pelanggan")
        window.location.href="pelanggan.php"
        </script>';
    }
}

if (isset($_POST['tambahpesanan'])){
    //deskripsi initial variable
    $id_pelanggan = $_POST['id_pelanggan'];

    $insert_pesanan = mysqli_query($koneksi, "INSERT INTO pesanan (id_pelanggan) 
    VALUES ('$id_pelanggan')");

    if ($insert_pesanan) {
        header('location:index.php');
    } else{
        echo '
        <script>
        alert("Gagal tambah pesanan")
        window.location.href="index.php"
        </script>';
    }

}

if (isset($_POST['addproduk'])){
    //deskripsi initial variable
    $id_produk = $_POST['id_produk'];
    $idp = $_POST['idp'];
    $qty = $_POST['qty'];

    //hitung stock sekarang ada berapa
    $hitung1 = mysqli_query($koneksi, "SELECT * FROM produk WHERE id_produk='$id_produk'");
    $hitung2 = mysqli_fetch_array($hitung1);
    $stocksekarang = $hitung2['stock']; //stock barang saat ini

    if($stocksekarang>=$qty){
        //kurangin stocknya dengan jumlah yang akan dikeluarkan
        $selisih = $stocksekarang - $qty;


        //stocknya cukup
        $insert = mysqli_query($koneksi, "INSERT INTO detail_pesanan (id_pesanan, id_produk, qty) 
        VALUES ('$idp','$id_produk','$qty')"); 
        $update = mysqli_query($koneksi, "UPDATE produk SET stock='$selisih' WHERE id_produk='$id_produk'");

        if ($insert && $update) {
             header('location:view.php?idp='.$idp);
        } else{
             echo '
             <script>
                alert("Gagal tambah produk")
                window.location.href="view.php"'.$idp.'
            </script>';
                }
    }else
    //stock tidak cukup
            echo '
             <script>
                alert("Stock tidak cukup")
                window.location.href="view.php"'.$idp.'
            </script>';

}

//tambah barang masuk
if(isset($_POST['barangmasuk'])){
    $id_produk = $_POST['id_produk'];
    $qty = $_POST['qty'];

    $insertbar = mysqli_query($koneksi, "INSERT INTO masuk (id_produk,qty)
    VALUES ('$id_produk',$qty)");

    if($insertbar){
        header('location:masuk.php');
    }else{
        echo '
        <script>
        alert("Gagal")
        window.location.href="masuk.php"
        </script>';
    }
}

//hapus produk pesanan
if(isset($_POST['hapusprodukpesanan'])){
    $iddetail = $_POST['iddetail']; //detail pesanan
    $idpr = $_POST['idpr'];
    $idp = $_POST['idp'];

    //cek qty sekarang
    $cek1 = mysqli_query($koneksi, "SELECT * FROM detail_pesanan WHERE id_detailpesanan='$iddetail'");
    $cek2 = mysqli_fetch_array($cek1);
    $qtysekarang = $cek2['qty'];

    //cek stok sekarang
    $cek3 = mysqli_query($koneksi, "SELECT * FROM produk WHERE id_produk='$idpr'");
    $cek4 = mysqli_fetch_array($cek3);
    $cekstocksekarang = $cek4['stock'];

    $hitung = $cekstocksekarang - $qtysekarang; 

    if ($hitung >= 0) {
        $update = mysqli_query($koneksi, "UPDATE produk SET stock='$hitung' WHERE id_produk='$idpr'");
    }

    $hapus = mysqli_query($koneksi, "DELETE FROM detail_pesanan WHERE id_produk='$idpr' AND id_detailpesanan='$iddetail'");

    if ($hapus) {
        if (!$update && $hitung < 0) {
            echo '<script>alert("Gagal menghapus produk dari pesanan. Stok tidak cukup.");</script>';
        } else {
            header('location:view.php?idp='.$idp);
        }
    } else {
        echo '<script>alert("Gagal menghapus produk dari pesanan.");</script>';
    }
}