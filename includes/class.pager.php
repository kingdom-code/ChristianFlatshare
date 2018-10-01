<?php

/**
 * Longhurstsolutions.com PAGER class 
 *
 * Creates the "pageSplitter" interface
 *
*/

class Pager {
	
	// Total number of items to show. Usually calculated from a "select count(*).." query
	var $items_total;		
	
	// Number of items to show per page. Usually 10, 25, 50 or 100.
	var $items_per_page;	
	
	// The offset used in the select query.
	// E.g. "... limit ".$start.",".$items_per_page
	var $start;
	
	// Contains the url to be appended to the links, excluding the $start parameter
	var $url;	
	
	// Split level. How many pages to show on the left and right of the current one
	var $s = 4;
	
	// Contructor
	function Pager($items_total,$start,$items_per_page,$url) {
		
		$this->items_total = $items_total;
		$this->items_per_page = $items_per_page;
		$this->url = $url;
		$this->start = $start;
		
	}
	
	// Returns a series of paging links:
	// e.g. First | Previous | 1 2 3 4 5 6 | Next | Last
	function createLinks() {
		
		if ($this->items_total > $this->items_per_page) { // Only create pageSplitter if we have more than one page
		
			$pmax = ceil($this->items_total / $this->items_per_page); // E.g. $items_per_page = 10, and total results = 64. Number of pages = 64/10 = 6.7, ceil(6.4) = 7.
			$p = (ceil(($this->start+0.1) / $this->items_per_page))? ceil(($this->start+0.1) / $this->items_per_page) : 1; // For $start = 0, current page ($p) is 1 but for $start = (e.g.) 30 (with 25 items per page), current page is ceil(30/ 25), ceil(1.2) = 2
			$sep = '&nbsp;&nbsp;|&nbsp;&nbsp;';
			$pageSplitter  = '<p class="m0">';
			
			// First page link
			if ($p == 1) {
				// Grey out "First page link" if we're currently on the first page
				$pageSplitter .= '<span class="grey">First</span>';
			} else {
				$pageSplitter .= '<a href="'.$this->url.'&start=0">First</a>';
			}
			$pageSplitter .= $sep;
			
			// Previous page link
			if ($p == 1) {
				$pageSplitter .= '<span class="grey">Previous</span>';
			} else {
				$pageSplitter .= '<a href="'.$this->url.'&start='.($this->start - $this->items_per_page).'">Previous</a>';
			}
			$pageSplitter .= $sep;
			
			// Links to the various pages	
			$splitStart = (($p-$this->s)>0)? ($p-$this->s):1;
			$splitEnd = (($p+$this->s)<=$pmax)? ($p+$this->s):$pmax;
			if ($splitStart != 1) { $pageSplitter .= '...'; }
			for($i=$splitStart;$i<=$splitEnd;$i++) {
				if ($i == $p) { $pageSplitter .= '<strong>'; }
				$pageSplitter .= '&nbsp;<a href="'.$this->url.'&start='.($i*$this->items_per_page-$this->items_per_page).'">'.$i.'</a>';
				if ($i == $p) { $pageSplitter .= '</strong>'; }
			}
			if ($splitEnd != $pmax) { $pageSplitter .= '&nbsp;...'; }
			$pageSplitter .= $sep;
			
			// Next page link
			if ($p == $pmax) {
				$pageSplitter .= '<span class="grey">Next</span>';
			} else {
				$pageSplitter .= '<a href="'.$this->url.'&start='.($this->start+$this->items_per_page).'">Next</a>';
			}
			$pageSplitter .= $sep;
			
			// Last page link
			if ($p == $pmax) {
				$pageSplitter .= '<span class="grey">Last</span>';
			} else {
				$pageSplitter .= '<a href="'.$this->url.'&start='.($pmax*$this->items_per_page-$this->items_per_page).'">Last</a>';
			}		
			$pageSplitter .= '</p>'."\n";
		
		} else {
		
			$pageSplitter = NULL;
			
		}
		
		return $pageSplitter;
		
	}
	
	// Returns the number of the first item that is currently displayed
	function getFirstItem() {
		if ($this->items_total > 0) {
			return $this->start + 1;
		} else {
			return 0;
		}
	}
	
	// Returns the number of the last item that is currently displayed
	function getLastItem() {
		$lastItem = $this->start + $this->items_per_page;
		if ($this->items_total < $lastItem) {
			$lastItem = $this->items_total;
		}
		return $lastItem;
	}

}

?>