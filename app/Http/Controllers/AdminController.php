<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\GalleryItem;
use App\Models\HomeSlide;
use App\Models\PriceItem;
use App\Models\Service;
use App\Models\SiteActivityLog;

use App\Support\EmailVerifier;
use App\Support\LegacySite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use RuntimeException;

class AdminController extends Controller
{
    public function entry(Request $request)
    {
        if ($request->session()->has('admin_id')) {
            return redirect('/admin/dashboard.php');
        }
        $key = $request->query('key');
        return redirect($key ? '/admin/login.php?key=' . urlencode((string) $key) : '/admin/login.php');
    }

    public function login(Request $request)
    {
        $secretKey = (string) config('legacy.admin_access_key');
        if ($request->query('key') !== $secretKey) {
            return response()->view('admin.gate', [], 403);
        }

        if ($request->session()->has('admin_id')) {
            return redirect('/admin/dashboard.php');
        }
        if ($request->isMethod('post')) {
            $username = trim((string) $request->input('username', ''));
            $password = trim((string) $request->input('password', ''));
            if ($username === '' || $password === '') {
                return back()->withInput()->with('error', 'Username dan password wajib diisi.');
            }
            $admin = Admin::query()->where('nama_pengguna', $username)->first();
            if ($admin !== null) {
                $stored = (string) $admin->kata_sandi;
                $valid = $this->looksLikeHash($stored) ? password_verify($password, $stored) : hash_equals($stored, $password);
                if ($valid) {
                    if (! $this->looksLikeHash($stored)) {
                        $admin->kata_sandi = password_hash($password, PASSWORD_DEFAULT);
                        $admin->save();
                    }
                    $request->session()->put('admin_id', (int) $admin->getKey());
                    $request->session()->put('admin_name', (string) $admin->nama_lengkap);
                    return redirect('/admin/dashboard.php');
                }
            }
            return back()->withInput()->with('error', 'Username atau password salah.');
        }
        return view('admin.login', ['accessKey' => $secretKey]);
    }

    public function logout(Request $request)
    {
        $request->session()->forget(['admin_id', 'admin_name']);
        return redirect('/admin/login.php?key=' . config('legacy.admin_access_key'));
    }

    public function dashboard()
    {
        $activityBreakdown = SiteActivityLog::query()->select('kunci_peristiwa', DB::raw('COUNT(*) as total'))->where('waktu_dibuat', '>=', now()->subDays(7))->groupBy('kunci_peristiwa')->orderByDesc('total')->orderBy('kunci_peristiwa')->limit(6)->get();
        $recentActivities = SiteActivityLog::query()->latest('id_log_aktivitas_situs')->limit(15)->get();
        return view('admin.dashboard', $this->adminPageData([
            'pageTitle' => 'Dashboard',
            'pageSubtitle' => 'Ringkasan aktivitas dan konten website.',
            'currentPage' => 'dashboard.php',
            'galleryCount' => GalleryItem::count(),
            'priceCount' => PriceItem::count(),
            'homeSlidesCount' => HomeSlide::count(),
            'todayViews' => SiteActivityLog::query()->where('kunci_peristiwa', 'page_view')->whereDate('waktu_dibuat', now()->toDateString())->count(),
            'weeklyUniqueVisitors' => SiteActivityLog::query()->where('waktu_dibuat', '>=', now()->subDays(7))->where('alamat_ip', '<>', '')->distinct('alamat_ip')->count('alamat_ip'),
            'weeklyWhatsappClicks' => SiteActivityLog::query()->where('kunci_peristiwa', 'whatsapp_click')->where('waktu_dibuat', '>=', now()->subDays(7))->count(),
            'weeklyGalleryPreviews' => SiteActivityLog::query()->where('kunci_peristiwa', 'gallery_preview')->where('waktu_dibuat', '>=', now()->subDays(7))->count(),
            'latestGallery' => GalleryItem::query()->latest('waktu_diperbarui')->limit(5)->get(),
            'latestPrices' => PriceItem::query()->with('service')->latest('waktu_diperbarui')->limit(5)->get(),
            'latestHomeSlides' => HomeSlide::query()->latest('waktu_diperbarui')->limit(5)->get(),
            'activityBreakdown' => $activityBreakdown,
            'recentActivities' => $recentActivities,
        ]));
    }

