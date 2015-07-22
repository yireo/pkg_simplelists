<?php
/**
 * Joomla! component SimpleLists
 *
 * @author    Yireo
 * @copyright Copyright 2015
 * @license   GNU Public License
 * @link      https://www.yireo.com/
 */

defined('_JEXEC') or die('Restricted access');

foreach ($displayData as $name => $value)
{
	$$name = $value;
}
?>
<?php if (!empty($items)): ?>
	<div id="simplelists-navigator">
		<ul>
			<?php foreach ($items as $item): ?>
				<?php if ($params->get('disable_jumplabels', 0) == 0) : ?>
					<li>
						<a href="<?php echo $url; ?>#item<?php echo $item->id; ?>" class="simplelist-hover"
						   id="simplelist-hover<?php echo $item->id; ?>">
							<?php echo $item->title; ?>
						</a>
					</li>
				<?php else: ?>
					<li>
						<a onclick="return false;" class="simplelist-hover" id="simplelist-hover<?php echo $item->id; ?>">
							<?php echo $item->title; ?>
						</a>
					</li>
				<?php endif; ?>
			<?php endforeach; ?>
		</ul>
	</div>
	
	<div class="<?php echo $page_class; ?>">
		<?php foreach ($items as $item) : ?>
			<div class="<?php echo $item->class; ?>" id="item<?php echo $item->id; ?>"
				 style="<?php echo $item->style; ?>">

				<a name="<?php echo $item->href; ?>"></a>

				<?php if ($item->title): ?>
					<h3 class="contentheading"><?php echo $item->title; ?></h3>
				<?php endif; ?>

				<?php if ($item->picture): ?>
					<?php echo $item->picture; ?>
				<?php endif; ?>

				<?php if ($item->text): ?>
					<?php echo $item->text; ?>
				<?php endif; ?>

				<?php if ($item->readmore): ?>
					<br/><?php echo $item->readmore; ?>
				<?php endif; ?>

			</div>
		<?php endforeach; ?>
	</div>
<?php else: ?>
	<?php echo $empty_list; ?>
<?php endif; ?>