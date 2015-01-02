<?php
	//error_reporting(E_ALL);
	//ini_set('display_errors', 'On');

	ini_set("memory_limit","300M");
	set_time_limit(0);
	$phpModelName = $_GET["layer"];
	$phpModelType = $_GET["modelType"];
	$phpProjectName = $_GET["projectName"];
	$phpXMin = $_GET["XMin"];
	$phpXMax = $_GET["XMax"];
	$phpYMin = $_GET["YMin"];
	$phpYMax = $_GET["YMax"];
	$phpZMin = $_GET["ZMin"];
	$phpZMax = $_GET["ZMax"];
	$idCol = "";
	$nodeTable = "";
	$faceTable = "";
	$nodes = "";
	$faces = "";

	function getModel($modelname,$modelType,$XMin,$XMax,$YMin,$YMax,$ZMin,$ZMax,$projectName) {
		global $idCol;
		$polygonString = "";
		$polygonZString = "";
		$polyhedralString = "";
		$tinzString = "";
		$lineString = "";
		$pointString = "";

		$polyhedralsurfaceID = array();
		$tinzID = array();
		$linestringID = array();
		$pointID = array();
		$polygonID = array();
		$polygonZID = array();

		include "dbconnect.php";

		// two types of requests to process
		// 1.  a standard query for all the data OR a standard query for all data within an MBR
		// 2.  a query for triangulated data OR a query for triangulated data within an MBR
		/* NB the triangualted data can be processed in two different ways on the client - firstly, as unintelligent data
		where the objects are created in bulk but will therefore not have any methods such as 'getNormal' */

		// get the details of the table
		$getMetadata = pg_query($db,"select * from projectMetadata where tableName = '".$modelname."' and projectname = '".$projectName."'");
		$metadata = pg_fetch_assoc($getMetadata);
		$geomCol = $metadata['geometrycolumnname'];
		$idCol = $metadata['idcolumnname'];
		if ((strpos($modelType,'Triang') > -1)) {
			// get the name of the nodes and nodeface list tables
			$nodeTable = $metadata['trianglenodetablename'];
			$faceTable = $metadata['trianglefacename'];
		}
	if ((!is_null($XMax)) && (strpos($modeltype,'Triangle') < 0)) {
				// this is a standard layer, and we have MBR values so we need to limit the geometry to retrieve
				// first we need the SRID of the geometry

				$whereclause ="where st_insertsects(".$geomCol.", st_envelope(st_geomfromtext('GEOMETRYCOLLECTION(POINT(";
				$whereclause = $whereclause.$XMin." ".$YMin." ".$ZMin."),POINT(".$XMax." ".$YMax." ".$ZMax."))',".$srid.")))";
			}
			else if (!is_null($XMax)) {
				// this is a triangulated layer, so we need to make sure we only retrieve the required nodes and faces
				$nodewhereclause = " where x > $XMin and x < $XMax and y > $YMin and y < $YMax and z > $ZMin and z < $ZMax";
				// make sure that the faces selected have all 3 nodes available
				$facewhereclause = " where node1 in (select id from \"$nodeTable\" $nodewhereclause )";
				$facewhereclause = $facewhereclause." and node2 in (select id from \"$nodeTable\" $nodewhereclause )";
				$facewhereclause = $facewhereclause." and node3 in (select id from \" $nodeTable\" $nodewhereclause )";
			}

		if ((strpos($modelType,'Triang') > -1)) {
				$nodesql = "select json_agg(nodejson) from (select x,y,z from \"$nodeTable\" $nodewhereclause) nodejson";
				$nodesquery = pg_query($db,$nodesql);

				// note that the node ids don't go from 0 to n necessarily - which is what is expected in three.js
				// as the nodes are extracted from a 0-base nodes array
				// this is particularly the case if we're only extracting part of the nodes as we are using an mbr query
				// so we need to transform the ids
				// because of this we don't need the faces as json immediately, but rather as an array
				$facesql ="select * from \"$faceTable\" $facewhereclause";
				$facesquery = pg_query($db,$facesql);
				$nodeidchangequery = pg_query($db,"select id, (row_number() over ())-1 as id from \"$nodeTable\" $nodewhereclause");
		}
		else {
			$query = "SELECT ST_AsText($geomCol), $idCol FROM  \"$modelname\" $whereclause";
			$modelquery = pg_query($db, urldecode($query));
		}


	// process standard layers - triangulated layer does not need further processing
	if ((strpos($modelType,'Triang')===false) ){
		while ($model = pg_fetch_row($modelquery)) {
			if (substr( $model[0], 0, 18  ) === "GEOMETRYCOLLECTION") {
				$cleanedCollection = str_replace("GEOMETRYCOLLECTION Z (", "", $model[0]);
				if  (substr( $cleanedCollection, 0, 5  ) === "TIN Z") {
					$TINZgeom = checkGeometryType($cleanedCollection);
					$tinzString .= $TINZgeom[1] . " %%% ";
					array_push( $tinzID, $model[1] );
				}
				else {
					$splitCollection = explode("),", $cleanedCollection);
					foreach($splitCollection as $collection){
						$geomtype = checkGeometryType($collection);
						if ($geomtype[0] == "POLYHEDRALSURFACE Z") {  $polyhedralString .= $geomtype[1]; }
						if ($geomtype[0] == "POLYGON ZM") {  $polygonString .= $geomtype[1];  }
						if ($geomtype[0] == "POLYGON Z") {  $polygonZString .= $geomtype[1];  }
					}
					if ($polyhedralString != "") { $polyhedralString .= " %%% "; array_push($polyhedralsurfaceID, $model[1]); }
					if ($polygonString != "") { $polygonString .= " %%% "; array_push($polygonID, $model[1]); }
					if ($polygonZString != "") { $polygonZString .= " %%% "; array_push($polygonZID, $model[1]); }

				}
			}
			else {
				$geomtype = checkGeometryType($model[0]);
				if ($geomtype[0] == "POLYHEDRALSURFACE Z") {$polyhedralString .=  $geomtype[1] . " %%% "; array_push($polyhedralsurfaceID, $model[1]); continue; }
				if ($geomtype[0] == "POLYGON ZM") {  $polygonString .=  $geomtype[1] . " %%% "; array_push($polygonID, $model[1]); continue; }
				if ($geomtype[0] == "POLYGON Z") {  $polygonZString .=  $geomtype[1] . " %%% "; array_push($polygonZID, $model[1]); continue; }
				if ($geomtype[0] == "LINESTRING Z") {  $lineString .=  $geomtype[1] . " %%% "; array_push($linestringID, $model[1]); continue; }
				if ($geomtype[0] == "TIN Z") {  $tinzString .= $geomtype[1] . " %%% "; array_push($tinzID, $model[1]); continue; }
				if ($geomtype[0] == "POINT Z") {  $pointString .=  $geomtype[1] . " %%% "; array_push($pointID, $model[1]);  }
			}
		}
	}
	else {
			// a triangulated layer uses pre-created stored triangles
			// and iterates through a list of faces to create intelligent face objects - i.e. with the required methods
			// in the javascript
			// now change the ID values in the faces data to make sure we start from a 0-based array
			$oldnodes = pg_fetch_all_columns($nodeidchangequery,0);
			$newnodes = pg_fetch_all_columns($nodeidchangequery,1);
			$ids = pg_fetch_all_columns($facesquery,0);
			$faceids = pg_fetch_all_columns($facesquery,1);
			$node1ids =pg_fetch_all_columns($facesquery,2);
			$node2ids = pg_fetch_all_columns($facesquery,3);
			$node3ids = pg_fetch_all_columns($facesquery,4);

			$newnodes1 = str_replace($oldnodes,$newnodes,$node1ids);
			$newnodes2 = str_replace($oldnodes,$newnodes,$node2ids);
			$newnodes3 = str_replace($oldnodes,$newnodes,$node3ids);

			// depending on whether this is bulk triangles or not, the required array is different
			// as bulk triangles just loads a json array into the faces object in the mesh directly
			// where as normal triangles actually creates the individual features - i.e. has intelligence in the model
			if ($modelType == "Triangulation"){
				$faces = array_map("mergeArrays", $ids,$faceids,$newnodes1, $newnodes2,$newnodes3);
				$nodes = pg_fetch_row($nodesquery);
			}
			else {
				$bulkfaces = array_map("mergeBulkArrays",$ids,$faceids,$newnodes1, $newnodes2,$newnodes3);
				$bulknodes = pg_fetch_row($nodesquery);
			}

	}
		return json_encode( array(	array("POLYGON ZM", $polygonString, $polygonID),
									array("POLYGON Z", $polygonZString, $polygonZID),
									array("POLYHEDRALSURFACE Z", $polyhedralString, $polyhedralsurfaceID),
									array("TIN Z", $tinzString, $tinzID),
									array("LINESTRING Z", $lineString, $linestringID),
									array("POINT Z", $pointString, $pointID),
									array("TRIANGLES",json_decode($nodes[0]),$faces),
									array("BULKTRIANGLES",json_decode($bulknodes[0]),$bulkfaces)));
	}

		function PolygonZ($pzModel) {
			// Unfortunately nearly unreadable, however a lot more efficent than the original code. It essentially a series of string replacements.
			$newPzModel = str_replace(" nan", "", str_replace(")", "", str_replace("(", "", str_replace(")", "",
			str_replace("),", " &&& ", $aModel = str_replace("POLYGON Z ((", "", str_replace(" -999999", "", $pzModel)))))));
			return $newPzModel;
		}
		function PolygonZM($pzmModel) {
			// Unfortunately nearly unreadable, however a lot more efficent than the original code. It essentially a series of string replacements.
			return str_replace(" nan", "", str_replace(")", "", str_replace("(", "", str_replace(")", "", str_replace("),", " &&& ", $aModel = str_replace("POLYGON ZM (", "", str_replace(" -999999", "", $pzmModel)))))));
		}

		function PolyhedralSurfaceZ($pszModel) {
			// this is made up of multiple polygons which in the st_astext are separated by ")),(("
			// which should be separated by :::
			return str_replace(")", "", str_replace("(", "", str_replace(")),(("," ::: ",str_replace("POLYHEDRALSURFACE Z (", "", str_replace(" nan", "", $pszModel)))));
		}

		function TINZ($tinzModel) {
			return str_replace(")", "", str_replace("(", " ||| ", str_replace("TIN Z (", "", str_replace(" nan", "", $tinzModel))));
		}

		function LineStringZ($lineString) {
			return str_replace(")", "", str_replace("(", "", str_replace("LINESTRING Z", "", str_replace(" -999999", "", str_replace(" nan", "", $lineString)))));
		}
		function LineStringZM($lineString) {
			return str_replace(")", "", str_replace("(", "", str_replace("LINESTRING Z", "", str_replace(" -999999", "", str_replace(" nan", "", $lineString)))));
		}

		function PointZ($pointString) {
			return str_replace(")", "", str_replace("(", "", str_replace("POINT ZM", "", str_replace(" -999999", "",  str_replace(" nan", "", $pointString)))));
		}

		// each individual geometry within an object separated by :::
		// each geometry separated by %%%
		function checkGeometryType($aGeometry) {
			if (substr( $aGeometry, 0, 10  ) === "POLYGON ZM")  {
				return array("POLYGON ZM", PolygonZM($aGeometry) . " ::: ");
			}
			if (substr( $aGeometry, 0, 9  ) === "POLYGON Z")  {
				return array("POLYGON Z", PolygonZ($aGeometry) . " ::: ");
			}
			if (substr( $aGeometry, 0, 19  ) === "POLYHEDRALSURFACE Z") {
				return array("POLYHEDRALSURFACE Z", PolyhedralSurfaceZ($aGeometry) . " ::: ");
			}
			if (substr( $aGeometry, 0, 5  ) === "TIN Z") {
				return array("TIN Z", TINZ($aGeometry));
			}
			if (substr( $aGeometry, 0, 12  ) === "LINESTRING Z") {
				return array("LINESTRING Z", LineStringZ($aGeometry));
			}
			if (substr( $aGeometry, 0, 13  ) === "LINESTRING ZM") {
				return array("LINESTRING ZM", LineStringZM($aGeometry));
			}
			if (substr( $aGeometry, 0, 7  ) === "POINT Z") {
				return array("POINT Z", PointZ($aGeometry));
			}
		}

