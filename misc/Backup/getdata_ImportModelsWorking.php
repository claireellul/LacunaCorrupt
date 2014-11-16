<?php 
error_reporting(E_ALL);

function getModel($modelname) {
	set_time_limit(0);
	include "dbconnect.php";

	$modelquery = pg_query($db, "SELECT ST_AsText(geom) FROM  $modelname LIMIT 500;");
	$numOfModels = pg_num_rows($modelquery);
	$modelCounter = 0;
	
	$POLYGONZMexportString = '';
	$POLYHEDRALSURFACEZexportSring = '';
	$POLYGONZM = "POLYGON ZM";
	$POLYHEDRALSURFACEZ = "POLYHEDRALSURFACE Z";
	
	$POLYGONZMcounter = 0;
	$POLYHEDRASURFACEZcounter = 0;
	
	function PolygonZM($pzmModel) {
		//$POLYHEDRALcounter += 1;
		//echo "made it";
		$aModel = str_replace(" nan", "", $pzmModel); #Remove " nan" if there are any for some reason
		$aModel = str_replace("POLYGON ZM (", "", $aModel); #Remove POLYGON ZM ( characters
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
	
	function checkGeometryType($aGeometry) {
		//echo $aGeometry;
		$exportString = "";
		if (substr( $aGeometry, 0, 10  ) === "POLYGON ZM")  { 
			$exportString = $exportString . PolygonZM($aGeometry) . " ::: "; 
			return array("POLYGON ZM", $exportString);
		}
		if (substr( $aGeometry, 0, 19  ) === "POLYHEDRALSURFACE Z") { 
			$exportString = $exportString . PolyhedralSurfaceZ($aGeometry) . " ::: "; 
			return array("POLYHEDRALSURFACE Z", $exportString);
		}
	}
	
	while ($model = pg_fetch_row($modelquery)) {
	
		 if (substr( $model[0], 0, 18  ) === "GEOMETRYCOLLECTION") { 
			$cleanedCollection = str_replace("GEOMETRYCOLLECTION Z (", "", $model[0]);
			$splitCollection = explode("),", $cleanedCollection);
			foreach($splitCollection as $collection){
				$geomtype = checkGeometryType($collection);
				if ($geomtype[0] == "POLYHEDRALSURFACE Z") {  $POLYHEDRALSURFACEZexportString = $POLYHEDRALSURFACEZexportString . $geomtype[1];  }
				if ($geomtype[0] == "POLYGON ZM") {  $POLYGONZMexportString = $POLYGONZMexportString . $geomtype[1];  }
			}
		}
		else {
			$geomtype = checkGeometryType($model[0]);
			if ($geomtype[0] == "POLYHEDRALSURFACE Z") {  $POLYHEDRALSURFACEZexportString = $POLYHEDRALSURFACEZexportString . $geomtype[1];  }
			if ($geomtype[0] == "POLYGON ZM") {  $POLYGONZMexportString = $POLYGONZMexportString . $geomtype[1];  }
			
		}
	}

	//echo  $POLYHEDRALSURFACEZexportString;
	
	// YOU CAN REMOVE THE FINAL ::: BEFORE YOU PASS IT SURELY? THEN YOU DONT HAVE TODO 
	$modelArray = array(array($POLYGONZM, $POLYGONZMexportString), array($POLYHEDRALSURFACEZ, $POLYHEDRALSURFACEZexportString));
	//echo $modelArray[3];

	return json_encode($modelArray);
}
//getModel('"Roofs"');

?> 