CREATE DATABASE IF NOT EXISTS db_siswa CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE db_siswa;

DROP TABLE IF EXISTS kmeans_distances;
DROP TABLE IF EXISTS kmeans_assignments;
DROP TABLE IF EXISTS kmeans_iterations;
DROP TABLE IF EXISTS kmeans_runs;
DROP TABLE IF EXISTS siswa;
DROP TABLE IF EXISTS clusters;
DROP TABLE IF EXISTS users;


CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','pimpinan') NOT NULL DEFAULT 'pimpinan',
    status ENUM('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO users (nama, username, password, role, status) VALUES
('Administrator', 'admin', '$2y$12$sDK9KAin4BY4Avopy1YJ2ei.FGny3wAz3U8L1qfBGV.kw227phMMm', 'admin', 'aktif'),
('Pimpinan', 'pimpinan', '$2y$12$lLjRHScqenosTsnoUO4weeuFkjoVeEJCt/6JUfjPDxQRimlAmxtry', 'pimpinan', 'aktif');

CREATE TABLE siswa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    nisn VARCHAR(30) NULL,
    nilai_akademik DECIMAL(8,2) NOT NULL COMMENT 'Nilai akademik hasil konversi sesuai Tabel 4.1',
    nilai_ekstrakurikuler DECIMAL(8,2) NOT NULL COMMENT 'Keaktifan/ekstrakurikuler hasil konversi sesuai Tabel 4.1',
    absensi DECIMAL(8,2) NOT NULL COMMENT 'Tingkat kehadiran/absensi hasil konversi sesuai Tabel 4.1',
    nilai_tugas DECIMAL(8,2) NOT NULL COMMENT 'Nilai tugas/praktik hasil konversi sesuai Tabel 4.1',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE clusters (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode VARCHAR(10) NOT NULL UNIQUE,
    nama VARCHAR(100) NOT NULL,
    centroid_akademik DECIMAL(12,6) NOT NULL,
    centroid_ekstrakurikuler DECIMAL(12,6) NOT NULL,
    centroid_absensi DECIMAL(12,6) NOT NULL,
    centroid_tugas DECIMAL(12,6) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE kmeans_runs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tanggal_proses DATETIME NOT NULL,
    status VARCHAR(30) NOT NULL,
    max_iterasi INT NOT NULL DEFAULT 10,
    catatan TEXT NULL
);

CREATE TABLE kmeans_iterations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    run_id INT NOT NULL,
    iterasi INT NOT NULL,
    cluster_id INT NOT NULL,
    centroid_akademik DECIMAL(12,6) NOT NULL,
    centroid_ekstrakurikuler DECIMAL(12,6) NOT NULL,
    centroid_absensi DECIMAL(12,6) NOT NULL,
    centroid_tugas DECIMAL(12,6) NOT NULL,
    FOREIGN KEY (run_id) REFERENCES kmeans_runs(id) ON DELETE CASCADE,
    FOREIGN KEY (cluster_id) REFERENCES clusters(id) ON DELETE CASCADE
);

CREATE TABLE kmeans_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    run_id INT NOT NULL,
    iterasi INT NOT NULL,
    siswa_id INT NOT NULL,
    cluster_id INT NOT NULL,
    jarak_terdekat DECIMAL(12,6) NOT NULL,
    FOREIGN KEY (run_id) REFERENCES kmeans_runs(id) ON DELETE CASCADE,
    FOREIGN KEY (siswa_id) REFERENCES siswa(id) ON DELETE CASCADE,
    FOREIGN KEY (cluster_id) REFERENCES clusters(id) ON DELETE CASCADE
);

CREATE TABLE kmeans_distances (
    id INT AUTO_INCREMENT PRIMARY KEY,
    assignment_id INT NOT NULL,
    run_id INT NOT NULL,
    iterasi INT NOT NULL,
    siswa_id INT NOT NULL,
    cluster_id INT NOT NULL,
    jarak DECIMAL(12,6) NOT NULL,
    FOREIGN KEY (assignment_id) REFERENCES kmeans_assignments(id) ON DELETE CASCADE,
    FOREIGN KEY (run_id) REFERENCES kmeans_runs(id) ON DELETE CASCADE,
    FOREIGN KEY (siswa_id) REFERENCES siswa(id) ON DELETE CASCADE,
    FOREIGN KEY (cluster_id) REFERENCES clusters(id) ON DELETE CASCADE
);

INSERT INTO clusters (kode, nama, centroid_akademik, centroid_ekstrakurikuler, centroid_absensi, centroid_tugas) VALUES
('C1', 'Kemajuan Belajar Rendah', 2, 1, 1, 1),
('C2', 'Kemajuan Belajar Sedang', 3, 2, 2, 3),
('C3', 'Kemajuan Belajar Tinggi', 5, 3, 3, 4);

