<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\GalleryItem;
use App\Models\HomeSlide;
use App\Models\PriceItem;
use App\Models\SiteActivityLog;

use App\Support\EmailVerifier;
use App\Support\LegacySite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
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
            'latestPrices' => PriceItem::query()->latest('waktu_diperbarui')->limit(5)->get(),
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
        $serviceFilter = (string) $request->query('service', 'all');
        if (! in_array($statusFilter, ['all', 'active', 'inactive'], true)) {
            $statusFilter = 'all';
        }
        if ($serviceFilter !== 'all' && ! in_array($serviceFilter, LegacySite::galleryServiceOptions(), true)) {
            $serviceFilter = 'all';
        }
        $query = GalleryItem::query();
        if ($statusFilter !== 'all') {
            $query->where('aktif', $statusFilter === 'active');
        }
        if ($serviceFilter !== 'all') {
            $query->where('judul', $serviceFilter);
        }
        return view('admin.gallery', $this->adminPageData([
            'pageTitle' => 'Kelola Galeri',
            'pageSubtitle' => 'Manajemen koleksi foto galeri.',
            'currentPage' => 'gallery.php',
            'galleryItems' => $query->orderBy('urutan_tampil')->orderBy('id_galeri_layanan')->get(),
            'galleryTotalCount' => GalleryItem::count(),
            'galleryActiveCount' => GalleryItem::where('aktif', true)->count(),
            'galleryInactiveCount' => GalleryItem::where('aktif', false)->count(),
            'serviceOptions' => LegacySite::galleryServiceOptions(),
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
                $serviceName = trim((string) $request->input('service_name', ''));
                $currentImagePath = trim((string) $request->input('current_image_path', ''));
                if ($serviceName === '' || ! in_array($serviceName, LegacySite::galleryServiceOptions(), true)) {
                    return redirect('/admin/gallery-form.php')->with('error', 'Pilih jenis layanan yang tersedia.');
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
                    'judul' => $serviceName,
                    // Hubungkan galeri dengan layanan melalui id_layanan
                    'id_layanan' => $this->getServiceIdByTitle($serviceName),
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
            'serviceOptions' => LegacySite::galleryServiceOptions(),
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
                    return redirect('/admin/home-slides-form.php')->with('error', 'Gambar slide wajib diunggah.');
                }
                $slide = $id > 0 ? HomeSlide::find($id) : new HomeSlide();
                if ($slide === null) {
                    return redirect('/admin/home-slides-form.php')->with('error', 'Data slide tidak ditemukan.');
                }
                $oldPath = $slide->exists ? (string) $slide->lokasi_gambar : '';
$slide->fill([
                    'judul' => $title,
                    'keterangan' => (string) $request->input('caption', ''),
                    'lokasi_gambar' => $imagePath,
                    'urutan_tampil' => (int) $request->input('sort_order', 0),
                    'aktif' => $request->boolean('is_active'),
                ]);
                $slide->save();
                if ($oldPath !== '' && $oldPath !== $imagePath) {
                    LegacySite::deleteUploadedFile($oldPath);
                }
                return redirect('/admin/home-slides.php')->with('success', $id > 0 ? 'Slide home berhasil diperbarui.' : 'Slide home berhasil ditambahkan.');
            }
        }
        $editId = $request->query('edit');
        $editData = $editId ? HomeSlide::find((int) $editId) : null;
        return view('admin.home-slides-form', $this->adminPageData([
            'pageTitle' => $editData ? 'Edit Slider Home' : 'Tambah Slider Home',
            'pageSubtitle' => $editData ? 'Perbarui data slider home.' : 'Tambah slider home baru.',
            'currentPage' => 'home-slides.php',
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
                PriceItem::whereKey($id)->delete();
                return redirect('/admin/prices.php')->with('success', 'Item harga berhasil dihapus.');
            }
        }
        $searchQuery = trim((string) $request->query('q', ''));
        $statusFilter = (string) $request->query('status', 'all');
        if (! in_array($statusFilter, ['all', 'active', 'inactive'], true)) {
            $statusFilter = 'all';
        }
        $query = PriceItem::query();
        if ($searchQuery !== '') {
            $query->where(function ($builder) use ($searchQuery): void {
                $builder->whereHas('service', function ($q) use ($searchQuery): void {
                    $q->where('judul', 'like', '%' . $searchQuery . '%');
                })->orWhere('info_ukuran', 'like', '%' . $searchQuery . '%')->orWhere('teks_harga', 'like', '%' . $searchQuery . '%');
            });
        }
        if ($statusFilter !== 'all') {
            $query->where('aktif', $statusFilter === 'active');
        }
        return view('admin.prices', $this->adminPageData([
            'pageTitle' => 'Kelola Harga',
            'pageSubtitle' => 'Perbarui daftar harga layanan, urutan tampil, dan status item yang muncul di website.',
            'currentPage' => 'prices.php',
            'priceItems' => $query->orderBy('urutan_tampil')->orderBy('id_harga_layanan')->get(),
            'priceTotalCount' => PriceItem::count(),
            'priceActiveCount' => PriceItem::where('aktif', true)->count(),
            'priceInactiveCount' => PriceItem::where('aktif', false)->count(),
            'searchQuery' => $searchQuery,
            'statusFilter' => $statusFilter,
        ]));
    }

    public function pricesForm(Request $request)
    {
        if ($request->isMethod('post')) {
            $action = (string) $request->input('action', '');
            $id = (int) $request->input('id', 0);
            if ($action === 'save') {
                $serviceName = trim((string) $request->input('service_name', ''));
                $sizeInfo = trim((string) $request->input('size_info', ''));
                $priceText = trim((string) $request->input('price_text', ''));
                if ($serviceName === '' || $sizeInfo === '' || $priceText === '') {
                    return redirect('/admin/prices-form.php')->with('error', 'Nama layanan, ukuran/satuan, dan harga wajib diisi.');
                }
                $item = $id > 0 ? PriceItem::find($id) : new PriceItem();
                if ($item === null) {
                    return redirect('/admin/prices-form.php')->with('error', 'Data harga tidak ditemukan.');
                }
                $item->fill([
                    'id_layanan' => $this->getServiceIdByTitle($serviceName),
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
        $editData = $editId ? PriceItem::find((int) $editId) : null;
        return view('admin.prices-form', $this->adminPageData([
            'pageTitle' => $editData ? 'Edit Harga' : 'Tambah Harga',
            'pageSubtitle' => $editData ? 'Perbarui data harga layanan.' : 'Tambah harga layanan baru.',
            'currentPage' => 'prices.php',
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
                \App\Models\Service::whereKey($id)->delete();
                return redirect('/admin/services.php')->with('success', 'Layanan berhasil dihapus.');
            }
        }
        $searchQuery = trim((string) $request->query('q', ''));
        $statusFilter = (string) $request->query('status', 'all');
        if (! in_array($statusFilter, ['all', 'active', 'inactive'], true)) {
            $statusFilter = 'all';
        }
        $query = \App\Models\Service::query();
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
            'pageSubtitle' => 'Atur daftar layanan yang tampil di halaman publik website.',
            'currentPage' => 'services.php',
            'serviceItems' => $query->orderBy('urutan_tampil')->orderBy('id_layanan')->get(),
            'serviceTotalCount' => \App\Models\Service::count(),
            'serviceActiveCount' => \App\Models\Service::where('aktif', true)->count(),
            'serviceInactiveCount' => \App\Models\Service::where('aktif', false)->count(),
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
                    return redirect('/admin/services-form.php')->with('error', 'Nama layanan dan deskripsi wajib diisi.');
                }
                $item = $id > 0 ? \App\Models\Service::find($id) : new \App\Models\Service();
                if ($item === null) {
                    return redirect('/admin/services-form.php')->with('error', 'Data layanan tidak ditemukan.');
                }
$item->fill([
                    'judul' => $title,
                    'deskripsi' => $description,
                    'urutan_tampil' => (int) $request->input('sort_order', 0),
                    'aktif' => $request->boolean('is_active'),
                ]);
                $item->save();
                return redirect('/admin/services.php')->with('success', $id > 0 ? 'Layanan berhasil diperbarui.' : 'Layanan berhasil ditambahkan.');
            }
        }
        $editId = $request->query('edit');
        $editData = $editId ? \App\Models\Service::find((int) $editId) : null;
        return view('admin.services-form', $this->adminPageData([
            'pageTitle' => $editData ? 'Edit Layanan' : 'Tambah Layanan',
            'pageSubtitle' => $editData ? 'Perbarui data layanan.' : 'Tambah layanan baru.',
            'currentPage' => 'services.php',
            'editData' => $editData,
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
            
            if ($identifier === '') {
                return back()->withInput()->with('error', 'Username atau email wajib diisi.');
            }
            
            $admin = Admin::query()
                ->where('nama_pengguna', $identifier)
                ->orWhere('email', $identifier)
                ->first();
            
            if ($admin === null) {
                return back()->withInput()->with('error', 'Akun tidak ditemukan. Periksa username atau email Anda.');
            }
            
            // Check if admin has email
            if (empty($admin->email)) {
                return back()->withInput()->with('error', 'Akun ini belum memiliki email. Hubungi administrator.');
            }
            
            // Generate OTP
            $otp = $this->generateOtp();
            
            // Save OTP to database
            $admin->otp_code = $otp;
            $admin->otp_expires_at = now()->addMinutes(15);
            $admin->save();
            
            // Send OTP via email
            $this->sendOtpEmail($admin, $otp);
            
            // Store admin_id in session temporarily
            $request->session()->put('recovery_admin_id', (int) $admin->getKey());
            $request->session()->put('recovery_identifier', $identifier);
            
            return redirect('/admin/recovery/verify.php');
        }
        
        return view('admin.recovery.request', ['accessKey' => $secretKey]);
    }
    
    public function recoveryVerify(Request $request)
    {
        if (!$request->session()->has('recovery_admin_id')) {
            return redirect('/admin/recovery/request.php');
        }
        
        $adminId = (int) $request->session()->get('recovery_admin_id');
        $identifier = (string) $request->session()->get('recovery_identifier', '');
        $admin = Admin::find($adminId);
        
        if ($admin === null) {
            $request->session()->forget(['recovery_admin_id', 'recovery_identifier']);
            return redirect('/admin/recovery/request.php');
        }
        
        $secretKey = (string) config('legacy.admin_access_key');
        
        if ($request->isMethod('post')) {
            $otp = trim((string) $request->input('otp', ''));
            
            if ($otp === '') {
                return back()->with('error', 'Kode OTP wajib diisi.');
            }
            
            // Verify OTP
            if ($admin->otp_code !== $otp) {
                return back()->with('error', 'Kode OTP tidak valid.');
            }
            
            // Check expiry
            if ($admin->otp_expires_at && now()->gt($admin->otp_expires_at)) {
                $admin->otp_code = null;
                $admin->otp_expires_at = null;
                $admin->save();
                return back()->with('error', 'Kode OTP sudah kedaluwarsa. Minta kode baru.');
            }
            
            // OTP valid - generate reset token
            $resetToken = bin2hex(random_bytes(32));
            
            // Store reset token
            $admin->reset_token = $resetToken;
            $admin->reset_expires_at = now()->addMinutes(30);
            $admin->otp_code = null;
            $admin->otp_expires_at = null;
            $admin->save();
            
            // Store reset info in session
            $request->session()->put('reset_admin_id', (int) $admin->getKey());
            $request->session()->put('reset_token', $resetToken);
            $request->session()->forget(['recovery_admin_id', 'recovery_identifier']);
            
            return redirect('/admin/recovery/reset.php');
        }
        
        return view('admin.recovery.verify', [
            'adminId' => $adminId,
            'email' => $admin->email,
            'identifier' => $identifier,
            'accessKey' => $secretKey,
        ]);
    }
    
    public function recoveryReset(Request $request)
    {
        if (!$request->session()->has('reset_admin_id') || !$request->session()->has('reset_token')) {
            return redirect('/admin/recovery/request.php');
        }
        
        $adminId = (int) $request->session()->get('reset_admin_id');
        $token = (string) $request->session()->get('reset_token');
        $admin = Admin::find($adminId);
        
        if ($admin === null || $admin->reset_token !== $token) {
            $request->session()->forget(['reset_admin_id', 'reset_token']);
            return redirect('/admin/recovery/request.php');
        }
        
        // Check token expiry
        if ($admin->reset_expires_at && now()->gt($admin->reset_expires_at)) {
            $admin->reset_token = null;
            $admin->reset_expires_at = null;
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
            
            // Update password
            $admin->kata_sandi = password_hash($password, PASSWORD_DEFAULT);
            $admin->reset_token = null;
            $admin->reset_expires_at = null;
            $admin->save();
            
            // Clear session
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
    
    // Helper methods for OTP
    private function generateOtp(): string
    {
        return (string) random_int(100000, 999999);
    }
    
private function sendOtpEmail(Admin $admin, string $otp, string $customSubject = null, string $targetEmail = null): bool
    {
        $email = $targetEmail ?? $admin->email;
        
        if (empty($email)) {
            return false;
        }
        
$subject = $customSubject ?? 'Kode Verifikasi Password Reset - Karunya Printing';
        $message = "Halo {$admin->nama_lengkap},\n\n";
        $message .= "Anda meminta reset password. Berikut kode verifikasi Anda:\n\n";
        $message .= "KODE: {$otp}\n\n";
        $message .= "Kode ini berlaku selama 15 menit.\n";
        $message .= "Jika Anda tidak meminta ini, abaikan email ini.\n\n";
        $message .= "Salam,\nKarunya Printing";
        
        $headers = "From: noreply@karunyaprinting.com\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        
try {
            $sent = mail($email, $subject, $message, $headers);
            // Log OTP for development/testing purposes
            \Log::info('OTP Email - To: ' . $email . ' | Subject: ' . $subject . ' | OTP: ' . $otp);
            return $sent;
        } catch (\Exception $e) {
            \Log::error('Failed to send OTP email: ' . $e->getMessage());
            return false;
        }
    }

    // ================== SETTINGS ==================
    public function settings(Request $request)
    {
        $adminId = (int) session('admin_id');
        $admin = Admin::find($adminId);

        if ($admin === null) {
            return redirect('/admin/login.php')->with('error', 'Session admin tidak valid.');
        }

        return view('admin.settings', $this->adminPageData([
            'pageTitle' => 'Menu Pengaturan',
            'pageSubtitle' => 'Kelola akun dan konfigurasi.',
            'currentPage' => 'settings.php',
            'adminData' => $admin,
            'isHubPage' => true,
            'appInfo' => [
                'name' => 'Karunya Management System',
                'version' => '1.5.0',
                'build' => '2024.05.31',
                'description' => 'Sistem manajemen konten khusus untuk Karunya Printing.'
            ]
        ]));
    }

    public function settingsContact(Request $request)
    {
        $adminId = (int) session('admin_id');
        $admin = Admin::find($adminId);

        if ($admin === null) {
            return redirect('/admin/login.php')->with('error', 'Session admin tidak valid.');
        }

        if ($request->isMethod('post')) {
            $settingsData = [
                'contact_phone' => $request->input('contact_phone'),
                'contact_phone_international' => $request->input('contact_phone_international'),
                'contact_email' => $request->input('contact_email'),
                'contact_address' => $request->input('contact_address'),
                'contact_map_url' => $request->input('contact_map_url'),
                'whatsapp_default_message' => $request->input('whatsapp_default_message'),
                'social_instagram' => $request->input('social_instagram'),
                'social_facebook' => $request->input('social_facebook'),
                'social_tiktok' => $request->input('social_tiktok'),
            ];

            foreach ($settingsData as $key => $value) {
                DB::table('site_settings')->updateOrInsert(
                    ['key' => $key],
                    ['value' => (string) ($value ?? '')]
                );
            }

            Cache::forget('site_settings');
            return back()->with('success', 'Informasi kontak berhasil diperbarui.');
        }

        $settings = DB::table('site_settings')->pluck('value', 'key')->toArray();

        return view('admin.settings-contact', $this->adminPageData([
            'pageTitle' => 'Pengaturan Kontak',
            'pageSubtitle' => 'Kelola nomor telepon, alamat, dan link media sosial website.',
            'currentPage' => 'settings-contact.php',
            'settings' => $settings,
            'adminData' => $admin,
            'showBackButton' => true,
        ]));
    }

    public function settingsProfile(Request $request)
    {
        $adminId = (int) session('admin_id');
        $admin = Admin::find($adminId);

        if ($admin === null) {
            return redirect('/admin/login.php')->with('error', 'Session admin tidak valid.');
        }

        if ($request->isMethod('post')) {
            $username = trim((string) $request->input('username', ''));
            $fullName = trim((string) $request->input('full_name', ''));

            if ($username === '') {
                return back()->with('error', 'Username wajib diisi.');
            }

            if (! preg_match('/^[A-Za-z0-9_.-]{3,30}$/', $username)) {
                return back()->with('error', 'Username hanya boleh berisi huruf, angka, titik, underscore atau minus, 3-30 karakter.');
            }

            if ($fullName === '') {
                return back()->with('error', 'Nama lengkap wajib diisi.');
            }

            $existing = Admin::query()->where('nama_pengguna', $username)->where('id_admin', '<>', $admin->id_admin)->first();
            if ($existing !== null) {
                return back()->with('error', 'Username sudah digunakan oleh admin lain.');
            }

            $admin->nama_pengguna = $username;
            $admin->nama_lengkap = $fullName;
            $admin->save();

            $request->session()->put('admin_name', $fullName);

            return back()->with('success', 'Profil berhasil diperbarui.');
        }

        return view('admin.settings-profile', $this->adminPageData([
            'pageTitle' => 'Profil Admin',
            'pageSubtitle' => 'Kelola informasi profil akun Anda.',
            'currentPage' => 'settings-profile.php',
            'adminData' => $admin,
            'showBackButton' => true,
        ]));
    }



    public function settingsPassword(Request $request)
    {
        $adminId = (int) session('admin_id');
        $admin = Admin::find($adminId);

        if ($admin === null) {
            return redirect('/admin/login.php')->with('error', 'Session admin tidak valid.');
        }

        if ($request->isMethod('post')) {
            $oldPassword = (string) $request->input('current_password', '');
            $newPassword = (string) $request->input('new_password', '');
            $passwordConfirmation = (string) $request->input('confirm_password', '');

            if ($oldPassword === '' || $newPassword === '' || $passwordConfirmation === '') {
                return back()->with('error', 'Semua kolom password wajib diisi.');
            }

            $stored = (string) $admin->kata_sandi;
            $valid = $this->looksLikeHash($stored) ? password_verify($oldPassword, $stored) : hash_equals($stored, $oldPassword);

            if (!$valid) {
                return back()->with('error', 'Password lama salah.');
            }

            if ($newPassword !== $passwordConfirmation) {
                return back()->with('error', 'Konfirmasi password baru tidak cocok.');
            }

            if (strlen($newPassword) < 6) {
                return back()->with('error', 'Password baru minimal 6 karakter.');
            }

            $admin->kata_sandi = password_hash($newPassword, PASSWORD_DEFAULT);
            $admin->save();

            return back()->with('success', 'Password berhasil diperbarui.');
        }

        return view('admin.settings-password', $this->adminPageData([
            'pageTitle' => 'Keamanan Akun',
            'pageSubtitle' => 'Ganti password Anda secara berkala untuk menjaga keamanan akun.',
            'currentPage' => 'settings-password.php',
            'adminData' => $admin,
            'showBackButton' => true,
        ]));
    }


    private function getServiceIdByTitle(string $serviceTitle): int
    {
        $serviceTitle = trim($serviceTitle);
        if ($serviceTitle === '') {
            return 0;
        }

        $service = \App\Models\Service::query()->where('judul', $serviceTitle)->first();

        return $service?->id_layanan ? (int) $service->id_layanan : 0;
    }


// ================== EMAIL CHANGE VERIFICATION ==================
    public function verifyEmailChange(Request $request)
    {
        if (!$request->session()->has('email_change_otp_id') || !$request->session()->has('email_change_new_email')) {
            return redirect('/admin/settings.php')->with('error', 'Sesi verifikasi email telah kedaluwarsa.');
        }
        
        $adminId = (int) $request->session()->get('email_change_otp_id');
        $newEmail = (string) $request->session()->get('email_change_new_email');
        $admin = Admin::find($adminId);
        
        if ($admin === null || $admin->pending_email !== $newEmail) {
            $request->session()->forget(['email_change_otp_id', 'email_change_new_email']);
            return redirect('/admin/settings.php')->with('error', 'Data verifikasi email tidak valid.');
        }
        
        if ($request->isMethod('post')) {
            $otp = trim((string) $request->input('otp', ''));
            
            if ($otp === '') {
                return back()->with('error', 'Kode OTP wajib diisi.');
            }
            
            // Verify OTP
            if ($admin->otp_code !== $otp) {
                return back()->with('error', 'Kode OTP tidak valid.');
            }
            
            // Check expiry
            if ($admin->otp_expires_at && now()->gt($admin->otp_expires_at)) {
                $admin->otp_code = null;
                $admin->otp_expires_at = null;
                $admin->pending_email = null;
                $admin->save();
                $request->session()->forget(['email_change_otp_id', 'email_change_new_email']);
                return redirect('/admin/settings.php')->with('error', 'Kode OTP sudah kedaluwarsa.');
            }
            
            // OTP valid - update email
            $oldEmail = $admin->email;
            $admin->email = $newEmail;
            $admin->pending_email = null;
            $admin->otp_code = null;
            $admin->otp_expires_at = null;
            $admin->save();
            
            // Clear session
            $request->session()->forget(['email_change_otp_id', 'email_change_new_email']);
            
            return redirect('/admin/settings.php')->with('success', 'Email berhasil diganti dari ' . $oldEmail . ' menjadi ' . $newEmail . '.');
        }
        
return view('admin.settings.verify-email', $this->adminPageData([
            'pageTitle' => 'Verifikasi Ganti Email',
            'pageSubtitle' => 'Masukkan kode OTP yang dikirim ke email lama Anda.',
            'currentPage' => 'settings.php',
            'newEmail' => $newEmail,
        ]));
    }

    private function looksLikeHash(string $value): bool
    {
        return preg_match('/^\$(2y|2a|2b|argon2i|argon2id)\$/', $value) === 1;
    }

    /**
     * Menggabungkan data halaman dengan data global untuk layout admin.
     */
    protected function adminPageData(array $data): array
    {
        return array_merge([
            'adminName' => session('admin_name'),
            'authAdminId' => session('admin_id'),
            'isHubPage' => false,
            'showBackButton' => false,
        ], $data);
    }
}
