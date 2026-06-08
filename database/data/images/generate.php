<?php

/**
 * Generates clean, category-correct product images for the catalog seeder using PHP GD.
 * Each product gets a studio-style card with a drawn illustration of the actual product
 * category (oil bottle, filter, brake disc, battery, tyre) plus brand + name.
 *
 * Rendered at 2x then downscaled for smooth (anti-aliased) edges.
 *
 *   php database/data/images/generate.php
 *
 * Output: database/data/images/products/<file>.jpg  (referenced by TenantCatalogSeeder)
 */
$FONT_BOLD = '/System/Library/Fonts/Supplemental/Arial Bold.ttf';
$FONT_REG = '/System/Library/Fonts/Supplemental/Arial.ttf';

if (! is_file($FONT_BOLD) || ! is_file($FONT_REG)) {
    fwrite(STDERR, "Arial TTF fonts not found; adjust \$FONT_* paths.\n");
    exit(1);
}

$OUT = __DIR__.'/products';
@mkdir($OUT, 0755, true);

$GLOBALS['S'] = 2;           // supersample factor
$W = 600 * $GLOBALS['S'];
$H = 600 * $GLOBALS['S'];

$PRODUCTS = [
    ['file' => 'mobil-1-5w30',      'brand' => 'Mobil 1',     'name' => '5W-30 Full Synthetic',    'shape' => 'oil_bottle',      'accent' => '#1565C0', 'spec' => '5W-30'],
    ['file' => 'castrol-gtx-10w40', 'brand' => 'Castrol',     'name' => 'GTX 10W-40',              'shape' => 'oil_bottle',      'accent' => '#2E7D32', 'spec' => '10W-40'],
    ['file' => 'shell-helix-hx7',   'brand' => 'Shell',       'name' => 'Helix HX7 5W-40',         'shape' => 'oil_bottle',      'accent' => '#F9A825', 'spec' => '5W-40'],
    ['file' => 'valvoline-daily',   'brand' => 'Valvoline',   'name' => 'Daily Protection',        'shape' => 'oil_bottle',      'accent' => '#C62828', 'spec' => 'SAE'],
    ['file' => 'kn-oil-filter',     'brand' => 'K&N',         'name' => 'Oil Filter HP-1004',      'shape' => 'filter_cylinder', 'accent' => '#B71C1C', 'spec' => 'OIL'],
    ['file' => 'bosch-air-filter',  'brand' => 'Bosch',       'name' => 'Premium Air Filter',      'shape' => 'filter_panel',    'accent' => '#1565C0', 'spec' => 'AIR'],
    ['file' => 'mann-cabin-filter', 'brand' => 'Mann',        'name' => 'Cabin Filter CU 26 009',  'shape' => 'filter_panel',    'accent' => '#00897B', 'spec' => 'CABIN'],
    ['file' => 'brembo-brake-pads', 'brand' => 'Brembo',      'name' => 'Front Brake Pads',        'shape' => 'brake_disc',      'accent' => '#D32F2F', 'spec' => 'BRAKE'],
    ['file' => 'dot4-brake-fluid',  'brand' => 'Bosch',       'name' => 'DOT 4 Brake Fluid 1L',    'shape' => 'fluid_bottle',    'accent' => '#FF8F00', 'spec' => 'DOT 4'],
    ['file' => 'exide-battery',     'brand' => 'Exide',       'name' => '12V 60Ah Premium',        'shape' => 'battery',         'accent' => '#1A237E', 'spec' => '12V'],
    ['file' => 'acdelco-battery',   'brand' => 'AC Delco',    'name' => '12V 70Ah',                'shape' => 'battery',         'accent' => '#EF6C00', 'spec' => '12V'],
    ['file' => 'michelin-primacy4', 'brand' => 'Michelin',    'name' => 'Primacy 4 195/65R15',     'shape' => 'tyre',            'accent' => '#263238', 'spec' => 'R15'],
    ['file' => 'bridgestone-dueler', 'brand' => 'Bridgestone', 'name' => 'Dueler H/T',             'shape' => 'tyre',            'accent' => '#263238', 'spec' => 'H/T'],
];

