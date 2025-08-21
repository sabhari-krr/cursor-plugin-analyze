<?php
defined('ABSPATH') or die();

// Ensure we have the required variables
if (!isset($pagination)) {
    $pagination = [];
}
if (!isset($search_term)) {
    $search_term = '';
}
?>

<?php if ($pagination['total_pages'] > 1): ?>
    <div class="wlpr-pagination-controls">
        <?php if ($pagination['has_prev']): ?>
            <?php
            $prev_args = [
                'paged' => $pagination['prev_page'],
                'per_page' => $pagination['per_page']
            ];
            
            // Include search term if it exists
            if (!empty($search_term)) {
                $prev_args['search'] = $search_term;
            }
            ?>
            <a href="<?php echo add_query_arg($prev_args); ?>"
                class="wlpr-pagination-prev">
                <?php _e('Prev', 'wployalty-point-email-reminder'); ?>
            </a>
        <?php endif; ?>

        <div class="wlpr-pagination-numbers">
            <?php
            $current_page = $pagination['current_page'];
            $total_pages = $pagination['total_pages'];
            $per_page = $pagination['per_page'];

            // Calculate range to show
            $range = 2; // Show 2 pages before and after current page
            $start_page = max(1, $current_page - $range);
            $end_page = min($total_pages, $current_page + $range);

            // Always show first page if not in range
            if ($start_page > 1): 
                $first_page_args = ['paged' => 1, 'per_page' => $per_page];
                if (!empty($search_term)) {
                    $first_page_args['search'] = $search_term;
                }
            ?>
                <a href="<?php echo add_query_arg($first_page_args); ?>"
                    class="wlpr-pagination-number">1</a>
                <?php if ($start_page > 2): ?>
                    <span class="wlpr-pagination-dots">...</span>
                <?php endif; ?>
            <?php endif; ?>

            <?php
            // Show page numbers in range
            for ($i = $start_page; $i <= $end_page; $i++): 
                $page_args = ['paged' => $i, 'per_page' => $per_page];
                if (!empty($search_term)) {
                    $page_args['search'] = $search_term;
                }
            ?>
                <a href="<?php echo add_query_arg($page_args); ?>"
                    class="wlpr-pagination-number <?php echo ($i == $current_page) ? 'wlpr-pagination-current' : ''; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>

            <?php
            // Always show last page if not in range
            if ($end_page < $total_pages): ?>
                <?php if ($end_page < $total_pages - 1): ?>
                    <span class="wlpr-pagination-dots">...</span>
                <?php endif; ?>
                <?php
                $last_page_args = ['paged' => $total_pages, 'per_page' => $per_page];
                if (!empty($search_term)) {
                    $last_page_args['search'] = $search_term;
                }
                ?>
                <a href="<?php echo add_query_arg($last_page_args); ?>"
                    class="wlpr-pagination-number">
                    <?php echo $total_pages; ?>
                </a>
            <?php endif; ?>
        </div>

        <?php if ($pagination['has_next']): ?>
            <?php
            $next_args = [
                'paged' => $pagination['next_page'],
                'per_page' => $pagination['per_page']
            ];
            
            // Include search term if it exists
            if (!empty($search_term)) {
                $next_args['search'] = $search_term;
            }
            ?>
            <a href="<?php echo add_query_arg($next_args); ?>"
                class="wlpr-pagination-next">
                <?php _e('Next', 'wployalty-point-email-reminder'); ?>
            </a>
        <?php endif; ?>
    </div>
<?php endif; ?> 