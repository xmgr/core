<?php
    
    namespace Xmgr\Console;
    
    use Xmgr\Console;
    
    /**
     * Class ProgressBar
     *
     * Represents a progress bar that displays the progress of an operation.
     */
    class ProgressBar {
        
        protected static int $count = 2;
        protected int        $position;
        
        protected int $total = 1;
        protected int $done  = 0;
        protected int $size  = 30;
        protected int $start = 0;
        
        /**
         * Class constructor.
         *
         * @param int $total The total number of items to be processed. Default is 1.
         * @param int $done  The number of items already processed. Default is 0.
         *
         * @return void
         */
        public function __construct(int $total = 1, int $done = 0) {
            $this->position = static::$count;
            static::$count  += 2;
            $this->setTotal($total);
            $this->update($done);
            
            $cols = Console::cols();
            if ($cols >= 20) {
                $this->size = (int)(Console::cols() / 100 * 75);
            }
            $this->start = microtime(true);
        }
        
        /**
         * Update the progress of a task.
         *
         * @param int $done The progress value to set. Must be a positive integer.
         *                  If the value is less than 1, it will be set to 1.
         *                  If the value is greater than the total, it will be set to the total.
         *
         * @return void
         */
        public function update(int $done): void {
            $done       = minmax(abs($done), 1, $this->total);
            $this->done = $done;
        }
        
        /**
         * Set the total value.
         *
         * @param int $total The total value to be set.
         *
         * @return void
         */
        protected function setTotal(int $total): void {
            $this->total = max(abs($total), 1);
        }
        
        /**
         * Increment the value of 'done' if it is less than 'total'.
         *
         * @param int $value The value to increment 'done' by.
         *
         * @return void
         */
        public function increment(int $value): void {
            if ($this->done < $this->total) {
                $this->done += $value;
                $this->done = min($this->done, $this->total);
            }
        }
        
        /**
         * Zeigt den Fortschritt einer Operation an.
         * (Garstig geklaut von Stackoverflow lul)
         *
         * @return void
         */
        public function show() {
            echo "\033[s";
            echo "\033[" . $this->position . ';H';
            if (!$this->total) {
                return;
            }
            
            // if we go over our bound, just ignore it
            if ($this->done > $this->total) return;
            
            $now = microtime(true);
            
            $perc = (double)($this->done / $this->total);
            
            $bar = floor($perc * $this->size);
            
            $status_bar = "\r[";
            $status_bar .= str_repeat('=', $bar);
            if ($bar < $this->size) {
                $status_bar .= '>';
                $status_bar .= str_repeat(' ', $this->size - $bar);
            } else {
                $status_bar .= '=';
            }
            
            $disp = number_format($perc * 100, 0);
            
            $status_bar .= "] $disp%  $this->done/$this->total";
            
            $rate = ($now - $this->start) / $this->done;
            $left = $this->total - $this->done;
            $eta  = round($rate * $left, 2);
            
            $elapsed = $now - $this->start;
            
            $status_bar .= ' remaining: ' . number_format($eta, 2) . ' sec.  elapsed: ' . number_format($elapsed, 2) . ' sec.';
            
            echo "$status_bar  ";
            
            
            // when done, send a newline
            if ($this->done >= $this->total) {
                echo "\n";
            }
            echo "\033[u";
            flush();
        }
        
        /**
         * Calculate the remaining value.
         *
         * This method calculates the remaining value by subtracting the
         * current done value from the total value.
         *
         * @return int The calculated remaining value.
         */
        public function remaining() {
            return $this->total - $this->done;
        }
        
        /**
         * Get the value of the 'done' property.
         *
         * @return mixed The value of the 'done' property.
         */
        public function done() {
            return $this->done;
        }
        
        /**
         * Check if the task is finished.
         *
         * @return bool Returns true if the task is finished, false otherwise.
         */
        public function finished() {
            return $this->done >= $this->total;
        }
        
        /**
         * Get the total value.
         *
         * @return mixed The total value.
         */
        public function total() {
            return $this->total;
        }
        
    }
