<?php
namespace Safe;
if (!defined('ABSPATH')) exit;
use Safe\Exceptions\ImageException;
function getimagesize(string $filename, array &$imageinfo = null): array
{
 error_clear_last();
 $result = \getimagesize($filename, $imageinfo);
 if ($result === false) {
 throw ImageException::createFromPhpError();
 }
 return $result;
}
function image2wbmp($image, ?string $filename = null, int $foreground = null): void
{
 error_clear_last();
 if ($foreground !== null) {
 $result = \image2wbmp($image, $filename, $foreground);
 } elseif ($filename !== null) {
 $result = \image2wbmp($image, $filename);
 } else {
 $result = \image2wbmp($image);
 }
 if ($result === false) {
 throw ImageException::createFromPhpError();
 }
}
function imageaffine($image, array $affine, array $clip = null)
{
 error_clear_last();
 if ($clip !== null) {
 $result = \imageaffine($image, $affine, $clip);
 } else {
 $result = \imageaffine($image, $affine);
 }
 if ($result === false) {
 throw ImageException::createFromPhpError();
 }
 return $result;
}
function imageaffinematrixconcat(array $m1, array $m2): array
{
 error_clear_last();
 $result = \imageaffinematrixconcat($m1, $m2);
 if ($result === false) {
 throw ImageException::createFromPhpError();
 }
 return $result;
}
function imageaffinematrixget(int $type, $options = null): array
{
 error_clear_last();
 if ($options !== null) {
 $result = \imageaffinematrixget($type, $options);
 } else {
 $result = \imageaffinematrixget($type);
 }
 if ($result === false) {
 throw ImageException::createFromPhpError();
 }
 return $result;
}
function imagealphablending($image, bool $blendmode): void
{
 error_clear_last();
 $result = \imagealphablending($image, $blendmode);
 if ($result === false) {
 throw ImageException::createFromPhpError();
 }
}
function imageantialias($image, bool $enabled): void
{
 error_clear_last();
 $result = \imageantialias($image, $enabled);
 if ($result === false) {
 throw ImageException::createFromPhpError();
 }
}
function imagearc($image, int $cx, int $cy, int $width, int $height, int $start, int $end, int $color): void
{
 error_clear_last();
 $result = \imagearc($image, $cx, $cy, $width, $height, $start, $end, $color);
 if ($result === false) {
 throw ImageException::createFromPhpError();
 }
}
function imagebmp($image, $to = null, bool $compressed = true): void
{
 error_clear_last();
 $result = \imagebmp($image, $to, $compressed);
 if ($result === false) {
 throw ImageException::createFromPhpError();
 }
}
function imagechar($image, int $font, int $x, int $y, string $c, int $color): void
{
 error_clear_last();
 $result = \imagechar($image, $font, $x, $y, $c, $color);
 if ($result === false) {
 throw ImageException::createFromPhpError();
 }
}
function imagecharup($image, int $font, int $x, int $y, string $c, int $color): void
{
 error_clear_last();
 $result = \imagecharup($image, $font, $x, $y, $c, $color);
 if ($result === false) {
 throw ImageException::createFromPhpError();
 }
}
function imagecolorat($image, int $x, int $y): int
{
 error_clear_last();
 $result = \imagecolorat($image, $x, $y);
 if ($result === false) {
 throw ImageException::createFromPhpError();
 }
 return $result;
}
function imagecolordeallocate($image, int $color): void
{
 error_clear_last();
 $result = \imagecolordeallocate($image, $color);
 if ($result === false) {
 throw ImageException::createFromPhpError();
 }
}
function imagecolormatch($image1, $image2): void
{
 error_clear_last();
 $result = \imagecolormatch($image1, $image2);
 if ($result === false) {
 throw ImageException::createFromPhpError();
 }
}
function imageconvolution($image, array $matrix, float $div, float $offset): void
{
 error_clear_last();
 $result = \imageconvolution($image, $matrix, $div, $offset);
 if ($result === false) {
 throw ImageException::createFromPhpError();
 }
}
function imagecopy($dst_im, $src_im, int $dst_x, int $dst_y, int $src_x, int $src_y, int $src_w, int $src_h): void
{
 error_clear_last();
 $result = \imagecopy($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h);
 if ($result === false) {
 throw ImageException::createFromPhpError();
 }
}
function imagecopymerge($dst_im, $src_im, int $dst_x, int $dst_y, int $src_x, int $src_y, int $src_w, int $src_h, int $pct): void
{
 error_clear_last();
 $result = \imagecopymerge($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $pct);
 if ($result === false) {
 throw ImageException::createFromPhpError();
 }
}
function imagecopymergegray($dst_im, $src_im, int $dst_x, int $dst_y, int $src_x, int $src_y, int $src_w, int $src_h, int $pct): void
{
 error_clear_last();
 $result = \imagecopymergegray($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $pct);
 if ($result === false) {
 throw ImageException::createFromPhpError();
 }
}
function imagecopyresampled($dst_image, $src_image, int $dst_x, int $dst_y, int $src_x, int $src_y, int $dst_w, int $dst_h, int $src_w, int $src_h): void
{
 error_clear_last();
 $result = \imagecopyresampled($dst_image, $src_image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);
 if ($result === false) {
 throw ImageException::createFromPhpError();
 }
}
function imagecopyresized($dst_image, $src_image, int $dst_x, int $dst_y, int $src_x, int $src_y, int $dst_w, int $dst_h, int $src_w, int $src_h): void
{
 error_clear_last();
 $result = \imagecopyresized($dst_image, $src_image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);
 if ($result === false) {
 throw ImageException::createFromPhpError();
 }
}
function imagecreate(int $width, int $height)
{
 error_clear_last();
 $result = \imagecreate($width, $height);
 if ($result === false) {
 throw ImageException::createFromPhpError();
 }
 return $result;
}
function imagecreatefrombmp(string $filename)
{
 error_clear_last();
 $result = \imagecreatefrombmp($filename);
 if ($result === false) {
 throw ImageException::createFromPhpError();
 }
 return $result;
}
function imagecreatefromgd(string $filename)
{
 error_clear_last();
 $result = \imagecreatefromgd($filename);
 if ($result === false) {
 throw ImageException::createFromPhpError();
 }
 return $result;
}
function imagecreatefromgd2(string $filename)
{
 error_clear_last();
 $result = \imagecreatefromgd2($filename);
 if ($result === false) {
 throw ImageException::createFromPhpError();
 }
 return $result;
}
function imagecreatefromgd2part(string $filename, int $srcX, int $srcY, int $width, int $height)
{
 error_clear_last();
 $result = \imagecreatefromgd2part($filename, $srcX, $srcY, $width, $height);
 if ($result === false) {
 throw ImageException::createFromPhpError();
 }
 return $result;
}
function imagecreatefromgif(string $filename)
{
 error_clear_last();
 $result = \imagecreatefromgif($filename);
 if ($result === false) {
 throw ImageException::createFromPhpError();
 }
 return $result;
}
function imagecreatefromjpeg(string $filename)
{
 error_clear_last();
 $result = \imagecreatefromjpeg($filename);
 if ($result === false) {
 throw ImageException::createFromPhpError();
 }
 return $result;
}
function imagecreatefrompng(string $filename)
{
 error_clear_last();
 $result = \imagecreatefrompng($filename);
 if ($result === false) {
 throw ImageException::createFromPhpError();
 }
 return $result;
}
function imagecreatefromwbmp(string $filename)
{
 error_clear_last();
 $result = \imagecreatefromwbmp($filename);
 if ($result === false) {
 throw ImageException::createFromPhpError();
 }
 return $result;
}
function imagecreatefromwebp(string $filename)
{
 error_clear_last();
 $result = \imagecreatefromwebp($filename);
 if ($result === false) {
 throw ImageException::createFromPhpError();
 }
 return $result;
}
function imagecreatefromxbm(string $filename)
{
 error_clear_last();
 $result = \imagecreatefromxbm($filename);
 if ($result === false) {
 throw ImageException::createFromPhpError();
 }
 return $result;
}
function imagecreatefromxpm(string $filename)
{
 error_clear_last();
 $result = \imagecreatefromxpm($filename);
 if ($result === false) {
 throw ImageException::createFromPhpError();
 }
 return $result;
}
function imagecreatetruecolor(int $width, int $height)
{
 error_clear_last();
 $result = \imagecreatetruecolor($width, $height);
 if ($result === false) {
 throw ImageException::createFromPhpError();
 }
 return $result;
}
function imagecrop($image, array $rect)
{
 error_clear_last();
 $result = \imagecrop($image, $rect);
 if ($result === false) {
 throw ImageException::createFromPhpError();
 }
 return $result;
}
function imagecropauto($image, int $mode = IMG_CROP_DEFAULT, float $threshold = .5, int $color = -1)
{
 error_clear_last();
 $result = \imagecropauto($image, $mode, $threshold, $color);
 if ($result === false) {
 throw ImageException::createFromPhpError();
 }
 return $result;
}
function imagedashedline($image, int $x1, int $y1, int $x2, int $y2, int $color): void
{
 error_clear_last();
 $result = \imagedashedline($image, $x1, $y1, $x2, $y2, $color);
 if ($result === false) {
 throw ImageException::createFromPhpError();
 }
}
function imagedestroy($image): void
{
 error_clear_last();
 $result = \imagedestroy($image);
 if ($result === false) {
 throw ImageException::createFromPhpError();
 }
}
function imageellipse($image, int $cx, int $cy, int $width, int $height, int $color): void
{
 error_clear_last();
 $result = \imageellipse($image, $cx, $cy, $width, $height, $color);
 if ($result === false) {
 throw ImageException::createFromPhpError();
 }
}
function imagefill($image, int $x, int $y, int $color): void
{
 error_clear_last();
 $result = \imagefill($image, $x, $y, $color);
 if ($result === false) {
 throw ImageException::createFromPhpError();
 }
}
function imagefilledarc($image, int $cx, int $cy, int $width, int $height, int $start, int $end, int $color, int $style): void
{
 error_clear_last();
 $result = \imagefilledarc($image, $cx, $cy, $width, $height, $start, $end, $color, $style);
 if ($result === false) {
 throw ImageException::createFromPhpError();
 }
}
function imagefilledellipse($image, int $cx, int $cy, int $width, int $height, int $color): void
{
 error_clear_last();
 $result = \imagefilledellipse($image, $cx, $cy, $width, $height, $color);
 if ($result === false) {
 throw ImageException::createFromPhpError();
 }
}
function imagefilledpolygon($image, array $points, int $num_points, int $color): void
{
 error_clear_last();
 $result = \imagefilledpolygon($image, $points, $num_points, $color);
 if ($result === false) {
 throw ImageException::createFromPhpError();
 }
}
function imagefilledrectangle($image, int $x1, int $y1, int $x2, int $y2, int $color): void
{
 error_clear_last();
 $result = \imagefilledrectangle($image, $x1, $y1, $x2, $y2, $color);
 if ($result === false) {
 throw ImageException::createFromPhpError();
 }
}
function imagefilltoborder($image, int $x, int $y, int $border, int $color): void
{
 error_clear_last();
 $result = \imagefilltoborder($image, $x, $y, $border, $color);
 if ($result === false) {
 throw ImageException::createFromPhpError();
 }
}
function imagefilter($image, int $filtertype, int $arg1 = null, int $arg2 = null, int $arg3 = null, int $arg4 = null): void
{
 error_clear_last();
 if ($arg4 !== null) {
 $result = \imagefilter($image, $filtertype, $arg1, $arg2, $arg3, $arg4);
 } elseif ($arg3 !== null) {
 $result = \imagefilter($image, $filtertype, $arg1, $arg2, $arg3);
 } elseif ($arg2 !== null) {
 $result = \imagefilter($image, $filtertype, $arg1, $arg2);
 } elseif ($arg1 !== null) {
 $result = \imagefilter($image, $filtertype, $arg1);
 } else {
 $result = \imagefilter($image, $filtertype);
 }
 if ($result === false) {
 throw ImageException::createFromPhpError();
 }
}
function imageflip($image, int $mode): void
{
 error_clear_last();
 $result = \imageflip($image, $mode);
 if ($result === false) {
 throw ImageException::createFromPhpError();
 }
}
function imagegammacorrect($image, float $inputgamma, float $outputgamma): void
{
 error_clear_last();
 $result = \imagegammacorrect($image, $inputgamma, $outputgamma);
 if ($result === false) {
 throw ImageException::createFromPhpError();
 }
}
function imagegd($image, $to = null): void
{
 error_clear_last();
 $result = \imagegd($image, $to);
 if ($result === false) {
 throw ImageException::createFromPhpError();
 }
}
function imagegd2($image, $to = null, int $chunk_size = 128, int $type = IMG_GD2_RAW): void
{
 error_clear_last();
 $result = \imagegd2($image, $to, $chunk_size, $type);
 if ($result === false) {
 throw ImageException::createFromPhpError();
 }
}
function imagegif($image, $to = null): void
{
 error_clear_last();
 $result = \imagegif($image, $to);
 if ($result === false) {
 throw ImageException::createFromPhpError();
 }
}
function imagegrabscreen()
{
 error_clear_last();
 $result = \imagegrabscreen();
 if ($result === false) {
 throw ImageException::createFromPhpError();
 }
 return $result;
}
function imagegrabwindow(int $window_handle, int $client_area = 0)
{
 error_clear_last();
 $result = \imagegrabwindow($window_handle, $client_area);
 if ($result === false) {
 throw ImageException::createFromPhpError();
 }
 return $result;
}
function imagejpeg($image, $to = null, int $quality = -1): void
{
 error_clear_last();
 $result = \imagejpeg($image, $to, $quality);
 if ($result === false) {
 throw ImageException::createFromPhpError();
 }
}
function imagelayereffect($image, int $effect): void
{
 error_clear_last();
 $result = \imagelayereffect($image, $effect);
 if ($result === false) {
 throw ImageException::createFromPhpError();
 }
}
function imageline($image, int $x1, int $y1, int $x2, int $y2, int $color): void
{
 error_clear_last();
 $result = \imageline($image, $x1, $y1, $x2, $y2, $color);
 if ($result === false) {
 throw ImageException::createFromPhpError();
 }
}
function imageloadfont(string $file): int
{
 error_clear_last();
 $result = \imageloadfont($file);
 if ($result === false) {
 throw ImageException::createFromPhpError();
 }
 return $result;
}
function imageopenpolygon($image, array $points, int $num_points, int $color): void
{
 error_clear_last();
 $result = \imageopenpolygon($image, $points, $num_points, $color);
 if ($result === false) {
 throw ImageException::createFromPhpError();
 }
}
function imagepng($image, $to = null, int $quality = -1, int $filters = -1): void
{
 error_clear_last();
 $result = \imagepng($image, $to, $quality, $filters);
 if ($result === false) {
 throw ImageException::createFromPhpError();
 }
}
function imagepolygon($image, array $points, int $num_points, int $color): void
{
 error_clear_last();
 $result = \imagepolygon($image, $points, $num_points, $color);
 if ($result === false) {
 throw ImageException::createFromPhpError();
 }
}
function imagerectangle($image, int $x1, int $y1, int $x2, int $y2, int $color): void
{
 error_clear_last();
 $result = \imagerectangle($image, $x1, $y1, $x2, $y2, $color);
 if ($result === false) {
 throw ImageException::createFromPhpError();
 }
}
function imagerotate($image, float $angle, int $bgd_color, int $dummy = 0)
{
 error_clear_last();
 $result = \imagerotate($image, $angle, $bgd_color, $dummy);
 if ($result === false) {
 throw ImageException::createFromPhpError();
 }
 return $result;
}
function imagesavealpha($image, bool $saveflag): void
{
 error_clear_last();
 $result = \imagesavealpha($image, $saveflag);
 if ($result === false) {
 throw ImageException::createFromPhpError();
 }
}
function imagescale($image, int $new_width, int $new_height = -1, int $mode = IMG_BILINEAR_FIXED)
{
 error_clear_last();
 $result = \imagescale($image, $new_width, $new_height, $mode);
 if ($result === false) {
 throw ImageException::createFromPhpError();
 }
 return $result;
}
function imagesetbrush($image, $brush): void
{
 error_clear_last();
 $result = \imagesetbrush($image, $brush);
 if ($result === false) {
 throw ImageException::createFromPhpError();
 }
}
function imagesetclip($im, int $x1, int $y1, int $x2, int $y2): void
{
 error_clear_last();
 $result = \imagesetclip($im, $x1, $y1, $x2, $y2);
 if ($result === false) {
 throw ImageException::createFromPhpError();
 }
}
function imagesetinterpolation($image, int $method = IMG_BILINEAR_FIXED): void
{
 error_clear_last();
 $result = \imagesetinterpolation($image, $method);
 if ($result === false) {
 throw ImageException::createFromPhpError();
 }
}
function imagesetpixel($image, int $x, int $y, int $color): void
{
 error_clear_last();
 $result = \imagesetpixel($image, $x, $y, $color);
 if ($result === false) {
 throw ImageException::createFromPhpError();
 }
}
function imagesetstyle($image, array $style): void
{
 error_clear_last();
 $result = \imagesetstyle($image, $style);
 if ($result === false) {
 throw ImageException::createFromPhpError();
 }
}
function imagesetthickness($image, int $thickness): void
{
 error_clear_last();
 $result = \imagesetthickness($image, $thickness);
 if ($result === false) {
 throw ImageException::createFromPhpError();
 }
}
function imagesettile($image, $tile): void
{
 error_clear_last();
 $result = \imagesettile($image, $tile);
 if ($result === false) {
 throw ImageException::createFromPhpError();
 }
}
function imagestring($image, int $font, int $x, int $y, string $string, int $color): void
{
 error_clear_last();
 $result = \imagestring($image, $font, $x, $y, $string, $color);
 if ($result === false) {
 throw ImageException::createFromPhpError();
 }
}
function imagestringup($image, int $font, int $x, int $y, string $string, int $color): void
{
 error_clear_last();
 $result = \imagestringup($image, $font, $x, $y, $string, $color);
 if ($result === false) {
 throw ImageException::createFromPhpError();
 }
}
function imagesx($image): int
{
 error_clear_last();
 $result = \imagesx($image);
 if ($result === false) {
 throw ImageException::createFromPhpError();
 }
 return $result;
}
function imagesy($image): int
{
 error_clear_last();
 $result = \imagesy($image);
 if ($result === false) {
 throw ImageException::createFromPhpError();
 }
 return $result;
}
function imagetruecolortopalette($image, bool $dither, int $ncolors): void
{
 error_clear_last();
 $result = \imagetruecolortopalette($image, $dither, $ncolors);
 if ($result === false) {
 throw ImageException::createFromPhpError();
 }
}
function imagettfbbox(float $size, float $angle, string $fontfile, string $text): array
{
 error_clear_last();
 $result = \imagettfbbox($size, $angle, $fontfile, $text);
 if ($result === false) {
 throw ImageException::createFromPhpError();
 }
 return $result;
}
function imagettftext($image, float $size, float $angle, int $x, int $y, int $color, string $fontfile, string $text): array
{
 error_clear_last();
 $result = \imagettftext($image, $size, $angle, $x, $y, $color, $fontfile, $text);
 if ($result === false) {
 throw ImageException::createFromPhpError();
 }
 return $result;
}
function imagewbmp($image, $to = null, int $foreground = null): void
{
 error_clear_last();
 if ($foreground !== null) {
 $result = \imagewbmp($image, $to, $foreground);
 } else {
 $result = \imagewbmp($image, $to);
 }
 if ($result === false) {
 throw ImageException::createFromPhpError();
 }
}
function imagewebp($image, $to = null, int $quality = 80): void
{
 error_clear_last();
 $result = \imagewebp($image, $to, $quality);
 if ($result === false) {
 throw ImageException::createFromPhpError();
 }
}
function imagexbm($image, ?string $filename, int $foreground = null): void
{
 error_clear_last();
 if ($foreground !== null) {
 $result = \imagexbm($image, $filename, $foreground);
 } else {
 $result = \imagexbm($image, $filename);
 }
 if ($result === false) {
 throw ImageException::createFromPhpError();
 }
}
function iptcembed(string $iptcdata, string $jpeg_file_name, int $spool = 0)
{
 error_clear_last();
 $result = \iptcembed($iptcdata, $jpeg_file_name, $spool);
 if ($result === false) {
 throw ImageException::createFromPhpError();
 }
 return $result;
}
function iptcparse(string $iptcblock): array
{
 error_clear_last();
 $result = \iptcparse($iptcblock);
 if ($result === false) {
 throw ImageException::createFromPhpError();
 }
 return $result;
}
function jpeg2wbmp(string $jpegname, string $wbmpname, int $dest_height, int $dest_width, int $threshold): void
{
 error_clear_last();
 $result = \jpeg2wbmp($jpegname, $wbmpname, $dest_height, $dest_width, $threshold);
 if ($result === false) {
 throw ImageException::createFromPhpError();
 }
}
function png2wbmp(string $pngname, string $wbmpname, int $dest_height, int $dest_width, int $threshold): void
{
 error_clear_last();
 $result = \png2wbmp($pngname, $wbmpname, $dest_height, $dest_width, $threshold);
 if ($result === false) {
 throw ImageException::createFromPhpError();
 }
}
