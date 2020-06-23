<?php

require_once 'Color.class.php';

class Vertex {
	private $_x;
	private $_y;
	private $_z;
	private $_w = 1.0;
	private $_color;

	public static $verbose = FALSE;

	public function __construct(array $xyzwc)
	{
		if (!isset($xyzwc['x']) || !isset($xyzwc['y']) || !isset($xyzwc['z']))
			return FALSE;
		if (isset($xyzwc['w']))
			$this->_w = $xyzwc['w'];
		if (isset($xyzwc['color']))
			$this->_color = $xyzwc['color'];
		else
			$this->_color = new Color( array( 'red' => 255, 'green' => 255, 'blue' => 255 ) );
		$this->_x = $xyzwc['x'];
		$this->_y = $xyzwc['y'];
		$this->_z = $xyzwc['z'];
		if (self::$verbose) {
			echo $this->__toString() . " constructed" . PHP_EOL;
		}
	}

	public function __destruct()
	{
		if (self::$verbose) {
			echo $this->__toString() . " destructed" . PHP_EOL;
		}
	}

	public function __toString()
	{
		return "Vertex( x: " . number_format($this->_x, 2, ".", "") .
			", y: " . number_format($this->_y, 2, ".", "") .
			", z: " . number_format($this->_z, 2, ".", "") .
			", w: " . number_format($this->_w, 2, ".", "") .
			(self::$verbose ? ", " . $this->_color : "") .
			" )";
	}

	public static function doc()
	{
		return file_get_contents('Vertex.doc.txt') . PHP_EOL;
	}

	public function getX() { return $this->_x; }
	public function getY() { return $this->_y; }
	public function getZ() { return $this->_z; }
	public function getW() { return $this->_w; }
	public function getColor() { return $this->_color; }

	public function setX( $x ) { $this->_x = $x; }
	public function setY( $y ) { $this->_y = $y; }
	public function setZ( $z ) { $this->_z = $z; }
	public function setW( $w ) { $this->_w = $w; }
	public function setColor( Color $color ) { $this->_color = $color; }

	public function __get( $property )
    {
		if (property_exists($this, $property))
			return $this->$property;
		$func = 'get'.strtoupper($property);
		if (method_exists($this, $func))
			return $this->$func();
    }

    public function __set( $property, $value )
    {
		if (property_exists($this, $property))
		{
			switch($property) {
				case "_x":
				case "_y":
				case "_z":
				case "_w":
					if (is_float($value))
						$this->$property = $value;
				case "_color":
					if (strtolower(get_class($value)) == "color")
						$this->$property = $value;
			}
			return ;
		}
		$func = 'set'.strtoupper($property);
		if (method_exists($this, $func))
			return $this->$func($value);
    }
}
?>
