<?php  
	session_start();
	//set jam
	date_default_timezone_set('Asia/Jakarta');	
	class DataBase{
		private $host = "localhost";
		private $user = "root";
		private $pass = "";
		private $db = "inventory_barang";
		public $koneksi;
		
		public function __construct(){
		$this->koneksi = mysqli_connect($this->host, $this->user, $this->pass, $this->db);
	}
		
		
		public function sambungkan(){
			$koneksi = mysqli_connect($this->host,$this->user,$this->pass, $this->db);
			return $koneksi;
//			mysqli_connect($this->host,$this->user,$this->pass);
//			mysqli_select_db($this->db);
		}
	}
	//membuat class admin
	class Admin extends DataBase{

		//method insert data admin
		public function simpan_admin($email,$pass,$nama,$gambar){
			$namafile = $gambar['name'];
			//lokasi sementara
			$lokasifile = $gambar['tmp_name'];
			//upload
			move_uploaded_file($lokasifile, "gambar_admin/$namafile");
			//insert
			mysqli_query($this->koneksi,"INSERT INTO admin(email,password,nama,gambar) VALUES('$email','$pass','$nama','$namafile')");
		}
		public function tampil_admin(){
			$qry = mysqli_query($this->koneksi,"SELECT * FROM admin");
			while ($pecah = mysqli_fetch_array($qry)) {
				//array
				$data[] = $pecah;
			}
			return $data;
		}
		public function ambil_admin($id){
			$qry = mysqli_query($this->koneksi,"SELECT * FROM admin WHERE kd_admin= '$id'");
			$pecah = mysqli_fetch_assoc($qry);
			return $pecah;
		}
		public function ubah_admin($email,$pass,$nama,$gambar,$id){
			$namafile = $gambar['name'];
			$lokasifile = $gambar['tmp_name'];
			//mengambil nama gambar sebelumnya untuk di hapus, akan di hapus
			//jika form gambar tidak kosong
			$ambil = $this->ambil_admin($id);
			$gambarhapus = $ambil['gambar'];
			if (!empty($lokasifile)) {
				//hapus gambar sebelumnya
				unlink("gambar_admin/$gambarhapus");
				//upload gambar baru
				move_uploaded_file($lokasifile, "gambar_admin/$namafile");
				//update
				mysqli_query($this->koneksi,"UPDATE admin 
					SET email = '$email', password='$pass', nama='$nama', gambar='$namafile' WHERE kd_admin='$id'");
			}
			else{
				//update tanpa upload gambar
				mysqli_query($this->koneksi,"UPDATE admin 
					SET email = '$email', password='$pass', nama='$nama' WHERE kd_admin='$id'");
			}
		}
		public function hapus_admin($hapus){
			//ambil nama gambar yang akan di hapus pada folder gambar
			$gbr = $this->ambil_admin($hapus);
			$namagbr = $gbr['gambar'];
			//hapus
			unlink("gambar_admin/$namagbr");
			mysqli_query($this->koneksi,"DELETE FROM admin WHERE kd_admin= '$hapus'");
		}
		public function login_admin($email,$pass){
			// mencocokan data di db dengan username dan pass yang di inputkan
			//var_dump($this->koneksi);
			//exit;
			$cek = mysqli_query($this->koneksi,"SELECT * FROM admin WHERE email='$email' AND password='$pass'");
			//mengambil data orang yang login dan cocok
			$data = mysqli_fetch_assoc($cek);
			// hitung data yang cocok
			$cocokan = mysqli_num_rows($cek);
			//jika akun yang cocok lebih besar dari 0 maka bisa login
			if ($cocokan > 0) {
				//bisa login
				$_SESSION['login_admin']['id'] = $data['kd_admin'];
				$_SESSION['login_admin']['email'] = $data['email'];
				$_SESSION['login_admin']['nama'] = $data['nama'];
				$_SESSION['login_admin']['gambar'] = $data['gambar'];

				return true;
			}// selain itu (akun yang cocok tdk lebih dari 0) maka ggl
			else{
				return false;
			}
		}
	}
	class Barang extends DataBase{
		public function tampil_barang(){
			$qry = mysqli_query($this->koneksi,"SELECT * FROM barang ORDER BY nama_barang ASC");
			while ($pecah = mysqli_fetch_array($qry)) {
				$data[] = $pecah;
			}
			return $data;
		}
		public function simpan_barang($kdbarang,$nama,$satuan,$hargaj,$hargab,$stok){
			mysqli_query($this->koneksi,"INSERT INTO barang(kd_barang,nama_barang,satuan,harga_jual,harga_beli,stok) 
				VALUES('$kdbarang','$nama','$satuan','$hargaj','$hargab','$stok')");
		}
		public function ubah_barang($nama,$satuan,$hargaj,$hargab,$stok,$kd){
			mysqli_query($this->koneksi,"UPDATE barang SET nama_barang='$nama', satuan='$satuan', harga_jual='$hargaj',harga_beli='$hargab',stok='$stok' WHERE kd_barang = '$kd' ");
		}
		public function ambil_barang($id){
			$qry = mysqli_query($this->koneksi,"SELECT * FROM barang WHERE kd_barang = '$id'");
			$pecah = mysqli_fetch_assoc($qry);

			return $pecah;
		}
		public function hapus_barang($kd){
			mysqli_query($this->koneksi,"DELETE FROM barang WHERE kd_barang = '$kd'");
		}
		public function simpan_barang_gudang($kdbarang,$hargaj,$kdbl){
			$dat = $this->ambil_barangpem($kdbl);
			$nama = $dat['nama_barang_beli'];
			$satuan = $dat['satuan'];
			$hargab = $dat['harga_beli'];
			$stok = $dat['item'];
			mysqli_query($this->koneksi,"INSERT INTO barang(kd_barang,nama_barang,satuan,harga_jual,harga_beli,stok) 
				VALUES('$kdbarang','$nama','$satuan','$hargaj','$hargab','$stok')");
			//update data barang pembelian dengan setatus 1
			mysqli_query($this->koneksi,"UPDATE barang_pembelian SET status='1' WHERE kd_barang_beli ='$kdbl'");
		}
		public function ambil_barangpem($kd){
			$qry = mysqli_query($this->koneksi,"SELECT * FROM barang_pembelian WHERE kd_barang_beli = '$kd'");
			$pecah = mysqli_fetch_assoc($qry);
			return $pecah;
		}
	}
	class Supplier extends DataBase{
		public function tampil_supplier(){
			$qry = mysqli_query($this->koneksi,"SELECT * FROM supplier");
			while ($pecah = mysqli_fetch_array($qry)) {
				$data[] = $pecah;
			}
			return $data;
		}
		public function simpan_supplier($nama,$alamat){
			mysqli_query($this->koneksi,"INSERT INTO supplier(nama_supplier,alamat) VALUES('$nama','$alamat')");
		}
		public function ubah_supplier($nama,$alamat,$id){
			mysqli_query($this->koneksi,"UPDATE supplier SET nama_supplier='$nama', alamat='$alamat' WHERE kd_supplier = '$id'");
		}
		public function hapus_supplier($id){
			mysqli_query($this->koneksi,"DELETE FROM supplier WHERE kd_supplier= '$id'");
		}
		public function ambil_supplier($id){
			$qry = mysqli_query($this->koneksi,"SELECT * FROM supplier WHERE kd_supplier= '$id'");
			$pecah = mysqli_fetch_assoc($qry);
			return $pecah;
		}
	}
	class Pembelian extends DataBase{
		public function kode_otomatis(){
			$qry = mysqli_query($this->koneksi,"SELECT MAX(kd_pembelian) AS kode FROM pembelian");
			$pecah = mysqli_fetch_array($qry);
			$kode = substr($pecah['kode'], 3,5);
			$jum = $kode + 1;
			if ($jum < 10) {
				$id = "PEM0000".$jum;
			}
			else if($jum >= 10 && $jum < 100){
				$id = "PEM000".$jum;
			}
			else if($jum >= 100 && $jum < 1000){
				$id = "PEM00".$jum;
			}
			else{
				$id = "PEM0".$jum;
			}
			return $id;
		}
		public function tampil_pembelian(){
			$qry = mysqli_query($this->koneksi,"SELECT * FROM pembelian p JOIN supplier s ON p.kd_supplier=s.kd_supplier ORDER BY kd_pembelian DESC");
			while ($pecah = mysqli_fetch_array($qry)) {
				$data[]=$pecah;
			}
			$cek = mysqli_num_rows($qry);
			if ($cek > 0) {
				return $data;
			}else{
				error_reporting(0);
			}
		}
		public function hitung_item_pembelian($kdpembelian){
			$qry = mysqli_query($this->koneksi,"SELECT count(*) as jumlah FROM d_pembelian WHERE kd_pembelian = '$kdpembelian'");
			$pecah = mysqli_fetch_array($qry);

			return $pecah;
		}
		//sementara
		public function tambah_barang_sementara($kode,$nama,$satuan,$hargab,$item){
			$tot = $item * $hargab;
			mysqli_query($this->koneksi,"INSERT INTO barangp_sementara(kd_pembelian,nama_barangp, satuan,harga_barangp,item,total) 
				VALUES('$kode','$nama','$satuan','$hargab','$item','$tot')");
		}
		public function tampil_barang_sementara($kode){
			$qry = mysqli_query($this->koneksi,"SELECT * FROM barangp_sementara WHERE kd_pembelian = '$kode'");
			while ($pecah = mysqli_fetch_array($qry)) {
				$data[]=$pecah;
			}
			$hitung = mysqli_num_rows($qry);
			if ($hitung > 0) {
				return $data;
			}
			else{
				error_reporting(0);
			}
		}
		public function hitung_total_sementara($kode){
			$qry = mysqli_query($this->koneksi,"SELECT sum(total) as jumlah FROM barangp_sementara WHERE kd_pembelian = '$kode'");
			$pecah = mysqli_fetch_array($qry);
			$cek = $this->cek_data_barangp($kode);
			if ($cek === true) {
				$subtotal = $pecah['jumlah'];
			}
			else{
				$subtotal = 0;
			}
			return $subtotal;
		}
		public function hapus_barang_sementara($hapus){
			mysqli_query($this->koneksi,"DELETE FROM barangp_sementara WHERE id_barangp ='$hapus'");
		}
		public function cek_data_barangp($kode){
			$qry = mysqli_query($this->koneksi,"SELECT * FROM barangp_sementara WHERE kd_pembelian = '$kode'");
			$hitung = mysqli_num_rows($qry);
			if ($hitung >=1) {
				return true;
			}
			else{
				return false;
			}
		}
		//end sementara
		public function simpan_pembelian($kdpembelian,$tglpembelian,$supplier,$totalpem){
			//insert pembelian
			$kdadmin = $_SESSION['login_admin']['id'];
			mysqli_query($this->koneksi,"INSERT INTO pembelian(kd_pembelian,tgl_pembelian,kd_admin,kd_supplier,total_pembelian) 
				VALUES('$kdpembelian','$tglpembelian','$kdadmin','$supplier','$totalpem')");
			
			//insert data barang
			mysqli_query($this->koneksi,"INSERT INTO barang_pembelian(kd_pembelian,nama_barang_beli,satuan,harga_beli,item,total) 
				SELECT kd_pembelian,nama_barangp,satuan,harga_barangp,item,total FROM barangp_sementara");
			//insert detail pembelian
			mysqli_query($this->koneksi,"INSERT INTO d_pembelian(kd_pembelian,kd_barang_beli,jumlah,subtotal) 
				SELECT kd_pembelian, kd_barang_beli,item,total FROM barang_pembelian WHERE kd_pembelian='$kdpembelian'");
			//hapus data barang pembelian sementara
			mysqli_query($this->koneksi,"DELETE FROM barangp_sementara WHERE kd_pembelian='$kdpembelian'");
		}
		public function tampil_barang_pembelian(){
			$qry = mysqli_query($this->koneksi,"SELECT * FROM barang_pembelian WHERE status = '0'");
			while ($pecah = mysqli_fetch_array($qry)) {
				$data[]=$pecah;
			}
			$hitung = mysqli_num_rows($qry);
			if ($hitung > 0) {
				return $data;
			}
			else{
				error_reporting(0);
			}	
		}
		public function ambil_kdpem(){
			$qry = mysqli_query($this->koneksi,"SELECT * FROM pembelian ORDER BY kd_pembelian DESC LIMIT 1");
			$pecah = mysqli_fetch_assoc($qry);
			return $pecah;
		}
		public function cek_hapuspembelian($kd){
			$qry = mysqli_query($this->koneksi,"SELECT * FROM barang_pembelian WHERE kd_pembelian = '$kd' AND status ='0'");
			$hitung = mysqli_num_rows($qry);
			if ($hitung > 0) {
				return false;
			}
			else{
				return true;
			}
		}
		public function hitung_jumlah_pembelian($kd){
			$qry = mysqli_query($this->koneksi,"SELECT SUM(subtotal) as total FROM d_pembelian WHERE kd_pembelian = '$kd'");
			$pecah = mysqli_fetch_assoc($qry);
			return $pecah['total'];
		}
		public function hapus_pembelian($kdpembelian){
			mysqli_query($this->koneksi,"DELETE FROM pembelian WHERE kd_pembelian='$kdpembelian'");
			mysqli_query($this->koneksi,"DELETE FROM barang_pembelian WHERE kd_pembelian = '$kdpembelian' AND status='1'");
		}
	}
	class Penjualan extends Barang {
		public function kode_otomatis(){
			$qry = mysqli_query($this->koneksi,"SELECT MAX(kd_penjualan) AS kode FROM penjualan");
			$pecah = mysqli_fetch_array($qry);
			$kode = substr($pecah['kode'], 3,5);
			$jum = $kode + 1;
			if ($jum < 10) {
				$id = "PEN0000".$jum;
			}
			else if($jum >= 10 && $jum < 100){
				$id = "PEN000".$jum;
			}
			else if($jum >= 100 && $jum < 1000){
				$id = "PEN00".$jum;
			}
			else{
				$id = "PEN0".$jum;
			}
			return $id;
		}
		public function tampil_barang_penjualan(){
			$qry = mysqli_query($this->koneksi,"SELECT * FROM barang WHERE stok > 0 ORDER BY nama_barang ASC");
			while ($pecah = mysqli_fetch_array($qry)) {
				$data[] = $pecah;
			}
			return $data;
		}
		public function tampil_penjualan(){
			$qry = mysqli_query($this->koneksi,"SELECT * FROM penjualan ORDER BY kd_penjualan DESC");
			while ($pecah = mysqli_fetch_array($qry)) {
				$data[]=$pecah;
			}
			$hitung = mysqli_num_rows($qry);
			if ($hitung > 0) {
				return $data;
			}
			else{
				error_reporting(0);
			}
		}
		public function cek_data_barangp($kode){
			$qry = mysqli_query($this->koneksi,"SELECT * FROM penjualan_sementara WHERE kd_penjualan = '$kode'");
			$hitung = mysqli_num_rows($qry);
			if ($hitung >=1) {
				return true;
			}
			else{
				return false;
			}
		}
		public function tampil_barang_sementara($kode){
			$qry = mysqli_query($this->koneksi,"SELECT * FROM penjualan_sementara WHERE kd_penjualan = '$kode'");
			while ($pecah = mysqli_fetch_array($qry)) {
				$data[]=$pecah;
			}
			$hitung = mysqli_num_rows($qry);
			if ($hitung > 0) {
				return $data;
			}
			else{
				error_reporting(0);
			}
		}
		public function tambah_penjualan_sementara($kdpen, $kdbarang, $item){
			$bar = $this->ambil_barang($kdbarang);
			$namabr = $bar['nama_barang'];
			$satuan = $bar['satuan'];
			$harga = $bar['harga_jual'];
			$total = $harga * $item;
			mysqli_query($this->koneksi,"INSERT INTO penjualan_sementara(kd_penjualan, kd_barang, nama_barang, satuan, harga, item, total) 
				VALUES('$kdpen', '$kdbarang','$namabr','$satuan','$harga','$item','$total')");
			// update barang
			$kurang = $bar['stok'] - $item;
			mysqli_query($this->koneksi,"UPDATE barang SET stok = '$kurang' WHERE kd_barang = '$kdbarang'");
		}
		public function cek_item($kdbarang,$item){
			$data = $this->ambil_barang($kdbarang);
			$jumitem = $data['stok'];
			if ($item < $jumitem+1) {
				return true;
			}
			else{
				echo "<script>bootbox.alert('Item tidak cukup, $jumitem tersisa di gudang!', function(){
					window.location='index.php?page=tambahpenjualan';
				});</script>";
			}
		}
		public function hitung_total_sementara($kode){
			$qry = mysqli_query($this->koneksi,"SELECT sum(total) as jumlah FROM penjualan_sementara WHERE kd_penjualan = '$kode'");
			$pecah = mysqli_fetch_array($qry);
			$cek = $this->cek_data_barangp($kode);
			if ($cek === true) {
				$subtotal = $pecah['jumlah'];
			}
			else{
				$subtotal = 0;
			}
			return $subtotal;
		}
		public function hitung_item_penjualan($kdpenjualan){
			$qry = mysqli_query($this->koneksi,"SELECT count(*) as jumlah FROM d_penjualan WHERE kd_penjualan = '$kdpenjualan'");
			$pecah = mysqli_fetch_array($qry);

			return $pecah;
		}
		public function simpan_penjualan($kdpenjualan,$tglpen,$ttlbayar,$subtotal){
			//insert penjualan
			$kdadmin = $_SESSION['login_admin']['id'];
			mysqli_query($this->koneksi,"INSERT INTO penjualan(kd_penjualan,tgl_penjualan,kd_admin,dibayar,total_penjualan) 
				VALUES('$kdpenjualan','$tglpen','$kdadmin','$ttlbayar','$subtotal')");
			
			//insert d penjualan
			mysqli_query($this->koneksi,"INSERT INTO d_penjualan(kd_penjualan,kd_barang,jumlah,subtotal) 
				SELECT kd_penjualan, kd_barang,item,total FROM penjualan_sementara WHERE kd_penjualan='$kdpenjualan'");
			//hapus semua penjualan sementera
			mysqli_query($this->koneksi,"DELETE FROM penjualan_sementara WHERE kd_penjualan = '$kdpenjualan'");
		}
		public function ambil_kdpen(){
			$qry = mysqli_query($this->koneksi,"SELECT * FROM penjualan ORDER BY kd_penjualan DESC LIMIT 1");
			$pecah = mysqli_fetch_assoc($qry);
			return $pecah;
		}
		public function hapus_penjualan_sementara($kd){
			//update barang, di kembalikan ke setok semula
			$datpen = $this->ambil_penjualan_sementara($kd);
			$datbar = $this->ambil_barang($datpen['kd_barang']);
			$stok = $datbar['stok'] + $datpen['item'];
			$kdbar = $datpen['kd_barang'];
			mysqli_query($this->koneksi,"UPDATE barang SET stok ='$stok' WHERE kd_barang = '$kdbar'");
			//hapus
			mysqli_query($this->koneksi,"DELETE FROM penjualan_sementara WHERE id_penjualan_sementara = '$kd'");
		}
		public function ambil_penjualan_sementara($kd){
			$qry = mysqli_query($this->koneksi,"SELECT * FROM penjualan_sementara WHERE id_penjualan_sementara = '$kd'");
			$pecah = mysqli_fetch_assoc($qry);
			return $pecah;
		}
	}
	class Nota extends DataBase{
		public function tampil_nota_pembelian($kd){
			$qry = mysqli_query($this->koneksi,"SELECT * FROM supplier sup 
				JOIN pembelian pem ON pem.kd_supplier = sup.kd_supplier
				JOIN admin adm ON adm.kd_admin = pem.kd_admin
				JOIN d_pembelian dpem ON pem.kd_pembelian = dpem.kd_pembelian
				JOIN barang_pembelian bpem ON dpem.kd_barang_beli = bpem.kd_barang_beli
				WHERE pem.kd_pembelian = '$kd'");
			
			while ($pecah = mysqli_fetch_array($qry)) {
				$data[]=$pecah;
			}
			$hitung = mysqli_num_rows($qry);
			if ($hitung > 0) {
				return $data;
			}
			else{
				error_reporting(0);
			}	
		}
		public function ambil_nota_pembelian($kd){
			$qry = mysqli_query($this->koneksi,"SELECT * FROM supplier sup 
				JOIN pembelian pem ON pem.kd_supplier = sup.kd_supplier
				JOIN admin adm ON adm.kd_admin = pem.kd_admin
				JOIN d_pembelian dpem ON pem.kd_pembelian = dpem.kd_pembelian
				JOIN barang_pembelian bpem ON dpem.kd_pembelian = bpem.kd_pembelian
				WHERE pem.kd_pembelian = '$kd'");
			$pecah = mysqli_fetch_assoc($qry);
			return $pecah;
		}
		public function tampil_nota_penjualan($kd){
			$qry = mysqli_query($this->koneksi,"SELECT * FROM penjualan pen
				JOIN admin adm ON adm.kd_admin = pen.kd_admin
				JOIN d_penjualan dpen ON pen.kd_penjualan = dpen.kd_penjualan
				JOIN barang bar ON dpen.kd_barang = bar.kd_barang
				WHERE pen.kd_penjualan = '$kd'");
			while ($pecah = mysqli_fetch_array($qry)) {
				$data[]=$pecah;
			}
			$hitung = mysqli_num_rows($qry);
			if ($hitung > 0) {
				return $data;
			}
			else{
				error_reporting(0);
			}	
		}
		public function ambil_nota_penjualan($kd){
			$qry = mysqli_query($this->koneksi,"SELECT * FROM penjualan pen
				JOIN admin adm ON adm.kd_admin = pen.kd_admin
				JOIN d_penjualan dpen ON pen.kd_penjualan = dpen.kd_penjualan
				JOIN barang bar ON dpen.kd_barang = bar.kd_barang
				WHERE pen.kd_penjualan = '$kd'");
			$pecah = mysqli_fetch_assoc($qry);
			return $pecah;
		}
	}
	class Laporan extends DataBase{
		public function tampil_penjualan_bulan($bln1,$bln2){
			$qry = mysqli_query($this->koneksi,"SELECT * FROM penjualan pen
				JOIN d_penjualan dpen ON pen.kd_penjualan = dpen.kd_penjualan
				JOIN barang bar ON dpen.kd_barang = bar.kd_barang 
				WHERE pen.tgl_penjualan BETWEEN '$bln1' AND '$bln2'");
			while ($pecah = mysqli_fetch_array($qry)) {
				$data[]=$pecah;
			}
			$hitung = mysqli_num_rows($qry);
			if ($hitung > 0) {
				return $data;
			}
			else{
				error_reporting(0);
			}
		}
		public function cek_penjualan_bulan($bln1,$bln2){
			$qry = mysqli_query($this->koneksi,"SELECT * FROM penjualan pen
				JOIN d_penjualan dpen ON pen.kd_penjualan = dpen.kd_penjualan
				JOIN barang bar ON dpen.kd_barang = bar.kd_barang
				WHERE pen.tgl_penjualan BETWEEN '$bln1' AND '$bln2'");
			$hitung = mysqli_num_rows($qry);
			if ($hitung >=1) {
				return true;
			}
			else{
				return false;
			}
		}
		public function hitung_total_penjualan(){
			$qry = mysqli_query($this->koneksi,"SELECT sum(dpen.subtotal) as jumlah FROM penjualan pen
				JOIN d_penjualan dpen ON pen.kd_penjualan = dpen.kd_penjualan
				JOIN barang bar ON dpen.kd_barang = bar.kd_barang");
			$pecah = mysqli_fetch_array($qry);
			$subtotal = $pecah['jumlah'];
			return $subtotal;
		}
		public function tampil_penjualan(){
			$qry = mysqli_query($this->koneksi,"SELECT * FROM penjualan pen
				JOIN d_penjualan dpen ON pen.kd_penjualan = dpen.kd_penjualan
				JOIN barang bar ON dpen.kd_barang = bar.kd_barang ");
			while ($pecah = mysqli_fetch_array($qry)) {
				$data[]=$pecah;
			}
			$hitung = mysqli_num_rows($qry);
			if ($hitung > 0) {
				return $data;
			}
			else{
				error_reporting(0);
			}
		}
		public function cek_penjualan(){
			$qry = mysqli_query($this->koneksi,"SELECT * FROM penjualan pen
				JOIN d_penjualan dpen ON pen.kd_penjualan = dpen.kd_penjualan
				JOIN barang bar ON dpen.kd_barang = bar.kd_barang");
			$hitung = mysqli_num_rows($qry);
			if ($hitung >=1) {
				return true;
			}
			else{
				return false;
			}
		}
		public function hitung_total_penjualan_bulan($bln1,$bln2){
			$qry = mysqli_query($this->koneksi,"SELECT sum(dpen.subtotal) as jumlah FROM penjualan pen
				JOIN d_penjualan dpen ON pen.kd_penjualan = dpen.kd_penjualan
				JOIN barang bar ON dpen.kd_barang = bar.kd_barang
				WHERE pen.tgl_penjualan BETWEEN '$bln1' AND '$bln2'");
			$pecah = mysqli_fetch_array($qry);
			$subtotal = $pecah['jumlah'];
			return $subtotal;
		}
		//end penjualan

		public function tampil_pembelian_bulan($bln1,$bln2){
			$qry = mysqli_query($this->koneksi,"SELECT * FROM supplier sup
				JOIN pembelian pem ON sup.kd_supplier = pem.kd_supplier
				JOIN d_pembelian dpem ON pem.kd_pembelian = dpem.kd_pembelian
				JOIN barang_pembelian barpem ON dpem.kd_barang_beli = barpem.kd_barang_beli 
				WHERE pem.tgl_pembelian BETWEEN '$bln1' AND '$bln2'");
			while ($pecah = mysqli_fetch_array($qry)) {
				$data[]=$pecah;
			}
			$hitung = mysqli_num_rows($qry);
			if ($hitung > 0) {
				return $data;
			}
			else{
				error_reporting(0);
			}
		}
		public function cek_pembelian_bulan($bln1,$bln2){
			$qry = mysqli_query($this->koneksi,"SELECT * FROM supplier sup
				JOIN pembelian pem ON sup.kd_supplier = pem.kd_supplier
				JOIN d_pembelian dpem ON pem.kd_pembelian = dpem.kd_pembelian
				JOIN barang_pembelian barpem ON dpem.kd_barang_beli = barpem.kd_barang_beli 
				WHERE pem.tgl_pembelian BETWEEN '$bln1' AND '$bln2'");
			$hitung = mysqli_num_rows($qry);
			if ($hitung >=1) {
				return true;
			}
			else{
				return false;
			}
		}
		public function hitung_total_pembelian_bulan($bln1,$bln2){
			$qry = mysqli_query($this->koneksi,"SELECT sum(dpem.subtotal) as jumlah FROM supplier sup
				JOIN pembelian pem ON sup.kd_supplier = pem.kd_supplier
				JOIN d_pembelian dpem ON pem.kd_pembelian = dpem.kd_pembelian
				JOIN barang_pembelian barpem ON dpem.kd_barang_beli = barpem.kd_barang_beli 
				WHERE pem.tgl_pembelian BETWEEN '$bln1' AND '$bln2'");
			$pecah = mysqli_fetch_array($qry);
			$subtotal = $pecah['jumlah'];
			return $subtotal;
		}
		public function hitung_total_pembelian(){
			$qry = mysqli_query($this->koneksi,"SELECT sum(dpem.subtotal) as jumlah FROM supplier sup
				JOIN pembelian pem ON sup.kd_supplier = pem.kd_supplier
				JOIN d_pembelian dpem ON pem.kd_pembelian = dpem.kd_pembelian
				JOIN barang_pembelian barpem ON dpem.kd_barang_beli = barpem.kd_barang_beli");
			$pecah = mysqli_fetch_array($qry);
			$subtotal = $pecah['jumlah'];
			return $subtotal;
		}
		public function tampil_pembelian(){
			$qry = mysqli_query($this->koneksi,"SELECT * FROM supplier sup
				JOIN pembelian pem ON sup.kd_supplier = pem.kd_supplier
				JOIN d_pembelian dpem ON pem.kd_pembelian = dpem.kd_pembelian
				JOIN barang_pembelian barpem ON dpem.kd_barang_beli = barpem.kd_barang_beli");
			while ($pecah = mysqli_fetch_array($qry)) {
				$data[]=$pecah;
			}
			$hitung = mysqli_num_rows($qry);
			if ($hitung > 0) {
				return $data;
			}
			else{
				error_reporting(0);
			}
		}
		public function cek_pembelian(){
			$qry = mysqli_query($this->koneksi,"SELECT * FROM supplier sup
				JOIN pembelian pem ON sup.kd_supplier = pem.kd_supplier
				JOIN d_pembelian dpem ON pem.kd_pembelian = dpem.kd_pembelian
				JOIN barang_pembelian barpem ON dpem.kd_barang_beli = barpem.kd_barang_beli");
			$hitung = mysqli_num_rows($qry);
			if ($hitung >=1) {
				return true;
			}
			else{
				return false;
			}
		}
		//end pembelian
		public function hitung_profit_bulan(){
			
		}
		public function hitung_profit_semua(){

		}
	}
	class Cetak_Laporan extends DataBase{
		public function laporan_penjualan_bulan($bln1,$bln2){
			$qry = mysqli_query($this->koneksi,"SELECT * FROM penjualan pen
				JOIN d_penjualan dpen ON pen.kd_penjualan = dpen.kd_penjualan
				JOIN barang bar ON dpen.kd_barang = bar.kd_barang 
				WHERE pen.tgl_penjualan BETWEEN '$bln1' AND '$bln2'");
			while ($pecah = mysqli_fetch_array($qry)) {
				$data[]=$pecah;
			}
			$hitung = mysqli_num_rows($qry);
			if ($hitung > 0) {
				return $data;
			}
			else{
				error_reporting(0);
			}
		}
		public function laporan_semua_penjualan(){
			$qry = mysqli_query($this->koneksi,"SELECT * FROM penjualan pen
				JOIN d_penjualan dpen ON pen.kd_penjualan = dpen.kd_penjualan
				JOIN barang bar ON dpen.kd_barang = bar.kd_barang ");
			while ($pecah = mysqli_fetch_array($qry)) {
				$data[]=$pecah;
			}
			$hitung = mysqli_num_rows($qry);
			if ($hitung > 0) {
				return $data;
			}
			else{
				error_reporting(0);
			}
		}
		public function laporan_pembelian_bulan($bln1,$bln2){
			$qry = mysqli_query($this->koneksi,"SELECT * FROM supplier sup
				JOIN pembelian pem ON sup.kd_supplier = pem.kd_supplier
				JOIN d_pembelian dpem ON pem.kd_pembelian = dpem.kd_pembelian
				JOIN barang_pembelian barpem ON dpem.kd_barang_beli = barpem.kd_barang_beli 
				WHERE pem.tgl_pembelian BETWEEN '$bln1' AND '$bln2'");
			while ($pecah = mysqli_fetch_array($qry)) {
				$data[]=$pecah;
			}
			$hitung = mysqli_num_rows($qry);
			if ($hitung > 0) {
				return $data;
			}
			else{
				error_reporting(0);
			}
		}public function laporan_semua_pembelian(){
			$qry = mysqli_query($this->koneksi,"SELECT * FROM supplier sup
				JOIN pembelian pem ON sup.kd_supplier = pem.kd_supplier
				JOIN d_pembelian dpem ON pem.kd_pembelian = dpem.kd_pembelian
				JOIN barang_pembelian barpem ON dpem.kd_barang_beli = barpem.kd_barang_beli");
			while ($pecah = mysqli_fetch_array($qry)) {
				$data[]=$pecah;
			}
			$hitung = mysqli_num_rows($qry);
			if ($hitung > 0) {
				return $data;
			}
			else{
				error_reporting(0);
			}
		}
	}
	class Perusahaan extends DataBase{
		public function tampil_perusahaan(){
			$qry = mysqli_query($this->koneksi,"SELECT * FROM perusahaan WHERE kd_perusahaan = '1'");
			$pecah = mysqli_fetch_assoc($qry);
			return $pecah;
		}
		public function simpan_perusahaan($nama,$alamat,$pemilik,$kota){
			mysqli_query($this->koneksi,"UPDATE perusahaan SET nama_perusahaan='$nama',alamat='$alamat', pemilik='$pemilik', kota='$kota' WHERE kd_perusahaan ='1' ");
		}
	}
	class Dashboard extends DataBase{
		public function penjualan_hariini(){
		$hari = date("Y-m-d");
			$qry = mysqli_query($this->koneksi,"SELECT * FROM penjualan WHERE tgl_penjualan = '$hari'");
			$hitung = mysqli_num_rows($qry);
			return $hitung;
		}
		public function pembelian_hariini(){
		$hari = date("Y-m-d");
			$qry = mysqli_query($this->koneksi,"SELECT * FROM pembelian WHERE tgl_pembelian = '$hari'");
			$hitung = mysqli_num_rows($qry);
			return $hitung;
		}
	}
	$DataBase = new DataBase();
	$DataBase->sambungkan();
	$admin = new Admin();
	$barang = new Barang();
	$supplier = new Supplier();
	$pembelian = new Pembelian();
	$penjualan = new Penjualan();
	$nota = new Nota();
	$laporan = new Laporan();
	$cetaklaporan =  new Cetak_Laporan();
	$perusahaan = new Perusahaan();
	$dashboard = new Dashboard();
?>