    // ================== GALLERY ==================
    public function gallery(Request $request)
    {
        if ($request->isMethod('post')) {
            $action = (string) $request->input('action', '');
            $id = (int) $request->input('id', 0);
            if ($action === 'delete' && $id > 0) {
                $item = GalleryItem::find($id);
                if ($item !== null) {
                    LegacySite::deleteUploadedFile($item->lokasi_gambar);
                    $item->delete();
                }
                return redirect('/admin/gallery.php')->with('success', 'Item galeri berhasil dihapus.');
            }
        }
        $statusFilter = (string) $request->query('status', 'all');
        $serviceFilter = (int) $request->query('service', 0);
        if (! in_array($statusFilter, ['all', 'active', 'inactive'], true)) {
            $statusFilter = 'all';
        }
        $query = GalleryItem::query();
        if ($statusFilter !== 'all') {
            $query->where('aktif', $statusFilter === 'active');
        }
        if ($serviceFilter > 0) {
            $query->where('id_layanan', $serviceFilter);
        }
        return view('admin.gallery', $this->adminPageData([
            'pageTitle' => 'Kelola Galeri',
            'pageSubtitle' => 'Manajemen koleksi foto galeri.',
            'currentPage' => 'gallery.php',
            'galleryItems' => $query->orderBy('urutan_tampil')->orderBy('id_galeri_layanan')->get(),
            'galleryTotalCount' => GalleryItem::count(),
            'galleryActiveCount' => GalleryItem::where('aktif', true)->count(),
            'galleryInactiveCount' => GalleryItem::where('aktif', false)->count(),
            'services' => Service::query()->where('aktif', true)->orderBy('urutan_tampil')->get(),
            'statusFilter' => $statusFilter,
            'serviceFilter' => $serviceFilter,
        ]));
    }

    public function galleryForm(Request $request)
    {
        if ($request->isMethod('post')) {
            $action = (string) $request->input('action', '');
            $id = (int) $request->input('id', 0);
            if ($action === 'save') {
                $serviceId = (int) $request->input('service_id', 0);
                $currentImagePath = trim((string) $request->input('current_image_path', ''));
                
                // Validasi service_id
                $service = Service::find($serviceId);
                if ($service === null) {
                    return redirect('/admin/gallery-form.php')->with('error', 'Pilih layanan yang tersedia.');
                }
                
                $imagePath = $currentImagePath;
                if ($request->hasFile('image_file')) {
                    try {
                        $imagePath = LegacySite::storeUploadedImage($request->file('image_file'), 'gallery');
                    } catch (RuntimeException $error) {
                        return redirect('/admin/gallery-form.php')->with('error', $error->getMessage());
                    }
                }
                if ($imagePath === '') {
                    return redirect('/admin/gallery-form.php')->with('error', 'Foto wajib diunggah.');
                }
                $item = $id > 0 ? GalleryItem::find($id) : new GalleryItem();
                if ($item === null) {
                    return redirect('/admin/gallery-form.php')->with('error', 'Data galeri tidak ditemukan.');
                }
                $oldPath = $item->exists ? (string) $item->lokasi_gambar : '';
                $item->fill([
                    'judul' => $service->judul,
                    'id_layanan' => $serviceId,
                    'lokasi_gambar' => $imagePath,
                    'urutan_tampil' => (int) $request->input('sort_order', 0),
                    'aktif' => $request->boolean('is_active'),
                ]);
                $item->save();
                if ($oldPath !== '' && $oldPath !== $imagePath) {
                    LegacySite::deleteUploadedFile($oldPath);
                }
                return redirect('/admin/gallery.php')->with('success', $id > 0 ? 'Item galeri berhasil diperbarui.' : 'Item galeri berhasil ditambahkan.');
            }
        }
        $editId = $request->query('edit');
        $editData = $editId ? GalleryItem::find((int) $editId) : null;
        return view('admin.gallery-form', $this->adminPageData([
            'pageTitle' => $editData ? 'Edit Galeri' : 'Tambah Galeri',
            'pageSubtitle' => $editData ? 'Perbarui data foto galeri layanan.' : 'Tambah foto galeri layanan baru.',
            'currentPage' => 'gallery.php',
            'editData' => $editData,
            'services' => Service::query()->where('aktif', true)->orderBy('urutan_tampil')->get(),
        ]));
    }

    // ================== HOME SLIDES ==================
    public function homeSlides(Request $request)
    {
        if ($request->isMethod('post')) {
            $action = (string) $request->input('action', '');
            $id = (int) $request->input('id', 0);
            if ($action === 'delete' && $id > 0) {
                $slide = HomeSlide::find($id);
                if ($slide !== null) {
                    LegacySite::deleteUploadedFile($slide->lokasi_gambar);
                    $slide->delete();
                }
                return redirect('/admin/home-slides.php')->with('success', 'Slide home berhasil dihapus.');
            }
        }
        $searchQuery = trim((string) $request->query('q', ''));
        $statusFilter = (string) $request->query('status', 'all');
        if (! in_array($statusFilter, ['all', 'active', 'inactive'], true)) {
            $statusFilter = 'all';
        }
        $query = HomeSlide::query();
        if ($searchQuery !== '') {
            $query->where(function ($builder) use ($searchQuery): void {
                $builder->where('judul', 'like', '%' . $searchQuery . '%')->orWhere('keterangan', 'like', '%' . $searchQuery . '%')->orWhere('lokasi_gambar', 'like', '%' . $searchQuery . '%');
            });
        }
        if ($statusFilter !== 'all') {
            $query->where('aktif', $statusFilter === 'active');
        }
        return view('admin.home-slides', $this->adminPageData([
            'pageTitle' => 'Kelola Slider Home',
            'pageSubtitle' => 'Atur gambar slider home, urutan tampil, dan status aktif slide untuk halaman utama.',
            'currentPage' => 'home-slides.php',
            'slides' => $query->orderBy('urutan_tampil')->orderBy('id_banner_beranda')->get(),
            'slidesTotalCount' => HomeSlide::count(),
            'slidesActiveCount' => HomeSlide::where('aktif', true)->count(),
            'slidesInactiveCount' => HomeSlide::where('aktif', false)->count(),
            'searchQuery' => $searchQuery,
            'statusFilter' => $statusFilter,
        ]));
    }

