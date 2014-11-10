<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="2.0" 
	xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
	xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
	
<xsl:output method="text" indent="yes" />

<xsl:template match="/result">

@prefix rdf: &lt;http://www.w3.org/1999/02/22-rdf-syntax-ns#&gt; .
@prefix rdfs: &lt;http://www.w3.org/2000/01/rdf-schema#&gt; .
@prefix gadm: &lt;http://gadm.geovocab.org/id/&gt; .
@prefix gadmont: &lt;http://gadm.geovocab.org/ontology#&gt; .
@prefix owl: &lt;http://www.w3.org/2002/07/owl#&gt; .

<xsl:apply-templates select="row[iso='ESP']"/>

</xsl:template>

<xsl:template match="row">

<xsl:variable name="id" select="concat(gadm_level,'_',gadm_id)"/>
gadm:<xsl:value-of select="$id" /> rdf:type gadmont:AdministrativeRegion .
<xsl:for-each select='*' >
<xsl:if test=".[not(@xsi:nil)]">
gadm:<xsl:value-of select="$id" /> gadmont:<xsl:value-of select="local-name()"/> '<xsl:value-of select="." />' .
</xsl:if>
</xsl:for-each>

</xsl:template>

</xsl:stylesheet>