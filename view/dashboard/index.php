<?php include '../../includes/header.php'; ?>

<div class="section-title">
  <h2>Daftar Buku</h2>
  <form method="get" class="search-form" style="margin-top:20px;">
    <input type="text" name="search" placeholder="Cari judul buku..." value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>" class="search-input">
    <button type="submit" class="search-btn">Cari</button>
  </form>
</div>

<div class="product-container">
<?php
$filename = 'data/books.txt';
$search = isset($_GET['search']) ? strtolower(trim($_GET['search'])) : '';

if (file_exists($filename) && filesize($filename) > 0) {
    $handle = fopen($filename, 'r');
    $contents = fread($handle, filesize($filename));
    fclose($handle);

    $lines = explode("\n", $contents);
    foreach ($lines as $line) {
        if (trim($line) === '') continue;
        list($id, $judul, $harga, $gambar) = explode('|', $line);
        if ($search && strpos(strtolower($judul), $search) === false) continue;
        echo '<a href="detail.php?id=' . htmlspecialchars($id) . '" class="card">';
        echo '<div class="overlay"></div>';
        echo '<img src="assets/images/' . trim($gambar) . '" alt="' . htmlspecialchars($judul) . '">';
        echo '<div class="price-tag">' . htmlspecialchars($harga) . '</div>';
        echo '<div class="card-content">';
        echo '<h3>' . htmlspecialchars($judul) . '</h3>';
        echo '</div>';
        echo '</a>';
    }
} else {
    echo "<p>Data buku tidak tersedia.</p>";
}
?>
</div>

<?php
$pollFile = 'data/polling.txt';
$bookFile = 'data/books.txt';

$pollCounts = [];
$bookCovers = [];

// Ambil data buku dulu
if (file_exists($bookFile)) {
    $bookLines = file($bookFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($bookLines as $line) {
        list($id, $judul, , $gambar) = explode('|', $line);
        $bookCovers[$id] = [
            'judul' => $judul,
            'gambar' => $gambar
        ];
    }
}

// Hitung polling
if (file_exists($pollFile)) {
    $pollLines = file($pollFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($pollLines as $line) {
        list(, , $idBuku,) = explode('|', $line);
        if (!isset($pollCounts[$idBuku])) {
            $pollCounts[$idBuku] = 0;
        }
        $pollCounts[$idBuku]++;
    }
}

// Ambil 3 teratas
arsort($pollCounts);
$top3 = array_slice($pollCounts, 0, 3, true);

// Susun dalam urutan 2-1-3
$ranked = array_keys($top3);
$customOrder = [$ranked[1] ?? null, $ranked[0] ?? null, $ranked[2] ?? null];
?>

<div class="top3">
  <h2 class="top3-title">Buku Terpopuler</h2>
  <div class="top3-container">
    <?php foreach ($customOrder as $index => $idBuku): 
      if (!$idBuku || !isset($bookCovers[$idBuku])) continue;
      $judul = $bookCovers[$idBuku]['judul'];
      $gambar = $bookCovers[$idBuku]['gambar'];
      $rankNumber = $index === 0 ? 2 : ($index === 1 ? 1 : 3);
    ?>
      <div class="top-card rank<?= $rankNumber ?>">
        <a href="detail.php?id=<?= htmlspecialchars($idBuku) ?>" class="book-link">
          <?php if ($rankNumber === 1): ?>
            <div class="crown-container">
              <img src="../../assets/images/crown.png" alt="Crown" class="crown">
            </div>
          <?php endif; ?>
          <div class="book-cover">
            <img src="../../assets/images/<?= $gambar ?>" alt="<?= htmlspecialchars($judul) ?>">
          </div>
          <div class="book-info">
            <p class="judul"><?= htmlspecialchars($judul) ?></p>
            <div class="rank-badge">#<?= $rankNumber ?></div>
          </div>
        </a>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- Tombol Lihat Polling -->
  <div class="polling-button-wrapper">
    <a href="polling.php" class="btn-primary">Lihat Polling</a>
  </div>
</div>

<?php include '../../includes/footer.php'; ?>


