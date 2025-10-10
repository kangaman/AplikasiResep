<?php
  // Catatan Rasa Pro — Landing Page (SEO Optimized)
?><!DOCTYPE html>
<html lang="id" prefix="og: http://ogp.me/ns#">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Catatan Rasa Pro — Kelola HPP &amp; Resep Kuliner</title>

  <link rel="canonical" href="https://saefulbahri.web.id/resep/">
  <meta name="robots" content="index,follow,max-snippet:-1,max-image-preview:large,max-video-preview:-1">
  <meta name="theme-color" content="#FF914D">
  <meta name="title" content="Catatan Rasa Pro — Kelola HPP &amp; Resep Kuliner">
  <meta name="description" content="Aplikasi profesional untuk menghitung HPP, mengelola resep, dan menentukan harga jual usaha kuliner Anda. Dirancang untuk UMKM dan pelaku bisnis kuliner Indonesia.">
  <meta name="keywords" content="Catatan Rasa Pro, resep digital, HPP kuliner, aplikasi resep, snack box, kalkulator usaha kuliner, manajemen resep, kuliner Indonesia">
  <meta property="og:locale" content="id_ID">
  <meta property="og:type" content="website">
  <meta property="og:url" content="https://saefulbahri.web.id/resep/">
  <meta property="og:title" content="Catatan Rasa Pro — Kelola HPP &amp; Resep Kuliner">
  <meta property="og:description" content="Aplikasi profesional untuk menghitung HPP, mengelola resep, dan menentukan harga jual usaha kuliner Anda.">
  <meta property="og:image" content="https://saefulbahri.web.id/resep/assets/og-cover-catatanrasa.jpg">
  <meta property="og:image:width" content="1200">
  <meta property="og:image:height" content="630">
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:url" content="https://saefulbahri.web.id/resep/">
  <meta name="twitter:title" content="Catatan Rasa Pro — Kelola HPP &amp; Resep Kuliner">
  <meta name="twitter:description" content="Kelola bahan, resep, dan harga jual usaha kuliner Anda dengan mudah dan akurat.">
  <meta name="twitter:image" content="https://saefulbahri.web.id/resep/assets/og-cover-catatanrasa.jpg">
  <link rel="icon" type="image/png" sizes="32x32" href="./assets/favicon-32.png">
  <link rel="apple-touch-icon" sizes="180x180" href="./assets/apple-touch-icon.png">
  <link rel="icon" href="./assets/favicon.ico">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&amp;display=swap" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          fontFamily: { sans: ['Inter', 'ui-sans-serif', 'system-ui'] },
          colors: {
            brand: {50:'#FFF7F0',100:'#FFE9D6',200:'#FFD9B8',300:'#FFC38A',400:'#FFA65C',500:'#FF914D',600:'#E6763B',700:'#C25B2C',800:'#8A3F1E',900:'#5C2A15'},
            ink: '#003366'
          },
          boxShadow: { soft: '0 8px 24px rgba(0,0,0,0.08)' }
        }
      }
    }
  </script>
  <style>
    .hero-grad {
      background: radial-gradient(1200px 600px at 10% 0%, rgba(255,255,255,.25) 0%, rgba(255,255,255,0) 55%),
                  linear-gradient(135deg, #FF914D 0%, #FFD280 100%);
    }
    .badge-glass { backdrop-filter: blur(8px); background: rgba(255,255,255,.25); border: 1px solid rgba(255,255,255,.4); }
  </style>
</head>
<body class="font-sans antialiased text-slate-800 bg-white">
  <header class="absolute inset-x-0 top-0 z-50">
    <div class="max-w-7xl mx-auto px-6">
      <div class="flex items-center justify-between py-5">
        <a href="https://saefulbahri.web.id/resep/" class="flex items-center gap-3 font-extrabold tracking-tight text-white" aria-label="Beranda Catatan Rasa Pro">
          <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-white/20 ring-1 ring-white/40 overflow-hidden">
            <img src="./logo-catatan-rasa-pro.svg" alt="Logo Catatan Rasa Pro" class="w-10 h-10 object-contain" loading="eager" fetchpriority="high">
          </span>
          <span>Catatan Rasa <span class="opacity-90">Pro</span></span>
        </a>
        <nav class="hidden md:flex items-center gap-6 text-white/90" aria-label="Navigasi utama">
          <a href="#fitur" class="hover:text-white">Fitur</a>
          <a href="#untuk-siapa" class="hover:text-white">Untuk Siapa</a>
          <a href="#cara-kerja" class="hover:text-white">Cara Kerja</a>
          <a href="#alasan" class="hover:text-white">Kenapa</a>
        </nav>
        <div class="hidden md:flex items-center gap-3">
          <a href="https://saefulbahri.web.id/resep/login.php" class="px-4 py-2 rounded-lg bg-white/20 text-white hover:bg-white/30 transition" aria-label="Masuk ke Catatan Rasa Pro">Login</a>
          <a href="https://saefulbahri.web.id/resep/registrasi.php" class="px-4 py-2 rounded-lg bg-white text-ink font-semibold hover:bg-slate-100 transition" aria-label="Daftar Catatan Rasa Pro">Mulai Gratis</a>
        </div>
      </div>
    </div>
  </header>

  <section class="hero-grad text-white pt-28 md:pt-36 pb-20">
    <div class="max-w-7xl mx-auto px-6">
      <div class="max-w-3xl">
        <div class="inline-flex items-center gap-2 badge-glass text-white/90 px-3 py-1.5 rounded-full text-sm mb-6">
          <span class="inline-block w-2 h-2 rounded-full bg-white/80"></span>
          Solusi Lengkap Untuk Usaha Kuliner
        </div>
        <h1 class="text-4xl md:text-6xl font-extrabold leading-tight">
          Kelola HPP &amp; Resep Kuliner <span class="block text-white/90">Dengan Mudah</span>
        </h1>
        <p class="mt-4 text-lg md:text-xl text-white/90 max-w-2xl">
          Platform lengkap untuk menghitung harga pokok produksi, mengelola resep, dan merencanakan produksi usaha kuliner Anda.
        </p>
        <div class="mt-8 flex flex-col sm:flex-row gap-3">
          <a href="https://saefulbahri.web.id/resep/registrasi.php" class="inline-flex items-center justify-center px-5 py-3 rounded-xl bg-white text-ink font-semibold shadow-soft hover:-translate-y-0.5 transition">
            Mulai Gratis
            <svg class="ml-2" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path d="M5 12h14M13 5l7 7-7 7" stroke-width="1.8"/></svg>
          </a>
          <a href="https://saefulbahri.web.id/resep/login.php" class="inline-flex items-center justify-center px-5 py-3 rounded-xl ring-1 ring-white/60 text-white hover:bg-white/10 transition">
            Login
          </a>
        </div>
      </div>
    </div>
  </section>

  <section id="untuk-siapa" class="py-16 md:py-20 bg-brand-50">
    <div class="max-w-7xl mx-auto px-6">
      <div class="text-center max-w-3xl mx-auto mb-12">
        <h2 class="text-3xl md:text-4xl font-bold text-slate-900">Untuk Siapa Catatan Rasa Pro Dibuat</h2>
        <p class="text-slate-600 mt-3">Dirancang untuk pelaku usaha kuliner yang ingin mengelola bahan, resep, dan harga jual secara efisien — tanpa repot hitung manual.</p>
      </div>
      <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
        <article class="bg-white rounded-2xl p-6 shadow-soft">
          <div class="text-4xl mb-3" aria-hidden="true">🧁</div>
          <h3 class="font-semibold text-lg mb-1">Pemilik Usaha Kue &amp; Snack Rumahan</h3>
          <p class="text-slate-600 text-sm">Kelola bahan, resep, dan harga jual tiap produk. Ketahui keuntungan bersih per produk secara instan.</p>
          <p class="text-slate-500 text-xs mt-3 italic">Contoh: Bu Sari menghitung HPP brownies &amp; menetapkan margin 30%.</p>
        </article>
        <article class="bg-white rounded-2xl p-6 shadow-soft">
          <div class="text-4xl mb-3" aria-hidden="true">🍱</div>
          <h3 class="font-semibold text-lg mb-1">Katering &amp; Usaha Snack Box</h3>
          <p class="text-slate-600 text-sm">Hitung kebutuhan bahan otomatis untuk ratusan paket &amp; buat daftar belanja sekali klik.</p>
          <p class="text-slate-500 text-xs mt-3 italic">Contoh: 100 paket → sistem hitung total bahan mentah.</p>
        </article>
        <article class="bg-white rounded-2xl p-6 shadow-soft">
          <div class="text-4xl mb-3" aria-hidden="true">👩‍🍳</div>
          <h3 class="font-semibold text-lg mb-1">Chef &amp; Pelatih Kursus Masak</h3>
          <p class="text-slate-600 text-sm">Simpan resep dengan HPP per porsi. Ideal untuk dokumentasi kelas &amp; simulasi biaya.</p>
          <p class="text-slate-500 text-xs mt-3 italic">Contoh: Chef Nia mempresentasikan HPP real kepada peserta.</p>
        </article>
        <article class="bg-white rounded-2xl p-6 shadow-soft">
          <div class="text-4xl mb-3" aria-hidden="true">🧾</div>
          <h3 class="font-semibold text-lg mb-1">Pemilik Kafe &amp; UMKM Kuliner</h3>
          <p class="text-slate-600 text-sm">Analisis profit per menu, sesuaikan harga saat bahan naik, dan pantau laba usaha.</p>
          <p class="text-slate-500 text-xs mt-3 italic">Contoh: Kafe memantau menu paling menguntungkan.</p>
        </article>
      </div>
    </div>
  </section>

  <section id="fitur" class="py-16 md:py-20">
      <div class="max-w-7xl mx-auto px-6">
        <div class="text-center max-w-3xl mx-auto mb-12">
          <h2 class="text-3xl md:text-4xl font-bold text-slate-900">Fitur Lengkap untuk Bisnis Kuliner Anda</h2>
          <p class="text-slate-600 mt-3">Semua yang Anda butuhkan untuk mengelola keuangan dan produksi usaha kuliner.</p>
        </div>
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php
            $features = [
                ['title' => 'Hitung HPP Otomatis', 'desc' => 'Kalkulasi harga pokok produksi per porsi akurat & real-time.'],
                ['title' => 'Pusat Resep Digital', 'desc' => 'Kelola resep & bahan olahan yang dapat digunakan ulang.'],
                ['title' => 'Kalkulator Snack Box', 'desc' => 'Rakit hampers & hitung total biaya dengan mudah.'],
                ['title' => 'Daftar Belanja Cerdas', 'desc' => 'Generate kebutuhan bahan otomatis berdasarkan target produksi.'],
                ['title' => 'Penetapan Harga Jual', 'desc' => 'Tentukan harga jual optimal dengan margin & markup.'],
                ['title' => 'Riwayat Harga Bahan', 'desc' => 'Pantau perubahan harga bahan & dampaknya ke resep.'],
            ];
            foreach ($features as $f):
            ?>
            <article class="bg-white rounded-2xl p-6 shadow-soft border border-slate-100">
                <div class="w-10 h-10 rounded-lg bg-brand-100 flex items-center justify-center mb-3">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#FF914D" aria-hidden="true"><path d="M12 20V10M6 20V14M18 20V6" stroke-width="1.8"/></svg>
                </div>
                <h3 class="font-semibold text-lg mb-1"><?php echo htmlspecialchars($f['title']); ?></h3>
                <p class="text-slate-600 text-sm"><?php echo htmlspecialchars($f['desc']); ?></p>
            </article>
            <?php endforeach; ?>
        </div>
      </div>
    </section>

  <section id="cara-kerja" class="py-16 md:py-20 bg-slate-50">
    <div class="max-w-7xl mx-auto px-6">
      <div class="text-center max-w-3xl mx-auto mb-12">
        <h2 class="text-3xl md:text-4xl font-bold text-slate-900">Cara Kerja yang Sederhana</h2>
        <p class="text-slate-600 mt-3">Hanya 3 langkah untuk mulai mengelola usaha kuliner Anda.</p>
      </div>
      <div class="grid md:grid-cols-3 gap-6">
        <article class="bg-white rounded-2xl p-6 shadow-soft">
          <div class="text-brand-600 font-extrabold text-2xl">1</div>
          <h3 class="font-semibold mt-2">Input Harga Bahan</h3>
          <p class="text-slate-600 text-sm mt-1">Masukkan daftar bahan baku beserta harga per satuan dan faktor susut (opsional).</p>
        </article>
        <article class="bg-white rounded-2xl p-6 shadow-soft">
          <div class="text-brand-600 font-extrabold text-2xl">2</div>
          <h3 class="font-semibold mt-2">Buat Resep &amp; Paket</h3>
          <p class="text-slate-600 text-sm mt-1">Susun resep, gunakan bahan olahan, dan rakit snack box dengan mudah.</p>
        </article>
        <article class="bg-white rounded-2xl p-6 shadow-soft">
          <div class="text-brand-600 font-extrabold text-2xl">3</div>
          <h3 class="font-semibold mt-2">Tentukan Harga Jual</h3>
          <p class="text-slate-600 text-sm mt-1">Dapatkan rekomendasi harga berdasarkan margin, overhead, dan pajak.</p>
        </article>
      </div>
    </div>
  </section>

  <section id="alasan" class="py-16 md:py-20">
    <div class="max-w-7xl mx-auto px-6">
      <div class="grid lg:grid-cols-2 gap-10 items-start">
        <div>
          <h2 class="text-3xl md:text-4xl font-bold text-slate-900">Kenapa Pilih Catatan Rasa Pro?</h2>
          <ul class="mt-6 space-y-3 text-slate-700">
            <li class="flex gap-3"><span class="text-brand-600" aria-hidden="true">✔</span> Hemat waktu menghitung HPP dan harga jual.</li>
            <li class="flex gap-3"><span class="text-brand-600" aria-hidden="true">✔</span> Akurat dalam menentukan keuntungan tiap produk.</li>
            <li class="flex gap-3"><span class="text-brand-600" aria-hidden="true">✔</span> Mudah merencanakan produksi &amp; belanja bahan.</li>
            <li class="flex gap-3"><span class="text-brand-600" aria-hidden="true">✔</span> Data aman &amp; dapat diakses kapan saja.</li>
            <li class="flex gap-3"><span class="text-brand-600" aria-hidden="true">✔</span> Antarmuka sederhana — tanpa perlu training.</li>
          </ul>
        </div>
        <aside class="bg-white rounded-2xl p-6 shadow-soft border border-slate-100">
          <div class="grid grid-cols-3 gap-4 text-center">
            <div>
              <div class="text-2xl font-extrabold text-ink">156+</div>
              <div class="text-xs text-slate-500">Total Resep</div>
            </div>
            <div>
              <div class="text-2xl font-extrabold text-ink">75%</div>
              <div class="text-xs text-slate-500">Penghematan Waktu</div>
            </div>
            <div>
              <div class="text-2xl font-extrabold text-ink">99.9%</div>
              <div class="text-xs text-slate-500">Akurasi HPP</div>
            </div>
          </div>
        </aside>
      </div>
    </div>
  </section>

  <section class="hero-grad py-16 md:py-20 text-white">
    <div class="max-w-7xl mx-auto px-6 text-center">
      <h2 class="text-3xl md:text-4xl font-extrabold">Siap Mengelola Bisnis Kuliner Anda dengan Lebih Baik?</h2>
      <p class="text-white/90 mt-3">Bergabunglah dengan pelaku usaha kuliner yang sudah merasakan manfaatnya.</p>
      <div class="mt-8">
        <a href="https://saefulbahri.web.id/resep/registrasi.php" class="inline-flex items-center justify-center px-6 py-3 rounded-xl bg-white text-ink font-semibold shadow-soft hover:-translate-y-0.5 transition">Daftar Sekarang — Gratis</a>
      </div>
    </div>
  </section>

  <footer class="bg-white py-8 border-t border-slate-200">
    <div class="max-w-7xl mx-auto px-6 text-center text-slate-600">
        <div class="mb-4 flex justify-center gap-4">
            <a href="faq.php" class="text-sm hover:text-primary">FAQ</a>
            <a href="privasi.php" class="text-sm hover:text-primary">Kebijakan Privasi</a>
            <a href="ketentuan.php" class="text-sm hover:text-primary">Ketentuan Layanan</a>
        </div>
        <p class="text-sm">&copy; <?php echo date('Y'); ?> Catatan Rasa Pro. Semua hak dilindungi.</p>
    </div>
  </footer>
</body>
</html>
