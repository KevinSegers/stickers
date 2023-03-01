<?php


namespace Stickers\TextSticker {

    require_once('fonts.php');
    require_once('colors.php');
    require_once ('TextMeasure.php');
    use Stickers\CustomSticker as CustomSticker;
    use \TextMeasure as TextMeasure;

    const TTF_FILES = CustomSticker\TTF_FILES;
    const FONT_DIR = __DIR__ . '/../assets/custom-fonts/';

    class Sizing {

        public $width;
        public $cap_height;

        public function __construct($width, $cap_height) {
            $this->width = $width;
            $this->cap_height = $cap_height;
        }

    }

    class TextOptions {

        public $font_slug;
        public $alignment;
        public $color;
        public $line_height;
        public $ttf_path;
        public $min_cap_height;

        public function __construct($font_slug, $alignment, $color, $line_height) {
            $this->font_slug = $font_slug;
            $this->alignment = $alignment;
            $this->color = $color;
            $this->line_height = $line_height;
            $font_info = TTF_FILES[$font_slug];
            $this->ttf_path = FONT_DIR . $font_info['filename'];
            $this->min_cap_height = $font_info['min_cap_height'];
        }

    }

    class ImageOptions {
        public $color;
        public $mirrored;

        public function __construct($color, $mirrored) {
            $this->color = $color;
            $this->mirrored = $mirrored;
        }

    }

    class Line {

        public $text;
        public $bbox;
        public $width;
        public $height;
        public $x;
        public $y;
        public $y_vertically_aligned;

        public function __construct($text) {
            $this->text = $text;
        }

        public function calculate_bbox($size, $fontfile) {
            $measure = new TextMeasure($this->text, $fontfile, $size);
            $result = $measure->measureText();
            $this->width = $result['width'];
            $this->height = $result['height'];
            $this->x = $result['x'];
            $this->y = $result['y'];
            $this->y_vertically_aligned = $this->y;
        }

        public function recalculate_bbox($line_height) {
            if ($line_height == $this->height) {
                return 0;
            }
            // So the max line height is higher than the height of this line. This means we need to push this text
            // down a bit.
            $difference = ($line_height - $this->height) / 2;
            // Move the baseline a few pixels down
            $this->y_vertically_aligned = $this->y + $difference;
            return $difference;
        }

        public function get_x($alignment, $box_width) {
            if ($alignment == 'left') {
                return 0;
            }
            if ($alignment == 'right') {
                return $box_width - $this->width;
            }
            return ($box_width - $this->width) / 2;
        }

    }

    class Sticker {

        public $text;
        public $sizing;
        public $text_options;
        public $image_options;
        private $_lines;

        public function __construct($text, $sizing, $text_options, $image_options) {
            $this->text = $text;
            $this->sizing = $sizing;
            $this->text_options = $text_options;
            $this->image_options = $image_options;
        }

        public function render_big_text($font_size) {
            $line_height = $font_size * $this->text_options->line_height;
            $lines = $this->get_lines();
            $nr_lines = count($lines);
            $width = 0;
            $height = 0;
            $counter = 0;
            $max_height = 0;
            foreach ($lines as $line) {
                $line->calculate_bbox($font_size, $this->text_options->ttf_path);
                $width = max($width, $line->width);
                $max_height = max($max_height, $line->height);
                ++$counter;
            }
            $counter = 0;
            foreach ($lines as $line) {
                if ($counter == 0) {
                    $line->recalculate_bbox($max_height);
                    $height += $max_height;
                } else if ($counter == $nr_lines - 1) {
                    $line->recalculate_bbox($max_height);
                    $height += $max_height;
                } else {
                    $line->recalculate_bbox($max_height);
                    $height += $max_height;
                }
                ++$counter;
            }
            $im = imagecreatetruecolor($width, $height);
            imagesavealpha($im, true);
            $bg = imagecolorallocate($im, 0, 0, 0);
            imagefill($im, 0, 0, $bg);
            $white = imagecolorallocate($im, 255, 255, 255);
            $counter = 0;
            foreach ($lines as $line) {
                $y = $line->y + ($line_height * $counter);
                $rgb = [rand(0, 255), rand(0, 255), rand(0, 255)];
                $c = imagecolorallocate($im, $rgb[0], $rgb[1], $rgb[2]);
                imagefilledrectangle($im, $line->x, $max_height * $counter, $line->width, $max_height * ($counter + 1), $c);
                imagecolordeallocate($im, $c);
                $c = imagecolorallocate($im, min(255, $rgb[0] + 35), $rgb[1] + 15, $rgb[2] + 15);
                imagefilledrectangle($im, $line->x, $max_height * $counter, $line->width, ($max_height * $counter) + $line->height, $c);
                imagecolordeallocate($im, $c);
                imagettftext($im, $font_size, 0, $line->x, $line->y_vertically_aligned + ($max_height * $counter), $white,
                    $this->text_options->ttf_path, $line->text);
                ++$counter;
            }
            imagecolordeallocate($im, $white);
            return array('image' => $im, 'width' => $width, 'height' => $height);
        }