    public function homeSlidesForm(Request $request)
    {
        if ($request->isMethod('post')) {
            $action = (string) $request->input('action', '');
            $id = (int) $request->input('id', 0);
            if ($action === 'save') {
                $title = trim((string) $request->input('title', ''));
                $currentImagePath = trim((string) $request->input('current_image_path', ''));
                $imagePath = $currentImagePath;
                if ($request->hasFile('image_file')) {
                    try {
                        $imagePath = LegacySite::storeUploadedImage($request->file('image_file'), 'home');
                    } catch (RuntimeException $error) {
                        return redirect('/admin/home-slides-form.php')->with('error', $error->getMessage());
                    }
                }
                if ($imagePath === '') {
                    return redirect('/admin/home-slides-form.php')->with('error', 'Foto wajib diunggah.');
                }
                $slide = $id > 0 ? HomeSlide::find($id) : new HomeSlide();
                if ($slide === null) {
                    return redirect('/admin/home-slides-form.php')->with('error', 'Data slide tidak ditemukan.');
                }
                $oldPath = $slide->exists ? (string) $slide->lokasi_gambar : '';
                $slide->fill([
                    'judul' => $title,
                    'keterangan' => trim((string) $request->input('caption', '')),
                    'lokasi_gambar' => $imagePath,
                    'urutan_tampil' => (int) $request->input('sort_order', 0),
                    'aktif' => $request->boolean('is_active'),
                ]);
                $slide->save();
                if ($oldPath !== '' && $oldPath !== $imagePath) {
                    LegacySite::deleteUploadedFile($oldPath);
                }
                return redirect('/admin/home-slides.php')->with('success', $id > 0 ? 'Slide berhasil diperbarui.' : 'Slide berhasil ditambahkan.');
            }
        }
        $editId = $request->query('edit');
        $editData = $editId ? HomeSlide::find((int) $editId) : null;
        return view('admin.home-slides-form', $this->adminPageData([
            'pageTitle' => $editData ? 'Edit Slide Home' : 'Tambah Slide Home',
            'pageSubtitle' => $editData ? 'Perbarui data slide home.' : 'Tambah slide home baru.',
            'currentPage' => 'home-slides.php',
            'editData' => $editData,
        ]));
    }

    // ================== SERVICES ==================
    public function services(Request $request)
    {
        if ($request->isMethod('post')) {
            $action = (string) $request->input('action', '');
            $id = (int) $request->input('id', 0);
            if ($action === 'delete' && $id > 0) {
                $service = Service::find($id);
                if ($service !== null) {
                    $service->delete();
                }
                return redirect('/admin/services.php')->with('success', 'Layanan berhasil dihapus.');
            }
        }
        $searchQuery = trim((string) $request->query('q', ''));
        $statusFilter = (string) $request->query('status', 'all');
        if (! in_array($statusFilter, ['all', 'active', 'inactive'], true)) {
            $statusFilter = 'all';
        }
        $query = Service::query();
        if ($searchQuery !== '') {
            $query->where(function ($builder) use ($searchQuery): void {
                $builder->where('judul', 'like', '%' . $searchQuery . '%')->orWhere('deskripsi', 'like', '%' . $searchQuery . '%');
            });
        }
        if ($statusFilter !== 'all') {
            $query->where('aktif', $statusFilter === 'active');
        }
        return view('admin.services', $this->adminPageData([
            'pageTitle' => 'Kelola Layanan',
            'pageSubtitle' => 'Manajemen daftar layanan yang ditampilkan di website.',
            'currentPage' => 'services.php',
            'serviceItems' => $query->orderBy('urutan_tampil')->orderBy('id_layanan')->get(),
            'serviceTotalCount' => Service::count(),
            'serviceActiveCount' => Service::where('aktif', true)->count(),
            'serviceInactiveCount' => Service::where('aktif', false)->count(),
            'searchQuery' => $searchQuery,
            'statusFilter' => $statusFilter,
        ]));
    }

