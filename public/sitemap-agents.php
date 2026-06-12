<?php
header("Content-type: application/xml; charset=UTF-8");

$agents = [
  ["slug" => "john-smith-ny", "date" => "2026-06-12"],

];

echo "<?xml version='1.0' encoding='UTF-8'?>";
echo "<urlset xmlns='http://www.sitemaps.org/schemas/sitemap/0.9'>";

foreach ($agents as $a) {
  $slug = htmlspecialchars($a['slug'] ?? '', ENT_QUOTES | ENT_XML1, 'UTF-8');
  $date = htmlspecialchars($a['date'] ?? '', ENT_QUOTES | ENT_XML1, 'UTF-8');

  echo "<url>";
  echo "<loc>https://omnireferrals.com/agents/{$slug}</loc>";
  echo "<lastmod>{$date}</lastmod>";
  echo "</url>";
}

echo "</urlset>";
?>

