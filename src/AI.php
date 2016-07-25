<?php

namespace Ryanhs\SimpleChessAI;
class AI extends \Ryanhs\Chess\Chess {
	
	public $nodes;
	private $nodesHistory;
	
	public function run($depth)
	{
		if ($depth == 0) {					
			// get long algebraic move
			$this->move($this->moves()[0]);
			$algebraicMove = $this->undo();
			echo 'bestmove '.$algebraicMove['from'].$algebraicMove['to'].PHP_EOL;
			return;
		}
		
		
		$this->nodesHistory = [];
		$this->nodes = 0;
		$this->lastTimestamp = time();
		$bestMove = $this->alphabetaMax(
						['move' => null, 'evalScore' => -1000000], 
						['move' => null, 'evalScore' => 1000000], 
						$depth
					);
		$this->nodes = array_sum($this->nodesHistory);
		
		// get long algebraic move
		$this->makeMove($bestMove['move']);
		$algebraicMove = $this->undo();
		echo 'bestmove '.$algebraicMove['from'].$algebraicMove['to'].PHP_EOL;
	}
	
	private function debug() {
		if (time() > $this->lastTimestamp) {
			$s = time() - $this->lastTimestamp;
			$nodes = ceil($this->nodes / $s);
			$totalNodes = array_sum($this->nodesHistory) + $this->nodes;
			echo "info string ".$nodes." nodes/s"." from ".$totalNodes." nodes".PHP_EOL;
			$this->nodesHistory[] = $this->nodes;
			$this->nodes = 0;
			$this->lastTimestamp = time();
		}
		
		$this->nodes++;
	}
	
	// pseudocode from: https://chessprogramming.wikispaces.com/Alpha-Beta
	// Max versus Min
	public function alphabetaMax($alpha, $beta, $depth)
	{
		$this->debug();
		if ($depth == 0 || $this->gameOver()) {
			return ['evalScore' => $this->evaluate()];
		}
		
		$moves = $this->generateMoves();
		foreach ($moves as $move) {
			$this->makeMove($move);
			$score = $this->alphabetaMin($alpha, $beta, $depth - 1);
					 $this->undo();
					 
			if ($score['evalScore'] >= $beta['evalScore']){
				return $beta;
			}
			if ($score['evalScore'] > $alpha['evalScore']){
				$alpha = ['move' => $move, 'evalScore' => $score['evalScore']];
			}
		}
		return $alpha;
	}
	
	// pseudocode from: https://chessprogramming.wikispaces.com/Alpha-Beta
	// Max versus Min
	public function alphabetaMin($alpha, $beta, $depth)
	{
		$this->debug();
		if ($depth == 0 || $this->gameOver()) {
			return ['evalScore' => -$this->evaluate()];
		}
		
		$moves = $this->generateMoves();
		foreach ($moves as $move) {
			$this->makeMove($move);
			$score = $this->alphabetaMax($alpha, $beta, $depth - 1);
					 $this->undo();
					 
			if ($score['evalScore'] <= $alpha['evalScore']){
				return $alpha;
			}
			if ($score['evalScore'] < $beta['evalScore']){
				$beta = ['move' => $move, 'evalScore' => $score['evalScore']];
			}
		}
		return $beta;
	}
	
	public function evaluate()
	{
		$them = $this->turn();
		$us = self::swap_color($them);
		
		// counter
		$counter = [
			'kw' => 0, 	'kb' => 0,
			'qw' => 0, 	'qb' => 0,
			'rw' => 0, 	'rb' => 0,
			'bw' => 0, 	'bb' => 0,
			'nw' => 0, 	'nb' => 0,
			'pw' => 0, 	'pb' => 0,
		];
		foreach ($this->board as $coor => $square) {
			if ($square == null)
				continue;
			
			if (is_array($square)) {				
				$counter[$square['type'].$square['color']]++;
			}
		}
		
		$isolated = ['w' => 0, 'b' => 0];
		$stacked = ['w' => 0, 'b' => 0];
		$doubled = ['w' => 0, 'b' => 0];
		$x88 = self::SQUARES;
		$x88_flip = array_flip(self::SQUARES);
		
		foreach ($this->board as $coor => $square) {
			if ($square == null)
				continue;
			
			if (is_array($square)) {
				// calculate isolated
				$this->turn = $square['color'];
				$tmp = count($this->generateMoves(['legal' => false, 'square' => $coor]));
				if ($tmp == 0) $isolated[$square['color']]++;
				$this->turn = $them;
				
				// calculate doubled/stack
				if ($square['type'] == 'p') {
					if ($coor - 16 > 0) {
						$tmp = $this->board[$coor - 16];
						if ($tmp['type'] == 'p') {
							if ($tmp['color'] == $square['color']) {
								$doubled[$tmp['color']]++;
							} else {
								$stacked[$tmp['color']]++;
							}
						}
					}
					if ($coor + 16 > 0) {
						$tmp = $this->board[$coor + 16];
						if ($tmp['type'] == 'p') {
							if ($tmp['color'] == $square['color']) {
								$doubled[$tmp['color']]++;
							} else {
								$stacked[$tmp['color']]++;
							}
						}
					}
				}
			}
		}
		
		
		// mobility
		$mobility = ['w' => 0, 'b' => 0];
		$this->turn = $us;
		$mobility[$us] = count($this->generateMoves([ 'legal' => true ]));
		$this->turn = $them;
		$mobility[$them] = count($this->generateMoves([ 'legal' => true ]));
		
		
		
		$result = 20000	* ($counter['k'.$us] - $counter['k'.$them])
				+ 900	* ($counter['q'.$us] - $counter['q'.$them])
				+ 500	* ($counter['r'.$us] - $counter['r'.$them])
				+ 330	* ($counter['b'.$us] - $counter['b'.$them])
				+ 320	* ($counter['n'.$us] - $counter['n'.$them])
				+ 100	* ($counter['p'.$us] - $counter['p'.$them])
				- 50	* ($doubled[$us] - $doubled[$them] + $stacked[$us] - $stacked[$them] + $isolated[$us] - $isolated[$them])
				+ 10	* ($mobility[$us] - $mobility[$them])
		;
		
		return $result;
	}
}
