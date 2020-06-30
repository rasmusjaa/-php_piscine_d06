<?php
class Color {
	public $red;
	public $green;
	public $blue;

	public static $verbose = FALSE;

	public function __construct(array $colors)
	{
		if (!isset($colors['rgb']) && (!isset($colors['red']) || !isset($colors['green']) || !isset($colors['blue'])))
			return FALSE;
		if (isset($colors['rgb']))
		{
			$colors['red'] = ($colors['rgb'] >> 16) & 0xFF;
            $colors['green'] = ($colors['rgb'] >> 8) & 0xFF;
            $colors['blue'] = $colors['rgb'] & 0xFF;
		}
		$this->red = intval($colors['red']);
        $this->green = intval($colors['green']);
        $this->blue = intval($colors['blue']);
		if (self::$verbose) {
			echo $this->__toString() . " constructed." . PHP_EOL;
		}
	}

	public function __destruct()
	{
		if (self::$verbose) {
			echo $this->__toString() . " destructed." . PHP_EOL;
		}
	}

	public function __toString()
	{
		return "Color( red: " . str_pad($this->red, 3, " ", STR_PAD_LEFT) .
			", green: " . str_pad($this->green, 3, " ", STR_PAD_LEFT) .
			", blue: " . str_pad($this->blue, 3, " ", STR_PAD_LEFT) . " )";
	}

	public static function doc()
	{
		return file_get_contents('Color.doc.txt') . PHP_EOL;
	}

	public function add(Color $colors)
	{
		return new Color([
			'red' => $this->red + $colors->red,
			'green' => $this->green + $colors->green,
			'blue' => $this->blue + $colors->blue
		]);
	}

	public function sub(Color $colors)
	{
		return new Color([
			'red' => $this->red - $colors->red,
			'green' => $this->green - $colors->green,
			'blue' => $this->blue - $colors->blue
		]);
	}

	public function mult($f)
	{
		return new Color([
			'red' => $this->red * $f,
			'green' => $this->green * $f,
			'blue' => $this->blue * $f
		]);
	}
}
?>
