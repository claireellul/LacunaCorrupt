<!DOCTYPE html>
<html lang="en">
	<head>
		<title>Lacuna - 3D Web GIS using HTML5</title>
		<meta charset="utf-8">

		<link rel="stylesheet" href="css/Lacuna.css">
		<link rel="stylesheet" href="css/perfect-scrollbar-0.4.10.min.css">
		<link rel='stylesheet' href='css/spectrum.css' />
		<link rel="stylesheet" href="http://code.jquery.com/ui/1.10.4/themes/smoothness/jquery-ui.css">
		<!-- <script src="poly2tri.js"></script> -->
		<script src="js/three67.js"></script>
		<script src="js/TrackballControls.js"></script>
		<script src="js/FirstPersonControls.js"></script>
		<script src="js/stats.min.js"></script>
		<script src="js/jquery-1.11.0.min.js"></script> <!-- jQuery must be defined first! -->
		<script src="js/jquery-ui.js"></script>
		<script src="js/perfect-scrollbar-0.4.10.with-mousewheel.min.js"></script>
		<script src='js/spectrum.js'></script>
		<script src='js/getrandomcolor.js'></script>
		<!-- <script src='js/pnltri.min.js'></script> -->
		<?php include 'ajax/dbconnect.php'; ?>

	</head>

	<body>
		<div id="topbar">
			<div id="logoholder">
				<img id="logo" src="imgs/LacunaLogo2.png"></img>
			</div>
			<div id="buttons">
				<div id="camera" title="Camera Options" ><img src="imgs/camera.png"></img></div> <div id="cameraoptions">
					<div id="lookat" title="Look At""><img id="lookatimage" src="imgs/lookat.png" ></img></div>
					<div id="camerasettings" title="Camera Settings"><img id="camerasettingsimage" src="imgs/camerasettings.png" > </div>
				</div>
				<div id="helper" title="Helper" ><img src="imgs/helper.png"></img></div> <div id="helperoptions">
					<div id="helpertoggle"> ON </div>
					<div id="helpercolour"> </div>
				</div>
				<div id="canvas" title="Canvas Colour" ><img src="imgs/canvas.png"></img></div> <div id="canvasoptions">
					<div id="canvascolour"> </div>
				</div>
				<div id="axis" title="Axis Options"><img src="imgs/axis.png" ></img> </div><div id="axisoptions">
					<div id="axistoggle">ON </div>
					<div id="axispos" title="Axis Position"><img id="axisposimage" src="imgs/axispos.png" ></img></div>
					<div id="axissize" title="Axis Size"><img id="axissizeimage" src="imgs/axissize.png" ></img></div>
				</div>
				<div id="wireframe" title="Wireframe Mode"><img src="imgs/wireframe.png" ></img> </div><div id="wireframeoptions">
					<div id="wireframetoggle"> OFF </div>
				</div>
				<div id="measure" title="Measure"><img src="imgs/measure.png" ></img> </div><div id="measureoptions">
					<div id="clickdistance" title="Distance by Click"><img id="clickdistanceimage" src="imgs/clickdistance.png" ></img> </div>
					<div id="area" title="Surface Area"><img id="areaimage" src="imgs/area.png" ></img> </div>
				</div>
				<div id="buffer" title="Buffers"><img  src="imgs/buffer.png" ></img> </div><div id="bufferoptions">
					<div id="sphere" title="Sphere Buffer"><img id="sphereimage" src="imgs/sphere.png" ></img> </div>
					<div id="cylinder" title="Cylinder Buffer"><img id="cylinderimage" src="imgs/cylinder.png" ></img> </div>
					<div id="box" title="Cube Buffer"><img id="boximage" src="imgs/box.png" ></img> </div>
				</div>
				<div id="select" title="Select" ><img src="imgs/select.png" ></img>  </div><div id="selectoptions">
					<div id="singleselect" title="Pointer Select"><img id="singleselectimage" src="imgs/singleselect.png" ></img> </div>
					<div id="multiselect" title="Marquee Select"><img id="multiselectimage" src="imgs/multiselect.png" ></img> </div>
				</div>
				<div id="objectedit" title="Object Edit"><img  src="imgs/objectedit.png" ></img> </div><div id="objecteditoptions">
					<div id="delete" title="Delete Selected"><img id="deleteimage" src="imgs/delete.png" ></img> </div>
					<div id="copy" title="Copy Selected"><img id="copyimage" src="imgs/copy.png" ></img> </div>
					<div id="translate" title="Translate Selected"><img id="translateimage" src="imgs/translate.png" ></img> </div>
					<div id="rotate" title="Rotate Selected"><img id="rotateimage" src="imgs/rotate.png" ></img> </div>
					<div id="scale" title="Scale Selected"><img id="scaleimage" src="imgs/scale.png" ></img> </div>
				</div>

				<div id="vertexedit" title="Vertex Level Editing"><img id="verteximage" src="imgs/vertexedit.png" ></img> </div></div>
				<div id="mode" title="GIS Mode"> Visualise </div>
			</div>
		</div>

		<div id="main">
				<div id="info">
					<div id="layerscontainer">
						<div id="layers">
							<p class="titles">Layers</p>
							<script> var projectName = "<?php echo($_GET['projectName'])?>";console.log("Project Name" +projectName); </script>
							<?php
								$projectName=$_GET['projectName'];



									//$result = pg_query($db, "SELECT table_name FROM information_schema.tables  WHERE table_schema = 'public' ORDER BY table_name ASC");

									// claire ellul - get only the layers for a specific project
									if ($projectName) {
										$result =pg_query($db, "SELECT tablename,* FROM projectMetadata  WHERE projectName = '".$projectName."' ORDER BY tableName ASC");
									}
									else {
										$result =pg_query($db, "SELECT tablename,* FROM projectMetadata   ORDER BY tableName ASC");
									}

									if (!$result) {
									  echo "An error occurred.\n";
									  exit;
									}
									else {
										$layerList = array();
										$layerDetails = array();
										while ($layer = pg_fetch_array($result)) {
											$divCol = $layer[0] . "col";
											$divZoom = $layer[0] . "zoom";
											$divAtt = $layer[0] . "attributes";

											if ( strlen($layer[0]) > 15 )  { $layerAlias = substr($layer[0] , 0, 14) . "...";	}
											else { $layerAlias = $layer[0]; }
											echo "<br>";
											echo "<input type='checkbox' id='$layer[0]' style='vertical-align: middle; float:left; width:45px' />";
											echo "<span title='$layer[0]' class='layertext'> $layerAlias </span> <div id='$divCol' title='Layer Colour' style='width: 11px; height:11px; display: inline-block; margin-left: 5px; border: black; border-style: solid; vertical-align: middle; margin-bottom: 2px; cursor: pointer' /> </div>";
											echo "<div id='$divZoom' title='Zoom to Layer' style='width: 11px; height:11px; display: inline-block; margin-left: 5px; border: black; border-style: solid; vertical-align: middle; margin-bottom: 2px; cursor: pointer' /><img src='imgs/zoom.png' style='float: left'> </div>";
											echo "<div class='allattributes' title='Attributes' style=' background-color: #080808; width: 11px; height:11px; display: inline-block; margin-left: 5px; border: black; border-style: solid; vertical-align: middle; margin-bottom: 2px; cursor: pointer; line-height: 1em; text-align: center; text-indent:0px;' />a</div>";
											$layerDetails[$layer[0]] = $layer;
											array_push($layerList, $layer[0]);
											echo "<br>";
										}
										echo "<br><br><br>";
									}
							?>
							<script> var jsLayerList = <?php echo json_encode($layerList);?>;console.log("555555" +JSON.stringify(jsLayerList)); </script>
							<script> var jsLayerDetails = <?php echo json_encode($layerDetails);?>; console.log("555555"+jsLayerDetails["IfcWall"]); </script>
						</div>
					</div>
					<div id="attributes">
						<div id="attributestop">
							<p class="titles"> Attributes </p>  <div id="loadselected"> </div>
						</div>
						 <div id="attributesholder"> </div>
					</div>
				</div>
				<div id="container" tabindex=0>
				</div>
		</div>
		<div id="bottombar">
			Coordinate System: British National Grid (SRID: 27700)
			<div id="coords">
				X <div id="xcoord"> </div>
				Y <div id="ycoord"> </div>
				Z <div id="zcoord"> </div>
			</div>
		</div>
		<div id="select-marquee"></div>
		<div id="dialog"><div id="dialogtext"></div></div>

		<script>
			$(document).ready(function() {
				$(document).bind("contextmenu",function(e){
					console.log(e.target.nodeName)
					console.log($(e.target.id).closest(".attributetables").length > 0)
					if ((e.target.nodeName == "TR" ) || (e.target.nodeName == "TH" ) || (e.target.nodeName == "TABLE" ) || (e.target.nodeName == "TD" ) || (e.target.nodeName == "CAPTION" )) { return true }
					else { return false }
				}); <!-- DISABLE CONTEXT MENU -->
				$( "#loadselected" ).button( {label: "Get Selected", text: true} );
				$('#layers').perfectScrollbar({suppressScrollX: true, scrollYMarginOffset: 3});
				$('#attributes').perfectScrollbar({scrollXMarginOffset: 10});
			});
		</script>
		<script src="js/constants.js"></script>
		<script src="js/interactionSetup.js"></script>
		<script src="js/interactionProcess.js"></script>
		<script src="js/camera.js"></script>
		<script src="js/objectEdit.js"></script>
		<script src="js/vertexEdit.js"></script>
		<script src="js/geometryMigration.js"></script>
		<script src="js/initialiseScene.js"></script>
		<script src="js/buffer.js"></script>
		<script src="js/saveEdit.js"></script>
		<script src="js/multiSelect.js"></script>
		<script src="js/changeLayerColour.js"></script>
		<script src="js/attributes.js"></script>
		<script src="js/measurementFunctions.js"></script>
		<script src="js/lacuna.js"></script>
		<script src="js/processGeometry.js"></script>
<!--		<script src="js/ArrowControls.js"></script>
		<script src="js/setupArrowControls.js"></script>
-->
		<script src="js/toolbar.js"></script>
		<script src="js/layersattributes.js"></script> <!-- Page needs to have loaded first to run this script successfully! KEEP AT THE END -->
		<script src="js/layerbuttons.js"></script>

	</body>
</html>