# Simple Chess AI

### this project only for fun!!

this project show how simple chess AI can be implemented in PHP,  
yeah it doesn't created to be strong AI ofcourse, due to poor performance of PHP compared to C, when this project created.  

### Algorithm
- using simple alpha-beta pruning

### Evaluation
- using simplified [https://chessprogramming.wikispaces.com/Simplified+evaluation+function]

## UCI  
this project use UCI format, so you can do a bit using UCI command

## Example to run
run this by ``php simple-chess-ai/uci.php``  
  
after the program run, you can type following command:  
``ucinewgame``  
``isready``  
``position fen $fen`` exp: ``position fen rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1``  
``go depth $depth`` exp: ``go depth 2``  

## Example output
```text
SimpleChessAI in PHP by Ryan H. Silalahi
uciok
readyok
bestmove e2e4
```
