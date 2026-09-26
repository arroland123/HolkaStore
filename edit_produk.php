<?php
session_start();
include 'koneksi.php';

// Cek parameter ID
$id_produk = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id_produk <= 0) {
    header("Location: admin.php");
    exit;
}

// Ambil data produk
$query = mysqli_query($conn, "SELECT * FROM produk WHERE id_produk = $id_produk");
if (mysqli_num_rows($query) == 0) {
    header("Location: admin.php");
    exit;
}
$produk = mysqli_fetch_assoc($query);

// Proses Update
if(isset($_POST['update'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    $harga = $_POST['harga'];
    $stok = $_POST['stok'];
    $kategori = $_POST['id_kategori'];
    
    // Ambil detail stok ukuran dan konversi ke JSON
    $stok_size = isset($_POST['stok_size']) ? $_POST['stok_size'] : [];
    $stok_size_filtered = [];
    foreach ($stok_size as $size => $qty) {
        $stok_size_filtered[$size] = max(0, intval($qty));
    }
    $stok_detail_json = json_encode($stok_size_filtered);
    
    $gambar_update = $produk['gambar'];
    if(!empty($_FILES['gambar']['name'])) {
        $gambar = $_FILES['gambar']['name'];
        $target = "assets/img/" . basename($gambar);
        if(move_uploaded_file($_FILES['gambar']['tmp_name'], $target)) {
            $gambar_update = mysqli_real_escape_string($conn, $gambar);
        }
    }
    
    $sql = "UPDATE produk SET 
            nama_produk = '$nama', 
            deskripsi = '$deskripsi', 
            harga = '$harga', 
            stok = '$stok', 
            id_kategori = '$kategori', 
            gambar = '$gambar_update',
            stok_detail = '$stok_detail_json'
            WHERE id_produk = $id_produk";
            
    if(mysqli_query($conn, $sql)) {
        echo "<script>alert('Produk berhasil diperbarui!'); window.location='admin.php';</script>";
    } else {
        echo "<script>alert('Gagal mengupdate produk: " . mysqli_error($conn) . "');</script>";
    }
}

// Decode stok detail untuk pre-fill
$stok_detail = json_decode($produk['stok_detail'], true) ?: [];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Edit Produk - Holka Store</title>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@300;400;500;600;700;800&display=swap" rel="stylesheet"/>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet"/>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        holka: {
                            primary: '#1e40af',
                            secondary: '#3b82f6',
                            accent: '#f8fafc',
                            success: '#10b981',
                            warning: '#f59e0b',
                            danger: '#ef4444'
                        }
                    },
                    fontFamily: {
                        sans: ['Manrope', 'sans-serif'],
                    },
                }
            }
        }
    </script>
    <style>
        html { font-size: 18px; }
        @media (min-width: 1024px) {
            html { font-size: 20px; }
        }
        body { background-color: #f1f5f9; }
    </style>
</head>
<body class="font-sans text-slate-900">

<header class="bg-white border-b border-slate-200 sticky top-0 z-50">
    <div class="container mx-auto px-4 lg:px-8">
        <div class="flex items-center justify-between h-20">
            <div class="logo flex items-center gap-2">
                <i class="fas fa-shopping-bag text-holka-primary text-xl"></i>
                <span class="text-2xl font-extrabold tracking-tight text-holka-primary">Holka Store</span>
            </div>
            <nav class="hidden md:flex items-center gap-8">
                <a class="text-slate-600 hover:text-holka-primary font-semibold transition-colors" href="index.php">
                    <i class="fas fa-home mr-1"></i> Toko
                </a>
                <a class="text-holka-primary border-b-2 border-holka-primary font-bold py-1" href="admin.php">
                    <i class="fas fa-user-lock mr-1"></i> Admin
                </a>
            </nav>
        </div>
    </div>
</header>

<main class="container mx-auto px-4 py-8 lg:px-8 max-w-4xl">
    <div class="mb-6 flex justify-between items-center">
        <h1 class="text-3xl font-extrabold text-slate-800">Edit Produk</h1>
        <a href="admin.php" class="bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold py-2 px-4 rounded-xl transition">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    <section class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-6">
            <form action="" method="POST" enctype="multipart/form-data" class="space-y-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="flex flex-col gap-1.5">
                        <label class="text-sm font-bold text-slate-600" for="nama">Nama Produk</label>
                        <input type="text" id="nama" name="nama" value="<?= htmlspecialchars($produk['nama_produk']) ?>" required class="border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-holka-secondary focus:border-transparent transition"/>
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="text-sm font-bold text-slate-600" for="kategori">Kategori</label>
                        <select id="kategori" name="id_kategori" required class="border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-holka-secondary focus:border-transparent transition">
                            <option value="">-- Pilih Kategori --</option>
                            <?php
                            $kats = mysqli_query($conn, "SELECT * FROM kategori");
                            while($k = mysqli_fetch_assoc($kats)) {
                                $selected = $k['id_kategori'] == $produk['id_kategori'] ? 'selected' : '';
                                echo "<option value='" . $k['id_kategori'] . "' $selected>" . htmlspecialchars($k['nama_kategori']) . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <div class="flex flex-col gap-1.5">
                    <label class="text-sm font-bold text-slate-600" for="deskripsi">Deskripsi Produk</label>
                    <textarea id="deskripsi" name="deskripsi" rows="4" required class="border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-holka-secondary focus:border-transparent transition resize-none"><?= htmlspecialchars($produk['deskripsi']) ?></textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="flex flex-col gap-1.5">
                        <label class="text-sm font-bold text-slate-600" for="harga">Harga (Rp)</label>
                        <input type="number" id="harga" name="harga" value="<?= $produk['harga'] ?>" required class="border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-holka-secondary focus:border-transparent transition"/>
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="text-sm font-bold text-slate-600" for="stok">Total Stok (Otomatis)</label>
                        <input type="number" id="stok" name="stok" value="<?= $produk['stok'] ?>" required readonly class="border border-slate-200 bg-slate-50 rounded-xl px-4 py-2.5 text-sm font-medium text-slate-500 focus:outline-none transition"/>
                    </div>
                </div>

                <!-- Detail Stok per Ukuran -->
                <div class="border border-slate-100 bg-slate-50/50 rounded-2xl p-5 space-y-4">
                    <h4 class="text-sm font-bold text-slate-700 flex items-center gap-1.5 border-b border-slate-100 pb-2">
                        <i class="fas fa-cubes text-holka-secondary"></i> Stok per Ukuran
                    </h4>
                    
                    <div id="size-baju-group" class="grid grid-cols-5 gap-3">
                        <?php foreach (['S', 'M', 'L', 'XL', 'XXL'] as $sz): ?>
                        <div class="flex flex-col gap-1">
                            <label class="text-xs font-bold text-slate-500 text-center" for="stok_<?= $sz ?>">Ukuran <?= $sz ?></label>
                            <input type="number" id="stok_<?= $sz ?>" name="stok_size[<?= $sz ?>]" min="0" value="<?= isset($stok_detail[$sz]) ? $stok_detail[$sz] : 0 ?>"
                                   class="size-stock-input text-center border border-slate-200 rounded-xl px-2 py-1.5 text-sm font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-holka-secondary focus:border-transparent transition"/>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <div id="size-celana-group" class="grid grid-cols-3 sm:grid-cols-9 gap-3 hidden">
                        <?php foreach (range(28, 36) as $sz): ?>
                        <div class="flex flex-col gap-1">
                            <label class="text-xs font-bold text-slate-500 text-center" for="stok_<?= $sz ?>">No. <?= $sz ?></label>
                            <input type="number" id="stok_<?= $sz ?>" name="stok_size[<?= $sz ?>]" min="0" value="<?= isset($stok_detail[$sz]) ? $stok_detail[$sz] : 0 ?>" disabled
                                   class="size-stock-input text-center border border-slate-200 rounded-xl px-2 py-1.5 text-sm font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-holka-secondary focus:border-transparent transition"/>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="flex flex-col gap-1.5">
                    <label class="text-sm font-bold text-slate-600" for="gambar">Gambar Produk (Kosongkan jika tidak ingin mengubah)</label>
                    <div class="mb-2">
                        <img src="assets/img/<?= htmlspecialchars($produk['gambar']) ?>" alt="Current Product Image" class="w-32 h-32 object-cover rounded-lg border border-slate-200">
                    </div>
                    <input type="file" id="gambar" name="gambar" accept="image/*" class="border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-holka-secondary transition file:mr-4 file:py-1 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-blue-100 file:text-holka-primary hover:file:bg-blue-200"/>
                </div>

                <div class="pt-2">
                    <button type="submit" name="update" class="bg-holka-primary hover:bg-blue-800 text-white font-bold px-8 py-3 rounded-xl transition-all flex items-center gap-2 shadow-sm w-full justify-center">
                        <i class="fas fa-save"></i> Simpan Perubahan Produk
                    </button>
                </div>
            </form>
        </div>
    </section>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const kategoriSelect = document.getElementById('kategori');
    
    if (kategoriSelect) {
        kategoriSelect.addEventListener('change', toggleSizeFields);
        toggleSizeFields(); // Inisialisasi saat load
    }
    
    document.querySelectorAll('.size-stock-input').forEach(input => {
        input.addEventListener('input', calculateTotalStok);
        input.addEventListener('change', calculateTotalStok);
    });
});

