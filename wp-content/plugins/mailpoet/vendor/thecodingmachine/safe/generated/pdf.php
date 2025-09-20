<?php
namespace Safe;
if (!defined('ABSPATH')) exit;
use Safe\Exceptions\PdfException;
function PDF_activate_item($pdfdoc, int $id): void
{
 error_clear_last();
 $result = \PDF_activate_item($pdfdoc, $id);
 if ($result === false) {
 throw PdfException::createFromPhpError();
 }
}
function PDF_add_locallink($pdfdoc, float $lowerleftx, float $lowerlefty, float $upperrightx, float $upperrighty, int $page, string $dest): void
{
 error_clear_last();
 $result = \PDF_add_locallink($pdfdoc, $lowerleftx, $lowerlefty, $upperrightx, $upperrighty, $page, $dest);
 if ($result === false) {
 throw PdfException::createFromPhpError();
 }
}
function PDF_add_nameddest($pdfdoc, string $name, string $optlist): void
{
 error_clear_last();
 $result = \PDF_add_nameddest($pdfdoc, $name, $optlist);
 if ($result === false) {
 throw PdfException::createFromPhpError();
 }
}
function PDF_add_note($pdfdoc, float $llx, float $lly, float $urx, float $ury, string $contents, string $title, string $icon, int $open): void
{
 error_clear_last();
 $result = \PDF_add_note($pdfdoc, $llx, $lly, $urx, $ury, $contents, $title, $icon, $open);
 if ($result === false) {
 throw PdfException::createFromPhpError();
 }
}
function PDF_add_pdflink($pdfdoc, float $bottom_left_x, float $bottom_left_y, float $up_right_x, float $up_right_y, string $filename, int $page, string $dest): void
{
 error_clear_last();
 $result = \PDF_add_pdflink($pdfdoc, $bottom_left_x, $bottom_left_y, $up_right_x, $up_right_y, $filename, $page, $dest);
 if ($result === false) {
 throw PdfException::createFromPhpError();
 }
}
function PDF_add_thumbnail($pdfdoc, int $image): void
{
 error_clear_last();
 $result = \PDF_add_thumbnail($pdfdoc, $image);
 if ($result === false) {
 throw PdfException::createFromPhpError();
 }
}
function PDF_add_weblink($pdfdoc, float $lowerleftx, float $lowerlefty, float $upperrightx, float $upperrighty, string $url): void
{
 error_clear_last();
 $result = \PDF_add_weblink($pdfdoc, $lowerleftx, $lowerlefty, $upperrightx, $upperrighty, $url);
 if ($result === false) {
 throw PdfException::createFromPhpError();
 }
}
function PDF_attach_file($pdfdoc, float $llx, float $lly, float $urx, float $ury, string $filename, string $description, string $author, string $mimetype, string $icon): void
{
 error_clear_last();
 $result = \PDF_attach_file($pdfdoc, $llx, $lly, $urx, $ury, $filename, $description, $author, $mimetype, $icon);
 if ($result === false) {
 throw PdfException::createFromPhpError();
 }
}
function PDF_begin_layer($pdfdoc, int $layer): void
{
 error_clear_last();
 $result = \PDF_begin_layer($pdfdoc, $layer);
 if ($result === false) {
 throw PdfException::createFromPhpError();
 }
}
function PDF_begin_page_ext($pdfdoc, float $width, float $height, string $optlist): void
{
 error_clear_last();
 $result = \PDF_begin_page_ext($pdfdoc, $width, $height, $optlist);
 if ($result === false) {
 throw PdfException::createFromPhpError();
 }
}
function PDF_begin_page($pdfdoc, float $width, float $height): void
{
 error_clear_last();
 $result = \PDF_begin_page($pdfdoc, $width, $height);
 if ($result === false) {
 throw PdfException::createFromPhpError();
 }
}
function PDF_circle($pdfdoc, float $x, float $y, float $r): void
{
 error_clear_last();
 $result = \PDF_circle($pdfdoc, $x, $y, $r);
 if ($result === false) {
 throw PdfException::createFromPhpError();
 }
}
function PDF_clip($p): void
{
 error_clear_last();
 $result = \PDF_clip($p);
 if ($result === false) {
 throw PdfException::createFromPhpError();
 }
}
function PDF_close_pdi_page($p, int $page): void
{
 error_clear_last();
 $result = \PDF_close_pdi_page($p, $page);
 if ($result === false) {
 throw PdfException::createFromPhpError();
 }
}
function PDF_close_pdi($p, int $doc): void
{
 error_clear_last();
 $result = \PDF_close_pdi($p, $doc);
 if ($result === false) {
 throw PdfException::createFromPhpError();
 }
}
function PDF_close($p): void
{
 error_clear_last();
 $result = \PDF_close($p);
 if ($result === false) {
 throw PdfException::createFromPhpError();
 }
}
function PDF_closepath_fill_stroke($p): void
{
 error_clear_last();
 $result = \PDF_closepath_fill_stroke($p);
 if ($result === false) {
 throw PdfException::createFromPhpError();
 }
}
function PDF_closepath_stroke($p): void
{
 error_clear_last();
 $result = \PDF_closepath_stroke($p);
 if ($result === false) {
 throw PdfException::createFromPhpError();
 }
}
function PDF_closepath($p): void
{
 error_clear_last();
 $result = \PDF_closepath($p);
 if ($result === false) {
 throw PdfException::createFromPhpError();
 }
}
function PDF_concat($p, float $a, float $b, float $c, float $d, float $e, float $f): void
{
 error_clear_last();
 $result = \PDF_concat($p, $a, $b, $c, $d, $e, $f);
 if ($result === false) {
 throw PdfException::createFromPhpError();
 }
}
function PDF_continue_text($p, string $text): void
{
 error_clear_last();
 $result = \PDF_continue_text($p, $text);
 if ($result === false) {
 throw PdfException::createFromPhpError();
 }
}
function PDF_curveto($p, float $x1, float $y1, float $x2, float $y2, float $x3, float $y3): void
{
 error_clear_last();
 $result = \PDF_curveto($p, $x1, $y1, $x2, $y2, $x3, $y3);
 if ($result === false) {
 throw PdfException::createFromPhpError();
 }
}
function PDF_delete($pdfdoc): void
{
 error_clear_last();
 $result = \PDF_delete($pdfdoc);
 if ($result === false) {
 throw PdfException::createFromPhpError();
 }
}
function PDF_end_layer($pdfdoc): void
{
 error_clear_last();
 $result = \PDF_end_layer($pdfdoc);
 if ($result === false) {
 throw PdfException::createFromPhpError();
 }
}
function PDF_end_page_ext($pdfdoc, string $optlist): void
{
 error_clear_last();
 $result = \PDF_end_page_ext($pdfdoc, $optlist);
 if ($result === false) {
 throw PdfException::createFromPhpError();
 }
}
function PDF_end_page($p): void
{
 error_clear_last();
 $result = \PDF_end_page($p);
 if ($result === false) {
 throw PdfException::createFromPhpError();
 }
}
function PDF_end_pattern($p): void
{
 error_clear_last();
 $result = \PDF_end_pattern($p);
 if ($result === false) {
 throw PdfException::createFromPhpError();
 }
}
function PDF_end_template($p): void
{
 error_clear_last();
 $result = \PDF_end_template($p);
 if ($result === false) {
 throw PdfException::createFromPhpError();
 }
}
function PDF_fill_stroke($p): void
{
 error_clear_last();
 $result = \PDF_fill_stroke($p);
 if ($result === false) {
 throw PdfException::createFromPhpError();
 }
}
function PDF_fill($p): void
{
 error_clear_last();
 $result = \PDF_fill($p);
 if ($result === false) {
 throw PdfException::createFromPhpError();
 }
}
function PDF_fit_image($pdfdoc, int $image, float $x, float $y, string $optlist): void
{
 error_clear_last();
 $result = \PDF_fit_image($pdfdoc, $image, $x, $y, $optlist);
 if ($result === false) {
 throw PdfException::createFromPhpError();
 }
}
function PDF_fit_pdi_page($pdfdoc, int $page, float $x, float $y, string $optlist): void
{
 error_clear_last();
 $result = \PDF_fit_pdi_page($pdfdoc, $page, $x, $y, $optlist);
 if ($result === false) {
 throw PdfException::createFromPhpError();
 }
}
function PDF_fit_textline($pdfdoc, string $text, float $x, float $y, string $optlist): void
{
 error_clear_last();
 $result = \PDF_fit_textline($pdfdoc, $text, $x, $y, $optlist);
 if ($result === false) {
 throw PdfException::createFromPhpError();
 }
}
function PDF_initgraphics($p): void
{
 error_clear_last();
 $result = \PDF_initgraphics($p);
 if ($result === false) {
 throw PdfException::createFromPhpError();
 }
}
function PDF_lineto($p, float $x, float $y): void
{
 error_clear_last();
 $result = \PDF_lineto($p, $x, $y);
 if ($result === false) {
 throw PdfException::createFromPhpError();
 }
}
function PDF_makespotcolor($p, string $spotname): int
{
 error_clear_last();
 $result = \PDF_makespotcolor($p, $spotname);
 if ($result === false) {
 throw PdfException::createFromPhpError();
 }
 return $result;
}
function PDF_moveto($p, float $x, float $y): void
{
 error_clear_last();
 $result = \PDF_moveto($p, $x, $y);
 if ($result === false) {
 throw PdfException::createFromPhpError();
 }
}
function PDF_open_file($p, string $filename): void
{
 error_clear_last();
 $result = \PDF_open_file($p, $filename);
 if ($result === false) {
 throw PdfException::createFromPhpError();
 }
}
function PDF_place_image($pdfdoc, int $image, float $x, float $y, float $scale): void
{
 error_clear_last();
 $result = \PDF_place_image($pdfdoc, $image, $x, $y, $scale);
 if ($result === false) {
 throw PdfException::createFromPhpError();
 }
}
function PDF_place_pdi_page($pdfdoc, int $page, float $x, float $y, float $sx, float $sy): void
{
 error_clear_last();
 $result = \PDF_place_pdi_page($pdfdoc, $page, $x, $y, $sx, $sy);
 if ($result === false) {
 throw PdfException::createFromPhpError();
 }
}
function PDF_rect($p, float $x, float $y, float $width, float $height): void
{
 error_clear_last();
 $result = \PDF_rect($p, $x, $y, $width, $height);
 if ($result === false) {
 throw PdfException::createFromPhpError();
 }
}
function PDF_restore($p): void
{
 error_clear_last();
 $result = \PDF_restore($p);
 if ($result === false) {
 throw PdfException::createFromPhpError();
 }
}
function PDF_rotate($p, float $phi): void
{
 error_clear_last();
 $result = \PDF_rotate($p, $phi);
 if ($result === false) {
 throw PdfException::createFromPhpError();
 }
}
function PDF_save($p): void
{
 error_clear_last();
 $result = \PDF_save($p);
 if ($result === false) {
 throw PdfException::createFromPhpError();
 }
}
function PDF_scale($p, float $sx, float $sy): void
{
 error_clear_last();
 $result = \PDF_scale($p, $sx, $sy);
 if ($result === false) {
 throw PdfException::createFromPhpError();
 }
}
function PDF_set_border_color($p, float $red, float $green, float $blue): void
{
 error_clear_last();
 $result = \PDF_set_border_color($p, $red, $green, $blue);
 if ($result === false) {
 throw PdfException::createFromPhpError();
 }
}
function PDF_set_border_dash($pdfdoc, float $black, float $white): void
{
 error_clear_last();
 $result = \PDF_set_border_dash($pdfdoc, $black, $white);
 if ($result === false) {
 throw PdfException::createFromPhpError();
 }
}
function PDF_set_border_style($pdfdoc, string $style, float $width): void
{
 error_clear_last();
 $result = \PDF_set_border_style($pdfdoc, $style, $width);
 if ($result === false) {
 throw PdfException::createFromPhpError();
 }
}
function PDF_set_info($p, string $key, string $value): void
{
 error_clear_last();
 $result = \PDF_set_info($p, $key, $value);
 if ($result === false) {
 throw PdfException::createFromPhpError();
 }
}
function PDF_set_layer_dependency($pdfdoc, string $type, string $optlist): void
{
 error_clear_last();
 $result = \PDF_set_layer_dependency($pdfdoc, $type, $optlist);
 if ($result === false) {
 throw PdfException::createFromPhpError();
 }
}
function PDF_set_parameter($p, string $key, string $value): void
{
 error_clear_last();
 $result = \PDF_set_parameter($p, $key, $value);
 if ($result === false) {
 throw PdfException::createFromPhpError();
 }
}
function PDF_set_text_pos($p, float $x, float $y): void
{
 error_clear_last();
 $result = \PDF_set_text_pos($p, $x, $y);
 if ($result === false) {
 throw PdfException::createFromPhpError();
 }
}
function PDF_set_value($p, string $key, float $value): void
{
 error_clear_last();
 $result = \PDF_set_value($p, $key, $value);
 if ($result === false) {
 throw PdfException::createFromPhpError();
 }
}
function PDF_setcolor($p, string $fstype, string $colorspace, float $c1, float $c2, float $c3, float $c4): void
{
 error_clear_last();
 $result = \PDF_setcolor($p, $fstype, $colorspace, $c1, $c2, $c3, $c4);
 if ($result === false) {
 throw PdfException::createFromPhpError();
 }
}
function PDF_setdash($pdfdoc, float $b, float $w): void
{
 error_clear_last();
 $result = \PDF_setdash($pdfdoc, $b, $w);
 if ($result === false) {
 throw PdfException::createFromPhpError();
 }
}
function PDF_setdashpattern($pdfdoc, string $optlist): void
{
 error_clear_last();
 $result = \PDF_setdashpattern($pdfdoc, $optlist);
 if ($result === false) {
 throw PdfException::createFromPhpError();
 }
}
function PDF_setflat($pdfdoc, float $flatness): void
{
 error_clear_last();
 $result = \PDF_setflat($pdfdoc, $flatness);
 if ($result === false) {
 throw PdfException::createFromPhpError();
 }
}
function PDF_setfont($pdfdoc, int $font, float $fontsize): void
{
 error_clear_last();
 $result = \PDF_setfont($pdfdoc, $font, $fontsize);
 if ($result === false) {
 throw PdfException::createFromPhpError();
 }
}
function PDF_setgray_fill($p, float $g): void
{
 error_clear_last();
 $result = \PDF_setgray_fill($p, $g);
 if ($result === false) {
 throw PdfException::createFromPhpError();
 }
}
function PDF_setgray_stroke($p, float $g): void
{
 error_clear_last();
 $result = \PDF_setgray_stroke($p, $g);
 if ($result === false) {
 throw PdfException::createFromPhpError();
 }
}
function PDF_setgray($p, float $g): void
{
 error_clear_last();
 $result = \PDF_setgray($p, $g);
 if ($result === false) {
 throw PdfException::createFromPhpError();
 }
}
function PDF_setlinejoin($p, int $value): void
{
 error_clear_last();
 $result = \PDF_setlinejoin($p, $value);
 if ($result === false) {
 throw PdfException::createFromPhpError();
 }
}
function PDF_setlinewidth($p, float $width): void
{
 error_clear_last();
 $result = \PDF_setlinewidth($p, $width);
 if ($result === false) {
 throw PdfException::createFromPhpError();
 }
}
function PDF_setmatrix($p, float $a, float $b, float $c, float $d, float $e, float $f): void
{
 error_clear_last();
 $result = \PDF_setmatrix($p, $a, $b, $c, $d, $e, $f);
 if ($result === false) {
 throw PdfException::createFromPhpError();
 }
}
function PDF_setmiterlimit($pdfdoc, float $miter): void
{
 error_clear_last();
 $result = \PDF_setmiterlimit($pdfdoc, $miter);
 if ($result === false) {
 throw PdfException::createFromPhpError();
 }
}
function PDF_setrgbcolor_fill($p, float $red, float $green, float $blue): void
{
 error_clear_last();
 $result = \PDF_setrgbcolor_fill($p, $red, $green, $blue);
 if ($result === false) {
 throw PdfException::createFromPhpError();
 }
}
function PDF_setrgbcolor_stroke($p, float $red, float $green, float $blue): void
{
 error_clear_last();
 $result = \PDF_setrgbcolor_stroke($p, $red, $green, $blue);
 if ($result === false) {
 throw PdfException::createFromPhpError();
 }
}
function PDF_setrgbcolor($p, float $red, float $green, float $blue): void
{
 error_clear_last();
 $result = \PDF_setrgbcolor($p, $red, $green, $blue);
 if ($result === false) {
 throw PdfException::createFromPhpError();
 }
}
function PDF_show_xy($p, string $text, float $x, float $y): void
{
 error_clear_last();
 $result = \PDF_show_xy($p, $text, $x, $y);
 if ($result === false) {
 throw PdfException::createFromPhpError();
 }
}
function PDF_show($pdfdoc, string $text): void
{
 error_clear_last();
 $result = \PDF_show($pdfdoc, $text);
 if ($result === false) {
 throw PdfException::createFromPhpError();
 }
}
function PDF_skew($p, float $alpha, float $beta): void
{
 error_clear_last();
 $result = \PDF_skew($p, $alpha, $beta);
 if ($result === false) {
 throw PdfException::createFromPhpError();
 }
}
function PDF_stroke($p): void
{
 error_clear_last();
 $result = \PDF_stroke($p);
 if ($result === false) {
 throw PdfException::createFromPhpError();
 }
}
