<?php

$cssFile = __DIR__ . '/resources/css/app.css';
$css = file_get_contents($cssFile);

// Define modules based on comment block titles
$modules = [
    'pricing' => ['PRICING PAGE', 'PRICING REDESIGN', 'PRICING PAGE CARDS', 'COMPONENT: PRICING CARD'],
    'home' => ['HOMEPAGE', 'HERO SECTION'],
    'contact' => ['CONTACT PAGE'],
    'dashboard' => ['DASHBOARD', 'AGENT DASHBOARD'],
    'listings' => ['PROPERTY LISTINGS', 'LISTING CARDS'],
    'auth' => ['LOGIN & REGISTER', 'AUTH FLOW', 'GATEWAY AUTH']
];

$outFiles = [];
foreach ($modules as $modName => $keywords) {
    // initialize empty array for extracted blocks
    $outFiles[$modName] = [];
}

// Regex to find: /* ======... \n SOMETHING \n ======... */  ... until the next /* ======
// But a safer way is line-by-line parsing state machine

$lines = explode("\n", $css);
$currentModule = null;
$buffer = [];
$remainingLines = [];

foreach ($lines as $i => $line) {
    // Check if line looks like a major header block start
    if (strpos($line, '/* ===') === 0) {
        // Look ahead 1-2 lines for the title
        $titleLine = isset($lines[$i+1]) ? $lines[$i+1] : '';
        $titleLine2 = isset($lines[$i+2]) ? $lines[$i+2] : '';
        $titleBlock = strtoupper($titleLine . ' ' . $titleLine2);
        
        $foundMod = null;
        foreach ($modules as $modName => $keywords) {
            foreach ($keywords as $kw) {
                if (strpos($titleBlock, $kw) !== false) {
                    $foundMod = $modName;
                    break 2; // Break out of both inner loops
                }
            }
        }
        
        if ($foundMod) {
            // We found a new section to extract!
            // If we were already in a module, flush the buffer to it
            if ($currentModule !== null) {
                $outFiles[$currentModule] = array_merge($outFiles[$currentModule], $buffer);
            } else {
                // We were in the base CSS, flush buffer to remainingLines
                $remainingLines = array_merge($remainingLines, $buffer);
            }
            $currentModule = $foundMod;
            $buffer = [];
        } else {
            // It's a header block, but not one of our modules.
            // If we are currently extracting a module, keep doing so? 
            // Or does this end the current module and return to base?
            // Actually, any new /* ===== header that is NOT matched should probably go back to base.
            if ($currentModule !== null) {
                $outFiles[$currentModule] = array_merge($outFiles[$currentModule], $buffer);
                $currentModule = null;
                $buffer = [];
            }
        }
    }
    
    $buffer[] = $line;
}

// Flush final buffer
if ($currentModule !== null) {
    $outFiles[$currentModule] = array_merge($outFiles[$currentModule], $buffer);
} else {
    $remainingLines = array_merge($remainingLines, $buffer);
}

// Write the files
$baseDir = __DIR__ . '/resources/css/modules/';
foreach ($outFiles as $modName => $linesArray) {
    if (count($linesArray) > 0) {
        $path = $baseDir . $modName . '.css';
        file_put_contents($path, implode("\n", $linesArray));
        echo "Extracted " . count($linesArray) . " lines to " . $modName . ".css\n";
    }
}

// Write the remaining app.css
file_put_contents($cssFile, implode("\n", $remainingLines));
echo "Left " . count($remainingLines) . " lines in app.css\n";

echo "Done!\n";
