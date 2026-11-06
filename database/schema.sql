
-- 1. Buat dan gunakan database
CREATE DATABASE IF NOT EXISTS db_toko_buku
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE db_toko_buku;

-- 2. Buat tabel buku
--    Kolom cover menyimpan URL gambar dari internet (string)
CREATE TABLE IF NOT EXISTS buku (
    id       INT(11)        NOT NULL AUTO_INCREMENT,
    judul    VARCHAR(150)   NOT NULL,
    penulis  VARCHAR(100)   NOT NULL,
    penerbit VARCHAR(100)   NOT NULL,
    harga    DECIMAL(12,0)  NOT NULL DEFAULT 0,
    stok     INT(11)        NOT NULL DEFAULT 0,
    deskripsi TEXT,
    cover    VARCHAR(255)   DEFAULT NULL,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2b. Tabel users (akun pelanggan & admin)
--     password menyimpan hasil password_hash() (bcrypt), BUKAN teks asli.
CREATE TABLE IF NOT EXISTS users (
    id         INT(11)      NOT NULL AUTO_INCREMENT,
    nama       VARCHAR(100) NOT NULL,
    email      VARCHAR(150) NOT NULL,
    password   VARCHAR(255) NOT NULL,
    role       ENUM('user','admin') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2c. Tabel pesanan (header transaksi checkout)
--     Relasi 1 user -> banyak pesanan. ON DELETE RESTRICT: user yang
--     punya riwayat pesanan tidak bisa dihapus sembarangan.
CREATE TABLE IF NOT EXISTS pesanan (
    id         INT(11)      NOT NULL AUTO_INCREMENT,
    user_id    INT(11)      NOT NULL,
    total      DECIMAL(12,0) NOT NULL DEFAULT 0,
    status     ENUM('pending','dibayar','dikirim','selesai','batal')
               NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_pesanan_user (user_id),
    CONSTRAINT fk_pesanan_user FOREIGN KEY (user_id)
        REFERENCES users (id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2d. Tabel detail_pesanan (rincian item per pesanan)
--     judul & harga disalin (snapshot) supaya riwayat pesanan tetap utuh
--     walaupun data buku diubah/dihapus admin di kemudian hari.
CREATE TABLE IF NOT EXISTS detail_pesanan (
    id         INT(11)       NOT NULL AUTO_INCREMENT,
    pesanan_id INT(11)       NOT NULL,
    buku_id    INT(11)       DEFAULT NULL,
    judul      VARCHAR(150)  NOT NULL,
    harga      DECIMAL(12,0) NOT NULL,
    jumlah     INT(11)       NOT NULL,
    subtotal   DECIMAL(12,0) NOT NULL,
    PRIMARY KEY (id),
    KEY idx_detail_pesanan (pesanan_id),
    CONSTRAINT fk_detail_pesanan FOREIGN KEY (pesanan_id)
        REFERENCES pesanan (id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_detail_buku FOREIGN KEY (buku_id)
        REFERENCES buku (id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Data contoh (INSERT INTO)
INSERT INTO buku (judul, penulis, penerbit, harga, stok, deskripsi, cover) VALUES
(
    'Laskar Pelangi',
    'Andrea Hirata',
    'Bentang Pustaka',
    89000,
    25,
    'Novel pertama dari Tetralogi Laskar Pelangi karya Andrea Hirata. Berkisah tentang sepuluh anak Belitung yang berjuang mendapatkan pendidikan di bawah guru-guru yang luar biasa. Sebuah kisah yang menginspirasi jutaan pembaca di seluruh dunia.',
    'https://images-na.ssl-images-amazon.com/images/S/compressed.photo.goodreads.com/books/1348613305i/1331181.jpg'
),
(
    'Bumi Manusia',
    'Pramoedya Ananta Toer',
    'Lentera Dipantara',
    95000,
    18,
    'Novel pertama dari Tetralogi Buru karya Pramoedya Ananta Toer. Berlatar belakang awal abad ke-20 di masa penjajahan Belanda, mengisahkan perjalanan hidup Minke, seorang pemuda pribumi yang cerdas dan penuh semangat.',
    'https://images-na.ssl-images-amazon.com/images/S/compressed.photo.goodreads.com/books/1388186521i/282748.jpg'
),
(
    'Atomic Habits',
    'James Clear',
    'Gramedia Pustaka Utama',
    119000,
    40,
    'Buku panduan revolusioner untuk membangun kebiasaan baik dan menghilangkan kebiasaan buruk. James Clear mengungkap cara kerja kebiasaan dan memberikan strategi praktis berdasarkan penelitian ilmiah untuk membuat perubahan kecil yang menghasilkan hasil luar biasa.',
    'https://images-na.ssl-images-amazon.com/images/S/compressed.photo.goodreads.com/books/1655988385i/40121378.jpg'
),
(
    'Filosofi Teras',
    'Henry Manampiring',
    'Kompas',
    98000,
    30,
    'Buku yang memperkenalkan filsafat Stoisisme kuno dengan cara yang relevan dan mudah dipahami oleh pembaca modern Indonesia. Berisi panduan praktis untuk menghadapi emosi negatif, kecemasan, dan ketidakpastian hidup sehari-hari.',
    'https://images-na.ssl-images-amazon.com/images/S/compressed.photo.goodreads.com/books/1560248449i/45894108.jpg'
),
(
    'Harry Potter dan Batu Bertuah',
    'J.K. Rowling',
    'Gramedia Pustaka Utama',
    129000,
    15,
    'Kisah petualangan seorang anak yatim piatu bernama Harry Potter yang pada hari ulang tahunnya yang ke-11 mengetahui bahwa dirinya adalah seorang penyihir. Ia kemudian belajar di Hogwarts School of Witchcraft and Wizardry.',
    'https://images-na.ssl-images-amazon.com/images/S/compressed.photo.goodreads.com/books/1474154022i/3.jpg'
);

INSERT INTO buku (judul, penulis, penerbit, harga, stok, deskripsi, cover) VALUES
(
    'The Psychology of Money',
    'Morgan Housel',
    'Harriman House',
    98000,
    29,
    'Kumpulan cerita tentang bagaimana perilaku, bukan kecerdasan, menentukan keberhasilan finansial. Cocok untuk anak muda yang baru belajar mengatur uang, menabung, dan berinvestasi.',
    'https://covers.openlibrary.org/b/isbn/9780857197689-L.jpg'
),
(
    'It Ends with Us',
    'Colleen Hoover',
    'Atria Books',
    135000,
    20,
    'Novel romance emosional tentang Lily, hubungan yang rumit, dan keberanian mengambil keputusan sulit. Salah satu novel yang paling ramai dibicarakan di komunitas pembaca muda.',
    'https://covers.openlibrary.org/b/isbn/9781501110368-L.jpg'
),
(
    'The Seven Husbands of Evelyn Hugo',
    'Taylor Jenkins Reid',
    'Atria Books',
    140000,
    16,
    'Bintang film legendaris Evelyn Hugo akhirnya mau mengungkap kisah hidup dan tujuh pernikahannya kepada seorang jurnalis muda. Penuh kejutan, glamor, dan rahasia yang disimpan selama puluhan tahun.',
    'https://covers.openlibrary.org/b/isbn/9781501139239-L.jpg'
),
(
    'The Hunger Games',
    'Suzanne Collins',
    'Scholastic',
    115000,
    19,
    'Katniss mengajukan diri menggantikan adiknya dalam pertarungan hidup mati yang disiarkan televisi. Novel distopia penuh aksi yang menjadi favorit banyak pembaca remaja.',
    'https://covers.openlibrary.org/b/isbn/9780439023528-L.jpg'
),
(
    'Percy Jackson dan Pencuri Petir',
    'Rick Riordan',
    'Gramedia Pustaka Utama',
    99000,
    21,
    'Percy Jackson mengetahui bahwa dirinya adalah putra dewa Yunani dan harus mencegah perang antar dewa. Petualangan fantasi seru dengan humor khas remaja.',
    'https://covers.openlibrary.org/b/isbn/9780786838653-L.jpg'
),
(
    'One Piece Vol. 1',
    'Eiichiro Oda',
    'Elex Media Komputindo',
    32000,
    40,
    'Petualangan Monkey D. Luffy yang bercita-cita menjadi Raja Bajak Laut. Volume pertama dari manga petualangan terpopuler sepanjang masa.',
    'https://covers.openlibrary.org/b/isbn/9781569319017-L.jpg'
),
(
    'Jujutsu Kaisen Vol. 1',
    'Gege Akutami',
    'Elex Media Komputindo',
    36000,
    35,
    'Yuji Itadori terseret ke dunia penyihir jujutsu setelah menelan jari terkutuk milik iblis terkuat. Manga aksi supranatural yang sangat digemari generasi muda.',
    'https://covers.openlibrary.org/b/isbn/9781974710027-L.jpg'
),
(
    'The Subtle Art of Not Giving a F*ck',
    'Mark Manson',
    'HarperOne',
    125000,
    31,
    'Pendekatan segar untuk memilih hal yang benar-benar layak dipedulikan dalam hidup. Buku pengembangan diri yang blak-blakan, lucu, dan jauh dari nasihat motivasi yang klise.',
    'https://covers.openlibrary.org/b/isbn/9780062457714-L.jpg'
),
(
    'Fourth Wing',
    'Rebecca Yarros',
    'Entangled Publishing',
    165000,
    18,
    'Violet Sorrengail terpaksa masuk akademi para penunggang naga, tempat yang mematikan bagi mereka yang lemah. Novel fantasi romance yang meledak di kalangan pembaca muda.',
    'https://covers.openlibrary.org/b/isbn/9781649374042-L.jpg'
),
(
    'Divergent',
    'Veronica Roth',
    'Katherine Tegen Books',
    110000,
    22,
    'Beatrice hidup di masyarakat yang terbagi menjadi lima faksi, dan ia menyimpan rahasia yang bisa membahayakan nyawanya. Novel distopia penuh aksi dan pilihan sulit.',
    'https://covers.openlibrary.org/b/isbn/9780062024039-L.jpg'
),
(
    'The Fault in Our Stars',
    'John Green',
    'Dutton Books',
    105000,
    17,
    'Kisah cinta Hazel dan Augustus, dua remaja pengidap kanker yang bertemu di kelompok dukungan. Menyentuh, lucu, dan menguras air mata.',
    'https://covers.openlibrary.org/b/isbn/9780525478812-L.jpg'
),
(
    'Twilight',
    'Stephenie Meyer',
    'Little, Brown and Company',
    99000,
    25,
    'Bella Swan pindah ke kota kecil yang berhujan dan jatuh cinta pada Edward Cullen, cowok misterius yang menyimpan rahasia besar.',
    'https://covers.openlibrary.org/b/isbn/9780316015844-L.jpg'
),
(
    'The Midnight Library',
    'Matt Haig',
    'Viking',
    135000,
    19,
    'Nora Seed menemukan perpustakaan ajaib yang memungkinkannya mencoba kehidupan lain dari setiap pilihan yang tidak pernah ia ambil. Cerita hangat tentang penyesalan dan harapan.',
    'https://covers.openlibrary.org/b/isbn/9780525559474-L.jpg'
),
(
    'Where the Crawdads Sing',
    'Delia Owens',
    'G.P. Putnam''s Sons',
    130000,
    15,
    'Kya, gadis yang tumbuh sendirian di rawa Carolina Utara, dituduh membunuh seorang pemuda kota. Perpaduan misteri, romansa, dan alam yang memikat.',
    'https://covers.openlibrary.org/b/isbn/9780735219090-L.jpg'
),
(
    'The Alchemist',
    'Paulo Coelho',
    'HarperOne',
    95000,
    26,
    'Perjalanan seorang gembala muda mengejar takdir dan harta karun impiannya. Dongeng filosofis tentang keberanian mengikuti mimpi.',
    'https://covers.openlibrary.org/b/isbn/9780062315007-L.jpg'
),
(
    'Ikigai',
    'Hector Garcia dan Francesc Miralles',
    'Penguin Books',
    98000,
    24,
    'Rahasia orang Jepang untuk hidup panjang dan bahagia dengan menemukan alasan untuk bangun setiap pagi. Buku ringan tentang makna dan tujuan hidup.',
    'https://covers.openlibrary.org/b/isbn/9780143130727-L.jpg'
),
(
    'A Court of Thorns and Roses',
    'Sarah J. Maas',
    'Bloomsbury',
    150000,
    20,
    'Feyre membunuh seekor serigala di hutan dan diseret ke dunia peri yang berbahaya. Novel fantasi romance dengan dunia yang luas dan karakter yang kuat.',
    'https://covers.openlibrary.org/b/isbn/9781619634442-L.jpg'
),
(
    'Throne of Glass',
    'Sarah J. Maas',
    'Bloomsbury',
    145000,
    14,
    'Celaena Sardothien, pembunuh bayaran muda, dibebaskan dari kerja paksa untuk ikut kompetisi memperebutkan posisi juara raja. Fantasi penuh aksi dan intrik.',
    'https://covers.openlibrary.org/b/isbn/9781619630345-L.jpg'
),
(
    'Red Queen',
    'Victoria Aveyard',
    'HarperTeen',
    115000,
    16,
    'Mare Barrow, gadis berdarah merah biasa, tiba-tiba menemukan kekuatan yang seharusnya hanya dimiliki bangsawan berdarah perak. Fantasi distopia penuh pengkhianatan.',
    'https://covers.openlibrary.org/b/isbn/9780062310644-L.jpg'
),
(
    'Normal People',
    'Sally Rooney',
    'Hogarth',
    120000,
    13,
    'Hubungan rumit Marianne dan Connell dari masa SMA hingga kuliah, penuh kedekatan, salah paham, dan kejujuran yang canggung.',
    'https://covers.openlibrary.org/b/isbn/9781984822178-L.jpg'
);

INSERT INTO buku (judul, penulis, penerbit, harga, stok, deskripsi, cover) VALUES
(
    'Naruto Vol. 1',
    'Masashi Kishimoto',
    'VIZ Media',
    55000,
    40,
    'Naruto Uzumaki, ninja muda yang dikucilkan desanya, bercita-cita menjadi Hokage. Awal dari salah satu manga petualangan paling berpengaruh.',
    'https://covers.openlibrary.org/b/isbn/9781569319000-L.jpg'
),
(
    'Dragon Ball Vol. 1',
    'Akira Toriyama',
    'VIZ Media',
    55000,
    35,
    'Petualangan Goku kecil bersama Bulma mencari tujuh bola naga yang bisa mengabulkan permintaan. Manga legendaris penuh aksi dan humor.',
    'https://covers.openlibrary.org/b/isbn/9781569319208-L.jpg'
),
(
    'Death Note Vol. 1',
    'Tsugumi Ohba dan Takeshi Obata',
    'VIZ Media',
    70000,
    28,
    'Seorang siswa jenius menemukan buku yang bisa membunuh siapa pun yang namanya ditulis di dalamnya. Perang pikiran antara dia dan detektif misterius L.',
    'https://covers.openlibrary.org/b/isbn/9781421501680-L.jpg'
),
(
    'Attack on Titan Vol. 1',
    'Hajime Isayama',
    'Kodansha Comics',
    75000,
    30,
    'Umat manusia terkurung di balik tembok raksasa untuk bertahan dari para Titan pemakan manusia, sampai tembok itu runtuh.',
    'https://covers.openlibrary.org/b/isbn/9781612620244-L.jpg'
),
(
    'Demon Slayer Vol. 1',
    'Koyoharu Gotouge',
    'VIZ Media',
    60000,
    36,
    'Tanjiro menjadi pemburu iblis setelah keluarganya dibantai dan adiknya berubah menjadi iblis. Kisah aksi yang penuh emosi.',
    'https://covers.openlibrary.org/b/isbn/9781974700523-L.jpg'
),
(
    'My Hero Academia Vol. 1',
    'Kohei Horikoshi',
    'VIZ Media',
    60000,
    32,
    'Di dunia di mana hampir semua orang punya kekuatan super, Izuku lahir tanpa kekuatan tapi tetap bermimpi menjadi pahlawan nomor satu.',
    'https://covers.openlibrary.org/b/isbn/9781421582696-L.jpg'
),
(
    'Fullmetal Alchemist Vol. 1',
    'Hiromu Arakawa',
    'VIZ Media',
    65000,
    22,
    'Dua bersaudara Elric mencari Batu Bertuah untuk memulihkan tubuh mereka setelah percobaan alkimia terlarang yang gagal.',
    'https://covers.openlibrary.org/b/isbn/9781591169208-L.jpg'
),
(
    'Tokyo Ghoul Vol. 1',
    'Sui Ishida',
    'VIZ Media',
    75000,
    20,
    'Kaneki berubah menjadi setengah ghoul setelah transplantasi organ dan harus hidup di antara dua dunia yang saling bermusuhan.',
    'https://covers.openlibrary.org/b/isbn/9781421580364-L.jpg'
),
(
    'Maus: A Survivor''s Tale I',
    'Art Spiegelman',
    'Pantheon Books',
    180000,
    12,
    'Novel grafis peraih Pulitzer tentang kisah ayah penulis yang selamat dari Holocaust, digambar dengan tikus dan kucing sebagai tokohnya.',
    'https://covers.openlibrary.org/b/isbn/9780394747231-L.jpg'
),
(
    'Persepolis',
    'Marjane Satrapi',
    'Pantheon Books',
    170000,
    14,
    'Novel grafis otobiografi tentang masa kecil dan remaja penulis di Iran saat Revolusi Islam, lucu sekaligus menyentuh.',
    'https://covers.openlibrary.org/b/isbn/9780375714832-L.jpg'
),
(
    'Campbell Biology',
    'Lisa Urry dkk.',
    'Pearson',
    950000,
    8,
    'Buku pelajaran biologi tingkat SMA dan awal kuliah. Membahas sel, genetika, evolusi, hingga ekologi dengan ilustrasi lengkap.',
    'https://covers.openlibrary.org/b/isbn/9780134093413-L.jpg'
),
(
    'Chemistry: The Central Science',
    'Theodore Brown dkk.',
    'Pearson',
    900000,
    8,
    'Buku pelajaran kimia tingkat SMA dan awal kuliah. Mencakup struktur atom, ikatan kimia, stoikiometri, dan reaksi kimia.',
    'https://covers.openlibrary.org/b/isbn/9780134414232-L.jpg'
),
(
    'Fundamentals of Physics',
    'David Halliday, Robert Resnick, Jearl Walker',
    'Wiley',
    850000,
    9,
    'Buku pelajaran fisika tingkat SMA lanjutan dan awal kuliah. Mulai dari mekanika, gelombang, listrik, hingga fisika modern.',
    'https://covers.openlibrary.org/b/isbn/9781118230718-L.jpg'
),
(
    'Concepts of Physics Vol. 1',
    'H.C. Verma',
    'Bharati Bhawan',
    180000,
    15,
    'Buku fisika SMA yang terkenal dengan penjelasan konsep dan soal latihan yang banyak, populer untuk persiapan olimpiade dan ujian masuk.',
    'https://covers.openlibrary.org/b/isbn/9788177091878-L.jpg'
),
(
    'English Grammar in Use',
    'Raymond Murphy',
    'Cambridge University Press',
    165000,
    25,
    'Buku tata bahasa Inggris tingkat menengah untuk siswa SMP dan SMA, lengkap dengan penjelasan ringkas dan latihan.',
    'https://covers.openlibrary.org/b/isbn/9781108457651-L.jpg'
),
(
    'Essential Grammar in Use',
    'Raymond Murphy',
    'Cambridge University Press',
    150000,
    25,
    'Buku tata bahasa Inggris tingkat dasar yang cocok untuk siswa SD akhir sampai SMP yang baru belajar bahasa Inggris.',
    'https://covers.openlibrary.org/b/isbn/9781107480551-L.jpg'
),
(
    'Oxford Advanced Learner''s Dictionary',
    'Oxford University Press',
    'Oxford University Press',
    280000,
    18,
    'Kamus bahasa Inggris lengkap untuk pelajar dengan contoh kalimat, pelafalan, dan penjelasan penggunaan kata.',
    'https://covers.openlibrary.org/b/isbn/9780194798792-L.jpg'
),
(
    'The Elements',
    'Theodore Gray',
    'Black Dog and Leventhal',
    250000,
    10,
    'Buku bergambar tentang seluruh unsur dalam tabel periodik dengan foto yang menarik, cocok untuk pelajar SMP dan SMA yang suka kimia.',
    'https://covers.openlibrary.org/b/isbn/9781579128951-L.jpg'
),
(
    'Calculus',
    'James Stewart',
    'Cengage Learning',
    800000,
    7,
    'Buku kalkulus yang banyak dipakai di SMA lanjutan dan awal kuliah, membahas limit, turunan, integral, dan deret.',
    'https://covers.openlibrary.org/b/isbn/9781285740621-L.jpg'
),
(
    'Principles of Economics',
    'N. Gregory Mankiw',
    'Cengage Learning',
    700000,
    9,
    'Pengantar ilmu ekonomi untuk pelajar SMA dan mahasiswa baru, membahas permintaan, penawaran, pasar, dan kebijakan ekonomi.',
    'https://covers.openlibrary.org/b/isbn/9781305585126-L.jpg'
),
(
    'The Study Quran',
    'Seyyed Hossein Nasr (ed.)',
    'HarperOne',
    650000,
    10,
    'Terjemahan dan tafsir Al-Quran dalam bahasa Inggris dengan catatan penjelasan dari berbagai tradisi tafsir klasik.',
    'https://covers.openlibrary.org/b/isbn/9780062227621-L.jpg'
),
(
    'Muhammad: His Life Based on the Earliest Sources',
    'Martin Lings',
    'Inner Traditions',
    250000,
    14,
    'Biografi Nabi Muhammad SAW yang disusun dari sumber-sumber paling awal, dengan bahasa yang mengalir dan mudah dibaca.',
    'https://covers.openlibrary.org/b/isbn/9781594771538-L.jpg'
),
(
    'The Bhagavad Gita',
    'Eknath Easwaran',
    'Nilgiri Press',
    160000,
    16,
    'Terjemahan kitab suci Hindu tentang dialog antara Arjuna dan Krishna di medan perang Kurukshetra, tentang kewajiban dan jalan spiritual.',
    'https://covers.openlibrary.org/b/isbn/9781586380199-L.jpg'
),
(
    'The Heart of the Buddha''s Teaching',
    'Thich Nhat Hanh',
    'Harmony',
    190000,
    13,
    'Pengantar ajaran inti Buddha seperti Empat Kebenaran Mulia dan Jalan Berunsur Delapan, dijelaskan dengan bahasa sederhana.',
    'https://covers.openlibrary.org/b/isbn/9780767903691-L.jpg'
),
(
    'Mere Christianity',
    'C.S. Lewis',
    'HarperOne',
    130000,
    17,
    'Penjelasan dasar-dasar iman Kristen oleh C.S. Lewis dengan logika yang jernih, berdasarkan siaran radionya pada masa perang.',
    'https://covers.openlibrary.org/b/isbn/9780060652920-L.jpg'
);

INSERT INTO buku (judul, penulis, penerbit, harga, stok, deskripsi, cover) VALUES
(
    'The Little Prince',
    'Antoine de Saint-Exupery',
    'Harcourt',
    85000,
    26,
    'Dongeng klasik tentang seorang pangeran kecil dari asteroid yang berkelana ke berbagai planet dan belajar tentang persahabatan, cinta, dan hal-hal yang benar-benar penting.',
    'https://covers.openlibrary.org/b/isbn/9780156012195-L.jpg'
),
(
    'Charlotte''s Web',
    'E.B. White',
    'HarperCollins',
    95000,
    24,
    'Kisah persahabatan antara seekor babi bernama Wilbur dan laba-laba pintar bernama Charlotte yang berusaha menyelamatkan nyawanya.',
    'https://covers.openlibrary.org/b/isbn/9780064400558-L.jpg'
),
(
    'The Lion, the Witch and the Wardrobe',
    'C.S. Lewis',
    'HarperCollins',
    100000,
    22,
    'Empat bersaudara menemukan lemari ajaib yang membawa mereka ke negeri Narnia, tempat singa Aslan melawan Penyihir Putih.',
    'https://covers.openlibrary.org/b/isbn/9780064471046-L.jpg'
),
(
    'Matilda',
    'Roald Dahl',
    'Puffin Books',
    85000,
    28,
    'Matilda, gadis kecil yang jenius dan punya kekuatan istimewa, melawan kepala sekolah yang galak dan orang tua yang cuek.',
    'https://covers.openlibrary.org/b/isbn/9780142410370-L.jpg'
),
(
    'Charlie and the Chocolate Factory',
    'Roald Dahl',
    'Puffin Books',
    85000,
    30,
    'Charlie Bucket memenangkan tiket emas untuk berkeliling pabrik cokelat ajaib milik Willy Wonka yang penuh kejutan.',
    'https://covers.openlibrary.org/b/isbn/9780142410318-L.jpg'
),
(
    'Diary of a Wimpy Kid',
    'Jeff Kinney',
    'Amulet Books',
    110000,
    35,
    'Catatan harian lucu Greg Heffley, anak SMP yang ingin populer tapi selalu saja ada kejadian konyol yang menimpanya.',
    'https://covers.openlibrary.org/b/isbn/9780810993136-L.jpg'
),
(
    'Wonder',
    'R.J. Palacio',
    'Alfred A. Knopf',
    120000,
    20,
    'Auggie, anak berwajah tidak biasa, masuk sekolah umum untuk pertama kalinya. Kisah hangat tentang persahabatan, keberanian, dan kebaikan.',
    'https://covers.openlibrary.org/b/isbn/9780375869020-L.jpg'
);

-- ============================================
-- 4. Akun contoh (password sudah di-hash bcrypt)
--    Admin     : admin@bukuku.test / admin123
--    Pelanggan : budi@bukuku.test  / user123
--    INSERT IGNORE agar import ulang tidak error duplikat.
-- ============================================
INSERT IGNORE INTO users (nama, email, password, role) VALUES
('Admin BukuKu', 'admin@bukuku.test', '$2y$10$AdwFFHLktZ11s/kyNKZi0.rLuCboqmhxrrFknVG60iz3qFMdbMMPW', 'admin'),
('Budi Santoso', 'budi@bukuku.test', '$2y$10$/Vb9/3NJ8RWI2SZ2dQPSiOuCr2xVI2Ehl7gBO2EJDqmU88FTQVX4W', 'user');