function toggleSizeFields() {
    const select = document.getElementById('kategori');
    if (!select) return;
    
    const selectedOption = select.options[select.selectedIndex];
    const categoryName = selectedOption ? selectedOption.text.toLowerCase() : '';
    
    const isCelana = categoryName.includes('celana') || categoryName.includes('jeans') || select.value == '3';
    
    const sizeBajuGroup = document.getElementById('size-baju-group');
    const sizeCelanaGroup = document.getElementById('size-celana-group');
    
    if (isCelana) {
        sizeCelanaGroup.classList.remove('hidden');
        sizeBajuGroup.classList.add('hidden');
        enableGroupInputs(sizeCelanaGroup, true);
        enableGroupInputs(sizeBajuGroup, false);
    } else {
        sizeCelanaGroup.classList.add('hidden');
        sizeBajuGroup.classList.remove('hidden');
        enableGroupInputs(sizeCelanaGroup, false);
        enableGroupInputs(sizeBajuGroup, true);
    }
    
    calculateTotalStok();
}

function enableGroupInputs(container, enable) {
    if (!container) return;
    const inputs = container.querySelectorAll('input');
    inputs.forEach(input => {
        input.disabled = !enable;
        if (!enable && !input.hasAttribute('data-prefilled')) {
            // Kita tidak reset value menjadi 0 saat di-disable karena bisa jadi produknya memang celana 
            // dan input bajunya disembunyikan. Tapi untuk prevent submission, input disabled tidak akan terkirim via POST.
        }
    });
}

function calculateTotalStok() {
    const totalStokInput = document.getElementById('stok');
    if (!totalStokInput) return;
    
    let total = 0;
    const activeInputs = document.querySelectorAll('.size-stock-input:not(:disabled)');
    
    activeInputs.forEach(input => {
        const val = parseInt(input.value);
        if (!isNaN(val) && val >= 0) {
            total += val;
        }
    });
    
    totalStokInput.value = total;
}
</script>
</body>
</html>
