		function is_inside_marquee(vector) {
			widthHalf = canvasWidth / 2
			heightHalf = canvasHeight / 2
			x2D = ( vector.x * widthHalf ) + widthHalf;
			y2D = - ( vector.y * heightHalf ) + heightHalf;

			marqueeMinY = marquee.position().top - canvasTopOffset
			marqueeMinX = marquee.position().left - canvasLeftOffset
			marqueeMaxX = marqueeMinX + marquee.width();
			marqueeMaxY = marqueeMinY + marquee.height();

			if ( ((x2D > marqueeMinX) && (x2D < marqueeMaxX)) &&
				 ((y2D > marqueeMinY) && (y2D < marqueeMaxY)) ) {
				return true;
				}
			else {
				return false
			}
		}
			function hoverrows() {
				$('tr').click(function () {
					$('tr').removeClass('selected');
					$(this).addClass('selected');
					selectedRow = $(this);
					tableName = selectedRow.closest('table').find("caption").first()[0].innerHTML ;
					td = $(selectedRow).children('td');
					trID = td[0].innerText;
					selectedObjectToGet = tableName + " " + trID
					if (LASTHIGHLIGHTED != "") {
						if (LASTHIGHLIGHTED.hasOwnProperty('material')) {
								LASTHIGHLIGHTED.material.color.setHex( 0xCCCCCC )
								if ( LASTHIGHLIGHTED.material.hasOwnProperty("ambient") ) { LASTHIGHLIGHTED.material.ambient.setHex ( 0xCCCCCC ) }
							}
						else if (LASTHIGHLIGHTED.hasOwnProperty('children')) {
							LASTHIGHLIGHTED.children.forEach( function( highlightmesh ) {
								highlightmesh.material.color.setHex( 0xCCCCCC );
								if ( highlightmesh.material.hasOwnProperty("ambient") ) { highlightmesh.material.ambient.setHex ( 0xCCCCCC ) }
							});
						}
					}
					SELECTED.sceneobject.forEach( function(selectedObject) {
						if (selectedObject.name === selectedObjectToGet) {
							if (selectedObject.hasOwnProperty('material')) {
								selectedObject.material.color.setHex( 0xFFCC00 )
								if (selectedObject.material.hasOwnProperty("ambient") ) { selectedObject.material.ambient.setHex ( 0xFFCC00 ) }
								LASTHIGHLIGHTED = selectedObject
							}
							else if (selectedObject.hasOwnProperty('children')) {
								selectedObject.children.forEach( function( highlightmesh ) {
									highlightmesh.material.color.setHex( 0xFFCC00 );
									if (highlightmesh.material.hasOwnProperty("ambient") ) { highlightmesh.material.ambient.setHex ( 0xFFCC00 ) }
									LASTHIGHLIGHTED = selectedObject
								});
							}
						}
					});
				});
			}

