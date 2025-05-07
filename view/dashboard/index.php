<?php include '../../includes/header.php'; ?>

<div class="section-title">
  <h2>Daftar Buku</h2>
  <form method="get" class="search-form" style="margin-top:20px;">
    <input type="text" name="search" placeholder="Cari judul buku..." value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>" class="search-input">
    <select name="genre" class="search-input" style="appearance: none;">
        <option value="">Semua Genre</option>
        <?php
        // Include database connection
        include '../../includes/db.php';

        $genreQuery = "SELECT id_genre, genre FROM genre ORDER BY genre ASC";
        $genreResult = mysqli_query($conn, $genreQuery);
        $genres = [];
        if ($genreResult && mysqli_num_rows($genreResult) > 0) {
            while ($genreRow = mysqli_fetch_assoc($genreResult)) {
                $genres[] = $genreRow;
            }
        }
        mysqli_close($conn);
        ?>
        <?php foreach ($genres as $genre): ?>
            <option value="<?= htmlspecialchars($genre['id_genre']) ?>" <?= isset($_GET['genre']) && $_GET['genre'] == $genre['id_genre'] ? 'selected' : '' ?>><?= htmlspecialchars($genre['genre']) ?></option>
        <?php endforeach; ?>
    </select>
    <button type="submit" class="search-btn">Cari</button>
  </form>
</div>

<div class="product-container">
<?php
// Include database connection
include '../../includes/db.php';

$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, trim($_GET['search'])) : '';
$genreFilter = isset($_GET['genre']) ? mysqli_real_escape_string($conn, $_GET['genre']) : '';

// Query to fetch books from database
$query = "SELECT b.id_buku, b.judul, b.harga, b.gambar01 FROM books b";
if (!empty($genreFilter)) {
    $query .= " JOIN genre_relasi gr ON b.id_buku = gr.id_book WHERE gr.id_genre = '$genreFilter'";
    if (!empty($search)) {
        $query .= " AND LOWER(b.judul) LIKE LOWER('%$search%')";
    }
} else if (!empty($search)) {
    $query .= " WHERE LOWER(b.judul) LIKE LOWER('%$search%')";
}

$result = mysqli_query($conn, $query);

if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        echo '<a href="../buku?id=' . htmlspecialchars($row['id_buku']) . '" class="card">';
        echo '<div class="overlay"></div>';
        echo '<img src="../../assets/images/cover/' . trim($row['gambar01']) . '" alt="' . htmlspecialchars($row['judul']) . '">';
        echo '<div class="price-tag">Rp.' . number_format($row['harga'], 0, ',', '.') . '</div>';
        echo '<div class="card-content">';
        echo '<h3>' . htmlspecialchars($row['judul']) . '</h3>';
        echo '</div>';
        echo '</a>';
    }
} else {
    echo "<p>Data buku tidak tersedia.</p>";
}

// Close database connection
mysqli_close($conn);
?>
</div>

<?php
// Include database connection again for polling data
include '../../includes/db.php';

$pollCounts = [];
$bookCovers = [];

// Fetch book data from database
$bookQuery = "SELECT id_buku, judul, gambar01 FROM books";
$bookResult = mysqli_query($conn, $bookQuery);

if ($bookResult && mysqli_num_rows($bookResult) > 0) {
    while ($row = mysqli_fetch_assoc($bookResult)) {
        $bookCovers[$row['id_buku']] = [
            'judul' => $row['judul'],
            'gambar01' => $row['gambar01']
        ];
    }
}

// Fetch polling data from database
$pollQuery = "SELECT id_buku, COUNT(*) as count FROM polling GROUP BY id_buku";
$pollResult = mysqli_query($conn, $pollQuery);

if ($pollResult && mysqli_num_rows($pollResult) > 0) {
    while ($row = mysqli_fetch_assoc($pollResult)) {
        $pollCounts[$row['id_buku']] = $row['count'];
    }
}

// Get top 3 books
arsort($pollCounts);
$top3 = array_slice($pollCounts, 0, 3, true);

// Arrange in 2-1-3 order
$ranked = array_keys($top3);
$customOrder = [$ranked[1] ?? null, $ranked[0] ?? null, $ranked[2] ?? null];

// Close database connection
mysqli_close($conn);
?>

<div class="top3">
  <h2 class="top3-title">Buku Terpopuler</h2>
  <div class="top3-container">
    <?php foreach ($customOrder as $index => $idBuku): 
      if (!$idBuku || !isset($bookCovers[$idBuku])) continue;
      $judul = $bookCovers[$idBuku]['judul'];
      $gambar = $bookCovers[$idBuku]['gambar01'];
      $rankNumber = $index === 0 ? 2 : ($index === 1 ? 1 : 3);
    ?>
      <div class="top-card rank<?= $rankNumber ?>">
        <a href="../buku/?id=<?= htmlspecialchars($idBuku) ?>" class="book-link">
          <?php if ($rankNumber === 1): ?>
            <div class="crown-container">
              <img src="../../assets/images/crown.png" alt="Crown" class="crown">
            </div>
          <?php endif; ?>
          <div class="book-cover">
            <img src="../../assets/images/cover/<?= $gambar ?>" alt="<?= htmlspecialchars($judul) ?>">
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
    <a href="../polling" class="btn-primary">Lihat Polling</a>
  </div>
</div>

<?php include '../../includes/footer.php'; ?>