function s($v)
{
    return (int) round($v * $GLOBALS['S']);
}
function col($im, $hex, $alpha = 0)
{
    $hex = ltrim($hex, '#');

    return imagecolorallocatealpha($im, hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2)), $alpha);
}
function fillrect($im, $a, $b, $c, $d, $col)
{
    imagefilledrectangle($im, s($a), s($b), s($c), s($d), $col);
}
function fellipse($im, $cx, $cy, $w, $h, $col)
{
    imagefilledellipse($im, s($cx), s($cy), s($w), s($h), $col);
}
function fpoly($im, $pts, $col)
{
    imagefilledpolygon($im, array_map('s', $pts), $col);
}
function rrect($im, $x1, $y1, $x2, $y2, $r, $col)
{
    fillrect($im, $x1 + $r, $y1, $x2 - $r, $y2, $col);
    fillrect($im, $x1, $y1 + $r, $x2, $y2 - $r, $col);
    fellipse($im, $x1 + $r, $y1 + $r, $r * 2, $r * 2, $col);
    fellipse($im, $x2 - $r, $y1 + $r, $r * 2, $r * 2, $col);
    fellipse($im, $x1 + $r, $y2 - $r, $r * 2, $r * 2, $col);
    fellipse($im, $x2 - $r, $y2 - $r, $r * 2, $r * 2, $col);
}
function ttfc($im, $size, $cx, $y, $color, $font, $text)
{
    $bb = imagettfbbox($size * $GLOBALS['S'], 0, $font, $text);
    $x = s($cx) - ($bb[2] - $bb[0]) / 2;
    imagettftext($im, $size * $GLOBALS['S'], 0, (int) $x, s($y), $color, $font, $text);
}

function darken($hex, $f = 0.7)
{
    $hex = ltrim($hex, '#');
    $r = (int) (hexdec(substr($hex, 0, 2)) * $f);
    $g = (int) (hexdec(substr($hex, 2, 2)) * $f);
    $b = (int) (hexdec(substr($hex, 4, 2)) * $f);

    return sprintf('#%02x%02x%02x', $r, $g, $b);
}

function drawShadow($im, $cx, $cy)
{
    fellipse($im, $cx, $cy, 300, 60, col($im, '#000000', 100));
    fellipse($im, $cx, $cy, 230, 42, col($im, '#000000', 95));
}

function oilBottle($im, $cx, $accent, $spec, $font)
{
    $dark = darken($accent, 0.65);
    fellipse($im, $cx, 452, 250, 46, col($im, '#000000', 102));
    // cap + neck
    rrect($im, $cx - 36, 188, $cx + 36, 224, 8, col($im, '#37474F'));
    fillrect($im, $cx - 30, 222, $cx + 30, 252, col($im, $dark));
    // body
    rrect($im, $cx - 78, 248, $cx + 78, 438, 26, col($im, $accent));
    // gloss highlight
    rrect($im, $cx - 64, 262, $cx - 44, 420, 10, col($im, '#FFFFFF', 95));
    // label
    rrect($im, $cx - 62, 300, $cx + 62, 398, 12, col($im, '#FFFFFF'));
    fillrect($im, $cx - 62, 314, $cx + 62, 322, col($im, $accent));
    fillrect($im, $cx - 62, 376, $cx + 62, 384, col($im, $accent));
    ttfc($im, 21, $cx, 358, col($im, $dark), $font, $spec);
}

function fluidBottle($im, $cx, $accent, $spec, $font)
{
    $dark = darken($accent, 0.6);
    fellipse($im, $cx, 452, 220, 44, col($im, '#000000', 102));
    rrect($im, $cx - 30, 196, $cx + 30, 226, 6, col($im, '#455A64'));
    rrect($im, $cx - 60, 230, $cx + 60, 440, 22, col($im, $accent));
    rrect($im, $cx - 48, 244, $cx - 32, 420, 8, col($im, '#FFFFFF', 100));
    rrect($im, $cx - 48, 300, $cx + 48, 400, 10, col($im, '#FFFFFF'));
    ttfc($im, 20, $cx, 345, col($im, $dark), $font, 'DOT 4');
    ttfc($im, 12, $cx, 378, col($im, '#607D8B'), $font, 'BRAKE FLUID');
}

function filterCylinder($im, $cx, $accent, $font)
{
    $dark = darken($accent, 0.7);
    fellipse($im, $cx, 452, 200, 40, col($im, '#000000', 102));
    // base plate (metal)
    fellipse($im, $cx, 426, 150, 46, col($im, '#90A4AE'));
    fellipse($im, $cx, 426, 60, 22, col($im, '#546E7A'));
    // body
    fillrect($im, $cx - 62, 256, $cx + 62, 426, col($im, $accent));
    fellipse($im, $cx, 426, 124, 38, col($im, $accent));
    fellipse($im, $cx, 256, 124, 40, col($im, $dark));
    // grip ridges
    for ($x = $cx - 50; $x <= $cx + 50; $x += 13) {
        fillrect($im, $x, 280, $x + 5, 408, col($im, '#000000', 110));
    }
    // top metal band + brand swoosh
    fellipse($im, $cx, 262, 110, 30, col($im, '#ECEFF1'));
    fellipse($im, $cx, 262, 110, 30, col($im, '#ECEFF1'));
    rrect($im, $cx - 58, 320, $cx + 58, 366, 10, col($im, '#FFFFFF'));
    ttfc($im, 18, $cx, 352, col($im, $dark), $font, 'FILTER');
}

