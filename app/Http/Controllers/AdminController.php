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
        $statusFilter = (string) $request->query('status', 'all');
        if (! in_array($statusFilter, ['all', 'active', 'inactive'], true)) {
            $statusFilter = 'all';
        }
        $query = Service::query();
        if ($statusFilter !== 'all') {
            $query->where('aktif', $statusFilter === 'active');
        }
        return view('admin.services', $this->adminPageData([
            'pageTitle' => 'Kelola Layanan',
            'pageSubtitle' => 'Manajemen daftar layanan yang ditampilkan di website.',
            'currentPage' => 'services.php',
            'services' => $query->orderBy('urutan_tampil')->orderBy('id_layanan')->get(),
            'servicesTotalCount' => Service::count(),
            'servicesActiveCount' => Service::where('aktif', true)->count(),
            'servicesInactiveCount' => Service::where('aktif', false)->count(),
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
        $statusFilter = (string) $request->query('status', 'all');
        $serviceFilter = (int) $request->query('service', 0);
        if (! in_array($statusFilter, ['all', 'active', 'inactive'], true)) {
            $statusFilter = 'all';
        }
        $query = PriceItem::query();
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
            'priceItems' => $query->orderBy('urutan_tampil')->orderBy('id_harga_item')->get(),
            'pricesTotalCount' => PriceItem::count(),
            'pricesActiveCount' => PriceItem::where('aktif', true)->count(),
            'pricesInactiveCount' => PriceItem::where('aktif', false)->count(),
            'services' => Service::query()->where('aktif', true)->orderBy('urutan_tampil')->get(),
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
                $serviceName = trim((string) $request->input('service_name', ''));
                $sizeInfo = trim((string) $request->input('size_info', ''));
                $priceText = trim((string) $request->input('price_text', ''));
                
                // Validasi service_id
                $service = Service::find($serviceId);
                if ($service === null) {
                    return redirect('/admin/prices-form.php')->with('error', 'Pilih layanan yang tersedia.');
                }
                
                if ($serviceName === '' || $sizeInfo === '' || $priceText === '') {
                    return redirect('/admin/prices-form.php')->with('error', 'Semua field wajib diisi.');
                }
                $item = $id > 0 ? PriceItem::find($id) : new PriceItem();
                if ($item === null) {
                    return redirect('/admin/prices-form.php')->with('error', 'Data harga tidak ditemukan.');
                }
                $item->fill([
                    'id_layanan' => $serviceId,
                    'nama_layanan' => $serviceName,
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
        if ($request->isMethod('post')) {
            $action = (string) $request->input('action', '');
            if ($action === 'save') {
                $adminId = (int) $request->session()->get('admin_id', 0);
                $admin = Admin::find($adminId);
                if ($admin === null) {
                    return back()->with('error', 'Admin tidak ditemukan.');
                }
                $fullName = trim((string) $request->input('full_name', ''));
                if ($fullName === '') {
                    return back()->with('error', 'Nama lengkap wajib diisi.');
                }
                $admin->nama_lengkap = $fullName;
                $admin->save();
                return back()->with('success', 'Profil berhasil diperbarui.');
            }
        }
        $adminId = (int) $request->session()->get('admin_id', 0);
        $admin = Admin::find($adminId);
        return view('admin.settings-profile', $this->adminPageData([
            'pageTitle' => 'Pengaturan Profil',
            'pageSubtitle' => 'Kelola profil admin.',
            'currentPage' => 'settings.php',
            'admin' => $admin,
        ]));
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

    protected function adminPageData(array $data = []): array
    {
        return array_merge([
            'pageTitle' => 'Admin',
            'pageSubtitle' => '',
            'currentPage' => '',
        ], $data);
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

