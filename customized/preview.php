<?php

namespace {
    if (!defined('CUSTOMIZED_STICKER_INCLUDE')) {
        ini_set('display_errors', 1);
        \error_reporting(E_ALL);
    }
}

namespace Stickers\CustomSticker {

    // Set up composer auto-loading for PHPixie\Image
    require_once(__DIR__ . '/../../../../vendor/autoload.php');
    require_once('fonts.php');
    require_once('colors.php');

    use GDText\Box as Box;
    use GDText\Color as Color;
    use GDText\TextWrapping as TextWrapping;

    // Directory containing the TTF files
    const FONT_DIR = __DIR__ . '/../assets/custom-fonts/';

    // We're using fixed line spacing / line height
    const LINE_SPACING = 1.1;
    // Max allowed width of stickers in mm
    const MAX_STICKER_WIDTH_MM = 3000;
    // Max allowed height of stickers in mm
    const MAX_STICKER_HEIGHT_MM = 590;
    // Max number of lines in the text
    const MAX_NR_LINES = 10;

    // The minimum and maximum dimensions of the preview image in pixels
    const MAX_IMAGE_WIDTH = 1008;
    const MIN_IMAGE_WIDTH = 50;
    const MAX_IMAGE_HEIGHT = 240;
    const MIN_IMAGE_HEIGHT = 120;
    // 0 pixels of padding
    const IMAGE_PADDING = 0;
    const SIZE_MARKER_OFFSET = 0;

    // The sample font size to use; since we need to know the ratios this shouldn't matter much at all.
    const SAMPLE_FONT_SIZE = 32;
    // The sample text to use to determine the cap height
    const SAMPLE_TEXT_CAP_HEIGHT = 'E';

    // Error messages
    const ERRORS = array(
        'font_required' => 'Gelieve een lettertype te selecteren',
        'text_required' => 'Gelieve tekst in te voeren',
        'max_nr_lines_exceeded' => 'Maximaal %d lijnen tekst toegelaten',
        'cap_height_too_low' => 'De kapitaalhoogte van dit lettertype moet minstens %d mm zijn',
        'cap_height_or_width_required' => 'Gelieve de kapitaalhoogte of breedte van de tekst in te vullen',
        'sticker_too_high' => 'De maximale hoogte van een sticker is %d cm',
        'sticker_too_wide' => 'Een sticker kan maximaal %d cm breed zijn',
        'min_line_height_too_low' => 'De kapitaalhoogte van dit lettertype moet minstens %d mm zijn. Gelieve breedte of kapitaalhoogte aan te passen.',
        'min_height_too_low' => 'De minimale hoogte van een sticker met dit lettertype is %d mm'
    );


    /**
     * Clamps the image width to a fixed range. We don't want random requests for images of 10.000 pixels.
     * @param $image_width int supplied image width
     * @return int clamped image width
     */
    function clamp_image_width($image_width = 1008) {
        return ceil(max(MIN_IMAGE_WIDTH, min(MAX_IMAGE_WIDTH, $image_width)));
    }

    function clamp_image_height($image_height) {
        return ceil(min(MAX_IMAGE_HEIGHT, max(MIN_IMAGE_HEIGHT, $image_height)));
    }

    function calculate_text_dimensions($text, $ttf_path, $font_size) {
        // We need to multiply the font-size with 0.75 to go from pixels to points--the same method used by GDText
        // when rendering the text.
        $text = trim($text);
        $font_size_pts = $font_size * 0.75 * LINE_SPACING;
        $angle = 0; // No angle please
        $rect = imageftbbox($font_size_pts, $angle, $ttf_path, $text, array('linespacing' => LINE_SPACING));
        return array('width' => abs($rect[2] - $rect[0]), 'height' => abs($rect[5] - $rect[1]));
    }

    /**
     * Turns the font slug from the backend into useful information: the ttf_path to the font file and its min cap height.
     * @param $font_slug string slug of the font
     * @return array with path to the TTF file and min height
     */
    function get_font_details_from_slug($font_slug) {
        $font_info = TTF_FILES[$font_slug];
        return array(
            'ttf_path' => FONT_DIR . $font_info['filename'],
            'min_height' => $font_info['min_cap_height']
        );
    }


    function get_lines($text) {
        $lines = preg_split('/\n|\r\n?/', $text);
        $actual = [];
        foreach ($lines as $line) {
            if (!empty(trim($line))) {
                $actual[] = $line;
            }
        }
        return $actual;
    }


