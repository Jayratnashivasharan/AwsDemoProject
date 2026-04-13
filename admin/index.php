<?php
/**
 * admin/index.php - Admin panel: list, update status, delete feedback
 */
require_once dirname(__DIR__) . '/db.php';
require_once dirname(__DIR__) . '/helpers.php';

$msg = $msg_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id     = (int) ($_POST['id'] ?? 0);

    if ($id > 0) {
        if ($action === 'delete') {
            $s = $conn->prepare("DELETE FROM feedback WHERE id = ?");
            $s->bind_param('i', $id);
            $s->execute();
            $msg = 'Feedback #' . $id . ' deleted.';
            $msg_type = 'alert-success';
        }
        if ($action === 'status') {
            $new_status = $_POST['new_status'] ?? 'new';
            if (in_array($new_status, ['new', 'reviewed', 'archived'])) {
                $s = $conn->prepare("UPDATE feedback SET status = ? WHERE id = ?");
                $s->bind_param('si', $new_status, $id);
                $s->execute();
                $msg = 'Status updated.';
                $msg_type = 'alert-info';
            }
        }
    }
}

$search = trim($_GET['q'] ?? '');
if ($search !== '') {
    $like = '%' . $conn->real_escape_string($search) . '%';
    $res  = $conn->query("SELECT * FROM feedback WHERE name LIKE '$like' OR email LIKE '$like' OR message LIKE '$like' ORDER BY created_at DESC");
} else {
    $res = $conn->query("SELECT * FROM feedback ORDER BY created_at DESC");
}
$rows = $res->fetch_all(MYSQLI_ASSOC);

$stats    = $conn->query("SELECT status, COUNT(*) AS c FROM feedback GROUP BY status")->fetch_all(MYSQLI_ASSOC);
$stat_map = array_column($stats, 'c', 'status');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Panel — FeedbackHub</title>
  <link rel="stylesheet" href="/feedback/css/style.css">
</head>
<body>

<nav>
  <div class="nav-inner">
    <a href="/feedback/" class="nav-brand"><span class="dot"></span> FeedbackHub</a>
    <ul class="nav-links">
      <li><a href="/feedback/">Submit</a></li>
      <li><a href="/feedback/view.php">View All</a></li>
      <li><a href="/feedback/admin/" class="active">Admin</a></li>
    </ul>
  </div>
</nav>

<div class="container-wide" style="padding:40px 20px 60px">

  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:28px;flex-wrap:wrap;gap:12px">
    <h1 style="font-family:var(--font-display);font-weight:400;font-size:2rem">Admin Panel</h1>
    <a href="/feedback/" class="btn btn-secondary btn-sm">+ New Feedback</a>
  </div>

  <?php if ($msg): ?>
    <div class="alert <?= $msg_type ?>"><?= h($msg) ?></div>
  <?php endif; ?>

  <div class="stats-bar" style="margin-bottom:24px">
    <div class="stat-pill"><strong><?= count($rows) ?></strong> Shown</div>
    <div class="stat-pill" style="color:#1a5fa8"><strong><?= $stat_map['new'] ?? 0 ?></strong> New</div>
    <div class="stat-pill" style="color:var(--success)"><strong><?= $stat_map['reviewed'] ?? 0 ?></strong> Reviewed</div>
    <div class="stat-pill" style="color:#6b5d52"><strong><?= $stat_map['archived'] ?? 0 ?></strong> Archived</div>
  </div>

  <form method="GET" style="margin-bottom:20px;display:flex;gap:8px">
    <input type="text" name="q" value="<?= h($search) ?>" placeholder="Search by name, email, or message…" style="max-width:360px">
    <button type="submit" class="btn btn-secondary btn-sm">Search</button>
    <?php if ($search): ?><a href="/feedback/admin/" class="btn btn-secondary btn-sm">Clear</a><?php endif; ?>
  </form>

  <div class="card">
    <div class="table-wrap">
      <?php if (empty($rows)): ?>
        <div class="empty-state" style="padding:40px">
          <div class="icon">🔍</div>
          <h3>No results</h3>
        </div>
      <?php else: ?>
      <table>
        <thead>
          <tr>
            <th>#</th><th>Name</th><th>Email</th><th>Message</th>
            <th>Rating</th><th>Status</th><th>Date</th><th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rows as $row): ?>
          <tr>
            <td style="color:var(--text-muted);font-size:.82rem"><?= $row['id'] ?></td>
            <td><strong><?= h($row['name']) ?></strong></td>
            <td style="color:var(--text-muted);font-size:.85rem"><?= h($row['email']) ?></td>
            <td class="td-msg" title="<?= h($row['message']) ?>"><?= h($row['message']) ?></td>
            <td><?= stars($row['rating']) ?></td>
            <td><?= statusBadge($row['status']) ?></td>
            <td style="font-size:.82rem;color:var(--text-muted);white-space:nowrap"><?= date('d M Y', strtotime($row['created_at'])) ?></td>
            <td>
              <div style="display:flex;gap:6px;align-items:center;flex-wrap:wrap">
                <form method="POST" style="display:inline">
                  <input type="hidden" name="action" value="status">
                  <input type="hidden" name="id" value="<?= $row['id'] ?>">
                  <select name="new_status" style="font-size:.78rem;padding:4px 8px;border-radius:4px;border:1.5px solid var(--border);background:var(--surface)">
                    <option value="new"      <?= $row['status']==='new'      ? 'selected':'' ?>>New</option>
                    <option value="reviewed" <?= $row['status']==='reviewed' ? 'selected':'' ?>>Reviewed</option>
                    <option value="archived" <?= $row['status']==='archived' ? 'selected':'' ?>>Archived</option>
                  </select>
                  <button type="submit" class="btn btn-sm btn-secondary" style="margin-left:2px">Save</button>
                </form>
                <form method="POST" onsubmit="return confirm('Delete this feedback?')" style="display:inline">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="id" value="<?= $row['id'] ?>">
                  <button type="submit" class="btn btn-danger">✕ Delete</button>
                </form>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>
    </div>
  </div>
</div>

<footer>
  &copy; <?= date('Y') ?> FeedbackHub — Admin Panel
</footer>
</body>
</html>
