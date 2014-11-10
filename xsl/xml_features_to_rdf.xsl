<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="2.0" 
	xmlns="http://gadm.geovocab.org/ontology#"
	xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
	xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
	xmlns:gadm="http://gadm.geovocab.org/id/"
	xmlns:gadmont="http://gadm.geovocab.org/ontology#">
	
<xsl:output method="xml" indent="yes" />

<xsl:template match="/result">
<rdf:RDF>
  <xsl:apply-templates/>
</rdf:RDF>


</xsl:template>

<xsl:template match="row">

  <rdf:Description rdf:about="{concat('http://gadm.geovocab.org/id/',gadm_level,'_',gadm_id)}">
  <xsl:for-each select='*' >
    <xsl:if test="@xsi:nil|@xsi:nil!='true'">
      <xsl:element name="gadm:{local-name()}"><xsl:value-of select="." /></xsl:element>
    </xsl:if>
  </xsl:for-each>
  </rdf:Description>
	
</xsl:template>

</xsl:stylesheet>