function filterPanel($im, $cx, $accent, $spec, $font)
{
    $dark = darken($accent, 0.7);
    fellipse($im, $cx, 446, 300, 40, col($im, '#000000', 104));
    // rubber frame
    rrect($im, $cx - 170, 250, $cx + 170, 410, 18, col($im, $accent));
    rrect($im, $cx - 170, 250, $cx + 170, 270, 18, col($im, $dark));
    // pleat bed
    rrect($im, $cx - 146, 276, $cx + 146, 396, 6, col($im, '#FAFAFA'));
    // accordion pleats
    $top = 280;
    $bot = 392;
    for ($x = $cx - 142; $x <= $cx + 142; $x += 16) {
        fpoly($im, [$x, $top, $x + 8, $bot, $x + 8, $top], col($im, '#CFD8DC'));
        fpoly($im, [$x + 8, $top, $x + 8, $bot, $x + 16, $bot], col($im, '#90A4AE'));
    }
    // support wires
    fillrect($im, $cx - 146, 326, $cx + 146, 330, col($im, $dark));
    ttfc($im, 16, $cx, 248, col($im, $dark), $font, $spec.' FILTER');
}

function brakeDisc($im, $cx, $accent, $font)
{
    $cy = 296;
    fellipse($im, $cx, 452, 240, 44, col($im, '#000000', 104));
    // rotor
    fellipse($im, $cx, $cy, 312, 312, col($im, '#78909C'));
    fellipse($im, $cx, $cy, 296, 296, col($im, '#B0BEC5'));
    fellipse($im, $cx, $cy, 250, 250, col($im, '#CFD8DC'));
    // drilled holes
    for ($a = 0; $a < 360; $a += 30) {
        $hx = $cx + cos(deg2rad($a)) * 108;
        $hy = $cy + sin(deg2rad($a)) * 108;
        fellipse($im, $hx, $hy, 18, 18, col($im, '#607D8B'));
    }
    // hat + hub
    fellipse($im, $cx, $cy, 150, 150, col($im, '#90A4AE'));
    fellipse($im, $cx, $cy, 60, 60, col($im, '#546E7A'));
    for ($a = 0; $a < 360; $a += 72) {
        fellipse($im, $cx + cos(deg2rad($a)) * 52, $cy + sin(deg2rad($a)) * 52, 13, 13, col($im, '#37474F'));
    }
    // caliper
    rrect($im, $cx + 116, $cy - 64, $cx + 182, $cy + 64, 16, col($im, $accent));
    rrect($im, $cx + 128, $cy - 40, $cx + 150, $cy + 40, 6, col($im, darken($accent, 0.7)));
    ttfc($im, 13, $cx + 149, $cy + 5, col($im, '#FFFFFF'), $font, '');
}

function battery($im, $cx, $accent, $font)
{
    $dark = darken($accent, 0.6);
    fellipse($im, $cx, 452, 270, 44, col($im, '#000000', 104));
    // body + lid
    rrect($im, $cx - 138, 268, $cx + 138, 430, 14, col($im, $accent));
    rrect($im, $cx - 138, 252, $cx + 138, 300, 14, col($im, $dark));
    fillrect($im, $cx - 138, 286, $cx + 138, 300, col($im, $dark));
    // terminals
    fellipse($im, $cx - 86, 268, 46, 46, col($im, '#C62828'));
    fellipse($im, $cx - 86, 268, 24, 24, col($im, '#8E0000'));
    fellipse($im, $cx + 86, 268, 42, 42, col($im, '#263238'));
    fellipse($im, $cx + 86, 268, 22, 22, col($im, '#000000'));
    // + / - marks
    fillrect($im, $cx - 96, 224, $cx - 76, 232, col($im, '#FFFFFF'));
    fillrect($im, $cx - 90, 218, $cx - 82, 238, col($im, '#FFFFFF'));
    fillrect($im, $cx + 76, 224, $cx + 96, 232, col($im, '#FFFFFF'));
    // label
    rrect($im, $cx - 104, 320, $cx + 104, 414, 8, col($im, '#FFFFFF'));
    ttfc($im, 30, $cx, 372, col($im, $dark), $font, '12V');
    ttfc($im, 14, $cx, 400, col($im, '#607D8B'), $font, 'MF BATTERY');
}

