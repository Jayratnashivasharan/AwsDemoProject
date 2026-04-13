<?php
/**
 * view.php - Public page to view all submitted feedback
 */
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';

$per_page      = 9;
$current       = max(1, (int) ($_GET['page'] ?? 1));
$offset        = ($current - 1) * $per_page;
$filter_rating = (int) ($_GET['rating'] ?? 0);
$where         = $filter_rating > 0 ? "WHERE rating = $filter_rating" : "";

$total_row = $conn->query("SELECT COUNT(*) AS cnt FROM feedback $where")->fetch_assoc();
$total     = (int) $total_row['cnt'];
$pages     = (int) ceil($total / $per_page);

$stmt = $conn->prepare(
    "SELECT id, name, email, message, rating, status, created_at
     FROM feedback $where
     ORDER BY created_at DESC
     LIMIT ? OFFSET ?"
);
$stmt->bind_param('ii', $per_page, $offset);
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$avg_row = $conn->query("SELECT ROUND(AVG(rating),1) AS avg_r FROM feedback")->fetch_assoc();
$avg_r   = $avg_row['avg_r'] ?? '—';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>All Feedback — FeedbackHub</title>
  <link rel="stylesheet" href="/feedback/css/style.css">
</head>
<body>

<nav>
  <div class="nav-inner">
    <a href="/feedback/" class="nav-brand"><span class="dot"></span> FeedbackHub</a>
    <ul class="nav-links">
      <li><a href="/feedback/">Submit</a></li>
      <li><a href="/feedback/view.php" class="active">View All</a></li>
      <li><a href="/feedback/admin/">Admin</a></li>
    </ul>
  </div>
</nav>

<div class="page-hero">
  <h1>What people are <em>saying</em></h1>
  <p><?= $total ?> feedback entries — average rating <?= $avg_r ?> / 5</p>
</div>

<div class="container-wide" style="padding-bottom:20px">

  <div class="stats-bar">
    <a href="/feedback/view.php" class="stat-pill" style="text-decoration:none">
      <strong><?= $total ?></strong> Total
    </a>
    <?php for ($r = 5; $r >= 1; $r--):
      $rrow = $conn->query("SELECT COUNT(*) AS c FROM feedback WHERE rating=$r")->fetch_assoc();
    ?>
    <a href="/feedback/view.php?rating=<?= $r ?>" class="stat-pill" style="text-decoration:none;<?= $filter_rating===$r ? 'border-color:var(--accent);color:var(--accent)' : '' ?>">
      <?= str_repeat('★', $r) ?> <strong><?= $rrow['c'] ?></strong>
    </a>
    <?php endfor; ?>
  </div>

  <?php if (empty($rows)): ?>
    <div class="empty-state">
      <div class="icon">📭</div>
      <h3>No feedback yet</h3>
      <p>Be the first to share your thoughts!</p>
      <a href="/feedback/" class="btn btn-primary" style="margin-top:16px">Submit Feedback</a>
    </div>
  <?php else: ?>
  <div class="feedback-grid">
    <?php foreach ($rows as $row): ?>
    <div class="feedback-card">
      <div class="fc-header">
        <div class="fc-avatar"><?= mb_strtoupper(mb_substr($row['name'], 0, 1)) ?></div>
        <div class="fc-meta">
          <div class="fc-name"><?= h($row['name']) ?></div>
          <div class="fc-email"><?= h($row['email']) ?></div>
        </div>
        <?= statusBadge($row['status']) ?>
      </div>
      <p class="fc-message"><?= nl2br(h($row['message'])) ?></p>
      <div class="fc-footer">
        <span><?= stars($row['rating']) ?></span>
        <span><?= timeAgo($row['created_at']) ?></span>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <?php if ($pages > 1): ?>
  <div style="display:flex;gap:8px;justify-content:center;padding:20px 0 50px">
    <?php for ($p = 1; $p <= $pages; $p++): ?>
      <a href="?page=<?= $p ?>&rating=<?= $filter_rating ?>"
         class="btn <?= $p === $current ? 'btn-primary' : 'btn-secondary' ?> btn-sm"><?= $p ?></a>
    <?php endfor; ?>
  </div>
  <?php endif; ?>
  <?php endif; ?>

</div>

<footer>
  &copy; <?= date('Y') ?> FeedbackHub — Built on LAMP Stack
</footer>
</body>
</html>
