<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="2.0" 
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
	xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:spatial="http://geovocab.org/spatial#"
	xmlns:owl="http://www.w3.org/2002/07/owl#">
	
<xsl:output method="text" indent="yes" />
<xsl:template match="/rdf:RDF">

	BEGIN;
	<xsl:apply-templates />
	COMMIT;

</xsl:template>

<xsl:template match="/rdf:RDF/rdf:Description[spatial:EQ]">

	INSERT INTO spatial_eq (gadm_level, gadm_id, uri) VALUES
	<xsl:for-each select='spatial:EQ' >
		<xsl:variable name="uri" select="replace(., '^.*/', '')" />
		 (<xsl:value-of select="substring-before($uri, '_')" />,<xsl:value-of select="substring-after($uri, '_')" />,'<xsl:value-of select="parent::node()/@rdf:about" />')
		<xsl:if test="position() != last()">,</xsl:if>
	</xsl:for-each>
	;

</xsl:template>

</xsl:stylesheet>