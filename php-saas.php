<?php
$examplePMMSE = <<<EOD
@var forecolor = #ff0000;
@var backcolor = #00ff00;

@mixin alert
	border: 1px solid @backcolor
	height: 100%

@mixin clear
	content: ''

body 
	color: @forecolor
	background: @backcolor
	
	div 
		margin: 0
		
#test
	=alert
	=clear
	color: @forecolor
EOD;

class PMMSE {
	
	var $vars = array();
	var $selectors = array();
	
	function PMMSE ($css = '') {
		$this->body = $css;
		$this->extractVars();
		$this->setVars();
		$this->runIndentationLoop();
		$this->optimise();
	}
	
	function extractVars() {
		preg_match_all('/@var ([a-z]+) = ([^;]+);/', $this->body, $matches);
		foreach($matches[1] as $key => $var) {
			$this->vars[$var] = $matches[2][$key];
		}
		$this->body = str_replace($matches[0], '', $this->body);
		
		preg_match_all('/@mixin ([a-z]+)(\n\t([^\n]+)+)+/', $this->body, $matches);
		foreach($matches[0] as $mixin) {
			preg_match('/@mixin ([a-z]+)/', $mixin, $mixMatch);
			$mixinName = $mixMatch[1];
			$mixinValue = preg_replace('/@mixin [a-z]+/', '', $mixin);
			$this->body = str_replace('='.$mixinName, $mixinValue, $this->body);
			$this->body = preg_replace('/@mixin ([a-z]+)(\n\t[^\n]+)+/', '', $this->body);
		}
	}
	
	function setVars() {
		foreach($this->vars as $var => $val) {
			$this->body = str_replace('@'.$var, $val, $this->body);
		}
	}
	
	function runIndentationLoop() {
		$lines = explode("\n", $this->body);
		$previousSelector = '';
		$pastLevel = 0;
		$this->body = '';
		foreach($lines as $key => $line) {
			if(eregi('\:', $line)) { //Rule
				$this->body .= "\n\t".trim($line).';';
			} elseif(eregi('(\t*)[a-z]', $line)) { // Selector
				if($previousSelector) $this->body .= "\n}\n";
				
				$charCount = count_chars($line, 1);
				if($charCount[9] > $pastLevel) {
					$pastLevel = $pastLevel + 1;
					$this->body .= $previousSelector.' ';
				}
				
				$this->body .= trim($line).' {';
				$previousSelector = trim($line);
			}
		}
		$this->body .= "\n}";
	}
	
	function optimise() {
		// $this->body = trim(preg_replace('/\}([^a-z]*)/', '} ', $this->body));
	}
	
}

$pmmse = new PMMSE($examplePMMSE);
echo $pmmse->body;