<?php
defined("ABSPATH") or exit;
?>

<div>
    <?php if (!empty($pagination_data) && $pagination_data['show_pagination']) { ?>
        <nav>
            <ul class="pagination">
                <?php if (!empty($pagination_data['previous_page'])) { ?>
                    <li>
                        <a href="<?php echo esc_url($pagination_data['previous_page']['link']) ?>">
                            <i class="rwp rwp-back-arrow"></i>
                        </a>
                    </li>
                <?php } ?>


                <?php foreach ($pagination_data['pages'] as $page) { ?>
                    <li class="<?php echo $pagination_data['current_page'] == $page['index'] ? 'active' : '' ?> ">
                        <a href="<?php echo esc_url($page['link']); ?>">
                            <?php echo esc_html($page['index']); ?>
                        </a>
                    </li>
                <?php } ?>
                <?php if (!empty($pagination_data['next_page'])) { ?>
                    <li>
                        <a href="<?php echo esc_url($pagination_data['next_page']['link']) ?>">
                            <i class="rwp rwp-forward-arrow"></i>
                        </a>
                    </li>
                <?php } ?>
            </ul>
        </nav>
    <?php } ?>
</div>
