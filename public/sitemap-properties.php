<?php
header("Content-type: application/xml; charset=UTF-8");

/* Example properties (DB se aayega real project me) */
$properties = [
 ["slug" => "dubai-villa-001", "date" => "2026-06-11"]
];

echo "<?xml version='1.0' encoding='UTF-8'?>";
echo "<urlset xmlns='http://www.sitemaps.org/schemas/sitemap/0.9'>";

foreach ($properties as $p) {
  $slug = htmlspecialchars($p['slug'] ?? '', ENT_QUOTES | ENT_XML1, 'UTF-8');
  $date = htmlspecialchars($p['date'] ?? '', ENT_QUOTES | ENT_XML1, 'UTF-8');

  echo "<url>";
  echo "<loc>https://omnireferrals.com/listings/{$slug}</loc>";
  echo "<lastmod>{$date}</lastmod>";
  echo "</url>";
}

echo "</urlset>";
?>

