<?php
header("Content-type: application/xml; charset=UTF-8");

/* DATABASE connection yahan lagegi */
$posts = [
  ["slug" => "how-to-get-leads", "date" => "2026-06-12"],
  ["slug" => "real-estate-tips-2026", "date" => "2026-06-10"]
];

echo "<?xml version='1.0' encoding='UTF-8'?>";
echo "<urlset xmlns='http://www.sitemaps.org/schemas/sitemap/0.9'>";

foreach ($posts as $post) {
  $slug = htmlspecialchars($post['slug'] ?? '', ENT_QUOTES | ENT_XML1, 'UTF-8');
  $date = htmlspecialchars($post['date'] ?? '', ENT_QUOTES | ENT_XML1, 'UTF-8');

  echo "<url>";
  echo "<loc>https://omnireferrals.com/blog/{$slug}</loc>";
  echo "<lastmod>{$date}</lastmod>";
  echo "</url>";
}

echo "</urlset>";
?>

