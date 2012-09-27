<?
	class Datamax {
		private $output;
		private $font;
		public function __construct($font=null) { $this->font($font); }
		public function font($font) {
			switch($font) {
				case 1: $this->font = '001'; break; // 6pt
				case 2: $this->font = '002'; break; // 8pt
				case 3: $this->font = '003'; break; // 10pt
				case 4: $this->font = '004'; break; // 12pt
				case 5: $this->font = '005'; break; // 14pt
				case 6: $this->font = '006'; break; // 18pt
				case 7: $this->font = '007'; break; // 24pt
				case 8: $this->font = '008'; break; // 30pt
				case 9: $this->font = '009'; break; // 36pt
				case 10: $this->font = '010'; break; // 48pt
				case 0:
				default: $this->font = '000'; // 5pt
			}
		}
		public function text($x, $y, $text, $wrapChars=null) {
			$x = self::decimalToCoordinate($x);
			$y = self::decimalToCoordinate($y);
			$this->output .= '1911'.$this->font.$x.$y.$text."\n";
		}
		public function timestamp($x, $y, $format) {
			$x = self::decimalToCoordinate($x);
			$y = self::decimalToCoordinate($y);
			$this->output .= '1191'.$this->font.$x.$y.'T'.$format."\n";
		}
		public function justifyCenter() { $this->output .= "JC\n"; }
		public function justifyLeft() { $this->output .= "JL\n"; }
		public function justifyRight() { $this->output .= "JR\n"; }
		public function boldOn() { $this->output .= "FB+\n"; }
		public function boldOff() { $this->output .= "FB-\n"; }
		public function increment($increment=1) {
			if($increment > 99) throw new Exception('Increment value is too big');
			$this->output .= '+'.str_pad($increment, 2, '0');
		}
		public function line($x, $y, $h, $w) {
			$x = self::decimalToCoordinate($x);
			$y = self::decimalToCoordinate($y);
			$h = self::decimalToCoordinate($h);
			$w = self::decimalToCoordinate($w);
			$this->output .= '1X11000'.$x.$y.'l'.$h.$w."\n";
		}
		public function go($qty, $ipAddress=FALSE) {
			// input checking
			if(($qty = filter_var($qty, FILTER_VALIDATE_INT, array('min_range' => 0))) === FALSE)
				throw new Exception(__METHOD__."(): Invalid quantity");
			if(($ipAddress = filter_var($ipAddress, FILTER_VALIDATE_IP)) === FALSE)
				throw new Exception(__METHOD__."(): invalid IP address");
			// finalize output
			$out = "A".date("NmdYHi", time())."000\n". // Set the current time and date
				"L\n". // Start formatting mode
				"Q".$qty."\n". // Tell labeler how many to print
				$this->output.
				"E\n";
			// send to labeler
			$errno = 0; $errstr = ''; $s = FALSE; $tries = 0;
			while($s === FALSE && $tries < 5) { // often doesn't connect immediately
				$s = fsockopen($ipAddress, 9100, $errno, $errstr, 10);
				$tries++;
				if($s === FALSE) sleep(3);
			}
			if($s === FALSE) throw new Exception(__METHOD__.'(): error opening socket');
			fwrite($s, $out);
			fclose($s);
		}
		static function decimalToCoordinate($z) {
			if($z >= 100) throw new Exception('Value is too big');
			$z = round($z*100, 2);
			$z = str_pad($z, 4, '0', STR_PAD_LEFT);
			return $z;
		}
	}