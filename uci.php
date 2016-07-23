<?php
ini_set('max_execution_time', 0);
ini_set('memory_limit', '-1');
require 'vendor/autoload.php';

use Ryanhs\SimpleChessAI\AI;
$ai = new AI();

// this code taken from http://www.thecave.info/php-stdin-command-line-input-user/
function read_stdin() {
	$fr = fopen("php://stdin","r");
	$input = fgets($fr,128);
	$input = rtrim($input);
	fclose($fr);
	return $input;
}

// make uci wrapper
function uci() {
	global $ai;
	$args = explode(' ', trim(read_stdin()));
	
	switch ($args[0]) {
		case "uci":
			echo 'uciok'.PHP_EOL;
			break;
			
		case "isready":
			echo 'readyok'.PHP_EOL;
			break;
			
		case "ucinewgame":
			$ai = new AI();
			break;
			
		case "position":
			if (count($args) >= 8) {
				if ($args[1] == 'fen') {
					$fen = join(' ', array_slice($args, 2));
					$ai->load($fen);
					//~ echo 'fen loaded'.PHP_EOL;
				}
			}
			break;
			
		case "go":
			if (count($args) >= 3) {
				if ($args[1] == 'depth') {
					$ai->run(intval($args[2]));
				}
			}
			break;
		
		
		case "quit":
			exit;
			break;
			
		case "":
		case "\r":
		case "\n":
		case "\r\n":
			break;
			
		default: 
			echo 'Unknown command: '.join(' ', $args).PHP_EOL;
			break;
	}
	
	uci();
}

// run
echo "SimpleChessAI in PHP by Ryan H. Silalahi".PHP_EOL;
uci();