    public function servicesForm(Request $request)
    {
        if ($request->isMethod('post')) {
            $action = (string) $request->input('action', '');
            $id = (int) $request->input('id', 0);
            if ($action === 'save') {
                $title = trim((string) $request->input('title', ''));
                $description = trim((string) $request->input('description', ''));
                if ($title === '' || $description === '') {
                    return redirect('/admin/services-form.php')->with('error', 'Nama dan deskripsi layanan wajib diisi.');
                }
                $service = $id > 0 ? Service::find($id) : new Service();
                if ($service === null) {
                    return redirect('/admin/services-form.php')->with('error', 'Data layanan tidak ditemukan.');
                }
                $service->fill([
                    'judul' => $title,
                    'deskripsi' => $description,
                    'urutan_tampil' => (int) $request->input('sort_order', 0),
                    'aktif' => $request->boolean('is_active'),
                ]);
                $service->save();
                return redirect('/admin/services.php')->with('success', $id > 0 ? 'Layanan berhasil diperbarui.' : 'Layanan berhasil ditambahkan.');
            }
        }
        $editId = $request->query('edit');
        $editData = $editId ? Service::find((int) $editId) : null;
        return view('admin.services-form', $this->adminPageData([
            'pageTitle' => $editData ? 'Edit Layanan' : 'Tambah Layanan',
            'pageSubtitle' => $editData ? 'Perbarui data layanan.' : 'Tambah layanan baru.',
            'currentPage' => 'services.php',
            'editData' => $editData,
        ]));
    }

    // ================== PRICES ==================
    public function prices(Request $request)
    {
        if ($request->isMethod('post')) {
            $action = (string) $request->input('action', '');
            $id = (int) $request->input('id', 0);
            if ($action === 'delete' && $id > 0) {
                $item = PriceItem::find($id);
                if ($item !== null) {
                    $item->delete();
                }
                return redirect('/admin/prices.php')->with('success', 'Item harga berhasil dihapus.');
            }
        }
        $searchQuery = trim((string) $request->query('q', ''));
        $statusFilter = (string) $request->query('status', 'all');
        $serviceFilter = (int) $request->query('service', 0);
        if (! in_array($statusFilter, ['all', 'active', 'inactive'], true)) {
            $statusFilter = 'all';
        }
        $query = PriceItem::query()->with('service');
        if ($searchQuery !== '') {
            $query->where(function ($builder) use ($searchQuery): void {
                $builder
                    ->where('info_ukuran', 'like', '%' . $searchQuery . '%')
                    ->orWhere('teks_harga', 'like', '%' . $searchQuery . '%')
                    ->orWhereHas('service', function ($serviceQuery) use ($searchQuery): void {
                        $serviceQuery->where('judul', 'like', '%' . $searchQuery . '%');
                    });
            });
        }
        if ($statusFilter !== 'all') {
            $query->where('aktif', $statusFilter === 'active');
        }
        if ($serviceFilter > 0) {
            $query->where('id_layanan', $serviceFilter);
        }
        return view('admin.prices', $this->adminPageData([
            'pageTitle' => 'Kelola Harga',
            'pageSubtitle' => 'Manajemen daftar harga layanan.',
            'currentPage' => 'prices.php',
            'priceItems' => $query->orderBy('urutan_tampil')->orderBy('id_harga_layanan')->get(),
            'priceTotalCount' => PriceItem::count(),
            'pricesActiveCount' => PriceItem::where('aktif', true)->count(),
            'pricesInactiveCount' => PriceItem::where('aktif', false)->count(),
            'services' => Service::query()->where('aktif', true)->orderBy('urutan_tampil')->get(),
            'searchQuery' => $searchQuery,
            'statusFilter' => $statusFilter,
            'serviceFilter' => $serviceFilter,
        ]));
    }

    public function pricesForm(Request $request)
    {
        if ($request->isMethod('post')) {
            $action = (string) $request->input('action', '');
            $id = (int) $request->input('id', 0);
            if ($action === 'save') {
                $serviceId = (int) $request->input('service_id', 0);
                $sizeInfo = trim((string) $request->input('size_info', ''));
                $priceText = trim((string) $request->input('price_text', ''));
                
                // Validasi service_id
                $service = Service::find($serviceId);
                if ($service === null) {
                    return redirect('/admin/prices-form.php')->with('error', 'Pilih layanan yang tersedia.');
                }
                
                if ($sizeInfo === '' || $priceText === '') {
                    return redirect('/admin/prices-form.php')->with('error', 'Semua field wajib diisi.');
                }
                $item = $id > 0 ? PriceItem::find($id) : new PriceItem();
                if ($item === null) {
                    return redirect('/admin/prices-form.php')->with('error', 'Data harga tidak ditemukan.');
                }
                $item->fill([
                    'id_layanan' => $serviceId,
                    'info_ukuran' => $sizeInfo,
                    'teks_harga' => $priceText,
                    'urutan_tampil' => (int) $request->input('sort_order', 0),
                    'aktif' => $request->boolean('is_active'),
                ]);
                $item->save();
                return redirect('/admin/prices.php')->with('success', $id > 0 ? 'Item harga berhasil diperbarui.' : 'Item harga berhasil ditambahkan.');
            }
        }
        $editId = $request->query('edit');
        $editData = $editId ? PriceItem::query()->with('service')->find((int) $editId) : null;
        return view('admin.prices-form', $this->adminPageData([
            'pageTitle' => $editData ? 'Edit Harga' : 'Tambah Harga',
            'pageSubtitle' => $editData ? 'Perbarui data harga layanan.' : 'Tambah harga layanan baru.',
            'currentPage' => 'prices.php',
            'editData' => $editData,
            'services' => Service::query()->where('aktif', true)->orderBy('urutan_tampil')->get(),
        ]));
    }

