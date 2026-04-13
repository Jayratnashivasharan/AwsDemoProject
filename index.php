<?php
/**
 * index.php - Feedback submission form
 */
require_once __DIR__ . '/helpers.php';

$success = $error = '';

if (isset($_GET['status'])) {
    if ($_GET['status'] === 'ok')    $success = 'Thank you! Your feedback has been submitted successfully.';
    if ($_GET['status'] === 'error') $error   = 'Something went wrong. Please try again.';
    if ($_GET['status'] === 'dup')   $error   = 'You have already submitted feedback with this email recently.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Share Feedback — FeedbackHub</title>
  <link rel="stylesheet" href="/feedback/css/style.css">
</head>
<body>

<nav>
  <div class="nav-inner">
    <a href="/feedback/" class="nav-brand"><span class="dot"></span> FeedbackHub</a>
    <ul class="nav-links">
      <li><a href="/feedback/" class="active">Submit</a></li>
      <li><a href="/feedback/view.php">View All</a></li>
      <li><a href="/feedback/admin/">Admin</a></li>
    </ul>
  </div>
</nav>

<div class="page-hero">
  <h1>Share your <em>thoughts</em></h1>
  <p>We read every message. Your feedback shapes what we build next.</p>
</div>

<div class="container" style="padding-bottom:60px">

  <?php if ($success): ?>
    <div class="alert alert-success">✓ <?= h($success) ?></div>
  <?php endif; ?>
  <?php if ($error): ?>
    <div class="alert alert-error">✕ <?= h($error) ?></div>
  <?php endif; ?>

  <div class="card" style="max-width:560px;margin:0 auto">
    <div class="card-header">
      <h2>Feedback Form</h2>
    </div>
    <div class="card-body">
      <form action="/feedback/submit.php" method="POST" novalidate>

        <div class="form-group">
          <label for="name">Full Name <span style="color:var(--accent)">*</span></label>
          <input type="text" id="name" name="name" placeholder="e.g. Priya Sharma"
                 maxlength="120" required autocomplete="name">
        </div>

        <div class="form-group">
          <label for="email">Email Address <span style="color:var(--accent)">*</span></label>
          <input type="email" id="email" name="email" placeholder="you@example.com"
                 maxlength="200" required autocomplete="email">
        </div>

        <div class="form-group">
          <label>Rating</label>
          <div class="star-rating">
            <input type="radio" id="s5" name="rating" value="5" checked>
            <label for="s5" title="Excellent">★</label>
            <input type="radio" id="s4" name="rating" value="4">
            <label for="s4" title="Good">★</label>
            <input type="radio" id="s3" name="rating" value="3">
            <label for="s3" title="Average">★</label>
            <input type="radio" id="s2" name="rating" value="2">
            <label for="s2" title="Poor">★</label>
            <input type="radio" id="s1" name="rating" value="1">
            <label for="s1" title="Terrible">★</label>
          </div>
        </div>

        <div class="form-group">
          <label for="message">Message <span style="color:var(--accent)">*</span></label>
          <textarea id="message" name="message" placeholder="Tell us about your experience…"
                    maxlength="2000" required></textarea>
        </div>

        <button type="submit" class="btn btn-primary btn-full">
          ↗ Submit Feedback
        </button>

      </form>
    </div>
  </div>

</div>

<footer>
  &copy; <?= date('Y') ?> FeedbackHub — Built on LAMP Stack
</footer>

</body>
</html>