    function estimate_font_size($text, $ttf_path, $desired_width, $desired_height, $max_estimates = 200) {
        $attempt = 0;
        $font_size = 10;
        $best_guess = 10;
        $best_guess_dimensions = array();
        $width = 0;
        $height = 0;
        $ender = array();
        do {
            $dimensions = calculate_text_dimensions($text, $ttf_path, $font_size);
            if ($dimensions['width'] < $desired_width && $dimensions['height'] < $desired_height) {
                $best_guess = $font_size;
                $best_guess_dimensions = $dimensions;
                $font_size += 1;
            } else {
                $ender['font_size'] = $font_size;
                $ender['desired_width'] = $desired_width;
                $ender['desired_height'] = $desired_height;
                $ender['measured_width'] = $dimensions['width'];
                $ender['measured_height'] = $dimensions['height'];
                break;
            }
            ++$attempt;
        } while ($attempt < $max_estimates && $width < $desired_width && $height < $desired_height);
        $ender['dimensions'] = $best_guess_dimensions;
        $ender['font_size'] = $best_guess;
        $ender['attempts'] = $attempt;
        return $ender;
    }


    /**
     * Calculates the dimensions of the sticker based on the desired cap height, font and text.
     * @param $desired_cap_height int cap height to apply
     * @param $text string text to use
     * @param $ttf_path string path to the TTF file
     * @return array containing the dimensions: cap_height, width and height
     */
    function calculate_dimensions_from_cap_height($desired_cap_height, $text, $ttf_path) {
        $text_dimensions = calculate_text_dimensions($text, $ttf_path, SAMPLE_FONT_SIZE);
        $cap_dimensions = calculate_text_dimensions(SAMPLE_TEXT_CAP_HEIGHT, $ttf_path, SAMPLE_FONT_SIZE);
        $cap_height = $cap_dimensions['height'];
        $ratio = $cap_height / $desired_cap_height;
        $min_line_height = PHP_INT_MAX;
        $lines = get_lines($text);
        foreach ($lines as $line) {
            $line_dimensions = calculate_text_dimensions($line, $ttf_path, SAMPLE_FONT_SIZE);
            $line_height = $line_dimensions['height'];
            $min_line_height = min($min_line_height, $line_height);
        }
        return array(
            'min_height' => ceil($min_line_height / $ratio),
            'cap_height' => $desired_cap_height,
            'width' => ceil($text_dimensions['width'] / $ratio),
            'height' => ceil($text_dimensions['height'] / $ratio)
        );
    }


    /**
     * Calculates the sticker dimensions based on the desired width, font and text.
     * @param $desired_width int width to use
     * @param $text string text to use
     * @param $ttf_path string path to the TTF file
     * @return array containing the dimensions: cap_height, width and height
     */
    function calculate_dimensions_from_width($desired_width, $text, $ttf_path) {
        $text_dimensions = calculate_text_dimensions($text, $ttf_path, SAMPLE_FONT_SIZE);
        $cap_dimensions = calculate_text_dimensions(SAMPLE_TEXT_CAP_HEIGHT, $ttf_path, SAMPLE_FONT_SIZE);
        $width = $text_dimensions['width'];
        $ratio = $width / $desired_width;
        $min_line_height = PHP_INT_MAX;
        $lines = get_lines($text);
        foreach ($lines as $line) {
            $line_dimensions = calculate_text_dimensions($line, $ttf_path, SAMPLE_FONT_SIZE);
            $line_height = $line_dimensions['height'];
            $min_line_height = min($min_line_height, $line_height);
        }
        return array(
            'min_height' => ceil($min_line_height / $ratio),
            'cap_height' => ceil($cap_dimensions['height'] / $ratio),
            'width' => $desired_width,
            'height' => ceil($text_dimensions['height'] / $ratio)
        );
    }


