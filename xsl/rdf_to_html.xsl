<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0"
   xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
   xmlns:owl="http://www.w3.org/2002/07/owl#"
   xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
   xmlns:rdfs="http://www.w3.org/2000/01/rdf-schema#"
   xmlns:skos="http://www.w3.org/2004/02/skos/core#"
   xmlns:xml="http://www.w3.org/XML/1998/namespace"
   xmlns:xsd="http://www.w3.org/2001/XMLSchema#"
   xmlns:vs="http://www.w3.org/2003/06/sw-vocab-status/ns#"
   xmlns:ngeo="http://geovocab.org/geometry#"
   xmlns:spatial="http://geovocab.org/spatial#"
   xmlns="http://www.w3.org/1999/xhtml">

	<xsl:output method="html" indent="yes"/>
	
    <xsl:template match="/">
		<html>
		<head>
			<title><xsl:value-of select="/rdf:RDF/owl:Ontology/rdfs:label"/></title>
			<link rel="alternate" type="application/rdf+xml" href="{concat(/rdf:RDF/owl:Ontology/@rdf:about,'.rdf')}"/>
			<link rel="stylesheet" type="text/css" href="http://www.w3.org/StyleSheets/TR/W3C-NOTE" /> 
			<style type="text/css"> 
				body { background-image: none; padding-left: 1em; }  /* Suppress the "W3C Note" logo */
				pre { background: #ffc; margin-left: 0; padding: 1.4em 2em; overflow: auto; }
				
				.red-highlight { color: #cc0000; }
				
				th { text-align: left; padding-left: 20px; padding-right: 50px; }
				th, td { vertical-align: top; }
			</style> 
		</head>
		<body>
			<xsl:if test="/rdf:RDF/owl:Ontology">
				<xsl:apply-templates select="/rdf:RDF/owl:Ontology"/>
				<hr/>
			</xsl:if>
			
			<xsl:if test="/rdf:RDF/owl:Class">
				<h2>Classes</h2>
				<xsl:apply-templates select="/rdf:RDF/owl:Class"/>
				<hr/>
			</xsl:if>
			
			<xsl:if test="/rdf:RDF/owl:DatatypeProperty">
				<h2>Datatype Properties</h2>
				<xsl:apply-templates select="/rdf:RDF/owl:DatatypeProperty"/>
				<hr/>
			</xsl:if>
			
			<xsl:if test="/rdf:RDF/owl:ObjectProperty">
				<h2>Object Properties</h2>
				<xsl:apply-templates select="/rdf:RDF/owl:ObjectProperty"/>
				<hr/>
			</xsl:if>

			<xsl:if test="/rdf:RDF/*[name() != 'owl:Ontology' and name() != 'owl:Class' and name() != 'owl:DatatypeProperty' and name() != 'owl:ObjectProperty']">
				<h2>Individuals</h2>
				<xsl:apply-templates select="/rdf:RDF/*[name() != 'owl:Ontology' and name() != 'owl:Class' and name() != 'owl:DatatypeProperty' and name() != 'owl:ObjectProperty']"/>
				<hr/>
			</xsl:if>
		</body>
		</html>
    </xsl:template>
	
	<xsl:template match="/rdf:RDF/*[not(owl:Ontology)]">
		<div id="{substring-after(@rdf:about,'#')}">
			<xsl:choose>
				<xsl:when test="rdfs:label">
					<h3><xsl:value-of select="rdfs:label"/></h3>
				</xsl:when>
				<xsl:otherwise>
					<h3><xsl:value-of select="@rdf:about"/></h3>
				</xsl:otherwise>
			</xsl:choose>
			<p><xsl:value-of select="rdfs:comment"/></p>
			
			<table>
			<tr><th>URI</th><td><a href="{@rdf:about}"><xsl:value-of select="@rdf:about"/></a></td></tr>
			<xsl:for-each select="child::*[not(self::rdfs:label or self::rdfs:comment)]">
				<tr>
					<th><xsl:value-of select="local-name()"/></th>
					<xsl:choose>
						<xsl:when test="child::node()/child::node()">
							<td>
							<b><xsl:value-of select="local-name(child::*)"/></b>
							<table>
							<xsl:for-each select="child::node()/child::*">
								<tr>
									<th><xsl:value-of select="local-name()"/></th>
									<xsl:choose>
										<xsl:when test="@rdf:resource">
											<td><a href="{@rdf:resource}"><xsl:value-of select="substring-after(@rdf:resource,'#')"/></a></td>
										</xsl:when>
										<xsl:otherwise>
											<td><xsl:value-of select="self::node()"/></td>
										</xsl:otherwise>
									</xsl:choose>
								</tr>
							</xsl:for-each>
							</table>							
							</td>
						</xsl:when>
						<xsl:when test="@rdf:resource">
							<td><a href="{@rdf:resource}"><xsl:value-of select="@rdf:resource"/></a></td>
						</xsl:when>
						<xsl:otherwise>
							<td><xsl:value-of select="self::node()"/></td>
						</xsl:otherwise>
					</xsl:choose>
				</tr>
			</xsl:for-each>
			</table>
		</div>
    </xsl:template>	

	<xsl:template match="owl:Ontology">
		<div id="{substring-after(@rdf:about,'#')}">
			<xsl:choose>
				<xsl:when test="rdfs:label">
					<h1><xsl:value-of select="rdfs:label"/></h1>
				</xsl:when>
				<xsl:otherwise>
					<h1><xsl:value-of select="@rdf:about"/></h1>
				</xsl:otherwise>
			</xsl:choose>
			<p><xsl:value-of select="rdfs:comment"/></p>
			
			<table>
			<xsl:for-each select="child::*[not(self::rdfs:label or self::rdfs:comment)]">
				<tr>
					<xsl:choose>
						<xsl:when test="@rdf:resource">
							<td><a href="{@rdf:resource}"><xsl:value-of select="substring-after(@rdf:resource,'#')"/></a></td>
						</xsl:when>
						<xsl:otherwise>
							<td><xsl:value-of select="self::node()"/></td>
						</xsl:otherwise>
					</xsl:choose>
				</tr>
			</xsl:for-each>
			</table>
		</div>
    </xsl:template>	
</xsl:stylesheet>