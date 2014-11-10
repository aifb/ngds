<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:gml="http://www.opengis.net/gml">
<xsl:output method="text" indent="yes" />

<xsl:template match="gml:MultiPolygon|gml:MultiSurface">
MULTIPOLYGON (<xsl:apply-templates select="child::gml:polygonMember|child::gml:surfaceMember" />)
</xsl:template>

<xsl:template match="gml:polygonMember|gml:surfaceMember">
(<xsl:apply-templates select="gml:interior|gml:innerBoundaryIs|gml:exterior|gml:outerBoundaryIs" />)<xsl:if test="position() != last()">,</xsl:if>
</xsl:template>

<xsl:template match="gml:Polygon|gml:Surface">
POLYGON (<xsl:apply-templates select="gml:interior|gml:innerBoundaryIs|gml:exterior|gml:outerBoundaryIs" />)
</xsl:template>

<xsl:template match="gml:interior|gml:innerBoundaryIs|gml:exterior|gml:outerBoundaryIs">
(<xsl:apply-templates select="child::gml:posList|child::gml:coordinates" />)<xsl:if test="position() != last()">,</xsl:if>
</xsl:template>

<xsl:template match="gml:MultiLineString|gml:MultiCurve">
MULTILINESTRING (<xsl:apply-templates select="child::gml:lineStringMember|child::gml:curveMember" />)
</xsl:template>

<xsl:template match="gml:lineStringMember|gml:curveMember">
(<xsl:apply-templates select="child::gml:posList|child::gml:coordinates" />)<xsl:if test="position() != last()">,</xsl:if>
</xsl:template>

<xsl:template match="gml:LineString|gml:Curve">
LINESTRING (<xsl:apply-templates select="child::gml:posList|child::gml:coordinates" />)
</xsl:template>

<xsl:template match="gml:MultiPoint">
MULTIPOINT (<xsl:apply-templates select="child::gml:pointMember" />)
</xsl:template>

<xsl:template match="child::gml:pointMember">
(<xsl:apply-templates select="child::gml:posList|child::gml:coordinates" />)<xsl:if test="position() != last()">,</xsl:if>
</xsl:template>

<xsl:template match="gml:posList|gml:coordinates">
<xsl:value-of select="self::node()"/>
</xsl:template>

</xsl:stylesheet>