function mergeArrays($a,$b,$c,$d,$e){
	//$keys = array("globalid","face_id","node1","node2","node3");
	global $idCol;
	$finalval = array();
	$finalval[$idCol] = $a;
	$finalval["face_id"] = $b;
	$finalval["node1"] = $c;
	$finalval["node2"] = $d;
	$finalval["node3"]= $e;
	return $finalval;
	//array($a,$b,$c,$d,$e);
}

function mergeBulkArrays($a,$b,$c,$d,$e){
	// return an array that when converted to json can be directly loaded
	// into a three.js mesh without any additional parsing

	global $idCol;
	$finalval = array();
	// the three node values
	$finalval["a"] = $c;
	$finalval["b"] = $d;
	$finalval["c"]= $e;
	$normal = array();
		$normal["x"]= 0;
		$normal["y"]= 0;
		$normal["z"]= 0;
	$vertexNormals = array();
	$finalval["normal"] = $normal;
	$finalval["vertexNormals"] = array();
	$finalval["color"] = array();
	$finalval["vertexColors"] = array();
	$finalval["vertexTangents"] = array();
	$finalval["materialIndex"]= 0;
	return $finalval;
}




	echo getModel($phpModelName,$phpModelType,$phpXMin,$phpXMax, $phpYMin,$phpYMax, $phpZMin, $phpZMax,$phpProjectName);

		// temporary test
		// first the vertex array
