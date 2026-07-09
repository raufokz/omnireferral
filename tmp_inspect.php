<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$p = App\Models\ServiceSeoPage::published()->first();
if (! $p) { echo "NO PUBLISHED PAGE\n"; exit; }

echo 'slug=' . $p->slug . PHP_EOL;
echo 'seo_title=' . var_export($p->seo_title, true) . PHP_EOL;
echo 'meta_desc=' . var_export($p->meta_description, true) . PHP_EOL;
echo 'canonical=' . var_export($p->canonical_url, true) . PHP_EOL;
echo 'is_pub=' . var_export($p->is_published, true) . PHP_EOL;
echo 'title=' . $p->title . PHP_EOL;
echo 'updated_at=' . $p->updated_at . PHP_EOL;

echo '--- FAQS ---' . PHP_EOL;
foreach ($p->getFaqs() as $f) { echo 'Q: ' . ($f['question'] ?? '') . PHP_EOL; }

echo '--- SECTIONS ---' . PHP_EOL;
foreach ($p->getSections() as $s) { echo 'H: ' . ($s['heading'] ?? '') . PHP_EOL; }
