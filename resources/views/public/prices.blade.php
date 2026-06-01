@extends('public.layout')

@section('page-styles')
    <link rel="stylesheet" href="{{ asset('css/public/styles/prices.css') }}?v={{ is_file(public_path('css/public/styles/prices.css')) ? filemtime(public_path('css/public/styles/prices.css')) : time() }}">
@endsection

@section('content')
<main>
    <section class="section reveal">
        <div class="container">
            <div class="section-head">
                <h2>Daftar Harga</h2>
                <p>Harga berbeda sesuai jenis layanan, ukuran, bahan, dan jumlah pesanan.</p>
            </div>

            <article class="panel price-panel">
                <div class="table-scroll">
                    <table class="price-table">
                        <thead>
                            <tr>
                                <th>Jenis Layanan</th>
                                <th>Ukuran / Satuan</th>
                                <th>Harga Mulai</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($priceItems as $item)
                                <tr>
                                    <td>{{ $item['nama_layanan'] }}</td>
                                    <td>{{ $item['info_ukuran'] }}</td>
                                    <td>{{ $item['teks_harga'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </article>

            <div class="price-cta">
                <a class="btn btn-primary" href="https://wa.me/{{ $contactPhoneInternational }}?text={{ rawurlencode('Halo, saya ingin tanya daftar harga Karunya Printing') }}" target="_blank" rel="noopener">Konsultasi Harga via WhatsApp</a>
            </div>
        </div>
    </section>
</main>
@endsection