    // ================== SETTINGS ==================
    public function settings(Request $request)
    {
        return view('admin.settings', $this->adminPageData([
            'pageTitle' => 'Pengaturan',
            'pageSubtitle' => 'Kelola pengaturan website.',
            'currentPage' => 'settings.php',
        ]));
    }

    public function settingsProfile(Request $request)
    {
        $adminId = (int) $request->session()->get('admin_id', 0);
        $admin = Admin::find($adminId);
        if ($admin === null) {
            return redirect('/admin/settings.php')->with('error', 'Admin tidak ditemukan.');
        }

        if ($request->isMethod('post')) {
            $action = (string) $request->input('action', '');
            if ($action === 'update_profile' || $action === 'save') {
                $username = trim((string) $request->input('username', ''));
                $fullName = trim((string) $request->input('full_name', ''));
                $email = trim((string) $request->input('email', ''));
                $phone = $this->normalizePhoneNumber((string) $request->input('phone', ''));
                $isTestingOtp = $request->boolean('test_otp');

                if (! preg_match('/^[A-Za-z0-9._-]{3,30}$/', $username)) {
                    return back()->withInput()->with('error', 'Username harus 3-30 karakter dan hanya boleh berisi huruf, angka, titik, underscore, atau minus.');
                }

                if ($email === '') {
                    return back()->withInput()->with('error', 'Email pemulihan wajib diisi sebagai jalur utama pengiriman OTP.');
                }

                $emailVerification = EmailVerifier::verify($email);
                if (! $emailVerification['valid']) {
                    return back()->withInput()->with('error', $emailVerification['message']);
                }

                if ($fullName === '' || mb_strlen($fullName) > 120) {
                    return back()->withInput()->with('error', 'Nama lengkap wajib diisi dan maksimal 120 karakter.');
                }

                if ($phone !== '' && ! preg_match('/^62[0-9]{8,15}$/', $phone)) {
                    return back()->withInput()->with('error', 'Nomor WhatsApp harus diawali kode negara 62 (contoh: 62812xxx) agar sistem pengiriman OTP berfungsi.');
                }

                $existingUsername = Admin::query()
                    ->where('nama_pengguna', $username)
                    ->where('id_admin', '<>', $admin->id_admin)
                    ->first();
                if ($existingUsername !== null) {
                    return back()->withInput()->with('error', 'Username sudah digunakan admin lain.');
                }

                $existingEmail = Admin::query()
                    ->where('email', $email)
                    ->where('id_admin', '<>', $admin->id_admin)
                    ->first();
                if ($existingEmail !== null) {
                    return back()->withInput()->with('error', 'Email sudah digunakan admin lain.');
                }

                $admin->nama_pengguna = $username;
                $admin->nama_lengkap = $fullName;
                $admin->email = $email;
                $admin->nomor_telepon = $phone !== '' ? $phone : null;
                $admin->save();

                $request->session()->put('admin_name', $fullName);

                // Fitur: Uji coba pengiriman OTP ke WhatsApp jika diminta
                if ($isTestingOtp && !empty($admin->nomor_telepon)) {
                    $otp = $this->generateOtp();
                    $admin->kode_otp = $otp;
                    $admin->waktu_otp_kedaluwarsa = now()->addMinutes(5);
                    $admin->save();
                    
                    $sent = $this->sendOtpWhatsApp($admin, $otp);
                    $msg = $sent ? 'Profil disimpan & OTP uji coba terkirim ke WhatsApp.' : 'Profil disimpan, namun gagal mengirim WhatsApp. Cek konfigurasi Gateway.';
                    return back()->with($sent ? 'success' : 'error', $msg);
                }

                return back()->with('success', 'Profil admin berhasil diperbarui.');
            }
        }

        return view('admin.settings-profile', $this->adminPageData([
            'pageTitle' => 'Pengaturan Profil',
            'pageSubtitle' => 'Kelola profil admin.',
            'currentPage' => 'settings.php',
            'adminData' => $admin,
            'showBackButton' => true,
        ]));
    }

