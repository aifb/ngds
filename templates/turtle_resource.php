<?php $uri = ($property['subject'] == "#")? ":".substr($property['subject'],1) : "<".$property['subject'].">"; ?>
<?php if ($property['datatype'] == 'bnode'): ?>
    <?php echo $uri ?> <?php echo $property['predicate'] ?> [
    <?php foreach ($property['object'] as $bnode_property): ?>
      <?php if ($bnode_property['datatype'] == 'anyURI'): ?>
	<?php echo $bnode_property['predicate'] ?> <<?php echo $bnode_property['object'] ?>> ;
      <?php elseif ($bnode_property['datatype'] == 'decimal'): ?>
	<?php echo $bnode_property['predicate'] ?> <?php echo $bnode_property['object'] ?> ;
      <?php else: ?>
      <?php if(isset($bnode_property['lang'])): ?>
	<?php echo $bnode_property['predicate'] ?> "<?php echo $bnode_property['object'] ?>"@<?php echo $bnode_property['lang'] ?> ;
      <?php else: ?>
      <?php if(isset($property['datatype']) && $bnode_property['datatype'] != "XMLLiteral"): ?>
	<?php echo $bnode_property['predicate'] ?> "<?php echo $bnode_property['object'] ?>"^^<<?php echo $bnode_property['datatype'] ?>> ;
      <?php else: ?>
	<?php echo $bnode_property['predicate'] ?> "<?php echo $bnode_property['object'] ?>" ;
      <?php endif; ?>
      <?php endif; ?>
      <?php endif; ?>
    <?php endforeach; ?>
    ] .
<?php elseif ($property['datatype'] == 'anyURI'): ?>
<?php echo $uri ?> <?php echo $property['predicate'] ?> <<?php echo $property['object'] ?>> .
<?php elseif ($property['datatype'] == 'decimal'): ?>
<?php echo $uri ?> <?php echo $property['predicate'] ?> <?php echo $property['object'] ?> .
<?php else: ?>
<?php if(isset($property['lang'])): ?>
<?php echo $uri ?> <?php echo $property['predicate'] ?> "<?php echo $property['object'] ?>"@<?php echo $property['lang'] ?> .
<?php else: ?>
<?php if(isset($property['datatype']) && $property['datatype'] != "XMLLiteral"): ?>
<?php echo $uri ?> <?php echo $property['predicate'] ?> "<?php echo $property['object'] ?>"^^<<?php echo $property['datatype'] ?>> .
<?php else: ?>
<?php echo $uri ?> <?php echo $property['predicate'] ?> "<?php echo $property['object'] ?>" .
<?php endif; ?>
<?php endif; ?>
<?php endif; ?>