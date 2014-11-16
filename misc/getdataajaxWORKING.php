<?php 
	error_reporting(E_ALL);
	ini_set('display_errors', 'On');
	
	ini_set("memory_limit","100M");
	set_time_limit(0);
	$phpModelName = $_GET["layer"];

	function getModel($modelname) {
		
		include "dbconnect.php";
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
		
		$polyhedralsurfaceID = array();
		$tinzID = array();
		$linestringID = array();
		$pointID = array();
		$polygonID = array();
		
		function PolygonZM($pzmModel) {
		
			$aModel = str_replace(" nan", "", $pzmModel); #Remove " nan" if there are any for some reason
			$aModel = str_replace(" -999999", "", $aModel); #Remove " nan" if there are any for some reason
			$aModel = str_replace("POLYGON ZM (", "", $aModel); #Remove POLYGON ZM ( characters
			$aModel = str_replace("),", " &&& ", $aModel);
			$aModel = str_replace("(", "", $aModel); // Get rid of remaining brackets (not needed)
			$aModel = str_replace(")", "", $aModel);
			return $aModel;
		}
		
		function PolyhedralSurfaceZ($pszModel) {
			$aModel = str_replace(" nan", "", $pszModel); #Remove " nan" if there are any for some reason
			$aModel = str_replace("POLYHEDRALSURFACE Z (", "", $aModel); #Remove 'GEOMETRYCOLLECTION Z ('
			$aModel = str_replace("(", "", $aModel);
			$aModel = str_replace(")", "", $aModel);
			return $aModel;
		}
		
		function TINZ($tinzModel) {
			$aModel = str_replace(" nan", "", $tinzModel); #Remove " nan" if there are any for some reason
			$aModel = str_replace("TIN Z (", "", $aModel); #Remove POLYGON ZM ( characters
			$aModel = str_replace("(", " ||| ", $aModel); // Get rid of remaining brackets (not needed)
			$aModel = str_replace(")", "", $aModel);
			//$aModel = str_replace(",", "", $aModel);
			//echo $aModel;
			return $aModel;
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
			//echo $aGeometry;
			$exportString = "";
			if (substr( $aGeometry, 0, 10  ) === "POLYGON ZM")  { 
				$exportString .= PolygonZM($aGeometry) . " ::: "; 
				return array("POLYGON ZM", $exportString);
			}
			if (substr( $aGeometry, 0, 19  ) === "POLYHEDRALSURFACE Z") { 
				$exportString .=  PolyhedralSurfaceZ($aGeometry) . " ::: "; 
				return array("POLYHEDRALSURFACE Z", $exportString);
			}
			if (substr( $aGeometry, 0, 5  ) === "TIN Z") { 
				$exportString .= TINZ($aGeometry); 
				return array("TIN Z", $exportString);
			}
			if (substr( $aGeometry, 0, 12  ) === "LINESTRING Z") { 
				$exportString .= LineStringZ($aGeometry); 
				return array("LINESTRING Z", $exportString);
			}
			if (substr( $aGeometry, 0, 7  ) === "POINT Z") { 
				$exportString .= PointZ($aGeometry); 
				return array("POINT Z", $exportString);
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
					if ($polyhedralString != "") { $polyhedralString .= " %%% "; array_push($polyhedralsurfaceID, $model[1]); }
					if ($polygonString != "") { $polygonString .= " %%% "; array_push($polygonID, $model[1]); }
					
				}
			}
			else {
				//echo "something else happened ";
				$geomtype = checkGeometryType($model[0]);
				if ($geomtype[0] == "POLYHEDRALSURFACE Z") {  $polyhedralString .=  $geomtype[1] . " %%% "; array_push($polyhedralsurfaceID, $model[1]); continue; }
				if ($geomtype[0] == "POLYGON ZM") {  $polygonString .=  $geomtype[1] . " %%% "; array_push($polygonID, $model[1]); continue; }
				if ($geomtype[0] == "LINESTRING Z") {  $lineString .=  $geomtype[1] . " %%% "; array_push($linestringID, $model[1]); continue; }
				if ($geomtype[0] == "TIN Z") {  $tinzString .= $geomtype[1] . " %%% "; array_push($tinzID, $model[1]);  }
				if ($geomtype[0] == "POINT Z") {  $pointString .=  $geomtype[1] . " %%% "; array_push($pointID, $model[1]); continue; }
			}
		}
		
		//echo $tinzString;

		// YOU CAN REMOVE THE FINAL ::: BEFORE YOU PASS IT SURELY? THEN YOU DONT HAVE TODO 
		$modelArray = array(	array("POLYGON ZM", $polygonString, $polygonID),
								array("POLYHEDRALSURFACE Z", $polyhedralString, $polyhedralsurfaceID),
								array("TIN Z", $tinzString, $tinzID), 
								array("LINESTRING Z", $lineString, $linestringID), 
								array("POINT Z", $pointString, $pointID)
								);
		//echo "hello";
		//echo $tinzString;
		return json_encode($modelArray);
	}
	
	echo getModel($phpModelName );
	

?> 