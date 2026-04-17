-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 17 Apr 2026 pada 04.29
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `laundry_db`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `laporan`
--

CREATE TABLE `laporan` (
  `id_laporan_periode` int(11) NOT NULL,
  `id_order` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `periode_bulan` int(11) NOT NULL,
  `periode_tahun` int(11) NOT NULL,
  `total_harga` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `laporan`
--

INSERT INTO `laporan` (`id_laporan_periode`, `id_order`, `id_user`, `periode_bulan`, `periode_tahun`, `total_harga`) VALUES
(1, 1, 4, 4, 2026, 40000.00),
(2, 2, 4, 4, 2026, 34200.00),
(3, 3, 4, 4, 2026, 32000.00),
(4, 4, 4, 4, 2026, 60000.00),
(5, 5, 4, 4, 2026, 40000.00),
(6, 6, 4, 4, 2026, 8000.00),
(7, 7, 4, 4, 2026, 8000.00),
(8, 8, 4, 4, 2026, 32000.00),
(9, 9, 4, 4, 2026, 6000.00),
(10, 10, 5, 4, 2026, 8000.00),
(11, 11, 5, 4, 2026, 8000.00),
(12, 12, 4, 4, 2026, 57000.00),
(13, 13, 4, 4, 2026, 8000.00),
(14, 14, 5, 4, 2026, 75000.00),
(16, 15, 5, 4, 2026, 100000.00);

-- --------------------------------------------------------

--
-- Struktur dari tabel `layanan`
--

CREATE TABLE `layanan` (
  `id_layanan` int(11) NOT NULL,
  `nama_layanan` varchar(100) NOT NULL,
  `harga_per_kg` decimal(10,2) NOT NULL,
  `estimasi_hari` int(11) NOT NULL,
  `deskripsi` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `layanan`
--

INSERT INTO `layanan` (`id_layanan`, `nama_layanan`, `harga_per_kg`, `estimasi_hari`, `deskripsi`) VALUES
(1, 'Cuci Reguler', 8000.00, 2, 'Cuci + pengeringan, tanpa setrika. Pakaian bersih dan wangi.'),
(2, 'Cuci + Setrika', 12000.00, 2, 'Cuci, kering, dan setrika rapi. Pakaian siap pakai.'),
(3, 'Setrika Saja', 6000.00, 1, 'Setrika pakaian saja. Cepat dan rapi.'),
(4, 'Dry Cleaning', 25000.00, 3, 'Pembersihan khusus untuk pakaian formal, jas, dan bahan sensitif.'),
(5, 'Express 1 Hari', 20000.00, 1, 'Layanan kilat selesai dalam 1 hari.'),
(6, 'Selimut & Bed Cover', 15000.00, 2, 'Cuci khusus untuk selimut dan bed cover besar.');

-- --------------------------------------------------------

--
-- Struktur dari tabel `orders`
--

CREATE TABLE `orders` (
  `id_order` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `id_layanan` int(11) NOT NULL,
  `tanggal_order` date NOT NULL,
  `status_order` enum('pending','proses','selesai','diambil') DEFAULT 'pending',
  `harga_snapshot` decimal(10,2) NOT NULL,
  `berat_cucian` decimal(10,2) NOT NULL,
  `catatan` text DEFAULT NULL,
  `pickup_date` date DEFAULT NULL,
  `pickup_time` varchar(50) DEFAULT NULL,
  `pickup_address` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `orders`
--

INSERT INTO `orders` (`id_order`, `id_user`, `id_layanan`, `tanggal_order`, `status_order`, `harga_snapshot`, `berat_cucian`, `catatan`, `pickup_date`, `pickup_time`, `pickup_address`) VALUES
(1, 4, 1, '2026-04-16', 'selesai', 40000.00, 5.00, '', NULL, NULL, NULL),
(2, 4, 3, '2026-04-16', 'selesai', 34200.00, 6.00, '', NULL, NULL, NULL),
(3, 4, 1, '2026-04-16', 'selesai', 32000.00, 4.00, '', NULL, NULL, NULL),
(4, 4, 2, '2026-04-16', 'selesai', 60000.00, 5.00, '', NULL, NULL, NULL),
(5, 4, 1, '2026-04-16', 'selesai', 40000.00, 5.00, '', NULL, NULL, NULL),
(6, 4, 1, '2026-04-16', 'selesai', 8000.00, 1.00, '', NULL, NULL, NULL),
(7, 4, 1, '2026-04-16', 'selesai', 8000.00, 1.00, '', NULL, NULL, NULL),
(8, 4, 1, '2026-04-16', 'selesai', 32000.00, 4.00, '', NULL, NULL, NULL),
(9, 4, 3, '2026-04-16', 'selesai', 6000.00, 1.00, '', NULL, NULL, NULL),
(10, 5, 1, '2026-04-16', 'selesai', 8000.00, 1.00, '', NULL, NULL, NULL),
(11, 5, 1, '2026-04-16', 'selesai', 8000.00, 1.00, '', NULL, NULL, NULL),
(12, 4, 3, '2026-04-16', 'selesai', 57000.00, 10.00, '', NULL, NULL, NULL),
(13, 4, 1, '2026-04-16', 'selesai', 8000.00, 1.00, '', '2026-04-17', '08:00-10:00', 'Bandung'),
(14, 5, 4, '2026-04-16', 'selesai', 75000.00, 3.00, '', '2026-04-17', '08:00-10:00', 'Jakarta'),
(15, 5, 5, '2026-04-16', 'selesai', 100000.00, 5.00, '', '2026-04-17', '08:00-10:00', 'Jakarta');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pembayaran`
--

CREATE TABLE `pembayaran` (
  `id_pembayaran` int(11) NOT NULL,
  `id_order` int(11) NOT NULL,
  `metode` enum('gopay','dana','bca','bri','mandiri','bni','tunai','saldo') NOT NULL,
  `jumlah_bayar` decimal(10,2) NOT NULL,
  `status_bayar` enum('belum_bayar','pending','lunas') DEFAULT 'belum_bayar',
  `bukti_bayar` varchar(255) DEFAULT NULL,
  `nomor_transaksi` varchar(50) DEFAULT NULL,
  `tanggal_pembayaran` datetime DEFAULT NULL,
  `bukti_pembayaran` varchar(255) DEFAULT NULL,
  `catatan_pembayaran` text DEFAULT NULL,
  `tanggal_verifikasi` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `pembayaran`
--

INSERT INTO `pembayaran` (`id_pembayaran`, `id_order`, `metode`, `jumlah_bayar`, `status_bayar`, `bukti_bayar`, `nomor_transaksi`, `tanggal_pembayaran`, `bukti_pembayaran`, `catatan_pembayaran`, `tanggal_verifikasi`) VALUES
(1, 13, 'saldo', 8000.00, 'lunas', NULL, 'TRX20260416125250722', '2026-04-16 17:52:50', NULL, NULL, NULL),
(2, 14, 'gopay', 75000.00, 'lunas', NULL, 'TRX20260416130136980', '2026-04-16 18:01:36', 'uploads/bukti_pembayaran/bukti_14_1776337296.jpg', 'Done', '2026-04-16 18:02:38'),
(3, 14, 'saldo', 75000.00, 'lunas', NULL, 'TRX20260416130156945', '2026-04-16 18:01:56', NULL, NULL, '2026-04-16 18:02:38'),
(4, 15, 'dana', 100000.00, 'lunas', NULL, 'TRX20260416171305931', '2026-04-16 22:13:05', 'uploads/bukti_pembayaran/bukti_15_1776352385.jpg', '', '2026-04-16 22:14:31');

-- --------------------------------------------------------

--
-- Struktur dari tabel `topup`
--

CREATE TABLE `topup` (
  `id_topup` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `nominal` decimal(10,2) NOT NULL,
  `metode` enum('gopay','dana','bca','bri','mandiri','bni','tunai') NOT NULL,
  `tanggal_topup` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `topup`
--

INSERT INTO `topup` (`id_topup`, `id_user`, `nominal`, `metode`, `tanggal_topup`) VALUES
(1, 5, 500000.00, 'tunai', '2026-04-16 17:31:26');

-- --------------------------------------------------------

--
-- Struktur dari tabel `transaksi_saldo`
--

CREATE TABLE `transaksi_saldo` (
  `id_transaksi` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `id_order` int(11) DEFAULT NULL,
  `nominal` decimal(10,2) NOT NULL,
  `jenis` enum('topup','pembayaran','refund','penambahan_manual') NOT NULL,
  `status` enum('pending','sukses','gagal') NOT NULL DEFAULT 'sukses',
  `keterangan` text DEFAULT NULL,
  `tanggal` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `transaksi_saldo`
--

INSERT INTO `transaksi_saldo` (`id_transaksi`, `id_user`, `id_order`, `nominal`, `jenis`, `status`, `keterangan`, `tanggal`) VALUES
(1, 4, 13, 8000.00, 'pembayaran', 'sukses', 'Pembayaran order #13 via Saldo', '2026-04-16 17:52:50'),
(2, 5, 14, 75000.00, 'pembayaran', 'sukses', 'Pembayaran order #14 via Saldo', '2026-04-16 18:01:56');

-- --------------------------------------------------------

--
-- Struktur dari tabel `user`
--

CREATE TABLE `user` (
  `id_user` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `no_hp` varchar(15) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `role` enum('customer','worker','supervisor','admin') NOT NULL DEFAULT 'customer',
  `saldo` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `user`
--

INSERT INTO `user` (`id_user`, `nama`, `email`, `password`, `no_hp`, `alamat`, `role`, `saldo`) VALUES
(1, 'Admin Utama', 'admin@laundry.com', '240be518fabd2724ddb6f04eeb1da5967448d7e831c08c8fa822809f74c720a9', '08123456789', 'Kantor Laundry', 'admin', 0.00),
(2, 'Kasir Laundry', 'worker@laundry.com', '312bba6ac1c4274943d7d3c1f346e8e27310c731e407ce5592d82f0d101fbff1', '08123456780', 'Kantor Laundry', 'worker', 0.00),
(3, 'Supervisor', 'supervisor@laundry.com', '4e4c56e4a15f89f05c2f4c72613da2a18c9665d4f0d6acce16415eb06f9be776', '08123456781', 'Kantor Laundry', 'supervisor', 0.00),
(4, 'Michel', 'michel@gmail.com', 'be8257908c36e1469d9cc5a25027223e52394b808f1ee6cce2aeb33a731a4db2', '000008989', 'Bandung', 'customer', 92000.00),
(5, 'Jolvin', 'jolvin@gmail.com', 'b75134893d461e534dd9f3e64005e9975cdace925936be318167b5d19ee7feb9', '89098768900', 'Jakarta', 'customer', 425000.00);

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `laporan`
--
ALTER TABLE `laporan`
  ADD PRIMARY KEY (`id_laporan_periode`),
  ADD KEY `id_order` (`id_order`),
  ADD KEY `id_user` (`id_user`);

--
-- Indeks untuk tabel `layanan`
--
ALTER TABLE `layanan`
  ADD PRIMARY KEY (`id_layanan`);

--
-- Indeks untuk tabel `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id_order`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_layanan` (`id_layanan`);

--
-- Indeks untuk tabel `pembayaran`
--
ALTER TABLE `pembayaran`
  ADD PRIMARY KEY (`id_pembayaran`),
  ADD KEY `id_order` (`id_order`);

--
-- Indeks untuk tabel `topup`
--
ALTER TABLE `topup`
  ADD PRIMARY KEY (`id_topup`),
  ADD KEY `id_user` (`id_user`);

--
-- Indeks untuk tabel `transaksi_saldo`
--
ALTER TABLE `transaksi_saldo`
  ADD PRIMARY KEY (`id_transaksi`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_order` (`id_order`);

--
-- Indeks untuk tabel `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `laporan`
--
ALTER TABLE `laporan`
  MODIFY `id_laporan_periode` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT untuk tabel `layanan`
--
ALTER TABLE `layanan`
  MODIFY `id_layanan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `orders`
--
ALTER TABLE `orders`
  MODIFY `id_order` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT untuk tabel `pembayaran`
--
ALTER TABLE `pembayaran`
  MODIFY `id_pembayaran` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `topup`
--
ALTER TABLE `topup`
  MODIFY `id_topup` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `transaksi_saldo`
--
ALTER TABLE `transaksi_saldo`
  MODIFY `id_transaksi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `user`
--
ALTER TABLE `user`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `laporan`
--
ALTER TABLE `laporan`
  ADD CONSTRAINT `laporan_ibfk_1` FOREIGN KEY (`id_order`) REFERENCES `orders` (`id_order`) ON DELETE CASCADE,
  ADD CONSTRAINT `laporan_ibfk_2` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE,
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`id_layanan`) REFERENCES `layanan` (`id_layanan`);

--
-- Ketidakleluasaan untuk tabel `pembayaran`
--
ALTER TABLE `pembayaran`
  ADD CONSTRAINT `pembayaran_ibfk_1` FOREIGN KEY (`id_order`) REFERENCES `orders` (`id_order`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `topup`
--
ALTER TABLE `topup`
  ADD CONSTRAINT `topup_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `transaksi_saldo`
--
ALTER TABLE `transaksi_saldo`
  ADD CONSTRAINT `transaksi_saldo_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE,
  ADD CONSTRAINT `transaksi_saldo_ibfk_2` FOREIGN KEY (`id_order`) REFERENCES `orders` (`id_order`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
