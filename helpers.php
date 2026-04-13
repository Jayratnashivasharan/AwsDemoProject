<?php
/**
 * helpers.php - Shared utility functions
 */

function h(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function stars(int $rating): string {
    $out = '';
    for ($i = 1; $i <= 5; $i++) {
        $out .= $i <= $rating ? '<span class="star filled">★</span>' : '<span class="star">☆</span>';
    }
    return $out;
}

function timeAgo(string $datetime): string {
    $now  = new DateTime();
    $then = new DateTime($datetime);
    $diff = $now->diff($then);
    if ($diff->y > 0) return $diff->y . ' year'  . ($diff->y > 1 ? 's' : '') . ' ago';
    if ($diff->m > 0) return $diff->m . ' month' . ($diff->m > 1 ? 's' : '') . ' ago';
    if ($diff->d > 0) return $diff->d . ' day'   . ($diff->d > 1 ? 's' : '') . ' ago';
    if ($diff->h > 0) return $diff->h . ' hour'  . ($diff->h > 1 ? 's' : '') . ' ago';
    if ($diff->i > 0) return $diff->i . ' min'   . ($diff->i > 1 ? 's' : '') . ' ago';
    return 'just now';
}

function statusBadge(string $status): string {
    $map = [
        'new'      => ['label' => 'New',      'class' => 'badge-new'],
        'reviewed' => ['label' => 'Reviewed', 'class' => 'badge-reviewed'],
        'archived' => ['label' => 'Archived', 'class' => 'badge-archived'],
    ];
    $s = $map[$status] ?? ['label' => ucfirst($status), 'class' => 'badge-new'];
    return '<span class="badge ' . $s['class'] . '">' . $s['label'] . '</span>';
}