    // ================== RECOVERY - FORGOT PASSWORD ==================
    public function recoveryRequest(Request $request)
    {
        if ($request->session()->has('admin_id')) {
            return redirect('/admin/dashboard.php');
        }

        $secretKey = (string) config('legacy.admin_access_key');

        if ($request->isMethod('post')) {
            $identifier = trim((string) $request->input('identifier', ''));
            $channel = (string) $request->input('channel', 'email');
            if (! in_array($channel, ['email', 'whatsapp'], true)) {
                $channel = 'email';
            }

            if ($identifier === '') {
                return back()->withInput()->with('error', 'Username, email, atau nomor WhatsApp wajib diisi.');
            }

            $phoneIdentifier = $this->normalizePhoneNumber($identifier);
            $admin = Admin::query()
                ->where('nama_pengguna', $identifier)
                ->orWhere('email', $identifier)
                ->when($phoneIdentifier !== '', function ($query) use ($phoneIdentifier): void {
                    $query->orWhere('nomor_telepon', $phoneIdentifier);
                })
                ->first();

            if ($admin === null || ! $admin->aktif) {
                return back()->withInput()->with('error', 'Akun tidak ditemukan atau tidak aktif.');
            }

            if ($channel === 'email' && empty($admin->email)) {
                return back()->withInput()->with('error', 'Akun ini belum memiliki email pemulihan.');
            }

            if ($channel === 'whatsapp' && empty($admin->nomor_telepon)) {
                return back()->withInput()->with('error', 'Akun ini belum memiliki nomor WhatsApp pemulihan.');
            }

            $otp = $this->generateOtp();
            $admin->kode_otp = $otp;
            $admin->waktu_otp_kedaluwarsa = now()->addMinutes(15);
            $admin->save();

            $sent = $channel === 'whatsapp'
                ? $this->sendOtpWhatsApp($admin, $otp)
                : $this->sendOtpEmail($admin, $otp);

            if (! $sent) {
                $admin->kode_otp = null;
                $admin->waktu_otp_kedaluwarsa = null;
                $admin->save();

                $message = $channel === 'whatsapp'
                    ? 'OTP WhatsApp belum dapat dikirim. Pastikan nomor WA admin sudah benar dan gateway WhatsApp sudah dikonfigurasi.'
                    : 'OTP email belum dapat dikirim. Periksa konfigurasi email website.';

                return back()->withInput()->with('error', $message);
            }

            $request->session()->put('recovery_admin_id', (int) $admin->id_admin);
            $request->session()->put('recovery_identifier', $identifier);
            $request->session()->put('recovery_channel', $channel);

            return redirect('/admin/recovery/verify.php')->with('success', 'Kode OTP berhasil dikirim.');
        }

        return view('admin.recovery.request', ['accessKey' => $secretKey]);
    }

    public function recoveryVerify(Request $request)
    {
        if (! $request->session()->has('recovery_admin_id')) {
            return redirect('/admin/recovery/request.php');
        }

        $adminId = (int) $request->session()->get('recovery_admin_id');
        $identifier = (string) $request->session()->get('recovery_identifier', '');
        $channel = (string) $request->session()->get('recovery_channel', 'email');
        $admin = Admin::find($adminId);

        if ($admin === null) {
            $request->session()->forget(['recovery_admin_id', 'recovery_identifier', 'recovery_channel']);
            return redirect('/admin/recovery/request.php');
        }

        $secretKey = (string) config('legacy.admin_access_key');

        if ($request->isMethod('post')) {
            $otp = trim((string) $request->input('otp', ''));

            if (! preg_match('/^[0-9]{6}$/', $otp)) {
                return back()->with('error', 'Kode OTP harus 6 digit angka.');
            }

            if (empty($admin->kode_otp) || empty($admin->waktu_otp_kedaluwarsa)) {
                return redirect('/admin/recovery/request.php')->with('error', 'Kode OTP tidak tersedia. Minta kode baru.');
            }

            if (now()->gt($admin->waktu_otp_kedaluwarsa)) {
                $admin->kode_otp = null;
                $admin->waktu_otp_kedaluwarsa = null;
                $admin->save();

                return redirect('/admin/recovery/request.php')->with('error', 'Kode OTP sudah kedaluwarsa. Minta kode baru.');
            }

            if (! hash_equals((string) $admin->kode_otp, $otp)) {
                return back()->with('error', 'Kode OTP tidak valid.');
            }

            $resetToken = bin2hex(random_bytes(32));
            $admin->token_reset = $resetToken;
            $admin->waktu_token_reset_kedaluwarsa = now()->addMinutes(30);
            $admin->kode_otp = null;
            $admin->waktu_otp_kedaluwarsa = null;
            $admin->save();

            // Logika baru: Langsung masukkan admin ke session (Login Langsung)
            $request->session()->put('admin_id', (int) $admin->id_admin);
            $request->session()->put('admin_name', (string) $admin->nama_lengkap);
            $request->session()->forget(['recovery_admin_id', 'recovery_identifier', 'recovery_channel']);

            return redirect('/admin/dashboard.php')->with('success', 'Verifikasi berhasil! Anda telah masuk secara otomatis.');
        }

        return view('admin.recovery.verify', [
            'adminId' => $adminId,
            'email' => $this->maskRecoveryContact($channel, $admin),
            'deliveryLabel' => $this->recoveryChannelLabel($channel),
            'identifier' => $identifier,
            'channel' => $channel,
            'accessKey' => $secretKey,
        ]);
    }

