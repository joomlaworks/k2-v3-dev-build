<?php
/**
 * @version		3.0.0
 * @package		K2
 * @author		JoomlaWorks http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2014 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die ;

?>

<div id="k2ModuleBox<?php echo $module->id; ?>" class="k2UserBlock<?php if($params->get('moduleclass_sfx')) echo ' '.$params->get('moduleclass_sfx'); ?>">

	<?php if($params->get('userGreetingText')): ?>
	<p class="ubGreeting"><?php echo $params->get('userGreetingText'); ?></p>
  	<?php endif; ?>

	<div class="k2UserBlockDetails">
	  <?php if($params->get('userAvatar') && $user->image): ?>
	  <a class="k2Avatar ubAvatar" href="<?php echo $user->link; ?>" title="<?php echo JText::_('K2_MY_PAGE'); ?>">
	  	<img src="<?php echo $user->image->src; ?>" alt="<?php echo htmlspecialchars($user->name); ?>" style="width:<?php echo $avatarWidth; ?>px;height:auto;" />
	  </a>
	  <?php endif; ?>
	  <span class="ubName"><?php echo $user->name; ?></span>
		<span class="ubCommentsCount"><?php echo JText::_('K2_YOU_HAVE'); ?> <b><?php echo $user->numOfComments; ?></b> <?php if($user->numOfComments == 1) echo JText::_('K2_PUBLISHED_COMMENT'); else echo JText::_('K2_PUBLISHED_COMMENTS'); ?></span>
	  <div class="clr"></div>
	</div>

  <ul class="k2UserBlockActions">
		<?php if($user->canAdd): ?>
		<li>
			<a class="k2Modal" href="<?php echo $user->addLink; ?>"><?php echo JText::_('K2_ADD_NEW_ITEM'); ?></a>
		</li>
		<?php endif; ?>
		<li>
			<a href="<?php echo $user->link; ?>"><?php echo JText::_('K2_MY_PAGE'); ?></a>
		</li>
		<li>
			<a href="<?php echo $logout->profileLink; ?>"><?php echo JText::_('K2_MY_ACCOUNT'); ?></a>
		</li>
		<?php if($logout->K2CommentsEnabled): ?>
		<li>
			<a class="modal" rel="{handler:'iframe',size:{x:990,y:550}}" href="<?php echo JRoute::_('index.php?option=com_k2&view=admin&Itemid=#comments'); ?>"><?php echo JText::_('K2_MODERATE_COMMENTS_TO_MY_PUBLISHED_ITEMS'); ?></a>
		</li>
		<?php endif; ?>
	</ul>

	<ul class="k2UserBlockRenderedMenu">
		<?php $level = 1; foreach($logout->menu as $key => $link): $level++; ?>
		<li class="linkItemId<?php echo $link->id; ?>">
			<?php if($link->type=='url' && $link->browserNav==0): ?>
			<a href="<?php echo $link->route; ?>"><?php echo $link->name; ?></a>
			<?php elseif(strpos($link->link,'option=com_k2&view=item&layout=itemform') || $link->browserNav==2): ?>
			<a class="modal" rel="{handler:'iframe',size:{x:990,y:550}}" href="<?php echo $link->route; ?>"><?php echo $link->name; ?></a>
			<?php else: ?>
			<a href="<?php echo $link->route; ?>"<?php if($link->browserNav==1) echo ' target="_blank"'; ?>><?php echo $link->name; ?></a>
			<?php endif; ?>

			<?php if(isset($logout->menu[$key+1]) && $logout->menu[$key]->level < $logout->menu[$key+1]->level): ?>
			<ul>
			<?php endif; ?>

			<?php if(isset($logout->menu[$key+1]) && $logout->menu[$key]->level > $logout->menu[$key+1]->level): ?>
			<?php echo str_repeat('</li></ul>', $logout->menu[$key]->level - $logout->menu[$key+1]->level); ?>
			<?php endif; ?>

		<?php if(isset($logout->menu[$key+1]) && $logout->menu[$key]->level == $logout->menu[$key+1]->level): ?>
		</li>
		<?php endif; ?>
		<?php endforeach; ?>
  </ul>

  <form action="<?php echo JRoute::_('index.php', true, $params->get('usesecure')); ?>" method="post">
    <input type="submit" name="Submit" class="button ubLogout" value="<?php echo JText::_('K2_LOGOUT'); ?>" />
    <input type="hidden" name="option" value="<?php echo $logout->option; ?>" />
    <input type="hidden" name="task" value="<?php echo $logout->task; ?>" />
    <input type="hidden" name="return" value="<?php echo $logout->return; ?>" />
	<?php echo JHtml::_('form.token'); ?>
  </form>
</div>
