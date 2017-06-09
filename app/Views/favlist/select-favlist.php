<?php if(!empty($lists) || !empty($create_list)): ?><section class="simplefavorite-favlist">
    <header class="simplefavorite-favlist__header">
        <h5 class="simplefavorite-favlist__title">
            <?php echo esc_attr(__('Manage your favlists', 'simplefavorites')); ?>
        </h5>
    </header>
    <div class="simplefavorite-favlist__content">
        <ul class="simplefavorite-favlist__list">
            <?php if(is_array($lists)): ?><?php foreach($lists as $list): ?><li class="simplefavorite-favlist__item">
                <input
                    class="simplefavorite-favlist__titleinput"
                    type="text"
                    name="favlist[<?php echo (int) $list->getId(); ?>][title]"
                    value="<?php echo addslashes(esc_attr($list->getTitle())); ?>"
                    data-value="<?php echo addslashes(esc_attr($list->getTitle())); ?>"
                    placholder="<?php echo addslashes(esc_attr(__('Insert favlist title', 'simplefavorites'))); ?>"
                    readonly="readonly"
                    data-listid="<?php echo $list->getId(); ?>"
                    data-listtitle />

                <a href="#"
                    class="simplefavorite-favlist__button is--edit"
                    data-siteid="<?php echo $list->getSiteId(); ?>"
                    data-listid="<?php echo $list->getId(); ?>"
                    data-postid="<?php echo $post_id; ?>"
                    data-favlistaction="edit">
                    <span class="simplefavorite-favlist__editbuttontext is--edit"><?php echo __('Edit', 'simplefavorites' ); ?></span>
                    <span class="simplefavorite-favlist__editbuttontext is--save"><?php echo __('Save', 'simplefavorites' ); ?></span>
                </a>

                <a href="#"
                    class="simplefavorite-favlist__button is--delete"
                    data-siteid="<?php echo $list->getSiteId(); ?>"
                    data-listid="<?php echo $list->getId(); ?>"
                    data-postid="<?php echo $post_id; ?>"
                    data-favlistaction="delete"><?php echo __('Delete List', 'simplefavorites' ); ?>

                </a>

                <a href="#"
                    class="simplefavorite-favlist__button is--<?php echo $list->getStatus() == 'publish' ? 'unpublish' : 'publish'; ?>"
                    data-siteid="<?php echo $list->getSiteId(); ?>"
                    data-listid="<?php echo $list->getId(); ?>"
                    data-postid="<?php echo $post_id; ?>"
                    data-favlistaction="<?php echo $list->getStatus() == 'publish' ? 'unpublish' : 'publish'; ?>">
                    <?php echo __($list->getStatus() == 'publish' ? 'Unpublish list' : 'Publish list', 'simplefavorites' ); ?>

                </a>

                <a href="<?php echo get_permalink($list->getId()); ?>" class="simplefavorite-favlist__button is--link"><?php echo __('View', 'simplefavorites' ); ?></a>

                <a href="#"
                    class="simplefavorite-favlist__button is--<?php echo $list->hasPost($post_id) ? 'remove' : 'add'; ?>"
                    data-siteid="<?php echo $list->getSiteId(); ?>"
                    data-listid="<?php echo $list->getId(); ?>"
                    data-postid="<?php echo $post_id; ?>"
                    data-favlistaction="<?php echo $list->hasPost($post_id) ? 'remove' : 'add'; ?>">
                    <?php echo __($list->hasPost($post_id) ? 'Remove from list' : 'Add to list', 'simplefavorites' ); ?>
                </a>
            </li><?php endforeach; ?><?php endif; ?>
            <?php if(!empty($create_list)): ?><li class="simplefavorite-favlist__item is--new">
                <input
                    class="simplefavorite-favlist__titleinput"
                    type="text"
                    name="favlist[0][title]"
                    value=""
                    data-value=""
                    placeholder="<?php echo addslashes(esc_attr(__('Create a new list', 'simplefavorites')) . (!empty($post_id) ? ' ' . __('(the post will be added)', 'simplefavorites') : '') ); ?>"
                    data-listid="0"
                    data-listtitle />

                <a href="#"
                    class="simplefavorite-favlist__button is--create"
                    data-siteid="<?php echo $list->getSiteId(); ?>"
                    data-listid="0"
                    data-postid="<?php echo $post_id; ?>"
                    data-favlistaction="create"><?php echo __('Create', 'simplefavorites' ); ?>

                </a>
            </li>

        </ul><?php endif; ?>
    </div><!-- .simplefavorite-favlist__content //-->
</section><?php endif; ?>
