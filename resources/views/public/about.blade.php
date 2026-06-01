@extends('public.layout')

@section('page-styles')
    <link rel="stylesheet" href="{{ asset('css/public/styles/about.css') }}?v={{ is_file(public_path('css/public/styles/about.css')) ? filemtime(public_path('css/public/styles/about.css')) : time() }}">
@endsection

@section('content')
<main>
    <section id="tentang" class="section reveal home-about-section">
        <div class="container">
            <div class="section-head">
                <span class="section-kicker">Tentang Kami</span>
                <h2>Tempat cetak yang sederhana, jelas, dan siap membantu dari awal.</h2>
                <p>Karunya Printing tumbuh dari kebutuhan cetak harian pelanggan sekitar, lalu berkembang menjadi partner untuk promosi usaha, acara, dan kebutuhan operasional.</p>
            </div>

            <div class="home-about-grid" style="grid-template-columns: 1fr;">
                <article class="panel home-story-card bg-cmyk-cyan">
                    <span class="home-card-kicker">Cerita Singkat</span>
                    <h3>Berdiri sejak September 2016</h3>
                    <p>Karunya Printing berawal dari usaha kecil yang melayani kebutuhan cetak harian masyarakat sekitar. Pada awalnya, fokus kami hanya pada cetak dokumen sederhana seperti fotokopi, print warna, dan penjilidan. Namun, seiring bertambahnya kepercayaan pelanggan, kami mulai menerima pesanan yang lebih beragam — mulai dari brosur, banner, stiker, hingga undangan.</p>
                    <p>Di tengah persaingan usaha percetakan yang semakin ketat, kami memilih untuk tidak sekadar menawarkan harga terendah. Kami percaya bahwa kepuasan pelanggan datang dari kombinasi hasil cetak yang rapi, komunikasi yang transparan, dan timeline yang realistis. Setiap pesanan, sekecil apapun, dikerjakan dengan perhatian penuh karena reputasi kami dibangun dari hasil kerja satu per satu.</p>
                    <p>Hari ini, Karunya Printing dipercaya untuk berbagai kebutuhan promosi usaha, acara, dan layanan cetak praktis yang dipakai setiap hari. Dari pelaku usaha mikro yang baru memulai, hingga organisasi yang mengadakan acara besar — kami siap membantu mereka mengekspresikan ide dan visi melalui media cetak yang berkualitas.</p>
                </article>
            </div>

            <div class="home-about-grid" style="grid-template-columns: 1fr;">
                <article class="panel home-detail-card bg-cmyk-magenta">
                    <span class="home-card-kicker">Visi</span>
                    <h3>Andalan yang cepat dan terjangkau</h3>
                    <p>Menjadi percetakan yang konsisten dalam kualitas hasil, mudah dihubungi, dan nyaman diajak konsultasi.</p>
                </article>
            </div>

            <div class="home-about-grid" style="grid-template-columns: 1fr;">
                <article class="panel home-detail-card bg-cmyk-yellow">
                    <span class="home-card-kicker">Misi</span>
                    <h3>Komitmen utama kami</h3>
                    <ul class="home-checklist">
                        <li>Memberikan hasil cetak terbaik dengan harga yang tetap masuk akal.</li>
                        <li>Memproses pesanan dengan alur yang jelas dan waktu yang realistis.</li>
                        <li>Mendampingi pelanggan dari kebutuhan awal sampai produksi selesai.</li>
                    </ul>
                </article>
            </div>

            <div class="home-about-grid" style="grid-template-columns: 1fr; margin-top: 1.5rem;">
                <article class="panel home-photo-card bg-cmyk-black">
                    @php
                        // Like logo: fixed static file from filesystem (not editable by admin).
                        $aboutSrc = asset('gambar/tempat.jpeg');
                    @endphp
                    <img src="{{ $aboutSrc }}" alt="Foto tempat Karunya Printing">
                    <div class="home-photo-copy">
                        <span class="home-card-kicker">Tempat Usaha</span>
                        <h3>Karunya Printing</h3>
                        <p>Lokasi mudah dijangkau untuk konsultasi, revisi kebutuhan, dan pengambilan pesanan.</p>
                    </div>
                </article>
            </div>
        </div>
    </section>
</main>
@endsection
