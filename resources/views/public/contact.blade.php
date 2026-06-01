@extends('public.layout')

@section('page-styles')
    <link rel="stylesheet" href="{{ asset('css/public/styles/contact.css') }}?v={{ is_file(public_path('css/public/styles/contact.css')) ? filemtime(public_path('css/public/styles/contact.css')) : time() }}">
@endsection

@section('content')
<main>
    <section class="section reveal">
        <div class="container contact-grid">
            <div>
                <div class="section-head left">
                    <h2>Kontak</h2>
                </div>
                <article class="panel bg-cmyk-cyan">
                    <p><strong>Alamat:</strong><br>{{ $contactAddress }}</p>
                    <p><strong>WhatsApp:</strong><br><a href="{{ $whatsAppUrl }}" target="_blank" rel="noopener">{{ $contactPhone }}</a></p>
                    <p><strong>Email:</strong><br><a href="mailto:{{ $contactEmail }}">{{ $contactEmail }}</a></p>
                </article>
            </div>
        </div>
    </section>
</main>
@endsection
