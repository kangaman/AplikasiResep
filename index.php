<?php
// Mulai sesi untuk memeriksa status login
session_start();

// Jika pengguna sudah login, langsung arahkan ke dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catatan Rasa Digital - Solusi Modern Manajemen Resep Kuliner</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Epilogue:wght@400;500;700;900&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: { 
                        "primary": "#ec7f13", 
                        "primary-dark": "#d4710f",
                        "background-light": "#f8f7f6", 
                        "background-dark": "#121212", 
                        "content-light": "#181411", 
                        "content-dark": "#f8f7f6",
                        "subtle-light": "#ffffff",
                        "subtle-dark": "#221910"
                    },
                    fontFamily: { "display": ["Epilogue", "sans-serif"] },
                },
            },
        }
    </script>
    <style>
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 300, 'GRAD' 0, 'opsz' 48; }
    </style>
</head>
<body class="bg-background-light dark:bg-background-dark font-display text-content-light dark:text-content-dark">

<header class="sticky top-0 z-20 bg-background-light/80 dark:bg-background-dark/80 backdrop-blur-sm">
    <nav class="container mx-auto px-6 py-4 flex justify-between items-center">
        <a href="index.php" class="text-xl font-bold text-primary">
            Catatan Rasa Digital
        </a>
        <div class="flex items-center gap-4">
            <a href="login.php" class="text-sm font-medium text-gray-600 dark:text-gray-300 hover:text-primary">Login</a>
            <a href="registrasi.php" class="text-sm font-bold bg-primary text-white px-5 py-2.5 rounded-full hover:bg-primary-dark shadow-lg transition-colors">Daftar Gratis</a>
        </div>
    </nav>
</header>