//		$nodes = json_encode(
///				array(
//				array(x=>529521.376878298,y=>182238.700127146,z=>1.02523780691187),
//				array(x=>529525.511197145,y=>182233.04278369,z=>1.0257378069118699),
//				array(x=>529525.979479803,y=>182233.384999049,z=>1.0257378069118699),
//				array(x=>529522.188326634,y=>182238.572759444,z=>1.0257378069118699)));
		// then the faces array
//		$faces = json_encode(array(
//		array(a=>3,b=>0,c=>1,normal=>array(x=>0,y=>0,z=>0),vertexNormals=>array(),color=>array(),vertexColors=>array(),vertexTangents=>array(),
//		materialIndex=>0),
//		array(a=>1,b=>2,c=>3,normal=>array(x=>0,y=>0,z=>0),vertexNormals=>array(),color=>array(),vertexColors=>array(),vertexTangents=>array(),
//		materialIndex=>0)));


//array(
//				array(x=>529521.376878298,y=>182238.700127146,z=>1.02523780691187),
//				array(x=>529525.511197145,y=>182233.04278369,z=>1.0257378069118699),
//				array(x=>529525.979479803,y=>182233.384999049,z=>1.0257378069118699),
//				array(x=>529522.188326634,y=>182238.572759444,z=>1.0257378069118699))


	/*		return json_encode( array(	array("POLYGON ZM", $polygonString, $polygonID),
										array("POLYHEDRALSURFACE Z", $polyhedralString, $polyhedralsurfaceID),
										array("TIN Z", $tinzString, $tinzID),
										array("LINESTRING Z", $lineString, $linestringID),
										array("POINT Z", $pointString, $pointID)
									 )
								);
*/

/*		if ($modelname == '"Vegetation"') {
			$modelquery = pg_query($db, "SELECT ST_AsText($geomCol), $idCol FROM  $modelname ORDER BY $idCol LIMIT 10 ; ");
		}
		else if ($modelname == '"EditMeBuildingWalls"' || $modelname == '"BuildingWalls"' ) {
			$modelquery = pg_query($db, "SELECT ST_AsText($geomCol), $idCol FROM  $modelname ORDER BY $idCol LIMIT 300 ; ");
		}
		else if ($modelname == '"HeightedOSMMTopoArea"' ) {
			$modelquery = pg_query($db, "SELECT ST_AsText($geomCol), $idCol FROM  \"$modelname\" ORDER BY $idCol LIMIT 2000 ; ");
		}
*/


?>