function tyre($im, $cx, $font, $spec)
{
    $cy = 292;
    fellipse($im, $cx, 452, 250, 40, col($im, '#000000', 104));
    // tread blocks
    for ($a = 0; $a < 360; $a += 9) {
        $x1 = $cx + cos(deg2rad($a)) * 168;
        $y1 = $cy + sin(deg2rad($a)) * 168;
        $x2 = $cx + cos(deg2rad($a + 4.5)) * 168;
        $y2 = $cy + sin(deg2rad($a + 4.5)) * 168;
        fpoly($im, [$cx, $cy, $x1, $y1, $x2, $y2], col($im, ($a / 9) % 2 ? '#0d0d0d' : '#2c2c2c'));
    }
    // rubber
    fellipse($im, $cx, $cy, 312, 312, col($im, '#161616'));
    fellipse($im, $cx, $cy, 280, 280, col($im, '#1f1f1f'));
    // sidewall lettering ring
    fellipse($im, $cx, $cy, 248, 248, col($im, '#0d0d0d'));
    // alloy rim
    fellipse($im, $cx, $cy, 196, 196, col($im, '#90A4AE'));
    fellipse($im, $cx, $cy, 182, 182, col($im, '#CFD8DC'));
    fellipse($im, $cx, $cy, 168, 168, col($im, '#B0BEC5'));
    // spokes
    for ($a = 0; $a < 360; $a += 72) {
        $ax = $cx + cos(deg2rad($a - 11)) * 80;
        $ay = $cy + sin(deg2rad($a - 11)) * 80;
        $bx = $cx + cos(deg2rad($a + 11)) * 80;
        $by = $cy + sin(deg2rad($a + 11)) * 80;
        $tx = $cx + cos(deg2rad($a)) * 158;
        $ty = $cy + sin(deg2rad($a)) * 158;
        fpoly($im, [$ax, $ay, $bx, $by, $tx, $ty], col($im, '#78909C'));
    }
    // hub
    fellipse($im, $cx, $cy, 96, 96, col($im, '#90A4AE'));
    fellipse($im, $cx, $cy, 64, 64, col($im, '#546E7A'));
    for ($a = 0; $a < 360; $a += 72) {
        fellipse($im, $cx + cos(deg2rad($a)) * 38, $cy + sin(deg2rad($a)) * 38, 12, 12, col($im, '#37474F'));
    }
    fellipse($im, $cx, $cy, 26, 26, col($im, '#263238'));
    ttfc($im, 13, $cx, 248, col($im, '#ECEFF1'), $font, $spec);
}

foreach ($PRODUCTS as $p) {
    $im = imagecreatetruecolor($W, $H);
    imagealphablending($im, true);

    // studio gradient background
    for ($y = 0; $y < $H; $y++) {
        $t = $y / $H;
        $r = (int) (244 - $t * 40);
        $g = (int) (246 - $t * 38);
        $b = (int) (250 - $t * 36);
        imagefilledrectangle($im, 0, $y, $W, $y, imagecolorallocate($im, $r, $g, $b));
    }
    // soft glow behind product
    fellipse($im, 300, 270, 460, 460, col($im, '#FFFFFF', 90));

    $cx = 300;
    switch ($p['shape']) {
        case 'oil_bottle': oilBottle($im, $cx, $p['accent'], $p['spec'], $FONT_BOLD);
            break;
        case 'fluid_bottle': fluidBottle($im, $cx, $p['accent'], $p['spec'], $FONT_BOLD);
            break;
        case 'filter_cylinder': filterCylinder($im, $cx, $p['accent'], $FONT_BOLD);
            break;
        case 'filter_panel': filterPanel($im, $cx, $p['accent'], $p['spec'], $FONT_BOLD);
            break;
        case 'brake_disc': brakeDisc($im, $cx, $p['accent'], $FONT_BOLD);
            break;
        case 'battery': battery($im, $cx, $p['accent'], $FONT_BOLD);
            break;
        case 'tyre': tyre($im, $cx, $FONT_BOLD, $p['spec']);
            break;
    }

    // text band
    ttfc($im, 30, $cx, 512, col($im, '#212121'), $FONT_BOLD, $p['brand']);
    ttfc($im, 17, $cx, 546, col($im, '#546E7A'), $FONT_REG, $p['name']);
    // accent underline
    fillrect($im, $cx - 32, 524, $cx + 32, 528, col($im, $p['accent']));

    $out = imagecreatetruecolor(600, 600);
    imagecopyresampled($out, $im, 0, 0, 0, 0, 600, 600, $W, $H);
    imagejpeg($out, $OUT.'/'.$p['file'].'.jpg', 92);
    imagedestroy($im);
    imagedestroy($out);
    echo "generated {$p['file']}.jpg\n";
}

echo 'Done: '.count($PRODUCTS)." images in {$OUT}\n";