<main>
    <section class="relative text-center py-24 md:py-32 px-6 bg-gray-900 text-white overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-t from-black via-transparent to-transparent"></div>
        <div class="absolute inset-0">
            <img src="uploads/resep_68c25f9e0d2282.48874403.jpg" alt="Latar belakang aneka kue" class="w-full h-full object-cover opacity-20">
        </div>
        <div class="relative z-10">
            <h1 class="text-4xl md:text-6xl font-black leading-tight tracking-tight mb-4 text-shadow-lg">
                Dari Ide Jadi Cuan.
            </h1>
            <p class="max-w-3xl mx-auto text-lg md:text-xl text-gray-300 mb-8">
                Ubah resep andalan Anda menjadi bisnis yang menguntungkan. Atur bahan, hitung modal, dan rencanakan produksi dengan presisi.
            </p>
            <a href="registrasi.php" class="text-base font-bold bg-primary text-white px-8 py-4 rounded-full hover:bg-primary-dark shadow-xl transition-transform transform hover:scale-105 inline-block">
                Mulai Kelola Resep Anda
            </a>
        </div>
    </section>

    <section id="features" class="py-20 bg-background-light dark:bg-subtle-dark">
        <div class="container mx-auto px-6">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold">Semua Alat untuk Sukses</h2>
                <p class="text-gray-600 dark:text-gray-400 mt-3 max-w-2xl mx-auto">Dari dapur rumah hingga skala bisnis, fitur kami dirancang untuk menyederhanakan setiap langkah Anda.</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <div class="bg-subtle-light dark:bg-background-dark p-8 rounded-xl shadow-lg border border-gray-200 dark:border-gray-800 text-center transition-transform transform hover:-translate-y-2">
                    <span class="material-symbols-outlined text-5xl text-primary">menu_book</span>
                    <h3 class="text-xl font-bold mt-4 mb-2">Pusat Resep Digital</h3>
                    <p class="text-gray-600 dark:text-gray-400">Simpan semua resep Anda di satu tempat, lengkap dengan gambar, video, dan porsi standar.</p>
                </div>
                <div class="bg-subtle-light dark:bg-background-dark p-8 rounded-xl shadow-lg border border-gray-200 dark:border-gray-800 text-center transition-transform transform hover:-translate-y-2">
                    <span class="material-symbols-outlined text-5xl text-primary">calculate</span>
                    <h3 class="text-xl font-bold mt-4 mb-2">Kalkulator Modal Akurat</h3>
                    <p class="text-gray-600 dark:text-gray-400">Hitung biaya produksi per resep secara otomatis berdasarkan harga bahan terbaru Anda.</p>
                </div>
                <div class="bg-subtle-light dark:bg-background-dark p-8 rounded-xl shadow-lg border border-gray-200 dark:border-gray-800 text-center transition-transform transform hover:-translate-y-2">
                    <span class="material-symbols-outlined text-5xl text-primary">blender</span>
                    <h3 class="text-xl font-bold mt-4 mb-2">Bahan Olahan Kompleks</h3>
                    <p class="text-gray-600 dark:text-gray-400">Buat resep untuk komponen (seperti isian atau vla), hitung biayanya, dan gunakan di resep lain.</p>
                </div>
                <div class="bg-subtle-light dark:bg-background-dark p-8 rounded-xl shadow-lg border border-gray-200 dark:border-gray-800 text-center transition-transform transform hover:-translate-y-2">
                    <span class="material-symbols-outlined text-5xl text-primary">shopping_cart</span>
                    <h3 class="text-xl font-bold mt-4 mb-2">Daftar Belanja Cerdas</h3>
                    <p class="text-gray-600 dark:text-gray-400">Pilih resep yang akan diproduksi dan dapatkan daftar belanja total kebutuhan bahan secara instan.</p>
                </div>
            </div>
        </div>
    </section>

    <section id="how-it-works" class="py-20 bg-background-light dark:bg-background-dark">
        <div class="container mx-auto px-6 text-center">
            <h2 class="text-3xl md:text-4xl font-bold mb-12">Mulai dalam 3 Langkah Mudah</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-12 relative">
                <div class="hidden md:block absolute top-1/2 left-0 w-full h-px bg-gray-300 dark:bg-gray-700" style="transform: translateY(-50%);"></div>
                
                <div class="relative z-10 flex flex-col items-center">
                    <div class="bg-primary text-white w-16 h-16 rounded-full flex items-center justify-center text-2xl font-bold shadow-lg mb-4">1</div>
                    <h3 class="text-xl font-bold mb-2">Input Bahan Dasar</h3>
                    <p class="text-gray-600 dark:text-gray-400">Masukkan semua bahan baku Anda beserta harga beli di menu Manajemen Bahan.</p>
                </div>
                <div class="relative z-10 flex flex-col items-center">
                    <div class="bg-primary text-white w-16 h-16 rounded-full flex items-center justify-center text-2xl font-bold shadow-lg mb-4">2</div>
                    <h3 class="text-xl font-bold mb-2">Buat Resep Anda</h3>
                    <p class="text-gray-600 dark:text-gray-400">Tulis resep andalan Anda. Sistem akan otomatis menghitung modal per resep.</p>
                </div>
                <div class="relative z-10 flex flex-col items-center">
                    <div class="bg-primary text-white w-16 h-16 rounded-full flex items-center justify-center text-2xl font-bold shadow-lg mb-4">3</div>
                    <h3 class="text-xl font-bold mb-2">Siap Produksi</h3>
                    <p class="text-gray-600 dark:text-gray-400">Hitung ulang porsi sesuai pesanan dan buat daftar belanja hanya dengan satu klik.</p>
                </div>
            </div>
        </div>
    </section>

    <section id="testimonials" class="py-20 bg-background-light dark:bg-subtle-dark">
        <div class="container mx-auto px-6">
            <h2 class="text-3xl md:text-4xl font-bold text-center mb-12">Dipercaya oleh Pebisnis Kuliner</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <div class="bg-subtle-light dark:bg-background-dark p-8 rounded-xl shadow-lg border border-gray-200 dark:border-gray-800">
                    <p class="text-gray-700 dark:text-gray-300 mb-6">"Aplikasi ini mengubah cara saya menghitung HPP. Semua jadi transparan dan cepat. Fitur kalkulator bahan olahan sangat membantu untuk produk kue saya yang kompleks."</p>
                    <div class="flex items-center">
                        <div>
                            <p class="font-bold">Ibu Rina</p>
                            <p class="text-sm text-primary">Pemilik Rina's Cake</p>
                        </div>
                    </div>
                </div>
                <div class="bg-subtle-light dark:bg-background-dark p-8 rounded-xl shadow-lg border border-gray-200 dark:border-gray-800">
                    <p class="text-gray-700 dark:text-gray-300 mb-6">"Dulu selalu pusing membuat daftar belanja untuk pesanan besar. Sekarang tinggal klik, semua bahan langsung terakumulasi. Sangat menghemat waktu!"</p>
                    <div class="flex items-center">
                        <div>
                            <p class="font-bold">Bapak Andi</p>
                            <p class="text-sm text-primary">Pengusaha Katering</p>
                        </div>
                    </div>
                </div>
                <div class="bg-subtle-light dark:bg-background-dark p-8 rounded-xl shadow-lg border border-gray-200 dark:border-gray-800">
                    <p class="text-gray-700 dark:text-gray-300 mb-6">"Sebagai pemula di hobi baking, aplikasi ini membantu saya jadi lebih terorganisir. Semua resep tersimpan rapi dan saya bisa tahu persis biaya setiap kue yang saya buat."</p>
                    <div class="flex items-center">
                        <div>
                            <p class="font-bold">Sarah</p>
                            <p class="text-sm text-primary">Home Baker</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <section class="bg-primary">
        <div class="container mx-auto px-6 py-16 text-center text-white">
            <h2 class="text-3xl font-bold">Siap Mengoptimalkan Dapur Anda?</h2>
            <p class="mt-2 mb-6">Daftar sekarang dan rasakan kemudahan mengelola resep dan biaya.</p>
            <a href="registrasi.php" class="text-base font-bold bg-white text-primary px-8 py-4 rounded-full hover:bg-gray-200 shadow-xl transition-colors">
                Buat Akun Gratis
            </a>
        </div>
    </section>

</main>

<footer class="bg-subtle-dark dark:bg-black text-center py-6">
    <div class="container mx-auto px-6">
        <p class="text-gray-400">&copy; <?php echo date("Y"); ?> - Catatan Rasa Digital</p>
    </div>
</footer>

</body>
</html>