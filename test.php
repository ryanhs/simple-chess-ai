<?php
ini_set('max_execution_time', 0);
ini_set('memory_limit', '-1');
require 'vendor/autoload.php';

use Ryanhs\SimpleChessAI\AI;



// defaulting depth
if (count($argv) >= 2) {
    $_GET['depth'] = $argv[1];
}
$depth = !empty($_GET['depth']) ? $_GET['depth'] : 2;


$ai = new AI('7k/8/8/8/8/8/7n/6KQ w - - 0 1');
$ai->reset();
$time_start = microtime(true);
	
	$ai->run($depth);
	
$time_end = microtime(true);

// output result
$execution_time = number_format(($time_end - $time_start), 2);
echo PHP_EOL.str_repeat("=", 25).PHP_EOL;
echo "Depth\t\t: ".$depth.PHP_EOL;
echo "Searched nodes\t: ".$ai->nodes.PHP_EOL;
echo "Execution time\t: ".$execution_time."s".PHP_EOL;
