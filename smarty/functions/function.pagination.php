<?php

function smarty_function_pagination($params, &$smarty) {
	if (empty($params['current'])) {
		$smarty->trigger_error('assign: missing \'current\' parameter');
	}
	else if (empty($params['total'])) {
		$smarty->trigger_error('assign: missing \'total\' parameter');
	}
	else if (empty($params['section'])) {
		$smarty->trigger_error('assign: missing \'section\' parameter');
	}
	else {
		$current =& $params['current'];
		$total   =& $params['total'];
		$section =& $params['section'];

		$pagination = null;

		// &laquo  = double
		// &lsaquo = single

		$first = '&laquo; First'; 
		$last  = 'Last &raquo;';

		$prev = '&laquo; Previous';
		$next = 'Next &raquo;';

		/*
		$pagination .= ' <span class="pagination">';
		$pagination .= $current != $total ? '<a href="/blog/first">' . $first . '</a>' : $first;
		$pagination .= '</span>';
		*/

		$pagination .= $current != 1 ? '<a href="/' . $section . '/page/' . ($current - 1) . '">' . $prev . '</a>' : '<span class="prev">' . $prev . '</span>';

		for ($i = 1; $i <= $total; $i++) {
			$pagination .= $i != $current ? '<a href="/' . $section . '/page/' . $i . '">' . $i . '</a>' : '<span class="current">' . $i . '</span>';
		}

		$pagination .= $current != $total ? '<a href="/' . $section . '/page/' . ($current + 1) . '">' . $next . '</a>' : '<span class="next">' . $next . '</span>';

		/*
		$pagination .= ' <span class="pagination">';
		$pagination .= $current != 1 ? '<a href="/blog/last">' . $last . '</a>' : $last;
		$pagination .= '</span> ';
		*/

		return '<div id="pagination">' . $pagination . '</pagination>';
	}
}

?>