    public function recoveryReset(Request $request)
    {
        if (! $request->session()->has('reset_admin_id') || ! $request->session()->has('reset_token')) {
            return redirect('/admin/recovery/request.php');
        }

        $adminId = (int) $request->session()->get('reset_admin_id');
        $token = (string) $request->session()->get('reset_token');
        $admin = Admin::find($adminId);

        if ($admin === null || ! hash_equals((string) $admin->token_reset, $token)) {
            $request->session()->forget(['reset_admin_id', 'reset_token']);
            return redirect('/admin/recovery/request.php');
        }

        if ($admin->waktu_token_reset_kedaluwarsa && now()->gt($admin->waktu_token_reset_kedaluwarsa)) {
            $admin->token_reset = null;
            $admin->waktu_token_reset_kedaluwarsa = null;
            $admin->save();
            $request->session()->forget(['reset_admin_id', 'reset_token']);

            return redirect('/admin/recovery/request.php')->with('error', 'Token reset sudah kedaluwarsa.');
        }

        $secretKey = (string) config('legacy.admin_access_key');

        if ($request->isMethod('post')) {
            $password = (string) $request->input('password', '');
            $passwordConfirmation = (string) $request->input('password_confirmation', '');

            if ($password === '' || $passwordConfirmation === '') {
                return back()->with('error', 'Password wajib diisi.');
            }

            if ($password !== $passwordConfirmation) {
                return back()->with('error', 'Password dan konfirmasi password tidak sama.');
            }

            if (strlen($password) < 6) {
                return back()->with('error', 'Password minimal 6 karakter.');
            }

            $admin->kata_sandi = password_hash($password, PASSWORD_DEFAULT);
            $admin->token_reset = null;
            $admin->waktu_token_reset_kedaluwarsa = null;
            $admin->save();

            $request->session()->forget(['reset_admin_id', 'reset_token']);

            return redirect('/admin/login.php?key=' . urlencode($secretKey))->with('success', 'Password berhasil direset. Silakan login dengan password baru.');
        }

        return view('admin.recovery.reset', [
            'adminId' => $adminId,
            'token' => $token,
            'username' => $admin->nama_pengguna,
            'accessKey' => $secretKey,
        ]);
    }