        public function render() {
            $canvas_width = 1000;
            $canvas_height = 300;
            $font_size = 100;
            $big_image = $this->render_big_text($font_size);
            while ($big_image['width'] < $canvas_width && $big_image['height'] < $canvas_height) {
                imagedestroy($big_image['image']);
                $font_size *= 10;
                $big_image = $this->render_big_text($font_size);
            }
            ob_start();
            imagepng($big_image['image']);
            $data = ob_get_contents();
            ob_end_clean();
            //imagedestroy($im);

            $dimensions = $this->convert_dimensions($font_size, $big_image['width'], $big_image['height']);
            $dimensions['data'] = $data;
            return $dimensions;
        }

        protected function convert_dimensions($font_size, $width, $height) {
            $line = new Line('E');
            $line->calculate_bbox($font_size, $this->text_options->ttf_path);
            if ($this->sizing->width) {
                $ratio = $this->sizing->width / $width;
                $new_width = $width * $ratio;
                $new_height = $height * $ratio;
                $cap_height = $line->height * $ratio;
            } else {
                $cap_height = $this->sizing->cap_height;
                $ratio = $this->sizing->cap_height / $line->height;
                $new_width = $width * $ratio;
                $new_height = $height * $ratio;
            }
            return array('width' => ceil($new_width), 'height' => ceil($new_height), 'cap_height' => ceil($cap_height));
        }

        protected function get_lines() {
            if (!isset($this->_lines)) {
                $lines = preg_split('/\n|\r\n?/', $this->text);
                $result = [];
                foreach ($lines as $line) {
                    $result[] = new Line($line);
                }
                $this->_lines = $result;
            }
            return $this->_lines;
        }
    }

}

namespace {
    ini_set('display_errors', 1);
    \error_reporting(E_ALL);
    require_once('fonts.php');
    function render() {
        $font_slug = isset($_GET['font_slug']) ? $_GET['font_slug'] : 'arial';
        $alignment = isset($_GET['alignment']) ? $_GET['alignment'] : 'left';
        $width_cap = isset($_GET['width_cap']) ? $_GET['width_cap'] : 'width';
        $input_width = '';
        $cap_height = '';
        if ($width_cap == 'width') {
            $input_width = isset($_GET['width']) ? $_GET['width'] : '400';
        } else {
            $cap_height = isset($_GET['cap_height']) ? $_GET['cap_height'] : '40';
        }
        header('Content-Type: text/html');
        echo '<html><body>';
        $default_text = "Dit is een zeer\nsimpele tekst om mee te\ntesten.\nWe kunnen er ook een zeer uitgebreide tekst (echt waar)\nvan maken zodat we een goed idee\nhebben van hoe het eruitziet.";
        $text = isset($_GET['text']) ? $_GET['text'] : $default_text;
        $sizing = new Stickers\TextSticker\Sizing($input_width, $cap_height);
        $text_options = new Stickers\TextSticker\TextOptions($font_slug, $alignment, 'black', 1.4);
        $image_options = new Stickers\TextSticker\ImageOptions('white', false);
        $sticker = new Stickers\TextSticker\Sticker($text, $sizing, $text_options, $image_options);
        $output = $sticker->render();
        $b = base64_encode($output['data']);
        echo '<img src="data:image/png;base64,'. $b .'">';
        echo '<pre>Breedte: ' . $output['width'] . ' mm | Hoogte: ' . $output['height'] . ' mm | Kapitaalhoogte: ' . $output['cap_height'] . ' mm</pre>';
        echo '<form action="textsticker.php" method="get">';
        echo '<p><select name="font_slug">';
        foreach (\Stickers\TextSticker\TTF_FILES as $slug => $fd) {
            $selected = '';
            if ($slug == $font_slug) {
                $selected = ' selected';
            }
            echo '<option name="' . $slug = '"' . $selected . '>' . $slug . '</option>';
        }
        echo '</select></p><p>';
        $alignment_options = ['left', 'center', 'right'];
        foreach ($alignment_options as $option) {
            $selected = '';
            if ($option == $alignment) {
                $selected = ' checked';
            }
            echo '<label><input type="radio" name="alignment" value="' . $option . '"' . $selected . '> ' . $option . '</label>';
        }
        echo '<p>';
        echo '<label><input type="radio" name="width_cap" value="width" ' . ($width_cap == 'width' ? 'checked' : '') . '> Breedte <input type="text" name="width" value="' . $input_width . '"></label>';
        echo '<label><input type="radio" name="width_cap" value="cap" ' . ($width_cap == 'cap' ? 'checked' : '') . '> Kapitaalhoogte <input type="text" name="cap_height" value="' . $cap_height . '"></label>';
        echo '</p>';
        echo '<textarea name="text" cols="80" rows="10">' . $text . '</textarea>';
        echo '</p><input type="submit"></form>';
        echo '</body></html>';
    }

    render();
}