function processClick(){
	console.log("click");
			if (CLICKDISTANCE === true) {
				//console.log(intersectedPoint);
				if ((firstClick === true) && (intersectedPoint != "")) {
					p1 = [intersectedPoint.x, intersectedPoint.y, intersectedPoint.z]
					//console.log(p1)
					firstClick = false
				}
				else if ((firstClick === false) && (intersectedPoint != null)) {
					p2 = [intersectedPoint.x, intersectedPoint.y, intersectedPoint.z]
					//console.log(p2)
					pointDistance = get_point_distance(p1, p2).toFixed(5)

					$("#dialogtext").text("The distance is " +  String(pointDistance) + "m");
					$("#dialog").dialog({ resizable: false,
								buttons: {
										Close: function () {
											$(this).dialog("close");
										}
							} });
					$('#dialog').dialog('option', 'title', 'Distance');
					firstClick = true
				}
			}

			if (AREA) {

				if (OBJECTDISTANCE === true)  {
					console.log("objectdistance false");
					if ((objectFirstClick === true) && ((intersectedObject != "") || (intersectedMesh != ""))) {
						if (intersectedObject instanceof THREE.Scene === false) {
							l1 = get_object_layer_id(intersectedObject)
							objectFirstClick = false
						}
						if (intersectedMesh instanceof THREE.Line === true) {
							l1 = get_object_layer_id(intersectedMesh)
							objectFirstClick = false
						}
						console.log(l1)
					}

					else if ((objectFirstClick === false) && ((intersectedObject != "") || (intersectedMesh != ""))) {
						if (intersectedObject instanceof THREE.Scene === false) {
							l2 = get_object_layer_id(intersectedObject)
							if ((l1[0] != l2[0]) && (l1[0] != l2[0])) {
								objectFirstClick = true
							}
						}
						if (intersectedMesh instanceof THREE.Line === true) {
							l2 = get_object_layer_id(intersectedMesh)
							if ((l1[0] != l2[0]) && (l1[0] != l2[0])) {
								objectFirstClick = true
							}
						}
					}
				}

				if (AREA == true)  {

					if ((intersectedObject != "") || (intersectedMesh != "")) {
						a = 0
						if (intersectedObject instanceof THREE.Scene === false) {
							intersectedObject.children.forEach( function(child) {
								vertices = child.geometry.vertices
								faces = child.geometry.faces
								console.log(vertices, faces);
								faces.forEach( function(triangle) {
									//console.log(vertices[triangle.a].x, vertices[triangle.a].y, vertices[triangle.a].z)
									d1 = distance(vertices[triangle.a].x, vertices[triangle.a].y, vertices[triangle.a].z, vertices[triangle.b].x, vertices[triangle.b].y, vertices[triangle.b].z )
									d2 = distance(vertices[triangle.b].x, vertices[triangle.b].y, vertices[triangle.b].z, vertices[triangle.c].x, vertices[triangle.c].y, vertices[triangle.c].z )
									d3 = distance(vertices[triangle.c].x, vertices[triangle.c].y, vertices[triangle.c].z, vertices[triangle.a].x, vertices[triangle.a].y, vertices[triangle.a].z )
									//console.log(area(d1, d2, d3));
									//console.log(a)
									console.log(a)
									console.log(a, d1, d2, d3)
									console.log(typeof(a), typeof(d1), typeof(d2), typeof(d3))

									a += get_area(d1, d2, d3)
								});
							});

							$("#dialogtext").text("Area: " + a + "m2");
							$("#dialog").dialog({ resizable: false,
								buttons: {
										Close: function () {
											$(this).dialog("close");
										}
							} });
							$('#dialog').dialog('option', 'title', 'Area');
						}

						else if (intersectedMesh instanceof THREE.Line === false && intersectedObject instanceof THREE.Scene === true) {
							vertices = intersectedMesh.geometry.vertices
							faces = intersectedMesh.geometry.faces

							faces.forEach( function(triangle) {
								//console.log(triangle)
								d1 = distance(vertices[triangle.a].x, vertices[triangle.a].y, vertices[triangle.a].z, vertices[triangle.b].x, vertices[triangle.b].y, vertices[triangle.b].z )
								d2 = distance(vertices[triangle.b].x, vertices[triangle.b].y, vertices[triangle.b].z, vertices[triangle.c].x, vertices[triangle.c].y, vertices[triangle.c].z )
								d3 = distance(vertices[triangle.c].x, vertices[triangle.c].y, vertices[triangle.c].z, vertices[triangle.a].x, vertices[triangle.a].y, vertices[triangle.a].z )

								a += get_area(d1, d2, d3)
							});

							$("#dialogtext").text("Area: " + a + "meters squared");
							$("#dialog").dialog({ resizable: false,
								buttons: {
										Close: function () {
											$(this).dialog("close");
										}
							} });
							$('#dialog').dialog('option', 'title', 'Area');

						}

					}

				}
			}

			if (intersectedPoint != "") {
					$("#xcoordinate").val( intersectedPoint.x )
					$("#ycoordinate").val( intersectedPoint.y )
					$("#zcoordinate").val( intersectedPoint.z )
			}

			// VERTEX EDITING
			if (VERTEX || VERTEX_EDIT) {
				console.log("Vertex or Vertex edit true");
				// IF VERTEX BUTTON CLICKED OR VERTEX EDITING ENABLED
				 vertexObject = intersectedObject
				 vertexMesh = intersectedMesh
				 vertexPoint = intersectedPoint

				if (VERTEX_EDIT === false) { vertexEditing() }
				if (VERTEX_EDIT === true) { vertexPicking() }
			}

		}
