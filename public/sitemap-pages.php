<?php
header("Content-type: application/xml; charset=UTF-8");

$pages = [
  "https://omnireferrals.com",
  "https://omnireferrals.com/pricing",
  "https://omnireferrals.com/listings"
];

echo "<?xml version='1.0' encoding='UTF-8'?>";
echo "<urlset xmlns='http://www.sitemaps.org/schemas/sitemap/0.9'>";

foreach ($pages as $page) {
  $pageEscaped = htmlspecialchars($page, ENT_QUOTES | ENT_XML1, 'UTF-8');
  echo "<url><loc>{$pageEscaped}</loc></url>";
}

echo "</urlset>";
?>

