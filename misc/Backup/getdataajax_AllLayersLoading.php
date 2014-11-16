<?php 
	
	$phpModelName = $_GET["layer"];

	function getModel($modelname) {
		set_time_limit(0);
		include "dbconnect.php";
		
		$modelquery = pg_query($db, "SELECT ST_AsText(geom) FROM  $modelname LIMIT 200 ; ");
		$numOfModels = pg_num_rows($modelquery);
		$modelCounter = 0;
		
		$POLYGONZMexportString = '';
		$POLYHEDRALSURFACEZexportSring = '';
		$TINZexportSring = '';
		$lineStringExportString = '';
		$pointStringExportString = '';
		
		$POLYGONZM = "POLYGON ZM";
		$POLYHEDRALSURFACEZ = "POLYHEDRALSURFACE Z";
		$TINZ = "TIN Z";
		$LINESTRINGZ = "LINESTRING Z";
		$POINTZ = "POINT Z";
		
		function PolygonZM($pzmModel) {
		
			$aModel = str_replace(" nan", "", $pzmModel); #Remove " nan" if there are any for some reason
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
			return $aModel;
		}
		
		function LineStringZ($lineString) {
			
			$aModel = str_replace(" nan", "", $lineString); #Remove " nan" if there are any for some reason
			$aModel = str_replace("LINESTRING Z", "", $aModel); #Remove POLYGON ZM ( characters
			$aModel = str_replace("M", "", $aModel);
			$aModel = str_replace("(", "", $aModel); // Get rid of remaining brackets (not needed)
			$aModel = str_replace(")", "", $aModel);
			
			return $aModel;
		}
		
		function PointZ($pointString) {
			
			$aModel = str_replace(" nan", "", $pointString); #Remove " nan" if there are any for some reason
			$aModel = str_replace("POINT Z", "", $aModel); #Remove POLYGON ZM ( characters
			$aModel = str_replace("M", "", $aModel);
			$aModel = str_replace("(", "", $aModel); // Get rid of remaining brackets (not needed)
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
			if (substr( $aGeometry, 0, 5  ) === "TIN Z") { 
				$exportString = $exportString . TINZ($aGeometry); 
				return array("TIN Z", $exportString);
			}
			if (substr( $aGeometry, 0, 12  ) === "LINESTRING Z") { 
				$exportString = $exportString . LineStringZ($aGeometry); 
				return array("LINESTRING Z", $exportString);
			}
			if (substr( $aGeometry, 0, 7  ) === "POINT Z") { 
				$exportString = $exportString . PointZ($aGeometry); 
				return array("POINT Z", $exportString);
			}
		}
		
		while ($model = pg_fetch_row($modelquery)) {
			//echo $model[0];
			if (substr( $model[0], 0, 18  ) === "GEOMETRYCOLLECTION") { 
				$cleanedCollection = str_replace("GEOMETRYCOLLECTION Z (", "", $model[0]);
				if  (substr( $cleanedCollection, 0, 5  ) === "TIN Z") {
					$TINZgeom = checkGeometryType($cleanedCollection);
					$TINZexportString = $TINZexportString . $TINZgeom[1] . " %%% ";
				}
				else {
					$splitCollection = explode("),", $cleanedCollection);
					foreach($splitCollection as $collection){
						$geomtype = checkGeometryType($collection);
						if ($geomtype[0] == "POLYHEDRALSURFACE Z") {  $POLYHEDRALSURFACEZexportString = $POLYHEDRALSURFACEZexportString . $geomtype[1]; }
						if ($geomtype[0] == "POLYGON ZM") {  $POLYGONZMexportString = $POLYGONZMexportString . $geomtype[1];  }
					}
					if ($POLYHEDRALSURFACEZexportString != "") { $POLYHEDRALSURFACEZexportString = $POLYHEDRALSURFACEZexportString . " %%% "; }
					if ($POLYGONZMexportString != "") { $POLYGONZMexportString = $POLYGONZMexportString . " %%% "; }
					
				}
			}
			else {
				$geomtype = checkGeometryType($model[0]);
				if ($geomtype[0] == "POLYHEDRALSURFACE Z") {  $POLYHEDRALSURFACEZexportString = $POLYHEDRALSURFACEZexportString . $geomtype[1] . " %%% "; }
				if ($geomtype[0] == "POLYGON ZM") {  $POLYGONZMexportString = $POLYGONZMexportString . $geomtype[1] . " %%% "; }
				if ($geomtype[0] == "LINESTRING Z") {  $lineStringExportString = $lineStringExportString . $geomtype[1] . " %%% "; }
				if ($geomtype[0] == "TIN Z") {  $TINZexportString = $TINZexportString . $geomtype[1] . " %%% "; }
				if ($geomtype[0] == "POINT Z") {  $pointStringExportString = $pointStringExportString . $geomtype[1] . " %%% "; }
			}
		}

		// YOU CAN REMOVE THE FINAL ::: BEFORE YOU PASS IT SURELY? THEN YOU DONT HAVE TODO 
		$modelArray = array(array($POLYGONZM, $POLYGONZMexportString), array($POLYHEDRALSURFACEZ, $POLYHEDRALSURFACEZexportString), array($TINZ, $TINZexportString), array($LINESTRINGZ, $lineStringExportString), array($POINTZ, $pointStringExportString));
		//echo $modelArray[1][1];

		return json_encode($modelArray);
	}
	echo getModel($phpModelName);
	

?> 