		<tr>
		<?php if ($property['datatype'] == 'bnode'): ?>
		  <th><?php echo $property['predicate'] ?></th>
		  <td>
		  <table>
		    <?php foreach ($property['object'] as $bnode_property): ?>
		      <?php print render("html_resource.php", array('property' => $bnode_property)); ?>
		    <?php endforeach; ?>
		  </table>
		  </td>
		<?php elseif ($property['datatype'] == 'anyURI'): ?>
		  <th><?php echo $property['predicate'] ?></th>
		  <td><a href="<?php echo $property['object'] ?>"><?php echo $property['object'] ?></a></td>
		<?php elseif (isset($property['lang'])): ?>
		  <th><?php echo $property['predicate'] ?>@<?php echo $property['lang'] ?></th>
		  <td><?php echo $property['object'] ?></td>
		<?php else: ?>
		  <th><?php echo $property['predicate'] ?></th>
		  <td><?php echo $property['object'] ?></td>
		<?php endif; ?>
		</tr>