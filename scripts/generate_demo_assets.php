<?php

declare(strict_types=1);

if (! extension_loaded('gd')) {
    fwrite(STDERR, "The GD extension is required.\n");
    exit(1);
}

$root = dirname(__DIR__).'/public/images';
@mkdir($root.'/properties', 0775, true);
@mkdir($root.'/icons', 0775, true);

$types = [
    'apartment' => 'APARTMENT',
    'self-contain' => 'SELF CONTAIN',
    'duplex' => 'DUPLEX',
    'shared-flat' => 'SHARED FLAT',
    'shop' => 'SHOP',
    'office' => 'OFFICE',
];

function colour(GdImage $image, string $hex): int
{
    return imagecolorallocate($image, hexdec(substr($hex, 1, 2)), hexdec(substr($hex, 3, 2)), hexdec(substr($hex, 5, 2)));
}

function drawProperty(string $path, string $label, int $variant, int $width, int $height): void
{
    $image = imagecreatetruecolor($width, $height);
    imageantialias($image, true);
    $navy = colour($image, '#0A2856');
    $blue = colour($image, '#145FCC');
    $pale = colour($image, '#DDE9FA');
    $sky = colour($image, '#EAF2FF');
    $white = colour($image, '#FFFFFF');
    $line = colour($image, '#B8C8E1');
    $ground = colour($image, '#D7E2F4');

    imagefilledrectangle($image, 0, 0, $width, $height, $sky);
    imagefilledellipse($image, (int) ($width * .82), (int) ($height * .17), (int) ($height * .13), (int) ($height * .13), $white);
    imagefilledrectangle($image, 0, (int) ($height * .72), $width, $height, $ground);

    $left = (int) ($width * ($variant === 1 ? .15 : .11));
    $top = (int) ($height * ($variant === 1 ? .25 : .30));
    $right = (int) ($width * ($variant === 1 ? .84 : .88));
    $bottom = (int) ($height * .78);

    imagefilledrectangle($image, $left, $top, $right, $bottom, $white);
    imagefilledrectangle($image, $left, $top, $right, $top + (int) ($height * .035), $navy);
    imagefilledpolygon($image, [
        $left - (int) ($width * .03), $top,
        (int) (($left + $right) / 2), $top - (int) ($height * .15),
        $right + (int) ($width * .03), $top,
    ], $navy);

    $windowWidth = (int) ($width * .11);
    $windowHeight = (int) ($height * .12);
    foreach ([.28, .46, .64] as $position) {
        $x = (int) ($width * $position);
        imagefilledrectangle($image, $x, $top + (int) ($height * .12), $x + $windowWidth, $top + (int) ($height * .12) + $windowHeight, $pale);
        imagerectangle($image, $x, $top + (int) ($height * .12), $x + $windowWidth, $top + (int) ($height * .12) + $windowHeight, $line);
        imageline($image, $x + intdiv($windowWidth, 2), $top + (int) ($height * .12), $x + intdiv($windowWidth, 2), $top + (int) ($height * .12) + $windowHeight, $line);
    }

    $doorLeft = (int) ($width * .46);
    imagefilledrectangle($image, $doorLeft, $bottom - (int) ($height * .23), $doorLeft + (int) ($width * .11), $bottom, $blue);
    imagefilledellipse($image, $doorLeft + (int) ($width * .09), $bottom - (int) ($height * .11), 6, 6, $white);
    imagefilledrectangle($image, (int) ($width * .07), (int) ($height * .07), (int) ($width * .29), (int) ($height * .13), $navy);
    imagestring($image, 5, (int) ($width * .085), (int) ($height * .085), 'LISTORA.NG', $white);
    imagefilledrectangle($image, (int) ($width * .67), (int) ($height * .83), (int) ($width * .94), (int) ($height * .91), $white);
    imagestring($image, 5, (int) ($width * .69), (int) ($height * .855), $label, $navy);

    imagewebp($image, $path.'.webp', 78);
    imageavif($image, $path.'.avif', 55);
    imagejpeg($image, $path.'.jpg', 80);
    imagedestroy($image);
}

foreach ($types as $slug => $label) {
    foreach ([1, 2] as $variant) {
        drawProperty($root.'/properties/'.$slug.'-'.$variant, $label, $variant, 1200, 800);
        drawProperty($root.'/properties/'.$slug.'-'.$variant.'-thumb', $label, $variant, 720, 480);
    }
}

function drawIcon(string $path, int $size, bool $maskable = false): void
{
    $image = imagecreatetruecolor($size, $size);
    imagesavealpha($image, true);
    $navy = colour($image, '#0A2856');
    $blue = colour($image, '#145FCC');
    $white = colour($image, '#FFFFFF');
    imagefilledrectangle($image, 0, 0, $size, $size, $maskable ? $navy : $white);
    $margin = (int) ($size * ($maskable ? .20 : .10));
    imagefilledrectangle($image, $margin, $margin, $size - $margin, $size - $margin, $navy);
    $stroke = max(8, (int) ($size * .075));
    imagesetthickness($image, $stroke);
    $houseLeft = (int) ($size * .33);
    $houseRight = (int) ($size * .67);
    $roofY = (int) ($size * .38);
    imageline($image, $houseLeft, $roofY, (int) ($size * .5), (int) ($size * .25), $white);
    imageline($image, (int) ($size * .5), (int) ($size * .25), $houseRight, $roofY, $white);
    imageline($image, $houseLeft, $roofY, $houseLeft, (int) ($size * .68), $white);
    imageline($image, $houseRight, $roofY, $houseRight, (int) ($size * .68), $white);
    imageline($image, $houseLeft, (int) ($size * .68), $houseRight, (int) ($size * .68), $white);
    imagefilledrectangle($image, (int) ($size * .47), (int) ($size * .50), (int) ($size * .56), (int) ($size * .68), $blue);
    imagepng($image, $path, 8);
    imagedestroy($image);
}

drawIcon($root.'/icons/listora-192.png', 192);
drawIcon($root.'/icons/listora-512.png', 512);
drawIcon($root.'/icons/listora-maskable-512.png', 512, true);

echo "Generated Listora property and PWA assets.\n";
