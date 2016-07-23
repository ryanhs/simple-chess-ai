<?php
ini_set('max_execution_time', 0);
require 'vendor/autoload.php';
use Ryanhs\Chess\Chess;


// some config here
$engineCommand = 'stockfish';
$depth = 2;
$fen = (new Chess())->fen();
echo $fen;exit;


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
fwrite($pipes[0], "ucinewgame\n");
fwrite($pipes[0], "isready\n");
fwrite($pipes[0], "position fen $fen\n");
fwrite($pipes[0], "go depth $depth\n");

// parse move, code referenced from: https://github.com/antiproton/Web-GUI-for-stockfish-chess
$str="";
while(true){
	usleep(100);
	$s = fgets($pipes[1],4096);
	echo $s;
	
	if(strpos(' '.$s,'bestmove')){
		break;
	}
}

// end process
fclose($pipes[0]);
fclose($pipes[1]);
if(isset($pipes[2])) fclose($pipes[2]);
proc_close($process);
