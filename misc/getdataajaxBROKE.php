<?php 
	//error_reporting(E_ALL);
	//ini_set('display_errors', 'On');
	
	include "dbconnect.php";
	ini_set("memory_limit","100M");
	set_time_limit(0);
	$phpModelName = $_GET["layer"];

	function getModel($modelname) {
		
		
		if ($modelname == '"Vegetation"') {
			$modelquery = pg_query($db, "SELECT ST_AsText(geom), \"ID\" FROM  $modelname ORDER BY \"ID\" LIMIT 5 ; ");
			}
		else if ($modelname == '"EditMeBuildingWalls"') {
			$modelquery = pg_query($db, "SELECT ST_AsText(geom), \"ID\" FROM  $modelname ORDER BY \"ID\" LIMIT 300 ;");
			}
		else {
			$modelquery = pg_query($db, "SELECT ST_AsText(geom), \"ID\" FROM  $modelname ORDER BY \"ID\"; ");
			}
		
		$polygonString = "";
		$polyhedralString = "";
		$tinzString = "";
		$lineString = "";
		$pointString = "";
		
		$polyhedralID = array();
		$tinzID = array();
		$linestringID = array();
		$pointID = array();
		$polygonID = array();
		
		function PolygonZM($pzmModel) {
			// Unfortunately nearly unreadable, however a lot more efficent than the original code. It essentially a series of string replacements.
			return str_replace(" nan", "", str_replace(")", "", str_replace("(", "", str_replace(")", "", str_replace("),", " &&& ", $aModel = str_replace("POLYGON ZM (", "", str_replace(" -999999", "", $pzmModel)))))));
		}
		
		function PolyhedralSurfaceZ($pszModel) {
			return str_replace(")", "", str_replace("(", "", str_replace("POLYHEDRALSURFACE Z (", "", str_replace(" nan", "", $pszModel))));
		}
		
		function TINZ($tinzModel) {
			return str_replace(")", "", str_replace("(", " ||| ", str_replace("TIN Z (", "", str_replace(" nan", "", $tinzModel))));
		}
		
		function LineStringZ($lineString) {
			
			$aModel = str_replace(" nan", "", $lineString); #Remove " nan" if there are any for some reason
			$aModel = str_replace(" -999999", "", $aModel); #Remove " nan" if there are any for some reason
			$aModel = str_replace("LINESTRING ZM", "", $aModel); #Remove POLYGON ZM ( characters
			$aModel = str_replace("(", "", $aModel); // Get rid of remaining brackets (not needed)
			$aModel = str_replace(")", "", $aModel);
			
			return $aModel;
		}
		
		function PointZ($pointString) {
			
			$aModel = str_replace(" nan", "", $pointString); #Remove " nan" if there are any for some reason
			$aModel = str_replace(" -999999", "", $aModel); #Remove " nan" if there are any for some reason
			$aModel = str_replace("POINT ZM", "", $aModel); #Remove POLYGON ZM ( characters
			$aModel = str_replace("(", "", $aModel); // Get rid of remaining brackets (not needed)
			$aModel = str_replace(")", "", $aModel);
			
			return $aModel;
		}
		
		function checkGeometryType($aGeometry) {

			if (substr( $aGeometry, 0, 10  ) === "POLYGON ZM")  { 
				return array("POLYGON ZM", PolygonZM($aGeometry) . " ::: ");
			}
			if (substr( $aGeometry, 0, 19  ) === "POLYHEDRALSURFACE Z") { 
				return array("POLYHEDRALSURFACE Z", PolyhedralSurfaceZ($aGeometry) . " ::: " );
			}
			if (substr( $aGeometry, 0, 5  ) === "TIN Z") { 
				return array("TIN Z", TINZ($aGeometry));
			}
			if (substr( $aGeometry, 0, 12  ) === "LINESTRING Z") { 
				return array("LINESTRING Z", LineStringZ($aGeometry));
			}
			if (substr( $aGeometry, 0, 7  ) === "POINT Z") { 
				return array("POINT Z", PointZ($aGeometry));
			}
		}
		
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
					}
					if ($polyhedralString != "") { $polyhedralString .= " %%% "; array_push($polyhedralID, $model[1]); }
					if ($polygonString != "") { $polygonString .= " %%% "; array_push($polygonID, $model[1]); }
					
				}
			}
			else {
				//echo "something else happened ";
				$geomtype = checkGeometryType($model[0]);
				if ($geomtype[0] == "POLYHEDRALSURFACE Z") {  $polyhedralString .=  $geomtype[1] . " %%% "; array_push($polyhedralID, $model[1]); continue; }
				if ($geomtype[0] == "POLYGON ZM") {  $polygonString .=  $geomtype[1] . " %%% "; array_push($polygonID, $model[1]); continue; }
				if ($geomtype[0] == "LINESTRING Z") {  $lineString .=  $geomtype[1] . " %%% "; array_push($linestringID, $model[1]); continue; }
				if ($geomtype[0] == "TIN Z") {  $tinzString .= $geomtype[1] . " %%% "; array_push($tinzID, $model[1]); continue; }
				if ($geomtype[0] == "POINT Z") {  $pointString .=  $geomtype[1] . " %%% "; array_push($pointID, $model[1]); }
			}
		}
		
		$modelArray = array(	array("POLYGON ZM", $polygonString, $polygonID),
								array("POLYHEDRALSURFACE Z", $polyhedralString, $polyhedralID),
								array("TIN Z", $tinzString, $tinzID), 
								array("LINESTRING Z", $lineString, $linestringID), 
								array("POINT Z", $pointString, $pointID)
								);

		return json_encode($modelArray);
	}
	
	echo getModel($phpModelName );
	

?> 