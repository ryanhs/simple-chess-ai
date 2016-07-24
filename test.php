<?php
ini_set('max_execution_time', 0);
require 'vendor/autoload.php';
use Ryanhs\Chess\Chess;

// defaulting depth
if (count($argv) >= 2) {
	$_GET['depth'] = $argv[1];
}
$depth = !empty($_GET['depth']) ? $_GET['depth'] : 2;


// set output raw
header('Content-Type: text/plain');
echo "Engine                 go depth {$depth}".PHP_EOL;
echo str_repeat('=', 35).PHP_EOL;


// engine list
benchmark('/usr/games/stockfish', $depth);
benchmark('/home/ryan/Documents/research/thesis/CAI-ITB/UCI-Engine/Gull/src/Gull', $depth);
benchmark('/home/ryan/Documents/research/thesis/CAI-ITB/UCI-Engine/critter_1.6a_linux/critter-16a-64bit', $depth);
benchmark('/usr/games/toga2', $depth);
benchmark('/home/ryan/Documents/research/thesis/CAI-ITB/UCI-Engine/Spike_12_linux/spike', $depth);
benchmark('/usr/games/fruit', $depth);
benchmark('node /home/ryan/Documents/research/thesis/CAI-ITB/UCI-Engine/lozza.js', $depth);
//~ benchmark('php uci.php', $depth);


function benchmark($engineCommand, $depth, $alias = false) {
	if ($alias == false) {
		$alias = basename(str_replace(' ', '/', $engineCommand));
	}
	
	$fen = (new Chess())->fen();

	// load engine
	$descriptorspec = array(
		0 => array("pipe","r"),
		1 => array("pipe","w"),
		2 => array("file", "/tmp/error-output.txt", "a") // stderr is a file to write to
	) ;
	$process = proc_open($engineCommand, $descriptorspec, $pipes, __DIR__, null, []) ;
	if (!is_resource($process)) {
		echo 'error on load engine!';
		exit;
	}


	// process move, code referenced from: https://github.com/antiproton/Web-GUI-for-stockfish-chess
	fwrite($pipes[0], "uci\n");
	usleep(100000);
	fwrite($pipes[0], "ucinewgame\n");
	usleep(100000);
	fwrite($pipes[0], "isready\n");
	usleep(100000);
	fwrite($pipes[0], "position fen $fen\n");
	usleep(100000);
	$time_start = microtime(true);
	fwrite($pipes[0], "go depth $depth\n");

	// parse move, code referenced from: https://github.com/antiproton/Web-GUI-for-stockfish-chess
	$str="";
	while(true){
		usleep(1000);
		$s = fgets($pipes[1],4096);
		//~ echo $s;
		
		if(strpos(' '.$s,'bestmove')){
			$time_end = microtime(true);
			break;
		}
	}
	
	$spacer = 25 - strlen($alias);
	$execution_time = ($time_end - $time_start);
	$execution_time = number_format($execution_time, 2);
	echo $alias.str_repeat(' ', $spacer).$execution_time."s".PHP_EOL;
	
	// end process
	fclose($pipes[0]);
	fclose($pipes[1]);
	if(isset($pipes[2])) fclose($pipes[2]);
	proc_close($process);
}