    /**
     * Creates an image containing the given text.
     * @param $dimensions array sticker dimensions to use
     * @param $text string text to add
     * @param $ttf_path string path to the TTF file
     * @param $image_width int the desired width of the actual image (not to be confused with the sticker width)
     * @param $fg_color string the foreground color as an HTML color name
     * @param $bg_color string the background color as an HTML color name (or null)
     * @param $alignment string one of left, center or right
     * @param $mirrored bool whether to mirror the image
     * @return mixed
     */
    function create_image($dimensions, $text, $ttf_path, $image_width, $fg_color, $bg_color, $alignment, $mirrored) {
        $image_width = clamp_image_width($image_width);
        $ratio = $dimensions['width'] / $dimensions['height'];
        $image_height = clamp_image_height($image_width / $ratio);
        $bg_color = color_name_to_rgb($bg_color);
        $im = imagecreatetruecolor($image_width + SIZE_MARKER_OFFSET, $image_height);
        // We need the alpha channel for transparent backgrounds
        imagesavealpha($im, true);
        // Set the background color
        if ($bg_color) {
            $color = imagecolorallocate($im, $bg_color[0], $bg_color[1], $bg_color[2]);
            imagefill($im, 0, 0, $color);
            imagecolordeallocate($im, $color);
            unset($color);
        } else {
            $color = imagecolorallocatealpha($im, 0, 0, 0, 127);
            imagefill($im, 0, 0, $color);
        }
        $fg_color = color_name_to_rgb($fg_color);
        // Estimate the font size
        $font_size_estimate = estimate_font_size(
            $text,
            $ttf_path,
            $image_width - (IMAGE_PADDING * 2),
            $image_height - (IMAGE_PADDING * 2)
        );
        // Ok, time to start rendering the text
        $box = new Box($im);
        // Set the box for the text with the correct padding
        $box->setBox(IMAGE_PADDING, IMAGE_PADDING, $image_width - IMAGE_PADDING, $image_height - IMAGE_PADDING);
        $box->setFontFace($ttf_path);
        $box->setFontSize($font_size_estimate['font_size']);
        $box->setFontColor(new Color($fg_color[0], $fg_color[1], $fg_color[2]));
        $box->setLineHeight(LINE_SPACING);
        $box->setTextWrapping(TextWrapping::NoWrap);
        $box->setTextAlign($alignment, 'center');
        $box_size = $box->draw($text);
        if ($mirrored) {
            imageflip($im, IMG_FLIP_HORIZONTAL);
        }
        $text_height = abs($box_size['y']['max'] - $box_size['y']['min']);
        $text_width = abs($box_size['x']['max'] - $box_size['x']['min']);
        $horizontal_middle = ceil($image_width / 2);
        $vertical_middle = ceil($image_height / 2);
        $text_top = ceil($text_height / 2);
        $text_left = ceil($text_width / 2);
        $markers = array(
            'left' => $horizontal_middle - $text_left,
            'right' => $horizontal_middle - $text_left + $text_width,
            'top' => $vertical_middle - $text_top,
            'bottom' => $vertical_middle - $text_top + $text_height
        );
        // Render the image and spit it out
        ob_start();
        imagepng($im);
        $data = ob_get_contents();
        ob_end_clean();
        return array('data' => $data, 'markers' => $markers);
    }


    function select_price($width, $nr_lines) {
        $width_cm = ceil($width / 10);
        if ($width_cm < 5) {
            $width_prefix = 'lt5';
        } else {
            $price_step = 5;
            $remainder = $width_cm % $price_step;
            $rounding = ($remainder === 0 ? 0 : ($price_step - $remainder));
            $width_prefix  = (string) ($width_cm + $rounding);
        }
        return $width_prefix . '_' . $nr_lines;
    }


    /**
     * Creates a response to a sticker sizing request.
     *
     * One of desired_cap_height or desired_width is required.
     *
     * @param $desired_cap_height int the cap height the user wants
     * @param $desired_width int the width the user wants
     * @param $text string the sticker text
     * @param $font_slug string the slug of the font to use for the text
     * @param $image_width int the width of the included image (0 for no image)
     * @param $fg_color string HTML color name of the text color
     * @param $bg_color string HTML color name of the background color
     * @param $alignment string one of left, center or right
     * @param $mirrored bool whether to mirror the image
     */
    function calculate_dimensions($desired_cap_height, $desired_width, $text, $font_slug, $image_width, $fg_color, $bg_color, $alignment, $mirrored) {
        $validation = validate_input($desired_cap_height, $desired_width, $text, $font_slug, $image_width, $fg_color, $bg_color, $alignment, $mirrored);
        $errors = $validation['errors'];
        $dimensions = null;
        $price_selection = null;
        $image_data = null;
        $markers = null;
        if (empty($errors)) {
            $desired_cap_height = $validation['desired_cap_height'];
            $desired_width = $validation['desired_width'];
            $text = $validation['text'];
            $font_details = $validation['font_details'];
            $image_width = $validation['image_width'];
            $fg_color = $validation['fg_color'];
            $bg_color = $validation['bg_color'];
            $alignment = $validation['alignment'];
            $mirrored = $validation['mirrored'];
            $nr_lines = $validation['nr_lines'];
            $ttf_path = $font_details['ttf_path'];
            if (isset($desired_cap_height) && $desired_cap_height > 0) {
                $dimensions = calculate_dimensions_from_cap_height($desired_cap_height, $text, $ttf_path);
            } else {
                $dimensions = calculate_dimensions_from_width($desired_width, $text, $ttf_path);
            }
            $height = $dimensions['height'];
            $width = $dimensions['width'];
            $errors = validate_sticker_dimensions($width, $height, $font_details['min_height'], $dimensions['min_height'], $dimensions['cap_height']);
            if (!$errors) {
                $price_selection = select_price($width, $nr_lines);
                if ($image_width) {
                    $img_data = create_image($dimensions, $text, $ttf_path, $image_width, $fg_color, $bg_color, $alignment, $mirrored);
                    $image_data = base64_encode($img_data['data']);
                    $markers = $img_data['markers'];
                }
            }
        }
        if ($errors) {
            $response = array('status' => 'error', 'errors' => $errors, 'dimensions' => $dimensions);
        } else {
            $response = array('status' => 'ok', 'dimensions' => $dimensions, 'price_selection' => $price_selection, 'image_data' => $image_data, 'markers' => $markers);
        }
        header('Content-Type: application/json');
        echo json_encode($response);
        die('');
    }