    public function settingsPassword(Request $request)
    {
        if ($request->isMethod('post')) {
            $action = (string) $request->input('action', '');
            if ($action === 'save') {
                $adminId = (int) $request->session()->get('admin_id', 0);
                $admin = Admin::find($adminId);
                if ($admin === null) {
                    return back()->with('error', 'Admin tidak ditemukan.');
                }
                $currentPassword = (string) $request->input('current_password', '');
                $newPassword = (string) $request->input('new_password', '');
                $confirmPassword = (string) $request->input('confirm_password', '');
                if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
                    return back()->with('error', 'Semua field wajib diisi.');
                }
                if ($newPassword !== $confirmPassword) {
                    return back()->with('error', 'Password baru dan konfirmasi tidak cocok.');
                }
                if (strlen($newPassword) < 6) {
                    return back()->with('error', 'Password minimal 6 karakter.');
                }
                $stored = (string) $admin->kata_sandi;
                $valid = $this->looksLikeHash($stored) ? password_verify($currentPassword, $stored) : hash_equals($stored, $currentPassword);
                if (! $valid) {
                    return back()->with('error', 'Password saat ini salah.');
                }
                $admin->kata_sandi = password_hash($newPassword, PASSWORD_DEFAULT);
                $admin->save();
                return back()->with('success', 'Password berhasil diubah.');
            }
        }
        return view('admin.settings-password', $this->adminPageData([
            'pageTitle' => 'Ubah Password',
            'pageSubtitle' => 'Ubah password admin.',
            'currentPage' => 'settings.php',
        ]));
    }

    public function settingsContact(Request $request)
    {
        if ($request->isMethod('post')) {
            $action = (string) $request->input('action', '');
            if ($action === 'save') {
                $setting = \App\Models\SiteSetting::query()->first();
                if ($setting === null) {
                    $setting = new \App\Models\SiteSetting();
                }
                $setting->fill([
                    'nomor_whatsapp' => trim((string) $request->input('whatsapp_number', '')),
                    'email_kontak' => trim((string) $request->input('contact_email', '')),
                    'alamat_lengkap' => trim((string) $request->input('full_address', '')),
                ]);
                $setting->save();
                return back()->with('success', 'Kontak berhasil diperbarui.');
            }
        }
        $setting = \App\Models\SiteSetting::query()->first();
        return view('admin.settings-contact', $this->adminPageData([
            'pageTitle' => 'Pengaturan Kontak',
            'pageSubtitle' => 'Kelola informasi kontak website.',
            'currentPage' => 'settings.php',
            'setting' => $setting,
        ]));
    }

    public function verifyEmailChange(Request $request)
    {
        return redirect('/admin/settings-profile.php')->with('error', 'Perubahan email sekarang dilakukan dari Profil Admin.');
    }

    protected function adminPageData(array $data = []): array
    {
        return array_merge([
            'pageTitle' => 'Admin',
            'pageSubtitle' => '',
            'currentPage' => '',
            'adminName' => (string) session('admin_name', ''),
            'showBackButton' => false,
        ], $data);
    }

    private function generateOtp(): string
    {
        return (string) random_int(100000, 999999);
    }

    private function sendOtpEmail(Admin $admin, string $otp): bool
    {
        $email = (string) $admin->email;
        if ($email === '') {
            return false;
        }

        $subject = 'Kode OTP Reset Password - Karunya Printing';
        $body = "Halo {$admin->nama_lengkap},\n\n";
        $body .= "Kode OTP reset password admin Anda adalah: {$otp}\n\n";
        $body .= "Kode ini berlaku selama 15 menit.\n";
        $body .= "Jika Anda tidak meminta reset password, abaikan pesan ini.\n\n";
        $body .= "Salam,\nKarunya Printing";

        try {
            Mail::raw($body, function ($message) use ($email, $subject): void {
                $message->to($email)->subject($subject);
            });

            return true;
        } catch (\Throwable $error) {
            Log::error('Failed to send admin recovery OTP email: ' . $error->getMessage());
            return false;
        }
    }

    private function sendOtpWhatsApp(Admin $admin, string $otp): bool
    {
        $phone = (string) $admin->nomor_telepon;
        $endpoint = (string) config('services.whatsapp_otp.endpoint', '');

        if ($phone === '' || $endpoint === '') {
            return false;
        }

        $message = "Kode OTP reset password admin Karunya Printing: {$otp}. Berlaku 15 menit. Abaikan jika Anda tidak meminta reset password.";
        $phoneField = (string) config('services.whatsapp_otp.phone_field', 'to');
        $messageField = (string) config('services.whatsapp_otp.message_field', 'message');
        $token = (string) config('services.whatsapp_otp.token', '');
        $tokenHeader = (string) config('services.whatsapp_otp.token_header', 'Authorization');
        $tokenPrefix = (string) config('services.whatsapp_otp.token_prefix', 'Bearer');

        try {
            $request = Http::timeout(12)->acceptJson();
            if ($token !== '') {
                $headerValue = trim($tokenPrefix . ' ' . $token);
                $request = $request->withHeaders([$tokenHeader => $headerValue]);
            }

            $response = $request->post($endpoint, [
                $phoneField => $phone,
                $messageField => $message,
            ]);

            if ($response->successful()) {
                return true;
            }

            Log::warning('WhatsApp OTP gateway returned non-success status.', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
        } catch (\Throwable $error) {
            Log::error('Failed to send admin recovery OTP WhatsApp: ' . $error->getMessage());
        }

        return false;
    }

    private function normalizePhoneNumber(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';
        if ($digits === '') {
            return '';
        }

        if (str_starts_with($digits, '0')) {
            return '62' . substr($digits, 1);
        }

        if (str_starts_with($digits, '8')) {
            return '62' . $digits;
        }

        return $digits;
    }

    private function maskRecoveryContact(string $channel, Admin $admin): string
    {
        if ($channel === 'whatsapp') {
            $phone = (string) $admin->nomor_telepon;
            if (strlen($phone) <= 6) {
                return $phone;
            }

            return substr($phone, 0, 4) . str_repeat('*', max(strlen($phone) - 7, 0)) . substr($phone, -3);
        }

        $email = (string) $admin->email;
        $parts = explode('@', $email, 2);
        if (count($parts) !== 2) {
            return $email;
        }

        $name = $parts[0];
        $maskedName = strlen($name) <= 2
            ? substr($name, 0, 1) . '*'
            : substr($name, 0, 2) . str_repeat('*', max(strlen($name) - 2, 1));

        return $maskedName . '@' . $parts[1];
    }

    private function recoveryChannelLabel(string $channel): string
    {
        return $channel === 'whatsapp' ? 'WhatsApp' : 'email';
    }

    protected function looksLikeHash(string $value): bool
    {
        return strlen($value) === 60 && str_starts_with($value, '$2');
    }

    private function getServiceIdByTitle(string $title): int
    {
        $service = Service::query()->where('judul', $title)->first();
        return $service ? (int) $service->id_layanan : 0;
    }
}
