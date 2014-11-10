  <?php if ($property['datatype'] == 'bnode'): ?>
    <<?php echo $property['predicate'] ?> rdf:parseType="Resource">
    <?php foreach ($property['object'] as $bnode_property): ?>
      <?php print render("rdf-xml_resource.php", array('property' => $bnode_property)); ?>
    <?php endforeach; ?>
    </<?php echo $property['predicate'] ?>>
  <?php elseif ($property['datatype'] == 'anyURI'): ?>
    <<?php echo $property['predicate'] ?> rdf:resource="<?php echo $property['object'] ?>" />
  <?php elseif ($property['datatype'] == 'decimal'): ?>
    <<?php echo $property['predicate'] ?> rdf:datatype="http://www.w3.org/2001/XMLSchema#decimal"><?php echo htmlspecialchars($property['object']) ?></<?php echo $property['predicate'] ?>>
  <?php else: ?>
    <?php if(isset($property['lang'])): ?>
    <<?php echo $property['predicate'] ?> xml:lang="<?php echo $property['lang'] ?>"><?php echo htmlspecialchars($property['object']) ?></<?php echo $property['predicate'] ?>>
    <?php else: ?>
    
    <?php if(isset($property['datatype']) && $property['datatype'] != "XMLLiteral"): ?>
    <<?php echo $property['predicate'] ?> rdf:datatype="<?php echo $property['datatype'] ?>"><?php echo htmlspecialchars($property['object']) ?></<?php echo $property['predicate'] ?>>
    <?php else: ?>
    <<?php echo $property['predicate'] ?>><?php echo htmlspecialchars($property['object']) ?></<?php echo $property['predicate'] ?>>
    <?php endif; ?>
    <?php endif; ?>
  <?php endif; ?>