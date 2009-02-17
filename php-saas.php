<?php

class PHPSaas {
	
	var $vars = array();
	var $selectors = array();
	
	function PHPSaas ($css = '') {
		$this->body = $css;
		$this->stripComments();
		$this->extractVars();
		$this->setVars();
		$this->runIndentationLoop();
		$this->optimise();
	}
	
	function stripComments() {
		$this->body = preg_replace('/\/\/([^\n]+)/', '', $this->body);
	}
	
	function extractVars() {
		preg_match_all('/@import \'([^\']+)\'/', $this->body, $matches);
		foreach($matches[0] as $key => $line) {
			$this->body = str_replace($line, file_get_contents($matches[1][0].'.psaas'), $this->body);
		}
		
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
			if(eregi('([a-z]+)\:', $line)) { //Rule
				$this->body .= "\n\t".trim($line).';';
			} elseif(eregi('(\t*)[\.a-z]', $line)) { // Selector
				if($previousSelector) $this->body .= "\n}\n";
				//echo $line."\n";
				$charCount = count_chars($line, 1);
				if(eregi('&', $line)) {
					$pastLevel = $charCount[9];
					$this->body .= str_replace('&', trim($previousSelector), trim($line)).' {';
				} else if($charCount[9] > $pastLevel) {
					$pastLevel = $pastLevel + 1;
					$this->body .= (eregi('(\t*)\:', $line)) ? $previousSelector : $previousSelector.' ';
					$this->body .= trim($line).' {';
				} else {
					$this->body .= trim($line).' {';
				}
				$previousSelector = trim($line);
			}
		}
		$this->body .= "\n}";
	}
	
	function optimise() {
		$this->body = trim(preg_replace('/\}([\n]+)/', '} ', $this->body));
	}
	
}