INSERT INTO siswa (nama, nisn, nilai_akademik, nilai_ekstrakurikuler, absensi, nilai_tugas) VALUES
('ARIL ANDIKA', '', 4, 3, 1, 2),
('DEKA LEONDRA', '', 4, 3, 3, 4),
('DHIA SARUFANA EFRI', '', 3, 3, 3, 4),
('DHIYA ZUHRA', '', 4, 3, 1, 1),
('FADOIL ALWAN', '', 4, 3, 1, 2),
('FEMAS VIONDA', '', 4, 3, 1, 1),
('HAFIZI NOVADLI', '', 4, 3, 1, 2),
('INTAN CAHYANI', '', 5, 3, 3, 4),
('KIAN DWI ALFIZI', '', 3, 3, 1, 1),
('KHEVIN ANGGRIYAN', '', 4, 3, 3, 4),
('LAURA SINTA BELA', '', 5, 3, 2, 2),
('MAUL OKTA BIAS', '', 4, 3, 3, 4),
('M.KURNIAWAN FIKRI', '', 4, 3, 1, 1),
('REHAN SAPUTRA', '', 4, 1, 3, 4),
('ABIMA ZIDHA', '', 4, 2, 2, 2),
('ANESA JULINDA', '', 5, 3, 3, 4),
('ANGGUN DEVIA SARI', '', 4, 1, 2, 3),
('ARYA SAPUTRA', '', 4, 2, 1, 1),
('AUREL VIDIA', '', 4, 2, 1, 2),
('AZHEL ARMYA', '', 5, 2, 3, 4),
('DIFA ANISA', '', 4, 3, 1, 2),
('DIFO FRANDIKA', '', 5, 2, 3, 4),
('FERDIA ARAFFI', '', 4, 2, 2, 3),
('GALANG AHMAD FADDRI', '', 4, 2, 3, 4),
('IRZA ELPIANTI', '', 4, 3, 1, 1),
('LEDISTYA MESKA PUTRI', '', 5, 2, 1, 2),
('MEIFI DWI LESTIA', '', 4, 1, 1, 1),
('MUHAMMAD DAFA', '', 4, 2, 3, 4),
('NABILA SAPITRI', '', 5, 2, 1, 2),
('NADIN NAZIRA PISTA', '', 5, 2, 3, 4),
('OLIVIA DAYANG PUTRI', '', 4, 2, 2, 3),
('OLIVIA PEBRIANA', '', 5, 2, 2, 3),
('PRETY LAUDIA BELA', '', 5, 1, 1, 2),
('TETIA NILIA MELITA', '', 4, 2, 2, 3),
('WAHYUNI RAMADAN NINGSIH', '', 4, 1, 2, 3),
('AFRAL RISMI', '', 4, 1, 1, 2),
('HANA PUTRI ANDINI', '', 5, 3, 3, 4),
('CHELSY PALENTINA', '', 4, 1, 1, 2),
('HEVI DAYANG', '', 5, 3, 3, 4),
('MELISA ARLINDA P', '', 4, 2, 3, 4),
('OZA NOPITA', '', 4, 3, 3, 4),
('REVALDI', '', 4, 3, 3, 4),
('VANIA FARANTIKA', '', 5, 3, 2, 3),
('SAGIA ASISKA', '', 4, 2, 3, 4),
('ZIKRA WANDISTA', '', 4, 2, 2, 2),
('SAQIFA AULIYA', '', 4, 2, 3, 4),
('KAILA AMELIA', '', 4, 1, 3, 4),
('MIZA ALFIKA', '', 5, 1, 2, 3),
('Al Faris Ibnu Azis', '', 4, 3, 3, 4),
('Ariel Yoka Puttra', '', 4, 1, 3, 4),
('Azil Apriansi Akia', '', 4, 1, 3, 4),
('Ahmad Azil Fefryal', '', 4, 1, 3, 4),
('Aidil Lekza Putra', '', 4, 1, 3, 4),
('Anggun Putri', '', 4, 1, 3, 4),
('Arhan Pratama', '', 4, 1, 3, 4),
('Avip Opan Febri', '', 3, 1, 3, 4),
('Billy Alfares', '', 3, 1, 3, 4),
('Elfin Joni', '', 3, 1, 3, 4),
('Erik Afandi', '', 4, 1, 3, 4),
('Erik Gemirza', '', 3, 1, 3, 4),
('Erik Pernando', '', 4, 1, 3, 4),
('Evan Diza Roanda', '', 3, 1, 3, 4),
('Fallen Febian Putra', '', 3, 1, 3, 4),
('Farelka Putra', '', 4, 1, 3, 4),
('Fickel Van Arozak', '', 4, 1, 3, 4),
('Frondy Razim Daib', '', 3, 1, 3, 4),
('Gipan Ayondi Hardian', '', 4, 1, 3, 4),
('Golwian', '', 5, 1, 3, 4),
('Ilham Zuhri Prastio', '', 3, 1, 3, 4),
('Irfan Deski', '', 4, 1, 3, 4),
('Moh. Rezky Al Hafiz', '', 4, 1, 3, 4),
('Putra Dion Satria', '', 3, 1, 3, 4),
('Restu Ramdhani', '', 4, 1, 3, 4),
('Rezal Altoriq', '', 4, 1, 3, 4),
('Robil Zabri', '', 3, 1, 3, 4),
('Zhaky Pramulya', '', 3, 1, 3, 4);


-- View kompatibel dengan nama tabel pada BAB IV PDF
DROP VIEW IF EXISTS tb_siswa;
CREATE VIEW tb_siswa AS
SELECT id, nama AS nama_siswa, nisn, '' AS kelas, nilai_akademik, absensi, nilai_ekstrakurikuler AS ekstrakurikuler, nilai_tugas, YEAR(CURRENT_DATE) AS tahun
FROM siswa;

DROP VIEW IF EXISTS tb_hasil_clustering_siswa;
CREATE VIEW tb_hasil_clustering_siswa AS
SELECT a.id AS id_hasil, a.siswa_id AS id, c.kode AS cluster, YEAR(r.tanggal_proses) AS tahun
FROM kmeans_assignments a
JOIN clusters c ON c.id = a.cluster_id
JOIN kmeans_runs r ON r.id = a.run_id
WHERE a.iterasi = (SELECT MAX(iterasi) FROM kmeans_assignments WHERE run_id = a.run_id);
