-- phpMyAdmin SQL Dump
-- version 4.9.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 14, 2020 at 01:29 AM
-- Server version: 10.4.6-MariaDB-log
-- PHP Version: 7.3.13

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_kerja`
--

-- --------------------------------------------------------

--
-- Table structure for table `tbl_pelamar`
--

CREATE TABLE `tbl_pelamar` (
  `id_pelamar` varchar(10) NOT NULL,
  `id_pengguna` varchar(255) NOT NULL,
  `nama` varchar(255) NOT NULL,
  `jk` varchar(10) NOT NULL,
  `alamat` varchar(255) NOT NULL,
  `tempat_lahir` varchar(255) NOT NULL,
  `tanggal_lahir` varchar(255) NOT NULL,
  `agama` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tbl_pelamar`
--

INSERT INTO `tbl_pelamar` (`id_pelamar`, `id_pengguna`, `nama`, `jk`, `alamat`, `tempat_lahir`, `tanggal_lahir`, `agama`, `status`) VALUES
('PEL001', 'PGN005', 'Kopiku', 'Pria', 'Jl. Gandaria II', 'Jakarta', '17-02-1997', 'Islam', 'Belum Menikah'),
('PEL002', 'PGN006', 'Kopiku', 'Pria', 'Jl. Gandaria II', 'Jakarta', '17-02-1997', 'Islam', 'Belum Menikah');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_pengguna`
--

CREATE TABLE `tbl_pengguna` (
  `id_pengguna` varchar(10) NOT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tbl_pengguna`
--

INSERT INTO `tbl_pengguna` (`id_pengguna`, `username`, `email`, `password`, `role`, `is_active`) VALUES
('PGN001', 'fht', 'fht@mail.com', '202cb962ac59075b964b07152d234b70', '', 0),
('PGN002', 'ibra', 'ibrahim170297@gmail.com', '5f4dcc3b5aa765d61d8327deb882cf99', 'Pelamar', 0),
('PGN003', 'kopiku', 'kopiku@gmail.com', '5f4dcc3b5aa765d61d8327deb882cf99', 'Perusahaan', 0),
('PGN004', 'kopiku', 'kopiku@gmail.com', '5f4dcc3b5aa765d61d8327deb882cf99', 'Perusahaan', 0),
('PGN005', 'kopiku', 'kopiku@gmail.com', '5f4dcc3b5aa765d61d8327deb882cf99', 'Perusahaan', 0),
('PGN006', 'baim', 'baim@gmail.com', '5f4dcc3b5aa765d61d8327deb882cf99', 'Perusahaan', 0);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_perusahaan`
--

CREATE TABLE `tbl_perusahaan` (
  `id_perusahaan` varchar(10) NOT NULL,
  `id_pengguna` varchar(10) NOT NULL,
  `nama` varchar(255) NOT NULL,
  `alamat` varchar(255) NOT NULL,
  `telepon` varchar(255) NOT NULL,
  `jenis` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tbl_perusahaan`
--

INSERT INTO `tbl_perusahaan` (`id_perusahaan`, `id_pengguna`, `nama`, `alamat`, `telepon`, `jenis`) VALUES
('PER001', 'PGN004', 'Kopiku', 'Jl. Gandaria II', '0877102123', 'Kafe');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tbl_pengguna`
--
ALTER TABLE `tbl_pengguna`
  ADD PRIMARY KEY (`id_pengguna`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
