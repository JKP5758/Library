<?php
include '../../includes/header.php';
include '../../includes/db.php';

// Cek login
$isLoggedIn = isset($_SESSION['username']);
$hasVoted = false;

if ($isLoggedIn) {
    $username = $_SESSION['username'];
    $userId = $_SESSION['id_user'];

    // Cek apakah user sudah melakukan polling
    $checkQuery = "SELECT id_polling FROM polling   WHERE id_user = ?";
    $checkStmt = mysqli_prepare($conn, $checkQuery);
    mysqli_stmt_bind_param($checkStmt, "i", $userId);
    mysqli_stmt_execute($checkStmt);
    $checkResult = mysqli_stmt_get_result($checkStmt);
    $hasVoted = mysqli_num_rows($checkResult) > 0;
}

// Fetch books from database
$booksQuery = "SELECT id_buku, judul, gambar01 FROM books";
$booksResult = mysqli_query($conn, $booksQuery);
$books = [];
while ($row = mysqli_fetch_assoc($booksResult)) {
    $books[] = [
        'id' => $row['id_buku'],
        'judul' => $row['judul'],
        'gambar' => $row['gambar01']
    ];
}

// Fetch poll counts from database
$pollQuery = "SELECT b.id_buku, b.judul, b.gambar01, COUNT(p.id_polling) as jumlah 
              FROM books b 
              LEFT JOIN polling p ON b.id_buku = p.id_buku 
              GROUP BY b.id_buku, b.judul, b.gambar01";
$pollResult = mysqli_query($conn, $pollQuery);
$pollCounts = [];
while ($row = mysqli_fetch_assoc($pollResult)) {
    $pollCounts[$row['id_buku']] = [
        'judul' => $row['judul'],
        'jumlah' => $row['jumlah'],
        'gambar' => $row['gambar01']
    ];
}

// Close database connection
mysqli_close($conn);
?>

<div class="site-content">
    <div class="form-container" style="max-width: 1000px;">
        <h2>Polling Buku Favorit</h2>
        
        <div class="polling-container">
            <div class="poll-chart">
                <canvas id="pollChart" width="50%" height="50%" ></canvas>
            </div>

            <div class="poll-form">
                <?php if (!$isLoggedIn): ?>
                    <div class="alert alert-info">
                        <p>Harap Login terlebih dahulu untuk melakukan polling</p>
                    </div>
                <?php elseif ($hasVoted): ?>
                    <div class="alert alert-info">
                        <p>Anda sudah melakukan polling sebelumnya.</p>
                    </div>
                <?php else: ?>
                    <div class="form-group">
                        <h3>Pilih Buku Favoritmu</h3>
                        <form action="proses_polling.php" method="POST">
                            <select name="buku" required>
                                <option value="">-- Pilih Buku --</option>
                                <?php foreach ($books as $book): ?>
                                    <option value="<?= $book['id'] ?>|<?= $book['judul'] ?>"><?= $book['judul'] ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit">Kirim Polling</button>
                        </form>
                    </div>
                <?php endif; ?>

                <div class="hasil_polling">
                    <h3>Hasil Polling</h3>
                    <div class="poll-results">
                        <?php
                        // Sort by jumlah DESC
                        uasort($pollCounts, function ($a, $b) {
                            return $b['jumlah'] <=> $a['jumlah'];
                        });

                        foreach ($pollCounts as $id => $data):
                        ?>
                        <a href="../dashboard/detail.php?id=<?= $id ?>" class="poll-result-item">
                            <img src="../../assets/images/cover/<?= htmlspecialchars($data['gambar']) ?>" 
                                 alt="<?= htmlspecialchars($data['judul']) ?>">
                            <div class="poll-result-info">
                                <h4><?= htmlspecialchars($data['judul']) ?></h4>
                                <p><?= $data['jumlah'] ?> suara</p>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('pollChart').getContext('2d');
const chart = new Chart(ctx, {
    type: 'pie',
    data: {
        labels: <?= json_encode(array_column($pollCounts, 'judul')) ?>,
        datasets: [{
            data: <?= json_encode(array_column($pollCounts, 'jumlah')) ?>,
            backgroundColor: [
                '#FF6384', '#36A2EB', '#FFCE56', '#8BC34A', '#FF9800',
                '#9C27B0', '#3F51B5', '#009688', '#795548', '#607D8B'
            ],
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom'
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        let total = context.dataset.data.reduce((a, b) => a + b, 0);
                        let val = context.parsed;
                        let percent = ((val / total) * 100).toFixed(1);
                        return `${context.label}: ${val} suara (${percent}%)`;
                    }
                }
            }
        }
    }
});
</script>

<?php include '../../includes/footer.php'; ?>
