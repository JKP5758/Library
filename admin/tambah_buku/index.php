<?php
include '../../includes/header.php';
?>

<div class="form-container">
    <h2>Tambah Buku Baru</h2>
    <form action="proses_tambah_buku.php" method="post" enctype="multipart/form-data">
        <div class="form-group">
            <label for="judul">Judul Buku</label>
            <input type="text" name="judul" id="judul" required>
        </div>
        <div class="form-group">
            <label for="harga">Harga</label>
            <div class="input-harga-wrapper">
                <span class="input-harga-label">Rp</span>
                <input type="text" name="harga" id="harga" required autocomplete="off" inputmode="numeric" pattern="[0-9.]+">
            </div>
        </div>
        
        <div class="form-group">
            <label>Gambar Buku</label>
            <div class="image-upload-container">
                <div class="image-upload-item">
                    <div class="image-preview" id="preview1">
                        <span class="placeholder-text">Gambar 1</span>
                    </div>
                    <input type="file" name="gambar01" id="gambar01" accept="image/*" onchange="previewImage(this, 'preview1')" required>
                    <label for="gambar01" class="upload-label">
                        <i class="fas fa-upload"></i> Pilih Gambar 1
                    </label>
                </div>
                
                <div class="image-upload-item">
                    <div class="image-preview" id="preview2">
                        <span class="placeholder-text">Gambar 2</span>
                    </div>
                    <input type="file" name="gambar02" id="gambar02" accept="image/*" onchange="previewImage(this, 'preview2')">
                    <label for="gambar02" class="upload-label">
                        <i class="fas fa-upload"></i> Pilih Gambar 2
                    </label>
                </div>
                
                <div class="image-upload-item">
                    <div class="image-preview" id="preview3">
                        <span class="placeholder-text">Gambar 3</span>
                    </div>
                    <input type="file" name="gambar03" id="gambar03" accept="image/*" onchange="previewImage(this, 'preview3')">
                    <label for="gambar03" class="upload-label">
                        <i class="fas fa-upload"></i> Pilih Gambar 3
                    </label>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label for="deskripsi">Deskripsi</label>
            <textarea name="deskripsi" id="deskripsi" required></textarea>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-primary">Simpan</button>
            <a href="../index.php" class="btn-secondary">Batal</a>
        </div>
    </form>
</div>

<script>
function previewImage(input, previewId) {
    const preview = document.getElementById(previewId);
    const placeholder = preview.querySelector('.placeholder-text');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            if (placeholder) {
                placeholder.style.display = 'none';
            }
            preview.style.backgroundImage = `url('${e.target.result}')`;
            preview.classList.add('has-image');
        }
        
        reader.readAsDataURL(input.files[0]);
    }
}

// Format harga input
const hargaInput = document.getElementById('harga');
hargaInput.addEventListener('input', function(e) {
    let value = this.value.replace(/[^\d]/g, '');
    if (value) {
        value = parseInt(value, 10).toLocaleString('id-ID');
    }
    this.value = value;
});
</script>

<?php
include '../../includes/footer.php';
?>
