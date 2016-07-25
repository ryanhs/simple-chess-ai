<?php

namespace Ryanhs\SimpleChessAI;
class AI extends \Ryanhs\Chess\Chess {
	
	public function run($depth)
	{
		$nodes = $this->generateNodes($depth);
		$bestMove = self::alphaBeta($nodes, '-,-,-1000000', '-,-,1000000', true);
		$sanMove = explode(',', $bestMove)[0];
		
		// get long algebraic move
		$this->move($sanMove);
		$algebraicMove = $this->undo();
		echo 'bestmove '.$algebraicMove['from'].$algebraicMove['to'].PHP_EOL;
	}
	
	public static function alphaBeta($node, $alpha, $beta, $maximisingPlayer)
	{
		$bestMove = null;
		if (!is_array($node)) {
			$bestMove = $node;
		}
		else if ($maximisingPlayer) {
			$bestMove = $alpha;
			
			// Recurse for all children of node.
			foreach ($node as $k => $v) {
				$childValue = self::alphaBeta($v, $bestMove, $beta, false);
				$tmpBestMove = max(explode(',',$bestMove)[2], explode(',',$k)[2]);
				$bestMove = explode(',',$bestMove)[2] == $tmpBestMove ? $bestMove : $k;
				
				if (explode(',',$beta)[2] <= explode(',',$bestMove)[2]) {
					break;
				}
			}
		}
		else {
			$bestMove = $beta;
			
			// Recurse for all children of node.
			foreach ($node as $k => $v) {
				$childValue = self::alphaBeta($v, $alpha, $bestMove, true);
				$tmpBestMove = min(explode(',',$bestMove)[2], explode(',',$k)[2]);
				$bestMove = explode(',',$bestMove)[2] == $tmpBestMove ? $bestMove : $k;
				
				if (explode(',',$bestMove)[2] <= explode(',',$alpha)[2]) {
					break;
				}
			}
		}
		return $bestMove;
	}
	
	public function generateNodes($depth)
	{
		$result = [];
		$nodes = 0;
		
		$moves = $this->moves([ 'legal' => true ]);
		$color = $this->turn();
		for ($i = 0, $len = count($moves); $i < $len; $i++) {
			$this->move($moves[$i]);
			
			if (!$this->kingAttacked($color)) {
				
				$calculatedMove = $moves[$i].','.$this->fen().','.$this->evaluate();
				
				if ($depth - 1 > 0) {
					$result[$calculatedMove] = $this->generateNodes($depth - 1);
				} else {
					$result[$calculatedMove] = $calculatedMove;
				}
			}
			$this->undoMove();
		}
		
		return $result;
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