    function validate_sticker_dimensions($width, $height, $min_cap_height, $min_height, $actual_cap_height) {
        $errors = [];
        if ($min_height < $min_cap_height || $min_cap_height > $height || $actual_cap_height < $min_cap_height) {
            $errors[] = sprintf(ERRORS['min_line_height_too_low'], $min_cap_height);
        }
        if ($height > MAX_STICKER_HEIGHT_MM) {
            $errors[] = sprintf(ERRORS['sticker_too_high'], (MAX_STICKER_HEIGHT_MM / 10));
        }
        if ($width > MAX_STICKER_WIDTH_MM) {
            $errors[] = sprintf(ERRORS['sticker_too_wide'], (MAX_STICKER_WIDTH_MM / 10));
        }
        return $errors;
    }


    function validate_input($desired_cap_height, $desired_width, $text, $font_slug, $image_width, $fg_color, $bg_color, $alignment, $mirrored) {
        $errors = [];
        $font_details = null;
        $nr_lines = 1;
        if (!isset($font_slug)) {
            $errors[] = ERRORS['font_required'];
        } else {
            $font_details = get_font_details_from_slug($font_slug);
        }
        if (!isset($text)) {
            $errors[] = ERRORS['text_required'];
        } else {
            $text = trim($text);
            $lines = preg_split("/\r\n|\n|\r/", $text);
            $nr_lines = sizeof($lines);
            if ($nr_lines > MAX_NR_LINES) {
                $errors[] = sprintf(ERRORS['max_nr_lines_exceeded'], MAX_NR_LINES);
            }
        }
        if (isset($desired_cap_height) && $desired_cap_height > 0) {
            if ($font_details) {
                $min_cap_height = $font_details['min_height'];
                if ($min_cap_height > $desired_cap_height) {
                    $errors[] = sprintf(ERRORS['min_line_height_too_low'], $min_cap_height);
                }
            }
        } else if (isset($desired_width) && $desired_width > 0) {
            // Nothing to validate, but the intent is more clear.
        } else {
            $errors[] = ERRORS['cap_height_or_width_required'];
        }
        $alignment = isset($alignment) && in_array($alignment, ['left', 'center', 'right']) ? $alignment : 'left';
        $mirrored = (isset($mirrored) && $mirrored == 'yes');
        $fg_color = $fg_color ? $fg_color : 'Black';
        $bg_color = $bg_color ? $bg_color : null;
        return array(
            'errors' => $errors,
            'desired_cap_height' => $desired_cap_height,
            'desired_width' => $desired_width,
            'text' => $text,
            'nr_lines' => $nr_lines,
            'font_details' => $font_details,
            'image_width' => $image_width,
            'fg_color' => $fg_color,
            'bg_color' => $bg_color,
            'alignment' => $alignment,
            'mirrored' => $mirrored,
        );
    }

    if (!defined('CUSTOMIZED_STICKER_INCLUDE')) {
        // Grab the parameters from the request.
        $request = $_GET;
        $desired_cap_height = isset($request['desired_cap_height']) ? $request['desired_cap_height'] : null;
        $desired_width = isset($request['desired_width']) ? $request['desired_width'] : null;
        $text = isset($request['text']) ? $request['text'] : null;
        $font_slug = isset($request['font_slug']) ? $request['font_slug'] : null;
        $bg_color = isset($request['bg_color']) ? $request['bg_color'] : null;
        $fg_color = isset($request['fg_color']) ? $request['fg_color'] : null;
        $image_width = isset($request['image_width']) ? $request['image_width'] : null;
        $alignment = isset($request['alignment']) ? $request['alignment'] : null;
        $mirrored = isset($request['mirrored']) ? $request['mirrored'] : false;
        // And create a response.
        calculate_dimensions($desired_cap_height, $desired_width, $text, $font_slug, $image_width, $fg_color, $bg_color, $alignment, $mirrored);
    }

}
