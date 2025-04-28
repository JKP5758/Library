<?php
include 'includes/header.php';

// Cek login
$isLoggedIn = isset($_SESSION['username']);
$hasVoted = false;

if ($isLoggedIn) {
    $username = $_SESSION['username'];
    $userId = $_SESSION['id'];

    // Cek apakah user sudah melakukan polling
    if (file_exists('data/polling.txt')) {
        $pollLines = file('data/polling.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($pollLines as $poll) {
            list($uid, $uname, $id_buku, $nama_buku) = explode('|', $poll);
            if ($uid === $userId) {
                $hasVoted = true;
                break;
            }
        }
    }
}

$books = [];
if (file_exists('data/books.txt')) {
    $lines = file('data/books.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $parts = explode('|', $line);
        $books[] = [
            'id' => $parts[0],
            'judul' => $parts[1],
            'gambar' => $parts[3]
        ];
    }
}

// Baca polling.txt
$pollCounts = [];
if (file_exists('data/polling.txt')) {
    $pollLines = file('data/polling.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($pollLines as $poll) {
        list($uid, $uname, $id_buku, $nama_buku) = explode('|', $poll);
        if (!isset($pollCounts[$id_buku])) {
            // Cari gambar buku dari array books
            $bookImage = '';
            foreach ($books as $book) {
                if ($book['id'] === $id_buku) {
                    $bookImage = $book['gambar'];
                    break;
                }
            }
            $pollCounts[$id_buku] = [
                'judul' => $nama_buku,
                'jumlah' => 0,
                'gambar' => $bookImage
            ];
        }
        $pollCounts[$id_buku]['jumlah']++;
    }
}
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
                        <a href="detail.php?id=<?= $id ?>" class="poll-result-item">
                            <img src="assets/images/<?= htmlspecialchars($data['gambar']) ?>" 
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

<?php include 'includes/footer.php'; ?>
